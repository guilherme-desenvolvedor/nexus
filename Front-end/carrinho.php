<?php
session_start();
require __DIR__ . "/../Back-end/config.php";
$CATALOGO = require __DIR__ . "/../Back-end/pacotes.php";

/* ────────────────────────────────────────────────────────────────────────────
   ESTADO
   ──────────────────────────────────────────────────────────────────────────── */
$logado = isset($_SESSION['nexus_user']);
$user   = $_SESSION['nexus_user'] ?? null;

if (!isset($_SESSION['nexus_cart']) || !is_array($_SESSION['nexus_cart'])) {
    $_SESSION['nexus_cart'] = [];   // formato: [ 'starter' => 2, 'premium' => 1 ]
}

/* Rótulos de status do pedido */
$STATUS_INFO = [
    'pendente'    => ['rotulo' => 'Pedido recebido',  'classe' => 'pendente'],
    'em_analise'  => ['rotulo' => 'Em análise',       'classe' => 'em_analise'],
    'em_producao' => ['rotulo' => 'Em produção',      'classe' => 'em_producao'],
    'concluido'   => ['rotulo' => 'Concluído',        'classe' => 'concluido'],
    'cancelado'   => ['rotulo' => 'Cancelado',        'classe' => 'cancelado'],
];
$FLUXO = ['pendente', 'em_analise', 'em_producao', 'concluido'];

/* ────────────────────────────────────────────────────────────────────────────
   AÇÕES (POST) — padrão PRG: processa e redireciona.
   O resultado vai numa mensagem de sessão ("flash") que aparece UMA vez só:
   não fica na URL, não reaparece no F5 e não dá para forjar digitando ?ok=123.
   ──────────────────────────────────────────────────────────────────────────── */
function flash(string $tipo, array $dados = []): void {
    $_SESSION['nexus_flash'] = ['tipo' => $tipo] + $dados;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao   = $_POST['acao']   ?? '';
    $pacote = $_POST['pacote'] ?? '';
    $cart   = &$_SESSION['nexus_cart'];

    switch ($acao) {
        case 'add':
            if (isset($CATALOGO[$pacote])) {
                $cart[$pacote] = min(99, ($cart[$pacote] ?? 0) + 1);
                flash('add', ['nome' => $CATALOGO[$pacote]['nome']]);
                header('Location: carrinho.php');
                exit;
            }
            break;

        case 'set_qtd':
            if (isset($CATALOGO[$pacote])) {
                $q = max(0, min(99, (int)($_POST['qtd'] ?? 0)));
                if ($q === 0) { unset($cart[$pacote]); flash('removido', ['nome' => $CATALOGO[$pacote]['nome']]); }
                else          { $cart[$pacote] = $q; }
            }
            break;

        case 'remove':
            if (isset($CATALOGO[$pacote]) && isset($cart[$pacote])) {
                flash('removido', ['nome' => $CATALOGO[$pacote]['nome']]);
            }
            unset($cart[$pacote]);
            break;

        case 'limpar':
            if (!empty($cart)) flash('limpo');
            $cart = [];
            break;

        case 'checkout':
            if (!$logado) {
                header('Location: Login.php?redir=carrinho');
                exit;
            }
            if (empty($cart)) {
                flash('erro_vazio');
                header('Location: carrinho.php');
                exit;
            }
            // Monta itens válidos + total (preço sempre do catálogo)
            $itens = [];
            $total = 0.0;
            foreach ($cart as $key => $qtd) {
                if (!isset($CATALOGO[$key])) continue;
                $qtd = max(1, min(99, (int)$qtd));
                $preco = (float)$CATALOGO[$key]['preco'];
                $itens[] = [$key, $CATALOGO[$key]['nome'], $preco, $qtd];
                $total  += $preco * $qtd;
            }
            if (empty($itens)) {
                flash('erro_vazio');
                header('Location: carrinho.php');
                exit;
            }

            $obs = trim($_POST['observacoes'] ?? '');
            if (mb_strlen($obs) > 2000) $obs = mb_substr($obs, 0, 2000);

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare(
                    "INSERT INTO pedidos (usuario_email, usuario_nome, usuario_cpf, status, total, observacoes)
                     VALUES (?, ?, ?, 'pendente', ?, ?)"
                );
                $stmt->execute([
                    $user['email'],
                    $user['nome'],
                    $user['cpf'] ?? null,
                    number_format($total, 2, '.', ''),
                    $obs !== '' ? $obs : null,
                ]);
                $pedidoId = (int)$pdo->lastInsertId();

                $stmtItem = $pdo->prepare(
                    "INSERT INTO pedido_itens (pedido_id, pacote_key, pacote_nome, preco, quantidade)
                     VALUES (?, ?, ?, ?, ?)"
                );
                foreach ($itens as [$k, $nome, $preco, $qtd]) {
                    $stmtItem->execute([$pedidoId, $k, $nome, number_format($preco, 2, '.', ''), $qtd]);
                }

                // Registra no feed de atividades do painel (se a tabela existir)
                try {
                    $pdo->prepare(
                        "INSERT INTO atividades (titulo, descricao, tipo, referencia_id)
                         VALUES (?, ?, 'financeiro', ?)"
                    )->execute([
                        'Novo pedido no site',
                        $user['nome'] . ' — R$ ' . number_format($total, 2, ',', '.'),
                        $pedidoId,
                    ]);
                } catch (Throwable $e) { /* opcional */ }

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                flash('erro_falha');
                header('Location: carrinho.php');
                exit;
            }

            $_SESSION['nexus_cart'] = [];
            flash('pedido', ['id' => $pedidoId, 'total' => $total]);
            header('Location: carrinho.php');
            exit;
    }

    header('Location: carrinho.php');
    exit;
}

