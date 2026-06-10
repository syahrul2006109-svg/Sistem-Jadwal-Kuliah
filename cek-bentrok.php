<?php
session_start();
include "connect.php";

/* CEK LOGIN */
if (!isset($_SESSION['nama'])) {
    echo "
    <script>
        alert('Session login tidak ditemukan. Silakan login ulang.');
        window.location='login.php';
    </script>";
    exit;
}

/* FUNGSI AMAN OUTPUT */
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/* TENTUKAN TOMBOL KEMBALI */
$role = $_SESSION['role'] ?? '';

if ($role == "Admin" || $role == "Dosen") {
    $backUrl = "dashboard-admin.php";
} else {
    $backUrl = "dashboard-user.php";
}

/* 
   DETEKSI BENTROK:
   - Hari sama
   - Waktu bertabrakan
   - Ruangan sama ATAU dosen sama
*/
$query = "
SELECT
    j1.id_jadwal AS id_jadwal_1,
    j2.id_jadwal AS id_jadwal_2,

    j1.hari,
    j1.waktu_mulai AS mulai_1,
    j1.waktu_selesai AS selesai_1,
    j2.waktu_mulai AS mulai_2,
    j2.waktu_selesai AS selesai_2,

    j1.gedung AS gedung_1,
    j2.gedung AS gedung_2,

    j1.id_dosen AS id_dosen_1,
    j2.id_dosen AS id_dosen_2,
    j1.id_ruang AS id_ruang_1,
    j2.id_ruang AS id_ruang_2,

    mk1.nama_matkul AS matkul_1,
    mk2.nama_matkul AS matkul_2,

    d1.nama_dosen AS dosen_1,
    d2.nama_dosen AS dosen_2,

    r1.nama_ruang AS ruang_1,
    r2.nama_ruang AS ruang_2

FROM jadwal_kuliah j1

JOIN jadwal_kuliah j2
ON j1.id_jadwal < j2.id_jadwal
AND j1.hari = j2.hari
AND j1.waktu_mulai < j2.waktu_selesai
AND j1.waktu_selesai > j2.waktu_mulai
AND (
    j1.id_ruang = j2.id_ruang
    OR j1.id_dosen = j2.id_dosen
)

LEFT JOIN mata_kuliah mk1 ON j1.id_matkul = mk1.id_matkul
LEFT JOIN mata_kuliah mk2 ON j2.id_matkul = mk2.id_matkul

LEFT JOIN dosen d1 ON j1.id_dosen = d1.id_dosen
LEFT JOIN dosen d2 ON j2.id_dosen = d2.id_dosen

LEFT JOIN ruangan r1 ON j1.id_ruang = r1.id_ruang
LEFT JOIN ruangan r2 ON j2.id_ruang = r2.id_ruang

ORDER BY
FIELD(j1.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
j1.waktu_mulai ASC
";

$data = mysqli_query($conn, $query);

if (!$data) {
    die("Query error: " . mysqli_error($conn));
}

$totalBentrok = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Deteksi Jadwal Bentrok</title>

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
    padding:35px;
    color:#071633;
}

.container{
    width:100%;
    max-width:1350px;
    margin:auto;
    background:white;
    border-radius:28px;
    padding:34px;
    box-shadow:0 15px 35px rgba(37,99,235,0.13);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:28px;
}

.title h1{
    font-size:32px;
    font-weight:800;
    color:#071633;
}

.title p{
    color:#64748b;
    margin-top:8px;
    font-size:15px;
}

.btn{
    border:none;
    text-decoration:none;
    padding:13px 20px;
    border-radius:15px;
    font-weight:700;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:9px;
    transition:0.25s;
    font-size:14px;
}

.btn:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(37,99,235,0.18);
}

.btn-back{
    background:#dbeafe;
    color:#1d4ed8;
}

.summary{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:18px;
    margin-bottom:25px;
}

