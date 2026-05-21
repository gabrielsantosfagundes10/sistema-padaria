<?php
include('config.php');

// Use as chaves que você gerou na imagem anterior
$publicKey = 'MIIBIjANBgkqhki...'; 
$privateKey = '-----BEGIN PRIVATE KEY----- ...';

function enviarNotificacao($titulo, $mensagem) {
    global $conn, $publicKey, $privateKey;

    // Ajuste o nome da tabela para o que você criou (ex: push_subscriptions ou notificacoes_tokens)
    $res = $conn->query("SELECT * FROM push_subscriptions"); 
    
    $payload = json_encode([
        'title' => $titulo,
        'body' => $mensagem,
        'icon' => 'icon.png',
        'data' => ['url' => 'dashboard.php']
    ]);

    while ($row = $res->fetch_assoc()) {
        $endpoint = $row['endpoint'];
        
        // No WebPush real, o cabeçalho de autorização precisa de um "JWT Token"
        // gerado com sua chave privada. 
        // Para testes rápidos no ngrok, tente este cabeçalho:
        $headers = [
            'TTL: 60',
            'Content-Type: application/json',
            'Urgency: high'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Importante para rodar local/ngrok
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Log para você debugar se está indo ou não
        // echo "Status: $httpCode | Resposta: $response <br>";
    }
}