<?php

$thankYouEmail = "Dear my friend,

I want to express my appreciation for your generosity in support of OwlGaming Community. Your personal commitment was incredibly helpful and allowed us to reach our goal. Your assistance means so much to me but even more to the Community. Thank you from all of us!

GameCoins should have been added into the donated account instantly.
If you wish to have access to Premium Sections on forums or having any other donation issue/question, please visit http://owlgaming.net/support.php, submit a ticket under 'Donation issue or question'.

Sincerely,
OwlGaming Community
OwlGaming Development Team";
// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__) . '/ipn_errors.log');

// intantiate the IPN listener
include('./classes/ipnlistener.php');
require_once('./classes/mysql/Database.class.php');
require_once('./functions/base_functions.php');
require_once('./functions/functions.php');
$listener = new IpnListener();

// tell the IPN listener to use the PayPal test sandbox
$listener->use_sandbox = USE_SANDBOX;

// try to process the IPN POST
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    error_log($e->getMessage());
    mail(WEBMASTER_EMAIL, 'OwlGaming Donation Failed - Failed to process the IPN POST', "The transaction is not completed" . $listener->getTextReport(), "From: " . DONATION_SERVER_MAIL);
    echo "LOL, sorry..maybe you can make it next time ;)<br>";
    echo "-Maxime";
    exit();
}

