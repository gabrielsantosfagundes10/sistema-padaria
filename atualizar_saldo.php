<?php
include('config.php');
if(isset($_POST['valor'])) {
    $valor = $_POST['valor'];
    // Atualiza o saldo do próximo dia no registro mais recente
    $conn->query("UPDATE fechamentos SET saldo_prox_dia = '$valor' ORDER BY id DESC LIMIT 1");
}
?>