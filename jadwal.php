<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

$id_dosen_login = $_SESSION['id_dosen'] ?? 0;
$prefill_id_ruang = $_GET['id_ruang'] ?? '';
$prefill_gedung = $_GET['gedung'] ?? '';
$prefill_hari = $_GET['hari'] ?? '';
$prefill_waktu_mulai = $_GET['waktu_mulai'] ?? '';
$prefill_waktu_selesai = $_GET['waktu_selesai'] ?? '';

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$qRuang = "SELECT id_ruang, nama_ruang, gedung FROM ruangan ORDER BY gedung, nama_ruang";
$resRuang = mysqli_query($conn, $qRuang);
$ruanganList = [];
while ($r = mysqli_fetch_assoc($resRuang)) {
    $ruanganList[] = $r;
}


// Hari yang mau ditampilkan di roster. Kalau mau tambah Jumat, tinggal tambahkan di array ini.
$hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis'];

// Slot waktu kampus: 5 sesi tetap untuk setiap hari.
// Jadwal yang muncul di tabel harus punya waktu yang sama persis dengan slot ini.
$jamKampus = [
    ['sesi' => 'I',   'waktu_mulai' => '07:30:00', 'waktu_selesai' => '09:10:00'],
    ['sesi' => 'II',  'waktu_mulai' => '09:15:00', 'waktu_selesai' => '10:55:00'],
    ['sesi' => 'III', 'waktu_mulai' => '11:00:00', 'waktu_selesai' => '12:40:00'],
    ['sesi' => 'IV',  'waktu_mulai' => '14:00:00', 'waktu_selesai' => '15:40:00'],
    ['sesi' => 'V',   'waktu_mulai' => '15:45:00', 'waktu_selesai' => '17:25:00'],
];

$slots = [];
foreach ($hariList as $hariItem) {
    foreach ($jamKampus as $jam) {
        $slots[] = [
            'hari' => $hariItem,
            'sesi' => $jam['sesi'],
            'waktu_mulai' => $jam['waktu_mulai'],
            'waktu_selesai' => $jam['waktu_selesai'],
        ];
    }
}


$qJadwal = "
SELECT 
    jk.id_jadwal, jk.id_matkul, jk.id_dosen, jk.id_ruang,
    jk.gedung, jk.hari, jk.waktu_mulai, jk.waktu_selesai,
    COALESCE(jk.kelas, '-') AS kelas,
    mk.nama_matkul, d.nama_dosen, r.nama_ruang
