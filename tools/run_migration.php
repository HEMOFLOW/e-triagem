<?php
// tools/run_migration.php
// Este script adiciona a coluna 'nivel_acesso' à tabela 'usuarios' se ela não existir.

include_once __DIR__ . '/../config/database.php';

echo "Iniciando migração do banco de dados...\n";

try {
    $pdo = getConnection();

    // 1. Verifica se a coluna 'nivel_acesso' já existe
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_schema = 'projeto_qr_code' 
        AND table_name = 'usuarios' 
        AND column_name = 'nivel_acesso'
    ");

    $columnExists = $stmt->fetchColumn();

    if ($columnExists > 0) {
        echo "MIGRAÇÃO IGNORADA: A coluna 'nivel_acesso' já existe na tabela 'usuarios'.\n";
    } else {
        // 2. Se não existe, adiciona a coluna
        echo "Executando migração: Adicionando coluna 'nivel_acesso'...\n";
        $pdo->exec("
            ALTER TABLE usuarios 
            ADD COLUMN nivel_acesso ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario'
        ");
        echo "MIGRAÇÃO CONCLUÍDA: A coluna 'nivel_acesso' foi adicionada com sucesso.\n";
    }

} catch (PDOException $e) {
    echo "ERRO DURANTE A MIGRAÇÃO: " . $e->getMessage() . "\n";
    echo "Verifique a conexão com o banco de dados e se a tabela 'usuarios' existe.\n";
}