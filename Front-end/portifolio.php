<?php require __DIR__ . "/navbar.php"; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS — Portfólio</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<style>
  :root {
    --black: #080808;
    --red: #E01B24;
    --red-dark: #a01318;
    --red-vivid: #FF1A1A;
    --white: #F5F5F5;
    --gray: #111111;
    --gray2: #1a1a1a;
    --gray3: #222222;
  }

  *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
  html { scroll-behavior:smooth; }
  body {
    background: var(--black);
    color: var(--white);
    font-family: 'DM Sans', sans-serif;
    overflow-x: hidden;
    cursor: none;
  }

  /* CURSOR */
  .cursor { width:10px; height:10px; background:var(--red); border-radius:50%; position:fixed; top:0; left:0; pointer-events:none; z-index:9999; transition:transform .15s; }
  .cursor-ring { width:34px; height:34px; border:1px solid rgba(224,27,36,.45); border-radius:50%; position:fixed; top:0; left:0; pointer-events:none; z-index:9998; transition:all .25s ease; }

  /* NAV */
  nav { position:fixed; top:0; width:100%; padding:1.2rem 4rem; display:flex; align-items:center; justify-content:space-between; z-index:100; background:transparent; }
  .nav-logo img { height:54px; width:auto; background:transparent; }
  nav ul { list-style:none; display:flex; gap:2.5rem; }
  nav ul a { color:var(--white); text-decoration:none; font-size:.82rem; letter-spacing:.12em; text-transform:uppercase; font-weight:500; opacity:.6; transition:opacity .3s,color .3s; }
  nav ul a:hover, nav ul a.active { opacity:1; color:var(--red); }

  /* HERO */
  .hero { min-height:100vh; display:flex; align-items:center; padding:0 4rem; position:relative; overflow:hidden; }
  .hero-bg { position:absolute; inset:0; background:radial-gradient(ellipse at 70% 50%, rgba(224,27,36,.08) 0%, transparent 60%); }
  .hero-grid { position:absolute; inset:0; background-image:linear-gradient(rgba(224,27,36,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(224,27,36,.04) 1px, transparent 1px); background-size:55px 55px; mask-image:radial-gradient(ellipse at center, black 25%, transparent 75%); }
  .hero-content { position:relative; z-index:1; max-width:900px; }
  .hero-tag { display:inline-flex; align-items:center; gap:.7rem; background:rgba(224,27,36,.08); border:1px solid rgba(224,27,36,.25); padding:.35rem 1rem; border-radius:2px; font-size:.7rem; letter-spacing:.22em; text-transform:uppercase; color:var(--red); margin-bottom:2rem; animation:fadeUp .7s ease forwards; opacity:0; }
  .hero-tag::before { content:''; width:5px; height:5px; background:var(--red); border-radius:50%; animation:pulse 1.5s infinite; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.2} }
  .hero h1 { font-family:'MuseoModerno', sans-serif; font-size:clamp(4.5rem,12vw,11rem); line-height:.9; letter-spacing:.01em; animation:fadeUp .7s .15s ease forwards; opacity:0; }
  .hero h1 .red { color:var(--red-vivid); }
  .hero-sub { margin-top:1.8rem; font-size:1.05rem; font-weight:300; color:rgba(245,245,245,.6); max-width:500px; line-height:1.75; animation:fadeUp .7s .3s ease forwards; opacity:0; }
  .hero-scroll { position:absolute; right:4rem; bottom:3rem; display:flex; flex-direction:column; align-items:center; gap:.8rem; animation:fadeUp .7s .5s ease forwards; opacity:0; }
  .hero-scroll span { writing-mode:vertical-rl; font-size:.65rem; letter-spacing:.22em; text-transform:uppercase; color:rgba(245,245,245,.35); }
  .hero-scroll-bar { width:1px; height:60px; background:linear-gradient(to bottom, var(--red), transparent); }
  @keyframes fadeUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

  /* SECTION */
  section { padding:7rem 4rem; }
  .section-label { display:flex; align-items:center; gap:1rem; font-size:.68rem; letter-spacing:.25em; text-transform:uppercase; color:var(--red); margin-bottom:4rem; }
  .section-label::after { content:''; width:50px; height:1px; background:var(--red); opacity:.4; }

  /* FILTROS */
  .filters { display:flex; gap:.8rem; flex-wrap:wrap; margin-bottom:3.5rem; }
  .filter-btn { padding:.45rem 1.2rem; border:1px solid rgba(255,255,255,.1); background:transparent; color:rgba(245,245,245,.5); font-family:'DM Sans', sans-serif; font-size:.75rem; letter-spacing:.12em; text-transform:uppercase; cursor:none; border-radius:2px; transition:all .3s; }
  .filter-btn:hover, .filter-btn.active { border-color:var(--red); color:var(--white); background:rgba(224,27,36,.08); }
  .filter-btn.active { background:var(--red); color:var(--white); border-color:var(--red); }

  /* GRID PROJETOS */
  .projects-grid { display:grid; grid-template-columns:repeat(12, 1fr); gap:1.2rem; }

  /* CARDS */
  .project-card {
    position:relative; overflow:hidden; border-radius:3px; cursor:none;
    background:var(--gray2); border:1px solid rgba(255,255,255,.05);
    transition:border-color .3s, transform .4s cubic-bezier(.25,.46,.45,.94);
  }
  .project-card:hover { border-color:rgba(224,27,36,.35); transform:translateY(-5px); }
  .project-card:hover .card-overlay { opacity:1; }
  .project-card:hover .card-img { transform:scale(1.04); }
  .project-card:hover .card-arrow { transform:translate(0,0); opacity:1; }

  /* TAMANHOS DO GRID */
  .card-lg { grid-column: span 8; height:480px; }
  .card-sm { grid-column: span 4; height:480px; }
  .card-md { grid-column: span 6; height:360px; }
  .card-wide { grid-column: span 12; height:300px; }
  .card-third { grid-column: span 4; height:360px; }
  .card-feature { grid-column: span 12; height:460px; }

  /* Card que é link de verdade (projeto publicado) */
  a.project-card { text-decoration:none; color:inherit; display:block; }

  /* Card com screenshot real */
  .card-img.has-photo { padding:0; }
  .card-img.has-photo img { width:100%; height:100%; object-fit:cover; object-position:top center; display:block; }
  .photo-fallback { position:absolute; inset:0; display:none; flex-direction:column; align-items:center; justify-content:center; gap:.6rem; text-align:center; padding:2rem; }
  .card-img.photo-missing .photo-fallback { display:flex; }
  .photo-fallback strong { font-family:'MuseoModerno', sans-serif; font-weight:900; font-size:clamp(1.8rem,4vw,2.6rem); letter-spacing:.06em; color:rgba(245,245,245,.9); }
  .photo-fallback small { font-size:.72rem; letter-spacing:.22em; text-transform:uppercase; color:var(--red); }

  .card-img-wrap { width:100%; height:100%; overflow:hidden; position:relative; }
  .card-img {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    transition:transform .6s cubic-bezier(.25,.46,.45,.94);
    position:relative;
  }

  /* Placeholder art para cada projeto */
  .card-img.proj-1 { background:linear-gradient(135deg, #0d0d0d 0%, #1a0505 50%, #0d0d0d 100%); }
  .card-img.proj-2 { background:linear-gradient(135deg, #080808 0%, #1c0808 60%, #080808 100%); }
  .card-img.proj-3 { background:linear-gradient(160deg, #0a0a0a 0%, #180303 70%, #0a0a0a 100%); }
  .card-img.proj-4 { background:linear-gradient(120deg, #0c0c0c 0%, #200808 55%, #0c0c0c 100%); }
  .card-img.proj-5 { background:linear-gradient(145deg, #090909 0%, #1a0404 65%, #090909 100%); }
  .card-img.proj-6 { background:linear-gradient(135deg, #0b0b0b 0%, #1e0606 50%, #0b0b0b 100%); }

  .card-mock {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    flex-direction:column; gap:1.2rem;
  }
  .mock-browser {
    width:75%; background:rgba(255,255,255,.04); border-radius:6px;
    border:1px solid rgba(255,255,255,.08); overflow:hidden;
  }
  .mock-bar {
    height:22px; background:rgba(255,255,255,.06);
    display:flex; align-items:center; padding:0 .6rem; gap:.35rem;
  }
  .mock-dot { width:6px; height:6px; border-radius:50%; }
  .mock-dot:nth-child(1) { background:#ff5f57; }
  .mock-dot:nth-child(2) { background:#febc2e; }
  .mock-dot:nth-child(3) { background:#28c840; }
  .mock-content { padding:1rem; }
  .mock-line { height:6px; border-radius:3px; background:rgba(255,255,255,.08); margin-bottom:.5rem; }
  .mock-line.red { background:rgba(224,27,36,.35); width:40%; }
  .mock-line.short { width:60%; }
  .mock-line.shorter { width:80%; }
  .mock-hero-block { height:50px; background:rgba(224,27,36,.12); border-radius:3px; margin-bottom:.5rem; }
  .mock-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:.4rem; }
  .mock-grid-item { height:30px; background:rgba(255,255,255,.05); border-radius:2px; }
  .mock-phone {
    width:22%; background:rgba(255,255,255,.04); border-radius:10px;
    border:1px solid rgba(255,255,255,.08); padding:.5rem; position:absolute; right:8%; bottom:10%;
  }
  .mock-phone-line { height:5px; border-radius:3px; background:rgba(255,255,255,.07); margin-bottom:.35rem; }
  .mock-phone-block { height:35px; background:rgba(224,27,36,.1); border-radius:3px; }

  .card-overlay {
    position:absolute; inset:0; opacity:0;
    background:linear-gradient(to top, rgba(8,8,8,.95) 0%, rgba(8,8,8,.5) 50%, transparent 100%);
    transition:opacity .4s ease;
    display:flex; flex-direction:column; justify-content:flex-end; padding:2rem;
  }
  .card-tags { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:.8rem; }
  .card-tag { padding:.2rem .6rem; background:rgba(224,27,36,.2); border:1px solid rgba(224,27,36,.3); color:var(--red); font-size:.65rem; letter-spacing:.12em; text-transform:uppercase; border-radius:2px; }
  .card-title { font-family:'MuseoModerno', sans-serif; font-size:1.4rem; font-weight:700; margin-bottom:.4rem; }
  .card-desc { font-size:.85rem; color:rgba(245,245,245,.6); line-height:1.55; }
  .card-arrow {
    position:absolute; top:1.5rem; right:1.5rem;
    width:40px; height:40px; background:var(--red); border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    transform:translate(8px,-8px); opacity:0;
    transition:all .3s ease;
  }
  .card-arrow svg { width:16px; height:16px; stroke:white; }

  /* NÚMEROS */
  .stats-section { background:var(--gray); }
  .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.05); }
  .stat-item { background:var(--gray); padding:3rem 2.5rem; text-align:center; transition:background .3s; }
  .stat-item:hover { background:rgba(224,27,36,.04); }
  .stat-num { font-family:'MuseoModerno', sans-serif; font-size:clamp(2.8rem,5vw,4.5rem); font-weight:900; color:var(--red-vivid); line-height:1; margin-bottom:.5rem; }
  .stat-label { font-size:.8rem; letter-spacing:.15em; text-transform:uppercase; color:rgba(245,245,245,.45); }

  /* PROCESSO */
  .process-section { }
  .process-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; }
  .process-item { padding:2.5rem 2rem; border:1px solid rgba(255,255,255,.06); border-radius:3px; position:relative; transition:border-color .3s, background .3s; }
  .process-item:hover { border-color:rgba(224,27,36,.3); background:rgba(224,27,36,.03); }
  .process-num { font-family:'MuseoModerno', sans-serif; font-size:4rem; font-weight:900; color:rgba(224,27,36,.15); line-height:1; margin-bottom:1.2rem; }
  .process-item h3 { font-family:'MuseoModerno', sans-serif; font-size:1.1rem; font-weight:700; margin-bottom:.7rem; }
  .process-item p { font-size:.9rem; line-height:1.7; color:rgba(245,245,245,.5); }
  .process-connector { position:absolute; top:3.5rem; right:-1.1rem; width:1.5rem; height:1px; background:rgba(224,27,36,.3); z-index:1; }
  .process-item:last-child .process-connector { display:none; }

  /* DEPOIMENTOS */
  .testimonials-section { background:var(--gray); }
  .testi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.2rem; }
  .testi-card { background:var(--black); border:1px solid rgba(255,255,255,.06); border-radius:3px; padding:2.2rem; transition:border-color .3s; }
  .testi-card:hover { border-color:rgba(224,27,36,.25); }
  .testi-quote { font-size:2.5rem; color:var(--red); font-family:'MuseoModerno', sans-serif; line-height:1; margin-bottom:1rem; opacity:.6; }
  .testi-text { font-size:.95rem; line-height:1.75; color:rgba(245,245,245,.65); margin-bottom:1.5rem; font-style:italic; }
  .testi-author { display:flex; align-items:center; gap:.9rem; }
  .testi-avatar { width:40px; height:40px; border-radius:50%; background:rgba(224,27,36,.15); border:1px solid rgba(224,27,36,.3); display:flex; align-items:center; justify-content:center; font-family:'MuseoModerno', sans-serif; font-weight:700; color:var(--red); font-size:.85rem; }
  .testi-name { font-size:.9rem; font-weight:600; }
  .testi-role { font-size:.75rem; color:rgba(245,245,245,.4); }

  /* CTA */
  .cta-section { text-align:center; position:relative; overflow:hidden; }
  .cta-section::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at center, rgba(224,27,36,.09) 0%, transparent 70%); pointer-events:none; }
  .cta-section h2 { font-family:'MuseoModerno', sans-serif; font-size:clamp(3rem,9vw,8rem); line-height:.95; margin-bottom:1.5rem; position:relative; }
  .cta-section p { font-size:1rem; color:rgba(245,245,245,.55); max-width:480px; margin:0 auto 2.8rem; line-height:1.75; }
  .btn-red { display:inline-block; background:var(--red); color:var(--white); padding:.95rem 2.8rem; font-family:'DM Sans', sans-serif; font-weight:700; font-size:.82rem; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; border-radius:2px; transition:background .3s, transform .3s; }
  .btn-red:hover { background:var(--red-dark); transform:translateY(-2px); }
  .btn-outline { display:inline-block; background:transparent; color:var(--white); padding:.95rem 2.8rem; font-family:'DM Sans', sans-serif; font-weight:700; font-size:.82rem; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; border-radius:2px; border:1px solid rgba(255,255,255,.2); transition:all .3s; margin-left:1rem; }
  .btn-outline:hover { border-color:var(--red); color:var(--red); }

  /* FOOTER */
  .footer-bar { border-top:1px solid rgba(255,255,255,.06); padding:1.8rem 4rem; display:flex; align-items:center; justify-content:space-between; }
  .footer-logo img { height:40px; background:transparent; }
  .footer-bar p { font-size:.75rem; color:rgba(245,245,245,.28); letter-spacing:.05em; }

  /* REVEAL */
  .reveal { opacity:0; transform:translateY(38px); transition:opacity .7s ease, transform .7s ease; }
  .reveal.visible { opacity:1; transform:translateY(0); }

  @media(max-width:900px){
    nav { padding:1.2rem 2rem; }
    nav ul { display:none; }
    section { padding:5rem 2rem; }
    .hero { padding:0 2rem; }
    .hero-scroll { display:none; }
    .card-lg, .card-sm, .card-md, .card-third { grid-column:span 12; height:280px; }
    .card-wide, .card-feature { grid-column:span 12; height:220px; }
    .stats-grid, .process-grid, .testi-grid { grid-template-columns:1fr 1fr; }
    .footer-bar { flex-direction:column; gap:1rem; text-align:center; padding:1.8rem 2rem; }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>


<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">
    <div class="hero-tag">Nosso Trabalho</div>
    <h1>NOSSO<br><span class="red">PORTFÓLIO</span></h1>
    <p class="hero-sub">Cada projeto é uma conexão construída. Veja como transformamos negócios reais em presenças digitais que ficam na memória.</p>
  </div>
  <div class="hero-scroll">
    <span>Explorar</span>
    <div class="hero-scroll-bar"></div>
  </div>
</section>

<!-- PROJETOS -->
<section>
  <div class="section-label">Projetos</div>

  <div class="filters reveal">
    <button class="filter-btn active" onclick="filterProjects('all', this)">Todos</button>
    <button class="filter-btn" onclick="filterProjects('landing', this)">Landing Page</button>
    <button class="filter-btn" onclick="filterProjects('ecommerce', this)">E-commerce</button>
    <button class="filter-btn" onclick="filterProjects('institucional', this)">Institucional</button>
    <button class="filter-btn" onclick="filterProjects('dashboard', this)">Dashboard</button>
  </div>

  <div class="projects-grid reveal" id="projectsGrid">

    <!-- NUBETECH - projeto real, publicado (link externo) -->
    <a class="project-card card-feature" data-cat="institucional" href="https://anubetech.netlify.app/" target="_blank" rel="noopener">
      <div class="card-img-wrap">
        <div class="card-img has-photo proj-1">
          <img src="imagens/portfolio/nubetech.jpg" alt="Site da NubeTech" loading="lazy"
               onerror="this.remove();this.parentNode.classList.add('photo-missing')">
          <div class="photo-fallback">
            <strong>NUBETECH</strong>
            <small>anubetech.netlify.app</small>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Institucional</span>
          <span class="card-tag">Projeto Real</span>
          <span class="card-tag">No Ar</span>
        </div>
        <div class="card-title">NubeTech — Tecnologia &amp; Jogos</div>
        <div class="card-desc">Site institucional da NubeTech, projeto anterior de um integrante da equipe NEXUS. Publicado e no ar — clique para visitar.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </a>

    <!-- Projeto 1 - GRANDE -->
    <div class="project-card card-lg" data-cat="landing">
      <div class="card-img-wrap">
        <div class="card-img proj-1">
          <div class="card-mock">
            <div class="mock-browser">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div class="mock-hero-block"></div>
                <div class="mock-line red"></div>
                <div class="mock-line short"></div>
                <div class="mock-line shorter"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Landing Page</span>
          <span class="card-tag">UI/UX</span>
        </div>
        <div class="card-title">Studio Arquitetura Moderna</div>
        <div class="card-desc">Landing page premium para escritório de arquitetura com galeria de projetos e formulário de orçamento integrado.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 2 - PEQUENO -->
    <div class="project-card card-sm" data-cat="ecommerce">
      <div class="card-img-wrap">
        <div class="card-img proj-2">
          <div class="card-mock">
            <div class="mock-browser" style="width:80%">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div class="mock-grid">
                  <div class="mock-grid-item"></div>
                  <div class="mock-grid-item" style="background:rgba(224,27,36,.2)"></div>
                  <div class="mock-grid-item"></div>
                </div>
                <div style="height:.4rem"></div>
                <div class="mock-line red"></div>
                <div class="mock-line shorter"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">E-commerce</span>
          <span class="card-tag">Dev</span>
        </div>
        <div class="card-title">Loja Moda Urbana</div>
        <div class="card-desc">Loja virtual completa com integração de pagamento, carrinho e painel do vendedor.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 3 - MÉDIO -->
    <div class="project-card card-md" data-cat="institucional">
      <div class="card-img-wrap">
        <div class="card-img proj-3">
          <div class="card-mock">
            <div class="mock-browser" style="width:78%">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div class="mock-line red" style="width:50%; height:8px; margin-bottom:.7rem"></div>
                <div class="mock-line shorter"></div>
                <div class="mock-line short"></div>
                <div style="height:.4rem"></div>
                <div class="mock-grid">
                  <div class="mock-grid-item"></div>
                  <div class="mock-grid-item"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Institucional</span>
          <span class="card-tag">Branding</span>
        </div>
        <div class="card-title">Clínica Saúde Total</div>
        <div class="card-desc">Site institucional para clínica médica com sistema de agendamento online integrado.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 4 - MÉDIO -->
    <div class="project-card card-md" data-cat="dashboard">
      <div class="card-img-wrap">
        <div class="card-img proj-4">
          <div class="card-mock">
            <div class="mock-browser" style="width:78%">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div style="display:flex; gap:.4rem; margin-bottom:.5rem">
                  <div style="flex:1; height:30px; background:rgba(224,27,36,.15); border-radius:3px"></div>
                  <div style="flex:1; height:30px; background:rgba(255,255,255,.05); border-radius:3px"></div>
                  <div style="flex:1; height:30px; background:rgba(255,255,255,.05); border-radius:3px"></div>
                </div>
                <div class="mock-line shorter"></div>
                <div class="mock-line short"></div>
                <div style="height:25px; background:linear-gradient(to right, rgba(224,27,36,.3) 60%, transparent 60%); border-radius:3px; margin-top:.4rem"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Dashboard</span>
          <span class="card-tag">Dev</span>
        </div>
        <div class="card-title">Painel Analytics Pro</div>
        <div class="card-desc">Dashboard de gestão com visualização de dados em tempo real e relatórios exportáveis.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 5 - WIDE -->
    <div class="project-card card-wide" data-cat="landing">
      <div class="card-img-wrap">
        <div class="card-img proj-5" style="flex-direction:row; gap:2rem; align-items:center; justify-content:center; padding:2rem">
          <div class="mock-browser" style="width:45%; flex-shrink:0">
            <div class="mock-bar">
              <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
            </div>
            <div class="mock-content">
              <div class="mock-hero-block" style="height:35px"></div>
              <div class="mock-line red" style="margin-top:.5rem"></div>
              <div class="mock-line shorter"></div>
            </div>
          </div>
          <div style="flex:1; max-width:30%">
            <div class="mock-line shorter" style="margin-bottom:.5rem; height:7px"></div>
            <div class="mock-line short" style="margin-bottom:.5rem; height:5px"></div>
            <div class="mock-line" style="margin-bottom:.5rem; height:5px"></div>
            <div style="height:28px; background:rgba(224,27,36,.2); border-radius:2px; width:60%; margin-top:.8rem"></div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Landing Page</span>
          <span class="card-tag">UI/UX</span>
          <span class="card-tag">Animações</span>
        </div>
        <div class="card-title">Restaurante Dom Sabor — Site Completo com Reservas Online</div>
        <div class="card-desc">Presença digital completa para restaurante premium com cardápio interativo e sistema de reservas.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 6 -->
    <div class="project-card card-third" data-cat="ecommerce">
      <div class="card-img-wrap">
        <div class="card-img proj-6">
          <div class="card-mock">
            <div class="mock-browser" style="width:80%">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div class="mock-line red" style="width:70%; height:7px; margin-bottom:.6rem"></div>
                <div class="mock-grid">
                  <div class="mock-grid-item" style="background:rgba(224,27,36,.18)"></div>
                  <div class="mock-grid-item"></div>
                  <div class="mock-grid-item"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">E-commerce</span>
        </div>
        <div class="card-title">Pet Shop Online</div>
        <div class="card-desc">Loja virtual especializada para pet shop com filtros avançados e área do cliente.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 7 -->
    <div class="project-card card-third" data-cat="institucional">
      <div class="card-img-wrap">
        <div class="card-img proj-1">
          <div class="card-mock">
            <div class="mock-browser" style="width:80%">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div class="mock-line shorter" style="height:7px; margin-bottom:.6rem"></div>
                <div class="mock-hero-block" style="height:35px"></div>
                <div class="mock-line red" style="width:50%; margin-top:.5rem"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Institucional</span>
        </div>
        <div class="card-title">Escritório Jurídico Silva</div>
        <div class="card-desc">Site sóbrio e profissional para escritório de advocacia com área de publicações.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

    <!-- Projeto 8 -->
    <div class="project-card card-third" data-cat="dashboard">
      <div class="card-img-wrap">
        <div class="card-img proj-3">
          <div class="card-mock">
            <div class="mock-browser" style="width:80%">
              <div class="mock-bar">
                <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
              </div>
              <div class="mock-content">
                <div style="display:flex; gap:.3rem; margin-bottom:.5rem; align-items:flex-end">
                  <div style="width:20%; height:35px; background:rgba(224,27,36,.3); border-radius:2px 2px 0 0"></div>
                  <div style="width:20%; height:25px; background:rgba(255,255,255,.1); border-radius:2px 2px 0 0"></div>
                  <div style="width:20%; height:40px; background:rgba(224,27,36,.2); border-radius:2px 2px 0 0"></div>
                  <div style="width:20%; height:18px; background:rgba(255,255,255,.08); border-radius:2px 2px 0 0"></div>
                  <div style="width:20%; height:30px; background:rgba(224,27,36,.15); border-radius:2px 2px 0 0"></div>
                </div>
                <div class="mock-line short"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-overlay">
        <div class="card-tags">
          <span class="card-tag">Dashboard</span>
          <span class="card-tag">Charts</span>
        </div>
        <div class="card-title">FinTrack — Gestão Financeira</div>
        <div class="card-desc">Painel financeiro com gráficos dinâmicos, metas e controle de despesas empresariais.</div>
      </div>
      <div class="card-arrow">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/></svg>
      </div>
    </div>

  </div>
</section>

<!-- STATS -->
<section class="stats-section">
  <div class="stats-grid reveal">
    <div class="stat-item">
      <div class="stat-num">12+</div>
      <div class="stat-label">Projetos Entregues</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">100%</div>
      <div class="stat-label">Clientes Satisfeitos</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">7</div>
      <div class="stat-label">Especialistas na Equipe</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">4</div>
      <div class="stat-label">Tipos de Solução</div>
    </div>
  </div>
</section>

<!-- PROCESSO -->
<section class="process-section">
  <div class="section-label">Como Trabalhamos</div>
  <div class="process-grid reveal">
    <div class="process-item">
      <div class="process-num">01</div>
      <h3>Briefing</h3>
      <p>Entendemos profundamente o seu negócio, seus objetivos e o que o seu cliente espera ver.</p>
      <div class="process-connector"></div>
    </div>
    <div class="process-item">
      <div class="process-num">02</div>
      <h3>Design</h3>
      <p>Criamos uma identidade visual única — sem templates, sem soluções genéricas. Só o que é seu.</p>
      <div class="process-connector"></div>
    </div>
    <div class="process-item">
      <div class="process-num">03</div>
      <h3>Desenvolvimento</h3>
      <p>Código limpo, performático e responsivo. Seu site funciona perfeitamente em qualquer dispositivo.</p>
      <div class="process-connector"></div>
    </div>
    <div class="process-item">
      <div class="process-num">04</div>
      <h3>Entrega</h3>
      <p>Lançamento, revisões finais e suporte pós-entrega. Estamos aqui mesmo depois do projeto concluído.</p>
    </div>
  </div>
</section>

<!-- DEPOIMENTOS -->
<section class="testimonials-section">
  <div class="section-label">O Que Dizem</div>
  <div class="testi-grid reveal">
    <div class="testi-card">
      <div class="testi-quote">"</div>
      <p class="testi-text">A NEXUS entregou muito além do que esperávamos. O site ficou exatamente com a cara da nossa marca — algo que nunca tínhamos conseguido antes.</p>
      <div class="testi-author">
        <div class="testi-avatar">R</div>
        <div>
          <div class="testi-name">Ricardo Alves</div>
          <div class="testi-role">CEO — Studio Arquitetura</div>
        </div>
      </div>
    </div>
    <div class="testi-card">
      <div class="testi-quote">"</div>
      <p class="testi-text">Processo transparente, prazo cumprido e resultado incrível. Aumentamos nossas conversões em 40% no primeiro mês.</p>
      <div class="testi-author">
        <div class="testi-avatar">M</div>
        <div>
          <div class="testi-name">Marina Costa</div>
          <div class="testi-role">Fundadora — Loja Moda Urbana</div>
        </div>
      </div>
    </div>
    <div class="testi-card">
      <div class="testi-quote">"</div>
      <p class="testi-text">Trabalhar com a NEXUS foi diferente de qualquer agência que já contratamos. São jovens, mas entregam com a seriedade de quem tem anos no mercado.</p>
      <div class="testi-author">
        <div class="testi-avatar">F</div>
        <div>
          <div class="testi-name">Felipe Rocha</div>
          <div class="testi-role">Diretor — Clínica Saúde Total</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2>SEU PROJETO<br>É O <span style="color:var(--red-vivid)">PRÓXIMO.</span></h2>
  <p>Pronto para ter uma presença digital que realmente representa o seu negócio? Vamos conversar.</p>
  <a href="index.php#contato" class="btn-red">Iniciar Projeto</a>
  <a href="sobre_nos.php" class="btn-outline">Ver Sobre Nós</a>
</section>

<!-- FOOTER -->
<footer class="footer-bar">
  <div class="footer-logo"><img src="data:image/png;base64,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" alt="NEXUS"></div>
  <p>© 2024 NEXUS. Todos os direitos reservados.</p>
  <p>Feito com propósito.</p>
</footer>

<script>
  // Cursor
  const cur = document.getElementById('cursor');
  const ring = document.getElementById('cursorRing');
  let mx=0, my=0, rx=0, ry=0;
  document.addEventListener('mousemove', e => { mx=e.clientX; my=e.clientY; cur.style.left=mx-5+'px'; cur.style.top=my-5+'px'; });
  function anim() { rx+=(mx-rx)*.1; ry+=(my-ry)*.1; ring.style.left=rx-17+'px'; ring.style.top=ry-17+'px'; requestAnimationFrame(anim); }
  anim();
  document.querySelectorAll('a,button').forEach(el => {
    el.addEventListener('mouseenter', () => { cur.style.transform='scale(2)'; ring.style.transform='scale(1.4)'; ring.style.borderColor='var(--red)'; });
    el.addEventListener('mouseleave', () => { cur.style.transform='scale(1)'; ring.style.transform='scale(1)'; ring.style.borderColor='rgba(224,27,36,.45)'; });
  });

  // Scroll reveal
  const obs = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) { setTimeout(() => e.target.classList.add('visible'), i * 80); obs.unobserve(e.target); }
    });
  }, { threshold: .1 });
  document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

  // Filter
  function filterProjects(cat, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.project-card').forEach(card => {
      if (cat === 'all' || card.dataset.cat === cat) {
        card.style.opacity = '1'; card.style.transform = ''; card.style.pointerEvents = '';
      } else {
        card.style.opacity = '0.15'; card.style.transform = 'scale(0.97)'; card.style.pointerEvents = 'none';
      }
    });
  }
</script>
</body>
</html>
