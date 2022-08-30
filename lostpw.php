<?php
include("header.php");
?>
<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="main-wrapper">
    <div id="lib_top">
        <h2>Password Recovery:</h2>
        <?php
        if (isset($_GET['userid']) and isset($_GET['token'])) {
            require_once './classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $db->escape($_GET['userid']) . "' ");
            if ($user and $user['id'] and is_numeric($user['id'])) {
                $token = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_GET['userid']) . "' AND token='" . $db->escape($_GET['token']) . "' AND action='reset_password' AND date >= NOW() - INTERVAL 10 MINUTE");
                if ($token and $token['userid'] and is_numeric($token['userid'])) {
                    ?>
                    <form onsubmit="ajax_change_password('<?php echo $user['id']; ?>', '<?php echo $_GET['token']; ?>');
                                        return false;">
                        <table>
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
                    </form>
                    <script>
                        function ajax_change_password(userid1, token1) {
                            if ($('#changepass').val() == "Change password") {
                                var errorText = '';
                                if (/\s/.test($('#newpass1').val())) {
                                    errorText += '*New password must not contain any white spaces.\n';
                                }
                                if ($('#newpass1').val().length < 6) {
                                    errorText += '*New password must contain at least 6 characters.\n';
                                }

                                if ($('#newpass1').val() != $('#newpass2').val()) {
                                    errorText += '*Re-type password does not match.\n';
                                }
                                if (errorText.length > 0) {
                                    alert(errorText);
                                    return false;
                                }
                                $('#changepass').val("Working..");
                                $.post("../ajax/ajax_load_acc_settings.php", {
                                    step: 'reset_change_password',
                                    newpass: $('#newpass1').val(),
                                    userid: userid1,
                                    token: token1,
                                }, function (data) {
                                    alert(data);
                                    $('#changepass').val("Change password");
                                });
                            }
                        }
                    </script>
                    <?php
                } else {
                    echo "<p>Opps, sorry. We couldn't continue to process the password reset for your account '" . $user['username'] . "'.</p> "
                    . "<p>It looked like this link is expired or invalid.</p>";
                }
            } else {
                echo "<p>Opps, sorry. Account does not exist.</p>";
            }
            $db->close();
        } else {
            ?>

            <form onsubmit="ajax_reset_password();
                        return false;">
                <table>
                    <tr>
                        <td>
                            <b>Username or Email:</b>
                        </td>
                        <td>
                            <input id="usernameEmail" maxlength="100" required="true">
                        </td>
                    </tr>
                </table>
                <br>
                <input id="resetpass" type="submit" value="Reset password">
            </form>

        <?php } ?>
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
        <script>
            function ajax_reset_password() {
                if ($('#resetpass').val() == "Reset password") {
                    $('#resetpass').val("Working..");
                    $.post("../ajax/ajax_load_acc_settings.php", {
                        step: 'reset_password',
                        clue: $('#usernameEmail').val(),
                    }, function (data) {
                        alert(data);
                        $('#resetpass').val("Reset password");
                    });
                }
            }
        </script>
