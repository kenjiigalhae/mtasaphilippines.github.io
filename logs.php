<?php
include("header.php");
?>
<div id="main-wrapper" style="margin-bottom: 30px">
    <?php
    @session_start();
    if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
        @session_destroy();
        echo "<center><h3>Session has timed out, please re-login to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        $_SESSION['timeout'] = time();
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        @$params = $_SERVER['QUERY_STRING'];
        $currentUrl = $protocol . '://' . $host . $script . '?' . $params;
        //echo $currentUrl;
        $_SESSION['lastpage'] = $currentUrl;
    }
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        ?>
        <div id="logs_top">
            <center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>
        </div>
        <div id="logs_mid">
            <center>
                <b>
                    <div id="logs_loading"></div>
                </b>
            </center>    
        </div>
    <?php } ?>
</div>
<div id="logs_result" style="width:100%; padding-bottom: 100px; text-align: center;"></div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">
        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        <script type="text/javascript" src="js/ajax_logs.js"></script>
        <script>
            ajax_load_logs_GUI();
        </script>