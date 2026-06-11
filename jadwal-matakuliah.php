<?php
session_start();
include "connect.php";

/* CEK LOGIN */
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

/* CEK KONEKSI */
if (!$conn) {
    die("Koneksi database gagal");
}

/* AMBIL DATA JADWAL DENGAN JOIN */
$query = "
SELECT 
    jadwal_kuliah.id_jadwal,
    jadwal_kuliah.hari,
    jadwal_kuliah.waktu_mulai,
    jadwal_kuliah.waktu_selesai,
    jadwal_kuliah.gedung,

    mata_kuliah.nama_matkul,
    dosen.nama_dosen,
    ruangan.nama_ruang

FROM jadwal_kuliah

LEFT JOIN mata_kuliah 
ON jadwal_kuliah.id_matkul = mata_kuliah.id_matkul

LEFT JOIN dosen 
ON jadwal_kuliah.id_dosen = dosen.id_dosen

LEFT JOIN ruangan 
ON jadwal_kuliah.id_ruang = ruangan.id_ruang

ORDER BY 
FIELD(jadwal_kuliah.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
jadwal_kuliah.waktu_mulai ASC
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

<title>Jadwal Mata Kuliah</title>

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
    color:#0f172a;
}

.container{
    width:100%;
    max-width:1300px;
    margin:auto;
    background:white;
    border-radius:28px;
    padding:32px;
    box-shadow:0 15px 35px rgba(37,99,235,0.13);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:28px;
}

.title h1{
    font-size:32px;
    font-weight:800;
    color:#0f172a;
}

.title p{
    color:#64748b;
    margin-top:5px;
}

.btn{
    border:none;
    outline:none;
    text-decoration:none;
    padding:12px 18px;
    border-radius:14px;
    font-weight:600;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:0.25s;
    font-size:14px;
    background:#dbeafe;
    color:#1d4ed8;
}

.btn:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(37,99,235,0.20);
}

.hero{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    padding:30px;
    border-radius:24px;
    margin-bottom:24px;
    position:relative;
    overflow:hidden;
}

.hero::after{
    content:"";
    position:absolute;
    width:180px;
    height:180px;
    border-radius:50%;
    background:rgba(255,255,255,0.13);
    right:-50px;
    top:-55px;
}

.hero h2{
    font-size:26px;
    margin-bottom:8px;
    position:relative;
    z-index:1;
}

.hero p{
    opacity:0.95;
    position:relative;
    z-index:1;
}

.search-box{
    margin-bottom:22px;
    display:flex;
    align-items:center;
    gap:10px;
    background:#f8fbff;
    border:1px solid #dbeafe;
    padding:14px 18px;
    border-radius:16px;
}

.search-box i{
    color:#64748b;
}

.search-box input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    font-size:14px;
}

.table-box{
    overflow-x:auto;
    border-radius:22px;
    border:1px solid #dbeafe;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:1000px;
}

th{
    background:linear-gradient(135deg, #2563eb, #38bdf8);
    color:white;
    text-align:left;
    padding:17px;
    font-size:14px;
    white-space:nowrap;
}

td{
    padding:16px;
    border-bottom:1px solid #e5efff;
    color:#334155;
    font-size:14px;
    white-space:nowrap;
}

tr:hover td{
    background:#f8fbff;
}

.badge{
    display:inline-block;
    padding:7px 13px;
    border-radius:999px;
    background:#dbeafe;
    color:#1d4ed8;
    font-size:13px;
    font-weight:700;
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
        padding:22px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }

    .title h1{
        font-size:26px;
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
                Jadwal Mata Kuliah
            </h1>
            <p>Lihat jadwal perkuliahan berdasarkan mata kuliah, dosen, ruangan, hari, dan waktu.</p>
        </div>

        <a href="dashboard-user.php" class="btn">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>

 
   

    <div class="table-box">
        <table id="jadwalTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen</th>
                    <th>Ruangan</th>
                    <th>Gedung</th>
                    <th>Hari</th>
                    <th>Jam Kuliah</th>
                </tr>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($data) > 0) { ?>
                    <?php $no = 1; ?>
                    <?php while ($d = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?php echo $no++; ?></td>

                            <td>
                                <span class="badge">
                                    <?php echo $d['nama_matkul'] ? $d['nama_matkul'] : 'Mata kuliah tidak ditemukan'; ?>
                                </span>
                            </td>

                            <td>
                                <?php echo $d['nama_dosen'] ? $d['nama_dosen'] : 'Dosen tidak ditemukan'; ?>
                            </td>

                            <td>
                                <?php echo $d['nama_ruang'] ? $d['nama_ruang'] : 'Ruangan tidak ditemukan'; ?>
                            </td>

                            <td>
                                <?php echo $d['gedung']; ?>
                            </td>

                            <td>
                                <span class="badge">
                                    <?php echo $d['hari']; ?>
                                </span>
                            </td>

                            <td>
                                <?php echo substr($d['waktu_mulai'], 0, 5); ?>
                                -
                                <?php echo substr($d['waktu_selesai'], 0, 5); ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="empty">
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
const table = document.getElementById("jadwalTable");
const rows = table.getElementsByTagName("tr");

searchInput.addEventListener("keyup", function(){
    const keyword = searchInput.value.toLowerCase();

    for(let i = 1; i < rows.length; i++){
        const text = rows[i].innerText.toLowerCase();

        if(text.includes(keyword)){
            rows[i].style.display = "";
        }else{
            rows[i].style.display = "none";
        }
    }
});
</script>

</body>
</html>
