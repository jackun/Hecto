window.addEventListener('load', function () {
    if (version === 0) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open('GET', 'manifest.json');
        xmlhttp.onload = function (e) {
            var manifest = JSON.parse(xmlhttp.responseText);
            version = manifest.version;
            settings_restore();
        };
        xmlhttp.send(null);
    } else {
        settings_restore();
    }
});
(document.getElementById('save')).addEventListener('click', settings_save);