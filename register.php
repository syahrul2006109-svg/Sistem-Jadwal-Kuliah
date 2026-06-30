<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Jadwal Kuliah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('gambar/kampus.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        .bg-circle { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.4; z-index: 0; }
        .bg1 { width: 250px; height: 250px; background: #93c5fd; top: -80px; left: -80px; }
        .bg2 { width: 280px; height: 280px; background: #2563eb; bottom: -100px; right: -100px; }

        .container {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 28px;
            padding: 40px;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .logo-box {
            width: 100px;
            height: 100px;
            background: transparent;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 500; }
        input { width: 100%; padding: 12px; border: none; outline: none; border-radius: 14px; background: rgba(0, 0, 0, 0.2); color: white; font-size: 14px; box-sizing: border-box; }
        .btn { width: 100%; padding: 14px; border: none; border-radius: 14px; background: white; color: #2563eb; font-weight: 600; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn:hover { background:rgb(0, 110, 255); transform: translateY(-3px); }
        .footer { margin-top: 20px; font-size: 13px; }
        .footer a { color: #fff; font-weight: 700; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="bg-circle bg1"></div>
    <div class="bg-circle bg2"></div>

    <div class="container">
        <div class="logo-box">
            <img src="logo.png" alt="Logo">
        </div>

        <h2>Daftar Akun</h2>
        <p>Silakan isi data untuk memulai</p>

        <form method="POST">
            <div class="input-group">
                <label>NIM</label>
                <input type="text" name="nim" placeholder="Masukkan NIM" required>
            </div>
            <div class="input-group">
                <label>Nama</label>
                <input type="text" name="nama" placeholder="Masukkan Nama" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan Email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan Password" required>
            </div>
            <button type="submit" name="register" class="btn">Register Sekarang</button>
        </form>

        <div class="footer">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>

</body>
</html>
