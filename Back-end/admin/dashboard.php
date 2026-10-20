<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";


$mensagens = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();

// Receita total aprovada
$receitaTotal = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='aprovado'")->fetchColumn();

// Receita semana anterior
$receitaSemanaAnterior = $pdo->query("
    SELECT COALESCE(SUM(amount),0) FROM payments
    WHERE status='aprovado'
    AND YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)-1
")->fetchColumn();

$crescimentoReceita = $receitaSemanaAnterior > 0
    ? (($receitaTotal - $receitaSemanaAnterior) / $receitaSemanaAnterior) * 100
    : 0;

// Novos clientes na semana (usa created_at)
$novosClientes = $pdo->query("
    SELECT COUNT(*) FROM contatos
    WHERE YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)
")->fetchColumn();

$clientesSemanaAnterior = $pdo->query("
    SELECT COUNT(*) FROM contatos
    WHERE YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)-1
")->fetchColumn();

$crescimentoClientes = $clientesSemanaAnterior > 0
    ? (($novosClientes - $clientesSemanaAnterior) / $clientesSemanaAnterior) * 100
    : 0;

// Projetos
$projetosAtivos    = $pdo->query("SELECT COUNT(*) FROM projetos WHERE status='ativo'")->fetchColumn();
$projetosConcluidos = $pdo->query("SELECT COUNT(*) FROM projetos WHERE status='concluido'")->fetchColumn();

$totalProjetos = $projetosAtivos + $projetosConcluidos;
$taxaConclusao = $totalProjetos > 0 ? ($projetosConcluidos / $totalProjetos) * 100 : 0;

$projetosAtivosSemAnt = $pdo->query("
    SELECT COUNT(*) FROM projetos WHERE status='ativo'
    AND YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)-1
")->fetchColumn();
$crescimentoProjetos = $projetosAtivosSemAnt > 0
    ? (($projetosAtivos - $projetosAtivosSemAnt) / $projetosAtivosSemAnt) * 100
    : 0;

$projetosConcluidosSemAnt = $pdo->query("
    SELECT COUNT(*) FROM projetos WHERE status='concluido'
    AND YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)-1
")->fetchColumn();
$totalSemAnt = $projetosAtivosSemAnt + $projetosConcluidosSemAnt;
$taxaSemAnt  = $totalSemAnt > 0 ? ($projetosConcluidosSemAnt / $totalSemAnt) * 100 : 0;
$crescimentoTaxa = $taxaConclusao - $taxaSemAnt;

