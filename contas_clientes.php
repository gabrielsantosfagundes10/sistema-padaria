<?php
include('trava.php');
include('config.php');

$sql = "SELECT * FROM clientes ORDER BY nome ASC";
$result = $conn->query($sql);

$nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Gerente';

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
    <title>Clientes - Elite OS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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

        /* --- CONTEÚDO PRINCIPAL --- */
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

        .date-display {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .date-display .calendar-box {
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

        .clock-display {
            font-size: 22px;
            font-weight: 900;
            color: var(--accent);
            letter-spacing: -1px;
        }

        .content-body { 
            flex: 1;
            padding: 30px; 
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            -webkit-overflow-scrolling: touch;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-shrink: 0;
        }

        /* --- BUSCA --- */
        .search-container {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 14px;
        }

        #busca {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: var(--white);
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            transition: var(--transition);
        }

        #busca:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(214, 158, 136, 0.1); }

        .btn-add-new {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(214, 158, 136, 0.3);
            white-space: nowrap;
        }
        .btn-add-new:hover { transform: translateY(-2px); background: #b87d66; }

        /* --- GRID DE CLIENTES --- */
        .lista-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding-bottom: 40px;
        }

        .card-cliente { 
            background: var(--white); 
            padding: 20px; 
            border-radius: 18px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            text-decoration: none; 
            color: var(--text-main); 
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .card-cliente:hover { 
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
        }

        .flex-left { display: flex; align-items: center; gap: 15px; }

        .cli-icon {
            width: 45px;
            height: 45px;
            background: #f1f5f9;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .info-cli { display: flex; flex-direction: column; }
        .info-cli strong { 
            font-size: 14px; 
            font-weight: 800; 
            text-transform: uppercase;
            color: var(--accent);
            letter-spacing: 0.5px;
        }
        .info-cli small { 
            color: var(--text-light); 
            font-weight: 600;
            margin-top: 3px; 
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .chevron-link { color: #cbd5e1; font-size: 12px; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        /* --- AJUSTES MOBILE PWA --- */
        @media (max-width: 1024px) {
            body { height: 100vh; overflow: hidden; position: fixed; width: 100%; }
            .main-wrapper { margin-left: 0 !important; height: 100vh; }
            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 0; 
                height: 100vh;
                overflow: hidden;
            }

            /* Cabeçalho de busca e botão fixos */
            .header-title { 
                padding: 20px 15px 15px; 
                margin-bottom: 0; 
                flex-direction: column; 
                align-items: stretch; 
                background: var(--bg-body);
                gap: 12px;
            }
            
            .search-container { max-width: 100%; }
            .btn-add-new { justify-content: center; }

            /* Lista com Scroll Independente */
            .lista-container { 
                flex: 1;
                overflow-y: auto;
                padding: 15px;
                grid-template-columns: 1fr; 
                padding-bottom: 120px; /* Espaço para sidebar inferior */
                -webkit-overflow-scrolling: touch;
            }
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
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-users"></i> Base de Clientes
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
            <div class="header-title">
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="busca" placeholder="Pesquisar cliente..." onkeyup="filtrar()" autocomplete="off">
                </div>
                
                <a href="novo_cliente.php" class="btn-add-new">
                    <i class="fa-solid fa-user-plus"></i> CADASTRAR CLIENTE
                </a>
            </div>

            <div class="lista-container" id="lista">
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <a href="cliente_detalhes.php?id=<?php echo $row['id']; ?>" class="card-cliente" data-nome="<?php echo $row['nome']; ?>">
                            <div class="flex-left">
                                <div class="cli-icon">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="info-cli">
                                    <strong><?php echo htmlspecialchars($row['nome']); ?></strong>
                                    <?php if(!empty($row['telefone'])): ?>
                                        <small><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($row['telefone']); ?></small>
                                    <?php else: ?>
                                        <small style="opacity: 0.6; font-style: italic;">Sem telefone</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="chevron-link">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: var(--text-light);">
                        <i class="fa-solid fa-user-slash" style="font-size: 3rem; opacity: 0.2; margin-bottom: 15px; display: block;"></i>
                        <p style="font-weight: 600;">Nenhum cliente cadastrado.</p>
                    </div>
                <?php endif; ?>
            </div>
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

    function removerAcentos(str) {
        if (!str) return "";
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }

    function filtrar() {
        let input = removerAcentos(document.getElementById('busca').value);
        let cards = document.getElementsByClassName('card-cliente');
        
        for (let card of cards) {
            let nomeTratado = removerAcentos(card.getAttribute('data-nome'));
            if (nomeTratado.includes(input)) {
                card.style.display = "flex";
            } else {
                card.style.display = "none";
            }
        }
    }
    </script>
</body>
</html>