<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

// --- 1. BUSCAR CATEGORIAS NO BANCO ---
$query_cats = $conn->query("SELECT * FROM categorias_gastos ORDER BY nome ASC");

// --- 2. LÓGICA DE SALVAR A CONTA ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $detalhe = isset($_POST['descricao']) ? $conn->real_escape_string($_POST['descricao']) : '';
    $valor = $_POST['valor'];
    $data = $_POST['data_vencimento'];

    // AJUSTE DE LÓGICA: 
    // A Categoria vai para a coluna 'descricao' para ser o título do card.
    // O Detalhe vai para a coluna 'categoria'.
    $titulo_card = strtoupper($categoria); 
    
    $sql = "INSERT INTO contas_padaria (descricao, categoria, valor, data_vencimento, status_pago, rascunho) 
            VALUES ('$titulo_card', '$detalhe', '$valor', '$data', 0, 0)";
    
    if ($conn->query($sql)) { 
        // --- NOTIFICAÇÃO TELEGRAM ---
        $data_formatada = date('d/m/Y', strtotime($data));
        $valor_formatado = number_format($valor, 2, ',', '.');
        
        $msg_telegram = "💸 <b>NOVA CONTA LANÇADA</b>\n\n";
        $msg_telegram .= "📂 <b>Insumo:</b> $titulo_card\n";
        
        if (!empty($detalhe)) {
            $msg_telegram .= "📝 <b>Obs:</b> $detalhe\n";
        }
        
        $msg_telegram .= "💰 <b>Valor:</b> R$ $valor_formatado\n";
        $msg_telegram .= "📅 <b>Vencimento:</b> $data_formatada";
        
        if(function_exists('enviarTelegram')) {
            enviarTelegram($msg_telegram);
        }

        header("Location: contas_padaria.php?sucesso=1"); 
        exit();
    }
}

// Meses para o header padrão
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
    <title>Lançar Gasto - PãoDaVida</title>
    
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

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: var(--transition);
        }

        /* --- TOP NAVBAR WEB --- */
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

        .content-body { padding: 30px; overflow-y: auto; flex: 1; -webkit-overflow-scrolling: touch; }

        /* --- FORMULÁRIO --- */
        .container-fluxo { max-width: 800px; margin: 0 auto; }
        .header-section { margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; }
        .header-section h2 { font-weight: 900; color: var(--accent); font-size: 1.5rem; text-transform: uppercase; }

        .input-card {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .input-card:focus-within {
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(214, 158, 136, 0.1);
        }

        .label-custom {
            display: block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 12px;
            letter-spacing: 1px;
        }

        input, select {
            width: 100%;
            border: none;
            background: transparent;
            font-family: 'Montserrat';
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .input-detalhe { font-size: 1rem; color: var(--text-light); font-weight: 500; }
        .grid-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        .btn-salvar {
            background: var(--accent);
            color: white;
            border: none;
            width: 100%;
            padding: 22px;
            border-radius: 18px;
            font-weight: 900;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-salvar:hover { transform: translateY(-3px); background: #332d2b; }

        .btn-cancelar {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
        }

        /* --- PWA / MOBILE ADJUSTMENTS --- */
        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0 !important; }
            .top-navbar { display: none !important; }
            .content-body { padding: 20px; padding-bottom: 120px; }
            .grid-inputs { grid-template-columns: 1fr; }
            .container-fluxo { max-width: 100%; }
            .input-card { padding: 18px; }
            input, select { font-size: 1.1rem; }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body>

    <?php 
    $activePage = 'financas'; 
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-file-invoice-dollar"></i> Lançar Despesas / Insumos
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
            <div class="container-fluxo">
                
                <div class="header-section">
                    <h2>Nova Saída</h2>
                    <i class="fa-solid fa-receipt" style="font-size: 1.5rem; opacity: 0.2;"></i>
                </div>

                <form method="POST">
                    
                    <div class="input-card">
                        <label class="label-custom">O que você está pagando? (Insumo)</label>
                        <select name="categoria" id="select_categoria" required>
                            <option value="">Selecione o Insumo...</option>
                            <?php while($cat = $query_cats->fetch_assoc()): ?>
                                <option value="<?php echo $cat['nome']; ?>"><?php echo $cat['nome']; ?></option>
                            <?php endwhile; ?>
                            <option value="Nova" style="color: var(--primary); font-weight: 800;">+ Adicionar Nova Categoria</option>
                        </select>
                    </div>

                    <div class="grid-inputs">
                        <div class="input-card">
                            <label class="label-custom">Valor do Gasto</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 900; color: var(--primary); font-size: 1.2rem;">R$</span>
                                <input type="number" step="0.01" name="valor" placeholder="0,00" required>
                            </div>
                        </div>

                        <div class="input-card">
                            <label class="label-custom">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="input-card">
                        <label class="label-custom">Detalhes / Observações (Opcional)</label>
                        <input type="text" name="descricao" class="input-detalhe" placeholder="Ex: NF 1234, Referente à carne..." autocomplete="off">
                    </div>

                    <button type="submit" class="btn-salvar">
                        <i class="fa-solid fa-check-circle"></i> SALVAR NO FINANCEIRO
                    </button>

                    <a href="contas_padaria.php" class="btn-cancelar">Desistir e Voltar</a>

                </form>
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

    document.getElementById('select_categoria').addEventListener('change', function() {
        if (this.value === 'Nova') {
            const novaCat = prompt("Digite o nome do novo insumo (Ex: LEITE, FARINHA, EMBALAGEM):");
            if (novaCat) {
                window.location.href = "gerenciar_categorias.php?nome=" + encodeURIComponent(novaCat);
            } else {
                this.value = ""; 
            }
        }
    });

    // PWA Settings Injection
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW error:', err));
        });
    }
    </script>
</body>
</html>