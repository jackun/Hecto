<?php
if($_SERVER['HTTP_HOST'] == 'localhost'){
    include "functions.php";
    $start = microtime_float();
    if(isset($_GET['flush'])) {
        $con->execute('delete from videos');
    }
    $url = "http://lusikas.com/api.php";
    $json = cache($url, 1110);
    $json = json_decode($json,true);
    if(array_key_exists('error', $json)){
        die('<b>Error:</b> '.$json['error']);
    }
    $i = 0;
    $sql = "INSERT INTO videos (id, title, user, watch,plays,erroneous,bkey) VALUES (NULL,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE plays=?,erroneous=?";
    $json['song'] = array_reverse($json['song']);
    foreach($json['song'] as $song){
        $title = addslashes((string)$song['title']);
        $watch = $song['watch'];
        // echo "Fetch default.jpg for $watch</br>\r\n";
        // yImage($watch);
        $user = $song['user'];
        $plays = (int)$song['plays'];
        $erroneous = (int)$song['erroneous'];
        $bkey = $song['bkey'];
        $con->execute($sql, $title, $user, $watch, $plays, $erroneous, $bkey, $plays, $erroneous);
        $i++;
    }
    print "{$i} videos<br>\n";
    $i=0;
    $con->execute('delete from videos_playlist');
    print "{$i} playlist rows<br>\n";
    print "took ~".round(microtime_float()-$start,5)." here, ~".round($json['stat']['time'],5)." on server";

}
?>
