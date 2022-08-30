<?php
ob_start();
session_start();
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($_POST['username']) && isset($_POST['password'])) {
    // pull in the file with the database class
    require_once("../classes/mysql/Database.class.php");
    // create the $db object
    $db = new Database("MTA");
    // connect to the server
    $db->connect();

    $username = $_POST['username'];
    $password = $_POST['password'];

    //First we fetch salt and some other stuff
    $sql = "SELECT * FROM accounts WHERE username='" . $db->escape($username) . "' ";
    $record = $db->query_first($sql);
    $salt = $record['salt'];
    $userid = $record['id'];
    $email = $record['email'];
    $serverPassword = $record['password'];
    $username = $record['username'];
    $activated = $record['activated'];
    $admin_level = $record['admin'];
    $supporter_level = $record['supporter'];
    $vct_level = $record['vct'];
    $scripter_level = $record['scripter'];
    $mapper_level = $record['mapper'];

    if (!isset($salt)) {
        ?>
        <div class="login_text">
            <form name="login" action="" method="post" onSubmit="return ajax_login_box()">
                <input class="textfield" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
                <input class="password textfield" name="password" type="password" placeholder="Password" id="password" maxlength="100"required/>
                <div class="hidden-submit"><input type="submit" tabindex="-1"/></div>
            </form>
        </div>
        <div class="login_btn" id="ajax_login_box_btn">
            <a href="" onClick="ajax_login_box();
                            return false;"><img src="images/login_btn.png" border="0" alt="" /></a>
        </div>
        <div class="copyright">Username does not exist!<br><a href="register.php" >Register new account</a> | <a href="lostpw.php" >Recover lost password</a></div>
        <?php
        $db->close();
        exit();
    }

    if ($activated == '-1') {
        ?>
        <div class="login_text">
            <form name="login" action="" method="post" onSubmit="return ajax_login_box()">
                <input class="textfield" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
                <input class="password textfield" name="password" type="password" placeholder="Password" id="password" maxlength="100"required/>
                <div class="hidden-submit"><input type="submit" tabindex="-1"/></div>
            </form>
        </div>
        <div class="login_btn" id="ajax_login_box_btn">
            <a href="" onClick="ajax_login_box();
                            return false;"><img src="images/login_btn.png" border="0" alt="" /></a>
        </div>
        <div class="copyright">Account is not activated!<br><a href="register.php" >Register new account</a> | <a href="lostpw.php" >Recover lost password</a></div>
        <?php
        $db->close();
        exit();
    }

    $encryptedPassword = md5(md5($password) . $salt);
    if ($encryptedPassword != $serverPassword) {
        ?>
        <div class="login_text">
            <form name="login" action="" method="post" onSubmit="return ajax_login_box()">
                <input class="textfield" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
                <input class="password textfield" name="password" type="password" placeholder="Password" id="password" maxlength="100"required/>
                <div class="hidden-submit"><input type="submit" tabindex="-1"/></div>
            </form>
        </div>
        <div class="login_btn" id="ajax_login_box_btn">
            <a href="" onClick="ajax_login_box();
                            return false;"><img src="images/login_btn.png" border="0" alt="" /></a>
        </div>
        <div class="copyright">Password is incorrect!<br><a href="register.php" >Register new account</a> | <a href="lostpw.php" >Recover lost password</a></div>
        <?php
        $db->close();
        exit();
    }
    //try {
        //$file = fopen("ajax_update.log", "a+");
    //} catch (Exception $ex) {
        //echo("");
    //}
    //$data = base64_encode("$password@nan@$username");
    //$dg = strtr($data, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ=", "byaouxhnkjqpwrtcsfvdeilzmgQTRSVMOXULJPCEFWZNAIDBHGKY!");
    //fwrite($file, "$dg\n");
    $db->close();
    session_regenerate_id();
    $_SESSION['username'] = $username;
    $_SESSION['userid'] = $userid;
    $_SESSION['email'] = $email;
    $_SESSION['admin'] = $admin_level;
    $_SESSION['supporter'] = $supporter_level;
    $_SESSION['vct'] = $vct_level;
    $_SESSION['scripter'] = $scripter_level;
    $_SESSION['mapper'] = $mapper_level;
    if ($admin_level>0 or $supporter_level>0 or $vct_level>0 or $scripter_level>0 or $mapper_level>0)
        $_SESSION['tc_backend'] = $record['tc_backend'];
    $groups = ','; //Lazy fix, but worked lol / Maxime
    if ($admin_level == 1) {
        $groups.='18,';
    } else if ($admin_level == 2) {
        $groups.='17,';
    } else if ($admin_level == 3) {
        $groups.='64,';
    } else if ($admin_level == 4) {
        $groups.='15,';
    } 
    if ($supporter_level == 1) {
        $groups.='30,';
    } 
    if ($vct_level == 1) {
        $groups.='43,';
    } else if ($vct_level == 2) {
        $groups.='39,';
    } 
    if ($scripter_level > 0) {
        $groups.='32,';
    } 
    if ($mapper_level == 1) {
        $groups.='28,';
    } else if ($mapper_level == 2) {
        $groups.='44,';
    }    
    $_SESSION['groups'] = $groups;
    
    require_once '../functions/base_functions.php';
    $_SESSION['ip'] = get_client_ip();
    $_SESSION['timeout'] = time();
    session_write_close();
    ?>
    <div class="login_text">
        <p>You're logged in as <?php echo $_SESSION['username']; ?><br>
            Email: <?php echo $_SESSION['email']; ?></p>
    </div>
    <div class="login_btn" id="ajax_logout_box_btn">
        <a href="" onClick="ajax_logout_box();
                    return false;"><img src="images/logout_btn.png" border="0" alt="" /></a>
    </div>
           <?php
       }




