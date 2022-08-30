<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="reg_top">
    <table align="center">
        <tr>
            <td>
                <h2>Register a new account</h2>
                <p>We are glad you have considered to be a new member and a part of our community and we wish you a wonderful experience here!<br>
                    Please enter the details for your new account below.</p>
                <p><i>Please note that, we do not allow multiple accounts per user and you are hereby creating an MTA game account.</i></p>
            </td>
        </tr>
    </table>
</div>
<div id="reg_mid">

    <form id="register_form" onsubmit="ajax_submit_register();
            return false;" method="post" action="" onkeypress="resetError();">
        <br>
        <table align="center" class="login-form" style="width: 650px;" cellpadding="2">
            <tr>
                <td style="text-align: left;">
                    <b>Username: </b>
                </td>
                <td style="text-align: left;">
                    <input type="text" id="reg_username" placeholder="" maxlength="30" required/>
                </td>
                <td rowspan="5" valign="bottom" style="text-align: left;">
                    <div style="margin-left: 20px;">
                        <b>Prove you're human:</b>
                        <br>

                        <a href="" onclick="ajax_reload_captcha();
                                return false;"><div id="img_captcha"><img src="captcha/captcha.php" /></div></a><br>
                        <input type="text" id="reg_captcha" placeholder="Enter the text above" maxlength="5" required style="width: 146px;" />

                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <b>Password: </b>
                </td>
                <td style="text-align: left;">
                    <input type="password" id="reg_password1" placeholder="" maxlength="50" min="6" required/>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <b>Re-type Password: </b>
                </td >
                <td style="text-align: left;">
                    <input type="password" id="reg_password2" placeholder="" maxlength="50" min="6" required/>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <b>Email Address: </b>
                </td>
                <td style="text-align: left;">
                    <input type="email" id="reg_email" placeholder="" maxlength="200" min="1" required/>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <b>Referrer: </b>
                </td>
                <td style="text-align: left;">
                    <?php 
                    $referrer = strip_tags($_GET['referrer']);
                    ?>
                    <input type="text" id="reg_referrer" placeholder="Person who invited you" maxlength="30" value="<?php if ($referrer) echo $referrer; ?>" />
                </td>
            </tr>
            <tr>
                <td colspan="3">
            <center>
                <br>
                <input type="checkbox" id="terms" required/> I have read and agreed to the <a href="http://forums.owlgaming.net/forumdisplay.php?366-Rules" target="_blank">terms and conditions</a>.<br>
                <input type="submit" id="submit_reg" value="Register" style="margin-top: 20px; "/> 
            </center>
            </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id="error_reg" style="padding-top: 10px; color:red;font-style: italic;font-size: 11px;"></div>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="3">
                    <div id="reg_status"></div>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="reg_bot">

</div>
<script type="text/javascript" src="js/ajax_register.js"/></script>