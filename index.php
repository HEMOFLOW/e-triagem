<?php
session_start();
// Mensagem de teste para confirmar que o PHP está funcionando
if (isset($_GET['testphp'])) {
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>PHP OK</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:2rem;">';
    echo '<h1 style="color:green;">PHP funcionando com sucesso!</h1>';
    echo '<p><a href="?info=1">Exibir phpinfo()</a></p>';
    echo '</body></html>';
    exit;
}
if (isset($_GET['info'])) {
    phpinfo();
    exit;
}
// Incluir configuração do banco apenas uma vez para reutilizar a conexão
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto QR Code - Sistema de Doação de Sangue</title>
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
                    <li><a href="index.php" class="active">Início</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="cadastro.php">Cadastro</a></li>
                    <li><a href="doadores.php">Doadores</a></li>
                    <li><a href="questionario.php">Questionário</a></li>
                    <li><a href="/agendamento.php">Agendamento</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
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
                        <h4>Cadastro de Usuários</h4>
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
            <p>&copy; 2025 Projeto QR Code - Sistema de Doação de Sangue. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
// Funções para estatísticas
function getTotalUsuarios() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log('Erro getTotalUsuarios: ' . $e->getMessage());
        return 0;
    }
}

function getTotalDoadores() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM doadores WHERE apto_para_doacao = 1");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log('Erro getTotalDoadores: ' . $e->getMessage());
        return 0;
    }
}

function getTotalAgendamentos() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log('Erro getTotalAgendamentos: ' . $e->getMessage());
        return 0;
    }
}

function getTotalDoacoes() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'REALIZADO'");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log('Erro getTotalDoacoes: ' . $e->getMessage());
        return 0;
    }
}
?>

