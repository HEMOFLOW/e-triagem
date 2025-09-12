<?php
$page_title = "Admin: Editar Usuário";
include_once 'config/database.php';
include 'layout/header.php';

// Proteção de página: apenas administradores podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    // O header já deve ter redirecionado, mas por segurança:
    exit('Acesso negado.');
}

$erro = '';
$sucesso = '';
$usuario_id_edicao = $_GET['id'] ?? null;

if (!$usuario_id_edicao) {
    header('Location: admin_consultar_usuarios.php');
    exit;
}

$pdo = getConnection();

try {
    // Buscar dados do usuário e do doador para edição
    $stmt = $pdo->prepare("
        SELECT u.*, d.id as doador_id, d.tipo_sanguineo, d.rh, d.peso, d.altura, d.ultima_doacao, d.proxima_doacao, d.observacoes
        FROM usuarios u
        LEFT JOIN doadores d ON u.id = d.usuario_id
        WHERE u.id = ?
    ");
    $stmt->execute([$usuario_id_edicao]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perfil) {
        header('Location: admin_consultar_usuarios.php?erro=usuario_nao_encontrado');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo->beginTransaction();

        // --- Atualizar dados pessoais (tabela usuarios) ---
        $nome = trim($_POST['nome'] ?? '');
        $data_nascimento = trim($_POST['data_nascimento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nivel_acesso = $_POST['nivel_acesso'] ?? 'usuario';

        if (empty($nome) || empty($data_nascimento) || empty($telefone)) {
            throw new Exception("Nome, data de nascimento e telefone são obrigatórios.");
        }

        $stmt_user = $pdo->prepare("UPDATE usuarios SET nome = ?, data_nascimento = ?, telefone = ?, email = ?, nivel_acesso = ? WHERE id = ?");
        $stmt_user->execute([$nome, $data_nascimento, $telefone, $email, $nivel_acesso, $usuario_id_edicao]);

        // --- Lógica para alterar a senha (opcional) ---
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (!empty($nova_senha)) {
            if ($nova_senha !== $confirmar_senha) {
                throw new Exception("As senhas não coincidem.");
            }
            if (strlen($nova_senha) < 6) {
                throw new Exception("A nova senha deve ter pelo menos 6 caracteres.");
            }

            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt_senha = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt_senha->execute([$senha_hash, $usuario_id_edicao]);
        }

        // --- Atualizar ou Inserir dados do doador (tabela doadores) ---
        $tipo_sanguineo = strtoupper(trim($_POST['tipo_sanguineo'] ?? ''));
        $rh = trim($_POST['rh'] ?? '');
        $peso = !empty($_POST['peso']) ? str_replace(',', '.', trim($_POST['peso'])) : null;
        $altura = !empty($_POST['altura']) ? str_replace(',', '.', trim($_POST['altura'])) : null;
        $ultima_doacao = !empty($_POST['ultima_doacao']) ? $_POST['ultima_doacao'] : null;
        $proxima_doacao = !empty($_POST['proxima_doacao']) ? $_POST['proxima_doacao'] : null;
        $observacoes = trim($_POST['observacoes'] ?? '');

        if (!empty($tipo_sanguineo) && !empty($rh)) {
            if ($perfil['doador_id']) {
                $stmt_doador = $pdo->prepare("UPDATE doadores SET tipo_sanguineo = ?, rh = ?, peso = ?, altura = ?, ultima_doacao = ?, proxima_doacao = ?, observacoes = ? WHERE id = ?");
                $stmt_doador->execute([$tipo_sanguineo, $rh, $peso, $altura, $ultima_doacao, $proxima_doacao, $observacoes, $perfil['doador_id']]);
            } else {
                $stmt_doador = $pdo->prepare("INSERT INTO doadores (usuario_id, tipo_sanguineo, rh, peso, altura, ultima_doacao, proxima_doacao, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_doador->execute([$usuario_id_edicao, $tipo_sanguineo, $rh, $peso, $altura, $ultima_doacao, $proxima_doacao, $observacoes]);
            }
        }
        
        $pdo->commit();
        $sucesso = "Perfil atualizado com sucesso!";
        
        // Recarregar os dados
        $stmt->execute([$usuario_id_edicao]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $erro = $e->getMessage();
}
?>
<div class="auth-container" style="max-width: 800px;">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-edit"></i>
            <h2>Editar Perfil de Usuário</h2>
            <p>Alterando dados de: <strong><?php echo htmlspecialchars($perfil['nome']); ?></strong></p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form" action="admin_edit_usuario.php?id=<?php echo $usuario_id_edicao; ?>">
            
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

            <div class="form-group">
                <label for="nivel_acesso">Nível de Acesso</label>
                <select id="nivel_acesso" name="nivel_acesso">
                    <option value="usuario" <?php echo ($perfil['nivel_acesso'] === 'usuario') ? 'selected' : ''; ?>>Usuário</option>
                    <option value="admin" <?php echo ($perfil['nivel_acesso'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>

            <hr class="form-divider">
            <h3 class="form-section-title">Alterar Senha</h3>
            <p class="form-section-subtitle">Deixe em branco para não alterar a senha.</p>

            <div class="form-group">
                <label for="nova_senha">Nova Senha</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 6 caracteres">
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Nova Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha">
            </div>

            <hr class="form-divider">
            <h3 class="form-section-title">Dados de Doador</h3>

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

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <a href="admin_consultar_usuarios.php" class="btn btn-secondary">Voltar para a Consulta</a>
            </div>
        </form>
    </div>
</div>
<?php include 'layout/footer.php'; ?>
