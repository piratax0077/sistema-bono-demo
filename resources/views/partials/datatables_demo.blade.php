@once
    <link rel="stylesheet" href="https://cdn.datatables.net/3.0.4/css/dataTables.dataTables.min.css">
    <style>
        .dt-container{padding:1rem}.dt-layout-row{gap:.8rem;align-items:center}.dt-search input,.dt-length select{border:1px solid #c9d8ef!important;border-radius:10px!important;background:#fff!important;padding:.5rem .65rem!important}.dt-search input:focus,.dt-length select:focus{border-color:#31bebe!important;box-shadow:0 0 0 .2rem rgba(49,190,190,.14)!important;outline:0}.dt-info{color:#6f8095!important;font-size:.86rem}.dt-paging-button{border:1px solid #d4dfef!important;border-radius:9px!important;background:#fff!important;color:#1848a1!important}.dt-paging-button.current{background:#1848a1!important;color:#fff!important;border-color:#1848a1!important}.dt-paging-button.disabled{opacity:.45}.dt-empty{text-align:center!important;color:#7b8798!important;padding:2.5rem 1rem!important}.dt-scroll-body{border-bottom:0!important}
        @media(max-width:640px){.dt-container{padding:.75rem}.dt-layout-row{display:grid!important;grid-template-columns:1fr}.dt-search,.dt-length{text-align:left!important}.dt-search input{width:100%}}
    </style>
    <script src="https://cdn.datatables.net/3.0.4/js/dataTables.min.js"></script>
    <script>
        (() => {
            const idioma = {
                emptyTable: 'No hay registros disponibles',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                lengthMenu: 'Mostrar _MENU_ registros',
                loadingRecords: 'Cargando…',
                processing: 'Procesando…',
                search: 'Buscar:',
                searchPlaceholder: 'Escriba para filtrar',
                zeroRecords: 'No se encontraron resultados',
                paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                aria: { orderable: 'Ordenar por esta columna', orderableReverse: 'Invertir el orden de esta columna' }
            };

            const iniciar = () => {
                if (typeof DataTable === 'undefined') return;

                document.querySelectorAll('table').forEach((tabla, indice) => {
                    if (tabla.dataset.datatable === 'false' || tabla.dataset.datatableReady === '1') return;
                    if (!tabla.querySelector('thead th') || !tabla.querySelector('tbody')) return;

                    tabla.querySelectorAll('tbody > tr').forEach(fila => {
                        const celda = fila.children.length === 1 ? fila.children[0] : null;
                        if (celda && Number(celda.getAttribute('colspan') || 1) > 1) fila.remove();
                    });

                    tabla.dataset.datatableReady = '1';
                    tabla.id ||= `tabla-demo-${indice + 1}`;
                    new DataTable(tabla, {
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        language: idioma,
                        autoWidth: false,
                        order: []
                    });
                });
            };

            document.readyState === 'loading'
                ? document.addEventListener('DOMContentLoaded', iniciar)
                : iniciar();
        })();
    </script>
@endonce
