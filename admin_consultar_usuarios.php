<?php
$page_title = "Admin: Consultar Usuários";
include 'layout/header.php';

// Proteção de página já está no header, mas uma dupla verificação não faz mal.
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') {
    // O header já deve ter redirecionado, mas por segurança:
    exit('Acesso negado.');
}

$pdo = getConnection();
$erro = '';
$usuarios = [];
$search_term = '';

try {
    // Lógica de busca
    $sql = "
        SELECT 
            u.id, 
            u.nome, 
            u.cpf, 
            u.telefone,
            d.tipo_sanguineo, 
            d.rh, 
            d.ultima_doacao
        FROM usuarios u
        LEFT JOIN doadores d ON u.id = d.usuario_id
    ";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = trim($_GET['search']);
        // Corrigido: usar placeholders diferentes para cada campo de busca
        $sql .= " WHERE u.nome LIKE :search_nome OR u.cpf LIKE :search_cpf";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'search_nome' => '%' . $search_term . '%',
            'search_cpf' => '%' . $search_term . '%'
        ]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $usuarios = $stmt->fetchAll();

} catch (Exception $e) {
    $erro = "Ocorreu um erro ao consultar os usuários: " . $e->getMessage();
}

?>
<div class="auth-container" style="max-width: 1000px;">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-users"></i>
            <h2>Consultar Usuários</h2>
            <p>Busque por usuários para visualizar ou editar seus detalhes.</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- Formulário de Busca -->
        <form method="GET" class="auth-form" action="admin_consultar_usuarios.php">
            <div class="form-group">
                <label for="search">Buscar por Nome ou CPF</label>
                <input type="text" id="search" name="search" placeholder="Digite o nome ou CPF..." value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="admin_consultar_usuarios.php" class="btn btn-secondary">Limpar Busca</a>
            </div>
        </form>

        <hr class="form-divider">

        <!-- Lista de Usuários -->
        <h3 class="form-section-title">Resultados da Busca</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Contato</th>
                        <th>Tipo Sanguíneo</th>
                        <th>Última Doação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['cpf']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['telefone']); ?></td>
                                <td>
                                    <?php if ($usuario['tipo_sanguineo']): ?>
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($usuario['tipo_sanguineo'] . $usuario['rh']); ?></span>
                                    <?php else: ?>
                                        <span class="badge">Não inf.</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $usuario['ultima_doacao'] ? date('d/m/Y', strtotime($usuario['ultima_doacao'])) : 'Nenhuma'; ?>
                                </td>
                                <td class="actions">
                                    <a href="admin_doador_detalhe.php?id=<?php echo $usuario['id']; ?>" class="btn-action btn-view" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="admin_edit_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-action btn-edit" title="Editar Usuário">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'layout/footer.php'; ?>
