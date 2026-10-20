<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/../config.php";

// Carregar todas as settings do banco
$settingsRaw = $pdo->query("SELECT name, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Salvar settings
$sucesso = '';
$erroSenha = '';
if (($_GET['senha'] ?? '') === 'ok') {
    $sucesso = 'Senha atualizada com sucesso!';
} elseif (($_GET['senha'] ?? '') === 'erro') {
    $erroSenha = $_GET['msg'] ?? 'Não foi possível atualizar a senha.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $campos = ['empresa_nome','empresa_email','empresa_telefone','empresa_site','empresa_cnpj','empresa_endereco','notif_novo_contato','notif_pagamento','notif_projeto','auto_refresh'];
    foreach ($campos as $campo) {
        $valor = isset($_POST[$campo]) ? $_POST[$campo] : '0';
        // Upsert: atualiza se existe, insere se não existe
        $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
        $stmt->execute([$campo, $valor]);
    }
    // Recarregar
    $settingsRaw = $pdo->query("SELECT name, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $sucesso = 'Configurações salvas com sucesso!';
}

// Helper para pegar setting com fallback
function setting($key, $default='') {
    global $settingsRaw;
    return $settingsRaw[$key] ?? $default;
}

$mensagensCount = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();
$projetosAtivos = $pdo->query("SELECT COUNT(*) FROM projetos WHERE status='ativo'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>NEXUS — Configurações</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.btn-primary{background:var(--red);color:#fff;border:none;padding:.6rem 1.4rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:600;cursor:pointer;transition:opacity .2s;}
.btn-primary:hover{opacity:.85;}
.btn-secondary{background:rgba(255,255,255,.05);color:var(--text);border:1px solid rgba(255,255,255,.08);padding:.6rem 1.4rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:500;cursor:pointer;transition:background .2s;}
.btn-secondary:hover{background:rgba(255,255,255,.09);}
.config-tabs{display:flex;gap:0;background:rgba(255,255,255,.04);border-radius:12px;padding:4px;margin-bottom:1.8rem;width:fit-content;}
.config-tab{padding:.5rem 1.2rem;border-radius:9px;font-size:.82rem;font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--text-dim);transition:all .2s;}
.config-tab.active{background:rgba(224,27,36,.18);color:var(--red);}
.config-section{display:none;}
.config-section.active{display:block;}
.config-card{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:1.6rem;margin-bottom:1rem;}
.config-card-title{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:1rem;margin-bottom:.3rem;}
.config-card-sub{font-size:.78rem;color:var(--text-dim);margin-bottom:1.4rem;}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-dim);margin-bottom:.4rem;}
.form-input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.65rem 1rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;}
.form-input:focus{border-color:rgba(224,27,36,.5);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;}
.form-actions{display:flex;gap:.75rem;padding-top:.5rem;}
.toggle-row{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;border-bottom:1px solid rgba(255,255,255,.04);}
.toggle-row:last-child{border-bottom:none;}
.toggle-label{font-size:.88rem;font-weight:500;}
.toggle-desc{font-size:.75rem;color:var(--text-dim);margin-top:.1rem;}
.toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;}
.toggle input{opacity:0;width:0;height:0;}
.toggle-slider{position:absolute;inset:0;background:rgba(255,255,255,.1);border-radius:99px;cursor:pointer;transition:.3s;}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;bottom:3px;left:3px;transition:.3s;}
.toggle input:checked+.toggle-slider{background:var(--red);}
.toggle input:checked+.toggle-slider::before{transform:translateX(20px);}
.alert{padding:.9rem 1.2rem;border-radius:10px;font-size:.85rem;margin-bottom:1rem;}
.alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);color:#10b981;}
.settings-raw{background:#0d0d0d;border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:1rem;font-family:monospace;font-size:.78rem;color:var(--text-dim);margin-top:.5rem;}
.settings-raw table{width:100%;border-collapse:collapse;}
.settings-raw td{padding:.3rem .6rem;border-bottom:1px solid rgba(255,255,255,.04);}
.settings-raw tr:last-child td{border-bottom:none;}
.settings-raw .key{color:var(--red);font-weight:600;}
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
    <a href="pagamentos.php" class="nav-item"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg><span>Pagamentos</span></a>
    <div class="sidebar-section-label" style="margin-top:.6rem">Sistema</div>
    <a href="configuracoes.php" class="nav-item active"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg><span>Configurações</span></a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card"><div class="user-avatar">NX</div><div><div class="user-name">Nexus</div><div class="user-role">Admin · Nexus</div></div></div>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>Configu<em>rações</em></h1>
      <div class="topbar-breadcrumb">Dashboard / <span>Configurações</span></div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" placeholder="Buscar..."></div>
      <button class="topbar-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="dot"></span></button>
    </div>
  </header>

  <div class="content">

    <?php if ($sucesso): ?>
      <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>
    <?php if ($erroSenha): ?>
      <div class="alert" style="background:rgba(224,27,36,.12);border:1px solid rgba(224,27,36,.3);color:#ff6b6b"><?= htmlspecialchars($erroSenha) ?></div>
    <?php endif; ?>

    <div class="config-tabs">
      <button class="config-tab active" onclick="switchTab('empresa',this)">Empresa</button>
      <button class="config-tab" onclick="switchTab('notificacoes',this)">Notificações</button>
      <button class="config-tab" onclick="switchTab('sistema',this)">Sistema</button>
      <button class="config-tab" onclick="switchTab('seguranca',this)">Segurança</button>
    </div>

    <!-- EMPRESA -->
    <div class="config-section active" id="tab-empresa">
      <form method="POST">
        <div class="config-card">
          <div class="config-card-title">Dados da Empresa</div>
          <div class="config-card-sub">Informações salvas na tabela <code>settings</code></div>
          <div class="form-group"><label class="form-label">Nome da Empresa</label><input type="text" name="empresa_nome" class="form-input" value="<?= htmlspecialchars(setting('empresa_nome')) ?>" placeholder="Nexus Soluções Digitais"></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">CNPJ</label><input type="text" name="empresa_cnpj" class="form-input" value="<?= htmlspecialchars(setting('empresa_cnpj')) ?>" placeholder="00.000.000/0001-00"></div>
            <div class="form-group"><label class="form-label">Telefone</label><input type="text" name="empresa_telefone" class="form-input" value="<?= htmlspecialchars(setting('empresa_telefone')) ?>" placeholder="(11) 99999-9999"></div>
          </div>
          <div class="form-group"><label class="form-label">E-mail Comercial</label><input type="email" name="empresa_email" class="form-input" value="<?= htmlspecialchars(setting('empresa_email')) ?>" placeholder="contato@empresa.com.br"></div>
          <div class="form-group"><label class="form-label">Site</label><input type="url" name="empresa_site" class="form-input" value="<?= htmlspecialchars(setting('empresa_site')) ?>" placeholder="https://empresa.com.br"></div>
          <div class="form-group"><label class="form-label">Endereço</label><input type="text" name="empresa_endereco" class="form-input" value="<?= htmlspecialchars(setting('empresa_endereco')) ?>" placeholder="Rua das Flores, 123 — São Paulo/SP"></div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Salvar no banco</button>
          </div>
        </div>
      </form>
    </div>

    <!-- NOTIFICAÇÕES -->
    <div class="config-section" id="tab-notificacoes">
      <form method="POST">
        <div class="config-card">
          <div class="config-card-title">Preferências de Notificação</div>
          <div class="config-card-sub">Salvo na tabela <code>settings</code></div>
          <div class="toggle-row">
            <div><div class="toggle-label">Novo contato recebido</div><div class="toggle-desc">Alerta ao receber novo formulário de contato</div></div>
            <label class="toggle"><input type="checkbox" name="notif_novo_contato" value="1" <?= setting('notif_novo_contato')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label>
          </div>
          <div class="toggle-row">
            <div><div class="toggle-label">Pagamento aprovado</div><div class="toggle-desc">Alerta de confirmação de pagamentos</div></div>
            <label class="toggle"><input type="checkbox" name="notif_pagamento" value="1" <?= setting('notif_pagamento')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label>
          </div>
          <div class="toggle-row">
            <div><div class="toggle-label">Projeto concluído</div><div class="toggle-desc">Aviso ao marcar projeto como concluído</div></div>
            <label class="toggle"><input type="checkbox" name="notif_projeto" value="1" <?= setting('notif_projeto')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label>
          </div>
          <div style="margin-top:1.2rem"><button type="submit" class="btn-primary">Salvar preferências</button></div>
        </div>
      </form>
    </div>

    <!-- SISTEMA -->
    <div class="config-section" id="tab-sistema">
      <form method="POST">
        <div class="config-card">
          <div class="config-card-title">Preferências do Sistema</div>
          <div class="config-card-sub">Comportamento do dashboard</div>
          <div class="toggle-row">
            <div><div class="toggle-label">Auto-atualização</div><div class="toggle-desc">Recarrega o dashboard a cada 30 segundos</div></div>
            <label class="toggle"><input type="checkbox" name="auto_refresh" value="1" <?= setting('auto_refresh','1')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label>
          </div>
          <div style="margin-top:1.2rem"><button type="submit" class="btn-primary">Salvar</button></div>
        </div>
      </form>

      <!-- Visualizar todas as settings do banco -->
      <div class="config-card">
        <div class="config-card-title">Settings no Banco</div>
        <div class="config-card-sub">Conteúdo atual da tabela <code>settings</code></div>
        <div class="settings-raw">
          <table>
            <?php foreach ($settingsRaw as $k=>$v): ?>
            <tr><td class="key"><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars($v) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($settingsRaw)): ?><tr><td colspan="2" style="opacity:.4">Nenhuma configuração salva ainda</td></tr><?php endif; ?>
          </table>
        </div>
      </div>
    </div>

    <!-- SEGURANÇA -->
    <div class="config-section" id="tab-seguranca">
      <form method="POST" action="alterar_senha.php">
        <div class="config-card">
          <div class="config-card-title">Alterar Senha</div>
          <div class="config-card-sub">A senha é da tabela <code>admins</code>, campo <code>password</code> (hash bcrypt)</div>
          <div class="form-group"><label class="form-label">Senha Atual</label><input type="password" name="senha_atual" class="form-input" placeholder="••••••••"></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Nova Senha</label><input type="password" name="nova_senha" class="form-input" placeholder="••••••••"></div>
            <div class="form-group"><label class="form-label">Confirmar</label><input type="password" name="confirmar" class="form-input" placeholder="••••••••"></div>
          </div>
          <div class="form-actions"><button type="submit" class="btn-primary">Atualizar senha</button></div>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
function switchTab(id,el){
  document.querySelectorAll('.config-section').forEach(s=>s.classList.remove('active'));
  document.querySelectorAll('.config-tab').forEach(t=>t.classList.remove('active'));
  document.getElementById('tab-'+id).classList.add('active');
  el.classList.add('active');
}
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