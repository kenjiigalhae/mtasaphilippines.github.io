<?php
session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
    echo "<center><h3>Session has timed out, please re-login to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    $_SESSION['timeout'] = time();
    $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    @$params = $_SERVER['QUERY_STRING'];
    $currentUrl = $protocol . '://' . $host . $script . '?' . $params;
    //echo $currentUrl;
    $_SESSION['lastpage'] = $currentUrl;
}
//require_once("../functions/functions.php");
if (!isset($_SESSION['userid']) or ! $_SESSION['userid'] and false) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    //Main point of this page
    $charID = $_POST['charid'];
    $userID = $_SESSION['userid']; //This make sure noone edit other char
    $root = realpath($_SERVER["DOCUMENT_ROOT"]);
    require_once("$root/classes/mysql/Database.class.php");
    $db = new Database("MTA");
    $db->connect();
    if ($_POST['step'] == "load_char_details") {
        $characterData = $db->query_first("SELECT * FROM `characters` 
	WHERE id='" . $charID . "' AND `account`='" . $userID . "'");

// workaround for the current image links
        $add = '';
        $addd = '';
        if (strlen($characterData['skin']) != 3)
            $add = '0';
        if (strlen($characterData['skin']) + 1 < 3)
            $addd = '0';
// end workaround
// faction
        $factionStr = "Unemployed";
        if ($characterData['faction_id'] != -1) {
            $factionRow = $db->query_first("SELECT `name`, `rank_" . $characterData['faction_rank'] . "` FROM `factions` WHERE `id`='" . mysql_real_escape_string($characterData['faction_id']) . "'");
            if (isset($factionRow) and $factionRow['name']) {
                if ($characterData['faction_leader'] == 1) {
                    $factionStr = $factionRow['rank_' . $characterData['faction_rank']] . ' <br>- Organization: <a href="#" onclick="switchToFactionPanel(' . $charID . '); return false;" >' . $factionRow['name'] . '</a>';
                } else {
                    $factionStr = $factionRow['rank_' . $characterData['faction_rank']] . ' <br>- Organization: ' . $factionRow['name'];
                }
            }
        }

// Netto worth
        $nettworth = 0;
        $nettworth = $nettworth + $characterData['money'];
        $nettworth = $nettworth + $characterData['bankmoney'];

        $intWorthRow = $db->query_first("SELECT sum(`cost`) AS 'inttotal' FROM `interiors` WHERE `owner`='" . $characterData['id'] . "' AND `deleted`='0'");
        $nettworth = $nettworth + $intWorthRow['inttotal'];
// End netto worth
// properties
        $propArr = array();
        $mQuery5 = $db->query("SELECT `id`,`name`, "
                . "(CASE WHEN uploaded_interior IS NULL THEN 0 ELSE 1 END) AS `is_custom`, "
                . "(CASE WHEN ((protected_until IS NULL) OR (protected_until > NOW() = 0)) THEN NULL ELSE DATE_FORMAT(protected_until,'%b %d, %Y at %h:%i %p') END) AS `protected`,"
                . "DATEDIFF(NOW(), `lastused`) AS `datediff` "
                . "FROM `interiors` "
                . "WHERE `owner`='" . $characterData['id'] . "' AND `deleted`='0' ");
        while ($introw = $db->fetch_array($mQuery5)) {
            array_push($propArr, array('id' => $introw['id'],
                'name' => $introw['name'],
                'is_custom' => ($introw['is_custom'] == 1),
                'protected' => $introw['protected'],
                'until' => $introw['until'],
                'lastuse' => $introw['datediff']));
        }
        $db->free_result();

// vehicles
        $vehArr = array();
        $mQuery6 = $db->query("SELECT v.id, vehyear, vehbrand, vehmodel, "
                . "year, c.model, brand, "
                . "(CASE WHEN c.id IS NULL THEN 0 ELSE 1 END) AS `is_unique`, "
                . "(CASE WHEN ((protected_until IS NULL) OR (protected_until > NOW() = 0)) THEN NULL ELSE DATE_FORMAT(protected_until,'%b %d, %Y at %h:%i %p') END) AS `protected`,"
                . "DATEDIFF(NOW(), `lastUsed`) AS `datediff` "
                . "FROM `vehicles` v "
                . "LEFT JOIN vehicles_shop s ON v.vehicle_shop_id=s.id "
                . "LEFT JOIN vehicles_custom c ON v.id=c.id "
                . "WHERE `owner`='" . $characterData['id'] . "' AND `deleted`='0' ");
        while ($vehrow = $db->fetch_array($mQuery6)) {
            if ($vehrow['is_unique'] == 1) {
                $vehrow['name'] = $vehrow['year'] . " " . $vehrow['brand'] . " " . $vehrow['model'];
            } else if (!is_null($vehrow['vehyear'])) {
                $vehrow['name'] = $vehrow['vehyear'] . " " . $vehrow['vehbrand'] . " " . $vehrow['vehmodel'];
            }
            array_push($vehArr, array('id' => $vehrow['id'],
                'name' => $vehrow['name'],
                'is_unique' => ($vehrow['is_unique'] == 1),
                'protected' => $vehrow['protected'],
                'until' => $vehrow['until'],
                'lastuse' => $vehrow['datediff']));
        }

        $db->free_result();

        $processedCharName = str_replace("_", " ", $characterData["charactername"]);
        if ($characterData["cked"] == 0)
            $status = 'Alive';
        else
            $status = 'Deceased';

        if ($characterData["gender"] == 0)
            $gender = 'Male';
        else
            $gender = 'Female';

        if ($characterData["active"] == 1)
            $active = 'Activated';
        else
            $active = 'Deactivated';

        $heightmin = 50;
        $heightmax = 250;
        $agemin = 2;
        $agemax = 250;
        $weightmin = 2;
        $weightmax = 300;
        ?>
        <h2>Character information details</h2>
        <table width="100%" border="0" class="nicetable">
            <tr>
                <td valign="top" colspan=2>

                    <table border=0 colspan=2 width="100%" cellspacing="10">
                        <tr>
                            <td rowspan=2 width="160">
                                <img src="<?php echo "/images/skinlist_detailed_og/" . $characterData['skin']; ?>.png">
                            </td>
                            <td width=3 rowspan=2>
                                <img src="/images/sep2.png">
                            </td>
                        </tr>
                        <tr>
                            <td valign=top >
                                <div id="char_details">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td valign="center" align="center" style="white-space:nowrap; padding 0; margin:0;" colspan="2">
                                                <div class=""> 
                                                    <h1><font size="40"><?php echo str_replace("_", " ", $processedCharName); ?></font></h1>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b>Basic Information:</b><br>
                                                - Status: <div id="char_alive" style="display: inline"><?php echo $status; ?></div> & <a href="#" onClick="return ajax_activate_character(<?php echo $characterData["id"]; ?>);" ><div id="char_active" style="display: inline"><?php echo $active; ?></div></a><br>
                                                - Gender: <?php echo $gender; ?><br>
                                                - Age: <?php echo $characterData["age"]; ?>  ((<?php echo number_format($characterData["hoursplayed"]); ?> hours played))<br>
                                                - Height: <?php echo $characterData["height"]; ?> cm(s)<br>
                                                - Weight: <?php echo $characterData["weight"]; ?> kg(s)<br>
                                                - Fingerprint: <br><?php echo $characterData["fingerprint"]; ?><br>
                                                <b>Profession & Finance</b><br>
                                                - Position: <?php echo $factionStr; ?>	<br>	
                                                - Bank account: $<?php echo number_format($characterData["bankmoney"]); ?>	<br>
                                                - Total assets worth: $<?php echo number_format($nettworth); ?>	<br>	
                                                <b>Others:</b><br>
                                                - Last seen: <?php
                                                $date = date_create($characterData["lastlogin"]);
                                                echo date_format($date, 'g:iA \o\n jS F Y');
                                                ?><br>around <?php echo $characterData["lastarea"]; ?><br><br><br>	

                                            </td>
                                            <td valign=top>
                                                <b>Properties owned (<?php echo count($propArr); ?>/<?php echo $characterData['maxinteriors']; ?>):</b><br><?php
                                                if (count($propArr) == 0) {
                                                    ?>    - None<br><?php
                                                } else {
                                                    //onClick="ajax_ucp_int_uploader('.$propertyID.', '.$characterData["id"].'); return false;"
                                                    echo '<ul style ="margin: 0px;">';
                                                    foreach ($propArr as $int) {
                                                        echo '<li> ' . $int['name'] . ''
                                                        . '<ul style="font-size:11px;"><i>'
                                                        . '<li>Last used: ' . formatLastuse($int['lastuse']) . ' </li>'
                                                        . '<li>' . inactiveProtection($int['id'], $characterData["id"], $int['protected'], $int['until']) . '</li>'
                                                        . '<li>' . formatCustomInt($int['id'], $characterData["id"], $int['is_custom']) . '</li>'
                                                        . '</i></ul>';
                                                    }
                                                    echo '</ul>';
                                                }
                                                ?> 
                                                <b>Vehicles owned (<?php echo count($vehArr); ?>/<?php echo $characterData['maxvehicles']; ?>):</b><br><?php
                                                if (count($vehArr) == 0) {
                                                    ?>    - None<br><?php
                                                } else {

                                                    //onClick="ajax_ucp_int_uploader('.$propertyID.', '.$characterData["id"].'); return false;"
                                                    echo '<ul style ="margin: 0px;">';
                                                    foreach ($vehArr as $veh) {
                                                        echo '<li> ' . $veh['name'] . ''
                                                        . '<ul style="font-size:11px;"><i>'
                                                        . '<li>Last used: ' . formatLastuse($veh['lastuse']) . ' </li>'
                                                        . '<li>' . inactiveProtectionVeh($veh['id'], $characterData["id"], $veh['protected'], $veh['until']) . '</li>'
                                                        . '</i></ul>';
                                                    }
                                                    echo '</ul>';
                                                    ?>
                                                    <?php
                                                }
                                                ?>


                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="faction_details">
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <input type="hidden" name="charid" value="<?php echo($charID); ?>"/>
        <?php
    } else if ($_POST['step'] == "load_faction_details") {
        $faction = $db->query_first("SELECT f.id, f.name, f.max_interiors FROM factions f LEFT JOIN characters c ON f.id=c.faction_id LEFT JOIN accounts a ON c.account=a.id WHERE c.id=$charID AND c.faction_leader=1 AND a.id=$userID");
        if ($faction and ! is_null($faction['id'])) {
            echo '<table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td valign="center" align="center" style="white-space:nowrap; padding 0; margin:0;" colspan="2">
                                                <div class=""> 
                                                    <h1><font size="40">' . $faction['name'] . '</font></h1>
                                                    <a href="#" onClick="$(\'#faction_details\').slideUp(500);$(\'#char_details\').slideDown(500);return false;">Go Back</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>';
            // properties
            $propArr = array();
            $mQuery5 = $db->query("SELECT `id`,`name`, "
                    . "(CASE WHEN uploaded_interior IS NULL THEN 0 ELSE 1 END) AS `is_custom` "
                    . "FROM `interiors` "
                    . "WHERE owner=-1 AND faction=" . $faction['id'] . " AND `deleted`='0' ");
            while ($introw = $db->fetch_array($mQuery5))
                array_push($propArr, array('id' => $introw['id'],
                    'name' => $introw['name'],
                    'is_custom' => ($introw['is_custom'] == 1)
                        )
                );
            $db->free_result();
            echo '<b>Faction Properties (' . count($propArr) . '/' . $faction['max_interiors'] . '):</b><br>';
            echo '<ul style ="margin: 0px;">';
            foreach ($propArr as $int) {
                echo '<li> ' . $int['name'] . ''
                . '<ul style="font-size:11px;"><i>'
                . '<li>' . formatCustomInt($int['id'], $charID, $int['is_custom']) . '</li>'
                . '</i></ul>';
            }
            echo '</ul>';


            echo '</td>
                                            <td>
                                            </td>
                                        </tr>
                               </table>';
        }
    }
    $db->close();
}

