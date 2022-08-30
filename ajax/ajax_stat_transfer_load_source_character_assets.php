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
    if (isset($charid) and $charid) {
        require_once("../classes/mysql/Database.class.php");
        require_once("../classes/mta_sdk.php");
        $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
        $mtaServerStats = $mtaServer->getResource("usercontrolpanel")->call("getServerStats");
        if (!isset($mtaServerStats) or ( !$mtaServerStats) or ! isset($mtaServerStats[1]) or ( !$mtaServerStats[1])) {
            echo "<p>Gameserver is <font color=#FF0000>OFFLINE.</font></p>"
            . "<p>We're sorry, the game server is currently down for scheduled maintenance. Please check back soon. </p>";
        } else {
            $db = new Database("MTA");
            $db->connect();
            $characterQry = $db->query_first("SELECT * FROM `characters` WHERE `account`='" . $_SESSION['userid'] . "' AND `id`='" . $charid . "'");

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
                $interiorArr[$interiorRow['id']] = $interiorRow['name'];
            }

            $db->close();
            ?>
            <form name="stat_transfer_assets_form" >
                <table id=logtable border=1 width=100%>
                    <tr>
                        <td>
                    <center><b>Money</b></center>
                    </td>
                    <td>
                    <center><b>Vehicles</b></center>
                    </td>
                    <td>
                    <center><b>Interiors</b></center>
                    </td>
                    <tr>
                        <td valign="top" width="20%">
                            Bank money: <BR />
                            <input type="number" name="bankmoney" value="<?php echo $characterQry['bankmoney']; ?>" required/><BR /><BR />
                            Money on hand: <BR />
                            <input type="number" name="money" value="<?php echo $characterQry['money']; ?>" /><BR required/>
                        </td>
                        <td valign="top">

                            <?php
                            foreach ($vehicleArr as $vehicleID => $vehicleModel) {
                                if (true)//($mtaServer->getResource("carshop-system")->call("isForSale", $vehicleModel))
                                    echo "											<input type=\"checkbox\" name=\"vehicle[]\" value=\"" . $vehicleID . "\" CHECKED> <b>" . $vehicleArr[$vehicleID] . "</b> (VIN: " . $vehicleID . ")<BR/>";
                                else
                                    echo "										<i> <b>" . $vehicleArr[$vehicleID] . "</b> (VIN: " . $vehicleID . ")</i><BR />";
                            }
                            ?>
                        </td>
                        <td valign="top">

                            <?php
                            foreach ($interiorArr as $interiorID => $interiorName) {
                                echo "											<input type=\"checkbox\" name=\"interior[]\" value=\"" . $interiorID . "\" CHECKED> <b>" . $interiorName . "</b> (ID " . $interiorID . ")<BR />";
                            }
                            ?>										
                        </td>
                    </tr>

                </table>
            </form>
            <?php
        }
    } else {
        echo "Error fetching character info.";
    }
}

