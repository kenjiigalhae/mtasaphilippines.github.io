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
function canUserAccessPlayerManager($groups) {
    return isPlayerTrialAdmin($groups) or isPlayerSupporter($groups) or isPlayerScripter($groups) or isPlayerMappingTeamLeader($groups);
}

function canUserManageAdminTeam($groups) {
    return isPlayerSeniorAdmin($groups);
}
