<?php

@session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    @session_destroy();
}
$perms = '';
if (isset($_SESSION['groups'])) {
    $perms = $_SESSION['groups'];
}
require_once 'functions.php';
$logTypes = array(
    '1' => array('Chat /h', isPlayerLeadAdmin($perms), false),
    '2' => array('Chat /l', isPlayerSeniorAdmin($perms), false),
    '3' => array('Chat /a', isPlayerTrialAdmin($perms), false),
    '4' => array('Admin commands', isPlayerAdmin($perms), false),
    '38' => array('Admin Reports', isPlayerSeniorAdmin($perms), true),
    '5' => array('Anticheat warnings', isPlayerSupporter($perms) or isPlayerScripter($perms), false),
    '6' => array('Vehicle related', isPlayerSupporter($perms) or isPlayerVCT($perms) or isPlayerScripter($perms), false),
    '37' => array('Interior related', isPlayerSupporter($perms) or isPlayerMappingTeamLeader($perms) or isPlayerMappingTeamMember($perms) or isPlayerScripter($perms), true),
    '7' => array('Player /say', isPlayerSupporter($perms), false),
    '8' => array('Player /b', isPlayerSupporter($perms), false),
    '9' => array('Player /r', isPlayerTrialAdmin($perms), false),
    '10' => array('Player /d', isPlayerTrialAdmin($perms), false),
    '11' => array('Player /f', isPlayerTrialAdmin($perms), false),
    '12' => array('Player /me', isPlayerSupporter($perms), false),
    '40' => array('Player /ame', isPlayerSupporter($perms), false),
    '13' => array('Player /district', isPlayerSupporter($perms), false),
    '14' => array('Player /do', isPlayerSupporter($perms), false),
    '15' => array('Player /pm', isPlayerSupporter($perms), false),
    '16' => array('Player /gov', isPlayerSupporter($perms), true),
    '17' => array('Player /don', isPlayerLeadAdmin($perms), false),
    '18' => array('Player /o', isPlayerTrialAdmin($perms), false),
    '19' => array('Player /s', isPlayerSupporter($perms), false),
    '20' => array('Player /m', isPlayerSupporter($perms), false),
    '21' => array('Player /w', isPlayerSupporter($perms), false),
    '22' => array('Player /c', isPlayerSupporter($perms), false),
    '23' => array('Player /n', isPlayerSupporter($perms), true),
    '24' => array('Gamemaster chat', isPlayerSupporter($perms), false),
    '25' => array('Cash transfer', isPlayerSupporter($perms) or isPlayerScripter($perms), false),
    '27' => array('Connection/Charselect', isPlayerSupporter($perms) or isPlayerScripter($perms), false),
    '28' => array('Roadblock & Spikes', isPlayerSupporter($perms) or isPlayerScripter($perms), true),
    '29' => array('Phone convo logs', isPlayerSupporter($perms), false),
    '30' => array('SMS logs', isPlayerSupporter($perms), false),
    '31' => array('veh/int lock/unlocks', isPlayerTrialAdmin($perms) or isPlayerVCT($perms), false),
    '32' => array('UCP Logs', isPlayerLeadAdmin($perms), false),
    '33' => array('Stattransfers', isPlayerSupporter($perms), false),
    '34' => array('Kill logs/Lost items', isPlayerSupporter($perms), false),
    '35' => array('Faction actions', isPlayerTrialAdmin($perms), true),
    '36' => array('Ammunation logs', isPlayerTrialAdmin($perms), true),
    '39' => array('Item Movement', isPlayerSupporter($perms), true),
);

function canUserAccessLogs($groups) {
    global $logTypes;
    foreach ($logTypes as $value) {
        if ($value[1]) {
            return true;
        }
    }
    return false;
}
