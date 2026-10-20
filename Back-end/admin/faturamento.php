<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

$filtroStatus = $_GET['status'] ?? 'todos';
$pagina    = max(1, intval($_GET['pagina'] ?? 1));
$porPagina = 12;
$offset    = ($pagina - 1) * $porPagina;

$where = []; $params = [];
if ($filtroStatus !== 'todos') { $where[] = "status = :status"; $params[':status'] = $filtroStatus; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM payments $whereSQL");
$stmtTotal->execute($params);
$totalReg  = $stmtTotal->fetchColumn();
$totalPags = ceil($totalReg / $porPagina);

$stmt = $pdo->prepare("SELECT * FROM payments $whereSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$porPagina,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$m = $pdo->query("
    SELECT
        COALESCE(SUM(amount),0) as total,
        COALESCE(SUM(CASE WHEN status='aprovado'  THEN amount ELSE 0 END),0) as aprovado,
        COALESCE(SUM(CASE WHEN status='pendente'  THEN amount ELSE 0 END),0) as pendente,
        COALESCE(SUM(CASE WHEN status='cancelado' THEN amount ELSE 0 END),0) as cancelado,
        COUNT(*) as qtd_total,
        SUM(CASE WHEN status='aprovado'  THEN 1 ELSE 0 END) as qtd_aprovado,
        SUM(CASE WHEN status='pendente'  THEN 1 ELSE 0 END) as qtd_pendente,
        COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) THEN amount ELSE 0 END),0) as mes_atual,
        COALESCE(SUM(CASE WHEN YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1) THEN amount ELSE 0 END),0) as semana_atual
    FROM payments
")->fetch(PDO::FETCH_ASSOC);

// Receita mensal últimos 6 meses
$receitaMensal = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%b/%y') as label,
           COALESCE(SUM(amount),0) as total
    FROM payments WHERE status='aprovado'
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY DATE_FORMAT(created_at,'%Y-%m') ASC
")->fetchAll(PDO::FETCH_ASSOC);

