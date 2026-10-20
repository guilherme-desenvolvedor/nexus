<?php require __DIR__ . "/navbar.php"; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS — Sobre Nós</title>
<link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@400;600;700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
<style>
  :root {
    --black: #080808;
    --red: #E01B24;
    --red-dark: #a01318;
    --white: #F5F5F5;
    --gray: #111111;
    --gray2: #1e1e1e;
  }
  *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body {
    background: var(--black);
    color: var(--white);
    font-family: 'DM Sans', sans-serif;
    overflow-x: hidden;
    cursor: none;
  }

  /* CURSOR */
  .cursor { width: 10px; height: 10px; background: var(--red); border-radius: 50%; position: fixed; top:0; left:0; pointer-events:none; z-index:9999; transition:transform .15s; }
  .cursor-ring { width:34px; height:34px; border:1px solid rgba(224,27,36,.45); border-radius:50%; position:fixed; top:0; left:0; pointer-events:none; z-index:9998; transition:all .25s ease; }

  /* NAV */
  nav { position:fixed; top:0; width:100%; padding:1.2rem 4rem; display:flex; align-items:center; justify-content:space-between; z-index:100; background:transparent; }
  .nav-logo img { height: 54px; width: auto; background: transparent; }
  nav ul { list-style:none; display:flex; gap:2.5rem; }
  nav ul a { color:var(--white); text-decoration:none; font-size:.82rem; letter-spacing:.12em; text-transform:uppercase; font-weight:500; opacity:.6; transition:opacity .3s,color .3s; }
  nav ul a:hover, nav ul a.active { opacity:1; color:var(--red); }

  /* HERO */
  .hero { min-height:100vh; display:flex; align-items:center; padding:0 5rem; position:relative; overflow:hidden; }
  .hero-bg { position:absolute; inset:0; background:radial-gradient(ellipse at 65% 50%,rgba(224,27,36,.09) 0%,transparent 65%); }
  .hero-grid { position:absolute; inset:0; background-image:linear-gradient(rgba(224,27,36,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(224,27,36,.04) 1px,transparent 1px); background-size:55px 55px; mask-image:radial-gradient(ellipse at center,black 25%,transparent 75%); }
  .hero-content { position:relative; z-index:1; max-width:950px; }
  .hero-tag { display:inline-flex; align-items:center; gap:.7rem; background:rgba(224,27,36,.08); border:1px solid rgba(224,27,36,.25); padding:.35rem 1rem; border-radius:2px; font-size:.7rem; letter-spacing:.22em; text-transform:uppercase; color:var(--red); margin-bottom:2rem; animation:fadeUp .7s ease forwards; opacity:0; }
  .hero-tag::before { content:''; width:5px; height:5px; background:var(--red); border-radius:50%; animation:pulse 1.5s infinite; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.2} }
  h1.hero-h { font-family:'MuseoModerno',sans-serif; font-size:clamp(5rem,13vw,12rem); line-height:.88; letter-spacing:.02em; animation:fadeUp .7s .18s ease forwards; opacity:0; }
  h1.hero-h .red { color:var(--red); }
  .hero-sub { margin-top:2rem; font-size:1.05rem; font-weight:300; color:rgba(245,245,245,.6); max-width:480px; line-height:1.75; animation:fadeUp .7s .35s ease forwards; opacity:0; }
  .hero-scroll { position:absolute; right:5rem; bottom:3rem; display:flex; flex-direction:column; align-items:center; gap:.8rem; animation:fadeUp .7s .5s ease forwards; opacity:0; }
  .hero-scroll span { writing-mode:vertical-rl; font-size:.65rem; letter-spacing:.22em; text-transform:uppercase; color:rgba(245,245,245,.35); }
  .hero-scroll-bar { width:1px; height:60px; background:linear-gradient(to bottom,var(--red),transparent); }
  @keyframes fadeUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

  section { padding:7rem 5rem; }
  .section-label { display:flex; align-items:center; gap:1rem; font-size:.68rem; letter-spacing:.25em; text-transform:uppercase; color:var(--red); margin-bottom:4rem; }
  .section-label::after { content:''; width:50px; height:1px; background:var(--red); opacity:.4; }

  /* ORIGEM */
  .origem-grid { display:grid; grid-template-columns:1fr 1fr; gap:7rem; align-items:center; }
  .origem-grid h2 { font-family:'MuseoModerno',sans-serif; font-size:clamp(2.2rem,4.5vw,3.8rem); font-weight:800; line-height:1.1; margin-bottom:0; }
  .origem-grid h2 em { font-style:normal; color:var(--red); }
  .origem-right p { font-size:1rem; line-height:1.88; color:rgba(245,245,245,.68); margin-bottom:1.4rem; }
  .origem-right p:last-child { margin-bottom:0; }

  /* NOME */
  .nome-section { background:var(--gray); position:relative; overflow:hidden; }
  .nome-bg-text { position:absolute; right:-1rem; top:50%; transform:translateY(-50%); font-family:'MuseoModerno',sans-serif; font-size:18rem; color:rgba(224,27,36,.035); line-height:1; pointer-events:none; user-select:none; }
  .nome-grid { display:grid; grid-template-columns:1fr 1.1fr; gap:5rem; align-items:center; position:relative; }
  .nome-word { font-family:'MuseoModerno',sans-serif; font-size:clamp(7rem,16vw,14rem); line-height:.9; position:relative; display:inline-block; color:#FF1A1A; }
  .nome-word::after { display:none; }
  .nome-latin { display:inline-block; font-size:.68rem; letter-spacing:.22em; text-transform:uppercase; color:rgba(245,245,245,.35); margin-bottom:1.2rem; }
  .nome-content h3 { font-family:'DM Sans',sans-serif; font-size:1.85rem; font-weight:600; margin-bottom:1rem; letter-spacing:-.02em; }
  .nome-content p { font-size:1rem; line-height:1.85; color:rgba(245,245,245,.68); }
  .nome-pills { display:flex; gap:.7rem; flex-wrap:wrap; margin-top:1.8rem; }
  .nome-pill { padding:.35rem .9rem; border:1px solid rgba(224,27,36,.35); color:var(--red); font-size:.75rem; letter-spacing:.1em; text-transform:uppercase; border-radius:2px; }

  /* TEAM PHOTO */
  .team-photo-wrap { position:relative; width:100%; height:56vh; min-height:320px; overflow:hidden; border-radius:3px; background:var(--gray2); display:flex; align-items:center; justify-content:center; }
  .team-placeholder { display:flex; flex-direction:column; align-items:center; gap:1rem; color:rgba(245,245,245,.2); }
  .team-placeholder svg { opacity:.2; }
  .team-placeholder span { font-size:.75rem; letter-spacing:.18em; text-transform:uppercase; }
  .team-badge { position:absolute; bottom:2rem; left:2rem; background:rgba(8,8,8,.88); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.07); padding:1rem 1.8rem; border-radius:3px; }
  .team-badge h3 { font-family:'DM Sans',sans-serif; font-size:1.05rem; font-weight:600; }
  .team-badge p { font-size:.78rem; color:rgba(245,245,245,.45); margin-top:.2rem; }
  .team-badge span { color:var(--red); }

  /* MEMBERS */
  .members-section { padding-top:5rem; }
  .members-section > h2 { font-family:'DM Sans',sans-serif; font-size:clamp(1.8rem,3.5vw,2.8rem); font-weight:700; margin-bottom:3rem; letter-spacing:-.02em; }

  .members-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1.2rem; }
  .member-card { background:var(--gray); border:1px solid rgba(255,255,255,.05); border-radius:3px; overflow:hidden; transition:transform .3s,border-color .3s; position:relative; }
  .member-card:hover { transform:translateY(-7px); border-color:rgba(224,27,36,.38); }
  .member-photo { width:100%; height:270px; overflow:hidden; position:relative; background:var(--gray2); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:.6rem; color:rgba(245,245,245,.18); }
  .member-photo svg { opacity:.2; }
  .member-photo span { font-size:.62rem; letter-spacing:.15em; text-transform:uppercase; }
  .member-photo img { width:100%; height:100%; object-fit:cover; display:block; }
  .member-num { position:absolute; top:.8rem; right:.9rem; font-family:'MuseoModerno',sans-serif; font-size:1.4rem; color:var(--red); opacity:.55; }
  .member-info { padding:1.4rem; }
  .member-info h3 { font-family:'DM Sans',sans-serif; font-weight:700; font-size:1rem; margin-bottom:.25rem; letter-spacing:-.01em; }
  .member-role { font-size:.7rem; letter-spacing:.12em; text-transform:uppercase; color:var(--red); margin-bottom:.7rem; }
  .member-divider { width:28px; height:2px; background:var(--red); margin:.6rem 0; transition:width .3s; }
  .member-card:hover .member-divider { width:55px; }
  .member-desc { font-size:.85rem; line-height:1.65; color:rgba(245,245,245,.5); }
  .member-socials { display:flex; gap:.6rem; margin-top:1rem; }
  .social-link { width:30px; height:30px; border:1px solid rgba(255,255,255,.1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:rgba(245,245,245,.4); transition:border-color .3s,color .3s,background .3s; }
  .social-link:hover { border-color:var(--red); color:var(--red); background:rgba(224,27,36,.06); }
  .social-link svg { width:14px; height:14px; stroke:currentColor; fill:none; }
  .member-photo.no-photo::after { content:"FOTO AQUI"; font-size:.62rem; letter-spacing:.15em; text-transform:uppercase; color:rgba(245,245,245,.2); }

  /* VALUES */
  .values-section { background:var(--gray); }
  .values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.05); }
  .value-item { background:var(--gray); padding:2.8rem 2.2rem; transition:background .3s; }
  .value-item:hover { background:rgba(224,27,36,.04); }
  .value-num { font-family:'MuseoModerno',sans-serif; font-size:3rem; color:var(--red); opacity:.2; line-height:1; margin-bottom:.9rem; }
  .value-item h3 { font-family:'DM Sans',sans-serif; font-size:1.05rem; font-weight:600; margin-bottom:.6rem; letter-spacing:-.01em; }
  .value-item p { font-size:.9rem; line-height:1.7; color:rgba(245,245,245,.5); }

  /* CTA */
  .cta-section { text-align:center; position:relative; overflow:hidden; }
  .cta-section::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at center,rgba(224,27,36,.09) 0%,transparent 70%); pointer-events:none; }
  .cta-section h2 { font-family:'MuseoModerno',sans-serif; font-size:clamp(3rem,9vw,8rem); line-height:.95; margin-bottom:1.5rem; position:relative; }
  .cta-section p { font-size:1rem; color:rgba(245,245,245,.55); max-width:480px; margin:0 auto 2.8rem; line-height:1.75; }
  .btn-red { display:inline-block; background:var(--red); color:var(--white); padding:.95rem 2.8rem; font-family:'DM Sans',sans-serif; font-weight:700; font-size:.82rem; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; border-radius:2px; transition:background .3s,transform .3s; }
  .btn-red:hover { background:var(--red-dark); transform:translateY(-2px); }

  /* FOOTER */
  .footer-bar { border-top:1px solid rgba(255,255,255,.06); padding:1.8rem 5rem; display:flex; align-items:center; justify-content:space-between; }
  .footer-logo img { height:40px; background: transparent; }
  .footer-bar p { font-size:.75rem; color:rgba(245,245,245,.28); letter-spacing:.05em; }

  /* REVEAL */
  .reveal { opacity:0; transform:translateY(38px); transition:opacity .7s ease,transform .7s ease; }
  .reveal.visible { opacity:1; transform:translateY(0); }

  @media(max-width:900px){
    nav { padding:1.4rem 2rem; }
    nav ul { display:none; }
    section { padding:5rem 2rem; }
    .hero { padding:0 2rem; }
    .hero-scroll { display:none; }
    .origem-grid,.nome-grid { grid-template-columns:1fr; gap:3rem; }
    .values-grid { grid-template-columns:1fr; }
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
    <div class="hero-tag">Sobre a Empresa</div>
    <h1 class="hero-h">QUEM<br><span class="red">SOMOS</span></h1>
    <p class="hero-sub">A história de sete estudantes que decidiram fazer diferente — e construíram uma empresa de verdade antes de se formar.</p>
  </div>
  <div class="hero-scroll">
    <span>Descobrir</span>
    <div class="hero-scroll-bar"></div>
  </div>
</section>

<!-- ORIGEM -->
<section>
  <div class="section-label">Nossa Origem</div>
  <div class="origem-grid reveal">
    <div>
      <h2>Nascemos dentro de uma <em>sala de aula.</em></h2>
    </div>
    <div class="origem-right">
      <p>O que era um projeto de TCC se tornou algo maior do que esperávamos — uma empresa construída por estudantes que queriam provar, na prática, que é possível entregar algo de qualidade ainda antes de se formar.</p>
      <p>Tudo partiu de uma percepção que tivemos ao longo do curso: o mercado de criação de sites está cheio de soluções genéricas, sem identidade, que pouco têm a ver com o negócio do cliente. Vimos nisso uma oportunidade.</p>
      <p>Se íamos criar um projeto para o TCC, que fosse algo que fizesse sentido de verdade — uma empresa que resolvesse um problema real, do jeito certo. A NEXUS é a prova de que projetos acadêmicos podem ter a seriedade e a qualidade de uma empresa de verdade.</p>
    </div>
  </div>
</section>

<!-- NOME NEXUS -->
<section class="nome-section">
  <div class="section-label">O Nome</div>
  <div class="nome-bg-text">NEXUS</div>
  <div class="nome-grid reveal">
    <div class="nome-word" data-text="NEXUS">NEXUS</div>
    <div class="nome-content">
      <span class="nome-latin">Do latim</span>
      <h3>Elo. Conexão. Vínculo.</h3>
      <p>O nome NEXUS veio naturalmente da nossa ideia central. Nexus, do latim, significa elo, conexão, vínculo. Era exatamente o que queríamos representar: a ponte entre um negócio e sua presença digital, entre o que o cliente precisa e o que o mundo vai ver.</p>
      <div class="nome-pills">
        <span class="nome-pill">Elo</span>
        <span class="nome-pill">Conexão</span>
        <span class="nome-pill">Vínculo</span>
        <span class="nome-pill">Presença Digital</span>
      </div>
    </div>
  </div>
</section>

<!-- FOTO DA EQUIPE -->
<section style="padding-bottom:0;">
  <div class="section-label">Nossa Equipe</div>
  <div class="team-photo-wrap reveal">
    <div class="team-placeholder">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
        <polyline points="21 15 16 10 5 21"/>
      </svg>
      <span>Substitua por &lt;img src="foto-equipe.jpg"&gt;</span>
    </div>
    <div class="team-badge">
      <h3>A equipe NEXUS</h3>
      <p>Unidos por um objetivo <span>comum.</span></p>
    </div>
  </div>
</section>

<!-- MEMBROS -->
<section class="members-section">
  <h2>Conheça cada um de nós</h2>

  <div class="members-grid">

    <!-- 01 Guilherme -->
    <div class="member-card reveal">
      <div class="member-photo">
        <img src="imagens/equipe/guilherme-pereira-gestor.jpeg" alt="Guilherme Pereira" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">01</div>
      </div>
      <div class="member-info">
        <h3>Guilherme</h3>
        <div class="member-role">Diretor Executivo &amp; Arquiteto de Software</div>
        <div class="member-divider"></div>
        <p class="member-desc">Responsável pela visão estratégica da NEXUS e pela arquitetura técnica dos projetos. Lidera a equipe garantindo que cada entrega tenha qualidade e identidade de verdade.</p>
        <div class="member-socials">
          <a href="https://linkedin.com/in/seu-perfil" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href="https://www.instagram.com/devguilhermep?igsh=Y2t6dGU4ZDlwbjAx" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
          <a href="https://seu-portfolio.com" target="_blank" class="social-link" title="Portfólio">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- 02 João -->
    <div class="member-card reveal">
      <div class="member-photo">
        <img src="imagens/equipe/joao-vitor-dev.jpeg" alt="João Vitor" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">02</div>
      </div>
      <div class="member-info">
        <h3>João</h3>
        <div class="member-role">Diretor de Projetos &amp; Desenvolvedor de Software</div>
        <div class="member-divider"></div>
        <p class="member-desc">Coordena o fluxo de projetos do início ao fim e atua diretamente no desenvolvimento de software, garantindo prazos, qualidade e comunicação com os clientes.</p>
        <div class="member-socials">
          <a href=" https://www.instagram.com/sant.anna_j?igsh=Y3F2ajdpN3M2bWxz" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href="https://instagram.com/seu-perfil" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
          <a href="https://seu-portfolio.com" target="_blank" class="social-link" title="Portfólio">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- 03 Arthur -->
    <div class="member-card reveal">
      <div class="member-photo">
        <img src="imagens/equipe/arthur-pereira-dev.jpeg" alt="Arthur Pereira" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">03</div>
      </div>
      <div class="member-info">
        <h3>Arthur</h3>
        <div class="member-role">Desenvolvedor de Software</div>
        <div class="member-divider"></div>
        <p class="member-desc">Atua no desenvolvimento de soluções web, transformando requisitos em código limpo, funcional e escalável para cada cliente da NEXUS.</p>
        <div class="member-socials">
          <a href="https://linkedin.com/in/seu-perfil" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href=" https://www.instagram.com/oarthur_pereira?igsh=dmY3YmVidmx1NXVr" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
          <a href="https://seu-portfolio.com" target="_blank" class="social-link" title="Portfólio">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </a>
        </div>
      
      </div>
    </div>

    <!-- 04 Bernardo -->
    <div class="member-card reveal">
      <div class="member-photo">
        <img src="imagens/equipe/bernardo-costa-analista.jpeg" alt="Bernardo Costa" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">04</div>
      </div>
      <div class="member-info">
        <h3>Bernardo</h3>
        <div class="member-role">Arquiteto de Software</div>
        <div class="member-divider"></div>
        <p class="member-desc">Define as estruturas técnicas que sustentam cada projeto — garantindo que os sistemas sejam sólidos, seguros e preparados para crescer junto com o negócio do cliente.</p>
            <div class="member-socials">
          <a href="https://linkedin.com/in/seu-perfil" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href="https://www.instagram.com/kaiser_polar?igsh=MTJ5czltdmxlNW1rag==" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- 05 Sophia -->
    <div class="member-card reveal">
      <div class="member-photo">
        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        <img src="imagens/equipe/sophia-design.jpeg" alt="Sophia" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">05</div>
      </div>
      <div class="member-info">
        <h3>Sophia</h3>
        <div class="member-role">Web Designer</div>
        <div class="member-divider"></div>
        <p class="member-desc">Cria a identidade visual e experiência de cada site que a NEXUS entrega. Transforma briefings em interfaces que comunicam, encantam e representam o cliente de verdade.</p>
      <div class="member-socials">
          <a href="https://linkedin.com/in/seu-perfil" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href="https://www.instagram.com/sophh.rc?igsh=eDlvdGlvZWZ4djg5" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- 06 Manu -->
    <div class="member-card reveal">
      <div class="member-photo">
        <img src="imagens/equipe/emanulle-teixeira-marketing.jpeg" alt="Emanulle Teixeira" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">06</div>
      </div>
      <div class="member-info">
        <h3>Emanuelle</h3>
        <div class="member-role">Marketing</div>
        <div class="member-divider"></div>
        <p class="member-desc">Responsável pela estratégia de marketing da NEXUS. Cuida do posicionamento da marca, das campanhas e de como comunicamos o valor do nosso trabalho para o mundo.</p>
            <div class="member-socials">
          <a href="https://linkedin.com/in/seu-perfil" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href=" https://www.instagram.com/manutxr.s?igsh=MXc1eWVveHhoYzNjNA==" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- 07 Danillo -->
    <div class="member-card reveal">
      <div class="member-photo">
        <img src="imagens/equipe/danillo-fernandes-marketing.jpeg" alt="Danillo Fernandes" loading="lazy" onerror="this.style.display='none';this.parentNode.classList.add('no-photo')">
        <div class="member-num">07</div>
      </div>
      <div class="member-info">
        <h3>Danillo</h3>
        <div class="member-role">Analista de Social Media</div>
        <div class="member-divider"></div>
        <p class="member-desc">Gerencia a presença digital da NEXUS nas redes sociais — criando conteúdo, engajando a audiência e construindo a comunidade em torno da marca.</p>
            <div class="member-socials">
          <a href="https://linkedin.com/in/seu-perfil" target="_blank" class="social-link" title="LinkedIn">
            <svg viewBox="0 0 24 24" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
          <a href="https://www.instagram.com/fernandsd__?igsh=MWQxbXF6MzJkN2F1bw==" target="_blank" class="social-link" title="Instagram">
            <svg viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
          </a>
        </div>
      </div>
    </div>
  </div><!-- /.members-grid -->
</section>
<!-- VALORES -->
<section class="values-section">
  <div class="section-label">O Que Nos Guia</div>
  <div class="values-grid reveal">
    <div class="value-item">
      <div class="value-num">01</div>
      <h3>Identidade Real</h3>
      <p>Cada site que entregamos reflete o negócio do cliente — não um template genérico. Qualidade com identidade, sempre.</p>
    </div>
    <div class="value-item">
      <div class="value-num">02</div>
      <h3>Seriedade Acadêmica</h3>
      <p>Somos estudantes, mas operamos como uma empresa de verdade. Prazo, responsabilidade e entrega acima de tudo.</p>
    </div>
    <div class="value-item">
      <div class="value-num">03</div>
      <h3>Conexão Verdadeira</h3>
      <p>Criamos pontes entre negócios e o mundo digital — este é o nosso propósito desde o primeiro dia.</p>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2>VAMOS<br>CONSTRUIR<br><span style="color:var(--red)">JUNTOS?</span></h2>
  <p>Se você quer uma presença digital com identidade de verdade, a NEXUS está pronta para ser o seu elo com o mundo.</p>
  <a href="index.php#contato" class="btn-red">Fale com a Equipe</a>
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
  let mx=0,my=0,rx=0,ry=0;
  document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cur.style.left=mx-5+'px';cur.style.top=my-5+'px';});
  function anim(){rx+=(mx-rx)*.1;ry+=(my-ry)*.1;ring.style.left=rx-17+'px';ring.style.top=ry-17+'px';requestAnimationFrame(anim);}
  anim();
  document.querySelectorAll('a,button').forEach(el=>{
    el.addEventListener('mouseenter',()=>{cur.style.transform='scale(2)';ring.style.transform='scale(1.4)';ring.style.borderColor='var(--red)';});
    el.addEventListener('mouseleave',()=>{cur.style.transform='scale(1)';ring.style.transform='scale(1)';ring.style.borderColor='rgba(224,27,36,.45)';});
  });

  // Scroll reveal
  const obs=new IntersectionObserver(entries=>{entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*70);obs.unobserve(e.target);}});},{threshold:.1});
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

</script>
</body>
</html>
