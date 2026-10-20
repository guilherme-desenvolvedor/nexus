<?php
session_start();
require __DIR__ . "/../config.php";

$error = "";

if ($_POST) {
    $email = $_POST["email"];
    $pass  = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin["password"])) {
        session_regenerate_id(true);
        $_SESSION["admin"] = $admin["id"];
        header("Location: dashboard.php");
        exit;
    }

    $error = "Credenciais inválidas";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS — Painel Admin</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<style>
:root {
  --black:#080808; --red:#E01B24; --red-dark:#a01318;
  --red-vivid:#FF1A1A; --white:#F5F5F5;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
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

/* CARD */
.page-wrap{position:relative;z-index:1;width:100%;display:flex;align-items:center;justify-content:center;padding:2rem 1.5rem;}
.card{background:rgba(17,17,17,.93);border:1px solid rgba(255,255,255,.08);border-radius:4px;backdrop-filter:blur(14px);box-shadow:0 0 60px rgba(0,0,0,.6),0 0 0 1px rgba(224,27,36,.05);position:relative;overflow:hidden;width:100%;max-width:420px;padding:3rem 2.6rem;animation:fadeUp .7s ease forwards;opacity:0;}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--red),transparent);}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

/* LOGO */
.card-logo{display:flex;align-items:center;justify-content:center;gap:.6rem;margin-bottom:1.8rem;}
.logo-icon{width:34px;height:34px;background:var(--red);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.logo-icon svg{width:20px;height:20px;}
.logo-text{font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:1.3rem;letter-spacing:.02em;}
.logo-text span{color:var(--red);}

/* STATUS TAG */
.status-tag{display:flex;align-items:center;gap:.6rem;background:rgba(224,27,36,.08);border:1px solid rgba(224,27,36,.2);padding:.28rem .85rem;border-radius:2px;font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--red);width:fit-content;margin:0 auto 1.6rem;}
.status-tag::before{content:'';width:5px;height:5px;background:var(--red);border-radius:50%;animation:blink 1.5s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}

/* TÍTULOS */
.card-title{font-family:'MuseoModerno',sans-serif;font-size:1.65rem;font-weight:900;line-height:1.1;margin-bottom:.5rem;text-align:center;}
.card-title em{font-style:normal;color:var(--red-vivid);}
.card-sub{font-size:.85rem;color:rgba(245,245,245,.45);text-align:center;margin-bottom:2.2rem;line-height:1.6;}

/* FORM */
.form-group{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.1rem;}
.form-group label{font-size:.66rem;letter-spacing:.18em;text-transform:uppercase;color:rgba(245,245,245,.38);}
.input-wrap{position:relative;}
.input-wrap svg{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:rgba(245,245,245,.3);fill:none;pointer-events:none;}
.form-group input{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:2px;padding:.8rem 1rem .8rem 2.6rem;color:var(--white);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;width:100%;transition:border-color .3s,background .3s;cursor:none;}
.form-group input:focus{border-color:rgba(224,27,36,.5);background:rgba(224,27,36,.04);}
.form-group input::placeholder{color:rgba(245,245,245,.18);}
.form-group input:-webkit-autofill,
.form-group input:-webkit-autofill:hover,
.form-group input:-webkit-autofill:focus{
  -webkit-text-fill-color:var(--white);
  -webkit-box-shadow:0 0 0 1000px #161616 inset;
  box-shadow:0 0 0 1000px #161616 inset;
  transition:background-color 9999s ease-in-out 0s;
}
.toggle-pass{position:absolute;right:.9rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:none;display:flex;padding:0;}
.toggle-pass svg{width:15px;height:15px;stroke:rgba(245,245,245,.3);fill:none;transition:stroke .3s;}
.toggle-pass:hover svg{stroke:rgba(245,245,245,.6);}

.btn-submit{width:100%;background:var(--red);color:var(--white);border:none;padding:1rem;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.85rem;letter-spacing:.18em;text-transform:uppercase;border-radius:2px;cursor:none;transition:background .3s,transform .3s;margin-top:.5rem;}
.btn-submit:hover{background:var(--red-dark);transform:translateY(-2px);}

.error-box{display:flex;align-items:center;gap:.6rem;background:rgba(224,27,36,.08);border:1px solid rgba(224,27,36,.25);border-radius:2px;padding:.75rem .9rem;margin-bottom:1.3rem;font-size:.8rem;color:#ff8a8a;}
.error-box svg{width:16px;height:16px;stroke:#ff8a8a;fill:none;flex-shrink:0;}

.back-site{display:block;text-align:center;margin-top:1.8rem;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(245,245,245,.3);text-decoration:none;transition:color .3s;}
.back-site:hover{color:var(--red);}
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<div class="bg-grid"></div>
<div class="bg-glow"></div>

<div class="page-wrap">
  <div class="card">
    <div class="card-logo">
      <div class="logo-icon">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="logo-text">ne<span>x</span>us</div>
    </div>

    <div class="status-tag" style="display:flex;margin-left:auto;margin-right:auto;">Área Restrita</div>

    <h1 class="card-title">Painel <em>Administrativo</em></h1>
    <p class="card-sub">Faça login com suas credenciais de administrador para acessar o painel.</p>

    <?php if ($error): ?>
    <div class="error-box">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label for="email">E-mail</label>
        <div class="input-wrap">
          <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6l-10 7L2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
          <input type="email" id="email" name="email" placeholder="admin@nexus.com" required autocomplete="username">
        </div>
      </div>

      <div class="form-group">
        <label for="password">Senha</label>
        <div class="input-wrap">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password" style="padding-right:2.6rem;">
          <button type="button" class="toggle-pass" id="togglePass">
            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit">Entrar</button>
    </form>

    <a href="../../Front-end/index.php" class="back-site">← Voltar ao site</a>
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

const togglePass=document.getElementById('togglePass'),passInput=document.getElementById('password');
togglePass.addEventListener('click',()=>{
  const show=passInput.type==='password';
  passInput.type=show?'text':'password';
  togglePass.innerHTML=show
    ?'<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
    :'<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
});
</script>
</body>
</html>
