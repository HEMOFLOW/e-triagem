<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Triagem - Sistema de Doação de Sangue</title>
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
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ativado'): ?>
        <div class="welcome-donor" style="background: #eafaf1; border: 2px solid #27ae60; color: #145a32; padding: 20px; margin: 20px auto; border-radius: 8px; max-width: 600px; text-align: center; font-size: 1.2em; box-shadow: 0 2px 8px #0001;">
            <i class="fas fa-hand-holding-heart" style="font-size:2em;"></i><br>
            <strong>Parabéns! Agora você é um doador ativo.</strong><br>
            Obrigado por dar esse passo tão importante para salvar vidas. Você pode acompanhar seus agendamentos e doações pelo menu do sistema.<br>
            <span style="font-size:0.95em; color:#229954;">Juntos, fazemos a diferença!</span>
        </div>
        <?php endif; ?>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h2>Bem-vindo ao Sistema de Doação de Sangue</h2>
                    <p>Facilite o cadastro e agendamento de doações através de QR Codes para acesso rápido e seguro.</p>
                    <div class="hero-buttons">
                        <a href="cadastro.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Cadastrar-se
                        </a>
                        <a href="login.php" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i> Fazer Login
                        </a>
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <?php
                            include_once 'config/database.php';
                            $pdo = getConnection();
                            $stmt = $pdo->prepare('SELECT d.id FROM doadores d WHERE d.usuario_id = ? AND d.apto_para_doacao = 1 AND d.bloqueado = 0');
                            $stmt->execute([$_SESSION['usuario_id']]);
                            $doador = $stmt->fetch();
                            $apto = false;
                            if ($doador) {
                                $stmt = $pdo->prepare('SELECT aprovado, data_preenchimento FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1');
                                $stmt->execute([$_SESSION['usuario_id']]);
                                $ultima = $stmt->fetch();
                                if ($ultima && $ultima['aprovado']) {
                                    $data = new DateTime($ultima['data_preenchimento']);
                                    $agora = new DateTime();
                                    $horas = ($agora->getTimestamp() - $data->getTimestamp()) / 3600;
                                    if ($horas <= 48) {
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE usuario_id = ? AND (status = 'REALIZADO' OR status = 'FALTA') AND data_agendamento >= ?");
                                        $stmt->execute([$_SESSION['usuario_id'], $ultima['data_preenchimento']]);
                                        $teve_doacao_ou_falta = $stmt->fetchColumn() > 0;
                                        if (!$teve_doacao_ou_falta) {
                                            $apto = true;
                                        }
                                    }
                                }
                            }
                            ?>
                            <?php if ($apto): ?>
                                <a href="agendamentos.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus"></i> Agendar Doação
                                </a>
                            <?php else: ?>
                                <a href="questionario.php" class="btn btn-warning">
                                    <i class="fas fa-clipboard-check"></i> Refazer Questionário para Nova Doação
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hero-image">
                    <i class="fas fa-qrcode"></i>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h3>Funcionalidades do Sistema</h3>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-user-check"></i>
                            <a href="agendamentos.php" class="btn btn-primary">Agendar Doação</a>
                        <p>Cadastro completo com validação de CPF e verificação por WhatsApp</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-heart"></i>
                        <h4>Gerenciamento de Doadores</h4>
                        <p>Controle completo de informações dos doadores de sangue</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-clipboard-check"></i>
                        <h4>Questionário de Aptidão</h4>
                        <p>Avaliação da aptidão do usuário para doação de sangue</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-calendar-alt"></i>
                        <h4>Sistema de Agendamento</h4>
                        <p>Agendamento de doações com controle de frequência mensal</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="container">
                <h3>Estatísticas do Sistema</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span class="stat-number"><?php echo getTotalUsuarios(); ?></span>
                        <span class="stat-label">Usuários Cadastrados</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span class="stat-number"><?php echo getTotalDoadores(); ?></span>
                        <span class="stat-label">Doadores Ativos</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar-check"></i>
                        <span class="stat-number"><?php echo getTotalAgendamentos(); ?></span>
                        <span class="stat-label">Agendamentos</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-tint"></i>
                        <span class="stat-number"><?php echo getTotalDoacoes(); ?></span>
                        <span class="stat-label">Doações Realizadas</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 E-Triagem - Sistema de Doação de Sangue. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
// Funções para estatísticas
function getTotalUsuarios() {
    include_once 'config/database.php';
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return '<span style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
}

function getTotalDoadores() {
    include_once 'config/database.php';
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM doadores WHERE apto_para_doacao = 1");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return '<span style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
}

function getTotalAgendamentos() {
    include_once 'config/database.php';
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return '<span style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
}

function getTotalDoacoes() {
    include_once 'config/database.php';
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'REALIZADO'");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return '<span style="color:red;">Erro: ' . htmlspecialchars($e->getMessage()) . '</span>';
    }
}
?>

