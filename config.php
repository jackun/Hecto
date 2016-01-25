<?php
define('LIVE_CONFIG', 'config_live.php');
if(file_exists(LIVE_CONFIG)) {
    include_once LIVE_CONFIG;
} else {
    define("DB_SERVER",   "localhost");
    define("DB_USERNAME", "root");
    define("DB_PASSWORD", "toor");
    define("DB_NAME",     "hecto");
    define('PROG_NAME',   "Hecto");
    define('SALT',        "kdjhfkjsfdhsdfhksdksjf");
    define('LEHEL', 100);
    define('DEFAULT_FORMAT', 'default');
    define('AUTOPLAY', 'false');
}

//ytplayer API
$formats = array(
    "small",
    "medium",
    "large",
    "hd720",
    "default"
);

$bgs = array(
    "#9CC8D3",
    "#D1E6EB"
);
$bg_special  = "#7FE6B3";
$bg_special2 = "#FFD042";

#YubiKey ID (first 12 characters in your OTP)
$token_ids = array(
    "cccccccceftc",
);

# signatureKey and apiKey for YubiKey
# ~~~~~~~~~~~~~~~~~~~~~~~~
# you can get one from there :  https://api.yubico.com/get-api-key/
# and yubikey from there :      https://store.yubico.com/
$signatureKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
$apiID = 0000;

# Youtube API key
# ~~~~~~~~~~~~~~~~
$YTKey = "AIzaSyBXvgH3EooBGKkicX2724L9EoD1M6PVPqE";
