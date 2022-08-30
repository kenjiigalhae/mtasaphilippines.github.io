<?php
//MTA UCP Functions - My MAXIME
define("LEADADMIN", 15);
define("SENIORADMIN", 64);
define("ADMIN", 17);
define("TRIALADMIN", 18);
define("SCRIPTER", 32);
define("SUPPORTER", 30);
define("VCT_LEADER", 39);
define("VCT_MEMBER", 43);
define("MAPPING_TEAM_LEADER", 44);
define("MAPPING_TEAM_MEMBER", 28);
define("DONOR", 26);

function isPlayer($rank, $groups) {
    $array = explode(',', $groups);
    return array_search($rank, $array);
}

function getUserTitle($groups) {
    if (isPlayer(LEADADMIN, $groups)) {
        return 'Lead Admin ';
    } else if (isPlayer(SENIORADMIN, $groups)) {
        return 'Senior Admin ';
    } else if (isPlayer(ADMIN, $groups)) {
        return 'Admin ';
    } else if (isPlayer(TRIALADMIN, $groups)) {
        return 'Trial Admin ';
    } else if (isPlayer(SCRIPTER, $groups)) {
        return 'Scripter ';
    } else if (isPlayer(SUPPORTER, $groups)) {
        return 'Supporter ';
    } else if (isPlayer(VCT_LEADER, $groups)) {
        return 'VCT Team Leader ';
    } else if (isPlayer(VCT_MEMBER, $groups)) {
        return 'VCT Team Member ';
    } else if (isPlayer(MAPPING_TEAM_LEADER, $groups)) {
        return 'Mapping Team Leader ';
    } else if (isPlayer(MAPPING_TEAM_MEMBER, $groups)) {
        return 'Mapping Team Member ';
    } else if (isPlayer(DONOR, $groups)) {
        return 'Donor ';
    } else {
        return '';
    }
}

function getAdminTitleFromIndex($index) {
    if ($index == 1) {
        return "Trial Admin";
    } else if ($index == 2) {
        return "Admin";
    } else if ($index == 3) {
        return "Senior Admin";
    } else if ($index == 4) {
        return "Lead Admin";
    }
    return "";
}

function getScipterTitleFromIndex($index) {
    if ($index == 1) {
        return "Script Tester";
    } else if ($index == 2) {
        return "Trial Scripter";
    } else if ($index == 3) {
        return "Scripter";
    }
    return "";
}

function getSupporterTitleFromIndex($index) {
    if ($index == 1) {
        return "Supporter";
    }
    return "";
}

function getVctTitleFromIndex($index) {
    if ($index == 1) {
        return "VCT Member";
    } else if ($index == 2) {
        return "VCT Leader";
    }
    return "";
}

function getMapperTitleFromIndex($index) {
    if ($index == 1) {
        return "Mapping Team Member";
    } else if ($index == 2) {
        return "Mapping Team Leader";
    }
    return "";
}

function getAllStaffTitlesFromIndexes($admin, $supporter, $vct, $scripter, $mapper) {
    $text = '';
    if ($admin > 0) {
        $text .= getAdminTitleFromIndex($admin);
    }
    if ($supporter > 0) {
        if (strlen($text) > 0) {
            $text .= ', ';
        }
        $text .= getSupporterTitleFromIndex($supporter);
    }
    if ($vct > 0) {
        if (strlen($text) > 0) {
            $text .= ', ';
        }
        $text .= getVctTitleFromIndex($vct);
    }
    if ($scripter > 0) {
        if (strlen($text) > 0) {
            $text .= ', ';
        }
        $text .= getScipterTitleFromIndex($scripter);
    }
    if ($mapper > 0) {
        if (strlen($text) > 0) {
            $text .= ', ';
        }
        $text .= getMapperTitleFromIndex($mapper);
    }
    return $text;
}

function isPlayerLeadAdmin($text) {
    return isPlayer(LEADADMIN, $text);
}

function isPlayerSeniorAdmin($text) {
    return isPlayerLeadAdmin($text) or isPlayer(SENIORADMIN, $text);
}

