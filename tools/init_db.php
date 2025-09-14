<?php
// tools/init_db.php
// Executa a inicialização do banco de dados definida em config/database.php

require_once __DIR__ . '/../config/database.php';

echo "Iniciando inicialização do banco de dados...\n";

$ok = initDatabase();

if ($ok) {
    echo "Inicialização concluída com sucesso.\n";
    exit(0);
} else {
    echo "Falha na inicialização do banco de dados. Verifique os logs.\n";
    exit(1);
}

?>
