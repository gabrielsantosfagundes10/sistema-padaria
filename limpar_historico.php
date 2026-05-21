<?php
include('trava.php');
include('config.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    // CORREÇÃO: Nome da tabela conforme sua imagem
    $conn->query("DELETE FROM contas_clientes_movimentos WHERE id_cliente = $id");
}
header("Location: cliente_detalhes.php?id=$id");
?>