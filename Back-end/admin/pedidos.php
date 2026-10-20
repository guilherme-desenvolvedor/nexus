<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

/* Garante que as tabelas existem — evita tela branca antes da migração 002 */
$tabelaOk = true;
try {
    $pdo->query("SELECT 1 FROM pedidos LIMIT 1");
} catch (Throwable $e) {
    $tabelaOk = false;
}

$STATUS = [
    'pendente'    => 'Pedido recebido',
    'em_analise'  => 'Em análise',
    'em_producao' => 'Em produção',
    'concluido'   => 'Concluído',
    'cancelado'   => 'Cancelado',
];
$PROXIMO = [
    'pendente'    => 'em_analise',
    'em_analise'  => 'em_producao',
    'em_producao' => 'concluido',
];

$filtro    = $_GET['status'] ?? 'todos';
$pagina    = max(1, intval($_GET['pagina'] ?? 1));
$porPagina = 12;
$offset    = ($pagina - 1) * $porPagina;

$pedidos = [];
$totalPags = 1;
$counters = ['pendente' => 0, 'em_analise' => 0, 'em_producao' => 0, 'concluido' => 0, 'cancelado' => 0, 'total' => 0, 'receita' => 0];

if ($tabelaOk) {
    $where = []; $params = [];
    if ($filtro !== 'todos' && isset($STATUS[$filtro])) {
        $where[] = "status = :status";
        $params[':status'] = $filtro;
    }
    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmtT = $pdo->prepare("SELECT COUNT(*) FROM pedidos $whereSQL");
    $stmtT->execute($params);
    $totalReg  = $stmtT->fetchColumn();
    $totalPags = max(1, ceil($totalReg / $porPagina));

    $stmt = $pdo->prepare("SELECT * FROM pedidos $whereSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($pedidos) {
        $ids = array_column($pedidos, 'id');
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $stmtI = $pdo->prepare("SELECT * FROM pedido_itens WHERE pedido_id IN ($in)");
        $stmtI->execute($ids);
        $porPedido = [];
        foreach ($stmtI->fetchAll(PDO::FETCH_ASSOC) as $it) {
            $porPedido[$it['pedido_id']][] = $it;
        }
        foreach ($pedidos as &$p) { $p['itens'] = $porPedido[$p['id']] ?? []; }
        unset($p);
    }

    $c = $pdo->query("
        SELECT
          SUM(status='pendente')    AS pendente,
          SUM(status='em_analise')  AS em_analise,
          SUM(status='em_producao') AS em_producao,
          SUM(status='concluido')   AS concluido,
          SUM(status='cancelado')   AS cancelado,
          COUNT(*)                  AS total,
          COALESCE(SUM(CASE WHEN status='concluido' THEN total ELSE 0 END),0) AS receita
        FROM pedidos
    ")->fetch(PDO::FETCH_ASSOC);
    if ($c) $counters = array_map('intval', $c) + ['receita' => $c['receita']];
    $counters['receita'] = $c['receita'] ?? 0;
}

$mensagensCount = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
$projetosAtivos = $pdo->query("SELECT COUNT(*) FROM projetos WHERE status='ativo'")->fetchColumn();
$emAberto = $counters['pendente'] + $counters['em_analise'] + $counters['em_producao'];

$brl = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Pedidos</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.filter-tabs{display:flex;background:rgba(255,255,255,.04);border-radius:10px;padding:3px;gap:2px;flex-wrap:wrap;margin-bottom:1.2rem;}
.filter-tab{padding:.4rem .9rem;border-radius:8px;font-size:.78rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .2s;text-decoration:none;display:flex;align-items:center;gap:.35rem;}
.filter-tab.active,.filter-tab:hover{background:rgba(224,27,36,.15);color:var(--red);}
.count-pill{background:rgba(224,27,36,.25);color:var(--red);border-radius:99px;padding:0 .45rem;font-size:.65rem;}
.filter-tab.active .count-pill{background:var(--red);color:#fff;}
.pedidos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1rem;}
.pedido-card{background:var(--card-bg,#111);border:1px solid var(--border);border-radius:14px;padding:1.3rem;transition:border-color .2s;}
.pedido-card:hover{border-color:rgba(224,27,36,.25);}
.pc-top{display:flex;justify-content:space-between;align-items:flex-start;gap:.8rem;margin-bottom:.7rem;}
.pc-id{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;color:var(--text);}
.pc-cliente{font-size:.8rem;color:var(--text-dim);margin-top:.15rem;}
.pc-cliente a{color:var(--text-dim);text-decoration:none;}
.pc-cliente a:hover{color:var(--red);}
.pc-total{font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.1rem;text-align:right;}
.pc-date{font-size:.7rem;color:var(--text-dim);text-align:right;margin-top:.2rem;}
.st-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.22rem .6rem;border-radius:6px;font-size:.66rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;}
.st-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;}
.st-badge.pendente{background:rgba(251,191,36,.12);color:#fbbf24;}
.st-badge.em_analise{background:rgba(96,165,250,.12);color:#60a5fa;}
.st-badge.em_producao{background:rgba(167,139,250,.14);color:#a78bfa;}
.st-badge.concluido{background:rgba(74,222,128,.12);color:#4ade80;}
.st-badge.cancelado{background:rgba(224,27,36,.14);color:#ff6b6b;}
.pc-itens{list-style:none;margin:.8rem 0;padding:.8rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);display:flex;flex-direction:column;gap:.35rem;}
.pc-itens li{display:flex;justify-content:space-between;font-size:.82rem;color:var(--text-mid);}
.pc-obs{font-size:.78rem;color:var(--text-dim);font-style:italic;margin:.6rem 0;border-left:2px solid rgba(224,27,36,.4);padding-left:.7rem;}
.pc-actions{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.9rem;}
.pc-actions button{padding:.4rem .85rem;border-radius:7px;font-size:.73rem;font-weight:700;cursor:pointer;border:1px solid transparent;transition:all .2s;}
.btn-adv{background:rgba(16,185,129,.12);color:#10b981;border-color:rgba(16,185,129,.25);}
.btn-adv:hover{background:rgba(16,185,129,.22);}
.btn-cancel{background:rgba(224,27,36,.1);color:var(--red);border-color:rgba(224,27,36,.2);}
.btn-cancel:hover{background:rgba(224,27,36,.2);}
.btn-reopen{background:rgba(255,255,255,.05);color:var(--text-dim);border-color:rgba(255,255,255,.08);}
.btn-reopen:hover{background:rgba(255,255,255,.1);}
.pagination{display:flex;justify-content:center;gap:.4rem;margin-top:1.5rem;}
.page-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg,#111);color:var(--text-dim);font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;}
.page-btn:hover,.page-btn.active{background:var(--red);color:#fff;border-color:var(--red);}
.aviso{background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.3);color:#fbbf24;border-radius:12px;padding:1.4rem;font-size:.9rem;line-height:1.6;}
.aviso code{background:rgba(255,255,255,.08);padding:.1rem .4rem;border-radius:4px;}
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
    <div class="logo-text">ne<span>x</span>us</div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>
    <a href="dashboard.php" class="nav-item"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Visão Geral</span></a>
    <a href="projetos.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 1 3 3v1a3 3 0 0 1-6 0V5a3 3 0 0 1 3-3z"/><path d="M19 9a7 7 0 1 1-14 0"/><path d="M12 12v9M8 21h8"/></svg><span>Projetos</span><span class="nav-badge"><?= $projetosAtivos ?></span></a>
    <a href="pedidos.php" class="nav-item active"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span>Pedidos</span><?php if ($emAberto > 0): ?><span class="nav-badge"><?= $emAberto ?></span><?php endif; ?></a>
    <a href="analise.php" class="nav-item"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Análise</span></a>
    <a href="clientes.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Clientes</span></a>
    <a href="mensagens.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>Mensagens</span><span class="nav-badge"><?= $mensagensCount ?></span></a>
    <div class="sidebar-section-label" style="margin-top:.6rem">Financeiro</div>
    <a href="faturamento.php" class="nav-item"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>Faturamento</span></a>
    <a href="pagamentos.php" class="nav-item"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg><span>Pagamentos</span></a>
    <div class="sidebar-section-label" style="margin-top:.6rem">Sistema</div>
    <a href="configuracoes.php" class="nav-item"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg><span>Configurações</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card"><div class="user-avatar">NX</div><div><div class="user-name">Nexus</div><div class="user-role">Admin · Nexus</div></div></div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Pe<em>didos</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Pedidos</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">

    <?php if (!$tabelaOk): ?>
      <div class="aviso">
        <strong>As tabelas de pedidos ainda não foram criadas.</strong><br>
        Importe o arquivo <code>DataBase/migrations/002_pedidos.sql</code> no phpMyAdmin
        (banco <code>nexus_db</code> → aba SQL) para ativar o carrinho de compras do site.
      </div>
    <?php else: ?>

    <div class="kpi-grid" style="margin-bottom:1.4rem">
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(251,191,36,.12)"><svg viewBox="0 0 24 24" style="stroke:#fbbf24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="kpi-label">Em aberto</div>
        <div class="kpi-value" style="color:#fbbf24"><?= $emAberto ?></div>
        <div style="margin-top:.4rem"><span class="kpi-period">aguardando andamento</span></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(167,139,250,.14)"><svg viewBox="0 0 24 24" style="stroke:#a78bfa"><path d="M12 2a3 3 0 0 1 3 3v1a3 3 0 0 1-6 0V5a3 3 0 0 1 3-3z"/><path d="M19 9a7 7 0 1 1-14 0"/><path d="M12 12v9M8 21h8"/></svg></div>
        <div class="kpi-label">Em produção</div>
        <div class="kpi-value" style="color:#a78bfa"><?= $counters['em_producao'] ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(74,222,128,.12)"><svg viewBox="0 0 24 24" style="stroke:#4ade80"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-label">Concluídos</div>
        <div class="kpi-value" style="color:#4ade80"><?= $counters['concluido'] ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-label">Receita concluída</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format((float)$counters['receita'], 0, ',', '.') ?></div>
      </div>
    </div>

    <div class="filter-tabs">
      <?php
        $tabs = ['todos' => 'Todos'] + $STATUS;
        foreach ($tabs as $key => $rot):
          $n = $key === 'todos' ? $counters['total'] : ($counters[$key] ?? 0);
      ?>
        <a href="?status=<?= $key ?>" class="filter-tab <?= $filtro === $key ? 'active' : '' ?>">
          <?= htmlspecialchars($rot) ?>
          <?php if ($n > 0): ?><span class="count-pill"><?= $n ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="pedidos-grid reveal">
      <?php if (empty($pedidos)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;opacity:.4">
          <?= $filtro !== 'todos' ? 'Nenhum pedido nesta categoria.' : 'Nenhum pedido ainda. Quando um cliente finalizar o carrinho no site, ele aparece aqui.' ?>
        </div>
      <?php else: ?>
        <?php foreach ($pedidos as $p): ?>
        <div class="pedido-card reveal">
          <div class="pc-top">
            <div>
              <div class="pc-id">Pedido #<?= (int)$p['id'] ?></div>
              <div class="pc-cliente">
                <?= htmlspecialchars($p['usuario_nome']) ?><br>
                <a href="mailto:<?= htmlspecialchars($p['usuario_email']) ?>"><?= htmlspecialchars($p['usuario_email']) ?></a>
              </div>
            </div>
            <div>
              <div class="pc-total"><?= $brl($p['total']) ?></div>
              <div class="pc-date"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
            </div>
          </div>

          <span class="st-badge <?= $p['status'] ?>"><?= htmlspecialchars($STATUS[$p['status']] ?? $p['status']) ?></span>

          <ul class="pc-itens">
            <?php foreach ($p['itens'] as $it): ?>
              <li>
                <span><?= htmlspecialchars($it['pacote_nome']) ?><?= $it['quantidade'] > 1 ? ' ×' . (int)$it['quantidade'] : '' ?></span>
                <span><?= $brl($it['preco'] * $it['quantidade']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>

          <?php if (!empty($p['observacoes'])): ?>
            <div class="pc-obs"><?= nl2br(htmlspecialchars($p['observacoes'])) ?></div>
          <?php endif; ?>

          <div class="pc-actions">
            <form method="post" action="pedidos_action.php?status=<?= htmlspecialchars($filtro) ?>" style="display:contents">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="acao" value="status">
              <?php if (isset($PROXIMO[$p['status']])): ?>
                <button type="submit" name="novo" value="<?= $PROXIMO[$p['status']] ?>" class="btn-adv">
                  → <?= htmlspecialchars($STATUS[$PROXIMO[$p['status']]]) ?>
                </button>
              <?php endif; ?>
              <?php if ($p['status'] !== 'cancelado' && $p['status'] !== 'concluido'): ?>
                <button type="submit" name="novo" value="cancelado" class="btn-cancel">Cancelar</button>
              <?php endif; ?>
              <?php if ($p['status'] === 'cancelado' || $p['status'] === 'concluido'): ?>
                <button type="submit" name="novo" value="em_analise" class="btn-reopen">Reabrir</button>
              <?php endif; ?>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPags > 1): ?>
    <div class="pagination">
      <?php if ($pagina > 1): ?><a href="?pagina=<?= $pagina - 1 ?>&status=<?= $filtro ?>" class="page-btn">‹</a><?php endif; ?>
      <?php for ($i = max(1, $pagina - 2); $i <= min($totalPags, $pagina + 2); $i++): ?>
        <a href="?pagina=<?= $i ?>&status=<?= $filtro ?>" class="page-btn <?= $i == $pagina ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($pagina < $totalPags): ?><a href="?pagina=<?= $pagina + 1 ?>&status=<?= $filtro ?>" class="page-btn">›</a><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; /* tabelaOk */ ?>
  </div>
</div>

<script>
const cur=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
(function anim(){rx+=(mx-rx)*.1;ry+=(my-ry)*.1;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,input').forEach(el=>{
  el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';ring.style.borderColor='var(--red)';});
  el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';ring.style.borderColor='rgba(224,27,36,.45)';});
});
const obs=new IntersectionObserver(entries=>{entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*50);obs.unobserve(e.target);}});},{threshold:.05});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>
</body>
</html>