/* Lê e consome a mensagem de sessão (se houver) */
$flash = $_SESSION['nexus_flash'] ?? null;
unset($_SESSION['nexus_flash']);

/* ────────────────────────────────────────────────────────────────────────────
   DADOS PARA EXIBIÇÃO
   ──────────────────────────────────────────────────────────────────────────── */
$itensCarrinho = [];
$totalCarrinho = 0.0;
foreach ($_SESSION['nexus_cart'] as $key => $qtd) {
    if (!isset($CATALOGO[$key])) { unset($_SESSION['nexus_cart'][$key]); continue; }
    $qtd = max(1, min(99, (int)$qtd));
    $sub = $CATALOGO[$key]['preco'] * $qtd;
    $itensCarrinho[] = [
        'key'   => $key,
        'nome'  => $CATALOGO[$key]['nome'],
        'desc'  => $CATALOGO[$key]['desc'],
        'preco' => $CATALOGO[$key]['preco'],
        'qtd'   => $qtd,
        'sub'   => $sub,
    ];
    $totalCarrinho += $sub;
}
$qtdTotalItens = array_sum(array_map(fn($i) => $i['qtd'], $itensCarrinho));

/* Pedidos do usuário */
$meusPedidos = [];
if ($logado) {
    try {
        $stmt = $pdo->prepare(
            "SELECT * FROM pedidos WHERE usuario_email = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$user['email']]);
        $meusPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($meusPedidos) {
            $ids = array_column($meusPedidos, 'id');
            $in  = implode(',', array_fill(0, count($ids), '?'));
            $stmtI = $pdo->prepare("SELECT * FROM pedido_itens WHERE pedido_id IN ($in)");
            $stmtI->execute($ids);
            $itensPorPedido = [];
            foreach ($stmtI->fetchAll(PDO::FETCH_ASSOC) as $it) {
                $itensPorPedido[$it['pedido_id']][] = $it;
            }
            foreach ($meusPedidos as &$p) {
                $p['itens'] = $itensPorPedido[$p['id']] ?? [];
            }
            unset($p);
        }
    } catch (Throwable $e) {
        $meusPedidos = [];
        $erroTabela  = true;
    }
}

$primeiroNome = $logado ? explode(' ', trim($user['nome']))[0] : '';
$brl = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu Carrinho — NEXUS</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<style>
  :root{
    --black:#080808; --red:#E01B24; --red-dark:#a01318;
    --red-vivid:#FF1A1A; --white:#F5F5F5; --gray:#111111;
  }
  *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
  [hidden]{display:none!important;}
  html{scroll-behavior:smooth;}
  body{background:var(--black);color:var(--white);font-family:'DM Sans',sans-serif;overflow-x:hidden;cursor:none;min-height:100vh;}

  .cursor{width:10px;height:10px;background:var(--red);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9999;transition:transform .15s;}
  .cursor-ring{width:34px;height:34px;border:1px solid rgba(224,27,36,.45);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9998;transition:all .25s ease;}

  .bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(224,27,36,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(224,27,36,.04) 1px,transparent 1px);background-size:55px 55px;mask-image:radial-gradient(ellipse at center,black 15%,transparent 70%);z-index:0;pointer-events:none;}

  .wrap{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:9rem 1.5rem 5rem;}

  .page-head{margin-bottom:2.5rem;}
  .page-kicker{display:inline-flex;align-items:center;gap:.6rem;font-size:.68rem;letter-spacing:.22em;text-transform:uppercase;color:var(--red);margin-bottom:1rem;}
  .page-kicker::before{content:'';width:5px;height:5px;background:var(--red);border-radius:50%;}
  .page-head h1{font-family:'MuseoModerno',sans-serif;font-size:clamp(2.2rem,5vw,3.4rem);font-weight:900;line-height:1;}
  .page-head h1 em{font-style:normal;color:var(--red-vivid);}

  /* LAYOUT: itens + resumo */
  .cart-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1.5rem;align-items:start;}
  .cart-layout.is-empty{grid-template-columns:1fr;}
  .cart-layout > .card + .card{margin-top:0;}
  .summary{position:sticky;top:6.5rem;}
  .pedidos-card{margin-top:1.5rem;}
  .back-link{display:inline-flex;align-items:center;gap:.5rem;margin-top:1.2rem;font-size:.74rem;letter-spacing:.12em;text-transform:uppercase;font-weight:600;color:rgba(245,245,245,.45);text-decoration:none;transition:color .2s;}
  .back-link:hover{color:var(--red);}
  .back-link svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;}

  .sum-lines{list-style:none;display:flex;flex-direction:column;gap:.7rem;padding-bottom:1.2rem;margin-bottom:1.2rem;border-bottom:1px solid rgba(255,255,255,.08);}
  .sum-lines li{display:flex;justify-content:space-between;gap:1rem;font-size:.85rem;color:rgba(245,245,245,.62);}
  .sum-lines li span:last-child{white-space:nowrap;color:var(--white);}
  .sum-note{font-size:.74rem;line-height:1.6;color:rgba(245,245,245,.38);margin-top:1rem;}
  .btn-link{display:block;width:100%;margin-top:.9rem;background:none;border:none;text-align:center;font-family:'DM Sans',sans-serif;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;font-weight:600;color:rgba(245,245,245,.38);cursor:none;transition:color .2s;}
  .btn-link:hover{color:var(--red);}
  .btn[disabled]{opacity:.6;pointer-events:none;}

  .card{background:rgba(14,14,14,.9);border:1px solid rgba(255,255,255,.07);border-radius:6px;padding:2rem;backdrop-filter:blur(10px);}
  .card + .card{margin-top:1.5rem;}
  .card-title{font-family:'MuseoModerno',sans-serif;font-size:1.15rem;font-weight:700;margin-bottom:1.4rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
  .card-title .count{font-family:'DM Sans',sans-serif;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(245,245,245,.4);font-weight:600;}

  /* ITEM DO CARRINHO */
  .cart-item{display:grid;grid-template-columns:1fr auto auto;gap:1.2rem;align-items:center;padding:1.2rem 0;border-top:1px solid rgba(255,255,255,.06);}
  .cart-item:first-of-type{border-top:none;}
  .ci-nome{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;}
  .ci-desc{font-size:.8rem;color:rgba(245,245,245,.42);margin-top:.25rem;line-height:1.5;max-width:42ch;}
  .ci-preco-unit{font-size:.75rem;color:rgba(245,245,245,.35);margin-top:.35rem;}
  .stepper{display:flex;align-items:center;border:1px solid rgba(255,255,255,.12);border-radius:3px;overflow:hidden;}
  .stepper button{width:32px;height:32px;background:rgba(255,255,255,.03);border:none;color:var(--white);font-size:1rem;cursor:none;transition:background .2s;}
  .stepper button:hover{background:rgba(224,27,36,.2);}
  .stepper .q{min-width:34px;text-align:center;font-size:.9rem;font-weight:600;}
  .ci-right{text-align:right;display:flex;flex-direction:column;gap:.5rem;align-items:flex-end;}
  .ci-sub{font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.05rem;}
  .ci-remove{background:none;border:none;color:rgba(245,245,245,.35);font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;cursor:none;transition:color .2s;}
  .ci-remove:hover{color:var(--red);}

  .cart-empty{text-align:center;padding:2rem 1rem;color:rgba(245,245,245,.4);}
  .cart-empty p{margin-bottom:1.4rem;font-size:.92rem;}

  .cart-foot{border-top:1px solid rgba(255,255,255,.1);margin-top:.6rem;padding-top:1.5rem;}
  .cart-total-row{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.4rem;}
  .cart-total-row .lbl{font-size:.75rem;letter-spacing:.16em;text-transform:uppercase;color:rgba(245,245,245,.45);}
  .cart-total-row .val{font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.8rem;color:var(--red-vivid);}

  .obs-field{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:2px;padding:.8rem 1rem;color:var(--white);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;resize:vertical;min-height:80px;margin-bottom:1.2rem;cursor:none;}
  .obs-field:focus{border-color:rgba(224,27,36,.5);background:rgba(224,27,36,.04);}
  .obs-label{display:block;font-size:.66rem;letter-spacing:.18em;text-transform:uppercase;color:rgba(245,245,245,.38);margin-bottom:.5rem;}

  .btn{display:inline-flex;align-items:center;justify-content:center;gap:.6rem;padding:.95rem 1.6rem;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.82rem;letter-spacing:.14em;text-transform:uppercase;border-radius:2px;cursor:none;text-decoration:none;border:1px solid transparent;transition:all .3s;width:100%;}
  .btn svg{width:15px;height:15px;stroke:currentColor;fill:none;}
  .btn-primary{background:var(--red);color:#fff;border-color:var(--red);}
  .btn-primary:hover{background:var(--red-dark);transform:translateY(-2px);}
  .btn-ghost{background:transparent;color:rgba(245,245,245,.55);border-color:rgba(255,255,255,.12);}
  .btn-ghost:hover{border-color:var(--red);color:var(--red);}
  .btn-row{display:flex;gap:.8rem;flex-wrap:wrap;}
  .btn-row .btn{width:auto;flex:1;min-width:180px;}

  .login-hint{display:flex;gap:.8rem;align-items:center;background:rgba(224,27,36,.06);border:1px solid rgba(224,27,36,.25);border-radius:3px;padding:1rem 1.2rem;font-size:.86rem;color:rgba(245,245,245,.7);margin-bottom:1.2rem;}
  .login-hint a{color:var(--red);font-weight:600;text-decoration:none;}
  .login-hint a:hover{color:var(--red-vivid);}

  /* SUGESTÃO DE PACOTES */
  .sug-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-top:.4rem;}
  .sug{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:3px;padding:1.1rem;text-align:left;}
  .sug h4{font-family:'MuseoModerno',sans-serif;font-size:.85rem;letter-spacing:.06em;text-transform:uppercase;color:var(--red);margin-bottom:.4rem;}
  .sug .p{color:var(--white);font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.15rem;margin-bottom:.8rem;}
  .sug button{width:100%;background:transparent;border:1px solid rgba(224,27,36,.4);color:var(--red);padding:.5rem;border-radius:2px;font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700;cursor:none;transition:all .25s;}
  .sug button:hover{background:var(--red);color:#fff;}

  /* PEDIDOS */
  .pedido{border:1px solid rgba(255,255,255,.07);border-radius:5px;padding:1.4rem;margin-top:1rem;}
  .pedido:first-of-type{margin-top:0;}
  .pedido-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
  .pedido-id{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;}
  .pedido-data{font-size:.75rem;color:rgba(245,245,245,.4);margin-top:.2rem;}
  .pedido-total{font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.1rem;}

  .st-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .7rem;border-radius:2px;font-size:.66rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;}
  .st-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}
  .st-badge.pendente{background:rgba(251,191,36,.1);color:#fbbf24;}
  .st-badge.em_analise{background:rgba(96,165,250,.1);color:#60a5fa;}
  .st-badge.em_producao{background:rgba(167,139,250,.12);color:#a78bfa;}
  .st-badge.concluido{background:rgba(74,222,128,.1);color:#4ade80;}
  .st-badge.cancelado{background:rgba(224,27,36,.12);color:#ff6b6b;}

  .pedido-itens{list-style:none;display:flex;flex-direction:column;gap:.4rem;margin:.4rem 0 1.2rem;}
  .pedido-itens li{display:flex;justify-content:space-between;font-size:.85rem;color:rgba(245,245,245,.62);}
  .pedido-itens li span:first-child::before{content:'▪';color:var(--red);margin-right:.5rem;}

  .pedido-obs{font-size:.8rem;color:rgba(245,245,245,.45);font-style:italic;border-left:2px solid rgba(224,27,36,.4);padding-left:.8rem;margin-bottom:1.2rem;}

  /* TIMELINE DE ANDAMENTO */
  .track{display:flex;align-items:flex-start;gap:0;}
  .track-step{flex:1;text-align:center;position:relative;}
  .track-step::before{content:'';position:absolute;top:11px;left:-50%;width:100%;height:2px;background:rgba(255,255,255,.1);z-index:0;}
  .track-step:first-child::before{display:none;}
  .track-dot{width:24px;height:24px;border-radius:50%;border:2px solid rgba(255,255,255,.15);background:var(--black);margin:0 auto .5rem;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;font-size:.7rem;}
  .track-label{font-size:.62rem;letter-spacing:.08em;text-transform:uppercase;color:rgba(245,245,245,.3);line-height:1.3;}
  .track-step.done .track-dot{border-color:var(--red);color:var(--red);}
  .track-step.done::before{background:var(--red);}
  .track-step.current .track-dot{border-color:var(--red);background:var(--red);color:#fff;box-shadow:0 0 0 4px rgba(224,27,36,.18);}
  .track-step.done .track-label,.track-step.current .track-label{color:rgba(245,245,245,.75);}

  .track-cancel{display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:#ff6b6b;padding:.6rem 0;}

  @media(max-width:900px){
    .cart-layout{grid-template-columns:1fr;}
    .summary{position:static;}
  }
  @media(max-width:720px){
    .wrap{padding:7rem 1rem 4rem;}
    .card{padding:1.4rem;}
    .cart-item{grid-template-columns:1fr;gap:.8rem;}
    .ci-right{align-items:flex-start;text-align:left;}
    .sug-grid{grid-template-columns:1fr;}
    .track-label{font-size:.55rem;}
  }
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<div class="bg-grid"></div>

<?php require __DIR__ . "/navbar.php"; ?>

<div class="wrap">

  <div class="page-head">
    <span class="page-kicker">Área do cliente</span>
    <h1>Meu <em>carrinho</em></h1>
  </div>

  <!-- ═══════════ CARRINHO ═══════════ -->
  <div class="cart-layout <?= empty($itensCarrinho) ? 'is-empty' : '' ?>">
  <div class="card">
    <div class="card-title">
      Itens do carrinho
      <span class="count"><?= $qtdTotalItens ?> <?= $qtdTotalItens === 1 ? 'item' : 'itens' ?></span>
    </div>

    <?php if (empty($itensCarrinho)): ?>
      <div class="cart-empty">
        <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="opacity:.35;margin-bottom:1rem"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <p>Seu carrinho está vazio. Escolha um pacote para começar:</p>
        <div class="sug-grid">
          <?php foreach ($CATALOGO as $k => $c): ?>
            <div class="sug">
              <h4><?= htmlspecialchars($c['nome']) ?></h4>
              <div class="p"><?= $brl($c['preco']) ?></div>
              <form method="post" action="carrinho.php">
                <input type="hidden" name="acao" value="add">
                <input type="hidden" name="pacote" value="<?= htmlspecialchars($k) ?>">
                <button type="submit">Adicionar</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>

      <?php foreach ($itensCarrinho as $it): ?>
        <div class="cart-item">
          <div>
            <div class="ci-nome"><?= htmlspecialchars($it['nome']) ?></div>
            <div class="ci-desc"><?= htmlspecialchars($it['desc']) ?></div>
            <div class="ci-preco-unit"><?= $brl($it['preco']) ?> cada</div>
          </div>
          <form method="post" action="carrinho.php" class="stepper">
            <input type="hidden" name="acao" value="set_qtd">
            <input type="hidden" name="pacote" value="<?= htmlspecialchars($it['key']) ?>">
            <button type="submit" name="qtd" value="<?= $it['qtd'] - 1 ?>" aria-label="Diminuir">−</button>
            <span class="q"><?= $it['qtd'] ?></span>
            <button type="submit" name="qtd" value="<?= $it['qtd'] + 1 ?>" aria-label="Aumentar">+</button>
          </form>
          <div class="ci-right">
            <div class="ci-sub"><?= $brl($it['sub']) ?></div>
            <form method="post" action="carrinho.php">
              <input type="hidden" name="acao" value="remove">
              <input type="hidden" name="pacote" value="<?= htmlspecialchars($it['key']) ?>">
              <button type="submit" class="ci-remove">Remover</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <a href="index.php#pacotes" class="back-link">
        <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Continuar escolhendo pacotes
      </a>
    <?php endif; ?>
  </div>

  <?php if (!empty($itensCarrinho)): ?>
  <!-- ═══════════ RESUMO / FINALIZAR ═══════════ -->
  <aside class="card summary">
    <div class="card-title">Resumo do pedido</div>

    <ul class="sum-lines">
      <?php foreach ($itensCarrinho as $it): ?>
        <li>
          <span><?= htmlspecialchars($it['nome']) ?><?= $it['qtd'] > 1 ? ' ×' . $it['qtd'] : '' ?></span>
          <span><?= $brl($it['sub']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="cart-total-row" style="margin-bottom:1.2rem">
      <span class="lbl">Total</span>
      <span class="val"><?= $brl($totalCarrinho) ?></span>
    </div>

    <?php if (!$logado): ?>
      <div class="login-hint">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" fill="none" stroke-width="2" style="flex-shrink:0"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span>Entre na sua conta para finalizar o pedido e acompanhar o andamento.</span>
      </div>
      <a href="Login.php?redir=carrinho" class="btn btn-primary">Entrar para finalizar</a>
    <?php else: ?>
      <form method="post" action="carrinho.php" id="checkoutForm">
        <input type="hidden" name="acao" value="checkout">
        <label class="obs-label" for="obs">Observações (opcional)</label>
        <textarea class="obs-field" id="obs" name="observacoes" maxlength="2000" placeholder="Conte um pouco sobre o seu negócio, prazos, referências..."></textarea>
        <button type="submit" class="btn btn-primary" id="checkoutBtn">
          Finalizar pedido
          <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>
      <p class="sum-note">Nossa equipe entra em contato pelo e-mail cadastrado (<?= htmlspecialchars($user['email']) ?>) e você acompanha o andamento aqui, em “Meus pedidos”.</p>
    <?php endif; ?>

    <form method="post" action="carrinho.php"
          data-confirm="Todos os pacotes serão removidos do carrinho."
          data-confirm-titulo="Esvaziar o carrinho?" data-confirm-ok="Esvaziar">
      <input type="hidden" name="acao" value="limpar">
      <button type="submit" class="btn-link">Esvaziar carrinho</button>
    </form>
  </aside>
  <?php endif; ?>
  </div><!-- /cart-layout -->

  <!-- ═══════════ MEUS PEDIDOS ═══════════ -->
  <?php if ($logado): ?>
    <div class="card pedidos-card" id="pedidos">
      <div class="card-title">
        Meus pedidos
        <span class="count"><?= count($meusPedidos) ?> <?= count($meusPedidos) === 1 ? 'pedido' : 'pedidos' ?></span>
      </div>

      <?php if (!empty($erroTabela)): ?>
        <div class="cart-empty">
          <p>O histórico de pedidos ainda não está disponível.<br>
          <span style="font-size:.8rem;opacity:.7">(Rode a migração <code>DataBase/migrations/002_pedidos.sql</code> no banco.)</span></p>
        </div>
      <?php elseif (empty($meusPedidos)): ?>
        <div class="cart-empty">
          <p>Você ainda não fez nenhum pedido.</p>
        </div>
      <?php else: ?>
        <?php foreach ($meusPedidos as $p): ?>
          <?php
            $stInfo = $STATUS_INFO[$p['status']] ?? ['rotulo' => $p['status'], 'classe' => 'pendente'];
            $cancelado = $p['status'] === 'cancelado';
            $idxAtual  = array_search($p['status'], $FLUXO, true);
          ?>
          <div class="pedido">
            <div class="pedido-top">
              <div>
                <div class="pedido-id">Pedido #<?= (int)$p['id'] ?></div>
                <div class="pedido-data">Feito em <?= date('d/m/Y \à\s H:i', strtotime($p['created_at'])) ?></div>
              </div>
              <div style="text-align:right">
                <span class="st-badge <?= $stInfo['classe'] ?>"><?= htmlspecialchars($stInfo['rotulo']) ?></span>
                <div class="pedido-total" style="margin-top:.5rem"><?= $brl($p['total']) ?></div>
              </div>
            </div>

            <ul class="pedido-itens">
              <?php foreach ($p['itens'] as $it): ?>
                <li>
                  <span><?= htmlspecialchars($it['pacote_nome']) ?><?= $it['quantidade'] > 1 ? ' ×' . (int)$it['quantidade'] : '' ?></span>
                  <span><?= $brl($it['preco'] * $it['quantidade']) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <?php if (!empty($p['observacoes'])): ?>
              <div class="pedido-obs"><?= nl2br(htmlspecialchars($p['observacoes'])) ?></div>
            <?php endif; ?>

            <?php if ($cancelado): ?>
              <div class="track-cancel">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Este pedido foi cancelado. Se tiver dúvidas, fale com a gente pelo WhatsApp.
              </div>
            <?php else: ?>
              <div class="track">
                <?php foreach ($FLUXO as $i => $etapa): ?>
                  <?php
                    $cls = '';
                    if ($idxAtual !== false && $i < $idxAtual) $cls = 'done';
                    elseif ($i === $idxAtual)                  $cls = 'current';
                  ?>
                  <div class="track-step <?= $cls ?>">
                    <div class="track-dot"><?= $cls === 'done' ? '✓' : '' ?></div>
                    <div class="track-label"><?= htmlspecialchars($STATUS_INFO[$etapa]['rotulo']) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . "/popup.php"; ?>

<script>
  // ===== Mensagem de sessão (aparece uma vez) =====
  (function () {
    const f = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    if (!f) return;
    const nome = f.nome || '';
    switch (f.tipo) {
      case 'add':      nexusToast(nome + ' adicionado ao carrinho'); break;
      case 'removido': nexusToast(nome + ' removido do carrinho'); break;
      case 'limpo':    nexusToast('Carrinho esvaziado'); break;
      case 'pedido':
        nexusPopup({
          tipo: 'sucesso',
          titulo: 'Pedido #' + f.id + ' registrado!',
          texto: 'Recebemos o seu pedido. Nossa equipe entrará em contato pelo e-mail cadastrado — e você acompanha cada etapa em “Meus pedidos”.',
          primario:   { rotulo: 'Ver meus pedidos', aoClicar: () => document.getElementById('pedidos')?.scrollIntoView({ behavior: 'smooth' }) },
          secundario: { rotulo: 'Fechar' }
        });
        break;
      case 'erro_vazio':
        nexusPopup({ tipo: 'erro', titulo: 'Carrinho vazio', texto: 'Adicione ao menos um pacote antes de finalizar o pedido.' });
        break;
      case 'erro_falha':
        nexusPopup({ tipo: 'erro', titulo: 'Não foi possível registrar', texto: 'Seu carrinho foi mantido. Tente finalizar de novo em instantes.' });
        break;
    }
  })();

  // ===== Evita pedido duplicado (clique duplo / conexão lenta) =====
  (function () {
    const form = document.getElementById('checkoutForm'), btn = document.getElementById('checkoutBtn');
    if (!form || !btn) return;
    form.addEventListener('submit', () => { btn.disabled = true; btn.firstChild.textContent = 'Enviando pedido… '; });
    addEventListener('pageshow', e => { if (e.persisted) { btn.disabled = false; btn.firstChild.textContent = 'Finalizar pedido '; } });
  })();

  const cur = document.getElementById('cursor'), ring = document.getElementById('cursorRing');
  let mx=0,my=0,rx=0,ry=0;
  addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
  (function a(){rx+=(mx-rx)*.12;ry+=(my-ry)*.12;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(a);})();
  document.querySelectorAll('a,button,input,select,textarea').forEach(el=>{
    el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';});
    el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';});
  });
</script>
</body>
</html>
