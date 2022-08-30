<?php

require_once ("../classes/mysql/Database.class.php");
$db = new Database("MTA");
$db->connect();
$fetchDonorQry = $db->query("SELECT DISTINCT `donor`, `username`, (SELECT SUM(`mc_gross`) FROM `donates` `x` WHERE `x`.`donor`=`d`.`donor` AND `x`.`date` >= NOW() - INTERVAL 90 DAY) AS `mc_gross` FROM `donates` `d` LEFT JOIN `accounts` `a` ON `a`.`id`=`d`.`donor` WHERE `d`.`date` >= NOW() - INTERVAL 90 DAY ORDER BY `mc_gross` DESC LIMIT 20");
$count = 1;
echo '<center><table width=100% cellpadding="10"><tr><td valign=top width=50%>
	<h2>Top donors of 3 recent months</h2>';
while ($donor = $db->fetch_array($fetchDonorQry)) {
    echo "<b>" . $count . ". <a href='#' onClick='return false;'>" . $donor['username'] . " ($" . $donor['mc_gross'] . ")</a></b><br>";
    $count = $count + 1;
}
if ($count == 1) {
    echo "<b>0. None</a></b><br>";
}
$db->free_result();
echo '</td>
	<td valign=top width=50%>
	<h2>Recent donors</h2>';
$fetchDonorQry = $db->query("SELECT `donor`, `username`, `mc_gross` FROM `donates` `d` LEFT JOIN `accounts` `a` ON `a`.`id`=`d`.`donor` ORDER BY `date` DESC LIMIT 20");
$count = 1;
while ($donor = $db->fetch_array($fetchDonorQry)) {
    echo "<b>" . $count . ". <a href='#' onClick='return false;'>" . $donor['username'] . " ($" . $donor['mc_gross'] . ")</a></b><br>";
    $count = $count + 1;
}
if ($count == 1) {
    echo "<b>0. None</a></b><br>";
}
$db->free_result();
echo '</td></tr></table>';
?> 
<input type="button" value="Hide" id="hide_top_donor"/></center>
<?php
$db->close();
?>


