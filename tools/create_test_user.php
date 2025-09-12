<?php
// CLI: php tools/create_test_user.php
require_once __DIR__ . '/../config/database.php';
$pdo = getConnection();
$nome = 'Teste API';
$cpf = '000.000.000-00';
$senha = password_hash('test123', PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, data_nascimento, cpf, telefone, email, senha) VALUES (?, '1990-01-01', ?, '(11)00000-0000', 'teste@example.com', ?)");
    $stmt->execute([$nome, $cpf, $senha]);
    $id = $pdo->lastInsertId();
    echo "Usuario criado com id: $id\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
