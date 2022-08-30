<?php
include("header.php");
?>
<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="main-wrapper">
    <div id="lib_top">
        <h2>Account Deactivation</h2>

        <?php
        if (!isset($_GET['userid']) or ! isset($_GET['token'])) {
            echo "Opps, it looked like this link is expired or invalid!";
        } else {
            require_once './classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $account = $db->query_first("SELECT id, username, activated FROM accounts WHERE id='" . $db->escape($_GET['userid']) . "' ");
            if ($account and $account['id'] and is_numeric($account['id'])) {
                $token = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_GET['userid']) . "' AND token='" . $db->escape($_GET['token']) . "' AND action='changepassword' AND date >= NOW() - INTERVAL 24 HOUR");
                if ($token and $token['userid'] and is_numeric($token['userid'])) {
                    $update = array();
                    $update['activated'] = 0;
                    if ($db->query_update("accounts", $update, "id='" . $token['userid'] . "'") and $db->query("DELETE FROM tokens WHERE id='" . $token['id'] . "'")) {
                        echo "Your account '" . $account['username'] . "' has been sucessfully deactivated!";
                    } else {
                        echo "<p>Opps, sorry. We couldn't deactivate your account '" . $account['username'] . "'.</p>"
                        . "<p>Please try again later.</p>";
                    }
                } else {
                    echo "<p>Opps, sorry. We couldn't deactivate your account '" . $account['username'] . "'. </p>"
                    . "<p>It looked like the link is expired or invalid.</p>";
                }
            } else {
                echo "<p>Opps, sorry we're unable to process your request.</p>"
                . "<p>The account you're trying to deactivate does not exist.</p>";
            }
            $db->close();
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
        

