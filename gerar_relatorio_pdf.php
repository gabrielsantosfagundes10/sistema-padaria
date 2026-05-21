<?php
include('trava.php');
include('config.php');

$mes_selecionado = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano_selecionado = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// 1. Faturamento Bruto Total do Mês
$sql_mes = "SELECT SUM(rendimento_dia) as total FROM fechamentos WHERE MONTH(data_fechamento) = '$mes_selecionado' AND YEAR(data_fechamento) = '$ano_selecionado'";
$res_mes = $conn->query($sql_mes)->fetch_assoc();
$total_mes_bruto = $res_mes['total'] ?? 0;

// 2. Total de Despesas do Mês (para calcular o líquido mensal)
$sql_contas_total = "SELECT SUM(valor) as total_dividas FROM contas_padaria WHERE MONTH(data_vencimento) = '$mes_selecionado' AND YEAR(data_vencimento) = '$ano_selecionado'";
$res_contas_total = $conn->query($sql_contas_total)->fetch_assoc();
$total_despesas_mes = $res_contas_total['total_dividas'] ?? 0;
$total_mes_liquido = $total_mes_bruto - $total_despesas_mes;

// 3. Faturamento Bruto e Líquido por Semana
// Nota: O líquido semanal é calculado subtraindo as contas que venceram naquela semana específica
$sql_semanas = "SELECT 
                    WEEK(f.data_fechamento) as num_semana, 
                    SUM(f.rendimento_dia) as bruto_semana,
                    (SELECT SUM(valor) FROM contas_padaria c WHERE WEEK(c.data_vencimento) = WEEK(f.data_fechamento) AND MONTH(c.data_vencimento) = '$mes_selecionado') as despesa_semana
                FROM fechamentos f
                WHERE MONTH(f.data_fechamento) = '$mes_selecionado' AND YEAR(f.data_fechamento) = '$ano_selecionado'
                GROUP BY num_semana
                ORDER BY num_semana ASC";
$res_semanas = $conn->query($sql_semanas);

$meses_nomes = ['01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril', '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório Contábil - <?php echo $meses_nomes[$mes_selecionado]; ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #2e2e2e; padding: 40px; line-height: 1.4; }
        .folha-contabil { border: 2px solid #555; padding: 25px; }
        
        .topo { text-align: center; border-bottom: 3px double #333; margin-bottom: 30px; padding-bottom: 10px; }
        .topo h1 { text-transform: uppercase; margin: 0; font-size: 26px; letter-spacing: 2px; }

        .secao-titulo { background: #444; color: #fff; padding: 6px 15px; font-weight: bold; margin-top: 30px; text-transform: uppercase; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #999; padding: 12px; text-align: left; }
        th { text-transform: uppercase; font-size: 12px; color: #444; border-bottom: 2px solid #333; }

        .valor { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 16px; }
        
        .resumo-final { margin-top: 40px; border-top: 3px double #333; padding-top: 20px; }
        .caixa-destaque { border: 2px solid #333; padding: 15px; display: inline-block; width: 100%; box-sizing: border-box; background: #fdfdfd; }
        .linha-resumo { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 18px; }

        .carimbo { margin-top: 60px; text-align: center; opacity: 0.5; font-size: 11px; text-transform: uppercase; }

        @media print { 
            .no-print { display: none; } 
            body { padding: 0; }
            .folha-contabil { border: none; }
        }
        .btn-print { background: #333; color: white; border: none; padding: 12px 30px; cursor: pointer; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">IMPRIMIR RELATÓRIO FISCAL</button>
    </div>

    <div class="folha-contabil">
        <div class="topo">
            <h1>Resumo de Resultados - Projeto Padaria</h1>
            <p>Período: <?php echo $meses_nomes[$mes_selecionado]; ?> / <?php echo $ano_selecionado; ?></p>
            <p style="font-size: 11px;">Emitido em: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <div class="secao-titulo">I - Desempenho Semanal (Bruto vs Líquido)</div>
        <table>
            <thead>
                <tr>
                    <th>Referência Semanal</th>
                    <th class="valor">Faturamento Bruto</th>
                    <th class="valor">Resultado Líquido</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $cont = 1;
                while($row = $res_semanas->fetch_assoc()): 
                    $liq_semana = $row['bruto_semana'] - ($row['despesa_semana'] ?? 0);
                ?>
                <tr>
                    <td>Semana <?php echo $cont++; ?> do Mês</td>
                    <td class="valor">R$ <?php echo number_format($row['bruto_semana'], 2, ',', '.'); ?></td>
                    <td class="valor">R$ <?php echo number_format($liq_semana, 2, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="secao-titulo">II - Consolidado Mensal</div>
        <div class="resumo-final">
            <div class="caixa-destaque">
                <div class="linha-resumo">
                    <span>TOTAL FATURAMENTO BRUTO:</span>
                    <span class="valor">R$ <?php echo number_format($total_mes_bruto, 2, ',', '.'); ?></span>
                </div>
                <div class="linha-resumo" style="color: #666; font-size: 16px;">
                    <span>(-) TOTAL DE DESPESAS OPERACIONAIS:</span>
                    <span class="valor">R$ <?php echo number_format($total_despesas_mes, 2, ',', '.'); ?></span>
                </div>
                <div class="linha-resumo" style="border-top: 1px dashed #333; margin-top: 10px; padding-top: 10px; font-weight: bold; font-size: 22px;">
                    <span>LUCRO LÍQUIDO FINAL:</span>
                    <span class="valor">R$ <?php echo number_format($total_mes_liquido, 2, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <div class="carimbo">
            <p>Documento gerado para controle interno de resultados financeiros.</p>
            <p>Assinatura: ___________________________________________________</p>
        </div>
    </div>

</body>
</html>