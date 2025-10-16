<?php
session_start();

// Verifica se está logado e se é administrador
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'config/database.php';
$pdo = getConnection();

$menu_incluido = false;
$perfil_admin = false;
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user_logado = $stmt->fetch();
if ($user_logado && $user_logado['perfil'] === 'admin') {
    $perfil_admin = true;
}

// Processar ações administrativas
if ($perfil_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['doador_id'], $_POST['novo_status'])) {
        $doador_id = intval($_POST['doador_id']);
        $novo_status = intval($_POST['novo_status']) ? 1 : 0;
        $stmt = $pdo->prepare('UPDATE doadores SET apto_para_doacao = ? WHERE id = ?');
        $stmt->execute([$novo_status, $doador_id]);
        header('Location: usuarios.php');
        exit;
    }
    if (isset($_POST['usuario_id'], $_POST['novo_perfil'])) {
        $usuario_id = intval($_POST['usuario_id']);
        $novo_perfil = $_POST['novo_perfil'] === 'admin' ? 'admin' : 'comum';
        // Impede que o admin altere o próprio perfil
        if ($usuario_id != $_SESSION['usuario_id']) {
            $stmt = $pdo->prepare('UPDATE usuarios SET perfil = ? WHERE id = ?');
            $stmt->execute([$novo_perfil, $usuario_id]);
        }
        header('Location: usuarios.php');
        exit;
    }
    if (isset($_POST['resetar_usuario_id'])) {
        $usuario_id = intval($_POST['resetar_usuario_id']);
        // Remove doador, respostas do questionário e agendamentos
        $stmt = $pdo->prepare('DELETE FROM doadores WHERE usuario_id = ?');
        $stmt->execute([$usuario_id]);
        $stmt = $pdo->prepare('DELETE FROM questionarios WHERE usuario_id = ?');
        $stmt->execute([$usuario_id]);
        $stmt = $pdo->prepare('DELETE FROM agendamentos WHERE usuario_id = ?');
        $stmt->execute([$usuario_id]);
        header('Location: usuarios.php');
        exit;
    }
}

// Filtro de busca
$filtro = $_GET['filtro'] ?? '';
$usuarios = [];

if ($filtro) {
    $sql = "SELECT * FROM usuarios WHERE nome LIKE ? OR cpf LIKE ? ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $busca = "%$filtro%";
    $stmt->execute([$busca, $busca]);
    $usuarios = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC");
    $usuarios = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .table th { background: #f8f9fa; }
        .btn { padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; }
        .btn-edit { background: #155724; color: #fff; }
        .btn-view { background: #007bff; color: #fff; }
        .status-apto { background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 3px; }
        .status-inapto { background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 3px; }
    </style>
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
    <div class="container">
        <h2>Usuários Cadastrados</h2>
        <form method="get" style="margin-bottom:16px;">
            <input type="text" name="filtro" placeholder="Buscar por nome ou CPF" value="<?php echo htmlspecialchars($filtro); ?>" style="padding:8px;width:250px;">
            <button type="submit" class="btn btn-view">Buscar</button>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Status Doador</th>
                    <th>Última Doação</th>
                    <th>Última Resposta Questionário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <?php
                    // Buscar status do doador
                    $stmt = $pdo->prepare("SELECT * FROM doadores WHERE usuario_id = ?");
                    $stmt->execute([$usuario['id']]);
                    $doador = $stmt->fetch();
                    // Buscar última doação REALIZADA
                    $ultima_doacao = '-';
                    if ($doador) {
                        $stmt = $pdo->prepare("SELECT data_agendamento FROM agendamentos WHERE usuario_id = ? AND status = 'REALIZADO' ORDER BY data_agendamento DESC LIMIT 1");
                        $stmt->execute([$usuario['id']]);
                        $row = $stmt->fetch();
                        if ($row && $row['data_agendamento']) {
                            $ultima_doacao = date('d/m/Y', strtotime($row['data_agendamento']));
                            // Atualiza a coluna ultima_doacao na tabela doadores
                            $stmt2 = $pdo->prepare("UPDATE doadores SET ultima_doacao = ? WHERE id = ?");
                            $stmt2->execute([$row['data_agendamento'], $doador['id']]);
                        }
                    }
                    // Status doador
                    $status = $doador ? ($doador['apto_para_doacao'] ? 'Apto' : 'Inapto') : 'Não cadastrado';
                    $statusClass = $doador ? ($doador['apto_para_doacao'] ? 'status-apto' : 'status-inapto') : '';
                    // Buscar última resposta do questionário
                    $stmt = $pdo->prepare("SELECT aprovado, data_preenchimento FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1");
                    $stmt->execute([$usuario['id']]);
                    $quest = $stmt->fetch();
                    $respQuest = $quest ? ($quest['aprovado'] ? 'Aprovado' : 'Reprovado') . ' em ' . date('d/m/Y', strtotime($quest['data_preenchimento'])) : '-';
                    ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($usuario['nome']); ?>
                            <?php if ($perfil_admin && $usuario['id'] != $_SESSION['usuario_id']): ?>
                                <form method="post" action="" style="display:inline;margin-left:8px;">
                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                    <select name="novo_perfil" onchange="this.form.submit()" style="padding:2px 6px;">
                                        <option value="comum" <?php if ($usuario['perfil'] === 'comum') echo 'selected'; ?>>Comum</option>
                                        <option value="admin" <?php if ($usuario['perfil'] === 'admin') echo 'selected'; ?>>Admin</option>
                                    </select>
                                </form>
                                <form method="post" action="" style="display:inline;margin-left:8px;">
                                    <input type="hidden" name="resetar_usuario_id" value="<?php echo $usuario['id']; ?>">
                                    <button type="submit" class="btn btn-edit" style="background:#6c757d; color:#fff; padding:2px 8px;" onclick="return confirm('Deseja realmente resetar este usuário? Isso apagará o cadastro de doador e respostas do questionário!');">Resetar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['cpf']); ?></td>
                        <td>
                            <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                            <?php if ($perfil_admin && $doador): ?>
                                <form method="post" action="" style="display:inline;margin-left:8px;">
                                    <input type="hidden" name="doador_id" value="<?php echo $doador['id']; ?>">
                                    <select name="novo_status" onchange="this.form.submit()" style="padding:2px 6px;">
                                        <option value="1" <?php if ($doador['apto_para_doacao']) echo 'selected'; ?>>Apto</option>
                                        <option value="0" <?php if (!$doador['apto_para_doacao']) echo 'selected'; ?>>Inapto</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $ultima_doacao; ?></td>
                        <td><?php echo $respQuest; ?></td>
                        <td>
                            <a href="visualizar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-view">Visualizar</a>
                            <?php /* O botão de editar status foi substituído pelo select inline para admin */ ?>
                            <?php if ($perfil_admin && $usuario['id'] != $_SESSION['usuario_id']): ?>
                                <a href="trocar_senha.php?id=<?php echo $usuario['id']; ?>" class="btn btn-edit" style="background:#ffc107; color:#212529; margin-left:5px;">Trocar Senha</a>
                                <a href="excluir_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-edit" style="background:#c82333; color:#fff; margin-left:5px;" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
