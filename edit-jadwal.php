<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['id_dosen'])) {
    echo "
    <script>
        alert('Akses ditolak. Silakan login sebagai dosen.');
        window.location='login.php';
    </script>";
    exit;
}

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function selected($value, $current) {
    return ((string)$value === (string)$current) ? 'selected' : '';
}

$id_dosen_login = (int)$_SESSION['id_dosen'];
$id_jadwal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_jadwal <= 0) {
    echo "
    <script>
        alert('ID jadwal tidak valid.');
        window.location='jadwal.php';
    </script>";
    exit;
}

/* AMBIL DATA JADWAL YANG MAU DIEDIT */
$stmt = mysqli_prepare($conn, "
    SELECT jk.*, d.nama_dosen
    FROM jadwal_kuliah jk
    LEFT JOIN dosen d ON jk.id_dosen = d.id_dosen
    WHERE jk.id_jadwal = ?
    AND jk.id_dosen = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ii", $id_jadwal, $id_dosen_login);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "
    <script>
        alert('Akses ditolak. Anda hanya bisa mengedit jadwal yang Anda tambahkan sendiri.');
        window.location='jadwal.php';
    </script>";
    exit;
}

$jadwal = mysqli_fetch_assoc($result);

/* DATA DROPDOWN */
$matkul = mysqli_query($conn, "SELECT * FROM mata_kuliah ORDER BY nama_matkul ASC");
$ruangan = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY nama_ruang ASC");

