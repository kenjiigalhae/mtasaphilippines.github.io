<?php

session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
} else {
    $_SESSION['timeout'] = time();
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "Session has timed out.";
    } else {
        $data['height'] = $_POST['heightval'];
        $data['weight'] = $_POST['weighteval'];
        if (!isset($_POST['charid']) or ! isset($data['height']) or ! isset($data['weight'])) {
            echo "Updating has failed.";
        } else {
            $root = realpath($_SERVER["DOCUMENT_ROOT"]);
            require_once("$root/classes/mysql/Database.class.php");
            $db = new Database("MTA");
            $db->connect();
            $db->query_update("characters", $data, "id='" . $_POST['charid'] . "'");
            echo "Updated successfully!";
            $db->close();
        }
    }
}