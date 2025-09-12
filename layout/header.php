<?php
// Inicia a sessão para ter acesso às variáveis de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'config/database.php';

// Esta variável pode ser definida na página antes de incluir o header
$page_title = $page_title ?? "Projeto QR Code";

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="dashboard.php" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-heartbeat"></i>
                    <h1>Projeto QR Code</h1>
                </a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="doadores.php">Meu Perfil</a></li>
                    
                    <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
                        <!-- Menu Administração -->
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Administração</a>
                            <div class="dropdown-content">
                                <a href="admin_perguntas.php">Gerenciar Perguntas</a>
                            </div>
                        </li>
                        <!-- Menu Usuários -->
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Usuários</a>
                            <div class="dropdown-content">
                                <a href="admin_consultar_usuarios.php">Consultar / Editar</a>
                            </div>
                        </li>
                         <!-- Menu Doadores -->
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Doadores</a>
                            <div class="dropdown-content">
                                <a href="admin_consultar_usuarios.php">Consultar Doadores</a>
                            </div>
                        </li>
                    <?php endif; ?>

                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="container">
