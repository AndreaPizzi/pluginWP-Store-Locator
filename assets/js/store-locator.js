(function ($) {

    const StoreLocator = {

        map: null,
        markers: [],
        allStores: [],

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
            this.bindEvents();
            this.loadInitialData();
        },

        initMap() {
            let styles = null;

            if (SL_Config.map_style) {
                try {
                    styles = JSON.parse(SL_Config.map_style);
                } catch (e) {
                    console.warn('Invalid Map Style JSON');
                }
            }

            this.map = new google.maps.Map(document.getElementById('sl-map'), {
                zoom: this.initialZoom,
                center: this.initialCenter,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                gestureHandling: "greedy",
                styles: styles
            });

            this.infoWindow = new google.maps.InfoWindow();
        },

        bindEvents() {

            $('#sl-state').on('change', (e) => {
                this.currentState = e.target.value;
                this.currentCity = '';
                this.updateCities();
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

        /*
        =====================================
        INITIAL LOAD
        =====================================
        */

        loadInitialData() {

            $.post(SL_Config.ajax_url, {
                action: 'sl_get_initial_data'
            }, (response) => {

                if (!response.success) return;

                const data = response.data;

                this.populateStates(data.states);
                this.populateTipologie(data.tipologie);
                this.buildLegend(data.tipologie);

                this.allStores = data.stores;

                this.renderMarkers(this.allStores);
            });
        },

        populateStates(states) {

            const select = $('#sl-state');
            select.html('<option value="">Seleziona Stato</option>');

            states.forEach(state => {
                select.append(`<option value="${state}">${state}</option>`);
            });
        },

        populateTipologie(tipologie) {

            const select = $('#sl-tipologia');
            select.html('<option value="">Seleziona Tipologia</option>');

            tipologie.forEach(term => {
                select.append(
                    `<option value="${term.slug}">${term.name}</option>`
                );
            });
        },

        /*
        =====================================
        CITY FILTER (client-side)
        =====================================
        */

        updateCities() {

            const citySelect = $('#sl-city');
            citySelect
                .prop('disabled', true)
                .html('<option value="">Seleziona Città</option>');

            if (!this.currentState) return;

            const cities = [...new Set(
                this.allStores
                    .filter(store => store.state === this.currentState)
                    .map(store => store.city)
            )];

            cities.sort();

            cities.forEach(city => {
                citySelect.append(`<option value="${city}">${city}</option>`);
            });

            citySelect.prop('disabled', false);
        },

        /*
        =====================================
        FILTER STORES (client-side)
        =====================================
        */

        fetchStores() {

            let filtered = this.allStores;

            if (this.currentState) {
                filtered = filtered.filter(store =>
                    store.state === this.currentState
                );
            }

            if (this.currentCity) {
                filtered = filtered.filter(store =>
                    store.city === this.currentCity
                );
            }

            if (this.currentTipologia) {
                filtered = filtered.filter(store =>
                    store.tipologia === this.currentTipologia
                );
            }

            this.clearMarkers();
            this.renderMarkers(filtered);
        },

        /*
        =====================================
        MARKERS
        =====================================
        */

        renderMarkers(stores) {

            if (!stores.length) return;

            const bounds = new google.maps.LatLngBounds();

            stores.forEach(store => {

                const position = {
                    lat: parseFloat(store.lat),
                    lng: parseFloat(store.lng)
                };

                const iconUrl = store.marker_icon
                    ? store.marker_icon
                    : SL_Config.default_marker;

                const marker = new google.maps.Marker({
                    position: position,
                    map: this.map,
                    title: store.title,
                    icon: iconUrl ? {
                        url: iconUrl,
                        scaledSize: new google.maps.Size(60, 60)
                    } : null
                });

                marker.addListener('click', () => {
                    this.openInfoWindow(marker, store);
                });

                this.markers.push(marker);
                bounds.extend(position);
            });

            // FitBounds intelligente
            if (!this.isResetting) {

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

        /*
        =====================================
        RESET
        =====================================
        */

        resetFilters() {

            this.currentState = '';
            this.currentCity = '';
            this.currentTipologia = '';

            $('#sl-state').val('');
            $('#sl-city')
                .val('')
                .prop('disabled', true)
                .html('<option value="">Seleziona Città</option>');
            $('#sl-tipologia').val('');

            this.clearMarkers();

            this.map.setZoom(this.initialZoom);
            this.map.setCenter(this.initialCenter);

            this.isResetting = true;

            this.renderMarkers(this.allStores);

            this.toggleResetButton();
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

        /*
        =====================================
        LEGEND
        =====================================
        */

        buildLegend(tipologie) {

            const legend = $('#sl-legend');
            if (!legend.length) return;

            legend.html('');

            tipologie.forEach(term => {

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
        }

    };

    $(document).ready(function () {

        if ($('#sl-map').length) {
            StoreLocator.init();
        }

    });

})(jQuery);