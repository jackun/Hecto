var default_server = 'lusikas.com',
    get_version = function () {
        return '{{VERSION}}';
    },
    get_host = function() {
        var result = localStorage.server || default_server;
        if(result.indexOf('tiny.pri.ee') >= 0) {
            localStorage.server = default_server;
            result = default_server;
        }
        return result;
    },
    get_bkey = function() {
         return localStorage.bkey || '';
    },
    settings_restore = function() {
        var bkey = get_bkey();
        if(bkey){
            $('#bkey').val(bkey);
            $('#bkey_link').html(
                "<a href='http://" + get_host() + "/?bkey=" + bkey + "'>Your playlist</a>"
            );
        } else {
            $('#bkey').val('');
        }
        $('#server').val(get_host());
        $('#version').text(get_version());
    },

    settings_save = function(){
        if($("#bkey").val()){
            localStorage.bkey = $("#bkey").val();
        }
        localStorage.server = $('#server').val() || default_server;
        settings_restore();
        $('#status').html("<font color='green' size=+2>Saved!</font>").fadeIn().delay(1000).fadeOut('slow');
    };

