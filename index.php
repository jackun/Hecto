<?php 
    include "functions.php"; 
    $time_start = microtime_float();
?>
<!DOCTYPE html public '❄'>
<html> 
  <head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    <title>Hecto</title>
    <link type="text/css" href="css/redmond/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" href="style.css">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.cooquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablednd_0_5.js"></script>
    <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script> 
    <script type="text/javascript" src="js/jquery.infinitescroll.min.js"></script>
    <script src="http://www.google.com/jsapi"></script> 
    <script type="text/javascript">
        google.load("swfobject", "2.1");
        var ytplayer = null,
            idx = 0,
            format = '<?php echo getformat();?>',
            current_check = '',
            notseeking = true,
            autoplay = <?php echo AUTOPLAY ?>;

        $(document).ready(function() {
            var l = location.hash;
            if (l.indexOf('#') === 0) {
                $('.song').each(function(i,e){
                    if($(e).data('watch') === l.substring(1)) {
                        set_current(l.substring(1));
                    }
                });
            }

            $("#slider").progressbar({value: 0});
            $('#slider').slider({
                value: 0,
                max: 100,
                animate: true
            });
            $('#slider').bind('slidestart', function(event, ui) {
                notseeking = false;
            });
            $('#slider').bind('slidestop', function(event, ui) {
                var dur = get_song_duration();
                if (dur && dur >= 0) {
                    seek_to(($('#slider').slider('value') / 100) * dur);
                }
                setTimeout(function() {
                    notseeking = true;
                }, 1000);
            });
            $('img').hover(function() {
                $(this).fadeTo(300, 0.7);
            }, function() {
                $(this).fadeTo(100, 1);
            });
            var $shuffle = $('#shuffle');
            if(cookie('shuffle') === '1') {
                $shuffle.prop('checked', true);
            }

            $shuffle.on('change', function(){
                update_cookies('shuffle');
            });

            $(document).bind('keypress', function(e) {
                target = (e.target && e.target.type) || 'other';
                if (target != 'text' && target != 'submit') {
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
                        if(current.length) {
                            current.children('.checkbox:first').prop(
                                'checked', function(e) {
                                    this.checked = !this.checked;
                                }
                            );
                        }
                    } else if (e.which == 13) {
                        play_keyboard_track();
                    }
                }
            });
            $('#songs').infinitescroll({
                navSelector  : "div.pagination",
                nextSelector : "div.pagination a:first",
                itemSelector : "#songs div.song",
                pathParse    : function() {
                    return ['?page=', ''];
                },
                loading : {
                    msgText      : 'Loading MOAR...',
                }
            });
        });

        $.fn.get_random = function(){
          var len = this.length,
              ran = Math.random() * len;
          if(len) {
            return $(this[Math.floor(ran)]);
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

        function onYouTubePlayerReady(playerId) {
            ytplayer = document.getElementById('myytplayer');
            setInterval(update_payer_info, 500);
            ytplayer.addEventListener('onStateChange', 'on_player_state_change');
            ytplayer.addEventListener('onError', 'on_player_error');
            volume = parseInt(cookie('volume'), 10);
            if (volume) {
                ytplayer.setVolume(volume);
            }
            if (autoplay) {
                play_track_no(get_current_watch())
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

        function update_payer_info() {
            var pros,
                pros2,
                time_total,
                time_now,
                m,
                s,
                buf = '';
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

                $("#slider").progressbar('value', pros);
                if (notseeking) {
                    $('#slider').slider('value', pros2);
                }
            }
            $('#time').html(buf);
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

        function load_new_video(watch, startSeconds) {
            if (ytplayer) {
                update_cookies('BW', get_song_bytes_loaded());
                location.href = "#" + watch;
                ytplayer.loadVideoById(watch, parseInt(startSeconds, 10), format);
                current_check = watch;
                setTimeout(function() {
                    add_one_play(watch);
                }, 10000);
                set_current(watch);
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
                    value = (vol - 10 < 0) ? 0 : vol - 10
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

        function update_cookies(what, value) {
            if(what === 'shuffle') {
                cookie('shuffle', $('#shuffle').prop('checked')?'1':'0');
            } else if(what === 'volume' && value) {
                cookie('volume', value);
            } else if(what === 'BW' && value) {
                var cbw = parseInt(cookie('BW'), 10) || 0;
                cookie('BW', cbw + value);
                $('#bw').html(b2KMGb(cbw));
            }
        }

        function update_song_title() {
            var title = get_current().data('title');
            if(title) {
                document.title = title + ' - Hecto';
                $('#song_title').html(title);
            }
        }

        function play_keyboard_track() {
            var current = get_current(1);
            if(current.length) {
                play_track_no(current.data('watch'));
            }
        }
        function get_scroll_offset() {
            return -1 * ($(window).height() / 3);
        }

        function keyboard_move(down) {
            var current = get_current(1),
                next;
            if (down) {
                next = current.nextAll('.song:first');
            } else {
                next = current.prevAll('.song:first');
            }
            if(next.length) {
                $('.current_move').removeClass('current_move');
                next.addClass('current_move');
                $.scrollTo($(".current_move"), {
                    offset: get_scroll_offset(),
                    duration: 40
                });
            }
            return next;
        }

        function set_current(watch) {
            $('.current').removeClass('current');
            $('.song#song-' + watch).addClass('current');
            idx = get_current().data('idx');
        }

        function get_current_watch(){
            var current = get_current();
            if(current.length) {
                return current.data('watch');
            }
        }

        function get_current(move) {
            var current;
            if(move) {
                current = $('.current_move');
                if(!current.length) {
                    current = get_current();
                }
            } else {
                current = $('.current');
                if(!current.length) {
                    current = $('.song:first');
                }
            }
            return current;
        }

        function get_checked() {
            return $('.checkbox:checked');
        }

        function get_next() {
            var checked = get_checked();
            if(checked.length) {
                var next = $('.checkbox:gt(' + idx + '):checked');
                if(!next.length) {
                    next = checked;
                }
                return next.first();
            }
            return get_current().nextAll('.song:first')
        }

        function get_prev() {
            var checked = get_checked();
            if(checked.length) {
                var prev = $('.checkbox:lt(' + idx + '):checked');
                if(!prev.length) {
                    prev = checked;
                }
                return prev.last();
            }
            return get_current().prevAll('.song:first')
        }

        function toggle_shuffle() {
            $('#shuffle').prop('checked', function(e) {
                this.checked = !this.checked;
            });
            update_cookies('shuffle');
        }

        function get_shuffle() {
          var current = get_current(),
              checked = get_checked(),
              next;
          if(checked.length) {
            next = checked;
          } else {
            next = $('.song');
          }
          next = next.get_random();
          if(next.length) {
            return next;
          }
        }

        function play_next() {
            var current = get_current_watch(),
                next;
            if($('#shuffle').prop('checked')) {
                next = get_shuffle();
            } else {
                next = get_next();
            }

            if(next.length) {
                var watch = next.data('watch');
                if(watch === current) {
                    seek_to(0);
                } else {
                    play_track_no(watch);
                }
            }
        }

        function play_prev() {
            var prev = get_prev();
            if(prev.length) {
                var id = prev.data('watch');
                play_track_no(id);
            }
        }

        function play_track_no(watch) {
            if (watch) {
                load_new_video(watch);
                update_song_title();
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
            if(key) {
                location.href = '?set_key=' + encodeURIComponent(key);
            }
        }
    </script> 
 </head> 
<body>
<div id="header">
    <div class="title">
        <a href='./'>Hecto</a>
    </div>
    <div class='nav'>
        <ul>
            <li id='song_title'>&nbsp;</li>
            <li id='time'>&nbsp;</li>
        </ul>
    </div>
</div>

<div id='content'>
    <div id='songs'>
        <?php if(count($rows_php)>0){
            $i = $start;
            foreach($rows_php as $row) {
                $class = ' even';
                if($i%2){
                    $class = ' odd';
                }
                $current_bkey = mysql_real_escape_string($row['bkey']);
                $title = htmlspecialchars($row['title']);
                echo "<div class='song{$class}' id='song-{$row['watch']}' data-idx=\"{$i}\" data-watch=\"{$row['watch']}\" data-title=\"{$title}\">";
                echo "<input class=checkbox name='playlist' value='{$row['id']}' data-watch=\"{$row['watch']}\" type=checkbox>&nbsp;&nbsp;";
                echo "<a href='#{$row['watch']}' onclick='play_track_no(\"{$row['watch']}\")'>{$row['title']}</a>";
                echo " <span class='small'><a href='?bkey={$current_bkey}'>{$current_bkey}</a> {$row['time']}</span>";
                // if(loggedin()){
                //     echo "<td>{$row['plays']}</td>";
                //     echo "<td>{$row['erroneous']}</td>";
                //     echo "<td>{$row['bkey']}</td>";
                //     echo "<td><a href='?delete={$row['id']}'>delete</a></td>";
                // }
                echo "</div>";
                $i++;
            }
            // if(!isset($_GET['p'])){ echo "<input type='button' value='Create playlist' onclick='create_playlist()'>";}
        }
        print '<div class=pagination>';
        if($next_link){
            $_GET['page'] = $page + 1;
            echo " <a href='?". http_build_query($_GET, '', '&') ."' id=next_page>MOAR >>> </a>";
        }
        print '</div>';
        ?>

    </div>
    <div id='sidebar'>
        <script type="text/javascript"> 
            var params = { allowScriptAccess: "always", bgcolor: "#cccccc" };
            var atts = { id: "myytplayer" };
            swfobject.embedSWF("http://www.youtube.com/apiplayer?enablejsapi=1&playerapiid=ytplayer", 
                         "ytapiplayer", "290", "217", "8", null, null, params, atts);
        </script> 
        <div id="ytapiplayer">You need Flash player 8+ and JavaScript enabled to view this video.</div>
        <div id='slider'></div>

        <a href="javascript:void(0);" onclick="play_prev();"><img src='images/prev.png' border=0></a>
        <a href="javascript:void(0);" onclick="play_pause();"><img src='images/pause.png' id=play border=0></a>
        <a href="javascript:void(0);" onclick="volume_mute();"><img src='images/unmute.png' id=mute border=0></a>
        <a href="javascript:void(0);" onclick="volume_up();"><img src='images/vol_up.png' border=0></a>
        <a href="javascript:void(0);" onclick="volume_down();"><img src='images/vol_down.png' border=0></a>
        <a href="javascript:void(0);" onclick="play_next();"><img src='images/next.png' border=0></a>

        <br><br>
        <label for=shuffle>Shuffle</label> <input type=checkbox id=shuffle>

        <div>
            <?php /*
            Drag this to your bookmark bar : <b><a href="javascript:(function(){var script = document.createElement('script');script.setAttribute('type','text/javascript'); script.setAttribute('src','http://<?php
                $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/');
            echo $_SERVER['HTTP_HOST'] . $dir;
            ?>/?bookmark='+encodeURIComponent(location.href)); document.body.appendChild(script); })();" onClick="alert('Drag this to your bookmark bar ;)'); return false;">Add to Hecto</a></b>

            <br />
                or */?>use this <a href="//<?php
                echo $_SERVER['HTTP_HOST'] . $dir . '/hecto.crx';
            ?>">Google Chrome extension</a>
        </div>
        <br><br>
        <div>
            git clone <a href="https://github.com/tanelpuhu/hecto">git://github.com/tanelpuhu/hecto.git</a>
        </div>

    </div>
</div>



<div class='clear'></div>
<?php
$time_end = microtime_float()-$time_start;
print sprintf("<div class='footer'>
    ~<span id=bw>#</span> / %s / <a href='javascript:void(0);' onClick='set_key();'>%s</a>
    <br>
    <a href='http://twitter.com/tanel'>@tanel</a><br>
    <a href='http://tanelpuhu.com'>tanelpuhu.com</a><br></div>
    ", $time_end, $bkey
);
?>
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
