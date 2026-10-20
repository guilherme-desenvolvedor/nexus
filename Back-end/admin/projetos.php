<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

$statusFiltro = $_GET['status'] ?? 'todos';
$busca = $_GET['busca'] ?? '';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

$where = []; $params = [];
if ($statusFiltro !== 'todos') { $where[] = "status = :status"; $params[':status'] = $statusFiltro; }
if ($busca) { $where[] = "(nome LIKE :busca OR cliente LIKE :busca)"; $params[':busca'] = "%$busca%"; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM projetos $whereSQL");
$stmtTotal->execute($params);
$totalReg = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalReg / $porPagina);

$stmt = $pdo->prepare("SELECT * FROM projetos $whereSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':limit',$porPagina,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$metricas = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status='ativo'     THEN 1 ELSE 0 END) as ativos,
        SUM(CASE WHEN status='concluido' THEN 1 ELSE 0 END) as concluidos,
        SUM(CASE WHEN status='pausado'   THEN 1 ELSE 0 END) as pausados,
        COALESCE(SUM(valor),0) as valor_total
    FROM projetos
")->fetch(PDO::FETCH_ASSOC);

$mensagens = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS — Projetos</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.btn-primary{background:var(--red);color:#fff;border:none;padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:opacity .2s;}
.btn-primary:hover{opacity:.85;}
.btn-secondary{background:rgba(255,255,255,.05);color:var(--text);border:1px solid rgba(255,255,255,.08);padding:.55rem 1.2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:background .2s;}
.btn-secondary:hover{background:rgba(255,255,255,.09);}
.filter-bar{display:flex;gap:.75rem;align-items:center;margin-bottom:1.4rem;flex-wrap:wrap;}
.filter-tabs{display:flex;background:rgba(255,255,255,.04);border-radius:10px;padding:3px;gap:2px;}
.filter-tab{padding:.4rem .9rem;border-radius:8px;font-size:.78rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .2s;text-decoration:none;}
.filter-tab.active,.filter-tab:hover{background:rgba(224,27,36,.15);color:var(--red);}
.search-filter{flex:1;max-width:300px;position:relative;}
.search-filter svg{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--text-dim);stroke-width:2;fill:none;}
.search-filter input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.5rem .9rem .5rem 2.2rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.83rem;outline:none;transition:border-color .2s;box-sizing:border-box;}
.search-filter input:focus{border-color:rgba(224,27,36,.4);}
.proj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;margin-bottom:1.5rem;}
.proj-card{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:1.4rem;transition:border-color .2s,transform .2s;position:relative;overflow:hidden;}
.proj-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--red);opacity:0;transition:opacity .2s;}
.proj-card:hover{border-color:rgba(224,27,36,.3);transform:translateY(-2px);}
.proj-card:hover::before{opacity:1;}
.proj-card-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;}
.proj-nome{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;color:var(--text);line-height:1.3;}
.proj-cliente{font-size:.78rem;color:var(--text-dim);margin-top:.2rem;}
.proj-desc{font-size:.78rem;color:var(--text-dim);margin-top:.5rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.proj-card-meta{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-top:1rem;}
.proj-meta-label{font-size:.68rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;}
.proj-meta-val{font-size:.88rem;font-weight:600;color:var(--text);}
.prazo-alert{color:#f59e0b;}
.proj-card-actions{display:flex;gap:.5rem;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);}
.proj-action-btn{flex:1;padding:.4rem;border-radius:8px;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.03);color:var(--text-dim);font-size:.72rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;transition:all .2s;text-align:center;}
.proj-action-btn:hover{background:rgba(224,27,36,.1);color:var(--red);border-color:rgba(224,27,36,.2);}
.pagination{display:flex;justify-content:center;gap:.4rem;margin-top:1.5rem;}
.page-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-dim);font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .2s;}
.page-btn:hover,.page-btn.active{background:var(--red);color:#fff;border-color:var(--red);}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:#111;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2rem;width:100%;max-width:500px;transform:scale(.96) translateY(10px);transition:transform .3s;}
.modal-overlay.open .modal{transform:scale(1) translateY(0);}
.modal-title{font-family:'MuseoModerno',sans-serif;font-size:1.3rem;font-weight:700;margin-bottom:1.5rem;}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:.4rem;}
.form-input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.65rem 1rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;}
.form-input:focus{border-color:rgba(224,27,36,.5);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
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
    <a href="projetos.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 1 3 3v1a3 3 0 0 1-6 0V5a3 3 0 0 1 3-3z"/><path d="M19 9a7 7 0 1 1-14 0"/><path d="M12 12v9M8 21h8"/></svg><span>Projetos</span><span class="nav-badge"><?= $metricas['ativos'] ?></span></a>
    <a href="pedidos.php" class="nav-item"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span>Pedidos</span></a>
    <a href="analise.php" class="nav-item"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Análise</span></a>
    <a href="clientes.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><span>Clientes</span></a>
    <a href="mensagens.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span>Mensagens</span><span class="nav-badge"><?= $mensagens ?></span></a>
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
      <h1>Pro<em>jetos</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Projetos</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">
    <div class="kpi-grid" style="margin-bottom:1.4rem">
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="kpi-label">Total</div>
        <div class="kpi-value"><?= $metricas['total'] ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(16,185,129,.12)"><svg viewBox="0 0 24 24" style="stroke:#10b981"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        <div class="kpi-label">Ativos</div>
        <div class="kpi-value" style="color:#10b981"><?= $metricas['ativos'] ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-label">Concluídos</div>
        <div class="kpi-value"><?= $metricas['concluidos'] ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-label">Valor em Carteira</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format($metricas['valor_total'], 0, ',', '.') ?></div>
      </div>
    </div>

    <div class="filter-bar reveal">
      <div class="filter-tabs">
        <a href="?status=todos<?= $busca?'&busca='.urlencode($busca):'' ?>" class="filter-tab <?= $statusFiltro==='todos'?'active':'' ?>">Todos</a>
        <a href="?status=ativo<?= $busca?'&busca='.urlencode($busca):'' ?>" class="filter-tab <?= $statusFiltro==='ativo'?'active':'' ?>">Ativos</a>
        <a href="?status=concluido<?= $busca?'&busca='.urlencode($busca):'' ?>" class="filter-tab <?= $statusFiltro==='concluido'?'active':'' ?>">Concluídos</a>
        <a href="?status=pausado<?= $busca?'&busca='.urlencode($busca):'' ?>" class="filter-tab <?= $statusFiltro==='pausado'?'active':'' ?>">Pausados</a>
      </div>
      <form method="GET" class="search-filter">
        <?php if ($statusFiltro!=='todos'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($statusFiltro) ?>"><?php endif; ?>
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="busca" placeholder="Buscar projeto ou cliente..." value="<?= htmlspecialchars($busca) ?>">
      </form>
      <div style="margin-left:auto;display:flex;gap:.75rem">
        <button class="btn-primary" onclick="abrirNovo()">
          <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Novo Projeto
        </button>
      </div>
    </div>

    <div class="proj-grid reveal">
      <?php if (empty($projetos)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;opacity:.4">
          <?= $statusFiltro !== 'todos' || $busca ? 'Nenhum projeto encontrado.' : 'Nenhum projeto cadastrado ainda. Crie o primeiro!' ?>
        </div>
      <?php else: ?>
        <?php foreach ($projetos as $p):
          $prazoAlerta = $p['prazo'] && strtotime($p['prazo']) < strtotime('+7 days') && $p['status'] === 'ativo';
        ?>
        <div class="proj-card reveal">
          <div class="proj-card-top">
            <div style="flex:1;min-width:0">
              <div class="proj-nome"><?= htmlspecialchars($p['nome']) ?></div>
              <div class="proj-cliente"><?= htmlspecialchars($p['cliente']) ?></div>
              <?php if ($p['descricao']): ?>
                <div class="proj-desc"><?= htmlspecialchars($p['descricao']) ?></div>
              <?php endif; ?>
            </div>
            <span class="status-badge <?= $p['status'] ?>" style="margin-left:.75rem;flex-shrink:0"><?= ucfirst($p['status']) ?></span>
          </div>
          <div class="proj-card-meta">
            <div>
              <div class="proj-meta-label">Valor</div>
              <div class="proj-meta-val">R$ <?= number_format($p['valor'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div>
              <div class="proj-meta-label">Prazo</div>
              <div class="proj-meta-val <?= $prazoAlerta ? 'prazo-alert' : '' ?>">
                <?= $p['prazo'] ? date('d/m/Y', strtotime($p['prazo'])) : '—' ?>
                <?= $prazoAlerta ? ' ⚠️' : '' ?>
              </div>
            </div>
            <div>
              <div class="proj-meta-label">Criado em</div>
              <div class="proj-meta-val"><?= date('d/m/Y', strtotime($p['created_at'])) ?></div>
            </div>
          </div>
          <div class="proj-card-actions">
            <button class="proj-action-btn" onclick="verProjeto(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">Ver</button>
            <button class="proj-action-btn" onclick="editarProjeto(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">Editar</button>
            <button class="proj-action-btn" style="color:var(--red)" onclick="excluirProjeto(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['nome']), ENT_QUOTES) ?>)">Excluir</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <div class="pagination">
      <?php if ($pagina>1): ?><a href="?pagina=<?=$pagina-1?>&status=<?=$statusFiltro?>&busca=<?=urlencode($busca)?>" class="page-btn">‹</a><?php endif; ?>
      <?php for($i=max(1,$pagina-2);$i<=min($totalPaginas,$pagina+2);$i++): ?>
        <a href="?pagina=<?=$i?>&status=<?=$statusFiltro?>&busca=<?=urlencode($busca)?>" class="page-btn <?=$i==$pagina?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
      <?php if ($pagina<$totalPaginas): ?><a href="?pagina=<?=$pagina+1?>&status=<?=$statusFiltro?>&busca=<?=urlencode($busca)?>" class="page-btn">›</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Novo/Editar Projeto -->
<div class="modal-overlay" id="modalNovo" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title" id="modalTitulo">Novo Projeto</div>
    <form method="POST" action="projetos_action.php">
      <input type="hidden" name="acao" id="formAcao" value="criar">
      <input type="hidden" name="id" id="formId" value="">
      <div class="form-group"><label class="form-label">Nome do Projeto</label><input type="text" name="nome" class="form-input" placeholder="Ex: Site Institucional" required></div>
      <div class="form-group"><label class="form-label">Cliente</label><input type="text" name="cliente" class="form-input" placeholder="Ex: DataFlow BR" required></div>
      <div class="form-group"><label class="form-label">Descrição</label><textarea name="descricao" class="form-input" rows="3" placeholder="Descrição do projeto..." style="resize:vertical"></textarea></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Valor (R$)</label><input type="number" name="valor" class="form-input" placeholder="0,00" step="0.01" min="0"></div>
        <div class="form-group"><label class="form-label">Prazo</label><input type="date" name="prazo" class="form-input"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
          <option value="ativo">Ativo</option>
          <option value="pausado">Pausado</option>
          <option value="concluido">Concluído</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-secondary" onclick="document.getElementById('modalNovo').classList.remove('open')">Cancelar</button>
        <button type="submit" class="btn-primary" id="modalSubmitBtn">Criar Projeto</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Ver Projeto -->
<div class="modal-overlay" id="modalVer" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title" id="verNome">Projeto</div>
    <div style="display:flex;flex-direction:column;gap:.9rem;font-size:.88rem">
      <div><div class="form-label" style="margin-bottom:.3rem">Cliente</div><div id="verCliente"></div></div>
      <div><div class="form-label" style="margin-bottom:.3rem">Descrição</div><div id="verDescricao" style="color:var(--text-dim)"></div></div>
      <div class="form-row">
        <div><div class="form-label" style="margin-bottom:.3rem">Valor</div><div id="verValor"></div></div>
        <div><div class="form-label" style="margin-bottom:.3rem">Prazo</div><div id="verPrazo"></div></div>
      </div>
      <div><div class="form-label" style="margin-bottom:.3rem">Status</div><div id="verStatus"></div></div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn-secondary" onclick="document.getElementById('modalVer').classList.remove('open')">Fechar</button>
    </div>
  </div>
</div>

<!-- Form oculto para exclusão -->
<form method="POST" action="projetos_action.php" id="formExcluir" style="display:none">
  <input type="hidden" name="acao" value="excluir">
  <input type="hidden" name="id" id="excluirId">
</form>

<script>
  const formProjeto = document.querySelector('#modalNovo form');

  function abrirNovo(){
    document.getElementById('formAcao').value = 'criar';
    document.getElementById('formId').value = '';
    document.getElementById('modalTitulo').textContent = 'Novo Projeto';
    document.getElementById('modalSubmitBtn').textContent = 'Criar Projeto';
    formProjeto.reset();
    document.getElementById('modalNovo').classList.add('open');
  }

  function editarProjeto(p){
    document.getElementById('formAcao').value = 'editar';
    document.getElementById('formId').value = p.id;
    document.getElementById('modalTitulo').textContent = 'Editar Projeto';
    document.getElementById('modalSubmitBtn').textContent = 'Salvar Alterações';
    formProjeto.nome.value = p.nome || '';
    formProjeto.cliente.value = p.cliente || '';
    formProjeto.descricao.value = p.descricao || '';
    formProjeto.valor.value = p.valor || '';
    formProjeto.prazo.value = p.prazo || '';
    formProjeto.status.value = p.status || 'ativo';
    document.getElementById('modalNovo').classList.add('open');
  }

  function verProjeto(p){
    document.getElementById('verNome').textContent = p.nome;
    document.getElementById('verCliente').textContent = p.cliente;
    document.getElementById('verDescricao').textContent = p.descricao || '(sem descrição)';
    document.getElementById('verValor').textContent = 'R$ ' + Number(p.valor || 0).toLocaleString('pt-BR', {minimumFractionDigits:2});
    document.getElementById('verPrazo').textContent = p.prazo ? new Date(p.prazo+'T00:00:00').toLocaleDateString('pt-BR') : '—';
    document.getElementById('verStatus').textContent = p.status.charAt(0).toUpperCase() + p.status.slice(1);
    document.getElementById('modalVer').classList.add('open');
  }

  function excluirProjeto(id, nome){
    if(!confirm('Excluir o projeto "'+nome+'"? Essa ação não pode ser desfeita.')) return;
    document.getElementById('excluirId').value = id;
    document.getElementById('formExcluir').submit();
  }

  const cur=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
  let mx=0,my=0,rx=0,ry=0;
  document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
  (function anim(){rx+=(mx-rx)*.1;ry+=(my-ry)*.1;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(anim);})();
  document.querySelectorAll('a,button,input,select,textarea').forEach(el=>{
    el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';ring.style.borderColor='var(--red)';});
    el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';ring.style.borderColor='rgba(224,27,36,.45)';});
  });
  const obs=new IntersectionObserver(entries=>{entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*60);obs.unobserve(e.target);}});},{threshold:.05});
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>
</body>
</html>