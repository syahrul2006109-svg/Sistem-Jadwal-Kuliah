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
        alert('ID dosen tidak ditemukan. Silakan login ulang sebagai dosen.');
        window.location='login.php';
    </script>";
    exit;
}

$id_dosen_login = $_SESSION['id_dosen'];

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/* AMBIL DATA DOSEN LOGIN */
$stmtDosen = mysqli_prepare($conn, "SELECT * FROM dosen WHERE id_dosen = ? LIMIT 1");
mysqli_stmt_bind_param($stmtDosen, "i", $id_dosen_login);
mysqli_stmt_execute($stmtDosen);
$resultDosen = mysqli_stmt_get_result($stmtDosen);
$dosenLogin = mysqli_fetch_assoc($resultDosen);

if (!$dosenLogin) {
    echo "
    <script>
        alert('Data dosen tidak ditemukan di database.');
        window.location='login.php';
    </script>";
    exit;
}

/* DATA DROPDOWN */
$matkul = mysqli_query($conn, "SELECT * FROM mata_kuliah ORDER BY nama_matkul ASC");
$ruangan = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY nama_ruang ASC");

/* SIMPAN DATA */
if (isset($_POST['simpan'])) {

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
            window.location='tambah-Jadwal.php';
        </script>";

    } else {

        /*
        CEK BENTROK:
        - ruangan sama pada waktu yang bertabrakan
        - atau dosen yang sama punya jadwal bertabrakan
        */
        $cek = mysqli_prepare($conn, "
            SELECT COUNT(*) AS total
            FROM jadwal_kuliah
            WHERE hari = ?
            AND (
                id_ruang = ?
                OR id_dosen = ?
            )
            AND waktu_mulai < ?
            AND waktu_selesai > ?
        ");

        mysqli_stmt_bind_param(
            $cek,
            "siiss",
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
                window.location='tambah-Jadwal.php';
            </script>";

        } else {

            $stmt = mysqli_prepare($conn, "
                INSERT INTO jadwal_kuliah
                (id_matkul, id_dosen, id_ruang, gedung, hari, waktu_mulai, waktu_selesai)
                VALUES
                (?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "iiissss",
                $id_matkul,
                $id_dosen_login,
                $id_ruang,
                $gedung,
                $hari,
                $waktu_mulai,
                $waktu_selesai
            );

            $simpan = mysqli_stmt_execute($stmt);

            if ($simpan) {
                echo "
                <script>
                    alert('Jadwal berhasil ditambahkan.');
                    window.location='jadwal.php';
                </script>";
            } else {
                echo "
                <script>
                    alert('Jadwal gagal ditambahkan: " . mysqli_error($conn) . "');
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

<title>Tambah Jadwal Kuliah</title>

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
                <i class="fa-solid fa-calendar-plus"></i>
                Tambah Jadwal Kuliah
            </h1>
            
        </div>

        <a href="jadwal.php" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>

  
    <form method="POST">

        <div class="form-grid">

            <div class="form-group full">
                <label>Dosen Pengampu</label>
                <input type="text"
                       class="readonly"
                       value="<?php echo e($dosenLogin['nama_dosen']); ?>"
                       readonly>
            </div>

            <div class="form-group">
                <label>Mata Kuliah</label>
                <select name="id_matkul" required>
                    <option value="">-- Pilih Mata Kuliah --</option>

                    <?php while ($m = mysqli_fetch_assoc($matkul)) { ?>
                        <option value="<?php echo e($m['id_matkul']); ?>">
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
                        <option value="<?php echo e($r['id_ruang']); ?>">
                            <?php echo e($r['nama_ruang']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Gedung</label>
                <input type="text"
                       name="gedung"
                       placeholder="Contoh: Gedung A"
                       required>
            </div>

            <div class="form-group">
                <label>Hari</label>
                <select name="hari" required>
                    <option value="">-- Pilih Hari --</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>

            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" required>
            </div>

            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" required>
            </div>

        </div>

        <div class="action">
            <a href="jadwal.php" class="btn btn-cancel">
                Batal
            </a>

            <button type="submit" name="simpan" class="btn btn-save">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Jadwal
            </button>
        </div>

    </form>

</div>

</body>
</html>