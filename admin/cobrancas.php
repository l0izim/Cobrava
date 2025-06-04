<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Processar pagamento se necessário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cobranca'])) {
    $id = (int)$_POST['id_cobranca'];
    $conn->query("UPDATE cobrancas SET status = 'pago' WHERE id = $id");
    exit(json_encode(['status' => 'success']));
}

// Obter todos os clientes com seus serviços
$clientes = $conn->query("
    SELECT c.*, s.nome AS servico_nome, s.valor_total 
    FROM clientes c 
    LEFT JOIN servicos s ON c.id_servico = s.id 
    ORDER BY c.nome ASC
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobranças - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
    :root {
        --sidebar-width: 250px;
        --sidebar-width-collapsed: 80px;
        --bg-dark: #173B5B;
        --bg-darker: #173B5B;
        --primary-color: #1e40af;
        --primary-hover: #1e3a8a;
        --text-light: #f8fafc;
        --text-lighter: #e2e8f0;
        --text-muted: #94a3b8;
        --card-bg: #1e293b;
        --card-bg-light: #2d3748;
        --card-border: #334155;
        --success-color: #16a34a;
        --warning-color: #d97706;
        --danger-color: #dc2626;
        --transition-speed: 0.3s;
    }
    
    * {
        box-sizing: border-box;
    }
    
    body {
        background-color: var(--bg-darker);
        color: var(--text-lighter);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        margin: 0;
        padding: 0;
        transition: all var(--transition-speed) ease;
        position: relative;
    }
    
    /* Main Content */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 1.5rem;
        transition: all var(--transition-speed) ease;
        min-height: 100vh;
    }
    
    .sidebar-collapsed + .main-content {
        margin-left: var(--sidebar-width-collapsed);
    }
    
    /* Overlay para mobile */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: none;
    }
    
    .sidebar-overlay-active {
        display: block;
    }
    
    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    /* Cards */
    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background-color: var(--primary-color);
        color: var(--text-light);
        border-bottom: 1px solid var(--card-border);
        font-weight: 600;
        padding: 1rem 1.25rem;
        border-radius: 10px 10px 0 0 !important;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    /* Tables */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table {
        color: var(--text-lighter);
        margin-bottom: 0;
        width: 100%;
    }
    
    .table th {
        background-color: var(--primary-color);
        border-color: var(--card-border);
        font-weight: 500;
        padding: 0.75rem;
        white-space: nowrap;
    }
    
    .table td {
        border-color: var(--card-border);
        vertical-align: middle;
        padding: 0.75rem;
        background-color: var(--card-bg);
    }
    
    .table-hover tbody tr:hover td {
        background-color: var(--card-bg-light);
    }
    
    /* Buttons */
    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border: none;
    }
    
    .btn-primary:hover {
        background-color: var(--primary-hover);
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Badges */
    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
        font-size: 0.85em;
    }
    
    /* Utility Classes */
    .text-muted {
        color: var(--text-muted) !important;
    }
    
    .bg-success {
        background-color: var(--success-color) !important;
    }
    
    .bg-warning {
        background-color: var(--warning-color) !important;
        color: var(--bg-dark) !important;
    }
    
    /* Modals */
    .modal-content {
        background-color: var(--card-bg);
        color: var(--text-lighter);
        border: 1px solid var(--card-border);
    }
    
    .modal-header, .modal-footer {
        border-color: var(--card-border);
    }
    
    .btn-close {
        filter: invert(1);
        opacity: 0.8;
    }
    
    /* Mobile Toggle Button */
    .mobile-menu-toggle {
        display: none;
        background: none;
        border: none;
        color: var(--text-light);
        font-size: 1.5rem;
        cursor: pointer;
        margin-right: 1rem;
    }
    
    /* ============= RESPONSIVE ADJUSTMENTS ============= */
    
    /* Large devices (desktops, 992px and up) */
    @media (min-width: 992px) {
        .main-content {
            padding: 2rem;
        }
        
        .modal-lg {
            max-width: 900px;
        }
    }
    
    /* Medium and small devices (tablets and phones, 992px and down) */
    @media (max-width: 992px) {
        /* Ajuste do conteúdo principal quando sidebar está recolhida */
        .main-content {
            margin-left: 0 !important;
            padding-top: 4rem; /* Espaço para o botão de menu */
        }
        
        /* Mostrar botão de menu mobile */
        .mobile-menu-toggle {
            display: block;
            position: fixed;
            top: 1rem;
            left: 1rem;
            background-color: var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
        }
        
        /* Ajustes de espaçamento para mobile */
        .card-body {
            padding: 1rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
        }
        
        /* Ajuste do header para mobile */
        .page-header {
            padding-top: 2rem; /* Espaço para o botão de menu */
        }
        
        /* Ajuste para modais em tablets */
        .modal-dialog {
            margin: 1rem auto;
        }
    }
    
    /* Extra small devices (phones, 575px and down) */
    @media (max-width: 575px) {
        .main-content {
            padding: 0.75rem;
            padding-top: 3.5rem; /* Mais espaço para o botão de menu */
        }
        
        .card-header {
            padding: 0.75rem;
        }
        
        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }
        
        /* Ajuste para tabelas em telas muito pequenas */
        .table-responsive {
            padding-bottom: 0.5rem;
        }
        
        .table td, .table th {
            padding: 0.4rem;
            font-size: 0.85rem;
        }
        
        /* Ajuste para botões de ação na tabela */
        .table td:last-child {
            white-space: nowrap;
        }
        
        /* Ajuste para badges em telas pequenas */
        .badge {
            padding: 0.3em 0.6em;
            font-size: 0.8em;
        }
    }
    
    /* Dark mode adjustments */
    h1, h2, h3, h4, h5, h6 {
        color: var(--text-light);
    }
    
    a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    a:hover {
        color: var(--primary-hover);
        text-decoration: underline;
    }
