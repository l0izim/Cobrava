<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cobranca";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Definir charset para utf8
$conn->set_charset("utf8mb4");