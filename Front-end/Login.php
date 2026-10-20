<?php
session_start();
require __DIR__ . "/../Back-end/config.php";

// ─── LOGOUT ────────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: Login.php');
    exit;
}
 
// ─── Validação de CPF ──────────────────────────────────────────────────────
function cpfValido(string $cpf): bool {
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t <= 10; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int)$cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int)$cpf[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

// ─── Estado ────────────────────────────────────────────────────────────────
$logado     = isset($_SESSION['nexus_user']);
$loginError = '';
$cadError   = '';
$activeForm = $_GET['form'] ?? ($_POST['form'] ?? 'login');

// Para onde mandar o usuário depois de entrar (?redir=carrinho)
$redir       = $_GET['redir'] ?? ($_POST['redir'] ?? '');
$destinoPos  = $redir === 'carrinho' ? 'carrinho.php' : 'index.php';
 
// ─── LOGIN ─────────────────────────────────────────────────────────────────
if (!$logado && $_SERVER['REQUEST_METHOD'] === 'POST' && $activeForm === 'login') {
    $loginInput = trim($_POST['login_input'] ?? '');
    $senha      = $_POST['senha'] ?? '';
 
    $stmt = $pdo->prepare(
        "SELECT `CPF`, `Nome Completo`, `Login`, `E-mail`, `Senha`
         FROM `cadastro`
         WHERE `Login` = ? OR `E-mail` = ?
         LIMIT 1"
    );
    $stmt->execute([$loginInput, $loginInput]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($senha, $user['Senha'])) {
        session_regenerate_id(true);
        $_SESSION['nexus_user'] = [
            'cpf'   => $user['CPF'],
            'nome'  => $user['Nome Completo'],
            'login' => $user['Login'],
            'email' => $user['E-mail'],
        ];
        header('Location: ' . $destinoPos);
        exit;
    } elseif ($user) {
        $loginError = 'Senha incorreta. Tente novamente.';
    } else {
        $loginError = 'Usuário ou e-mail não encontrado.';
    }
}
 
// ─── CADASTRO ──────────────────────────────────────────────────────────────
if (!$logado && $_SERVER['REQUEST_METHOD'] === 'POST' && $activeForm === 'cadastro') {
    $campos = [
        'nome'       => trim($_POST['nome']       ?? ''),
        'endereco'   => trim($_POST['endereco']   ?? ''),
        'cep'        => preg_replace('/\D/', '', $_POST['cep']      ?? ''),
        'num_casa'   => trim($_POST['num_casa']   ?? ''),
        'complement' => trim($_POST['complement'] ?? ''),
        'bairro'     => trim($_POST['bairro']     ?? ''),
        'telefone'   => preg_replace('/\D/', '', $_POST['telefone'] ?? ''),
        'email'      => trim($_POST['email']      ?? ''),
        'cpf'        => preg_replace('/\D/', '', $_POST['cpf']      ?? ''),
        'login_cad'  => trim($_POST['login_cad']  ?? ''),
        'senha_cad'  => $_POST['senha_cad']        ?? '',
        'senha_conf' => $_POST['senha_conf']        ?? '',
    ];
 
    $obrigatorios = array_diff_key($campos, ['complement' => '']);
    $vazio = array_filter($obrigatorios, fn($v) => $v === '');
 
    if (!empty($vazio)) {
        $cadError = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
        $cadError = 'E-mail inválido.';
    } elseif (!cpfValido($campos['cpf'])) {
        $cadError = 'CPF inválido.';
    } elseif ($campos['senha_cad'] !== $campos['senha_conf']) {
        $cadError = 'As senhas não coincidem.';
    } elseif (strlen($campos['senha_cad']) < 6) {
        $cadError = 'A senha deve ter ao menos 6 caracteres.';
    } else {
        $check = $pdo->prepare(
            "SELECT `CPF` FROM `cadastro`
             WHERE `CPF` = ? OR `E-mail` = ? OR `Login` = ? LIMIT 1"
        );
        $check->execute([$campos['cpf'], $campos['email'], $campos['login_cad']]);
 
        if ($check->fetch()) {
            $cadError = 'CPF, e-mail ou login já cadastrado.';
        } else {
            $hash = password_hash($campos['senha_cad'], PASSWORD_DEFAULT);
            $ins  = $pdo->prepare(
                "INSERT INTO `cadastro`
                 (`Nome Completo`, `Endereço`, `CEP`, `Número da casa`, `Complemento`,
                  `Bairro`, `Telefone`, `E-mail`, `CPF`, `Login`, `Senha`)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->execute([
                $campos['nome'],     $campos['endereco'], $campos['cep'],
                $campos['num_casa'], $campos['complement'], $campos['bairro'],
                $campos['telefone'], $campos['email'],    $campos['cpf'],
                $campos['login_cad'], $hash,
            ]);
            session_regenerate_id(true);
            $_SESSION['nexus_user'] = [
                'cpf'   => $campos['cpf'],
                'nome'  => $campos['nome'],
                'login' => $campos['login_cad'],
                'email' => $campos['email'],
            ];
            header('Location: ' . $destinoPos);
            exit;
        }
    }
}
 
// Primeiro nome para exibição
$primeiroNome = '';
if ($logado) {
    $partes = explode(' ', $_SESSION['nexus_user']['nome']);
    $primeiroNome = $partes[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $logado ? 'Minha Conta' : ($activeForm === 'cadastro' ? 'Cadastro' : 'Login'); ?> — NEXUS</title>
  <link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
  <style>
    :root {
      --black:#080808; --red:#E01B24; --red-dark:#a01318;
      --red-vivid:#FF1A1A; --white:#F5F5F5; --gray:#111111;
    }
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
    [hidden]{display:none!important;}
    html{scroll-behavior:smooth;}
    body{
      background:var(--black);color:var(--white);
      font-family:'DM Sans',sans-serif;
      min-height:100vh;cursor:none;
      display:flex;align-items:center;justify-content:center;
      overflow-x:hidden;
    }
 
    /* CURSOR */
    .cursor{width:10px;height:10px;background:var(--red);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9999;transition:transform .15s;}
    .cursor-ring{width:34px;height:34px;border:1px solid rgba(224,27,36,.45);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9998;transition:all .25s ease;}
 
    /* FUNDO */
    .bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(224,27,36,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(224,27,36,.04) 1px,transparent 1px);background-size:55px 55px;mask-image:radial-gradient(ellipse at center,black 25%,transparent 75%);z-index:0;}
    .bg-glow{position:fixed;inset:0;background:radial-gradient(ellipse at 50% 50%,rgba(224,27,36,.09) 0%,transparent 60%);z-index:0;}
    .bg-line-v{position:fixed;top:0;left:50%;transform:translateX(-50%);width:1px;height:100%;background:linear-gradient(to bottom,transparent,rgba(224,27,36,.12) 30%,rgba(224,27,36,.12) 70%,transparent);z-index:0;animation:lineFade 1.2s .5s forwards;opacity:0;}
    @keyframes lineFade{to{opacity:1;}}
 
    /* VOLTAR */
    .back-link{position:fixed;top:2rem;left:2rem;display:flex;align-items:center;gap:.5rem;color:rgba(245,245,245,.35);text-decoration:none;font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;z-index:10;transition:color .3s;}
    .back-link:hover{color:var(--white);}
    .back-link svg{width:14px;height:14px;stroke:currentColor;fill:none;}
 
    /* PAGE WRAP */
    .page-wrap{position:relative;z-index:1;width:100%;display:flex;align-items:flex-start;justify-content:center;padding:5rem 1.5rem 3rem;}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
 
    /* TÍTULOS */
    .card-title{font-family:'MuseoModerno',sans-serif;font-size:1.85rem;font-weight:900;line-height:1.05;margin-bottom:.5rem;text-align:center;}
    .card-title em{font-style:normal;color:var(--red-vivid);}
    .card-sub{font-size:.85rem;color:rgba(245,245,245,.45);text-align:center;margin-bottom:2.2rem;line-height:1.6;}
 
    /* AVATAR */
    .avatar-wrap{display:flex;flex-direction:column;align-items:center;gap:1rem;margin-bottom:2rem;}
    .avatar{width:80px;height:80px;border-radius:50%;background:rgba(224,27,36,.1);border:2px solid rgba(224,27,36,.3);display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-size:2rem;font-weight:900;color:var(--red);box-shadow:0 0 24px rgba(224,27,36,.15);}
    .avatar-name{font-family:'MuseoModerno',sans-serif;font-size:1.3rem;font-weight:700;}
    .avatar-email{font-size:.82rem;color:rgba(245,245,245,.35);letter-spacing:.04em;}
 
    /* DADOS DO PERFIL */
    .perfil-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem .8rem;margin-bottom:1.8rem;}
    .perfil-item{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:2px;padding:.7rem .9rem;}
    .perfil-label{font-size:.6rem;letter-spacing:.18em;text-transform:uppercase;color:rgba(245,245,245,.28);margin-bottom:.2rem;}
    .perfil-value{font-size:.88rem;color:rgba(245,245,245,.8);}
 
    /* BOTÕES DE AÇÃO NO PERFIL */
    .perfil-actions{display:flex;flex-direction:column;gap:.6rem;}
    .btn-action{display:flex;align-items:center;justify-content:center;gap:.6rem;width:100%;padding:.9rem;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.82rem;letter-spacing:.14em;text-transform:uppercase;border-radius:2px;cursor:none;text-decoration:none;transition:all .3s;}
    .btn-action svg{width:15px;height:15px;stroke:currentColor;fill:none;flex-shrink:0;}
    .btn-ir-index{background:var(--red);color:var(--white);border:1px solid var(--red);}
    .btn-ir-index:hover{background:var(--red-dark);}
    .btn-logout{background:transparent;color:rgba(245,245,245,.5);border:1px solid rgba(255,255,255,.1);}
    .btn-logout:hover{border-color:var(--red);color:var(--red);}
 
    /* FORM */
    .form-group{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1rem;}
    .form-group label{font-size:.66rem;letter-spacing:.18em;text-transform:uppercase;color:rgba(245,245,245,.38);}
    .form-group .field-hint{font-size:.7rem;color:rgba(245,245,245,.22);margin-top:.15rem;}
    .form-group input{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:2px;padding:.8rem 1rem;color:var(--white);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;width:100%;transition:border-color .3s,background .3s;cursor:none;}
    .form-group input:focus{border-color:rgba(224,27,36,.5);background:rgba(224,27,36,.04);}
    .form-group input::placeholder{color:rgba(245,245,245,.18);}
    .form-group input.valid  {border-color:rgba(74,222,128,.4);}
    .form-group input.invalid{border-color:rgba(224,27,36,.6);}
    .field-feedback{font-size:.7rem;margin-top:.2rem;display:none;}
    .field-feedback.show{display:block;}
    .field-feedback.ok{color:rgba(74,222,128,.8);}
    .field-feedback.err{color:rgba(255,100,100,.8);}
 
    /* GRIDS */
    .form-row-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    .form-row-3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:1rem;}
 
    /* FORÇA DA SENHA */
    .strength-bar{height:3px;border-radius:2px;background:rgba(255,255,255,.07);margin-top:.4rem;overflow:hidden;}
    .strength-fill{height:100%;width:0;border-radius:2px;transition:width .4s,background .4s;}
 
    /* OPÇÕES LOGIN */
    .form-options{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.6rem;}
    .check-label{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:rgba(245,245,245,.4);cursor:none;}
    .check-label input[type="checkbox"]{appearance:none;width:15px;height:15px;border:1px solid rgba(255,255,255,.15);border-radius:2px;background:transparent;cursor:none;position:relative;transition:border-color .3s,background .3s;flex-shrink:0;}
    .check-label input[type="checkbox"]:checked{background:var(--red);border-color:var(--red);}

    /* CHECKBOX TICK */
    .check-label input[type="checkbox"]:checked::after{content:'';position:absolute;left:4px;top:1px;width:4px;height:8px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg);}
    .forgot-link{font-size:.78rem;color:var(--red);text-decoration:none;}
    .forgot-link:hover{color:var(--red-vivid);}

    /* BOTÃO PRIMÁRIO */
    .btn-primary{width:100%;background:var(--red);color:#fff;border:none;padding:1rem;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.85rem;letter-spacing:.14em;text-transform:uppercase;border-radius:2px;cursor:none;display:flex;align-items:center;justify-content:center;gap:.6rem;transition:background .3s,transform .3s;}
    .btn-primary:hover{background:var(--red-dark);transform:translateY(-2px);}
    .btn-primary svg{width:15px;height:15px;stroke:currentColor;fill:none;}
    .btn-ghost{background:transparent;color:rgba(245,245,245,.5);border:1px solid rgba(255,255,255,.12);padding:1rem 1.4rem;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;border-radius:2px;cursor:none;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:border-color .3s,color .3s;}
    .btn-ghost:hover{border-color:var(--red);color:var(--red);}

    /* CARD */
    .auth-card{position:relative;width:100%;max-width:440px;background:rgba(14,14,14,.92);border:1px solid rgba(255,255,255,.07);border-radius:6px;padding:2.6rem 2.4rem;backdrop-filter:blur(14px);box-shadow:0 30px 90px rgba(0,0,0,.6);}
    .auth-card.wide{max-width:720px;}
    .auth-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--red),transparent);}

    /* LOGO */
    .brand-mark{display:flex;align-items:center;justify-content:center;gap:.6rem;margin-bottom:1.4rem;}
    .brand-mark .ring{width:34px;height:34px;border:1px solid rgba(224,27,36,.4);border-radius:50%;}
    .brand-mark .tag{background:#0d0d0d;border:1px solid rgba(255,255,255,.08);padding:.35rem .7rem;font-family:'MuseoModerno',sans-serif;font-weight:900;letter-spacing:.04em;font-size:.9rem;}
    .brand-mark .tag b{color:var(--red);font-weight:900;}

    /* PILL */
    .pill{display:inline-flex;align-items:center;gap:.45rem;margin:0 auto 1.1rem;padding:.35rem .85rem;border:1px solid rgba(224,27,36,.4);border-radius:999px;font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--red);}
    .pill::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--red);animation:blink 1.6s infinite;}
    .pill-wrap{text-align:center;}

    /* ALERTA */
    .alert{display:flex;align-items:center;gap:.55rem;padding:.8rem 1rem;border-radius:2px;font-size:.8rem;margin-bottom:1.4rem;}
    .alert svg{width:15px;height:15px;flex-shrink:0;stroke:currentColor;fill:none;}
    .alert.err{background:rgba(224,27,36,.1);border:1px solid rgba(224,27,36,.35);color:#ff8a8a;}

    /* STEPS */
    .steps{display:flex;align-items:flex-start;justify-content:space-between;margin:0 0 2rem;}
    .step{display:flex;flex-direction:column;align-items:center;gap:.45rem;flex:0 0 auto;width:64px;}
    .step-dot{width:32px;height:32px;border-radius:50%;border:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:.85rem;color:rgba(245,245,245,.4);transition:all .3s;}
    .step-label{font-size:.58rem;letter-spacing:.14em;text-transform:uppercase;color:rgba(245,245,245,.3);transition:color .3s;}
    .step.active .step-dot{border-color:var(--red);color:var(--red);box-shadow:0 0 0 4px rgba(224,27,36,.12);}
    .step.active .step-label{color:var(--red);}
    .step.done .step-dot{border-color:rgba(74,222,128,.5);color:#4ade80;}
    .step.done .step-label{color:rgba(74,222,128,.7);}
    .step-line{flex:1;height:1px;background:rgba(255,255,255,.1);margin-top:16px;}

    /* PAINÉIS */
    .step-panel{display:none;}
    .step-panel.active{display:block;animation:fadeUp .3s ease;}
    @keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
    .panel-label{font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--red);margin-bottom:1.1rem;}

    /* REVISÃO */
    .review-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem 1.4rem;margin-bottom:1.6rem;}
    .review-item .rk{font-size:.58rem;letter-spacing:.16em;text-transform:uppercase;color:rgba(245,245,245,.3);margin-bottom:.2rem;}
    .review-item .rv{font-size:.9rem;color:rgba(245,245,245,.85);word-break:break-word;}

    .nav-buttons{display:flex;gap:.8rem;margin-top:.4rem;}
    .nav-buttons .btn-primary{flex:1;}

    /* DIVISOR + TROCA */
    .divider{display:flex;align-items:center;gap:1rem;margin:1.6rem 0 1.2rem;color:rgba(245,245,245,.25);font-size:.68rem;letter-spacing:.2em;}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.08);}
    .switch-line{text-align:center;font-size:.82rem;color:rgba(245,245,245,.4);}
    .switch-line a{color:var(--red);text-decoration:none;font-weight:600;}
    .switch-line a:hover{color:var(--red-vivid);}

    @media (max-width:560px){
      .auth-card,.auth-card.wide{padding:2rem 1.3rem;}
      .form-row-2,.form-row-3,.review-grid{grid-template-columns:1fr;}
      .step{width:auto;}
      .step-label{display:none;}
    }
  </style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow"></div>
