<?php
// Script para criar/configurar a tabela de configuração global do sistema (idade máxima)
include_once 'database.php';
$pdo = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) NOT NULL UNIQUE,
    valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$pdo->exec($sql);

// Define idade máxima padrão se não existir
$stmt = $pdo->prepare("SELECT COUNT(*) FROM configuracoes WHERE chave = 'idade_maxima_doador'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('idade_maxima_doador', '69')");
    $stmt->execute();
}
echo "Tabela de configuração criada e idade máxima padrão definida.";
