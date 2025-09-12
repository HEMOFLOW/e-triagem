<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'config/database.php';
$pdo = getConnection();
$erro = '';

// Buscar perguntas ativas do banco de dados
$stmt_perguntas = $pdo->query("SELECT id, texto_pergunta, resposta_inapta FROM perguntas ORDER BY id ASC");
$perguntas = $stmt_perguntas->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respostas_usuario = $_POST['respostas'] ?? [];
    $observacoes = $_POST['observacoes'] ?? '';
    $aprovado = true;

    // Validação: verificar se todas as perguntas foram respondidas
    if (count($respostas_usuario) < count($perguntas)) {
        $erro = "Por favor, responda todas as perguntas.";
    } else {
        // Validação: verificar se alguma resposta torna o usuário inapto
        foreach ($perguntas as $pergunta) {
            $id = $pergunta['id'];
            $resposta_dada = $respostas_usuario[$id] ?? null;
            if ($resposta_dada == $pergunta['resposta_inapta']) {
                $aprovado = false;
                break; // Se uma resposta for inapta, já pode parar a verificação
            }
        }
    }

    if (empty($erro)) {
        $pdo->beginTransaction();
        try {
            // 1. Inserir o registro mestre do questionário
            $stmt_quest = $pdo->prepare("INSERT INTO questionarios (usuario_id, aprovado, observacoes) VALUES (?, ?, ?)");
            $stmt_quest->execute([$_SESSION['usuario_id'], $aprovado, $observacoes]);
            $questionario_id = $pdo->lastInsertId();

            // 2. Inserir cada resposta individualmente
            $stmt_resp = $pdo->prepare("INSERT INTO respostas_usuario (questionario_id, pergunta_id, resposta_dada) VALUES (?, ?, ?)");
            foreach ($respostas_usuario as $pergunta_id => $resposta_dada) {
                $stmt_resp->execute([$questionario_id, $pergunta_id, $resposta_dada]);
            }

            $pdo->commit();
            $status_redirect = $aprovado ? 'apto' : 'inapto';
            header('Location: dashboard.php?status=' . $status_redirect);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Ocorreu um erro ao salvar seu questionário. Tente novamente.";
            error_log("Erro ao salvar questionário: " . $e->getMessage());
        }
    }
}

// Carrega dados do usuário para o header
$stmt_usuario = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt_usuario->execute([$_SESSION['usuario_id']]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

$page_title = "Questionário de Aptidão";
include 'layout/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-clipboard-check"></i>
            <h2>Questionário de Aptidão</h2>
            <p>Responda com atenção para verificar sua aptidão para doação.</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <?php foreach ($perguntas as $index => $pergunta): ?>
            <div class="form-group question-group">
                <label><?php echo ($index + 1) . ". " . htmlspecialchars($pergunta['texto_pergunta']); ?></label>
                <div class="radio-group">
                    <label><input type="radio" name="respostas[<?php echo $pergunta['id']; ?>]" value="1" required> Sim</label>
                    <label><input type="radio" name="respostas[<?php echo $pergunta['id']; ?>]" value="0"> Não</label>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="form-group">
                <label for="observacoes">Observações (opcional)</label>
                <textarea id="observacoes" name="observacoes" placeholder="Alguma informação adicional?"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-paper-plane"></i> Enviar Respostas
            </button>
        </form>

        <div class="auth-footer">
            <p><a href="dashboard.php">&larr; Voltar ao Dashboard</a></p>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
<style>
.question-group {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 1rem;
    border-left: 4px solid #007bff;
}
.radio-group {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}
.radio-group label {
    cursor: pointer;
    display: flex;
    align-items: center;
}
</style>
