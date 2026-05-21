<?php
include('trava.php');
include('config.php');

// Define o fuso horário
date_default_timezone_set('America/Sao_Paulo');

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) { header("Location: clientes.php"); exit; }

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if (!$cliente) { header("Location: clientes.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $tel = $conn->real_escape_string($_POST['telefone']);
    
    $conn->query("UPDATE clientes SET nome = '$nome', telefone = '$tel' WHERE id = $id");
    
    header("Location: cliente_detalhes.php?id=$id&editado=1");
    exit();
}

// Tradução do mês para o header
$meses = array(
    'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr',
    'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
    'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
);
$mes_pt = $meses[date('M')];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <title>Editar Cliente - Elite OS</title>
    
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

        /* --- DESKTOP STRUCTURE --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
        }

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

        .content-body { 
            flex: 1;
            padding: 40px; 
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* --- FORM CARD --- */
        .card-form {
            background: var(--white);
            width: 100%;
            max-width: 550px;
            padding: 40px;
            border-radius: 25px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        .card-header { text-align: center; margin-bottom: 35px; }
        .card-header i {
            width: 60px; height: 60px;
            background: #f1f5f9;
            color: var(--primary);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin: 0 auto 15px;
        }

        .card-header h2 {
            font-size: 20px; font-weight: 900;
            text-transform: uppercase;
            color: var(--accent);
            letter-spacing: 1px;
        }

        .form-group { margin-bottom: 25px; }
        .form-group label {
            display: block; font-size: 11px; font-weight: 800;
            text-transform: uppercase; color: var(--text-light);
            margin-bottom: 10px; padding-left: 5px;
        }

        .form-group input {
            width: 100%; padding: 15px 20px;
            border-radius: 12px; border: 1px solid #e2e8f0;
            background: #f8fafb; font-family: 'Montserrat', sans-serif;
            font-size: 15px; font-weight: 600; color: var(--text-main);
            transition: var(--transition);
        }

        .form-group input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(214, 158, 136, 0.1);
        }

        .actions { display: flex; flex-direction: column; gap: 10px; }

        .btn-save {
            width: 100%; padding: 18px;
            background: var(--accent); color: white;
            border: none; border-radius: 12px;
            font-weight: 800; font-size: 13px;
            text-transform: uppercase; letter-spacing: 1px;
            cursor: pointer; transition: var(--transition);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }

        .btn-save:hover { background: #000; transform: translateY(-2px); }

        .btn-cancel {
            display: block; text-align: center;
            text-decoration: none; color: var(--text-light);
            font-weight: 700; font-size: 11px;
            text-transform: uppercase; transition: var(--transition);
            padding: 10px;
        }

        /* --- PWA / MOBILE ADJUSTMENTS --- */
        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0; }
            .top-navbar { display: none; } /* Hide Topbar in PWA */

            .content-body { 
                padding: 20px; 
                padding-bottom: 120px; /* Space for fixed buttons */
                background-color: var(--bg-body);
            }

            .card-form { 
                padding: 20px; border: none; 
                background: transparent; box-shadow: none; 
            }

            .card-header i { background: var(--white); }

            .form-group input {
                background: var(--white);
                border: 1px solid rgba(0,0,0,0.1);
            }

            .actions {
                position: fixed;
                bottom: 85px; /* Above bottom sidebar */
                left: 20px;
                right: 20px;
                background: transparent;
                z-index: 10;
            }

            .btn-save {
                padding: 22px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }

            .btn-cancel {
                background: transparent;
                font-size: 12px;
                margin-top: 5px;
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
            <div style="font-weight: 700; color: var(--text-light); text-transform: uppercase; font-size: 12px;">
                <i class="fa-solid fa-user-pen"></i> Editar Cliente
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
            <div class="card-form">
                <div class="card-header">
                    <i class="fa-solid fa-id-card"></i>
                    <h2>Editar Registro</h2>
                </div>

                <form method="POST" id="formEdit">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required placeholder="Ex: João Silva">
                    </div>
                    
                    <div class="form-group">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone']); ?>" placeholder="(00) 00000-0000">
                    </div>
                    
                    <div class="actions">
                        <button type="submit" class="btn-save" id="btnSalvar">
                            <i class="fa-solid fa-check" id="iconBtn"></i> 
                            <span id="textBtn">Salvar Alterações</span>
                        </button>

                        <a href="cliente_detalhes.php?id=<?php echo $id; ?>" class="btn-cancel">
                            Descartar e Voltar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Relógio (Desktop)
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
        
        if(telInput) {
            var currentVal = telInput.value.replace(/\D/g, '');
            if(currentVal.length > 10) {
                VMasker(telInput).maskPattern(telMask[1]);
            } else {
                VMasker(telInput).maskPattern(telMask[0]);
            }
            telInput.addEventListener('input', inputHandler.bind(undefined, telMask, 14), false);
        }

        // Feedback visual ao salvar
        document.getElementById('formEdit').onsubmit = function() {
            const btn = document.getElementById('btnSalvar');
            const icon = document.getElementById('iconBtn');
            const text = document.getElementById('textBtn');
            
            btn.disabled = true;
            btn.style.opacity = "0.7";
            icon.className = "fa-solid fa-spinner fa-spin";
            text.innerText = "SALVANDO...";
        };
    </script>

</body>
</html>