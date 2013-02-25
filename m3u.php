#EXTM3U
<?php
include "functions.php";
$time_start = microtime_float();

$start = isset($_GET['start'])?(int)$_GET['start']:0;
$rows = isset($_GET['rows'])?(int)$_GET['rows']:200;
if($start<0) $start = 0;
if($rows>200) $rows = 200;

$result = $con->execute("SELECT id,watch,title,user,plays,erroneous,bkey,time  FROM videos order by id desc limit ?,?", $start, $rows);
foreach ($result as $row) {
    print "#EXTINF:0,{$row->title}\nhttp://youtube.com/watch?v={$row->watch}\n";
}
?>
