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
    $data['active'] = $_POST['active'];
    if (!isset($_POST['charid']) or ! isset($data['active'])) {
        if ($data['active'] == 0) {
            echo "Activated";
        } else {
            echo "Deactivated";
        }
    } else {
        $root = realpath($_SERVER["DOCUMENT_ROOT"]);
        require_once("$root/classes/mysql/Database.class.php");
        $db = new Database("MTA");
        $db->connect();
        $db->query_update("characters", $data, "id='" . $_POST['charid'] . "'");
        if ($data['active'] == 1) {
            echo "Activated";
        } else {
            echo "Deactivated";
        }
        $db->close();
    }
}