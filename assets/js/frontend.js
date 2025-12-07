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

            // Reposition suggestions on scroll/resize
            $(window).on("scroll resize", function () {
                if (self.activeField && self.activeField.length) {
                    var $suggestions = self.getSuggestionsContainer(self.activeField);
                    if ($suggestions.is(":visible")) {
                        self.positionSuggestions(self.activeField, $suggestions);
                    }
                }
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

            var $suggestions = this.getSuggestionsContainer($field);

            if ($suggestions.length === 0) {
                return;
            }

            // Position suggestions relative to field
            this.positionSuggestions($field, $suggestions);

            $suggestions.html('<div class="ndnci-wpaa-loading">' + ndnciWpaaData.i18n.searching + "</div>").show();

            this.searchTimeout = setTimeout(function () {
                self.search(query, $field);
            }, this.searchDelay);
        },

        /**
         * Position suggestions container relative to field
         */
        positionSuggestions: function ($field, $suggestions) {
            // Try to find the wrapper first
            var $wrapper = $field.closest(".ndnci-wpaa-field-wrapper");

            if ($wrapper.length) {
                // If wrapper exists, append suggestions to it (it will be inside the label if wrapper is inside label)
                if ($suggestions.parent()[0] !== $wrapper[0]) {
                    $wrapper.append($suggestions);
                }
            } else {
                // Fallback: try to find parent label
                var $parent = $field.closest("label");

                // If no label, use direct parent
                if ($parent.length === 0) {
                    $parent = $field.parent();
                }

                // Append to the parent (inside the label or parent div)
                if ($suggestions.parent()[0] !== $parent[0]) {
                    $parent.append($suggestions);
                }
            }

            $suggestions.css({
                position: "relative", // Relative to flow naturally in the document
                top: "auto",
                left: "auto",
                width: "100%",
                "z-index": 9999,
                display: "block",
            });
        },

        /**
         * Get suggestions container for a field
         */
        getSuggestionsContainer: function ($field) {
            var fieldName = $field.attr("name");

            // Try to find in wrapper first (CF7, WPForms)
            var $wrapper = $field.closest(".ndnci-wpaa-field-wrapper, .wpforms-field");
            if ($wrapper.length) {
                var $suggestions = $wrapper.find(".ndnci-wpaa-suggestions");
                if ($suggestions.length) {
                    return $suggestions;
                }
            }

            // CF7 might extract elements outside wrapper - look in parent label/p
            var $parentLabel = $field.closest("label, p, div.field-wrapper");
            if ($parentLabel.length) {
                var $parentSuggestions = $parentLabel.find(".ndnci-wpaa-suggestions");
                if ($parentSuggestions.length) {
                    return $parentSuggestions;
                }

                // Look for next element after parent (CF7 structure)
                var $nextSuggestions = $parentLabel.next(".ndnci-wpaa-suggestions");
                if ($nextSuggestions.length) {
                    return $nextSuggestions;
                }
            }

            // Look for next sibling suggestions (after wrapper)
            if ($wrapper.length) {
                var $nextSuggestions = $wrapper.next(".ndnci-wpaa-suggestions");
                if ($nextSuggestions.length) {
                    return $nextSuggestions;
                }
            }

            // Last resort - find all suggestions and match by proximity or field name pattern
            var $allSuggestions = $(".ndnci-wpaa-suggestions");

            if ($allSuggestions.length > 0) {
                // Try to find the closest one to our field
                var fieldOffset = $field.offset();
                var closest = null;
                var minDistance = Infinity;

                $allSuggestions.each(function () {
                    var $this = $(this);
                    var offset = $this.offset();
                    if (offset) {
                        var distance =
                            Math.abs(offset.top - fieldOffset.top) + Math.abs(offset.left - fieldOffset.left);
                        if (distance < minDistance) {
                            minDistance = distance;
                            closest = $this;
                        }
                    }
                });

                if (closest) {
                    return $(closest);
                }
            }

            // Fallback to siblings
            return $field.siblings(".ndnci-wpaa-suggestions");
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
            var $suggestions = this.getSuggestionsContainer($field);

            if (!results || results.length === 0) {
                $suggestions.html('<div class="ndnci-wpaa-no-results">' + ndnciWpaaData.i18n.noResults + "</div>");
                return;
            }

            var html = "";
            $.each(results, function (index, result) {
                var displayText = result.description;

                // Format address if available
                if (result.address) {
                    var parts = [];
                    // Address (Street)
                    if (result.address.street) {
                        parts.push(result.address.street);
                    }
                    // Postal Code
                    if (result.address.postal_code) {
                        parts.push(result.address.postal_code);
                    }
                    // City
                    if (result.address.city) {
                        parts.push(result.address.city);
                    }

                    if (parts.length > 0) {
                        displayText = parts.join(", ");
                    }
                }

                html +=
                    '<div class="ndnci-wpaa-suggestion" data-place-id="' +
                    result.place_id +
                    '" title="' +
                    result.description +
                    '">' +
                    '<span class="ndnci-wpaa-suggestion-text">' +
                    displayText +
                    "</span>" +
                    "</div>";
            });

            $suggestions.html(html);

            // Position and show suggestions
            this.positionSuggestions($field, $suggestions);
            $suggestions.show();
        },

        /**
         * Show error
         */
        showError: function ($field, message) {
            var $suggestions = this.getSuggestionsContainer($field);
            $suggestions.html('<div class="ndnci-wpaa-error">' + message + "</div>");
        },

        /**
         * Get place ID field for an address field
         */
        getPlaceIdField: function ($field) {
            var fieldName = $field.attr("name");
            var placeIdName = fieldName + "_place_id";

            // Try in wrapper first
            var $wrapper = $field.closest(".ndnci-wpaa-field-wrapper, .wpforms-field, .gfield");
            if ($wrapper.length) {
                var $placeIdField = $wrapper.find(".ndnci-wpaa-place-id");
                if ($placeIdField.length) {
                    return $placeIdField;
                }
            }

            // Look in parent
            var $parent = $field.closest("label, p, div.field-wrapper");
            if ($parent.length) {
                var $placeIdField = $parent.find('.ndnci-wpaa-place-id[name="' + placeIdName + '"]');
                if ($placeIdField.length) {
                    return $placeIdField;
                }

                // Look after parent
                var $nextPlaceId = $parent.next(".ndnci-wpaa-place-id");
                if ($nextPlaceId.length && $nextPlaceId.attr("name") === placeIdName) {
                    return $nextPlaceId;
                }
            }

            // Search by name anywhere in form
            var $form = $field.closest("form");
            var $placeIdByName = $form.find('input[name="' + placeIdName + '"]');
            if ($placeIdByName.length) {
                return $placeIdByName;
            }

            return $();
        },

        /**
         * Hide suggestions
         */
        hideSuggestions: function ($field) {
            this.getSuggestionsContainer($field).hide();
        },

        /**
         * Select suggestion
         */
        selectSuggestion: function ($suggestion) {
            var self = this;
            var placeId = $suggestion.data("place-id");
            var description = $suggestion.find(".ndnci-wpaa-suggestion-text").text();

            // Find the wrapper and field
            var $wrapper = $suggestion.closest(".ndnci-wpaa-field-wrapper, .wpforms-field, .gfield, label, p");
            var $field = $wrapper.find(".ndnci-wpaa-address-field");

            // If not found in wrapper, the suggestion might be outside - find closest field
            if ($field.length === 0) {
                var $allFields = $(".ndnci-wpaa-address-field");
                var suggestionOffset = $suggestion.offset();
                var closest = null;
                var minDistance = Infinity;

                $allFields.each(function () {
                    var $this = $(this);
                    var offset = $this.offset();
                    if (offset) {
                        var distance = Math.abs(offset.top - suggestionOffset.top);
                        if (distance < minDistance) {
                            minDistance = distance;
                            closest = $this;
                        }
                    }
                });

                if (closest) {
                    $field = $(closest);
                }
            }

            // Find place_id field using helper
            var $placeIdField = this.getPlaceIdField($field);

            $field.val(description);
            if ($placeIdField.length) {
                $placeIdField.val(placeId);
            }

            this.hideSuggestions($field);

            // Get place details
            this.getPlaceDetails(placeId, $field);
        },
        /**
         * Get place details
         */ getPlaceDetails: function (placeId, $field) {
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
            var $maps = $(".ndnci-wpaa-map");

            if ($maps.length === 0) {
                return;
            }

            $maps.each(function () {
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
         */ initLeafletMap: function ($mapContainer) {
            if (typeof L === "undefined") {
                console.error("WPAA: Leaflet library not loaded.");
                return;
            }

            var mapId = $mapContainer.attr("id");
            if (!mapId) {
                mapId = "ndnci-wpaa-map-" + Math.random().toString(36).substr(2, 9);
                $mapContainer.attr("id", mapId);
            }

            try {
                var map = L.map(mapId).setView([48.8566, 2.3522], 13);

                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution:
                        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                }).addTo(map);

                $mapContainer.data("map-instance", map);
                $mapContainer.data("markers", []);
            } catch (error) {
                console.error("WPAA: Error initializing Leaflet map:", error);
            }
        },

        /**
         * Initialize Google Map
         */
        initGoogleMap: function ($mapContainer) {
            if (typeof google === "undefined" || typeof google.maps === "undefined") {
                console.error("WPAA: Google Maps library not loaded.");
                return;
            }

            try {
                var map = new google.maps.Map($mapContainer[0], {
                    center: { lat: 48.8566, lng: 2.3522 },
                    zoom: 13,
                });

                $mapContainer.data("map-instance", map);
                $mapContainer.data("markers", []);
            } catch (error) {
                console.error("WPAA: Error initializing Google Map:", error);
            }
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
                // Contact Form 7: [name="fieldname"]
                // Gravity Forms: [name="input_X"]
                // WPForms: [name="wpforms[fields][X]"]
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