FROM jadwal_kuliah jk
LEFT JOIN mata_kuliah mk ON jk.id_matkul = mk.id_matkul
LEFT JOIN dosen d        ON jk.id_dosen  = d.id_dosen
LEFT JOIN ruangan r      ON jk.id_ruang  = r.id_ruang
ORDER BY FIELD(jk.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jk.waktu_mulai
";
$resJadwal = mysqli_query($conn, $qJadwal);
if (!$resJadwal) die("Query error: " . mysqli_error($conn));

$jadwalMap = [];
while ($j = mysqli_fetch_assoc($resJadwal)) {
    $key = $j['hari'] . '|' . $j['waktu_mulai'] . '|' . $j['id_ruang'];
    $jadwalMap[$key] = $j;
}

$paletteHeader = [
    '#2563eb', // LT.001 - biru
    '#0ea5e9', // LT.002 - biru langit
    '#10b981', // LT.003 - hijau
    '#059669', // LT.004 - hijau tua
    '#f59e0b', // LT.005 - amber
    '#d97706', // LT.006 - oranye
    '#8b5cf6', // LT.007 - ungu
    '#6d28d9', // LT.008 - ungu tua
    '#ef4444', // LT.009 - merah
    '#ec4899', // LT.010 - pink
    '#14b8a6', // cadangan
    '#f43f5e', // cadangan
];
$paletteCell = [
    '#dbeafe',
    '#e0f2fe',
    '#d1fae5',
    '#a7f3d0',
    '#fef3c7',
    '#ffedd5',
    '#ede9fe',
    '#ddd6fe',
    '#fee2e2',
    '#fce7f3',
    '#ccfbf1',
    '#ffe4e6',
];
$paletteCellText = [
    '#1e40af',
    '#0369a1',
    '#065f46',
    '#047857',
    '#92400e',
    '#9a3412',
    '#4c1d95',
    '#5b21b6',
    '#991b1b',
    '#9d174d',
    '#134e4a',
    '#9f1239',
];


$ruangIndex = [];
foreach ($ruanganList as $i => $r) {
    $ruangIndex[$r['id_ruang']] = $i;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Kuliah – Roster</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{
    min-height:100vh;
    background:#eef5ff;
    padding:28px;
    color:#071633;
}

.container{
    width:100%;
    max-width:100%;
    background:white;
    border-radius:28px;
    padding:30px;
    box-shadow:0 15px 35px rgba(37,99,235,0.13);
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
    margin-bottom:24px;
}
.title h1{font-size:28px;font-weight:800;color:#071633;}
.title p{color:#64748b;margin-top:6px;font-size:14px;}

.header-actions{display:flex;gap:12px;flex-wrap:wrap;}

.btn{
    border:none;text-decoration:none;
    padding:11px 18px;border-radius:13px;
    font-weight:700;cursor:pointer;
    display:inline-flex;align-items:center;gap:8px;
    transition:.22s;font-size:14px;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(37,99,235,0.18);}
.btn-back{background:#dbeafe;color:#1d4ed8;}
.btn-add{background:linear-gradient(135deg,#2563eb,#38bdf8);color:white;}

.search-box{
    margin-bottom:20px;
    display:flex;align-items:center;gap:12px;
    background:#f8fbff;border:1.5px solid #dbeafe;
    padding:14px 18px;border-radius:15px;
}
.search-box i{color:#64748b;}
.search-box input{
    width:100%;border:none;outline:none;
    background:transparent;font-size:15px;color:#0f172a;
}

.legend{
    display:flex;flex-wrap:wrap;gap:10px;
    margin-bottom:18px;
}
.legend-item{
    display:flex;align-items:center;gap:7px;
    font-size:13px;font-weight:600;
    padding:6px 14px;border-radius:999px;
}
.legend-dot{
    width:12px;height:12px;border-radius:50%;
    display:inline-block;
}

.table-wrap{
    overflow:auto;
    max-height:calc(100vh - 260px);
    border-radius:18px;
    border:1.5px solid #dbeafe;
    position:relative;
}

table{
    border-collapse:collapse;
    min-width:900px;
    width:100%;
}

thead tr th{
    position:sticky;
    top:0;
    z-index:3;
    text-align:center;
    padding:14px 12px;
    font-size:13px;
    font-weight:700;
    white-space:nowrap;
    color:white;
    border-right:1.5px solid rgba(255,255,255,0.25);
}

th.col-hari, td.col-hari{
    position:sticky;left:0;z-index:4;
    min-width:90px;
    background:white;
    border-right:2px solid #dbeafe;
    text-align:center;
    font-weight:700;
    font-size:13px;
}
th.col-jam, td.col-jam{
    position:sticky;left:90px;z-index:4;
    min-width:110px;
    background:white;
    border-right:2px solid #dbeafe;
    text-align:center;
    font-size:12px;
    color:#475569;
    font-weight:600;
}

/* Override sticky header cells (need higher z) */
thead th.col-hari{z-index:6;}
thead th.col-jam{z-index:6;}

td{
    padding:10px 10px;
    border-bottom:1px solid #e5efff;
    border-right:1px solid #e5efff;
    font-size:12px;
    vertical-align:top;
    min-width:140px;
    max-width:200px;
}

.hari-badge{
    display:inline-block;
    padding:5px 12px;
    border-radius:999px;
    background:#dbeafe;
    color:#1d4ed8;
    font-weight:800;
    font-size:12px;
}

.jadwal-card{
    border-radius:10px;
    padding:8px 10px;
    font-size:12px;
    line-height:1.5;
    word-break:break-word;
    white-space:normal;
}
.jadwal-card .matkul{font-weight:700;margin-bottom:2px;}
.jadwal-card .dosen{font-size:11px;margin-bottom:2px;}
.jadwal-card .jam{font-size:11px;opacity:.8;margin-bottom:3px;}
.jadwal-card .aksi{
    margin-top:6px;
    display:flex;gap:5px;flex-wrap:wrap;
}
.jadwal-card .aksi a{
    font-size:11px;font-weight:700;
    padding:3px 9px;border-radius:8px;
    text-decoration:none;display:inline-flex;
    align-items:center;gap:4px;
    transition:.2s;
}
.jadwal-card .aksi a:hover{opacity:.8;}
.btn-edit-sm{background:#2563eb;color:white;}
.btn-del-sm{background:#ef4444;color:white;}

.empty-cell{color:#cbd5e1;text-align:center;padding:18px 0;font-size:11px;}

.jadwal-card.mine {
    background: #dbeafe;
    color: #1e40af;
    border-left: 4px solid #2563eb;
}

.jadwal-card.other {
    background: #e5e7eb;
    color: #374151;
    border-left: 4px solid #9ca3af;
}

.empty-link {
    display: block;
    width: 100%;
    min-height: 76px;
    padding: 18px 10px;
    border-radius: 12px;
    text-decoration: none;
    color: #cbd5e1;
    background: white;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    border: 1.5px dashed transparent;
    transition: .2s;
}

.empty-link:hover {
    color: #2563eb;
    background: #f8fbff;
    border-color: #93c5fd;
}

.empty-link i {
    display: block;
    margin-bottom: 5px;
    font-size: 15px;
}

tr:hover td{background:#f8fbff;}

tr:hover td{background:#f8fbff;}
/* But don't override the colored sticky cells on hover */
tr:hover td.col-hari,
tr:hover td.col-jam{background:white;}

.jadwal-card .kelas {
    display: inline-block;
    margin: 3px 0 5px;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.12);
    color: #1d4ed8;
    font-size: 10px;
    font-weight: 800;
}

.jadwal-card.other .kelas {
    background: #d1d5db;
    color: #374151;
}

.jadwal-card.mine .kelas {
    background: rgba(37, 99, 235, 0.16);
    color: #1d4ed8;
}

@media(max-width:768px){
    body{padding:14px;}
    .container{padding:18px;}
    .title h1{font-size:20px;}
}
thead th.col-jam {
    color: #ffffff !important;
    font-weight: 900 !important;
    opacity: 1 !important;
}

thead th.col-hari {
    color: #ffffff !important;
    font-weight: 900 !important;
    opacity: 1 !important;
}

</style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <div class="title">
            <h1><i class="fa-solid fa-calendar-days"></i> Jadwal Kuliah</h1>
            <p>Roster jadwal perkuliahan – Semester Genap 2025/2026</p>
        </div>
        <div class="header-actions">
            <a href="dashboard-admin.php" class="btn btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari mata kuliah, dosen, atau ruangan...">
    </div>

    <div class="legend">
        <?php foreach ($ruanganList as $r):
            $idx = $ruangIndex[$r['id_ruang']] ?? 0;
        ?>
        <span class="legend-item" style="background:<?= $paletteCell[$idx % count($paletteCell)] ?>;color:<?= $paletteCellText[$idx % count($paletteCellText)] ?>">
            <span class="legend-dot" style="background:<?= $paletteHeader[$idx % count($paletteHeader)] ?>"></span>
            <?= e($r['nama_ruang']) ?>
        </span>
        <?php endforeach; ?>
    </div>

    
    <div class="table-wrap" id="rosterWrap">
        <table id="rosterTable">
            <thead>
                <tr>
                    
                    <th class="col-hari" style="background:#1e3a5f;">Hari</th>
                    <th class="col-jam"  style="background:#1e3a5f;">Jam</th>

                    <?php foreach ($ruanganList as $r):
                        $idx = $ruangIndex[$r['id_ruang']] ?? 0;
                        $bgH = $paletteHeader[$idx % count($paletteHeader)];
                    ?>
                    <th style="background:<?= $bgH ?>">
                        <?= e($r['nama_ruang']) ?><br>
                        <span style="font-size:11px;font-weight:500;opacity:.85;"><?= e($r['gedung']) ?></span>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php
                $prevHari = null;
                foreach ($slots as $slot):
                    $hari = $slot['hari'];
                    $mulai = substr($slot['waktu_mulai'],0,5);
                    $selesai = substr($slot['waktu_selesai'],0,5);
                ?>
                <tr>
                    
                    <td class="col-hari">
                        <?php if ($hari !== $prevHari): $prevHari = $hari; ?>
                            <span class="hari-badge"><?= e($hari) ?></span>
                        <?php else: ?>
                            <span style="color:#cbd5e1;font-size:11px;">↑</span>
                        <?php endif; ?>
                    </td>

            
                    <td class="col-jam">
                        <strong>Sesi <?= e($slot['sesi']) ?></strong><br>
                        <?= $mulai ?><br><span style="color:#94a3b8">–</span><br><?= $selesai ?>
                    </td>

                
                    <?php foreach ($ruanganList as $r):
                        $idx  = $ruangIndex[$r['id_ruang']] ?? 0;
                        $bgC  = $paletteCell[$idx % count($paletteCell)];
                        $txtC = $paletteCellText[$idx % count($paletteCellText)];
                        $bgH  = $paletteHeader[$idx % count($paletteHeader)];
                        $key  = $hari . '|' . $slot['waktu_mulai'] . '|' . $r['id_ruang'];
                        $j    = $jadwalMap[$key] ?? null;
                    ?>
                    <td>
    <?php if ($j): ?>

        <?php
            $isMine = ((int)$j['id_dosen'] === (int)$id_dosen_login);
            $cardClass = $isMine ? 'mine' : 'other';
        ?>

        <div class="jadwal-card <?= $cardClass ?>">
            <div class="matkul"><?= e($j['nama_matkul'] ?: '-') ?></div>

            <div class="kelas">
                    <?= e($j['kelas'] ?? '-') ?>
            </div>

            <div class="dosen">
                <i class="fa-solid fa-chalkboard-user" style="font-size:10px"></i>
                <?= e($j['nama_dosen'] ?: '-') ?>
            </div>

            <div class="jam">
                <i class="fa-regular fa-clock" style="font-size:10px"></i>
                <?= substr($j['waktu_mulai'],0,5) ?> – <?= substr($j['waktu_selesai'],0,5) ?>
            </div>

            <?php if ($isMine): ?>
                <div class="aksi">
                    <a href="edit-jadwal.php?id=<?= e($j['id_jadwal']) ?>" class="btn-edit-sm">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>

                    <a href="hapus-jadwal.php?id=<?= e($j['id_jadwal']) ?>" class="btn-del-sm"
                       onclick="return confirm('Hapus jadwal ini?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <a class="empty-link"
           href="tambah-Jadwal.php?id_ruang=<?= urlencode($r['id_ruang']) ?>&gedung=<?= urlencode($r['gedung']) ?>&hari=<?= urlencode($hari) ?>&waktu_mulai=<?= urlencode($slot['waktu_mulai']) ?>&waktu_selesai=<?= urlencode($slot['waktu_selesai']) ?>">
            <i class="fa-solid fa-plus"></i>
            Kosong
        </a>

    <?php endif; ?>
</td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
const searchInput = document.getElementById("searchInput");
const rows = document.querySelectorAll("#rosterTable tbody tr");

searchInput.addEventListener("keyup", function(){
    const kw = this.value.toLowerCase();
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(kw) ? "" : "none";
    });
});
</script>
</body>
</html>
