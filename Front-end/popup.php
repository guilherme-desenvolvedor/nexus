<?php
/**
 * Popup / toast / confirmação da NEXUS — componente compartilhado.
 *
 * Incluir UMA vez por página, antes do <script> principal da página
 * (para o cursor customizado enxergar os botões do popup):
 *
 *     <?php require __DIR__ . "/popup.php"; ?>
 *
 * API (JavaScript):
 *   nexusPopup({ tipo, titulo, texto, primario, secundario })
 *       tipo:       'sucesso' | 'erro' | 'pergunta'
 *       primario:   { rotulo, aoClicar }   (padrão: "Entendi", só fecha)
 *       secundario: { rotulo, aoClicar }   (opcional)
 *   nexusToast('mensagem curta')
 *
 * Confirmação declarativa: <form data-confirm="Texto?" data-confirm-titulo="Título"
 *                                data-confirm-ok="Rótulo do botão"> — o envio só
 *                                acontece se o cliente confirmar.
 */
?>
<style>
  .nx-overlay { display:none; position:fixed; inset:0; z-index:9000; align-items:center; justify-content:center; padding:1.2rem; background:rgba(4,4,4,.78); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); }
  .nx-overlay.nx-open { display:flex; animation:nxFade .25s ease; }
  .nx-modal { position:relative; width:100%; max-width:440px; background:#0e0e0e; border:1px solid rgba(255,255,255,.09); border-radius:6px; padding:2.4rem 2rem 1.8rem; text-align:center; box-shadow:0 30px 90px rgba(0,0,0,.75); animation:nxPop .35s cubic-bezier(.2,1.2,.4,1); }
  .nx-modal::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,var(--nx-cor,#E01B24),transparent); }
  .nx-modal[data-tipo="sucesso"]  { --nx-cor:#4ade80; }
  .nx-modal[data-tipo="erro"]     { --nx-cor:#E01B24; }
  .nx-modal[data-tipo="pergunta"] { --nx-cor:#E01B24; }

  .nx-icon { width:68px; height:68px; margin:0 auto 1.3rem; border-radius:50%; display:flex; align-items:center; justify-content:center; background:color-mix(in srgb,var(--nx-cor,#E01B24) 12%,transparent); border:1px solid color-mix(in srgb,var(--nx-cor,#E01B24) 40%,transparent); color:var(--nx-cor,#E01B24); }
  .nx-icon svg { width:32px; height:32px; display:none; fill:none; stroke:currentColor; stroke-width:2.4; stroke-linecap:round; stroke-linejoin:round; }
  .nx-modal[data-tipo="sucesso"]  .nx-i-ok,
  .nx-modal[data-tipo="erro"]     .nx-i-erro,
  .nx-modal[data-tipo="pergunta"] .nx-i-pergunta { display:block; }
  .nx-i-ok path { stroke-dasharray:30; stroke-dashoffset:30; animation:nxDraw .5s .2s ease forwards; }

  .nx-modal h3 { font-family:'MuseoModerno',sans-serif; font-size:1.4rem; font-weight:900; line-height:1.15; margin-bottom:.7rem; color:#F5F5F5; }
  .nx-modal p  { font-size:.92rem; line-height:1.7; color:rgba(245,245,245,.6); margin-bottom:1.7rem; }
  .nx-actions { display:flex; gap:.7rem; }
  .nx-actions:has(.nx-btn[hidden]) .nx-btn-primary { width:100%; }
  .nx-btn { flex:1; padding:.9rem 1rem; font-family:'DM Sans',sans-serif; font-weight:700; font-size:.78rem; letter-spacing:.14em; text-transform:uppercase; border-radius:2px; cursor:none; border:1px solid transparent; transition:background .25s,transform .25s,border-color .25s,color .25s; }
  .nx-btn[hidden] { display:none; }
  .nx-btn-primary { background:#E01B24; color:#fff; border-color:#E01B24; }
  .nx-btn-primary:hover { background:#a01318; border-color:#a01318; transform:translateY(-2px); }
  .nx-btn-ghost { background:transparent; color:rgba(245,245,245,.6); border-color:rgba(255,255,255,.14); }
  .nx-btn-ghost:hover { border-color:#E01B24; color:#E01B24; }
  .nx-btn:focus-visible { outline:2px solid #F5F5F5; outline-offset:2px; }

  .nx-toast { position:fixed; left:50%; bottom:1.8rem; z-index:9000; transform:translate(-50%,20px); opacity:0; pointer-events:none; display:flex; align-items:center; gap:.65rem; max-width:calc(100vw - 2rem); padding:.85rem 1.3rem; background:#0e0e0e; border:1px solid rgba(74,222,128,.4); border-radius:4px; color:#F5F5F5; font-family:'DM Sans',sans-serif; font-size:.86rem; box-shadow:0 14px 40px rgba(0,0,0,.6); transition:opacity .3s,transform .3s; }
  .nx-toast.nx-show { opacity:1; transform:translate(-50%,0); }
  .nx-toast svg { width:16px; height:16px; flex-shrink:0; fill:none; stroke:#4ade80; stroke-width:2.6; stroke-linecap:round; stroke-linejoin:round; }

  @keyframes nxFade { from{opacity:0} to{opacity:1} }
  @keyframes nxPop  { from{opacity:0;transform:translateY(18px) scale(.94)} to{opacity:1;transform:none} }
  @keyframes nxDraw { to{stroke-dashoffset:0} }
  @media (prefers-reduced-motion:reduce) {
    .nx-overlay.nx-open,.nx-modal,.nx-i-ok path { animation:none; stroke-dashoffset:0; }
    .nx-toast { transition:none; }
  }
  @media (max-width:480px) {
    .nx-modal { padding:2rem 1.3rem 1.4rem; }
    .nx-actions { flex-direction:column-reverse; }
  }
</style>

<div class="nx-overlay" id="nxOverlay" role="dialog" aria-modal="true" aria-labelledby="nxTitulo" aria-describedby="nxTexto">
  <div class="nx-modal" id="nxModal" data-tipo="sucesso">
    <div class="nx-icon" aria-hidden="true">
      <svg class="nx-i-ok" viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
      <svg class="nx-i-erro" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
      <svg class="nx-i-pergunta" viewBox="0 0 24 24"><path d="M12 8v5M12 16.5v.01"/><circle cx="12" cy="12" r="9.5"/></svg>
    </div>
    <h3 id="nxTitulo"></h3>
    <p id="nxTexto"></p>
    <div class="nx-actions">
      <button type="button" class="nx-btn nx-btn-ghost" id="nxSec" hidden></button>
      <button type="button" class="nx-btn nx-btn-primary" id="nxPri"></button>
    </div>
  </div>
</div>

<div class="nx-toast" id="nxToast" role="status" aria-live="polite">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
  <span id="nxToastTxt"></span>
</div>

<script>
(function () {
  var overlay = document.getElementById('nxOverlay');
  var modal   = document.getElementById('nxModal');
  var titulo  = document.getElementById('nxTitulo');
  var texto   = document.getElementById('nxTexto');
  var pri     = document.getElementById('nxPri');
  var sec     = document.getElementById('nxSec');
  var toast   = document.getElementById('nxToast');
  var toastTx = document.getElementById('nxToastTxt');
  var acaoPri = null, acaoSec = null, foco = null, toastTimer = null;

  function fechar(chamar) {
    if (!overlay.classList.contains('nx-open')) return;
    overlay.classList.remove('nx-open');
    document.documentElement.style.overflow = '';
    document.removeEventListener('keydown', teclas, true);
    var cb = chamar;
    acaoPri = acaoSec = null;
    if (foco && foco.focus) { try { foco.focus(); } catch (e) {} }
    if (typeof cb === 'function') cb();
  }

  function teclas(e) {
    if (e.key === 'Escape') { e.preventDefault(); fechar(acaoSec); return; }
    if (e.key !== 'Tab') return;
    // prende o foco dentro do popup
    var btns = [sec, pri].filter(function (b) { return !b.hidden; });
    var i = btns.indexOf(document.activeElement);
    e.preventDefault();
    if (e.shiftKey) btns[(i <= 0 ? btns.length : i) - 1].focus();
    else            btns[(i + 1) % btns.length].focus();
  }

  window.nexusPopup = function (o) {
    o = o || {};
    foco = document.activeElement;
    modal.setAttribute('data-tipo', o.tipo || 'sucesso');
    titulo.textContent = o.titulo || '';
    texto.textContent  = o.texto  || '';

    var p = o.primario   || {};
    var s = o.secundario || null;
    pri.textContent = p.rotulo || 'Entendi';
    acaoPri = p.aoClicar || null;
    if (s) { sec.textContent = s.rotulo || 'Cancelar'; sec.hidden = false; acaoSec = s.aoClicar || null; }
    else   { sec.hidden = true; acaoSec = null; }

    overlay.classList.add('nx-open');
    document.documentElement.style.overflow = 'hidden';
    document.addEventListener('keydown', teclas, true);
    pri.focus();
  };

  pri.addEventListener('click', function () { fechar(acaoPri); });
  sec.addEventListener('click', function () { fechar(acaoSec); });
  overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) fechar(acaoSec); });

  window.nexusToast = function (msg) {
    toastTx.textContent = msg;
    toast.classList.add('nx-show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toast.classList.remove('nx-show'); }, 3400);
  };

  // <form data-confirm="..."> só envia se o cliente confirmar
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f.hasAttribute || !f.hasAttribute('data-confirm') || f.__nxOk) return;
    e.preventDefault();
    nexusPopup({
      tipo: 'pergunta',
      titulo: f.getAttribute('data-confirm-titulo') || 'Tem certeza?',
      texto: f.getAttribute('data-confirm'),
      primario:   { rotulo: f.getAttribute('data-confirm-ok') || 'Confirmar', aoClicar: function () { f.__nxOk = true; f.submit(); } },
      secundario: { rotulo: 'Cancelar' }
    });
  });
})();
</script>
