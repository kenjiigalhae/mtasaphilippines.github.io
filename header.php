<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
session_start();

function logout() {
    $_SESSION['username'] = null;
    $_SESSION['userid'] = null;
    $_SESSION['email'] = null;
    $_SESSION['groups'] = null;
    $_SESSION['timeout'] = null;
}

if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
} else {
    //$_SESSION['timeout'] = time();
    $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    @$params = $_SERVER['QUERY_STRING'];
    $currentUrl = $protocol . '://' . $host . $script . '?' . $params;
    //echo $currentUrl;
    $_SESSION['lastpage'] = $currentUrl;
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content="OwlGaming Community - Multi Theft Auto: Roleplay - As a Free to Play game, you will be able to experience core content without paying a single cent!" />
        <meta name="keywords" content="gtasa,sanandreas,grandtheftauto,community,mtarp,rp,roleplay,gta,mta,multitheftauto,vbulletin,forum,bbs,discussion,bulletin board, account,creation," />
        <meta name="author" content="Maxime" />

        <title>OwlGaming Community - Your World. Your Imagination</title>

        <link href="css/style.css" type="text/css" rel="stylesheet" />
        <link href="css/nivo-slider.css" type="text/css" rel="stylesheet" />
        <link href="css/pascal.css" type="text/css" rel="stylesheet" />
        <link rel="shortcut icon" href="/images/icons/favicon.png" type="image/x-icon" />
        <script type="text/javascript" src="js/ajax_login_box.js"></script>
        <!--<script type="text/javascript" src="js/bootbox.min.js"></script>-->
        <script type="text/javascript" src="js/ajax_server_statistics.js"></script>

    </head>

    <body>
        <noscript>
            <div style="color: #D8000C;
                 background-color: #FFBABA;text-align: center;">
                We're sorry but our site <strong>requires</strong> JavaScript to run properly, please enable it.
            </div>    
        </noscript>
        <div id="header">
            <div class="head_wrap">
                <!--<div class="logo"><a href="http://www.zasprexstudios.com" title="Server N"><img src="images/logo.png" border="0" alt="" /></a></div>-->
                <div class="nav_holder">
                    <ul>
                        <div id="main_menu_header"></div>
                    </ul>
                </div>
            </div>
        </div>