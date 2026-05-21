<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

$hoje = date('Y-m-d'); 
$amanha = date('Y-m-d', strtotime('+1 day'));

$sql = "SELECT * FROM encomendas WHERE data_entrega >= '$hoje' ORDER BY data_entrega ASC, horario ASC";
$result = $conn->query($sql);

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
    <title>Encomendas - PãoDaVida</title>
    
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
            --text-soft: #94a3b8;
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

        /* --- CONTEÚDO --- */
        .content-body { 
            flex: 1;
            padding: 30px; 
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-add-new {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(214, 158, 136, 0.3);
        }
        .btn-add-new:hover { transform: translateY(-2px); background: #b87d66; }

        .lista-encomendas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            padding-bottom: 20px;
        }

        .card-encomenda { 
            background: var(--white); 
            padding: 25px; 
            border-radius: 20px; 
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
            border-left: 10px solid #cbd5e0;
            transition: var(--transition);
        }

        .card-encomenda:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }

        .badge-data {
            position: absolute; top: 0; right: 0;
            padding: 6px 15px; font-size: 10px; font-weight: 900;
            color: white; border-bottom-left-radius: 15px; border-top-right-radius: 18px;
        }

        @keyframes pulsar {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .hoje { border-left-color: #ef4444 !important; animation: pulsar 2s infinite; }
        .hoje .badge-data { background: #ef4444; }

        .amanha { border-left-color: #f59e0b !important; }
        .amanha .badge-data { background: #f59e0b; }

        .cli-nome { font-size: 1.1rem; font-weight: 800; color: var(--accent); text-transform: uppercase; margin-bottom: 5px; display: block; }
        .cli-tel { font-size: 0.85rem; color: var(--text-light); font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 10px; }
        
        .status { font-size: 9px; padding: 5px 12px; border-radius: 50px; font-weight: 800; text-transform: uppercase; display: inline-block; margin-bottom: 15px; }
        .status-pago { background: #dcfce7; color: #15803d; }
        .status-pendente { background: #fee2e2; color: #b91c1c; }
        .status-metade { background: #fef3c7; color: #b45309; }

        .entrega-info { 
            background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #f1f5f9;
        }
        .tipo-pedido { color: #b45309; font-weight: 800; text-transform: uppercase; font-size: 13px; display: block; margin-top: 5px; }

        .valor-tag { font-size: 1.1rem; font-weight: 800; color: var(--accent); }

        .btn-admin { display: flex; gap: 15px; margin-top: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .btn-acao { text-decoration: none; font-size: 11px; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; gap: 5px; }
        .btn-edit { color: #3b82f6; }
        .btn-del { color: #ef4444; }

        /* --- AJUSTES EXCLUSIVOS PWA (MOBILE) --- */
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

            .header-title { 
                padding: 20px 15px;
                padding-top: calc(20px + env(safe-area-inset-top));
                margin-bottom: 0;
                background: var(--bg-body);
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                flex-shrink: 0;
                border-bottom: 1px solid var(--border-color);
            }
            
            .header-title h1 { 
                font-size: 22px !important; 
                font-weight: 900 !important;
                color: var(--accent) !important;
            }
            .header-title p { display: none !important; } 

            /* Estilo padronizado com a tela de Avisos */
            .btn-add-new { 
                width: 100%; 
                justify-content: center;
                padding: 16px;
                font-size: 11px;
                background: var(--accent) !important; /* Cor idêntica aos avisos */
                color: var(--white) !important;
                font-family: 'Montserrat', sans-serif !important;
                font-weight: 800 !important;
                letter-spacing: 0.5px;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }

            .lista-encomendas {
                flex: 1;
                overflow-y: auto;
                padding: 15px;
                grid-template-columns: 1fr;
                gap: 15px;
                padding-bottom: calc(100px + env(safe-area-inset-bottom)); 
                -webkit-overflow-scrolling: touch;
                display: flex; 
                flex-direction: column;
            }

            .card-encomenda { padding: 20px; border-radius: 18px; }
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
                <i class="fa-solid fa-bread-slice"></i> Gestão de Encomendas
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
                    <h1 style="font-size: 24px; font-weight: 800;">Lista de Encomendas</h1>
                    <p style="color: var(--text-light); font-size: 14px;">Pedidos pendentes e futuros registrados.</p>
                </div>
                <a href="nova_encomenda.php" class="btn-add-new">
                    <i class="fa-solid fa-plus"></i> NOVA ENCOMENDA
                </a>
            </div>

            <div class="lista-encomendas">
                <?php if ($result->num_rows == 0): ?>
                    <div style="text-align: center; padding: 40px; background: white; border-radius: 20px; border: 1px solid var(--border-color);">
                        <p style="font-weight: 700; color: var(--text-light);">Nenhuma encomenda encontrada.</p>
                    </div>
                <?php endif; ?>

                <?php while($en = $result->fetch_assoc()): 
                    $classe_alerta = '';
                    $texto_badge = '';
                    if ($en['data_entrega'] == $hoje) { $classe_alerta = 'hoje'; $texto_badge = 'HOJE'; }
                    elseif ($en['data_entrega'] == $amanha) { $classe_alerta = 'amanha'; $texto_badge = 'AMANHÃ'; }

                    $st = strtolower($en['status_pagamento']);
                    $classe_status = 'status-pendente';
                    if (strpos($st, 'pago') !== false && strpos($st, 'r$') === false) $classe_status = 'status-pago';
                    elseif (strpos($st, 'metade') !== false) $classe_status = 'status-metade';
                ?>
                    <div class="card-encomenda <?php echo $classe_alerta; ?>">
                        <?php if($texto_badge): ?>
                            <div class="badge-data"><?php echo $texto_badge; ?></div>
                        <?php endif; ?>

                        <span class="cli-nome"><?php echo htmlspecialchars($en['nome_cliente']); ?></span>
                        
                        <?php if(!empty($en['telefone_cliente'])): ?>
                            <a href="tel:<?php echo $en['telefone_cliente']; ?>" class="cli-tel">
                                <i class="fa-solid fa-phone"></i> <?php echo $en['telefone_cliente']; ?>
                            </a>
                        <?php endif; ?>

                        <span class="status <?php echo $classe_status; ?>"><?php echo $en['status_pagamento']; ?></span>

                        <div class="entrega-info">
                            <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">
                                <i class="fa-solid fa-clock" style="color: var(--primary);"></i> 
                                <?php echo date('d/m', strtotime($en['data_entrega'])); ?>
                                <?php if($en['horario']) echo " às " . substr($en['horario'], 0, 5); ?>
                            </div>
                            <span class="tipo-pedido"><?php echo $en['tipo']; ?></span>
                            <?php if(!empty($en['sabor_detalhes'])): ?>
                                <div style="font-size: 12px; margin-top: 8px; color: var(--text-light); line-height: 1.4;">
                                    <strong>Obs:</strong> <?php echo $en['sabor_detalhes']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="valor-tag">
                            <span style="font-size: 10px; color: var(--text-light); display: block;">VALOR TOTAL</span>
                            <?php echo ($en['valor'] > 0) ? 'R$ '.number_format($en['valor'], 2, ',', '.') : '<span style="color:#f59e0b;">A calcular</span>'; ?>
                        </div>

                        <div class="btn-admin">
                            <a href="editar_encomenda.php?id=<?php echo $en['id']; ?>" class="btn-acao btn-edit">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="#" onclick="confirmarExclusao(<?php echo $en['id']; ?>)" class="btn-acao btn-del">
                                <i class="fa-solid fa-trash"></i> Excluir
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
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

    function confirmarExclusao(id) {
        Swal.fire({
            title: 'EXCLUIR?',
            text: "Remover esta encomenda permanentemente?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#334155',
            confirmButtonText: 'SIM, EXCLUIR',
            cancelButtonText: 'CANCELAR'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'excluir_encomenda.php?id=' + id; } })
    }
    </script>
</body>
</html>