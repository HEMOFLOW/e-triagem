<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'config/database.php';

// Buscar dados do usuário
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    // Buscar dados do doador
    $stmt = $pdo->prepare("SELECT * FROM doadores WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $doador = $stmt->fetch();
    
    // Buscar agendamentos
    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE usuario_id = ? ORDER BY data_agendamento DESC LIMIT 5");
    $stmt->execute([$_SESSION['usuario_id']]);
    $agendamentos = $stmt->fetchAll();
    
    // Buscar questionário
    $stmt = $pdo->prepare("SELECT * FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1");
    $stmt->execute([$_SESSION['usuario_id']]);
    $questionario = $stmt->fetch();
    
} catch (PDOException $e) {
    $erro = "Erro ao carregar dados do usuário.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Triagem</title>
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
        <div class="container">
            <div class="dashboard">
                <div class="dashboard-header">
                    <h2>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h2>
                    <p>Gerencie suas informações e agendamentos de doação</p>
                </div>

                <div class="dashboard-grid">
                    <!-- Card de Informações Pessoais -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-user"></i> Informações Pessoais</h3>
                        <div class="info-list">
                            <div class="info-item">
                                <strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?>
                            </div>
                            <div class="info-item">
                                <strong>CPF:</strong> <?php echo htmlspecialchars($usuario['cpf']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Telefone:</strong> <?php echo htmlspecialchars($usuario['telefone']); ?>
                            </div>
                            <div class="info-item">
                                <strong>E-mail:</strong> <?php echo htmlspecialchars($usuario['email'] ?: 'Não informado'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Data de Nascimento:</strong> 
                                <?php echo date('d/m/Y', strtotime($usuario['data_nascimento'])); ?>
                            </div>
                        </div>
                        <?php
                        // Considera admin se o campo 'perfil' for 'admin'
                        $isAdmin = isset($usuario['perfil']) && strtolower($usuario['perfil']) === 'admin';
                        ?>
                        <?php if ($isAdmin): ?>
                            <a href="usuarios.php" class="btn btn-primary">
                                <i class="fas fa-users"></i> Gerenciar Usuários
                            </a>
                        <?php else: ?>
                            <a href="perfil.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar Perfil
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Card de Doador -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-heart"></i> Status de Doador</h3>
                        <?php if ($doador): ?>
                            <div class="info-list">
                                <div class="info-item">
                                    <strong>Tipo Sanguíneo:</strong> 
                                    <?php 
                                        $tipo = isset($doador['tipo_sanguineo']) ? $doador['tipo_sanguineo'] : '';
                                        $rh = isset($doador['rh']) ? $doador['rh'] : '';
                                        echo ($tipo || $rh) ? htmlspecialchars($tipo . $rh) : 'Não informado';
                                    ?>
                                </div>
                                <div class="info-item">
                                    <strong>Peso:</strong> 
                                    <?php echo isset($doador['peso']) && $doador['peso'] !== '' ? htmlspecialchars($doador['peso']) . ' kg' : 'Não informado'; ?>
                                </div>
                                <div class="info-item">
                                    <strong>Altura:</strong> 
                                    <?php echo isset($doador['altura']) && $doador['altura'] !== '' ? htmlspecialchars($doador['altura']) . ' m' : 'Não informado'; ?>
                                </div>
                                <div class="info-item">
                                    <strong>Status:</strong> 
                                    <span class="status <?php echo !empty($doador['apto_para_doacao']) ? 'status-apto' : 'status-inapto'; ?>">
                                        <?php echo !empty($doador['apto_para_doacao']) ? 'Apto' : 'Inapto'; ?>
                                    </span>
                                </div>
                                <?php if (!empty($doador['ultima_doacao'])): ?>
                                    <div class="info-item">
                                        <strong>Última Doação:</strong> 
                                        <?php echo date('d/m/Y', strtotime($doador['ultima_doacao'])); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($doador['proxima_doacao'])): ?>
                                    <div class="info-item">
                                        <strong>Próxima Doação:</strong> 
                                        <?php echo date('d/m/Y', strtotime($doador['proxima_doacao'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p>Você ainda não se cadastrou como doador.</p>
                            <a href="doadores.php" class="btn btn-primary">
                                <i class="fas fa-heart"></i> Cadastrar como Doador
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Card de Questionário -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-clipboard-check"></i> Questionário de Aptidão</h3>
                        <?php if ($questionario): ?>
                            <div class="info-list">
                                <div class="info-item">
                                    <strong>Status:</strong> 
                                    <span class="status <?php echo $questionario['aprovado'] ? 'status-apto' : 'status-inapto'; ?>">
                                        <?php echo $questionario['aprovado'] ? 'Aprovado' : 'Reprovado'; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <strong>Data:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($questionario['data_preenchimento'])); ?>
                                </div>
                            </div>
                            <a href="questionario.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Refazer Questionário
                            </a>
                        <?php else: ?>
                            <p>Você ainda não respondeu o questionário de aptidão.</p>
                            <a href="questionario.php" class="btn btn-primary">
                                <i class="fas fa-clipboard-check"></i> Responder Questionário
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Card de Agendamentos -->
                    <div class="dashboard-card">
                        <h3><i class="fas fa-calendar-alt"></i> Próximos Agendamentos</h3>
                        <?php if ($agendamentos): ?>
                            <div class="agendamentos-list">
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <div class="agendamento-item">
                                        <div class="agendamento-data">
                                            <?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?>
                                            às <?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?>
                                        </div>
                                        <div class="agendamento-status status-<?php echo strtolower($agendamento['status']); ?>">
                                            <?php echo ucfirst($agendamento['status']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="agendamentos.php" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Novo Agendamento
                            </a>
                        <?php else: ?>
                            <p>Você ainda não possui agendamentos.</p>
                            <a href="agendamentos.php" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Agendar Doação
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Triagem - Sistema de Doação de Sangue. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>

<style>
.info-list {
    margin-bottom: 1.5rem;
}

.info-item {
    margin-bottom: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-item:last-child {
    border-bottom: none;
}

.status {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-apto {
    background: #d4edda;
    color: #155724;
}

.status-inapto {
    background: #f8d7da;
    color: #721c24;
}

.agendamentos-list {
    margin-bottom: 1.5rem;
}

.agendamento-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 0.5rem;
}

.agendamento-data {
    font-weight: 600;
}

.agendamento-status {
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-agendado {
    background: #fff3cd;
    color: #856404;
}

.status-confirmado {
    background: #d1ecf1;
    color: #0c5460;
}

.status-realizado {
    background: #d4edda;
    color: #155724;
}

.status-cancelado {
    background: #f8d7da;
    color: #721c24;
}
</style>

