<?php
include('trava.php');
include('config.php');

date_default_timezone_set('America/Sao_Paulo');

// --- 1. PREPARAÇÃO DO BANCO (AUTO-FIX EXPANDIDO) ---
$conn->query("ALTER TABLE contas_padaria ADD COLUMN IF NOT EXISTS rascunho TINYINT(1) DEFAULT 0");

// Garantir que a tabela de rascunho tenha todos os campos necessários
$conn->query("CREATE TABLE IF NOT EXISTS rascunho_fechamento (
    id INT PRIMARY KEY DEFAULT 1,
    data_caixa DATE,
    valor_inicial DECIMAL(10,2) DEFAULT 0.00,
    valor_final DECIMAL(10,2) DEFAULT 0.00,
    valor_maquininha DECIMAL(10,2) DEFAULT 0.00,
    retiradas DECIMAL(10,2) DEFAULT 0.00,
    UNIQUE(id)
)");

// Verificar se as colunas novas existem (caso a tabela já existisse antes)
$conn->query("ALTER TABLE rascunho_fechamento ADD COLUMN IF NOT EXISTS data_caixa DATE");
$conn->query("ALTER TABLE rascunho_fechamento ADD COLUMN IF NOT EXISTS valor_final DECIMAL(10,2) DEFAULT 0.00");
$conn->query("ALTER TABLE rascunho_fechamento ADD COLUMN IF NOT EXISTS valor_maquininha DECIMAL(10,2) DEFAULT 0.00");
$conn->query("ALTER TABLE rascunho_fechamento ADD COLUMN IF NOT EXISTS retiradas DECIMAL(10,2) DEFAULT 0.00");

// --- 2. LÓGICA DE PERSISTÊNCIA TEMPORÁRIA ---
$query_rascunho = $conn->query("SELECT * FROM rascunho_fechamento WHERE id = 1");
if ($query_rascunho && $query_rascunho->num_rows > 0) {
    $r = $query_rascunho->fetch_assoc();
} else {
    $conn->query("INSERT INTO rascunho_fechamento (id, data_caixa) VALUES (1, CURDATE())");
    $r = ['data_caixa' => date('Y-m-d'), 'valor_inicial' => 0, 'valor_final' => 0, 'valor_maquininha' => 0, 'retiradas' => 0];
}

// AJAX: Salvar qualquer campo do formulário em tempo real
if (isset($_POST['auto_save_campo'])) {
    $campo = $conn->real_escape_string($_POST['campo']);
    $valor = $conn->real_escape_string($_POST['valor']);
    $conn->query("UPDATE rascunho_fechamento SET $campo = '$valor' WHERE id = 1");
    exit;
}

// Ajax para salvar saídas temporárias
if (isset($_POST['add_saida_temp'])) {
    $cat = $conn->real_escape_string($_POST['categoria']);
    $val = floatval($_POST['valor']);
    $conn->query("INSERT INTO contas_padaria (descricao, categoria, valor, data_vencimento, status_pago, rascunho) 
                  VALUES ('Saída de Caixa', '$cat', '$val', CURDATE(), 0, 1)");
    exit;
}

// Ajax para remover saída temporária
if (isset($_POST['remove_saida_temp'])) {
    $id_saida = intval($_POST['id']);
    $conn->query("DELETE FROM contas_padaria WHERE id = $id_saida AND rascunho = 1");
    exit;
}

// --- 3. BUSCAR AS CATEGORIAS ---
$query_categorias = $conn->query("SELECT * FROM categorias_gastos ORDER BY nome ASC");

// --- 4. LÓGICA DE SALVAR FECHAMENTO FINAL ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['salvar_fechamento'])) {
    $data = $_POST['data_fechamento'];
    $inicial = floatval($_POST['valor_inicial_dinheiro']);
    $final = floatval($_POST['valor_final_dinheiro']);
    $maquininha = floatval($_POST['valor_maquininha']);
    $retiradas = floatval($_POST['retiradas']); 

    $query_saidas_rascunho = $conn->query("SELECT SUM(valor) as total FROM contas_padaria WHERE rascunho = 1");
    $res_saidas = $query_saidas_rascunho->fetch_assoc();
    $total_saidas_acumuladas = $res_saidas['total'] ?? 0;
    
    $proximo_dia = $final; 
    $rendimento = ($final + $total_saidas_acumuladas + $retiradas - $inicial) + $maquininha;

    $stmt = $conn->prepare("INSERT INTO fechamentos (data_fechamento, valor_inicial_dinheiro, valor_final_dinheiro, valor_maquininha, saídas, retiradas, saldo_prox_dia, rendimento_dia, oculto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sddddddd", $data, $inicial, $final, $maquininha, $total_saidas_acumuladas, $retiradas, $proximo_dia, $rendimento);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE contas_padaria SET rascunho = 0, status_pago = 1, data_vencimento = '$data' WHERE rascunho = 1");
        // Limpar rascunho após salvar oficialmente
        $conn->query("UPDATE rascunho_fechamento SET valor_inicial = 0, valor_final = 0, valor_maquininha = 0, retiradas = 0 WHERE id = 1");
        header("Location: fechamento.php?sucesso=1");
        exit();
    }
}

