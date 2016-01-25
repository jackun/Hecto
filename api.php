<?php
include "functions.php";

$time_start = microtime_float();
$start = 0;
if(isset($_GET['start'])) {
  $start = (int)$_GET['start'];
  if($start < 0) {
    $start = 0;
  }
}
$rows = 1000;
if(isset($_GET['rows'])) {
  $rows = (int)$_GET['rows'];
  if($rows > 1000) {
    $rows = 1000;
  }
}

$return = array();
$bkey = '';
if(isset($_GET['bkey'])) {
  $bkey = $_GET['bkey'];
  $return['bkey'] = $bkey;
}

$prep = $con->execute("
    SELECT id,
           watch,
           title,
           user,
           plays,
           erroneous,
           bkey,
           time
      FROM videos
      where bkey = ? or ? = ''
      order by id desc
      limit ?,?
", $bkey, $bkey, $start, $rows);

foreach ($prep as $rida) {
    $return['song'][] = $rida;
}

$return['stat']['time'] =  microtime_float()-$time_start;
$data =  json_encode($return);

if(isset($_GET['callback'])){
    $data = "{$_GET['callback']}($data)";
}

echo $data;
