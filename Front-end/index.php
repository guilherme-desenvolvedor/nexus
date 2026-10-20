<?php
session_start();
require __DIR__ . "/navbar.php";
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS — Desenvolvimento Web</title>
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
  }

  *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
  html { scroll-behavior:smooth; }
  body { background:var(--black); color:var(--white); font-family:'DM Sans',sans-serif; overflow-x:hidden; cursor:none; }

  /* CURSOR */
  .cursor { width:10px; height:10px; background:var(--red); border-radius:50%; position:fixed; top:0; left:0; pointer-events:none; z-index:9999; transition:transform .15s; }
  .cursor-ring { width:34px; height:34px; border:1px solid rgba(224,27,36,.45); border-radius:50%; position:fixed; top:0; left:0; pointer-events:none; z-index:9998; transition:all .25s ease; }

  /* ===================== HERO ===================== */
  .hero { min-height:100vh; display:flex; align-items:center; padding:0 4rem; position:relative; overflow:hidden; }
  .hero-bg { position:absolute; inset:0; background:radial-gradient(ellipse at 65% 45%, rgba(224,27,36,.1) 0%, transparent 58%); }
  .hero-grid { position:absolute; inset:0; background-image:linear-gradient(rgba(224,27,36,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(224,27,36,.04) 1px,transparent 1px); background-size:55px 55px; mask-image:radial-gradient(ellipse at center,black 25%,transparent 75%); }
  .hero-line { position:absolute; top:0; right:28%; width:1px; height:100%; background:linear-gradient(to bottom,transparent,rgba(224,27,36,.18) 30%,rgba(224,27,36,.18) 70%,transparent); opacity:0; animation:lineFade 1.2s .8s forwards; }
  @keyframes lineFade { to{opacity:1;} }

  .hero-content { position:relative; z-index:1; max-width:780px; }
  .hero-tag { display:inline-flex; align-items:center; gap:.7rem; background:rgba(224,27,36,.08); border:1px solid rgba(224,27,36,.25); padding:.35rem 1rem; border-radius:2px; font-size:.7rem; letter-spacing:.22em; text-transform:uppercase; color:var(--red); margin-bottom:2rem; animation:fadeUp .7s ease forwards; opacity:0; }
  .hero-tag::before { content:''; width:5px; height:5px; background:var(--red); border-radius:50%; animation:blink 1.5s infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

  .hero h1 { font-family:'MuseoModerno',sans-serif; font-size:clamp(4.2rem,11vw,10.5rem); line-height:.9; letter-spacing:.01em; animation:fadeUp .7s .15s ease forwards; opacity:0; }
  .hero h1 .red { color:var(--red-vivid); }
  .hero h1 .outline { -webkit-text-stroke:1px rgba(245,245,245,.22); color:transparent; }

  .hero-sub { margin-top:2rem; font-size:1.05rem; font-weight:300; color:rgba(245,245,245,.58); max-width:470px; line-height:1.82; animation:fadeUp .7s .3s ease forwards; opacity:0; }
  .hero-actions { display:flex; gap:1.2rem; margin-top:2.5rem; align-items:center; animation:fadeUp .7s .45s ease forwards; opacity:0; }
  .btn-red { display:inline-block; background:var(--red); color:var(--white); padding:.95rem 2.4rem; font-family:'DM Sans',sans-serif; font-weight:700; font-size:.82rem; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; border-radius:2px; transition:background .3s,transform .3s; }
  .btn-red:hover { background:var(--red-dark); transform:translateY(-2px); }
  .btn-ghost { display:inline-flex; align-items:center; gap:.6rem; color:rgba(245,245,245,.6); text-decoration:none; font-size:.88rem; font-weight:500; transition:color .3s; }
  .btn-ghost:hover { color:var(--white); }
  .btn-ghost svg { width:18px; height:18px; stroke:currentColor; transition:transform .3s; }
  .btn-ghost:hover svg { transform:translateX(4px); }

  .hero-nums { position:absolute; right:4rem; top:50%; transform:translateY(-50%); display:flex; flex-direction:column; gap:2rem; z-index:1; animation:fadeUp .7s .6s ease forwards; opacity:0; }
  .hero-num-item { text-align:right; }
  .hero-num-val { font-family:'MuseoModerno',sans-serif; font-size:2.6rem; font-weight:900; color:var(--red-vivid); line-height:1; }
  .hero-num-label { font-size:.62rem; letter-spacing:.18em; text-transform:uppercase; color:rgba(245,245,245,.3); }

  .hero-scroll { position:absolute; left:4rem; bottom:3rem; display:flex; align-items:center; gap:.8rem; animation:fadeUp .7s .65s ease forwards; opacity:0; }
  .hero-scroll span { font-size:.62rem; letter-spacing:.2em; text-transform:uppercase; color:rgba(245,245,245,.3); }
  .hero-scroll-bar { width:55px; height:1px; background:linear-gradient(to right,var(--red),transparent); }

  @keyframes fadeUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

  /* MARQUEE */
  .marquee-wrap { overflow:hidden; border-top:1px solid rgba(255,255,255,.05); border-bottom:1px solid rgba(255,255,255,.05); padding:3.5rem 0; }
  .marquee-track { display:flex; gap:2.5rem; width:max-content; animation:marquee 22s linear infinite; }
  .marquee-track:hover { animation-play-state:paused; }
  @keyframes marquee { to{transform:translateX(-50%);} }
  .m-item { font-family:'MuseoModerno',sans-serif; font-size:clamp(2.2rem,4.5vw,3.5rem); font-weight:900; white-space:nowrap; color:rgba(245,245,245,.06); display:flex; align-items:center; gap:2rem; }
  .m-item.hi { color:rgba(224,27,36,.15); }
  .m-dot { width:9px; height:9px; background:var(--red); border-radius:50%; opacity:.5; flex-shrink:0; }

  /* SEÇÃO GERAL */
  section { padding:7rem 4rem; }
  .sec-label { display:flex; align-items:center; gap:1rem; font-size:.68rem; letter-spacing:.25em; text-transform:uppercase; color:var(--red); margin-bottom:4rem; }
  .sec-label::after { content:''; width:50px; height:1px; background:var(--red); opacity:.4; }

  /* ===================== DEPOIMENTOS ===================== */
  .testi-section { background:var(--gray); }
  .testi-header { display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:end; margin-bottom:4rem; }
  .testi-header h2 { font-family:'MuseoModerno',sans-serif; font-size:clamp(2rem,4vw,3.2rem); font-weight:900; line-height:1.05; }
  .testi-header h2 em { font-style:normal; color:var(--red-vivid); }
  .testi-header p { font-size:1rem; color:rgba(245,245,245,.5); line-height:1.82; }

  .testi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.2rem; }
  .t-card { background:var(--black); border:1px solid rgba(255,255,255,.06); border-radius:3px; padding:2.4rem; transition:border-color .35s; display:flex; flex-direction:column; }
  .t-card:hover { border-color:rgba(224,27,36,.28); }
  .t-quote { font-family:'MuseoModerno',sans-serif; font-size:3rem; color:var(--red); line-height:1; margin-bottom:.8rem; opacity:.5; }
  .t-stars { display:flex; gap:.25rem; margin-bottom:1.2rem; }
  .t-star { width:14px; height:14px; fill:var(--red); }
  .t-text { font-size:.95rem; line-height:1.78; color:rgba(245,245,245,.62); font-style:italic; flex:1; margin-bottom:1.8rem; }
  .t-author { display:flex; align-items:center; gap:.9rem; }
  .t-avatar { width:42px; height:42px; border-radius:50%; background:rgba(224,27,36,.12); border:1px solid rgba(224,27,36,.28); display:flex; align-items:center; justify-content:center; font-family:'MuseoModerno',sans-serif; font-weight:700; color:var(--red); font-size:.9rem; flex-shrink:0; }
  .t-name { font-size:.9rem; font-weight:600; }
  .t-role { font-size:.73rem; color:rgba(245,245,245,.38); }

  /* ===================== SOBRE (MÍNIMO) ===================== */
  .about-section { }
  .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:7rem; align-items:center; }
  .about-left h2 { font-family:'MuseoModerno',sans-serif; font-size:clamp(2rem,4vw,3.4rem); font-weight:900; line-height:1.05; margin-bottom:1.5rem; }
  .about-left h2 em { font-style:normal; color:var(--red-vivid); }
  .about-left p { font-size:1rem; line-height:1.88; color:rgba(245,245,245,.55); margin-bottom:1.2rem; }
  .about-left a { display:inline-flex; align-items:center; gap:.5rem; color:var(--red); text-decoration:none; font-size:.82rem; letter-spacing:.12em; text-transform:uppercase; font-weight:600; margin-top:1rem; border-bottom:1px solid rgba(224,27,36,.3); padding-bottom:.2rem; transition:border-color .3s; }
  .about-left a:hover { border-color:var(--red); }
  .about-left a svg { width:14px; height:14px; stroke:currentColor; }

  .about-right { display:grid; grid-template-columns:1fr 1fr; gap:1px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.05); }
  .about-stat { background:var(--gray); padding:2.2rem 1.8rem; transition:background .3s; }
  .about-stat:hover { background:rgba(224,27,36,.04); }
  .about-stat-val { font-family:'MuseoModerno',sans-serif; font-size:2.8rem; font-weight:900; color:var(--red-vivid); line-height:1; margin-bottom:.4rem; }
  .about-stat-label { font-size:.75rem; letter-spacing:.12em; text-transform:uppercase; color:rgba(245,245,245,.38); }

  /* ===================== PACOTES ===================== */
  .plans-section { background:var(--gray); }
  .plans-section h2 { font-family:'MuseoModerno',sans-serif; font-size:clamp(2rem,4vw,3.2rem); font-weight:900; line-height:1.05; margin-bottom:.8rem; }
  .plans-section h2 em { font-style:normal; color:var(--red-vivid); }
  .plans-intro { font-size:1rem; color:rgba(245,245,245,.5); max-width:520px; line-height:1.8; margin-bottom:4rem; }

  .plans-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.2rem; align-items:start; }
  .plan-card { background:var(--black); border:1px solid rgba(255,255,255,.07); border-radius:3px; padding:2.8rem 2.4rem; position:relative; transition:border-color .35s, transform .3s; }
  .plan-card:hover { border-color:rgba(224,27,36,.3); transform:translateY(-4px); }
  .plan-card.featured { border-color:var(--red); background:rgba(224,27,36,.04); }
  .plan-card.featured::before { content:'Mais escolhido'; position:absolute; top:-1px; left:50%; transform:translateX(-50%); background:var(--red); color:var(--white); font-size:.65rem; letter-spacing:.15em; text-transform:uppercase; font-weight:700; padding:.3rem .9rem; border-radius:0 0 4px 4px; white-space:nowrap; }

  .plan-name { font-family:'MuseoModerno',sans-serif; font-size:.8rem; font-weight:700; letter-spacing:.2em; text-transform:uppercase; color:var(--red); margin-bottom:1.2rem; }
  .plan-price { font-family:'MuseoModerno',sans-serif; font-size:3rem; font-weight:900; line-height:1; margin-bottom:.3rem; }
  .plan-price span { font-size:1.2rem; font-weight:400; vertical-align:top; margin-top:.6rem; display:inline-block; color:rgba(245,245,245,.5); font-family:'DM Sans',sans-serif; }
  .plan-desc { font-size:.88rem; color:rgba(245,245,245,.45); line-height:1.65; margin-bottom:2rem; padding-bottom:2rem; border-bottom:1px solid rgba(255,255,255,.07); }
  .plan-features { list-style:none; display:flex; flex-direction:column; gap:.9rem; margin-bottom:2.4rem; }
  .plan-features li { display:flex; align-items:flex-start; gap:.8rem; font-size:.9rem; color:rgba(245,245,245,.65); line-height:1.5; }
  .plan-features li::before { content:''; width:16px; height:16px; min-width:16px; border-radius:50%; background:rgba(224,27,36,.15); border:1px solid rgba(224,27,36,.3); display:flex; align-items:center; justify-content:center; margin-top:.1rem; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23E01B24' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:center; }
  .plan-features li.dim { color:rgba(245,245,245,.28); }
  .plan-features li.dim::before { border-color:rgba(255,255,255,.1); background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.2)' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cline x1='18' y1='6' x2='6' y2='18'/%3E%3Cline x1='6' y1='6' x2='18' y2='18'/%3E%3C/svg%3E"); }

  .plan-btn { display:block; text-align:center; padding:.9rem; font-family:'DM Sans',sans-serif; font-weight:700; font-size:.82rem; letter-spacing:.15em; text-transform:uppercase; text-decoration:none; border-radius:2px; transition:all .3s; border:1px solid rgba(224,27,36,.4); color:var(--red); }
  .plan-btn:hover { background:var(--red); color:var(--white); border-color:var(--red); }
  .plan-card.featured .plan-btn { background:var(--red); color:var(--white); border-color:var(--red); }
  .plan-card.featured .plan-btn:hover { background:var(--red-dark); }

  .plans-note { margin-top:2rem; text-align:center; font-size:.82rem; color:rgba(245,245,245,.3); }
  .plans-note a { color:var(--red); text-decoration:none; }

  button.plan-btn { width:100%; background:transparent; font-family:'DM Sans',sans-serif; cursor:none; }
  .plan-cart-form { margin-bottom:.7rem; }
  .plan-btn-alt { display:block; text-align:center; font-size:.72rem; letter-spacing:.12em; text-transform:uppercase; color:rgba(245,245,245,.38); text-decoration:none; transition:color .3s; }
  .plan-btn-alt:hover { color:var(--red); }

  /* ===================== CONTATO ===================== */
  .contact-section { }
  .contact-grid { display:grid; grid-template-columns:1fr 1fr; gap:7rem; align-items:start; }
  .contact-left h2 { font-family:'MuseoModerno',sans-serif; font-size:clamp(2rem,4vw,3.4rem); font-weight:900; line-height:1.05; margin-bottom:1.2rem; }
  .contact-left h2 em { font-style:normal; color:var(--red-vivid); }
  .contact-left p { font-size:1rem; color:rgba(245,245,245,.5); line-height:1.82; margin-bottom:2.5rem; }
  .contact-channels { display:flex; flex-direction:column; gap:1rem; }
  .contact-ch { display:flex; align-items:center; gap:1rem; padding:1.2rem 1.4rem; border:1px solid rgba(255,255,255,.07); border-radius:3px; text-decoration:none; color:var(--white); transition:border-color .3s, background .3s; }
  .contact-ch:hover { border-color:rgba(224,27,36,.3); background:rgba(224,27,36,.04); }
  .contact-ch-icon { width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .contact-ch-icon svg { width:22px; height:22px; display:block; }
  .contact-ch-label { font-size:.68rem; letter-spacing:.15em; text-transform:uppercase; color:rgba(245,245,245,.35); margin-bottom:.2rem; }
  .contact-ch-val { font-size:.95rem; font-weight:500; }

  /* FORMULÁRIO */
  .contact-form { display:flex; flex-direction:column; gap:1.2rem; }
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1.2rem; }
  .form-group { display:flex; flex-direction:column; gap:.5rem; }
  .form-group label { font-size:.72rem; letter-spacing:.15em; text-transform:uppercase; color:rgba(245,245,245,.4); }
  .form-group input, .form-group select, .form-group textarea {
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
    border-radius:2px; padding:.85rem 1rem; color:var(--white);
    font-family:'DM Sans',sans-serif; font-size:.92rem;
    transition:border-color .3s; outline:none; resize:none;
    cursor:none;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:rgba(224,27,36,.5); background:rgba(224,27,36,.04); }
  .form-group select option { background:var(--gray); }
  .form-submit { background:var(--red); color:var(--white); border:none; padding:1rem; font-family:'DM Sans',sans-serif; font-weight:700; font-size:.85rem; letter-spacing:.18em; text-transform:uppercase; border-radius:2px; cursor:none; transition:background .3s, transform .3s; }
  .form-submit:hover { background:var(--red-dark); transform:translateY(-2px); }

  /* FOOTER */
  .footer-bar { border-top:1px solid rgba(255,255,255,.06); padding:1.8rem 4rem; display:flex; align-items:center; justify-content:space-between; }
  .footer-logo img { height:40px; }
  .footer-bar p { font-size:.75rem; color:rgba(245,245,245,.25); letter-spacing:.05em; }

  /* REVEAL */
  .reveal { opacity:0; transform:translateY(36px); transition:opacity .7s ease,transform .7s ease; }
  .reveal.visible { opacity:1; transform:translateY(0); }

  @media(max-width:900px){
    nav { padding:1.2rem 2rem; }
    nav ul,.nav-cta { display:none; }
    section { padding:5rem 2rem; }
    .hero { padding:0 2rem; }
    .hero-nums,.hero-scroll { display:none; }
    .testi-header,.about-grid,.contact-grid { grid-template-columns:1fr; gap:2.5rem; }
    .testi-grid,.plans-grid { grid-template-columns:1fr; }
    .about-right { grid-template-columns:1fr 1fr; }
    .form-row { grid-template-columns:1fr; }
    .footer-bar { flex-direction:column; gap:1rem; text-align:center; padding:1.8rem 2rem; }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-line"></div>
  <div class="hero-content">
    <div class="hero-tag">Desenvolvimento Web — Est. 2024</div>
    <h1>
      SEU SITE<br>
      <span class="red">MERECE</span><br>
      <span class="outline">MAIS.</span>
    </h1>
    <p class="hero-sub">Criamos sites com identidade real — feitos para o seu negócio, não para qualquer um. Sem templates, sem soluções genéricas.</p>
    <div class="hero-actions">
      <a href="#pacotes" class="btn-red">Ver Pacotes</a>
      <a href="#sobre" class="btn-ghost">
        Conhecer a NEXUS
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
    </div>
  </div>
  <div class="hero-nums">
    <div class="hero-num-item">
      <div class="hero-num-val">12+</div>
      <div class="hero-num-label">Projetos</div>
    </div>
    <div class="hero-num-item">
      <div class="hero-num-val">100%</div>
      <div class="hero-num-label">Satisfação</div>
    </div>
    <div class="hero-num-item">
      <div class="hero-num-val">7</div>
      <div class="hero-num-label">Especialistas</div>
    </div>
  </div>
  <div class="hero-scroll">
    <div class="hero-scroll-bar"></div>
    <span>Scroll</span>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-wrap">
  <div class="marquee-track">
    <div class="m-item">DESIGN <span class="m-dot"></span></div>
    <div class="m-item hi">IDENTIDADE REAL <span class="m-dot"></span></div>
    <div class="m-item">DESENVOLVIMENTO <span class="m-dot"></span></div>
    <div class="m-item hi">CONEXÃO DIGITAL <span class="m-dot"></span></div>
    <div class="m-item">NEXUS <span class="m-dot"></span></div>
    <div class="m-item hi">SITES QUE VENDEM <span class="m-dot"></span></div>
    <div class="m-item">DESIGN <span class="m-dot"></span></div>
    <div class="m-item hi">IDENTIDADE REAL <span class="m-dot"></span></div>
    <div class="m-item">DESENVOLVIMENTO <span class="m-dot"></span></div>
    <div class="m-item hi">CONEXÃO DIGITAL <span class="m-dot"></span></div>
    <div class="m-item">NEXUS <span class="m-dot"></span></div>
    <div class="m-item hi">SITES QUE VENDEM <span class="m-dot"></span></div>
  </div>
</div>

<!-- ===== DEPOIMENTOS ===== -->
<section class="testi-section">
  <div class="sec-label">O Que Dizem de Nós</div>
  <div class="testi-header reveal">
    <h2>Clientes que<br>confiam na <em>NEXUS</em></h2>
    <p>Não acredite só na nossa palavra — veja o que quem já trabalhou com a gente tem a dizer sobre a experiência e o resultado.</p>
  </div>
  <div class="testi-grid reveal">

    <div class="t-card">
      <div class="t-quote">"</div>
      <div class="t-stars">
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      </div>
      <p class="t-text">A NEXUS entregou exatamente o que o nosso escritório precisava — um site com a nossa cara, que transmite credibilidade desde o primeiro acesso. Superou todas as expectativas.</p>
      <div class="t-author">
        <div class="t-avatar">R</div>
        <div>
          <div class="t-name">Ricardo Alves</div>
          <div class="t-role">CEO — Studio Arquitetura Moderna</div>
        </div>
      </div>
    </div>

    <div class="t-card">
      <div class="t-quote">"</div>
      <div class="t-stars">
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      </div>
      <p class="t-text">Processo super transparente, prazo cumprido e o resultado ficou incrível. Nossa loja teve um aumento real nas vendas logo no primeiro mês. Recomendo sem hesitar.</p>
      <div class="t-author">
        <div class="t-avatar">M</div>
        <div>
          <div class="t-name">Marina Costa</div>
          <div class="t-role">Fundadora — Loja Moda Urbana</div>
        </div>
      </div>
    </div>

    <div class="t-card">
      <div class="t-quote">"</div>
      <div class="t-stars">
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <svg class="t-star" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      </div>
      <p class="t-text">São jovens, mas entregam com a seriedade de quem tem anos de mercado. Fui tratado como cliente de verdade, fui ouvido em cada detalhe. Resultado: site que realmente nos representa.</p>
      <div class="t-author">
        <div class="t-avatar">F</div>
        <div>
          <div class="t-name">Felipe Rocha</div>
          <div class="t-role">Diretor — Clínica Saúde Total</div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ===== SOBRE (MÍNIMO) ===== -->
<section class="about-section" id="sobre">
  <div class="sec-label">Sobre a NEXUS</div>
  <div class="about-grid reveal">
    <div class="about-left">
      <h2>Uma empresa criada por <em>estudantes sérios</em></h2>
      <p>A NEXUS nasceu como um projeto de TCC e se transformou em algo real. Somos sete estudantes que identificaram um problema no mercado: sites genéricos que não representam o negócio do cliente.</p>
      <p>Nossa missão é simples — ser o <strong style="color:var(--white)">elo (nexus)</strong> entre o seu negócio e o mundo digital, com identidade, qualidade e comprometimento de verdade.</p>
      <a href="sobre_nos.php">Conheça nossa história <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
    </div>
    <div class="about-right">
      <div class="about-stat">
        <div class="about-stat-val">12+</div>
        <div class="about-stat-label">Projetos entregues</div>
      </div>
      <div class="about-stat">
        <div class="about-stat-val">100%</div>
        <div class="about-stat-label">Clientes satisfeitos</div>
      </div>
      <div class="about-stat">
        <div class="about-stat-val">7</div>
        <div class="about-stat-label">Especialistas</div>
      </div>
      <div class="about-stat">
        <div class="about-stat-val">0</div>
        <div class="about-stat-label">Templates genéricos</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== PACOTES ===== -->
<section class="plans-section" id="pacotes">
  <div class="sec-label">Nossos Pacotes</div>
  <h2>Escolha o plano<br><em>certo para você</em></h2>
  <p class="plans-intro">Cada pacote foi pensado para um momento diferente do negócio. Todos incluem design personalizado — nenhum template.</p>

  <div class="plans-grid reveal">

    <!-- STARTER -->
    <div class="plan-card">
      <div class="plan-name">Starter</div>
      <div class="plan-price"><span>R$</span> 800</div>
      <p class="plan-desc">Ideal para quem precisa marcar presença na internet de forma rápida e com qualidade.</p>
      <ul class="plan-features">
        <li>Landing page de 1 página</li>
        <li>Design personalizado</li>
        <li>Responsivo (mobile)</li>
        <li>Formulário de contato</li>
        <li>Integração com redes sociais</li>
        <li class="dim">Sistema de agendamento</li>
        <li class="dim">Blog ou área de notícias</li>
        <li class="dim">Painel administrativo</li>
      </ul>
      <form method="post" action="carrinho.php" class="plan-cart-form">
        <input type="hidden" name="acao" value="add">
        <input type="hidden" name="pacote" value="starter">
        <button type="submit" class="plan-btn">Adicionar ao carrinho</button>
      </form>
      <a href="#contato" class="plan-btn-alt">ou fale com a gente</a>
    </div>

    <!-- PROFISSIONAL (destaque) -->
    <div class="plan-card featured">
      <div class="plan-name">Profissional</div>
      <div class="plan-price"><span>R$</span> 1.800</div>
      <p class="plan-desc">A escolha ideal para quem quer um site completo, com mais páginas e funcionalidades.</p>
      <ul class="plan-features">
        <li>Site com até 5 páginas</li>
        <li>Design personalizado</li>
        <li>Responsivo (mobile)</li>
        <li>Formulário de contato</li>
        <li>Integração com redes sociais</li>
        <li>Sistema de agendamento</li>
        <li>Blog ou área de notícias</li>
        <li class="dim">Painel administrativo</li>
      </ul>
      <form method="post" action="carrinho.php" class="plan-cart-form">
        <input type="hidden" name="acao" value="add">
        <input type="hidden" name="pacote" value="profissional">
        <button type="submit" class="plan-btn">Adicionar ao carrinho</button>
      </form>
      <a href="#contato" class="plan-btn-alt">ou fale com a gente</a>
    </div>

    <!-- PREMIUM -->
    <div class="plan-card">
      <div class="plan-name">Premium</div>
      <div class="plan-price"><span>R$</span> 3.500</div>
      <p class="plan-desc">Solução completa para negócios que precisam de um sistema robusto e escalável.</p>
      <ul class="plan-features">
        <li>Site com páginas ilimitadas</li>
        <li>Design personalizado</li>
        <li>Responsivo (mobile)</li>
        <li>Formulário de contato</li>
        <li>Integração com redes sociais</li>
        <li>Sistema de agendamento</li>
        <li>Blog ou área de notícias</li>
        <li>Painel administrativo</li>
      </ul>
      <form method="post" action="carrinho.php" class="plan-cart-form">
        <input type="hidden" name="acao" value="add">
        <input type="hidden" name="pacote" value="premium">
        <button type="submit" class="plan-btn">Adicionar ao carrinho</button>
      </form>
      <a href="#contato" class="plan-btn-alt">ou fale com a gente</a>
    </div>

  </div>
  <p class="plans-note">Precisa de algo diferente? <a href="#contato">Fale com a gente</a> — montamos um orçamento sob medida.</p>
</section>

<!-- ===== CONTATO ===== -->
<section class="contact-section" id="contato">
  <div class="sec-label">Entre em Contato</div>

  <div class="contact-grid reveal">

    <div class="contact-left">
      <h2>Vamos construir<br>algo <em>juntos?</em></h2>
      <p>Manda uma mensagem, escolhe o pacote ou só tira uma dúvida. A gente responde rápido e sem enrolação.</p>
      <div class="contact-channels">
        <a href="mailto:nexus.suporte.26@gmail.com" class="contact-ch">
          <div class="contact-ch-icon"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="#EA4335" d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/></svg></div>
          <div>
            <div class="contact-ch-label">E-mail</div>
            <div class="contact-ch-val">nexus.suporte.26@gmail.com</div>
          </div>
        </a>
        <a href="https://wa.me/5521983867111" class="contact-ch" target="_blank" rel="noopener">
          <div class="contact-ch-icon"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="#25D366" d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414z"/></svg></div>
          <div>
            <div class="contact-ch-label">WhatsApp</div>
            <div class="contact-ch-val">(21) 98386-7111</div>
          </div>
        </a>
        <a href="https://www.instagram.com/ky.nexus" class="contact-ch" target="_blank" rel="noopener">
          <div class="contact-ch-icon"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><defs><linearGradient id="igGrad" x1="0%" y1="100%" x2="100%" y2="0%"><stop offset="0%" stop-color="#FED576"/><stop offset="26%" stop-color="#F47133"/><stop offset="61%" stop-color="#BC3081"/><stop offset="100%" stop-color="#4C63D2"/></linearGradient></defs><path fill="url(#igGrad)" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></div>
          <div>
            <div class="contact-ch-label">Instagram</div>
            <div class="contact-ch-val">@ky.nexus</div>
          </div>
        </a>
      </div>
    </div>

    <form class="contact-form" id="contactForm" action="../Back-end/contact.php" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>Seu nome</label>
          <input type="text" name="nome" placeholder="João Silva" required>
        </div>
        <div class="form-group">
          <label>E-mail</label>
          <input type="email" name="email" placeholder="joao@email.com" required>
        </div>
      </div>
      <div class="form-group">
        <label>Pacote de interesse</label>
        <select name="pacote" required>
          <option value="">Selecione um pacote</option>
          <option value="Starter — R$ 800">Starter — R$ 800</option>
          <option value="Profissional — R$ 1.800">Profissional — R$ 1.800</option>
          <option value="Premium — R$ 3.500">Premium — R$ 3.500</option>
          <option value="Orçamento personalizado">Quero um orçamento personalizado</option>
        </select>
      </div>
      <div class="form-group">
        <label>Conta sobre o seu projeto</label>
        <textarea name="mensagem" rows="5" placeholder="Descreva brevemente o seu negócio e o que você precisa..." required></textarea>
      </div>
      <button type="submit" class="form-submit">Enviar Mensagem →</button>
    </form>

  </div>
</section>

<!-- FOOTER -->
<footer class="footer-bar">
  <p>© <?php echo date('Y'); ?> NEXUS. Todos os direitos reservados.</p>
  <p>Feito com propósito.</p>
</footer>

<?php require __DIR__ . "/popup.php"; ?>

<script>
  // ===== Formulário de contato: feedback por popup =====
  (function () {
    const form = document.getElementById('contactForm');
    if (!form) return;
    const CHAVE = 'nexus_contato_rascunho';
    const store = {
      get() { try { return JSON.parse(sessionStorage.getItem(CHAVE) || 'null'); } catch (e) { return null; } },
      set(v) { try { sessionStorage.setItem(CHAVE, JSON.stringify(v)); } catch (e) {} },
      del()  { try { sessionStorage.removeItem(CHAVE); } catch (e) {} }
    };

    // Ao enviar: guarda o texto (caso dê erro não se perde) e trava o botão contra clique duplo
    form.addEventListener('submit', () => {
      const d = {}; new FormData(form).forEach((v, k) => d[k] = v);
      store.set(d);
      const b = form.querySelector('.form-submit');
      b.disabled = true; b.textContent = 'Enviando…';
    });

    // Botão travado não pode ficar assim se o cliente voltar pelo histórico do navegador
    window.addEventListener('pageshow', e => {
      if (!e.persisted) return;
      const b = form.querySelector('.form-submit');
      b.disabled = false; b.textContent = 'Enviar Mensagem →';
    });

    // Resultado vindo do Back-end/contact.php
    const q = new URLSearchParams(location.search);
    const status = q.get('status');
    if (status !== 'success' && status !== 'error') return;

    history.replaceState(null, '', location.pathname + '#contato');   // F5 não repete o popup

    if (status === 'success') {
      store.del();
      nexusPopup({
        tipo: 'sucesso',
        titulo: 'Mensagem enviada!',
        texto: 'Recebemos o seu contato. Nossa equipe vai responder em breve no e-mail que você informou.',
        primario: { rotulo: 'Fechar' }
      });
      return;
    }

    const textos = {
      campos: 'Alguns campos estão vazios. Preencha nome, e-mail, pacote e mensagem e tente de novo.',
      email:  'O e-mail informado não parece válido. Confira e tente de novo.',
      envio:  'Não conseguimos enviar agora. Sua mensagem foi mantida no formulário — tente de novo em instantes ou fale com a gente pelo WhatsApp.'
    };
    const rascunho = store.get();
    if (rascunho) Object.keys(rascunho).forEach(k => { if (form.elements[k]) form.elements[k].value = rascunho[k]; });

    nexusPopup({
      tipo: 'erro',
      titulo: 'Não foi possível enviar',
      texto: textos[q.get('motivo')] || textos.envio,
      primario: { rotulo: 'Voltar ao formulário', aoClicar: () => document.getElementById('contato').scrollIntoView() },
      secundario: { rotulo: 'WhatsApp', aoClicar: () => window.open('https://wa.me/5521983867111', '_blank', 'noopener') }
    });
  })();
</script>

<script>
  // Cursor
  const cur = document.getElementById('cursor');
  const ring = document.getElementById('cursorRing');
  if (cur && ring) {
    let mx=0,my=0,rx=0,ry=0;
    document.addEventListener('mousemove',e=>{ mx=e.clientX; my=e.clientY; cur.style.left=mx-5+'px'; cur.style.top=my-5+'px'; });
    (function anim(){ rx+=(mx-rx)*.1; ry+=(my-ry)*.1; ring.style.left=rx-17+'px'; ring.style.top=ry-17+'px'; requestAnimationFrame(anim); })();
    document.querySelectorAll('a,button,input,select,textarea').forEach(el=>{
      el.addEventListener('mouseenter',()=>{ cur.style.transform='scale(2)'; ring.style.transform='scale(1.4)'; ring.style.borderColor='var(--red)'; });
      el.addEventListener('mouseleave',()=>{ cur.style.transform='scale(1)'; ring.style.transform='scale(1)'; ring.style.borderColor='rgba(224,27,36,.45)'; });
    });
  }

  // Reveal
  const obs = new IntersectionObserver(entries=>{
    entries.forEach((e,i)=>{ if(e.isIntersecting){ setTimeout(()=>e.target.classList.add('visible'), i*80); obs.unobserve(e.target); } });
  },{ threshold:.08 });
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>
</body>
</html>
