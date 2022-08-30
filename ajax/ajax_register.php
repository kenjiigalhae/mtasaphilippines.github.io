<?php

@session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    @session_destroy();
}

if (isset($_SESSION['username']) and $_SESSION['username']) {
    echo "*You're currently logged in as '" . $_SESSION['username'] . "', please logout and try again.";
} else {
    if (!isset($_SESSION['captcha']) or ! isset($_POST['captcha']) or strtolower($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
        echo "*Captcha is not correct.";
    } else {
        require_once '../classes/mysql/Database.class.php';
        $dbf = new Database("MTA");
        $dbf->connect();
        $checkUsername = $dbf->query_first("SELECT username FROM accounts WHERE username = '" . $dbf->escape($_POST['username']) . "' ");
        if ($checkUsername and $checkUsername['username'] and strlen($checkUsername['username']) > 0) {
            echo "*Username '" . $checkUsername['username'] . "' is already taken.";
        } else {
            $checkEmail = $dbf->query_first("SELECT email FROM accounts WHERE email='" . $_POST['email'] . "' ");
            if ($checkEmail and $checkEmail['email'] and strlen($checkEmail['email']) > 0) {
                echo "*Email address '" . $checkEmail['email'] . "' is already in use.";
            } else {
                $referrerId = 0;
                if (strlen($_POST['referrer']) > 0) {
                    $checkReferrer = $dbf->query_first("SELECT id FROM accounts WHERE username = '" . $dbf->escape($_POST['referrer']) . "' ");
                    if (!$checkReferrer or ! $checkReferrer['id'] or ! is_numeric($checkReferrer['id'])) {
                        $dbf->close();
                        die("*Referrer account name '" . $_POST['referrer'] . "' does not exist.");
                    }
                    $referrerId = $checkReferrer['id'];
                }
                $account = array();
                $account['username'] = $_POST['username'];
                $rand = substr(md5(uniqid(mt_rand(), true)), 0, -2);
                $account['salt'] = $rand;
                $hashedPassword = md5(md5($_POST['password']) . $rand);
                $account['password'] = $hashedPassword;
                $account['email'] = $_POST['email'];
                $account['referrer'] = $referrerId;
                $account['activated'] = 0;
                $inserted_id = $dbf->query_insert("accounts", $account);
                if (is_numeric($inserted_id)) {
                    session_destroy();
                    require_once '../functions/functions_account.php';
                    $makeToken = makeToken($dbf, $inserted_id, "INGAME_ACC_REGISTRATION");
                    sendActivationEmail($inserted_id, $account['username'], $makeToken[0], $account['email']);
                    echo $inserted_id;
                } else {
                    echo "Opps, sorry we couldn't create a new account for you at the moment. Please try again later.";
                }
                $dbf->close();
            }
        }
    }
}
    