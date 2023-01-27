<?php

require __DIR__ . '/vendor/autoload.php';

use TeamTNT\TNTSearch\TNTSearch;
$page = 1;
$indexes = array();

if(isset($_GET['page'])) {
    $tmp_page = (int)$_GET['page'];
    if($tmp_page > 0) {
        $page = $tmp_page;
    }
}
$start = $page * LEHEL - LEHEL;

    if(isset($_GET['q'])){
        $_GET['q'] = trim($_GET['q']);

        $tnt = new TNTSearch;
        $tnt->loadConfig($tnt_config);
        $tnt->selectIndex("title.index");
        $tnt->fuzziness = true;
        $res = $tnt->search($_GET["q"]);
        //print_r($res);

        $i = 0;
        $in = "";
        foreach ($res["ids"] as $item)
        {
            $key = ":id".$i++;
            $in .= "$key,";
            $indexes[$key] = $item; // collecting values into key-value array
        }
    }

function add_query_params()
{
    global $indexes;
    $q = "";
    $c = "where";
    if(isset($_GET['p'])){
        $q.=", videos_playlist as v2 where v2.playlist = :p and v.id = v2.video_id";
    }

    if(isset($_GET['bkey'])){
        $q.=" where v.bkey = :bkey";
        $c = "and";
    }
    if(isset($_GET['user'])){
        $q.=" $c v.user = :user";
        $c = "and";
    }
    if(isset($_GET['q'])){
        //$_GET['q'] = trim($_GET['q']);
        //$q.=" $c MATCH(v.title) AGAINST  (:q)";
        $ids = implode(', ', array_keys($indexes));
        $q.=" $c v.id IN($ids) ORDER BY FIELD(v.id, $ids)";
        $c = "and";
    }
    if(isset($_GET['watch'])){
        $q.=" $c v.watch = :watch";
    }

    return $q;
}

function bind_query_params($sth)
{
    global $indexes;
    if(isset($_GET['p'])){
        $sth->bindValue(':p', $_GET['p']);
    }
    if(isset($_GET['bkey'])){
        $sth->bindValue(':bkey', $_GET['bkey']);
    }
    if(isset($_GET['user'])){
        $sth->bindValue(':user', $_GET['user']);
    }
    if(isset($_GET['q'])){
        $_GET['q'] = trim($_GET['q']);
        //$sth->bindValue(':q', $_GET['q']);

        foreach($indexes as $k => $v)
        {
            //echo "-> $k => $v\n";
            $sth->bindValue($k, $v);
        }
    }
    if(isset($_GET['watch'])){
        $sth->bindValue(':watch', $_GET['watch']);
    }
}

$q_count = "SELECT count(v.id) FROM videos as v"; 
$q_count .= add_query_params();

$sth = $con->prepare($q_count);
bind_query_params($sth);

$sth->execute();
$row = $sth->fetch();
$page_count = (int)($row[0] / LEHEL) + 1;

$q = "SELECT v.id, v.title, DATE_FORMAT(v.time, '%H:%i %d/%m/%y') as time, v.watch,v.plays,v.erroneous,v.bkey FROM videos as v"; 
$q .= add_query_params();

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

if (!isset($_GET["q"]))
    //PDO can't sort
    $q.=" order by v.$order $sort limit :start,:lehel";
else
    $q.=" limit :start,:lehel";

$sth = $con->prepare($q);
//$sth->bindValue(':order', "v.$order");
//$sth->bindValue(':sort', $sort);
$sth->bindParam(':start', $start, PDO::PARAM_INT);
$sth->bindValue(':lehel', (LEHEL+1), PDO::PARAM_INT);

bind_query_params($sth);

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