<div class="bg-line-v"></div>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<a href="index.php" class="back-link">
  <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
  Voltar
</a>

<div class="page-wrap">

<?php if ($logado): ?>
  <!-- ═══════════ PERFIL ═══════════ -->
  <div class="auth-card">
    <div class="avatar-wrap">
      <div class="avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($primeiroNome, 0, 1))) ?></div>
      <div class="avatar-name"><?= htmlspecialchars($_SESSION['nexus_user']['nome']) ?></div>
      <div class="avatar-email"><?= htmlspecialchars($_SESSION['nexus_user']['email']) ?></div>
    </div>
    <div class="perfil-grid">
      <div class="perfil-item"><div class="perfil-label">Login</div><div class="perfil-value"><?= htmlspecialchars($_SESSION['nexus_user']['login']) ?></div></div>
      <div class="perfil-item"><div class="perfil-label">CPF</div><div class="perfil-value"><?= htmlspecialchars($_SESSION['nexus_user']['cpf']) ?></div></div>
    </div>
    <div class="perfil-actions">
      <a href="carrinho.php" class="btn-action btn-ir-index">
        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        Meu carrinho &amp; pedidos
      </a>
      <a href="index.php" class="btn-action btn-logout">
        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Ir para o site
      </a>
      <a href="Login.php?logout=1" class="btn-action btn-logout">
        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair da conta
      </a>
    </div>
  </div>

