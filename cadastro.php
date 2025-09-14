<?php
session_start();

// Se já estiver logado, redirecionar
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once 'config/database.php';
    
    $nome = trim($_POST['nome'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $telefone = $_POST['telefone'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validações
    if (empty($nome) || empty($data_nascimento) || empty($cpf) || empty($telefone) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (strlen($cpf) != 11) {
        $erro = 'CPF deve ter 11 dígitos.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        try {
            $pdo = getConnection();
            
            // Verificar se CPF já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
            $stmt->execute([$cpf]);
            if ($stmt->fetch()) {
                $erro = 'Este CPF já está cadastrado.';
            } else {
                // Inserir usuário
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nome, data_nascimento, cpf, telefone, email, senha) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $cpf_formatado = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                
                if ($stmt->execute([$nome, $data_nascimento, $cpf_formatado, $telefone, $email, $senha_hash])) {
                    $sucesso = 'Cadastro realizado com sucesso! Faça login para continuar.';
                } else {
                    $erro = 'Erro ao cadastrar usuário. Tente novamente.';
                }
            }
        } catch (PDOException $e) {
            $erro = 'Erro interno. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Projeto QR Code</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus"></i>
                <h2>Cadastro</h2>
                <p>Crie sua conta no sistema</p>
            </div>
            
            <?php if ($erro): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($sucesso); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-user"></i>
                        Nome Completo *
                    </label>
                    <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
                </div>
                
                <div class="form-group">
                    <label for="data_nascimento">
                        <i class="fas fa-calendar"></i>
                        Data de Nascimento *
                    </label>
                    <input type="date" id="data_nascimento" name="data_nascimento" required>
                </div>
                
                <div class="form-group">
                    <label for="cpf">
                        <i class="fas fa-id-card"></i>
                        CPF *
                    </label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone">
                        <i class="fas fa-phone"></i>
                        Telefone WhatsApp *
                    </label>
                    <input type="text" id="telefone" name="telefone" placeholder="(11) 99999-9999" required>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        E-mail
                    </label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="senha">
                        <i class="fas fa-lock"></i>
                        Senha *
                    </label>
                    <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">
                        <i class="fas fa-lock"></i>
                        Confirmar Senha *
                    </label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>
                    Cadastrar
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
                <p><a href="index.php">← Voltar ao início</a></p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
        
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });
        
        // Validação de senha
        document.getElementById('confirmar_senha').addEventListener('input', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmar = e.target.value;
            
            if (senha !== confirmar) {
                e.target.setCustomValidity('As senhas não coincidem');
            } else {
                e.target.setCustomValidity('');
            }
        });
    </script>
</body>
</html>

