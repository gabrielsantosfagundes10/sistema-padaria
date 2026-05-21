<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

// Variável para ativar o link correto no sidebar
$activePage = 'compras';

// Lógica para Adicionar Item
if (isset($_POST['add_item'])) {
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $qtd = mysqli_real_escape_string($conn, $_POST['quantidade']);
    if (!empty($item)) {
        $conn->query("INSERT INTO lista_compras (item, quantidade, status) VALUES ('$item', '$qtd', 'pendente')");
    }
    header("Location: lista_compras.php");
    exit;
}

// Lógica para Marcar como Comprado
if (isset($_GET['comprar'])) {
    $id = (int)$_GET['comprar'];
    $conn->query("UPDATE lista_compras SET status = 'comprado' WHERE id = $id");
    header("Location: lista_compras.php");
    exit;
}

// Lógica para Excluir
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $conn->query("DELETE FROM lista_compras WHERE id = $id");
    header("Location: lista_compras.php");
    exit;
}

// Busca itens pendentes
$itens = $conn->query("SELECT * FROM lista_compras WHERE status = 'pendente' ORDER BY id DESC");

// Tradução do mês para o header desktop
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
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Elite OS - Lista de Compras</title>
    
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
            --danger: #ef4444;
            --success: #22c55e;
            --safe-area-bottom: env(safe-area-inset-bottom);
        }

        /* Reset e Base */
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

        /* --- CONTEÚDO PRINCIPAL (DESKTOP MELHORADO) --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        /* TOPBAR ESTILO PADARIA (GRAVADO) */
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

        .content-body { 
            padding: 30px; 
            overflow-y: auto; 
            flex: 1;
            display: block;
            -webkit-overflow-scrolling: touch;
        }

        /* Estilo dos Cards e Painéis */
        .add-panel {
            background: var(--accent);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(30, 26, 25, 0.1);
        }

        .add-form { 
            display: grid; 
            grid-template-columns: 1fr 150px 80px; 
            gap: 15px; 
        }

        .add-form input { 
            padding: 18px; 
            border-radius: 15px; 
            border: 1px solid rgba(255,255,255,0.1); 
            background: rgba(255,255,255,0.05);
            font-family: 'Montserrat'; 
            font-weight: 600; 
            color: white;
            transition: var(--transition);
        }

        .add-form input::placeholder { color: rgba(255,255,255,0.4); }
        .add-form input:focus { background: rgba(255,255,255,0.15); border-color: var(--primary); }

        .btn-add { 
            background: var(--primary); 
            color: var(--accent); 
            border: none; 
            border-radius: 15px; 
            cursor: pointer; 
            transition: var(--transition); 
            font-size: 20px;
            font-weight: 900;
        }
        .btn-add:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(214, 158, 136, 0.4); }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding-bottom: 40px;
        }

        .item-card {
            background: var(--white);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-color);
            border-left: 6px solid var(--primary);
            transition: var(--transition);
        }
        .item-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

        .item-main b {
            display: block;
            font-size: 17px;
            font-weight: 800;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .item-main span {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 6px;
        }

        .item-actions { display: flex; gap: 10px; }

        .btn-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-comprar { background: #dcfce7; color: var(--success); }
        .btn-comprar:hover { background: var(--success); color: white; }
        .btn-excluir { background: #fee2e2; color: var(--danger); }
        .btn-excluir:hover { background: var(--danger); color: white; }

        /* --- AJUSTES EXCLUSIVOS PWA (MANTIDO INTACTO) --- */
        @media (max-width: 1024px) {
            body { display: block; overflow: hidden; position: fixed; width: 100%; height: 100%; }

            .main-wrapper { 
                margin-left: 0 !important; 
                width: 100vw; 
                height: 100%;
                display: block;
            }

            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 20px 20px calc(110px + var(--safe-area-bottom)); 
                height: 100vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-header-simple {
                display: block;
                margin-bottom: 25px;
                padding-top: calc(10px + env(safe-area-inset-top));
            }
            .mobile-header-simple h2 { 
                font-size: 32px; 
                font-weight: 900; 
                text-transform: uppercase; 
                color: var(--accent);
                letter-spacing: -1.5px;
                line-height: 1;
            }

            .add-panel { 
                padding: 20px; 
                border-radius: 20px; 
                background: #f1f5f9; 
                box-shadow: none;
                border: 1px solid var(--border-color);
            }
            
            .add-panel h2 { color: var(--accent) !important; opacity: 1 !important; }

            .add-form { grid-template-columns: 1fr; gap: 12px; }
            
            .add-form input { 
                padding: 16px; 
                font-size: 16px; 
                background: var(--white); 
                color: var(--accent);
                border: 1px solid #e2e8f0;
            }
            .add-form input::placeholder { color: #94a3b8; }

            .btn-add { 
                padding: 16px; 
                background: var(--accent) !important; 
                color: var(--white) !important;
                border-radius: 12px;
                font-family: 'Montserrat', sans-serif;
                font-weight: 800;
            }

            .items-grid { grid-template-columns: 1fr; gap: 12px; }
            
            .item-card { border-radius: 18px; }
        }

        @media (min-width: 1025px) {
            .mobile-header-simple { display: none; }
        }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 800; color: var(--accent); text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">
                <i class="fa-solid fa-basket-shopping" style="color: var(--primary); margin-right: 8px;"></i> Lista de Compras
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
            
            <div class="mobile-header-simple">
                <h2>Compras</h2>
                <div style="width: 40px; height: 6px; background: var(--primary); border-radius: 3px; margin-top: 8px;"></div>
            </div>

            <div class="add-panel">
                <h2 style="color: white; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; font-weight: 800; opacity: 0.7;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Novo Item
                </h2>
                <form class="add-form" method="POST">
                    <input type="text" name="item" placeholder="O que comprar?" required>
                    <input type="text" name="quantidade" placeholder="Quantidade">
                    <button type="submit" name="add_item" class="btn-add">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </form>
            </div>

            <div style="margin-bottom: 20px; padding-left: 5px;">
                <h3 style="font-size: 13px; font-weight: 900; color: var(--accent); text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-list-ul" style="color: var(--primary);"></i> Itens Pendentes
                </h3>
            </div>

            <div class="items-grid">
                <?php if ($itens->num_rows > 0): ?>
                    <?php while($row = $itens->fetch_assoc()): ?>
                        <div class="item-card">
                            <div class="item-main">
                                <b><?php echo htmlspecialchars($row['item']); ?></b>
                                <span><?php echo !empty($row['quantidade']) ? $row['quantidade'] : 'S/ Qtd'; ?></span>
                            </div>
                            <div class="item-actions">
                                <a href="?comprar=<?php echo $row['id']; ?>" class="btn-circle btn-comprar">
                                    <i class="fa-solid fa-check"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="confirmarExclusao(<?php echo $row['id']; ?>)" class="btn-circle btn-excluir">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px 20px; background: white; border-radius: 24px; border: 2px dashed #e2e8f0;">
                        <i class="fa-solid fa-cart-flatbed-suitcases" style="font-size: 32px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                        <h4 style="color: var(--accent); font-weight: 800; text-transform: uppercase; font-size: 12px;">Tudo comprado por aqui!</h4>
                    </div>
                <?php endif; ?>
            </div>

            <div id="pwa-settings-last" style="height: 1px;"></div>
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
                title: 'REMOVER?',
                text: "O item será excluído da lista.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#1e1a19',
                confirmButtonText: 'SIM, EXCLUIR',
                cancelButtonText: 'CANCELAR',
                background: '#fff',
                color: '#1e1a19'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?excluir=' + id;
                }
            })
        }

        // Ajuste para o botão voltar no PWA
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.location.href = "index.php"; 
        };
    </script>
</body>
</html>