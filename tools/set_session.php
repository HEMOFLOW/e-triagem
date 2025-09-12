<?php
// Acessar via browser para simular login: /tools/set_session.php?uid=ID
session_start();
if (!isset($_GET['uid'])) { echo "Passe uid=ID na query string"; exit; }
$_SESSION['usuario_id'] = intval($_GET['uid']);
echo "Sessao criada para usuario_id={$_SESSION['usuario_id']}";
