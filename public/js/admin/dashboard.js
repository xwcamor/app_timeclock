$(document).ready(function() {
    // 1. Configuración de Select2 (mantener igual)
    $('.select2-search').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder');
        },
        allowClear: true,
        minimumResultsForSearch: 1,
        dropdownParent: $('#filterBody')
    });

    // 2. Sistema de colapso MEJORADO (manteniendo expandido)
    const filterElement = document.getElementById('filterBody');
    const filterCollapse = new bootstrap.Collapse(filterElement, {
        toggle: false
    });
    
    // Estado inicial (expandido)
    filterCollapse.show();
    
    // Control del icono
    function updateIcon(isShowing) {
        const icon = $('#advancedFilterCard .collapse-icon');
        if(isShowing) {
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        } else {
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        }
    }
    
    // Eventos del colapso
    filterElement.addEventListener('shown.bs.collapse', function() {
        updateIcon(true);
    });
    
    filterElement.addEventListener('hidden.bs.collapse', function() {
        updateIcon(false);
    });
    
    // Controlador de clic manual
    document.querySelector('#advancedFilterCard .card-header').addEventListener('click', function(e) {
        e.preventDefault();
        filterCollapse.toggle();
    });

    // 3. Sistema de paginación (mantener igual)
    $('#perPageSelect').change(function() {
        const params = new URLSearchParams(window.location.search);
        params.set('per_page', $(this).val());
        params.set('page', 1);
        window.location.href = '?' + params.toString();
    });

    // 4. Sistema de filtros (ORIGINAL FUNCIONAL - NO MODIFICAR)
    $('#btnApplyFilters').click(function() {
        const params = new URLSearchParams();
        params.set('page', 1);
        
        if ($('#filterStartDate').val()) params.set('start_date', $('#filterStartDate').val());
        if ($('#filterEndDate').val()) params.set('end_date', $('#filterEndDate').val());
        if ($('#filterWorkSchedule').val() !== 'all') params.set('work_schedule', $('#filterWorkSchedule').val());
        if ($('#filterWorkEndSchedule').val() !== 'all') params.set('work_end_schedule', $('#filterWorkEndSchedule').val());
        if ($('input[name="filterLateMinutes"]:checked').val() !== 'all') params.set('late_minutes', $('input[name="filterLateMinutes"]:checked').val());
        if ($('input[name="filterOvertime"]:checked').val() !== 'all') params.set('overtime', $('input[name="filterOvertime"]:checked').val());
        if ($('#filterWorkedHours').val() || $('#filterWorkedMinutes').val()) {
            params.set('worked_hours', $('#filterWorkedHours').val() || 0);
            params.set('worked_minutes', $('#filterWorkedMinutes').val() || 0);
        }
        if ($('#filterLoginType').val() !== 'all') params.set('login_type', $('#filterLoginType').val());
        if ($('#filterAssistanceStatus').val() !== 'all') params.set('assistance_status', $('#filterAssistanceStatus').val());
        if ($('#filterEntryStatus').val() !== 'all') params.set('entry_status', $('#filterEntryStatus').val());
        if ($('#filterExitStatus').val() !== 'all') params.set('exit_status', $('#filterExitStatus').val());
        if ($('#filterName').val() !== 'all') params.set('name', $('#filterName').val());
        if ($('#filterDNI').val() !== 'all') params.set('dni', $('#filterDNI').val());
        if ($('#filterEmail').val() !== 'all') params.set('email', $('#filterEmail').val());
        if ($('#filterArea').val() !== 'all') params.set('area', $('#filterArea').val());
        if ($('#filterPosition').val() !== 'all') params.set('position', $('#filterPosition').val());
        
        window.location.href = '?' + params.toString();
    });

    // 5. Limpiar filtros (manteniendo expandido)
    $('#btnClearFilters').click(function() {
        // Forzar a que el panel quede expandido
        filterCollapse.show();
        window.location.href = '?';
    });

    // 6. Sistema de mapa (mantener igual)
    let currentMap = null;
    window.showMap = function(location, title) {
        $('#mapModalTitle').text('Ubicación de ' + title);
        const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
        mapModal.show();
        
        $('#mapModal').one('shown.bs.modal', function() {
            if (currentMap) {
                currentMap.remove();
                $('#mapContainer').empty();
            }

            if (!location || !location.includes(',')) {
                $('#mapContainer').html('<div class="alert alert-warning text-center py-4">Ubicación no disponible</div>');
                return;
            }

            const coords = location.split(',');
            if (coords.length !== 2) {
                $('#mapContainer').html('<div class="alert alert-danger text-center py-4">Formato de ubicación inválido</div>');
                return;
            }

            const lat = parseFloat(coords[0].trim());
            const lng = parseFloat(coords[1].trim());
            
            if (isNaN(lat) || isNaN(lng)) {
                $('#mapContainer').html('<div class="alert alert-danger text-center py-4">Coordenadas inválidas</div>');
                return;
            }

            currentMap = L.map('mapContainer').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(currentMap);
            
            L.marker([lat, lng]).addTo(currentMap)
                .bindPopup('<b>' + title + '</b><br>Lat: ' + lat.toFixed(6) + '<br>Lng: ' + lng.toFixed(6))
                .openPopup();

            setTimeout(() => {
                currentMap.invalidateSize();
            }, 10);
        });
    };
});