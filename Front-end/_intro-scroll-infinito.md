# Intro em scroll — símbolo ∞ → site  (guardado pra depois)

Isto foi **removido do `sobre_nos.php`** em 08/09/2026 pra a apresentação, porque o
vídeo ainda não estava pronto. Pra reativar, é só colar os 3 trechos abaixo de
volta nos lugares indicados e (idealmente) colocar o vídeo em
`Front-end/imagens/logo-reel.mp4`.

Conceito: o ∞ gira (continuidade) e tomba de lado; conforme o scroll entram as
frases reais do site com fade (transição vídeo→site feita em CSS/JS, texto nítido).
Enquanto não há vídeo, um ∞ em SVG gira em 3D como fallback.

A imagem de referência do símbolo está em `Front-end/imagens/logo-infinito.png`
(usar no Google Flow / Veo).

---

## 1) CSS — colar dentro do `<style>`, logo depois da regra `.members-section > h2 { … }`

```css
  /* ═══ INTRO EM SCROLL — vídeo do símbolo ∞ ═══ */
  .reel-scroll { position:relative; width:100vw; margin-left:calc(50% - 50vw); height:280vh; background:#080808; }
  .reel-stage { position:sticky; top:0; height:100vh; overflow:hidden; display:flex; align-items:center; justify-content:center; }
  .reel-stage::after { content:''; position:absolute; inset:0; pointer-events:none; background:radial-gradient(ellipse at center, rgba(8,8,8,.62) 0%, rgba(8,8,8,.28) 45%, transparent 78%); opacity:0; transition:opacity .5s ease; }
  .reel-stage.words-on::after { opacity:1; }
  .reel-video { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; background:#080808; }
  .reel-fallback { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; perspective:900px; }
  .reel-video.has-src ~ .reel-fallback { display:none; }
  .reel-inf { width:min(44vw,500px); max-width:80%; filter:drop-shadow(0 0 55px rgba(224,27,36,.45)); animation:reelSpin 7s linear infinite; transform-style:preserve-3d; transition:opacity .5s ease,transform .5s ease; }
  .reel-stage.words-on .reel-inf { opacity:.16; transform:scale(1.35); }
  @keyframes reelSpin { to { transform:rotate3d(1,.55,0,360deg); } }
  .reel-phrases { position:relative; z-index:3; text-align:center; padding:0 6vw; pointer-events:none; }
  .reel-phrases p { margin:.06em 0; opacity:0; transform:translateY(28px); transition:opacity .55s ease, transform .55s ease; font-family:'MuseoModerno',sans-serif; font-weight:900; line-height:1.02; color:#F5F5F5; font-size:clamp(2.6rem,8vw,6rem); }
  .reel-phrases p.reel-tag { font-family:'DM Sans',sans-serif; font-weight:400; font-size:clamp(1rem,2.3vw,1.5rem); color:rgba(245,245,245,.55); max-width:600px; margin:.9em auto 0; }
  .reel-phrases p.show { opacity:1; transform:none; }
  .reel-hint { position:absolute; bottom:2.4rem; left:50%; transform:translateX(-50%); font-size:.66rem; letter-spacing:.24em; text-transform:uppercase; color:rgba(245,245,245,.4); display:flex; flex-direction:column; align-items:center; gap:.55rem; transition:opacity .4s ease; }
  .reel-hint span { width:1px; height:32px; background:linear-gradient(rgba(245,245,245,.45),transparent); }
  .reel-hint.gone { opacity:0; }
  @media (prefers-reduced-motion:reduce){ .reel-inf{animation:none} }
```

---

## 2) HTML — colar logo depois do `</section>` do HERO (antes de `<!-- ORIGEM -->`)

