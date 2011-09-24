<?php 
    include "functions.php"; 
    $time_start = microtime_float();
    $imgs = array();
?>
<!DOCTYPE html>
<html> 
  <head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
    <title><?php echo PROG_NAME;?></title> 
    <link type="text/css" href="css/redmond/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
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
            kontroll = "",
            cued = hetkel,
            notseeking = true,
            autoplay = false;
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
        var onTheMove = hetkel;
        $(document).ready(function() {
            $("#slider").css('height', '10px').css('width', '<?php echo $flash_width;?>px').css('margin-top', '5px');
            $("#slider").progressbar({
                value: 0
            });
            $('#slider').slider({
                value: 0,
                max: 100,
                animate: true
            });
            $('#slider').bind('slidestart', function(event, ui) {
                notseeking = false;
            });
            $('#slider').bind('slidestop', function(event, ui) {
                var dur = getDuration();
                if (dur && dur >= 0) {
                    seekTo(($('#slider').slider('value') / 100) * dur);
                }
                setTimeout(function() {
                    notseeking = true;
                }, 1000);
            });
            $('.thead td').addClass('thead');
            $('.colorize td').addClass('odd');
            $('.colorize td').hover(function() {
                $(this).removeClass('odd').addClass('even');
            }, function() {
                $(this).removeClass('even').addClass('odd');
                $('.thead td').addClass('thead');
            });
            $('img').hover(function() {
                $(this).fadeTo(300, 0.7);
            }, function() {
                $(this).fadeTo(100, 1);
            });
            $("#main").tableDnD();
            $(document).bind('keypress', function(e) {
                target = (e.target && e.target.type) || 'other';
                if (target != 'text' && target != 'submit') {
                    if (e.which == 32) {
                        playSong();
                        return false;
                    } else if (e.which == 110 || e.which == 78) { //N
                        nextSong();
                    } else if (e.which == 106 || e.which == 74) { //J
                        setYourMove(1);
                    } else if (e.which == 107 || e.which == 75) { //K
                        setYourMove();
                    } else if (e.which == 120 || e.which == 88) { //X
                        $("#main tr[id='" + onTheMove + "'] input[type=checkbox]").attr('checked', function(e) {
                            this.checked = !this.checked;
                        });
                    } else if (e.which == 13) {
                        track(onTheMove);
                    }
                }
            });
        });

        function addPlay(id) {
            if (kontroll == id) {
                $.ajax({
                    type: "GET",
                    url: "<?php echo $_SERVER['PHP_SELF'];?>",
                    data: "addPlay=" + id
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
            ytplayer = document.getElementById("myytplayer");
            setInterval(updateytplayerInfo, 500);
            updateytplayerInfo();
            ytplayer.addEventListener("onStateChange", "onytplayerStateChange");
            ytplayer.addEventListener("onError", "onPlayerError");
            volume = parseInt(cookie('volume'));
            if (volume) {
                ytplayer.setVolume(volume);
            }
            if (autoplay) {
                track(hetkel);
            }
        }

        function onytplayerStateChange(newState) {
            if (newState === 0) {
                setTimeout(function() {
                    nextSong();
                }, 500);
            }
        }

        function onPlayerError(errorCode) {
            $.ajax({
                type: "GET",
                url: "<?php echo $_SERVER['PHP_SELF'];?>",
                data: "erroneous=" + songs[hetkel].watch
            });
            setTimeout(function() {
                nextSong();
            }, 2000);
        }

        function updateytplayerInfo() {
            buf = "";
            if (ytplayer) {
                pros = (getBytesLoaded() / getBytesTotal()) * 100;
                time_total = getDuration();
                time_now = getCurrentTime();
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
                if (getCurrentTime() > getDuration() - 10 && cued != hetkel) {
                    //cueVideo(songs[getNext()].watch,0);
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

        function loadNewVideo(watch, startSeconds) {
            if (ytplayer) {
                cbw = parseInt(cookie('BW'));
                if (cbw) {
                    cbw = cookie('BW', cbw + getBytesLoaded());
                } else {
                    cbw = cookie('BW', getBytesLoaded());
                }
                $('#bw').html(b2KMGb(cbw));
                location.href = "#" + songs[hetkel].watch;
                ytplayer.loadVideoById(watch, parseInt(startSeconds), format);
                kontroll = watch;
                setTimeout(function() {
                    addPlay(watch);
                }, 10000);
                $('td,tr').removeClass('current');
                $('#main #' + hetkel + ' td,#main #' + hetkel).addClass('current');
            }
        }

        function playSong() {
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

        function volUp() {
            if (ytplayer) {
                var vol = ytplayer.getVolume();
                ytplayer.setVolume((vol + 10 > 100) ? 100 : vol + 10);
                cookie('volume', ytplayer.getVolume());
            }
        }

        function volDown() {
            if (ytplayer) {
                var vol = ytplayer.getVolume();
                ytplayer.setVolume((vol - 10 < 0) ? 0 : vol - 10);
                cookie('volume', ytplayer.getVolume());
            }
        }

        function getPlayerState() {
            if (ytplayer) {
                return ytplayer.getPlayerState();
            }
        }

        function getBytesLoaded() {
            if (ytplayer) {
                return ytplayer.getVideoBytesLoaded();
            }
        }

        function getBytesTotal() {
            if (ytplayer) {
                return ytplayer.getVideoBytesTotal();
            }
        }

        function getCurrentTime() {
            if (ytplayer) {
                return ytplayer.getCurrentTime();
            }
        }

        function getDuration() {
            if (ytplayer) {
                return ytplayer.getDuration();
            }
        }

        function muteSong() {
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

        function cueVideo(id, seconds) {
            if (ytplayer) {
                ytplayer.cueVideoById(id, seconds);
            }
        }

        function seekTo(seconds) {
            if (ytplayer) {
                ytplayer.seekTo(seconds, true);
            }
        }

        function setSongtitle() {
            document.title = songs[hetkel].title + ' - <?php echo PROG_NAME;?>';
            $('#song_title').html(songs[hetkel].title);
        }

        function nextSong() {
            enne = hetkel;
            hetkel = getNext();
            if (enne == hetkel) {
                seekTo(0);
            } else {
                track(hetkel);
            }
        }

        function setYourMove(down) {
            if (down) {
                next = parseInt($('#main #' + onTheMove).next('tr').attr('id'));
                if (next >= 0) {
                    onTheMove = next;
                } else {
                    onTheMove = parseInt($('#main tr:gt(0):first').attr('id'));
                }
            } else {
                prev = parseInt($('#main #' + onTheMove).prev('tr').attr('id'));
                if (prev >= 0) {
                    onTheMove = prev;
                } else {
                    onTheMove = parseInt($('#main tr:last').attr('id'));
                }
            }
            $('#main tr td').removeClass('checked');
            $("#main tr[id='" + onTheMove + "'] td").addClass('checked');
            $.scrollTo($("#main tr[id='" + onTheMove + "'] td"), {
                offset: -100,
                duration: 100
            });
            return onTheMove;
        }

        function getNext() {
            if (getSelected().length != 0) {
                next = getNextSelectedFromCurrent();
            } else {
                next = parseInt($('#main .current').next('tr').attr('id'));
            }
            if (next >= 0) {
                return next;
            } else {
                return parseInt($('#main tr:gt(0):first').attr('id'));
            }
        }

        function previousSong() {
            prev = parseInt($('#main .current').prev('tr').attr('id'));
            if (prev >= 0) {
                //pass
            } else {
                prev = parseInt($('#main tr:last').attr('id'));
            }
            track(prev);
        }

        function track(nr) {
            if (nr <= kokku) {
                hetkel = nr;
                loadNewVideo(songs[hetkel].watch);
                setSongtitle(songs[hetkel].title, songs[hetkel].watch);
                onTheMove = nr;
            }
        }

        function getSelected(false_list) {
            if (false_list) {
                old = $("#main tr[id='" + hetkel + "'] input[type=checkbox]").attr('checked');
                $("#main tr[id='" + hetkel + "'] input[type=checkbox]").attr('checked', 'true');
            }
            selected = $("#main input[type=checkbox][checked!=false]").toArray();
            if (false_list) {
                $("#main tr[id='" + hetkel + "'] input[type=checkbox]").attr('checked', old);
            }
            return selected;
        }

        function anySelected() {
            if (getSelected().length) {
                return 1;
            }
            return 0;
        }

        function getNextSelectedFromCurrent() {
            selected = getSelected(1);
            passed_current = false;
            modded = false;
            next = hetkel;
            $.each(selected, function(i, e) {
                if (passed_current === true && modded === false) {
                    next = parseInt($(e).attr('id'));
                    modded = true;
                }
                if (parseInt($(e).attr('id')) == hetkel) {
                    passed_current = true;
                }
            });
            if (modded === false) {
                next = parseInt($("#main input[type=checkbox][checked!=false]:first").attr('id'));
            }
            return next;
        }

        function create_playlist() {
            if (anySelected() == 0) {
                return;
            }
            url = '';
            $('#main input[type=checkbox]').each(function() {
                if ($(this).attr('checked')) {
                    url += $(this).val() + ',';
                }
            })
            if (url != '') {
                url = url.substring(0, url.length - 1)
                location.href = '?create_playlist=' + url
            }
        }

        function shuffle() {
            <?php
            $tmp = $_GET;
            $tmp['shuffle'] = 'true';
            echo "location.href = '?".http_build_query($tmp, '', '&')."';\n"; ?>
        }

        function toggleLayout() {
            <?php
            $tmp = $_GET;
            $tmp['toggleLayout'] = 'true';
            echo "location.href = '?".http_build_query($tmp, '', '&')."';\n"; ?>
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
    <div id="text">
        <a href='./'><?php echo PROG_NAME;?></a>
    </div>
    <div id='links'>
        <?php if(loggedin()){echo "<a href='?logout=true'>logout</a>";} ?>
        <form>
            <input name=q size=15 value="<?php echo isset($_GET['q'])?$_GET['q']:'';?>">&nbsp;<input type=submit value='Search'>
        </form>
        <input type='button' value='<?php echo $toggle_text;?>' onclick="toggleLayout()">
        <input type='button' value='Shuffle' onclick="shuffle()">
    </div>
</div>

<div id="bottombar" style="filter:alpha(opacity=80);-moz-opacity:0.8;-khtml-opacity: 0.8;opacity: 0.8;position:fixed;bottom:0px;left:0px; height:50px; width:100%; background-color:#9CC8D3;display: block;z-index:10;">&nbsp;</div>
<div style="position:fixed;bottom:0px;left:0px;font-size:20px;z-index:11;width:100%;">
    <table border=0 width=100% height=45>
        <tr>
            <td width=45><a href="javascript:void(0);" onclick="previousSong();"><img src='images/prev.png' border=0></a></td>
            <td width=45><a href="javascript:void(0);" onclick="playSong();"><img src='images/pause.png' id=play border=0></a></td>
            <td width=45><a href="javascript:void(0);" onclick="muteSong();"><img src='images/unmute.png' id=mute border=0></a></td>
            <td width=45><a href="javascript:void(0);" onclick="volUp();"><img src='images/vol_up.png' border=0></a></td>
            <td width=50><a href="javascript:void(0);" onclick="volDown();"><img src='images/vol_down.png' border=0></a></td>
            <td width=70><a href="javascript:void(0);" onclick="nextSong();"><img src='images/next.png' border=0></a></td>
            <td id='song_title'>&nbsp;</td>
            <td id='time' width=180>&nbsp;</td>
            <td width=120>
            <form action="" method="POST">
            <select name="format" id='format'>
<?php 
    foreach($formats as $f){echo "<option value='$f' ".(getformat()==$f?'selected':'').">$f</option>";}
?>
            </select>
            <input type="submit" value="OK" onclick="return set_format();">
            </form>
            </td>
        </tr>
    </table>
</div>

<div id="content">
    <table border=0 width=95%>
      <tr>
        <td valign=top width=310>    
            <div id="ytapiplayer">You need Flash player 8+ and JavaScript enabled to view this video.</div> 
            <script type="text/javascript"> 
                var params = { allowScriptAccess: "always", bgcolor: "#cccccc" };
                var atts = { id: "myytplayer" };
                swfobject.embedSWF("http://www.youtube.com/apiplayer?enablejsapi=1&playerapiid=ytplayer", 
                             "ytapiplayer", "<?php echo $flash_width;?>", "<?php echo $flash_height;?>", "8", null, null, params, atts);
            </script> 
            <div id=slider></div>
            <h3>Latest playlists:</h3>
            <table border=0 cellpadding=2 cellspacing=3 width=100% class=colorize>
                <tr class=thead>
                    <td><b>Link</b></td>
                    <td><b>Date</b></td>
                    <td width=30><b>Vids</b></td>
                    <?php if(loggedin()){ echo "<td width=30><b>Delete</b></td>";} ?>
                </tr>
                <?php
                    $i = 0;
                    $ret = $c->q("SELECT playlist, DATE_FORMAT(time, '%D %b %Y %H:%i') as time2, count(video_id) as vids FROM videos_playlist group by playlist order by time desc limit 0,20;");
                    while($row = mysql_fetch_assoc($ret)){
                            $i++;
                            echo "<tr>";
                            echo "<td><a href='?p={$row['playlist']}'>{$row['playlist']}</a></td>";
                            echo "<td>{$row['time2']}</td>";
                            echo "<td>{$row['vids']}</td>";
                            if(loggedin())
                                echo "<td><a href='?delete_playlist={$row['playlist']}'>delete</a></td>";
                            echo "</tr>";
                    }
                ?>
            </table>

            <h3>Youtube Users:</h3>
            <div class='odd padd10' style='text-align: justify;'><?php
                    $ret = $c->q("select distinct user, count(user) kaunt from videos group by user");
                    $result = array();
                    $max = $min = 0;
                    $max_font = 25;
                    $min_font = 10;
                    while($rida = mysql_fetch_array($ret)){
                        $result[$rida['user']] = $rida['kaunt'];
                    }
                    arsort($result);
                    $result = array_slice($result, 0, 40);
                    $result = shuffle_assoc($result);
                    if(count($result)){
                        $max = max($result);
                        $min = min($result);
                    }

                    foreach($result as $user=>$kaunt){
                        $font = $min_font + floor(($kaunt*100/$max)*($max_font-$min_font)/100);
                        print "<span style='font-size:{$font}px'><a href='?user=$user'>$user</a></span> ";
                    }?>
            </div>
            <h3>Top users:</h3>
            <table border=0 cellpadding=2 cellspacing=3 width=100% class=colorize>
                <tr class=thead>
                    <td><b>Link</b></td>
                    <td width=30><b>Vids</b></td>
                </tr>
                <?php
                    $i = 0;
                    $ret = $c->q("SELECT COUNT(*) AS ridu,bkey FROM  videos GROUP BY bkey ORDER BY ridu desc LIMIT 0 , 40");
                    $sum = 0;
                    while($row = mysql_fetch_assoc($ret)){
                        $i++;
                        echo "<tr>";
                        echo "<td><a href='?bkey={$row['bkey']}'>{$row['bkey']}</a></td>";
                        echo "<td>{$row['ridu']}</td>";
                        echo "</tr>";
                        $sum+=$row['ridu'];
                    }
                    echo "<tr>";
                    echo "<td>&nbsp;</td>";
                    echo "<td>{$sum}</td>";
                    echo "</tr>";
                ?>
            </table>

            <h3>Add you own:</h3>
            <div class='odd padd10'>
                Drag this to your bookmark bar : <b><a href="javascript:(function(){var script = document.createElement('script');script.setAttribute('type','text/javascript'); script.setAttribute('src','http://<?php
                    echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                ?>/?bookmark='+encodeURIComponent(location.href)); document.body.appendChild(script); })();" onClick="alert('Drag this to your bookmark bar ;)'); return false;">Add to Hecto</a></b>
            </div>

            <h3>Get source:</h3>
            <div class='odd padd10'>
            <b>git clone <a href="https://github.com/tanelpuhu/hecto">git://github.com/tanelpuhu/hecto.git</a></b>
            </div>
        </td>
        <td valign=top>
            <div id=dbuug></div>
            <?php if(count($rows_php)>0){?>
                <table border=0 cellpadding=2 cellspacing=3 width=100% class=colorize id=main>
                    <tr class="thead nodrop nodrag">
                        <td><b><a href='<?php echo orderby('title');?>'>Title</a></b><span class=right><b>Time</b></span></td>
                        <?php
                            if(loggedin()){
                                echo "<td width=30><b><a href='".orderby('plays')."'>Plays</a></b></td>";
                                echo "<td width=30><b><a href='".orderby('erroneous')."'>Errors</a></b></td>";
                                echo "<td width=30><b><a href='".orderby('bkey')."'>BKey</a></b></td>";
                                echo "<td width=30><b>Delete</b></td>";
                            }
                        ?>
                    </tr> 
                    <?php
                        $i = 0;
                        foreach($rows_php as $row){
                            echo "<tr id={$i}>";
                            $imgs[$i] = array('img'=>yImage($row['watch'],0), 'watch'=>$row['watch']);
                            echo "<td><input name='playlist' value='{$row['id']}' id={$i} type=checkbox>&nbsp;&nbsp;";
                            echo "<a href='#{$row['watch']}' onclick='track({$i})'>{$row['title']}</a>";
                            echo "<span class=right><nobr>{$row['time']}</nobr></span></td>";
                            if(loggedin()){
                                echo "<td>{$row['plays']}</td>";
                                echo "<td>{$row['erroneous']}</td>";
                                echo "<td>{$row['bkey']}</td>";
                                echo "<td><a href='?delete={$row['id']}'>delete</a></td>";
                            }
                            echo "</tr>";
                            $i++;
                        }
                echo "</table>";
                if(!isset($_GET['p'])){ echo "<input type='button' value='Create playlist' onclick='create_playlist()'>";}
                echo "<span class=right>";
                if($prev_link){
                    if(($start - LEHEL) <=0){
                        unset($_GET['start']);
                    }else{
                        $_GET['start'] = $start - LEHEL;
                    }
                    echo " <a href='?". http_build_query($_GET, '', '&') ."'><<< Previous</a>";
                }
                if($prev_link and $next_link) echo "|";
                if($next_link){
                    $_GET['start'] = $start + LEHEL;
                    echo " <a href='?". http_build_query($_GET, '', '&') ."'>Next >>></a>";
                }
                echo "</span>";
            }else{
                    echo "<h3>None found!</h3>";
            }
    if($layout != 'wide'){
    ?>
        </td>
        <td valign=top width=210>
            <?php
                $i=0;
                foreach((array)$imgs as $id=>$img){
                    echo "<a href='#{$img['watch']}' onclick='track({$id});'><img src='{$img['img']}' width=65 height=48 vspace=1 hspace=1 border=0></a>";
                    $i++;
                    if($i>2){
                    echo "<br>";
                    $i=0;
                    }
                }
    }
            ?>
        </td>
      </tr>
    </table>
</div>
<?php
$time_end = microtime_float()-$time_start;
echo <<<END
<div id='footer'>
    ~<span id=bw>#</span>
    /
    {$time_end}
    /
    <a href='javascript:void(0);' onClick="set_key();">{$_COOKIE['h2bkey']}</a>
    <br>
    <a href='http://twitter.com/tanel'>@tanel</a><br>
    <a href='http://tanelpuhu.com'>tanelpuhu.com</a><br></div>
END;
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
