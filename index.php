<?php
// Lógica para pular o login se for dispositivo móvel
$iphone = strpos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'], "iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'], "webOS");
$berry = strpos($_SERVER['HTTP_USER_AGENT'], "BlackBerry");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'], "iPod");
$symbian = strpos($_SERVER['HTTP_USER_AGENT'], "Symbian");

if ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pão Da Vida | Login</title>

    <link rel="manifest" href="./manifest.json?v=4">
    
    <meta name="theme-color" content="#2A1B15">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pão da Vida">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #2A1B15;       /* Marrom Base */
            --accent-clay: #CC7A2D;   /* Tom de pão assado / Argila */
            --form-bg: #FFFFFF;
            --input-bg: #F5F5F7;
            --text-main: #1D1D1F;
            --text-secondary: #86868B;
            --radius: 16px;
            --transition: all 0.3s ease;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            -webkit-font-smoothing: antialiased;
        }

        body {
            font-family: 'Inter', sans-serif;
            /* Fundo Marrom Escuro com a imagem padaria01.png */
            background: linear-gradient(rgba(42, 27, 21, 0.85), rgba(42, 27, 21, 0.95)), 
                        url('images/padaria01.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .login-card {
            background: var(--form-bg);
            padding: 56px 48px;
            border-radius: 32px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
            text-align: center;
        }

        .header {
            margin-bottom: 35px;
        }

        .header img {
            width: 180px; /* Logo aumentada */
            height: auto;
            margin-bottom: 24px;
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 26px;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: -1px;
            line-height: 1;
        }

        .header p {
            color: var(--accent-clay);
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            height: 64px;
            padding: 0 20px;
            background: var(--input-bg);
            border: 2px solid transparent;
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 500;
            color: var(--text-main);
            transition: var(--transition);
            text-align: center;
        }

        input:focus {
            background: #fff;
            border-color: var(--accent-clay);
            outline: none;
            box-shadow: 0 0 0 4px rgba(204, 122, 45, 0.1);
        }

        input::placeholder {
            color: #A1A1A6;
        }

        button {
            width: 100%;
            height: 64px;
            background: var(--accent-clay);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 8px;
            box-shadow: 0 10px 20px rgba(204, 122, 45, 0.2);
        }

        button:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(204, 122, 45, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        /* Mensagem de Erro */
        .error-box {
            background: #1D1D1F;
            border-radius: var(--radius);
            padding: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            animation: shake 0.4s ease;
        }

        .error-box span {
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .footer {
            text-align: center;
        }

        .footer span {
            color: rgba(255,255,255,0.6);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* Ajustes Mobile Desktop */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card" id="mainCard">
        <div class="header">
            <img src="images/paovidalogo.png" alt="Padaria Pão da Vida">
            <h2>Pão da Vida</h2>
            <p>Acesso Administrativo</p>
        </div>

        <div id="errorBox" class="error-box">
            <i class="fa-solid fa-circle-exclamation" style="color: var(--accent-clay);"></i>
            <span>Senha Incorreta</span>
        </div>

        <form action="processa_login.php" method="POST">
            <div class="form-group">
                <input type="password" name="senha" id="inputSenha" placeholder="Senha de acesso" required autofocus>
            </div>
            <button type="submit">Entrar no Sistema</button>
        </form>
    </div>

    <div class="footer">
        <span>Padaria Pão da Vida &bull; 2026</span>
    </div>
</div>

<script>
    window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('erro')) {
            const errorBox = document.getElementById('errorBox');
            const mainCard = document.getElementById('mainCard');
            const input = document.getElementById('inputSenha');
            
            errorBox.style.display = 'flex';
            mainCard.style.animation = 'shake 0.4s ease';
            input.style.borderColor = '#1D1D1F';
            
            setTimeout(() => {
                mainCard.style.animation = '';
            }, 400);
        }
    };

    // Registro do SW para manter o funcionamento base
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
            .then(reg => console.log('SW ativo'))
            .catch(err => console.log('Erro SW:', err));
        });
    }
</script>

</body>
</html>