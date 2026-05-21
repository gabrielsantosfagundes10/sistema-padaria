<?php
include('trava.php');
include('config.php');

// Define o fuso horário para não bugar a hora
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $tel = $conn->real_escape_string($_POST['telefone']); 
    
    $conn->query("INSERT INTO clientes (nome, telefone) VALUES ('$nome', '$tel')");
    header("Location: contas_clientes.php");
    exit();
}

// Meses para o header padrão Elite OS
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Novo Cliente - PãoDaVida</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-masker/1.2.0/vanilla-masker.min.js"></script>
    
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

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; -webkit-tap-highlight-color: transparent; }

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

        /* --- TOPBAR (Desktop Only) --- */
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

        /* --- CONTENT --- */
        .content-body { padding: 30px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; align-items: center; }

        .form-container {
            width: 100%;
            max-width: 600px;
            background: var(--white);
            padding: 40px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .header-form { margin-bottom: 35px; }
        .header-form h1 { font-size: 24px; font-weight: 900; color: var(--accent); text-transform: uppercase; margin-bottom: 5px; }
        .header-form p { color: var(--text-light); font-size: 14px; font-weight: 500; }

        .form-group { margin-bottom: 25px; }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 18px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            background: #f8fafb;
            color: var(--text-main);
            font-size: 16px;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            transition: var(--transition);
        }

        input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(214, 158, 136, 0.1);
        }

        /* --- ACTIONS --- */
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

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

        .btn-submit:hover {
            background: #332d2b;
            transform: translateY(-2px);
        }

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

        .btn-cancel:hover { background: #e2e8f0; color: var(--text-main); }

        /* --- AJUSTES EXCLUSIVOS PWA --- */
        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0; height: 100vh; }
            .top-navbar { display: none; } /* Remove topbar no PWA */
            
            .content-body { 
                padding: 20px; 
                padding-bottom: 100px; /* Espaço para a sidebar inferior */
                height: 100vh;
                background-color: var(--bg-body);
            }

            .form-container { 
                padding: 20px; 
                border: none; 
                background: transparent; 
                box-shadow: none; 
                max-width: 100%;
            }

            .header-form h1 { font-size: 28px; }

            input {
                background: var(--white);
                border: 1px solid rgba(0,0,0,0.1);
                font-size: 15px;
            }

            .actions {
                position: fixed;
                bottom: 85px; /* Acima da sidebar inferior */
                left: 20px;
                right: 20px;
                flex-direction: column;
                gap: 10px;
                background: transparent;
            }

            .btn-submit {
                order: 1;
                padding: 22px;
                font-size: 14px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }

            .btn-cancel {
                order: 2;
                padding: 15px;
                background: transparent;
                color: var(--text-light);
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'clientes';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-user-plus"></i> Novo Cliente
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
            <div class="form-container">
                <div class="header-form">
                    <h1>Novo Cliente</h1>
                    <p>Preencha os dados básicos do cliente abaixo.</p>
                </div>

                <form method="POST" id="formCliente">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="nome" required placeholder="Digite o nome" autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" autocomplete="off">
                    </div>

                    <div class="actions">
                        <button type="submit" id="btnSalvar" class="btn-submit">
                            <i class="fa-solid fa-check" id="iconBtn"></i> 
                            <span id="textBtn">Finalizar Cadastro</span>
                        </button>
                        <a href="contas_clientes.php" class="btn-cancel">Voltar para lista</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Relógio
        function updateClock() {
            const clockEl = document.getElementById('clock');
            if(clockEl) {
                const now = new Date();
                clockEl.textContent = 
                    now.getHours().toString().padStart(2, '0') + ':' + 
                    now.getMinutes().toString().padStart(2, '0');
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Máscara de Telefone
        function inputHandler(masks, max, event) {
            var c = event.target;
            var v = c.value.replace(/\D/g, '');
            var m = c.value.length > max ? 1 : 0;
            VMasker(c).unMask();
            VMasker(c).maskPattern(masks[m]);
            c.value = VMasker.toPattern(v, masks[m]);
        }

        var telMask = ['(99) 9999-9999', '(99) 99999-9999'];
        var telInput = document.querySelector('#telefone');
        VMasker(telInput).maskPattern(telMask[0]);
        telInput.addEventListener('input', inputHandler.bind(undefined, telMask, 14), false);

        // Feedback de Submissão
        document.getElementById('formCliente').onsubmit = function() {
            const btn = document.getElementById('btnSalvar');
            const icon = document.getElementById('iconBtn');
            const text = document.getElementById('textBtn');
            
            btn.disabled = true;
            btn.style.opacity = "0.7";
            icon.className = "fa-solid fa-spinner fa-spin";
            text.innerText = "CADASTRANDO...";
        };
    </script>

</body>
</html>