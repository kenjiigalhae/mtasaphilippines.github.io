<?php
$system_max_results = 200;
@session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    @session_destroy();
} else {
    $_SESSION['timeout'] = time();
}

if (!isset($_SESSION['groups'])) {
    echo "Session has timed out.";
    exit();
} else {
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid'] and false) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        $perms = $_SESSION['groups'];
        require_once("../functions/functions_logs.php");
        if (!canUserAccessLogs($perms)) {
            die("<center><h3>You don't access to the logging system.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
        }
        ?>

        <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!"; ?> Welcome to OwlGaming Logging System!</h2>
        <p>There are only a few groups of users who have access to this page, if you're see this, it means you're in one of those permission groups. However, based on the permission group / staff rank you're currently in, you can only see and search for certain type of logs displaying below:</p>
        <p>
            <b><u>Cautions:</u></b><br>
            <i>
                - To ensure an acceptable system performance and for an optimal logs querying speed, despite of your "Max Results" settings, the logging system only returns maximum <?php echo $system_max_results; ?> results per query and only returns logs from 6 months ago until present. <br>
                - Try to navigate "Start Point", "End Point" and use options/filters wisely. <br>
                - Server writes new logs at 7:00 AM (Server Time) everyday.<br>
                - If you've already known how many results you're gonna need, specify it to speed up the querying speed! <br>
            </i>
        </p>
        <form name='logs_form' action='' method='POST' onsubmit="return onLogsSubmit();">
            <table id="logtable" border="1" align=center width="100%">
                <tr>
                    <td colspan="5">
                        <b>What type of logs are you searching for?</b> <div id="logtype_reminder" style="display: inline; font-size: 12px;font-style: italic;color: red;">(Choose at least 1 and at most 5 options)</div><br>
                        <table border="0" cellspacing="0" cellpadding="0" align="left" >
                            <tr>

                                <?php
                                $count = 0;
                                $itemsPerCols = 7;
                                $needCloseTd = false;
                                foreach ($logTypes as $id => $detailarr)
                                    if ($detailarr[1]) {
                                        if ($count == 0) {
                                            echo '<td valign="center">';
                                        }
                                        $count = $count + 1;
                                        ?>
                                    <input type="checkbox" name=logtype[] value="<?php echo $id; ?>" > <?php echo $detailarr[0]; ?><BR />
                                    <?php
                                    if ($count == $itemsPerCols) {
                                        echo '</td><td valign="center">';
                                    }
                                    if ($count >= $itemsPerCols) {
                                        $count = 0;
                                    }
                                }
                            if ($count == 0) {
                                echo "- None";
                            }
                            ?>

                </tr>
            </table>
        </td>  
        </tr>
        <tr>
            <td width="15%">
                <b>Search Keyword: </b>
            </td>
            <td  colspan="3">
                <input type="input" id="keyword" required maxlength="80" minlength="1" style="width: 99%;">
            </td>
            <td colspan="3" rowspan="2" align="center" valign="center" width="162">
                <b>Max Results:</b><br>
                <input type="number" id="max_results" required max="<?php echo $system_max_results; ?>" min="1" step="1" value="50" >
            </td>
        </tr>
        <tr>
            <td valign="center">
                <b>Type of Keyword:</b>
            </td>
            <td colspan="3"> 
                <select id="keyword_type" style="width: 100%;">
                    <option value="account">Account name</option>
                    <option value="character">Character name</option>
                    <option value="vehicle ID">Vehicle ID</option>
                    <option value="interior ID">Interior ID</option>
                    <option value="phonenumber">Phone number</option>
                    <option value="logtext">Log text</option>
                </select>
            </td>
        </tr>
        <tr>
            <td valign="center">
                <b>Start Point:</b> 
            </td>
            <td > 
                <select id="start_point" style="width: 100%;">
                    <option value="0" selected>Now</option>
                    <option value="1">1 hour ago</option>
                    <option value="2">2 hours ago</option>
                    <option value="3">3 hours ago</option>
                    <option value="6">6 hours ago</option>
                    <option value="12">12 hours ago</option>
                    <option value="24">1 day ago</option>
                    <option value="48">2 days ago</option>
                    <option value="72">3 days ago</option>
                    <option value="168">1 week ago</option>
                    <option value="336">2 weeks ago</option>
                    <option value="504">3 weeks ago</option>
                    <option value="732">1 month ago</option>
                    <option value="1464">2 months ago</option>
                    <option value="2196">3 months ago</option>
                    <option value="4392">6 months ago</option>
                </select>
            </td>
            <td valign="center">
                <b>End Point:</b> 
            </td>
            <td> 
                <select id="end_point" style="width: 100%;">
                    <option value="0">Now</option>
                    <option value="1">1 hour ago</option>
                    <option value="2">2 hours ago</option>
                    <option value="3">3 hours ago</option>
                    <option value="6">6 hours ago</option>
                    <option value="12">12 hours ago</option>
                    <option value="24" selected>1 day ago</option>
                    <option value="48">2 days ago</option>
                    <option value="72">3 days ago</option>
                    <option value="168">1 week ago</option>
                    <option value="336">2 weeks ago</option>
                    <option value="504">3 weeks ago</option>
                    <option value="732">1 month ago</option>
                    <option value="1464">2 months ago</option>
                    <option value="2196">3 months ago</option>
                    <option value="4392">6 months ago</option>
                </select>
            </td>
            <td align="center" valign="center" width="162">
                <input type="submit" id="search_btn" value="Search" style="margin-left: 0px; margin-top: 0px; width: 98%"/>
            </td>
        </tr>
        </table>
        </form>
        <?php
    }
}