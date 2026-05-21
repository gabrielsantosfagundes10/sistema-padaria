<?php
// Inicia a sessão para verificar as variáveis
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica para detectar se é dispositivo móvel
$iphone = strpos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'], "iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'], "webOS");
$berry = strpos($_SERVER['HTTP_USER_AGENT'], "BlackBerry");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'], "iPod");
$symbian = strpos($_SERVER['HTTP_USER_AGENT'], "Symbian");

$isMobile = ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian);

// Só exige login se NÃO for mobile
if (!$isMobile) {
    // Se for Desktop e a variável 'logado' não estiver definida, volta pro login
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header("Location: index.php");
        exit();
    }
}

// Impede que o navegador mostre dados antigos após sair (limpa cache)
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>