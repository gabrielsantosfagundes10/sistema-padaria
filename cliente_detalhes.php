<?php
include('trava.php');
include('config.php');

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) { header("Location: contas_clientes.php"); exit; }

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if (!$cliente) { header("Location: contas_clientes.php"); exit; }

// Processamento de Movimentação
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valor = $_POST['valor'];
    $tipo = $_POST['tipo']; 
    $valor_final = ($tipo == 'divida') ? $valor : -$valor;
    
    $stmt_mov = $conn->prepare("INSERT INTO contas_clientes_movimentos (id_cliente, valor, data_movimento) VALUES (?, ?, NOW())");
    $stmt_mov->bind_param("id", $id, $valor_final);
    $stmt_mov->execute();
    
    header("Location: cliente_detalhes.php?id=$id");
    exit;
}

// Cálculo de Saldo
$res_soma = $conn->query("SELECT SUM(valor) as total FROM contas_clientes_movimentos WHERE id_cliente = $id");
$row_soma = $res_soma->fetch_assoc();
$saldo = $row_soma['total'] ? $row_soma['total'] : 0;

// Histórico
$historico = $conn->query("SELECT * FROM contas_clientes_movimentos WHERE id_cliente = $id ORDER BY id DESC");

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
    <title>Elite OS - Detalhes</title>
    
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
            --danger: #ff4757;
            --success: #2ed573;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            height: 100vh;
            overflow-x: hidden;
        }

        /* --- DESKTOP (SEM ALTERAÇÕES) --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: relative;
            overflow: hidden;
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
            flex: 1;
            padding: 30px; 
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            align-items: start;
        }

        .card-elite {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .saldo-box {
            background: #f8fafb;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 15px 0;
            border: 1px dashed #e2e8f0;
        }

        .saldo-valor { font-size: 32px; font-weight: 900; letter-spacing: -1px; }
        .valor-pos { color: var(--danger); }
        .valor-neg { color: var(--success); }

        .admin-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .action-btn {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 800;
            color: var(--text-light);
        }

        .action-btn i {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 16px;
        }

        input[type="number"] {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            font-family: 'Montserrat';
            font-weight: 700;
            font-size: 20px;
            text-align: center;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            border: none;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
        }

        .btn-divida { background: var(--accent); }
        .btn-pagamento { background: var(--primary); }

        .item-historico {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 18px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-color);
        }

        /* --- RESPONSIVIDADE PWA (SINCRONIZADO) --- */
        @media (max-width: 1024px) {
            body { 
                display: block; 
                height: auto; 
                min-height: 100vh; 
                overflow-x: hidden;
                padding-bottom: 80px; /* Espaço para sidebar bottom */
                background-color: var(--bg-body);
            }
            
            .main-wrapper { 
                margin-left: 0 !important; 
                width: 100%; 
                height: auto; 
                display: block;
            }

            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 0; 
                width: 100%;
                display: block;
            }

            .layout-grid { 
                display: block; 
                width: 100%;
            }

            .sidebar-dados { 
                padding: 15px 15px 5px; 
                width: 100%;
            }

            .mobile-header-pwa {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 10px 5px 15px;
            }

            .btn-back-pwa {
                width: 42px;
                height: 42px;
                background: var(--white);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--accent);
                text-decoration: none;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }

            .main-historico { 
                padding: 25px 15px 40px; 
                background: var(--white);
                border-top-left-radius: 30px;
                border-top-right-radius: 30px;
                box-shadow: 0 -10px 30px rgba(0,0,0,0.04);
                width: 100%;
                min-height: 40vh;
            }

            .card-elite { padding: 20px; border-radius: 20px; width: 100%; }
        }

        @media (min-width: 1025px) {
            .mobile-header-pwa { display: none; }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'clientes';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="contas_clientes.php" style="color: var(--text-light);"><i class="fa-solid fa-arrow-left"></i></a>
                <div style="font-weight: 700; color: var(--text-light); text-transform: uppercase; font-size: 12px;">Detalhes do Cliente</div>
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
            <div class="layout-grid">
                
                <div class="sidebar-dados">
                    <div class="mobile-header-pwa">
                        <a href="contas_clientes.php" class="btn-back-pwa"><i class="fa-solid fa-chevron-left"></i></a>
                        <h2 style="font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Conta de Cliente</h2>
                    </div>

                    <div class="card-elite">
                        <div style="text-align: center;">
                            <h2 style="font-weight: 900; text-transform: uppercase; color: var(--accent);"><?php echo htmlspecialchars($cliente['nome']); ?></h2>
                            <div class="saldo-box">
                                <small style="display:block; text-transform:uppercase; font-weight:800; font-size:10px; color:var(--text-light); margin-bottom:5px;">Saldo Devedor Atual</small>
                                <div class="saldo-valor <?php echo $saldo > 0 ? 'valor-pos' : 'valor-neg'; ?>">
                                    R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="admin-actions">
                            <a href="editar_cliente.php?id=<?php echo $id; ?>" class="action-btn">
                                <i class="fa-solid fa-pen-to-square"></i> EDITAR
                            </a>
                            <a href="javascript:void(0)" onclick="confirmarLimpeza(<?php echo $id; ?>)" class="action-btn">
                                <i class="fa-solid fa-eraser"></i> ZERAR
                            </a>
                            <a href="javascript:void(0)" onclick="confirmarExclusao(<?php echo $id; ?>)" class="action-btn">
                                <i class="fa-solid fa-trash-can" style="color:var(--danger)"></i> EXCLUIR
                            </a>
                        </div>
                    </div>

                    <div class="card-elite">
                        <div style="font-size:11px; font-weight:900; text-transform:uppercase; margin-bottom:15px; color:var(--danger)">
                            <i class="fa-solid fa-plus-circle"></i> Adicionar Dívida
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="tipo" value="divida">
                            <input type="number" name="valor" step="0.01" placeholder="0,00" required>
                            <button type="submit" class="btn-submit btn-divida">Pendurar Valor</button>
                        </form>
                    </div>

                    <div class="card-elite">
                        <div style="font-size:11px; font-weight:900; text-transform:uppercase; margin-bottom:15px; color:var(--success)">
                            <i class="fa-solid fa-check-circle"></i> Registrar Pagamento
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="tipo" value="pagamento">
                            <input type="number" name="valor" step="0.01" placeholder="0,00" required>
                            <button type="submit" class="btn-submit btn-pagamento">Abater Saldo</button>
                        </form>
                    </div>
                </div>

                <div class="main-historico">
                    <div style="font-size:13px; font-weight:900; text-transform:uppercase; margin-bottom:20px; color:var(--accent); display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-receipt"></i> Histórico de Movimentações
                    </div>

                    <?php if ($historico->num_rows > 0): ?>
                        <?php while($h = $historico->fetch_assoc()): ?>
                            <div class="item-historico">
                                <div>
                                    <strong class="<?php echo $h['valor'] > 0 ? 'valor-pos' : 'valor-neg'; ?>" style="font-size:16px; font-weight:800;">
                                        <?php echo $h['valor'] > 0 ? '+' : '-'; ?> R$ <?php echo number_format(abs($h['valor']), 2, ',', '.'); ?>
                                    </strong>
                                    <span style="display:block; font-size:10px; color:var(--text-light); font-weight:600; margin-top:4px;">
                                        <i class="fa-regular fa-clock"></i> <?php echo date('d/m/Y - H:i', strtotime($h['data_movimento'])); ?>
                                    </span>
                                </div>
                                <div style="opacity: 0.2;">
                                    <i class="fa-solid <?php echo $h['valor'] > 0 ? 'fa-cart-shopping' : 'fa-money-bill-1-wave'; ?> fa-lg"></i>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; opacity: 0.4;">
                            <i class="fa-solid fa-box-open" style="font-size: 30px; margin-bottom: 10px;"></i>
                            <p style="font-size: 10px; font-weight: 800; text-transform: uppercase;">Nenhum registro encontrado</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
    function updateClock() {
        const now = new Date();
        const clockEl = document.getElementById('clock');
        if(clockEl) {
            clockEl.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    function confirmarLimpeza(id) {
        Swal.fire({
            title: 'ZERAR CONTA?',
            text: "Isso apagará o histórico e zerará o saldo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d69e88',
            confirmButtonText: 'SIM, ZERAR',
            cancelButtonText: 'VOLTAR'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'limpar_historico.php?id=' + id; } })
    }

    function confirmarExclusao(id) {
        Swal.fire({
            title: 'EXCLUIR CLIENTE?',
            text: "Ação irreversível!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            confirmButtonText: 'EXCLUIR AGORA',
            cancelButtonText: 'CANCELAR'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'excluir_cliente.php?id=' + id; } })
    }
    </script>
</body>
</html>