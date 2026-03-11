(function ($) {

    const StoreLocator = {

        

        map: null,
        markers: [],
        currentState: '',
        currentCity: '',
        currentTipologia: '',
        infoWindow: null,
        initialCenter: { lat: 41.8719, lng: 12.5674 },
        initialZoom: 6,
        hasInitialized: false,
        isResetting: false,

        init() {
            
            this.initMap();
            this.loadStates();
            this.bindEvents();
            this.fetchStores(); // ← carica tutti gli store subito
            this.loadTipologie();
            this.buildLegend();
        },

        initMap() {
            
            this.map = new google.maps.Map(document.getElementById('sl-map'), {
                zoom: 6,
                center: this.initialCenter, // Italia default
                zoomControl: this.initialZoom,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                gestureHandling: "greedy"
            });
            this.infoWindow = new google.maps.InfoWindow();
        },

        bindEvents() {

            $('#sl-state').on('change', (e) => {
                this.currentState = e.target.value;
                this.currentCity = '';
                this.loadCities(this.currentState);
                this.fetchStores();
                this.toggleResetButton();
            });

            $('#sl-city').on('change', (e) => {
                this.currentCity = e.target.value;
                this.fetchStores();
                this.toggleResetButton();
            });

            $('#sl-tipologia').on('change', (e) => {
                this.currentTipologia = e.target.value;
                this.fetchStores();
                this.toggleResetButton();
            });

            $('#sl-reset').on('click', () => {
                this.resetFilters();
            });
        },

        loadStates() {

            $.post(SL_Config.ajax_url, {
                action: 'sl_get_states'
            }, (response) => {
                
                if (!response.success) return;

                const select = $('#sl-state');
                response.data.forEach(state => {
                    select.append(`<option value="${state}">${state}</option>`);
                });

            });
        },

        loadTipologie() {

            $.post(SL_Config.ajax_url, {
                action: 'sl_get_tipologie'
            }, (response) => {

                if (!response.success) return;

                const select = $('#sl-tipologia');

                response.data.forEach(term => {
                    select.append(
                        `<option value="${term.slug}">${term.name}</option>`
                    );
                });

            });
        },

        loadCities(state) {
            const citySelect = $('#sl-city');
            citySelect.prop('disabled', true).html('<option value="">Seleziona Città</option>');

            if (!state) return;

            $.post(SL_Config.ajax_url, {
                action: 'sl_get_cities',
                state: state
            }, (response) => {

               

                if (!response.success) return;

                response.data.forEach(city => {
                    citySelect.append(`<option value="${city}">${city}</option>`);
                });

                citySelect.prop('disabled', false);

            });
        },

        fetchStores() {

            $.post(SL_Config.ajax_url, {
                action: 'sl_get_stores',
                state: this.currentState,
                city: this.currentCity,
                tipologia: this.currentTipologia
            }, (response) => {

                if (!response.success) return;

                this.clearMarkers();
                this.renderMarkers(response.data);

            });
        },

        renderMarkers(stores) {

            if (!stores.length) return;

            const bounds = new google.maps.LatLngBounds();

            stores.forEach(store => {

                const position = {
                    lat: parseFloat(store.lat),
                    lng: parseFloat(store.lng)
                };

                const iconUrl = store.marker_icon ? store.marker_icon : SL_Config.default_marker;

                const marker = new google.maps.Marker({
                    position: position,
                    map: this.map,
                    title: store.title,
                    icon: iconUrl ? {
                        url: iconUrl,
                        scaledSize: new google.maps.Size(60, 60) // dimensione personalizzata
                    } : null
                   // icon: store.marker_icon ? store.marker_icon : SL_Config.default_marker

                });

                marker.addListener('click', () => {
                    this.openInfoWindow(marker, store);
                });

                this.markers.push(marker);
                bounds.extend(position);
            });

            if (!this.isResetting) {

            // Primo caricamento o filtro attivo
            this.map.fitBounds(bounds);

            google.maps.event.addListenerOnce(this.map, 'bounds_changed', () => {
                if (this.map.getZoom() > 14) {
                    this.map.setZoom(14);
                }
            });

        }

        this.hasInitialized = true;
        this.isResetting = false;
        },

        openInfoWindow(marker, store) {
            const content = `
                <div class="sl-infowindow">
                    <h4>${store.title}</h4>
                    <p>${store.address}</p>
                    <p>${store.city} - ${store.state}</p>
                </div>
            `;

            this.infoWindow.setContent(content);
            this.infoWindow.open(this.map, marker);
        },

        clearMarkers() {
            this.markers.forEach(marker => marker.setMap(null));
            this.markers = [];
        },

        toggleResetButton() {
            const hasFilter =
                this.currentState ||
                this.currentCity ||
                this.currentTipologia;

            if (hasFilter) {
                $('#sl-reset').fadeIn(150);
            } else {
                $('#sl-reset').fadeOut(150);
            }
        },

        resetFilters() {

            // Reset stato interno
            this.currentState = '';
            this.currentCity = '';
            this.currentTipologia = '';

            // Reset select UI
            $('#sl-state').val('');
            $('#sl-city')
                .val('')
                .prop('disabled', true)
                .html('<option value="">Seleziona Città</option>');

            $('#sl-tipologia').val('');

            // Reset marker
            this.clearMarkers();

            // Reset zoom + center
            this.map.setZoom(this.initialZoom);
            this.map.setCenter(this.initialCenter);

            this.isResetting = true;

            // Ricarica tutti gli store
            this.fetchStores();

            this.toggleResetButton();
        },
        
        buildLegend() {

            $.post(SL_Config.ajax_url, {
                action: 'sl_get_tipologie'
            }, (response) => {

                if (!response.success) return;

                const legend = $('#sl-legend');
                legend.html('');

                response.data.forEach(term => {

                    const icon = term.marker_icon 
                        ? term.marker_icon 
                        : SL_Config.default_marker;

                    legend.append(`
                        <div class="sl-legend-item">
                            <img src="${icon}" />
                            <span>${term.name}</span>
                        </div>
                    `);
                });

            });
        },

    };

    $(document).ready(function () {


        if ($('#sl-map').length) {
            StoreLocator.init();
        }
    });

    

})(jQuery);