.summary-card{
    border-radius:22px;
    padding:22px;
    border:1px solid #dbeafe;
    background:#f8fbff;
}

.summary-card.danger{
    background:#fee2e2;
    border-color:#fecaca;
    color:#991b1b;
}

.summary-card.success{
    background:#dcfce7;
    border-color:#bbf7d0;
    color:#166534;
}

.summary-card h3{
    font-size:18px;
    margin-bottom:7px;
}

.summary-card p{
    font-size:14px;
    line-height:1.7;
}

.search-box{
    margin-bottom:22px;
    display:flex;
    align-items:center;
    gap:12px;
    background:#f8fbff;
    border:1px solid #dbeafe;
    padding:16px 18px;
    border-radius:17px;
}

.search-box i{
    color:#64748b;
}

.search-box input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    font-size:15px;
    color:#0f172a;
}

.table-box{
    overflow-x:auto;
    border-radius:22px;
    border:1px solid #dbeafe;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:1250px;
}

th{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    text-align:left;
    padding:17px;
    font-size:14px;
    white-space:nowrap;
}

td{
    padding:16px;
    border-bottom:1px solid #e5efff;
    color:#1e293b;
    font-size:14px;
    vertical-align:top;
}

tr:hover td{
    background:#f8fbff;
}

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:7px 13px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    white-space:nowrap;
}

.badge-danger{
    background:#fee2e2;
    color:#991b1b;
}

.badge-blue{
    background:#dbeafe;
    color:#1d4ed8;
}

.schedule-box{
    background:#f8fbff;
    border:1px solid #dbeafe;
    border-radius:15px;
    padding:13px;
    line-height:1.8;
}

.schedule-box strong{
    color:#071633;
}

.empty{
    text-align:center;
    padding:45px 25px;
    color:#166534;
    background:#dcfce7;
    border-radius:22px;
    border:1px solid #bbf7d0;
}

.empty i{
    display:block;
    font-size:45px;
    margin-bottom:12px;
}

