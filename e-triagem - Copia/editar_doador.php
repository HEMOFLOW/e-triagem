<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();
$tipos = $pdo->query('SELECT tipo, rh FROM tipos_sanguineos ORDER BY tipo, rh')->fetchAll();
$id = $_GET['id'] ?? '';
if (!$id) { echo 'Doador não encontrado.'; exit; }
$stmt = $pdo->prepare('SELECT * FROM doadores WHERE id = ?');
$stmt->execute([$id]);
$doador = $stmt->fetch();
if (!$doador) { echo 'Doador não encontrado.'; exit; }
$mensagem = '';
// Buscar nome do usuário
$usuario_nome = '';
if (isset($doador['usuario_id'])) {
    $stmt = $pdo->prepare('SELECT nome FROM usuarios WHERE id = ?');
    $stmt->execute([$doador['usuario_id']]);
    $usuario_nome = $stmt->fetchColumn();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apto_para_doacao'])) {
        $status = $_POST['apto_para_doacao'] ?? '';
        $tipo_sanguineo = $_POST['tipo_sanguineo'] ?? '';
        $rh = $_POST['rh'] ?? '';
        $peso = $_POST['peso'] ?? '';
        $altura = $_POST['altura'] ?? '';
        $sql = 'UPDATE doadores SET apto_para_doacao = ?, tipo_sanguineo = ?, rh = ?, peso = ?, altura = ? WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$status, $tipo_sanguineo, $rh, $peso, $altura, $id])) {
            $mensagem = '<div style="background:#d4edda;border:1px solid #155724;color:#155724;padding:10px;margin-bottom:15px;font-weight:bold;">Dados do doador atualizados com sucesso!</div>';
            $doador['apto_para_doacao'] = $status;
            $doador['tipo_sanguineo'] = $tipo_sanguineo;
            $doador['rh'] = $rh;
            $doador['peso'] = $peso;
            $doador['altura'] = $altura;
        } else {
            $mensagem = '<div style="background:#f8d7da;border:1px solid #721c24;color:#721c24;padding:10px;margin-bottom:15px;font-weight:bold;">Erro ao atualizar dados!</div>';
        }
    }
    if (isset($_POST['liberar_questionario'])) {
        // Remove a última resposta do questionário se for inapto
        $stmt = $pdo->prepare('SELECT usuario_id FROM doadores WHERE id = ?');
        $stmt->execute([$id]);
        $usuario_id = $stmt->fetchColumn();
        if ($usuario_id) {
            $stmt = $pdo->prepare('SELECT id, aprovado FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1');
            $stmt->execute([$usuario_id]);
            $ultima = $stmt->fetch();
            if ($ultima && !$ultima['aprovado']) {
                $stmt = $pdo->prepare('DELETE FROM questionarios WHERE id = ?');
                $stmt->execute([$ultima['id']]);
                $mensagem = '<div style="background:#d4edda;border:1px solid #155724;color:#155724;padding:10px;margin-bottom:15px;font-weight:bold;">Usuário liberado para responder o questionário novamente!</div>';
            } else {
                $mensagem = '<div style="background:#ffeeba;border:1px solid #856404;color:#856404;padding:10px;margin-bottom:15px;font-weight:bold;">Não há bloqueio ativo para este usuário.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Status do Doador</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <div class="container" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;">
            <h2 style="margin-bottom:10px;">Editar Status do Doador</h2>
            <?php if (!empty($usuario_nome)): ?>
                <div style="font-size:1.15em;font-weight:bold;margin-bottom:12px;">Usuário: <?php echo htmlspecialchars($usuario_nome); ?></div>
            <?php endif; ?>
            <?php echo $mensagem; ?>
            <form method="post" style="display:flex;flex-direction:column;align-items:flex-start;gap:12px;margin-bottom:18px;max-width:400px;">
                <label for="apto" style="font-size:1.1em;">Status do Doador:</label>
                <select name="apto_para_doacao" id="apto" style="font-size:1.1em;padding:6px 12px;">
                    <option value="1" <?php if (is_array($doador) && !empty($doador['apto_para_doacao'])) echo 'selected'; ?>>Apto</option>
                    <option value="0" <?php if (is_array($doador) && isset($doador['apto_para_doacao']) && !$doador['apto_para_doacao']) echo 'selected'; ?>>Inapto</option>
                </select>
                <label for="tipo_sanguineo">Tipo Sanguíneo:</label>
                <select name="tipo_sanguineo" id="tipo_sanguineo" style="width:100px;">
                    <option value="">Selecione</option>
                    <?php foreach ($tipos as $t): ?>
                            <option value="<?php echo $t['tipo'] . $t['rh']; ?>" <?php if (($doador['tipo_sanguineo'] ?? '') . ($doador['rh'] ?? '') === $t['tipo'] . $t['rh']) echo 'selected'; ?>><?php echo $t['tipo'] . $t['rh']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="tipo_sanguineo">Tipo Sanguíneo e RH:</label>
                    <select name="tipo_sanguineo" id="tipo_sanguineo" style="width:140px;">
                        <option value="">Selecione</option>
                        <?php foreach ($tipos as $t): 
                            $valor = $t['tipo'] . $t['rh'];
                            $selecionado = (isset($doador['tipo_sanguineo'], $doador['rh']) && ($doador['tipo_sanguineo'] . $doador['rh']) === $valor) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $valor; ?>" <?php echo $selecionado; ?>><?php echo $t['tipo'] . $t['rh']; ?></option>
                        <?php endforeach; ?>
                    </select>
                <label for="peso">Peso (kg):</label>
                <input type="number" name="peso" id="peso" step="0.01" min="0" style="width:100px;" value="<?php echo htmlspecialchars($doador['peso'] ?? ''); ?>">
                <label for="altura">Altura (m):</label>
                <input type="number" name="altura" id="altura" step="0.01" min="0" style="width:100px;" value="<?php echo htmlspecialchars($doador['altura'] ?? ''); ?>">
                <div style="display:flex;gap:12px;margin-top:10px;">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <?php if (isset($doador['usuario_id'])): ?>
                        <a href="visualizar_usuario.php?id=<?php echo $doador['usuario_id']; ?>" class="btn btn-secondary">Voltar</a>
                    <?php else: ?>
                        <a href="usuarios.php" class="btn btn-secondary">Voltar</a>
                    <?php endif; ?>
                </div>
            </form>
            <form method="post" style="margin-top:0;">
                <input type="hidden" name="liberar_questionario" value="1">
                <button type="submit" class="btn btn-primary" style="background:#007bff;min-width:350px;font-size:1.1em;">Liberar para responder questionário novamente</button>
            </form>
        </div>
    </main>
    </body>
    </html>
</body>
</html>
