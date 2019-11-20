<?php
if(!file_exists("config.php")) {
    die("Config file missing!");
}

include "config.php";
include "mysql.php";
include "func.cache.php";

function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

//I hate IE.
if(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])) {
    header("Location: http://www.google.com/chrome");
    die();
}

function return_to_referer() {
    $path = './';
    if(isset($_SERVER['HTTP_REFERER'])) {
        $path = $_SERVER['HTTP_REFERER'];
    }
    header("Location: {$path}");
    die();
}

function login_cookies() {
    $check = substr($_GET['q'],0,32);
    setcookie("h2hash", md5($_SERVER['SERVER_SIGNATURE'].$_SERVER['HTTP_USER_AGENT'].md5($check).$check.SALT), time()+60*60*24*31);
    setcookie("h2chec", $check, time()+60*60*24*31);
    return_to_referer();
}

function loggedin() {
    if($_SERVER['REMOTE_ADDR']=='127.0.0.1') return 1;
    if(isset($_COOKIE['h2hash']) and isset($_COOKIE['h2chec'])){
        if($_COOKIE['h2hash'] == md5($_SERVER['SERVER_SIGNATURE'].$_SERVER['HTTP_USER_AGENT'].md5($_COOKIE['h2chec']).$_COOKIE['h2chec'].SALT)){
            return 1;
        }
    }
    return 0;
}

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

function set_bkey($value){
    $bkey = substr($value, 0, 32);
    setcookie("h2bkey", $bkey, time()+60*60*24*365*5);
}

function get_bkey($con) {
    if(isset($_GET['pluginkey'])){
        $bkey = $_GET['pluginkey'];
        if(trim($bkey) == ""){
            $bkey = get_new_uniq_row($con, 'videos', 'bkey');
        }
    } elseif(isset($_COOKIE['h2bkey'])) {
        $bkey = $_COOKIE['h2bkey'];
    } else {
        $bkey = get_new_uniq_row($con, 'videos', 'bkey');
    }
    set_bkey($bkey);
    return $bkey;
}

function bookmark($con, $msg, $watch = '') {
    if(isset($_GET['plugin'])){
        $msg = str_replace(" ", "&nbsp;", $msg);
        die(json_encode(array(
            'msg' => $msg,
            'watch' => $watch,
            'bkey' => get_bkey($con))
        ));
    }
    $time = time();
    $javascript = <<<END
(function(msg){
    var shown = false,
        by_id = function (id){
            return document.getElementById(id);
        },
        hidem = function(){
            document.body.removeChild(by_id('h2bg1'));
            document.body.removeChild(by_id('h2bg2'));
            shown = false;
        },
        clbk{$time} = function (dic) {
            if(shown != true) {
                shown = true;
                var d = document.createElement('div');
                var style = 'text-align:center;position:absolute;top:0;left:0;background:#333;filter:alpha(opacity=70);-moz-opacity:0.7;-khtml-opacity: 0.7;opacity: 0.7;width: 100%; height: 100px; z-index: 30003;';
                d.setAttribute('id','h2bg1');
                d.setAttribute('style',style);
                document.body.appendChild(d);

                var d = document.createElement('div');
                var style = "text-align:center;position:absolute;top:0;left:0;z-index: 40003;width:100%;margin-top:20px;font-size:50px;font-weight:bold;color:#fff;font-family:'Trebuchet MS', sans-serif;";
                d.setAttribute('id','h2bg2');
                d.setAttribute('style',style);
                document.body.appendChild(d);
                by_id('h2bg2').innerHTML = dic.msg;
            }
            setTimeout(hidem, 5000);
        };
        clbk{$time}(msg);
})
END;
   $call = json_encode(array(
        'msg' => $msg,
        'watch' => $watch
    ));;
   die("$javascript($call)");
}

