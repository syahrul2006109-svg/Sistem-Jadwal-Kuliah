<?php
session_start();
include "connect.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['id_dosen'])) {
    echo "
    <script>
        alert('Akses ditolak. Anda bukan dosen.');
        window.location='jadwal.php';
    </script>";
    exit;
}

$id_jadwal = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_dosen_login = (int)$_SESSION['id_dosen'];

if ($id_jadwal <= 0) {
    echo "
    <script>
        alert('ID jadwal tidak valid.');
        window.location='jadwal.php';
    </script>";
    exit;
}


$stmt = mysqli_prepare($conn, "
    DELETE FROM jadwal_kuliah 
    WHERE id_jadwal = ? 
    AND id_dosen = ?
");

mysqli_stmt_bind_param($stmt, "ii", $id_jadwal, $id_dosen_login);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo "
    <script>
        alert('Jadwal berhasil dihapus.');
        window.location='jadwal.php';
    </script>";
} else {
    echo "
    <script>
        alert('Akses ditolak. Anda hanya bisa menghapus jadwal yang Anda tambahkan sendiri.');
        window.location='jadwal.php';
    </script>";
}
?>
