<?php
// Caminho completo para o seu config.php (ajuste se necessário)
include(__DIR__ . '/config.php');

date_default_timezone_set('America/Sao_Paulo');

$hoje = date('Y-m-d');
$amanha = date('Y-m-d', strtotime('+1 day'));

// --- 1. NOTIFICAÇÃO DE ENCOMENDAS DE HOJE ---
$res_hoje = $conn->query("SELECT nome_cliente, tipo, horario FROM encomendas WHERE data_entrega = '$hoje' ORDER BY horario ASC");

if ($res_hoje->num_rows > 0) {
    $msg_hoje = "🚨 <b>ENCOMENDAS DE HOJE (" . date('d/m', strtotime($hoje)) . ")</b>\n\n";
    while ($e = $res_hoje->fetch_assoc()) {
        $hora = !empty($e['horario']) ? " às " . substr($e['horario'], 0, 5) : "";
        $msg_hoje .= "• " . $e['nome_cliente'] . ": " . $e['tipo'] . $hora . "\n";
    }
    enviarTelegram($msg_hoje);
}

// --- 2. NOTIFICAÇÃO DE ENCOMENDAS DE AMANHÃ ---
$res_amanha = $conn->query("SELECT nome_cliente, tipo, horario FROM encomendas WHERE data_entrega = '$amanha' ORDER BY horario ASC");

if ($res_amanha->num_rows > 0) {
    $msg_amanha = "⏳ <b>LEMBRETE: ENCOMENDAS DE AMANHÃ (" . date('d/m', strtotime($amanha)) . ")</b>\n\n";
    while ($e = $res_amanha->fetch_assoc()) {
        $hora = !empty($e['horario']) ? " às " . substr($e['horario'], 0, 5) : "";
        $msg_amanha .= "• " . $e['nome_cliente'] . ": " . $e['tipo'] . $hora . "\n";
    }
    enviarTelegram($msg_amanha);
}

// --- 3. NOTIFICAÇÃO DE CONTAS VENCENDO HOJE ---
$res_contas = $conn->query("SELECT descricao, valor FROM contas_padaria WHERE data_vencimento = '$hoje' AND status_pago = 0");

if ($res_contas->num_rows > 0) {
    $msg_contas = "💰 <b>CONTAS QUE VENCEM HOJE</b>\n\n";
    while ($c = $res_contas->fetch_assoc()) {
        $valor = number_format($c['valor'], 2, ',', '.');
        $msg_contas .= "❌ " . $c['descricao'] . " (R$ $valor)\n";
    }
    enviarTelegram($msg_contas);
}

echo "Notificações enviadas com sucesso em: " . date('d/m/Y H:i:s');
?>