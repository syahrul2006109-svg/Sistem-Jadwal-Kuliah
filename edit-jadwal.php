<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

$id_jadwal = $_GET['id'] ?? 0;

// Ambil data jadwal saat ini
$qJadwal = "SELECT jk.*, r.nama_ruang, r.gedung 
            FROM jadwal_kuliah jk 
            LEFT JOIN ruangan r ON jk.id_ruang = r.id_ruang 
            WHERE jk.id_jadwal = '$id_jadwal'";
$resJadwal = mysqli_query($conn, $qJadwal);

if (mysqli_num_rows($resJadwal) === 0) {
    echo "<script>alert('Jadwal tidak ditemukan!'); window.location='jadwal.php';</script>";
    exit;
}
$data = mysqli_fetch_assoc($resJadwal);

// Proses jika tombol simpan ditekan
if (isset($_POST['simpan'])) {
    $id_matkul_baru = $_POST['id_matkul'];
    $kelas_baru = mysqli_real_escape_string($conn, $_POST['kelas']);

    $qUpdate = "UPDATE jadwal_kuliah 
                SET id_matkul = '$id_matkul_baru', kelas = '$kelas_baru' 
                WHERE id_jadwal = '$id_jadwal'";
    
    if (mysqli_query($conn, $qUpdate)) {
        echo "<script>alert('Jadwal berhasil diupdate!'); window.location='jadwal.php';</script>";
        exit;
    } else {
        $error = "Gagal mengupdate data: " . mysqli_error($conn);
    }
}

// Ambil daftar mata kuliah untuk dropdown
$qMatkul = "SELECT * FROM mata_kuliah ORDER BY nama_matkul";
$resMatkul = mysqli_query($conn, $qMatkul);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal Kuliah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-body: #f4f7fe;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --accent-blue: #3b82f6;
            --border-light: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }

        .form-container {
            background: var(--bg-card);
            width: 100%;
            max-width: 500px;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(112, 144, 176, 0.12);
        }

        .header { margin-bottom: 24px; text-align: center; }
        .header h2 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .header p { font-size: 13px; color: #64748b; margin-top: 5px; }

        .info-box {
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .info-item { font-size: 13px; display: flex; align-items: center; gap: 10px; color: #475569; }
        .info-item i { color: var(--accent-blue); width: 16px; text-align: center; }
        .info-item strong { color: #0f172a; font-weight: 600; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #0f172a; }
        .form-control {
            width: 100%; padding: 12px 16px; font-size: 13px; border: 1px solid var(--border-light);
            border-radius: 10px; outline: none; transition: 0.3s; background: #ffffff;
        }
        .form-control:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

        .btn-group { display: flex; gap: 12px; margin-top: 30px; }
        .btn { flex: 1; padding: 12px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; transition: 0.2s; }
        .btn-cancel { background: #f1f5f9; color: #475569; }
        .btn-cancel:hover { background: #e2e8f0; }
        .btn-save { background: var(--accent-blue); color: #ffffff; }
        .btn-save:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59,130,246,0.3); }

        .alert { background: #fef2f2; color: #ef4444; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="form-container">
    <div class="header">
        <h2>Ubah Informasi Kelas</h2>
        <p>Edit mata kuliah atau kelas untuk jadwal ini</p>
    </div>

    <?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>

    <div class="info-box">
        <div class="info-item"><i class="fa-solid fa-calendar-day"></i> Hari: <strong><?= htmlspecialchars($data['hari']) ?></strong></div>
        <div class="info-item"><i class="fa-regular fa-clock"></i> Jam: <strong><?= substr($data['waktu_mulai'],0,5) ?> - <?= substr($data['waktu_selesai'],0,5) ?></strong></div>
        <div class="info-item"><i class="fa-solid fa-door-open"></i> Ruang: <strong><?= htmlspecialchars($data['nama_ruang']) ?> (<?= htmlspecialchars($data['gedung']) ?>)</strong></div>
    </div>

    <form action="" method="POST">
        
        <div class="form-group">
            <label for="id_matkul">Mata Kuliah</label>
            <select name="id_matkul" id="id_matkul" class="form-control" required>
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php while($m = mysqli_fetch_assoc($resMatkul)): ?>
                    <option value="<?= $m['id_matkul'] ?>" <?= ($m['id_matkul'] == $data['id_matkul']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nama_matkul']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

      

        <div class="btn-group">
            <a href="jadwal.php" class="btn btn-cancel">Batal</a>
            <button type="submit" name="simpan" class="btn btn-save">Simpan Perubahan</button>
        </div>
    </form>
</div>

</body>
</html>
