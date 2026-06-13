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
$prefill_id_ruang = $_GET['id_ruang'] ?? '';
$prefill_gedung = $_GET['gedung'] ?? '';
$prefill_hari = $_GET['hari'] ?? '';
$prefill_waktu_mulai = $_GET['waktu_mulai'] ?? '';
$prefill_waktu_selesai = $_GET['waktu_selesai'] ?? '';

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}


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


$matkul = mysqli_query($conn, "SELECT * FROM mata_kuliah ORDER BY nama_matkul ASC");
$ruangan = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY nama_ruang ASC");


if (isset($_POST['simpan'])) {

    $id_matkul     = (int)($_POST['id_matkul'] ?? 0);
    $id_ruang      = (int)($_POST['id_ruang'] ?? 0);
    $kelas         = trim($_POST['kelas'] ?? '');
    $gedung        = trim($_POST['gedung'] ?? '');
    $hari          = trim($_POST['hari'] ?? '');
    $waktu_mulai   = trim($_POST['waktu_mulai'] ?? '');
    $waktu_selesai = trim($_POST['waktu_selesai'] ?? '');

    if ($id_matkul <= 0 || $id_ruang <= 0 || $kelas === '' || $gedung === '' || $hari === '' || $waktu_mulai === '' || $waktu_selesai === '') {

        echo "
        <script>
            alert('Data jadwal belum lengkap. Pastikan mata kuliah, kelas, ruangan, gedung, hari, dan jam sudah diisi.');
            window.history.back();
        </script>";
        exit;

    } elseif ($waktu_mulai >= $waktu_selesai) {

        echo "
        <script>
            alert('Waktu selesai harus lebih besar dari waktu mulai.');
            window.history.back();
        </script>";
        exit;

    } else {

        // Cek bentrok hanya pada HARI YANG SAMA.
        // Bentrok terjadi kalau waktu overlap dan ruangan/dosen/kelas sama.
        $cek = mysqli_prepare($conn, "
            SELECT 
                jk.hari,
                jk.waktu_mulai,
                jk.waktu_selesai,
                jk.kelas,
                mk.nama_matkul,
                d.nama_dosen,
                r.nama_ruang
            FROM jadwal_kuliah jk
            LEFT JOIN mata_kuliah mk ON jk.id_matkul = mk.id_matkul
            LEFT JOIN dosen d ON jk.id_dosen = d.id_dosen
            LEFT JOIN ruangan r ON jk.id_ruang = r.id_ruang
            WHERE LOWER(TRIM(jk.hari)) = LOWER(TRIM(?))
            AND jk.waktu_mulai < ?
            AND jk.waktu_selesai > ?
            AND (
                jk.id_ruang = ?
                OR jk.id_dosen = ?
                OR jk.kelas = ?
            )
            LIMIT 1
        ");

        mysqli_stmt_bind_param(
            $cek,
            "sssiis",
            $hari,
            $waktu_selesai,
            $waktu_mulai,
            $id_ruang,
            $id_dosen_login,
            $kelas
        );

        mysqli_stmt_execute($cek);
        $resultCek = mysqli_stmt_get_result($cek);
        $bentrok = mysqli_fetch_assoc($resultCek);

        if ($bentrok) {

            $pesanBentrok = "Jadwal bentrok pada hari " . $bentrok['hari'] . "\\n" .
                "Mata kuliah: " . ($bentrok['nama_matkul'] ?? '-') . "\\n" .
                "Kelas: " . ($bentrok['kelas'] ?? '-') . "\\n" .
                "Dosen: " . ($bentrok['nama_dosen'] ?? '-') . "\\n" .
                "Ruangan: " . ($bentrok['nama_ruang'] ?? '-') . "\\n" .
                "Jam: " . substr($bentrok['waktu_mulai'], 0, 5) . " - " . substr($bentrok['waktu_selesai'], 0, 5);

            echo "
            <script>
                alert(" . json_encode($pesanBentrok) . ");
                window.history.back();
            </script>";
            exit;

        } else {

            $stmt = mysqli_prepare($conn, "
                INSERT INTO jadwal_kuliah
                (id_matkul, id_dosen, id_ruang, kelas, gedung, hari, waktu_mulai, waktu_selesai)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "iiisssss",
                $id_matkul,
                $id_dosen_login,
                $id_ruang,
                $kelas,
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
                exit;
            } else {
                echo "
                <script>
                    alert('Jadwal gagal ditambahkan: " . mysqli_error($conn) . "');
                    window.history.back();
                </script>";
                exit;
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
    <label>Kelas</label>
    <select name="kelas" required>
        <option value="">-- Pilih Kelas --</option>

        <optgroup label="Format Roster">
            <option value="IK24">IK24</option>
            <option value="IK23">IK23</option>

            <option value="MA24">MA24</option>
            <option value="MA23">MA23</option>

            <option value="SI24">SI24</option>
            <option value="SI23">SI23</option>

            <option value="TP24">TP24</option>
            <option value="TP23">TP23</option>

            <option value="SA24">SA24</option>

            <option value="SD24">SD24</option>
            <option value="SD23">SD23</option>

            <option value="SE24">SE24</option>
            <option value="SE23">SE23</option>

            <option value="MG24">MG24</option>
            <option value="MG23">MG23</option>

            <option value="BT24">BT24</option>
            <option value="BT23">BT23</option>

            <option value="TS24">TS24</option>
            <option value="AR24">AR24</option>
        </optgroup>
    </select>
</div>

            <div class="form-group">
                <label>Ruangan</label>
                <select name="id_ruang" required>
                    <option value="">-- Pilih Ruangan --</option>

                    <?php while ($r = mysqli_fetch_assoc($ruangan)) { ?>
                        <option value="<?php echo e($r['id_ruang']); ?>"
                            <?php echo ((string)$prefill_id_ruang === (string)$r['id_ruang']) ? 'selected' : ''; ?>>
                            <?php echo e($r['nama_ruang']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Gedung</label>
                <input type="text"
                       name="gedung"
                       value="<?php echo e($prefill_gedung); ?>"
                        placeholder="Contoh: Gedung A"
                        required>
            </div>

            <div class="form-group">
                <label>Hari</label>
                <select name="hari" required>
                    <option value="">-- Pilih Hari --</option>
                    <option value="Senin" <?= $prefill_hari === 'Senin' ? 'selected' : '' ?>>Senin</option>
                    <option value="Selasa" <?= $prefill_hari === 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                    <option value="Rabu" <?= $prefill_hari === 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                    <option value="Kamis" <?= $prefill_hari === 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                    <option value="Jumat" <?= $prefill_hari === 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                    <option value="Sabtu" <?= $prefill_hari === 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                    <option value="Minggu" <?= $prefill_hari === 'Minggu' ? 'selected' : '' ?>>Minggu</option>
                </select>
            </div>

            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="waktu_mulai" value="<?php echo e(substr($prefill_waktu_mulai, 0, 5)); ?>" required>
            </div>

            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="waktu_selesai" value="<?php echo e(substr($prefill_waktu_selesai, 0, 5)); ?>" required>
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
