function cookie(name, value) {
    if (name && value !== undefined) {
        $.setCookie(name, value, {
            duration: 5000
        });
        return value;
    }
    if (name) {
        return $.readCookie(name);
    }
}

function b2KMGb(bytes) {
    var units = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'],
        n = 0,
        unit = units[n];
    while (bytes >= 1024) {
        n += 1;
        unit = units[n];
        bytes = bytes / 1024;
        if (n > 8) {
            break;
        }
    }
    bytes = Math.round(bytes * 100) / 100;
    return bytes + unit;
}

function update_cookies(what, value) {
    if (what === 'shuffle') {
        cookie('shuffle', $('#shuffle').prop('checked') ? '1' : '0');
    } else if (what === 'volume' && value) {
        cookie('volume', value);
    } else if (what === 'BW' && value) {
        var cbw = parseInt(cookie('BW') || "0", 10);
        cookie('BW', cbw + value);
        $('#bw').html(b2KMGb(cbw));
    }
}

function add_one_play(watch) {
    if (current_check == watch) {
        $.ajax({
            type: "GET",
            url: get_self(),
            data: "add_one_play=" + watch
        });
    }
}

function play_pause() {
    if (ytplayer) {
        if (ytplayer.getPlayerState() == 1) {
            ytplayer.pauseVideo();
            $('#play').attr('src', "images/play.png");
        } else {
            ytplayer.playVideo();
            $('#play').attr('src', "images/pause.png");
        }
    }
}

function volume_up() {
    if (ytplayer) {
        var vol = ytplayer.getVolume(),
            value = (vol + 10 > 100) ? 100 : vol + 10;
        ytplayer.setVolume(value);
        update_cookies('volume', value);
    }
}

function volume_down() {
    if (ytplayer) {
        var vol = ytplayer.getVolume(),
            value = (vol - 10 < 0) ? 0 : vol - 10;
        ytplayer.setVolume(value);
        update_cookies('volume', value);
    }
}

function get_player_state() {
    if (ytplayer) {
        return ytplayer.getPlayerState();
    }
}

function get_song_loaded() {
    if (ytplayer) {
        return ytplayer.getVideoLoadedFraction();
    }
}

function get_song_current_time() {
    if (ytplayer) {
        return ytplayer.getCurrentTime();
    }
}

function get_song_duration() {
    if (ytplayer) {
        return ytplayer.getDuration();
    }
}

function volume_mute() {
    if (ytplayer) {
        if (ytplayer.isMuted()) {
            ytplayer.unMute();
            $('#mute').attr('src', "images/unmute.png");
        } else {
            ytplayer.mute();
            $('#mute').attr('src', "images/mute.png");
        }
    }
}

function seek_to(seconds) {
    if (ytplayer) {
        ytplayer.seekTo(seconds, true);
    }
}

function get_current(move) {
    var current;
    if (move) {
        current = $('.current_move');
        if (!current.length) {
            current = get_current();
        }
    } else {
        current = $('.current');
        if (!current.length) {
            current = $('.song:first');
        }
    }
    return current;
}

function set_current(watch, focus = true) {
    var el = $('.song#song-' + watch);
    if (el.length) {
        $('.current').removeClass('current');
        el.addClass('current');
        idx = el.data('idx');
        if (!isElementInViewport(el) && focus)
            el[0].scrollIntoView({ behavior: "smooth", block: "center", })
    }
}

function set_current_from_ytplayer(focus = true)
{
    if (ytplayer && !$('.current').length)
    {
        set_current(ytplayer.getVideoData().video_id, focus);
    }
}

function isElementInViewport (el) {

    // Special bonus for those using jQuery
    if (typeof jQuery === "function" && el instanceof jQuery) {
        el = el[0];
    }

    var rect = el.getBoundingClientRect();
    var padding = 30;

    return (
        rect.top >= padding &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) - padding && /* or $(window).height() */
        rect.right <= (window.innerWidth || document.documentElement.clientWidth) /* or $(window).width() */
    );
}

