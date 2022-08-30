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
    <link href="css/login-form.css" type="text/css" rel="stylesheet" />
    <div id="logs_top">
        <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!"; ?> Welcome to OwlGaming Logging System!</h2>
        <p>There are only a few groups of users who have access to this page, if you're see this, it means you're in one of those permission groups. However, based on the permission group / staff rank you're currently in, you can only see and search for "things" displaying below or only certain parts of the logging system. All the options that you don't have sufficient access to are hidden.</p>
        
    </div>
    <div id="logs_mid">
        <center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>
    </div>
    <div id="logs_bottom">

    </div>
<script type="text/javascript" src="js/ajax_logs.js"></script>
<script>
    setTimeout(function(){ajax_load_logs_GUI();}, 50);
</script>
<?php } ?>