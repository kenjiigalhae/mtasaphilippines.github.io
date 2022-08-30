<?php

require_once("../classes/mysql/Database.class.php");
if (isset($_POST['getUserIdFromUsername'])) {
    $db = new Database("MTA");
    $db->connect();
    $q = $db->query_first("SELECT id FROM accounts WHERE username='".$_POST['getUserIdFromUsername']."' ");
    if ($q and $q['id']) {
        echo $q['id'];
    }
}


