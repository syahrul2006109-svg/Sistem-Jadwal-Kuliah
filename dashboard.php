<?php
session_start();

if(!isset($_SESSION['nama'])){
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard - Sistem Jadwal Kuliah</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins', sans-serif;
    background:#f1f5f9;
    display:flex;
}

/* SIDEBAR */

.sidebar{
    width:270px;
    height:100vh;

    background:linear-gradient(
        180deg,
        #0f172a,
        #1e3a8a
    );

    position:fixed;
    left:0;
    top:0;

    padding:30px 20px;

    color:white;

    overflow-y:auto;
}

.logo{
    display:flex;
    align-items:center;
    gap:15px;

    margin-bottom:40px;
}

.logo-icon{
    width:60px;
    height:60px;

    border-radius:18px;

    background:white;

    display:flex;
    justify-content:center;
    align-items:center;

    font-size:28px;

    color:#2563eb;
}

.logo-text h2{
    font-family:'Outfit', sans-serif;
    font-size:24px;
}

.logo-text p{
    font-size:12px;
    color:#cbd5e1;
}

.menu-title{
    color:#94a3b8;
    font-size:13px;
    margin-bottom:15px;
    margin-top:20px;
}

.sidebar ul{
    list-style:none;
}

.sidebar ul li{
    margin-bottom:12px;
}

.sidebar ul li a{
    text-decoration:none;
    color:white;

    display:flex;
    align-items:center;
    gap:15px;

    padding:14px 16px;

    border-radius:14px;

    transition:0.3s;
}

.sidebar ul li a:hover{
    background:rgba(255,255,255,0.12);
    transform:translateX(5px);
}

/* MAIN */

.main{
    margin-left:270px;
    width:100%;
    padding:30px;
}

/* TOPBAR */

.topbar{
    background:white;

    padding:18px 25px;

    border-radius:20px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:30px;

    box-shadow:0 4px 15px rgba(0,0,0,0.06);
}

.topbar h1{
    color:#0f172a;
    font-size:28px;
}

.profile{
    display:flex;
    align-items:center;
    gap:12px;
}

.avatar{
    width:45px;
    height:45px;

    border-radius:50%;

    background:#2563eb;

    color:white;

    display:flex;
    justify-content:center;
    align-items:center;

    font-weight:bold;
}

/* HERO */

.hero{
    background:linear-gradient(
        135deg,
        #2563eb,
        #38bdf8
    );

    color:white;

    padding:40px;

    border-radius:25px;

    margin-bottom:30px;

    position:relative;

    overflow:hidden;

    box-shadow:0 10px 25px rgba(37,99,235,0.25);
}

.hero::before{
    content:'';

    position:absolute;

    width:250px;
    height:250px;

    background:rgba(255,255,255,0.1);

    border-radius:50%;

    top:-100px;
    right:-80px;
}

.hero h2{
    font-size:38px;
    margin-bottom:10px;

    font-family:'Outfit', sans-serif;
}

.hero p{
    color:#e0f2fe;
}

/* CARDS */

.cards{
    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(240px,1fr));

    gap:20px;
}

.card-link{
    text-decoration:none;
}

.card{
    background:white;

    border-radius:22px;

    padding:25px;

    box-shadow:0 5px 15px rgba(0,0,0,0.06);

    transition:0.3s;
}

.card:hover{
    transform:translateY(-8px);
}

.icon-box{
    width:65px;
    height:65px;

    border-radius:18px;

    background:#dbeafe;

    display:flex;
    justify-content:center;
    align-items:center;

    margin-bottom:20px;
}

.icon-box i{
    font-size:28px;
    color:#2563eb;
}

.card h3{
    color:#0f172a;
    margin-bottom:10px;
}

.card p{
    color:#64748b;
    font-size:14px;
    line-height:1.6;
}

/* TABLE */

.table-box{
    background:white;

    margin-top:35px;

    border-radius:22px;

    padding:25px;

    box-shadow:0 5px 15px rgba(0,0,0,0.06);
}

.table-box h2{
    margin-bottom:20px;
    color:#0f172a;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#2563eb;
    color:white;

    padding:15px;
    text-align:left;
}

table td{
    padding:15px;
    border-bottom:1px solid #e2e8f0;
}

.status{
    background:#dcfce7;
    color:#166534;

    padding:6px 14px;

    border-radius:20px;

    font-size:13px;
}

/* RESPONSIVE */

@media(max-width:900px){

    body{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        height:auto;
        position:relative;
    }

    .main{
        margin-left:0;
    }
}

