<?php
include('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha = $_POST['senha'];
    $senha = $conn->real_escape_string($senha);

    // Busca qualquer usuário que tenha essa senha
    $sql = "SELECT id, nome FROM usuarios WHERE senha = '$senha' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $dados = $result->fetch_assoc();
        
        // --- ADIÇÃO CRUCIAL PARA O PROJETO PADARIA ---
        $_SESSION['logado'] = true; // Esta linha agora ativa o seu trava.php
        // ---------------------------------------------

        $_SESSION['usuario_id'] = $dados['id'];
        $_SESSION['usuario_nome'] = $dados['nome'];
        
        header("Location: dashboard.php");
        exit(); // Adicionado exit para garantir que o script pare aqui
    } else {
        // Senha incorreta
        header("Location: index.php?erro=senha");
        exit();
    }
}
?>