function get_current_watch() {
    var current = get_current();
    if (current.length) {
        return current.data('watch-id');
    }
}

function get_checked() {
    return $('#songs .cbox:checked');
}

function get_next() {

    if (watch_queue.length) {
        var song = watch_queue.shift();
        set_current(song.watch_id);
        refresh_queue_list();
        return get_current();
    }

    var checked = get_checked();
    if (checked.length) {
        var next = $('#songs .cbox:gt(' + idx + '):checked');
        if (!next.length) {
            next = checked;
        }
        return next.first();
    }

    var current = $('.current');
    if (!current.length)
        return $('.song:first');

    return current.nextAll('.song:first');
}

function get_shuffle() {
    var checked = get_checked(),
        next;
    if (checked.length) {
        next = checked;
    } else {
        next = $('.song');
    }
    next = next.get_random();
    if (next.length) {
        return next;
    }
}

function update_song_title() {
    var title = get_current().data('title');
    if (title) {
        document.title = title + ' - Hecto';
        $('.song_title').html(title);
    }
}

function update_player_info() {
    var pros, pros2, time_total, time_now, m, s, buf = '';
    if (ytplayer) {
        pros = get_song_loaded() * 100;
        time_total = get_song_duration();
        time_now = get_song_current_time();
        if (time_total >= 0 && time_now >= 0) {
            pros2 = (time_now / time_total) * 100;
            m = Math.floor(time_now / 60);
            s = Math.floor(time_now % 60);
            buf = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
            buf = buf + " / ";
            m = Math.floor(time_total / 60);
            s = Math.floor(time_total % 60);
            buf += (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
        } else {
            pros = pros2 = 0;
        }
        var done = pros - pros2;
        if(done < 0) {
            done = 0;
        }
        // return;
        // eats cpu
        $(pro_playing).css('width', pros2 + '%');
        $(pro_loaded).css('width', done + '%');
    }
    $('.song_time').html(buf);
    if (ytplayer.getPlayerState() === 0) {
        play_next();
    }
}

function video_info(data) {
    var item = data.items[0],
        desc = item.snippet.description;
    while(desc.indexOf('\n') !== -1) {
        desc = desc.replace('\n', '<br>');
    }
    $('#song_descr').html(desc);
}

function load_new_video(watch, startSeconds) {
    if (ytplayer) {
        location.href = "#" + watch;
        ytplayer.loadVideoById(watch, parseInt(startSeconds, 10), format);
        current_check = watch;
        setTimeout(function() {
            add_one_play(watch);
        }, 10000);
        $.getJSON('videos.php?id=' + watch, video_info);

        set_current(watch);
    }
}

function cue_track_no(watch)
{
    if (watch)
    {
        ytplayer.cueVideoById(watch);
        if (!$('.current').length)
            set_current(watch);
    }
}

function play_track_no(watch) {
    if (watch) {
        if (on_player_error_st) {
            clearTimeout(on_player_error_st);
            on_player_error_st = null;
        }

        load_new_video(watch);
        update_song_title();
    }
}

function get_scroll_offset() {
    return -1 * ($(window).height() / 3);
}

function onYouTubeIframeAPIReady() {
    ytplayer = new YT.Player('ytapiplayer', {
        height: '300',
        width: '533',
        playerVars: {
            'controls': 1,
            'iv_load_policy': 3, //annotations off
        },
        //videoId: 'M7lc1UVf-VE',
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

function onPlayerStateChange(event) {
    if (!dont_force_medium && event.data == YT.PlayerState.BUFFERING) {
        //event.target.setPlaybackQuality('medium'); // R.I.P
    }
}

function onPlayerReady(event) {
    setInterval(update_player_info, 500);
    // ytplayer.addEventListener("onStateChange", "on_player_state_change");
    ytplayer.addEventListener('onError', 'on_player_error');
    var volume = parseInt(cookie('volume'), 10);
    if (volume) {
        ytplayer.setVolume(volume);
    }

    var watch = get_current_watch();
    if (autoplay) {
        play_track_no(watch);
    } else {
        cue_track_no(watch);
    }
}

function play_next() {
    var current = get_current_watch(),
        next;
    if ($('#shuffle').prop('checked') && !watch_queue.length) {
        next = get_shuffle();
    } else {
        next = get_next();
    }

    if (next.length) {
        var data = ytplayer.getVideoData();
        var watch = next.data('watch-id');
        if (data && watch === data.video_id) {
            seek_to(0);
        } else {
            play_track_no(watch);
        }
    }
}

function on_player_state_change(newState) {
    if (newState === 0) {
        setTimeout(function() {
            play_next();
        }, 1000);
    }
}

function on_player_error(errorCode) {
    $.ajax({
        type: "GET",
        url: get_self(),
        data: "erroneous=" + get_current_watch()
    });
    on_player_error_st = setTimeout(function() {
        on_player_error_st = null;
        play_next();
    }, 2000);
}

function play_keyboard_track() {
    var current = get_current(1);
    if (current.length) {
        play_track_no(current.data('watch-id'));
    }
}


function keyboard_move(down) {
    var current = get_current(1),
        next;
    if (down) {
        next = current.nextAll('.song:first');
    } else {
        next = current.prevAll('.song:first');
    }
    if (next.length) {
        $('.current_move').removeClass('current_move');
        next.addClass('current_move');
        $.scrollTo($(".current_move"), {
            offset: get_scroll_offset(),
            duration: 40
        });
    }
    return next;
}

function get_prev() {
    var checked = get_checked();
    if (checked.length) {
        var prev = $('#songs .cbox:lt(' + idx + '):checked');
        if (!prev.length) {
            prev = checked;
        }
        return prev.last();
    }
    return get_current().prevAll('.song:first');
}

function toggle_shuffle() {
    $('#shuffle').prop('checked', function(e) {
        this.checked = !this.checked;
    });
    update_cookies('shuffle');
}

function play_prev() {
    var prev = get_prev();
    if (prev.length) {
        var id = prev.data('watch-id');
        play_track_no(id);
    }
}

function set_format() {
    format = $('#format').val();
    return false; //Don't POST form
}

function plugin() {
    if(chrome) {
        chrome.webstore.install();
    }
}

function ytlocal() {
    location.href = '/ytlocal/' + location.hash;
    return false;
}

function hide_song(node) {
    $(node).parents().eq(2).remove();
    return false;
}

function delete_song(idx)
{
    ConfirmDialog("Delete song?",
    function()
    {
        console.log("Deleting", idx);
        $.ajax("?delete=" + idx)
        .done(function(){
            $("tr[data-idx=\"" + idx +"\"]").remove();
        });
    });
}

function edit_title(node)
{
    var parent = $(node).parents().eq(2);
    var watch = parent.data("watch-id");
    $("#text-edit").val(parent.find("#title").text());
    $("#text-status").text("");
    $("#edit-popup").dialog({
        height: 200,
        width: 600,
        modal: true,
        buttons: [
            {
                text: "Save",
                class: "save-button",
                click: function () {
                    var new_title = $("#text-edit").val();

                    $.post("index.php", {"watch": watch, "new_title": new_title})
                    .done(function() {
                        parent.find("#title").text(new_title);
                        $(this).dialog("close");
                    }.bind(this))
                    .fail(function(r){
                        $("#text-status").text(r.status + ": " + r.responseText);
                    });
                }
            },
            {
                text: "Cancel",
                class: "cancel-button",
                click:
                function(){
                    $(this).dialog("close");
                }
            }
        ]
    });
}

function ConfirmDialog(message, callback) {
    $('<div></div>').appendTo('body')
    .html('<div><h6>' + message + '?</h6></div>')
    .dialog({
        modal: true,
        title: 'Delete message',
        zIndex: 10000,
        autoOpen: true,
        width: 'auto',
        resizable: false,
        buttons: {
            Yes: function() {
                callback();
                $(this).dialog("close");
            },
            No: function() {
                $(this).dialog("close");
            }
        },
        close: function(event, ui) {
            $(this).remove();
        }
    });
};

function paginate()
{
    /*$('#songs table tbody').infinitescroll({
        state: { currPage: <?php echo isset($_GET["page"]) ? intval($_GET["page"]) : "1"; ?> },
        debug: false,
        navSelector  : "tr.pagination",
        nextSelector : "tr.pagination a:first",
        itemSelector : "tr",
        dataType: 'html',
        pathParse    : function() {
            // Whatever....wtf does this do anyway
            var extra = [];
            var m = window.location.search.match(/bkey=(.*?)(&|$)/);
            if(m) extra.push('bkey=' + m[1]);
            return ['?' + extra.join('&') + (extra.length ? '&' : '') + 'page=', ''];
        },
        loading : {
            msgText      : 'Loading MOAR...',
        }
    }, function (e) {
        if (location.hash.length)
            set_current(location.hash.substring(1));
    });*/

    var container = $('#songs table tbody');
    container.infiniteScroll({
        path: '.pagination__next',
        append: false,//'.article',
        status: '.page-load-status',
        hideNav: '.pagination',
        history: false,
    });

    container.on( 'load.infiniteScroll', function( event, data ) {
        container.find("tr.pagination").remove();
        container.append($(data).find("#songs table tbody").children());
        set_current_from_ytplayer(false);
    });
}


var watch_queue = [];
function add_to_queue(node)
{
    var parent = $(node).parents().eq(1);
    var song = {
        id: watch_queue.length ? watch_queue[watch_queue.length - 1].id + 1 : 0,
        watch_id: parent.data('watch-id'),
        title: parent.data('title'),
    }
    watch_queue.push(song);
    refresh_queue_list();
    $('#queue-popup').show();
}

function remove_queued(id)
{
    watch_queue = watch_queue.filter(function( obj ) {
        return obj.id !== id;
    });
    refresh_queue_list();
}

function clear_queue()
{
    watch_queue = [];
    $('#queue-status').empty();
    $('#queue-popup').hide();
}

function refresh_queue_list()
{
    if (!watch_queue.length)
        $('#queue-popup').hide();
    var list = $('#queue-status');
    list.empty();
    watch_queue.forEach(function(n){
        list.append(`<div>
        <a title="Remove from queue" href='javascript:void(0);' onclick='javascript:remove_queued(${n.id});'>
        <i class="icon-white icon-remove-sign"></i></a>
        ${n.title}</div>`);
    });
}

function toggle_queue()
{
    $('#queue-status').toggle();
}

$(document).ready(function() {
    var l = location.hash;
    if (l.indexOf('#') === 0) {
        var video_id = l.substring(1);
        if ($('.song#song-' + video_id).length)
            set_current(video_id);
        else
        {
            // TODO How to autoload to correct video? Just prepending the track that's not in the first "page" to the list for now.
            $.ajax(get_self() + "?watch=" + video_id).then(function(html){
                var node = $(html).find("tr:first");
                $("#songs table tbody").prepend(node);
                set_current(video_id);
                if (autoplay) {
                    play_track_no(video_id);
                } else {
                    cue_track_no(video_id);
                }
            });

        }
    }

    pro_playing = $('#pro-playing');
    pro_loaded = $('#pro-loaded');

    $('#progress').on('click', function(event, ui) {
        var dur = get_song_duration();
        if (dur && dur >= 0) {
            seek_to((event.pageX - $(this).offset().left)*100/$(this).width() / 100 * dur);
        }
    });
    $('img').hover(function() {
        $(this).fadeTo(300, 0.7);
    }, function() {
        $(this).fadeTo(100, 1);
    });
    var $shuffle = $('#shuffle');
    if (cookie('shuffle') === '1') {
        $shuffle.prop('checked', true);
    }

    $shuffle.on('change', function() {
        update_cookies('shuffle');
    });


    $('#powersave').click(function(e) {
        if(this.checked) {
            $('#ytapiplayer').css('height', '0px')
                .css('margin-bottom', '217px');
            $(pro_playing).css('height', '0px');
            $(pro_loaded).css('height', '0px');
        } else {
            $('#ytapiplayer').css('height', '')
                .css('margin-bottom', '');
            $(pro_playing).css('height', '');
            $(pro_loaded).css('height', '');
        }
    });

    $('#autoplay').prop('checked', autoplay);
    $('#autoplay').click(function(e) {
        cookie("autoplay", (autoplay = this.checked) ? 1 : 0);
    });

    /*$('#dont-force-medium').prop('checked', dont_force_medium);
    $('#dont-force-medium').click(function(e) {
        cookie("dont-force-medium", (dont_force_medium = this.checked) ? 1 : 0);
    });*/

    function switch_size_fn(checked)
    {
        if (checked)
        {
            $('div#sidebar').addClass('bigplayer_sidebar');
            $('div#songs').addClass('bigplayer_songs');
            $('#ytapiplayer').addClass('bigplayer_yt');
            $('#songs table').addClass('hide-right-td');
        }
        else
        {
            $('div#sidebar').removeClass('bigplayer_sidebar');
            $('div#songs').removeClass('bigplayer_songs');
            $('#ytapiplayer').removeClass('bigplayer_yt');
            $('#songs table').removeClass('hide-right-td');
        }
    }

    $('#switch-size').prop('checked', switch_size);
    $('#switch-size').click(function(e) {
        cookie("switch-size", (switch_size = this.checked) ? 1 : 0);
        switch_size_fn(this.checked);
    });
    switch_size_fn(switch_size);

    $('#page_select').change(function(e){
        //this.form.submit();
        var table = $('#songs table');
        var q = $(this).parent().serialize();
        table.empty();
        table.load('?'+ q + ' #songs table tbody', function(){
            paginate();
            set_current_from_ytplayer();
        });
    });

    $('form#search').submit(function(e){
        e.preventDefault();
        var table = $('#songs table');
        var q = $(this).serialize();
        table.empty();
        table.load('?'+ q + ' #songs table tbody', set_current_from_ytplayer);
    });

    $('a.brand').click(function(e){
        e.preventDefault();
        var table = $('#songs table');
        table.empty();
        var q = '?';
        var m = window.location.search.match(/(bkey=.*?)(&|$)/);
        if (m) q += m[1];
        table.load(this.href + q + ' #songs table tbody', function(){
            paginate();
            set_current_from_ytplayer();
        });
    });

    $(document).bind('keypress', function(e) {
        var target = (e.target && e.target.type) || 'other';
        if ('text,submit,search'.indexOf(target) === -1) {
            if (e.which == 32) {
                play_pause();
                return false;
            } else if (e.which == 110 || e.which == 78) { //N
                play_next();
            } else if (e.which == 106 || e.which == 74) { //J
                keyboard_move(1);
            } else if (e.which == 107 || e.which == 75) { //K
                keyboard_move();
            } else if (e.which == 115 || e.which == 83) { //K
                toggle_shuffle();
            } else if (e.which == 120 || e.which == 88) { //X
                var current = get_current(1);
                if (current.length) {
                    current.children('td:first').children('.cbox:first').prop('checked', function(e) {
                        this.checked = !this.checked;
                    });
                }
            } else if (e.which == 13) {
                play_keyboard_track();
            }
        }
    });
});
