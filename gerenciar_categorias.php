<?php
include('trava.php');
include('config.php');

// --- LÓGICA DE CADASTRO RÁPIDO (Vindo do prompt) ---
if (isset($_GET['nome'])) {
    $nome = $conn->real_escape_string($_GET['nome']);
    
    // Verifica se já não existe
    $check = $conn->query("SELECT id FROM categorias_gastos WHERE nome = '$nome'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO categorias_gastos (nome) VALUES ('$nome')");
    }
    
    // CORREÇÃO: Redireciona para o nome exato do seu arquivo
    header("Location: nova_conta_padaria.php"); 
    exit();
}

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $conn->query("DELETE FROM categorias_gastos WHERE id = $id");
    header("Location: gerenciar_categorias.php");
    exit();
}

$categorias = $conn->query("SELECT * FROM categorias_gastos ORDER BY nome ASC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Gerenciar Categorias - PãoDaVida</title>
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
            --danger: #e74c3c;
            --border-color: rgba(0,0,0,0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-body);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .content-body { padding: 40px; overflow-y: auto; }

        .card-categorias {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
            max-width: 500px;
        }

        .item-cat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .btn-del {
            color: var(--danger);
            background: #fff5f5;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }
        .btn-del:hover { background: var(--danger); color: white; }

        .btn-voltar {
            text-decoration: none;
            color: var(--text-light);
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .main-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <div class="main-wrapper">
        <main class="content-body">
            <a href="nova_conta_padaria.php" class="btn-voltar">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao Lançamento
            </a>

            <div class="card-categorias">
                <h2 style="font-weight: 900; color: var(--accent); margin-bottom: 15px; text-transform: uppercase;">Categorias Ativas</h2>
                
                <div class="lista-categorias">
                    <?php if($categorias->num_rows > 0): ?>
                        <?php while($c = $categorias->fetch_assoc()): ?>
                            <div class="item-cat">
                                <span style="font-weight: 600; color: var(--text-main);"><?php echo $c['nome']; ?></span>
                                <a href="gerenciar_categorias.php?excluir=<?php echo $c['id']; ?>" 
                                   onclick="return confirm('Excluir esta categoria?')" class="btn-del">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--text-light); font-size: 13px;">Nenhuma categoria cadastrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>