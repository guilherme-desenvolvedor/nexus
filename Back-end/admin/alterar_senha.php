<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha  = $_POST['nova_senha']  ?? '';
    $confirmar  = $_POST['confirmar']   ?? '';

    $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($senhaAtual, $admin['password'])) {
        $erro = 'Senha atual incorreta.';
    } elseif (strlen($novaSenha) < 6) {
        $erro = 'A nova senha deve ter ao menos 6 caracteres.';
    } elseif ($novaSenha !== $confirmar) {
        $erro = 'A confirmação não coincide com a nova senha.';
    } else {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $upd  = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $upd->execute([$hash, $_SESSION['admin']]);
        header("Location: configuracoes.php?senha=ok");
        exit;
    }
}

header("Location: configuracoes.php?senha=erro&msg=" . urlencode($erro));
exit;
