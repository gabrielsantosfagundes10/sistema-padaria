<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

// Lógica de datas para o filtro
$inicio = $_GET['inicio'] ?? date('Y-m-d', strtotime('last sunday'));
$fim = $_GET['fim'] ?? date('Y-m-d', strtotime('next saturday'));

// Busca os registros do período
$sql = "SELECT * FROM fechamentos WHERE data_fechamento BETWEEN '$inicio' AND '$fim' ORDER BY data_fechamento DESC";
$res = $conn->query($sql);

// Soma o total do período
$sql_total = "SELECT SUM(rendimento_dia) as total_geral FROM fechamentos WHERE data_fechamento BETWEEN '$inicio' AND '$fim'";
$res_total = $conn->query($sql_total)->fetch_assoc();
$total_da_semana = $res_total['total_geral'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Detalhes da Semana - Elite OS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
            transition: var(--transition);
        }

        /* Top Navbar - Padronizada */
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
        .btn-voltar:hover { color: var(--accent); transform: translateX(-3px); }

        /* Conteúdo principal */
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

        .header-section { margin-bottom: 25px; flex-shrink: 0; }

        .periodo-badge { 
            background: #fff; color: var(--accent); 
            padding: 12px 20px; border-radius: 15px; font-size: 13px; 
            font-weight: 800; margin-bottom: 20px; display: inline-flex;
            align-items: center; gap: 10px; border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            flex-shrink: 0;
        }

        /* Área de Scroll Interno */
        .lista-fechamentos-scroll {
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
            margin-bottom: 20px;
            -webkit-overflow-scrolling: touch;
        }

        .lista-fechamentos-scroll::-webkit-scrollbar { width: 4px; }
        .lista-fechamentos-scroll::-webkit-scrollbar-track { background: transparent; }
        .lista-fechamentos-scroll::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }

        .card-dia { 
            background: var(--white); 
            border: 1px solid var(--border-color); 
            padding: 20px 25px; border-radius: 20px; 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: var(--transition);
        }

        .info-dia .data { display: block; font-weight: 800; color: var(--accent); font-size: 18px; }
        .info-dia .semana { font-size: 10px; color: var(--text-light); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }

        .valor-lucro { font-weight: 900; color: var(--success); font-size: 20px; text-align: right; }

        .acoes { display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end; }
        
        .btn-acao { 
            width: 38px; height: 38px; border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; 
            text-decoration: none; border: none; cursor: pointer; font-size: 14px;
            transition: var(--transition);
        }
        .btn-editar { background: #f1f5f9; color: #3498db; }
        .btn-excluir { background: #fcf1f0; color: #e74c3c; }

        /* Rodapé de Resumo */
        .resumo-footer { 
            padding: 20px 35px; 
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
        .resumo-footer strong { font-size: 26px; font-weight: 900; }

        /* AJUSTES MOBILE / PWA (NÃO AFETA DESKTOP) */
        @media (max-width: 768px) {
            .main-wrapper { 
                margin-left: 0 !important; 
                height: 100vh;
            }

            .top-navbar { display: none; }
            
            .content-body { 
                padding: 15px !important; 
                padding-bottom: calc(180px + var(--safe-area-bottom)) !important;
            }
            
            .header-section { margin-top: 10px; }
            .header-section p { display: none !important; } 
            
            .lista-fechamentos-scroll {
                margin-bottom: 10px;
            }

            .card-dia { 
                padding: 15px !important; 
                border-radius: 15px;
            }
            
            .info-dia .data { font-size: 16px; }
            .valor-lucro { font-size: 18px; }

            .resumo-footer { 
                position: fixed;
                bottom: calc(85px + var(--safe-area-bottom)); 
                left: 15px;
                right: 15px;
                z-index: 90;
                padding: 15px 25px; 
                border-radius: 18px;
                width: calc(100% - 30px);
                flex-shrink: 0;
            }
            .resumo-footer strong { font-size: 22px; }
            .pwa-footer-info { display: none !important; }
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
            <a href="gastos.php" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
            <div style="font-weight: 800; font-size: 13px; color: var(--accent); text-transform: uppercase; letter-spacing: 1px;">
                Gestão Financeira
            </div>
        </header>

        <main class="content-body">
            <div class="container-lista">
                
                <div class="header-section">
                    <h1 style="font-size: 22px; font-weight: 900; color: var(--accent); text-transform: uppercase;">Histórico Semanal</h1>
                    <p style="color: var(--text-light); font-size: 13px;">Confira e gerencie os fechamentos realizados.</p>
                </div>

                <div class="periodo-badge">
                    <i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i>
                    <?php echo date('d/m/Y', strtotime($inicio)); ?> — <?php echo date('d/m/Y', strtotime($fim)); ?>
                </div>

                <div class="lista-fechamentos-scroll" id="scrollContainer">
                    <?php if($res->num_rows > 0): ?>
                        <?php while($f = $res->fetch_assoc()): 
                            $dia_semana_num = date('w', strtotime($f['data_fechamento']));
                            $nomes_dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
                        ?>
                            <div class="card-dia">
                                <div class="info-dia">
                                    <span class="semana"><?php echo $nomes_dias[$dia_semana_num]; ?></span>
                                    <span class="data"><?php echo date('d/m/Y', strtotime($f['data_fechamento'])); ?></span>
                                </div>
                                <div class="valores">
                                    <div class="valor-lucro">R$ <?php echo number_format($f['rendimento_dia'], 2, ',', '.'); ?></div>
                                    <div class="acoes">
                                        <a href="editar_fechamento.php?id=<?php echo $f['id']; ?>" class="btn-acao btn-editar" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button onclick="confirmarExclusao(<?php echo $f['id']; ?>)" class="btn-acao btn-excluir" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding: 50px 0; background: #fff; border-radius: 25px; border: 1px dashed var(--border-color);">
                            <i class="fa-solid fa-calendar-xmark" style="font-size: 35px; color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p style="color: var(--text-light); font-weight: 600; font-size: 14px;">Sem registros para este período.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="resumo-footer">
                    <span>Total do Período</span>
                    <strong>R$ <?php echo number_format($total_da_semana, 2, ',', '.'); ?></strong>
                </div>

                <p class="pwa-footer-info" style="text-align: center; color: var(--text-light); font-size: 10px; font-weight: 800; margin-top: 40px; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 20px;">
                    Detalhes Semana - Pão Da Vida
                </p>

            </div>
        </main>
    </div>

    <script>
    function confirmarExclusao(id) {
        Swal.fire({
            title: 'Excluir registro?',
            text: "Esta ação não pode ser desfeita.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            background: '#fff',
            color: '#1e1a19',
            borderRadius: '20px'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'excluir_fechamento.php?id=' + id + '&confirmado=1';
            }
        })
    }

    function handleMobileLayout() {
        if (window.innerWidth <= 768) {
            const scrollArea = document.getElementById('scrollContainer');
            const availableHeight = window.innerHeight - 340; 
            scrollArea.style.maxHeight = availableHeight + 'px';
        }
    }

    window.addEventListener('resize', handleMobileLayout);
    window.addEventListener('load', handleMobileLayout);
    </script>

</body>
</html>