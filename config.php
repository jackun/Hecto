<?php
define("DB_SERVER",   "localhost");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "toor");
define("DB_NAME",     "hecto");
define('PROG_NAME',   "Hecto");
define('SALT',        "kdjhfkjsfdhsdfhksdksjf");
define('LEHEL', 200);
define('DEFAULT_FORMAT', 'default');

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

# Youtube dev. key
# ~~~~~~~~~~~~~~~~
# format : "?key=XXXXXXXXXXXXXXX"
# you can get one from there : http://code.google.com/apis/youtube/dashboard/
# leave blank if you dont want to use this
$YTKey = "";
