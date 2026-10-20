<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pedidos.php");
    exit;
}

$id     = (int)($_POST['id'] ?? 0);
$acao   = $_POST['acao'] ?? '';
$status = $_GET['status'] ?? ($_POST['filtro'] ?? 'todos');

$validos = ['pendente', 'em_analise', 'em_producao', 'concluido', 'cancelado'];

if ($id > 0 && $acao === 'status' && in_array($_POST['novo'] ?? '', $validos, true)) {
    $novo = $_POST['novo'];

    $stmt = $pdo->prepare("SELECT usuario_nome FROM pedidos WHERE id = ?");
    $stmt->execute([$id]);
    $nome = $stmt->fetchColumn();

    if ($nome !== false) {
        $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?")->execute([$novo, $id]);

        $rotulos = [
            'pendente' => 'Pedido recebido', 'em_analise' => 'Em análise',
            'em_producao' => 'Em produção', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado',
        ];
        try {
            $pdo->prepare(
                "INSERT INTO atividades (titulo, descricao, tipo, referencia_id)
                 VALUES (?, ?, 'financeiro', ?)"
            )->execute(["Pedido #$id — " . $rotulos[$novo], $nome, $id]);
        } catch (Throwable $e) { /* opcional */ }
    }
}

if ($id > 0 && $acao === 'excluir') {
    $pdo->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$id]); // itens caem por ON DELETE CASCADE
}

header("Location: pedidos.php?status=" . urlencode($status));
exit;