/* PROSES UPDATE */
if (isset($_POST['update'])) {

    $id_matkul     = (int)$_POST['id_matkul'];
    $id_ruang      = (int)$_POST['id_ruang'];
    $gedung        = trim($_POST['gedung']);
    $hari          = trim($_POST['hari']);
    $waktu_mulai   = trim($_POST['waktu_mulai']);
    $waktu_selesai = trim($_POST['waktu_selesai']);

    if ($waktu_mulai >= $waktu_selesai) {

        echo "
        <script>
            alert('Waktu selesai harus lebih besar dari waktu mulai.');
            window.location='edit-jadwal.php?id=$id_jadwal';
        </script>";

    } else {

        /*
        CEK BENTROK, tapi abaikan jadwal yang sedang diedit.
        Bentrok kalau:
        - hari sama
        - ruangan sama atau dosen sama
        - waktu saling bertabrakan
        */
        $cek = mysqli_prepare($conn, "
            SELECT COUNT(*) AS total
            FROM jadwal_kuliah
            WHERE id_jadwal != ?
            AND hari = ?
            AND (
                id_ruang = ?
                OR id_dosen = ?
            )
            AND waktu_mulai < ?
            AND waktu_selesai > ?
        ");

        mysqli_stmt_bind_param(
            $cek,
            "isiiss",
            $id_jadwal,
            $hari,
            $id_ruang,
            $id_dosen_login,
            $waktu_selesai,
            $waktu_mulai
        );

        mysqli_stmt_execute($cek);
        $resultCek = mysqli_stmt_get_result($cek);
        $dataCek = mysqli_fetch_assoc($resultCek);

        if ($dataCek['total'] > 0) {

            echo "
            <script>
                alert('Jadwal bentrok! Ruangan atau dosen sudah memiliki jadwal di waktu tersebut.');
                window.location='edit-jadwal.php?id=$id_jadwal';
            </script>";

        } else {

            $update = mysqli_prepare($conn, "
                UPDATE jadwal_kuliah
                SET 
                    id_matkul = ?,
                    id_ruang = ?,
                    gedung = ?,
                    hari = ?,
                    waktu_mulai = ?,
                    waktu_selesai = ?
                WHERE id_jadwal = ?
                AND id_dosen = ?
            ");

            mysqli_stmt_bind_param(
                $update,
                "iissssii",
                $id_matkul,
                $id_ruang,
                $gedung,
                $hari,
                $waktu_mulai,
                $waktu_selesai,
                $id_jadwal,
                $id_dosen_login
            );

            $resultUpdate = mysqli_stmt_execute($update);

            if ($resultUpdate) {
                echo "
                <script>
                    alert('Jadwal berhasil diperbarui.');
                    window.location='jadwal.php';
                </script>";
            } else {
                echo "
                <script>
                    alert('Jadwal gagal diperbarui: " . mysqli_error($conn) . "');
                </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Jadwal Kuliah</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    min-height:100vh;
    background:#eef5ff;
    padding:38px;
    color:#071633;
}

.container{
    width:100%;
    max-width:980px;
    margin:auto;
    background:white;
    border-radius:28px;
    padding:36px;
    box-shadow:0 15px 35px rgba(37,99,235,0.13);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:30px;
}

.title h1{
    font-size:32px;
    font-weight:800;
}

.title p{
    color:#64748b;
    margin-top:8px;
    font-size:15px;
}

.btn{
    border:none;
    text-decoration:none;
    padding:13px 21px;
    border-radius:15px;
    font-weight:700;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:9px;
    transition:0.25s;
    font-size:14px;
}

.btn:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(37,99,235,0.18);
}

.btn-back,
.btn-cancel{
    background:#dbeafe;
    color:#1d4ed8;
}

.btn-save{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
}

.info-box{
    background:#dbeafe;
    color:#1e3a8a;
    padding:18px 20px;
    border-radius:18px;
    margin-bottom:28px;
    line-height:1.7;
    font-size:15px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px 24px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group.full{
    grid-column:1 / 3;
}

label{
    font-size:15px;
    font-weight:700;
    color:#1e293b;
    margin-bottom:9px;
}

input,
select{
    width:100%;
    padding:16px 17px;
    border:1px solid #dbeafe;
    border-radius:15px;
    outline:none;
    background:#f8fbff;
    color:#071633;
    font-size:15px;
}

input:focus,
select:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,0.10);
}

.readonly{
    background:#eaf2ff;
    color:#1d4ed8;
    font-weight:800;
}

.action{
    display:flex;
    justify-content:flex-end;
    gap:14px;
    margin-top:34px;
}

@media(max-width:768px){
    body{
        padding:18px;
    }

    .container{
        padding:25px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }

    .title h1{
        font-size:26px;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .form-group.full{
        grid-column:1;
    }

    .action{
        flex-direction:column;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <div class="title">
            <h1>
                <i class="fa-solid fa-calendar-pen"></i>
                Edit Jadwal Kuliah
            </h1>
            <p>Ubah data jadwal kuliah yang sudah pernah Anda tambahkan.</p>
        </div>

        <a href="jadwal.php" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="info-box">
        <strong>Catatan:</strong>
        Data lama otomatis ditampilkan. Anda hanya perlu mengganti bagian yang ingin diubah.
        Dosen tidak dapat mengubah jadwal milik dosen lain.
    </div>

    <form method="POST">

        <div class="form-grid">

            <div class="form-group full">
                <label>Dosen Pengampu</label>
                <input type="text"
                       class="readonly"
                       value="<?php echo e($jadwal['nama_dosen']); ?>"
                       readonly>
            </div>

            <div class="form-group">
                <label>Mata Kuliah</label>
                <select name="id_matkul" required>
                    <option value="">-- Pilih Mata Kuliah --</option>

                    <?php while ($m = mysqli_fetch_assoc($matkul)) { ?>
                        <option value="<?php echo e($m['id_matkul']); ?>"
                            <?php echo selected($m['id_matkul'], $jadwal['id_matkul']); ?>>
                            <?php echo e($m['nama_matkul']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Ruangan</label>
                <select name="id_ruang" required>
                    <option value="">-- Pilih Ruangan --</option>

                    <?php while ($r = mysqli_fetch_assoc($ruangan)) { ?>
                        <option value="<?php echo e($r['id_ruang']); ?>"
                            <?php echo selected($r['id_ruang'], $jadwal['id_ruang']); ?>>
                            <?php echo e($r['nama_ruang']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Gedung</label>
                <input type="text"
                       name="gedung"
                       value="<?php echo e($jadwal['gedung']); ?>"
                       placeholder="Contoh: Gedung A"
                       required>
            </div>

            <div class="form-group">
                <label>Hari</label>
                <select name="hari" required>
                    <option value="">-- Pilih Hari --</option>
                    <option value="Senin" <?php echo selected('Senin', $jadwal['hari']); ?>>Senin</option>
                    <option value="Selasa" <?php echo selected('Selasa', $jadwal['hari']); ?>>Selasa</option>
                    <option value="Rabu" <?php echo selected('Rabu', $jadwal['hari']); ?>>Rabu</option>
                    <option value="Kamis" <?php echo selected('Kamis', $jadwal['hari']); ?>>Kamis</option>
                    <option value="Jumat" <?php echo selected('Jumat', $jadwal['hari']); ?>>Jumat</option>
                    <option value="Sabtu" <?php echo selected('Sabtu', $jadwal['hari']); ?>>Sabtu</option>
                    <option value="Minggu" <?php echo selected('Minggu', $jadwal['hari']); ?>>Minggu</option>
                </select>
            </div>

            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time"
                       name="waktu_mulai"
                       value="<?php echo e(substr($jadwal['waktu_mulai'], 0, 5)); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time"
                       name="waktu_selesai"
                       value="<?php echo e(substr($jadwal['waktu_selesai'], 0, 5)); ?>"
                       required>
            </div>

        </div>

        <div class="action">
            <a href="jadwal.php" class="btn btn-cancel">
                Batal
            </a>

            <button type="submit" name="update" class="btn btn-save">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

</body>
</html>