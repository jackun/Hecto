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
$rows = 50000;
if(isset($_GET['rows'])) {
  $rows = (int)$_GET['rows'];
  if($rows > 100000) {
    $rows = 100000;
  }
}

$return = array();
$where = '';
$bkey = '';
if(isset($_GET['bkey'])) {
  $bkey = $_GET['bkey'];
  $return['bkey'] = $bkey;
  $where = " where bkey = :bkey";
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
      {$where}
      order by id desc
      limit :start,:rows
");

$prep->setFetchMode(PDO::FETCH_ASSOC);
$prep->bindValue(":start", $start, PDO::PARAM_INT);
$prep->bindValue(":rows", $rows, PDO::PARAM_INT);
if($bkey) {
    $prep->bindValue(":bkey", $bkey, PDO::PARAM_STR);
}
$prep->execute();
foreach ($prep as $rida) {
    $return['song'][] = $rida;
}

$return['stat']['time'] =  microtime_float()-$time_start;
$data =  json_encode($return);

if(isset($_GET['callback'])){
    $data = "{$_GET['callback']}($data)";
}

echo $data;
