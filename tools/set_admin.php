<?php
// tools/set_admin.php
include_once __DIR__ . '/../config/database.php';

echo "Iniciando script para definir administrador...\n";

// CPF e Senha fornecidos
$cpf_admin = '29156413823'; // CPF sem máscara
$senha_admin_raw = '@Ed85962u';

try {
    $pdo = getConnection();
    
    // 1. Verificar se o usuário com este CPF existe
    // Tenta encontrar com e sem máscara para garantir
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ? OR cpf = ?");
    $stmt->execute([$cpf_admin, '291.564.138-23']);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        // 2. Se existe, atualiza o nível de acesso para 'admin' e reseta a senha
        echo "Usuário encontrado. Atualizando para admin e redefinindo a senha...\n";
        $senha_hash = password_hash($senha_admin_raw, PASSWORD_DEFAULT);
        
        $update_stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso = 'admin', senha = ? WHERE id = ?");
        if ($update_stmt->execute([$senha_hash, $usuario['id']])) {
            echo "SUCESSO: Usuário com CPF {$usuario['cpf']} agora é um administrador.\n";
        } else {
            echo "ERRO: Falha ao atualizar o usuário.\n";
        }
    } else {
        // 3. Se não existe, cria um novo usuário admin
        echo "Usuário não encontrado. Criando um novo usuário administrador...\n";
        $senha_hash = password_hash($senha_admin_raw, PASSWORD_DEFAULT);
        
        $insert_stmt = $pdo->prepare(
            "INSERT INTO usuarios (nome, data_nascimento, cpf, telefone, email, senha, nivel_acesso) 
             VALUES (?, ?, ?, ?, ?, ?, 'admin')"
        );
        
        // Dados de exemplo para o novo admin
        $nome = 'Administrador Principal';
        $data_nascimento = '2000-01-01';
        $telefone = '(00) 00000-0000';
        $email = 'admin@sistema.com';
        
        if ($insert_stmt->execute([$nome, $data_nascimento, $cpf_admin, $telefone, $email, $senha_hash])) {
            echo "SUCESSO: Novo usuário administrador criado com CPF $cpf_admin.\n";
        } else {
            echo "ERRO: Falha ao criar o novo usuário administrador.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "ERRO DE CONEXÃO: " . $e->getMessage() . "\n";
    echo "Verifique se o banco de dados está acessível e as credenciais em config/database.php estão corretas.\n";
}
