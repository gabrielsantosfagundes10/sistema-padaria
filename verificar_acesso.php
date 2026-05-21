<?php
include('trava.php');
// session_start() removido para evitar conflito com trava.php

$senha_mestra = "2026"; 
$erro = "";

// Lógica para detectar se é Mobile/PWA simplificada
$isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);

// Se for mobile, pula a verificação de senha e vai direto para relatórios
if ($isMobile) {
    $_SESSION['pode_ver_relatorios'] = true;
    header("Location: relatorios.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_digitada = $_POST['senha'] ?? '';
    
    if ($senha_digitada === $senha_mestra) {
        $_SESSION['pode_ver_relatorios'] = true;
        header("Location: relatorios.php");
        exit;
    } else {
        $erro = "Senha incorreta. Acesso negado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <title>Acesso Restrito - Elite OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f4f7f6;
            --accent-color: #d69e88;
            --dark-bg: #1e1a19;
            --white: #ffffff;
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-body);
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: radial-gradient(circle at top right, rgba(214, 158, 136, 0.12), transparent),
                        radial-gradient(circle at bottom left, rgba(30, 26, 25, 0.05), transparent);
            transition: var(--transition);
        }

        .lock-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 60px 45px;
            border-radius: 40px;
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: fadeInScale 0.7s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .icon-lock-wrapper {
            width: 90px;
            height: 90px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: var(--dark-bg);
            font-size: 36px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08), 
                        inset 0 0 0 2px var(--accent-color);
            position: relative;
        }

        .icon-lock-wrapper::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1px solid var(--accent-color);
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.5; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        h2 { 
            font-size: 24px; 
            font-weight: 900; 
            color: var(--dark-bg); 
            margin-bottom: 12px; 
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        p { 
            font-size: 14px; 
            color: #64748b; 
            margin-bottom: 40px; 
            line-height: 1.6;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 22px;
            background: #ffffff;
            border: 2px solid #edf2f7;
            border-radius: 20px;
            color: var(--dark-bg);
            font-size: 26px;
            text-align: center;
            font-weight: 800;
            letter-spacing: 10px;
            outline: none;
            transition: var(--transition);
        }

        input:focus { 
            border-color: var(--accent-color); 
            box-shadow: 0 15px 30px rgba(214, 158, 136, 0.2);
            transform: translateY(-3px);
        }

        button {
            width: 100%;
            padding: 22px;
            background: var(--dark-bg);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 3px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        button:hover {
            background: #2d2826;
            box-shadow: 0 20px 40px rgba(30, 26, 25, 0.3);
            transform: translateY(-4px);
        }

        .error-msg { 
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444; 
            padding: 15px;
            border-radius: 15px;
            font-size: 11px; 
            font-weight: 800; 
            margin-top: 25px; 
            text-transform: uppercase;
            border: 1px solid rgba(239, 68, 68, 0.15);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        /* Ajustes PWA e Mobile */
        @media (max-width: 1024px) { 
            .main-wrapper { 
                margin-left: 0 !important; 
                width: 100%; 
                background: var(--bg-body); /* Limpa o gradiente pesado no mobile */
            }
            .lock-card { 
                display: none; /* Segurança extra: esconde o card se o redirecionamento PHP falhar */
            }
            .brand-footer { display: none; }
        }

        .brand-footer {
            position: absolute;
            bottom: 40px;
            font-size: 11px;
            font-weight: 900;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'relatorios'; 
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <div class="lock-card">
            <div class="icon-lock-wrapper">
                <i class="fa-solid fa-lock"></i>
            </div>
            
            <h2>Área Restrita</h2>
            <p>O acesso aos relatórios exige a senha mestra</p>
            
            <form method="POST">
                <div style="position: relative;">
                    <input type="password" name="senha" placeholder="••••" maxlength="8" required autofocus>
                </div>
                
                <button type="submit">
                    <span>Desbloquear</span>
                    <i class="fa-solid fa-key"></i>
                </button>
                
                <?php if($erro): ?>
                    <div class="error-msg">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $erro; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="brand-footer">Área Restrita - Pão Da Vida</div>
    </div>

    <script>
        // Configurações PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('Service Worker não registrado:', err));
            });
        }
    </script>

</body>
</html>