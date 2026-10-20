<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

$busca  = $_GET['busca'] ?? '';
$pacote = $_GET['pacote'] ?? 'todos';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$porPagina = 12;
$offset = ($pagina - 1) * $porPagina;

$where = []; $params = [];
if ($busca)  { $where[] = "(nome LIKE :busca OR email LIKE :busca OR pacote LIKE :busca)"; $params[':busca'] = "%$busca%"; }
if ($pacote !== 'todos') { $where[] = "pacote = :pacote"; $params[':pacote'] = $pacote; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM contatos $whereSQL");
$stmtTotal->execute($params);
$totalClientes = $stmtTotal->fetchColumn();
$totalPaginas  = ceil($totalClientes / $porPagina);

$stmt = $pdo->prepare("SELECT * FROM contatos $whereSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$porPagina,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalGeral  = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
$novosHoje   = $pdo->query("SELECT COUNT(*) FROM contatos WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$novosSemana = $pdo->query("SELECT COUNT(*) FROM contatos WHERE YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)")->fetchColumn();

// Lista de pacotes para filtro
$pacotesDisponiveis = $pdo->query("SELECT DISTINCT pacote FROM contatos WHERE pacote IS NOT NULL AND pacote != '' ORDER BY pacote")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Clientes</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.btn-primary{background:var(--red);color:#fff;border:none;padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:opacity .2s;}
.btn-primary:hover{opacity:.85;}
.btn-secondary{background:rgba(255,255,255,.05);color:var(--text);border:1px solid rgba(255,255,255,.08);padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:background .2s;}
.btn-secondary:hover{background:rgba(255,255,255,.09);}
.filter-bar{display:flex;gap:.75rem;align-items:center;margin-bottom:1.4rem;flex-wrap:wrap;}
.filter-tabs{display:flex;background:rgba(255,255,255,.04);border-radius:10px;padding:3px;gap:2px;flex-wrap:wrap;}
.filter-tab{padding:.4rem .9rem;border-radius:8px;font-size:.75rem;font-weight:600;letter-spacing:.04em;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .2s;text-decoration:none;white-space:nowrap;}
.filter-tab.active,.filter-tab:hover{background:rgba(224,27,36,.15);color:var(--red);}
.search-filter{flex:1;max-width:300px;position:relative;}
.search-filter svg{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--text-dim);stroke-width:2;fill:none;}
.search-filter input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.5rem .9rem .5rem 2.2rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.83rem;outline:none;transition:border-color .2s;box-sizing:border-box;}
.search-filter input:focus{border-color:rgba(224,27,36,.4);}
.clients-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:1.5rem;}
.client-card{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:1.4rem;transition:border-color .2s,transform .2s;}
.client-card:hover{border-color:rgba(224,27,36,.25);transform:translateY(-2px);}
.client-card-top{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;}
.client-avatar-big{width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,rgba(224,27,36,.3),rgba(224,27,36,.1));display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;color:var(--red);flex-shrink:0;border:1px solid rgba(224,27,36,.2);}
.client-name{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.95rem;color:var(--text);}
.client-email{font-size:.75rem;color:var(--text-dim);margin-top:.15rem;word-break:break-all;}
.client-divider{height:1px;background:var(--border);margin:.8rem 0;}
.client-meta{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.client-meta-label{font-size:.65rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.1rem;}
.client-meta-val{font-size:.82rem;font-weight:600;color:var(--text);}
.client-msg-preview{font-size:.75rem;color:var(--text-dim);line-height:1.5;margin-top:.5rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;padding:.6rem;background:rgba(255,255,255,.03);border-radius:8px;border-left:2px solid rgba(224,27,36,.3);}
.client-card-footer{display:flex;gap:.5rem;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);}
.client-btn{flex:1;padding:.38rem;border-radius:8px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);color:var(--text-dim);font-size:.7rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;transition:all .2s;text-align:center;}
.client-btn:hover{background:rgba(224,27,36,.1);color:var(--red);border-color:rgba(224,27,36,.2);}
.pacote-tag{display:inline-block;background:rgba(224,27,36,.1);color:var(--red);border:1px solid rgba(224,27,36,.2);border-radius:99px;padding:.15rem .6rem;font-size:.65rem;font-weight:700;letter-spacing:.04em;margin-top:.3rem;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.pagination{display:flex;justify-content:center;gap:.4rem;margin-top:1.5rem;}
.page-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-dim);font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;}
.page-btn:hover,.page-btn.active{background:var(--red);color:#fff;border-color:var(--red);}
/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:#111;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2rem;width:100%;max-width:500px;transform:scale(.96) translateY(10px);transition:transform .3s;max-height:85vh;overflow-y:auto;}
.modal-overlay.open .modal{transform:scale(1) translateY(0);}
.modal-title{font-family:'MuseoModerno',sans-serif;font-size:1.3rem;font-weight:700;margin-bottom:1.5rem;}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:.4rem;}
.form-input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.65rem 1rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;}
.form-input:focus{border-color:rgba(224,27,36,.5);}
.modal-actions{display:flex;gap:.75rem;margin-top:1.5rem;}
.modal-actions .btn-primary,.modal-actions .btn-secondary{flex:1;justify-content:center;}
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
    <a href="clientes.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Clientes</span></a>
    <a href="mensagens.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>Mensagens</span><span class="nav-badge"><?= $totalGeral ?></span></a>
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
      <h1>Cli<em>entes</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Clientes</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">
    <div class="kpi-grid" style="margin-bottom:1.4rem">
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="kpi-label">Total de Contatos</div>
        <div class="kpi-value"><?= $totalGeral ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(16,185,129,.12)"><svg viewBox="0 0 24 24" style="stroke:#10b981"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
        <div class="kpi-label">Novos Hoje</div>
        <div class="kpi-value" style="color:#10b981"><?= $novosHoje ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="kpi-label">Novos na Semana</div>
        <div class="kpi-value"><?= $novosSemana ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <div class="kpi-label">Pacotes Distintos</div>
        <div class="kpi-value"><?= count($pacotesDisponiveis) ?></div>
      </div>
    </div>

    <div class="filter-bar reveal">
      <div class="filter-tabs">
        <a href="?pacote=todos<?= $busca?'&busca='.urlencode($busca):'' ?>" class="filter-tab <?= $pacote==='todos'?'active':'' ?>">Todos</a>
        <?php foreach ($pacotesDisponiveis as $pk): ?>
          <a href="?pacote=<?= urlencode($pk) ?><?= $busca?'&busca='.urlencode($busca):'' ?>" class="filter-tab <?= $pacote===$pk?'active':'' ?>"><?= htmlspecialchars($pk) ?></a>
        <?php endforeach; ?>
      </div>
      <form method="GET" class="search-filter">
        <?php if ($pacote!=='todos'): ?><input type="hidden" name="pacote" value="<?= htmlspecialchars($pacote) ?>"><?php endif; ?>
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="busca" placeholder="Nome, email ou pacote..." value="<?= htmlspecialchars($busca) ?>">
      </form>
    </div>

    <div class="clients-grid">
      <?php if (empty($clientes)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;opacity:.4">Nenhum contato encontrado.</div>
      <?php else: ?>
        <?php foreach ($clientes as $c):
          $nome = $c['nome'] ?? 'Sem nome';
          $partes = explode(' ', $nome);
          $iniciais = strtoupper(substr($partes[0],0,1) . (isset($partes[1]) ? substr($partes[1],0,1) : substr($nome,1,1)));
          $dataRef = $c['data_envio'] ?: $c['created_at'];
        ?>
        <div class="client-card reveal">
          <div class="client-card-top">
            <div class="client-avatar-big"><?= $iniciais ?></div>
            <div style="min-width:0">
              <div class="client-name"><?= htmlspecialchars($nome) ?></div>
              <div class="client-email"><?= htmlspecialchars($c['email']) ?></div>
              <?php if ($c['pacote']): ?>
                <div class="pacote-tag"><?= htmlspecialchars($c['pacote']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($c['mensagem']): ?>
            <div class="client-msg-preview"><?= htmlspecialchars($c['mensagem']) ?></div>
          <?php endif; ?>
          <div class="client-divider"></div>
          <div class="client-meta">
            <div>
              <div class="client-meta-label">Contato em</div>
              <div class="client-meta-val"><?= $dataRef ? date('d/m/Y', strtotime($dataRef)) : '—' ?></div>
            </div>
            <div>
              <div class="client-meta-label">Cadastrado</div>
              <div class="client-meta-val"><?= $c['created_at'] ? date('d/m/Y', strtotime($c['created_at'])) : '—' ?></div>
            </div>
          </div>
          <div class="client-card-footer">
            <button class="client-btn" onclick="verMensagem(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($nome)) ?>', '<?= htmlspecialchars(addslashes($c['email'])) ?>', '<?= htmlspecialchars(addslashes($c['mensagem'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($c['pacote'] ?? '')) ?>')">Ver</button>
            <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="client-btn" style="display:flex;align-items:center;justify-content:center;text-decoration:none">Email</a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <div class="pagination">
      <?php if ($pagina>1): ?><a href="?pagina=<?=$pagina-1?>&pacote=<?=urlencode($pacote)?>&busca=<?=urlencode($busca)?>" class="page-btn">‹</a><?php endif; ?>
      <?php for($i=max(1,$pagina-2);$i<=min($totalPaginas,$pagina+2);$i++): ?>
        <a href="?pagina=<?=$i?>&pacote=<?=urlencode($pacote)?>&busca=<?=urlencode($busca)?>" class="page-btn <?=$i==$pagina?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
      <?php if ($pagina<$totalPaginas): ?><a href="?pagina=<?=$pagina+1?>&pacote=<?=urlencode($pacote)?>&busca=<?=urlencode($busca)?>" class="page-btn">›</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal ver contato -->
<div class="modal-overlay" id="modalCliente" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,.07)">
      <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,rgba(224,27,36,.3),rgba(224,27,36,.1));display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1.1rem;color:var(--red);border:1px solid rgba(224,27,36,.2);flex-shrink:0" id="mAvatar">--</div>
      <div>
        <div style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1.1rem" id="mNome"></div>
        <div style="font-size:.78rem;color:var(--text-dim)" id="mEmail"></div>
        <div style="margin-top:.3rem" id="mPacoteTag"></div>
      </div>
    </div>
    <div style="font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);margin-bottom:.6rem">Mensagem</div>
    <div style="font-size:.88rem;line-height:1.75;color:var(--text-dim);white-space:pre-wrap;background:rgba(255,255,255,.03);padding:1rem;border-radius:10px;min-height:60px" id="mMensagem"></div>
    <div style="display:flex;gap:.75rem;margin-top:1.5rem">
      <a id="mEmailLink" href="#" class="btn-primary" style="flex:1;justify-content:center;text-decoration:none">Responder por email</a>
      <button class="btn-secondary" onclick="document.getElementById('modalCliente').classList.remove('open')" style="flex:1;justify-content:center">Fechar</button>
    </div>
  </div>
</div>

<script>
function verMensagem(id, nome, email, msg, pacote) {
  const partes = nome.split(' ');
  const ini = (partes[0][0] + (partes[1]?.[0] || partes[0][1] || '')).toUpperCase();
  document.getElementById('mAvatar').textContent = ini;
  document.getElementById('mNome').textContent = nome;
  document.getElementById('mEmail').textContent = email;
  document.getElementById('mMensagem').textContent = msg || '(sem mensagem)';
  document.getElementById('mEmailLink').href = 'mailto:' + email;
  const pt = document.getElementById('mPacoteTag');
  pt.innerHTML = pacote ? `<span style="background:rgba(224,27,36,.1);color:var(--red);border:1px solid rgba(224,27,36,.2);border-radius:99px;padding:.15rem .6rem;font-size:.65rem;font-weight:700">${pacote}</span>` : '';
  document.getElementById('modalCliente').classList.add('open');
}
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
document.querySelector('.search-filter input')?.addEventListener('keydown',e=>{if(e.key==='Enter')e.target.closest('form').submit();});
</script>
</body>
</html>