<?php
session_start();
include_once 'config/database.php';

// --- PASSO 1: VERIFICAR LOGIN E CARREGAR DADOS ESSENCIAIS ---

// Se não estiver logado, redireciona para o login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$pdo = getConnection();

// Carrega os dados do usuário. Se falhar, a página irá parar com um erro claro.
$stmt_usuario = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_usuario->execute([$usuario_id]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

// Se o usuário não for encontrado no banco de dados, desloga por segurança.
if (!$usuario) {
    session_destroy();
    header('Location: login.php?erro=usuario_nao_encontrado');
    exit;
}

// Carrega os dados do doador
$stmt_doador = $pdo->prepare("SELECT * FROM doadores WHERE usuario_id = ?");
$stmt_doador->execute([$usuario_id]);
$doador = $stmt_doador->fetch(PDO::FETCH_ASSOC);

// --- LÓGICA DE VALIDADE DO QUESTIONÁRIO ---
$stmt_quest = $pdo->prepare("SELECT aprovado, data_preenchimento FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1");
$stmt_quest->execute([$usuario_id]);
$ultimo_questionario = $stmt_quest->fetch(PDO::FETCH_ASSOC);

$status_aptidao = 'Não respondido';
$status_class = 'status-neutro';

if ($ultimo_questionario) {
    $data_questionario = new DateTime($ultimo_questionario['data_preenchimento']);
    $data_atual = new DateTime();
    $diferenca = $data_atual->diff($data_questionario);
    $dias_passados = $diferenca->days;

    if ($ultimo_questionario['aprovado'] == 1) {
        if ($dias_passados <= 3) {
            $status_aptidao = 'Apto';
            $status_class = 'status-apto';
        } else {
            $status_aptidao = 'Expirado';
            $status_class = 'status-inapto';
        }
    } else {
        $status_aptidao = 'Inapto';
        $status_class = 'status-inapto';
    }
}
// --- FIM DA LÓGICA DE VALIDADE ---

$stmt_agendamentos = $pdo->prepare("SELECT * FROM agendamentos WHERE usuario_id = ? ORDER BY data_agendamento DESC LIMIT 5");
$stmt_agendamentos->execute([$usuario_id]);
$agendamentos = $stmt_agendamentos->fetchAll(PDO::FETCH_ASSOC);


// --- PASSO 2: INCLUIR O HEADER (AGORA QUE OS DADOS JÁ EXISTEM) ---
$page_title = "Dashboard";
include 'layout/header.php';

?>

<!-- --- PASSO 3: EXIBIR O CONTEÚDO DA PÁGINA --- -->
<div class="dashboard">
    <div class="dashboard-header">
        <h2>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h2>
        <p>Gerencie suas informações e agendamentos de doação</p>
    </div>

    <?php
    // Exibe a mensagem de status do questionário (se houver)
    if (isset($_GET['status'])) {
        if ($_GET['status'] === 'apto') {
            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Parabéns! Seu questionário foi aprovado. O status é válido por 3 dias.</div>';
        } elseif ($_GET['status'] === 'inapto') {
            echo '<div class="alert alert-error"><i class="fas fa-times-circle"></i> Infelizmente, você não está apto para doar no momento.</div>';
        }
    }
    ?>

    <div class="dashboard-grid">
        <!-- Card de Informações Pessoais -->
        <div class="dashboard-card">
            <h3><i class="fas fa-user"></i> Informações Pessoais</h3>
            <div class="info-list">
                <div class="info-item"><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></div>
                <div class="info-item"><strong>CPF:</strong> <?php echo htmlspecialchars($usuario['cpf']); ?></div>
            </div>
            <a href="doadores.php" class="btn btn-primary"><i class="fas fa-edit"></i> Editar Perfil</a>
        </div>

        <!-- Card de Doador -->
        <div class="dashboard-card">
            <h3><i class="fas fa-heart"></i> Status de Doador</h3>
            <?php if ($doador): ?>
                <div class="info-list">
                    <div class="info-item"><strong>Tipo Sanguíneo:</strong> <?php echo htmlspecialchars($doador['tipo_sanguineo'] . $doador['rh']); ?></div>
                    <div class="info-item">
                        <strong>Status de Aptidão:</strong>
                        <span class="<?php echo $status_class; ?>"><?php echo $status_aptidao; ?></span>
                    </div>
                </div>
            <?php else: ?>
                <p>Você ainda não se cadastrou como doador.</p>
            <?php endif; ?>
            <a href="doadores.php" class="btn btn-primary"><i class="fas fa-heart"></i> Atualizar Dados</a>
        </div>

        <!-- Card de Questionário -->
        <div class="dashboard-card">
            <h3><i class="fas fa-clipboard-check"></i> Questionário</h3>
            <p>Mantenha seu status atualizado respondendo ao questionário.</p>
            <a href="questionario.php" class="btn btn-primary"><i class="fas fa-clipboard-check"></i> Responder</a>
        </div>

        <!-- Card de Agendamentos -->
        <div class="dashboard-card">
            <h3><i class="fas fa-calendar-alt"></i> Agendamentos</h3>
            <?php if (!empty($agendamentos)): ?>
                <div class="agendamentos-list">
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <div class="agendamento-item">
                            <div><?php echo date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])); ?></div>
                            <div class="status-<?php echo strtolower($agendamento['status']); ?>"><?php echo ucfirst($agendamento['status']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Você não possui agendamentos.</p>
            <?php endif; ?>
            <a href="agendamento.php" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Agendar</a>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
