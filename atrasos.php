<?php
include('trava.php');
include('config.php');

// Define fuso horário para os registros
date_default_timezone_set('America/Sao_Paulo');

// Função auxiliar para formatar tempo (minutos para horas/minutos)
function formatarTempo($minutos) {
    if ($minutos < 60) {
        return $minutos . "m";
    }
    $horas = floor($minutos / 60);
    $min_restantes = $minutos % 60;
    return $min_restantes > 0 ? "{$horas}h {$min_restantes}m" : "{$horas}h";
}

// Lógica para Salvar Registro
if (isset($_POST['salvar_atraso'])) {
    $nome = $conn->real_escape_string($_POST['funcionario']);
    $motivo = $conn->real_escape_string($_POST['motivo']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $minutos_finais = 0;
    $data_hoje = date('Y-m-d');

    if ($tipo == 'atraso') {
        $horario_previsto = $_POST['horario_previsto'];
        $horario_chegada = $_POST['horario_chegada'];

        $inicio = new DateTime($data_hoje . ' ' . $horario_previsto);
        $chegada = new DateTime($data_hoje . ' ' . $horario_chegada);

        if ($chegada > $inicio) {
            $intervalo = $inicio->diff($chegada);
            $total_minutos = ($intervalo->h * 60) + $intervalo->i;
            
            if ($total_minutos > 10) {
                $minutos_finais = $total_minutos - 10;
            }
        }
        $data_registro = $data_hoje . ' ' . $horario_chegada . ':00';
    } else {
        $data_registro = date('Y-m-d H:i:s');
    }

    $minutos_finais = (int) $minutos_finais;

    $sql = "INSERT INTO atrasos (funcionario, motivo, tipo, minutos_atraso, data_atraso) 
            VALUES ('$nome', '$motivo', '$tipo', '$minutos_finais', '$data_registro')";
    
    if ($conn->query($sql)) {
        header("Location: atrasos.php?sucesso=1");
        exit();
    }
}

if (isset($_GET['limpar_tudo'])) {
    if ($conn->query("TRUNCATE TABLE atrasos")) {
        header("Location: atrasos.php?limpado=1");
        exit();
    }
}

$result = $conn->query("SELECT * FROM atrasos ORDER BY data_atraso DESC");
$dados_agrupados = [];
while ($row = $result->fetch_assoc()) {
    $dados_agrupados[$row['funcionario']][] = $row;
}

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
    <title>Controle de Atrasos - Elite OS</title>
    
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
            --danger: #ef4444;
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
            position: relative;
            overflow: hidden;
        }

        /* NAVBAR - Oculta no Mobile */
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

        /* CONTEÚDO COM SCROLL INDEPENDENTE */
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
            flex-shrink: 0;
        }

        .form-container {
            background: var(--white);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            flex-shrink: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr 0.8fr 0.8fr 1.5fr auto;
            gap: 15px;
            align-items: flex-end;
        }

        .field-group { display: flex; flex-direction: column; gap: 8px; }
        .field-group label { font-size: 10px; font-weight: 800; color: var(--text-light); text-transform: uppercase; padding-left: 5px; }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-main);
            background: #fff;
        }

        .btn-reg {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0 30px;
            border-radius: 10px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
            cursor: pointer;
            height: 45px;
            transition: var(--transition);
        }
        .btn-reg:hover { background: #000; transform: translateY(-2px); }

        .funcionarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding-bottom: 30px;
        }

        .card-funcionario {
            background: var(--white);
            border-radius: 24px;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }
        .card-funcionario:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.03); }

        .card-header-func {
            padding: 20px 25px;
            background: #fcfcfc;
            border-bottom: 1px solid var(--border-color);
            border-radius: 24px 24px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header-func h2 { font-size: 14px; font-weight: 900; text-transform: uppercase; color: var(--accent); }

        .resumo-stats {
            display: flex;
            padding: 20px 25px;
            gap: 15px;
        }

        .stat-item { flex: 1; padding: 12px; border-radius: 15px; text-align: center; }
        .stat-atraso { background: #fff5f5; color: var(--danger); }
        .stat-falta { background: #f1f5f9; color: var(--accent); }
        .stat-label { font-size: 9px; font-weight: 800; text-transform: uppercase; display: block; opacity: 0.7; margin-bottom: 2px; }
        .stat-value { font-size: 16px; font-weight: 900; }

        .lista-eventos { padding: 0 25px 25px; }
        .evento-item {
            padding: 15px 0;
            border-bottom: 1px dashed #f1f5f9;
        }
        .evento-item:last-child { border-bottom: none; }

        .evento-topo { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }

        .tag { font-size: 8px; font-weight: 900; text-transform: uppercase; padding: 4px 8px; border-radius: 6px; }
        .tag-atraso { background: #ffe3e3; color: var(--danger); }
        .tag-falta { background: #1e1a19; color: #fff; }

        .motivo-texto { 
            font-size: 11px; 
            color: var(--text-light); 
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
        }

        .btn-limpar { 
            background: transparent; 
            border: 1px solid var(--danger); 
            color: var(--danger); 
            padding: 10px 20px; 
            border-radius: 10px; 
            font-weight: 800; 
            font-size: 11px; 
            text-transform: uppercase; 
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-limpar:hover { background: var(--danger); color: white; }

        /* AJUSTES MOBILE PWA EXCLUSIVOS */
        @media (max-width: 1024px) {
            body { height: 100vh; overflow: hidden; position: fixed; width: 100%; }
            .main-wrapper { margin-left: 0 !important; height: 100vh; }
            .top-navbar { display: none !important; }
            
            .content-body { 
                padding: 0; 
                height: 100vh;
                overflow: hidden;
            }

            /* Cabeçalho e Form Fixos no Topo */
            .header-title { 
                padding: 20px 15px 10px; 
                margin-bottom: 0; 
                flex-direction: row; 
                align-items: center; 
                background: var(--bg-body);
                border-bottom: none;
            }
            .header-title h1 { font-size: 18px !important; }
            .header-title p { display: none; }
            .btn-limpar { padding: 8px 12px; font-size: 9px; width: auto; }

            .form-container { 
                padding: 15px; 
                margin: 0 15px 15px; 
                border-radius: 15px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            }
            .form-grid { grid-template-columns: 1fr; gap: 10px; }
            .btn-reg { width: 100%; height: 40px; }

            /* Lista com Scroll Independente */
            .funcionarios-grid { 
                flex: 1;
                overflow-y: auto;
                padding: 15px;
                grid-template-columns: 1fr; 
                padding-bottom: 120px; /* Espaço sidebar inferior */
                -webkit-overflow-scrolling: touch;
            }
            
            .card-funcionario { border-radius: 18px; }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body>

    <?php 
    $activePage = 'atrasos'; 
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light); font-size: 14px;">
                <i class="fa-solid fa-clock-rotate-left"></i> Recursos Humanos / Pontualidade
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
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; color: var(--accent);">PONTUALIDADE</h1>
                    <p style="color: var(--text-light); font-size: 14px;">Tolerância de 10min aplicada automaticamente.</p>
                </div>
                <button onclick="confirmarLimpeza()" class="btn-limpar">LIMPAR</button>
            </div>

            <div class="form-container">
                <form method="POST" class="form-grid">
                    <div class="field-group">
                        <label>Funcionário</label>
                        <input type="text" name="funcionario" placeholder="Nome do funcionário" required>
                    </div>
                    
                    <div class="field-group">
                        <label>Tipo</label>
                        <select name="tipo" id="tipoSelect">
                            <option value="atraso">Atraso</option>
                            <option value="falta">Falta</option>
                        </select>
                    </div>

                    <div class="field-group" id="groupPrevisto">
                        <label>Horário Escala</label>
                        <input type="time" name="horario_previsto" value="14:00">
                    </div>

                    <div class="field-group" id="groupChegada">
                        <label>Horário Chegada</label>
                        <input type="time" name="horario_chegada" value="<?php echo date('H:i'); ?>">
                    </div>

                    <div class="field-group">
                        <label>Motivo / Observação</label>
                        <input type="text" name="motivo" placeholder="Opcional...">
                    </div>

                    <button type="submit" name="salvar_atraso" class="btn-reg">Salvar</button>
                </form>
            </div>

            <div class="funcionarios-grid">
                <?php if(empty($dados_agrupados)): ?>
                    <p style="text-align: center; grid-column: 1/-1; padding: 40px; color: var(--text-light); font-weight: 600;">Nenhum registro encontrado este mês.</p>
                <?php endif; ?>

                <?php foreach ($dados_agrupados as $func => $eventos): 
                    $total_min = 0;
                    $total_faltas = 0;
                    foreach($eventos as $e) {
                        $total_min += $e['minutos_atraso'];
                        if($e['tipo'] == 'falta') $total_faltas++;
                    }
                ?>
                    <div class="card-funcionario">
                        <div class="card-header-func">
                            <h2><?php echo htmlspecialchars($func); ?></h2>
                            <i class="fa-solid fa-user-clock" style="opacity:0.2"></i>
                        </div>
                        <div class="resumo-stats">
                            <div class="stat-item stat-atraso">
                                <span class="stat-label">Total Atrasado</span>
                                <span class="stat-value"><?php echo formatarTempo($total_min); ?></span>
                            </div>
                            <div class="stat-item stat-falta">
                                <span class="stat-label">Faltas</span>
                                <span class="stat-value"><?php echo $total_faltas; ?></span>
                            </div>
                        </div>
                        <div class="lista-eventos">
                            <?php foreach($eventos as $ev): ?>
                                <div class="evento-item">
                                    <div class="evento-topo">
                                        <div>
                                            <span class="tag <?php echo $ev['tipo'] == 'falta' ? 'tag-falta' : 'tag-atraso'; ?>">
                                                <?php echo $ev['tipo']; ?>
                                            </span>
                                            <strong style="margin-left: 8px; font-size: 11px;"><?php echo date('d/m', strtotime($ev['data_atraso'])); ?></strong>
                                        </div>
                                        <div style="text-align: right;">
                                            <span style="font-size: 11px; font-weight: 800; color: var(--accent);"><?php echo date('H:i', strtotime($ev['data_atraso'])); ?></span>
                                            <?php if($ev['minutos_atraso'] > 0): ?>
                                                <span style="font-size: 10px; color: var(--danger); font-weight: 800;"> (+<?php echo formatarTempo($ev['minutos_atraso']); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($ev['motivo'])): ?>
                                        <span class="motivo-texto">
                                            <i class="fa-regular fa-comment-dots" style="font-size: 12px; color: var(--primary);"></i> 
                                            <?php echo htmlspecialchars($ev['motivo']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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

    document.getElementById('tipoSelect').addEventListener('change', function() {
        const isFalta = (this.value === 'falta');
        document.getElementById('groupPrevisto').style.display = isFalta ? 'none' : 'flex';
        document.getElementById('groupChegada').style.display = isFalta ? 'none' : 'flex';
    });

    function confirmarLimpeza() {
        Swal.fire({
            title: 'LIMPAR TUDO?',
            text: 'Isso apagará o histórico permanentemente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#334155',
            confirmButtonText: 'SIM, APAGAR',
            cancelButtonText: 'CANCELAR'
        }).then((result) => { if (result.isConfirmed) window.location.href = 'atrasos.php?limpar_tudo=1'; })
    }
    </script>
</body>
</html>
