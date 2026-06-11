<?php
session_start();
include "connect.php";

function cekPassword($password_input, $password_db) {
    return password_verify($password_input, $password_db) || $password_input === $password_db;
}

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM dosen WHERE email_dosen = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result_dosen = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result_dosen) > 0) {
        $dosen = mysqli_fetch_assoc($result_dosen);

        if (cekPassword($password, $dosen['password'])) {

            $_SESSION['id_dosen'] = $dosen['id_dosen'];
            $_SESSION['nama'] = $dosen['nama_dosen'];
            $_SESSION['email'] = $dosen['email_dosen'];
            $_SESSION['role'] = 'Admin';

            header("Location: dashboard-admin.php");
            exit;
        }
    }

    
    $stmt = mysqli_prepare($conn, "SELECT * FROM mahasiswa WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result_mahasiswa = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result_mahasiswa) > 0) {
        $mahasiswa = mysqli_fetch_assoc($result_mahasiswa);

        if (cekPassword($password, $mahasiswa['password'])) {

            $_SESSION['nama'] = $mahasiswa['nama'];
            $_SESSION['nim'] = $mahasiswa['nim'];
            $_SESSION['email'] = $mahasiswa['email'];
            $_SESSION['role'] = 'user';
        
            header("Location: dashboard-user.php");
            exit;
        }
    }

    echo "<script>
        alert('Email atau password salah!');
        window.location='login.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - Sistem Jadwal Kuliah</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Poppins', sans-serif;

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    padding:20px;

    background:
    linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
    url('gambar/kampus.jpeg');

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    overflow:hidden;
    position:relative;
}

.bg-circle{
    position:absolute;
    border-radius:50%;
    filter:blur(90px);
    opacity:0.4;
}

.bg1{
    width:250px;
    height:250px;
    background:#93c5fd;

    top:-80px;
    left:-80px;
}

.bg2{
    width:280px;
    height:280px;
    background:#2563eb;

    bottom:-100px;
    right:-100px;
}

.container{

    width:100%;
    max-width:420px;

    background:rgba(255,255,255,0.15);

    backdrop-filter:blur(18px);

    border:1px solid rgba(255,255,255,0.2);

    border-radius:28px;

    padding:40px;

    color:white;

    box-shadow:
    0 10px 30px rgba(0,0,0,0.25);

    position:relative;
    z-index:1;
}

.logo{
    display:flex;
    justify-content:center;
    margin-bottom:20px;
}

.logo-box{

    width:120px;
    height:120px;

    background:white;

    border-radius:20px;

    overflow:hidden;

    display:flex;
    justify-content:center;
    align-items:center;

    box-shadow:
    0 10px 20px rgba(0,0,0,0.15);
}

.logo-box img{

    width:100%;
    height:100%;

    object-fit:cover;

    display:block;
}

h2{

    text-align:center;

    font-family:'Outfit', sans-serif;

    font-size:34px;

    margin-bottom:10px;
}

.subtitle{

    text-align:center;

    color:#dbeafe;

    margin-bottom:30px;

    font-size:14px;
}

.input-group{
    margin-bottom:20px;
}

.input-group label{

    display:block;

    margin-bottom:8px;

    font-size:14px;

    font-weight:500;
}

.input-group input{

    width:100%;

    padding:14px;

    border:none;
    outline:none;

    border-radius:14px;

    background:rgba(255,255,255,0.2);

    color:white;

    font-size:14px;

    transition:0.3s;
}

.input-group input::placeholder{
    color:#e2e8f0;
}

.input-group input:focus{

    background:rgba(255,255,255,0.3);

    box-shadow:
    0 0 10px rgba(255,255,255,0.25);
}

.btn{

    width:100%;

    padding:14px;

    border:none;

    border-radius:14px;

    background:white;

    color:#2563eb;

    font-size:16px;

    font-weight:600;

    cursor:pointer;

    transition:0.3s;
}

.btn:hover{

    transform:translateY(-3px);

    background:#eff6ff;
}

.footer{

    text-align:center;

    margin-top:25px;

    font-size:14px;

    color:#dbeafe;
}

.footer a{

    color:white;

    text-decoration:none;

    font-weight:600;
}

.footer a:hover{
    text-decoration:underline;
}

@media(max-width:480px){

    .container{
        padding:30px 20px;
    }

    h2{
        font-size:28px;
    }

    .logo-box{
        width:100px;
        height:100px;
    }
}

</style>

</head>

<body>

<div class="bg-circle bg1"></div>
<div class="bg-circle bg2"></div>

<div class="container">

    <div class="logo">

        <div class="logo-box">
        <img src="logo.png" alt="Logo ITH">
        </div>

    </div>

    <h2>
        Sistem Jadwal Kuliah
    </h2>

    <p class="subtitle">
        Login untuk mengakses sistem
    </p>

    <form method="POST">

        <div class="input-group">

            <label>Email</label>

            <input type="email"
                   name="email"
                   placeholder="Masukkan Email"
                   required>

        </div>

        <div class="input-group">

            <label>Password</label>

            <input type="password"
                   name="password"
                   placeholder="Masukkan Password"
                   required>

        </div>

        <button type="submit"
                name="login"
                class="btn">

            Login

        </button>

    </form>

    <div class="footer">

        Belum punya akun?

        <a href="register.php">
            Register
        </a>

    </div>

</div>

</body>
</html>
