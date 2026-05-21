<?php
// Define o fuso horário para Brasília/São Paulo
date_default_timezone_set('America/Sao_Paulo');

include('trava.php');
include('config.php');

// --- LÓGICA PARA MARCAR COMO LIDO ---
if (isset($_GET['ler'])) {
    $id = intval($_GET['ler']);
    $conn->query("UPDATE avisos SET lido = 1 WHERE id = $id");
    header("Location: avisos.php");
    exit();
}

// Lógica de exclusão
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM avisos WHERE id = $id");
    header("Location: avisos.php?apagado=1");
    exit();
}

$avisos = $conn->query("SELECT * FROM avisos ORDER BY data_postagem DESC");

// Meses para o header
$meses_nomes = array(
    'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr',
    'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
    'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
);
$mes_pt = $meses_nomes[date('M')];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <title>Mural de Avisos - PãoDaVida</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --sidebar-width: 270px;
            --bg-body: #f8fafb;
            --primary: #d69e88;
            --accent: #1e1a19;
            --danger: #ef4444;
            --success: #1dd1a1;
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

        /* --- NAVBAR --- */
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

        .content-body { padding: 30px; overflow-y: auto; flex: 1; }

        /* --- HEADER ACTIONS --- */
        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
        }

        .btn-add { 
            background: var(--accent);
            color: white; 
            padding: 14px 25px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: 800; 
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-add:hover { background: #332d2b; transform: translateY(-2px); }

        /* --- CARDS DE AVISO --- */
        .card-aviso { 
            background: var(--white); 
            padding: 25px; 
            border-radius: 20px; 
            margin-bottom: 20px; 
            position: relative;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: var(--transition);
        }
        
        .aviso-novo { border-left: 6px solid var(--danger); }
        .aviso-lido { border-left: 6px solid #cbd5e1; }

        .aviso-texto { font-size: 16px; color: var(--text-main); line-height: 1.6; margin-bottom: 20px; font-weight: 500; }

        .badge-novo { 
            background: var(--danger); 
            color: white; 
            font-size: 9px; 
            padding: 3px 10px; 
            border-radius: 50px; 
            margin-left: 8px; 
            font-weight: 900; 
            text-transform: uppercase;
            vertical-align: middle;
        }

        .meta { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-top: 1px solid var(--border-color); 
            padding-top: 15px; 
        }

        .data { font-size: 11px; color: var(--text-light); font-weight: 700; text-transform: uppercase; }

        .actions-btns { display: flex; align-items: center; gap: 10px; }

        .btn-read {
            background: rgba(29, 209, 161, 0.08);
            color: #16a34a;
            text-decoration: none;
            font-weight: 800;
            font-size: 10px;
            padding: 8px 16px;
            border-radius: 8px;
            text-transform: uppercase;
            transition: var(--transition);
        }
        .btn-read:hover { background: #16a34a; color: white; }

        .btn-del { 
            color: var(--text-light); 
            text-decoration: none; 
            font-weight: 800; 
            font-size: 10px; 
            padding: 8px;
            text-transform: uppercase;
            transition: var(--transition);
        }
        .btn-del:hover { color: var(--danger); }

        /* AJUSTES EXCLUSIVOS PWA / MOBILE */
        @media (max-width: 768px) {
            body { height: 100vh; overflow: hidden; position: fixed; width: 100%; }
            .main-wrapper { margin-left: 0 !important; height: 100vh; }
            .top-navbar { display: none !important; }
            
            /* Torna o content-body um container flex para controlar o scroll interno */
            .content-body { 
                padding: 0; 
                display: flex;
                flex-direction: column;
                height: 100vh;
                overflow: hidden;
            }

            /* Fixa o título e o botão no topo do PWA */
            .header-title { 
                padding: 20px 15px;
                margin-bottom: 0;
                background: var(--bg-body);
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                flex-shrink: 0;
                border-bottom: 1px solid var(--border-color);
            }
            
            .header-title h1 { font-size: 22px !important; }
            .header-title p { display: none !important; }

            .btn-add { 
                width: 100%; 
                justify-content: center;
                padding: 12px;
                font-size: 11px;
            }

            /* Container que realmente faz o scroll dos avisos */
            .avisos-container {
                flex: 1;
                overflow-y: auto;
                padding: 15px;
                padding-bottom: 100px; /* Espaço para sidebar inferior */
                -webkit-overflow-scrolling: touch;
            }

            .card-aviso { 
                padding: 15px; 
                margin-bottom: 12px; 
                border-radius: 15px;
            }
            
            .aviso-texto { font-size: 14px; margin-bottom: 15px; }

            .meta { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 12px;
            }

            .actions-btns { width: 100%; justify-content: space-between; }
            .btn-read { flex: 1; text-align: center; justify-content: center; display: flex; align-items: center; }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .main-wrapper { margin-left: 80px; }
        }
    </style>
</head>
<body>

    <?php 
    $activePage = 'avisos';
    include('sidebar.php'); 
    ?>

    <div class="main-wrapper">
        <header class="top-navbar">
            <div style="font-weight: 700; color: var(--text-light);">
                <i class="fa-solid fa-message"></i> Mural Interno
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
                    <h1 style="font-size: 26px; font-weight: 900; color: var(--accent);">AVISOS E RECADOS</h1>
                    <p style="color: var(--text-light); font-size: 14px; font-weight: 500;">Comunicação direta com a equipe.</p>
                </div>
                <a href="novo_aviso.php" class="btn-add">
                    <i class="fa-solid fa-plus"></i> Novo Aviso
                </a>
            </div>

            <div class="avisos-container">
                <?php if ($avisos->num_rows == 0): ?>
                    <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 20px; border: 1px solid var(--border-color);">
                        <i class="fa-solid fa-envelopes-bulk" style="font-size: 40px; color: #e2e8f0; margin-bottom: 20px; display: block;"></i>
                        <p style="font-weight: 700; text-transform: uppercase; color: var(--text-light); font-size: 12px; letter-spacing: 1px;">Nenhuma mensagem no mural.</p>
                    </div>
                <?php endif; ?>

                <?php while($av = $avisos->fetch_assoc()): ?>
                    <div class="card-aviso <?php echo ($av['lido'] == 0) ? 'aviso-novo' : 'aviso-lido'; ?>">
                        <div class="aviso-texto">
                            <?php echo nl2br(htmlspecialchars($av['mensagem'])); ?>
                            <?php if($av['lido'] == 0): ?><span class="badge-novo">Novo</span><?php endif; ?>
                        </div>
                        
                        <div class="meta">
                            <span class="data">
                                <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                                <?php echo date('d/m/Y', strtotime($av['data_postagem'])); ?> às <?php echo date('H:i', strtotime($av['data_postagem'])); ?>
                            </span>
                            
                            <div class="actions-btns">
                                <?php if($av['lido'] == 0): ?>
                                    <a href="avisos.php?ler=<?php echo $av['id']; ?>" class="btn-read">
                                        <i class="fa-solid fa-check"></i> Marcar Lido
                                    </a>
                                <?php endif; ?>

                                <a href="#" onclick="confirmarExclusao(<?php echo $av['id']; ?>)" class="btn-del">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
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

    function confirmarExclusao(id) {
        Swal.fire({
            title: 'Excluir aviso?',
            text: "Esta mensagem será removida permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'avisos.php?excluir=' + id; }
        })
    }

    <?php if(isset($_GET['apagado'])): ?>
        Swal.fire({ icon: 'success', title: 'Removido!', text: 'O aviso foi excluído com sucesso.', timer: 1500, showConfirmButton: false });
    <?php endif; ?>
    </script>
</body>
</html>
