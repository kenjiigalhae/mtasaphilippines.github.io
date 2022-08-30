<?php
if ($_POST['step'] == "reset_change_password") {
    if (isset($_POST['newpass']) and isset($_POST['userid']) and isset($_POST['token'])) {
        require_once '../classes/mysql/Database.class.php';
        $db = new Database("MTA");
        $db->connect();
        $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $db->escape($_POST['userid']) . "' ");
        if ($user and $user['id'] and is_numeric($user['id'])) {
            $token = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_POST['userid']) . "' AND token='" . $db->escape($_POST['token']) . "' AND action='reset_password' AND date >= NOW() - INTERVAL 10 MINUTE");
            if ($token and $token['userid'] and is_numeric($token['userid'])) {
                $update = array();
                $update['password'] = md5(md5($_POST['newpass']) . $user['salt']);
                if ($db->query("DELETE FROM tokens WHERE id='" . $token['id'] . "'") and $db->query_update("accounts", $update, "id='" . $user['id'] . "'")) {
                    echo "You have successfully changed your MTA account password to '" . $_POST['newpass'] . "'.";
                } else {
                    echo "Internal Error!";
                }
            } else {
                echo "Opps, sorry. We couldn't continue to process the password reset for your account '" . $user['username'] . "'.\n\n "
                . "It looked like this link is expired or invalid.";
            }
        } else {
            echo "Internal Error!";
        }
        $db->close();
    } else {
        echo "Internal Error!";
    }
} else if ($_POST['step'] == "reset_password") {
    if (isset($_POST['clue'])) {
        require_once '../classes/mysql/Database.class.php';
        $db = new Database("MTA");
        $db->connect();
        $clue = $db->escape($_POST['clue']);
        $user = $db->query_first("SELECT * FROM accounts WHERE username='" . $clue . "' OR email='" . $clue . "' ");
        if ($user and $user['id'] and is_numeric($user['id'])) {
            $db->query("DELETE FROM tokens WHERE userid='" . $user['id'] . "' ");
            $token = md5(uniqid(mt_rand(), true));
            $insert = array();
            $insert['userid'] = $user['id'];
            $insert['token'] = $token;
            $insert['action'] = $_POST['step'];
            $db->query_insert("tokens", $insert);
            $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $protocol . '://' . $host;
            $emailContent = "You or someone has requested for a password reset for your MTA account '" . $user['username'] . "' from the OwlGaming UCP. 
                    
Please click the link below within 10 minutes to reset password:
" . $currentUrl . "/lostpw.php?userid=" . $user['id'] . "&token=" . $token . "
    
If you didn't request for this, just simply ignore this email.

Sincerely,
OwlGaming Community
OwlGaming Development Team";
            mail($user['email'], "Account Password Reset at OwlGaming MTA Roleplay", $emailContent);
            echo "An email contains a link to reset your password has been dispatched!\n";
        } else {
            echo "*Username or email address does not exist.";
        }
        $db->close();
    }
} else {
    session_start();
    if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
        session_destroy();
        echo "Session has timed out, please re-login to access this content.";
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
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "You must be logged in to access this content.";
    } else {
        if ($_POST['step'] == "load_acc_settings_gui") {
            require_once '../classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $_SESSION['userid'] . "' ");
            ?>
            <h2>Password:</h2>
            <p>This password is used to log into this UCP and the MTA Server. It's always a good idea to change it up every once in awhile..</p>
            <form onsubmit="ajax_change_password();
                    return false;">
                <table>
                    <tr>
                        <td>
                            <b>Current password:</b>
                        </td>
                        <td>
                            <input id="curpass" type="password" maxlength="100" required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>New password:</b>
                        </td>
                        <td>
                            <input id="newpass1" type="password" maxlength="100" required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Retype password:</b>
                        </td>
                        <td>
                            <input id="newpass2" type="password" maxlength="100" required="true">
                        </td>
                    </tr>
                </table>
                <br>
                <input id="changepass" type="submit" value="Change password">
                <input id="resetpass" type="button" value="Reset password" onclick="ajax_reset_password('<?php echo $_SESSION['username']; ?>');">
            </form>
            <hr>
            <h2>Email Address:</h2>
            <p>This email address is used to recovery your lost password, confirmations, validations and other important actions. It's very important to have a real and working email address. Changing account email address is possible, however it will require confirmations from both of your current and new email addresses.</p>
            <form onsubmit="ajax_change_email('<?php echo $user['email']; ?>');
                    return false;">
                <table>
                    <tr>
                        <td>
                            <b>Email:</b>
                        </td>
                        <td>
                            <input id="email" type="email" maxlength="100" required="true" value="<?php echo $user['email']; ?>">
                        </td>
                    </tr>
                </table>
                <br>
                <input id="changeemail" type="submit" value="Change email">
            </form>
            <hr>
            <h2>Serial Whitelist:</h2>
            <p>Serials are used by MTA server and server administrators to reliably identify a PC that a player is using. They are bound to the software and hardware configuration. Serials are 32 characters long and cointain letters and numbers. </p>
            <p>Serials are the most accurate form of identifying players that MTA has. By default, you're allowed to connect to OwlGaming MTA server from any PC. However, allowing only connections from certain PC(s) by making a whitelist of serials can greatly improve your account security. Hacker won't be able to login to your account from a strange PC even when your password is completely exposed.</p>
            <p>It's always recommended to have at least one serial of your favorite PC added to the serial whitelist.<br>
                You can retrieve serial number from a PC by typing command "serial" in your MTA's console (press F8).</p>
            <p><font color='red'><i><small>*Serial whitelist is only optional for regular players. All MTA staff members are required to add at least one serial number to be able to login MTA server.*</small></i></font></p>
            <?php
            $query = $db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(),last_login_date))/3600 AS 'hourdiff', DATE_FORMAT(last_login_date,'%b %d, %Y at %h:%i %p') AS 'last_login_date',DATE_FORMAT(creation_date,'%b %d, %Y at %h:%i %p') AS 'creation_date'  FROM serial_whitelist WHERE userid='" . $_SESSION['userid'] . "' ORDER BY id");
            $count = 1;
            ?>
            <p><b>Only allow connections from the following whitelist (<?php echo $db->affected_rows(); ?>/<?php echo $user['serial_whitelist_cap']; ?>):</b></p>
            <form onsubmit="ajax_add_new_serial();
                    return false;">
                <table id="logtable" border="1" align=center width="100%">
                    <tr>
                        <td align=center width=20><b>No.</b></td>
                        <td align=center ><b>Serial Number</b></td>
                        <td align=center ><b>Status</b></td>
                        <td align=center><b>Last Connection</b></td>
                        <td align=center><b>Creation Date</b></td>
                        <td align=center><b>Actions</b></td>
                    </tr>  
                    <?php
                    if ($db->affected_rows() > 0) {
                        while ($serial = $db->fetch_array($query)) {
                            $last_login = 'Never';
                            if ($serial['last_login_date'] and $serial['last_login_ip']) {
								$hoursAgo = round($serial['hourdiff']);
								if ($hoursAgo < 1) {
									$hoursAgo = 'Less than an hour ago';
								} else {
									$hoursAgo = 'About '.$hoursAgo.' hour(s) ago';
								}
                                $last_login = $serial['last_login_date'] . " (".$hoursAgo.")<br>From " . $serial['last_login_ip'];
                            }
                            $status = "<font color='red'>Email activation required</font>";
                            if ($serial['status'] == 1) {
                                $status = "<font color='green'>Active</font>";
                            }
                            echo "<tr><td align=center>" . $count . "</td><td align=center>" . $serial['serial'] . "</td><td align=center>" . $status . "</td><td align=center>" . $last_login . "</td><td align=center>" . $serial['creation_date'] . "</td><td width=10% align=center><input id='remove_serial_btn_" . $serial['id'] . "' onclick='ajax_remove_serial(" . $serial['id'] . ");' type='button' value='Remove' style='margin-left: 0px; margin-top: 0px; width: 100%;' /></td></tr>";
                            $count+=1;
                        }
                    }
                    ?>
                    <tr>
                        <td align=center><?php echo $count; ?></td>
                        <td colspan='4' align=center><input id='new_serial' placeholder="Enter new serial number" required maxlength="32" style='width: 95%'></td>
                        <td align=center><input type='submit' value='Add' id='add_new_serial' style='margin-left: 0px; margin-top: 0px; width: 100%;'></td>
                    </tr>  
                </table> 
            </form>
            <?php
            $db->close();
        } else if ($_POST['step'] == "changepassword") {
            require_once '../classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $_SESSION['userid'] . "'");
            if (md5(md5($_POST['curpass']) . $user['salt']) != $user['password']) {
                echo('*Current password is incorrect!*');
            } else {
                $update = array();
                $update['password'] = md5(md5($_POST['newpass']) . $user['salt']);
                $update['activated'] = 0;
                if (!$db->query_update("accounts", $update, "id='" . $_SESSION['userid'] . "'")) {
                    echo $db->oops();
                } else {
                    $db->query("DELETE FROM tokens WHERE userid='" . $user['id'] . "' ");
                    $token = md5(uniqid(mt_rand(), true));
                    $insert = array();
                    $insert['userid'] = $_SESSION['userid'];
                    $insert['token'] = $token;
                    $insert['action'] = $_POST['step'];
                    $db->query_insert("tokens", $insert);
                    $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
                    $host = $_SERVER['HTTP_HOST'];
                    $currentUrl = $protocol . '://' . $host;
                    $emailContent = "You or someone has changed your account password from the OwlGaming UCP. 
                    
If you didn't perform this action, please click this link within 24 hour to deactivate your account for safety:
" . $currentUrl . "/deactivate.php?userid=" . $_SESSION['userid'] . "&token=" . $token . "
You can anytime re-activate your account by using password recovery feature on our UCP later on.

If you're the one who performed this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                    mail($user['email'], "Account Password Changed at OwlGaming MTA Roleplay", $emailContent);
                    echo "You have successfully changed your MTA account password!\n";
                }
            }
            $db->close();
        } else if ($_POST['step'] == "change_email_step_1") {
            if (isset($_POST['curMail']) and isset($_POST['newMail'])) {
                require_once '../classes/mysql/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $_SESSION['userid'] . "' AND email='" . $db->escape($_POST['curMail']) . "'");
                if ($user and $user['id'] and is_numeric($user['id'])) {
                    $mail = $db->query_first("SELECT id FROM accounts WHERE email='" . $db->escape($_POST['newMail']) . "' LIMIT 1");
                    if (!($mail and $mail['id'] and is_numeric($mail['id']))) {
                        $db->query("DELETE FROM tokens WHERE userid='" . $_SESSION['userid'] . "' ");
                        $token = md5(uniqid(mt_rand(), true));
                        $insert = array();
                        $insert['userid'] = $_SESSION['userid'];
                        $insert['token'] = $token;
                        $insert['data'] = $_POST['newMail'];
                        $insert['action'] = $_POST['step'];
                        $db->query_insert("tokens", $insert);
                        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
                        $host = $_SERVER['HTTP_HOST'];
                        $currentUrl = $protocol . '://' . $host;
                        $emailContent = "You or someone has request an account email change from '" . $_POST['curMail'] . "' to '" . $_POST['newMail'] . "' from the OwlGaming UCP. 
                    
Please click this link within 10 minutes to proceed to next step:
" . $currentUrl . "/changemail.php?userid=" . $_SESSION['userid'] . "&token=" . $token . "

If you're the one who performed this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                        mail($user['email'], "Account Email Change Request at OwlGaming MTA Roleplay", $emailContent);
                        echo "An email has been dispatched to your current email address '" . $_POST['curMail'] . "'.\n\n"
                        . "Please check your email's inbox for further instructions.";
                    } else {
                        echo "Opps, sorry. The email address '" . $_POST['newMail'] . "' is already in use.";
                    }
                } else {
                    echo 'Internal Error!';
                }
                $db->close();
            } else {
                echo 'Internal Error!';
            }
        } else if ($_POST['step'] == "add_new_serial") {
            if (isset($_POST['serial'])) {
                require_once '../classes/mysql/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $serial = $db->query_first("SELECT * FROM serial_whitelist WHERE serial='" . $db->escape($_POST['serial']) . "'");
                if ($serial and $serial['id'] and is_numeric($serial['id'])) {
                    if ($serial['userid'] == $_SESSION['userid']) {
                        echo "You have already added this serial number.";
                    } else {
                        echo "Sorry this serial number has been already in use to another account.";
                    }
                } else {
                    $addedSerials = $db->query_first("SELECT COUNT(*) AS 'total' FROM serial_whitelist WHERE userid='" . $_SESSION['userid'] . "'");
                    $user = $db->query_first("SELECT serial_whitelist_cap FROM accounts WHERE id='" . $_SESSION['userid'] . "'");
                    if ($addedSerials['total'] < $user['serial_whitelist_cap']) {
                        $insert = array();
                        $insert['serial'] = $_POST['serial'];
                        $insert['userid'] = $_SESSION['userid'];
                        if ($db->query_insert("serial_whitelist", $insert)) {
                            require_once '../functions/functions_account.php';
                            $token = makeToken($db, $_SESSION['userid'], $_POST['step'], $_POST['serial']);
                            if ($token) {
                                $emailContent = "You or someone has added new serial number '" . $_POST['serial'] . "' to your MTA account '" . $_SESSION['username'] . "' from the OwlGaming UCP. 
                    
Please click this link within 10 minutes to activate the serial:
" . $token[1] . "/serial.php?userid=" . $_SESSION['userid'] . "&token=" . $token[0] . "

If you didn't perform this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                                mail($_SESSION['email'], "Serial Whitelist Activation at OwlGaming MTA Roleplay", $emailContent);
                                echo "ok";
                            } else {
                                echo "Internal Error! Try again later.";
                            }
                        } else {
                            echo "Internal Error! Try again later.";
                        }
                    } else {
                        echo ($user['serial_whitelist_cap']*$user['serial_whitelist_cap']);
                    }
                }
                $db->close();
            } else {
                echo "Opps, sorry. We couldn't add new serial. \n\nConnection to server seemed broken, please try again later.";
            }
        } else if ($_POST['step'] == "remove_serial") {
            if (isset($_POST['serialid'])) {
                require_once '../classes/mysql/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $serial = $db->query_first("SELECT * FROM serial_whitelist WHERE id='" . $db->escape($_POST['serialid']) . "'");
                if ($serial['status'] == 0) {
                    if ($db->query("DELETE FROM serial_whitelist WHERE id='" . $db->escape($_POST['serialid']) . "'")) {
                        echo "ok";
                    } else {
                        echo "Opps, sorry. We couldn't remove that serial. \n\nConnection to server seemed broken, please try again later.";
                    }
                } else {
                    require_once '../functions/functions_account.php';
                    $token = makeToken($db, $_SESSION['userid'], $_POST['step'], $serial['serial']);
                    if ($token) {
                        $emailContent = "You or someone has requested to remove an active serial number '" . $serial['serial'] . "' from your MTA account '" . $_SESSION['username'] . "' from the OwlGaming UCP. 
                    
Please click this link within 10 minutes to deactivate and remove the serial:
" . $token[1] . "/serial.php?userid=" . $_SESSION['userid'] . "&token=" . $token[0] . "

If you didn't perform this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                        mail($_SESSION['email'], "Serial Whitelist Deactivation at OwlGaming MTA Roleplay", $emailContent);
                        echo "ok-email";
                    } else {
                        echo "Opps, sorry. We couldn't remove that serial. \n\nConnection to server seemed broken, please try again later.";
                    }
                }
                $db->close();
            }
        } else if ($_POST['step'] == "increase_serial_cap") {
            require_once '../classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $curCap = $db->query_first("SELECT serial_whitelist_cap AS cap FROM accounts WHERE id=".$_SESSION['userid'])['cap'];
            require_once '../functions/functions_account.php';
            $takeGC = takeGC($db, $_SESSION['userid'], ($curCap*$curCap), "Additional serial whitelist capacity (".($curCap+1).")");
            if (!$takeGC[0]) {
                if ($takeGC[1] ==  "You lack of GC(s) to purchase this item.") {
                    echo "lackGC";
                } else {
                    echo "Opps, sorry. We couldn't process that request. \n\nConnection to server seemed broken, please try again later.";
                }
            } else {
                if ($db->query("UPDATE accounts SET serial_whitelist_cap=serial_whitelist_cap+1 WHERE id='" . $_SESSION['userid'] . "'")) {
                    echo "ok";
                } else {
                    echo "Opps, sorry. We couldn't process that request. \n\nConnection to server seemed broken, please try again later.";
                }
            }
            $db->close();
        }
    }
}


    