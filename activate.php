<?php
include("header.php");
?>
<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="main-wrapper">
    <div id="lib_top">
        <center><h2>OwlGaming Roleplay MTA Account Activation</h2></center>
        <?php if (!isset($_GET['userid']) or ! isset($_GET['token'])) { ?>
            <form id="register_form" onsubmit="ajax_resend_activation_email();
                        return false;" method="post" action="" onkeypress="resetError();">
                <br>

                <table border=0 align="center" class="login-form" cellpadding="2">
                    <tr>
                        <td style="text-align: center;">
                            <input type="text" id="reg_username" placeholder="Enter Username" maxlength="30" value="<?php echo $_GET['username']; ?>" required/>
                            <div>
                                <br>
                            <b>Prove you're human:</b>
                            <br>
                            <a href="" onclick="ajax_reload_captcha();
                                            return false;"><div id="img_captcha"><img src="captcha/captcha.php" /></div></a>
                            <input type="text" id="reg_captcha" placeholder="Enter the text above" maxlength="5" required style="width: 146px;" />
                            </div>
                            <input type="submit" id="submit_reg" value="Activate" style="margin-top: 20px; "/>
                        </td>
                    <tr>
                        <td >

                            <div id="error_reg" style="padding-top: 10px; color:red;font-style: italic;font-size: 11px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" >
                            <div id="reg_status"></div>
                        </td>
                    </tr>
                </table>
            </form>

            <?php
        } else {
            require_once './classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $account = $db->query_first("SELECT id, username, activated FROM accounts WHERE id='" . $db->escape($_GET['userid']) . "' ");
            if ($account and $account['id'] and is_numeric($account['id'])) {
                $token = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_GET['userid']) . "' AND token='" . $db->escape($_GET['token']) . "' AND date >= NOW() - INTERVAL 30 MINUTE");
                if ($token and $token['userid'] and is_numeric($token['userid'])) {
                    $update = array();
                    $update['activated'] = 1;
                    if ($db->query_update("accounts", $update, "id='" . $token['userid'] . "'") and $db->query("DELETE FROM tokens WHERE id='" . $token['id'] . "'")) {
                        echo "<p>Your account '" . $account['username'] . "' has been sucessfully activated!</p>";
                    } else {
                        echo "<p>Opps, sorry. We couldn't activate your account '" . $account['username'] . "'."
                        . "<p>Please try again later.</p>";
                    }
                } else {
                    echo "<p>Opps, sorry. We couldn't activate your account '" . $account['username'] . "'."
                    . "<p>It looked like the link is expired or invalid. Please click here to generate another activation email.</p>";
                }
            } else {
                echo "<p>Opps, sorry we're unable to process your request.</p>"
                . "<p>The account you're trying to activate does not exist.</p>";
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

        <script type="text/javascript" src="js/ajax_register.js"/></script>

