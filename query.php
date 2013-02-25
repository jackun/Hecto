<?php
$page = 1;
if(isset($_GET['page'])) {
    $tmp_page = (int)$_GET['page'];
    if($tmp_page > 0) {
        $page = $tmp_page;
    }
}
$start = $page * LEHEL - LEHEL;

$q = "SELECT v.id, v.title, DATE_FORMAT(v.time, '%H:%i %d/%m') as time, v.watch,v.plays,v.erroneous,v.bkey FROM videos as v"; 
if(isset($_GET['p'])){
    $q.=", videos_playlist as v2 where v2.playlist = '".mysql_escape_string($_GET['p'])."' and v.id = v2.video_id";
}elseif(isset($_GET['bkey'])){
    $q.=" where v.bkey = '".mysql_escape_string($_GET['bkey'])."'";
}elseif(isset($_GET['user'])){
    $q.=" where v.user = '".mysql_escape_string($_GET['user'])."'";
}elseif(isset($_GET['q'])){
    $_GET['q'] = trim($_GET['q']);
    $q.=" where MATCH(v.title) AGAINST  ('".mysql_escape_string($_GET['q'])."')";
}elseif(isset($_GET['watch'])){
    $q.=" where v.watch = '".mysql_escape_string($_GET['watch'])."'";
}
$sorts  = array('asc','desc');
$orders = array('id','title','plays','erroneous');

$sort = "desc";
if(isset($_GET['sort']) and in_array($_GET['sort'],$sorts)) {
    $sort = $_GET['sort'];
}

$order = "id";
if(isset($_GET['order']) and in_array($_GET['order'],$orders)) {
    $order = $_GET['order'];
}

$q.=" order by v.{$order} {$sort} limit {$start},".(LEHEL+1);

$ret = $con->execute($q);
$rows_php = array();
$hetkel = 0;
$rm_login_data = loggedin();
$watch = isset($_GET['watch'])?$_GET['watch']:'';
foreach ($ret as $row) {
    if($watch == $row->watch) {
        $hetkel = count($rows_php);
    }
    // $row->id = (int)$row['id'];
    if($rm_login_data){

    }else{
        unset($row->plays);
        unset($row->erroneous);
    }
    $rows_php[] = $row;
}
$next_link = false;
$prev_link = false;
if($ret->rowcount() > LEHEL) {
    $next_link = true;
    array_pop($rows_php);
}

if($start >= LEHEL){
    $prev_link = true;
}

if(isset($_GET['shuffle'])) {
    shuffle($rows_php);
}
$rows_json = json_encode($rows_php);