function formatCustomInt($id, $charid, $iscustom) {
    if ($iscustom) {
        return '<a href="" onClick="ajax_load_int_uploader(' . $id . ', ' . $charid . '); return false;">Change custom interior</a>';
    } else {
        return '<a href="" onClick="ajax_load_int_uploader(' . $id . ', ' . $charid . '); return false;">Upload custom interior</a>';
    }
}

function inactiveProtection($id, $charid, $protected) {
    if ($_SESSION['userid'] == 1 or true) {
        if (is_null($protected)) {
            return '<a href="" onClick="ajax_load_int_protection(' . $id . ', ' . $charid . '); return false;"); return false;">Protect from inactivity</a>';
        } else {
            return '<a href="" onClick="ajax_load_int_protection(' . $id . ', ' . $charid . ',\'' . $protected . '\'); return false;">Protected until ' . $protected . '</a>';
        }
    } else {
        if (is_null($protected)) {
            return '<a href="" onClick="alert(\'This feature is under construction. Try again later.\'); return false;">Protect from inactivity</a>';
        } else {
            return '<a href="" onClick="alert(\'This feature is under construction. Try again later.\'); return false;">Protected until ' . $protected . '</a>';
        }
    }
}

function formatLastuse($days) {
    $text = '';
    if ($days == 0) {
        $text = "Today.";
    } else if ($days == 1) {
        $text = "Yesterday.";
    } else if ($days > 1) {
        $text = $days . " days ago.";
    }
    return $text;
}

function inactiveProtectionVeh($id, $charid, $protected) {
    if ($_SESSION['userid'] == 1 or true) {
        if (is_null($protected)) {
            return '<a href="" onClick="ajax_load_int_protection_veh(' . $id . ', ' . $charid . '); return false;"); return false;">Protect from inactivity</a>';
        } else {
            return '<a href="" onClick="ajax_load_int_protection_veh(' . $id . ', ' . $charid . ',\'' . $protected . '\'); return false;">Protected until ' . $protected . '</a>';
        }
    } else {
        if (is_null($protected)) {
            return '<a href="" onClick="alert(\'This feature is under construction. Try again later.\'); return false;">Protect from inactivity</a>';
        } else {
            return '<a href="" onClick="alert(\'This feature is under construction. Try again later.\'); return false;">Protected until ' . $protected . '</a>';
        }
    }
}
?> 

