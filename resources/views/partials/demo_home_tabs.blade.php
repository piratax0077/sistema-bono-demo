@if(config('demo.enabled') && auth()->check() && in_array(request()->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true))
<style>
    .home-tabs{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:22px}.home-tabs button{padding:12px 20px;border:1px solid #cbdaf0;border-radius:12px;background:white;color:#1848a1;font:700 15px/1.4 system-ui;cursor:pointer}.home-tabs button[aria-selected="true"]{background:#1848a1;color:white;border-color:#1848a1}.home-tabs button:focus-visible{outline:3px solid #22b8bb;outline-offset:3px}[data-home-panel][hidden]{display:none!important}.technical-panel{background:white;border:1px solid #cbdaf0;border-radius:20px;padding:20px}.technical-panel h2{color:#18304f;font-size:24px}.technical-panel p{color:#52657e}.technical-panel iframe{display:block;width:100%;height:78vh;min-height:440px;border:1px solid #d5e1f1;border-radius:12px;margin-top:16px}.technical-panel a{color:#1848a1;font-weight:700}
</style>
<div class="home-tabs" role="tablist" aria-label="Contenido de Inicio">
    <button type="button" role="tab" id="tab-presentation" aria-controls="panel-presentation" aria-selected="true" data-home-tab="presentation">Presentación del sistema</button>
    <button type="button" role="tab" id="tab-technical" aria-controls="panel-technical" aria-selected="false" tabindex="-1" data-home-tab="technical">Ficha técnica confidencial</button>
</div>
<section id="panel-technical" class="technical-panel" role="tabpanel" aria-labelledby="tab-technical" data-home-panel="technical" hidden>
    <h2>Ficha técnica confidencial</h2>
    <p>Documento de trabajo para revisión técnica. Acceso local con sesión iniciada; no acredita patentabilidad ni una auditoría del sistema.</p>
    <a href="{{ route('demo.technical-sheet') }}" target="_blank" rel="noopener">Abrir documento en otra pestaña ↗</a>
    <iframe title="Ficha técnica confidencial del sistema de bonos" data-src="{{ route('demo.technical-sheet') }}"></iframe>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = [...document.querySelectorAll('[data-home-tab]')];
    function select(tab) {
        tabs.forEach(button => {const active = button === tab; button.setAttribute('aria-selected', String(active)); button.tabIndex = active ? 0 : -1;});
        document.querySelectorAll('[data-home-panel]').forEach(panel => {panel.hidden = panel.dataset.homePanel !== tab.dataset.homeTab;});
        if (tab.dataset.homeTab === 'technical') {
            const frame = document.querySelector('#panel-technical iframe');
            if (!frame.hasAttribute('src')) frame.src = frame.dataset.src;
        }
    }
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => select(tab));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft','ArrowRight','Home','End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            select(tabs[next]); tabs[next].focus();
        });
    });
});
</script>
@endif
