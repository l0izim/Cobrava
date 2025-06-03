<?php 
session_start(); 
include '../db.php';

if (!isset($_SESSION['cliente_id'])) {
  header("Location: ../login.php"); 
  exit; 
}

$id_cliente = $_SESSION['cliente_id'];

// Consulta protegida contra SQL Injection
// Codigo feito por IA
$stmt = $conn->prepare("SELECT cobrancas.*, servicos.nome AS servico FROM cobrancas JOIN servicos ON cobrancas.id_servico = servicos.id WHERE cobrancas.id_cliente = ?");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minhas Cobranças</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-dark: #0f172a;
      --secondary-dark: #1e293b;
      --accent-color: #1e40af;
      --accent-hover: #1e3a8a;
      --text-light: #f8fafc;
      --text-lighter: #e2e8f0;
      --text-muted: #94a3b8;
      --success-color: #16a34a;
      --warning-color: #d97706;
      --danger-color: #dc2626;
      --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    body {
      background-color: var(--primary-dark);
      color: var(--text-light);
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Sidebar */
    .sidebar {
      width: 280px;
      height: 100vh;
      position: fixed;
      top: 0; 
      left: 0;
      background-color: var(--secondary-dark);
      color: white;
      overflow-y: auto;
      box-shadow: var(--card-shadow);
      border-right: 1px solid rgba(255, 255, 255, 0.05);
      transition: var(--transition);
      z-index: 1000;
    }
    
    /* Main Content */
    .main-content {
      margin-left: 280px;
      padding: 2rem;
      transition: var(--transition);
      min-height: 100vh;
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
    
    /* Container da tabela com rolagem horizontal */
    .table-container {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch; /* Rolagem suave no iOS */
      margin-bottom: 1.5rem;
      border-radius: 8px;
      background-color: var(--secondary-dark);
    }
    
    /* Estilo da tabela */
    .table {
      color: var(--text-light);
      margin-bottom: 0;
      min-width: 800px; /* Largura mínima para forçar rolagem em telas pequenas */
      width: 100%;
    }
    
    .table-dark {
      background-color: var(--secondary-dark);
    }
    
    .table-striped>tbody>tr:nth-of-type(odd) {
      background-color: rgba(255, 255, 255, 0.03);
    }
    
    .table th {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
      font-weight: 500;
      padding: 1rem;
      white-space: nowrap;
    }
    
    .table td {
      border-color: rgba(255, 255, 255, 0.05);
      padding: 1rem;
      vertical-align: middle;
    }
    
    /* Badges */
    .badge {
      font-weight: 500;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      font-size: 0.75rem;
    }
    
    /* Buttons */
    .btn {
      padding: 0.5rem 1rem;
      font-weight: 500;
      transition: var(--transition);
    }
    
    .btn-sm {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }
    
    .btn-success {
      background-color: var(--success-color);
      border: none;
    }
    
    .btn-success:hover {
      background-color: #15803d;
      transform: translateY(-2px);
    }
    
    /* Mobile Toggle Button */
    .mobile-menu-toggle {
      display: none;
      background-color: var(--accent-color);
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      align-items: center;
      justify-content: center;
      z-index: 1001;
    }
    
    /* Modals */
    .modal-content {
      background-color: var(--secondary-dark);
      border: 1px solid rgba(255, 255, 255, 0.05);
      color: var(--text-light);
    }
    
    .modal-header, .modal-footer {
      border-color: rgba(255, 255, 255, 0.05);
    }
    
    .btn-close {
      filter: invert(1);
      opacity: 0.8;
    }
    
    /* Forms */
    .form-control, .form-select {
      background-color: rgba(15, 23, 42, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-light);
      padding: 0.75rem 1rem;
    }
    
    .form-control:focus, .form-select:focus {
      background-color: rgba(15, 23, 42, 0.7);
      border-color: var(--accent-color);
      color: var(--text-light);
      box-shadow: 0 0 0 0.25rem rgba(30, 64, 175, 0.25);
    }
    
    .form-control:disabled {
      background-color: rgba(15, 23, 42, 0.3);
      color: var(--text-muted);
    }
    
    /* Alerts */
    .alert {
      border-radius: 8px;
      border: none;
    }
    
    /* Indicador de rolagem para mobile */
    .scroll-indicator {
      display: none;
      text-align: center;
      color: var(--text-muted);
      font-size: 0.8rem;
      margin-top: 0.5rem;
    }
    
    /* ============= RESPONSIVE ADJUSTMENTS ============= */
    
    /* Large devices (desktops, 992px and up) */
    @media (min-width: 992px) {
      .modal-lg {
        max-width: 800px;
      }
    }
    
    /* Medium and small devices (tablets and phones, 992px and down) */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        width: 280px;
      }
      
      .sidebar-open {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        padding-top: 4rem;
      }
      
      .mobile-menu-toggle {
        display: flex;
        position: fixed;
        top: 1rem;
        left: 1rem;
      }
      
      .table th, .table td {
        padding: 0.75rem;
      }
      
      .scroll-indicator {
        display: block;
      }
    }
    
    /* Extra small devices (phones, 575px and down) */
    @media (max-width: 575px) {
      .main-content {
        padding: 1rem;
        padding-top: 3.5rem;
      }
      
      .table-container {
        margin-left: -1rem;
        margin-right: -1rem;
        width: calc(100% + 2rem);
      }
      
      .table th, .table td {
        padding: 0.5rem;
        font-size: 0.85rem;
      }
      
      .badge {
        padding: 0.4rem 0.6rem;
        font-size: 0.7rem;
      }
      
      .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
      }
      
      /* Ajuste para campos de formulário em telas pequenas */
      .modal-body .row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }
  </style>
