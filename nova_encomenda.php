<?php
ob_start(); 
// Define o fuso horário para Brasília antes de qualquer ação
date_default_timezone_set('America/Sao_Paulo');

include('trava.php');
include('config.php');

// Ativa exibição de erros para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Coleta e limpa os dados
    $tipo = $conn->real_escape_string($_POST['tipo']); 
    $sabor = $conn->real_escape_string($_POST['sabor']);
    $valor = !empty($_POST['valor']) ? $_POST['valor'] : 0;
    $data = $_POST['data_entrega'];
    $horario = $_POST['horario'];
    $status = $_POST['status_pagamento'];
    
    if ($status == 'parcial' && !empty($_POST['valor_parcial'])) {
        $status = "Pago R$ " . $_POST['valor_parcial'];
    }

    $nome = $conn->real_escape_string($_POST['nome_cliente']);
    $tel = $conn->real_escape_string($_POST['telefone']);

    // 2. Executa o INSERT no Banco de Dados
    $sql = "INSERT INTO encomendas (tipo, sabor_detalhes, valor, data_entrega, horario, status_pagamento, nome_cliente, telefone_cliente) 
            VALUES ('$tipo', '$sabor', '$valor', '$data', '$horario', '$status', '$nome', '$tel')";
    
    if ($conn->query($sql)) { 
        
        // --- TRUQUE DE VELOCIDADE: REDIRECIONAMENTO TURBO ---
        header("Location: encomendas.php?sucesso=1");
        header("Connection: close");
        header("Content-Length: 0");
        
        ob_end_flush();
        flush(); 

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // 3. PROCESSAMENTO EM SEGUNDO PLANO
        $data_formatada = date('d/m/Y', strtotime($data));
        $hoje = date('Y-m-d');
        $amanha = date('Y-m-d', strtotime('+1 day'));

        if ($data == $hoje) {
            $titulo = "🚨 <b>URGENTE: ENCOMENDA PARA HOJE!</b>";
        } elseif ($data == $amanha) {
            $titulo = "⏳ <b>ATENÇÃO: ENCOMENDA PARA AMANHÃ!</b>";
        } else {
            $titulo = "📦 <b>NOVA ENCOMENDA REGISTRADA</b>";
        }

        $msg = "$titulo\n\n";
        $msg .= "👤 <b>Cliente:</b> $nome\n";
        $msg .= "🎂 <b>Pedido:</b> $tipo\n";
        $msg .= "📅 <b>Entrega:</b> $data_formatada às $horario\n";
        $msg .= "💰 <b>Valor:</b> R$ " . number_format($valor, 2, ',', '.') . "\n";
        $msg .= "💳 <b>Status:</b> $status";
        
        if(function_exists('enviarTelegram')) {
            enviarTelegram($msg);
        }
        
        exit();
    } else {
        die("Erro ao salvar no banco: " . $conn->error);
    }
}

