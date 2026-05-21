<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

$meses_nomes = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];

function getSemanasDoMes($mes, $ano) {
    $semanas = [];
    $primeiro_dia = new DateTime("$ano-$mes-01");
    $ultimo_dia = new DateTime("$ano-$mes-01");
    $ultimo_dia->modify('last day of this month');

    $atual = clone $primeiro_dia;
    
    while ($atual <= $ultimo_dia) {
        $inicio_semana = clone $atual;
        if ($inicio_semana->format('w') != 0) {
            $inicio_semana->modify('last sunday');
        }
        
        $fim_semana = clone $inicio_semana;
        $fim_semana->modify('+6 days');
        
        $chave = $inicio_semana->format('Y-m-d') . '|' . $fim_semana->format('Y-m-d');
        if (!isset($semanas[$chave])) {
            $semanas[$chave] = [
                'inicio' => $inicio_semana->format('Y-m-d'),
                'fim' => $fim_semana->format('Y-m-d'),
                'label' => $inicio_semana->format('d/m') . ' a ' . $fim_semana->format('d/m')
            ];
        }
        $atual->modify('+1 week');
    }
    return $semanas;
}

$lista_semanas = getSemanasDoMes($mes, $ano);
$labels_grafico = [];
$dados_grafico = [];
$total_acumulado = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Desempenho Mensal - Elite OS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #d69e88;
            --accent: #1e1a19;
            --success: #2ecc71;
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
            width: 100vw;
            overflow: hidden;
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
            transition: var(--transition);
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

        .content-body { 
            padding: 30px; 
            flex: 1; 
            display: flex; 
            flex-direction: column;
            overflow: hidden; 
        }

        .container-central {
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .btn-voltar { 
            text-decoration: none; color: var(--text-light); 
            font-weight: 700; font-size: 12px; text-transform: uppercase;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-voltar:hover { color: var(--accent); }

        .chart-card {
            background: var(--white);
            padding: 20px;
            border-radius: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            height: 260px;
            flex-shrink: 0;
        }

        .semanas-scroll-area {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 15px;
            -webkit-overflow-scrolling: touch;
        }

        .semanas-scroll-area::-webkit-scrollbar { width: 4px; }
        .semanas-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .semanas-scroll-area::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }

        .semana-card { 
            background: var(--white); 
            padding: 18px 25px; 
            border-radius: 20px; 
            margin-bottom: 10px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        .semana-card:hover { transform: translateX(5px); border-color: var(--primary); }

        .info-txt .label-sm { display:block; font-size: 10px; color: var(--text-light); text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
        .info-txt .periodo { font-size: 15px; color: var(--accent); font-weight: 700; margin-top: 2px; display: block; }
        
        .valor-sem { font-size: 17px; font-weight: 900; color: var(--success); }

        .resumo-mensal { 
            padding: 20px 30px; 
            background: var(--accent);
            border-radius: 25px;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .resumo-mensal span { font-size: 11px; font-weight: 700; text-transform: uppercase; opacity: 0.7; }
        .resumo-mensal strong { font-size: 24px; font-weight: 900; }

        /* Responsividade PWA */
        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
            .top-navbar { display: none; }
            
            .content-body { 
                padding: 15px; 
                padding-bottom: 160px; /* Espaço para resumo e sidebar inferior */
            }
            
            .chart-card { height: 200px; }

            .resumo-mensal { 
                position: fixed;
                bottom: 85px; 
                left: 15px;
                right: 15px;
                z-index: 90;
                padding: 15px 20px; 
                border-radius: 20px;
                width: calc(100% - 30px);
            }
            .resumo-mensal strong { font-size: 20px; }
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
            <a href="relatorios.php?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
            <div style="font-weight: 800; font-size: 13px; color: var(--accent); text-transform: uppercase; letter-spacing: 1px;">
                Mensal: <?php echo $meses_nomes[$mes]; ?> / <?php echo $ano; ?>
            </div>
        </header>

        <main class="content-body">
            <div class="container-central">
                
                <div style="margin-bottom: 20px; flex-shrink: 0;">
                    <h1 style="font-size: 22px; font-weight: 900; color: var(--accent);">DESEMPENHO MENSAL</h1>
                </div>

                <div class="chart-card">
                    <canvas id="graficoSemanas"></canvas>
                </div>

                <div class="semanas-scroll-area">
                    <?php 
                    $cont = 1;
                    $labels_grafico = [];
                    $dados_grafico = [];
                    $total_acumulado = 0;

                    foreach ($lista_semanas as $s): 
                        $inicio = $s['inicio'];
                        $fim = $s['fim'];
                        
                        $sql = "SELECT SUM(rendimento_dia) as total FROM fechamentos WHERE data_fechamento BETWEEN '$inicio' AND '$fim'";
                        $res = $conn->query($sql)->fetch_assoc();
                        $total_sem = $res['total'] ?? 0;
                        
                        $labels_grafico[] = "Sem. " . $cont;
                        $dados_grafico[] = (float)$total_sem;
                        $total_acumulado += $total_sem;
                    ?>
                        <div class="semana-card">
                            <div class="info-txt">
                                <span class="label-sm">Semana <?php echo $cont; ?></span>
                                <span class="periodo">
                                    <i class="fa-regular fa-calendar-check" style="color: var(--primary); margin-right: 5px;"></i> 
                                    <?php echo date('d/m', strtotime($inicio)); ?> a <?php echo date('d/m', strtotime($fim)); ?>
                                </span>
                            </div>
                            <div class="valor-sem">
                                R$ <?php echo number_format($total_sem, 2, ',', '.'); ?>
                            </div>
                        </div>
                    <?php 
                        $cont++;
                    endforeach; ?>
                </div>

                <div class="resumo-mensal">
                    <div>
                        <span>Total Acumulado</span>
                    </div>
                    <strong>R$ <?php echo number_format($total_acumulado, 2, ',', '.'); ?></strong>
                </div>

            </div>
        </main>
    </div>

<script>
const ctx = document.getElementById('graficoSemanas').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_grafico); ?>,
        datasets: [{
            label: 'Rendimento',
            data: <?php echo json_encode($dados_grafico); ?>,
            backgroundColor: 'rgba(214, 158, 136, 0.5)',
            borderColor: '#d69e88',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: '#d69e88',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e1a19',
                titleFont: { family: 'Montserrat', size: 12, weight: 'bold' },
                bodyFont: { family: 'Montserrat', size: 11 },
                padding: 10,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return ' R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.02)', drawBorder: false },
                ticks: {
                    color: '#64748b',
                    font: { family: 'Montserrat', size: 9 },
                    callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); }
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: '#334155',
                    font: { family: 'Montserrat', size: 10, weight: '700' }
                }
            }
        }
    }
});

// Correção para evitar bug de tela branca no scroll
document.querySelector('.semanas-scroll-area').addEventListener('scroll', function() {}, {passive: true});
</script>

</body>
</html>