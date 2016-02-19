<?php
$page = 1;
if(isset($_GET['page'])) {
    $tmp_page = (int)$_GET['page'];
    if($tmp_page > 0) {
        $page = $tmp_page;
    }
}
$start = $page * LEHEL - LEHEL;

$q = "SELECT v.id, v.title, DATE_FORMAT(v.time, '%H:%i %d/%m/%y') as time, v.watch,v.plays,v.erroneous,v.bkey FROM videos as v"; 
if(isset($_GET['p'])){
    $q.=", videos_playlist as v2 where v2.playlist = :p and v.id = v2.video_id";
}elseif(isset($_GET['bkey'])){
    $q.=" where v.bkey = :bkey";
}elseif(isset($_GET['user'])){
    $q.=" where v.user = :user";
}elseif(isset($_GET['q'])){
    $_GET['q'] = trim($_GET['q']);
    $q.=" where MATCH(v.title) AGAINST  (:q)";
}elseif(isset($_GET['watch'])){
    $q.=" where v.watch = :watch";
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

//PDO can't sort
$q.=" order by v.$order $sort limit :start,:lehel";

$sth = $con->prepare($q);
//$sth->bindValue(':order', "v.$order");
//$sth->bindValue(':sort', $sort);
$sth->bindParam(':start', $start, PDO::PARAM_INT);
$sth->bindValue(':lehel', (LEHEL+1), PDO::PARAM_INT);

if(isset($_GET['p'])){
    $sth->bindValue(':p', $_GET['p']);
}elseif(isset($_GET['bkey'])){
    $sth->bindValue(':bkey', $_GET['bkey']);
}elseif(isset($_GET['user'])){
    $sth->bindValue(':user', $_GET['user']);
}elseif(isset($_GET['q'])){
    $_GET['q'] = trim($_GET['q']);
    $sth->bindValue(':q', $_GET['q']);
}elseif(isset($_GET['watch'])){
    $sth->bindValue(':watch', $_GET['watch']);
}

$sth->execute();

$rows_php = array();
$hetkel = 0;
$rm_login_data = loggedin();
$watch = isset($_GET['watch'])?$_GET['watch']:'';
while($row = $sth->fetchObject()) {
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
if($sth->rowCount() > LEHEL) {
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
