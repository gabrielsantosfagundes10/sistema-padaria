<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    :root {
        --sidebar-width: 270px;
        --sidebar-bg: #1e1a19; /* Marrom grafite fechado */
        --accent-color: #d69e88; /* Tom canela/pão exclusivo */
        --accent-soft: rgba(214, 158, 136, 0.08);
        --text-muted: #8e8684;
        --text-bright: #ffffff;
        --danger: #ef4444;
        --nav-radius: 12px;
        --transition-premium: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* --- ESTRUTURA SIDEBAR (DESKTOP) --- */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        background-color: var(--sidebar-bg);
        position: fixed;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
        z-index: 99999;
        border-right: 1px solid rgba(255, 255, 255, 0.03);
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        font-family: 'Montserrat', sans-serif;
        
        /* Anti-Flicker / Anti-Espasmo */
        will-change: transform;
        transform: translateZ(0); 
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        contain: layout;
    }

    /* Logo e Nome */
    .brand-area {
        padding: 40px 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .brand-logo {
        width: 90px;
        height: auto;
        transition: var(--transition-premium);
    }

    .brand-name {
        color: var(--text-bright);
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 4px;
        text-transform: uppercase;
        opacity: 0.9;
    }

    /* Container de Navegação */
    .nav-container {
        flex-grow: 1;
        overflow-y: auto;
        padding: 0 20px;
        -webkit-overflow-scrolling: touch;
    }

    .nav-container::-webkit-scrollbar {
        width: 4px;
    }

    .nav-container::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
    }

    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-item {
        margin-bottom: 4px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 18px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        border-radius: var(--nav-radius);
        transition: var(--transition-premium);
        position: relative;
    }

    .nav-link i {
        width: 25px;
        font-size: 16px;
        margin-right: 12px;
    }

    .nav-link:hover {
        color: var(--text-bright);
        background: rgba(255, 255, 255, 0.04);
    }

    /* Estado Ativo */
    .nav-item.active .nav-link {
        background: var(--accent-soft);
        color: var(--accent-color);
    }

    .nav-item.active .nav-link::after {
        content: "";
        position: absolute;
        right: 12px;
        width: 5px;
        height: 5px;
        background: var(--accent-color);
        border-radius: 50%;
        box-shadow: 0 0 8px var(--accent-color);
    }

    /* Logout */
    .logout-area {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px;
        background: rgba(255, 255, 255, 0.03);
        color: var(--text-muted);
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: var(--nav-radius);
        transition: var(--transition-premium);
    }

    /* --- AJUSTE MOBILE PWA (BARRA INFERIOR ROLÁVEL ANTI-ESPASMO) --- */
    @media (max-width: 768px) {
        .sidebar {
            width: 100% !important;
            height: 65px !important;
            min-height: 65px !important;
            top: auto !important;
            bottom: 0 !important;
            left: 0 !important;
            flex-direction: row !important;
            background-color: #1e1a19 !important;
            border-right: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: env(safe-area-inset-bottom);
            box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.5);
            
            /* Bloqueia o "espasmo" visual forçando o hardware */
            contain: size layout style !important;
            transform: translateZ(0) !important;
        }

        /* Esconde elementos desnecessários no mobile */
        .brand-area, .logout-area, .brand-name, .nav-link span, .nav-item.active .nav-link::after {
            display: none !important;
        }

        .nav-container {
            padding: 0 !important;
            overflow-x: auto !important; /* Scroll horizontal recuperado */
            overflow-y: hidden !important;
            display: flex !important;
            width: 100% !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .nav-container::-webkit-scrollbar {
            display: none;
        }

        .nav-list {
            display: flex !important;
            flex-direction: row !important;
            width: auto !important;
            height: 100%;
            align-items: center;
            padding: 0 10px !important;
            margin: 0;
            white-space: nowrap !important; /* Impede que os itens quebrem linha */
        }

        .nav-item {
            margin-bottom: 0 !important;
            flex: 0 0 70px !important; /* Largura fixa para cada ícone rolável */
            display: flex;
            justify-content: center;
        }

        .nav-link {
            padding: 0 !important;
            width: 100%;
            height: 65px;
            justify-content: center;
            background: transparent !important;
        }

        .nav-link i {
            margin-right: 0 !important;
            font-size: 22px !important;
            color: var(--text-muted);
        }

        .nav-item.active .nav-link i {
            color: var(--accent-color);
            text-shadow: 0 0 10px rgba(214, 158, 136, 0.4);
        }

        /* Ajuste do corpo para o conteúdo não sumir atrás da barra */
        body {
            padding-bottom: calc(70px + env(safe-area-inset-bottom)) !important;
        }
    }
</style>

<aside class="sidebar" id="mainSidebar">
    <div class="brand-area">
        <img src="images/paovidalogo.png" alt="Logo" class="brand-logo">
        <div class="brand-name">Pão da Vida</div>
    </div>

    <div class="nav-container" id="pwa-nav-scroll">
        <ul class="nav-list">
            <li class="nav-item <?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php" class="nav-link">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Painel</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'avisos') ? 'active' : ''; ?>">
                <a href="avisos.php" class="nav-link">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Avisos</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'encomendas') ? 'active' : ''; ?>">
                <a href="encomendas.php" class="nav-link">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>Encomendas</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'atrasos') ? 'active' : ''; ?>">
                <a href="atrasos.php" class="nav-link">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Atrasos</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'clientes') ? 'active' : ''; ?>">
                <a href="contas_clientes.php" class="nav-link">
                    <i class="fa-solid fa-user-group"></i>
                    <span>Clientes</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'compras') ? 'active' : ''; ?>">
                <a href="lista_compras.php" class="nav-link">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Compras</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'financas') ? 'active' : ''; ?>">
                <a href="contas_padaria.php" class="nav-link">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Finanças</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'caixa') ? 'active' : ''; ?>">
                <a href="fechamento.php" class="nav-link">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Caixa</span>
                </a>
            </li>

            <li class="nav-item <?php echo ($activePage == 'relatorios') ? 'active' : ''; ?>">
                <a href="verificar_acesso.php" class="nav-link">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Relatórios</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="logout-area">
        <a href="logout.php" class="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Sair</span>
        </a>
    </div>
</aside>

<script>
    (function() {
        const navScroll = document.getElementById('pwa-nav-scroll');
        const isMobile = window.innerWidth <= 768;

        // 1. Manter posição do scroll ao mudar de página (Persistência)
        const currentScroll = sessionStorage.getItem('pwa_nav_pos');
        if (isMobile && currentScroll) {
            navScroll.scrollLeft = parseInt(currentScroll);
        }

        // 2. Salvar posição antes de descarregar a página
        window.addEventListener('beforeunload', () => {
            if (isMobile) {
                sessionStorage.setItem('pwa_nav_pos', navScroll.scrollLeft);
            }
        });

        // 3. Centralizar o item ativo no carregamento inicial
        if (isMobile && !currentScroll) {
            const activeItem = navScroll.querySelector('.nav-item.active');
            if (activeItem) {
                const centerOffset = activeItem.offsetLeft - (window.innerWidth / 2) + (activeItem.offsetWidth / 2);
                navScroll.scrollLeft = centerOffset;
            }
        }
    })();
</script>