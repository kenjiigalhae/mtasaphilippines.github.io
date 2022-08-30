<?php
//MTA database server
define('DB_SERVER', "eu3.optiklink.com");
define('DB_USER', "u18996_MAkhs7ak5X");
define('DB_PASS', "AmPhh7cH+Qz^N6r=sSmc+AVd");
define('DB_DATABASE', "s18996_kenjii");

//Forums database server
define('DB_FORUMS_SERVER', "50.62.177.152");
define('DB_FORUMS_USER', "root");
define('DB_FORUMS_PASS', "AmPhh7cH+Qz^N6r=sSmc+AVd");
define('DB_FORUMS_DATABASE', "s18996_kenjii");

//Logs database server
define('DB_LOGS_SERVER', "localhost");
define('DB_LOGS_USER', "root");
define('DB_LOGS_PASS', "");
define('DB_LOGS_DATABASE', "s18996_kenjii");

//MTA SDK
define('SDK_IP', "91.121.137.31");
define('SDK_PORT', "22005");
define('SDK_USER', "website");
define('SDK_PASSWORD', "HackNuaDi");

//FORUMS API
define('API_FORUMS_CREATE_ACCOUNT_URL', 'http://forums.owlgaming.net/remote_create_account.php');
define('API_FORUMS_CREATE_ACCOUNT_ACCESSKEY', 'jduwoghvbdpwjvheywdngjehfksnfhf');
define('API_DEFAULT_FORUM_USERGROUP', 2);

//Donation Stuff
define('USE_SANDBOX', false);
if (USE_SANDBOX) {
    define('SELLER_EMAIL', "ducchuseller2@live.com");
    define('PAYPAL_URL', "https://www.sandbox.paypal.com/cgi-bin/webscr");
} else {
    define('SELLER_EMAIL', "donate@owlgaming.net");
    define('PAYPAL_URL', "https://www.paypal.com/cgi-bin/webscr");
}
define('WEBMASTER_EMAIL', "ducchu@live.com");
define('DONATION_SERVER_MAIL', "donate@owlgaming.net");