function isPlayerAdmin($text) {
    return isPlayerSeniorAdmin($text) or isPlayer(ADMIN, $text);
}

function isPlayerTrialAdmin($text) {
    return isPlayerAdmin($text) or isPlayer(TRIALADMIN, $text);
}

function isPlayerSupporter($text) {
    return isPlayerTrialAdmin($text) or isPlayer(SUPPORTER, $text);
}

function isPlayerVCT($text) {
    return isPlayer(VCT_LEADER, $text) or isPlayer(VCT_MEMBER, $text);
}

function isPlayerScripter($text) {
    return isPlayer(SCRIPTER, $text);
}

function isPlayerMappingTeamLeader($text) {
    return isPlayer(MAPPING_TEAM_LEADER, $text);
}

function isPlayerMappingTeamMember($text) {
    return isPlayerMappingTeamLeader($text) or isPlayer(MAPPING_TEAM_MEMBER, $text);
}

function getBanningInfo($connection, $userid, $appstate, $activated, $groups) {
    $preparedText = "";

    if ($activated == 1) {
        $preparedText .= " <font color=green>Activated</font> |";
    } else {
        if (!$groups) {
            $preparedText .= " <font color=red>Not Activated</font> |";
        } else {
            $preparedText .= " <font color=red>Not Activated</font> <a href='/activate.php' >Activate Now</a> |";
        }
    }

    if ($appstate < 3) {
        $preparedText .= " <font color=red>Pending Application</font> |";
    }
    $conn = null;
    if ($connection) {
        $conn = $connection;
    } else {
        $conn = new Database("MTA");
        $conn->connect();
    }
    $ban = $conn->query_first("SELECT bans.id AS id, bans.admin AS admin, accounts.username AS username, bans.reason AS reason FROM bans LEFT JOIN accounts ON bans.admin=accounts.id WHERE account='" . $userid . "' LIMIT 1");

    if ($ban and $ban['id'] and is_numeric($ban['id'])) {
        $preparedText.= " <font color=red>Banned <img src=images/banned.png width=11 height=11></img> by Admin " . $ban['username'] . " (Reason: " . $ban['reason'] . ")</font> |";
    }

    if (!$connection) {
        $conn->close();
    }

    return substr($preparedText, 0, -1);
}

