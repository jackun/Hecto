<?php

$error_code = array( 100=>"Continue", 101=>"Switching Protocols", 200=>"OK", 201=>"Created", 202=>"Accepted", 203=>"Non-Authoritative Information", 204=>"No Content", 205=>"Reset Content", 206=>"Partial Content", 300=>"Multiple Choices", 301=>"Moved Permanently", 302=>"Found", 303=>"See Other", 304=>"Not Modified", 305=>"Use Proxy", 306=>"(Unused)", 307=>"Temporary Redirect", 400=>"Bad Request", 401=>"Unauthorized", 402=>"Payment Required", 403=>"Forbidden", 404=>"Not Found", 405=>"Method Not Allowed", 406=>"Not Acceptable", 407=>"Proxy Authentication Required", 408=>"Request Timeout", 409=>"Conflict", 410=>"Gone", 411=>"Length Required", 412=>"Precondition Failed", 413=>"Request Entity Too Large", 414=>"Request-URI Too Long", 415=>"Unsupported Media Type", 416=>"Requested Range Not Satisfiable", 417=>"Expectation Failed", 500=>"Internal Server Error", 501=>"Not Implemented", 502=>"Bad Gateway", 503=>"Service Unavailable", 504=>"Gateway Timeout", 505=>"HTTP Version Not Supported" ); 

function mcurl($url, $username = "", $password = ""){
    $curl = curl_init();
        @curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        @curl_setopt($curl, CURLOPT_HEADER, 0);
        @curl_setopt($curl, CURLOPT_URL, $url);
        if(substr($url, 0, 5) == "https"){
            @curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  2);
            @curl_setopt($curl, CURLOPT_USERAGENT, $defined_vars['HTTP_USER_AGENT']);
            @curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
    if($username <> "" and $password <> "")
        @curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
    $s = curl_exec($curl);
    $info = curl_getinfo($curl);
        curl_close($curl);
        if($info['http_code'] == 200)
                {
                    return $s;
                }
                    else{
                      return $info['http_code']; // HACK
                }
    return $s;
}


function cache($url, $cache_aeg = 20, $ignore_error = 0, $cache_dir = "./cache/", $ext = "", $username = "", $password = ""){
    global $CACHETUD, $error_code;
    $CACHETUD = true;
    if(!is_dir($cache_dir))
        if(!mkdir($cache_dir))
            die("<b>$cache_dir</b> ei eksisteeri");

    $cache_aeg *= 60;
    $hash = md5($url);
    $fail = $cache_dir.$hash.$ext;
    if(file_exists($fail) and (filemtime($fail) + $cache_aeg) > time())
        return implode("", file($fail));
    else {
        $cont = mcurl($url, $username, $password);
        if (!$ignore_error && gettype($cont) == "integer"){ // HACK
          die('Youtube API: ' . $error_code[$cont]);
        }
        $h = fopen($fail, "w+");
        fwrite($h, $cont);
        fclose($h);
        $CACHETUD = false;
        return $cont;
    }
    return NULL;
}

function cache_image($url){
    $ext = ".".end(explode(".", $url));
    cache($url, 24*60*36509, 1, "./cache/", $ext);
    return md5($url).$ext;
}

function cache_wipe($dir = "./cache/"){

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) if($file[0] != "."){
            unlink($dir . $file);
        }
        closedir($dh);
    }
}
die("WIPED!!1!");
}
?>
