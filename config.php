<?php
date_default_timezone_set('America/Sao_Paulo');

// --- DADOS DO BANCO DE DEMO ---
$host = "localhost";
$user = "u360443047_padariademo";
$pass = "010906Gbl";
$db   = "u360443047_padariademo";

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