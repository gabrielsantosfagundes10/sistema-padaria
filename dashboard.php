<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('trava.php');
include('config.php');

// Configuração robusta para Hostinger/Servidores Internacionais
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

$nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Gerente';
$hoje = date('Y-m-d');
$amanha = date('Y-m-d', strtotime('+1 day'));

// --- BUSCA DE DADOS (CORRIGIDA PARA PRECISÃO) ---
// Contagem de encomendas
$enc_hoje = $conn->query("SELECT COUNT(*) as t FROM encomendas WHERE data_entrega = '$hoje'")->fetch_assoc()['t'];
$enc_amanha = $conn->query("SELECT COUNT(*) as t FROM encomendas WHERE data_entrega = '$amanha'")->fetch_assoc()['t'];

// Contagem de contas (Ajustado para garantir que pegue apenas não pagas e datas exatas)
$con_hoje = $conn->query("SELECT COUNT(*) as t FROM contas_padaria WHERE data_vencimento = '$hoje' AND status_pago = 0")->fetch_assoc()['t'];
$con_amanha = $conn->query("SELECT COUNT(*) as t FROM contas_padaria WHERE data_vencimento = '$amanha' AND status_pago = 0")->fetch_assoc()['t'];
$con_atrasadas = $conn->query("SELECT COUNT(*) as t FROM contas_padaria WHERE data_vencimento < '$hoje' AND status_pago = 0")->fetch_assoc()['t'];

// Busca de avisos não lidos no mural
$res_avisos = $conn->query("SELECT COUNT(*) as t FROM avisos WHERE lido = 0");
$novos_avisos = $res_avisos ? $res_avisos->fetch_assoc()['t'] : 0;

// Busca de itens pendentes na lista de compras
$res_compras = $conn->query("SELECT COUNT(*) as t FROM lista_compras WHERE status = 'pendente'");
$itens_compras = $res_compras ? $res_compras->fetch_assoc()['t'] : 0;

// Variável para controle de exibição do "Tudo sob controle"
$tem_alerta = ($con_atrasadas > 0 || $con_hoje > 0 || $con_amanha > 0 || $enc_hoje > 0 || $enc_amanha > 0 || $novos_avisos > 0 || $itens_compras > 0);

