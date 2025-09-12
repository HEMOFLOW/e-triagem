<?php
session_start();
include_once 'config/database.php';

// Proteção: apenas administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    http_response_code(403);
    die('Acesso negado.');
}

try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT texto_pergunta, resposta_inapta FROM perguntas ORDER BY id ASC");
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=modelo_perguntas.csv');

    $output = fopen('php://output', 'w');

    // Cabeçalho do CSV
    fputcsv($output, ['texto_pergunta', 'resposta_inapta']);

    // Adiciona uma linha de exemplo se não houver perguntas
    if (empty($perguntas)) {
        fputcsv($output, ['Você teve febre nos últimos 7 dias?', 'SIM']);
        fputcsv($output, ['Você pesa menos de 50kg?', 'SIM']);
        fputcsv($output, ['Você está bem de saúde hoje?', 'NAO']);
    } else {
        // Dados do banco
        foreach ($perguntas as $pergunta) {
            fputcsv($output, $pergunta);
        }
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die('Erro ao gerar o arquivo CSV: ' . $e->getMessage());
}
?>
