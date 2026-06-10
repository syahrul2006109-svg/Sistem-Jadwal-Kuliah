<?php
session_start();
include "connect.php";

$hari_array = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu'
];

$hariIni = $hari_array[date('l')];

/* Total Jadwal */
$qTotalJadwal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jadwal_kuliah");
$dTotalJadwal = mysqli_fetch_assoc($qTotalJadwal);
$totalJadwal = $dTotalJadwal['total'];

/* Jadwal Hari Ini */
$qJadwalHariIni = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM jadwal_kuliah 
    WHERE hari = '$hariIni'
");
$dJadwalHariIni = mysqli_fetch_assoc($qJadwalHariIni);
$totalJadwalHariIni = $dJadwalHariIni['total'];

/* Total Mata Kuliah */
$qTotalMatkul = mysqli_query($conn, "SELECT COUNT(*) AS total FROM mata_kuliah");
$dTotalMatkul = mysqli_fetch_assoc($qTotalMatkul);
$totalMatkul = $dTotalMatkul['total'];

/* Total Konflik Jadwal */
$qKonflik = mysqli_query($conn, "
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

$dKonflik = mysqli_fetch_assoc($qKonflik);
$totalKonflik = $dKonflik['total'];

date_default_timezone_set("Asia/Makassar");

/* =========================
   CEK LOGIN MAHASISWA
========================= */
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

/* Jangan terlalu ketat role supaya tidak redirect loop */
$namaUser  = $_SESSION['nama'] ?? 'Mahasiswa';
$nimUser   = $_SESSION['nim'] ?? '-';
$emailUser = $_SESSION['email'] ?? '-';
$roleUser  = $_SESSION['role'] ?? 'user';
$inisial   = strtoupper(substr($namaUser, 0, 1));

function e($text) {
    return htmlspecialchars((string)($text ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatJam($jam) {
    if (!$jam) return '-';
    return substr($jam, 0, 5);
}

function hariIndonesia($englishDay) {
    $hari = [
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
        'Sunday'    => 'Minggu'
    ];

    return $hari[$englishDay] ?? $englishDay;
}

function statusJadwal($hari, $mulai, $selesai) {
    $hariIni = hariIndonesia(date('l'));
    $jamSekarang = date('H:i:s');

    if ($hari != $hariIni) {
        return [
            'text' => 'Terjadwal',
            'class' => 'blue'
        ];
    }

    if ($jamSekarang < $mulai) {
        $selisihMenit = floor((strtotime($mulai) - strtotime($jamSekarang)) / 60);

        if ($selisihMenit >= 60) {
            $jam = floor($selisihMenit / 60);
            $menit = $selisihMenit % 60;
            $text = $menit > 0 ? "$jam jam $menit menit lagi" : "$jam jam lagi";
        } else {
            $text = "$selisihMenit menit lagi";
        }

        return [
            'text' => $text,
            'class' => 'green'
        ];
    }

    if ($jamSekarang >= $mulai && $jamSekarang <= $selesai) {
        return [
            'text' => 'Sedang berlangsung',
            'class' => 'orange'
        ];
    }

    return [
        'text' => 'Selesai',
        'class' => 'gray'
    ];
}

function countTable($conn, $table) {
    $safe = str_replace('`', '``', $table);
    $q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$safe`");
    if (!$q) return 0;
    $d = mysqli_fetch_assoc($q);
    return (int)($d['total'] ?? 0);
}

/* =========================
   DATA STATISTIK
========================= */
$totalJadwal  = countTable($conn, 'jadwal_kuliah');
$totalMatkul  = countTable($conn, 'mata_kuliah');
$totalDosen   = countTable($conn, 'dosen');
$totalRuangan = countTable($conn, 'ruangan');

$hariIni = hariIndonesia(date('l'));
$jamSekarang = date('H:i:s');

/* Jadwal hari ini */
$queryHariIni = mysqli_query($conn, "
    SELECT 
        jk.*,
        mk.nama_matkul,
        d.nama_dosen,
        r.nama_ruang
    FROM jadwal_kuliah jk
    LEFT JOIN mata_kuliah mk ON jk.id_matkul = mk.id_matkul
    LEFT JOIN dosen d ON jk.id_dosen = d.id_dosen
    LEFT JOIN ruangan r ON jk.id_ruang = r.id_ruang
    WHERE jk.hari = '$hariIni'
    ORDER BY jk.waktu_mulai ASC
");

$jadwalHariIni = [];
if ($queryHariIni) {
    while ($row = mysqli_fetch_assoc($queryHariIni)) {
        $jadwalHariIni[] = $row;
    }
}

$totalHariIni = count($jadwalHariIni);

/* Jadwal berikutnya hari ini */
$queryBerikutnya = mysqli_query($conn, "
    SELECT 
        jk.*,
        mk.nama_matkul,
        d.nama_dosen,
        r.nama_ruang
    FROM jadwal_kuliah jk
    LEFT JOIN mata_kuliah mk ON jk.id_matkul = mk.id_matkul
    LEFT JOIN dosen d ON jk.id_dosen = d.id_dosen
    LEFT JOIN ruangan r ON jk.id_ruang = r.id_ruang
    WHERE jk.hari = '$hariIni'
    AND jk.waktu_selesai >= '$jamSekarang'
    ORDER BY jk.waktu_mulai ASC
    LIMIT 1
");

$jadwalBerikutnya = null;
if ($queryBerikutnya && mysqli_num_rows($queryBerikutnya) > 0) {
    $jadwalBerikutnya = mysqli_fetch_assoc($queryBerikutnya);
}

/* Semua jadwal untuk kalender mingguan */
$hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
$kalender = [];
foreach ($hariList as $h) {
    $kalender[$h] = [];
}

$queryKalender = mysqli_query($conn, "
    SELECT 
        jk.*,
        mk.nama_matkul,
        d.nama_dosen,
        r.nama_ruang
    FROM jadwal_kuliah jk
    LEFT JOIN mata_kuliah mk ON jk.id_matkul = mk.id_matkul
    LEFT JOIN dosen d ON jk.id_dosen = d.id_dosen
    LEFT JOIN ruangan r ON jk.id_ruang = r.id_ruang
    ORDER BY 
    FIELD(jk.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
    jk.waktu_mulai ASC
");

if ($queryKalender) {
    while ($row = mysqli_fetch_assoc($queryKalender)) {
        if (isset($kalender[$row['hari']])) {
            $kalender[$row['hari']][] = $row;
        }
    }
}

/* Hitung konflik dengan logika yang sama: hari sama, waktu tabrakan, ruangan sama ATAU dosen sama */
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
    $dKonflik = mysqli_fetch_assoc($queryKonflik);
    $totalKonflik = (int)($dKonflik['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Mahasiswa - Sistem Jadwal Kuliah</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .stats{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:24px;
    margin-bottom:28px;
}

.stat-card{
    background:white;
    border-radius:26px;
    padding:28px;
    box-shadow:0 14px 35px rgba(37,99,235,0.12);
    border:1px solid #dbeafe;
    transition:0.25s;
}

.stat-link{
    text-decoration:none;
    color:inherit;
    display:block;
}

.stat-card:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 40px rgba(37,99,235,0.18);
}

.stat-icon{
    width:68px;
    height:68px;
    border-radius:20px;
    background:#dbeafe;
    color:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    margin-bottom:28px;
}

.stat-number{
    font-size:34px;
    font-weight:800;
    color:#071633;
    margin-bottom:8px;
}

.stat-label{
    font-size:16px;
    color:#64748b;
    font-weight:500;
}

@media(max-width:1100px){
    .stats{
        grid-template-columns:repeat(2, 1fr);
    }
}

@media(max-width:650px){
    .stats{
        grid-template-columns:1fr;
    }
}

.stat-link:hover{
    transform:translateY(-5px);
    box-shadow:0 15px 35px rgba(37,99,235,0.18);
}
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

:root{
    --primary:#2563eb;
    --secondary:#38bdf8;
    --sidebar:#102a63;
    --bg:#eef5ff;
    --white:#ffffff;
    --text:#071633;
    --muted:#64748b;
    --border:#dbeafe;
    --light:#f8fbff;
    --blue-light:#dbeafe;
    --green:#166534;
    --green-bg:#dcfce7;
    --orange:#9a3412;
    --orange-bg:#ffedd5;
    --gray:#64748b;
    --gray-bg:#f1f5f9;
    --red:#991b1b;
    --red-bg:#fee2e2;
    --shadow:0 14px 35px rgba(37,99,235,0.12);
}

body{
    min-height:100vh;
    background:var(--bg);
    color:var(--text);
}

.app{
    min-height:100vh;
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:285px;
    min-height:100vh;
    position:fixed;
    top:0;
    left:0;
    bottom:0;
    background:linear-gradient(180deg, #0f2a5f, #2563eb);
    color:white;
    padding:28px 22px;
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
    align-items:center;
    justify-content:center;
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

.menu-label{
    font-size:12px;
    font-weight:700;
    letter-spacing:1px;
    text-transform:uppercase;
    opacity:.75;
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
    font-weight:600;
    transition:.25s;
}

.menu a i{
    width:22px;
    text-align:center;
}

.menu a:hover,
.menu a.active{
    background:rgba(255,255,255,.18);
    transform:translateX(5px);
}

.sidebar-footer{
    margin-top:35px;
    padding-top:18px;
    border-top:1px solid rgba(255,255,255,.18);
}

/* MAIN */
.main{
    margin-left:285px;
    width:calc(100% - 285px);
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
}

.page-title p{
    color:var(--muted);
    margin-top:6px;
    font-size:14px;
}

.profile{
    display:flex;
    align-items:center;
    gap:13px;
}

.avatar{
    width:58px;
    height:58px;
    border-radius:50%;
    background:linear-gradient(135deg, var(--primary), var(--secondary));
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
    font-weight:800;
}

.profile h3{
    font-size:17px;
}

.profile span{
    color:var(--muted);
    font-size:13px;
}

.hero{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    border-radius:30px;
    padding:36px 40px;
    box-shadow:var(--shadow);
    margin-bottom:26px;
    position:relative;
    overflow:hidden;
}

.hero::before{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    border-radius:50%;
    background:rgba(255,255,255,.13);
    right:-70px;
    top:-80px;
}

.hero::after{
    content:"";
    position:absolute;
    width:160px;
    height:160px;
    border-radius:50%;
    background:rgba(255,255,255,.10);
    right:150px;
    bottom:-80px;
}

.hero-content{
    position:relative;
    z-index:1;
}

.hero h2{
    font-size:34px;
    font-weight:800;
    margin-bottom:10px;
}

.hero p{
    max-width:760px;
    line-height:1.7;
    opacity:.95;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:18px;
    margin-bottom:26px;
}

.stat-card{
    background:white;
    border:1px solid var(--border);
    border-radius:24px;
    padding:22px;
    box-shadow:var(--shadow);
    transition:.25s;
}

.stat-card:hover{
    transform:translateY(-5px);
}

.stat-icon{
    width:58px;
    height:58px;
    border-radius:18px;
    background:var(--blue-light);
    color:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
    margin-bottom:15px;
}

.stat-number{
    font-size:30px;
    font-weight:800;
}

.stat-label{
    color:var(--muted);
    font-size:14px;
    margin-top:4px;
}

.grid{
    display:grid;
    grid-template-columns:1.3fr .8fr;
    gap:24px;
    margin-bottom:26px;
}

.card{
    background:white;
    border:1px solid var(--border);
    border-radius:26px;
    padding:26px;
    box-shadow:var(--shadow);
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
    text-decoration:none;
    color:var(--primary);
    font-size:14px;
    font-weight:700;
}

.next-card{
    background:linear-gradient(135deg, #eff6ff, #ffffff);
    border:1px solid var(--border);
    border-radius:22px;
    padding:22px;
}

.next-top{
    display:flex;
    gap:16px;
    align-items:center;
    margin-bottom:18px;
}

.next-icon{
    width:60px;
    height:60px;
    border-radius:18px;
    background:var(--blue-light);
    color:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:25px;
    flex-shrink:0;
}

.next-title h4{
    font-size:20px;
    font-weight:800;
}

.next-title p{
    color:var(--muted);
    font-size:14px;
    margin-top:4px;
}

.info-row{
    display:grid;
    grid-template-columns:130px 1fr;
    gap:10px;
    margin-bottom:10px;
    font-size:14px;
    color:#334155;
}

.info-row strong{
    color:var(--text);
}

.status{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    margin-top:8px;
}

.status.green{background:var(--green-bg); color:var(--green);}
.status.blue{background:var(--blue-light); color:var(--primary);}
.status.orange{background:var(--orange-bg); color:var(--orange);}
.status.gray{background:var(--gray-bg); color:var(--gray);}
.status.red{background:var(--red-bg); color:var(--red);}

.quick-grid{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:16px;
}

.quick-card{
    text-decoration:none;
    color:var(--text);
    background:var(--light);
    border:1px solid var(--border);
    border-radius:20px;
    padding:18px;
    transition:.25s;
}

.quick-card:hover{
    background:white;
    transform:translateY(-4px);
    box-shadow:0 12px 25px rgba(37,99,235,.12);
}

.quick-icon{
    width:52px;
    height:52px;
    border-radius:16px;
    background:var(--blue-light);
    color:var(--primary);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
    margin-bottom:13px;
}

.quick-card h4{
    font-size:16px;
    font-weight:800;
    margin-bottom:6px;
}

.quick-card p{
    color:var(--muted);
    font-size:13px;
    line-height:1.6;
}

.today-list{
    display:flex;
    flex-direction:column;
    gap:13px;
}

.today-item{
    background:var(--light);
    border:1px solid var(--border);
    border-radius:20px;
    padding:16px;
    display:flex;
    gap:15px;
}

.today-time{
    min-width:92px;
    color:var(--primary);
    font-weight:800;
    line-height:1.6;
}

.today-detail h4{
    font-size:15px;
    margin-bottom:6px;
}

.today-detail p{
    color:var(--muted);
    font-size:13px;
    line-height:1.6;
}

.empty{
    border:1px dashed #bfdbfe;
    background:var(--light);
    border-radius:20px;
    padding:26px;
    text-align:center;
    color:var(--muted);
}

.empty i{
    display:block;
    color:var(--primary);
    font-size:34px;
    margin-bottom:10px;
}

.calendar-card{
    margin-bottom:26px;
}

.calendar-wrapper{
    display:grid;
    grid-template-columns:repeat(7, minmax(170px, 1fr));
    gap:14px;
    overflow-x:auto;
    padding-bottom:8px;
}

.day{
    min-width:170px;
    background:var(--light);
    border:1px solid var(--border);
    border-radius:20px;
    overflow:hidden;
}

.day-head{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    padding:13px;
    text-align:center;
    font-weight:800;
}

.day-head.today{
    background:linear-gradient(135deg, #16a34a, #22c55e);
}

.day-body{
    padding:13px;
    min-height:200px;
}

.mini-jadwal{
    background:white;
    border:1px solid var(--border);
    border-radius:15px;
    padding:12px;
    margin-bottom:11px;
    box-shadow:0 6px 18px rgba(37,99,235,.08);
}

.mini-time{
    color:var(--primary);
    font-weight:800;
    font-size:13px;
    margin-bottom:7px;
}

.mini-title{
    font-size:13px;
    font-weight:800;
    line-height:1.4;
    margin-bottom:7px;
}

.mini-info{
    color:var(--muted);
    font-size:12px;
    line-height:1.5;
}

.mini-empty{
    background:white;
    border:1px dashed #bfdbfe;
    color:#94a3b8;
    border-radius:14px;
    padding:16px;
    text-align:center;
    font-size:13px;
}

@media(max-width:1400px){
    .stats{grid-template-columns:repeat(2, 1fr);}
    .calendar-wrapper{grid-template-columns:repeat(4, minmax(170px, 1fr));}
}

@media(max-width:1050px){
    .sidebar{position:relative; width:100%; min-height:auto;}
    .main{margin-left:0; width:100%;}
    .app{flex-direction:column;}
    .grid{grid-template-columns:1fr;}
}

@media(max-width:760px){
    .main{padding:18px;}
    .topbar{flex-direction:column; align-items:flex-start;}
    .stats{grid-template-columns:1fr;}
    .quick-grid{grid-template-columns:1fr;}
    .calendar-wrapper{grid-template-columns:1fr;}
    .day{min-width:100%;}
    .hero{padding:28px 24px;}
    .hero h2{font-size:27px;}
    .info-row{grid-template-columns:1fr; gap:3px;}
}
</style>
</head>
<body>

<div class="app">

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

        <div class="menu-label">Menu Mahasiswa</div>

        <ul class="menu">
            <li><a href="dashboard-user.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a></li>
            <li><a href="jadwal-matakuliah.php"><i class="fa-solid fa-calendar-days"></i> Jadwal Kuliah</a></li>
            <li><a href="matakuliah-mahasiswa.php"> <i class="fa-solid fa-book"></i>Mata Kuliah </a></li>
            <li><a href="cek-bentrok.php"><i class="fa-solid fa-triangle-exclamation"></i> Konflik Jadwal</a></li>
            <li><a href="notifikasi-reminder.php"><i class="fa-solid fa-bell"></i> Reminder</a></li>
        </ul>

        <div class="sidebar-footer">
            <ul class="menu">
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </aside>

    <main class="main">

        <section class="topbar">
            <div class="page-title">
                <h1>Dashboard Mahasiswa</h1>
            </div>

            <div class="profile">
                <div class="avatar"><?php echo e($inisial); ?></div>
                <div>
                    <h3><?php echo e($namaUser); ?></h3>
                    <span>NIM: <?php echo e($nimUser); ?></span>
                </div>
            </div>
        </section>

        <section class="hero">
            <div class="hero-content">
                <h2>Halo, <?php echo e($namaUser); ?> 👋</h2>
               
            </div>
        </section>

        <section class="stats">

<a href="jadwal-matakuliah.php" class="stat-card stat-link">
    <div class="stat-icon">
        <i class="fa-solid fa-calendar-days"></i>
    </div>

    <div class="stat-number">
        <?php echo $totalJadwal; ?>
    </div>

    <div class="stat-label">
        Total Jadwal
    </div>
</a>

<a href="jadwal-matakuliah.php" class="stat-card stat-link">
    <div class="stat-icon">
        <i class="fa-solid fa-calendar-check"></i>
    </div>

    <div class="stat-number">
        <?php echo $totalJadwalHariIni; ?>
    </div>

    <div class="stat-label">
        Jadwal Hari Ini
    </div>
</a>

<a href="matakuliah-mahasiswa.php" class="stat-card stat-link">
    <div class="stat-icon">
        <i class="fa-solid fa-book-open"></i>
    </div>

    <div class="stat-number">
        <?php echo $totalMatkul; ?>
    </div>

    <div class="stat-label">
        Mata Kuliah
    </div>
</a>

<a href="cek-bentrok.php" class="stat-card stat-link">
    <div class="stat-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>

    <div class="stat-number">
        <?php echo $totalKonflik; ?>
    </div>

    <div class="stat-label">
        Konflik Jadwal
    </div>
</a>

</section>
        <section class="grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-clock"></i> Kelas Berikutnya</h3>
                    <a href="jadwal-matakuliah.php">Lihat Jadwal</a>
                </div>

                <?php if ($jadwalBerikutnya) { ?>
                    <?php $statusNext = statusJadwal($jadwalBerikutnya['hari'], $jadwalBerikutnya['waktu_mulai'], $jadwalBerikutnya['waktu_selesai']); ?>

                    <div class="next-card">
                        <div class="next-top">
                            <div class="next-icon"><i class="fa-solid fa-book"></i></div>
                            <div class="next-title">
                                <h4><?php echo e($jadwalBerikutnya['nama_matkul'] ?: 'Mata Kuliah'); ?></h4>
                                <p><?php echo e($jadwalBerikutnya['hari']); ?>, <?php echo e(formatJam($jadwalBerikutnya['waktu_mulai'])); ?> - <?php echo e(formatJam($jadwalBerikutnya['waktu_selesai'])); ?></p>
                            </div>
                        </div>

                        <div class="info-row"><strong>Dosen</strong><span><?php echo e($jadwalBerikutnya['nama_dosen'] ?: '-'); ?></span></div>
                        <div class="info-row"><strong>Ruangan</strong><span><?php echo e($jadwalBerikutnya['nama_ruang'] ?: '-'); ?></span></div>
                        <div class="info-row"><strong>Gedung</strong><span><?php echo e($jadwalBerikutnya['gedung'] ?: '-'); ?></span></div>

                        <span class="status <?php echo e($statusNext['class']); ?>">
                            <i class="fa-solid fa-clock"></i>
                            <?php echo e($statusNext['text']); ?>
                        </span>
                    </div>

                <?php } else { ?>
                    <div class="empty">
                        <i class="fa-solid fa-mug-hot"></i>
                        Tidak ada jadwal lagi untuk hari ini.
                    </div>
                <?php } ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-bolt"></i> Akses Cepat</h3>
                </div>

                <div class="quick-grid">
                    <a href="jadwal-matakuliah.php" class="quick-card">
                        <div class="quick-icon"><i class="fa-solid fa-calendar-days"></i></div>
                        <h4>Jadwal Kuliah</h4>
                        <p>Lihat semua jadwal mata kuliah.</p>
                    </a>

                    <a href="notifikasi-reminder.php" class="quick-card">
                        <div class="quick-icon"><i class="fa-solid fa-bell"></i></div>
                        <h4>Reminder</h4>
                        <p>Cek pengingat jadwal kuliah.</p>
                    </a>

                    <a href="cek-bentrok.php" class="quick-card">
                        <div class="quick-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <h4>Konflik</h4>
                        <p>Lihat deteksi jadwal bentrok.</p>
                    </a>
                </div>
            </div>
        </section>

        <section class="grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-calendar-day"></i> Jadwal Hari Ini - <?php echo e($hariIni); ?></h3>
                    <a href="jadwal-matakuliah.php">Semua Jadwal</a>
                </div>

                <?php if (!empty($jadwalHariIni)) { ?>
                    <div class="today-list">
                        <?php foreach ($jadwalHariIni as $j) { ?>
                            <?php $status = statusJadwal($j['hari'], $j['waktu_mulai'], $j['waktu_selesai']); ?>

                            <div class="today-item">
                                <div class="today-time">
                                    <?php echo e(formatJam($j['waktu_mulai'])); ?><br>
                                    <?php echo e(formatJam($j['waktu_selesai'])); ?>
                                </div>
                                <div class="today-detail">
                                    <h4><?php echo e($j['nama_matkul'] ?: 'Mata Kuliah'); ?></h4>
                                    <p>
                                        Dosen: <?php echo e($j['nama_dosen'] ?: '-'); ?><br>
                                        Ruang: <?php echo e($j['nama_ruang'] ?: '-'); ?> | Gedung: <?php echo e($j['gedung'] ?: '-'); ?>
                                    </p>
                                    <span class="status <?php echo e($status['class']); ?>">
                                        <i class="fa-solid fa-clock"></i>
                                        <?php echo e($status['text']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        Tidak ada jadwal kuliah hari ini.
                    </div>
                <?php } ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-circle-info"></i> Info Akun</h3>
                </div>

                <div class="next-card">
                    <div class="info-row"><strong>Nama</strong><span><?php echo e($namaUser); ?></span></div>
                    <div class="info-row"><strong>NIM</strong><span><?php echo e($nimUser); ?></span></div>
                    <div class="info-row"><strong>Email</strong><span><?php echo e($emailUser); ?></span></div>
                    <div class="info-row"><strong>Role</strong><span><?php echo e($roleUser); ?></span></div>
                    <span class="status blue"><i class="fa-solid fa-user-check"></i> Login aktif</span>
                </div>
            </div>
        </section>

        <section class="card calendar-card">
            <div class="card-header">
                <h3><i class="fa-solid fa-calendar-week"></i> Kalender Jadwal Mingguan</h3>
                <a href="jadwal-matakuliah.php">Lihat Tabel</a>
            </div>

            <div class="calendar-wrapper">
                <?php foreach ($hariList as $hari) { ?>
                    <div class="day">
                        <div class="day-head <?php echo ($hari == $hariIni) ? 'today' : ''; ?>">
                            <?php echo e($hari); ?>
                        </div>

                        <div class="day-body">
                            <?php if (!empty($kalender[$hari])) { ?>
                                <?php foreach ($kalender[$hari] as $j) { ?>
                                    <div class="mini-jadwal">
                                        <div class="mini-time">
                                            <?php echo e(formatJam($j['waktu_mulai'])); ?> - <?php echo e(formatJam($j['waktu_selesai'])); ?>
                                        </div>
                                        <div class="mini-title">
                                            <?php echo e($j['nama_matkul'] ?: 'Mata Kuliah'); ?>
                                        </div>
                                        <div class="mini-info">
                                            <i class="fa-solid fa-user"></i>
                                            <?php echo e($j['nama_dosen'] ?: '-'); ?>
                                        </div>
                                        <div class="mini-info">
                                            <i class="fa-solid fa-door-open"></i>
                                            <?php echo e($j['nama_ruang'] ?: '-'); ?> | <?php echo e($j['gedung'] ?: '-'); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="mini-empty">Tidak ada jadwal</div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>
