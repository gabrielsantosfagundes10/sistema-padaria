<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Instalar App - Padaria</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { 
            font-family: 'Montserrat', sans-serif; 
            color: #fff;
            background: radial-gradient(circle at top right, rgba(255, 87, 51, 0.1), transparent), #050505;
            margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; padding: 20px;
        }
        .install-card { 
            width: 100%; max-width: 450px; background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(20px); padding: 40px; border-radius: 35px; 
            border: 1px solid rgba(255,255,255,0.1); text-align: center;
        }
        .app-logo { 
            width: 120px; height: 120px; 
            margin: 0 auto 25px; border-radius: 25px; 
            display: block; 
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            object-fit: cover;
            border: 2px solid rgba(255, 87, 51, 0.3);
        }
        h1 { font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; font-size: 22px; }
        p { color: rgba(255,255,255,0.6); font-size: 14px; line-height: 1.6; margin-bottom: 30px; }
        
        #btnInstalar { 
            width: 100%; padding: 20px; background: linear-gradient(45deg, #ff5733, #ff8c00); 
            color: #fff; border: none; border-radius: 18px; font-weight: 900; 
            cursor: pointer; font-size: 14px; text-transform: uppercase; 
            letter-spacing: 1px; display: none; /* Só aparece se for compatível */
            box-shadow: 0 10px 20px rgba(255, 87, 51, 0.3);
        }
        
        .instrucoes-ios { 
            display: none; background: rgba(255,255,255,0.05); padding: 20px; 
            border-radius: 20px; font-size: 13px; color: rgba(255,255,255,0.8); line-height: 1.5;
        }
        .instrucoes-ios i { color: #ff5733; font-size: 18px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="install-card">
    <img src="icon.png" alt="Logo Padaria" class="app-logo">
    <h1>Padaria App</h1>
    <p>Tenha o controle da padaria na palma da sua mão com acesso rápido e notificações.</p>

    <button id="btnInstalar">
        <i class="fa-solid fa-download" style="margin-right: 8px;"></i> Instalar Agora
    </button>

    <div id="msgIOS" class="instrucoes-ios">
        <i class="fa-solid fa-arrow-up-from-bracket"></i><br>
        No iPhone, toque no botão de <b>Compartilhar</b> (quadrado com seta) e escolha <b>"Adicionar à Tela de Início"</b>.
    </div>
</div>

<script>
    let deferredPrompt;
    const btnInstalar = document.getElementById('btnInstalar');
    const msgIOS = document.getElementById('msgIOS');

    // Detectar se é iPhone
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    if (isIOS) {
        msgIOS.style.display = 'block';
    }

    // Captura o evento de instalação (Android/PC)
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        btnInstalar.style.display = 'block';
        msgIOS.style.display = 'none'; // Se pode instalar via botão, esconde instrução manual
    });

    btnInstalar.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                btnInstalar.style.display = 'none';
            }
            deferredPrompt = null;
        }
    });

    window.addEventListener('appinstalled', () => {
        btnInstalar.style.display = 'none';
        alert('App instalado com sucesso!');
    });
</script>

</body>
</html>