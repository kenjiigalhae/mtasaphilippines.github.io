<?php
session_start();
$normalPrice = 10;
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
}

if (!isset($_SESSION['userid'])) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    if (isset($_POST['intid']) and isset($_POST['intid'])) {
        $intid = $_POST['intid'];
        $charid = $_POST['charid'];
        $userID = $_SESSION['userid']; //This make sure noone edit other char
        require_once("../classes/mysql/Database.class.php");
        $db = new Database("MTA");
        $db->connect();
        $isFactionInt = false;
        $int = $db->query_first("SELECT i.*, DATEDIFF(NOW(), `lastused`) AS `datediff`, DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`, 
            charactername AS ownername, 
            (CASE WHEN uploaded_interior IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, uploaded_interior, NOW()) END) AS uploaded 
            FROM interiors i LEFT JOIN characters c ON i.owner=c.id 
	WHERE i.id='" . $intid . "' AND i.owner=" . $charid);
        if (!$int or ! $int['id']) {
            $int = $db->query_first("SELECT i.*, DATEDIFF(NOW(), `lastused`) AS `datediff`, DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`, 
            f.name AS ownername, 
            (CASE WHEN uploaded_interior IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, uploaded_interior, NOW()) END) AS uploaded 
            FROM interiors i 
            LEFT JOIN factions f ON i.faction=f.id 
            LEFT JOIN characters c ON c.faction_id=f.id 
	WHERE i.id=" . $intid . " AND c.id=" . $charid . " AND f.id=c.faction_id AND f.id IS NOT NULL");
            if (!$int or ! $int['id']) {
                $db->close();
                die('This interior is no longer belonged to you or your faction or you\'re not a faction leader.');
            } else {
                $isFactionInt = true;
            }
        }
        $db->close();
        ?>
        <h2>Custom interior upload for '<?php echo $int['name']; ?>'</h2>

        <table border="0" cellpadding="20">
            <tr>
                <td >
                    <table border="0" align=center class="nicetable" style="padding:10px;">
                        <tr><td colspan=3 align=center><img src="../images/interiordesign.png"/></td></tr>
                        <tr>
                            <td><b>Interior Name</td><td>:</td>
                            <td>  <?php echo $int['name']; ?></td>
                        </tr>
                        <tr>
                            <td><b>Cost</td><td>:</td>
                            <td>  $<?php echo number_format($int['cost']); ?></td>
                        </tr>

                        <tr>
                            <td><b>Owner</td><td>:</td>
                            <td>  <?php echo str_replace("_", " ", $int['ownername']); ?></td>
                        </tr>

                        <tr>
                            <td><b>Supplies</td><td>:</td>
                            <td>  <?php echo $int['supplies']; ?> Kg(s)</td>
                        </tr>

                        <tr>
                            <td><b>Last used</td><td>:</td>
                            <td>  <?php echo $int['lastused']; ?></td>
                        </tr>
                        <?php if ($int['uploaded'] >= 0) { ?>
                            <tr>
                                <td><b>Custom interior</td><td>:</td>
                                <td>
                                    <a href="../uploader/ajax_downloader.php?userid=<?php echo $_SESSION['userid']; ?>&intid=<?php echo $intid; ?>">Download</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
                <td>
                    <br><?php
                    if ($isFactionInt) {
                        $normalPrice = 0;
                        echo "<p>This is a faction interior so uploads for this interior is always <b>free of charge</b>.</p>";
                    } else {
                        if ($int['uploaded'] < 0) {
                            ?>
                            <p>You can spend <b><?php echo $normalPrice; ?> GC(s)</b> on getting a new custom interior for your property.</p>
                            <?php
                        } else {
                            if ($int['uploaded'] <= 24) {
                                $normalPrice = 0;
                                ?>
                                <p>You have just uploaded a custom interior for this property just <?php echo $int['uploaded']; ?> hour(s) ago. So the re-uploading for this property is now <b>free of charge</b>.</p> <?php
                            } else if ($int['uploaded'] <= 24*30) {
                                $normalPrice = $normalPrice / 2;
                                ?><p>A custom interior for this property was uploaded <?php echo floor($int['uploaded']/24); ?> day(s) ago. So the re-uploading fee for this property is now only a half (<b><?php echo $normalPrice; ?> GCs</b>).</p><?php
                            } else {
                                ?><p>A custom interior for this property was uploaded <?php echo floor($int['uploaded']/24); ?> day(s) ago. So the re-uploading fee for this property is still (<b><?php echo $normalPrice; ?> GCs</b>).</p><?php
                            
                            }
                        }
                    }
                    ?>
                    <p>Use a the MTA Map Editor to map the interior, and then upload the .map file here.<br>
                        The map must be in accordance to the following requirements to be uploaded:</p>
                    <ul>
                        <li>
                            You may upload a .map file only.
                        </li>
                        <li>
                            File size must be smaller than 100kB.
                        </li>
                        <li>
                            Map file must contain no more than 250 objects.
                        </li>
                        <li>
                            Map file must contain 1 marker for the exit of the interior.
                        </li>
                        <li>
                            All objects must be placed inside the world boundaries on the X,Y,Z axis between -3000 and 3000.
                        </li>
                        <li>
                            Interior and dimension of your map doesn't matter.
                        </li>
                        <li>
                            The interior should fit the exterior of the building it is being applied to.
                        </li>
                        <li>
                            You must have created the map yourself or have permission from the one who did.
                        </li>
                    </ul>
                    <b><i><u>Notices:</u></i></b>
                    <ul><i>
                            <li>After the successful upload, your map should be processed and the interior should be set up and ready to use in the game instantly.</li>
                            <?php if ($int['uploaded'] < 0) { ?><li>Uploads for Faction Interiors is always <b>free of charge</b>.</li><li>Once you have paid GC(s) to upload a custom interior to one property, future uploads by you to the same property will be <b>free of charge</b> within 24 hours and will cost a half (<b><?php echo ($normalPrice / 2); ?> GCs</b>) within 30 days for each re-uploads. This offer stays regardless of whether you will sell/loose the property to other players.</li>
        <?php } ?><li>Special object settings from map editor: doublesided (bool), collisions (bool), breakable (bool, scale (float) and alpha (int, values are 140-255, where 255 is fully opaque and 140 is fully transparent).</li>
                        </i></ul>
                </td>
            </tr>
        </table>
        <input id="uploadCost" type="hidden" value="<?php echo $normalPrice; ?>">
        <?php include("../uploader/interior_uploader.php"); ?>
        <br>
        <center><a href="" onClick="$('#char_info_mid_top').slideUp(500);
                        return false;">Close Interior Uploader</a></center>
        <?php
    }
}