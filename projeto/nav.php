<?php
// Menu de navegação global reutilizável
if (!isset($is_admin)) $is_admin = false;
?>
<nav class="nav">
    <button class="menu-toggle" aria-label="Abrir menu" onclick="document.querySelector('.nav ul').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>
    <ul>
        <li><a href="index.php">Início</a></li>
        <li><a href="login.php">Login</a></li>
        <?php
        // Exibir menu Cadastro igual para todos
        echo '<li><a href="cadastro.php">Cadastro</a></li>';
        ?>
        <?php
        $is_admin = false;
        $is_logged = false;
        if (isset($_SESSION['usuario_id'])) {
            $is_logged = true;
            include_once 'config/database.php';
            $pdo = getConnection();
            $stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
            $stmt->execute([$_SESSION['usuario_id']]);
            $user = $stmt->fetch();
            if ($user && $user['perfil'] === 'admin') {
                $is_admin = true;
            }
        }
        if ($is_logged) {
            echo '<li><a href="doadores.php">Doadores</a></li>';
        }
        ?>
        <?php
        // Exibe o link do questionário apenas para doadores ativos e não bloqueados
        if ($is_logged) {
            $stmt = $pdo->prepare('SELECT id, apto_para_doacao FROM doadores WHERE usuario_id = ? AND bloqueado = 0');
            $stmt->execute([$_SESSION['usuario_id']]);
            $doador = $stmt->fetch();
            if ($doador && $doador['apto_para_doacao']) {
                // Verifica bloqueio de questionário (inapto nas últimas 24h)
                $stmt = $pdo->prepare('SELECT aprovado, data_preenchimento FROM questionarios WHERE usuario_id = ? ORDER BY data_preenchimento DESC LIMIT 1');
                $stmt->execute([$_SESSION['usuario_id']]);
                $ultima = $stmt->fetch();
                $bloqueado = false;
                if ($ultima && !$ultima['aprovado']) {
                    $data = new DateTime($ultima['data_preenchimento']);
                    $agora = new DateTime();
                    $horas = ($agora->getTimestamp() - $data->getTimestamp()) / 3600;
                    if ($horas < 24) {
                        $bloqueado = true;
                    }
                }
                if (!$bloqueado) {
                    echo '<li><a href="questionario.php">Responder Questionário</a></li>';
                }
                // Só mostra agendamento se apto pelo questionário
                $apto = false;
                if ($ultima && $ultima['aprovado']) {
                    $data = new DateTime($ultima['data_preenchimento']);
                    $agora = new DateTime();
                    $horas = ($agora->getTimestamp() - $data->getTimestamp()) / 3600;
                    if ($horas <= 48) {
                        $apto = true;
                    }
                }
                if ($apto) {
                    echo '<li><a href="agendamentos.php">Agendamentos</a></li>';
                }
            }
        }
        ?>
            <?php
            if (isset($_SESSION['usuario_id'])) {
                if ($is_admin) {
                    echo '<li><a href="doacoes.php">Doações</a></li>';
                    echo '<li><a href="configurar_idade_maxima.php"><i class="fas fa-user-clock"></i> Idade Máxima</a></li>';
                }
                echo '<li><a href="logout.php">Sair</a></li>';
                echo '<li><a href="trocar_senha_usuario.php"><i class="fas fa-key"></i> Trocar Senha</a></li>';
            }
            ?>
    </ul>
</nav>
<script>
// Fecha o menu ao clicar fora (mobile)
document.addEventListener('click', function(e) {
    const nav = document.querySelector('.nav');
    const ul = nav.querySelector('ul');
    const btn = nav.querySelector('.menu-toggle');
    if (ul.classList.contains('open') && !nav.contains(e.target)) {
        ul.classList.remove('open');
    }
});
</script>
