<?php
$page_title = "Gerenciar Perguntas";
include 'layout/header.php';

// Proteção
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header('Location: login.php');
    exit('Acesso negado.');
}

$pdo = getConnection();
$erro = '';
$sucesso = '';
$pergunta_edit = null;

// Processar formulário (Adicionar ou Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto_pergunta = trim($_POST['texto_pergunta']);
    $resposta_inapta = $_POST['resposta_inapta'];
    $pergunta_id = $_POST['pergunta_id'] ?? null;

    if (empty($texto_pergunta) || !isset($resposta_inapta) || $resposta_inapta === '') {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        if ($pergunta_id) {
            // Lógica de EDIÇÃO
            $stmt = $pdo->prepare("UPDATE perguntas SET texto_pergunta = ?, resposta_inapta = ? WHERE id = ?");
            $stmt->execute([$texto_pergunta, $resposta_inapta, $pergunta_id]);
            $sucesso = "Pergunta atualizada com sucesso!";
        } else {
            // Lógica para ADICIONAR
            $stmt = $pdo->prepare("INSERT INTO perguntas (texto_pergunta, resposta_inapta) VALUES (?, ?)");
            $stmt->execute([$texto_pergunta, $resposta_inapta]);
            $sucesso = "Pergunta adicionada com sucesso!";
        }
    }
}

// Deletar pergunta
if (isset($_GET['delete'])) {
    $id_delete = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM perguntas WHERE id = ?");
    $stmt->execute([$id_delete]);
    $sucesso = "Pergunta deletada com sucesso!";
    // Redireciona para a mesma página sem os parâmetros GET para evitar re-deletar ao atualizar
    header('Location: admin_perguntas.php?status=deletado');
    exit;
}

// Buscar pergunta para edição
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM perguntas WHERE id = ?");
    $stmt->execute([$id_edit]);
    $pergunta_edit = $stmt->fetch();
}

// Buscar todas as perguntas para listar
$stmt_perguntas = $pdo->query("SELECT * FROM perguntas ORDER BY id ASC");
$perguntas = $stmt_perguntas->fetchAll();

?>
<div class="auth-container" style="max-width: 900px;">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-question-circle"></i>
            <h2>Gerenciar Perguntas do Questionário</h2>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>

        <!-- Formulário para Adicionar/Editar -->
        <form method="POST" action="admin_perguntas.php" class="auth-form">
            <input type="hidden" name="pergunta_id" value="<?php echo $pergunta_edit['id'] ?? ''; ?>">
            <h3 class="form-section-title"><?php echo $pergunta_edit ? 'Editar Pergunta' : 'Adicionar Nova Pergunta'; ?></h3>
            
            <div class="form-group">
                <label for="texto_pergunta">Texto da Pergunta</label>
                <input type="text" id="texto_pergunta" name="texto_pergunta" value="<?php echo htmlspecialchars($pergunta_edit['texto_pergunta'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="resposta_inapta">Qual resposta torna o doador INAPTO?</label>
                <select id="resposta_inapta" name="resposta_inapta" required>
                    <option value="" <?php echo !isset($pergunta_edit['resposta_inapta']) ? 'selected' : ''; ?>>Selecione...</option>
                    <option value="1" <?php echo (isset($pergunta_edit['resposta_inapta']) && $pergunta_edit['resposta_inapta'] == 1) ? 'selected' : ''; ?>>Sim</option>
                    <option value="0" <?php echo (isset($pergunta_edit['resposta_inapta']) && $pergunta_edit['resposta_inapta'] == 0) ? 'selected' : ''; ?>>Não</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $pergunta_edit ? 'Salvar Alterações' : 'Adicionar Pergunta'; ?></button>
            <?php if ($pergunta_edit): ?>
                <a href="admin_perguntas.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php endif; ?>
        </form>

        <hr class="form-divider">

        <!-- Lista de Perguntas -->
        <h3 class="form-section-title">Perguntas Atuais</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Texto da Pergunta</th>
                        <th>Resposta Inapta</th>
                        <th style="width: 220px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($perguntas)): ?>
                        <tr>
                            <td colspan="4">Nenhuma pergunta cadastrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($perguntas as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['texto_pergunta']); ?></td>
                                <td><?php echo $p['resposta_inapta'] == 1 ? 'Sim' : 'Não'; ?></td>
                                <td>
                                    <a href="admin_perguntas.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i> Editar</a>
                                    <a href="admin_perguntas.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Tem certeza que deseja deletar esta pergunta?')" class="btn btn-sm btn-danger" title="Deletar"><i class="fas fa-trash"></i> Deletar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'layout/footer.php'; ?>
