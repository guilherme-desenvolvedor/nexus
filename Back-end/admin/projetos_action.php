<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: projetos.php");
    exit;
}

$acao = $_POST['acao'] ?? 'criar';

if ($acao === 'excluir') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("SELECT nome FROM projetos WHERE id = ?");
        $stmt->execute([$id]);
        $nome = $stmt->fetchColumn();
        if ($nome !== false) {
            $pdo->prepare("DELETE FROM projetos WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO atividades (titulo, descricao, tipo, referencia_id) VALUES (?, ?, 'projeto', ?)")
                ->execute(["Projeto excluído", $nome, $id]);
        }
    }
    header("Location: projetos.php");
    exit;
}

$nome      = trim($_POST['nome'] ?? '');
$cliente   = trim($_POST['cliente'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$valor     = ($_POST['valor'] ?? '') !== '' ? (float)$_POST['valor'] : null;
$prazo     = ($_POST['prazo'] ?? '') !== '' ? $_POST['prazo'] : null;
$status    = in_array($_POST['status'] ?? '', ['ativo', 'pausado', 'concluido'], true) ? $_POST['status'] : 'ativo';

if ($nome === '' || $cliente === '') {
    header("Location: projetos.php");
    exit;
}

if ($acao === 'editar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare("UPDATE projetos SET nome=?, cliente=?, descricao=?, valor=?, prazo=?, status=? WHERE id=?")
            ->execute([$nome, $cliente, $descricao, $valor, $prazo, $status, $id]);
        $pdo->prepare("INSERT INTO atividades (titulo, descricao, tipo, referencia_id) VALUES (?, ?, 'projeto', ?)")
            ->execute(["Projeto atualizado", $nome, $id]);
    }
} else {
    $stmt = $pdo->prepare("INSERT INTO projetos (nome, cliente, descricao, valor, prazo, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $cliente, $descricao, $valor, $prazo, $status]);
    $id = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO atividades (titulo, descricao, tipo, referencia_id) VALUES (?, ?, 'projeto', ?)")
        ->execute(["Novo projeto criado", $nome, $id]);
}

header("Location: projetos.php");
exit;
