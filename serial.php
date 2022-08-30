<?php
include("header.php");
?>
<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="main-wrapper">
    <div id="lib_top">
        <h2>Serial Activation</h2>
        <?php
        if (isset($_GET['userid']) and isset($_GET['token'])) {
            require_once './classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $db->escape($_GET['userid']) . "' ");
            if ($user and $user['id'] and is_numeric($user['id'])) {
                $tokenCheck = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_GET['userid']) . "' AND token='" . $db->escape($_GET['token']) . "' AND date >= NOW() - INTERVAL 10 MINUTE");
                if ($tokenCheck and $tokenCheck['userid'] and is_numeric($tokenCheck['userid'])) {
                    if ($tokenCheck['action'] == "add_new_serial") {
                        $db->query("DELETE FROM tokens WHERE userid='" . $tokenCheck['userid'] . "' ");
                        $update = array();
                        if ($db->query("UPDATE serial_whitelist SET status=1 WHERE serial='".$tokenCheck['data']."' AND userid='".$tokenCheck['userid']."' ")) {
                            echo "<p>You have successfully activated serial number '".$tokenCheck['data']."' from account '".$user['username']."' serial whitelist!</p> ";
                        } else {
                            echo "<p>Opps, sorry.</p> "
                            . "<p>It looked like this link is expired or invalid.</p>";
                        }
                    } else if ($tokenCheck['action'] == "remove_serial") {
                        if ($db->query("DELETE FROM serial_whitelist WHERE serial='".$tokenCheck['data']."' AND userid='".$tokenCheck['userid']."'") and $db->query("DELETE FROM tokens WHERE userid='" . $tokenCheck['userid'] . "' ")) {
                            echo "<p>You have successfully deactivated and removed serial number '".$tokenCheck['data']."' from account '".$user['username']."' serial whitelist!</p> ";
                        } else {
                            echo "<p>Opps, sorry.</p> "
                            . "<p>It looked like this link is expired or invalid.</p>";
                        }
                    } else {
                        echo "<p>Opps, sorry.</p> "
                        . "<p>It looked like this link is expired or invalid.</p>";
                    }
                } else {
                    echo "<p>Opps, sorry.</p> "
                    . "<p>It looked like this link is expired or invalid.</p>";
                }
            } else {
                echo "<p>Opps, sorry.</p> "
                . "<p>It looked like this link is expired or invalid.</p>";
            }
            $db->close();
        } else {
            echo "<p>Opps, sorry.</p> "
            . "<p>It looked like this link is expired or invalid.</p>";
        }
        ?>
    </div>
    <div id="lib_mid" ></div>
    <div id="lib_bot"></div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        
