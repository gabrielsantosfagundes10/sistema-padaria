<?php
include('trava.php');
include('config.php');

/**
 * PROJETO ELITE OS - PADARIA
 * AJUSTE FINAL: Inversão de lógica baseada no dump do banco.
 * Título Principal = Campo 'categoria' (Mandioca, Leite)
 * Badge/Subtítulo = Campo 'descricao' (Saída de Caixa)
 */

$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

$meses_nomes = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];

// SQL buscando os dados da tabela contas_padaria
$sql = "SELECT * FROM contas_padaria 
        WHERE MONTH(data_vencimento) = '$mes' 
        AND YEAR(data_vencimento) = '$ano' 
        AND status_pago = 1 
        AND rascunho = 0
        ORDER BY data_vencimento ASC";
$res = $conn->query($sql);

$sql_soma = "SELECT SUM(valor) as total FROM contas_padaria 
             WHERE MONTH(data_vencimento) = '$mes' 
             AND YEAR(data_vencimento) = '$ano'
             AND status_pago = 1
             AND rascunho = 0";
$soma = $conn->query($sql_soma)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Dívidas - Elite OS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #e74c3c; 
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
            width: 100vw;
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

        .container-lista {
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .btn-voltar { 
            text-decoration: none; color: var(--text-light); 
            font-weight: 700; font-size: 11px; text-transform: uppercase;
            display: flex; align-items: center; gap: 8px; letter-spacing: 1px;
            transition: var(--transition);
        }
        .btn-voltar:hover { color: var(--accent); transform: translateX(-3px); }

        .header-section { margin-bottom: 25px; flex-shrink: 0; }

        .periodo-badge { 
            background: #fff; color: var(--accent); 
            padding: 12px 20px; border-radius: 15px; font-size: 13px; 
            font-weight: 800; margin-bottom: 20px; display: inline-flex;
            align-items: center; gap: 10px; border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            flex-shrink: 0;
        }

        .scroll-lista {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
            -webkit-overflow-scrolling: touch;
        }

        .scroll-lista::-webkit-scrollbar { width: 4px; }
        .scroll-lista::-webkit-scrollbar-track { background: transparent; }
        .scroll-lista::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }

        .card-conta { 
            background: var(--white); 
            border: 1px solid var(--border-color); 
            padding: 20px 25px; border-radius: 20px; 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: var(--transition);
        }

        .info-conta { flex: 1; display: flex; flex-direction: column; }
        
        .info-conta .titulo-item { 
            display: block; 
            font-weight: 800; 
            color: var(--accent); 
            font-size: 19px; 
            text-transform: uppercase; 
            margin-bottom: 4px; 
        }

        .info-conta .data-saida { 
            font-size: 11px; 
            color: var(--text-light); 
            text-transform: uppercase; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
            display: block; 
            margin-bottom: 12px;
        }
        
        .badge-categoria {
            font-size: 10px;
            background: #f1f5f9;
            color: var(--text-light);
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 800;
            display: inline-block;
            border: 1px solid var(--border-color);
            text-transform: uppercase;
        }

        .valor-negativo { font-weight: 900; color: var(--primary); font-size: 20px; white-space: nowrap; }

        .resumo-footer { 
            padding: 25px 35px; 
            background: var(--accent);
            border-radius: 25px;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .resumo-footer span { font-size: 11px; font-weight: 700; text-transform: uppercase; opacity: 0.7; }
        .resumo-footer strong { font-size: 28px; font-weight: 900; }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
            .top-navbar { display: none; }
            
            .content-body { 
                padding: 15px; 
                padding-bottom: 160px; /* Espaço para footer e sidebar */
            }

            .header-section { margin-bottom: 15px; }
            .header-section h1 { font-size: 20px !important; }

            .card-conta { 
                padding: 18px; 
                flex-direction: row; 
                align-items: center;
            }
            .info-conta .titulo-item { font-size: 16px; }
            .valor-negativo { font-size: 17px; }

            .resumo-footer { 
                position: fixed;
                bottom: 85px;
                left: 15px;
                right: 15px;
                z-index: 90;
                padding: 15px 25px; 
                border-radius: 20px;
                width: calc(100% - 30px);
                flex-direction: row;
                text-align: left;
            }
            .resumo-footer strong { font-size: 22px; }
            .resumo-footer span { font-size: 10px; }
            
            /* Remove textos desnecessários no mobile para limpar o PWA */
            .pwa-hide { display: none; }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'contas'; 
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <a href="relatorios.php?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
            <div style="font-weight: 800; font-size: 13px; color: var(--accent); text-transform: uppercase; letter-spacing: 1px;">
                Gestão Financeira
            </div>
        </header>

        <main class="content-body">
            <div class="container-lista">
                
                <div class="header-section">
                    <h1 style="font-size: 24px; font-weight: 900; color: var(--accent);">DÍVIDAS PAGAS</h1>
                    <p class="pwa-hide" style="color: var(--text-light); font-size: 13px;">Relatório detalhado por categoria de saída.</p>
                </div>

                <div class="periodo-badge">
                    <i class="fa-regular fa-calendar-check" style="color: #2ecc71;"></i>
                    <?php echo $meses_nomes[$mes]; ?> / <?php echo $ano; ?>
                </div>

                <div class="scroll-lista">
                    <?php if($res && $res->num_rows > 0): ?>
                        <?php while($c = $res->fetch_assoc()): 
                            $nome_produto = !empty($c['categoria']) ? $c['categoria'] : $c['descricao'];
                            $tipo_saida = !empty($c['categoria']) ? $c['descricao'] : 'GERAL';
                            $tipo_saida = str_ireplace('(Rascunho)', '', $tipo_saida);
                        ?>
                            <div class="card-conta">
                                <div class="info-conta">
                                    <span class="titulo-item">
                                        <?php echo strtoupper($nome_produto); ?>
                                    </span>

                                    <span class="data-saida">
                                        <i class="fa-solid fa-calendar-day" style="color: var(--primary); margin-right: 5px; opacity: 0.7;"></i> 
                                        <?php echo date('d/m/y', strtotime($c['data_vencimento'])); ?>
                                    </span>

                                    <div>
                                        <span class="badge-categoria">
                                            <?php echo strtoupper($tipo_saida); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="valor-negativo">
                                    - R$ <?php echo number_format($c['valor'], 2, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding: 40px 20px; background: #fff; border-radius: 25px; border: 1px dashed var(--border-color);">
                            <i class="fa-solid fa-receipt" style="font-size: 30px; color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p style="color: var(--text-light); font-weight: 600; font-size: 13px;">Nenhum registro encontrado.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="resumo-footer">
                    <div>
                        <span>Total de Saídas</span>
                    </div>
                    <strong>R$ <?php echo number_format($soma['total'] ?? 0, 2, ',', '.'); ?></strong>
                </div>

                <p class="pwa-hide" style="text-align: center; color: var(--text-light); font-size: 9px; font-weight: 800; margin-top: 25px; text-transform: uppercase; letter-spacing: 2px; flex-shrink: 0;">
                    Dívidas - Pão Da Vida
                </p>
            </div>
        </main>
    </div>

</body>
</html>