// Meses para o header
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
    <title>Agendar Encomenda - PãoDaVida</title>
    
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
            position: relative;
            overflow: hidden;
        }

        /* NAVBAR - Oculta no Mobile */
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

        /* CONTEÚDO COM SCROLL INDEPENDENTE */
        .content-body { 
            padding: 30px; 
            overflow-y: auto; 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            align-items: center;
            -webkit-overflow-scrolling: touch;
        }

        .form-container {
            width: 100%;
            max-width: 900px;
            background: var(--white);
            padding: 40px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            margin-bottom: 20px;
        }

        .header-form { margin-bottom: 35px; border-left: 5px solid var(--primary); padding-left: 20px; }
        .header-form h1 { font-size: 24px; font-weight: 900; color: var(--accent); text-transform: uppercase; }
        .header-form p { color: var(--text-light); font-size: 14px; font-weight: 500; }

        /* GRID DO FORMULÁRIO */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .full-width { grid-column: span 2; }

        .form-group { margin-bottom: 5px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select, textarea {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            background: #f8fafb;
            color: var(--text-main);
            font-size: 15px;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            transition: var(--transition);
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(214, 158, 136, 0.1);
        }

        #div_parcial {
            background: #fff9f7;
            padding: 20px;
            border-radius: 15px;
            border: 2px dashed var(--primary);
            grid-column: span 2;
        }

        /* BOTÕES */
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            grid-column: span 2;
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* RESPONSIVIDADE MOBILE PWA */
        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0 !important; }
            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 15px; 
                padding-bottom: 100px; /* Espaço para sidebar inferior */
            }
            
            .form-container { padding: 20px; border-radius: 15px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width, #div_parcial, .actions { grid-column: span 1; }
            .actions { flex-direction: column-reverse; }
            
            .header-form h1 { font-size: 20px; }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .main-wrapper { margin-left: 80px; }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body>

    <?php 
    $activePage = 'encomendas';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-calendar-plus"></i> Agendar Pedido
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
                    <h1>Nova Encomenda</h1>
                    <p>Preencha os dados abaixo para registrar o pedido.</p>
                </div>

                <form method="POST" id="formEncomenda">
                    <div class="form-grid">
                        <div class="full-width form-group">
                            <label>O que o cliente pediu?</label>
                            <input type="text" name="tipo" placeholder="Ex: 2kg de Bolo de Chocolate" required>
                        </div>

                        <div class="full-width form-group">
                            <label>Detalhes (Sabor/Recheio/Observações)</label>
                            <textarea name="sabor" rows="2" placeholder="Descreva os detalhes do pedido..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Valor Total (R$)</label>
                            <input type="number" step="0.01" name="valor" placeholder="0,00">
                        </div>

                        <div class="form-group">
                            <label>Status de Pagamento</label>
                            <select name="status_pagamento" id="status_pagamento">
                                <option value="pendente">🔴 Pendente</option>
                                <option value="metade">🟡 Metade Pago</option>
                                <option value="pago">🟢 Totalmente Pago</option>
                                <option value="parcial">🔵 Outro Valor</option>
                            </select>
                        </div>

                        <div id="div_parcial" style="display:none;">
                            <label>Quanto o cliente já pagou?</label>
                            <input type="number" step="0.01" name="valor_parcial" placeholder="R$ 0,00">
                        </div>

                        <div class="form-group">
                            <label>Data de Entrega</label>
                            <input type="date" name="data_entrega" required>
                        </div>

                        <div class="form-group">
                            <label>Horário de Retirada</label>
                            <input type="time" name="horario">
                        </div>

                        <div class="form-group">
                            <label>Nome do Cliente</label>
                            <input type="text" name="nome_cliente" placeholder="Nome completo" required>
                        </div>

                        <div class="form-group">
                            <label>Telefone / WhatsApp</label>
                            <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000">
                        </div>

                        <div class="actions">
                            <a href="encomendas.php" class="btn-cancel">Voltar</a>
                            <button type="submit" id="btnSalvar" class="btn-submit">
                                <i class="fa-solid fa-check" id="iconBtn"></i> 
                                <span id="textBtn">Finalizar Agendamento</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Relógio em tempo real
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

        // Máscara de Telefone
        var telMask = ['(99) 9999-9999', '(99) 99999-9999'];
        var telInput = document.querySelector('#telefone');
        if(telInput) {
            VMasker(telInput).maskPattern(telMask[0]);
            telInput.addEventListener('input', function(e) {
                var m = e.target.value.length > 14 ? 1 : 0;
                VMasker(e.target).unMask();
                VMasker(e.target).maskPattern(telMask[m]);
            });
        }

        // Controle do campo de valor parcial
        document.getElementById('status_pagamento').addEventListener('change', function() {
            document.getElementById('div_parcial').style.display = (this.value === 'parcial') ? 'block' : 'none';
        });

        // Feedback de envio
        document.getElementById('formEncomenda').onsubmit = function() {
            let btn = document.getElementById('btnSalvar');
            let icon = document.getElementById('iconBtn');
            let text = document.getElementById('textBtn');
            
            btn.disabled = true;
            btn.style.opacity = "0.7";
            icon.className = "fa-solid fa-spinner fa-spin";
            text.innerText = "SALVANDO PEDIDO...";
        };
    </script>
</body>
</html>