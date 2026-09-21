@php
    $heroEyebrow = $eyebrow ?? 'Medichile · Escritorio seguro';
    $heroTitle = $title ?? ('Hola, '.(auth()->user()->name ?? 'Usuario'));
    $heroDescription = $description ?? 'Gestiona tus tareas desde una experiencia conectada, segura y trazable.';
    $heroChips = collect($chips ?? [])->filter(fn ($chip) => filled($chip));
@endphp

@once
    <style>
        .role-welcome-hero{position:relative;isolation:isolate;overflow:hidden;padding:34px 42px;border-radius:28px;background:linear-gradient(125deg,#153d8d,#1e69ad 55%,#31bebe);color:#fff;box-shadow:0 24px 58px rgba(24,72,161,.22)}
        .role-welcome-hero:after{content:"";position:absolute;z-index:-1;width:280px;height:280px;border-radius:50%;right:-80px;top:-120px;background:rgba(255,255,255,.09)}
        .role-welcome-hero__eyebrow{color:#fff;font-size:.78rem;letter-spacing:.16em;text-transform:uppercase;font-weight:850}
        .role-welcome-hero h1{margin:.5rem 0;color:#fff;font-size:clamp(2rem,4vw,3.25rem);font-weight:800;line-height:1.08;letter-spacing:-.035em}
        .role-welcome-hero p{max-width:850px;margin:0;color:#ddf5ff;font-size:1.08rem;line-height:1.55}
        .role-welcome-hero__chips{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}
        .role-welcome-hero__chip{padding:8px 13px;border:1px solid rgba(255,255,255,.28);border-radius:999px;background:rgba(255,255,255,.09);color:#fff;font-size:.85rem;font-weight:750}
        @media(max-width:640px){.role-welcome-hero{padding:25px 22px;border-radius:22px}.role-welcome-hero h1{font-size:2rem}.role-welcome-hero p{font-size:1rem}}
    </style>
@endonce

<section class="role-welcome-hero {{ $class ?? '' }}">
    <div class="role-welcome-hero__eyebrow">{{ $heroEyebrow }}</div>
    <h1>{{ $heroTitle }}</h1>
    <p>{{ $heroDescription }}</p>
    @if($heroChips->isNotEmpty())
        <div class="role-welcome-hero__chips">
            @foreach($heroChips as $chip)
                <span class="role-welcome-hero__chip">{{ $chip }}</span>
            @endforeach
        </div>
    @endif
</section>
