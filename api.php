<?php
include "functions.php";

#for chrome plugin to get first/defult key
if(isset($_GET['get_bkey'])){
   die(json_encode(array('msg'=>getBkey())));
}

$time_start = microtime_float();

$start = isset($_GET['start'])?(int)$_GET['start']:0;
$rows = isset($_GET['rows'])?(int)$_GET['rows']:50000;
if($start<0) $start = 0;
if($rows>100000) $rows = 100000;

$result = $c->q("SELECT id,watch,title,user,plays,erroneous,bkey,time  FROM videos order by id desc limit $start,$rows");
$return = array();
while($rida = mysql_fetch_assoc($result)){
    $return['song'][] = $rida;
}
$result = $c->q("SELECT playlist,video_id FROM videos_playlist order by time desc limit $start,$rows");
while($rida = mysql_fetch_assoc($result)){
    $return['playlist'][] = $rida;
}
$return['stat']['time'] =  microtime_float()-$time_start;
$data =  json_encode($return);
if(isset($_GET['callback'])){
    $data = "{$_GET['callback']}($data)";
}
echo $data;
?>
