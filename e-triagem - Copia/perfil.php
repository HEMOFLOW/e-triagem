$menu_incluido = false;
<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();
$id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();
// Buscar dados doador (se existir)
$stmt = $pdo->prepare('SELECT * FROM doadores WHERE usuario_id = ?');
$stmt->execute([$id]);
$doador = $stmt->fetch();
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? $usuario['nome'];
    $telefone = $_POST['telefone'] ?? $usuario['telefone'];
    $email = $_POST['email'] ?? $usuario['email'];
    $sql = 'UPDATE usuarios SET nome = ?, telefone = ?, email = ? WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$nome, $telefone, $email, $id]);
    // Atualizar dados doador se existir
    if ($doador) {
        $tipo_sanguineo = $_POST['tipo_sanguineo'] ?? $doador['tipo_sanguineo'];
        $rh = $_POST['rh'] ?? $doador['rh'];
        $peso = $_POST['peso'] ?? $doador['peso'];
        $altura = $_POST['altura'] ?? $doador['altura'];
        $sql = 'UPDATE doadores SET tipo_sanguineo = ?, rh = ?, peso = ?, altura = ? WHERE usuario_id = ?';
        $stmt = $pdo->prepare($sql);
        $ok = $ok && $stmt->execute([$tipo_sanguineo, $rh, $peso, $altura, $id]);
        // Atualizar array local para refletir na tela
        $doador['tipo_sanguineo'] = $tipo_sanguineo;
        $doador['rh'] = $rh;
        $doador['peso'] = $peso;
        $doador['altura'] = $altura;
    }
    if ($ok) {
        $mensagem = '<div style="background:#d4edda;border:1px solid #155724;color:#155724;padding:10px;margin-bottom:15px;font-weight:bold;">Perfil atualizado com sucesso!</div>';
        $usuario['nome'] = $nome;
        $usuario['telefone'] = $telefone;
        $usuario['email'] = $email;
    } else {
        $mensagem = '<div style="background:#f8d7da;border:1px solid #721c24;color:#721c24;padding:10px;margin-bottom:15px;font-weight:bold;">Erro ao atualizar perfil!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-view" style="background:#212529;color:#fff;font-weight:bold;padding:10px 22px;margin-bottom:18px;display:inline-block;text-decoration:none;">&#8592; Início</a>
        <h2>Meu Perfil</h2>
        <?php echo $mensagem; ?>
        <form method="post">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            <br><br>
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required>
            <br><br>
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>">
            <br><br>
            <?php if ($doador): ?>
                <label for="tipo_sanguineo">Tipo Sanguíneo:</label>
                <select name="tipo_sanguineo" id="tipo_sanguineo" required>
                    <option value="A" <?php if ($doador['tipo_sanguineo'] === 'A') echo 'selected'; ?>>A</option>
                    <option value="B" <?php if ($doador['tipo_sanguineo'] === 'B') echo 'selected'; ?>>B</option>
                    <option value="AB" <?php if ($doador['tipo_sanguineo'] === 'AB') echo 'selected'; ?>>AB</option>
                    <option value="O" <?php if ($doador['tipo_sanguineo'] === 'O') echo 'selected'; ?>>O</option>
                </select>
                <label for="rh">Fator RH:</label>
                <select name="rh" id="rh" required>
                    <option value="+" <?php if ($doador['rh'] === '+') echo 'selected'; ?>>+</option>
                    <option value="-" <?php if ($doador['rh'] === '-') echo 'selected'; ?>>-</option>
                </select>
                <br><br>
                <label for="peso">Peso (kg):</label>
                <input type="number" step="0.1" name="peso" id="peso" value="<?php echo htmlspecialchars($doador['peso']); ?>" required>
                <br><br>
                <label for="altura">Altura (m):</label>
                <input type="number" step="0.01" name="altura" id="altura" value="<?php echo htmlspecialchars($doador['altura']); ?>" required>
                <br><br>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</body>
</html>
