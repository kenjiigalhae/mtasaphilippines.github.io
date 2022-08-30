<?php
session_start();
session_destroy();

?>
<div class="login_text">
    <form name="login" action="" method="post" onSubmit="ajax_login_box(); return false;">
        <input class="textfield" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
        <input class="password textfield" name="password" type="password" placeholder="Password" id="password" maxlength="100"required/>
        <div class="hidden-submit"><input type="submit" tabindex="-1"/></div>
    </form>
</div>
<div class="login_btn" id="ajax_login_box_btn">
    <a href="" onClick="ajax_login_box(); return false;"><img src="images/login_btn.png" border="0" alt="" /></a>
</div>
<div class="copyright">You have successfully logged out!<br><a href="register.php" >Register new account</a> | <a href="lostpw.php" target="new">Recover lost password</a></div>
<?php

//header("Location: ../index.php");
//die();