// Outras funções (Categoria e Limpeza)
if (isset($_POST['add_categoria_rapida'])) {
    $nova_cat = $conn->real_escape_string($_POST['nome_categoria']);
    if(!empty($nova_cat)) $conn->query("INSERT INTO categorias_gastos (nome) VALUES ('$nova_cat')");
    header("Location: fechamento.php");
    exit();
}

if (isset($_GET['limpar_tudo'])) {
    $conn->query("UPDATE fechamentos SET oculto = 1 WHERE oculto = 0");
    header("Location: fechamento.php?limpado=1");
    exit();
}

$historico = $conn->query("SELECT * FROM fechamentos WHERE oculto = 0 ORDER BY id DESC");
$saidas_salvas = $conn->query("SELECT * FROM contas_padaria WHERE rascunho = 1");

function traduzirDia($data) {
    $dias = array('Sun' => 'Dom', 'Mon' => 'Seg', 'Tue' => 'Ter', 'Wed' => 'Qua', 'Thu' => 'Qui', 'Fri' => 'Sex', 'Sat' => 'Sáb');
    return $dias[date('D', strtotime($data))];
}

$mes_pt = array('Jan'=>'Jan','Feb'=>'Fev','Mar'=>'Mar','Apr'=>'Abr','May'=>'Mai','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Set','Oct'=>'Out','Nov'=>'Nov','Dec'=>'Dez')[date('M')];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <title>Fechamento de Caixa - PãoDaVida</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

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

        input, select { 
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; 
            background: #f8fafc; border-radius: 10px; font-size: 14px; font-weight: 600; font-family: 'Montserrat';
        }

        input:focus { border-color: var(--primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(214, 158, 136, 0.1); }

        .btn-add-saida {
            background: var(--accent); color: white; border: none; padding: 12px;
            border-radius: 10px; font-weight: 900; font-size: 11px; cursor: pointer; width: 100%;
            text-transform: uppercase;
        }

        .saidas-lista-box {
            background: #fff;
            border: 2px dashed #e2e8f0;
            border-radius: 15px;
            padding: 15px;
            margin-top: 15px;
            max-height: 160px;
            overflow-y: auto;
        }

        .saida-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; margin-bottom: 6px; font-size: 12px; font-weight: 700;
        }

        .btn-salvar { 
            background: var(--accent); color: white; border: none; 
            padding: 18px; width: 100%; border-radius: 15px; font-weight: 900; 
            font-size: 13px; text-transform: uppercase; cursor: pointer;
            transition: var(--transition); margin-top: 10px;
        }
        .btn-salvar:hover { background: #332d2b; transform: translateY(-2px); }

        .historico-section {
            background: var(--white);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
            border: 1px solid var(--border-color);
            min-height: 300px;
        }

        .historico-header {
            padding: 15px 25px;
            background: var(--accent);
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-container { flex: 1; overflow-y: auto; padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { position: sticky; top: 0; background: #fff; z-index: 5; padding: 15px 10px; font-size: 10px; text-transform: uppercase; text-align: left; color: var(--text-light); border-bottom: 2px solid var(--bg-body); }
        td { padding: 15px 10px; font-size: 13px; border-bottom: 1px solid var(--bg-body); font-weight: 600; }
        
        .val-pos { color: var(--success); }
        .val-neg { color: var(--danger); }

        /* --- PWA / MOBILE ADJUSTMENTS --- */
        @media (max-width: 1024px) {
            .main-wrapper { margin-left: 0 !important; }
            .top-navbar { display: none !important; }
            .content-body { padding: 15px; padding-bottom: 100px; }
            .fechamento-container { grid-template-columns: 1fr; }
            .form-card { padding: 15px; }
            .grid-inputs { grid-template-columns: 1fr 1fr; }
            .historico-section { min-height: 400px; }
            th, td { padding: 10px 5px; font-size: 11px; }
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body>

    <?php $activePage = 'caixa'; include('sidebar.php'); ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-cash-register"></i> Fluxo de Caixa
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
            
            <form method="POST" id="formFechamento" class="fechamento-container">
                <div class="form-card">
                    <div class="section-label">Abertura e Conferência</div>
                    <div class="grid-inputs">
                        <div>
                            <label>Data do Caixa</label>
                            <input type="date" name="data_fechamento" value="<?php echo $r['data_caixa']; ?>" onchange="autoSave('data_caixa', this.value)" required>
                        </div>
                        <div>
                            <label>Fundo Inicial</label>
                            <input type="number" step="0.01" name="valor_inicial_dinheiro" value="<?php echo number_format($r['valor_inicial'], 2, '.', ''); ?>" style="border-left: 4px solid var(--primary);" onchange="autoSave('valor_inicial', this.value)" onfocus="this.select()">
                        </div>
                        <div>
                            <label>Dinheiro Gaveta</label>
                            <input type="number" step="0.01" name="valor_final_dinheiro" value="<?php echo number_format($r['valor_final'], 2, '.', ''); ?>" placeholder="0.00" onchange="autoSave('valor_final', this.value)" onfocus="this.select()">
                        </div>
                    </div>

                    <div class="grid-inputs">
                        <div>
                            <label>Cartões / PIX</label>
                            <input type="number" step="0.01" name="valor_maquininha" value="<?php echo number_format($r['valor_maquininha'], 2, '.', ''); ?>" placeholder="0.00" onchange="autoSave('valor_maquininha', this.value)" onfocus="this.select()">
                        </div>
                        <div>
                            <label>Sangria</label>
                            <input type="number" step="0.01" name="retiradas" value="<?php echo number_format($r['retiradas'], 2, '.', ''); ?>" style="color: var(--danger);" onchange="autoSave('retiradas', this.value)" onfocus="this.select()">
                        </div>
                    </div>
                    
                    <button type="submit" name="salvar_fechamento" class="btn-salvar">Finalizar Turno</button>
                </div>

                <div class="form-card">
                    <div class="section-label">
                        <span>Lançar Saídas</span>
                        <a href="javascript:void(0)" onclick="novaCategoria()" style="color: var(--primary); text-decoration: none; font-size: 9px; font-weight: 800;">+ CATEGORIA</a>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <select id="campo_categoria">
                            <option value="">Escolha...</option>
                            <?php 
                            $query_categorias->data_seek(0);
                            while($cat = $query_categorias->fetch_assoc()) echo "<option value='".$cat['nome']."'>".$cat['nome']."</option>"; 
                            ?>
                        </select>
                        <input type="number" step="0.01" id="campo_valor" placeholder="Valor R$ 0,00" onfocus="this.select()">
                        <button type="button" class="btn-add-saida" onclick="addSaida()">Adicionar</button>
                    </div>

                    <div class="saidas-lista-box" id="caixa_lista" <?php echo ($saidas_salvas->num_rows > 0) ? '' : 'style="display:none;"'; ?>>
                        <div id="lista_dinamica">
                            <?php 
                            $total_inicial_saidas = 0;
                            if($saidas_salvas) {
                                while($s = $saidas_salvas->fetch_assoc()): 
                                    $total_inicial_saidas += $s['valor'];
                            ?>
                                <div class="saida-item">
                                    <span><?php echo $s['categoria']; ?></span>
                                    <div>
                                        <strong style="margin-right:10px;">R$ <?php echo number_format($s['valor'], 2, '.', ''); ?></strong>
                                        <i class="fa-solid fa-circle-xmark" style="color:var(--danger); cursor:pointer;" onclick="removerSaida(this, <?php echo $s['id']; ?>, <?php echo $s['valor']; ?>)"></i>
                                    </div>
                                </div>
                            <?php endwhile; } ?>
                        </div>
                        <div style="text-align: right; font-size: 11px; font-weight: 900; margin-top: 10px; color: var(--danger);">
                            TOTAL: R$ <span id="label_total"><?php echo number_format($total_inicial_saidas, 2, '.', ''); ?></span>
                        </div>
                    </div>
                </div>
            </form>

            <section class="historico-section">
                <div class="historico-header">
                    <h2 style="font-size: 11px; font-weight: 900; text-transform: uppercase;">Histórico</h2>
                    <button onclick="confirmarLimpeza()" style="background:none; border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 6px 12px; border-radius: 8px; font-size: 9px; cursor: pointer;">LIMPAR</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Gaveta</th>
                                <th>Cartão</th>
                                <th>Saídas</th>
                                <th>Rendimento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($h = $historico->fetch_assoc()): ?>
                            <tr>
                                <td style="color:var(--accent); font-weight:800;"><?php echo traduzirDia($h['data_fechamento'])." ".date('d/m', strtotime($h['data_fechamento'])); ?></td>
                                <td>R$ <?php echo number_format($h['valor_final_dinheiro'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($h['valor_maquininha'], 2, ',', '.'); ?></td>
                                <td class="val-neg">R$ <?php echo number_format($h['saídas'], 2, ',', '.'); ?></td>
                                <td class="val-pos" style="font-weight: 900;">R$ <?php echo number_format($h['rendimento_dia'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        let totalSaidas = <?php echo $total_inicial_saidas; ?>;

        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('clock');
            if(clockEl) {
                clockEl.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        function autoSave(campo, valor) {
            let formData = new FormData();
            formData.append('auto_save_campo', '1');
            formData.append('campo', campo);
            formData.append('valor', valor);
            fetch('fechamento.php', { method: 'POST', body: formData });
        }

        function addSaida() {
            const cat = document.getElementById('campo_categoria').value;
            const val = parseFloat(document.getElementById('campo_valor').value);
            if (!cat || !val) return Swal.fire('Atenção', 'Selecione categoria e valor.', 'warning');

            let formData = new FormData();
            formData.append('add_saida_temp', '1');
            formData.append('categoria', cat);
            formData.append('valor', val);

            fetch('fechamento.php', { method: 'POST', body: formData }).then(() => {
                location.reload();
            });
        }

        function removerSaida(btn, id, valor) {
            let formData = new FormData();
            formData.append('remove_saida_temp', '1');
            formData.append('id', id);

            fetch('fechamento.php', { method: 'POST', body: formData }).then(() => {
                btn.closest('.saida-item').remove();
                totalSaidas -= valor;
                document.getElementById('label_total').innerText = totalSaidas.toFixed(2);
                if(totalSaidas <= 0) document.getElementById('caixa_lista').style.display = 'none';
            });
        }

        function novaCategoria() {
            Swal.fire({
                title: 'Nova Categoria',
                input: 'text',
                confirmButtonColor: '#d69e88',
                showCancelButton: true
            }).then((r) => {
                if(r.value) {
                    const f = document.createElement('form');
                    f.method = 'POST';
                    f.innerHTML = `<input type="hidden" name="add_categoria_rapida" value="1"><input type="hidden" name="nome_categoria" value="${r.value}">`;
                    document.body.appendChild(f);
                    f.submit();
                }
            });
        }

        function confirmarLimpeza() {
            Swal.fire({
                title: 'Ocultar Histórico?',
                text: "Os dados saem da tela mas ficam no banco.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e1a19',
                confirmButtonText: 'Sim, ocultar'
            }).then((result) => { if (result.isConfirmed) window.location.href = 'fechamento.php?limpar_tudo=1'; });
        }

        <?php if(isset($_GET['sucesso'])): ?>
            Swal.fire({ icon: 'success', title: 'FECHADO!', text: 'Caixa salvo com sucesso.', timer: 2000, showConfirmButton: false });
        <?php endif; ?>

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW error:', err));
            });
        }
    </script>
</body>
</html>