function convertToHoursMins($time, $format = '%d:%d') {
    settype($time, 'integer');
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

function getJailingInfo($jailState, $jailingPermanent, $jailingPeriod, $jailedByAdminName, $JailingReason) {
    $preparedText = "";
    if ($jailState == 1) {
        if ($jailingPermanent == 1)
            $preparedText = "<font color=red><br><img src=images/banned.png width=11 height=11></img> Jailed by Admin " . $jailedByAdminName . " (Jailtime: Forever, Reason: " . $JailingReason . ")</font>";
        else
            $preparedText = "<font color=red><br><img src=images/banned.png width=11 height=11></img> Jailed by Admin " . $jailedByAdminName . " (For: " . convertToHoursMins($jailingPeriod, '%02d hours %02d minutes') . ", Reason: " . $JailingReason . ")</font>";
    }
    return $preparedText;
}

function getLastLoginInfo($lastLogin1, $lastIP, $mtaserial) {
    if (($lastLogin1 == "") or ( $mtaserial == ""))
        return "Never";
    return $lastLogin1 . " </td><td>From IP: " . $lastIP . "</td>";
}

function getAccountInfo($userID, $groups) {
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        $root = realpath($_SERVER["DOCUMENT_ROOT"]);
        require_once("$root/classes/mysql/Database.class.php");
        $dbMTA = new Database("MTA");
        $dbMTA->connect();
        //MTA query
        $mQuery1 = "SELECT *, DATE_FORMAT(`a`.`lastlogin`,'%b %d, %Y at %h:%i %p') AS `lastLogin1`, DATE_FORMAT(`a`.`registerdate`,'%b %d, %Y at %h:%i %p') AS `registerdate1` , 
	 (SELECT `b`.`username` FROM `accounts` `b` WHERE `b`.`id`=`a`.`adminjail_by`) AS `adminjail_by1` 
	FROM `accounts` `a` WHERE `a`.`id`='" . $userID . "' LIMIT 1";
        $userRow = $dbMTA->query_first($mQuery1);
        $hoursplayed = $dbMTA->query_first("SELECT SUM(hoursplayed) AS hoursplayed FROM characters WHERE account='" . $userID . "' ")['hoursplayed'];

        // Fetch general shit
        $username2 = $userRow['username'];
        $emailAddress = $userRow['email'];
        $registerdate = $userRow['registerdate1'];
        $gameCoin = $userRow['credits'];

        //Fetch jailing shit
        $jailState = $userRow['adminjail'];
        $jailingPeriod = $userRow['adminjail_time'];
        $jailedByAdminName = $userRow['adminjail_by1'];
        $jailingReason = $userRow['adminjail_reason'];
        $jailingPermanent = $userRow['adminjail_permanent'];
        //Fetch some other shit
        $lastLogin1 = $userRow['lastLogin1'];
        $lastIP = $userRow['ip'];
        $mtaSerial = $userRow['mtaserial'];
        $cpaCredits = $userRow['cpa_earned'];
        //$dbMTA->free_result();


        if (!$groups) {
            echo '<h2>' . $username2 . '\'s account details...</h2>';
        } else {
            ?>
            <h2>Howdy, <?php echo getUserTitle($groups) ?><?php echo $username2 ?>! Here are your account details...</h2>
        <?php } ?>
        <table border=0 width="100%" align="left">
            <tr>
                <td>
                    <b>Account Status:</b>
                </td>
                <td colspan="3">
                    <?php
                    echo getBanningInfo($dbMTA, $userRow['id'], $userRow['appstate'], $userRow['activated'], $groups);
                    echo getJailingInfo($jailState, $jailingPermanent, $jailingPeriod, $jailedByAdminName, $jailingReason)
                    ?>
                </td>

            </tr>
            <tr>
                <td>
                    <b>Email Address:</b>
                </td>
                <td> 
                    <?php echo $emailAddress; ?>
                </td>
                <td>
                    <b>Hoursplayed: </b><?php echo number_format($hoursplayed); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Register Date:</b>
                </td>
                <td> 
                    <?php
                    echo $registerdate;
                    ?>
                </td>
                <td>
                    <?php if ($groups) { ?><a href="#" onclick="ajax_load_acc_settings();"><img src=images/icons/settings.png width=10 height=10><b> Account Settings</b></a> <?php } ?>
                </td>
            </tr>
            <tr>
                <td valign=top>
                    <b>Last Login:</b>
                </td>

                <td> 
                    <?php
                    echo getLastLoginInfo($lastLogin1, $lastIP, $mtaSerial);
                    ?>
                </td>

            </tr>
            <tr>
                <td>
                    <b>Game Coins:</b>
                </td>
                <td>
                    <b><img src=images/gamecoin.png width=10 height=10></img><font color="#8F6B00"> <?php echo number_format($gameCoin) ?></b></font>&nbsp;&nbsp;&nbsp; <?php if ($groups) { ?> <a href="/donate.php"><b>Get more coins!</b></a><?php } ?>
                </td>
                <td>
                    <b><img src=images/credits.png width=10 height=10></img><font color="#8F6B00"> <?php echo round($cpaCredits, 2); ?></b></font>&nbsp;&nbsp;&nbsp;<?php if ($groups) { ?><a href="" onClick="alert('This feature is currently not available.');
                                        return false;" ><b>Earn more credits!</b></a><?php } ?>
                </td>

                <td>
                    <?php if ($groups) { ?><a href="" onClick="return ajax_stat_transfer();"><img src="images/transfer.png" width=20 height=13/><b> Transfer assets between characters.</b></a><?php } ?>

                </td>
            </tr>
        </table>
        <?php
        $dbMTA->close();
    }
}

function getCharactersFromAccount($userID, $hideThisChar) {
    $username = $_SESSION['username'];
    $root = realpath($_SERVER["DOCUMENT_ROOT"]);
    require_once("$root/classes/mysql/Database.class.php");
    $dbMTA = new Database("MTA");
    $dbMTA->connect();
    $preparedText = "SELECT `active`, `age`, `lastarea`, `id`, `charactername`,`cked`,`skin`, `gender` FROM `characters` WHERE `account`='" . $userID . "'";

    if (isset($hideThisChar) and $hideThisChar)
        $preparedText = $preparedText . " AND `id`!='" . $hideThisChar . "'";

    $charQuery = $dbMTA->query($preparedText);
    $isThisAccountEmpty = true;
    $activeChars = array();
    $deactiveChars = array();

    while ($result = $dbMTA->fetch_array($charQuery)) {
        if ($result["active"] == '1')
            array_push($activeChars, $result);
        else
            array_push($deactiveChars, $result);
    }
    $dbMTA->close();
    //Active Chars
    ?><h2>Active Characters (<?php echo count($activeChars); ?>)</h2><?php if (count($activeChars) == 0) { ?>
        <p><b> None</b></p><?php
    } else {
        ?>
        <p><a href="" onClick="return ajax_stat_transfer();"><img src="images/transfer.png" width=20 height=13></img> <b>Transfer assets between characters.</b></a></p>

        <table align="center" border="0" width=100% class=nicetable>
            <tr><?php
                $rowpos = 1;
                //while ($row = mysql_fetch_assoc($charQuery))
                foreach ($activeChars as &$row) {
                    if ($rowpos == 5) {
                        echo "								</tr>\r\n";
                        echo "								<tr>\r\n";

                        $rowpos = 1;
                    }

                    if ($row["cked"] == 0)
                        $status = '<font color="#009900">Alive</font>';
                    else
                        $status = '<font color="#993300">Deceased</font>';

                    if ($row["gender"] == 0)
                        $gender = 'Male';
                    else
                        $gender = 'Female';

                    echo "
							<td>
								<table border=0>
									<tr>
										<td>
											<a onClick=\"return ajax_load_char_details('" . $row["id"] . "');\" href=\"\"><img src=\"images/MTA_skins/Skin_" . $row['skin'] . ".png\"></a>
										</td>
										<td width=3>
											<img src=\"images/sep2.png\">
										</td>
										<td valign=top>
											<a onClick=\"return ajax_load_char_details('" . $row["id"] . "');\" href=\"\"><b>" . str_replace("_", " ", $row["charactername"]) . "</b></a></br>
											Status: " . $status . "<br>
											Gender: " . $gender . "<br>
											Age: " . $row["age"] . "<br>
											Location: <br>" . substr($row["lastarea"], 0, 20) . "..<br>
											
										</td>
									</tr>
								</table>
							</td>\r\n";

                    $rowpos++;
                }
                ?></tr>
        </table> <?php }
            ?>

    <!--//De-activated chars-->
    <h2>De-activated characters (<?php echo count($deactiveChars); ?>)</h2><?php if (count($deactiveChars) == 0) { ?>
        <p><b> None</b></p><?php
    } else {
        ?>
        <p><b>These don't show up in character selection screen in game.</b></p>

        <table align="center" border="0" width=100% class=nicetable>
            <tr><?php
                $rowpos = 1;
                //while ($row = mysql_fetch_assoc($charQuery))
                foreach ($deactiveChars as &$row) {
                    if ($rowpos == 7) {
                        echo "								</tr>\r\n";
                        echo "								<tr>\r\n";

                        $rowpos = 1;
                    }

                    // workaround for the current image links
                    $add = '';
                    $addd = '';
                    if (strlen($row['skin']) != 3)
                        $add = '0';
                    if (strlen($row['skin']) + 1 < 3)
                        $addd = '0';
                    // end workaround

                    if ($row["cked"] == 0)
                        $status = '<font color="#009900">Alive</font>';
                    else
                        $status = '<font color="#993300">Dead</font>';
                    echo "									<td align=\"center\"><a onClick=\"return ajax_load_char_details('" . $row["id"] . "');\" href=\"\"><img src=\"images/chars/" . $add . $addd . $row['skin'] . ".png\"><BR /><b>" . str_replace("_", " ", $row["charactername"]) . "</b></a><BR />" . $status . "</td>\r\n";
                    $rowpos++;
                }
                ?>
            </TR>
        </TABLE> <?php
    }
}

function getAdminHistory($connection = false, $userID) {
    $db = $connection;
    if (!$db) {
        $root = realpath($_SERVER["DOCUMENT_ROOT"]);
        require_once $root . '/classes/mysql/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }

    //MTA query
    $actions = array(0 => "jail", 1 => "kick", 2 => "ban", 3 => "app", 4 => "warn", 5 => "autoban", 6 => "other", 99 => "force-app");
    $q = $db->query("SELECT DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, action, h.admin AS adminid, reason, duration, a.username as adminname, b.username AS username"
            . ", c.charactername AS user_char, h.id as recordid "
            . "FROM adminhistory h "
            . "LEFT JOIN accounts a ON a.id = h.admin "
            . "LEFT JOIN accounts b ON b.id = h.user "
            . "LEFT JOIN characters c ON h.user_char=c.id "
            . "WHERE user = " . $db->escape($userID) . " ORDER BY h.id DESC");
    $results = array();
    while ($his = $db->fetch_array($q)) {
        $his['action'] = $actions[$his['action']];
        if (!$his['action']) {
            $his['action'] = "other";
        }
        if (is_null($his['adminname']))
            $his['adminname'] = "SYSTEM";
        array_push($results, $his);
    }
    if (!$connection)
        $db->close();
    return $results;
}

/*

  if (count($userHistory) > 0) {

  ?>
  <table id="newspaper-a" border="0" align=center width=100%>
  <tr>
  <th>ID</th>
  <th>Action</th>
  <th>Reason</th>
  <th>Time</th>
  <th>Admin</th>
  <th>Date</th>
  </tr>
  <?php foreach ($userHistory as &$row) { ?>
  <tr>
  <td>
  <?php echo $row["recordid"]; ?>
  </td>
  <td>
  <?php echo $actions[$row["action"]]; ?>
  </td>
  <td>
  <?php echo $row["reason"]; ?>
  </td>
  <td>
  <?php echo $row["duration"]; ?>
  </td>
  <td>
  <?php echo $row["admin"]; ?>
  </td>
  <td>
  <?php echo $row["date"]; ?>
  </td>
  </tr>
  <?php } ?>
  </table>
  <?php
  } else {
  ?>
  <b> You have a clean admin history.</b><?php
  }
  }
 */

function validate_email($email, $MySQLConnForums) {
    // checks proper syntax
    if (preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email)) {
        // gets domain name
        list($username, $domain) = split('@', $email);
        // checks for if MX records in the DNS
        if (!checkdnsrr($domain, 'MX')) {
            return false;
        }
        $mQuery1 = mysql_query("SELECT `email` FROM `user` WHERE `email`='" . $email . "' LIMIT 1", $MySQLConnForums);
        if (mysql_num_rows($mQuery1) == 1)
            return false;
        else
            return true;
    }
    return false;
}

function validate_username_reg($input, $MySQLConnForums, $pattern = '/[^A-Za-z0-9]/') {
    if (preg_match($pattern, $input))
        return false;

    if (strlen($input) < 3)
        return false;

    $mQuery1 = mysql_query("SELECT `username` FROM `user` WHERE `username`='" . $input . "' LIMIT 1", $MySQLConnForums);
    if (mysql_num_rows($mQuery1) == 1)
        return false;

    return true;
}

function getCarShopNameFromID($ID) {
    if ($ID == 1)
        return "Grotti's Cars";
    elseif ($ID == 2)
        return "Jefferson Car Shop";
    elseif ($ID == 3)
        return "Idlewood Bike Shop";
    elseif ($ID == 4)
        return "Sandro's Cars";
    elseif ($ID == 5)
        return "Industrial Vehicle Shop";
    elseif ($ID == 6)
        return "Santa Maria Boat Shop";
    else
        return "Not available";
}