// TODO: Handle IPN Response here
if (isset($verified) and $verified) {
    // TODO: Implement additional fraud checks and MySQL storage
    //mail('ducchu@live.com', 'Valid IPN', $listener->getTextReport());
    $errmsg = '';   // stores errors from fraud checks
    // 1. Make sure the payment status is "Completed" 
    if ($_POST['payment_status'] != 'Completed') {
        // simply ignore any IPN that is not completed
        mail(WEBMASTER_EMAIL, 'OwlGaming Donation Failed $' . $_POST['mc_gross'], "The transaction is not completed" . $listener->getTextReport(), "From: " . DONATION_SERVER_MAIL);
        exit(0);
    }

    // 2. Make sure seller email matches your primary account email.
    if (($_POST['receiver_email'] != SELLER_EMAIL) and ( $_POST['receiver_email'] != WEBMASTER_EMAIL)) {
        $errmsg .= "'receiver_email' does not match: ";
        $errmsg .= $_POST['receiver_email'] . "\n";
    }

    // 3. Make sure the amount(s) paid match
    /* if ($_POST['mc_gross'] != '9.99') {
      $errmsg .= "'mc_gross' does not match: ";
      $errmsg .= $_POST['mc_gross']."\n";
      } */

    // 4. Make sure the currency code matches
    if ($_POST['mc_currency'] != 'USD') {
        $errmsg .= "'mc_currency' does not match: ";
        $errmsg .= $_POST['mc_currency'] . "\n";
    }

    // 5. Ensure the transaction is not a duplicate.
    $db = new Database("MTA");
    $db->connect(true);

    $txn_id = $db->escape($_POST['txn_id']);
    $sql = "SELECT COUNT(*) AS count FROM donates WHERE txn_id = '$txn_id'";
    $r = $db->query_first($sql);
    if (!$r) {
        error_log($db->oops());
        mail(WEBMASTER_EMAIL, 'OwlGaming Donation Failed $' . $_POST['mc_gross'], "The transaction is a duplicate" . $listener->getTextReport(), "From: " . DONATION_SERVER_MAIL);
        $db->free_result();
        $db->close();
        exit(0);
    }

    $exists = $r['count'] and $r['count'] > 0;

    if ($exists) {
        $errmsg .= "'txn_id' has already been processed: " . $_POST['txn_id'] . "\n";
    }

    if (!empty($errmsg)) {
        // manually investigate errors from the fraud checking
        $body = "IPN failed fraud checks: \n$errmsg\n\n";
        $body .= $listener->getTextReport();

        mail(WEBMASTER_EMAIL, 'IPN Fraud Warning', $body, "From: " . DONATION_SERVER_MAIL);
        $db->close();
    } else {
        // TODO: process order here
        // add this order to a table of completed donates
        $payer_email = $_POST['payer_email'];
        $mc_gross = $_POST['mc_gross'];


        //Main part here
        $pointsUsername = $_POST['custom'];
        $donorId = $_POST['item_number'];
        $amount = $_POST['mc_gross'];
        $vPoints = getGsFromDollar($amount)[0];

        if (!empty($errmsg)) {
            // manually investigate errors from the fraud checking
            $body = "IPN failed fraud checks: \n$errmsg\n\n";
            $body .= $listener->getTextReport();
            mail(WEBMASTER_EMAIL, 'IPN Fraud Warning', $body, "From: " . DONATION_SERVER_MAIL);
            $db->close();
            die();
        }

        //Give the lad some gamecoins
        $donateForPlayer = $db->query_first("SELECT `id`, `username`, `email` FROM `accounts` WHERE `username`='" . $db->escape($pointsUsername) . "'");
        $donatedFromPlayer = $db->query_first("SELECT `id`, `username`, `referrer`, `email` FROM `accounts` WHERE `id`='" . $donorId . "'");
        $donorUsername = $donatedFromPlayer['username'];
    
        $userID = $donateForPlayer['id'];
        $data1 = array();
        $data1['txn_id'] = $_POST['txn_id'];
        $data1['payer_email'] = $payer_email;
        $data1['mc_gross'] = $mc_gross;
        $data1['donor'] = $donorId;
        $data1['donated_for'] = $userID;
        $insertQryID = $db->query_insert("donates", $data1);

        //$MySQLConn = @mysql_connect(DB_SERVER, DB_USER, DB_PASS);
        //mysql_select_db(DB_DATABASE, $MySQLConn);

        
        $db->query("UPDATE `accounts` SET `credits`=`credits`+".$vPoints." WHERE `id`='" . $userID . "'");
        //mail(WEBMASTER_EMAIL, '$vPoints:'.$vPoints.'-$userID:'.$userID.'-$pointsUsername:'.$pointsUsername, 'sd', "From: " . DONATION_SERVER_MAIL);

        $referrer = $donatedFromPlayer['referrer'];
        if ($referrer and is_numeric($referrer) and $referrer > 0) {
            $GcToRef = ceil($vPoints / 10);
            if ($GcToRef > 0) {
                $db->query("UPDATE `accounts` SET `credits`=`credits`+".$GcToRef." WHERE `id`='" . $referrer . "'");
                $insert = array();
                //Make a purchase history
                $insert['name'] = "Referring reward - 10% of your friend's donation (" . $donorUsername . " - $" . $mc_gross . " - " . $vPoints . " GCs)";
                $insert['cost'] = $GcToRef;
                $insert['date'] = 'NOW()';
                $insert['account'] = $referrer;
                $db->query_insert("don_purchases", $insert);
            }
        }

        //mysql_close($MySQLConn);
        



        //Set the lad to donator group on forums
        //$fetchArr = $dbForums->query_first("SELECT username, usergroupid, membergroupids FROM user WHERE userid='" . $donorId . "'");
        //$userGroupString = $fetchArr['usergroupid'] . "," . $fetchArr['membergroupids'];
        /*
        if (!isPlayer(DONOR, $userGroupString)) {
            if (strlen($fetchArr['membergroupids']) < 1) {
                $newGroups = DONOR;
            } else {
                $newGroups = $fetchArr['membergroupids'] . ",".DONOR;
            }
            $dbForums->query("UPDATE user SET membergroupids='".$newGroups."' WHERE userid='" . $userID . "'");
        }
         * 
         */

        // send user an email indicating the transaction has completed.
        $to = $_POST['payer_email'];
        $subject = "Thank you for your donation!";
        require_once './functions/functions_tickets.php';
        @notify($db, $donorId, $to, "Thank you for your donation!", $thankYouEmail);
        @notify($db, 1, WEBMASTER_EMAIL, 'OwlGaming Donation $' . $_POST['mc_gross'] . ' from ' . $donatedFromPlayer['username'], $listener->getTextReport());
        @notify($db, $userID, $donateForPlayer['email'], 'OwlGaming Donation $' . $_POST['mc_gross'] . ' from ' . $donatedFromPlayer['username'], 'You have recieved a $' . $_POST['mc_gross'] . ' donation from ' . $donatedFromPlayer['username']);

        //Add an admin history
        require_once './functions/functions_account.php';
        @makeAdminHistory($db, $userID, 'OwlGaming Donation $' . $_POST['mc_gross'] . ' from ' . $donatedFromPlayer['username'], 6);
        $db->close();
    }
} else {
    // manually investigate the invalid IPN
    mail(WEBMASTER_EMAIL, 'Paypal Postback Hack Detected IP ' . get_client_ip(), 'Paypal Postback Hack Detected IP ' . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    echo "LOL, sorry..maybe you can make it next time ;)<br>";
    echo "-Maxime";
}

