<?php
include('trava.php');
include('config.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    // Deleta o cliente (as movimentações seriam deletadas se houver chave estrangeira, ou permanecem no banco)
    $conn->query("DELETE FROM clientes WHERE id = $id");
}
header("Location: contas_clientes.php");
?>