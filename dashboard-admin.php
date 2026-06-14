<?php
session_start();
include "connect.php";


if (!isset($conn) || $conn->connect_error) {
    die("Koneksi database gagal. Cek file connect.php dan nama database.");
}

$conn->set_charset("utf8mb4");


if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}


$roleLogin = $_SESSION['role'] ?? '';

if (!in_array($roleLogin, ['Admin', 'Dosen'])) {
    header("Location: login.php");
    exit;
}


function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function namaHariIndonesia($hariInggris) {
    $hari = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    return $hari[$hariInggris] ?? $hariInggris;
}

function tableExists($conn, $table) {
    $tableSafe = mysqli_real_escape_string($conn, $table);
    $query = mysqli_query($conn, "SHOW TABLES LIKE '$tableSafe'");

    return $query && mysqli_num_rows($query) > 0;
}

function columnExists($conn, $table, $column) {
    if (!tableExists($conn, $table)) {
        return false;
    }

    $tableSafe = str_replace("`", "``", $table);
    $columnSafe = mysqli_real_escape_string($conn, $column);

    $query = mysqli_query($conn, "SHOW COLUMNS FROM `$tableSafe` LIKE '$columnSafe'");

    return $query && mysqli_num_rows($query) > 0;
}

function countTable($conn, $table) {
    if (!tableExists($conn, $table)) {
        return 0;
    }

    $tableSafe = str_replace("`", "``", $table);
    $query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tableSafe`");

    if (!$query) {
        return 0;
    }

    $data = mysqli_fetch_assoc($query);
    return (int)($data['total'] ?? 0);
}

function pickValue($row, $keys, $default = '-') {
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return $row[$key];
        }
    }

    return $default;
}

function formatJam($jam) {
    if (!$jam || $jam == '-') {
        return '-';
    }

    return substr($jam, 0, 5);
}



$namaAdmin = $_SESSION['nama'];
$emailAdmin = $_SESSION['email'] ?? '-';
$inisialAdmin = strtoupper(substr($namaAdmin, 0, 1));



$totalMahasiswa = countTable($conn, 'mahasiswa');
$totalDosen     = countTable($conn, 'dosen');
$totalMatkul    = countTable($conn, 'mata_kuliah');
$totalJadwal    = countTable($conn, 'jadwal_kuliah');
$totalRuangan   = countTable($conn, 'ruangan');



$hariIni = namaHariIndonesia(date('l'));
$judulJadwal = "Jadwal Hari Ini - " . $hariIni;
$jadwalHariIni = [];

if (
    tableExists($conn, 'jadwal_kuliah') &&
    columnExists($conn, 'jadwal_kuliah', 'hari') &&
    columnExists($conn, 'jadwal_kuliah', 'waktu_mulai')
) {
    $hariSafe = mysqli_real_escape_string($conn, $hariIni);

    $queryJadwal = mysqli_query($conn, "
        SELECT *
        FROM jadwal_kuliah
        WHERE hari = '$hariSafe'
        ORDER BY waktu_mulai ASC
        LIMIT 6
    ");

    if ($queryJadwal && mysqli_num_rows($queryJadwal) > 0) {
        while ($row = mysqli_fetch_assoc($queryJadwal)) {
            $jadwalHariIni[] = $row;
        }
    }
}



if (empty($jadwalHariIni) && tableExists($conn, 'jadwal_kuliah')) {
    $judulJadwal = "Jadwal Terbaru";

    $orderBy = "ORDER BY 1 DESC";

    if (
        columnExists($conn, 'jadwal_kuliah', 'hari') &&
        columnExists($conn, 'jadwal_kuliah', 'waktu_mulai')
    ) {
        $orderBy = "ORDER BY hari ASC, waktu_mulai ASC";
    }

    $queryJadwal = mysqli_query($conn, "
        SELECT *
        FROM jadwal_kuliah
        $orderBy
        LIMIT 6
    ");

    if ($queryJadwal) {
        while ($row = mysqli_fetch_assoc($queryJadwal)) {
            $jadwalHariIni[] = $row;
        }
    }
}

$totalKonflik = 0;

$queryKonflik = mysqli_query($conn, "
    SELECT COUNT(*) AS total
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
");

if ($queryKonflik) {
    $dataKonflik = mysqli_fetch_assoc($queryKonflik);
    $totalKonflik = $dataKonflik['total'];
}



$mahasiswaTerbaru = [];

if (tableExists($conn, 'mahasiswa')) {
    $orderMahasiswa = columnExists($conn, 'mahasiswa', 'nim') ? "ORDER BY nim DESC" : "ORDER BY 1 DESC";

    $queryMahasiswa = mysqli_query($conn, "
        SELECT *
        FROM mahasiswa
        $orderMahasiswa
        LIMIT 5
    ");

    if ($queryMahasiswa) {
        while ($row = mysqli_fetch_assoc($queryMahasiswa)) {
            $mahasiswaTerbaru[] = $row;
        }
    }
}



$hariKalender = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu'
];

$jadwalKalender = [];

foreach ($hariKalender as $hari) {
    $jadwalKalender[$hari] = [];
}

if (
    tableExists($conn, 'jadwal_kuliah') &&
    tableExists($conn, 'mata_kuliah') &&
    tableExists($conn, 'dosen') &&
    tableExists($conn, 'ruangan')
) {
    $queryKalender = mysqli_query($conn, "
        SELECT 
            jk.id_jadwal,
            jk.hari,
            jk.waktu_mulai,
            jk.waktu_selesai,
            jk.gedung,

            mk.nama_matkul,
            d.nama_dosen,
            r.nama_ruang

        FROM jadwal_kuliah jk

        LEFT JOIN mata_kuliah mk
        ON jk.id_matkul = mk.id_matkul

        LEFT JOIN dosen d
        ON jk.id_dosen = d.id_dosen

        LEFT JOIN ruangan r
        ON jk.id_ruang = r.id_ruang

        ORDER BY 
        FIELD(jk.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
        jk.waktu_mulai ASC
    ");

    if ($queryKalender) {
        while ($row = mysqli_fetch_assoc($queryKalender)) {
            $hari = $row['hari'];

            if (isset($jadwalKalender[$hari])) {
                $jadwalKalender[$hari][] = $row;
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

<title>Dashboard Admin - Sistem Jadwal Kuliah</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

:root{
    --primary:#2563eb;
    --primary-dark:#1e40af;
    --primary-light:#dbeafe;
    --secondary:#38bdf8;
    --sidebar:#102a63;
    --sidebar-light:#1d4ed8;
    --white:#ffffff;
    --bg:#eef5ff;
    --text:#0f172a;
    --muted:#64748b;
    --border:#dbeafe;
    --danger:#ef4444;
    --danger-bg:#fee2e2;
    --success:#22c55e;
    --success-bg:#dcfce7;
    --warning:#f59e0b;
    --warning-bg:#fef3c7;
    --shadow:0 14px 35px rgba(37,99,235,0.12);
}

body{
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
}

.app{
    display:flex;
    min-height:100vh;
}


.sidebar{
    width:290px;
    min-height:100vh;
    background:linear-gradient(180deg, #0f2a5f, #1d4ed8);
    color:white;
    padding:28px 22px;
    position:fixed;
    top:0;
    left:0;
    bottom:0;
    overflow-y:auto;
    box-shadow:8px 0 30px rgba(15,42,95,0.18);
}

.brand{
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:35px;
}

.brand-logo{
    width:62px;
    height:62px;
    border-radius:18px;
    background:rgba(255,255,255,0.18);
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    flex-shrink:0;
}

.brand-logo img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.brand-logo i{
    font-size:27px;
}

.brand-text h2{
    font-size:22px;
    line-height:1.18;
    font-weight:800;
}

.brand-text p{
    margin-top:5px;
    font-size:13px;
    opacity:0.85;
}

.menu-label{
    font-size:12px;
    font-weight:700;
    letter-spacing:1px;
    text-transform:uppercase;
    opacity:0.7;
    margin-bottom:14px;
}

.menu{
    list-style:none;
}

.menu li{
    margin-bottom:9px;
}

.menu a{
    color:white;
    text-decoration:none;
    padding:14px 16px;
    border-radius:16px;
    display:flex;
    align-items:center;
    gap:14px;
    font-weight:500;
    transition:0.25s;
}

.menu a i{
    width:22px;
    text-align:center;
    font-size:17px;
}

.menu a:hover,
.menu a.active{
    background:rgba(255,255,255,0.18);
    transform:translateX(5px);
}

.sidebar-footer{
    margin-top:35px;
    padding-top:18px;
    border-top:1px solid rgba(255,255,255,0.18);
}



.main{
    margin-left:290px;
    width:calc(100% - 290px);
    padding:30px;
}



.topbar{
    background:white;
    border-radius:26px;
    padding:22px 28px;
    box-shadow:var(--shadow);
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:26px;
}

.page-title h1{
    font-size:30px;
    font-weight:800;
    color:var(--text);
}

.page-title p{
    color:var(--muted);
    margin-top:6px;
    font-size:14px;
}

.top-actions{
    display:flex;
    align-items:center;
    gap:18px;
}

.search-box{
    width:280px;
    background:#f8fbff;
    border:1px solid var(--border);
    border-radius:16px;
    padding:12px 15px;
    display:flex;
    align-items:center;
    gap:10px;
}

.search-box input{
    border:none;
    outline:none;
    background:transparent;
    width:100%;
    color:var(--text);
}

.search-box i{
    color:var(--muted);
}

.profile{
    display:flex;
    align-items:center;
    gap:13px;
}

.avatar{
    width:56px;
    height:56px;
    border-radius:50%;
    background:linear-gradient(135deg, var(--primary), var(--secondary));
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    font-weight:800;
}

.profile-info h3{
    font-size:17px;
    line-height:1.2;
}

.profile-info span{
    color:var(--muted);
    font-size:13px;
}



.hero{
    position:relative;
    overflow:hidden;
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    border-radius:30px;
    padding:38px 42px;
    box-shadow:var(--shadow);
    margin-bottom:26px;
}

.hero::before{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    border-radius:50%;
    background:rgba(255,255,255,0.13);
    right:-70px;
    top:-75px;
}

.hero::after{
    content:"";
    position:absolute;
    width:160px;
    height:160px;
    border-radius:50%;
    background:rgba(255,255,255,0.10);
    right:150px;
    bottom:-85px;
}

.hero-content{
    position:relative;
    z-index:1;
}

.hero h2{
    font-size:34px;
    margin-bottom:10px;
    font-weight:800;
}

.hero p{
    max-width:760px;
    line-height:1.7;
    opacity:0.95;
}


.stats{
    display:grid;
    grid-template-columns:repeat(6, 1fr);
    gap:18px;
    margin-bottom:26px;
}

.stat-card{
    background:white;
    border-radius:24px;
    padding:22px;
    box-shadow:var(--shadow);
    border:1px solid var(--border);
    transition:0.25s;
}

.stat-card:hover{
    transform:translateY(-5px);
}

.stat-icon{
    width:58px;
    height:58px;
    border-radius:18px;
    background:var(--primary-light);
    color:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
    margin-bottom:16px;
}

.stat-number{
    font-size:28px;
    font-weight:800;
    color:var(--text);
}

.stat-label{
    margin-top:4px;
    font-size:14px;
    color:var(--muted);
}


.content-grid{
    display:grid;
    grid-template-columns:1fr;
    gap:24px;
    margin-bottom:26px;
}

.card{
    background:white;
    border-radius:26px;
    padding:26px;
    box-shadow:var(--shadow);
    border:1px solid var(--border);
}

.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.card-header h3{
    font-size:20px;
    font-weight:800;
}

.card-header a{
    color:var(--primary);
    text-decoration:none;
    font-weight:600;
    font-size:14px;
}



.quick-grid{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:16px;
}

.quick-card{
    text-decoration:none;
    color:var(--text);
    background:#f8fbff;
    border:1px solid var(--border);
    border-radius:22px;
    padding:20px;
    transition:0.25s;
}

.quick-card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 25px rgba(37,99,235,0.12);
    background:white;
}

.quick-icon{
    width:56px;
    height:56px;
    border-radius:18px;
    background:var(--primary-light);
    color:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    margin-bottom:15px;
}

.quick-card h4{
    font-size:17px;
    margin-bottom:7px;
}

.quick-card p{
    color:var(--muted);
    font-size:13px;
    line-height:1.6;
}



.schedule-list{
    display:flex;
    flex-direction:column;
    gap:13px;
}

.schedule-item{
    background:#f8fbff;
    border:1px solid var(--border);
    border-radius:20px;
    padding:16px;
    display:flex;
    justify-content:space-between;
    gap:15px;
}

.schedule-time{
    min-width:105px;
    font-weight:800;
    color:var(--primary);
}

.schedule-detail h4{
    font-size:15px;
    margin-bottom:5px;
}

.schedule-detail p{
    color:var(--muted);
    font-size:13px;
    line-height:1.6;
}

.empty-state{
    background:#f8fbff;
    border:1px dashed #bfdbfe;
    color:var(--muted);
    border-radius:20px;
    padding:28px;
    text-align:center;
}

.empty-state i{
    display:block;
    font-size:34px;
    color:var(--primary);
    margin-bottom:10px;
}



.table-card{
    background:white;
    border-radius:26px;
    padding:26px;
    box-shadow:var(--shadow);
    border:1px solid var(--border);
    margin-bottom:26px;
}

.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#eff6ff;
    color:#1e3a8a;
    text-align:left;
    padding:16px;
    font-size:14px;
    white-space:nowrap;
}

td{
    padding:16px;
    border-bottom:1px solid #e5efff;
    color:#334155;
    font-size:14px;
    white-space:nowrap;
}

tr:hover td{
    background:#f8fbff;
}

.badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:7px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.badge-success{
    background:var(--success-bg);
    color:#166534;
}

.badge-danger{
    background:var(--danger-bg);
    color:#991b1b;
}

.badge-warning{
    background:var(--warning-bg);
    color:#92400e;
}

.badge-blue{
    background:var(--primary-light);
    color:var(--primary-dark);
}




.kalender-card{
    min-width:0;
}

.kalender-card .card-header h3{
    display:flex;
    align-items:center;
    gap:10px;
}

.kalender-wrapper{
    display:grid;
    grid-template-columns:repeat(7, minmax(180px, 1fr));
    gap:14px;
    overflow-x:auto;
    padding-bottom:10px;
}

.kalender-day{
    min-width:180px;
    background:#f8fbff;
    border:1px solid var(--border);
    border-radius:20px;
    overflow:hidden;
}

.kalender-day-header{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    padding:14px;
    font-weight:800;
    text-align:center;
}

.kalender-day-body{
    padding:14px;
    min-height:230px;
}

.jadwal-mini{
    background:white;
    border:1px solid var(--border);
    border-radius:16px;
    padding:13px;
    margin-bottom:12px;
    box-shadow:0 6px 18px rgba(37,99,235,0.08);
}

.jadwal-time{
    color:var(--primary);
    font-weight:800;
    font-size:14px;
    margin-bottom:8px;
}

.jadwal-title{
    color:#071633;
    font-weight:800;
    font-size:14px;
    margin-bottom:8px;
    line-height:1.4;
}

.jadwal-info{
    color:var(--muted);
    font-size:12px;
    margin-bottom:5px;
    line-height:1.5;
}

.jadwal-info i{
    color:var(--primary);
    margin-right:5px;
}

.jadwal-empty{
    background:white;
    color:#94a3b8;
    border:1px dashed #bfdbfe;
    border-radius:14px;
    padding:18px;
    text-align:center;
    font-size:13px;
}

@media(max-width:1400px){
    .kalender-wrapper{
        grid-template-columns:repeat(4, minmax(180px, 1fr));
    }
}

@media(max-width:760px){
    .kalender-wrapper{
        grid-template-columns:1fr;
    }

    .kalender-day{
        min-width:100%;
    }
}



@media(max-width:1400px){
    .stats{
        grid-template-columns:repeat(3, 1fr);
    }
}

@media(max-width:1050px){
    .sidebar{
        position:relative;
        width:100%;
        min-height:auto;
    }

    .main{
        margin-left:0;
        width:100%;
    }

    .app{
        flex-direction:column;
    }

    .stats{
        grid-template-columns:repeat(2, 1fr);
    }

    .content-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:760px){
    .main{
        padding:18px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
    }

    .top-actions{
        width:100%;
        flex-direction:column;
        align-items:flex-start;
    }

    .search-box{
        width:100%;
    }

    .stats{
        grid-template-columns:1fr;
    }

    .quick-grid{
        grid-template-columns:1fr;
    }

    .hero{
        padding:28px 24px;
    }

    .hero h2{
        font-size:27px;
    }
}
</style>
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-logo">
                <?php if (file_exists("logo.png")) { ?>
                    <img src="logo.png" alt="Logo">
                <?php } else { ?>
                    <i class="fa-solid fa-building-columns"></i>
                <?php } ?>
            </div>

            <div class="brand-text">
                <h2>Sistem<br>Jadwal Kuliah</h2>
                
            </div>
        </div>

        <div class="menu-label">Menu Utama</div>

        <ul class="menu">
            <li>
                <a href="dashboard-admin.php" class="active">
                    <i class="fa-solid fa-house"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="mahasiswa.php">
                    <i class="fa-solid fa-user-graduate"></i>
                    Mahasiswa
                </a>
            </li>

            <li>
                <a href="matakuliah.php">
                    <i class="fa-solid fa-book"></i>
                    Mata Kuliah
                </a>
            </li>

            <li>
                <a href="jadwal.php">
                    <i class="fa-solid fa-calendar-days"></i>
                    Jadwal Kuliah
                </a>
            </li>

            <li>
                <a href="ruangan.php">
                    <i class="fa-solid fa-building"></i>
                    Ruangan
                </a>
            </li>


            <li>
                <a href="notifikasi-reminder.php">
                    <i class="fa-solid fa-bell"></i>
                    Reminder
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <ul class="menu">
                <li>
                    <a href="logout-admin.php">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- TOPBAR -->
        <section class="topbar">

            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Selamat datang di pusat pengelolaan sistem jadwal kuliah.</p>
            </div>

            <div class="top-actions">

                <div class="profile">
                    <div class="avatar">
                        <?php echo e($inisialAdmin); ?>
                    </div>

                    <div class="profile-info">
                        <h3><?php echo e($namaAdmin); ?></h3>
                        <span><?php echo e($roleLogin); ?></span>
                    </div>
                </div>

            </div>

        </section>

        <!-- HERO -->
        <section class="hero">
            <div class="hero-content">
                <h2>Halo, <?php echo e($namaAdmin); ?> 👋</h2>
                <p>
                    Kelola data mahasiswa, mata kuliah, ruangan, jadwal kuliah,
        
                </p>
            </div>
        </section>

        <!-- STATISTICS -->
        <section class="stats">

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div class="stat-number"><?php echo $totalMahasiswa; ?></div>
                <div class="stat-label">Mahasiswa</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div class="stat-number"><?php echo $totalDosen; ?></div>
                <div class="stat-label">Dosen</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div class="stat-number"><?php echo $totalMatkul; ?></div>
                <div class="stat-label">Mata Kuliah</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo $totalJadwal; ?></div>
                <div class="stat-label">Jadwal Kuliah</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div class="stat-number"><?php echo $totalRuangan; ?></div>
                <div class="stat-label">Ruangan</div>
            </div>

      
        </section>

        <!-- CONTENT GRID -->
        <section class="content-grid">

            <!-- KALENDER JADWAL KULIAH -->
            <div class="card kalender-card">
                <div class="card-header">
                    <h3>
                        <i class="fa-solid fa-calendar-week"></i>
                        Kalender Jadwal Kuliah
                    </h3>

                    <a href="jadwal.php">
                        Lihat Semua
                    </a>
                </div>

                <div class="kalender-wrapper">

                    <?php foreach ($hariKalender as $hari) { ?>

                        <div class="kalender-day">

                            <div class="kalender-day-header">
                                <?php echo e($hari); ?>
                            </div>

                            <div class="kalender-day-body">

                                <?php if (!empty($jadwalKalender[$hari])) { ?>

                                    <?php foreach ($jadwalKalender[$hari] as $j) { ?>

                                        <div class="jadwal-mini">

                                            <div class="jadwal-time">
                                                <?php echo e(formatJam($j['waktu_mulai'])); ?>
                                                -
                                                <?php echo e(formatJam($j['waktu_selesai'])); ?>
                                            </div>

                                            <div class="jadwal-title">
                                                <?php echo e($j['nama_matkul'] ?: 'Mata Kuliah'); ?>
                                            </div>

                                            <div class="jadwal-info">
                                                <i class="fa-solid fa-user"></i>
                                                <?php echo e($j['nama_dosen'] ?: 'Dosen'); ?>
                                            </div>

                                            <div class="jadwal-info">
                                                <i class="fa-solid fa-door-open"></i>
                                                <?php echo e($j['nama_ruang'] ?: '-'); ?>
                                                |
                                                <?php echo e($j['gedung'] ?: '-'); ?>
                                            </div>

                                        </div>

                                    <?php } ?>

                                <?php } else { ?>

                                    <div class="jadwal-empty">
                                        Tidak ada jadwal
                                    </div>

                                <?php } ?>

                            </div>

                        </div>

                    <?php } ?>

                </div>
            </div>



        </section>


        <!-- MAHASISWA TERBARU -->
        <section class="table-card">
            <div class="card-header">
                <h3>Data Mahasiswa Terbaru</h3>
                <a href="mahasiswa.php">Kelola Mahasiswa</a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($mahasiswaTerbaru)) { ?>

                            <?php $no = 1; ?>
                            <?php foreach ($mahasiswaTerbaru as $mhs) { ?>

                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo e(pickValue($mhs, ['nim'], '-')); ?></td>
                                    <td><?php echo e(pickValue($mhs, ['nama'], '-')); ?></td>
                                    <td><?php echo e(pickValue($mhs, ['email'], '-')); ?></td>
                                    <td>
                                        <span class="badge badge-blue">
                                            <i class="fa-solid fa-user-check"></i>
                                            Aktif
                                        </span>
                                    </td>
                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="5">
                                    <span class="badge badge-warning">
                                        <i class="fa-solid fa-circle-info"></i>
                                        Belum ada data mahasiswa
                                    </span>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

</div>

</body>
</html>
