<?php
    ob_start();
    session_start();

    error_reporting(1);
    ini_set('display_errors', 1);

include "config.php";

function setformat($value) {
    setcookie("format", $value, time()+60*60*24*365*5);
}

function getformat() {
    if(isset($_COOKIE['format'])){
        $format = $_COOKIE['format'];
    }else{
        $format = DEFAULT_FORMAT;
    }
    return $format;
}

?>
<!DOCTYPE html public '❄'>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="icon.ico" rel="icon" type="image/x-icon" />
    <title>Hecto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <!-- <link href='http://fonts.googleapis.com/css?family=Lato' type='text/css' rel='stylesheet' /> -->
   <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Droid+Sans" />
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css' rel="stylesheet" >
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="style_pl.css?201904181">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.cooquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablednd_0_5.js"></script>
    <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>
    <script type="text/javascript" src="js/infinite-scroll.pkgd.min.js"></script>
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
            pro_loaded;

        var on_player_error_st = null;
        var song_index = 0;
        var song_descs = {};

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

        function video_info(desc /*data*/) {
            //var item = data.items[0],
             //   desc = item.snippet.description;
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
                //$.getJSON('videos.php?id=' + watch, video_info);
                if (watch in song_descs) {
                    video_info(song_descs[watch]);
                }

                set_current(watch);
            }
        }

        function play_track_no(watch) {
            if (watch) {
                if (on_player_error_st) {
                    clearTimeout(on_player_error_st);
                    on_player_error_st = null;
                }
                console.log("watch:", watch);
                load_new_video(watch);
                update_song_title();
            }
        }

        function play_ev(ev) {
            ev = ev.target || ev;
            console.log(ev);
            if (ev) {
                var watch = $(ev).attr('data-watch') || $(ev).attr('href').substr(1);
                console.log("ev watch:", watch);
                play_track_no(watch);
            }
        }

        function get_scroll_offset() {
            return -1 * ($(window).height() - 250);
        }

        function onYouTubeIframeAPIReady() {
            ytplayer = new YT.Player('ytapiplayer', {
                width: '100%',
                height: '100%',
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
            //if (autoplay)
            {
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
                    $.scrollTo($(".current"), {
                        offset: get_scroll_offset(),
                        duration: 40
                    });
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

        function play_prev() {
            var prev = get_prev();
            if (prev.length) {
                var id = prev.data('watch-id');
                play_track_no(id);
                $.scrollTo($(".current"), {
                    offset: get_scroll_offset(),
                    duration: 40
                });
            }
        }

        function toggle_shuffle() {
            $('#shuffle').prop('checked', function(e) {
                this.checked = !this.checked;
            });
            update_cookies('shuffle');
        }

        function set_format() {
            format = $('#format').val();
            return false; //Don't POST form
        }

        function hide_song(node) {
            $(node).parents().eq(2).remove();
            return false;
        }

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

            $('#fs').on('click', function() {
                //ytplayer.toggleFullscreen();
                ytplayer.getIframe().requestFullscreen()
            });

<?php if(!isset($_GET["noinfi"])): ?>
            var paginated = false;
            function paginate()
            {
                var container = document.querySelector('#songs table tbody');
                var plid = 'PL588EF9C03333F829';
                if ((m = window.location.search.match(/plid=(.*?)(&|$)/)))
                    plid = m[1];

                var infScroll = new InfiniteScroll( container, {
                  page: null,
                  debug: true,
                  responseType: 'text',
                  path: function() {
                      if (this.options.page !== null)
                        return 'videos.php?plid=' + plid + '&page=' + this.options.page;
                    return 'videos.php?plid=' + plid;
                    //return 'yt_playlist.json';
                  },
                  // load response as JSON
                  responseBody: 'json',
                  status: '.page-load-status',
                  history: false,
                });

                infScroll.on( 'load', function( body ) {
                        this.options.page = load_playlist( JSON.parse(body) );
                });

                // load initial page
                infScroll.loadNextPage();
            }

<?php endif; ?>

/*
            $('a.brand').click(function(e){
                e.preventDefault();
                var table = $('#songs table');
                table.empty();
                var q = '?';
                var m = window.location.search.match(/(bkey=.*?)(&|$)/);
                if (m) q += m[1];
                table.load(this.href + q + ' #songs table tbody', paginate);
            });
*/

        function load_playlist(data)
        {
            console.log("Items:", data.items.length);
            if (data.items.length == 0) {
                console.log("load_playlist: no json data");
                return;
            }

            // Test to see if the browser supports the HTML template element by checking
            // for the presence of the template element's content attribute.
            if ('content' in document.createElement('template')) {

                // Instantiate the table with the existing HTML tbody
                // and the row with the template
                var tbody = document.querySelector("#songs tbody");
                var template = document.querySelector('#song-row');

                for (var i = 0; i < data.items.length; i++)
                {
                    var snippet = data.items[i].snippet;

                    // Clone the new row and insert it into the table
                    var clone = template.content.cloneNode(true);
                    var tr = clone.querySelector("tr");
                    if (song_index % 2 == 1)
                        tr.className = "song even";
                    $(tr).attr('data-idx', song_index);
                    $(tr).attr('data-watch-id', snippet.resourceId.videoId);
                    $(tr).attr('id', 'song-' + snippet.resourceId.videoId);
                    $(tr).attr('data-watch-title', snippet.title);

                    var td = clone.querySelectorAll("td");
                    if ('default' in snippet.thumbnails)
                        td[0].innerHTML = "<a onclick=\"play_ev(this)\" href=\"#" + snippet.resourceId.videoId + "\"><img src=\"" + snippet.thumbnails.default.url + "\"></a>";
                    else // private or deleted
                        continue;

                    var anchor = td[1].querySelector("a");
                    anchor.href = '#' + snippet.resourceId.videoId;
                    anchor.onclick = play_ev;
                    $(anchor).attr('data-watch', snippet.resourceId.videoId);
                    anchor.textContent = snippet.title;

                    song_descs[snippet.resourceId.videoId] = snippet.description;
                    tbody.appendChild(clone);

                    song_index++;
                }

                $('#pl_info').html("Tracks: " + data.pageInfo.totalResults);
                if ('nextPageToken' in data)
                    return data.nextPageToken;
                return undefined;

                /*if ('nextPageToken' in data)
                {
                    var pagin = document.createElement("tr");
                    $(pagin).addClass('pagination');
                    pagin.innerHTML = "<td colspan=3><a href='videos.php?plid=" + data.items[0].snippet.playlistId + "&page=" + data.nextPageToken + "' id=next_page>MOAR >>> </a></td>";
                    tbody.appendChild(pagin);
                }*/
            } else {
                // Find another way to add the rows to the table because
                // the HTML template element is not supported.
            }
        }

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

//            $.getJSON('videos.php?plid=<?php echo htmlspecialchars($_GET["plid"]);?>', load_playlist);
            //$.getJSON('videos.php?plid=PL588EF9C03333F829', load_playlist);
            //$.getJSON('yt_playlist.json', load_playlist);
            try {
                paginate();
            } catch(e){
                var tbody = document.querySelector("#songs tbody");
                tbody.innerHTML = "Error:" + e;
            }

        });
    </script>
 </head>
<body>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container-fluid topbar">
                <a class="brand" href="#"><img src="images/logo.png" title="Hecto"></a>
                <a href="?plid=PL588EF9C03333F829">Playlist 1</a>
                <a href="?plid=PLd9auH4JIHvupoMgW5YfOjqtj6Lih0MKw">"BEST OF THE 80's"</a>
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
          <span id="pl_info"></span>
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


<div class="container-fluid" style="margin-bottom: 40px;">
  <div class="row-fluid" style="display: flex; flex-direction: column; width: 80%; margin: auto;">

    <div style="position: fixed; width: 80%;">
        <div class="front-row">
            <div id='player'>
                <div id="ytapiplayer"></div>
            </div>
            <div id='sidebar'>
                <button id="fs">Fullscreen</button>
                <input class="cbox" type=checkbox id=shuffle>
                <label for=shuffle>Shuffle</label>
                <div id='song_descr'></div>
            </div>
        </div>
    </div>

    <div id='songs'>
        <table class="table table-condensed table-hover">
            <thead>
                <tr>
                    <td>Thumb</td>
                    <td>Video</td>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <template id="song-row">
            <tr class='song odd' id='song-id' data-idx="" data-watch-id="" data-title="">
                <td style='width: 128px;'>
                </td>
                <td>
                    <a href='#'>{title}</a>
                </td>
            </tr>
        </template>

    </div>
  </div>
</div>

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

</body>
</html>
<?php ob_end_flush();
