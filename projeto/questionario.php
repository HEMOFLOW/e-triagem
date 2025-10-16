<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();

// Definir $is_admin corretamente
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user_logado = $stmt->fetch();
$is_admin = $user_logado && $user_logado['perfil'] === 'admin';

// Bloqueio: só doador ativo ou admin pode acessar
if (!$is_admin) {
    $stmt = $pdo->prepare('SELECT * FROM doadores WHERE usuario_id = ? AND apto_para_doacao = 1 AND bloqueado = 0');
    $stmt->execute([$_SESSION['usuario_id']]);
    $doador_ativo = $stmt->fetch();
    if (!$doador_ativo) {
        header('Location: doadores.php?msg=precisa_ativar');
        exit;
    }
}

// Carregar perguntas e respostas corretas do banco
$stmt = $pdo->query('SELECT * FROM questionario_config ORDER BY id ASC');
$config = $stmt->fetchAll();
$perguntas = array_column($config, 'pergunta');
$respostas_corretas = array_column($config, 'resposta_correta');
// Se não houver perguntas no banco, usar padrão
if (count($perguntas) === 0) {
    $perguntas = [
        'Você está em boas condições de saúde?',
        'Dormiu pelo menos 6 horas nas últimas 24h?',
        'Está alimentado?',
        'Está gripado, resfriado ou com febre?',
        'Fez cirurgia nos últimos 12 meses?',
        'Fez tatuagem ou piercing nos últimos 12 meses?',
        'Teve contato com pessoa com hepatite?',
        'Usou drogas ilícitas?',
        'Está gestante ou amamentando?',
        'Teve comportamento de risco para doenças sexualmente transmissíveis?'
    ];
    $respostas_corretas = ['1','1','1','0','0','0','0','0','0','0'];
}
$msg = '';
$usuario_id = $_SESSION['usuario_id'];
// ADMIN: editar perguntas e respostas corretas
if ($is_admin && isset($_POST['editar_perguntas'])) {
    $perguntas = $_POST['pergunta'] ?? $perguntas;
    $respostas_corretas = array_map(function($v) { return (string)$v === '1' ? '1' : '0'; }, $_POST['resposta_correta'] ?? $respostas_corretas);
    // Limpar tabela e inserir novas perguntas/respostas
    $pdo->exec('DELETE FROM questionario_config');
    $stmt = $pdo->prepare('INSERT INTO questionario_config (pergunta, resposta_correta) VALUES (?, ?)');
    foreach ($perguntas as $i => $pergunta) {
        $stmt->execute([
            $pergunta,
            $respostas_corretas[$i] ?? '0'
        ]);
    }
    $msg = '<span style="color:green;">Perguntas e respostas atualizadas!</span>';
    // Recarregar do banco
    $stmt = $pdo->query('SELECT * FROM questionario_config ORDER BY id ASC');
    $config = $stmt->fetchAll();
    $perguntas = array_column($config, 'pergunta');
    $respostas_corretas = array_column($config, 'resposta_correta');
}

// Não usa mais sessão para perguntas/respostas corretas

// DOADOR: responder questionário
if (!$is_admin && isset($_POST['responder'])) {
    $respostas = $_POST['resposta'] ?? [];
    $aprovado = 1;
    foreach ($respostas_corretas as $i => $correta) {
        $resposta_usuario = isset($respostas[$i]) ? (string)$respostas[$i] : '';
        $correta_str = (string)$correta;
        if ($resposta_usuario !== $correta_str) {
            $aprovado = 0;
            break;
        }
    }
    // Salva no banco (agora incluindo observacoes)
    $sql = "INSERT INTO questionarios (
        usuario_id, pergunta_1, pergunta_2, pergunta_3, pergunta_4, pergunta_5, pergunta_6, pergunta_7, pergunta_8, pergunta_9, pergunta_10, observacoes, aprovado, data_preenchimento
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        $usuario_id,
        $respostas[0] ?? 0,
        $respostas[1] ?? 0,
        $respostas[2] ?? 0,
        $respostas[3] ?? 0,
        $respostas[4] ?? 0,
        $respostas[5] ?? 0,
        $respostas[6] ?? 0,
        $respostas[7] ?? 0,
        $respostas[8] ?? 0,
        $respostas[9] ?? 0,
        '', // observacoes (vazio)
        $aprovado
    ]);
    if ($ok) {
        if ($aprovado) {
            $msg = '<div style="background:#eafaf1;border:2px solid #27ae60;color:#145a32;padding:18px 24px;margin:20px auto 24px auto;border-radius:8px;max-width:600px;text-align:center;font-size:1.2em;box-shadow:0 2px 8px #0001;">'
                . '<i class="fas fa-check-circle" style="font-size:2em;"></i><br><strong>Parabéns! Questionário aprovado.</strong><br>Você está apto a doar por 2 dias.<br>Agora você pode agendar sua doação normalmente!'
                . '</div>';
        } else {
            $msg = '<div style="background:#ffeaea;border:2px solid #e74c3c;color:#c0392b;padding:18px 24px;margin:20px auto 24px auto;border-radius:8px;max-width:600px;text-align:center;font-size:1.2em;box-shadow:0 2px 8px #0001;">'
                . '<i class="fas fa-times-circle" style="font-size:2em;"></i><br><strong>Questionário reprovado!</strong><br>Você não está apto a doar no momento.<br>Consulte as orientações e tente novamente depois.'
                . '</div>';
        }
    } else {
        $msg = '<div style="background:#ffeaea;border:2px solid #e74c3c;color:#c0392b;padding:18px 24px;margin:20px auto 24px auto;border-radius:8px;max-width:600px;text-align:center;font-size:1.1em;box-shadow:0 2px 8px #0001;">Erro ao salvar respostas.</div>';
    }
}

