<?php
$servername = "localhost";
$username = "root";
$pass = "";
$dbname = "sistem_jadwal_kuliah"; //nama data base
$conn = new mmysqli($servername, $username, $pass, $dbname);
if ($conn->connect_error) {
  echo "Koneksi Gagal ".$conn->connect_error;
}
?>
