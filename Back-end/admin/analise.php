<?php
// ============================================================
// analise.php — usa contatos, payments, projetos, atividades
// ============================================================
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

// Receita diária (30 dias)
$receitaDiaria = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%d/%m') as label,
           COALESCE(SUM(amount),0) as total
    FROM payments WHERE status='aprovado'
    AND created_at >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Receita mensal (12 meses)
$receitaMensal = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%b/%y') as label,
           COALESCE(SUM(amount),0) as total
    FROM payments WHERE status='aprovado'
    AND created_at >= DATE_SUB(CURDATE(),INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY DATE_FORMAT(created_at,'%Y-%m') ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Contatos por mês (6 meses)
$contatosMensal = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%b/%y') as label, COUNT(*) as qtd
    FROM contatos
    WHERE created_at >= DATE_SUB(CURDATE(),INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY DATE_FORMAT(created_at,'%Y-%m') ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Contatos por pacote
$contatosPorPacote = $pdo->query("
    SELECT COALESCE(NULLIF(pacote,''),'Sem pacote') as pacote, COUNT(*) as qtd
    FROM contatos GROUP BY pacote ORDER BY qtd DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Projetos por status
$projetosPorStatus = $pdo->query("
    SELECT status, COUNT(*) as qtd, COALESCE(SUM(valor),0) as total_valor
    FROM projetos GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Métricas gerais
$stats = $pdo->query("
    SELECT
        (SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='aprovado') as receita_total,
        (SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='aprovado' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())) as receita_mes,
        (SELECT COALESCE(AVG(amount),0) FROM payments WHERE status='aprovado') as ticket_medio,
        (SELECT COUNT(*) FROM contatos) as total_contatos,
        (SELECT COUNT(*) FROM contatos WHERE MONTH(created_at)=MONTH(CURDATE())) as contatos_mes,
        (SELECT COUNT(*) FROM projetos WHERE status='ativo') as proj_ativos,
        (SELECT COUNT(*) FROM projetos WHERE status='concluido') as proj_concluidos
")->fetch(PDO::FETCH_ASSOC);

$mensagensCount = $stats['total_contatos'];
$projetosAtivos = $stats['proj_ativos'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Análise</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<style>
.analytics-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;}
.canvas-wrap{position:relative;height:200px;margin-top:1rem;}
.stat-block{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:1.4rem;}
.stat-block-title{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.95rem;margin-bottom:1rem;}
.stat-row{display:flex;align-items:center;gap:.6rem;padding:.55rem 0;border-bottom:1px solid rgba(255,255,255,.04);}
.stat-row:last-child{border-bottom:none;}
.stat-label{font-size:.8rem;color:var(--text-dim);flex:1;}
.stat-bar-wrap{width:80px;height:4px;background:rgba(255,255,255,.07);border-radius:99px;overflow:hidden;}
.stat-bar-fill{height:100%;background:var(--red);border-radius:99px;}
.stat-val{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.88rem;text-align:right;min-width:40px;}
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
    <a href="analise.php" class="nav-item active"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Análise</span></a>
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
      <h1>Aná<em>lise</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Análise</span></div>
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
        <div class="kpi-label">Receita Total</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format($stats['receita_total'],0,',','.') ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon" style="background:rgba(16,185,129,.12)"><svg viewBox="0 0 24 24" style="stroke:#10b981"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-label">Receita do Mês</div>
        <div class="kpi-value" style="color:#10b981"><sup>R$</sup><?= number_format($stats['receita_mes'],0,',','.') ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg></div>
        <div class="kpi-label">Ticket Médio</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format($stats['ticket_medio'],0,',','.') ?></div>
      </div>
      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="kpi-label">Contatos</div>
        <div class="kpi-value"><?= $stats['total_contatos'] ?><sup style="font-size:1rem;color:var(--text-dim)"> +<?= $stats['contatos_mes'] ?>/mês</sup></div>
      </div>
    </div>

    <!-- Receita diária full width -->
    <div class="chart-card reveal" style="margin-bottom:1rem">
      <div class="chart-header">
        <div><div class="chart-title">Receita Diária</div><div class="chart-sub">Últimos 30 dias · aprovados</div></div>
      </div>
      <div class="canvas-wrap"><canvas id="chartDiario"></canvas></div>
    </div>

    <div class="analytics-grid" style="margin-bottom:1rem">
      <div class="chart-card reveal">
        <div class="chart-header"><div><div class="chart-title">Receita Mensal</div><div class="chart-sub">Últimos 12 meses</div></div></div>
        <div class="canvas-wrap"><canvas id="chartMensal"></canvas></div>
      </div>
      <div class="chart-card reveal">
        <div class="chart-header"><div><div class="chart-title">Novos Contatos</div><div class="chart-sub">Últimos 6 meses</div></div></div>
        <div class="canvas-wrap"><canvas id="chartContatos"></canvas></div>
      </div>
    </div>

    <div class="analytics-grid" style="margin-bottom:1rem">
      <div class="stat-block reveal">
        <div class="stat-block-title">Contatos por Pacote</div>
        <?php $maxPk = max(array_column($contatosPorPacote,'qtd') ?: [1]); ?>
        <?php foreach ($contatosPorPacote as $pk): ?>
        <div class="stat-row">
          <span class="stat-label"><?= htmlspecialchars($pk['pacote']) ?></span>
          <div class="stat-bar-wrap"><div class="stat-bar-fill" style="width:<?= round($pk['qtd']/$maxPk*100) ?>%"></div></div>
          <span class="stat-val"><?= $pk['qtd'] ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($contatosPorPacote)): ?><div style="opacity:.3;text-align:center;padding:1rem">Sem dados</div><?php endif; ?>
      </div>
      <div class="stat-block reveal">
        <div class="stat-block-title">Resumo de Projetos</div>
        <?php $totProj = array_sum(array_column($projetosPorStatus,'qtd')) ?: 1; ?>
        <?php foreach ($projetosPorStatus as $ps): ?>
        <div class="stat-row">
          <span class="stat-label"><?= ucfirst($ps['status']) ?></span>
          <div class="stat-bar-wrap"><div class="stat-bar-fill" style="width:<?= round($ps['qtd']/$totProj*100) ?>%"></div></div>
          <span class="stat-val"><?= $ps['qtd'] ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($projetosPorStatus)): ?><div style="opacity:.3;text-align:center;padding:1rem">Sem projetos</div><?php endif; ?>
        <div class="stat-row" style="margin-top:.4rem;border-top:1px solid rgba(255,255,255,.06);padding-top:.8rem">
          <span class="stat-label">Valor total em carteira</span>
          <span class="stat-val" style="font-size:.78rem">R$ <?= number_format(array_sum(array_column($projetosPorStatus,'total_valor')),0,',','.') ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const diasL=<?= json_encode(array_column($receitaDiaria,'label')) ?>;
const diasV=<?= json_encode(array_map('floatval',array_column($receitaDiaria,'total'))) ?>;
const mesL=<?= json_encode(array_column($receitaMensal,'label')) ?>;
const mesV=<?= json_encode(array_map('floatval',array_column($receitaMensal,'total'))) ?>;
const ctL=<?= json_encode(array_column($contatosMensal,'label')) ?>;
const ctV=<?= json_encode(array_map('intval',array_column($contatosMensal,'qtd'))) ?>;

const def={responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:'#1a1a1a',titleColor:'#f5f5f5',bodyColor:'#aaa',borderColor:'rgba(255,255,255,.1)',borderWidth:1,padding:10}},scales:{x:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'rgba(245,245,245,.3)',font:{family:'DM Sans',size:10}}},y:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'rgba(245,245,245,.3)',font:{family:'DM Sans',size:10},callback:v=>'R$'+(v/1000).toFixed(0)+'k'}}}};

new Chart(document.getElementById('chartDiario'),{type:'line',data:{labels:diasL.length?diasL:['Sem dados'],datasets:[{data:diasV.length?diasV:[0],borderColor:'#E01B24',backgroundColor:'rgba(224,27,36,.08)',fill:true,tension:0.4,pointRadius:2,pointBackgroundColor:'#E01B24'}]},options:{...def}});
new Chart(document.getElementById('chartMensal'),{type:'bar',data:{labels:mesL.length?mesL:['Sem dados'],datasets:[{data:mesV.length?mesV:[0],backgroundColor:'rgba(224,27,36,.55)',borderRadius:6,borderSkipped:false}]},options:{...def}});
new Chart(document.getElementById('chartContatos'),{type:'bar',data:{labels:ctL.length?ctL:['Sem dados'],datasets:[{data:ctV.length?ctV:[0],backgroundColor:'rgba(245,245,245,.12)',borderRadius:6,borderSkipped:false}]},options:{...def,scales:{...def.scales,y:{...def.scales.y,ticks:{...def.scales.y.ticks,callback:v=>v}}},plugins:{...def.plugins,tooltip:{...def.plugins.tooltip,callbacks:{label:ctx=>' '+ctx.parsed.y+' contatos'}}}}});

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