<?php else: ?>
  <!-- ═══════════ LOGIN ═══════════ -->
  <div class="auth-card" id="cardLogin" <?= $activeForm === 'cadastro' ? 'hidden' : '' ?>>
    <div class="brand-mark"><span class="ring"></span><span class="tag">ne<b>x</b>us</span></div>
    <div class="pill-wrap"><span class="pill">Área Restrita</span></div>
    <h1 class="card-title">Bem-vindo de <em>volta.</em></h1>
    <p class="card-sub">Acesse o painel com seu login ou e-mail.</p>

    <?php if ($loginError): ?>
      <div class="alert err">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($loginError) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="Login.php">
      <input type="hidden" name="form" value="login">
      <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">
      <div class="form-group">
        <label>Login ou e-mail</label>
        <input type="text" name="login_input" required autocomplete="username" value="<?= htmlspecialchars($_POST['login_input'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Senha</label>
        <input type="password" name="senha" required autocomplete="current-password">
      </div>
      <div class="form-options">
        <label class="check-label"><input type="checkbox" name="lembrar"> Lembrar de mim</label>
        <a href="#" class="forgot-link">Esqueci a senha</a>
      </div>
      <button type="submit" class="btn-primary">
        Entrar
        <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    </form>

    <div class="divider">OU</div>
    <p class="switch-line">Não tem conta? <a href="#" onclick="mostrar('cadastro');return false;">Cadastre-se gratuitamente</a></p>
  </div>

  <!-- ═══════════ CADASTRO ═══════════ -->
  <div class="auth-card wide" id="cardCadastro" <?= $activeForm === 'cadastro' ? '' : 'hidden' ?>>
    <div class="brand-mark"><span class="tag">ne<b>x</b>us</span></div>
    <div class="pill-wrap"><span class="pill">Novo Cadastro</span></div>
    <h1 class="card-title">Crie sua <em>conta.</em></h1>
    <p class="card-sub">Preencha os dados abaixo para acessar a plataforma NEXUS.</p>

    <?php if ($cadError): ?>
      <div class="alert err">
        <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($cadError) ?>
      </div>
    <?php endif; ?>

    <div class="steps" id="steps">
      <div class="step active" data-step="1"><div class="step-dot">1</div><div class="step-label">Pessoal</div></div>
      <div class="step-line"></div>
      <div class="step" data-step="2"><div class="step-dot">2</div><div class="step-label">Endereço</div></div>
      <div class="step-line"></div>
      <div class="step" data-step="3"><div class="step-dot">3</div><div class="step-label">Acesso</div></div>
      <div class="step-line"></div>
      <div class="step" data-step="4"><div class="step-dot">4</div><div class="step-label">Revisão</div></div>
    </div>

    <form method="POST" action="Login.php" id="formCadastro">
      <input type="hidden" name="form" value="cadastro">
      <input type="hidden" name="redir" value="<?= htmlspecialchars($redir) ?>">

      <!-- STEP 1 -->
      <div class="step-panel active" data-panel="1">
        <div class="panel-label">Dados pessoais</div>
        <div class="form-group">
          <label>Nome completo *</label>
          <input type="text" name="nome" data-req value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label>CPF *</label>
            <input type="text" name="cpf" id="cpf" data-req inputmode="numeric" placeholder="000.000.000-00" maxlength="14">
            <div class="field-feedback" id="cpfFb"></div>
          </div>
          <div class="form-group">
            <label>Telefone *</label>
            <input type="text" name="telefone" id="telefone" data-req inputmode="numeric" placeholder="(00) 00000-0000" maxlength="15">
          </div>
        </div>
        <div class="form-group">
          <label>E-mail *</label>
          <input type="email" name="email" data-req placeholder="voce@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
      </div>

      <!-- STEP 2 -->
      <div class="step-panel" data-panel="2">
        <div class="panel-label">Endereço</div>
        <div class="form-row-3">
          <div class="form-group">
            <label>CEP *</label>
            <input type="text" name="cep" id="cep" data-req inputmode="numeric" placeholder="00000-000" maxlength="9">
            <div class="field-hint">Preenchido automaticamente</div>
          </div>
          <div class="form-group">
            <label>Número *</label>
            <input type="text" name="num_casa" data-req placeholder="42">
          </div>
          <div class="form-group">
            <label>Complemento</label>
            <input type="text" name="complement" placeholder="Apto 12">
          </div>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label>Logradouro *</label>
            <input type="text" name="endereco" id="endereco" data-req placeholder="Rua / Avenida">
          </div>
          <div class="form-group">
            <label>Bairro *</label>
            <input type="text" name="bairro" id="bairro" data-req placeholder="Centro">
          </div>
        </div>
      </div>

      <!-- STEP 3 -->
      <div class="step-panel" data-panel="3">
        <div class="panel-label">Dados de acesso</div>
        <div class="form-group">
          <label>Login *</label>
          <input type="text" name="login_cad" id="login_cad" data-req value="<?= htmlspecialchars($_POST['login_cad'] ?? '') ?>">
          <div class="field-hint">Apenas letras, números e _ (sem espaços)</div>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label>Senha *</label>
            <input type="password" name="senha_cad" id="senha_cad" data-req>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="field-feedback" id="strengthTxt"></div>
          </div>
          <div class="form-group">
            <label>Confirmar senha *</label>
            <input type="password" name="senha_conf" id="senha_conf" data-req>
            <div class="field-feedback" id="confFb"></div>
          </div>
        </div>
      </div>

      <!-- STEP 4 -->
      <div class="step-panel" data-panel="4">
        <div class="panel-label">Revise seus dados</div>
        <div class="review-grid" id="reviewGrid"></div>
      </div>

      <div class="nav-buttons">
        <button type="button" class="btn-ghost" id="btnPrev" hidden>
          <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;stroke:currentColor;fill:none"><polyline points="15 18 9 12 15 6"/></svg>
          Voltar
        </button>
        <button type="button" class="btn-primary" id="btnNext">
          Próximo
          <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
        <button type="submit" class="btn-primary" id="btnSubmit" hidden>
          Criar conta
          <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </div>
    </form>

    <div class="divider">OU</div>
    <p class="switch-line">Já tem conta? <a href="#" onclick="mostrar('login');return false;">Fazer login</a></p>
  </div>
