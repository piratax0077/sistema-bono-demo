<footer class="demo-footer" aria-label="Información de Medichile">
    <div class="demo-footer__inner">
        <div class="demo-footer__brand">
            <span class="demo-footer__mark" aria-hidden="true">M</span>
            <div><strong>Medichile</strong><small>Salud digital integrada</small></div>
        </div>
        <p class="demo-footer__message">Atención conectada, segura y trazable durante todo el recorrido del paciente.</p>
        <div class="demo-footer__trust" aria-label="Características de seguridad">
            <span>Identidad protegida</span><span>Autorización en app</span><span>Trazabilidad auditable</span>
        </div>
        <div class="demo-footer__bottom">
            <span>© {{ now()->year }} Medichile</span>
            <span>Entorno demostrativo · Los datos y operaciones visibles pueden ser simulados.</span>
        </div>
    </div>
</footer>
<style>
.demo-footer{position:relative;z-index:1;margin-top:42px;border-top:1px solid #d6e3f1;background:linear-gradient(135deg,#123d86,#1d66a7 58%,#28b9b8);color:#fff}.demo-footer__inner{width:min(1460px,calc(100% - 36px));margin:0 auto;padding:30px 0 20px}.demo-footer__brand{display:flex;align-items:center;gap:11px}.demo-footer__brand strong,.demo-footer__brand small{display:block}.demo-footer__brand strong{font-size:1.05rem}.demo-footer__brand small{margin-top:1px;color:#cce9f2;font-size:.75rem;letter-spacing:.08em;text-transform:uppercase}.demo-footer__mark{display:grid;width:38px;height:38px;place-items:center;border-radius:12px;background:#fff;color:#1848a1;font-weight:900}.demo-footer__message{max-width:660px;margin:18px 0 14px;color:#e4f4fa;font-size:.92rem}.demo-footer__trust{display:flex;flex-wrap:wrap;gap:8px}.demo-footer__trust span{padding:7px 10px;border:1px solid #ffffff38;border-radius:999px;background:#ffffff12;color:#f1fbff;font-size:.74rem;font-weight:700}.demo-footer__trust span:before{content:'✓';margin-right:6px;color:#65e2d5}.demo-footer__bottom{display:flex;justify-content:space-between;gap:16px;margin-top:22px;padding-top:15px;border-top:1px solid #ffffff26;color:#cce3ed;font-size:.72rem}@media(max-width:700px){.demo-footer{margin-top:28px}.demo-footer__inner{width:min(100% - 28px,1460px);padding-top:24px}.demo-footer__bottom{flex-direction:column;gap:5px}}
</style>
