/**
 * Frontend JavaScript
 *
 * @package WordPress_Address_Autocomplete
 */

(function ($) {
    "use strict";

    var NDNCI_WPAA = {
        searchTimeout: null,
        searchDelay: 500,
        activeField: null,

        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
            this.initMaps();
        },

        /**
         * Bind events
         */
        bindEvents: function () {
            var self = this;

            // Address autocomplete field
            $(document).on("input", ".ndnci-wpaa-address-field", function (e) {
                self.handleInput($(this));
            });

            $(document).on("focus", ".ndnci-wpaa-address-field", function (e) {
                self.activeField = $(this);
            });

            $(document).on("click", ".ndnci-wpaa-suggestion", function (e) {
                e.preventDefault();
                self.selectSuggestion($(this));
            });

            // Close suggestions on outside click
            $(document).on("click", function (e) {
                if (!$(e.target).closest(".ndnci-wpaa-address-field, .ndnci-wpaa-suggestions").length) {
                    $(".ndnci-wpaa-suggestions").hide();
                }
            });

            // Monitor address field changes for map updates
            $(document).on("change", ".ndnci-wpaa-address-field", function () {
                self.updateMaps();
            });
        },

        /**
         * Handle input event
         */
        handleInput: function ($field) {
            var self = this;
            var query = $field.val().trim();

            clearTimeout(this.searchTimeout);

            if (query.length < 3) {
                this.hideSuggestions($field);
                return;
            }

            var $suggestions = $field.siblings(".ndnci-wpaa-suggestions");
            $suggestions.html('<div class="wpaa-loading">' + ndnciWpaaData.i18n.searching + "</div>").show();

            this.searchTimeout = setTimeout(function () {
                self.search(query, $field);
            }, this.searchDelay);
        },

        /**
         * Search for addresses
         */
        search: function (query, $field) {
            var self = this;

            $.ajax({
                url: ndnciWpaaData.ajaxUrl,
                type: "POST",
                data: {
                    action: "ndnci_wpaa_search",
                    nonce: ndnciWpaaData.nonce,
                    query: query,
                },
                success: function (response) {
                    if (response.success && response.data.results) {
                        self.displaySuggestions(response.data.results, $field);
                    } else {
                        self.showError($field, response.data.message || ndnciWpaaData.i18n.error);
                    }
                },
                error: function () {
                    self.showError($field, ndnciWpaaData.i18n.error);
                },
            });
        },

        /**
         * Display suggestions
         */
        displaySuggestions: function (results, $field) {
            var $suggestions = $field.siblings(".ndnci-wpaa-suggestions");

            if (!results || results.length === 0) {
                $suggestions.html('<div class="wpaa-no-results">' + ndnciWpaaData.i18n.noResults + "</div>");
                return;
            }

            var html = "";
            $.each(results, function (index, result) {
                html +=
                    '<div class="wpaa-suggestion" data-place-id="' +
                    result.place_id +
                    '">' +
                    '<span class="wpaa-suggestion-text">' +
                    result.description +
                    "</span>" +
                    "</div>";
            });

            $suggestions.html(html).show();
        },

        /**
         * Show error
         */
        showError: function ($field, message) {
            var $suggestions = $field.siblings(".ndnci-wpaa-suggestions");
            $suggestions.html('<div class="wpaa-error">' + message + "</div>");
        },

        /**
         * Hide suggestions
         */
        hideSuggestions: function ($field) {
            $field.siblings(".ndnci-wpaa-suggestions").hide();
        },

        /**
         * Select suggestion
         */
        selectSuggestion: function ($suggestion) {
            var self = this;
            var placeId = $suggestion.data("place-id");
            var description = $suggestion.find(".ndnci-wpaa-suggestion-text").text();
            var $field = $suggestion
                .closest(".wpcf7-form-control-wrap, .wpforms-field, .gfield")
                .find(".ndnci-wpaa-address-field");
            var $placeIdField = $suggestion
                .closest(".wpcf7-form-control-wrap, .wpforms-field, .gfield")
                .find(".ndnci-wpaa-place-id");

            $field.val(description);
            $placeIdField.val(placeId);

            this.hideSuggestions($field);

            // Get place details
            this.getPlaceDetails(placeId, $field);
        },

        /**
         * Get place details
         */
        getPlaceDetails: function (placeId, $field) {
            var self = this;

            $.ajax({
                url: ndnciWpaaData.ajaxUrl,
                type: "POST",
                data: {
                    action: "ndnci_wpaa_get_place_details",
                    nonce: ndnciWpaaData.nonce,
                    place_id: placeId,
                },
                success: function (response) {
                    if (response.success && response.data.details) {
                        $field.data("place-details", response.data.details);
                        $field.trigger("wpaa_place_selected", [response.data.details]);
                        self.updateMaps();
                    }
                },
            });
        },

        /**
         * Initialize maps
         */
        initMaps: function () {
            var self = this;

            $(".ndnci-wpaa-map").each(function () {
                var $map = $(this);

                if (ndnciWpaaData.provider === "openstreetmap") {
                    self.initLeafletMap($map);
                } else if (ndnciWpaaData.provider === "google-maps") {
                    self.initGoogleMap($map);
                }
            });
        },

        /**
         * Initialize Leaflet map (OpenStreetMap)
         */
        initLeafletMap: function ($mapContainer) {
            if (typeof L === "undefined") {
                console.error("Leaflet library not loaded");
                return;
            }

            var mapId = $mapContainer.attr("id") || "wpaa-map-" + Math.random().toString(36).substr(2, 9);
            $mapContainer.attr("id", mapId);

            var map = L.map(mapId).setView([48.8566, 2.3522], 13);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);

            $mapContainer.data("map-instance", map);
            $mapContainer.data("markers", []);
        },

        /**
         * Initialize Google Map
         */
        initGoogleMap: function ($mapContainer) {
            if (typeof google === "undefined" || typeof google.maps === "undefined") {
                console.error("Google Maps library not loaded");
                return;
            }

            var map = new google.maps.Map($mapContainer[0], {
                center: { lat: 48.8566, lng: 2.3522 },
                zoom: 13,
            });

            $mapContainer.data("map-instance", map);
            $mapContainer.data("markers", []);
        },

        /**
         * Update all maps
         */
        updateMaps: function () {
            var self = this;

            $(".ndnci-wpaa-map").each(function () {
                var $map = $(this);
                var fieldIds = $map.data("fields");

                if (!fieldIds) {
                    return;
                }

                var locations = self.getLocationsFromFields(fieldIds);

                if (ndnciWpaaData.provider === "openstreetmap") {
                    self.updateLeafletMap($map, locations);
                } else if (ndnciWpaaData.provider === "google-maps") {
                    self.updateGoogleMap($map, locations);
                }
            });
        },

        /**
         * Get locations from address fields
         */
        getLocationsFromFields: function (fieldIds) {
            var locations = [];
            var fieldIdsArray = fieldIds.toString().split(",");

            $.each(fieldIdsArray, function (index, fieldId) {
                fieldId = fieldId.trim();

                // Try different selectors for different form plugins
                var $field = $(
                    '[name="input_' +
                        fieldId +
                        '"], ' +
                        '[name="wpforms[fields][' +
                        fieldId +
                        ']"], ' +
                        '[name="' +
                        fieldId +
                        '"]',
                ).filter(".ndnci-wpaa-address-field");

                if ($field.length && $field.data("place-details")) {
                    var details = $field.data("place-details");
                    if (details.location) {
                        locations.push({
                            lat: details.location.lat,
                            lng: details.location.lng,
                            description: details.description,
                        });
                    }
                }
            });

            return locations;
        },

        /**
         * Update Leaflet map
         */
        updateLeafletMap: function ($mapContainer, locations) {
            var map = $mapContainer.data("map-instance");
            var markers = $mapContainer.data("markers") || [];
            var mode = $mapContainer.data("mode") || "markers";

            if (!map || !locations.length) {
                return;
            }

            // Clear existing markers
            $.each(markers, function (index, marker) {
                map.removeLayer(marker);
            });
            markers = [];

            // Add new markers
            var bounds = [];
            $.each(locations, function (index, location) {
                var marker = L.marker([location.lat, location.lng]).addTo(map);
                marker.bindPopup(location.description);
                markers.push(marker);
                bounds.push([location.lat, location.lng]);
            });

            $mapContainer.data("markers", markers);

            // Draw route if mode is route
            if (mode === "route" && locations.length > 1) {
                var polyline = L.polyline(bounds, { color: "#3388ff" }).addTo(map);
                markers.push(polyline);
            }

            // Fit bounds
            if (bounds.length > 0) {
                map.fitBounds(bounds);
            }
        },

        /**
         * Update Google Map
         */
        updateGoogleMap: function ($mapContainer, locations) {
            var map = $mapContainer.data("map-instance");
            var markers = $mapContainer.data("markers") || [];
            var mode = $mapContainer.data("mode") || "markers";

            if (!map || !locations.length) {
                return;
            }

            // Clear existing markers
            $.each(markers, function (index, marker) {
                marker.setMap(null);
            });
            markers = [];

            // Add new markers
            var bounds = new google.maps.LatLngBounds();
            $.each(locations, function (index, location) {
                var marker = new google.maps.Marker({
                    position: { lat: location.lat, lng: location.lng },
                    map: map,
                    title: location.description,
                });

                var infoWindow = new google.maps.InfoWindow({
                    content: location.description,
                });

                marker.addListener("click", function () {
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
                bounds.extend(marker.getPosition());
            });

            $mapContainer.data("markers", markers);

            // Draw route if mode is route
            if (mode === "route" && locations.length > 1) {
                var path = locations.map(function (loc) {
                    return { lat: loc.lat, lng: loc.lng };
                });

                var polyline = new google.maps.Polyline({
                    path: path,
                    geodesic: true,
                    strokeColor: "#3388ff",
                    strokeOpacity: 1.0,
                    strokeWeight: 2,
                });

                polyline.setMap(map);
                markers.push(polyline);
            }

            // Fit bounds
            if (locations.length > 0) {
                map.fitBounds(bounds);
            }
        },
    };

    // Initialize on document ready
    $(document).ready(function () {
        NDNCI_WPAA.init();
    });

    // Expose NDNCI_WPAA object globally
    window.NDNCI_WPAA = NDNCI_WPAA;
})(jQuery);
