<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$namaMahasiswa = $_SESSION['nama'];
$nimMahasiswa  = $_SESSION['nim'] ?? '-';

/*
|--------------------------------------------------------------------------
| AMBIL DATA MATA KULIAH
|--------------------------------------------------------------------------
| Data diambil dari tabel mata_kuliah.
| Jika mata kuliah sudah dipakai di jadwal_kuliah, nama dosen akan ikut tampil.
*/

$query = mysqli_query($conn, "
    SELECT 
        mk.id_matkul,
        mk.nama_matkul,

        GROUP_CONCAT(DISTINCT d.nama_dosen SEPARATOR ', ') AS dosen_pengampu,
        COUNT(DISTINCT jk.id_jadwal) AS total_jadwal

    FROM mata_kuliah mk

    LEFT JOIN jadwal_kuliah jk
    ON mk.id_matkul = jk.id_matkul

    LEFT JOIN dosen d
    ON jk.id_dosen = d.id_dosen

    GROUP BY mk.id_matkul, mk.nama_matkul

    ORDER BY mk.nama_matkul ASC
");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$totalMatkul = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Mata Kuliah Mahasiswa</title>

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
    color:#0f172a;
}

.container{
    width:100%;
    max-width:1250px;
    margin:auto;
}

/* HEADER */

.header{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    border-radius:28px;
    padding:35px;
    margin-bottom:28px;
    box-shadow:0 15px 35px rgba(37,99,235,0.18);
    position:relative;
    overflow:hidden;
}

.header::before{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    border-radius:50%;
    background:rgba(255,255,255,0.13);
    top:-100px;
    right:-80px;
}

.header-content{
    position:relative;
    z-index:1;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
}

.header h1{
    font-size:34px;
    font-weight:800;
    margin-bottom:8px;
}

.header p{
    color:#dbeafe;
    line-height:1.7;
}

.profile-box{
    background:rgba(255,255,255,0.16);
    border:1px solid rgba(255,255,255,0.25);
    padding:16px 20px;
    border-radius:20px;
    min-width:230px;
}

.profile-box strong{
    display:block;
    font-size:16px;
}

.profile-box span{
    color:#dbeafe;
    font-size:14px;
}

/* SUMMARY */

.summary{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:20px;
    margin-bottom:26px;
}

.summary-card{
    background:white;
    border:1px solid #dbeafe;
    border-radius:24px;
    padding:24px;
    box-shadow:0 12px 30px rgba(37,99,235,0.10);
}

.summary-icon{
    width:58px;
    height:58px;
    border-radius:18px;
    background:#dbeafe;
    color:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    margin-bottom:14px;
}

.summary-card h3{
    font-size:28px;
    font-weight:800;
}

.summary-card p{
    color:#64748b;
    font-size:14px;
    margin-top:4px;
}

/* SEARCH */

.search-box{
    background:white;
    border:1px solid #dbeafe;
    border-radius:18px;
    padding:16px 18px;
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:25px;
    box-shadow:0 10px 25px rgba(37,99,235,0.08);
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

/* GRID */

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(310px, 1fr));
    gap:22px;
}

.card{
    background:white;
    border:1px solid #dbeafe;
    border-radius:24px;
    padding:24px;
    box-shadow:0 12px 30px rgba(37,99,235,0.10);
    transition:0.25s;
}

.card:hover{
    transform:translateY(-5px);
}

.card-top{
    display:flex;
    gap:16px;
    align-items:center;
    margin-bottom:18px;
}

.card-icon{
    width:60px;
    height:60px;
    border-radius:18px;
    background:#dbeafe;
    color:#2563eb;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:24px;
    flex-shrink:0;
}

.card h3{
    font-size:21px;
    font-weight:800;
    color:#0f172a;
}

.card-id{
    color:#64748b;
    font-size:13px;
    margin-top:4px;
}

.info{
    margin-bottom:12px;
    color:#475569;
    line-height:1.7;
    font-size:14px;
}

