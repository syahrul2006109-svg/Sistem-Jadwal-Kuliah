<?php
include "connect.php";

if(isset($_POST['register'])){

    $nim      = $_POST['nim'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $role = "user";

    $cek = mysqli_query($conn,
    "SELECT * FROM mahasiswa
     WHERE email='$email'");

    if(mysqli_num_rows($cek) > 0){

        echo "
        <script>
            alert('Email sudah terdaftar');
        </script>
        ";

    }else{

        $query = "INSERT INTO mahasiswa
                  (nim,nama,email,password,role)

                  VALUES

                  ('$nim',
                   '$nama',
                   '$email',
                   '$password',
                   '$role')";

        $result = mysqli_query($conn,$query);

        if($result){

            echo "
            <script>
                alert('Register Berhasil');
                window.location='login.php';
            </script>
            ";

        }else{

            echo "
            <script>
                alert('Register Gagal');
            </script>
            ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Jadwal Kuliah</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins', sans-serif;
        }

        html{
            width:100%;
            height:100%;
        }

        body{
            min-height:100vh;
            width:100%;
            margin:0;

            display:flex;
            justify-content:center;
            align-items:center;

            background:linear-gradient(
                135deg,
                #0f172a,
                #1d4ed8,
                #60a5fa
            );

            overflow-x:hidden;
            position:relative;

            padding:20px;
        }

        .bg-circle{
            position:absolute;
            border-radius:50%;
            filter:blur(80px);
            opacity:0.5;
        }

        .bg1{
            width:220px;
            height:220px;
            background:#93c5fd;
            top:-60px;
            left:-60px;
        }

        .bg2{
            width:250px;
            height:250px;
            background:#2563eb;
            bottom:0;
            right:0;
        }

        .container{
            width:100%;
            max-width:420px;

            background:rgba(255,255,255,0.15);

            backdrop-filter:blur(20px);

            border:1px solid rgba(255,255,255,0.2);

            padding:40px;

            border-radius:25px;

            box-shadow:0 10px 30px rgba(0,0,0,0.25);

            color:white;

            position:relative;
            z-index:1;
        }

        .logo{
            display:flex;
            justify-content:center;
            margin-bottom:20px;
        }

        .logo-box{
            width:90px;
            height:90px;
            background:white;
            border-radius:20px;

            display:flex;
            justify-content:center;
            align-items:center;

            box-shadow:0 5px 20px rgba(0,0,0,0.15);
        }

        .logo-box span{
            font-size:40px;
        }

        h2{
            text-align:center;
            font-size:30px;
            margin-bottom:8px;
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

            border-radius:12px;

            background:rgba(255,255,255,0.2);

            color:white;
            font-size:14px;

            transition:0.3s;
        }

        .input-group input::placeholder{
            color:#e5e7eb;
        }

        .input-group input:focus{
            background:rgba(255,255,255,0.3);
            box-shadow:0 0 10px rgba(255,255,255,0.2);
        }

        .btn{
            width:100%;
            padding:14px;

            border:none;
            border-radius:12px;

            background:white;
            color:#2563eb;

            font-size:16px;
            font-weight:600;

            cursor:pointer;
            transition:0.3s;
        }

        .btn:hover{
            transform:translateY(-2px);

            background:#eff6ff;

            box-shadow:0 10px 20px rgba(255,255,255,0.2);
        }

        .footer{
            text-align:center;
            margin-top:25px;

            color:#dbeafe;
            font-size:14px;
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

            body{
                padding:15px;
            }

            .container{
                padding:30px 20px;
            }

            h2{
                font-size:24px;
            }

            .subtitle{
                font-size:13px;
            }

            .logo-box{
                width:75px;
                height:75px;
            }

            .logo-box span{
                font-size:32px;
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
                <span>📘</span>
            </div>
        </div>

        <h2>Sistem Jadwal Kuliah</h2>

        <p class="subtitle">
            Buat akun untuk mengakses sistem jadwal kuliah
        </p>

        <form method="POST">

<div class="input-group">
    <label>NIM</label>
    <input type="text"
           name="nim"
           placeholder="Masukkan NIM"
           required>
</div>

<div class="input-group">
    <label>Nama</label>
    <input type="text"
           name="nama"
           placeholder="Masukkan Nama"
           required>
</div>

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
        name="register"
        class="btn">
    Register
</button>

</form>
        <div class="footer">

            Sudah punya akun?

            <a href="login.php">
                Login
            </a>

        </div>

    </div>

</body>
</html>
