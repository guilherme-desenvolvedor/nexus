<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

// Ação de atualizar status
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['acao'],$_POST['id'])) {
    $map = ['aprovar'=>'aprovado','recusar'=>'cancelado','pendente'=>'pendente'];
    if (isset($map[$_POST['acao']])) {
        $pdo->prepare("UPDATE payments SET status=? WHERE id=?")->execute([$map[$_POST['acao']],(int)$_POST['id']]);
    }
    header("Location: pagamentos.php?status=".($_GET['status']??'pendente'));
    exit;
}

$filtroStatus = $_GET['status'] ?? 'pendente';
$pagina = max(1,intval($_GET['pagina']??1));
$porPagina = 12;
$offset = ($pagina-1)*$porPagina;

$where=[]; $params=[];
if ($filtroStatus!=='todos'){$where[]="status=:status";$params[':status']=$filtroStatus;}
$whereSQL=$where?'WHERE '.implode(' AND ',$where):'';

$total=$pdo->prepare("SELECT COUNT(*) FROM payments $whereSQL");
$total->execute($params);
$totalReg=$total->fetchColumn();
$totalPags=ceil($totalReg/$porPagina);

$stmt=$pdo->prepare("SELECT * FROM payments $whereSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$porPagina,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$pagamentos=$stmt->fetchAll(PDO::FETCH_ASSOC);

$counters=$pdo->query("
    SELECT
        SUM(CASE WHEN status='aprovado'  THEN 1 ELSE 0 END) as aprovado,
        SUM(CASE WHEN status='pendente'  THEN 1 ELSE 0 END) as pendente,
        SUM(CASE WHEN status='cancelado' THEN 1 ELSE 0 END) as cancelado,
        COALESCE(SUM(CASE WHEN status='aprovado'  THEN amount ELSE 0 END),0) as val_aprovado,
        COALESCE(SUM(CASE WHEN status='pendente'  THEN amount ELSE 0 END),0) as val_pendente
    FROM payments
")->fetch(PDO::FETCH_ASSOC);

$mensagensCount=$pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
$projetosAtivos=$pdo->query("SELECT COUNT(*) FROM projetos WHERE status='ativo'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Pagamentos</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.filter-tabs{display:flex;background:rgba(255,255,255,.04);border-radius:10px;padding:3px;gap:2px;}
.filter-tab{padding:.4rem .9rem;border-radius:8px;font-size:.78rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .2s;text-decoration:none;display:flex;align-items:center;gap:.35rem;}
.filter-tab.active,.filter-tab:hover{background:rgba(224,27,36,.15);color:var(--red);}
.count-pill{background:rgba(224,27,36,.25);color:var(--red);border-radius:99px;padding:0 .45rem;font-size:.65rem;}
.filter-tab.active .count-pill{background:var(--red);color:#fff;}
.payments-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:.8rem;}
.payment-card{background:var(--card-bg);border:1px solid var(--border);border-radius:14px;padding:1.2rem;transition:border-color .2s;}
.payment-card:hover{border-color:rgba(224,27,36,.2);}
.payment-card-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.7rem;}
.payment-name{font-weight:600;font-size:.88rem;color:var(--text);}
.payment-email{font-size:.73rem;color:var(--text-dim);margin-top:.1rem;}
.payment-tid{font-size:.66rem;color:var(--text-dim);font-family:monospace;margin-top:.15rem;}
.payment-amount{font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.15rem;}
.payment-amount.aprovado{color:#10b981;}
.payment-amount.pendente{color:#f59e0b;}
.payment-amount.cancelado{color:var(--red);opacity:.6;}
.payment-meta{display:flex;gap:.6rem;margin:.6rem 0;font-size:.75rem;color:var(--text-dim);flex-wrap:wrap;}
.method-tag{background:rgba(255,255,255,.06);border-radius:6px;padding:.15rem .5rem;font-size:.7rem;}
.payment-actions{display:flex;gap:.4rem;flex-wrap:wrap;}
.btn-green{background:rgba(16,185,129,.12);color:#10b981;border:1px solid rgba(16,185,129,.25);padding:.38rem .9rem;border-radius:7px;font-size:.74rem;font-weight:700;cursor:pointer;transition:all .2s;}
.btn-green:hover{background:rgba(16,185,129,.22);}
.btn-danger{background:rgba(224,27,36,.1);color:var(--red);border:1px solid rgba(224,27,36,.2);padding:.38rem .9rem;border-radius:7px;font-size:.74rem;font-weight:700;cursor:pointer;transition:all .2s;}
.btn-danger:hover{background:rgba(224,27,36,.2);}
.btn-ghost{background:rgba(255,255,255,.04);color:var(--text-dim);border:1px solid rgba(255,255,255,.07);padding:.38rem .9rem;border-radius:7px;font-size:.74rem;font-weight:600;cursor:pointer;transition:all .2s;}
.btn-ghost:hover{background:rgba(255,255,255,.09);}
.pagination{display:flex;justify-content:center;gap:.4rem;margin-top:1.5rem;}
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
    <a href="faturamento.php" class="nav-item"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span>Faturamento</span></a>
    <a href="pagamentos.php" class="nav-item active"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg><span>Pagamentos</span></a>
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
      <h1>Paga<em>mentos</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Pagamentos</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">
    <div class="kpi-grid" style="margin-bottom:1.4rem">
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(16,185,129,.12)"><svg viewBox="0 0 24 24" style="stroke:#10b981"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-label">Aprovados</div>
        <div class="kpi-value" style="color:#10b981"><?= $counters['aprovado'] ?></div>
        <div style="margin-top:.4rem"><span class="kpi-period">R$ <?= number_format($counters['val_aprovado'],0,',','.') ?></span></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(245,158,11,.12)"><svg viewBox="0 0 24 24" style="stroke:#f59e0b"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="kpi-label">Pendentes</div>
        <div class="kpi-value" style="color:#f59e0b"><?= $counters['pendente'] ?></div>
        <div style="margin-top:.4rem"><span class="kpi-period">R$ <?= number_format($counters['val_pendente'],0,',','.') ?></span></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div class="kpi-label">Cancelados</div>
        <div class="kpi-value"><?= $counters['cancelado'] ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
        <div class="kpi-label">Total</div>
        <div class="kpi-value"><?= $counters['aprovado']+$counters['pendente']+$counters['cancelado'] ?></div>
      </div>
    </div>

    <div style="margin-bottom:1rem">
      <div class="filter-tabs">
        <a href="?status=todos" class="filter-tab <?=$filtroStatus==='todos'?'active':''?>">Todos</a>
        <a href="?status=pendente" class="filter-tab <?=$filtroStatus==='pendente'?'active':''?>">
          Pendentes <?php if($counters['pendente']>0):?><span class="count-pill"><?=$counters['pendente']?></span><?php endif;?>
        </a>
        <a href="?status=aprovado" class="filter-tab <?=$filtroStatus==='aprovado'?'active':''?>">Aprovados</a>
        <a href="?status=cancelado" class="filter-tab <?=$filtroStatus==='cancelado'?'active':''?>">Cancelados</a>
      </div>
    </div>

    <div class="payments-grid reveal">
      <?php if (empty($pagamentos)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;opacity:.4">
          <?= $filtroStatus==='pendente' ? 'Nenhum pagamento pendente. 🎉' : 'Nenhum pagamento nesta categoria.' ?>
        </div>
      <?php else: ?>
        <?php foreach ($pagamentos as $p): ?>
        <div class="payment-card reveal">
          <div class="payment-card-top">
            <div>
              <div class="payment-name"><?= htmlspecialchars($p['name'] ?? 'Sem nome') ?></div>
              <?php if($p['email']): ?><div class="payment-email"><?= htmlspecialchars($p['email']) ?></div><?php endif; ?>
              <?php if($p['transaction_id']): ?><div class="payment-tid"><?= htmlspecialchars($p['transaction_id']) ?></div><?php endif; ?>
            </div>
            <span class="status-badge <?=$p['status']?>"><?= ucfirst($p['status']) ?></span>
          </div>
          <div class="payment-amount <?=$p['status']?>">R$ <?= number_format($p['amount'],2,',','.') ?></div>
          <div class="payment-meta">
            <?php if($p['method']): ?><span class="method-tag"><?= htmlspecialchars($p['method']) ?></span><?php endif; ?>
            <span><?= date('d/m/Y H:i',strtotime($p['created_at'])) ?></span>
          </div>
          <div class="payment-actions">
            <?php if ($p['status']==='pendente'): ?>
              <form method="POST" style="display:contents">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <button type="submit" name="acao" value="aprovar" class="btn-green">✓ Aprovar</button>
                <button type="submit" name="acao" value="recusar" class="btn-danger">✗ Recusar</button>
              </form>
            <?php elseif ($p['status']==='aprovado'): ?>
              <form method="POST" style="display:contents">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <button type="submit" name="acao" value="pendente" class="btn-ghost">↩ Estornar</button>
              </form>
            <?php else: ?>
              <form method="POST" style="display:contents">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <button type="submit" name="acao" value="pendente" class="btn-ghost">↩ Reativar</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPags>1): ?>
    <div class="pagination">
      <?php if($pagina>1):?><a href="?pagina=<?=$pagina-1?>&status=<?=$filtroStatus?>" class="page-btn">‹</a><?php endif;?>
      <?php for($i=max(1,$pagina-2);$i<=min($totalPags,$pagina+2);$i++):?>
        <a href="?pagina=<?=$i?>&status=<?=$filtroStatus?>" class="page-btn <?=$i==$pagina?'active':''?>"><?=$i?></a>
      <?php endfor;?>
      <?php if($pagina<$totalPags):?><a href="?pagina=<?=$pagina+1?>&status=<?=$filtroStatus?>" class="page-btn">›</a><?php endif;?>
    </div>
    <?php endif; ?>
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