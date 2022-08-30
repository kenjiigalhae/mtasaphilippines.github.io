<?php
include("header.php");
?>
<div id="main-wrapper">
    <div id="lib_top">
        <?php
// define the path and name of cached file
        $cachefile = './cache/stats.php';
        $cachetime = 60 * 15; // 15 mins
// Check if the cached file is still fresh. If it is, serve it up and exit.
        if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
            include($cachefile);
        } else {
// if there is either no file OR the file to too old, render the page and capture the HTML.
            ob_start();
            ?>
            <html>

                <?php
                require_once './classes/mysql/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $totalAccounts = $db->query_first("SELECT COUNT(*) AS `totalAccounts` FROM `accounts` ");
                $totalAccounts = number_format($totalAccounts["totalAccounts"]);

                $totalCharacters = $db->query_first("SELECT COUNT(*) AS `totalCharacters` FROM `characters` ");
                $totalCharacters = number_format($totalCharacters["totalCharacters"]);

                $totalCharactersMissing = $db->query_first("SELECT COUNT(*) AS `totalCharacters` FROM `characters` WHERE cked = 1");
                $totalCharactersMissing = number_format($totalCharactersMissing["totalCharacters"]);

                $totalCharactersBuried = $db->query_first("SELECT COUNT(*) AS `totalCharacters` FROM `characters` WHERE cked = 2");
                $totalCharactersBuried = number_format($totalCharactersBuried["totalCharacters"]);

                $totalInteriors = $db->query_first("SELECT COUNT(*) AS `totalInteriors` FROM `interiors` WHERE `deleted`='0' ");
                $totalInteriors = number_format($totalInteriors["totalInteriors"]);

                $totalVehicles = $db->query_first("SELECT COUNT(*) AS `totalVehicles` FROM `vehicles` WHERE `deleted`='0' ");
                $totalVehicles = number_format($totalVehicles["totalVehicles"]);

                $totalPlayedHours = $db->query_first("SELECT SUM(`hoursplayed`) AS `totalPlayedHours` FROM `characters` ");
                $totalPlayedHours = number_format($totalPlayedHours["totalPlayedHours"]);

                $totalBankMoney = $db->query_first("SELECT SUM(`bankmoney`) AS `totalBankMoney` FROM `characters` ");
                $totalBankMoney = number_format($totalBankMoney["totalBankMoney"]);

                $mostActiveRpers = $db->fetch_all_array("SELECT `account`, (SELECT `username` FROM `accounts` `a` WHERE `a`.`id`=`account`) AS `rper`, SUM(`hoursplayed`) AS `totalHours`, `charactername`, `hoursplayed` FROM `characters` WHERE `lastlogin` > NOW() - interval 30 day GROUP BY `account` ORDER BY `totalHours` DESC LIMIT 10;");
                $RichestRpers = $db->fetch_all_array("SELECT (`bankmoney`+`money`) AS `totalMoney`, `charactername` FROM `characters` WHERE `lastlogin` > NOW() - interval 30 day ORDER BY `totalMoney` DESC LIMIT 10;");

                $mostRecentDead = $db->query_first("SELECT username, charactername AS name, DATE_FORMAT(death_date,'%b %d, %Y at %h:%i %p') AS fdate, ck_info, age FROM characters c LEFT JOIN accounts a ON c.account=a.id WHERE cked>0 ORDER BY death_date DESC LIMIT 1");

                $mostDeaths = $db->query_first("SELECT charactername AS name, username, deaths FROM characters c LEFT JOIN accounts a ON c.account=a.id WHERE cked=0 ORDER BY deaths DESC, c.lastlogin DESC LIMIT 1");
                $mostreports = $db->fetch_all_array("SELECT id, username, admin, supporter, vct, scripter, mapper, adminreports FROM accounts WHERE admin > 0 OR supporter > 0 OR vct > 0 OR scripter > 0 OR mapper > 0 ORDER BY adminreports DESC, admin DESC, supporter DESC, vct DESC, scripter DESC, mapper DESC, id LIMIT 10");
                $mostAds = $db->query_first("SELECT username, charactername, COUNT(*) AS count FROM advertisements v LEFT JOIN characters c ON v.created_by=c.id LEFT JOIN accounts a ON c.account=a.id GROUP BY created_by ORDER BY count DESC LIMIT 1");

                $factions = $db->fetch_all_array("SELECT COUNT(*) AS count, name, bankmoney FROM factions f LEFT JOIN characters c ON f.id=c.faction_id GROUP BY f.id ORDER BY count DESC, bankmoney DESC LIMIT 10");

                require_once './functions/functions.php';
                ?>
                <h2>Server Statistics</h2>
                <ul>
                    <li>
                        <b>Total accounts:</b> <?php echo $totalAccounts; ?>
                    </li>
                    <li>
                        <b>Total characters:</b> <?php echo ($totalCharacters); ?> (<?php echo ($totalCharactersMissing); ?> missing & <?php echo ($totalCharactersBuried); ?> found dead)
                    </li>
                    <li>
                        <b>Total interiors:</b> <?php echo $totalInteriors; ?>
                    </li>
                    <li>
                        <b>Total vehicles:</b> <?php echo $totalVehicles; ?>
                    </li>
                    <li>
                        <b>Total hoursplayed:</b> <?php echo $totalPlayedHours; ?> hours
                    </li>
                    <li>
                        <b>Total money in the Bank of San Andreas:</b> $<?php echo $totalBankMoney; ?>
                    </li>

                </ul>
                <h2>Player Statistics</h2>
                
                
                <li>
                    <b>The most recent death:</b> <?php echo str_replace("_", " ", $mostRecentDead['name']); ?> (<?php echo $mostRecentDead['username']; ?>) died at the age of <?php echo $mostRecentDead['age']; ?>, death causes: <?php echo $mostRecentDead['ck_info']; ?>, <?php echo $mostRecentDead['fdate']; ?> 
                </li>
                <li>
                    <b>The worst-luck-Brian character alive:</b> <?php echo str_replace("_", " ", $mostDeaths['name']); ?> (<?php echo $mostDeaths['username']; ?>) visited hospital <?php echo $mostDeaths['deaths']; ?> times
                </li>
                <li>
                    <b>The most wannabe-businessman:</b> <?php echo str_replace("_", " ", $mostAds['charactername']); ?> (<?php echo $mostAds['username']; ?>) with <?php echo $mostAds['count']; ?> advertisements on going.
                </li>
                <p><i>*The following tables excluded inactive characters (those haven't logged in 30 days or longer)*</i></p>
                <table width="100%">
                    <tr>
                        <td width="50%">
                            <table id="logtable" border="1" align="center" width="100%">
                                <tr>
                                    <td colspan="4">
                                <center><b>Most active</b></center>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"><b>Rank</b></td>
                        <td align="center"><b>Username</b></td>
                        <td align="center"><b>Character</b></td>
                        <td align="center"><b>Hours</b></td>
                    </tr>
                    <?php
                    $count = 1;
                    foreach ($mostActiveRpers as $activeRper) {
                        ?>
                        <tr>
                            <td align=center><b><?php echo $count; ?></b></td>
                            <td><b><?php echo $activeRper["rper"]; ?></b></td>
                            <td><?php echo str_replace("_", " ", $activeRper["charactername"]); ?> (<?php echo $activeRper["hoursplayed"]; ?> hours)</td>
                            <td align=center><?php echo $activeRper["totalHours"]; ?> hours</td>
                        </tr>
                        <?php
                        $count = $count + 1;
                    }
                    ?>
                </table>
                </td>
                <td width="50%">
                    <table id="logtable" border="1" align="center" width="100%">
                        <tr>
                            <td colspan="4">
                        <center><b>Wealthiest</b></center>
                </td>
                </tr>
                <tr>
                    <td align="center"><b>Rank</b></td>
                    <td align="center"><b>Character</b></td>
                    <td align="center"><b>Money</b></td>
                </tr>
                <?php
                $count = 1;
                foreach ($RichestRpers as $richRper) {
                    ?>
                    <tr>
                        <td align=center><b><?php echo $count; ?></b></td>
                        <td align=center><b><?php echo strtok($richRper["charactername"], '_'); ?></b></td>
                        <td align=center>$<?php echo number_format($richRper["totalMoney"]); ?></td>
                    </tr>
                    <?php
                    $count = $count + 1;
                }
                ?>
                </table>
                </td>
                </tr>
                </table>

                <table width="100%">
                    <tr>

                        <td width="50%">
                            <table id="logtable" border="1" align="center" width="100%">
                                <tr>
                                    <td colspan="4">
                                <center><b>Top ten staff members</b></center>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"><b>Rank</b></td>
                        <td align="center"><b>Position</b></td>
                        <td align="center"><b>Username</b></td>
                        <td align="center"><b>Reports solved</b></td>
                    </tr>
                    <?php
                    $count = 1;
                    foreach ($mostreports as $staff) {
                        ?>
                        <tr>
                            <td align=center><b><?php echo $count; ?></b></td>
                            <td align=center><?php echo getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']); ?></td>
                            <td align=center><b><?php echo $staff["username"]; ?></b></td>
                            <td align=center><?php echo number_format($staff["adminreports"]); ?></td>
                        </tr>
                        <?php
                        $count = $count + 1;
                    }
                    ?>
                </table>
                </td>

                <td width="50%">
                    <table id="logtable" border="1" align="center" width="100%">
                        <tr>
                            <td colspan="4">
                        <center><b>Top ten factions</b></center>
                </td>
                </tr>
                <tr>
                    <td align="center"><b>Rank</b></td>
                    <td align="center"><b>Faction</b></td>
                    <td align="center"><b>Members</b></td>
                    <td align="center"><b>Bank</b></td>
                </tr>
                <?php
                $count = 1;
                foreach ($factions as $faction) {
                    ?>
                    <tr>
                        <td align=center><b><?php echo $count; ?></b></td>
                        <td><b><?php echo $faction["name"]; ?></b></td>
                        <td align=center><?php echo $faction["count"]; ?></td>
                        <td align=center>$<?php echo number_format($faction["bankmoney"]); ?></td>
                    </tr>
                    <?php
                    $count = $count + 1;
                }
                ?>
                </table>
                </td>
                </tr>
                </table>
                 <!--<br><i>*(This page is cached to optimize loading speed thus far it only get updated once every 15 minutes.)*</i>-->
            </html>

            <?php
// We're done! Save the cached content to a file
            $fp = fopen($cachefile, 'w');
            fwrite($fp, ob_get_contents());
            fclose($fp);
// finally send browser output
            ob_end_flush();
        }
        ?>
    </div>
    <div id="lib_mid" ></div>
    <div id="lib_bot"></div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>


