<?php

session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
} else {
    $_SESSION['timeout'] = time();
}
if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    echo "Session has timed out.";
} else {
    $transferCost = 15;
    require_once("../classes/mysql/Database.class.php");
    $db = new Database("MTA");
    $db->connect();
    $q = $db->query_first("SELECT credits FROM accounts WHERE id='".$_SESSION['userid']."' ");
    echo "You are currently having <b>".$q['credits']." GC(s)</b> so that you will be able to do <b>".floor($q['credits'] / $transferCost)." transfer(s)</b>";
}
