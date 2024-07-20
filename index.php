<?php
ob_start();
session_start();

if(isset($_SERVER["REMOTE_ADDR"]) && strpos($_SERVER["REMOTE_ADDR"], "192.168") === 0)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
else
{
    error_reporting(0);
    ini_set('display_errors', 0);
}

    $logged_in_user = true;
    /*$logged_in_user = $_SESSION["logged_in_user"];
    if(!$logged_in_user && isset($_POST["passwd"]))
    {
        $_SESSION["logged_in_user"] = hash("sha256", $_POST["passwd"]) === "f10fb0086f2fac5c4f3c5904fa22675c10d432e8b3967a06e705cf67f6969c21";
        header("Location: ".str_replace("index.php", "", $_SERVER['PHP_SELF']));
        die;
    }*/

    include "functions.php";
    $time_start = microtime_float();
?>
<?php if(!$logged_in_user): ?>
<!DOCTYPE html public '❄'>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="icon.ico" rel="icon" type="image/x-icon" />
    <title>Hecto</title>
  </head>
  <body>
    <form method="POST">
    Password: <input type="password" name="passwd"/>
    <input type="submit"/>
    </form>
  </body>
</html>
<?php die; endif;?>
<!DOCTYPE html public '❄'>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="icon.ico" rel="icon" type="image/x-icon" />
    <title>Hecto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <link href='http://fonts.googleapis.com/css?family=Lato' type='text/css' rel='stylesheet' /> -->
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Droid+Sans" />
    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css' rel="stylesheet" >
    <link href="https://chrome.google.com/webstore/detail/ipinhbmnlgjnjlejfkaioflaphakdcnc" rel="chrome-webstore-item" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="style.css?201904181">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/jquery.cooquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablednd_0_5.js"></script>
    <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="js/infinite-scroll.pkgd.min.js"></script>
    <script src="https://www.google.com/jsapi?key=ABQIAAAAUFhWyG3PCr5qQ1N1-Da58BSijuhDh6bhkVNiCWkwXm1RWNn4jxTIhy9VD42I5uMUjGdZgqjFfBxulQ" type="text/javascript"></script>
    <script type="text/javascript">
        (function() {
            var s = document.createElement('script');
            var t = document.getElementsByTagName('script')[0];

            s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = '//www.youtube.com/iframe_api';
            
            t.parentNode.insertBefore(s, t);
         })();
        var ytplayer = null,
            idx = 0,
            format = '<?php echo getformat();?>',
            current_check = '',
            autoplay = parseInt(cookie("autoplay")) === 1,
            dont_force_medium = parseInt(cookie("dont-force-medium")) === 1,
            switch_size = parseInt(cookie("switch-size")) === 1,
            pro_playing,
            pro_loaded,
            on_player_error_st = null;

        $.fn.get_random = function() {
            var len = this.length,
                ran = Math.random() * len;
            if (len) {
                return $(this[Math.floor(ran)]);
            }
        };

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
                    url: "<?php echo $_SERVER['PHP_SELF'];?>",
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

        function set_current(watch) {
            $('.current').removeClass('current');
            $('.song#song-' + watch).addClass('current');
            idx = get_current().data('idx');
            var current = document.querySelector(".current");
            if (!isElementInViewport(current))
                current.scrollIntoView({ behavior: "smooth", block: "center", })
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
            var checked = get_checked();
            if (checked.length) {
                var next = $('#songs .cbox:gt(' + idx + '):checked');
                if (!next.length) {
                    next = checked;
                }
                return next.first();
            }
            return get_current().nextAll('.song:first');
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
            if (autoplay) {
                play_track_no(get_current_watch());
            }
        }

        function play_next() {
            var current = get_current_watch(),
                next;
            if ($('#shuffle').prop('checked')) {
                next = get_shuffle();
            } else {
                next = get_next();
            }

            if (next.length) {
                var watch = next.data('watch-id');
                if (watch === current) {
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
                url: "<?php echo $_SERVER['PHP_SELF'];?>",
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

        function set_key() {
            var key = prompt('New key?', '<?php
                echo $_COOKIE['h2bkey'];
            ?>');
            if (key) {
                location.href = '?set_key=' + encodeURIComponent(key);
            }
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

        $(document).ready(function() {
            var l = location.hash;
            if (l.indexOf('#') === 0) {
                $('.song').each(function(i, e) {
                    if ($(e).data('watch-id') === l.substring(1)) {
                        set_current(l.substring(1));
                    }
                });
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

<?php if(!isset($_GET["noinfi"])): ?>
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
                });
            }
            paginate();
<?php endif; ?>

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
                table.load('?'+ q + ' #songs table tbody', paginate);
            });

            $('form#search').submit(function(e){
                e.preventDefault();
                var table = $('#songs table');
                var q = $(this).serialize();
                table.empty();
                table.load('?'+ q + ' #songs table tbody');
            });

            $('a.brand').click(function(e){
                e.preventDefault();
                var table = $('#songs table');
                table.empty();
                var q = '?';
                var m = window.location.search.match(/(bkey=.*?)(&|$)/);
                if (m) q += m[1];
                table.load(this.href + q + ' #songs table tbody', paginate);
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
    </script>
 </head>
<body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid topbar">
        	<a class="brand" href="./"><img src="images/logo.png" title="Hecto"></a>

        <form id="jump_to_page" method="GET" class='navbar-form pull-right'>
<?php if(isset($_GET["bkey"])):?>
            <input type="hidden" name="bkey" value="<?php echo htmlspecialchars($_GET["bkey"]);?>"/>
<?php endif;?>
            <select id="page_select" name="page">
<?php
        for($id=1; $id<=$page_count; $id++)
        {
            echo "                <option value=\"$id\"";
            if(isset($_GET["page"]) && $id==$_GET["page"])
                echo " selected=\"selected\"";
            echo ">Page $id</option>\n";
        }
?>
            </select>
            <input type="submit" value="Go" style="background-color: #12b04f; line-height: 25px; border-radius: 5px; font-weight: bold; color: white;">
        </form>

          <form id="search" class='navbar-form pull-right' method='GET'>
<?php if(isset($_GET["bkey"])):?>
            <input type="hidden" name="bkey" value="<?php echo htmlspecialchars($_GET["bkey"]);?>"/>
<?php endif;?>
            <input class="span7" name=q size=15 placeholder=Search type=search value="<?php
                if(isset($_GET['q'])){
                  echo htmlspecialchars($_GET['q']);
                }
            ?>">
          </form>
          <?php
            if(loggedin()){
              print "<span class='navbar-text'><a href='?logout=1'>Logout</a></span>";
            }
          ?>
        </div>
      </div>
    </div>

    <div class="navbar navbar-fixed-bottom">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class=btn href="javascript:void(0);" onclick="volume_down();"><img src='images/vol_down.png?v=2' border=0></a>
          <a class=btn href="javascript:void(0);" onclick="volume_up();"><img src='images/vol_up.png?v=2' border=0></a>
          <a class=btn href="javascript:void(0);" onclick="play_prev();"><img src='images/prev.png?v=2' border=0></a>
          <a class=btn href="javascript:void(0);" onclick="play_pause();"><img src='images/pause.png?v=2' id=play border=0></a>
          <a class=btn href="javascript:void(0);" onclick="play_next();"><img src='images/next.png?v=2' border=0></a>
          <div class='pull-right progress-outer'>

            <div class="progress" id="progress">
                <div class="bar" id="pro-playing" style="width: 0%;"></div>
                <div class="bar" id="pro-loaded" style="width: 0%;"></div>
                <div class="progress-inner">
                    <div class='song_time'>&nbsp;</div>
                    <div class='song_title'>&nbsp;</div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>


<div class="container-fluid">
  <div class="row-fluid">
    <div class="span8" id='songs'>
        <table class="table table-condensed table-hover">
        <tbody>
        <?php
        if(count($rows_php) > 0){
            $i = $start;
            foreach($rows_php as $row) {
                $class = ' even';
                if($i%2){
                    $class = ' odd';
                }
                $html_bkey = htmlspecialchars($row->bkey);
                $url_bkey = urlencode($row->bkey);
                $title = htmlspecialchars($row->title);
                echo "
                    <tr class='song{$class}' id='song-{$row->watch}' data-idx=\"{$row->id}\" data-watch-id=\"{$row->watch}\" data-title=\"{$title}\">
                        <td style='width: 16px;'>
                            <input id='cbx-{$row->id}' class=cbox name='playlist' value='{$row->id}' data-watch-id=\"{$row->watch}\" type=checkbox>
                            <label for='cbx-{$row->id}'></label>
                        </td>
                        <td class='td-id-number'>{$row->id})</td>
                        <td><a id='title' href='#{$row->watch}' onclick='play_track_no(\"{$row->watch}\")'>{$title}</a></td>
                        <td class='text-right'>
                            <span class='small'>
                                <a href='javascript:void(0);' onclick='javascript:hide_song(this);'>Hide</a> / 
                                <a href='?bkey={$url_bkey}'>{$html_bkey}</a> {$row->time}";

                    if(loggedin()){
                        echo " | {$row->plays} | {$row->erroneous}";
                        echo " | <a href='javascript:void(0);' onclick='javascript:delete_song({$row->id});'>delete</a>";
                        echo " | <a href='javascript:void(0);' onclick='javascript:edit_title(this);'>edit</a>";
                    }

                echo "
                            </span>
                        </td>
                    </tr>";
                $i++;
            }
        }
        print '<tr class=pagination><td colspan=4>';
        if($next_link){
            $_GET['page'] = $page + 1;
            echo " <a class=\"pagination__next\" href='?". http_build_query($_GET, '', '&') ."'>MOAR >>> </a>";
        }
        print '</td></tr>';
        ?>
        </tbody>
        </table>

        <!-- status elements -->
        <div class="page-load-status">
            <div class="loader-ellips infinite-scroll-request">
                <span class="loader-ellips__dot"></span>
                <span class="loader-ellips__dot"></span>
                <span class="loader-ellips__dot"></span>
                <span class="loader-ellips__dot"></span>
            </div>
            <p class="infinite-scroll-last">End of content</p>
            <p class="infinite-scroll-error">No more pages to load</p>
        </div>
    </div>
    <div class="span4" id='sidebar'>

        <input class="cbox" type=checkbox id=switch-size>
        <label for=switch-size>Switch size</label>

       	<div id="ytapiplayer"></div>
        <div id='slider'></div>

        <div class="sideblock">
            <div id="song_descr"></div>
            <hr>

            <!--input class="cbox" type=checkbox id=dont-force-medium>
            <label for=dont-force-medium>Don't force medium quality</label-->

            <input class="cbox" type=checkbox id=autoplay>
            <label for=autoplay>Autoplay</label>

            <input class="cbox" type=checkbox id=shuffle>
            <label for=shuffle>Shuffle</label>

            <input class="cbox" type=checkbox id=powersave>
            <label for=powersave>Powersave</label>
            <hr>

            Drag this to your bookmark bar : <b><a href="javascript:void(!function(){fetch('https://<?php
                $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/');
                echo $_SERVER['HTTP_HOST'] . $dir . "/";
            ?>',{body:new URLSearchParams({bookmark:window.location.href,pluginkey:'<?php echo $bkey;?>',ver:2}),method:'POST'}).then((r)=>r.text().then((t)=> {var s = document.createElement('script');s.setAttribute('type','text/javascript');s.appendChild(document.createTextNode(t));document.body.appendChild(s);}))}())">Add to Hecto</a></b>

            <br />
                or use this <a href="javascript:plugin();">Google Chrome extension</a>
            <?php
            /*<hr>
            git clone <a href="https://github.com/tanelpuhu/hecto">git://github.com/tanelpuhu/hecto.git</a>
            <hr>
            <!-- Slightly different interface, with online searching <a href='javascript:ytlocal();'>here</a> -->
            <hr>*/
            ?>
          <?php
            $time_end = round(microtime_float()-$time_start, 5);
            print sprintf("
                %s | <a href='javascript:void(0);' onClick='set_key();'>%s</a>
                ", $time_end, $bkey
            );
          ?>
        </div>
    </div>
  </div>
</div>

<div id="edit-popup" style="display: none" title="Edit video title">
    <input id="text-edit" maxlength="256"></input>
    <div id="text-status"></div>
</div>

  <!--script type="text/javascript">
      var gaJsHost = (("https:" == document.location.protocol) ? "ssl" : "www");
      document.write(unescape("%3Cscript src='//" + gaJsHost + ".google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <script type="text/javascript">
      try {
          var pageTracker = _gat._getTracker("UA-260300-10");
          pageTracker._initData();
          pageTracker._trackPageview();
      } catch(err) {}
  </script-->
  </body>
</html>
<?php ob_end_flush();
