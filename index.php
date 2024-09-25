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
    <script src="fun.js" type="text/javascript"></script>
    <script type="text/javascript">
        function get_self() {
            return "<?php echo $_SERVER['PHP_SELF']; ?>";
        }

        <?php if(!isset($_GET["noinfi"])): ?>
        $(document).ready(paginate);
        <?php endif; ?>

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

        function set_key() {
            var key = prompt('New key?', '<?php
                echo $_COOKIE['h2bkey'];
            ?>');
            if (key) {
                location.href = '?set_key=' + encodeURIComponent(key);
            }
        }

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
