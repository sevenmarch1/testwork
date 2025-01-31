jQuery(document).ready(function($) {
    function loadWeatherData(search = '') {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_weather_data',
                search: search,
                nonce: weatherNonce
            },
            success: function(response) {
                $('#weather-table tbody').html(response);
            }
        });
    }

    $('#weather-search').on('input', function() {
        let searchVal = $(this).val();
        loadWeatherData(searchVal);
    });
});