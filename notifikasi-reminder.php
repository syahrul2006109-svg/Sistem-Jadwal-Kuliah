<?php
session_start();
include "connect.php";

date_default_timezone_set("Asia/Makassar");

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$role = $_SESSION['role'] ?? '';

if ($role == "Admin" || $role == "Dosen") {
    $backUrl = "dashboard-admin.php";
} else {
    $backUrl = "dashboard-user.php";
}

function statusJadwal($hari, $mulai, $selesai) {
    $hari_array = [
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
        'Sunday'    => 'Minggu'
    ];

    $hari_ini = $hari_array[date('l')];
    $jam_sekarang = date('H:i:s');

    if ($hari != $hari_ini) {
        return [
            'text' => 'Terjadwal',
            'class' => 'blue'
        ];
    }

    if ($jam_sekarang < $mulai) {
        $sekarang = strtotime(date('H:i:s'));
        $mulaiTime = strtotime($mulai);
        $selisihMenit = floor(($mulaiTime - $sekarang) / 60);

        if ($selisihMenit >= 60) {
            $jam = floor($selisihMenit / 60);
            $menit = $selisihMenit % 60;

            if ($menit > 0) {
                $text = $jam . " jam " . $menit . " menit lagi";
            } else {
                $text = $jam . " jam lagi";
            }
        } else {
            $text = $selisihMenit . " menit lagi";
        }

        return [
            'text' => $text,
            'class' => 'green'
        ];
    }

    if ($jam_sekarang >= $mulai && $jam_sekarang <= $selesai) {
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

/*
|--------------------------------------------------------------------------
| AMBIL SEMUA JADWAL
|--------------------------------------------------------------------------
| jadwal_kuliah = sumber utama
| pengingat = tambahan pesan dan status aktif/nonaktif
*/

$data = mysqli_query($conn, "
    SELECT 
        jk.id_jadwal,
        jk.hari,
        jk.waktu_mulai,
        jk.waktu_selesai,
        jk.gedung,

        mk.nama_matkul,
        d.nama_dosen,
        r.nama_ruang,

        p.id_pengingat,
        p.waktu_pengingat,
        p.pesan,
        p.status AS status_pengingat

    FROM jadwal_kuliah jk

    LEFT JOIN mata_kuliah mk
    ON jk.id_matkul = mk.id_matkul

    LEFT JOIN dosen d
    ON jk.id_dosen = d.id_dosen

    LEFT JOIN ruangan r
    ON jk.id_ruang = r.id_ruang

    LEFT JOIN pengingat p
    ON jk.id_jadwal = p.id_jadwal

    ORDER BY 
    FIELD(jk.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
    jk.waktu_mulai ASC
");

if (!$data) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta http-equiv="refresh" content="30">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reminder Jadwal Kuliah</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
    max-width:1350px;
    margin:auto;
}


.header{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    padding:38px;
    border-radius:28px;
    color:white;
    margin-bottom:30px;
    box-shadow:0 15px 35px rgba(37,99,235,0.20);
    position:relative;
    overflow:hidden;
}

.header::before{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    background:rgba(255,255,255,0.13);
    border-radius:50%;
    top:-100px;
    right:-80px;
}

.header h1{
    font-size:36px;
    font-weight:800;
    margin-bottom:10px;
    position:relative;
    z-index:1;
}

.header p{
    color:#dbeafe;
    font-size:16px;
    position:relative;
    z-index:1;
}


.search-box{
    margin-bottom:24px;
    display:flex;
    align-items:center;
    gap:12px;
    background:white;
    border:1px solid #dbeafe;
    padding:16px 18px;
    border-radius:18px;
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


.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(340px, 1fr));
    gap:24px;
}

.card{
    background:white;
    border-radius:24px;
    padding:26px;
    box-shadow:0 12px 30px rgba(37,99,235,0.10);
    border:1px solid #dbeafe;
    transition:0.25s;
}

.card:hover{
    transform:translateY(-5px);
}

.top{
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:22px;
}

.icon{
    width:66px;
    height:66px;
    border-radius:18px;
    background:#dbeafe;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-shrink:0;
}

.icon i{
    font-size:28px;
    color:#2563eb;
}

.card h3{
    font-size:24px;
    color:#071633;
    font-weight:800;
}

.card .matkul{
    color:#1d4ed8;
    font-weight:700;
    margin-top:4px;
}

.info{
    margin-bottom:12px;
    color:#475569;
    font-size:15px;
    line-height:1.7;
}

.info strong{
    color:#071633;
}


.status{
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:12px;
    padding:9px 16px;
    border-radius:30px;
    font-size:13px;
    font-weight:700;
}

.status.green{
    background:#dcfce7;
    color:#166534;
}

.status.blue{
    background:#dbeafe;
    color:#1d4ed8;
}

.status.orange{
    background:#ffedd5;
    color:#9a3412;
}

.status.gray{
    background:#f1f5f9;
    color:#64748b;
}

.status.red{
    background:#fee2e2;
    color:#991b1b;
}


.btn{
    display:inline-flex;
    align-items:center;
    gap:10px;
    margin-top:35px;
    text-decoration:none;
    background:#2563eb;
    color:white;
    padding:14px 22px;
    border-radius:14px;
    font-weight:700;
    transition:0.25s;
}

.btn:hover{
    background:#1d4ed8;
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(37,99,235,0.20);
}


.empty{
    background:white;
    padding:45px;
    border-radius:24px;
    text-align:center;
    color:#64748b;
    box-shadow:0 12px 30px rgba(37,99,235,0.10);
    border:1px solid #dbeafe;
}

.empty i{
    font-size:45px;
    color:#2563eb;
    margin-bottom:15px;
}

.empty h2{
    color:#071633;
    margin-bottom:8px;
}

@media(max-width:768px){
    body{
        padding:20px;
    }

    .header{
        padding:28px;
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
        <h1>
            <i class="fa-solid fa-bell"></i>
            Reminder Jadwal Kuliah
        </h1>

        <p>
            Menampilkan reminder dari semua jadwal kuliah yang tersimpan di sistem
        </p>
    </div>

    <?php if (mysqli_num_rows($data) > 0) { ?>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari mata kuliah, dosen, ruangan, gedung, atau hari...">
        </div>

        <div class="cards" id="reminderCards">

            <?php while ($d = mysqli_fetch_assoc($data)) { ?>

                <?php
                $statusOtomatis = statusJadwal(
                    $d['hari'],
                    $d['waktu_mulai'],
                    $d['waktu_selesai']
                );

                $pesan = $d['pesan'];

                if (!$pesan) {
                    $pesan = "Jangan lupa mengikuti jadwal kuliah sesuai waktu yang ditentukan.";
                }

                $statusPengingat = $d['status_pengingat'];

                if (!$statusPengingat) {
                    $statusPengingat = "Aktif";
                }

                $waktuPengingat = $d['waktu_pengingat'];

                if (!$waktuPengingat) {
                    $waktuPengingat = "Belum diatur";
                }
                ?>

                <div class="card">

                    <div class="top">
                        <div class="icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div>
                            <h3><?php echo e($d['hari']); ?></h3>
                            <div class="matkul">
                                <?php echo e($d['nama_matkul'] ?: 'Mata kuliah tidak ditemukan'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="info">
                        <strong>Jam:</strong>
                        <?php echo e(substr($d['waktu_mulai'], 0, 5)); ?>
                        -
                        <?php echo e(substr($d['waktu_selesai'], 0, 5)); ?>
                    </div>

                    <div class="info">
                        <strong>Dosen:</strong>
                        <?php echo e($d['nama_dosen'] ?: 'Dosen tidak ditemukan'); ?>
                    </div>

                    <div class="info">
                        <strong>Ruangan:</strong>
                        <?php echo e($d['nama_ruang'] ?: 'Ruangan tidak ditemukan'); ?>
                    </div>

                    <div class="info">
                        <strong>Gedung:</strong>
                        <?php echo e($d['gedung']); ?>
                    </div>

                    <div class="info">
                        <strong>Waktu Pengingat:</strong>
                        <?php echo e($waktuPengingat); ?>
                    </div>

                    <div class="info">
                        <strong>Pesan:</strong>
                        <?php echo e($pesan); ?>
                    </div>

                    <div class="info">
                        <strong>Status Pengingat:</strong>
                        <?php echo e($statusPengingat); ?>
                    </div>

                    <span class="status <?php echo e($statusOtomatis['class']); ?>">
                        <i class="fa-solid fa-clock"></i>
                        <?php echo e($statusOtomatis['text']); ?>
                    </span>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty">
            <i class="fa-solid fa-calendar-xmark"></i>

            <h2>Belum Ada Jadwal</h2>

            <p>
                Belum ada jadwal kuliah yang tersimpan di database.
            </p>
        </div>

    <?php } ?>

    <a href="<?php echo e($backUrl); ?>" class="btn">
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

            if (text.includes(keyword)) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    });
}
</script>

</body>
</html>
