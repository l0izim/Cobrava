<?php
session_start();
include 'db.php';

// Configurações de segurança
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Inicializa variável de erro
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Verificação do admin (hardcoded)
    if ($email == 'admin@admin.com' && $senha == 'admin') {
        $_SESSION['admin'] = true;
        header("Location: admin/admin.php");
        exit;
    }

    // Verificação do cliente
    $stmt = $conn->prepare("SELECT id, nome, senha, status FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $cliente = $result->fetch_assoc();
        if (password_verify($senha, $cliente['senha'])) {
            if ($cliente['status'] === 'ativo') {
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                header("Location: cliente/contas_pagas.php");
                exit;
            } else {
                $erro = "Sua conta está inativa. Entre em contato com o suporte.";
            }
        }
    }

    // Se chegou aqui, login falhou
    if (empty($erro)) {
        $erro = "Credenciais inválidas! Por favor, tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Cobrança</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-dark: #0f172a;
            --secondary-dark: #1e293b;
            --accent-color: #173B5B;
            --accent-hover: #173B5B;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            background: linear-gradient(135deg, #173B5B 0%, #D6E7F5 100%);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            margin: auto;
            padding: 2.5rem;
            background-color: var(--secondary-dark);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transform: translateY(0);
            transition: var(--transition);
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2.5rem;
            font-weight: 700;
            font-size: 2rem;
            color: var(--text-light);
            letter-spacing: -0.5px;
        }
        
        .logo i {
            color: var(--accent-color);
            font-size: 2.2rem;
            vertical-align: middle;
        }
        
        .form-control {
            background-color: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 0.75rem 1rem;
            height: 48px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--accent-color);
            color: var(--text-light);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            height: 48px;
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background-color: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            width: 44px;
            justify-content: center;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 0.75rem 1rem;
        }
        
        .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .logo {
                font-size: 1.75rem;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center px-3">
        <div class="login-container">
            <div class="logo">
                <i class="bi bi-credit-card-fill me-2"></i>
                COBRAVA
            </div>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($erro) ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
                </button>
            </form>
            
            <div class="footer-text">
                © <?= date('Y') ?> Cobrava - Todos os direitos reservados
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Foco automático no campo de email
            document.getElementById('email').focus();
            
            // Efeito de hover mais suave
            const loginContainer = document.querySelector('.login-container');
            loginContainer.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease-out';
            });
        });
    </script>
</body>
</html>