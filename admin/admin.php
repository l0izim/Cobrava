<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Processar formulários
// Todo METHOD post ira ser lancado para o bd por aqui
// Cada input tem o name acao onde ha dois value, cadastrar_servico e cadastrar_cliente onde estara dentro de um form
// ao dar submit no form ele ativara o case e pegar oque foi cadastrado de dado, assim armazenando no bd.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'cadastrar_servico':
                $nome = $conn->real_escape_string($_POST['nome_servico']);
                $descricao = $conn->real_escape_string($_POST['descricao_servico']);
                $valor = str_replace(['.', ','], ['', '.'], $_POST['valor_servico']);
                
                $stmt = $conn->prepare("INSERT INTO servicos (nome, descricao, valor_total) VALUES (?, ?, ?)");
                $stmt->bind_param("ssd", $nome, $descricao, $valor);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $success = "Serviço cadastrado com sucesso!";
                } else {
                    $error = "Erro ao cadastrar serviço.";
                }
                break;
                
            case 'cadastrar_cliente':
                $nome = $conn->real_escape_string($_POST['nome_cliente']);
                $email = $conn->real_escape_string($_POST['email_cliente']);
                $senha = password_hash($_POST['senha_cliente'], PASSWORD_DEFAULT);
                $id_servico = (int)$_POST['servico_cliente'];
                $parcelas = (int)$_POST['parcelas_cliente'];
                $status = $conn->real_escape_string($_POST['status_cliente']);
                
                // Calcular valor da parcela
                $servico = $conn->query("SELECT valor_total FROM servicos WHERE id = $id_servico")->fetch_assoc();
                $valor_parcela = $servico['valor_total'] / $parcelas;
                
                $stmt = $conn->prepare("INSERT INTO clientes (nome, email, senha, id_servico, parcelas, valor_parcela, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiids", $nome, $email, $senha, $id_servico, $parcelas, $valor_parcela, $status);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $cliente_id = $stmt->insert_id;
                    
                    // Criar cobranças para o cliente
                    $data_vencimento = date('Y-m-d');
                    for ($i = 1; $i <= $parcelas; $i++) {
                        $data_vencimento = date('Y-m-d', strtotime("+$i month"));
                        $conn->query("INSERT INTO cobrancas (id_cliente, id_servico, valor_total, data_vencimento, status) VALUES ($cliente_id, $id_servico, $valor_parcela, '$data_vencimento', 'pendente')");
                    }
                    
                    $success = "Cliente cadastrado com sucesso!";
                } else {
                    $error = "Erro ao cadastrar cliente.";
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
    :root {
        --sidebar-width: 250px;
        --sidebar-width-collapsed: 80px;
        --bg-dark: #0f172a;
        --bg-darker: #020617;
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
    
    .mobile-menu-toggle {
        display: none;
        background: none;
        border: none;
        color: var(--text-light);
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
        z-index: 1001;
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
    
    /* Forms */
    .form-control, .form-select {
        background-color: var(--card-bg-light);
        border: 1px solid var(--card-border);
        color: var(--text-light);
        width: 100%;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(30, 64, 175, 0.25);
        background-color: var(--card-bg-light);
        color: var(--text-light);
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
    
    /* Responsive Grid */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -0.75rem;
        margin-left: -0.75rem;
    }
    
    .col-md-6, .col-lg-6 {
        padding-right: 0.75rem;
        padding-left: 0.75rem;
        flex: 0 0 100%;
        max-width: 100%;
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
    
    /* ============= RESPONSIVE ADJUSTMENTS ============= */
    
    /* Medium devices (tablets, 768px and up) */
    @media (min-width: 768px) {
        .main-content {
            padding: 2rem;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        /* Ajuste para tabelas em tablets */
        .table-responsive {
            padding-bottom: 0.5rem;
        }
    }
    
    /* Large devices (desktops, 992px and up) */
    @media (min-width: 992px) {
        .col-lg-6 {
            flex: 0 0 50%;
            max-width: 50%;
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
        
        /* Ajuste para formulários em telas muito pequenas */
        .row.g-2 > [class*="col-"] {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        /* Ajuste para botões de ação na tabela */
        .table td:last-child {
            white-space: nowrap;
        }
    }
    
    /* Dark mode adjustments */
    .form-control::placeholder {
        color: var(--text-muted);
        opacity: 1;
    }
    
    select.form-control:not([size]):not([multiple]) {
        height: calc(2.25rem + 2px);
    }
    
    /* Ensure text is always readable */
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
            <h2><i class="bi bi-speedometer2 me-2"></i> Painel Administrativo</h2>
        </div>

        <!-- Mensagens de sucesso/erro -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-plus-circle me-2"></i>
                        <span>Cadastrar Serviço</span>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="acao" value="cadastrar_servico">
                            
                            <div class="mb-3">
                                <label for="nome_servico" class="form-label text-light">Nome do Serviço</label>
                                <input type="text" class="form-control" id="nome_servico" name="nome_servico" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descricao_servico" class="form-label text-light">Descrição</label>
                                <textarea class="form-control" id="descricao_servico" name="descricao_servico" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="valor_servico" class="form-label text-light">Valor Total (R$)</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" id="valor_servico" name="valor_servico" placeholder="99,90" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-save me-2 text-light"></i> Cadastrar Serviço
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-person-plus me-2"></i>
                        <span>Cadastrar Cliente</span>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="acao" value="cadastrar_cliente">
                            
                            <div class="mb-3">
                                <label for="nome_cliente" class="form-label text-light">Nome Completo</label>
                                <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email_cliente" class="form-label text-light">Email</label>
                                <input type="email" class="form-control" id="email_cliente" name="email_cliente" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha_cliente" class="form-label text-light">Senha</label>
                                <input type="password" class="form-control" id="senha_cliente" name="senha_cliente" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="servico_cliente" class="form-label text-light">Serviço</label>
                                <select class="form-select" id="servico_cliente" name="servico_cliente" required>
                                    <option value="">Selecione um serviço...</option>
                                    <?php 
                                    //vai procurar os servicos cadastradas do bd
                                    $servicos = $conn->query("SELECT * FROM servicos ORDER BY nome ASC");
                                    while ($servico = $servicos->fetch_assoc()): ?>
                                        <option value="<?= $servico['id'] ?>">
                                            <?= htmlspecialchars($servico['nome']) ?> - R$ <?= number_format($servico['valor_total'], 2, ',', '.') ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="parcelas_cliente" class="form-label text-light">Parcelas</label>
                                    <input type="number" class="form-control" id="parcelas_cliente" name="parcelas_cliente" min="1" value="1" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="status_cliente" class="form-label text-light">Status</label>
                                    <select class="form-select" id="status_cliente" name="status_cliente" required>
                                        <option value="ativo" selected>Ativo</option>
                                        <option value="inativo">Inativo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                                <i class="bi bi-person-check me-2"></i> Cadastrar Cliente
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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
                                <th class="text-light">Parcelas</th>
                                <th class="text-light">Valor Parcela</th>
                                <th class="text-light">Status</th>
                                <th class="text-light">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            //Verificar no bd na tabela cliente
                            $clientes = $conn->query(" 
                                SELECT c.*, s.nome AS servico_nome 
                                FROM clientes c
                                LEFT JOIN servicos s ON c.id_servico = s.id
                                ORDER BY c.nome ASC
                            ");
                            //Verifica se a consulta retornou pelo menos um cliente. se retornar ela exibira os dados cadastrados
                            //se nao achar ela retornar "nenhum cliente cadastrado
                            if ($clientes->num_rows > 0): ?> 
                                <?php while ($cliente = $clientes->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-light"><?= htmlspecialchars($cliente['nome']) ?></td>
                                        <td class="text-light"><?= htmlspecialchars($cliente['email']) ?></td>
                                        <td class="text-light"><?= htmlspecialchars($cliente['servico_nome'] ?? 'N/A') ?></td>
                                        <td class="text-light"><?= $cliente['parcelas'] ?></td>
                                        <td class="text-light">R$ <?= number_format($cliente['valor_parcela'], 2, ',', '.') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $cliente['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($cliente['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="cobrancas.php?cliente=<?= $cliente['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-credit-card"></i> Cobranças
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhum cliente cadastrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para valor monetário
        document.getElementById('valor_servico')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
            value = value.replace(/(\d)(\d{3}),/g, "$1.$2,");
            e.target.value = value;
        });

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
    </script>
</body>
</html>