// Tradução do mês para Português
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
    <title>Pão Da Vida</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1e1a19">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="icon.png">

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

        .main-layout-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
            min-height: 0;
            height: 100%;
        }

        .panel {
            background: var(--white);
            border-radius: 24px;
            padding: 25px;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            height: 100%;
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-shrink: 0;
        }

        .panel-title {
            font-size: 16px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .alert-scroll {
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
        }

        .alert-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 18px;
            background: #fcfcfc;
            border: 1px solid #f0f0f0;
            margin-bottom: 15px;
            text-decoration: none;
            transition: var(--transition);
        }

        .alert-item:hover {
            border-color: var(--primary);
            background: var(--white);
            transform: translateX(5px);
        }

        .alert-tag {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: inline-block;
        }

        .tag-red { background: #fee2e2; color: #b91c1c; }
        .tag-blue { background: #e0f2fe; color: #0369a1; }
        .tag-orange { background: #ffedd5; color: #9a3412; }
        .tag-green { background: #dcfce7; color: #166534; }
        .tag-purple { background: #f3e8ff; color: #6b21a8; }
        .tag-brown { background: #efeae6; color: #634832; }

        .alert-text b { font-size: 15px; color: var(--accent); display: block; margin-bottom: 2px; }
        .alert-text span { font-size: 13px; color: var(--text-light); font-weight: 500; }

        .data-detail-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
            overflow-y: auto;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px;
            background: #f8fafb;
            border-radius: 18px;
            border: 1px solid transparent;
            transition: var(--transition);
        }

        .data-label {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .data-label i {
            width: 38px;
            height: 38px;
            background: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .data-label span {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
        }

        .data-value {
            font-size: 18px;
            font-weight: 900;
            color: var(--accent);
            background: var(--white);
            padding: 6px 14px;
            border-radius: 10px;
            min-width: 45px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        /* --- MOBILE PWA REFINADO --- */
        @media (max-width: 768px) {
            body { height: 100vh; overflow: hidden; background-color: #f8fafb; }
            .main-wrapper { margin-left: 0 !important; height: 100vh; max-height: 100vh; }
            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 12px; 
                padding-bottom: 85px; /* Espaço para sidebar inferior maior */
                height: 100vh;
                display: flex; 
                flex-direction: column;
                gap: 8px;
                overflow: hidden;
            }

            .main-layout-grid { 
                display: flex; 
                flex-direction: column; 
                gap: 8px; 
                height: 100%; 
                overflow: hidden;
            }

            .panel { 
                flex: 1; 
                padding: 18px; 
                border-radius: 20px; 
                min-height: 0; 
            }

            .panel-header { margin-bottom: 12px; }
            .panel-title { font-size: 14px; font-weight: 900; }

            .alert-item { padding: 12px 15px; margin-bottom: 8px; border-radius: 15px; }
            .alert-item div[style*="font-size: 24px"] { font-size: 20px !important; margin-right: 12px !important; }
            .alert-text b { font-size: 13px; margin-bottom: 3px; }
            .alert-text span { font-size: 11px; }
            .alert-tag { font-size: 8px; padding: 3px 8px; }

            .data-detail-list { gap: 8px; }
            .data-row { padding: 12px 15px; border-radius: 14px; }
            .data-label i { width: 32px; height: 32px; font-size: 14px; }
            .data-label span { font-size: 12px; font-weight: 800; }
            .data-value { font-size: 15px; padding: 4px 10px; min-width: 38px; }

            .pwa-footer-brand { display: none !important; }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .main-wrapper { margin-left: 80px; }
            .main-layout-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'dashboard';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-house"></i> Dashboard Principal
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
            
            <div class="main-layout-grid">
                
                <div class="panel">
                    <div class="panel-header">
                        <h3 class="panel-title"><i class="fa-solid fa-bell" style="color: var(--primary)"></i> Central de Alertas</h3>
                        <button id="btnInstall" style="display:none; background:var(--primary); color:white; border:none; padding:5px 10px; border-radius:8px; font-size:10px; font-weight:800; cursor:pointer;">INSTALAR APP</button>
                    </div>
                    
                    <div class="alert-scroll">
                        
                        <?php if ($novos_avisos > 0): ?>
                            <a href="avisos.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">📢</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-purple">Mural</span>
                                    <b>Novos Comunicados</b>
                                    <span>Existem <?php echo $novos_avisos; ?> novos avisos no mural.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($con_atrasadas > 0): ?>
                            <a href="contas_padaria.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">⚠️</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-red">Financeiro</span>
                                    <b>Contas em Atraso</b>
                                    <span>Você possui <?php echo $con_atrasadas; ?> pendências vencidas.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($enc_hoje > 0): ?>
                            <a href="encomendas.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">🥐</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-blue">Produção</span>
                                    <b>Entregas para Hoje</b>
                                    <span>Existem <?php echo $enc_hoje; ?> encomendas para hoje.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($con_hoje > 0): ?>
                            <a href="contas_padaria.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">💸</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-orange">Financeiro</span>
                                    <b>Vencimentos de Hoje</b>
                                    <span><?php echo $con_hoje; ?> contas vencem hoje.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($enc_amanha > 0): ?>
                            <a href="encomendas.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">📅</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-green">Agendamento</span>
                                    <b>Encomendas para Amanhã</b>
                                    <span>Insumos para <?php echo $enc_amanha; ?> encomendas.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($con_amanha > 0): ?>
                            <a href="contas_padaria.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">🕙</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-blue">Financeiro</span>
                                    <b>Vencimentos de Amanhã</b>
                                    <span>Amanhã vencem <?php echo $con_amanha; ?> contas.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if ($itens_compras > 0): ?>
                            <a href="lista_compras.php" class="alert-item">
                                <div style="margin-right: 18px; font-size: 24px;">🛒</div>
                                <div class="alert-text">
                                    <span class="alert-tag tag-brown">Estoque</span>
                                    <b>Faltando Insumos</b>
                                    <span>Existem <?php echo $itens_compras; ?> itens na lista de compras.</span>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (!$tem_alerta): ?>
                            <div style="text-align: center; padding: 60px 20px;">
                                <i class="fa-solid fa-check-double" style="font-size: 40px; color: #22c55e; opacity: 0.3; margin-bottom: 15px; display: block;"></i>
                                <p style="color: var(--text-light); font-weight: 600;">Tudo em dia!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3 class="panel-title"><i class="fa-solid fa-chart-line" style="color: var(--primary)"></i> Operação</h3>
                    </div>
                    
                    <div class="data-detail-list">
                        <div class="data-row">
                            <div class="data-label">
                                <i class="fa-solid fa-truck-loading" style="color: #0070f3;"></i>
                                <span>Encomendas hoje</span>
                            </div>
                            <div class="data-value"><?php echo $enc_hoje; ?></div>
                        </div>

                        <div class="data-row">
                            <div class="data-label">
                                <i class="fa-solid fa-calendar-check" style="color: #f5a623;"></i>
                                <span>Encomendas amanhã</span>
                            </div>
                            <div class="data-value"><?php echo $enc_amanha; ?></div>
                        </div>

                        <div class="data-row">
                            <div class="data-label">
                                <i class="fa-solid fa-circle-exclamation" style="color: #ff4d4f;"></i>
                                <span>Contas vencidas</span>
                            </div>
                            <div class="data-value"><?php echo $con_atrasadas; ?></div>
                        </div>

                        <div class="data-row">
                            <div class="data-label">
                                <i class="fa-solid fa-money-bill-wave" style="color: #52c41a;"></i>
                                <span>Contas hoje</span>
                            </div>
                            <div class="data-value"><?php echo $con_hoje; ?></div>
                        </div>

                        <div class="data-row">
                            <div class="data-label">
                                <i class="fa-solid fa-basket-shopping" style="color: #d69e88;"></i>
                                <span>Lista Compras</span>
                            </div>
                            <div class="data-value"><?php echo $itens_compras; ?></div>
                        </div>

                        <div class="data-row">
                            <div class="data-label">
                                <i class="fa-solid fa-bullhorn" style="color: #6b21a8;"></i>
                                <span>Novos Avisos</span>
                            </div>
                            <div class="data-value"><?php echo $novos_avisos; ?></div>
                        </div>
                    </div>

                    <div class="pwa-footer-brand" style="margin-top: auto; padding-top: 25px; text-align: center; border-top: 1px solid var(--border-color);">
                         <p style="font-size: 10px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 1.5px;">
                            Padaria &bull; Pão Da Vida
                         </p>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // --- LOGICA DE INSTALAÇÃO PWA NO DASHBOARD ---
        let deferredPrompt;
        const btnInstall = document.getElementById('btnInstall');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            btnInstall.style.display = 'block';
        });

        btnInstall.addEventListener('click', (e) => {
            btnInstall.style.display = 'none';
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                }
                deferredPrompt = null;
            });
        });

        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js').then(function(registration) {
                console.log('SW registrado com sucesso:', registration.scope);
            }, function(err) {
                console.log('Falha no SW:', err);
            });
        }

        // Função do Relógio Sincronizada com o Servidor (PHP Date)
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

        // Lógica de Saída Segura
        let tempoUltimoClique = 0;
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            const agora = new Date().getTime();
            if (agora - tempoUltimoClique < 2000) { 
                window.location.replace('logout.php'); 
            } else {
                tempoUltimoClique = agora;
                window.history.pushState(null, "", window.location.href);
                Swal.fire({ 
                    toast: true, 
                    position: 'bottom', 
                    showConfirmButton: false, 
                    timer: 2000, 
                    background: '#1e1a19', 
                    color: '#fff', 
                    title: 'Pressione outra vez para sair' 
                });
            }
        };
    </script>
</body>
</html>