@media(max-width:600px){

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

    .hero h2{
        font-size:28px;
    }

    .main{
        padding:20px;
    }
}

</style>

</head>
<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            📘
        </div>

        <div class="logo-text">
            <h2>Sistem Jadwal</h2>
            <p>Manajemen Perkuliahan</p>
        </div>

    </div>

    <div class="menu-title">
        MENU UTAMA
    </div>

    <ul>

        <li>
            <a href="dashboard.php">
                <i class="fa-solid fa-house"></i>
                Dashboard
            </a>
        </li>

        <?php if($role == 'Admin'){ ?>

        <li>
            <a href="mahasiswa.php">
                <i class="fa-solid fa-users"></i>
                Mahasiswa
            </a>
        </li>

        <li>
            <a href="akun.php">
                <i class="fa-solid fa-user-shield"></i>
                Manajemen Akun
            </a>
        </li>

        <?php } ?>

        <li>
            <a href="matakuliah.php">
                <i class="fa-solid fa-book"></i>
                Mata Kuliah
            </a>
        </li>

        <li>
            <a href="jadwal-matakuliah.php">
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

        <li>
            <a href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </li>

    </ul>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <h1>Dashboard</h1>

        <div class="profile">

            <div class="avatar">
                <?php echo strtoupper(substr($nama,0,1)); ?>
            </div>

            <div>

                <strong>
                    <?php echo $nama; ?>
                </strong>

                <br>

                <small>
                    <?php echo $role; ?>
                </small>

            </div>

        </div>

    </div>

    <!-- HERO -->

    <div class="hero">

        <h2>
            Selamat Datang 👋
        </h2>

        <p>
            Sistem Informasi Jadwal Kuliah Mahasiswa
        </p>

    </div>

    <!-- CARDS -->

    <div class="cards">

        <?php if($role == 'Admin'){ ?>

        <a href="mahasiswa.php" class="card-link">

            <div class="card">

                <div class="icon-box">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>

                <h3>Mahasiswa</h3>

                <p>
                    Kelola data mahasiswa aktif.
                </p>

            </div>

        </a>

        <a href="akun.php" class="card-link">

            <div class="card">

                <div class="icon-box">
                    <i class="fa-solid fa-users"></i>
                </div>

                <h3>Manajemen Akun</h3>

                <p>
                    Kelola akun admin dan mahasiswa.
                </p>

            </div>

        </a>

        <?php } ?>

        <a href="matakuliah.php" class="card-link">

            <div class="card">

                <div class="icon-box">
                    <i class="fa-solid fa-book"></i>
                </div>

                <h3>Mata Kuliah</h3>

                <p>
                    Informasi mata kuliah dan SKS.
                </p>

            </div>

        </a>

        <a href="jadwal-matakuliah.php" class="card-link">

            <div class="card">

                <div class="icon-box">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <h3>Jadwal Kuliah</h3>

                <p>
                    Kelola jadwal perkuliahan mahasiswa.
                </p>

            </div>

        </a>

        <a href="ruangan.php" class="card-link">

            <div class="card">

                <div class="icon-box">
                    <i class="fa-solid fa-building"></i>
                </div>

                <h3>Ruangan</h3>

                <p>
                    Informasi gedung dan ruangan kuliah.
                </p>

            </div>

        </a>

        <a href="notifikasi-reminder.php" class="card-link">

            <div class="card">

                <div class="icon-box">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <h3>Reminder</h3>

                <p>
                    Pengingat otomatis jadwal kuliah.
                </p>

            </div>

        </a>

    </div>

    <!-- TABLE ADMIN -->

    <?php if($role == 'Admin'){ ?>

    <div class="table-box">

        <h2>
            Aktivitas Sistem
        </h2>

        <table>

            <tr>
                <th>No</th>
                <th>Aktivitas</th>
                <th>Status</th>
            </tr>

            <tr>
                <td>1</td>
                <td>Mahasiswa Login</td>
                <td>
                    <span class="status">
                        Berhasil
                    </span>
                </td>
            </tr>

            <tr>
                <td>2</td>
                <td>Data Jadwal Diakses</td>
                <td>
                    <span class="status">
                        Aktif
                    </span>
                </td>
            </tr>

            <tr>
                <td>3</td>
                <td>Sistem Database</td>
                <td>
                    <span class="status">
                        Online
                    </span>
                </td>
            </tr>

        </table>

    </div>

    <?php } ?>

</div>

</body>
</html>