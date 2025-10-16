<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    if (empty($senha_atual) || empty($nova_senha) || empty($confirma_senha)) {
        $mensagem = 'Preencha todos os campos.';
    } elseif ($nova_senha !== $confirma_senha) {
        $mensagem = 'A nova senha e a confirmação não coincidem.';
    } else {
        // Busca senha atual
        $stmt = $pdo->prepare('SELECT senha FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();
        if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
            $mensagem = 'Senha atual incorreta.';
        } else {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
            if ($stmt->execute([$hash, $_SESSION['usuario_id']])) {
                $mensagem = 'Senha alterada com sucesso!';
            } else {
                $mensagem = 'Erro ao atualizar senha.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Trocar Senha</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <h1>E-Triagem</h1>
            </div>
            <?php include 'nav.php'; ?>
        </div>
    </header>
    <main class="main">
        <div class="container" style="max-width:400px;margin:32px auto;">
            <h2>Trocar Senha</h2>
            <?php if ($mensagem): ?>
                <div style="background:#f8f9fa;color:#333;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="senha_atual">Senha atual:</label>
                <input type="password" name="senha_atual" id="senha_atual" required>
                <label for="nova_senha">Nova senha:</label>
                <input type="password" name="nova_senha" id="nova_senha" required>
                <label for="confirma_senha">Confirmar nova senha:</label>
                <input type="password" name="confirma_senha" id="confirma_senha" required>
                <button type="submit" class="btn btn-primary" style="margin-top:16px;">Alterar Senha</button>
                    <a href="agendamentos.php" class="btn btn-primary">Agendar Doação</a>
            </form>
        </div>
    </main>
</body>
</html>
