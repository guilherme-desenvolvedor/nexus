<?php
// ============================================================
// mensagens.php — usa contatos: nome, email, pacote, mensagem,
//                               data_envio, created_at
// ============================================================
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

$pagina    = max(1, intval($_GET['pagina'] ?? 1));
$porPagina = 15;
$offset    = ($pagina - 1) * $porPagina;
$busca     = $_GET['busca'] ?? '';

$where = []; $params = [];
if ($busca) {
    $where[] = "(nome LIKE :busca OR email LIKE :busca OR mensagem LIKE :busca OR pacote LIKE :busca)";
    $params[':busca'] = "%$busca%";
}
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM contatos $whereSQL");
$total->execute($params);
$totalReg  = $total->fetchColumn();
$totalPags = ceil($totalReg / $porPagina);

$stmt = $pdo->prepare("SELECT * FROM contatos $whereSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$porPagina,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalGeral = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
$hoje       = $pdo->query("SELECT COUNT(*) FROM contatos WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$semana     = $pdo->query("SELECT COUNT(*) FROM contatos WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Mensagens</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.search-filter{flex:1;max-width:320px;position:relative;}
.search-filter svg{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--text-dim);stroke-width:2;fill:none;}
.search-filter input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.5rem .9rem .5rem 2.2rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.83rem;outline:none;transition:border-color .2s;box-sizing:border-box;}
.search-filter input:focus{border-color:rgba(224,27,36,.4);}
.msg-list{display:flex;flex-direction:column;gap:.6rem;}
.msg-item{background:var(--card-bg);border:1px solid var(--border);border-radius:14px;padding:1.2rem 1.4rem;display:grid;grid-template-columns:auto 1fr auto;gap:1.2rem;align-items:start;transition:border-color .2s,transform .15s;cursor:pointer;}
.msg-item:hover{border-color:rgba(224,27,36,.25);transform:translateX(3px);}
.msg-avatar{width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,rgba(224,27,36,.3),rgba(224,27,36,.1));display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.9rem;color:var(--red);flex-shrink:0;border:1px solid rgba(224,27,36,.2);}
.msg-remetente{font-weight:600;font-size:.88rem;color:var(--text);margin-bottom:.1rem;}
.msg-email{font-size:.72rem;color:var(--text-dim);margin-bottom:.3rem;}
.msg-pacote{display:inline-block;background:rgba(224,27,36,.08);color:var(--red);border:1px solid rgba(224,27,36,.15);border-radius:99px;padding:.1rem .5rem;font-size:.63rem;font-weight:700;margin-bottom:.4rem;}
.msg-preview{font-size:.82rem;color:var(--text-dim);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.msg-time{font-size:.72rem;color:var(--text-dim);text-align:right;white-space:nowrap;}
.msg-btn{display:block;margin-top:.4rem;padding:.3rem .7rem;border-radius:6px;border:1px solid rgba(224,27,36,.25);background:rgba(224,27,36,.08);color:var(--red);font-size:.67rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;transition:all .2s;text-align:center;}
.msg-btn:hover{background:rgba(224,27,36,.18);}
.pagination{display:flex;justify-content:center;gap:.4rem;margin-top:1.5rem;}
.page-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-dim);font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;}
.page-btn:hover,.page-btn.active{background:var(--red);color:#fff;border-color:var(--red);}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:#111;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2rem;width:100%;max-width:540px;transform:scale(.96) translateY(10px);transition:transform .3s;max-height:85vh;overflow-y:auto;}
.modal-overlay.open .modal{transform:scale(1) translateY(0);}
.btn-primary{background:var(--red);color:#fff;border:none;padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;justify-content:center;transition:opacity .2s;text-decoration:none;}
.btn-primary:hover{opacity:.85;}
.btn-secondary{background:rgba(255,255,255,.05);color:var(--text);border:1px solid rgba(255,255,255,.08);padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:.4rem;justify-content:center;transition:background .2s;}
.btn-secondary:hover{background:rgba(255,255,255,.09);}
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
    <a href="projetos.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 1 3 3v1a3 3 0 0 1-6 0V5a3 3 0 0 1 3-3z"/><path d="M19 9a7 7 0 1 1-14 0"/><path d="M12 12v9M8 21h8"/></svg><span>Projetos</span></a>
    <a href="pedidos.php" class="nav-item"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span>Pedidos</span></a>
    <a href="analise.php" class="nav-item"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Análise</span></a>
    <a href="clientes.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Clientes</span></a>
    <a href="mensagens.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>Mensagens</span><span class="nav-badge"><?= $totalGeral ?></span></a>
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
      <h1>Mensa<em>gens</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Mensagens</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">
    <div class="kpi-grid" style="margin-bottom:1.4rem">
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <div class="kpi-label">Total de Mensagens</div>
        <div class="kpi-value"><?= $totalGeral ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(16,185,129,.12)"><svg viewBox="0 0 24 24" style="stroke:#10b981"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="kpi-label">Hoje</div>
        <div class="kpi-value" style="color:#10b981"><?= $hoje ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        <div class="kpi-label">Esta Semana</div>
        <div class="kpi-value"><?= $semana ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <div class="kpi-label">Exibindo</div>
        <div class="kpi-value"><?= count($contatos) ?><sup style="font-size:1rem;color:var(--text-dim)"> / <?= $totalReg ?></sup></div>
      </div>
    </div>

    <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:1.4rem">
      <form method="GET" class="search-filter">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="busca" placeholder="Buscar por nome, email, pacote ou mensagem..." value="<?= htmlspecialchars($busca) ?>">
      </form>
    </div>

    <div class="msg-list reveal">
      <?php if (empty($contatos)): ?>
        <div style="text-align:center;padding:4rem;opacity:.4">Nenhuma mensagem encontrada.</div>
      <?php else: ?>
        <?php foreach ($contatos as $c):
          $nome = $c['nome'] ?? 'Anônimo';
          $partes = explode(' ', $nome);
          $ini = strtoupper(substr($partes[0],0,1).(isset($partes[1])?substr($partes[1],0,1):substr($nome,1,1)));
          $dataRef = $c['data_envio'] ?: $c['created_at'];
        ?>
        <div class="msg-item reveal" onclick="abrirMsg('<?= htmlspecialchars(addslashes($nome)) ?>','<?= htmlspecialchars(addslashes($c['email'])) ?>','<?= htmlspecialchars(addslashes($c['pacote'] ?? '')) ?>','<?= htmlspecialchars(addslashes($c['mensagem'] ?? '')) ?>','<?= $dataRef ? date('d/m/Y H:i', strtotime($dataRef)) : '' ?>')">
          <div class="msg-avatar"><?= $ini ?></div>
          <div>
            <div class="msg-remetente"><?= htmlspecialchars($nome) ?></div>
            <div class="msg-email"><?= htmlspecialchars($c['email']) ?></div>
            <?php if ($c['pacote']): ?><span class="msg-pacote"><?= htmlspecialchars($c['pacote']) ?></span><?php endif; ?>
            <div class="msg-preview"><?= htmlspecialchars($c['mensagem'] ?? '(sem mensagem)') ?></div>
          </div>
          <div>
            <div class="msg-time"><?= $dataRef ? date('d/m H:i', strtotime($dataRef)) : '—' ?></div>
            <button class="msg-btn" onclick="event.stopPropagation();window.location='mailto:<?= htmlspecialchars($c['email']) ?>'">Responder</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPags > 1): ?>
    <div class="pagination">
      <?php if ($pagina>1): ?><a href="?pagina=<?=$pagina-1?>&busca=<?=urlencode($busca)?>" class="page-btn">‹</a><?php endif; ?>
      <?php for($i=max(1,$pagina-2);$i<=min($totalPags,$pagina+2);$i++): ?>
        <a href="?pagina=<?=$i?>&busca=<?=urlencode($busca)?>" class="page-btn <?=$i==$pagina?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
      <?php if ($pagina<$totalPags): ?><a href="?pagina=<?=$pagina+1?>&busca=<?=urlencode($busca)?>" class="page-btn">›</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal mensagem -->
<div class="modal-overlay" id="modalMsg" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.2rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,.07)">
      <div style="width:50px;height:50px;border-radius:13px;background:linear-gradient(135deg,rgba(224,27,36,.3),rgba(224,27,36,.1));display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;color:var(--red);border:1px solid rgba(224,27,36,.2);flex-shrink:0" id="mAv">--</div>
      <div>
        <div style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1.05rem" id="mNm"></div>
        <div style="font-size:.76rem;color:var(--text-dim)" id="mEm"></div>
        <div style="margin-top:.25rem" id="mPk"></div>
        <div style="font-size:.7rem;color:var(--text-dim);margin-top:.15rem" id="mDt"></div>
      </div>
    </div>
    <div style="font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);margin-bottom:.5rem">Mensagem</div>
    <div style="font-size:.88rem;line-height:1.75;color:var(--text-dim);white-space:pre-wrap;background:rgba(255,255,255,.03);padding:1rem;border-radius:10px;min-height:60px" id="mMs"></div>
    <div style="display:flex;gap:.75rem;margin-top:1.4rem">
      <a id="mLink" href="#" class="btn-primary" style="flex:1">Responder por email</a>
      <button class="btn-secondary" onclick="document.getElementById('modalMsg').classList.remove('open')" style="flex:1">Fechar</button>
    </div>
  </div>
