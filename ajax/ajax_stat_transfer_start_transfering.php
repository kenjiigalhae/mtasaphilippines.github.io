<?php

$transferCost = 15;
session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
} else {
    $_SESSION['timeout'] = time();
}

$completed = '<center><h2>&nbsp;&nbsp;Transferring Completed!</h2><img src="../images/icons/completed.jpg"/><input type="hidden" id="stat_transfer_result" value="completed"/></center>';
$not_completed = '<center><h2>&nbsp;&nbsp;Transferring Failed!</h2><img src="../images/icons/not_completed.jpg"/><input type="hidden" id="stat_transfer_result" value="failed"/></center>';
$bullet_ok = '<br><img src="../images/icons/ok.png" width="9"/>&nbsp;';
$bullet_not_ok = '<br><img src="../images/icons/not_ok.png" width="9"/>&nbsp;';
$response = '';

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    $response = $not_completed . $bullet_not_ok . "Session has timed out.";
    echo $response;
    exit();
} else {
    @$fromCharId = $_POST['fromChar'];
    @$toCharId = $_POST['toChar'];
    @$money = $_POST['tmoney'];
    @$bankmoney = $_POST['tbankmoney'];
    @$vehicles = $_POST['vehicles'];
    @$interiors = $_POST['interiors'];

    //Start validating everything
    if (!isset($fromCharId) or ! isset($toCharId)) {
        $response = $not_completed . $bullet_not_ok . "Connection to server was broken.</p>";
        echo $response;
        exit();
    }

    require_once("../classes/mysql/Database.class.php");
    $db = new Database("MTA");
    $db->connect();
    $account = $db->query_first("SELECT id, credits FROM accounts WHERE id='" . $_SESSION['userid'] . "' ");

    //Check if player has enough GC.
    if (!isset($account['credits']) or $account['credits'] < $transferCost) {
        $response = $not_completed . $bullet_not_ok . "You lack of GC(s) to perform this action. Please <a href='../donate.php'>get more GC(s)</a> then try again.</p>";
        echo $response;
        $db->close();
        exit();
    }

    //Check if those characters are still belonged to this account.
    $fromChar = $db->query_first("SELECT id, charactername, money, bankmoney FROM characters WHERE id='" . $fromCharId . "' AND account='" . $_SESSION['userid'] . "' ");
    $toChar = $db->query_first("SELECT id, charactername FROM characters WHERE id='" . $toCharId . "' AND account='" . $_SESSION['userid'] . "' ");
    if (!isset($fromChar) or ! isset($toChar) or ! is_numeric($fromChar['id']) or ! is_numeric($toChar['id'])) {
        $response = $not_completed . $bullet_not_ok . "Character '" . $fromChar['charactername'] . "' or '" . $toChar['charactername'] . "' is no longer belonged to you.</p>";
        echo $response;
        $db->close();
        exit();
    }

    $somethingTransferred = false;
    $collectedMessages = '';

    $dbLogs = new Database("LOGS");
    $dbLogs->connect(true);
    //Start transfer money on hand
    if (!is_numeric($money) or $money < 1 or $fromChar['money'] < $money) {
        $collectedMessages = $collectedMessages . $bullet_not_ok . "Money on hand ($" . number_format($money) . ") couldn't be transferred. Reason: Invalid input money.";
    } else {
        $successSoFar = true;
        //Take money from source char
        if ($successSoFar) {
            $data1['money'] = "INCREMENT(" . -$money . ")";
            $successSoFar = $db->query_update("characters", $data1, "id='" . $fromCharId . "' AND account='" . $_SESSION['userid'] . "' ");
        }
        //Give money to dest char
        if ($successSoFar) {
            $data1['money'] = "INCREMENT(" . $money . ")";
            $successSoFar = $db->query_update("characters", $data1, "id='" . $toCharId . "' AND account='" . $_SESSION['userid'] . "' ");
        } else { //If can't give money to dest char then refund taken money to source char
            $data1['money'] = "INCREMENT(" . $money . ")";
            $db->query_update("characters", $data1, "id='" . $fromCharId . "' AND account='" . $_SESSION['userid'] . "' ");
        }
        if ($successSoFar) {
            $collectedMessages = $collectedMessages . $bullet_ok . "Money on hand ($" . number_format($money) . ") was successfully transferred from " . $fromChar['charactername'] . " to " . $toChar['charactername'] . ".";
            $somethingTransferred = true;
            $data4['time'] = 'NOW()';
            $data4['action'] = '1';
            $data4['source'] = 'ac' . $_SESSION['userid'];
            $data4['affected'] = 'ac' . $_SESSION['userid'] . ';ch' . $fromCharId . ';ch' . $toCharId;
            $data4['content'] = 'Stat transferred money on hand of $' . number_format($money) . ' from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.';
            $dbLogs->query_insert("owl_logs", $data4);
        } else {
            $collectedMessages = $collectedMessages . $bullet_not_ok . "Money on hand ($" . number_format($money) . ") was not transferred. Reason: Internal Error.";
        }
    }

    //Start transfer bankmoney
    if (!is_numeric($bankmoney) or $bankmoney < 1 or $fromChar['bankmoney'] < $bankmoney) {
        $collectedMessages = $collectedMessages . $bullet_not_ok . "Money in bank ($" . number_format($money) . ") couldn't be transferred. Reason: Invalid input bank money.";
    } else {
        $successSoFar = true;
        //Take money from source char
        if ($successSoFar) {
            $data2['bankmoney'] = "INCREMENT(" . -$bankmoney . ")";
            $successSoFar = $db->query_update("characters", $data2, "id='" . $fromCharId . "' AND account='" . $_SESSION['userid'] . "' ");
        }
        //Give money to dest char
        if ($successSoFar) {
            $data2['bankmoney'] = "INCREMENT(" . $bankmoney . ")";
            $successSoFar = $db->query_update("characters", $data2, "id='" . $toCharId . "' AND account='" . $_SESSION['userid'] . "' ");
        } else { //If can't give money to dest char then refund taken money to source char
            $data2['bankmoney'] = "INCREMENT(" . $bankmoney . ")";
            $db->query_update("characters", $data2, "id='" . $fromCharId . "' AND account='" . $_SESSION['userid'] . "' ");
        }
        if ($successSoFar) {
            $collectedMessages = $collectedMessages . $bullet_ok . "Money in bank ($" . number_format($bankmoney) . ") was successfully transferred from " . $fromChar['charactername'] . " to " . $toChar['charactername'] . ".";
            $somethingTransferred = true;
            $data5['time'] = 'NOW()';
            $data5['action'] = '1';
            $data5['source'] = 'ac' . $_SESSION['userid'];
            $data5['affected'] = 'ac' . $_SESSION['userid'] . ';ch' . $fromCharId . ';ch' . $toCharId;
            $data5['content'] = 'Stat transferred money in bank of $' . number_format($bankmoney) . ' from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.';
            $dbLogs->query_insert("owl_logs", $data5);
        } else {
            $collectedMessages = $collectedMessages . $bullet_not_ok . "Money in bank ($" . number_format($bankmoney) . ") was not transferred. Reason: Internal Error.";
        }
    }

    //Prepare the mta sdk instance
    $mtaServer = null;
    if ((isset($vehicles) && is_array($vehicles) && count($vehicles) > 0) || (isset($interiors) && is_array($interiors) && count($interiors) > 0)) {
        require_once("../classes/mta_sdk.php");
        $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
    }

    //Start transfer vehicles
    if (isset($vehicles) && is_array($vehicles) && count($vehicles) > 0) {
        foreach ($vehicles as $vehId) {
            //First check if this vehicle is still belonged to these characters
            $fromCar = $db->query_first("SELECT id FROM vehicles WHERE id='" . $vehId . "' AND owner='" . $fromCharId . "' ");
            $successSoFar = true;
            if (!isset($fromCar) or ! is_numeric($fromCar['id'])) {
                $successSoFar = false;
            }
            if ($successSoFar) {
                $data3['owner'] = $toCharId;
                $successSoFar = $db->query_update("vehicles", $data3, "id='" . $vehId . "' AND owner='" . $fromCharId . "'");
            }
            if ($successSoFar) {
                $somethingTransferred = true;
                $collectedMessages = $collectedMessages . $bullet_ok . "Vehicle ID #" . $vehId . " was successfully transferred from " . $fromChar['charactername'] . " to " . $toChar['charactername'] . ".";
                $collectedMessages = $collectedMessages . $bullet_ok . "Vehicle ID #" . $vehId . ": All keys were successfully deleted from everywhere in game. ";
                $collectedMessages = $collectedMessages . $bullet_ok . "Vehicle ID #" . $vehId . ": One key was successfully created and was given to " . $toChar['charactername'] . ".";

                $data7['time'] = 'NOW()';
                $data7['action'] = '1';
                $data7['source'] = 'ac' . $_SESSION['userid'];
                $data7['affected'] = 'ac' . $_SESSION['userid'] . ';ch' . $fromCharId . ';ch' . $toCharId . ';veh' . $vehId;
                $data7['content'] = 'Stat transferred vehicle (ID ' . $vehId . ') from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.';
                $dbLogs->query_insert("owl_logs", $data7);

                $mtaServer->getResource("usercontrolpanel")->call("deleteItem", 3, $vehId);
                $data6['type'] = '1';
                $data6['owner'] = $toCharId;
                $data6['itemID'] = '3';
                $data6['itemValue'] = $vehId;
                $db->query_insert("items", $data6);
            } else {
                $collectedMessages = $collectedMessages . $bullet_not_ok . "Vehicle ID #" . $vehId . " transfering has failed!";
            }
        }
    }

    //Start transfer interiors
    if (isset($interiors) && is_array($interiors) && count($interiors) > 0) {
        foreach ($interiors as $intId) {
            //First check if this interior is still belonged to these characters
            $fromInt = $db->query_first("SELECT id, type FROM interiors WHERE id='" . $intId . "' AND owner='" . $fromCharId . "' ");
            $successSoFar = true;
            if (!isset($fromInt) or ! is_numeric($fromInt['id'])) {
                $successSoFar = false;
            }
            if ($successSoFar) {
                $data8['owner'] = $toCharId;
                $successSoFar = $db->query_update("interiors", $data8, "id='" . $intId . "' AND owner='" . $fromCharId . "'");
            }
            if ($successSoFar) {
                $somethingTransferred = true;
                $collectedMessages = $collectedMessages . $bullet_ok . "Interior ID #" . $intId . " was successfully transferred from " . $fromChar['charactername'] . " to " . $toChar['charactername'] . ".";
                $collectedMessages = $collectedMessages . $bullet_ok . "Interior ID #" . $intId . ": All keys were successfully deleted from everywhere in game. ";
                $collectedMessages = $collectedMessages . $bullet_ok . "Interior ID #" . $intId . ": One key was successfully created and was given to " . $toChar['charactername'] . ".";

                $data9['time'] = 'NOW()';
                $data9['action'] = '1';
                $data9['source'] = 'ac' . $_SESSION['userid'];
                $data9['affected'] = 'ac' . $_SESSION['userid'] . ';ch' . $fromCharId . ';ch' . $toCharId . ';in' . $intId;
                $data9['content'] = 'Stat transferred interior (ID ' . $intId . ') from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.';
                $dbLogs->query_insert("owl_logs", $data9);
                
                $itemID = 4;
                if ($fromInt['type'] == 1) {
                    $itemID = 5;
                }
                $mtaServer->getResource("usercontrolpanel")->call("deleteItem", $itemID, $intId);
                $data10['type'] = '1';
                $data10['owner'] = $toCharId;
                $data10['itemID'] = $itemID;
                $data10['itemValue'] = $intId;
                $db->query_insert("items", $data10);
            } else {
                $collectedMessages = $collectedMessages . $bullet_not_ok . "Interior ID #" . $intId . " transfering has failed!";
            }
        }
    }

    if ($somethingTransferred) {
        //Now it's time to take their GCs
        $data11['credits'] = "INCREMENT(" . -$transferCost . ")";
        if (!$db->query_update("accounts", $data11, "id='" . $_SESSION['userid'] . "' ")) {
            $mtaServer->getResource("usercontrolpanel")->call("sendMessageToAdmins", "Player " . $username . " has successfully performed a stats transfer on UCP however GC(s) couldn't be deducted, please notify Maxime ASAP!");
        } else {
            //Make a purchase history
            $data13['name'] = 'Assets transferred from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.';
            $data13['cost'] = -$transferCost;
            $data13['date'] = 'NOW()';
            $data13['account'] = $_SESSION['userid'];
            $db->query_insert("don_purchases", $data13);
        }
        $response = $completed . $collectedMessages;
        //Add an admin history
        require_once '../functions/functions_account.php';
        @makeAdminHistory($db, $_SESSION['userid'], 'Assets transferred from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.', 6);
    } else {
        $response = $not_completed . $collectedMessages . $bullet_not_ok . "Nothing was sucessfully transferred.";
    }
    echo $response;
    require_once '../functions/functions_tickets.php';
    @notify($db, $_SESSION['userid'], $_SESSION['email'], 'Assets transfer status from ' . $fromChar['charactername'] . ' to ' . $toChar['charactername'] . '.', strip_tags($response));
    @$mtaServer->getResource("usercontrolpanel")->call("statTransfer", $_SESSION['username'], $fromCharId, $toCharId, $_SESSION['userid']);
    $db->close();
    $dbLogs->close();
}
