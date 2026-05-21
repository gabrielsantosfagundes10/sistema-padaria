<?php
include('config.php');
header('Content-Type: application/json');

$hoje = date('Y-m-d');

// Contagens para o Sininho e para o Som
$avisos = $conn->query("SELECT COUNT(*) as t FROM avisos WHERE lido = 0")->fetch_assoc()['t'];
$enc_hoje = $conn->query("SELECT COUNT(*) as t FROM encomendas WHERE data_entrega = '$hoje'")->fetch_assoc()['t'];
$con_hoje = $conn->query("SELECT COUNT(*) as t FROM contas_padaria WHERE data_vencimento = '$hoje' AND status_pago = 0")->fetch_assoc()['t'];
$con_atrasadas = $conn->query("SELECT COUNT(*) as t FROM contas_padaria WHERE data_vencimento < '$hoje' AND status_pago = 0")->fetch_assoc()['t'];

echo json_encode([
    'avisos' => (int)$avisos,
    'encomendas_hoje' => (int)$enc_hoje,
    'contas_hoje' => (int)$con_hoje,
    'contas_atrasadas' => (int)$con_atrasadas
]);