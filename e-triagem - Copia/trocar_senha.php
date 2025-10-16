<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();

// Só admin pode trocar senha de outros usuários
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch();
if (!$user || $user['perfil'] !== 'admin') {
    echo 'Ação não permitida.';
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo 'Usuário não encontrado.';
    exit;
}

$stmt = $pdo->prepare('SELECT nome FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();
if (!$usuario) {
    echo 'Usuário não encontrado.';
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['nova_senha'] ?? '';
    if (strlen($novaSenha) < 6) {
        $msg = '<span style="color:red;">A senha deve ter pelo menos 6 caracteres.</span>';
    } else {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        if ($stmt->execute([$hash, $id])) {
            $msg = '<span style="color:green;">Senha alterada com sucesso!</span>';
        } else {
            $msg = '<span style="color:red;">Erro ao alterar senha.</span>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Trocar Senha do Usuário</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Trocar Senha de <?php echo htmlspecialchars($usuario['nome']); ?></h2>
        <?php echo $msg; ?>
        <form method="post">
            <label>Nova Senha:</label><br>
            <input type="password" name="nova_senha" required minlength="6" style="padding:8px;width:250px;"><br><br>
            <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
            <a href="usuarios.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</body>
</html>