<?php endif; ?>

</div>

<script>
  /* ---- cursor ---- */
  const cur=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
  let mx=0,my=0,rx=0,ry=0;
  addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
  (function a(){rx+=(mx-rx)*.12;ry+=(my-ry)*.12;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(a);})();
  document.querySelectorAll('a,button,input,select').forEach(el=>{
    el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';});
    el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';});
  });

  /* ---- alternar login / cadastro ---- */
  function mostrar(qual){
    const l=document.getElementById('cardLogin'), c=document.getElementById('cardCadastro');
    if(!l||!c) return;
    l.hidden = qual!=='login';
    c.hidden = qual!=='cadastro';
    history.replaceState(null,'','Login.php'+(qual==='cadastro'?'?form=cadastro':''));
  }

  /* ---- máscaras ---- */
  const soDig = v => v.replace(/\D/g,'');
  function maskCPF(v){v=soDig(v).slice(0,11);return v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');}
  function maskTel(v){v=soDig(v).slice(0,11);return v.length>10?v.replace(/(\d{2})(\d{5})(\d{4})/,'($1) $2-$3'):v.replace(/(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3').replace(/-$/,'');}
  function maskCEP(v){v=soDig(v).slice(0,8);return v.replace(/(\d{5})(\d)/,'$1-$2');}

  const cpf=document.getElementById('cpf');
  const tel=document.getElementById('telefone');
  const cep=document.getElementById('cep');
  if(cpf) cpf.addEventListener('input',()=>{cpf.value=maskCPF(cpf.value);validaCPF();});
  if(tel) tel.addEventListener('input',()=>tel.value=maskTel(tel.value));
  if(cep) cep.addEventListener('input',()=>{cep.value=maskCEP(cep.value);if(soDig(cep.value).length===8)buscaCEP();});

  /* ---- validação de CPF ---- */
  function cpfValido(cpf){
    cpf=soDig(cpf); if(cpf.length!==11||/^(\d)\1{10}$/.test(cpf))return false;
    for(let t=9;t<11;t++){let s=0;for(let i=0;i<t;i++)s+=+cpf[i]*(t+1-i);let d=(10*s%11)%10;if(+cpf[t]!==d)return false;}
    return true;
  }
  function validaCPF(){
    const fb=document.getElementById('cpfFb'); if(!cpf.value){fb.className='field-feedback';return;}
    if(cpfValido(cpf.value)){fb.textContent='CPF válido ✓';fb.className='field-feedback show ok';cpf.classList.remove('invalid');cpf.classList.add('valid');}
    else{fb.textContent='CPF inválido';fb.className='field-feedback show err';cpf.classList.remove('valid');cpf.classList.add('invalid');}
  }

  /* ---- ViaCEP ---- */
  async function buscaCEP(){
    const d=soDig(cep.value); if(d.length!==8)return;
    try{
      const r=await fetch('https://viacep.com.br/ws/'+d+'/json/');
      const j=await r.json();
      if(!j.erro){
        if(j.logradouro) document.getElementById('endereco').value=j.logradouro;
        if(j.bairro) document.getElementById('bairro').value=j.bairro;
      }
    }catch(e){}
  }

  /* ---- força da senha ---- */
  const sc=document.getElementById('senha_cad');
  if(sc) sc.addEventListener('input',()=>{
    const v=sc.value; let n=0;
    if(v.length>=6)n++; if(v.length>=10)n++; if(/[A-Z]/.test(v)&&/[a-z]/.test(v))n++; if(/\d/.test(v))n++; if(/[^A-Za-z0-9]/.test(v))n++;
    const f=document.getElementById('strengthFill'), t=document.getElementById('strengthTxt');
    const pct=[0,20,45,65,85,100][n], cor=n<=1?'#e01b24':n<=2?'#f0932b':n<=3?'#f9ca24':'#4ade80';
    const lbl=['Muito fraca','Fraca','Razoável','Boa','Forte','Excelente'][n];
    f.style.width=pct+'%'; f.style.background=cor;
    t.textContent=v?lbl:''; t.className='field-feedback'+(v?' show':'');
    t.style.color=cor;
    checkConf();
  });
  const cf=document.getElementById('senha_conf');
  function checkConf(){
    if(!cf) return; const fb=document.getElementById('confFb');
    if(!cf.value){fb.className='field-feedback';return;}
    if(cf.value===sc.value){fb.textContent='As senhas coincidem ✓';fb.className='field-feedback show ok';}
    else{fb.textContent='As senhas não coincidem';fb.className='field-feedback show err';}
  }
  if(cf) cf.addEventListener('input',checkConf);

  /* ---- wizard ---- */
  let passo=1; const TOTAL=4;
  const steps=document.querySelectorAll('#steps .step');
  const panels=document.querySelectorAll('.step-panel');
  const btnPrev=document.getElementById('btnPrev'),btnNext=document.getElementById('btnNext'),btnSubmit=document.getElementById('btnSubmit');
  const formC=document.getElementById('formCadastro');

  function render(){
    panels.forEach(p=>p.classList.toggle('active',+p.dataset.panel===passo));
    steps.forEach(s=>{
      const n=+s.dataset.step;
      s.classList.toggle('active',n===passo);
      s.classList.toggle('done',n<passo);
    });
    btnPrev.hidden = passo===1;
    btnNext.hidden = passo===TOTAL;
    btnSubmit.hidden = passo!==TOTAL;
    if(passo===TOTAL) montarRevisao();
  }
  function validaPasso(){
    const painel=document.querySelector('.step-panel[data-panel="'+passo+'"]');
    let ok=true;
    painel.querySelectorAll('[data-req]').forEach(i=>{
      if(!i.value.trim()){i.classList.add('invalid');ok=false;}
      else i.classList.remove('invalid');
    });
    if(!ok){alert('Preencha todos os campos obrigatórios deste passo.');return false;}
    if(passo===1){
      if(!cpfValido(cpf.value)){alert('CPF inválido.');return false;}
      const em=painel.querySelector('[name=email]').value;
      if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){alert('E-mail inválido.');return false;}
    }
    if(passo===3){
      if(sc.value.length<6){alert('A senha deve ter ao menos 6 caracteres.');return false;}
      if(sc.value!==cf.value){alert('As senhas não coincidem.');return false;}
    }
    return true;
  }
  function montarRevisao(){
    const g=document.getElementById('reviewGrid');
    const val=n=>formC.querySelector('[name="'+n+'"]').value || '—';
    const rows=[
      ['Nome',val('nome')],['CPF',val('cpf')],
      ['Telefone',val('telefone')],['E-mail',val('email')],
      ['CEP',val('cep')],['Endereço',val('endereco')+', '+val('num_casa')],
      ['Complemento',val('complement')||'—'],['Bairro',val('bairro')],
      ['Login',val('login_cad')],['Senha','•'.repeat(val('senha_cad').length)],
    ];
    g.innerHTML=rows.map(([k,v])=>'<div class="review-item"><div class="rk">'+k+'</div><div class="rv">'+v.replace(/</g,'&lt;')+'</div></div>').join('');
  }
  if(btnNext) btnNext.addEventListener('click',()=>{ if(validaPasso()&&passo<TOTAL){passo++;render();} });
  if(btnPrev) btnPrev.addEventListener('click',()=>{ if(passo>1){passo--;render();} });
  if(formC) formC.addEventListener('submit',e=>{ if(!validaPasso()){e.preventDefault();} });
  if(steps.length) render();
</script>
</body>
</html>
