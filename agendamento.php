<?php
session_start();

// Página de Agendamento integrada ao sistema de doação
require_once __DIR__ . '/config/database.php';

// Usuário precisa estar logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;

try {
    $pdo = getConnection();
} catch (Exception $e) {
    error_log('Erro de conexão: ' . $e->getMessage());
    $error = 'Erro de conexão com o banco de dados.';
}

// Processar submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $data = $_POST['data_agendamento'] ?? null; // formato YYYY-MM-DD
    $hora = $_POST['hora_agendamento'] ?? null; // formato HH:MM

    if (empty($data) || empty($hora)) {
        $error = 'Preencha data e hora do agendamento.';
    } else {
        try {
            // Verificar conflitos: mesmo horário já agendado
            $conflictStmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = ? AND hora_agendamento = ? AND status != 'CANCELADO'");
            $conflictStmt->execute([$data, $hora]);
            $conflicts = $conflictStmt->fetchColumn();
            if ($conflicts > 0) {
                $error = 'Horário indisponível. Escolha outro horário.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO agendamentos (usuario_id, data_agendamento, hora_agendamento, status) VALUES (?, ?, ?, 'AGENDADO')");
                $stmt->execute([$_SESSION['usuario_id'], $data, $hora]);
                $success = 'Agendamento criado com sucesso.';
                // Redirecionar para o dashboard para evitar reenvio de formulário
                header('Location: dashboard.php?msg=agendado');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Erro inserir agendamento: ' . $e->getMessage());
            $error = 'Não foi possível salvar o agendamento.';
        }
    }
}

// Buscar agendamentos do usuário
$agendamentos = [];
if (!$error) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE usuario_id = ? ORDER BY data_agendamento DESC, hora_agendamento DESC");
        $stmt->execute([$_SESSION['usuario_id']]);
        $agendamentos = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro buscar agendamentos: ' . $e->getMessage());
        // não interrompe a exibição da página
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - Projeto QR Code</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <h1>Projeto QR Code</h1>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="perfil.php">Perfil</a></li>
                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <h2>Agendar Doação</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="post" action="agendamento.php" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_agendamento">Data do Agendamento</label>
                        <input type="date" id="data_agendamento" name="data_agendamento" required>
                    </div>
                    <div class="form-group">
                        <label for="hora_agendamento">Hora do Agendamento</label>
                        <input type="time" id="hora_agendamento" name="hora_agendamento" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Agendar</button>
            </form>

            <h3 style="margin-top:2rem;">Seus Agendamentos</h3>
            <?php if ($agendamentos): ?>
                <div class="agendamentos-list">
                    <?php foreach ($agendamentos as $row): ?>
                        <div class="agendamento-item">
                            <div class="agendamento-data">
                                <?php echo date('d/m/Y', strtotime($row['data_agendamento'])); ?>
                                às <?php echo date('H:i', strtotime($row['hora_agendamento'])); ?>
                            </div>
                            <div class="agendamento-status status-<?php echo strtolower($row['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst(strtolower($row['status']))); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Você ainda não possui agendamentos.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Projeto QR Code - Sistema de Doação de Sangue.</p>
        </div>
    </footer>

    <style>
    /* estilos mínimos locais para a página de agendamento */
    .form { max-width: 600px; }
    .form-row { display:flex; gap:1rem; }
    .form-group { flex:1; }
    .alert { padding:0.75rem; border-radius:4px; margin-bottom:1rem; }
    .alert-error { background:#f8d7da; color:#721c24; }
    .alert-success { background:#d4edda; color:#155724; }
    .agendamentos-list { margin-top:1rem; }
    .agendamento-item { display:flex; justify-content:space-between; padding:0.5rem; background:#f8f9fa; border-radius:4px; margin-bottom:0.5rem; }
    </style>

</body>
</html>
