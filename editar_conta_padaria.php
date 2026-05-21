<?php
include('trava.php');
include('config.php');

// Define fuso horário
date_default_timezone_set('America/Sao_Paulo');

$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM contas_padaria WHERE id = $id");
$dados = $res->fetch_assoc();

if (!$dados) {
    header("Location: contas_padaria.php");
    exit();
}

// --- LÓGICA DE ATUALIZAÇÃO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoria_selecionada = $conn->real_escape_string($_POST['categoria']); // O Insumo
    $detalhe = $conn->real_escape_string($_POST['descricao']); // A observação
    $valor = $_POST['valor'];
    $data = $_POST['data_vencimento'];

    // Seguindo sua lógica de inversão para o card:
    // A Categoria (Insumo) vai para 'descricao' (Título)
    // O Detalhe vai para a coluna 'categoria'
    $titulo_card = strtoupper($categoria_selecionada);

    $sql = "UPDATE contas_padaria SET 
            descricao='$titulo_card', 
            categoria='$detalhe', 
            valor='$valor', 
            data_vencimento='$data' 
            WHERE id = $id";
            
    if ($conn->query($sql)) {
        header("Location: contas_padaria.php?editado=1");
        exit();
    }
}

// Busca as categorias para o select
$query_cats = $conn->query("SELECT * FROM categorias_gastos ORDER BY nome ASC");

// Meses para o header
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Editar Gasto - PãoDaVida</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #c69400; /* Amarelo Queimado */
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
        }

        /* --- TOP NAVBAR --- */
        .top-navbar {
            height: 75px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .date-display {
            display: flex;
            align-items: center;
            gap: 15px;
        }

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

        .clock-display {
            font-size: 22px;
            font-weight: 900;
            color: var(--accent);
            letter-spacing: -1px;
        }

        .content-body { padding: 30px; overflow-y: auto; flex: 1; }

        .container-fluxo {
            max-width: 800px;
            margin: 0 auto;
        }

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
            box-shadow: 0 10px 25px rgba(198, 148, 0, 0.1);
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

        .grid-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

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

        .btn-salvar:hover { transform: translateY(-3px); background: #000; }

        .btn-cancelar {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            transition: var(--transition);
        }

        .btn-cancelar:hover { color: var(--primary); }

        select { cursor: pointer; appearance: none; -webkit-appearance: none; }

        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0; }
        }
        @media (max-width: 600px) {
            .grid-inputs { grid-template-columns: 1fr; }
        }
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
                <i class="fa-solid fa-pen-to-square"></i> Editar Lançamento #<?php echo $id; ?>
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
                    <h2>Alterar Dados</h2>
                    <i class="fa-solid fa-file-pen" style="font-size: 1.5rem; opacity: 0.2;"></i>
                </div>

                <form method="POST">
                    
                    <div class="input-card">
                        <label class="label-custom">Insumo (Categoria)</label>
                        <select name="categoria" id="select_categoria" required>
                            <option value="">Selecione o Insumo...</option>
                            <?php 
                            while($cat = $query_cats->fetch_assoc()): 
                                // O título atual está em 'descricao'
                                $selected = (strtoupper($cat['nome']) == strtoupper($dados['descricao'])) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $cat['nome']; ?>" <?php echo $selected; ?>>
                                    <?php echo $cat['nome']; ?>
                                </option>
                            <?php endwhile; ?>
                            <option value="Nova" style="color: var(--primary); font-weight: 800;">+ Adicionar Nova Categoria</option>
                        </select>
                    </div>

                    <div class="grid-inputs">
                        <div class="input-card">
                            <label class="label-custom">Valor do Gasto</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 900; color: var(--primary); font-size: 1.2rem;">R$</span>
                                <input type="number" step="0.01" name="valor" value="<?php echo $dados['valor']; ?>" required>
                            </div>
                        </div>

                        <div class="input-card">
                            <label class="label-custom">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" value="<?php echo $dados['data_vencimento']; ?>" required>
                        </div>
                    </div>

                    <div class="input-card">
                        <label class="label-custom">Detalhes / Observações</label>
                        <input type="text" name="descricao" class="input-detalhe" 
                               value="<?php echo htmlspecialchars($dados['categoria']); ?>" 
                               placeholder="Ex: NF 1234, Referente à carne..." autocomplete="off">
                    </div>

                    <button type="submit" class="btn-salvar">
                        <i class="fa-solid fa-arrows-rotate"></i> ATUALIZAR LANÇAMENTO
                    </button>

                    <a href="contas_padaria.php" class="btn-cancelar">Desistir e Voltar</a>

                </form>
            </div>
        </main>
    </div>

    <script>
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = 
            now.getHours().toString().padStart(2, '0') + ':' + 
            now.getMinutes().toString().padStart(2, '0');
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
    </script>
</body>
</html>