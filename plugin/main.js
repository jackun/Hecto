var default_server = 'lusikas.com',
    get_host = function(){
        return localStorage.server || default_server;
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
    }

function settings_save(){
    if($("#bkey").val()){
        localStorage.bkey = $("#bkey").val();
    }
    localStorage.server = $('#server').val() || default_server;
    settings_restore();
    $('#status').html("<font color='green' size=+2>Saved!</font>").fadeIn().delay(1000).fadeOut('slow');
}

