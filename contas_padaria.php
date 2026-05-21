<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

$hoje = date('Y-m-d');
$amanha = date('Y-m-d', strtotime('+1 day'));

// --- LÓGICA DE ALERTA PARA O TELEGRAM ---
$sql_alertas = "SELECT descricao, valor, data_vencimento FROM contas_padaria 
                WHERE status_pago = 0 
                AND rascunho = 0
                AND data_vencimento <= '$amanha'";

$res_alertas = $conn->query($sql_alertas);

if ($res_alertas && $res_alertas->num_rows > 0) {
    $msg_alerta = "⚠️ <b>ALERTA DE CONTAS</b>\n\n";
    $tem_urgencia = false;

    while($conta = $res_alertas->fetch_assoc()) {
        $venc = $conta['data_vencimento'];
        $valor = number_format($conta['valor'], 2, ',', '.');
        
        if ($venc < $hoje) {
            $msg_alerta .= "❌ <b>ATRASADA:</b> ";
            $tem_urgencia = true;
        } elseif ($venc == $hoje) {
            $msg_alerta .= "🚨 <b>VENCE HOJE:</b> ";
            $tem_urgencia = true;
        } else {
            $msg_alerta .= "⏳ <b>VENCE AMANHÃ:</b> ";
            $tem_urgencia = true;
        }
        
        $msg_alerta .= $conta['descricao'] . " (R$ $valor)\n\n";
    }

    if ($tem_urgencia) {
        // enviarTelegram($msg_alerta); 
    }
}

// Lógica para marcar como pago
if (isset($_GET['pagar'])) {
    $id = intval($_GET['pagar']);
    $conn->query("UPDATE contas_padaria SET status_pago = 1 WHERE id = $id");
    header("Location: contas_padaria.php?sucesso=1");
    exit();
}

// Lógica para excluir
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM contas_padaria WHERE id = $id");
    header("Location: contas_padaria.php?excluido=1");
    exit();
}

