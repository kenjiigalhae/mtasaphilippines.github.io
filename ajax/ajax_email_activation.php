<?php
@session_start();
if (!isset($_SESSION['captcha']) or ! isset($_POST['captcha']) or strtolower($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
    echo "*Captcha is not correct.";
} else {
    require_once '../classes/mysql/Database.class.php';
    $dbf = new Database("MTA");
    $dbf->connect();
    $checkUsername = $dbf->query_first("SELECT id, username, activated, email FROM accounts WHERE username = '" . $dbf->escape($_POST['username']) . "' ");
    if ($checkUsername and $checkUsername['username'] and strlen($checkUsername['username']) > 0) {
        if ($checkUsername['activated'] == 0) {
            $dbf->query("DELETE FROM tokens WHERE userid='".$checkUsername['id']."' ");
            $token = md5(uniqid(mt_rand(), true));
            $data = array();
            $data['userid'] = $checkUsername['id'];
            $data['token'] = $token;
            $dbf->query_insert("tokens", $data);
            $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $protocol . '://' . $host;
            $emailContent = "Your OwlGaming MTA account for '" . $checkUsername['username'] . "' is almost ready for action!

Follow this link to activate your MTA account:
" . $currentUrl . "/activate.php?userid=" . $data['userid'] . "&token=" . $data['token'] . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
            mail($checkUsername['email'], "Account Activation at OwlGaming MTA Roleplay", $emailContent);
            session_destroy();
            $dbf->close();
            echo "ok";
        } else {
            echo "*Username '" . $_POST['username'] . "' is already activated.";
        }
    } else {
        echo "*Username '" . $_POST['username'] . "' does not exist.";
    }
}

    