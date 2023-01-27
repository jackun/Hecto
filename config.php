<?php
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

define('LIVE_CONFIG', 'config_live.php');

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
$YTKey = "INVALID";

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

$tnt_config = [
    'driver'    => 'mysql',
    'host'      => DB_SERVER,
    'database'  => DB_NAME,
    'username'  => DB_USERNAME,
    'password'  => DB_PASSWORD,
    'storage'   => __DIR__ .'/tnt/',
    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
];

//public $fuzzy_prefix_length  = 2;
//public $fuzzy_max_expansions = 50;
//public $fuzzy_distance       = 2; //represents the Levenshtein distance;
