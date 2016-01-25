#EXTM3U
<?php
include "functions.php";
$time_start = microtime_float();

$start = isset($_GET['start'])?(int)$_GET['start']:0;
$rows = isset($_GET['rows'])?(int)$_GET['rows']:200;
$bkey = isset($_GET['bkey'])?$_GET['bkey']:'';
if($start<0) $start = 0;
if($rows>200) $rows = 200;

$result = $con->execute("SELECT id,watch,title,user,plays,erroneous,bkey,time  FROM videos where bkey = ? or ? = '' order by id desc limit ?,?", $bkey, $bkey, $start, $rows);
foreach ($result as $row) {
    print "#EXTINF:0,{$row->title} (by {$row->bkey})\nhttp://youtube.com/watch?v={$row->watch}\n";
}
?>
