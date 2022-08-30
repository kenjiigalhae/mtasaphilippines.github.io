<?php
session_start();
$normalPrice = 1; // per week
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

if (!isset($_SESSION['userid'])) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    if (isset($_POST['vehid'])) {
        $vehid = $_POST['vehid'];
        $charid = $_POST['charid'];
        $userID = $_SESSION['userid']; //This make sure noone edit other char
        require_once("../classes/mysql/Database.class.php");
        $db = new Database("MTA");
        $db->connect();
        $veh = $db->query_first("SELECT v.id, vehyear, vehbrand, vehmodel, "
                . "year, c.model, brand, "
                . "(CASE WHEN c.id IS NULL THEN 0 ELSE 1 END) AS `is_unique`, "
                . "(CASE WHEN ((protected_until IS NULL) OR (protected_until > NOW() = 0)) THEN NULL ELSE DATE_FORMAT(protected_until,'%b %d, %Y at %h:%i %p') END) AS `protected`,"
                . "DATEDIFF(NOW(), `lastUsed`) AS `datediff` "
                . "FROM `vehicles` v "
                . "LEFT JOIN vehicles_shop s ON v.vehicle_shop_id=s.id "
                . "LEFT JOIN vehicles_custom c ON v.id=c.id "
                . "WHERE `owner`='" . $charid . "' AND `deleted`='0' AND v.id=$vehid LIMIT 1");
        if (!$veh or ! $veh['id']) {
            echo ('This vehicle is no longer belonged to you.');
        } else {
            if ($_POST['step'] == 1) {
                if ($veh['is_unique'] == 1) {
                    $veh['name'] = $veh['year'] . " " . $veh['brand'] . " " . $veh['model'];
                } else if (!is_null($veh['vehyear'])) {
                    $veh['name'] = $veh['vehyear'] . " " . $veh['vehbrand'] . " " . $veh['vehmodel'];
                }
                ?>
                <link href="../css/login-form.css" type="text/css" rel="stylesheet" />
                <h2>Interior Inactivity Protection for '<?php echo $veh['name']; ?>'</h2>

                <table border="0" cellpadding="20">
                    <tr>
                        <td >
                            <table border="0" align=center class="nicetable" style="padding:10px;">
                                <tr><td colspan=3 align=center><img src="../images/vehicledesign.png"/></td></tr>
                                <tr>
                                    <td colspan="3" align="center"><b><?php echo $veh['name']; ?></b></td>
                                </tr>
                            </table>
                        </td>
                        <td valign='top'>
                            <br>
                            <p>A vehicle goes inactive when your character hasn't been logged in game for 30 days or when no body has started its engine for 14 days while parking outdoor. </p>
                            <p>An inactive vehicle is a waste of resources and thus far the vehicle's ownership will be removed or stripped from you to give other players opportunities to buy and use it more efficiently.</p>
                            <p><?php
                                if (!$_POST['protected_until'])
                                    echo 'This vehicle is currently <b>unprotected</b>. To prevent this to happen, you may want to spend your GC(s) to protect it from the inactive vehicle scanner.';
                                else
                                    echo 'This vehicle is currently protected until <b>' . $_POST['protected_until'] . '</b>. However, you can extend this protection anytime you like.';
                                ?></p>
                            <br><br><br>
                            <form onsubmit="ajax_veh_protect('<?php echo $vehid; ?>', '<?php echo $charid; ?>');
                                    return false;">
                                <table border=0 align="center" class="login-form" cellpadding="2" align='center'>
                                    <tr>
                                        <td><br>
                                            <b><?php
                                                if (!$_POST['protected_until'])
                                                    echo 'Protect this vehicle for';
                                                else
                                                    echo 'Extend this protection for';
                                                ?> <input type='number' id='protection_weeks' min='1' max='99999999' step='1' required value='1' onchange="live_update_int_protection('<?php echo $normalPrice; ?>');"> week(s)
                                                <div id='protection_cost'>Cost: <?php echo $normalPrice; ?> GC(s)</div></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='submit' id='btn_int_protect' value='<?php
                                            if (!$_POST['protected_until'])
                                                echo 'Protect';
                                            else
                                                echo 'Extend';
                                            ?>'>
                                                   <?php if ($_POST['protected_until']) { ?>
                                                <input type='button' id='btn_int_remove_protect' value='Remove Protection' onclick="remove_protection('<?php echo $vehid; ?>', '<?php echo $charid; ?>');">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </form>

                        </td>
                    </tr>
                </table>
                <br>
                <center><a href="" onClick="$('#char_info_mid_top').slideUp(500);
                        return false;">Close Vehicle Protection</a></center>
                <script>
                    function ajax_veh_protect(vehid1, charid1, btnText) {
                        if ($('#btn_int_protect').val() != "Working.." && $('#btn_int_remove_protect').val() != "Working..") {
                            $('#btn_int_protect').val('Working..');
                            //alert(vehid1);
                            //alert(charid1);
                            $.post("../ajax/ajax_ucp_veh_protection.php", {
                                step: 2,
                                vehid: vehid1,
                                charid: charid1,
                                weeks: $('#protection_weeks').val(),
                            }, function (data) {
                                if (data == "error") {
                                    alert("Opps, sorry. We couldn't process your request. Try again later.");
                                    $('#btn_int_protect').val('Protect');
                                } else if (data == "lack") {
                                    if (confirm("Opps, sorry. You lack of GC(s) to purchase this item. \n\nYou can always get more GC(s) by donating to servers, do you want to go to the donation page now?")) {
                                        self.location = "donate.php";
                                    } else {
                                        $('#btn_int_protect').val('Protect');
                                    }
                                } else if (data == "mtadown") {
                                    alert("MTA server is OFFLINE. Please try again later.");
                                    $('#btn_int_protect').val('Protect');
                                } else {
                                    alert(data);
                                    self.location = "ucp.php";
                                }
                            });
                        }
                    }

                    function live_update_int_protection(cost) {
                        var weeks = $('#protection_weeks').val();
                        $('#protection_cost').html("Cost: " + (weeks * cost) + " GC(s)");
                    }

                    function remove_protection(vehid1, charid1) {
                        if ($('#btn_int_protect').val() != "Working.." && $('#btn_int_remove_protect').val() != "Working..") {
                            $('#btn_int_remove_protect').val('Working..');
                            $.post("../ajax/ajax_ucp_veh_protection.php", {
                                step: 3,
                                vehid: vehid1,
                                charid: charid1,
                            }, function (data) {
                                if (data == "error") {
                                    alert("Opps, sorry. We couldn't process your request. Try again later.");
                                    $('#btn_int_remove_protect').val('Remove Protection');
                                } else if (data == "mtadown") {
                                    alert("MTA server is OFFLINE. Please try again later.");
                                    $('#btn_int_remove_protect').val('Remove Protection');
                                } else {
                                    alert(data);
                                    self.location = "ucp.php";
                                }
                            });
                        }
                    }
                </script>
                <?php
            } else {
                require_once("../classes/mta_sdk.php");
                $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
                $mtaServerStats = $mtaServer->getResource("usercontrolpanel")->call("getServerStats");
                if (!isset($mtaServerStats) or ( !$mtaServerStats) or ! isset($mtaServerStats[1]) or ( !$mtaServerStats[1])) {
                    echo "mtadown";
                } else {
                    if ($_POST['step'] == 2) {
                        $weeks = $_POST['weeks'];
                        $weeks = round($weeks);
                        if ($weeks > 0) {
                            require_once '../functions/functions_account.php';
                            $takeGC = takeGC($db, $userID, $weeks * $normalPrice, "Vehicle inactivity protection (" . $veh['name'] . " - " . $weeks . " week(s))");
                            if (!$takeGC[0]) {
                                if ($takeGC[1] == "You lack of GC(s) to purchase this item.") {
                                    echo 'lack';
                                } else {
                                    echo "error";
                                }
                            } else {
                                $update = '';
                                $extended = "extended this vehicle's inactivity protection by";
                                if (is_null($veh['protected'])) {
                                    $update = "NOW() + INTERVAL " . $weeks . " WEEK";
                                    $extended = "protected this vehicle from the inactive scanner for";
                                } else {
                                    $update = "protected_until + INTERVAL " . $weeks . " WEEK";
                                }
                                if ($db->query("UPDATE vehicles SET protected_until=" . $update . " WHERE id=" . $vehid . " AND owner=" . $charid . "")) {
                                    echo "You have successfully " . $extended . " " . $weeks . " week(s)!";
                                    $mtaServer->getResource("vehicle-system")->call("reloadVehicle", $vehid);
                                } else {
                                    echo 'error';
                                }
                            }
                        } else {
                            echo 'error';
                        }
                    } else if ($_POST['step'] == 3) {
                        if ($db->query("UPDATE vehicles SET protected_until=NULL WHERE id=" . $vehid . " AND owner=" . $charid . "")) {
                            echo "You have successfully removed the inactivity protection from this vehicle!";
                            $mtaServer->getResource("vehicle-system")->call("reloadVehicle", $vehid);
                        } else {
                            echo 'error';
                        }
                    }
                }
            }
            $db->close();
        }
    }
}