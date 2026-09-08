<script>
document.addEventListener('DOMContentLoaded', function () {
    const cleanRut = value => (value || '').toUpperCase().replace(/[^0-9K]/g, '').slice(0, 9);
    const formatRut = value => {
        const clean = cleanRut(value);
        if (clean.length < 2) return clean;
        const body = clean.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return body + '-' + clean.slice(-1);
    };
    const validRut = value => {
        const clean = cleanRut(value);
        if (!/^\d{7,8}[0-9K]$/.test(clean)) return false;
        const body = clean.slice(0, -1);
        let sum = 0, multiplier = 2;
        for (let i = body.length - 1; i >= 0; i--) {
            sum += Number(body[i]) * multiplier;
            multiplier = multiplier === 7 ? 2 : multiplier + 1;
        }
        const result = 11 - (sum % 11);
        const verifier = result === 11 ? '0' : (result === 10 ? 'K' : String(result));
        return verifier === clean.slice(-1);
    };
    document.querySelectorAll('[data-rut-input]').forEach(input => {
        input.value = formatRut(input.value);
        input.setAttribute('inputmode', 'text');
        input.setAttribute('maxlength', '12');
        input.addEventListener('input', () => {
            const end = input.value.length;
            input.value = formatRut(input.value);
            input.setCustomValidity('');
            input.setSelectionRange(Math.min(end + 1, input.value.length), Math.min(end + 1, input.value.length));
        });
        input.addEventListener('blur', () => {
            input.value = formatRut(input.value);
            const clean = cleanRut(input.value);
            input.setCustomValidity(clean && !validRut(clean) ? 'Ingrese un RUT chileno válido.' : '');
        });
    });
});
</script>
