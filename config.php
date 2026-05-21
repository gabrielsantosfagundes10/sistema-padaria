<?php
date_default_timezone_set('America/Sao_Paulo');

// --- DADOS DO BANCO DE DEMO ---
$host = "localhost";
$user = "usuario"; // Credenciais alteradas por confidencialidade
$pass = "senha"; // Credenciais alteradas por confidencialidade
$db   = "banco"; // Credenciais alteradas por confidencialidade

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$conn->query("SET time_zone = '-03:00'");

// Função Telegram desativada na demo
function enviarTelegram($mensagem) {
    // desativado na versão demo
}
?>
