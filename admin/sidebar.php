<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <!-- Botão de Toggle para Mobile -->
    <div class="sidebar-toggle d-lg-none">
        <button class="btn btn-toggle">
            <i class="bi bi-list"></i>
        </button>
    </div>
    
    <!-- Logo/Cabeçalho -->
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="bi bi-credit-card fs-4"></i>
            <span class="logo-text">Cobrava</span>
        </div>
    </div>
    
    <!-- Itens do Menu -->
    <ul class="sidebar-menu">
        <li class="menu-item <?= $current_page === 'admin.php' ? 'active' : '' ?>">
            <a href="admin.php" class="menu-link">
                <i class="bi bi-speedometer2"></i>
                <span class="menu-text">Painel</span>
            </a>
        </li>
        <li class="menu-item <?= $current_page === 'cobrancas.php' ? 'active' : '' ?>">
            <a href="cobrancas.php" class="menu-link">
                <i class="bi bi-credit-card"></i>
                <span class="menu-text">Cobranças</span>
            </a>
        </li>
    </ul>
    
    <!-- Rodapé da Sidebar -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name">Administrador</div>
                <div class="user-role">Super Admin</div>
            </div>
        </div>
        <a href="../logout.php" class="btn btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sair</span>
        </a>
    </div>
</div>

<div class="sidebar-overlay"></div>

<style>
    /* Variáveis de estilo */
    :root {
        --sidebar-width: 250px;
        --sidebar-width-collapsed: 70px;
        --sidebar-bg: #0f172a;
        --sidebar-color: #e2e8f0;
        --sidebar-active-bg: #1e40af;
        --sidebar-active-color: #ffffff;
        --sidebar-hover-bg: #1e293b;
        --sidebar-border: #1e293b;
        --transition-speed: 0.3s;
    }
    
    /* Estilo base da Sidebar */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: var(--sidebar-bg);
        color: var(--sidebar-color);
        border-right: 1px solid var(--sidebar-border);
        transition: all var(--transition-speed) ease;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }
    
    /* Cabeçalho da Sidebar */
    .sidebar-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--sidebar-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 60px;
    }
    
    .logo-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .logo-text {
        font-weight: 600;
        font-size: 1.1rem;
        transition: opacity var(--transition-speed);
    }
    
    /* Menu da Sidebar */
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 1rem 0;
        flex-grow: 1;
        overflow-y: auto;
    }
    
    .menu-item {
        margin: 0.25rem 0.5rem;
        border-radius: 6px;
        overflow: hidden;
    }
    
    .menu-item.active {
        background-color: var(--sidebar-active-bg);
    }
    
    .menu-item.active .menu-link {
        color: var(--sidebar-active-color);
    }
    
    .menu-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: var(--sidebar-color);
        text-decoration: none;
        transition: all 0.2s;
        gap: 0.75rem;
    }
    
    .menu-link:hover {
        background-color: var(--sidebar-hover-bg);
    }
    
    .menu-link i {
        font-size: 1.25rem;
        width: 24px;
        text-align: center;
    }
    
    .menu-text {
        transition: opacity var(--transition-speed);
    }
    
    /* Rodapé da Sidebar */
    .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid var(--sidebar-border);
        margin-top: auto;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-avatar i {
        font-size: 1.25rem;
    }
    
    .user-details {
        line-height: 1.2;
    }
    
    .user-name {
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .user-role {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .btn-logout {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.5rem 1rem;
        background-color: transparent;
        border: 1px solid #334155;
        color: var(--sidebar-color);
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .btn-logout:hover {
        background-color: #1e293b;
        color: #ffffff;
    }
    
    /* Botão de Toggle */
    .sidebar-toggle {
        display: none;
        padding: 1rem;
        border-bottom: 1px solid var(--sidebar-border);
    }
    
    .btn-toggle {
        background: none;
        border: none;
        color: var(--sidebar-color);
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    /* Sidebar Collapsed */
    .sidebar-collapsed {
        width: var(--sidebar-width-collapsed);
    }
    
    .sidebar-collapsed .logo-text,
    .sidebar-collapsed .menu-text,
    .sidebar-collapsed .user-details,
    .sidebar-collapsed .btn-logout span {
        opacity: 0;
        width: 0;
        height: 0;
        overflow: hidden;
        display: inline-block;
    }
    
    .sidebar-collapsed .sidebar-header,
    .sidebar-collapsed .menu-link {
        justify-content: center;
    }
    
    .sidebar-collapsed .menu-item {
        margin: 0.25rem 0;
    }
    
    /* Overlay para mobile */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
        transition: all var(--transition-speed) ease;
    }
    
    .sidebar-overlay-active {
        display: block;
    }
    
    /* Responsividade */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            width: var(--sidebar-width);
        }
        
        .sidebar-open {
            transform: translateX(0);
        }
        
        .sidebar-toggle {
            display: block;
        }
        
        .main-content {
            margin-left: 0 !important;
            transition: margin-left var(--transition-speed) ease;
        }
    }
</style>

<script>
    // Toggle da Sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const btnToggle = document.querySelector('.btn-toggle');
        const overlay = document.querySelector('.sidebar-overlay');
        
        // Toggle sidebar em desktop
        function toggleDesktopSidebar() {
            sidebar.classList.toggle('sidebar-collapsed');
            
            if (sidebar.classList.contains('sidebar-collapsed')) {
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                localStorage.removeItem('sidebarCollapsed');
            }
        }
        
        // Toggle sidebar em mobile
        function toggleMobileSidebar() {
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('sidebar-overlay-active');
            
            if (sidebar.classList.contains('sidebar-open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Verificar estado inicial
        if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth >= 992) {
            sidebar.classList.add('sidebar-collapsed');
        }
        
        // Configurar evento de clique
        btnToggle.addEventListener('click', function() {
            if (window.innerWidth >= 992) {
                toggleDesktopSidebar();
            } else {
                toggleMobileSidebar();
            }
        });
        
        // Fechar sidebar ao clicar no overlay (mobile)
        overlay.addEventListener('click', function() {
            toggleMobileSidebar();
        });
        
        // Ajustar ao redimensionar a janela
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('sidebar-overlay-active');
                document.body.style.overflow = '';
            } else if (window.innerWidth < 992) {
                sidebar.classList.remove('sidebar-collapsed');
            }
        });
    });
</script>