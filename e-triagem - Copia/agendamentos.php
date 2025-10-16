<?php
session_start();
include_once 'config/database.php';
$pdo = getConnection();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verifica se o usuário é doador ativo
$stmt = $pdo->prepare('SELECT d.id FROM doadores d WHERE d.usuario_id = ? AND d.apto_para_doacao = 1 AND d.bloqueado = 0');
$stmt->execute([$_SESSION['usuario_id']]);
$doador = $stmt->fetch();
if (!$doador) {
    echo '<div style="margin:40px auto;max-width:600px;padding:24px;background:#ffeaea;border:1px solid #e74c3c;color:#c0392b;border-radius:8px;text-align:center;font-size:1.2em;">';
    echo 'Apenas usuários doadores ativos podem acessar o agendamento. Ative seu perfil de doador para continuar.';
    echo '</div>';
    exit;
}
// Verifica se está apto pelo questionário
$stmt = $pdo->prepare('SELECT aprovado, data_preenchimento FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1');
$stmt->execute([$_SESSION['usuario_id']]);
$ultima = $stmt->fetch();
$apto = false;
if ($ultima && $ultima['aprovado']) {
    $data = new DateTime($ultima['data_preenchimento']);
    $agora = new DateTime();
    $horas = ($agora->getTimestamp() - $data->getTimestamp()) / 3600;
    if ($horas <= 48) {
        // Verifica se já existe um agendamento REALIZADO ou FALTA após o questionário
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE usuario_id = ? AND (status = 'REALIZADO' OR status = 'FALTA') AND data_agendamento >= ?");
        $stmt->execute([$_SESSION['usuario_id'], $ultima['data_preenchimento']]);
        $teve_doacao_ou_falta = $stmt->fetchColumn() > 0;
        if (!$teve_doacao_ou_falta) {
            $apto = true;
        }
    }
}
if (!$apto) {
    echo '<div style="margin:40px auto;max-width:600px;padding:24px;background:#ffeaea;border:1px solid #e74c3c;color:#c0392b;border-radius:8px;text-align:center;font-size:1.2em;">';
    echo 'Você só pode agendar se estiver apto pelo questionário. Se já realizou uma doação ou teve falta, é necessário refazer o questionário para liberar novo agendamento.';
    echo '<br><br>';
    echo '<a href="questionario.php" class="btn btn-warning" style="margin-top:10px;display:inline-block;font-size:1em;"><i class="fas fa-clipboard-check"></i> Responder Questionário</a>';
    echo '</div>';
    exit;
}

// Agendamento
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data_agendamento'], $_POST['hora_agendamento'])) {
    $data = $_POST['data_agendamento'];
    $hora = $_POST['hora_agendamento'];
    $stmt = $pdo->prepare('INSERT INTO agendamentos (usuario_id, data_agendamento, hora_agendamento, status) VALUES (?, ?, ?, "AGENDADO")');
    $stmt->execute([$_SESSION['usuario_id'], $data, $hora]);
    $msg = 'Agendamento realizado com sucesso!';
}

// Lista agendamentos do usuário
$stmt = $pdo->prepare('SELECT * FROM agendamentos WHERE usuario_id = ? ORDER BY data_agendamento DESC, hora_agendamento DESC');
$stmt->execute([$_SESSION['usuario_id']]);
$agendamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Agendamentos</title>
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
        <div class="container">
            <h2>Meus Agendamentos</h2>
            <?php if ($msg): ?>
                <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;"> <?php echo $msg; ?> </div>
            <?php endif; ?>
            <form method="post" style="margin-bottom:32px;">
                <label>Data: <input type="date" name="data_agendamento" required></label>
                <label>Hora: <input type="time" name="hora_agendamento" required></label>
                <button type="submit" class="btn btn-primary">Agendar</button>
            </form>
            <h3>Histórico de Agendamentos</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $ag): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ag['data_agendamento']); ?></td>
                            <td><?php echo htmlspecialchars($ag['hora_agendamento']); ?></td>
                            <td><?php echo htmlspecialchars($ag['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($agendamentos)): ?>
                        <tr><td colspan="3">Nenhum agendamento encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