</div>

<script>
function abrirMsg(nome,email,pacote,msg,data){
  const p=nome.split(' ');
  document.getElementById('mAv').textContent=(p[0][0]+(p[1]?.[0]||p[0][1]||'')).toUpperCase();
  document.getElementById('mNm').textContent=nome;
  document.getElementById('mEm').textContent=email;
  document.getElementById('mMs').textContent=msg||'(sem mensagem)';
  document.getElementById('mDt').textContent=data?'Enviado em '+data:'';
  document.getElementById('mLink').href='mailto:'+email;
  const pk=document.getElementById('mPk');
  pk.innerHTML=pacote?`<span style="background:rgba(224,27,36,.1);color:var(--red);border:1px solid rgba(224,27,36,.2);border-radius:99px;padding:.15rem .6rem;font-size:.63rem;font-weight:700">${pacote}</span>`:'';
  document.getElementById('modalMsg').classList.add('open');
}
const cur=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
(function anim(){rx+=(mx-rx)*.1;ry+=(my-ry)*.1;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(anim);})();
document.querySelectorAll('a,button,input').forEach(el=>{
  el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';ring.style.borderColor='var(--red)';});
  el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';ring.style.borderColor='rgba(224,27,36,.45)';});
});
const obs=new IntersectionObserver(entries=>{entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*40);obs.unobserve(e.target);}});},{threshold:.05});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
document.querySelector('.search-filter input')?.addEventListener('keydown',e=>{if(e.key==='Enter')e.target.closest('form').submit();});
</script>
</body>
</html>