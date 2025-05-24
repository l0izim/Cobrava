<?php
session_start();
include '../db.php';

if (!isset($_SESSION['cliente_id'])) {
    exit('erro');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id_cobranca'];

    // Verificar se a cobrança pertence ao cliente
    $check = $conn->query("SELECT id FROM cobrancas WHERE id = $id AND id_cliente = {$_SESSION['cliente_id']}");
    
    if ($check->num_rows === 1) {
        // Atualiza status para "pago"
        $conn->query("UPDATE cobrancas SET status = 'pago' WHERE id = $id");
        
        // Verificar se todas as cobranças estão pagas
        $pendentes = $conn->query("SELECT COUNT(*) as total FROM cobrancas WHERE id_cliente = {$_SESSION['cliente_id']} AND status = 'pendente'")->fetch_assoc();
        
        if ($pendentes['total'] == 0) {
            // Atualizar status do cliente se todas as cobranças estiverem pagas
            $conn->query("UPDATE clientes SET status = 'ativo' WHERE id = {$_SESSION['cliente_id']}");
        }
        
        echo 'ok';
    } else {
        echo 'erro';
    }
}