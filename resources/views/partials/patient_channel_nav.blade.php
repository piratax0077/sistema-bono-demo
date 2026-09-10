<nav class="patient-channel-nav" aria-label="Canales de atención del paciente">
    <span class="patient-channel-nav__label">Acceso paciente</span>
    <a class="{{ request()->routeIs('paciente.home') ? 'active' : '' }}" href="{{ route('paciente.home') }}">Inicio</a>
    <a class="{{ request()->routeIs('paciente.totem', 'totem.local') ? 'active' : '' }}" href="{{ route('paciente.totem', ['tab' => 'comprar']) }}">Paciente Tótem</a>
    <a class="{{ request()->routeIs('paciente.escritorio', 'cliente.dashboard') ? 'active' : '' }}" href="{{ route('paciente.escritorio') }}">Página propia · Escritorio paciente</a>
    <a class="{{ request()->routeIs('paciente.agenda') ? 'active' : '' }}" href="{{ route('paciente.agenda') }}">Paciente desde Agenda</a>
</nav>
<style>
.patient-channel-nav{position:sticky;top:45px;z-index:9990;display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;padding:10px 16px;background:#fff;border-bottom:1px solid #d8e8e3;box-shadow:0 3px 14px rgba(6,52,47,.08);font:700 14px/1.2 Inter,Segoe UI,Arial,sans-serif}.patient-channel-nav__label{color:#56716b;margin-right:8px;text-transform:uppercase;letter-spacing:.09em;font-size:11px}.patient-channel-nav a{color:#075e54;text-decoration:none;border:1px solid #a7cec4;border-radius:999px;padding:9px 14px;background:#f6fbf9}.patient-channel-nav a.active{background:#07866f;color:#fff;border-color:#07866f}@media(max-width:700px){.patient-channel-nav{top:76px}.patient-channel-nav__label{width:100%;text-align:center;margin:0}.patient-channel-nav a{flex:1;text-align:center;min-width:130px}}
</style>