function add($con, $video) {
    global $YTKey;
    $url = parse_url($video);
    $url['host'] = ltrim($url['host'], "w.");
    if(strtolower($url['host']) != "youtube.com") {
        bookmark($con, 'Wrong page, are you on youtube.');
    }
    $query = $url['query'];
    $v = "";
    parse_str($query);
    $watch = $v;
    if(preg_match('/[^a-z0-9_-]+/i', $watch)) {
        bookmark($con, 'Illegal stuff in video id.');
    }

    if($watch) {
        $ret = $con->execute("SELECT 1 FROM videos WHERE watch = ?", $watch);
        if($ret->rowcount() > 0) {
            if(!isset($_GET['bookmark'])) {
                header("Location: ./?response=exists&watch={$watch}");
            } else {
                bookmark($con, 'Already exists!', $watch);
            }
        } else {
            $json = cache("https://www.googleapis.com/youtube/v3/videos?key={$YTKey}&part=snippet&id={$watch}", 120);
            $data = json_decode($json);
            $title = substr($data->items[0]->snippet->title, 0, 64);
            $user = 'nobody';
            if($title != "") {
                $bkey = get_bkey($con);
                $con->execute("INSERT INTO videos (id, title, user, watch, time, bkey) VALUES (NULL, ?, ?, ?, CURRENT_TIMESTAMP, ?)", $title, $user, $watch, $bkey);
                yImage($watch);
                if(!isset($_GET['bookmark'])) {
                    header('Location: ./?response=added&watch='.$watch);
                } else {
                    bookmark($con, 'Added!',$watch);
                }
            } else {
                if(!isset($_GET['bookmark'])) {
                    header('Location: ./?response=empty_title');
                } else {
                    bookmark($con, 'No Title???');
                }
            }
        }
    } else {
        if(!isset($_GET['bookmark'])){
            header('Location: ./?response=wrong_format');
        } else {
            bookmark($con, 'Wrong page, are you on youtube?');
        }
    }
    die();
}

function get_new_uniq_row($con, $table, $column, $min = 5, $max = 32){
    $id = md5(time().microtime().$_SERVER['HTTP_USER_AGENT']);
    while($min <= $max) {
        $k2 = substr($id, 0, $min);
        $res = $con->execute("SELECT 1 FROM {$table} where {$column} = ?", $k2);
        if($res->rowcount() == 0) {
            $id = $k2;
            break;
        }
        $min++;
    }
    return $id;
}

function orderby($mis) {
    $tmp = $_GET;
    $tmp['order'] = $mis;
    if(!isset($_GET['sort'])){
        $tmp['sort'] = 'asc';
    }
    $tmp['sort'] = $tmp['sort'] == 'asc'?'desc':'asc';
    return "?". http_build_query($tmp, '', '&');
}

function yImage($watch, $init = 1) {
    $url = "http://i2.ytimg.com/vi/{$watch}/default.jpg";
    if(!$init){
        $ext = ".".end(explode(".", $url));
        return "cache/".md5($url).$ext;
    }
    return "cache/".cache_image($url);
}

function shuffle_assoc($array) {
    $keys = array_keys($array);
    shuffle($keys);
    $new = array();
    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }
    return $new;
}

if(isset($_GET['logout'])) {
    setcookie("h2hash", 0, time()-60*60*24*31);
    setcookie("h2chec", 0, time()-60*60*24*31);
    return_to_referer();
}

if(isset($_GET['delete'])) {
    if(!loggedin()) die("Not allowed!");
    $delete = (int)$_GET['delete'];
    $con->execute("delete from videos where id = ? limit 1", $delete);
    //$con->execute("delete from videos_playlist where video_id = ?", $delete);
    return_to_referer();
}

if(isset($_GET['delete_playlist'])) {
    if(!loggedin()) die("Not allowed!");
    $delete_playlist = (int)$_GET['delete_playlist'];
    $con->execute("delete from videos_playlist where playlist = ?", $delete_playlist);
    return_to_referer();
}

if(isset($_GET['add_one_play'])) {
    $con->execute("update videos set plays=plays+1 where watch=? limit 1", $_GET['add_one_play']);
    die();
}
if(isset($_GET['erroneous'])) {
    $con->execute("update videos set erroneous=erroneous+1 where watch=? limit 1;", $_GET['erroneous']);
    die();
}
if(isset($_GET['toggleLayout'])) {
    if(isset($_COOKIE['layout'])){
        setcookie("layout", 0, time()-60*60*24*31);
    }else{
        setcookie("layout", 1, time()+60*60*24*31*500);
    }
    return_to_referer();
}

if(isset($_COOKIE['layout'])) {
    $layout = 'wide';
    $toggle_text = 'Tiny';
    $flash_width = 510;
    $flash_height = 380;
}else{
    $layout = 'tiny';
    $toggle_text = 'Wide';
    $flash_width = 300;
    $flash_height = 216;
}

if(isset($_POST['format'])) {
    setformat($_POST['format']);
    return_to_referer();
}

if(isset($_GET['set_key'])) {
    set_bkey($_GET['set_key']);
    return_to_referer();
}

$bkey = get_bkey($con);
#Bookmark / Plugin
if(isset($_GET['bookmark'])) {
    if(isset($_GET['pluginkey'])) {
        set_bkey($_GET['pluginkey']);
    }
    add($con, $_GET['bookmark']);
    die();
}

if(isset($_GET['q'])) {
    if($_GET['q'] == 'login:' . SALT) {
        login_cookies();
        die();
    }
}

include "query.php";
