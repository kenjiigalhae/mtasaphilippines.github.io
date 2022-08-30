<?php

session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
    echo "<center><h3>Session has timed out, please re-login to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    $_SESSION['timeout'] = time();
}
require_once("../functions/functions.php");
if (!isset($_SESSION['userid']) or ! $_SESSION['userid'] and false) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {

    require_once("./functions/functions.php");
    $logTypes = array(
        '1' => array('Chat /h', isPlayerLeadAdmin($perms), false),
        '2' => array('Chat /l', isPlayerLeadAdmin($perms), false),
        '3' => array('Chat /a', isPlayerTrialAdmin($perms), false),
        '4' => array('Admin commands', isPlayerTrialAdmin($perms), false),
        '38' => array('Admin Reports', isPlayerTrialAdmin($perms), true),
        '5' => array('Anticheat warnings', isPlayerTrialAdmin($perms), false),
        '6' => array('Vehicle related actions', isPlayerTrialAdmin($perms), false),
        '37' => array('Interior related things', isPlayerTrialAdmin($perms), true),
        '7' => array('Player /say', isPlayerTrialAdmin($perms), false),
        '8' => array('Player /b', isPlayerTrialAdmin($perms), false),
        '9' => array('Player /r', isPlayerTrialAdmin($perms), false),
        '10' => array('Player /d', isPlayerTrialAdmin($perms), false),
        '11' => array('Player /f', isPlayerTrialAdmin($perms), false),
        '12' => array('Player /me', isPlayerTrialAdmin($perms), false),
        '40' => array('Player /ame', isPlayerTrialAdmin($perms), false),
        '13' => array('Player /district', isPlayerTrialAdmin($perms), false),
        '14' => array('Player /do', isPlayerTrialAdmin($perms), false),
        '15' => array('Player /pm', isPlayerTrialAdmin($perms), false),
        '16' => array('Player /gov**', isPlayerTrialAdmin($perms), true),
        '17' => array('Player /don', isPlayerLeadAdmin($perms), false),
        '18' => array('Player /o', isPlayerTrialAdmin($perms), false),
        '19' => array('Player /s', isPlayerTrialAdmin($perms), false),
        '20' => array('Player /m', isPlayerTrialAdmin($perms), false),
        '21' => array('Player /w', isPlayerTrialAdmin($perms), false),
        '22' => array('Player /c', isPlayerTrialAdmin($perms), false),
        '23' => array('Player /n**', isPlayerTrialAdmin($perms), true),
        '24' => array('Gamemaster chat', isPlayerTrialAdmin($perms), false),
        '25' => array('Cash transfer', isPlayerTrialAdmin($perms), false),
        '27' => array('Connection/Charselect', isPlayerTrialAdmin($perms), false),
        '28' => array('Roadblock & Spikes**', isPlayerTrialAdmin($perms), true),
        '29' => array('Phone logs', isPlayerTrialAdmin($perms), false),
        '30' => array('SMS logs', isPlayerTrialAdmin($perms), false),
        '31' => array('veh/int locking/unlocking', isPlayerTrialAdmin($perms), false),
        '32' => array('UCP Logs', isPlayerLeadAdmin($perms), false),
        '33' => array('Stattransfers', isPlayerTrialAdmin($perms), false),
        '34' => array('Kill logs/Lost items', isPlayerTrialAdmin($perms), false),
        '35' => array('Faction actions', isPlayerTrialAdmin($perms), true),
        '36' => array('Ammunation logs', isPlayerTrialAdmin($perms), true),
        '39' => array('Item Movement', isPlayerTrialAdmin($perms), true),
    );

    $characterCache = array();

    function nameCache($id) {
        global $characterCache, $mySqlMTAConn;
        if (isset($characterCache[$id]))
            return $characterCache[$id];

        $pos = strpos($id, "ch");
        if ($pos === false) {
            $pos = strpos($id, "fa");
            if ($pos === false) {
                $pos = strpos($id, "ve");
                if ($pos === false) {
                    $pos = strpos($id, "ac");
                    if ($pos === false) {
                        $pos = strpos($id, "in");
                        if ($pos === false) {
                            $pos = strpos($id, "ph");
                            if ($pos === false) {
                                $characterCache[$id] = $id . '[unrec]';
                                return $id;
                            } else {
                                $tempid = substr($id, 2);
                                $characterCache[$id] = "phone " . $tempid;
                                return $id;
                            }
                        } else {
                            $tempid = substr($id, 2);
                            $characterCache[$id] = "interior " . $tempid;
                            return $id;
                        }
                    } else {
                        $tempid = substr($id, 2);
                        $awsQry = mysql_query("SELECT `username` FROM `accounts` WHERE `id`='" . $tempid . "'", $mySqlMTAConn);
                        if (mysql_num_rows($awsQry) == 1) {
                            $awsRow = mysql_fetch_assoc($awsQry);
                            $characterCache[$id] = $awsRow['username'];
                            return $awsRow['username'];
                        } else {
                            $characterCache[$id] = $id;
                            return $id;
                        }
                    }
                } else {
                    $tempid = substr($id, 2);
                    $characterCache[$id] = "vehicle " . $tempid;
                    return $characterCache[$id];
                }
            } else {
                $tempid = substr($id, 2);
                $awsQry = mysql_query("SELECT `name` FROM `factions` WHERE `id`='" . $tempid . "'", $mySqlMTAConn);
                if (mysql_num_rows($awsQry) == 1) {
                    $awsRow = mysql_fetch_assoc($awsQry);
                    $characterCache[$id] = '[F]' . $awsRow['name'];
                    return $awsRow['name'];
                } else {
                    $characterCache[$id] = $id;
                    return $id;
                }
            }
        } else {
            $tempid = substr($id, 2);
            $awsQry = mysql_query("SELECT `charactername` FROM `characters` WHERE `id`='" . $tempid . "'", $mySqlMTAConn);
            if (mysql_num_rows($awsQry) == 1) {
                $awsRow = mysql_fetch_assoc($awsQry);
                $characterCache[$id] = $awsRow['charactername'];
                return $awsRow['charactername'];
            } else {
                $characterCache[$id] = $id . '[' . $tempid . ']';
                return $id;
            }
        }
    }

    $selectarr = array();
}