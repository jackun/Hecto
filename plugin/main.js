var default_server = 'lusikas.com',
    version = 0,
    get_version = function () {
        return version;
    },
    get_host = function () {
        return localStorage.server || default_server;
    },
    get_bkey = function () {
        return localStorage.bkey || '';
    },
    settings_restore = function () {
        var bkey = get_bkey();
        if (bkey) {
            $('#bkey').val(bkey);
            $('#bkey_link').html(
                "<a target=_blank href='http://" + get_host() +
                "/?bkey=" + bkey + "'>Your playlist</a>");
        } else {
            $('#bkey').val('');
        }
        $('#server').val(get_host());
        $('#version').text(get_version());
    },
    settings_save = function () {
        localStorage.bkey = $("#bkey").val();
        localStorage.server = $('#server').val() || default_server;
        settings_restore();
        if ($("#bkey").val()) {
            $('#status').html("<font color='green' size=+2>Saved!</font>").fadeIn().delay(1000).fadeOut('slow');
        } else {
            $('#status').html("<font color='red' size=+2>All fields!</font>").fadeIn().delay(1000).fadeOut('slow');
        }
    };

window.addEventListener('load', function () {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET', 'manifest.json');
    xmlhttp.onload = function (e) {
        var manifest = JSON.parse(xmlhttp.responseText);
        version = manifest.version;
    };
    xmlhttp.send(null);
});