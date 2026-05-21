<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

// Captura o mês e ano atual (ou selecionado no filtro)
$mes_atual = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano_atual = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

/**
 * QUERY UNIFICADA ELITE OS - CATEGORIZAÇÃO TOTAL
 */
$sql_grafico = "
    SELECT categoria_nome, SUM(valor_total) as total 
    FROM (
        SELECT 
            CASE 
                WHEN categoria IS NOT NULL AND categoria <> '' THEN UPPER(categoria)
                WHEN descricao IS NOT NULL AND descricao <> '' THEN UPPER(descricao)
                ELSE 'DIVERSOS'
            END as categoria_nome, 
            valor as valor_total 
        FROM contas_padaria 
        WHERE MONTH(data_vencimento) = '$mes_atual' 
        AND YEAR(data_vencimento) = '$ano_atual'
        AND status_pago = 1
        AND rascunho = 0
    ) as unificacao
    GROUP BY categoria_nome 
    ORDER BY total DESC";

$res_grafico = $conn->query($sql_grafico);

$categorias = [];
$valores = [];
$total_geral = 0;

if ($res_grafico) {
    while ($row = $res_grafico->fetch_assoc()) {
        $cat_nome = $row['categoria_nome'];
        $categorias[] = mb_strtoupper($cat_nome, 'UTF-8');
        $valores[] = $row['total'];
        $total_geral += $row['total'];
    }
}

$labels_js = json_encode($categorias);
$dados_js = json_encode($valores);