$mensagensCount = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
$projetosAtivos = $pdo->query("SELECT COUNT(*) FROM projetos WHERE status='ativo'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Faturamento</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.btn-primary{background:var(--red);color:#fff;border:none;padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:opacity .2s;}
.btn-primary:hover{opacity:.85;}
.btn-secondary{background:rgba(255,255,255,.05);color:var(--text);border:1px solid rgba(255,255,255,.08);padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:background .2s;}
.btn-secondary:hover{background:rgba(255,255,255,.09);}
.filter-tabs{display:flex;background:rgba(255,255,255,.04);border-radius:10px;padding:3px;gap:2px;}
.filter-tab{padding:.4rem .9rem;border-radius:8px;font-size:.78rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .2s;text-decoration:none;}
.filter-tab.active,.filter-tab:hover{background:rgba(224,27,36,.15);color:var(--red);}
.fin-summary{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:1.4rem;}
.fin-summary-title{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:1.2rem;}
.fin-summary-item{display:flex;justify-content:space-between;align-items:center;padding:.65rem 0;border-bottom:1px solid rgba(255,255,255,.04);}
.fin-summary-item:last-child{border-bottom:none;}
.fin-summary-label{font-size:.8rem;color:var(--text-dim);}
.fin-summary-val{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.92rem;}
.bar-chart{display:flex;align-items:flex-end;gap:.5rem;height:100px;padding:.5rem 0;margin-top:.5rem;}
.bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;}
.bar{width:100%;background:var(--red);border-radius:4px 4px 0 0;min-height:4px;position:relative;}
.bar::after{content:attr(data-val);position:absolute;top:-1.4rem;left:50%;transform:translateX(-50%);font-size:.6rem;color:var(--text-dim);white-space:nowrap;}
.bar-label{font-size:.63rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.04em;}
.fin-table-card{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
table{width:100%;border-collapse:collapse;}
thead tr{border-bottom:1px solid var(--border);}
thead th{padding:.75rem 1.2rem;text-align:left;font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);}
tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;}
tbody tr:hover{background:rgba(255,255,255,.02);}
tbody tr:last-child{border-bottom:none;}
tbody td{padding:.85rem 1.2rem;font-size:.84rem;}
.amount-aprovado{color:#10b981;font-family:'MuseoModerno',sans-serif;font-weight:700;}
.amount-pendente{color:#f59e0b;font-family:'MuseoModerno',sans-serif;font-weight:700;}
.amount-cancelado{color:var(--red);opacity:.6;font-family:'MuseoModerno',sans-serif;font-weight:700;text-decoration:line-through;}
.method-tag{display:inline-block;background:rgba(255,255,255,.06);border-radius:6px;padding:.15rem .5rem;font-size:.7rem;color:var(--text-dim);}
.pagination{display:flex;justify-content:center;gap:.4rem;padding:1rem;}
.page-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-dim);font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;}
.page-btn:hover,.page-btn.active{background:var(--red);color:#fff;border-color:var(--red);}
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
    <a href="pedidos.php" class="nav-item"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span>Pedidos</span></a>
    <a href="analise.php" class="nav-item"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Análise</span></a>
    <a href="clientes.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Clientes</span></a>
    <a href="mensagens.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>Mensagens</span><span class="nav-badge"><?= $mensagensCount ?></span></a>
    <div class="sidebar-section-label" style="margin-top:.6rem">Financeiro</div>
    <a href="faturamento.php" class="nav-item active"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>Faturamento</span></a>
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
      <h1>Fatura<em>mento</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Faturamento</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">
    <div class="kpi-grid" style="margin-bottom:1.4rem">
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-label">Receita Aprovada</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format($m['aprovado'],0,',','.') ?></div>
        <div style="margin-top:.4rem"><span class="kpi-period"><?= $m['qtd_aprovado'] ?> transações</span></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(245,158,11,.12)"><svg viewBox="0 0 24 24" style="stroke:#f59e0b"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="kpi-label">Pendente</div>
        <div class="kpi-value" style="color:#f59e0b"><sup>R$</sup><?= number_format($m['pendente'],0,',','.') ?></div>
        <div style="margin-top:.4rem"><span class="kpi-period"><?= $m['qtd_pendente'] ?> aguardando</span></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(16,185,129,.12)"><svg viewBox="0 0 24 24" style="stroke:#10b981"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="kpi-label">Este Mês</div>
        <div class="kpi-value" style="color:#10b981"><sup>R$</sup><?= number_format($m['mes_atual'],0,',','.') ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        <div class="kpi-label">Esta Semana</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format($m['semana_atual'],0,',','.') ?></div>
      </div>
    </div>

    <!-- Gráfico + resumo -->
    <div style="display:grid;grid-template-columns:1fr 260px;gap:1rem;margin-bottom:1.4rem">
      <div class="chart-card reveal">
        <div class="chart-header">
          <div><div class="chart-title">Receita Mensal</div><div class="chart-sub">Últimos 6 meses · aprovados</div></div>
        </div>
        <?php $maxVal = max(array_column($receitaMensal,'total') ?: [1]); ?>
        <div class="bar-chart">
          <?php if (empty($receitaMensal)): ?>
            <div style="flex:1;display:flex;align-items:center;justify-content:center;opacity:.3;font-size:.85rem">Sem dados ainda</div>
          <?php else: ?>
            <?php foreach ($receitaMensal as $rm):
              $pct = $maxVal > 0 ? ($rm['total']/$maxVal)*88 : 4;
            ?>
            <div class="bar-wrap">
              <div class="bar" style="height:<?= round($pct) ?>px" data-val="R$<?= number_format($rm['total']/1000,0)?>k"></div>
              <div class="bar-label"><?= htmlspecialchars($rm['label']) ?></div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="fin-summary reveal">
        <div class="fin-summary-title">Resumo</div>
        <div class="fin-summary-item"><span class="fin-summary-label" style="color:#10b981">✓ Aprovado</span><span class="fin-summary-val" style="color:#10b981">R$ <?= number_format($m['aprovado'],0,',','.') ?></span></div>
        <div class="fin-summary-item"><span class="fin-summary-label" style="color:#f59e0b">⏳ Pendente</span><span class="fin-summary-val" style="color:#f59e0b">R$ <?= number_format($m['pendente'],0,',','.') ?></span></div>
        <div class="fin-summary-item"><span class="fin-summary-label" style="color:var(--red)">✗ Cancelado</span><span class="fin-summary-val" style="color:var(--red)">R$ <?= number_format($m['cancelado'],0,',','.') ?></span></div>
        <div class="fin-summary-item"><span class="fin-summary-label">Total registros</span><span class="fin-summary-val"><?= $m['qtd_total'] ?></span></div>
      </div>
    </div>

    <!-- Tabela -->
    <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:1rem">
      <div class="filter-tabs">
        <a href="?status=todos" class="filter-tab <?= $filtroStatus==='todos'?'active':'' ?>">Todos</a>
        <a href="?status=aprovado" class="filter-tab <?= $filtroStatus==='aprovado'?'active':'' ?>">Aprovados</a>
        <a href="?status=pendente" class="filter-tab <?= $filtroStatus==='pendente'?'active':'' ?>">Pendentes</a>
        <a href="?status=cancelado" class="filter-tab <?= $filtroStatus==='cancelado'?'active':'' ?>">Cancelados</a>
      </div>
    </div>

    <div class="fin-table-card reveal">
      <table>
        <thead>
          <tr>
            <th>#ID</th>
            <th>Cliente / Email</th>
            <th>Método</th>
            <th>Status</th>
            <th>Valor</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pagamentos)): ?>
            <tr><td colspan="6" style="text-align:center;padding:3rem;opacity:.4">Nenhum registro<?= $filtroStatus!=='todos'?' nesta categoria':'' ?>.</td></tr>
          <?php else: ?>
            <?php foreach ($pagamentos as $p): ?>
            <tr>
              <td style="color:var(--text-dim);font-size:.75rem">#<?= $p['id'] ?></td>
              <td>
                <div style="font-weight:600"><?= htmlspecialchars($p['name'] ?? '—') ?></div>
                <?php if ($p['email']): ?><div style="font-size:.74rem;color:var(--text-dim)"><?= htmlspecialchars($p['email']) ?></div><?php endif; ?>
                <?php if ($p['transaction_id']): ?><div style="font-size:.66rem;color:var(--text-dim);font-family:monospace"><?= htmlspecialchars($p['transaction_id']) ?></div><?php endif; ?>
              </td>
              <td><?= $p['method'] ? '<span class="method-tag">'.htmlspecialchars($p['method']).'</span>' : '—' ?></td>
              <td><span class="status-badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
              <td class="amount-<?= $p['status'] ?>">R$ <?= number_format($p['amount'],2,',','.') ?></td>
              <td style="color:var(--text-dim);font-size:.78rem"><?= date('d/m/Y',strtotime($p['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <?php if ($totalPags > 1): ?>
      <div class="pagination">
        <?php if ($pagina>1): ?><a href="?pagina=<?=$pagina-1?>&status=<?=$filtroStatus?>" class="page-btn">‹</a><?php endif; ?>
        <?php for($i=max(1,$pagina-2);$i<=min($totalPags,$pagina+2);$i++): ?>
          <a href="?pagina=<?=$i?>&status=<?=$filtroStatus?>" class="page-btn <?=$i==$pagina?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
        <?php if ($pagina<$totalPags): ?><a href="?pagina=<?=$pagina+1?>&status=<?=$filtroStatus?>" class="page-btn">›</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
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
const obs=new IntersectionObserver(entries=>{entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*60);obs.unobserve(e.target);}});},{threshold:.05});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>
</body>
</html>