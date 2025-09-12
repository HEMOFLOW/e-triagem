<?php
$page_title = "Meu Perfil";
include 'layout/header.php';

// Redireciona se não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$erro = '';
$sucesso = '';
$usuario_id = $_SESSION['usuario_id'];

$pdo = getConnection();

try {
    // Buscar dados do usuário e do doador
    $stmt = $pdo->prepare("
        SELECT u.*, d.id as doador_id, d.tipo_sanguineo, d.rh, d.peso, d.altura, d.ultima_doacao, d.proxima_doacao, d.observacoes
        FROM usuarios u
        LEFT JOIN doadores d ON u.id = d.usuario_id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perfil) {
        // Se não encontrar o usuário, algo está muito errado. Deslogar.
        session_destroy();
        header('Location: login.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Iniciar transação para garantir a integridade dos dados
        $pdo->beginTransaction();

        // --- Atualizar dados pessoais (tabela usuarios) ---
        $nome = trim($_POST['nome'] ?? '');
        $data_nascimento = trim($_POST['data_nascimento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nome) || empty($data_nascimento) || empty($telefone)) {
            throw new Exception("Nome, data de nascimento e telefone são obrigatórios.");
        }

        $stmt_user = $pdo->prepare("UPDATE usuarios SET nome = ?, data_nascimento = ?, telefone = ?, email = ? WHERE id = ?");
        $stmt_user->execute([$nome, $data_nascimento, $telefone, $email, $usuario_id]);

        // --- Atualizar ou Inserir dados do doador (tabela doadores) ---
        $tipo_sanguineo = strtoupper(trim($_POST['tipo_sanguineo'] ?? ''));
        $rh = trim($_POST['rh'] ?? '');
        $peso = !empty($_POST['peso']) ? str_replace(',', '.', trim($_POST['peso'])) : null;
        $altura = !empty($_POST['altura']) ? str_replace(',', '.', trim($_POST['altura'])) : null;
        $ultima_doacao = !empty($_POST['ultima_doacao']) ? $_POST['ultima_doacao'] : null;
        $proxima_doacao = !empty($_POST['proxima_doacao']) ? $_POST['proxima_doacao'] : null;
        $observacoes = trim($_POST['observacoes'] ?? '');

        // Só processa dados de doador se tipo sanguíneo e RH forem informados
        if (!empty($tipo_sanguineo) && !empty($rh)) {
            if ($perfil['doador_id']) {
                // Atualizar doador existente
                $stmt_doador = $pdo->prepare("UPDATE doadores SET tipo_sanguineo = ?, rh = ?, peso = ?, altura = ?, ultima_doacao = ?, proxima_doacao = ?, observacoes = ? WHERE id = ?");
                $stmt_doador->execute([$tipo_sanguineo, $rh, $peso, $altura, $ultima_doacao, $proxima_doacao, $observacoes, $perfil['doador_id']]);
            } else {
                // Inserir novo doador
                $stmt_doador = $pdo->prepare("INSERT INTO doadores (usuario_id, tipo_sanguineo, rh, peso, altura, ultima_doacao, proxima_doacao, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_doador->execute([$usuario_id, $tipo_sanguineo, $rh, $peso, $altura, $ultima_doacao, $proxima_doacao, $observacoes]);
            }
        }
        
        // Commit da transação
        $pdo->commit();

        $sucesso = "Perfil atualizado com sucesso!";
        // Recarregar os dados para exibir no formulário
        $stmt->execute([$usuario_id]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    }

} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro doadores.php: ' . $e->getMessage());
    $erro = $e->getMessage(); // Exibe o erro específico para o usuário
}
?>
<div class="auth-container" style="max-width: 800px;">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-edit"></i>
            <h2>Meu Perfil</h2>
            <p>Veja e atualize suas informações pessoais e de doador.</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            
            <h3 class="form-section-title">Dados Pessoais</h3>
            
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($perfil['nome'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="cpf">CPF (não editável)</label>
                <input type="text" id="cpf" name="cpf" readonly disabled value="<?php echo htmlspecialchars($perfil['cpf'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento *</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required value="<?php echo htmlspecialchars($perfil['data_nascimento'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="telefone">Telefone *</label>
                <input type="text" id="telefone" name="telefone" required value="<?php echo htmlspecialchars($perfil['telefone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($perfil['email'] ?? ''); ?>">
            </div>

            <hr class="form-divider">
            <h3 class="form-section-title">Dados de Doador</h3>
            <p class="form-section-subtitle">Preencha esta seção para se registrar como doador.</p>

            <div class="form-group">
                <label for="tipo_sanguineo">Tipo Sanguíneo</label>
                <select id="tipo_sanguineo" name="tipo_sanguineo">
                    <option value="">Não informado</option>
                    <?php $val = $perfil['tipo_sanguineo'] ?? ''; ?>
                    <option value="A" <?php echo $val === 'A' ? 'selected' : ''; ?>>A</option>
                    <option value="B" <?php echo $val === 'B' ? 'selected' : ''; ?>>B</option>
                    <option value="AB" <?php echo $val === 'AB' ? 'selected' : ''; ?>>AB</option>
                    <option value="O" <?php echo $val === 'O' ? 'selected' : ''; ?>>O</option>
                </select>
            </div>

            <div class="form-group">
                <label for="rh">Fator RH</label>
                <?php $valrh = $perfil['rh'] ?? ''; ?>
                <select id="rh" name="rh">
                    <option value="">Não informado</option>
                    <option value="+" <?php echo $valrh === '+' ? 'selected' : ''; ?>>Positivo (+)</option>
                    <option value="-" <?php echo $valrh === '-' ? 'selected' : ''; ?>>Negativo (-)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="peso">Peso (kg)</label>
                <input type="number" step="0.1" id="peso" name="peso" value="<?php echo htmlspecialchars($perfil['peso'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="altura">Altura (m)</label>
                <input type="number" step="0.01" id="altura" name="altura" value="<?php echo htmlspecialchars($perfil['altura'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="ultima_doacao">Última Doação</label>
                <input type="date" id="ultima_doacao" name="ultima_doacao" value="<?php echo htmlspecialchars($perfil['ultima_doacao'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="proxima_doacao">Próxima Doação Agendada</label>
                <input type="date" id="proxima_doacao" name="proxima_doacao" value="<?php echo htmlspecialchars($perfil['proxima_doacao'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="observacoes">Observações Médicas</label>
                <textarea id="observacoes" name="observacoes"><?php echo htmlspecialchars($perfil['observacoes'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>
    </div>
</div>

<script src="assets/js/script.js"></script>
<?php include 'layout/footer.php'; ?>
