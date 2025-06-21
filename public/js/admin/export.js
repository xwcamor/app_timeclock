$(document).ready(function() {
    if (!$.fn.dataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json"
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"lfB>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    className: 'btn btn-success me-2',
                    exportOptions: {
                        // Excluye la última columna (Acciones)
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="bi bi-filetype-csv"></i> CSV',
                    className: 'btn btn-info me-2',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    className: 'btn btn-danger me-2',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Imprimir',
                    className: 'btn btn-secondary',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ]
        });
    }
});