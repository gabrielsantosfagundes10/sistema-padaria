<?php
include('trava.php');
include('config.php');

$id = $_GET['id'];
$en = $conn->query("SELECT * FROM encomendas WHERE id = $id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $sabor = $conn->real_escape_string($_POST['sabor']);
    $valor = $_POST['valor'];
    $data = $_POST['data_entrega'];
    $horario = $_POST['horario'];
    $status = $conn->real_escape_string($_POST['status_pagamento']);
    $nome = $conn->real_escape_string($_POST['nome_cliente']);
    $tel = $conn->real_escape_string($_POST['telefone']);

    $sql = "UPDATE encomendas SET 
            tipo='$tipo', 
            sabor_detalhes='$sabor', 
            valor='$valor', 
            data_entrega='$data', 
            horario='$horario', 
            status_pagamento='$status', 
            nome_cliente='$nome',
            telefone_cliente='$tel' 
            WHERE id=$id";
    
    if($conn->query($sql)){
        header("Location: encomendas.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Editar Encomenda - Padaria</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-masker/1.2.0/vanilla-masker.min.js"></script>
    
    <style>
        * { box-sizing: border-box; transition: all 0.3s ease; }
        
        body { 
            font-family: 'Montserrat', sans-serif; 
            color: #fff;
            background: linear-gradient(rgba(0,0,0,0.92), rgba(0,0,0,0.92)), url('images/padaria01.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container { 
            width: 100%;
            max-width: 800px; 
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(20px);
            padding: 40px; 
            border-radius: 40px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
        }

        .back-link { 
            text-decoration: none; 
            color: rgba(255,255,255,0.4); 
            font-weight: 800; 
            text-transform: uppercase; 
            font-size: 11px; 
            letter-spacing: 2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 30px;
        }
        .back-link:hover { color: #fff; transform: translateX(-5px); }

        h2 { 
            margin: 0 0 40px 0; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: -1px;
            font-size: 28px;
            color: #fff;
        }
        h2 i { color: #ff5733; margin-right: 10px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 768px) {
            .form-grid { grid-template-columns: repeat(2, 1fr); }
            .full-width { grid-column: span 2; }
        }

        .input-group { margin-bottom: 5px; }

        label { 
            font-size: 10px; 
            color: rgba(255,255,255,0.5); 
            font-weight: 800; 
            display: block; 
            margin-bottom: 8px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input, select, textarea { 
            width: 100%; 
            padding: 16px; 
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255,255,255,0.1); 
            border-radius: 15px; 
            color: #fff;
            font-size: 1rem;
            font-family: 'Montserrat';
            font-weight: 600;
        }

        input:focus, textarea:focus { 
            border-color: #ff5733; 
            background: rgba(255, 255, 255, 0.1);
            outline: none; 
        }

        textarea { resize: none; }

        button { 
            width: 100%; 
            padding: 20px; 
            background: #27ae60;
            color: white; 
            border: none; 
            border-radius: 20px; 
            font-weight: 900; 
            cursor: pointer; 
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 20px;
            box-shadow: 0 15px 30px rgba(39, 174, 96, 0.2);
        }

        button:hover { 
            background: #2ecc71; 
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(39, 174, 96, 0.4);
        }

        /* Ajuste para inputs de data e hora no dark mode */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        @media (max-width: 600px) {
            .container { padding: 25px; border-radius: 30px; }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="encomendas.php" class="back-link"><i class="fa-solid fa-chevron-left"></i> Voltar sem salvar</a>
    
    <h2><i class="fa-solid fa-pen-to-square"></i> Editar Encomenda</h2>
    
    <form method="POST">
        <div class="form-grid">
            
            <div class="input-group full-width">
                <label>Produto / Encomenda</label>
                <input type="text" name="tipo" value="<?php echo htmlspecialchars($en['tipo']); ?>" required placeholder="Ex: Bolo de Chocolate">
            </div>

            <div class="input-group full-width">
                <label>Descrição e Detalhes</label>
                <textarea name="sabor" rows="3" placeholder="Detalhes específicos da encomenda..."><?php echo htmlspecialchars($en['sabor_detalhes']); ?></textarea>
            </div>
            
            <div class="input-group">
                <label>Valor (R$)</label>
                <input type="number" step="0.01" name="valor" value="<?php echo $en['valor']; ?>" placeholder="0.00">
            </div>

            <div class="input-group">
                <label>Data de Entrega</label>
                <input type="date" name="data_entrega" value="<?php echo $en['data_entrega']; ?>" required>
            </div>

            <div class="input-group">
                <label>Horário</label>
                <input type="time" name="horario" value="<?php echo $en['horario']; ?>">
            </div>

            <div class="input-group">
                <label>Status do Pagamento</label>
                <input type="text" name="status_pagamento" value="<?php echo htmlspecialchars($en['status_pagamento']); ?>" placeholder="Ex: Pago, Metade, Pendente">
            </div>

            <div class="input-group">
                <label>Nome do Cliente</label>
                <input type="text" name="nome_cliente" value="<?php echo htmlspecialchars($en['nome_cliente']); ?>" required>
            </div>

            <div class="input-group">
                <label>Telefone do Cliente</label>
                <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($en['telefone_cliente']); ?>" placeholder="(00) 00000-0000">
            </div>

        </div>

        <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Atualizar Encomenda</button>
    </form>
</div>

<script>
    // Máscara de Telefone Inteligente
    function inputHandler(masks, max, event) {
        var c = event.target;
        var v = c.value.replace(/\D/g, '');
        var m = c.value.length > max ? 1 : 0;
        VMasker(c).unMask();
        VMasker(c).maskPattern(masks[m]);
        c.value = VMasker.toPattern(v, masks[m]);
    }

    var telMask = ['(99) 9999-9999', '(99) 99999-9999'];
    var telInput = document.querySelector('#telefone');
    
    // Inicializa a máscara
    if(telInput.value.length > 14) {
        VMasker(telInput).maskPattern(telMask[1]);
    } else {
        VMasker(telInput).maskPattern(telMask[0]);
    }
    
    telInput.addEventListener('input', inputHandler.bind(undefined, telMask, 14), false);
</script>

</body>
</html>