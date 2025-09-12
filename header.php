<?php
// session_start() deve ser chamado na página principal (ex: dashboard.php) ANTES de incluir este header.
// Este arquivo não deve mais se conectar ou fazer consultas ao banco de dados.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Projeto QR Code'); ?> - Projeto QR Code</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="/dashboard.php">Projeto QR Code</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="/dashboard.php">Dashboard</a></li>
                        <li><a href="/doadores.php">Meu Perfil</a></li>
                        
                        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 'admin'): ?>
                            <li class="dropdown">
                                <a href="#" class="dropbtn">Administração <i class="fas fa-caret-down"></i></a>
                                <div class="dropdown-content">
                                    <a href="/admin_gerenciar_perguntas.php">Gerenciar Perguntas</a>
                                    <a href="/admin_consultar_usuarios.php">Usuários</a>
                                    <a href="/admin_consultar_doadores.php">Doadores</a>
                                </div>
                            </li>
                        <?php endif; ?>

                        <li><a href="/logout.php">Sair</a></li>
                        <li class="user-welcome">
                           Olá, <?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?>
                        </li>
                    <?php else: ?>
                        <li><a href="/login.php">Login</a></li>
                        <li><a href="/cadastro.php">Cadastro</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">