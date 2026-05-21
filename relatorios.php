<?php
include('trava.php');
include('config.php');

// O session_start() foi removido pois o trava.php já o gerencia.
// Isso corrige o erro visual de "session already active".

// VERIFICAÇÃO DE SEGURANÇA
if (!isset($_SESSION['pode_ver_relatorios']) || $_SESSION['pode_ver_relatorios'] !== true) {
    header("Location: verificar_acesso.php");
    exit;
}

date_default_timezone_set('America/Sao_Paulo');

// --- LÓGICA DE PERSISTÊNCIA ---
$mes_selecionado = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano_selecionado = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// --- CÁLCULO CICLO SEMANAL ---
$inicio_semana = date('Y-m-d', strtotime('last sunday', strtotime(date('Y-m-d') . ' +1 day')));
$fim_semana = date('Y-m-d', strtotime($inicio_semana . ' +6 days'));
$periodo_semana = date('d/m', strtotime($inicio_semana)) . ' a ' . date('d/m', strtotime($fim_semana));

$sql_semana = "SELECT SUM(rendimento_dia) as total FROM fechamentos WHERE data_fechamento BETWEEN '$inicio_semana' AND '$fim_semana'";
$res_semana = $conn->query($sql_semana)->fetch_assoc();
$total_semana = $res_semana['total'] ?? 0;

// --- FATURAMENTO BRUTO MENSAL ---
$sql_mes = "SELECT SUM(rendimento_dia) as total FROM fechamentos WHERE MONTH(data_fechamento) = '$mes_selecionado' AND YEAR(data_fechamento) = '$ano_selecionado'";
$res_mes = $conn->query($sql_mes)->fetch_assoc();
$total_mes = $res_mes['total'] ?? 0;

// --- DÍVIDAS DO MÊS ---
$sql_contas = "SELECT SUM(valor) as total_dividas FROM contas_padaria WHERE MONTH(data_vencimento) = '$mes_selecionado' AND YEAR(data_vencimento) = '$ano_selecionado'";
$res_contas = $conn->query($sql_contas)->fetch_assoc();
$total_dividas = $res_contas['total_dividas'] ?? 0;

$valor_liquido = $total_mes - $total_dividas;

$meses_nomes = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];