// Busca as contas
$result = $conn->query("SELECT * FROM contas_padaria WHERE status_pago = 0 AND rascunho = 0 ORDER BY data_vencimento ASC");

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
    <title>Financeiro - PãoDaVida</title>
    
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
            --danger: #ef4444;
            --warning: #f5a623;
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
            transition: var(--transition);
        }

        /* --- NAVBAR DESKTOP --- */
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

        .content-body { padding: 30px; overflow-y: auto; flex: 1; }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 35px;
        }

        .btn-add-new {
            background: var(--accent);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            display: flex; align-items: center; gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .grid-contas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        /* --- CARDS --- */
        .card { 
            background: var(--white); 
            padding: 25px; 
            border-radius: 20px; 
            display: flex; 
            flex-direction: column;
            border: 1px solid var(--border-color);
            border-left: 8px solid #cbd5e0;
            transition: var(--transition);
        }

        .vencida { border-left-color: #000 !important; background: var(--danger); color: white; }
        .vence-hoje { border-left-color: var(--danger) !important; }
        .vence-amanha { border-left-color: var(--warning) !important; }

        .status-badge { 
            font-size: 9px; font-weight: 900; text-transform: uppercase; 
            margin-bottom: 12px; display: inline-block;
            padding: 6px 12px; border-radius: 8px; background: #f1f5f9;
        }
        .vencida .status-badge { background: rgba(0,0,0,0.2); color: white; }

        .conta-nome { font-size: 1.2rem; font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .conta-valor { font-size: 1.7rem; font-weight: 900; margin-top: 15px; display: block; }

        .actions { 
            display: flex; align-items: center; justify-content: flex-end; 
            gap: 15px; margin-top: 20px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 18px;
        }

        .btn-check { 
            background: #28a745; color: white; padding: 10px 18px; 
            border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 10px;
        }
        
        /* --- AJUSTES PWA (pumpcerto_somentepwa) --- */
        @media (max-width: 768px) {
            body { height: 100vh; overflow: hidden; position: fixed; width: 100%; }
            .main-wrapper { margin-left: 0 !important; height: 100vh; }
            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 0; 
                display: flex;
                flex-direction: column;
                height: 100vh;
                overflow: hidden;
            }

            /* Trava o Título e Botão no topo */
            .header-title { 
                padding: 20px 15px;
                margin-bottom: 0;
                background: var(--bg-body);
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                flex-shrink: 0;
                border-bottom: 1px solid var(--border-color);
            }
            
            .header-title h1 { font-size: 22px !important; }
            .header-title p { display: none !important; } /* Tira o subtítulo */

            .btn-add-new { 
                width: 100%; 
                justify-content: center;
                padding: 12px;
            }

            /* Container de Scroll */
            .contas-container {
                flex: 1;
                overflow-y: auto;
                padding: 15px;
                padding-bottom: 100px; /* Espaço para sidebar inferior */
                -webkit-overflow-scrolling: touch;
            }

            .grid-contas { grid-template-columns: 1fr; gap: 15px; }
            .card { padding: 18px; }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body>

    <?php 
    $activePage = 'financas';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-file-invoice-dollar"></i> Financeiro Padaria
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
            <div class="header-title">
                <div>
                    <h1 style="font-size: 26px; font-weight: 900; color: var(--accent);">CONTAS A PAGAR</h1>
                    <p style="color: var(--text-light); font-size: 14px; font-weight: 500;">Contas pendentes de pagamento (Boletos/Fixas).</p>
                </div>
                <a href="nova_conta_padaria.php" class="btn-add-new">
                    <i class="fa-solid fa-plus"></i> NOVA CONTA
                </a>
            </div>

            <div class="contas-container">
                <div class="grid-contas">
                    <?php 
                    if ($result->num_rows == 0) {
                        echo "<div style='grid-column: 1/-1; text-align:center; padding: 60px 20px; background:white; border-radius:20px; border:1px solid var(--border-color);'>
                                <p style='color:var(--text-light); font-weight: 700;'>Tudo em dia!</p>
                              </div>";
                    }

                    while($row = $result->fetch_assoc()): 
                        $classe_urgencia = '';
                        $badge = '';
                        $nome_conta = !empty($row['descricao']) ? htmlspecialchars($row['descricao']) : 'CONTA SEM DESCRIÇÃO';

                        if($row['data_vencimento'] < $hoje) {
                            $classe_urgencia = 'vencida';
                            $badge = '<span class="status-badge">⚠️ ATRASADA</span>';
                        } elseif($row['data_vencimento'] == $hoje) {
                            $classe_urgencia = 'vence-hoje';
                            $badge = '<span class="status-badge" style="color:var(--danger);">🚨 VENCE HOJE</span>';
                        } elseif($row['data_vencimento'] == $amanha) {
                            $classe_urgencia = 'vence-amanha';
                            $badge = '<span class="status-badge" style="color:var(--warning);">⏳ VENCE AMANHÃ</span>';
                        } else {
                            $badge = '<span class="status-badge" style="color:#636e72;">✅ NO PRAZO</span>';
                        }
                    ?>
                        <div class="card <?php echo $classe_urgencia; ?>">
                            <div>
                                <?php echo $badge; ?>
                                <span class="conta-nome"><?php echo $nome_conta; ?></span>
                                <span style="font-size: 0.85rem; font-weight: 600; opacity: 0.8;">
                                    Venc: <?php echo date('d/m/Y', strtotime($row['data_vencimento'])); ?>
                                </span>
                                <span class="conta-valor">R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></span>
                            </div>

                            <div class="actions">
                                <a href="editar_conta_padaria.php?id=<?php echo $row['id']; ?>" style="color: #3b82f6;"><i class="fa-solid fa-pen-to-square"></i></a>
                                <button onclick="confirmarExclusao(<?php echo $row['id']; ?>)" style="color: var(--danger); border:none; background:none; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                                <a href="#" onclick="confirmarPagamento(<?php echo $row['id']; ?>)" class="btn-check">Dar Baixa</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    function updateClock() {
        const now = new Date();
        const clock = document.getElementById('clock');
        if(clock) {
            clock.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    function confirmarPagamento(id) {
        Swal.fire({
            title: 'Confirmar Pagamento?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'SIM, FOI PAGO!',
            cancelButtonText: 'CANCELAR'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'contas_padaria.php?pagar=' + id; } })
    }

    function confirmarExclusao(id) {
        Swal.fire({
            title: 'EXCLUIR?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'SIM, EXCLUIR!',
            cancelButtonText: 'CANCELAR'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'contas_padaria.php?excluir=' + id; } })
    }

    <?php if(isset($_GET['sucesso'])): ?>
        Swal.fire({ icon: 'success', title: 'PAGO!', timer: 2000, showConfirmButton: false });
    <?php endif; ?>
    </script>
</body>
</html>