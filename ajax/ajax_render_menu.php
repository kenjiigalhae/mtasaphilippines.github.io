<?php
session_start();
require_once("../functions/functions_logs.php");
//require_once '../functions/functions_player.php';
//Main menu
$menu = array();
//Home
array_push($menu, ["Home", "index.php", "_self"]);
//Forums
array_push($menu, ["Forums", "http://forums.owlgaming.net", "_blank"]);
array_push($menu, ["Refer a friend", "refer-a-friend.php", "_self"]); //Referrer
//UCP
if(isset($_SESSION['username']) ){
    array_push($menu, ["UCP", "ucp.php", "_self"]);
}
//Logs
if(isset($_SESSION['groups']) and canUserAccessLogs($_SESSION['groups'])){
    array_push($menu, ["Logs", "logs.php", "_self"]);
}

//Library
array_push($menu, ["Library", "library.php", "_self"]);
array_push($menu, ["Statistics", "stats.php", "_self"]);
array_push($menu, ["Support Center", "support.php", "_self"]);
//Donate
array_push($menu, ["Donate", "donate.php", "_self"]);

if(!isset($_SESSION['username']) or !$_SESSION['username']){
    array_push($menu, ["Register", "register.php", "_self"]); //Register
}
foreach ($menu as &$item) { ?>
   <li><a class="hover" href="<?php echo $item[1]; ?>" target="<?php echo $item[2]; ?>"><?php echo $item[0]; ?></a></li>
<?php } 