<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$navCartCount = 0;
if (!empty($_SESSION['nexus_cart']) && is_array($_SESSION['nexus_cart'])) {
    $navCartCount = array_sum(array_map('intval', $_SESSION['nexus_cart']));
}
?>
<!-- NAV -->
<nav id="navbar">
  <a href="index.php" class="nav-logo"><img src="data:image/png;base64,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" alt="NEXUS"></a>
  <ul>
<li><a href="./index.php">Início</a></li>
<li><a href="./sobre_nos.php">Sobre</a></li>
<li><a href="./portifolio.php">Portifólio</a></li>
<li><a href="./index.php#contato">Contato</a></li>
  </ul>
  <div class="nav-right">
    <a href="https://wa.me/5521983867111" class="nav-cta">Falar Conosco</a>
    <a href="carrinho.php" class="nav-cart" aria-label="Meu carrinho">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <?php if ($navCartCount > 0): ?><span class="nav-cart-badge"><?php echo $navCartCount; ?></span><?php endif; ?>
    </a>
    <?php if (isset($_SESSION['nexus_user'])): ?>
      <?php
        $nome = $_SESSION['nexus_user']['nome'];
        $inicial = mb_strtoupper(mb_substr(explode(' ', $nome)[0], 0, 1));
        $primeiroNome = explode(' ', $nome)[0];
      ?>
      <div class="user-badge" id="userBadge">
        <div class="user-avatar"><?php echo $inicial; ?></div>
        <span class="user-name"><?php echo htmlspecialchars($primeiroNome); ?></span>
        <svg class="user-chevron" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        <div class="user-dropdown" id="userDropdown">
          <div class="dropdown-header">
            <div class="dropdown-avatar"><?php echo $inicial; ?></div>
            <div>
              <div class="dropdown-name"><?php echo htmlspecialchars($nome); ?></div>
              <div class="dropdown-email"><?php echo htmlspecialchars($_SESSION['nexus_user']['email']); ?></div>
            </div>
          </div>
          <div class="dropdown-sep"></div>
          <a href="Login.php" class="dropdown-item">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Meu Perfil
          </a>
          <a href="carrinho.php" class="dropdown-item">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Meu Carrinho &amp; Pedidos
          </a>
          <a href="Login.php?logout=1" class="dropdown-item dropdown-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair da Conta
          </a>
        </div>
      </div>
    <?php else: ?>
      <a href="Login.php" class="nav-cta">Login</a>
    <?php endif; ?>
  </div>
</nav>
 
