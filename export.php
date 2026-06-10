<?php
include 'connect.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=jadwal.xls");

$data = mysqli_query($conn,
"SELECT * FROM jadwal_kuliah");
?>

<table border="1">

<tr>
    <th>ID</th>
    <th>Hari</th>
    <th>Jam</th>
</tr>

<?php while($d = mysqli_fetch_assoc($data)){ ?>

<tr>

<td><?php echo $d['id_jadwal']; ?></td>

<td><?php echo $d['hari']; ?></td>

<td>
<?php echo $d['waktu_mulai']; ?> -
<?php echo $d['waktu_selesai']; ?>
</td>

</tr>

<?php } ?>

</table>