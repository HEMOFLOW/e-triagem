
<?php
require_once 'config/database.php';
session_start();

$mensagem = '';
$etapa = $_SESSION['etapa'] ?? 'cpf';
$cpfInformado = $_POST['cpf'] ?? '';
$novaSenha = $_POST['nova_senha'] ?? '';

// Função para formatar CPF para o padrão do banco
function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    return $cpf;
}
$novaSenha = $_POST['nova_senha'] ?? '';

$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($etapa === 'cpf' && !empty($cpfInformado)) {
        // Formata o CPF para o padrão do banco antes de buscar
        // Formata o CPF para o padrão do banco antes de buscar
        $cpfFormatado = formatarCPF($cpfInformado);
        $sql = "SELECT nome FROM usuarios WHERE cpf = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cpfFormatado]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $_SESSION['etapa'] = 'senha';
            $_SESSION['cpfInformado'] = $cpfFormatado;
            $_SESSION['nomeUsuario'] = $usuario['nome'];
            $etapa = 'senha';
            $mensagem = '<div style="background:#d4edda;border:1px solid #155724;color:#155724;padding:10px;margin-bottom:15px;font-weight:bold;">Usuário encontrado: <strong>' . htmlspecialchars($usuario['nome']) . '</strong><br>Confirme se é o usuário correto e informe a nova senha.</div>';
        } else {
            $mensagem = '<div style="background:#f8d7da;border:1px solid #721c24;color:#721c24;padding:10px;margin-bottom:15px;font-weight:bold;">❌ Usuário não encontrado! Tente novamente.</div>';
            $_SESSION['etapa'] = 'cpf';
            $etapa = 'cpf';
        }
    } elseif ($etapa === 'senha' && !empty($novaSenha)) {
        $cpfInformado = $_SESSION['cpfInformado'] ?? '';
        if (!empty($cpfInformado)) {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET senha = ? WHERE cpf = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$senhaHash, $cpfInformado])) {
                if ($stmt->rowCount() > 0) {
                    $mensagem = '<div style="background:#d4edda;border:1px solid #155724;color:#155724;padding:10px;margin-bottom:15px;font-weight:bold;">✅ Senha atualizada com sucesso!</div>';
                } else {
                    $mensagem = '<div style="background:#f8d7da;border:1px solid #721c24;color:#721c24;padding:10px;margin-bottom:15px;font-weight:bold;">❌ Erro: senha não foi atualizada!</div>';
                }
            } else {
                $mensagem = "<div style='background:#f8d7da;border:1px solid #721c24;color:#721c24;padding:10px;margin-bottom:15px;font-weight:bold;'>❌ Erro ao atualizar senha: " . implode(' | ', $stmt->errorInfo()) . "</div>";
            }
        } else {
            $mensagem = '<div style="background:#f8d7da;border:1px solid #721c24;color:#721c24;padding:10px;margin-bottom:15px;font-weight:bold;">❌ CPF não informado!</div>';
        }
        $_SESSION['etapa'] = 'cpf';
        $_SESSION['cpfInformado'] = '';
        $etapa = 'cpf';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<title>Editar Senha</title>
	<style>
		body { font-family: Arial, sans-serif; background: #f9f9f9; }
		.container { max-width: 400px; margin: 40px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
		h2 { text-align: center; }
		label { display: block; margin-bottom: 8px; }
		input { width: 100%; padding: 8px; margin-bottom: 16px; border-radius: 4px; border: 1px solid #ccc; }
		button { width: 100%; padding: 10px; background: #155724; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
		button:hover { background: #117a37; }
	</style>
</head>
<body>
	<div class="container">
		<h2>Editar Senha</h2>
		<?php echo $mensagem; ?>
		<?php if ($etapa === 'cpf') { ?>
            <form method="post" onsubmit="return corrigeCPF();">
                <label for="cpf">Informe seu CPF:</label>
                    <input type="text" name="cpf" id="cpf" required maxlength="14" placeholder="000.000.000-00" autocomplete="off">
                <button type="submit">Continuar</button>
            </form>
		<?php } elseif ($etapa === 'senha') { ?>
			<div style="margin-bottom:10px;font-weight:bold;">Usuário: <?php echo htmlspecialchars($_SESSION['nomeUsuario'] ?? ''); ?></div>
			<form method="post">
				<label for="nova_senha">Nova Senha:</label>
				<input type="password" name="nova_senha" id="nova_senha" required>
				<button type="submit">Atualizar Senha</button>
			</form>
		<?php } ?>
	</div>
</body>
</html>
