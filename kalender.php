<?php
include 'connect.php';

$data = mysqli_query($conn,
"SELECT * FROM jadwal_kuliah
ORDER BY hari ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Kalender Jadwal Kuliah</title>

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

    background:linear-gradient(
        135deg,
        #0f172a,
        #2563eb,
        #38bdf8
    );

    min-height:100vh;

    padding:40px;
}



.container{
    width:100%;
    max-width:1200px;

    margin:auto;

    background:white;

    border-radius:25px;

    padding:35px;

    box-shadow:0 10px 30px rgba(0,0,0,0.15);
}



.header{
    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:35px;
}

.title h1{
    font-family:'Outfit', sans-serif;
    font-size:36px;
    color:#0f172a;
}

.title p{
    color:#64748b;
    margin-top:5px;
}



.btn{
    background:linear-gradient(
        135deg,
        #2563eb,
        #38bdf8
    );

    color:white;

    padding:12px 22px;

    border-radius:12px;

    text-decoration:none;

    font-weight:600;

    transition:0.3s;
}

.btn:hover{
    transform:translateY(-3px);

    box-shadow:0 10px 20px rgba(37,99,235,0.25);
}


.calendar{
    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(280px,1fr));

    gap:25px;
}



.card{
    background:#ffffff;

    border-radius:22px;

    padding:25px;

    position:relative;

    overflow:hidden;

    box-shadow:0 5px 15px rgba(0,0,0,0.08);

    transition:0.3s;

    border:1px solid #e2e8f0;
}

.card:hover{
    transform:translateY(-8px);

    box-shadow:0 12px 25px rgba(37,99,235,0.15);
}

.card::before{
    content:'';

    position:absolute;

    width:120px;
    height:120px;

    background:rgba(59,130,246,0.08);

    border-radius:50%;

    top:-40px;
    right:-40px;
}



.day{
    display:flex;
    align-items:center;
    gap:12px;

    margin-bottom:20px;
}

.day-icon{
    width:55px;
    height:55px;

    border-radius:16px;

    background:#dbeafe;

    display:flex;
    justify-content:center;
    align-items:center;
}

.day-icon i{
    color:#2563eb;
    font-size:24px;
}

.day h2{
    color:#0f172a;
    font-size:24px;

    font-family:'Outfit', sans-serif;
}



.info{
    margin-bottom:14px;

    display:flex;
    align-items:center;

    gap:10px;

    color:#475569;
}

.info i{
    color:#2563eb;
    width:20px;
}



.badge{
    margin-top:18px;

    display:inline-block;

    padding:7px 14px;

    border-radius:20px;

    background:#dbeafe;

    color:#1d4ed8;

    font-size:13px;
    font-weight:600;
}



@media(max-width:768px){

    body{
        padding:20px;
    }

    .container{
        padding:25px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

    .title h1{
        font-size:30px;
    }
}

</style>

</head>
<body>

<div class="container">


    <div class="header">

        <div class="title">

            <h1>
                Kalender Jadwal Kuliah
            </h1>

            <p>
                Jadwal perkuliahan mahasiswa berdasarkan hari
            </p>

        </div>

        <a href="dashboard-admin.php" class="btn">

            <i class="fa-solid fa-arrow-left"></i>
            Kembali

        </a>

    </div>


    <div class="calendar">

        <?php while($d = mysqli_fetch_assoc($data)){ ?>

        <div class="card">

            <div class="day">

                <div class="day-icon">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>

                <h2>
                    <?php echo $d['hari']; ?>
                </h2>

            </div>

            <div class="info">

                <i class="fa-solid fa-clock"></i>

                <span>

                    <?php echo $d['waktu_mulai']; ?>

                    -

                    <?php echo $d['waktu_selesai']; ?>

                </span>

            </div>

            <div class="info">

                <i class="fa-solid fa-building"></i>

                <span>
                    <?php echo $d['gedung']; ?>
                </span>

            </div>

            <span class="badge">

                Jadwal Aktif

            </span>

        </div>

        <?php } ?>

    </div>

</div>

</body>
</html>
