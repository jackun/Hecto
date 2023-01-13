<?php
include "config.php";

define("API_VIDEOS", "https://www.googleapis.com/youtube/v3/videos?key=$YTKey&part=snippet&id=");
define("API_PLAYLIST", "https://www.googleapis.com/youtube/v3/playlists?key=$YTKey&channelId=$ChannelId&part=snippet&id=");
define("API_PLAYLIST_ITEMS", "https://www.googleapis.com/youtube/v3/playlistItems?key=$YTKey&part=snippet&maxResults=100&playlistId=");

if (isset($_GET["id"]))
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, API_VIDEOS . $_GET["id"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_AUTOREFERER, true);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    echo curl_exec($c);
    curl_close($c);
}
else if (isset($_GET["plid"]))
{
    if ($_GET["page"] == "undefined")
    {
        header("Content-Type: application/json", true, 404);
        die("{}");
    }

    $page = isset($_GET["page"]) ? $_GET["page"] : "";
    $plid = $_GET["plid"];
    $cached = "./cache/yt_playlist_${plid}-${page}.json";
    if (file_exists($cached))
    {
        header("Content-Type: application/json", true, 200);
        readfile($cached);
        die();
    }

    $url = API_PLAYLIST_ITEMS . $plid;
    if (!empty($page))
        $url .= "&pageToken=${page}";

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_AUTOREFERER, true);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($c);
    curl_close($c);
    
    if (!empty($data)) {
        header("Content-Type: application/json", true, 200);
        echo $data;

        $h = fopen($cached, "w");
        fwrite($h, $data);
        fclose($h);

    } else
        header("Content-Type: application/json", true, 500);
}