// Projetos recentes
$projetosArray = $pdo->query("
    SELECT * FROM projetos ORDER BY created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Atividades recentes
$atividadesArray = $pdo->query("
    SELECT * FROM atividades ORDER BY created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <div class="logo-text">ne<span>x</span>us</div>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Principal</div>
    <a href="dashboard.php" class="nav-item active">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      <span>Visão Geral</span>
    </a>
    <a href="projetos.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 1 3 3v1a3 3 0 0 1-6 0V5a3 3 0 0 1 3-3z"/><path d="M19 9a7 7 0 1 1-14 0"/><path d="M12 12v9M8 21h8"/></svg>
      <span>Projetos</span>
      <span class="nav-badge"><?= $projetosAtivos ?></span>
    </a>
    <a href="pedidos.php" class="nav-item">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <span>Pedidos</span>
    </a>
    <a href="analise.php" class="nav-item">
      <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      <span>Análise</span>
    </a>
    <a href="clientes.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Clientes</span>
    </a>
    <a href="mensagens.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <span>Mensagens</span>
      <span class="nav-badge"><?= $mensagens ?></span>
    </a>
    <div class="sidebar-section-label" style="margin-top:.6rem">Financeiro</div>
    <a href="faturamento.php" class="nav-item">
      <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      <span>Faturamento</span>
    </a>
    <a href="pagamentos.php" class="nav-item">
      <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      <span>Pagamentos</span>
    </a>
    <div class="sidebar-section-label" style="margin-top:.6rem">Sistema</div>
    <a href="configuracoes.php" class="nav-item">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
      <span>Configurações</span>
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">NX</div>
      <div>
        <div class="user-name">Nexus</div>
        <div class="user-role">Admin · Nexus</div>
      </div>
    </div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Visão <em>Geral</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Resumo</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Buscar...">
      </div>
      <button class="topbar-btn">
        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="dot"></span>
      </button>
      <button class="topbar-btn">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
      </button>
    </div>
  </header>

  <div class="content">

    <div class="period-bar reveal">
      <div class="period-tabs">
        <button class="period-tab">Hoje</button>
        <button class="period-tab active">Semana</button>
        <button class="period-tab">Mês</button>
        <button class="period-tab">Ano</button>
      </div>
      <div class="date-range">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <?= date('d M', strtotime('monday this week')) ?> — <?= date('d M, Y', strtotime('sunday this week')) ?>
      </div>
    </div>

    <div class="kpi-grid">

      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-label">Receita Total</div>
        <div class="kpi-value"><sup>R$</sup><?= number_format($receitaTotal, 2, ',', '.') ?></div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem">
          <span class="kpi-change <?= $crescimentoReceita >= 0 ? 'up' : 'down' ?>">
            <?php if ($crescimentoReceita >= 0): ?><svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg><?php else: ?><svg viewBox="0 0 24 24"><polyline points="18 9 12 15 6 9"/></svg><?php endif; ?>
            <?= ($crescimentoReceita >= 0 ? '+' : '') . number_format($crescimentoReceita, 1, ',', '.') ?>%
          </span>
          <span class="kpi-period">vs semana anterior</span>
        </div>
      </div>

      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="kpi-label">Novos Clientes</div>
        <div class="kpi-value"><?= $novosClientes ?></div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem">
          <span class="kpi-change <?= $crescimentoClientes >= 0 ? 'up' : 'down' ?>">
            <?php if ($crescimentoClientes >= 0): ?><svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg><?php else: ?><svg viewBox="0 0 24 24"><polyline points="18 9 12 15 6 9"/></svg><?php endif; ?>
            <?= ($crescimentoClientes >= 0 ? '+' : '') . number_format($crescimentoClientes, 1, ',', '.') ?>%
          </span>
          <span class="kpi-period">vs semana anterior</span>
        </div>
      </div>

      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        <div class="kpi-label">Projetos Ativos</div>
        <div class="kpi-value"><?= $projetosAtivos ?></div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem">
          <span class="kpi-change <?= $crescimentoProjetos >= 0 ? 'up' : 'down' ?>">
            <?php if ($crescimentoProjetos >= 0): ?><svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg><?php else: ?><svg viewBox="0 0 24 24"><polyline points="18 9 12 15 6 9"/></svg><?php endif; ?>
            <?= ($crescimentoProjetos >= 0 ? '+' : '') . number_format($crescimentoProjetos, 1, ',', '.') ?>%
          </span>
          <span class="kpi-period">vs semana anterior</span>
        </div>
      </div>

      <div class="kpi-card reveal">
        <div class="kpi-icon"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-label">Taxa de Conclusão</div>
        <div class="kpi-value"><?= number_format($taxaConclusao, 0) ?><sup style="font-size:1.2rem;color:var(--text-dim)">%</sup></div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem">
          <span class="kpi-change <?= $crescimentoTaxa >= 0 ? 'up' : 'down' ?>">
            <?php if ($crescimentoTaxa >= 0): ?><svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg><?php else: ?><svg viewBox="0 0 24 24"><polyline points="18 9 12 15 6 9"/></svg><?php endif; ?>
            <?= ($crescimentoTaxa >= 0 ? '+' : '') . number_format($crescimentoTaxa, 1, ',', '.') ?>%
          </span>
          <span class="kpi-period">vs semana anterior</span>
        </div>
      </div>

    </div>

    <!-- CHARTS -->
    <div class="charts-grid reveal">
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <div class="chart-title">Receita por Período</div>
            <div class="chart-sub">Comparativo semanal · em R$ mil</div>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><div class="legend-dot red"></div> Atual</div>
            <div class="legend-item"><div class="legend-dot white"></div> Anterior</div>
          </div>
        </div>
        <div class="chart-svg-wrap">
          <svg viewBox="0 0 520 200" style="height:200px">
            <defs>
              <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#E01B24" stop-opacity=".25"/>
                <stop offset="100%" stop-color="#E01B24" stop-opacity="0"/>
              </linearGradient>
              <linearGradient id="areaGrad2" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#F5F5F5" stop-opacity=".1"/>
                <stop offset="100%" stop-color="#F5F5F5" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <line x1="0" y1="160" x2="520" y2="160" stroke="rgba(255,255,255,.05)" stroke-width="1"/>
            <line x1="0" y1="120" x2="520" y2="120" stroke="rgba(255,255,255,.05)" stroke-width="1"/>
            <line x1="0" y1="80"  x2="520" y2="80"  stroke="rgba(255,255,255,.05)" stroke-width="1"/>
            <line x1="0" y1="40"  x2="520" y2="40"  stroke="rgba(255,255,255,.05)" stroke-width="1"/>
            <text x="0" y="163" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">0</text>
            <text x="0" y="123" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">40k</text>
            <text x="0" y="83"  font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">80k</text>
            <text x="0" y="43"  font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">120k</text>
            <path d="M 30 110 C 100 125 240 85 380 100 C 420 88 460 95 500 90 L 500 160 L 30 160 Z" fill="url(#areaGrad2)"/>
            <path d="M 30 110 C 100 125 240 85 380 100 C 420 88 460 95 500 90" fill="none" stroke="rgba(245,245,245,.2)" stroke-width="1.5" stroke-dasharray="4 4"/>
            <path d="M 30 130 C 100 90 240 60 380 65 C 420 55 460 45 500 35 L 500 160 L 30 160 Z" fill="url(#areaGrad)"/>
            <path d="M 30 130 C 100 90 240 60 380 65 C 420 55 460 45 500 35" fill="none" stroke="#E01B24" stroke-width="2"/>
            <circle cx="30"  cy="130" r="4" fill="#E01B24"/>
            <circle cx="170" cy="75"  r="4" fill="#E01B24"/>
            <circle cx="310" cy="85"  r="4" fill="#E01B24"/>
            <circle cx="380" cy="65"  r="4" fill="#E01B24"/>
            <circle cx="500" cy="35"  r="5" fill="#E01B24" stroke="#080808" stroke-width="2"/>
            <text x="22"  y="180" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">Seg</text>
            <text x="162" y="180" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">Ter</text>
            <text x="302" y="180" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">Qua</text>
            <text x="372" y="180" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">Qui</text>
            <text x="492" y="180" font-size="10" fill="rgba(245,245,245,.3)" font-family="DM Sans">Sex</text>
          </svg>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-header">
          <div>
            <div class="chart-title">Pacotes</div>
            <div class="chart-sub">Distribuição por tipo de serviço</div>
          </div>
        </div>
        <?php
          // Distribuição real por pacote
          $pacotes = $pdo->query("
              SELECT pacote, COUNT(*) as qtd FROM contatos
              WHERE pacote IS NOT NULL AND pacote != ''
              GROUP BY pacote ORDER BY qtd DESC
          ")->fetchAll(PDO::FETCH_ASSOC);
          $totalPacotes = array_sum(array_column($pacotes, 'qtd')) ?: 1;
          $cores = ['#E01B24','rgba(245,245,245,.35)','rgba(224,27,36,.5)','rgba(245,245,245,.15)'];
        ?>
        <div class="donut-wrap">
          <svg class="donut-svg" viewBox="0 0 160 160">
            <circle cx="80" cy="80" r="55" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="22"/>
            <?php
              $offset = 0;
              $circ = 2 * M_PI * 55; // ≈ 345.6
              foreach ($pacotes as $i => $pk):
                $pct = $pk['qtd'] / $totalPacotes;
                $dash = $pct * $circ;
            ?>
            <circle cx="80" cy="80" r="55" fill="none"
                    stroke="<?= $cores[$i % count($cores)] ?>"
                    stroke-width="22"
                    stroke-dasharray="<?= round($dash,1) ?> <?= round($circ,1) ?>"
                    stroke-dashoffset="-<?= round($offset,1) ?>"
                    transform="rotate(-90 80 80)"/>
            <?php $offset += $dash; endforeach; ?>
            <?php if (empty($pacotes)): ?>
              <circle cx="80" cy="80" r="55" fill="none" stroke="rgba(255,255,255,.07)" stroke-width="22"/>
            <?php endif; ?>
            <text x="80" y="76" text-anchor="middle" font-family="MuseoModerno" font-size="22" font-weight="900" fill="white"><?= count($pacotes) ?></text>
            <text x="80" y="92" text-anchor="middle" font-family="DM Sans" font-size="10" fill="rgba(245,245,245,.4)">pacotes</text>
          </svg>
          <div class="donut-legend" style="padding:0 .5rem">
            <?php foreach ($pacotes as $i => $pk): ?>
            <div class="donut-item">
              <div class="donut-item-left">
                <div class="donut-item-dot" style="background:<?= $cores[$i % count($cores)] ?>"></div>
                <span class="donut-item-label"><?= htmlspecialchars($pk['pacote']) ?></span>
              </div>
              <span class="donut-item-val"><?= round($pk['qtd'] / $totalPacotes * 100) ?>%</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($pacotes)): ?>
              <div style="opacity:.3;font-size:.8rem;padding:.5rem">Sem dados ainda</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- BOTTOM -->
    <div class="bottom-grid reveal" style="margin-top:1.2rem">

      <div class="table-card">
        <div class="table-head">
          <div class="table-head-title">Projetos Recentes</div>
          <a href="projetos.php" class="table-head-link">Ver todos →</a>
        </div>
        <table>
          <thead>
            <tr>
              <th>Projeto</th>
              <th>Cliente</th>
              <th>Status</th>
              <th>Prazo</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($projetosArray)): ?>
              <tr><td colspan="5" style="text-align:center;padding:2rem;opacity:.4">Nenhum projeto cadastrado ainda</td></tr>
            <?php else: ?>
              <?php foreach ($projetosArray as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['cliente']) ?></td>
                <td><span class="status-badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                <td style="font-size:.8rem;color:var(--text-dim)"><?= $p['prazo'] ? date('d/m/Y', strtotime($p['prazo'])) : '—' ?></td>
                <td>R$ <?= number_format($p['valor'] ?? 0, 2, ',', '.') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="activity-card">
        <div class="activity-head">
          <div class="activity-head-title">Atividade Recente</div>
          <a href="#" class="table-head-link" style="font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--red);text-decoration:none;font-weight:600">Ver tudo</a>
        </div>
        <div class="activity-list">
          <?php if (empty($atividadesArray)): ?>
            <div style="text-align:center;padding:2rem;opacity:.4;font-size:.85rem">Nenhuma atividade registrada</div>
          <?php else: ?>
            <?php foreach ($atividadesArray as $a): ?>
            <div class="activity-item">
              <div class="activity-avatar">
                <?= strtoupper(substr($a['titulo'], 0, 2)) ?>
              </div>
              <div class="activity-content">
                <div class="activity-text">
                  <strong><?= htmlspecialchars($a['titulo']) ?></strong>
                  <?= $a['descricao'] ? ' · ' . htmlspecialchars($a['descricao']) : '' ?>
                </div>
                <div class="activity-time">
                  <?= date('d/m/Y H:i', strtotime($a['created_at'])) ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.period-tab').forEach(tab=>{
    tab.addEventListener('click',()=>{document.querySelectorAll('.period-tab').forEach(t=>t.classList.remove('active'));tab.classList.add('active');});
  });
  document.querySelectorAll('.nav-item').forEach(item=>{
    item.addEventListener('click',e=>{
      if(item.getAttribute('href')==='#'){e.preventDefault();}
    });
  });
  const obs=new IntersectionObserver(entries=>{entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*80);obs.unobserve(e.target);}});},{threshold:.08});
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

  // Cursor
  const cur=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
  let mx=0,my=0,rx=0,ry=0;
  document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
  (function anim(){rx+=(mx-rx)*.1;ry+=(my-ry)*.1;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(anim);})();
  document.querySelectorAll('a,button,input,select,textarea').forEach(el=>{
    el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';ring.style.borderColor='var(--red)';});
    el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';ring.style.borderColor='rgba(224,27,36,.45)';});
  });
</script>
</body>
</html>