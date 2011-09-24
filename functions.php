<?php
if(!file_exists("config.php")) {
    die("Config file missing!");
}

include "config.php";
include "func.cache.php";
include "func.mysql.php";

function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

//I hate IE.
if(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])) {
    header("Location: http://www.google.com/chrome");
    die();
} 

$c = new MySqlC(DB_SERVER, DB_USERNAME, DB_PASSWORD);
$c->select(DB_NAME);

$api_url = "http://gdata.youtube.com/feeds/api/videos/";

function login_cookies() {
    $check = substr($_GET['q'],0,32);
    setcookie("h2hash", md5($_SERVER['SERVER_SIGNATURE'].$_SERVER['HTTP_USER_AGENT'].md5($check).$check.SALT), time()+60*60*24*31);
    setcookie("h2chec", $check, time()+60*60*24*31);
    header("Location: ".$_SERVER['HTTP_REFERER']);
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

function setBkey($value){
    setcookie("h2bkey", $value, time()+60*60*24*365*5);
}

function getBkey(){
    if(isset($_GET['pluginkey'])){
        $pluginkey = mysql_escape_string($_GET['pluginkey']);
        if(trim($pluginkey)==""){
            $pluginkey = getNewUniqRow('videos','bkey');
        }
        $bkey = substr($pluginkey,0,32);
    }elseif(isset($_COOKIE['h2bkey'])){
        $bkey = $_COOKIE['h2bkey'];
    }else{
        $bkey = getNewUniqRow('videos','bkey');
    }
    $bkey = mysql_escape_string($bkey);
    setBkey($bkey);
    return $bkey;
}

function bookmark($msg, $watch = '') {
    if(isset($_GET['plugin'])){
        $msg = str_replace(" ", "&nbsp;", $msg);
        die(json_encode(array('msg'=>$msg,'watch'=>$watch,'bkey'=>getBkey())));
    }
   $javascript = <<<END
var shown = false;
function by_id(id){
    return document.getElementById(id);
}
function hidem(){
    document.body.removeChild(by_id('h2bg1'));
    document.body.removeChild(by_id('h2bg2'));
    shown = false;
}

function clbk(dic){
    if(shown!=true){
        shown = true;
        var d = document.createElement('div');
        var style = 'text-align:center;position:absolute;top:0;left:0;background:#333;filter:alpha(opacity=70);-moz-opacity:0.7;-khtml-opacity: 0.7;opacity: 0.7;width: 100%; height: 100px; z-index: 30003;';
        d.setAttribute('id','h2bg1');
        d.setAttribute('style',style);
        d.setAttribute('onClick','hidem()');
        document.body.appendChild(d);

        var d = document.createElement('div');
        var style = "text-align:center;position:absolute;top:0;left:0;z-index: 40003;width:100%;margin-top:20px;font-size:50px;font-weight:bold;color:#fff;font-family:'Trebuchet MS', sans-serif;";
        d.setAttribute('id','h2bg2');
        d.setAttribute('style',style);
        d.setAttribute('onClick','hidem()');
        document.body.appendChild(d);
        by_id('h2bg2').innerHTML=dic.msg;
    }
}

END;
   $call = json_encode(array('msg'=>$msg));;
   $call = "clbk($call);";
   die($javascript.$call);
}

function add($video) {
    global $api_url, $c, $YTKey;
    $watch = "";
    $url = parse_url($video);
    $url['host'] = ltrim($url['host'], "w.");
    if(strtolower($url['host']) != "youtube.com") {
        bookmark('Wrong page, are you on youtube.');
    }
    $query = array_key_exists('fragment', $url)?$url['fragment']:$url['query'];
    $query = $url['query'];
    $v = "";
    parse_str($query);
    $watch = $v;
    if(preg_match('/[^a-z0-9_-]+/i', $watch)) {
        bookmark('Illegal stuff in video id.');
    }

    if($watch) {
        $q = "SELECT count(watch) FROM videos WHERE watch = '".(mysql_escape_string($watch))."'";
        $ret = mysql_fetch_array($c->q($q));
        if($ret[0] != 0){
            if(!isset($_GET['bookmark'])){
                header('Location: ./?response=exists&watch='.$watch);
            }else bookmark('Already exists!',$watch);
            die();
        }else{
            $xml = cache($api_url.$watch.$YTKey,120);
            $xml_array = simplexml_load_string($xml);
            $title = addslashes(end($xml_array->title));
            $user = addslashes($xml_array->author->name);
            if($title != ""){
                $bkey = getBkey();
                $q = "INSERT INTO videos (id, title, user, watch, time, bkey) VALUES (NULL,'{$title}','{$user}','{$watch}',CURRENT_TIMESTAMP, '{$bkey}')";
                $c->q($q);
                yImage($watch);
                if(!isset($_GET['bookmark'])){
                    header('Location: ./?response=added&watch='.$watch);
                }else bookmark('Added!',$watch);
            }else{
                if(!isset($_GET['bookmark'])){
                    header('Location: ./?response=empty_title');
                }else bookmark('No Title???');
                }
            die();
        }
    } else {
        if(!isset($_GET['bookmark'])){
            header('Location: ./?response=wrong_format');
        } else bookmark('Wrong page, are you on youtube?');
    }
}

function getNewUniqRow($table, $column, $min = 5, $max = 32){
    global $c;
    $id = md5(time().microtime().$_SERVER['HTTP_USER_AGENT']);
    while($min<=$max){
        $k2 = substr($id,0,$min);
        $q = "SELECT count({$column}) FROM {$table} where {$column} = '$k2';";
        $res = $c->q($q);
        $fetch = mysql_fetch_array($res);
        if($fetch[0] == 0){
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
    $sort = 'asc';
    if(!isset($_GET['sort'])){
        $_GET['sort'] == $sort;
    }
    $tmp['sort'] = $sort == 'asc'?'desc':'asc';
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
    header("Location: ".$_SERVER['HTTP_REFERER']);
}

if(isset($_GET['delete'])) {
    if(!loggedin()) die("Not allowed!");
    $delete = (int)$_GET['delete'];
    $c->q("delete from videos where id = {$delete} limit 1;");
    $c->q("delete from videos_playlist where video_id = {$delete};");
    header("Location: ".$_SERVER['HTTP_REFERER']);
}

if(isset($_GET['delete_playlist'])) {
    if(!loggedin()) die("Not allowed!");
    $delete_playlist = (int)$_GET['delete_playlist'];
    $c->q("delete from videos_playlist where playlist = {$delete_playlist};");
    header("Location: ".$_SERVER['HTTP_REFERER']);
}

if(isset($_GET['add'])) {
    add($_GET['add']);
}

if(isset($_GET['bookmark'])) {
    add($_GET['bookmark']);
    die();
}

if(isset($_GET['create_playlist'])) {
    $id = getNewUniqRow('videos_playlist','playlist');
    $create_playlist = explode(',',$_GET['create_playlist']);
    $in = array();
    foreach((array)$create_playlist as $playlist) {
        $playlist = (int)$playlist;
        if($playlist){
            $in[] = "('{$id}',{$playlist})";
        }
    }
    if(count($in)) {
        $c->q("insert into videos_playlist (playlist, video_id) values ".implode(',', $in));
    }
    header("Location: ?p={$id}");
}

if(isset($_GET['addPlay'])) {
    $c->q("update videos set plays=plays+1 where watch='".mysql_escape_string($_GET['addPlay'])."' limit 1;");
    die();
}
if(isset($_GET['erroneous'])) {
    $c->q("update videos set erroneous=erroneous+1 where watch='".mysql_escape_string($_GET['erroneous'])."' limit 1;");
    die();
}
if(isset($_GET['toggleLayout'])) {
    if(isset($_COOKIE['layout'])){
        setcookie("layout", 0, time()-60*60*24*31);
    }else{
        setcookie("layout", 1, time()+60*60*24*31*500);
    }
    header("Location: {$_SERVER['HTTP_REFERER']}");
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
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die();
}

if(isset($_GET['set_key'])) {
    setBkey($_GET['set_key']);
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die();
}

include "query.php";