</head>
<body>

    <?php include 'sidebar_cliente.php'; ?>
    
    <!-- Overlay para fechar a sidebar no mobile -->
    <div class="sidebar-overlay"></div>
    
    <div class="main-content">
        <div class="page-header">
            <button class="mobile-menu-toggle d-lg-none">
                <i class="bi bi-list"></i>
            </button>
            <h2><i class="bi bi-speedometer2 me-2"></i> Minhas Cobranças</h2>
        </div>
  <!--a partir da pessoa cadastrada é necessario q ela tenha 1 cobranca para aparece, caso nao tenho nenhum-->
    <?php if ($res->num_rows > 0): ?>
      <div class="table-container">
        <table class="table table-dark table-striped table-hover">
          <thead>
            <tr>
              <th><i class="bi bi-tag me-2"></i> Serviço</th>
              <th><i class="bi bi-currency-dollar me-2"></i> Valor</th>
              <th><i class="bi bi-calendar-check me-2"></i> Vencimento</th>
              <th><i class="bi bi-info-circle me-2"></i> Status</th>
              <th><i class="bi bi-gear me-2"></i> Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $res->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['servico']) ?></td>
                <td>R$ <?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                <td><?= date('d/m/Y', strtotime($row['data_vencimento'])) ?></td>
                <td>
                  <?php if ($row['status'] === 'pago'): ?>
                    <span class="badge bg-success">
                      <i class="bi bi-check-circle me-1"></i> Pago
                    </span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">
                      <i class="bi bi-exclamation-triangle me-1"></i> Pendente
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <!--se o status tiver cobrancas pendentes ira aparecerar o
                   botao para pagar e o modal assim q for pressionado-->
                  <?php if ($row['status'] === 'pendente'): ?
                    <button class="btn btn-success btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalPagamento" 
                            data-id="<?= (int)$row['id'] ?>" 
                            data-servico="<?= htmlspecialchars($row['servico']) ?>" 
                            data-valor="<?= number_format($row['valor_total'], 2, ',', '.') ?>">
                      <i class="bi bi-credit-card me-1"></i> Pagar
                    </button>
                  <?php else: ?>
                    <span class="text-light">Nenhuma ação</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div class="scroll-indicator d-lg-none">
        <i class="bi bi-arrow-left-right"></i> Role horizontalmente para ver mais informações
      </div>
    <?php else: ?>
      <div class="alert alert-info d-flex align-items-center">
        <i class="bi bi-info-circle-fill me-2"></i>
        Você não possui cobranças no momento.
      </div>
    <?php endif; ?>
  </div>

  <!-- MODAL DE PAGAMENTO -->
  <div class="modal fade" id="modalPagamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="formPagamento" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-credit-card me-2"></i> Realizar Pagamento
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_cobranca" id="id_cobranca">
          <div class="mb-3">
            <label class="form-label">Serviço</label>
            <input type="text" class="form-control" id="servico_nome" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Valor</label>
            <input type="text" class="form-control" id="valor_cobranca" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Forma de Pagamento</label>
            <select name="forma_pagamento" class="form-select" required>
              <option value="" selected disabled>Selecione...</option>
              <option value="cartao">Cartão de Crédito</option>
              <option value="pix">PIX</option>
              <option value="boleto">Boleto Bancário</option>
            </select>
          </div>
          <div class="mb-3" id="cartaoFields" style="display: none;">
            <label class="form-label">Número do Cartão</label>
            <input type="text" name="numero_cartao" class="form-control" placeholder="0000 0000 0000 0000">
            <div class="row mt-2">
              <div class="col-md-6">
                <label class="form-label">Validade</label>
                <input type="text" name="validade" class="form-control" placeholder="MM/AA">
              </div>
              <div class="col-md-6">
                <label class="form-label">CVV</label>
                <input type="text" name="cvv" class="form-control" placeholder="123">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle me-1"></i> Confirmar Pagamento
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL DE SUCESSO -->
  <div class="modal fade" id="modalSucesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content bg-success text-white text-center p-4 border-0">
        <div class="mb-3">
          <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
        </div>
        <h5 class="modal-title">Pagamento realizado com sucesso!</h5>
        <p class="mb-4">Obrigado pela sua confiança.</p>
        <div>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="location.reload()">
            <i class="bi bi-arrow-left me-1"></i> Voltar
          </button>
        </div>
      </div>
    </div>
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

    // Configuração do modal de pagamento
    const modalPagamento = document.getElementById('modalPagamento');
    if (modalPagamento) {
      modalPagamento.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const servico = button.getAttribute('data-servico');
        const valor = button.getAttribute('data-valor');

        document.getElementById('id_cobranca').value = id;
        document.getElementById('servico_nome').value = servico;
        document.getElementById('valor_cobranca').value = "R$ " + valor;
      });
    }

    // Mostrar/ocultar campos de cartão
    const formaPagamento = document.querySelector('select[name="forma_pagamento"]');
    if (formaPagamento) {
      formaPagamento.addEventListener('change', function() {
        const cartaoFields = document.getElementById('cartaoFields');
        cartaoFields.style.display = this.value === 'cartao' ? 'block' : 'none';
      });
    }

    // Envio do formulário de pagamento
    const formPagamento = document.getElementById('formPagamento');
    if (formPagamento) {
      formPagamento.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processando...';

        try {
          const response = await fetch('pagar.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.text();
          
          if (result.trim() === 'ok') {
            const modal = bootstrap.Modal.getInstance(modalPagamento);
            modal.hide();
            
            const modalSucesso = new bootstrap.Modal(document.getElementById('modalSucesso'));
            modalSucesso.show();
          } else {
            alert("Erro ao processar pagamento. Por favor, tente novamente.");
          }
        } catch (error) {
          alert("Erro na conexão. Verifique sua internet e tente novamente.");
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirmar Pagamento';
        }
      });
    }
  </script>
</body>
</html>