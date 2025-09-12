<?php
// Acessar via HTTP: /tools/create_test_user_web.php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
try {
    $pdo = getConnection();
    $nome = 'Teste Web';
    $cpf = '999.999.999-99';
    $senha = password_hash('webtest', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, data_nascimento, cpf, telefone, email, senha) VALUES (?, '1990-01-01', ?, '(11)11111-1111', 'webtest@example.com', ?)");
    $stmt->execute([$nome, $cpf, $senha]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