```html
<!-- ═══ INTRO EM SCROLL — vídeo do símbolo ∞ (Google Flow / Veo) ═══════════════
     Coloque o vídeo (clipe 1 + clipe 2 unidos) em  Front-end/imagens/logo-reel.mp4
     (opcional: logo-reel.webm). O <video> NÃO tem autoplay/loop: o frame é
     controlado pelo scroll (scrub). Sem vídeo, o ∞ em SVG gira como fallback. -->
<section class="reel-scroll" id="reelScroll">
  <div class="reel-stage" id="reelStage">
    <video class="reel-video" id="reelVideo" muted playsinline preload="auto">
      <source src="imagens/logo-reel.webm" type="video/webm">
      <source src="imagens/logo-reel.mp4" type="video/mp4">
    </video>
    <div class="reel-fallback" aria-hidden="true">
      <svg class="reel-inf" viewBox="0 0 24 24" aria-hidden="true"><path fill="#E01B24" d="M18.6 6.62c-1.44 0-2.8.56-3.77 1.53L12 10.66 10.48 12h.01L7.8 14.39c-.64.64-1.49.99-2.4.99a3.39 3.39 0 0 1 0-6.76c.91 0 1.76.35 2.44 1.03l1.13 1 1.51-1.34L9.22 8.2A5.363 5.363 0 0 0 5.4 6.62 5.398 5.398 0 0 0 0 12c0 2.96 2.42 5.38 5.4 5.38 1.44 0 2.8-.56 3.77-1.53l2.83-2.5.01.01L14 11.34h-.01l2.68-2.39c.64-.64 1.49-.99 2.4-.99a3.39 3.39 0 0 1 0 6.76c-.9 0-1.76-.35-2.44-1.03l-1.14-1.01-1.51 1.34 1.27 1.12a5.386 5.386 0 0 0 3.82 1.57c2.98 0 5.4-2.41 5.4-5.38s-2.42-5.37-5.4-5.37z"/></svg>
    </div>
    <div class="reel-phrases" id="reelPhrases">
      <p data-at="0.55">Elo.</p>
      <p data-at="0.68">Conexão.</p>
      <p data-at="0.80">Vínculo.</p>
      <p class="reel-tag" data-at="0.90">A ponte entre o seu negócio e o que o mundo vai ver.</p>
    </div>
    <div class="reel-hint" id="reelHint">role para continuar<span></span></div>
  </div>
</section>
```

---

## 3) JS — colar dentro do `<script>` do rodapé, depois do bloco "Scroll reveal"

```js
  // ── Intro em scroll (vídeo ∞ → frases) ──
  (function(){
    var wrap=document.getElementById('reelScroll'); if(!wrap) return;
    var vid=document.getElementById('reelVideo');
    var stage=document.getElementById('reelStage');
    var hint=document.getElementById('reelHint');
    var ps=[].slice.call(document.querySelectorAll('#reelPhrases p'));
    var dur=0, scrub=false, tick=false;
    vid.addEventListener('loadedmetadata',function(){ dur=vid.duration||0; if(dur){ vid.classList.add('has-src'); scrub=true; upd(); } });
    vid.addEventListener('error',function(){ scrub=false; });
    function upd(){
      if(tick) return; tick=true;
      requestAnimationFrame(function(){
        var total=wrap.offsetHeight-window.innerHeight;
        var p=total>0 ? (-wrap.getBoundingClientRect().top)/total : 0;
        p=Math.max(0,Math.min(1,p));
        if(scrub && dur){ var vp=Math.min(1,p/0.72); try{ vid.currentTime=vp*dur; }catch(e){} }
        stage.classList.toggle('words-on', p>0.5);
        hint.classList.toggle('gone', p>0.04);
        ps.forEach(function(el){ el.classList.toggle('show', p>=parseFloat(el.dataset.at)); });
        tick=false;
      });
    }
    window.addEventListener('scroll',upd,{passive:true});
    window.addEventListener('resize',upd);
    upd();
  })();
```

---

## Ajustes rápidos

- **Frases / quando aparecem:** edite os `<p data-at="0.xx">` (0 = topo da seção, 1 = fim).
- **Duração do scroll:** `height:280vh` no `.reel-scroll` (mais = experiência mais longa).
- **Sem vídeo:** funciona com o ∞ SVG girando; as frases entram igual.
- As frases hoje repetem a seção "O Nome" logo abaixo (teaser → revelação). Se
  quiser diferenciar: ex. "Nascemos numa sala de aula." / "Empresa antes de formar."
