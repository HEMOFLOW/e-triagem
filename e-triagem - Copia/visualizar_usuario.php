<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();
$id = $_GET['id'] ?? '';
if (!$id) { echo 'Usuário não encontrado.'; exit; }
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();
if (!$usuario) { echo 'Usuário não encontrado.'; exit; }
$stmt = $pdo->prepare('SELECT * FROM doadores WHERE usuario_id = ?');
$stmt->execute([$id]);
$doador = $stmt->fetch();
$stmt = $pdo->prepare('SELECT * FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1');
$stmt->execute([$id]);
$quest = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Usuário</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <h2>Dados do Usuário</h2>
        <table class="table" style="max-width:600px;">
            <?php if ($doador): ?>
                <tr><th>Tipo Sanguíneo</th><td><?php echo htmlspecialchars($doador['tipo_sanguineo'] ?? '-'); ?></td></tr>
            <?php endif; ?>
            <tr><th>ID</th><td><?php echo htmlspecialchars($usuario['id']); ?></td></tr>
            <tr><th>Nome</th><td><?php echo htmlspecialchars($usuario['nome']); ?></td></tr>
            <tr><th>CPF</th><td><?php echo htmlspecialchars($usuario['cpf']); ?></td></tr>
            <tr><th>Data de Nascimento</th><td><?php echo date('d/m/Y', strtotime($usuario['data_nascimento'])); ?></td></tr>
            <tr><th>Telefone</th><td><?php echo htmlspecialchars($usuario['telefone']); ?></td></tr>
            <tr><th>E-mail</th><td><?php echo htmlspecialchars($usuario['email'] ?: 'Não informado'); ?></td></tr>
            <tr><th>Perfil</th><td><?php echo htmlspecialchars($usuario['perfil'] ?? 'comum'); ?></td></tr>
            <tr><th>Data de Cadastro</th><td><?php echo isset($usuario['data_cadastro']) ? date('d/m/Y H:i', strtotime($usuario['data_cadastro'])) : '-'; ?></td></tr>
            <tr><th>Último Acesso</th><td><?php echo isset($usuario['ultimo_acesso']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : '-'; ?></td></tr>
        </table>
        <hr>
        <h3>Status de Doador</h3>
        <?php if ($doador): ?>
            <table class="table" style="max-width:500px;">
                <tr><th>Tipo Sanguíneo</th><td><?php echo htmlspecialchars($doador['tipo_sanguineo'] . $doador['rh']); ?></td></tr>
                <tr><th>Peso</th><td><?php echo htmlspecialchars($doador['peso']); ?> kg</td></tr>
                <tr><th>Altura</th><td><?php echo htmlspecialchars($doador['altura']); ?> m</td></tr>
                <tr><th>Status</th><td><?php echo $doador['apto_para_doacao'] ? 'Apto' : 'Inapto'; ?></td></tr>
                <tr><th>Última Doação</th><td><?php echo $doador['ultima_doacao'] ? date('d/m/Y', strtotime($doador['ultima_doacao'])) : '-'; ?></td></tr>
                <tr><th>Próxima Doação</th><td><?php echo $doador['proxima_doacao'] ? date('d/m/Y', strtotime($doador['proxima_doacao'])) : '-'; ?></td></tr>
            </table>
            <a href="editar_doador.php?id=<?php echo $doador['id']; ?>" class="btn btn-primary">Editar Status do Doador</a>
        <?php else: ?>
            <p>Usuário não cadastrado como doador.</p>
        <?php endif; ?>
        <hr>
        <h3>Última Resposta do Questionário</h3>
        <?php if ($quest): ?>
            <table class="table" style="max-width:500px;">
                <tr><th>Status</th><td><?php echo $quest['aprovado'] ? 'Aprovado' : 'Reprovado'; ?></td></tr>
                <tr><th>Data</th><td><?php echo date('d/m/Y H:i', strtotime($quest['data_preenchimento'])); ?></td></tr>
            </table>
        <?php else: ?>
            <p>Usuário ainda não respondeu o questionário.</p>
        <?php endif; ?>
        <hr>
        <a href="usuarios.php" class="btn btn-secondary">Voltar à lista</a>
    </main>
    </div>
</body>
</html>