@media(max-width:768px){
    body{
        padding:18px;
    }

    .container{
        padding:24px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }

    .title h1{
        font-size:25px;
    }

    .summary{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <div class="title">
            <h1>
                <i class="fa-solid fa-triangle-exclamation"></i>
                Deteksi Jadwal Bentrok
            </h1>
            <p>
                Sistem mendeteksi jadwal yang bertabrakan berdasarkan hari, jam, dosen, dan ruangan.
            </p>
        </div>

        <a href="<?php echo e($backUrl); ?>" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="summary">

        <?php if ($totalBentrok > 0) { ?>
            <div class="summary-card danger">
                <h3>
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo $totalBentrok; ?> Konflik Terdeteksi
                </h3>
                <p>
                    Ada jadwal yang memiliki waktu bertabrakan pada ruangan atau dosen yang sama.
                </p>
            </div>
        <?php } else { ?>
            <div class="summary-card success">
                <h3>
                    <i class="fa-solid fa-circle-check"></i>
                    Tidak Ada Konflik
                </h3>
                <p>
                    Semua jadwal aman. Tidak ada jadwal dosen atau ruangan yang saling bertabrakan.
                </p>
            </div>
        <?php } ?>

        <div class="summary-card">
            <h3>
                <i class="fa-solid fa-circle-info"></i>
                Cara Sistem Mengecek
            </h3>
            <p>
                Jadwal dianggap bentrok jika hari sama, waktu saling bertabrakan,
                lalu ruangan sama atau dosen yang mengajar sama.
            </p>
        </div>

    </div>

    <?php if ($totalBentrok > 0) { ?>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari konflik berdasarkan hari, dosen, mata kuliah, ruangan, atau gedung...">
        </div>

        <div class="table-box">
            <table id="bentrokTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Konflik</th>
                        <th>Hari</th>
                        <th>Jadwal 1</th>
                        <th>Jadwal 2</th>
                        <th>Waktu Bentrok</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = 1; ?>
                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>

                        <?php
                        $konflikRuangan = ((int)$d['id_ruang_1'] === (int)$d['id_ruang_2']);
                        $konflikDosen   = ((int)$d['id_dosen_1'] === (int)$d['id_dosen_2']);

                        if ($konflikRuangan && $konflikDosen) {
                            $jenisKonflik = "Dosen & Ruangan";
                            $keterangan = "Dosen dan ruangan sama pada waktu yang bertabrakan.";
                        } elseif ($konflikRuangan) {
                            $jenisKonflik = "Ruangan Bentrok";
                            $keterangan = "Ruangan yang sama dipakai oleh dua jadwal pada waktu yang bertabrakan.";
                        } elseif ($konflikDosen) {
                            $jenisKonflik = "Dosen Bentrok";
                            $keterangan = "Dosen yang sama memiliki dua jadwal pada waktu yang bertabrakan.";
                        } else {
                            $jenisKonflik = "Bentrok";
                            $keterangan = "Jadwal bertabrakan.";
                        }
                        ?>

                        <tr>
                            <td><?php echo $no++; ?></td>

                            <td>
                                <span class="badge badge-danger">
                                    <?php echo e($jenisKonflik); ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    <?php echo e($d['hari']); ?>
                                </span>
                            </td>

                            <td>
                                <div class="schedule-box">
                                    <strong>ID Jadwal:</strong> <?php echo e($d['id_jadwal_1']); ?><br>
                                    <strong>Mata Kuliah:</strong> <?php echo e($d['matkul_1'] ?: 'Tidak ditemukan'); ?><br>
                                    <strong>Dosen:</strong> <?php echo e($d['dosen_1'] ?: 'Tidak ditemukan'); ?><br>
                                    <strong>Ruangan:</strong> <?php echo e($d['ruang_1'] ?: 'Tidak ditemukan'); ?><br>
                                    <strong>Gedung:</strong> <?php echo e($d['gedung_1']); ?>
                                </div>
                            </td>

                            <td>
                                <div class="schedule-box">
                                    <strong>ID Jadwal:</strong> <?php echo e($d['id_jadwal_2']); ?><br>
                                    <strong>Mata Kuliah:</strong> <?php echo e($d['matkul_2'] ?: 'Tidak ditemukan'); ?><br>
                                    <strong>Dosen:</strong> <?php echo e($d['dosen_2'] ?: 'Tidak ditemukan'); ?><br>
                                    <strong>Ruangan:</strong> <?php echo e($d['ruang_2'] ?: 'Tidak ditemukan'); ?><br>
                                    <strong>Gedung:</strong> <?php echo e($d['gedung_2']); ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-danger">
                                    <?php echo e(substr($d['mulai_1'], 0, 5)); ?>
                                    -
                                    <?php echo e(substr($d['selesai_1'], 0, 5)); ?>
                                </span>

                                <br><br>

                                <span class="badge badge-danger">
                                    <?php echo e(substr($d['mulai_2'], 0, 5)); ?>
                                    -
                                    <?php echo e(substr($d['selesai_2'], 0, 5)); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo e($keterangan); ?>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>

    <?php } else { ?>

        <div class="empty">
            <i class="fa-solid fa-circle-check"></i>
            <h3>Semua Jadwal Aman</h3>
            <p>Tidak ada jadwal kuliah yang bentrok berdasarkan data saat ini.</p>
        </div>

    <?php } ?>

</div>

<script>
const searchInput = document.getElementById("searchInput");

if (searchInput) {
    const rows = document.querySelectorAll("#bentrokTable tbody tr");

    searchInput.addEventListener("keyup", function() {
        const keyword = searchInput.value.toLowerCase();

        rows.forEach(function(row) {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(keyword) ? "" : "none";
        });
    });
}
</script>

</body>
</html>