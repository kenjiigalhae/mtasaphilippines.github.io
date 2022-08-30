<?php
session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
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
    $userID = $_SESSION['userid'];
}
if (!isset($userID) or ! $userID) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    
    /*
    require_once("../functions/functions.php");
    require_once("../classes/mysql/Database.class.php");
    $transferCost = 15;
    $db = new Database("MTA");
    $db->connect();
    $userRow = $db->query_first("SELECT `username`,`credits` FROM `accounts` WHERE id='" . $userID . "' LIMIT 1");
    $username = $userRow['username'];
    $gameCoins = $userRow['credits'];
    $transfers = floor($gameCoins / $transferCost);
    $charArr = array();
    $mQuery2 = $db->query("SELECT `id`,`charactername` FROM `characters` WHERE `account`='" . $userID . "' ORDER BY `charactername` ASC");
    while ($characterRow = $db->fetch_array($mQuery2)) {
        $charArr[$characterRow['id']] = $characterRow['charactername'];
    }
    $db->close();
    ?>
    <h2>Transfer assets between characters</h2>
    <div id="stat_transfer_gc_vs_times">You are currently having <b><?php echo $gameCoins; ?> GC</b> so that you will be able to do <b><?php echo $transfers; ?> transfer(s).</b></div>
    You can get more GC by <a href="?page_id=47" target=new>donating to our server</a>.<br><br>
    It costs <b><?php echo $transferCost; ?> GC for each time transferring</b> some or all assets(money, interiors, vehicles,...) from a character to an alternate character of yours. <BR /><BR />

    <table width="100%" border="0" class=nicetable>
        <tr>
            <td colspan="3" cellspacing="0" cellpadding="0" align="center">
                <div id="validateText">Please select the source and destination character for the transfer below.</div>
            </td>
        </tr>
        <tr>
            <td align="center" width="45%">
                From: 
                <select name="fromcharacter" id="fromcharacter" onchange="sFromCharChange()">
                    <option value="0">Select a character</option>
                    <?php
                    foreach ($charArr as $characterID => $characterName) {
                        echo"<option value=\"" . $characterID . "\">" . str_replace("_", " ", $characterName) . "</option>\r\n";
                    }
                    ?>												</select>
            </td>
            <td align="center" width="10%" rowspan="2">
                <img id="transfer_icon" src="/images/icons/transfer_icon_inactive.png" width="80%" onmouseover="mouseOverTransferIcon();" onmouseout="mouseOutTransferIcon();" onmousedown="mouseDownTransferIcon();" onmouseup="mouseUpTransferIcon();" onclick="mouseClickTransferIcon();"/>
            </td>
            <td align="center" width="45%">
                To:
                <select name="tocharacter" id="tocharacter" onchange="sToCharChange()">
                    <option value="0">Select a character</option>
                    <?php
                    foreach ($charArr as $characterID => $characterName) {
                        echo"													<option value=\"" . $characterID . "\">" . str_replace("_", " ", $characterName) . "</option>\r\n";
                    }
                    ?>												</select>
            </td>
        </tr>
        <tr>
            <td align="center">
                <div id="fromCharPreview"><input type="hidden" id="selectedFromCharId" value="0"/></div>
            </td>

            <td align="center">
                <div id="toCharPreview"><input type="hidden" id="selectedToCharId" value="0"/></div>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">
                <div id="source_char_assets"></div>
            </td>
        </tr>
    </table>
    <?php
     * 
     */
}