// ...existing code...

// Buscar última resposta do questionário
$stmt = $pdo->prepare('SELECT * FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1');
$stmt->execute([$usuario_id]);
$ultima = $stmt->fetch();
$apto = false;
$restante = 0;
if ($ultima) {
    $data = new DateTime($ultima['data_preenchimento']);
    $agora = new DateTime();
    $horas = ($agora->getTimestamp() - $data->getTimestamp()) / 3600;
    if ($ultima['aprovado'] && $horas <= 48) {
        $apto = true;
        $restante = 48 - (int)$horas;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Questionário de Triagem</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-view" style="background:#212529;color:#fff;font-weight:bold;padding:10px 22px;margin-bottom:18px;display:inline-block;text-decoration:none;">&#8592; Início</a>
        <h2>Questionário de Triagem</h2>
        <?php echo $msg; ?>
        <?php if ($is_admin): ?>
            <form method="post">
                <h3>Editar Perguntas e Respostas Corretas</h3>
                <input type="hidden" name="editar_perguntas" value="1">
                <table class="table">
                    <thead><tr><th>Pergunta</th><th>Resposta Correta</th></tr></thead>
                    <tbody>
                    <?php foreach ($perguntas as $i => $pergunta): ?>
                        <tr>
                            <td><input type="text" name="pergunta[]" value="<?php echo htmlspecialchars($pergunta); ?>" style="width:350px;"></td>
                            <td>
                                <select name="resposta_correta[]">
                                    <option value="1" <?php if ($respostas_corretas[$i]) echo 'selected'; ?>>Sim</option>
                                    <option value="0" <?php if (!$respostas_corretas[$i]) echo 'selected'; ?>>Não</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
            <hr>
        <?php else: ?>
            <h3>Status do Questionário</h3>
            <?php
            $bloqueado = false;
            $horas_restantes = 0;
            if ($ultima && !$ultima['aprovado']) {
                $data = new DateTime($ultima['data_preenchimento']);
                $agora = new DateTime();
                $horas = ($agora->getTimestamp() - $data->getTimestamp()) / 3600;
                if ($horas < 24) {
                    $bloqueado = true;
                    $horas_restantes = ceil(24 - $horas);
                }
            }
            // Se não houver resposta anterior, não bloquear
            if (!$ultima) {
                $bloqueado = false;
            }
            ?>
            <?php if ($ultima): ?>
                <p>Última resposta: <b><?php echo date('d/m/Y H:i', strtotime($ultima['data_preenchimento'])); ?></b></p>
                <p>Status: <b style="color:<?php echo $apto ? 'green' : 'red'; ?>;">
                    <?php echo $apto ? 'Apto a agendar doação (' . $restante . 'h restantes)' : 'Inapto para agendar doação'; ?>
                </b></p>
            <?php endif; ?>
            <?php if ($bloqueado): ?>
                <div style="background:#ffeaea;border:2px solid #e74c3c;color:#c0392b;padding:18px 24px;margin:20px auto 24px auto;border-radius:8px;max-width:600px;text-align:center;font-size:1.1em;box-shadow:0 2px 8px #0001;">
                    <i class="fas fa-exclamation-triangle" style="font-size:2em;"></i><br>
                    Você foi considerado inapto no último questionário.<br>
                    Só será possível responder novamente em <?php echo $horas_restantes; ?> hora(s).
                </div>
            <?php else: ?>
                <form method="post">
                    <h3>Responda o questionário para doar</h3>
                    <input type="hidden" name="responder" value="1">
                    <table class="table">
                        <thead><tr><th>Pergunta</th><th>Sua Resposta</th></tr></thead>
                        <tbody>
                        <?php foreach ($perguntas as $i => $pergunta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pergunta); ?></td>
                                <td>
                                    <select name="resposta[<?php echo $i; ?>]" style="width: 100%; min-width: 120px; font-size: 1.1em; padding: 10px; border-radius: 8px; border: 1px solid #ccc; box-sizing: border-box;">
                                        <option value="1">Sim</option>
                                        <option value="0">Não</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Enviar Respostas</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