</style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- Overlay para fechar a sidebar no mobile -->
    <div class="sidebar-overlay"></div>
    
    <div class="main-content">
        <div class="page-header">
            <button class="mobile-menu-toggle d-lg-none">
                <i class="bi bi-list"></i>
            </button>
            <h2><i class="bi bi-credit-card me-2"></i> Gerenciar Cobranças</h2>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-people me-2"></i>
                <span>Clientes Cadastrados</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-light">Nome</th>
                                <th class="text-light">Email</th>
                                <th class="text-light">Serviço</th>
                                <th class="text-light">Valor Total</th>
                                <th class="text-light">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($clientes->num_rows > 0): ?>
                                <?php while ($cliente = $clientes->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-light"><?= htmlspecialchars($cliente['nome']) ?></td>
                                        <td class="text-light"><?= htmlspecialchars($cliente['email']) ?></td>
                                        <td class="text-light"><?= htmlspecialchars($cliente['servico_nome'] ?? '-') ?></td>
                                        <td class="text-light">R$ <?= number_format($cliente['valor_total'], 2, ',', '.') ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCobrancas<?= $cliente['id'] ?>">
                                                <i class="bi bi-eye me-1"></i> Ver Cobranças
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Nenhum cliente cadastrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modais de cobranças -->
        <?php 
        $clientes->data_seek(0); // Resetar o ponteiro do resultado
        while ($cliente = $clientes->fetch_assoc()): 
            $cobrancas = $conn->query("
                SELECT * FROM cobrancas 
                WHERE id_cliente = {$cliente['id']} 
                ORDER BY data_vencimento ASC
            ");
        ?>
            <div class="modal fade" id="modalCobrancas<?= $cliente['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cobranças de <?= htmlspecialchars($cliente['nome']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if ($cobrancas->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-light">Valor</th>
                                                <th class="text-light">Vencimento</th>
                                                <th class="text-light">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($cobranca = $cobrancas->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="text-light">R$ <?= number_format($cobranca['valor_total'], 2, ',', '.') ?></td>
                                                    <td class="text-light"><?= date('d/m/Y', strtotime($cobranca['data_vencimento'])) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $cobranca['status'] === 'pago' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($cobranca['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Nenhuma cobrança encontrada para este cliente.</p>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle da Sidebar para mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-open');
                overlay.classList.toggle('sidebar-overlay-active');
                
                if (sidebar.classList.contains('sidebar-open')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('sidebar-overlay-active');
                document.body.style.overflow = '';
            });
            
            // Ajustar ao redimensionar a janela
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('sidebar-open');
                    overlay.classList.remove('sidebar-overlay-active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Envio do formulário de pagamento via AJAX
        document.querySelectorAll('[id^="formPagamento"]').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = this.querySelector('button[type="submit"]');
                
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';
                
                try {
                    const response = await fetch('cobrancas.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        // Atualizar a tabela ou mostrar mensagem de sucesso
                        location.reload();
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao processar pagamento');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'Confirmar Pagamento';
                }
            });
        });
    </script>
</body>
</html>