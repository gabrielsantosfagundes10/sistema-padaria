<?php
session_start();
session_unset();
session_destroy();

// Redireciona para o index.php (onde está seu formulário de login)
header("Location: index.php");
exit();
?>