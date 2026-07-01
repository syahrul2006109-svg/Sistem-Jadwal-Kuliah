<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

$id_dosen_login       = $_SESSION['id_dosen'] ?? 0;
$prefill_id_ruang     = $_GET['id_ruang'] ?? '';
$prefill_gedung       = $_GET['gedung'] ?? '';
$prefill_hari         = $_GET['hari'] ?? '';
$prefill_waktu_mulai  = $_GET['waktu_mulai'] ?? '';
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

// Hari yang mau ditampilkan di roster
$hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis'];

// Slot waktu kampus
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
            'hari'          => $hariItem,
            'sesi'          => $jam['sesi'],
            'waktu_mulai'   => $jam['waktu_mulai'],
            'waktu_selesai' => $jam['waktu_selesai'],
        ];
    }
}

$qJadwal = "
    SELECT 
        jk.id_jadwal, jk.id_matkul, jk.id_dosen, jk.id_ruang,
        jk.gedung, jk.hari, jk.waktu_mulai, jk.waktu_selesai,

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

// Estetik Palette: Warna solid untuk aksen, background transparan/terang untuk isi
$paletteHeader = [
    '#3b82f6', '#0ea5e9', '#10b981', '#059669', 
    '#f59e0b', '#d97706', '#8b5cf6', '#6d28d9', 
    '#ef4444', '#ec4899', '#14b8a6', '#f43f5e'
];
$paletteCell = [
    '#eff6ff', '#e0f2fe', '#d1fae5', '#a7f3d0', 
    '#fef3c7', '#ffedd5', '#f5f3ff', '#ede9fe', 
    '#fef2f2', '#fdf2f8', '#ccfbf1', '#ffe4e6'
];
$paletteCellText = [
    '#1e40af', '#0369a1', '#065f46', '#047857', 
    '#92400e', '#9a3412', '#4c1d95', '#5b21b6', 
    '#991b1b', '#9d174d', '#134e4a', '#9f1239'
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
        :root {
            --bg-body: #f4f7fe;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --accent-blue: #3b82f6;
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            background: var(--bg-body);
            padding: 24px;
            color: var(--text-main);
            overflow: hidden; 
        }

        /* --- Kustomisasi Scrollbar --- */
        ::-webkit-scrollbar { width: 8px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { 
            background: #cbd5e1; 
            border-radius: 20px; 
            border: 2px solid #ffffff; 
        }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        ::-webkit-scrollbar-corner { background: transparent; }

        .container {
            width: 100%;
            height: 100%;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: 0 10px 40px rgba(112, 144, 176, 0.12);
            display: flex;
            flex-direction: column;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .title h1 { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .title p { color: var(--text-muted); margin-top: 2px; font-size: 13px; font-weight: 400; }

        .btn {
            border: none;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            font-size: 13px;
        }
        
        .btn-back { 
            background: #f1f5f9; 
            color: #475569; 
        }
        .btn-back:hover { 
            background: #e2e8f0; 
            color: #0f172a; 
            transform: translateY(-2px); 
        }

        .controls-wrapper {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            padding: 12px 18px;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
        }
        
        .search-box:focus-within {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: #ffffff;
        }

        .search-box i { color: #94a3b8; font-size: 14px; }
        .search-box input {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-size: 13px;
            color: #1e293b;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding-bottom: 5px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }
        
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        /* --- Table Styling --- */
        .table-wrap {
            flex: 1; 
            overflow: auto; 
            border-radius: var(--radius-md);
            border: 1px solid var(--border-light);
            background: #ffffff;
            position: relative;
        }

        table {
            border-collapse: separate; 
            border-spacing: 0;
            width: max-content; 
            min-width: 100%;
        }

        thead tr th {
            position: sticky;
            top: 0;
            z-index: 10;
            text-align: center;
            padding: 16px 12px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            color: white;
            border-bottom: 2px solid rgba(0,0,0,0.05);
            border-right: 1px solid rgba(255,255,255,0.15);
        }

        /* Sticky Kolom Kiri */
        th.col-hari, td.col-hari {
            position: sticky;
            left: 0;
            z-index: 11;
            width: 90px;
            min-width: 90px;
            max-width: 90px;
            background: #f8fafc;
            border-right: 1px solid var(--border-light);
            text-align: center;
        }
        
        th.col-jam, td.col-jam {
            position: sticky;
            left: 90px;
            z-index: 11;
            width: 120px;
            min-width: 120px;
            max-width: 120px;
            background: #f8fafc;
            border-right: 1px solid var(--border-light);
            text-align: center;
        }

        thead th.col-hari, thead th.col-jam { 
            z-index: 15; 
            background: #0f172a !important; 
            color: #ffffff; 
            border-right-color: rgba(255,255,255,0.1);
        }

        td.col-jam {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        td.col-jam strong {
            color: #0f172a;
            font-weight: 700;
            font-size: 12px;
            display: block;
            margin-bottom: 2px;
        }

        /* Fixed Lebar Ruangan */
        th:not(.col-hari):not(.col-jam), 
        td:not(.col-hari):not(.col-jam) {
            width: 190px;
            min-width: 190px;
            max-width: 190px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            vertical-align: top;
            transition: background 0.2s;
        }

        /* Garis Pemisah Hari yang Estetik */
        tr.garis-pembatas-hari td {
            border-top: 3px solid #e2e8f0 !important;
        }

        .hari-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 0;
            width: 100%;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        /* --- Desain Jadwal Card   --- */
        .jadwal-card {
            border-radius: 12px;
            padding: 12px;
            font-size: 12px;
            line-height: 1.5;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .jadwal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            z-index: 2;
        }

        .jadwal-card.mine { 
            border-left: 4px solid var(--accent-blue); 
            background: #f8fbff;
        }
        
        .jadwal-card.other { 
            border-left: 4px solid #94a3b8; 
            background: #ffffff;
        }

        .jadwal-card .matkul { 
            font-weight: 700; 
            margin-bottom: 6px; 
            font-size: 13px; 
            color: #0f172a; 
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .jadwal-card .kelas {
            display: inline-block;
            margin-bottom: 6px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .jadwal-card.mine .kelas { background: #dbeafe; color: #1e40af; }
        .jadwal-card.other .kelas { background: #f1f5f9; color: #475569; }

        .jadwal-card .dosen, .jadwal-card .jam { 
            font-size: 11px; 
            color: #64748b;
            margin-bottom: 4px; 
            display: flex; 
            align-items: center; 
            gap: 6px; 
        }

        .jadwal-card .aksi { 
            margin-top: 10px; 
            padding-top: 10px;
            border-top: 1px dashed #e2e8f0;
            display: flex; 
            gap: 6px; 
        }
        
        .jadwal-card .aksi a {
            flex: 1;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            padding: 6px 8px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.2s;
        }
        
        .btn-edit-sm { background: #eff6ff; color: #3b82f6; }
        .btn-edit-sm:hover { background: #3b82f6; color: white; }
        .btn-del-sm { background: #fef2f2; color: #ef4444; }
        .btn-del-sm:hover { background: #ef4444; color: white; }

        /* --- State Kosong Minimalis --- */
        .empty-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            min-height: 85px;
            padding: 10px;
            border-radius: 12px;
            text-decoration: none;
            color: #94a3b8;
            background: transparent;
            font-size: 11px;
            font-weight: 600;
            border: 1.5px dashed #cbd5e1;
            transition: all 0.25s ease;
        }

        .empty-link:hover {
            color: var(--accent-blue);
            background: #f8fbff;
            border-color: #93c5fd;
            transform: scale(0.98);
        }

        .empty-link i { margin-bottom: 6px; font-size: 16px; opacity: 0.7; }

        /* Hover efek pada baris tabel */
        tr:hover td { background: #f8fafc; }
        tr:hover td.col-hari, tr:hover td.col-jam { background: #f1f5f9; }

        @media(max-width:768px) {
            body { padding: 12px; overflow: auto; height: auto; }
            .container { padding: 16px; height: auto; border-radius: 20px; }
            .title h1 { font-size: 20px; }
            .controls-wrapper { flex-direction: column; align-items: stretch; gap: 12px; }
            .table-wrap { max-height: 500px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <div class="title">
            <h1>Jadwal Kuliah</h1>
            <p>Roster jadwal perkuliahan – Semester Genap 2025/2026</p>
        </div>
        <div class="header-actions">
            <a href="dashboard-admin.php" class="btn btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="controls-wrapper">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari jadwal, dosen, atau mata kuliah...">
        </div>
        <div class="legend">
            <?php foreach ($ruanganList as $r): 
                $idx = $ruangIndex[$r['id_ruang']] ?? 0;
            ?>
            <span class="legend-item" style="background:<?= $paletteCell[$idx % count($paletteCell)] ?>; color:<?= $paletteCellText[$idx % count($paletteCellText)] ?>">
                <span class="legend-dot" style="background:<?= $paletteHeader[$idx % count($paletteHeader)] ?>"></span>
                <?= e($r['nama_ruang']) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-wrap" id="rosterWrap">
        <table id="rosterTable">
            <thead>
                <tr>
                    <th class="col-hari">Hari</th>
                    <th class="col-jam">Sesi & Jam</th>
                    <?php foreach ($ruanganList as $r): 
                        $idx = $ruangIndex[$r['id_ruang']] ?? 0;
                        $bgH = $paletteHeader[$idx % count($paletteHeader)];
                    ?>
                    <th style="background:<?= $bgH ?>">
                        <?= e($r['nama_ruang']) ?><br>
                        <span style="font-size:11px; font-weight:400; opacity:.85; letter-spacing: 0.5px;">
                            <?= e($r['gedung']) ?>
                        </span>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $prevHari = null;
                foreach ($slots as $slot):
                    $hari = $slot['hari'];
                    $mulai = substr($slot['waktu_mulai'], 0, 5);
                    $selesai = substr($slot['waktu_selesai'], 0, 5);
                    
                    // Deteksi hari baru untuk garis batas estetis
                    $isNewDay = ($hari !== $prevHari);
                    $rowClass = $isNewDay ? 'garis-pembatas-hari' : '';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td class="col-hari">
                        <?php if ($isNewDay): $prevHari = $hari; ?>
                            <div class="hari-badge"><?= e($hari) ?></div>
                        <?php else: ?>
                            <span style="color:#cbd5e1; font-size:14px;">⋮</span>
                        <?php endif; ?>
                    </td>

                    <td class="col-jam">
                        <strong>Sesi <?= e($slot['sesi']) ?></strong>
                        <i class="fa-regular fa-clock" style="font-size:10px; margin-right:3px;"></i><?= $mulai ?> - <?= $selesai ?>
                    </td>

                    <?php foreach ($ruanganList as $r): 
                        $key  = $hari . '|' . $slot['waktu_mulai'] . '|' . $r['id_ruang'];
                        $j    = $jadwalMap[$key] ?? null;
                    ?>
                    <td>
                        <?php if ($j): 
                            $isMine = ((int)$j['id_dosen'] === (int)$id_dosen_login);
                            $cardClass = $isMine ? 'mine' : 'other';
                        ?>
                            <div class="jadwal-card <?= $cardClass ?>">
                                <div class="matkul"><?= e($j['nama_matkul'] ?: '-') ?></div>
                                <div class="dosen">
                                    <i class="fa-solid fa-user-tie"></i>
                                    <?= e($j['nama_dosen'] ?: '-') ?>
                                </div>

                                <?php if ($isMine): ?>
                                    <div class="aksi">
                                        <a href="edit-jadwal.php?id=<?= e($j['id_jadwal']) ?>" class="btn-edit-sm">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <a href="hapus-jadwal.php?id=<?= e($j['id_jadwal']) ?>" class="btn-del-sm" onclick="return confirm('Yakin ingin menghapus jadwal ini?')">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <a class="empty-link" href="tambah-Jadwal.php?id_ruang=<?= urlencode($r['id_ruang']) ?>&gedung=<?= urlencode($r['gedung']) ?>&hari=<?= urlencode($hari) ?>&waktu_mulai=<?= urlencode($slot['waktu_mulai']) ?>&waktu_selesai=<?= urlencode($slot['waktu_selesai']) ?>">
                                <i class="fa-solid fa-plus-circle"></i>
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

searchInput.addEventListener("keyup", function() {
    const kw = this.value.toLowerCase();
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(kw) ? "" : "none";
    });
});
</script>

</body>
</html>
