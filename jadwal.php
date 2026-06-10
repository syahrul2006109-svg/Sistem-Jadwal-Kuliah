<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

$id_dosen_login = $_SESSION['id_dosen'] ?? 0;

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

$query = "
SELECT 
    jk.id_jadwal,
    jk.id_matkul,
    jk.id_dosen,
    jk.id_ruang,
    jk.gedung,
    jk.hari,
    jk.waktu_mulai,
    jk.waktu_selesai,

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
FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
jk.waktu_mulai ASC
";

$data = mysqli_query($conn, $query);

if (!$data) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Jadwal Kuliah</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
    padding:35px;
    color:#071633;
}

.container{
    width:100%;
    max-width:1400px;
    margin:auto;
    background:white;
    border-radius:28px;
    padding:34px;
    box-shadow:0 15px 35px rgba(37,99,235,0.13);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:30px;
}

.title h1{
    font-size:32px;
    font-weight:800;
    color:#071633;
}

.title p{
    color:#64748b;
    margin-top:8px;
    font-size:15px;
}

.header-actions{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.btn{
    border:none;
    text-decoration:none;
    padding:13px 20px;
    border-radius:15px;
    font-weight:700;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:9px;
    transition:0.25s;
    font-size:14px;
}

.btn:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(37,99,235,0.18);
}

.btn-back{
    background:#dbeafe;
    color:#1d4ed8;
}

.btn-add{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
}

.search-box{
    margin-bottom:22px;
    display:flex;
    align-items:center;
    gap:12px;
    background:#f8fbff;
    border:1px solid #dbeafe;
    padding:16px 18px;
    border-radius:17px;
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

.table-box{
    overflow-x:auto;
    border-radius:22px;
    border:1px solid #dbeafe;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:1150px;
}

th{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    text-align:left;
    padding:18px;
    font-size:14px;
    white-space:nowrap;
}

td{
    padding:17px 18px;
    border-bottom:1px solid #e5efff;
    color:#1e293b;
    font-size:14px;
    white-space:nowrap;
}

tr:hover td{
    background:#f8fbff;
}

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:7px 14px;
    border-radius:999px;
    background:#dbeafe;
    color:#1d4ed8;
    font-size:13px;
    font-weight:800;
}

.badge-gray{
    background:#f1f5f9;
    color:#64748b;
}

.action{
    display:flex;
    gap:10px;
}

.btn-edit{
    background:#2563eb;
    color:white;
}

.btn-delete{
    background:#ef4444;
    color:white;
}

.btn-disabled{
    background:#f1f5f9;
    color:#64748b;
    cursor:not-allowed;
}

.empty{
    text-align:center;
    padding:35px;
    color:#64748b;
}

@media(max-width:768px){
    body{
        padding:18px;
    }

    .container{
        padding:24px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }

    .title h1{
        font-size:25px;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <div class="title">
            <h1>
                <i class="fa-solid fa-calendar-days"></i>
                Data Jadwal Kuliah
            </h1>
            <p>Kelola jadwal kuliah, mata kuliah, ruangan, dan waktu perkuliahan.</p>
        </div>

        <div class="header-actions">
            <a href="dashboard-admin.php" class="btn btn-back">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>

            <a href="tambah-Jadwal.php" class="btn btn-add">
                <i class="fa-solid fa-plus"></i>
                Tambah Jadwal
            </a>
        </div>
    </div>

   

    <div class="table-box">
        <table id="jadwalTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Jadwal</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen</th>
                    <th>Ruangan</th>
                    <th>Gedung</th>
                    <th>Hari</th>
                    <th>Jam Kuliah</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($data) > 0) { ?>
                    <?php $no = 1; ?>
                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>

                        <?php
                        $punya_saya = ((int)$d['id_dosen'] === (int)$id_dosen_login);
                        ?>

                        <tr>
                            <td><?php echo $no++; ?></td>

                            <td>
                                <span class="badge">
                                    <?php echo e($d['id_jadwal']); ?>
                                </span>
                            </td>

                            <td><?php echo e($d['nama_matkul'] ?: 'Mata kuliah tidak ditemukan'); ?></td>

                            <td><?php echo e($d['nama_dosen'] ?: 'Dosen tidak ditemukan'); ?></td>

                            <td><?php echo e($d['nama_ruang'] ?: 'Ruangan tidak ditemukan'); ?></td>

                            <td><?php echo e($d['gedung']); ?></td>

                            <td>
                                <span class="badge">
                                    <?php echo e($d['hari']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo e(substr($d['waktu_mulai'], 0, 5)); ?>
                                -
                                <?php echo e(substr($d['waktu_selesai'], 0, 5)); ?>
                            </td>

                            <td>
                                <?php if ($punya_saya) { ?>

                                    <div class="action">
                                        <a href="edit-jadwal.php?id=<?php echo e($d['id_jadwal']); ?>" class="btn btn-edit">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>

                                        <a href="hapus-jadwal.php?id=<?php echo e($d['id_jadwal']); ?>"
                                           class="btn btn-delete"
                                           onclick="return confirm('Yakin ingin menghapus jadwal ini?')">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </a>
                                    </div>

                                <?php } else { ?>

                                    <span class="badge badge-gray">
                                        Hanya lihat
                                    </span>

                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9" class="empty">
                            <i class="fa-solid fa-circle-info"></i>
                            Belum ada data jadwal kuliah.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<script>
const searchInput = document.getElementById("searchInput");
const rows = document.querySelectorAll("#jadwalTable tbody tr");

searchInput.addEventListener("keyup", function(){
    const keyword = searchInput.value.toLowerCase();

    rows.forEach(function(row){
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(keyword) ? "" : "none";
    });
});
</script>

</body>
</html>