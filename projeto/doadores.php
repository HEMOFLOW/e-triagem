<?php
// Página de gerenciamento de doadores (apenas para admin)
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();

// Verifica perfil do usuário
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch();
$is_admin = $user && $user['perfil'] === 'admin';

// Se for admin, lista todos os doadores
if ($is_admin) {
    // Listar todos que já ativaram o perfil de doador em qualquer momento, trazendo data de nascimento
    $stmt = $pdo->query("SELECT d.*, u.nome, u.email, u.data_nascimento FROM doadores d JOIN usuarios u ON d.usuario_id = u.id ORDER BY d.id DESC");
    $doadores = $stmt->fetchAll();

    // Buscar idade máxima
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'idade_maxima_doador'");
    $stmt->execute();
    $idade_maxima = $stmt->fetchColumn();
    if (!$idade_maxima) $idade_maxima = 69;

    // Desbloquear doador se solicitado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desbloquear_id'])) {
        $id_desbloquear = intval($_POST['desbloquear_id']);
        // Buscar data de nascimento do usuário
        $stmt = $pdo->prepare('SELECT u.data_nascimento FROM doadores d JOIN usuarios u ON d.usuario_id = u.id WHERE d.id = ?');
        $stmt->execute([$id_desbloquear]);
        $data_nasc = $stmt->fetchColumn();
        $idade = null;
        if ($data_nasc) {
            $dt = new DateTime($data_nasc);
            $agora = new DateTime();
            $idade = $agora->diff($dt)->y;
        }
        if ($idade !== null && $idade > $idade_maxima) {
            header('Location: doadores.php?msg=inapto_idade');
            exit;
        }
        $stmt = $pdo->prepare('UPDATE doadores SET bloqueado = 0 WHERE id = ?');
        $stmt->execute([$id_desbloquear]);
        header('Location: doadores.php?msg=desbloqueado');
        exit;
    }
}

// Se for usuário comum, verifica se já é doador e se está dentro da idade máxima
if (!$is_admin) {
    $stmt = $pdo->prepare('SELECT * FROM doadores WHERE usuario_id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $meu_doador = $stmt->fetch();
    $ativacao_erro = null;
    // Buscar idade máxima
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'idade_maxima_doador'");
    $stmt->execute();
    $idade_maxima = $stmt->fetchColumn();
    if (!$idade_maxima) $idade_maxima = 69;
    // Calcular idade do usuário
    $stmt = $pdo->prepare('SELECT data_nascimento FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $data_nasc = $stmt->fetchColumn();
    $idade = null;
    if ($data_nasc) {
        $dt = new DateTime($data_nasc);
        $agora = new DateTime();
        $idade = $agora->diff($dt)->y;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ativar_doador'])) {
        if ($idade !== null && $idade > $idade_maxima) {
            $ativacao_erro = 'Você não pode ser doador: idade acima do limite permitido (' . $idade_maxima . ' anos).';
        } elseif (!$meu_doador) {
            try {
                $stmt = $pdo->prepare('INSERT INTO doadores (usuario_id, apto_para_doacao, faltas, bloqueado) VALUES (?, 1, 0, 0)');
                $stmt->execute([$_SESSION['usuario_id']]);
                header('Location: index.php?msg=ativado');
                exit;
            } catch (Exception $e) {
                $ativacao_erro = 'Erro ao ativar perfil de doador: ' . htmlspecialchars($e->getMessage());
            }
        } else {
            $ativacao_erro = 'Você já é cadastrado como doador!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Doadores</title>
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
        <div class="container">
            <?php if ($is_admin): ?>
                <h2>Gerenciar Doadores</h2>
                <form method="post">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Idade</th>
                            <th>Faltas</th>
                            <th>Bloqueado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doadores as $d): ?>
                        <?php
                            $idade = null;
                            if ($d['data_nascimento']) {
                                $dt = new DateTime($d['data_nascimento']);
                                $agora = new DateTime();
                                $idade = $agora->diff($dt)->y;
                            }
                            $inapto_idade = ($idade !== null && $idade > $idade_maxima);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['nome']); ?></td>
                            <td><?php echo htmlspecialchars($d['email']); ?></td>
                            <td style="font-weight:bold;color:
                                <?php echo $inapto_idade ? '#721c24' : ($d['apto_para_doacao'] ? '#155724' : '#721c24'); ?>;">
                                <?php
                                    if ($inapto_idade) {
                                        echo 'Inapto por idade';
                                    } else {
                                        echo $d['apto_para_doacao'] ? 'Apto' : 'Inapto';
                                    }
                                ?>
                            </td>
                            <td><?php echo $idade !== null ? $idade : '-'; ?></td>
                            <td><?php echo $d['faltas']; ?></td>
                            <td style="font-weight:bold;color:<?php echo $d['bloqueado'] ? '#721c24' : '#155724'; ?>;">
                                <?php echo $d['bloqueado'] ? 'Sim' : 'Não'; ?>
                            </td>
                            <td>
                                <a href="editar_doador.php?id=<?php echo $d['id']; ?>" class="btn btn-edit">Editar</a>
                                <?php if ($d['bloqueado'] && !$inapto_idade): ?>
                                    <button type="submit" name="desbloquear_id" value="<?php echo $d['id']; ?>" class="btn btn-primary" style="margin-left:8px;">Desbloquear</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </form>
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'desbloqueado'): ?>
                    <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">Perfil de doador desbloqueado com sucesso!</div>
                <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'inapto_idade'): ?>
                    <div style="background:#f8d7da;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">Não é possível desbloquear: usuário acima da idade máxima permitida para doação.</div>
                <?php endif; ?>
            <?php else: ?>
                <h2>Seja um Doador!</h2>
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ativado'): ?>
                    <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">Seu perfil de doador foi ativado! Agora você pode responder o questionário e agendar doações.</div>
                <?php endif; ?>
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'precisa_ativar'): ?>
                    <div style="background:#fff3cd;color:#856404;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">Você precisa ativar seu perfil de doador para acessar o questionário.</div>
                <?php endif; ?>
                <?php if (!empty($ativacao_erro)): ?>
                    <div style="background:#f8d7da;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">
                        <?php echo $ativacao_erro; ?>
                    </div>
                <?php endif; ?>
                <?php if ($meu_doador): ?>
                    <div style="background:#eaf6ff;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:18px;font-weight:bold;">Você já é cadastrado como doador! Responda o questionário para ficar apto a doar.</div>
                <?php else: ?>
                    <p style="font-size:1.2em;margin-bottom:18px;">Ative seu perfil de doador para participar das campanhas e salvar vidas! Basta clicar no botão abaixo:</p>
                    <form method="post">
                        <button type="submit" name="ativar_doador" class="btn btn-primary" style="font-size:1.1em;padding:12px 32px;">Quero ser Doador</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
