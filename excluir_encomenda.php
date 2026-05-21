<?php
include('trava.php');
include('config.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM encomendas WHERE id = $id");
}
header("Location: encomendas.php");
?>