.info strong{
    color:#0f172a;
}

.badge{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:8px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
    background:#dbeafe;
    color:#1d4ed8;
    margin-top:8px;
}

.badge.green{
    background:#dcfce7;
    color:#166534;
}

.badge.gray{
    background:#f1f5f9;
    color:#64748b;
}

/* BUTTON */

.btn{
    display:inline-flex;
    align-items:center;
    gap:9px;
    text-decoration:none;
    padding:13px 20px;
    border-radius:15px;
    font-weight:700;
    transition:0.25s;
    margin-top:30px;
}

.btn-back{
    background:#2563eb;
    color:white;
}

.btn-back:hover{
    background:#1d4ed8;
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(37,99,235,0.20);
}

/* EMPTY */

.empty{
    background:white;
    border:1px solid #dbeafe;
    border-radius:24px;
    padding:45px;
    text-align:center;
    color:#64748b;
    box-shadow:0 12px 30px rgba(37,99,235,0.10);
}

.empty i{
    font-size:44px;
    color:#2563eb;
    margin-bottom:15px;
}

.empty h2{
    color:#0f172a;
    margin-bottom:8px;
}

/* RESPONSIVE */

@media(max-width:900px){
    .header-content{
        flex-direction:column;
        align-items:flex-start;
    }

    .summary{
        grid-template-columns:1fr;
    }
}

@media(max-width:768px){
    body{
        padding:20px;
    }

    .header{
        padding:26px;
    }

    .header h1{
        font-size:28px;
    }

    .cards{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <div class="header-content">

            <div>
                <h1>
                    <i class="fa-solid fa-book-open"></i>
                    Mata Kuliah
                </h1>

                
            </div>

            <div class="profile-box">
                <strong><?php echo e($namaMahasiswa); ?></strong>
                <span>NIM: <?php echo e($nimMahasiswa); ?></span>
            </div>

        </div>
    </div>

   

    <?php if ($totalMatkul > 0) { ?>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari mata kuliah atau dosen...">
        </div>

        <div class="cards">

            <?php while ($d = mysqli_fetch_assoc($query)) { ?>

                <?php
                $dosenPengampu = $d['dosen_pengampu'];

                if (!$dosenPengampu) {
                    $dosenPengampu = "Belum ada dosen di jadwal";
                }

                $totalJadwal = (int)$d['total_jadwal'];
                ?>

                <div class="card">

                    <div class="card-top">
                        <div class="card-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>

                        <div>
                            <h3><?php echo e($d['nama_matkul']); ?></h3>
                            <div class="card-id">
                                Kode/ID: <?php echo e($d['id_matkul']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="info">
                        <strong>Dosen Pengampu:</strong><br>
                        <?php echo e($dosenPengampu); ?>
                    </div>

                    <div class="info">
                        <strong>Jumlah Jadwal:</strong>
                        <?php echo $totalJadwal; ?> jadwal
                    </div>

                    <?php if ($totalJadwal > 0) { ?>
                        <span class="badge green">
                            <i class="fa-solid fa-circle-check"></i>
                            Terjadwal
                        </span>
                    <?php } else { ?>
                        <span class="badge gray">
                            <i class="fa-solid fa-circle-info"></i>
                            Belum dijadwalkan
                        </span>
                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty">
            <i class="fa-solid fa-book"></i>
            <h2>Belum Ada Mata Kuliah</h2>
            <p>Data mata kuliah belum tersedia di database.</p>
        </div>

    <?php } ?>

    <a href="dashboard-user.php" class="btn btn-back">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Dashboard
    </a>

</div>

<script>
const searchInput = document.getElementById("searchInput");

if (searchInput) {
    const cards = document.querySelectorAll(".card");

    searchInput.addEventListener("keyup", function() {
        const keyword = searchInput.value.toLowerCase();

        cards.forEach(function(card) {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(keyword) ? "" : "none";
        });
    });
}
</script>

</body>
</html>