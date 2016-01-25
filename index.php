<?php
    include "functions.php";
    $time_start = microtime_float();
?>
<!DOCTYPE html public '❄'>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="icon.ico" rel="icon" type="image/x-icon" />
    <title>Hecto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href='http://fonts.googleapis.com/css?family=Lato' type='text/css' rel='stylesheet' />
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css' rel="stylesheet" >
    <link href="https://chrome.google.com/webstore/detail/ipinhbmnlgjnjlejfkaioflaphakdcnc" rel="chrome-webstore-item" />
    <link type="text/css" rel="stylesheet" href="style.css">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.cooquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablednd_0_5.js"></script>
    <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>
    <script src="https://www.google.com/jsapi?key=ABQIAAAAUFhWyG3PCr5qQ1N1-Da58BSijuhDh6bhkVNiCWkwXm1RWNn4jxTIhy9VD42I5uMUjGdZgqjFfBxulQ" type="text/javascript"></script>
    <script type="text/javascript">
        google.load("swfobject", "2.1");
        (function() {
            var s = document.createElement('script');
            var t = document.getElementsByTagName('script')[0];

            s.type = 'text/javascript';
            s.async = true;
            s.src = '//api.flattr.com/js/0.6/load.js?mode=auto&uid=tanel&title=Hecto&description=Hecto&category=audio&button=compact';

            t.parentNode.insertBefore(s, t);
         })();
        var ytplayer = null,
            idx = 0,
            format = '<?php echo getformat();?>',
            current_check = '',
            autoplay = <?php echo AUTOPLAY ?>,
            pro_playing,
            pro_loaded,
            api_videos = 'https://www.googleapis.com/youtube/v3/videos?key=AIzaSyBXvgH3EooBGKkicX2724L9EoD1M6PVPqE&part=snippet';

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
                var cbw = parseInt(cookie('BW'), 10) || 0;
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

        function get_song_bytes_loaded() {
            if (ytplayer) {
                return ytplayer.getVideoBytesLoaded();
            }
        }

        function get_song_bytes_total() {
            if (ytplayer) {
                return ytplayer.getVideoBytesTotal();
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
                return current.data('watch');
            }
        }

        function get_checked() {
            return $('.checkbox:checked');
        }

        function get_next() {
            var checked = get_checked();
            if (checked.length) {
                var next = $('.checkbox:gt(' + idx + '):checked');
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

        function update_payer_info() {
            var pros, pros2, time_total, time_now, m, s, buf = '';
            if (ytplayer) {
                pros = (get_song_bytes_loaded() / get_song_bytes_total()) * 100;
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
                update_cookies('BW', get_song_bytes_loaded());
                location.href = "#" + watch;
                ytplayer.loadVideoById(watch, parseInt(startSeconds, 10), format);
                current_check = watch;
                setTimeout(function() {
                    add_one_play(watch);
                }, 10000);
                $.getJSON(api_videos + '&id=' + watch, video_info);

                set_current(watch);
            }
        }

        function play_track_no(watch) {
            if (watch) {
                load_new_video(watch);
                update_song_title();
            }
        }

        function get_scroll_offset() {
            return -1 * ($(window).height() / 3);
        }

        function onYouTubePlayerReady(playerId) {
            ytplayer = document.getElementById('myytplayer');
            setInterval(update_payer_info, 500);
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
                var watch = next.data('watch');
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
            setTimeout(function() {
                play_next();
            }, 2000);
        }

        function play_keyboard_track() {
            var current = get_current(1);
            if (current.length) {
                play_track_no(current.data('watch'));
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
                var prev = $('.checkbox:lt(' + idx + '):checked');
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
                var id = prev.data('watch');
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

        $(document).ready(function() {
            var l = location.hash;
            if (l.indexOf('#') === 0) {
                $('.song').each(function(i, e) {
                    if ($(e).data('watch') === l.substring(1)) {
                        set_current(l.substring(1));
                    }
                });
            }

            pro_playing = $('#pro-playing');
            pro_loaded = $('#pro-loaded');

            $('#progress').on('click', function(event, ui) {
                var dur = get_song_duration();
                if (dur && dur >= 0) {
                    seek_to((event.pageX - $('#progress').offset().left)*100/$('#progress').width() / 100 * dur);
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

            var bw = cookie('BW');
            $('#bw').html(b2KMGb(bw));

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
                            current.children('td:first').children('.checkbox:first').prop('checked', function(e) {
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
          <a class="brand" href="./">Hecto</a>
          <form class='navbar-form pull-right' method='GET'>
            <input class="span7" name=q size=15 placeholder=Search type=search value="<?php
                if(isset($_GET['q'])){
                  echo htmlspecialchars($_GET['q']);
                }
            ?>">
          </form>
          <?php
            if(loggedin()){
              print "<span class='navbar-text'><a href='?logout=1'>Logout</span>";
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
                    <tr class='song{$class}' id='song-{$row->watch}' data-idx=\"{$i}\" data-watch=\"{$row->watch}\" data-title=\"{$title}\">
                        <td><input class=checkbox name='playlist' value='{$row->id}' data-watch=\"{$row->watch}\" type=checkbox>&nbsp;&nbsp;
                        <td><a href='#{$row->watch}' onclick='play_track_no(\"{$row->watch}\")'>{$title}</a>
                        <td class='text-right'>
                            <span class='small'>
                                <a href='?bkey={$url_bkey}'>{$html_bkey}</a> {$row->time}
                            </span>
                ";
                if(loggedin()){
                    echo " | {$row->plays} | {$row->erroneous} | <a href='?delete={$row->id}'>delete</a>";
                }
                echo "</span></div>";
                $i++;
            }
        }
        print '</table><div class=pagination>';
        if($next_link){
            $_GET['page'] = $page + 1;
            echo " <a href='?". http_build_query($_GET, '', '&') ."' id=next_page>MOAR >>> </a>";
        }
        print '</div>';
        ?>
    </div>
    <div class="span4" id='sidebar'>
        <script type="text/javascript">
            var params = {
              allowScriptAccess: "always",
              bgcolor: "#cccccc",
              wmode: "opaque"
            };
            var atts = { id: "myytplayer" };
            swfobject.embedSWF("http://www.youtube.com/apiplayer?enablejsapi=1&playerapiid=ytplayer",
                         "ytapiplayer", "390", "300", "8", null, null, params, atts);
        </script>
        <div id="ytapiplayer">You need Flash player 8+ and JavaScript enabled to view this video.</div>

        <div class="sideblock">
            <div id="song_descr"></div>
            <hr>

            <label for=shuffle>Shuffle <input type=checkbox id=shuffle></label>
            <hr>

            Drag this to your bookmark bar : <b><a href="javascript:(function(){var script = document.createElement('script');script.setAttribute('type','text/javascript'); script.setAttribute('src','http://<?php
                $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/');
            echo $_SERVER['HTTP_HOST'] . $dir;
            ?>/?bookmark='+encodeURIComponent(location.href)); document.body.appendChild(script); })();" onClick="alert('Drag this to your bookmark bar ;)'); return false;">Add to Hecto</a></b>

            <br />
                or use this <a href="javascript:plugin();">Google Chrome extension</a>
            <hr>
            git clone <a href="https://github.com/tanelpuhu/hecto">git://github.com/tanelpuhu/hecto.git</a>
            <hr>
            Slightly different interface, with online searching <a href='javascript:ytlocal();'>here</a>
            <hr>
          <?php
            $time_end = round(microtime_float()-$time_start, 5);
            print sprintf("
                ~<span id=bw>#</span> | %s | <a href='javascript:void(0);' onClick='set_key();'>%s</a>
                ", $time_end, $bkey
            );
          ?>
        </div>
    </div>
</div>



  <script type="text/javascript">
      var gaJsHost = (("https:" == document.location.protocol) ? "ssl" : "www");
      document.write(unescape("%3Cscript src='//" + gaJsHost + ".google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <script type="text/javascript">
      try {
          var pageTracker = _gat._getTracker("UA-260300-10");
          pageTracker._initData();
          pageTracker._trackPageview();
      } catch(err) {}
  </script>
  </body>
</html>
