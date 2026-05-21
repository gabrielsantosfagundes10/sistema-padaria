<?php
include('config.php');

$dados = json_decode(file_get_contents('php://input'), true);

if (isset($dados['endpoint'])) {
    $endpoint = $dados['endpoint'];
    $p256dh = $dados['keys']['p256dh'];
    $auth = $dados['keys']['auth'];

    // Evita duplicados
    $check = $conn->query("SELECT id FROM notificacoes_tokens WHERE endpoint = '$endpoint'");
    
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO notificacoes_tokens (endpoint, p256dh, auth) VALUES ('$endpoint', '$p256dh', '$auth')";
        $conn->query($sql);
    }
}
?>