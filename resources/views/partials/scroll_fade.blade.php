<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
html, body, table, th, td, input, select, textarea, button, .btn, .nav, .navbar, .menu{font-family:'Manrope', sans-serif}
.fade-reveal{opacity:0;transform:translateY(38px) scale(.97);transition:opacity .85s cubic-bezier(.16,1,.3,1),transform .85s cubic-bezier(.16,1,.3,1);will-change:opacity,transform}
.fade-reveal.is-visible{opacity:1;transform:none}
@media (prefers-reduced-motion: reduce){.fade-reveal{opacity:1;transform:none;transition:none}}
html.page-fade-ready body{opacity:0}
html.page-fade-in body{opacity:1;transition:opacity .32s ease}
html.page-fade-out body{opacity:0 !important;transition:opacity .22s ease}
@media (prefers-reduced-motion: reduce){html.page-fade-ready body,html.page-fade-out body{opacity:1 !important;transition:none !important}}
</style>
<script>document.documentElement.classList.add('page-fade-ready');</script>
<script>
(function(){
    var pending = [];
    var ticking = false;

    function reveal(el){
        el.classList.add('is-visible');
    }

    function checkPending(){
        var viewportH = window.innerHeight || document.documentElement.clientHeight;
        pending = pending.filter(function(el){
            var rect = el.getBoundingClientRect();
            var visible = rect.top < viewportH * 0.82 && rect.bottom > 0;
            if(visible){
                reveal(el);
                return false;
            }
            return true;
        });
        ticking = false;
        if(!pending.length){
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
        }
    }

    function onScroll(){
        if(!ticking){
            ticking = true;
            window.requestAnimationFrame(checkPending);
        }
    }

    function initScrollFade(){
        var selector = '.card, .hero, .role, .action, .option, .slot, .metric, .stat, .trust-item, .security-step, .flow-step, .voucher, .result, .panel, table, .card-home, .appointment, .demo-guide-step';
        var targets = document.querySelectorAll(selector);
        if(!targets.length) return;

        var siblingIndex = new Map();
        targets.forEach(function(el){
            el.classList.add('fade-reveal');
            var parent = el.parentElement;
            var count = siblingIndex.get(parent) || 0;
            el.style.transitionDelay = (Math.min(count, 5) * 70) + 'ms';
            siblingIndex.set(parent, count + 1);
        });

        pending = Array.prototype.slice.call(targets);
        checkPending();

        if(pending.length){
            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll);
        }
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initScrollFade);
    } else {
        initScrollFade();
    }

    window.addEventListener('load', function(){
        if(pending.length){ checkPending(); }
    });

    function initPageFadeIn(){
        var html = document.documentElement;
        html.classList.remove('page-fade-ready');
        html.classList.add('page-fade-in');
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initPageFadeIn);
    } else {
        initPageFadeIn();
    }

    document.addEventListener('click', function(event){
        if(event.defaultPrevented || event.button !== 0) return;
        if(event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        var link = event.target.closest ? event.target.closest('a[href]') : null;
        if(!link) return;
        var href = link.getAttribute('href');
        if(!href || href.charAt(0) === '#') return;
        if(link.target && link.target !== '' && link.target !== '_self') return;
        if(link.hasAttribute('download')) return;
        if(/^(mailto:|tel:|javascript:)/i.test(href)) return;
        if(link.origin !== window.location.origin) return;

        event.preventDefault();
        document.documentElement.classList.add('page-fade-out');
        setTimeout(function(){ window.location.href = link.href; }, 200);
    });

    window.addEventListener('pageshow', function(event){
        if(event.persisted){
            document.documentElement.classList.remove('page-fade-out', 'page-fade-ready');
            document.documentElement.classList.add('page-fade-in');
        }
    });
})();
</script>
