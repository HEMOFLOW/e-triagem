<?php
// Página para admin gerenciar doações agendadas
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

// Registrar doação ou falta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'], $_POST['agendamento_id'], $_POST['usuario_id'])) {
    $agendamento_id = intval($_POST['agendamento_id']);
    $usuario_id = intval($_POST['usuario_id']);
    $acao = $_POST['acao'];
    if ($acao === 'doou') {
        // Marca agendamento como realizado
        $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'REALIZADO' WHERE id = ?");
        $stmt->execute([$agendamento_id]);
        // Atualiza doador: apto, zera faltas, registra última doação
        $stmt = $pdo->prepare("UPDATE doadores SET apto_para_doacao = 1, faltas = 0, bloqueado = 0, motivo_bloqueio = NULL, ultima_doacao = CURDATE() WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
    } elseif ($acao === 'faltou') {
        // Marca agendamento como faltou
        $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'CANCELADO' WHERE id = ?");
        $stmt->execute([$agendamento_id]);
        // Incrementa faltas
        $stmt = $pdo->prepare("UPDATE doadores SET faltas = faltas + 1 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        // Busca número de faltas
        $stmt = $pdo->prepare("SELECT faltas FROM doadores WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $faltas = $stmt->fetchColumn();
        if ($faltas >= 2) {
            $stmt = $pdo->prepare("UPDATE doadores SET bloqueado = 1, motivo_bloqueio = 'Faltou a 2 agendamentos. Compareça à unidade para liberação.' WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE doadores SET apto_para_doacao = 0 WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
        }
    }
}

// Lista de agendamentos futuros
$stmt = $pdo->query("SELECT a.*, u.nome, d.faltas, d.bloqueado FROM agendamentos a JOIN usuarios u ON a.usuario_id = u.id JOIN doadores d ON d.usuario_id = u.id WHERE a.status = 'AGENDADO' AND a.data_agendamento >= CURDATE() ORDER BY a.data_agendamento, a.hora_agendamento");
$agendamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Doações</title>
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
        
        <h2>Gerenciar Doações Agendadas</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th>Faltas</th>
                    <th>Bloqueado</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agendamentos as $ag): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ag['nome']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($ag['data_agendamento'])); ?></td>
                    <td><?php echo substr($ag['hora_agendamento'],0,5); ?></td>
                    <td><?php echo htmlspecialchars($ag['status']); ?></td>
                    <td><?php echo $ag['faltas']; ?></td>
                    <td><?php echo $ag['bloqueado'] ? 'Sim' : 'Não'; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="agendamento_id" value="<?php echo $ag['id']; ?>">
                            <input type="hidden" name="usuario_id" value="<?php echo $ag['usuario_id']; ?>">
                                <button type="submit" name="acao" value="doou" class="btn btn-primary">Agendar Doação</button>
                            <button type="submit" name="acao" value="faltou" class="btn btn-edit" style="background:#c82333;">Não Compareceu</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
