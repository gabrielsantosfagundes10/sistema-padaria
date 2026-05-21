<?php
// Tenta localizar o arquivo de configuração do OpenSSL no XAMPP
$config_path = "C:/xampp/php/extras/ssl/openssl.cnf";

$config = array(
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);

// Se o arquivo do XAMPP existir, adiciona ao comando
if (file_exists($config_path)) {
    $config["config"] = $config_path;
}

$res = openssl_pkey_new($config);

if (!$res) {
    die("Erro ao gerar chaves: " . openssl_error_string());
}

openssl_pkey_export($res, $privKey, null, $config);
$pubKeyDetails = openssl_pkey_get_details($res);
$pubKey = $pubKeyDetails["key"];

// Formata para o padrão Web Push (Base64 URL Safe)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Extrai a parte bruta da chave pública (removendo os cabeçalhos PEM)
$pubKeyClean = str_replace(["-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n", "\r"], '', $pubKey);

echo "<h3>Chaves Geradas com Sucesso!</h3>";
echo "<b>Public Key (Para o Dashboard.php):</b><br>";
echo "<textarea style='width:100%;height:60px;word-break:break-all;'>" . $pubKeyClean . "</textarea><br><br>";
echo "<b>Private Key (Para o seu arquivo de envio):</b><br>";
echo "<textarea style='width:100%;height:150px;'>" . $privKey . "</textarea>";
?>