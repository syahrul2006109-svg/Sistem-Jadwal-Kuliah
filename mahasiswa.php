<?php
session_start();
include 'connect.php';


if(!isset($_SESSION['nama'])){
    header("Location: login.php");
    exit;
}


$data = mysqli_query($conn,
"SELECT * FROM mahasiswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Mahasiswa</title>


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

    min-height:100vh;

    background:linear-gradient(
        135deg,
        #0f172a,
        #2563eb,
        #38bdf8
    );

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

    margin-bottom:30px;
}

.title h1{
    font-family:'Outfit', sans-serif;

    font-size:35px;

    color:#0f172a;

    margin-bottom:5px;
}

.title p{
    color:#64748b;
}


.btn{
    background:linear-gradient(
        135deg,
        #2563eb,
        #38bdf8
    );

    color:white;

    padding:12px 20px;

    border-radius:12px;

    text-decoration:none;

    font-weight:600;

    transition:0.3s;

    display:inline-flex;
    align-items:center;
    gap:10px;
}

.btn:hover{
    transform:translateY(-3px);

    box-shadow:0 10px 20px rgba(37,99,235,0.25);
}


.table-box{
    overflow-x:auto;
}

table{
    width:100%;

    border-collapse:collapse;

    overflow:hidden;

    border-radius:20px;
}


table th{
    background:linear-gradient(
        135deg,
        #2563eb,
        #38bdf8
    );

    color:white;

    padding:18px;

    text-align:left;

    font-size:15px;
}


table td{
    padding:16px;

    border-bottom:1px solid #e2e8f0;

    color:#334155;

    background:white;
}

table tr:hover td{
    background:#eff6ff;

    transition:0.3s;
}


.admin{
    background:#dbeafe;
    color:#1d4ed8;
}

.user{
    background:#dcfce7;
    color:#166534;
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
        font-size:28px;
    }

    table th,
    table td{
        padding:12px;
        font-size:14px;
    }
}

</style>

</head>
<body>

<div class="container">


    <div class="header">

        <div class="title">

            <h1>
                <i class="fa-solid fa-users"></i>
                Data Mahasiswa
            </h1>

            <p>
                Sistem Informasi Jadwal Kuliah
            </p>

        </div>

        <a href="dashboard-admin.php" class="btn">

            <i class="fa-solid fa-arrow-left"></i>

            Kembali

        </a>

    </div>


    <div class="table-box">

        <table>

            <tr>

                <th>
                    <i class="fa-solid fa-id-card"></i>
                    NIM
                </th>

                <th>
                    <i class="fa-solid fa-user"></i>
                    Nama
                </th>

                <th>
                    <i class="fa-solid fa-envelope"></i>
                    Email
                </th>

                

            </tr>

            <?php while($d = mysqli_fetch_assoc($data)){ ?>

            <tr>

                <td>
                    <?php echo $d['nim']; ?>
                </td>

                <td>
                    <?php echo $d['nama']; ?>
                </td>

                <td>
                    <?php echo $d['email']; ?>
                </td>

                <td>

                   

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>
