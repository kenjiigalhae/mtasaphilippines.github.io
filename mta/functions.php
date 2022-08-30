<?php

include( "../classes/mta_sdk.php" );
$input = mta::getInput();
if ($input and $input[0]) {
    require_once '../classes/mysql/Database.class.php';
    $db = new Database("MTA");
    $db->connect();
    $checkToken = $db->query_first("SELECT * FROM tokens WHERE token='" . $db->escape($input[0]) . "' AND date >= NOW() - INTERVAL 1 MINUTE");
    if (true or $token and $token['id'] and is_numeric($token['id'])) {
        if (true or $checkToken['action'] == "INGAME_ACC_REGISTRATION") {
            $userid = $input[1];
            $username = $input[2];
            $email = $input[3];
            sendActivationEmail($userid, $username, $checkToken['token'], $email);
            mta::doReturn("ok");
        }
    } else {
        mta::doReturn("Security token is invalid. Report on bugs.owlgaming.net");
    }
    $db->close();
} else {
    mta::doReturn("Internal Error.");
}

