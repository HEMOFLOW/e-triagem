<?php
// tools/reset_database.php
// ATENÇÃO: Este script apagará as tabelas existentes e as recriará.
// Use com cuidado.

require_once __DIR__ . '/../config/database.php';

function reset_database() {
    try {
        $pdo = getConnection();
        echo "Conectado ao banco de dados com sucesso.\n";

        // Desativar verificação de chaves estrangeiras temporariamente
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
        echo "Verificação de chaves estrangeiras desativada.\n";

        // Lista de tabelas para apagar (em ordem que não cause conflitos)
        $tables = ['agendamentos', 'questionarios', 'doadores', 'usuarios'];

        foreach ($tables as $table) {
            echo "Verificando tabela '$table'...\n";
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $pdo->exec("DROP TABLE `$table`");
                echo "Tabela '$table' apagada.\n";
            } else {
                echo "Tabela '$table' não existe, pulando.\n";
            }
        }

        // Reativar verificação de chaves estrangeiras
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
        echo "Verificação de chaves estrangeiras reativada.\n";

        echo "Reset do banco de dados concluído. As tabelas foram removidas.\n";
        echo "Iniciando a recriação das tabelas...\n";

        // Chamar a função de inicialização para recriar tudo
        initDatabase();

        echo "Banco de dados recriado e inicializado com sucesso!\n";

    } catch (PDOException $e) {
        // Se algo der errado, tentar reativar as chaves
        if (isset($pdo)) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
        }
        die("ERRO durante o reset do banco de dados: " . $e->getMessage() . "\n");
    }
}

// Executar a função
reset_database();
?>
