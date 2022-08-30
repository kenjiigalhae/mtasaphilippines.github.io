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
    $charid = $_POST['charid'];
    $loadto = $_POST['loadto'];
    if (isset($charid) and $charid) {
        require_once("../classes/mysql/Database.class.php");
        require_once("../classes/mta_sdk.php");
        $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
        $serverOnline = $mtaServer->getResource("usercontrolpanel")->call("isServerOnline");
        if (!$serverOnline or $serverOnline[0] != 1) {
            echo "<p>Gameserver is <font color=#FF0000>OFFLINE.</font></p>"
            . "<p>We're sorry, the game server is currently down for scheduled maintenance. Please check back soon. </p>";
        } else {
            $db = new Database("MTA");
            $db->connect();
            $characterQry = $db->query_first("SELECT * FROM `characters` WHERE `account`='" . $_SESSION['userid'] . "' AND `id`='" . $charid . "'");
            /*
              $vehicleArr = array();
              $vehicleQuery = $db->query("SELECT `id`, `model` FROM `vehicles` WHERE `owner`='" . $characterQry['id'] . "' AND `deleted`='0' ");
              while ($vehicleRow = $db->fetch_array($vehicleQuery)) {
              $vehicleName = $mtaServer->getResource("usercontrolpanel")->call("getVehicleName", $vehicleRow['id']);
              if ($vehicleName and $vehicleName[0]) {
              $vehicleArr[$vehicleRow['id']] = $vehicleName[0];
              //die($vehicleName[0]);
              } else {
              require_once("../functions/base_functions.php");
              $vehicleArr[$vehicleRow['id']] = $vehicleIDtoName[$vehicleRow['model']];
              }
              }
              $interiorArr = array();
              $interiorQuery = $db->query("SELECT `id`, `name` FROM `interiors` WHERE `owner`='" . $characterQry['id'] . "' AND `deleted`='0' ");
              while ($interiorRow = $db->fetch_array($interiorQuery)) {
              $interiorArr[$interiorQuery['id']] = $interiorRow['name'];
              }

             */
            $db->close();
            if ($characterQry["cked"] == 0)
                $status = '<font color="#009900">Alive</font>';
            else
                $status = '<font color="#993300">Deceased</font>';

            if ($characterQry["gender"] == 0)
                $gender = 'Male';
            else
                $gender = 'Female';
            ?>
            <table align=center border=0 width=220 height=120>
                <tr>
                    <td>
                <center>
                    <a onClick="return ajax_load_char_details('<?php echo $characterQry['id']; ?>');" href=""><img src="/images/MTA_skins/Skin_<?php echo $characterQry['skin']; ?>.png"></a>
                </center>
            </td>
            <td width=3>
                <img src="/images/sep2.png">
            </td>
            <td valign=top>
                <a onClick="return ajax_load_char_details('<?php echo $characterQry['id']; ?>');" href=""><b><?php echo str_replace("_", " ", $characterQry["charactername"]); ?></b></a><br>
                Status: <?php echo $status; ?><br>
                Gender: <?php echo $gender; ?><br>
                Age: <?php echo $characterQry["age"]; ?><br>
                Location: <?php echo $characterQry["lastarea"]; ?><br>
            </td>
            </tr>
            </table>
            <input type="hidden" id="<?php echo $loadto . "Selected"; ?>" value="<?php echo $charid; ?>"/>
            <?php
        }
    } else {
        echo "Error fetching character info.";
    }
}

