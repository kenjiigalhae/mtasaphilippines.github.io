<?php
include("header.php");
?>
<div id="main-wrapper">
    <div id="thank_top">
        <h2>Refer a friend, get rewards.</h2>
        <p>As we all know, OwlGaming Roleplay is more fun when you play it with your friends. And so, to encourage you to help your friends started a virtual life here and be a part of us, we're going to give you rewards for getting them to sign up.</p>
        <p>This reward can be anything we don't know yet, but it could be some GC(s) for now ;)</p>
        <p>When will you be eligible for a reward?<br>
            <i>- Every time someone that you have referred reaches 50 hoursplayed on any character: <b>10 GC(s)</b><br>
                - Every time someone that you have referred donates to the community: <b>10% of what your friend donates</b>  </i>
        </p>
        <p>So how do you start referring a friend? <?php if (isset($_SESSION['username']) and $_SESSION['username']) { ?>
                You just send them <a href="register.php?referrer=<?php echo $_SESSION['username']; ?>" target="_blank" ><b>this link to our register page</b></a> and it's that easy!</p> <?php
        } else {
            $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $script = '/register.php';
            $params = 'referrer=Your_Account_Name_Here';
            $siteURL = $protocol . '://' . $host . $script . '?' . $params;
    ?>
            You just send them this link to our register page: <br> <b><a href="<?php echo $siteURL;
    ?>" target="_blank"><?php echo $siteURL;
    ?></a></b>
<?php } ?>
    </div>

    <div id="char_info_mid">
        


                    </div>
                    <div id="char_info"></div>
                    </div>
                    <div class="content_wrap">
                        <div class="text_holder">
                            <div class="features_box">

                            </div>	
                            <?php
                            include("sub.php");
                            include("footer.php");
                            ?>

