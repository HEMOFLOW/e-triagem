<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();

// Verifica se é admin
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch();
if (!$user || $user['perfil'] !== 'admin') {
    echo '<p style="color:red;">Acesso restrito ao administrador.</p>';
    exit;
}

// Busca idade máxima atual
$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'idade_maxima_doador'");
$stmt->execute();
$idade_maxima = $stmt->fetchColumn();
if (!$idade_maxima) $idade_maxima = 69;

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_idade = intval($_POST['idade_maxima'] ?? 0);
    if ($nova_idade < 18 || $nova_idade > 120) {
        $msg = 'Idade máxima deve ser entre 18 e 120.';
    } else {
        $stmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = 'idade_maxima_doador'");
        $stmt->execute([$nova_idade]);
        $idade_maxima = $nova_idade;
        $msg = 'Idade máxima atualizada com sucesso!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurar Idade Máxima do Doador</title>
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
            <h2>Configurar Idade Máxima do Doador</h2>
            <?php if ($msg): ?>
                <div style="background:#f8f9fa;color:#333;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="idade_maxima">Idade máxima permitida para doação:</label>
                <input type="number" name="idade_maxima" id="idade_maxima" min="18" max="120" value="<?php echo htmlspecialchars($idade_maxima); ?>" required>
                <button type="submit" class="btn btn-primary" style="margin-top:16px;">Salvar</button>
            </form>
        </div>
    </main>
</body>
</html>
