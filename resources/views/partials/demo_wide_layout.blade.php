<style>
    /* Shared page width; dialogs keep their own readable dimensions. */
    body .home-shell, body .shell, body .page-shell, body .admin-shell, body .max-w-7xl,
    body .container:not(.modal .container):not(dialog .container) {
        box-sizing:border-box;
        width:100% !important;
        max-width:none !important;
        margin-left:auto !important;
        margin-right:auto !important;
        padding-left:clamp(12px,1.4vw,28px) !important;
        padding-right:clamp(12px,1.4vw,28px) !important;
        min-width:0;
    }
    body:has(.totem-header-row){display:block;padding:0}
    body:has(.totem-header-row) main.shell{padding:0 !important;width:calc(100% - 24px) !important;margin:16px auto}
    body:has(.totem-header-row) .body{padding:clamp(18px,2vw,32px)}
    .demo-guide-grid > *, .actions > *, .content-grid > *{min-width:0;overflow-wrap:anywhere}
    @media(min-width:1400px){
        body .demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:repeat(4,minmax(0,1fr))}
    }
    @media(min-width:2100px){
        body .demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:repeat(7,minmax(0,1fr))}
    }
</style>
