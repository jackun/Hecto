<?php 
    include "functions.php"; 
    $time_start = microtime_float();
?>
<!DOCTYPE html public '❄'>
<html> 
  <head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    <title><?php echo PROG_NAME;?></title> 
    <link type="text/css" href="css/redmond/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" href="style.css">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.cooquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablednd_0_5.js"></script>
    <script type="text/javascript" src="js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script> 
    <script src="http://www.google.com/jsapi"></script> 
    <script type="text/javascript">
        google.load("swfobject", "2.1");
        var ytplayer = null,
            format = '<?php echo getformat();?>',
            songs = <?php echo $rows_json; ?>,
            hetkel = <?php echo $hetkel; ?>,
            kokku = songs.length,
            kontroll = '',
            cued = hetkel,
            notseeking = true,
            autoplay = <?php echo AUTOPLAY ?>,
            l = location.href;

        if (l.indexOf('#') > -1) {
            watch = l.substring(l.indexOf('#') + 1, l.length);
            for (var i = 0; i < songs.length; i++) {
                if (songs[i].watch == watch) {
                    hetkel = i;
                    break;
                }
            }
        }
        $(document).ready(function() {
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
                    } else if (e.which == 120 || e.which == 88) { //X
                        var current = get_current(1);
                        if(current.length) {
                            var id = current.data('id')
                            $('#check-' + id).prop('checked', function(e) {
                                this.checked = !this.checked;
                            });
                        }
                    } else if (e.which == 13) {
                        play_keyboard_track();
                    }
                }
            });
        });

        function add_one_play(id) {
            if (kontroll == id) {
                $.ajax({
                    type: "GET",
                    url: "<?php echo $_SERVER['PHP_SELF'];?>",
                    data: "add_one_play=" + id
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
            volume = parseInt(cookie('volume'));
            if (volume) {
                ytplayer.setVolume(volume);
            }
            if (autoplay) {
                play_track_no(hetkel);
            }
        }

        function on_player_state_change(newState) {
            if (newState === 0) {
                setTimeout(function() {
                    play_next();
                }, 500);
            }
        }

        function on_player_error(errorCode) {
            $.ajax({
                type: "GET",
                url: "<?php echo $_SERVER['PHP_SELF'];?>",
                data: "erroneous=" + songs[hetkel].watch
            });
            setTimeout(function() {
                play_next();
            }, 2000);
        }

        function update_payer_info() {
            buf = "";
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
                }
                else {
                    pros = pros2 = 0;
                }

                $("#slider").progressbar('value', pros);
                if (notseeking) {
                    $('#slider').slider('value', pros2);
                }
                if (get_song_current_time() > get_song_duration() - 10 && cued != hetkel) {
                    cued = hetkel;
                }
            }
            $('#time').html(buf);
        }

        function b2KMGb(bytes) {
            units = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
            n = 0;
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
                var cbw = parseInt(cookie('BW')) || 0;
                cookie('BW', cbw + get_song_bytes_loaded());
                $('#bw').html(b2KMGb(cbw));
                location.href = "#" + songs[hetkel].watch;
                ytplayer.loadVideoById(watch, parseInt(startSeconds), format);
                kontroll = watch;
                setTimeout(function() {
                    add_one_play(watch);
                }, 10000);
                $('.current').removeClass('current');
                $('.song#song-' + hetkel).addClass('current');
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
                var vol = ytplayer.getVolume();
                ytplayer.setVolume((vol + 10 > 100) ? 100 : vol + 10);
                cookie('volume', ytplayer.getVolume());
            }
        }

        function volume_down() {
            if (ytplayer) {
                var vol = ytplayer.getVolume();
                ytplayer.setVolume((vol - 10 < 0) ? 0 : vol - 10);
                cookie('volume', ytplayer.getVolume());
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

        function update_song_title() {
            document.title = songs[hetkel].title + ' - <?php echo PROG_NAME;?>';
            $('#song_title').text(songs[hetkel].title);
        }

        function play_keyboard_track() {
            var current = get_current(1);
            if(current.length) {
                play_track_no(current.data('id'));
            }
        }
        function get_scroll_offset() {
            return -1 * ($(window).height() / 3);
        }

        function keyboard_move(down) {
            var current = get_current(1),
                next;
            if (down) {
                next = current.next('.song');
            } else {
                next = current.prev('.song');
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
                var next = $('.checkbox:gt(' + hetkel + '):checked');
                if(!next.length) {
                    next = checked;
                }
                return next.first();
            }
            return get_current().next('.song')
        }

        function get_prev() {
            var checked = get_checked();
            if(checked.length) {
                var prev = $('.checkbox:lt(' + hetkel + '):checked');
                if(!prev.length) {
                    prev = checked;
                }
                return prev.last();
            }
            return get_current().prev('.song')
        }

        function play_next() {
            var current = hetkel,
                next = get_next();
            if(next.length) {
                var id = next.data('id');
                if(id == current) {
                    seek_to(0);
                } else {
                    play_track_no(id);
                }
            }
        }

        function play_prev() {
            var prev = get_prev();
            if(prev.length) {
                var id = prev.data('id');
                play_track_no(id);
            }
        }

        function play_track_no(nr) {
            if (nr <= kokku) {
                hetkel = nr;
                load_new_video(songs[hetkel].watch);
                update_song_title(songs[hetkel].title, songs[hetkel].watch);
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
        <a href='./'><?php echo PROG_NAME;?></a>
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
            $i = 0;
            foreach($rows_php as $row) {
                $class = ' even';
                if($i%2){
                    $class = ' odd';
                }
                $bkey = mysql_real_escape_string($row['bkey']);
                echo "<div class='song{$class}' id='song-{$i}' data-id='{$i}'>";
                echo "<input class=checkbox name='playlist' value='{$row['id']}' id='check-{$i}' data-id='{$i}' type=checkbox>&nbsp;&nbsp;";
                echo "<a href='#{$row['watch']}' onclick='play_track_no({$i})'>{$row['title']}</a>";
                echo " <span class='small'><a href='?bkey={$bkey}'>{$bkey}</a> {$row['time']}</span>";
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
            $_GET['start'] = $start + LEHEL;
            echo " <a href='?". http_build_query($_GET, '', '&') ."'>MOAR >>> </a>";
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
