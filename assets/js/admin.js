/**
 * Admin JavaScript
 *
 * @package WordPress_Address_Autocomplete
 */

(function ($) {
    "use strict";

    var NDNCI_WPAAAdmin = {
        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
            this.toggleApiKeyField();
        },

        /**
         * Bind events
         */
        bindEvents: function () {
            var self = this;

            // Provider change
            $("#wpaa_provider").on("change", function () {
                self.toggleApiKeyField();
            });

            // Test connection button
            $("#ndnci-wpaa-test-connection").on("click", function (e) {
                e.preventDefault();
                self.testConnection();
            });

            // Clear cache button
            $("#ndnci-wpaa-clear-cache").on("click", function (e) {
                e.preventDefault();
                self.clearCache();
            });
        },

        /**
         * Toggle API key field visibility
         */
        toggleApiKeyField: function () {
            var provider = $("#wpaa_provider").val();
            var $apiKeyRow = $("#wpaa_google_maps_api_key").closest("tr");

            if (provider === "google-maps") {
                $apiKeyRow.show();
            } else {
                $apiKeyRow.hide();
            }
        },

        /**
         * Test provider connection
         */
        testConnection: function () {
            var $button = $("#ndnci-wpaa-test-connection");
            var $result = $(".ndnci-wpaa-test-result");

            $button.prop("disabled", true).text(ndnciWpaaAdminData.i18n.testing);
            $result.html("");

            $.ajax({
                url: ndnciWpaaAdminData.ajaxUrl,
                type: "POST",
                data: {
                    action: "ndnci_wpaa_test_connection",
                    nonce: ndnciWpaaAdminData.nonce,
                },
                success: function (response) {
                    if (response.success) {
                        $result.html('<span style="color: green;">✓ ' + response.data.message + "</span>");
                    } else {
                        $result.html('<span style="color: red;">✗ ' + response.data.message + "</span>");
                    }
                },
                error: function () {
                    $result.html('<span style="color: red;">✗ ' + ndnciWpaaAdminData.i18n.testFailed + "</span>");
                },
                complete: function () {
                    $button.prop("disabled", false).text($button.data("original-text") || "Test Provider Connection");
                },
            });
        },

        /**
         * Clear cache
         */
        clearCache: function () {
            var $button = $("#ndnci-wpaa-clear-cache");
            var $result = $(".ndnci-wpaa-cache-result");

            if (!confirm("Are you sure you want to clear all cached data?")) {
                return;
            }

            $button.prop("disabled", true);
            $result.html("");

            $.ajax({
                url: ndnciWpaaAdminData.ajaxUrl,
                type: "POST",
                data: {
                    action: "ndnci_wpaa_clear_cache",
                    nonce: ndnciWpaaAdminData.nonce,
                },
                success: function (response) {
                    if (response.success) {
                        $result.html('<span style="color: green;">✓ ' + response.data.message + "</span>");
                    } else {
                        $result.html('<span style="color: red;">✗ ' + response.data.message + "</span>");
                    }
                },
                error: function () {
                    $result.html('<span style="color: red;">✗ Error clearing cache</span>');
                },
                complete: function () {
                    $button.prop("disabled", false);

                    setTimeout(function () {
                        $result.fadeOut();
                    }, 3000);
                },
            });
        },
    };

    // Initialize on document ready
    $(document).ready(function () {
        NDNCI_WPAAAdmin.init();
    });
})(jQuery);