$ano_inicio = 2024;
$ano_limite = date('Y') + 1; 
$anos_disponiveis = range($ano_limite, $ano_inicio);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Relatórios - Elite OS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #d69e88;
            --accent: #1e1a19;
            --success: #2ecc71;
            --danger: #e74c3c;
            --white: #ffffff;
            --text-main: #334155;
            --text-light: #64748b;
            --border-color: rgba(0,0,0,0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --safe-area-bottom: env(safe-area-inset-bottom);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* Estrutura Principal */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: var(--transition);
            position: relative;
            will-change: transform;
            backface-visibility: hidden;
        }

        /* Navbar Superior (Apenas Desktop) */
        .top-navbar {
            height: 75px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .content-body { 
            padding: 30px; 
            overflow-y: auto; 
            flex: 1; 
            -webkit-overflow-scrolling: touch;
        }

        /* Filtros */
        .filtro-card {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }

        .grid-filtros { 
            display: grid; 
            grid-template-columns: 2fr 1fr 1fr; 
            gap: 15px; 
            align-items: flex-end;
        }

        select { 
            width: 100%; padding: 12px 15px; border: 1px solid #e2e8f0; 
            background: #f8fafc; color: var(--text-main); border-radius: 12px; 
            font-size: 14px; font-weight: 600; font-family: 'Montserrat';
            appearance: none;
        }

        .btn-filtrar { 
            background: var(--accent); color: white; border: none; 
            padding: 13px; border-radius: 12px; font-weight: 800; 
            text-transform: uppercase; font-size: 12px; cursor: pointer;
            transition: var(--transition);
        }

        .btn-pdf {
            background: #fff; border: 1px solid #e74c3c; color: #e74c3c;
            padding: 8px 15px; border-radius: 10px; font-size: 10px;
            font-weight: 800; text-transform: uppercase; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-pdf:hover { background: #e74c3c; color: #fff; }

        /* Cards de Resultado */
        .grid-cards { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 20px; 
        }

        .card-resultado { 
            padding: 30px; border-radius: 25px; border: 1px solid var(--border-color);
            background: var(--white); text-decoration: none; color: inherit;
            transition: var(--transition); display: flex; flex-direction: column;
            position: relative; overflow: hidden;
        }
        .card-resultado:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }

        .card-semana { border-left: 6px solid #3498db; }
        .card-mes { border-left: 6px solid var(--success); }
        .card-liquido { 
            background: var(--accent); color: #fff;
            border-left: 6px solid var(--primary);
        }
        .card-gastos { border-left: 6px solid var(--danger); }

        .card-resultado span { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-light); display: flex; align-items: center; gap: 8px; }
        .card-liquido span { color: rgba(255,255,255,0.6); }
        .card-resultado strong { font-size: 32px; font-weight: 900; margin: 10px 0; display: block; }
        
        .tag-info { 
            font-size: 10px; font-weight: 700; background: #f1f5f9; 
            padding: 6px 12px; border-radius: 8px; width: fit-content; color: var(--text-main);
        }
        .card-liquido .tag-info { background: rgba(255,255,255,0.1); color: #fff; }

        /* Ajustes Mobile / PWA */
        @media (max-width: 768px) {
            .main-wrapper { 
                margin-left: 0; 
                padding-bottom: calc(90px + var(--safe-area-bottom));
                height: 100vh;
                overflow: hidden;
            }
            .top-navbar { display: none; } 
            
            .content-body { 
                padding: 20px; 
                overflow-y: auto !important; 
                -webkit-overflow-scrolling: touch;
                display: block;
            }
            
            .grid-filtros { grid-template-columns: 1fr; gap: 10px; }
            .grid-cards { grid-template-columns: 1fr; }
            
            .filtro-card { padding: 15px; border-radius: 15px; }
            
            .card-resultado { padding: 20px; border-radius: 20px; }
            .card-resultado strong { font-size: 24px; }

            .pwa-subtitle, .pwa-footer { display: none !important; }

            .pdf-container-mobile {
                display: block !important;
                margin-top: 5px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'relatorios'; 
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-chart-line"></i> Inteligência de Dados
            </div>
            
            <a href="gerar_relatorio_pdf.php?mes=<?php echo $mes_selecionado; ?>&ano=<?php echo $ano_selecionado; ?>" target="_blank" class="btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </a>
        </header>

        <main class="content-body">
            <div style="margin-bottom: 25px;">
                <h1 style="font-size: 26px; font-weight: 900; color: var(--accent); text-transform: uppercase;">Relatórios</h1>
                <p class="pwa-subtitle" style="color: var(--text-light); font-size: 14px; font-weight: 500;">Visão geral do faturamento e saúde financeira.</p>
                
                <div class="pdf-container-mobile" style="display: none;">
                     <a href="gerar_relatorio_pdf.php?mes=<?php echo $mes_selecionado; ?>&ano=<?php echo $ano_selecionado; ?>" target="_blank" class="btn-pdf" style="width: 100%; justify-content: center; padding: 12px; font-size: 11px;">
                        <i class="fa-solid fa-file-pdf"></i> GERAR PDF DO MÊS
                    </a>
                </div>
            </div>

            <div class="filtro-card">
                <form method="GET">
                    <div class="grid-filtros">
                        <div>
                            <label style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--text-light); display: block; margin-bottom: 8px;">Mês de Referência</label>
                            <select name="mes">
                                <?php foreach($meses_nomes as $num => $nome): ?>
                                    <option value="<?php echo $num; ?>" <?php echo ($mes_selecionado == $num) ? 'selected' : ''; ?>>
                                        <?php echo $nome; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--text-light); display: block; margin-bottom: 8px;">Ano</label>
                            <select name="ano">
                                <?php foreach($anos_disponiveis as $ano): ?>
                                    <option value="<?php echo $ano; ?>" <?php echo ($ano_selecionado == $ano) ? 'selected' : ''; ?>>
                                        <?php echo $ano; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-filtrar">Atualizar Dados</button>
                    </div>
                </form>
            </div>

            <div class="grid-cards">
                <a href="detalhes_semana.php" class="card-resultado card-semana">
                    <span>Faturamento Semanal</span>
                    <strong>R$ <?php echo number_format($total_semana, 2, ',', '.'); ?></strong>
                    <div class="tag-info"><i class="fa-regular fa-calendar"></i> <?php echo $periodo_semana; ?></div>
                </a>

                <a href="detalhes_mes.php?mes=<?php echo $mes_selecionado; ?>&ano=<?php echo $ano_selecionado; ?>" class="card-resultado card-mes">
                    <span>Faturamento Bruto</span>
                    <strong>R$ <?php echo number_format($total_mes, 2, ',', '.'); ?></strong>
                    <div class="tag-info"><i class="fa-solid fa-arrow-up-right-dots"></i> <?php echo $meses_nomes[$mes_selecionado]; ?></div>
                </a>

                <a href="detalhes_contas_mes.php?mes=<?php echo $mes_selecionado; ?>&ano=<?php echo $ano_selecionado; ?>" class="card-resultado card-liquido">
                    <span>Lucro Líquido Real</span>
                    <strong>R$ <?php echo number_format($valor_liquido, 2, ',', '.'); ?></strong>
                    <div class="tag-info">
                        <i class="fa-solid fa-receipt"></i> Pós Despesas
                    </div>
                </a>

                <a href="gastos.php" class="card-resultado card-gastos">
                    <span>Análise de Gastos</span>
                    <strong style="color: var(--danger);"><i class="fa-solid fa-chart-pie"></i></strong>
                    <div class="tag-info"><i class="fa-solid fa-magnifying-glass-chart"></i> Detalhar Custos</div>
                </a>
            </div>

            <p class="pwa-footer" style="text-align: center; color: var(--text-light); font-size: 10px; font-weight: 800; margin-top: 40px; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 20px;">
                Área Restrita - Pão Da Vida
            </p>
        </main>
    </div>

    <script>
        // Ajuste dinâmico para garantir scroll no mobile
        function adjustScroll() {
            if (window.innerWidth <= 768) {
                document.querySelector('.content-body').style.height = (window.innerHeight - 20) + 'px';
            }
        }
        window.addEventListener('resize', adjustScroll);
        window.addEventListener('load', adjustScroll);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js').then(function() {
                console.log('Service Worker Registered');
            });
        }
    </script>
</body>
</html>