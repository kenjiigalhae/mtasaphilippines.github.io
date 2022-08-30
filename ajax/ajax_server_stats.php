<?php
// define the path and name of cached file
$cachefile = '../cache/ajax_server_stats.php';
$cachetime = 60; // 1 min
// Check if the cached file is still fresh. If it is, serve it up and exit.
if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
    include($cachefile);
    exit;
}
// if there is either no file OR the file to too old, render the page and capture the HTML.
ob_start();
?>
<html>
    <?php
    require_once("../classes/mysql/config.inc.php");
    require_once("../classes/mta_sdk.php");

    $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
    $mtaServerStats = $mtaServer->getResource("usercontrolpanel")->call("getServerStats");
    if (!isset($mtaServerStats) or ( !$mtaServerStats) or ! isset($mtaServerStats[1]) or ( !$mtaServerStats[1])) {
        echo "<p>Gameserver is <font color=#FF0000>OFFLINE.</font></p>"
        . "<p>We're sorry, the game server is currently down for scheduled maintenance. Please check back soon.</p>";
        exit();
    } else {
        echo "<p>Gameserver is <font color=#33CC33>ONLINE.</font></p>"
        . "<p>Server IP: <a href='mtasa://" . SDK_IP . ":" . $mtaServerStats[1] . "'>" . SDK_IP . ":" . $mtaServerStats[1] . "</a><br>"
        . "Online Roleplayers: " . $mtaServerStats[3] . "/" . $mtaServerStats[4] . "<br>"
        . "Map: " . $mtaServerStats[6] . "<br>"
        . "Gamemode: " . $mtaServerStats[7] . "<br>"
        . "FPS Limit: " . $mtaServerStats[5] . "<br>"
        . "MTA Version: " . $mtaServerStats[2]['tag'] . "<br>"
        . "Script Version: v" . $mtaServerStats[8] . "<br>"
        . "Servertime: " . date('H:i d/m/Y') . "<br></p>";
    }
    ?>
</html>
<?php
// We're done! Save the cached content to a file
$fp = fopen($cachefile, 'w');
fwrite($fp, ob_get_contents());
fclose($fp);
// finally send browser output
ob_end_flush();
?>

