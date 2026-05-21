<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

$id = $_GET['id'];

// 1. CAPTURA DAS DATAS PARA RETORNO AO RELATÓRIO
$inicio_voltar = $_GET['inicio'] ?? '';
$fim_voltar = $_GET['fim'] ?? '';

$dados = $conn->query("SELECT * FROM fechamentos WHERE id = '$id'")->fetch_assoc();

// 2. SEGURANÇA: Datas para o botão voltar
if (empty($inicio_voltar) || empty($fim_voltar)) {
    $data_ref = new DateTime($dados['data_fechamento']);
    $w = $data_ref->format('w'); 
    $temp_inicio = clone $data_ref;
    $inicio_voltar = $temp_inicio->modify("-" . $w . " days")->format('Y-m-d');
    $temp_fim = clone $temp_inicio;
    $fim_voltar = $temp_fim->modify("+6 days")->format('Y-m-d');
}

// 3. LÓGICA DE ATUALIZAÇÃO
if (isset($_POST['atualizar'])) {
    $data = $_POST['data_fechamento'];
    $inicial = floatval($_POST['valor_inicial_dinheiro']);
    $final = floatval($_POST['valor_final_dinheiro']);
    $maquininha = floatval($_POST['valor_maquininha']);
    
    $saidas = floatval($dados['saídas']);
    $retiradas = floatval($dados['retiradas']);

    $rendimento = ($final + $saidas + $retiradas - $inicial) + $maquininha;
    $saldo_prox_dia = $final;

    $stmt = $conn->prepare("UPDATE fechamentos SET 
            data_fechamento = ?, 
            valor_inicial_dinheiro = ?, 
            valor_final_dinheiro = ?, 
            valor_maquininha = ?, 
            rendimento_dia = ?,
            saldo_prox_dia = ?
            WHERE id = ?");
    
    $stmt->bind_param("sdddddi", $data, $inicial, $final, $maquininha, $rendimento, $saldo_prox_dia, $id);

    if ($stmt->execute()) {
        header("Location: detalhes_semana.php?inicio=$inicio_voltar&fim=$fim_voltar&sucesso_edit=1");
        exit();
    }
}

$mes_pt = array('Jan'=>'Jan','Feb'=>'Fev','Mar'=>'Mar','Apr'=>'Abr','May'=>'Mai','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Set','Oct'=>'Out','Nov'=>'Nov','Dec'=>'Dez')[date('M')];
$link_cancelar = "detalhes_semana.php?inicio=$inicio_voltar&fim=$fim_voltar";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Editar Fechamento - Elite OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #d69e88;
            --accent: #1e1a19;
            --success: #27ae60;
            --danger: #c0392b;
            --white: #ffffff;
            --text-main: #334155;
            --text-light: #64748b;
            --border-color: rgba(0,0,0,0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --safe-area-bottom: env(safe-area-inset-bottom);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

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
            padding: 25px; 
            overflow-y: auto; 
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            -webkit-overflow-scrolling: touch;
        }

        .fechamento-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            flex-shrink: 0;
        }

        .form-card {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
        }

        .section-label {
            font-size: 11px;
            font-weight: 900;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 1px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .grid-inputs { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); 
            gap: 15px; 
            margin-bottom: 15px; 
        }

        label { display: block; font-size: 10px; color: var(--text-light); font-weight: 700; margin-bottom: 5px; text-transform: uppercase; }

        input { 
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; 
            background: #f8fafc; border-radius: 10px; font-size: 14px; font-weight: 600; font-family: 'Montserrat';
        }

        input:focus { border-color: var(--primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(214, 158, 136, 0.1); }

        .btn-salvar { 
            background: var(--accent); color: white; border: none; 
            padding: 18px; width: 100%; border-radius: 15px; font-weight: 900; 
            font-size: 13px; text-transform: uppercase; cursor: pointer;
            transition: var(--transition); margin-top: 10px;
        }
        .btn-salvar:hover { background: #332d2b; transform: translateY(-2px); }

        .btn-cancelar {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info-fixa {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            border: 1px dashed var(--primary);
            margin-bottom: 10px;
        }

        /* --- AJUSTES EXCLUSIVOS PWA / MOBILE --- */
        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0; }
            
            /* Remove topbar no PWA */
            .top-navbar { display: none !important; }

            .content-body {
                padding: 15px;
                /* Espaço para não bater na sidebar inferior */
                padding-bottom: calc(100px + var(--safe-area-bottom)); 
            }

            .fechamento-container { 
                grid-template-columns: 1fr; 
                gap: 15px;
            }

            .form-card { padding: 20px; }

            /* Ajuste de inputs no mobile para não quebrar */
            .grid-inputs { grid-template-columns: 1fr; }

            /* Estilo dos inputs no toque */
            input { font-size: 16px; padding: 15px; }

            .btn-salvar { padding: 20px; font-size: 14px; }
        }

        /* Correção de bug de tela branca e scroll */
        .content-body {
            height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'relatorios'; 
    // A sidebar.php já deve estar configurada para ir para o bottom no mobile conforme suas instruções anteriores
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-pen-to-square"></i> Elite OS - Editar Registro #<?php echo $id; ?>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="clock-display" style="font-weight: 900; color: var(--accent); font-size: 20px;">MODO EDIÇÃO</div>
            </div>
        </header>

        <main class="content-body">
            
            <div class="fechamento-container">
                <div class="form-card">
                    <div class="section-label">Ajustar Valores de Entrada</div>
                    
                    <form method="POST">
                        <div class="grid-inputs">
                            <div>
                                <label>Data do Caixa</label>
                                <input type="date" name="data_fechamento" value="<?php echo $dados['data_fechamento']; ?>" required>
                            </div>
                            <div>
                                <label>Fundo Inicial</label>
                                <input type="number" step="0.01" name="valor_inicial_dinheiro" value="<?php echo $dados['valor_inicial_dinheiro']; ?>" style="border-left: 4px solid var(--primary);">
                            </div>
                            <div>
                                <label>Dinheiro na Gaveta</label>
                                <input type="number" step="0.01" name="valor_final_dinheiro" value="<?php echo $dados['valor_final_dinheiro']; ?>">
                            </div>
                        </div>

                        <div class="grid-inputs">
                            <div>
                                <label>Total Cartões / PIX</label>
                                <input type="number" step="0.01" name="valor_maquininha" value="<?php echo $dados['valor_maquininha']; ?>">
                            </div>
                        </div>

                        <button type="submit" name="atualizar" class="btn-salvar">Salvar e Recalcular</button>
                        
                        <a href="<?php echo $link_cancelar; ?>" class="btn-cancelar">
                            <i class="fa-solid fa-arrow-left"></i> Voltar sem alterar
                        </a>
                    </form>
                </div>

                <div class="form-card">
                    <div class="section-label">Dados Fixos de Saída</div>
                    
                    <div class="info-fixa">
                        <label>Gastos Lançados (Saídas)</label>
                        <div style="font-size: 18px; font-weight: 900; color: var(--danger);">
                            R$ <?php echo number_format($dados['saídas'], 2, ',', '.'); ?>
                        </div>
                    </div>

                    <div class="info-fixa">
                        <label>Sangrias / Retiradas</label>
                        <div style="font-size: 18px; font-weight: 900; color: var(--danger);">
                            R$ <?php echo number_format($dados['retiradas'], 2, ',', '.'); ?>
                        </div>
                    </div>

                    <p style="font-size: 10px; color: var(--text-light); font-weight: 600; margin-top: 15px; line-height: 1.4;">
                        * Valores consolidados no fechamento original. Alterações aqui impactam o rendimento final automaticamente.
                    </p>
                </div>
            </div>

            <div id="pwa-settings" style="display:none;"></div>
        </main>
    </div>

    <script>
        // Prevenir o comportamento de "voltar" do navegador para não perder dados, 
        // mas o link 'Voltar' funciona normalmente para a tela anterior.
        window.addEventListener('load', () => {
            // Ajuste rápido para evitar bugs de viewport em teclados mobile
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    document.querySelector('.content-body').style.paddingBottom = '300px';
                });
                input.addEventListener('blur', () => {
                    document.querySelector('.content-body').style.paddingBottom = '100px';
                });
            });
        });
    </script>
</body>
</html>