$meses_nomes = array(
    '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr',
    '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
    '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
);
$mes_pt = $meses_nomes[$mes_atual];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Inteligência de Gastos - Elite OS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #d69e88;
            --accent: #1e1a19;
            --white: #ffffff;
            --text-main: #2d3436;
            --text-dark-blue: #2c3e50;
            --text-light: #64748b;
            --border-color: rgba(0,0,0,0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --safe-area-top: env(safe-area-inset-top);
            --safe-area-bottom: env(safe-area-inset-bottom);
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

        /* ESTRUTURA DESKTOP */
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

        .btn-voltar { 
            text-decoration: none; color: var(--text-light); 
            font-weight: 700; font-size: 11px; text-transform: uppercase;
            display: flex; align-items: center; gap: 8px; letter-spacing: 1px;
            transition: var(--transition);
        }
        .btn-voltar:hover { color: var(--accent); }

        .content-body { 
            padding: 30px; 
            overflow-y: auto; 
            flex: 1; 
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 35px;
        }

        .header-title h1 { 
            font-size: 26px; 
            font-weight: 900; 
            color: var(--text-dark-blue);
            text-transform: uppercase;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 25px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .chart-card {
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            text-align: center;
        }

        .chart-container { position: relative; height: 450px; }

        .info-side { display: flex; flex-direction: column; gap: 20px; }

        .summary-card {
            background: var(--text-dark-blue);
            color: white;
            padding: 30px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .summary-card h2 { font-size: 11px; text-transform: uppercase; opacity: 0.7; letter-spacing: 1px; }
        .summary-card p { font-size: 28px; font-weight: 900; margin-top: 10px; }

        .top-list {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
        }

        .top-list h3 { 
            font-size: 13px; 
            font-weight: 800; 
            margin-bottom: 20px; 
            color: var(--text-dark-blue);
            text-transform: uppercase;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .list-item:last-child { border: none; }
        .list-item .name { font-weight: 700; font-size: 13px; color: var(--text-dark-blue); }
        .list-item .val { font-weight: 800; color: #e17055; }

        .filter-select {
            padding: 10px 15px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            font-family: 'Montserrat';
            font-weight: 700;
            font-size: 12px;
            background: #f8fafc;
            color: var(--accent);
            cursor: pointer;
        }

        /* --- AJUSTES EXCLUSIVOS PWA (MOBILE) --- */
        @media (max-width: 1024px) {
            body { display: block; overflow: hidden; position: fixed; width: 100%; height: 100%; }

            .main-wrapper { 
                margin-left: 0 !important; 
                width: 100vw; 
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 15px 15px 0 15px; 
                height: 100vh;
                display: flex;
                flex-direction: column;
                overflow: hidden; 
                padding-top: calc(10px + var(--safe-area-top));
            }

            .header-title { 
                margin-bottom: 15px; 
                flex-shrink: 0; 
                align-items: center;
            }
            .header-title h1 { font-size: 22px; letter-spacing: -1px; }
            .header-title p { display: none; }

            .dashboard-grid { 
                display: flex;
                flex-direction: column;
                gap: 12px;
                flex: 1;
                overflow: hidden;
                padding-bottom: 180px; /* Espaço para o Resumo e a Sidebar fixa */
            }

            .chart-card { 
                height: 300px; 
                padding: 15px; 
                flex-shrink: 0; 
                border-radius: 20px;
            }
            .chart-container { height: 100%; width: 100%; }

            .info-side { 
                flex: 1; 
                overflow: hidden; 
                display: flex;
                flex-direction: column;
                gap: 0;
            }

            /* Resumo flutuante acima da sidebar inferior */
            .summary-card {
                position: fixed;
                bottom: calc(85px + var(--safe-area-bottom)); /* Acima da sidebar inferior */
                left: 15px;
                right: 15px;
                z-index: 100;
                padding: 18px 22px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                border-radius: 18px;
                background: var(--accent);
            }
            .summary-card h2 { font-size: 10px; opacity: 0.6; }
            .summary-card p { margin-top: 0; font-size: 22px; color: var(--primary); }
            .summary-card i { display: none; }

            /* Lista com Scroll Independente */
            .top-list { 
                flex: 1; 
                display: flex; 
                flex-direction: column; 
                overflow: hidden;
                border: none;
                background: transparent;
                padding: 10px 5px;
            }
            .top-list h3 { margin-bottom: 15px; padding-left: 5px; }

            .scroll-itens {
                overflow-y: auto;
                flex: 1;
                padding-bottom: 20px;
                -webkit-overflow-scrolling: touch;
            }
            
            .list-item {
                background: var(--white);
                margin-bottom: 8px;
                padding: 15px;
                border-radius: 12px;
                border: 1px solid var(--border-color);
            }

            .pwa-hide { display: none; }
        }

        @media (min-width: 1025px) {
            .pwa-hide { display: block; }
        }
    </style>
</head>
<body>

    <?php $activePage = 'gastos'; include('sidebar.php'); ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <a href="relatorios.php" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
            <div style="font-weight: 800; font-size: 13px; color: var(--accent); text-transform: uppercase; letter-spacing: 1px;">
                Inteligência de Gastos
            </div>
        </header>

        <main class="content-body">
            <div class="header-title">
                <div>
                    <h1>Análise Mensal</h1>
                    <p class="pwa-hide" style="color: #64748b; font-size: 14px; font-weight: 500;">Visão detalhada de todos os pagamentos.</p>
                </div>
                
                <form method="GET">
                    <select name="mes" class="filter-select" onchange="this.form.submit()">
                        <?php
                        $meses_full = ["01"=>"Janeiro","02"=>"Fevereiro","03"=>"Março","04"=>"Abril","05"=>"Maio","06"=>"Junho","07"=>"Julho","08"=>"Agosto","09"=>"Setembro","10"=>"Outubro","11"=>"Novembro","12"=>"Dezembro"];
                        foreach($meses_full as $num => $nome) {
                            $sel = ($mes_atual == $num) ? 'selected' : '';
                            echo "<option value='$num' $sel>$nome</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <div class="dashboard-grid">
                <section class="chart-card">
                    <div class="chart-container">
                        <canvas id="meuGrafico"></canvas>
                    </div>
                </section>

                <section class="info-side">
                    <div class="summary-card">
                        <div>
                            <h2>Gasto Pago (<?php echo $mes_pt; ?>)</h2>
                            <p>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></p>
                        </div>
                        <i class="fa-solid fa-wallet" style="position:absolute; right:-10px; bottom:-10px; font-size:60px; opacity:0.1;"></i>
                    </div>

                    <div class="top-list">
                        <h3>Categorias</h3>
                        <div class="scroll-itens">
                            <?php
                            if(empty($categorias)) {
                                echo "<div style='text-align:center; padding: 40px 0;'>
                                        <i class='fa-solid fa-receipt' style='font-size: 30px; color: #cbd5e1; margin-bottom: 10px; display: block;'></i>
                                        <p style='font-size:12px; color:#64748b; font-weight: 600;'>Sem dados este mês.</p>
                                      </div>";
                            } else {
                                foreach($categorias as $index => $cat) {
                                    echo "
                                    <div class='list-item'>
                                        <span class='name'>{$cat}</span>
                                        <span class='val'>R$ ".number_format($valores[$index], 2, ',', '.')."</span>
                                    </div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </section>
            </div>
            
            <p class="pwa-hide" style="text-align: center; color: var(--text-light); font-size: 9px; font-weight: 800; margin-top: 40px; text-transform: uppercase; letter-spacing: 2px;">
                Gastos - Pão Da Vida
            </p>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('meuGrafico').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $labels_js; ?>,
                datasets: [{
                    data: <?php echo $dados_js; ?>,
                    backgroundColor: [
                        '#ff7675', '#55efc4', '#74b9ff', '#fdcb6e', '#a29bfe', '#fab1a0', '#00b894', '#636e72', '#ffeaa7', '#d63031', '#0984e3', '#e84393'
                    ],
                    borderWidth: 5,
                    borderColor: '#ffffff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { family: 'Montserrat', size: 10, weight: '700' },
                            color: '#2c3e50',
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: '#2c3e50',
                        padding: 15,
                        titleFont: { size: 14, weight: '800' },
                        callbacks: {
                            label: function(c) {
                                return ' R$ ' + c.parsed.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });

        // Bloqueia pull-to-refresh e scroll indesejado no mobile, permitindo apenas na lista
        document.addEventListener('touchmove', function (e) {
            if (window.innerWidth <= 1024) {
                if (!e.target.closest('.scroll-itens')) {
                    e.preventDefault();
                }
            }
        }, { passive: false });

        // Ajuste para o botão voltar
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.location.href = "relatorios.php"; 
        };
    </script>
</body>
</html>