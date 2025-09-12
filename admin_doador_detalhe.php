<?php
$page_title = "Detalhes do Doador";
include 'layout/header.php';

// Proteção
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header('Location: login.php');
    exit('Acesso negado.');
}

$pdo = getConnection();
$erro = '';
$sucesso = '';
$usuario_id = $_GET['id'] ?? null;
$doador = null;
$ultimo_questionario = null;
$respostas_do_ultimo_questionario = [];

if (!$usuario_id) {
    header('Location: admin_consultar_usuarios.php');
    exit;
}

try {
    // Buscar dados do doador/usuário
    $stmt_doador = $pdo->prepare("SELECT u.*, d.tipo_sanguineo, d.rh, d.ultima_doacao FROM usuarios u LEFT JOIN doadores d ON u.id = d.usuario_id WHERE u.id = ?");
    $stmt_doador->execute([$usuario_id]);
    $doador = $stmt_doador->fetch(PDO::FETCH_ASSOC);

    if (!$doador) {
        header('Location: admin_consultar_usuarios.php');
        exit;
    }

    // Buscar o último questionário respondido
    $stmt_quest = $pdo->prepare("SELECT * FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1");
    $stmt_quest->execute([$usuario_id]);
    $ultimo_questionario = $stmt_quest->fetch(PDO::FETCH_ASSOC);

    if ($ultimo_questionario) {
        // Se encontrou um questionário, busca as respostas dele
        $stmt_respostas = $pdo->prepare("
            SELECT r.pergunta_id, r.resposta_dada, p.texto_pergunta, p.resposta_inapta
            FROM respostas_usuario r
            JOIN perguntas p ON r.pergunta_id = p.id
            WHERE r.questionario_id = ?
            ORDER BY r.pergunta_id ASC
        ");
        $stmt_respostas->execute([$ultimo_questionario['id']]);
        $respostas_do_ultimo_questionario = $stmt_respostas->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- LÓGICA DE VALIDAÇÃO PARA PERMITIR DOAÇÃO ---
    $pode_doar = false;
    $motivo_inapto = 'O usuário ainda não respondeu ao questionário.';

    if ($ultimo_questionario) {
        if ($ultimo_questionario['aprovado'] == 1) {
            $data_questionario = new DateTime($ultimo_questionario['data_preenchimento']);
            $data_atual = new DateTime();
            $diferenca = $data_atual->diff($data_questionario);
            $dias_passados = $diferenca->days;

            if ($dias_passados <= 3) {
                $pode_doar = true;
                $motivo_inapto = '';
            } else {
                $motivo_inapto = 'O questionário de aptidão está expirado (mais de 3 dias).';
            }
        } else {
            $motivo_inapto = 'O resultado do último questionário foi INAPTO.';
        }
    }

    // Processar registro de doação
    if ($pode_doar && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_doacao'])) {
        $data_doacao = $_POST['data_doacao'];
        if (empty($data_doacao)) {
            $erro = "A data da doação é obrigatória.";
        } else {
            $stmt = $pdo->prepare("UPDATE doadores SET ultima_doacao = ? WHERE usuario_id = ?");
            $stmt->execute([$data_doacao, $usuario_id]);
            $sucesso = "Doação registrada com sucesso!";
            $stmt_doador->execute([$usuario_id]); // Recarrega os dados
            $doador = $stmt_doador->fetch(PDO::FETCH_ASSOC);
        }
    }

} catch (Exception $e) {
    $erro = "Ocorreu um erro: " . $e->getMessage();
}
?>
<div class="auth-container" style="max-width: 900px;">
    <div class="auth-card">
        <div class="auth-header"><i class="fas fa-user-check"></i><h2>Detalhes do Doador</h2></div>

        <?php if ($erro) echo "<div class='alert alert-error'><i class='fas fa-exclamation-circle'></i> ".htmlspecialchars($erro)."</div>"; ?>
        <?php if ($sucesso) echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> ".htmlspecialchars($sucesso)."</div>"; ?>

        <div class="info-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="form-section-title">Informações do Doador</h3>
                <a href="admin_edit_usuario.php?id=<?php echo $doador['id']; ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Editar</a>
            </div>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($doador['nome']); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($doador['cpf']); ?></p>
            <p><strong>Tipo Sanguíneo:</strong> <?php echo htmlspecialchars($doador['tipo_sanguineo'] . $doador['rh']); ?></p>
            <p><strong>Última Doação:</strong> <?php echo $doador['ultima_doacao'] ? date('d/m/Y', strtotime($doador['ultima_doacao'])) : 'Nenhuma'; ?></p>
        </div>

        <hr class="form-divider">

        <div class="info-section">
            <h3 class="form-section-title">Respostas do Último Questionário</h3>
            <?php if (!$ultimo_questionario): ?>
                <p>Este usuário ainda não respondeu ao questionário.</p>
            <?php else: ?>
                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i:s', strtotime($ultimo_questionario['data_preenchimento'])); ?></p>
                <p><strong>Resultado:</strong> <?php echo $ultimo_questionario['aprovado'] ? '<span class="status-apto">APTO</span>' : '<span class="status-inapto">INAPTO</span>'; ?></p>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Pergunta</th><th>Resposta</th></tr></thead>
                        <tbody>
                            <?php foreach ($respostas_do_ultimo_questionario as $resp): ?>
                                <?php
                                    $is_inapta = ($resp['resposta_dada'] == $resp['resposta_inapta']);
                                    $row_class = $is_inapta ? 'linha-inapta' : '';
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><?php echo htmlspecialchars($resp['texto_pergunta']); ?></td>
                                    <td><?php echo $resp['resposta_dada'] == 1 ? 'Sim' : 'Não'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($ultimo_questionario['observacoes'])): ?>
                    <p><strong>Observações:</strong> <?php echo htmlspecialchars($ultimo_questionario['observacoes']); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <hr class="form-divider">

        <div class="info-section">
            <h3 class="form-section-title">Registrar Nova Doação</h3>
            <?php if ($pode_doar): ?>
                <form method="POST" action="admin_doador_detalhe.php?id=<?php echo $usuario_id; ?>" class="auth-form">
                    <div class="form-group">
                        <label for="data_doacao">Data da Doação</label>
                        <input type="date" id="data_doacao" name="data_doacao" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" name="registrar_doacao" class="btn btn-primary"><i class="fas fa-tint"></i> Registrar Doação</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i><strong>Doação não permitida:</strong> <?php echo htmlspecialchars($motivo_inapto); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>.linha-inapta { background-color: #f8d7da !important; color: #721c24; font-weight: bold; }</style>
<?php include 'layout/footer.php'; ?>
