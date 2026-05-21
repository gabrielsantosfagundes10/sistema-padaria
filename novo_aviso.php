<?php
ob_start(); // Inicia o buffer para permitir redirecionamento instantâneo
// Define o fuso horário para Brasília/São Paulo antes de qualquer ação
date_default_timezone_set('America/Sao_Paulo');

include('trava.php');
include('config.php');

// Ativa exibição de erros para diagnóstico silencioso
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $texto = $conn->real_escape_string($_POST['mensagem']);
    // O autor é pego da sessão automaticamente
    $autor = $_SESSION['usuario_nome'] ?? 'Sistema'; 
    
    // Agora o INSERT usará o horário correto de Brasília definido acima
    $sql_insert = "INSERT INTO avisos (mensagem, autor) VALUES ('$texto', '$autor')";
    
    if ($conn->query($sql_insert)) {
        
        // --- TRUQUE DE VELOCIDADE: REDIRECIONAMENTO TURBO ---
        header("Location: avisos.php?sucesso=1");
        header("Connection: close");
        header("Content-Length: 0");
        
        ob_end_flush();
        flush(); 

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // --- ENVIO EM SEGUNDO PLANO PARA O TELEGRAM ---
        $msg_telegram = "📢 <b>NOVO AVISO NO MURAL!</b>\n\n";
        $msg_telegram .= "✍️ <b>Autor:</b> $autor\n";
        $msg_telegram .= "📝 <b>Recado:</b> $texto";
        
        if(function_exists('enviarTelegram')) {
            enviarTelegram($msg_telegram);
        }

        exit();
    }
}

// Meses para o header padrão
$meses_nomes = array(
    'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr',
    'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
    'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
);
$mes_pt = $meses_nomes[date('M')];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <title>Novo Aviso - PãoDaVida</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #d69e88;
            --accent: #1e1a19;
            --white: #ffffff;
            --text-main: #334155;
            --text-light: #64748b;
            --border-color: rgba(0,0,0,0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* --- NAVBAR --- */
        .top-navbar {
            height: 75px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .date-display { display: flex; align-items: center; gap: 15px; }
        .calendar-box {
            background: var(--accent);
            color: var(--white);
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            text-align: center;
            line-height: 1.2;
        }
        .clock-display { font-size: 22px; font-weight: 900; color: var(--accent); letter-spacing: -1px; }

        .content-body { padding: 30px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; align-items: center; }

        /* --- CONTAINER DO FORMULÁRIO --- */
        .form-container {
            width: 100%;
            max-width: 700px;
            background: var(--white);
            padding: 40px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .header-form { margin-bottom: 30px; }
        .header-form h1 { font-size: 24px; font-weight: 900; color: var(--accent); text-transform: uppercase; }
        .header-form p { color: var(--text-light); font-size: 14px; font-weight: 500; }

        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 10px; font-size: 12px; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px; }

        textarea {
            width: 100%;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid #f1f5f9;
            background: #f8fafb;
            color: var(--text-main);
            font-size: 16px;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
            min-height: 180px;
            resize: none;
            line-height: 1.6;
            transition: var(--transition);
        }

        textarea:focus { border-color: var(--primary); background: var(--white); box-shadow: 0 0 0 4px rgba(214, 158, 136, 0.1); }

        .char-count { text-align: right; font-size: 11px; color: var(--text-light); margin-top: 8px; font-weight: 700; }

        .actions { display: flex; gap: 15px; margin-top: 10px; }

        .btn-submit {
            flex: 2;
            padding: 18px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
        }

        .btn-submit:hover { background: #332d2b; transform: translateY(-2px); }

        .btn-cancel {
            flex: 1;
            padding: 18px;
            background: #f1f5f9;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            text-align: center;
            transition: var(--transition);
        }

        /* --- AJUSTES EXCLUSIVOS PWA (MOBILE) --- */
        @media (max-width: 768px) {
            body { height: 100vh; overflow: hidden; position: fixed; width: 100%; }
            .main-wrapper { margin-left: 0 !important; height: 100vh; }
            .top-navbar { display: none !important; }
            
            /* Transforma o content-body em container flex para travar o topo */
            .content-body { 
                padding: 0; 
                display: flex;
                flex-direction: column;
                height: 100vh;
                overflow: hidden;
                align-items: stretch;
            }

            /* Título fixo no topo no PWA */
            .header-form { 
                padding: 20px 15px;
                margin-bottom: 0;
                background: var(--bg-body);
                border-bottom: 1px solid var(--border-color);
                flex-shrink: 0;
            }
            .header-form h1 { font-size: 18px; }
            .header-form p { display: none !important; } /* Remove descrição secundária */

            /* Scroll apenas na área do formulário */
            .form-container { 
                flex: 1;
                overflow-y: auto;
                padding: 20px 15px;
                background: transparent;
                border: none;
                box-shadow: none;
                max-width: 100%;
                display: flex;
                flex-direction: column;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 100px; /* Espaço Sidebar */
            }

            textarea { min-height: 160px; font-size: 14px; }

            /* Botões fixos ou no final do formulário scrollável */
            .actions { 
                flex-direction: column; 
                gap: 10px; 
                margin-top: 15px;
            }
            
            .btn-submit { order: 1; padding: 16px; font-size: 12px; width: 100%; }
            .btn-cancel { order: 2; padding: 16px; font-size: 12px; width: 100%; }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .main-wrapper { margin-left: 80px; }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'avisos';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-plus"></i> Novo Aviso
            </div>
            
            <div class="date-display">
                <div class="calendar-box">
                    <div style="font-size: 14px;"><?php echo date('d'); ?></div>
                    <div><?php echo $mes_pt; ?></div>
                </div>
                <div class="clock-display" id="clock">00:00</div>
            </div>
        </header>

        <main class="content-body">
            <div class="header-form">
                <h1>Publicar Recado</h1>
                <p>A mensagem será exibida para todos os colaboradores.</p>
            </div>

            <div class="form-container">
                <form method="POST" id="formAviso">
                    <div class="form-group">
                        <label>Mensagem do Aviso</label>
                        <textarea name="mensagem" id="msgAviso" placeholder="Digite aqui o comunicado importante..." required maxlength="500"></textarea>
                        <div class="char-count"><span id="count">0</span> / 500</div>
                    </div>

                    <div class="actions">
                        <button type="submit" id="btnPublicar" class="btn-submit">
                            <i class="fa-solid fa-paper-plane" id="iconBtn"></i> 
                            <span id="textBtn">Publicar no Mural</span>
                        </button>
                        <a href="avisos.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('clock');
            if(clockEl) {
                clockEl.textContent = 
                    now.getHours().toString().padStart(2, '0') + ':' + 
                    now.getMinutes().toString().padStart(2, '0');
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        const textarea = document.getElementById('msgAviso');
        const count = document.getElementById('count');
        const form = document.getElementById('formAviso');
        const btn = document.getElementById('btnPublicar');
        const textBtn = document.getElementById('textBtn');
        const iconBtn = document.getElementById('iconBtn');

        textarea.addEventListener('input', () => {
            count.innerText = textarea.value.length;
            if(textarea.value.length >= 450) {
                count.style.color = "#ef4444";
            } else {
                count.style.color = "var(--text-light)";
            }
        });

        form.onsubmit = function() {
            btn.disabled = true;
            btn.style.opacity = "0.7";
            btn.style.cursor = "not-allowed";
            textBtn.innerText = "PUBLICANDO...";
            iconBtn.className = "fa-solid fa-spinner fa-spin";
        };
    </script>
</body>
</html>