<style>
  nav { position:fixed; top:0; width:100%; padding:1.2rem 4rem; display:flex; align-items:center; justify-content:space-between; z-index:100; background:transparent; transition:background .4s; }
  nav.scrolled { background:rgba(8,8,8,.95); backdrop-filter:blur(12px); border-bottom:1px solid rgba(255,255,255,.05); }
  .nav-logo img { height:54px; width:auto; }
  nav ul { list-style:none; display:flex; gap:2.5rem; }
  nav ul a { color:var(--white); text-decoration:none; font-size:.82rem; letter-spacing:.12em; text-transform:uppercase; font-weight:500; opacity:.6; transition:opacity .3s,color .3s; }
  nav ul a:hover, nav ul a.active { opacity:1; color:var(--red); }
  .nav-right { display:flex; align-items:center; gap:.8rem; }
  .nav-cta { padding:.55rem 1.4rem; border:1px solid var(--red); color:var(--red); text-decoration:none; font-size:.75rem; letter-spacing:.12em; text-transform:uppercase; font-weight:600; border-radius:2px; transition:all .3s; opacity:1 !important; }
  .nav-cta:hover { background:var(--red); color:var(--white); }

  /* CARRINHO */
  .nav-cart { position:relative; display:flex; align-items:center; justify-content:center; width:38px; height:38px; border:1px solid rgba(255,255,255,.1); border-radius:2px; color:var(--white); text-decoration:none; transition:border-color .3s,background .3s,color .3s; }
  .nav-cart svg { width:17px; height:17px; stroke:currentColor; }
  .nav-cart:hover { border-color:rgba(224,27,36,.4); background:rgba(224,27,36,.05); color:var(--red); }
  .nav-cart-badge { position:absolute; top:-7px; right:-7px; min-width:18px; height:18px; padding:0 4px; background:var(--red); color:#fff; border-radius:999px; font-size:.66rem; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid var(--black); }

  /* BADGE DO USUÁRIO */
  .user-badge { position:relative; display:flex; align-items:center; gap:.55rem; padding:.45rem .85rem .45rem .45rem; border:1px solid rgba(255,255,255,.1); border-radius:2px; cursor:pointer; transition:border-color .3s,background .3s; user-select:none; }
  .user-badge:hover { border-color:rgba(224,27,36,.4); background:rgba(224,27,36,.05); }
  .user-badge.open { border-color:rgba(224,27,36,.5); background:rgba(224,27,36,.07); }
  .user-avatar { width:26px; height:26px; border-radius:50%; background:var(--red); display:flex; align-items:center; justify-content:center; font-family:'MuseoModerno',sans-serif; font-size:.75rem; font-weight:900; color:#fff; flex-shrink:0; }
  .user-name { font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; font-weight:600; color:var(--white); }
  .user-chevron { width:12px; height:12px; stroke:rgba(245,245,245,.4); transition:transform .3s; }
  .user-badge.open .user-chevron { transform:rotate(180deg); }
 
  /* DROPDOWN */
  .user-dropdown { display:none; position:absolute; top:calc(100% + .6rem); right:0; min-width:240px; background:rgba(14,14,14,.97); border:1px solid rgba(255,255,255,.08); border-radius:4px; backdrop-filter:blur(16px); box-shadow:0 20px 60px rgba(0,0,0,.7); overflow:hidden; animation:dropDown .2s ease; }
  .user-badge.open .user-dropdown { display:block; }
  @keyframes dropDown { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
 
  .dropdown-header { display:flex; align-items:center; gap:.8rem; padding:1rem 1rem .8rem; }
  .dropdown-avatar { width:38px; height:38px; border-radius:50%; background:rgba(224,27,36,.15); border:1px solid rgba(224,27,36,.3); display:flex; align-items:center; justify-content:center; font-family:'MuseoModerno',sans-serif; font-size:1rem; font-weight:900; color:var(--red); flex-shrink:0; }
  .dropdown-name { font-size:.85rem; font-weight:600; color:var(--white); line-height:1.3; }
  .dropdown-email { font-size:.72rem; color:rgba(245,245,245,.35); letter-spacing:.02em; margin-top:.1rem; }
 
  .dropdown-sep { height:1px; background:rgba(255,255,255,.06); margin:0; }
 
  .dropdown-item { display:flex; align-items:center; gap:.65rem; padding:.75rem 1rem; font-size:.8rem; letter-spacing:.06em; text-transform:uppercase; font-weight:600; color:rgba(245,245,245,.5); text-decoration:none; transition:background .2s,color .2s; }
  .dropdown-item svg { width:14px; height:14px; stroke:currentColor; flex-shrink:0; }
  .dropdown-item:hover { background:rgba(255,255,255,.04); color:var(--white); }
  .dropdown-logout:hover { background:rgba(224,27,36,.08); color:var(--red); }
 
  /* Linha vermelha no topo do dropdown */
  .user-dropdown::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,var(--red),transparent); }
</style>
 
<script>
  // Scroll da navbar
  window.addEventListener('scroll', () => {
    document.getElementById('navbar')?.classList.toggle('scrolled', window.scrollY > 40);
  });
 
  // Toggle do dropdown do usuário
  const badge = document.getElementById('userBadge');
  if (badge) {
    badge.addEventListener('click', (e) => {
      e.stopPropagation();
      badge.classList.toggle('open');
    });
    document.addEventListener('click', () => badge.classList.remove('open'));
    document.getElementById('userDropdown')?.addEventListener('click', e => e.stopPropagation());
  }
 
  // Marca link ativo
  const links = document.querySelectorAll('nav ul a');
  links.forEach(a => {
    if (a.href === location.href || location.pathname.endsWith(a.getAttribute('href'))) {
      a.classList.add('active');
    }
  });
</script>