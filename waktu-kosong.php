<?php
include 'connect.php';

$data = mysqli_query($conn,
"SELECT * FROM jadwal_kuliah ORDER BY waktu_mulai ASC");

$sebelumnya = null;

while($d = mysqli_fetch_assoc($data)){

    if($sebelumnya != null){

        echo "Waktu kosong dari ";
        echo $sebelumnya;
        echo " sampai ";
        echo $d['waktu_mulai'];
        echo "<br><br>";
    }

    $sebelumnya = $d['waktu_selesai'];
}
?>