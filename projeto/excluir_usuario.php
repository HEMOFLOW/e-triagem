<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include_once 'config/database.php';
$pdo = getConnection();

// Só admin pode excluir
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch();
if (!$user || $user['perfil'] !== 'admin') {
    echo 'Ação não permitida.';
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo 'Usuário não encontrado.';
    exit;
}

// Não permitir que o admin exclua a si mesmo
if ($id == $_SESSION['usuario_id']) {
    echo 'Você não pode excluir seu próprio usuário.';
    exit;
}

// Verifica se o usuário a ser excluído é admin
$stmt = $pdo->prepare('SELECT perfil FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();
if (!$usuario) {
    echo 'Usuário não encontrado.';
    exit;
}

if ($usuario['perfil'] === 'admin') {
    // Conta quantos admins existem
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'");
    $totalAdmins = $stmt->fetchColumn();
    if ($totalAdmins <= 1) {
        echo 'Não é possível excluir o último administrador.';
        exit;
    }
}

// Excluir usuário
$stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
if ($stmt->execute([$id])) {
    header('Location: usuarios.php?msg=excluido');
    exit;
} else {
    echo 'Erro ao excluir usuário.';
}
