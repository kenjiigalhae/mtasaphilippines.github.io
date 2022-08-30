<?php

session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
    echo "Session has timed out, please re-login to access this content.";
} else {
    $_SESSION['timeout'] = time();
}

if (!isset($_SESSION['userid']) or ! $_SESSION['userid'] and false) {
    echo "You must be logged in to access this content.";
} else {
    //if ($_SESSION['userid'] != 1) {
      //  die("This feature is under construction, please try again later.");
    //}
    if (isset($_POST['intid']) and isset($_POST['intid'])) {
        $intid = $_POST['intid'];
        $charid = $_POST['charid'];
        $userID = $_SESSION['userid']; //This make sure noone edit other char
        $uploadCost = $_POST['uploadCost'];
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

        $fileData = file_get_contents($root = realpath($_SERVER["DOCUMENT_ROOT"]) . '\\uploader\\uploads\\custom_interiors\\' . $intid . '.map');
        if (!$fileData) {
            $db->close();
            die('Server could not read the content of the file. Please try again.');
        }
        $comment = $_SESSION['username'] . " " . date("d-m-Y");
        // Cleanup old items
        $queries[] = "DELETE FROM `tempobjects` WHERE `dimension`='" . $intid . "'";
        $queries[] = "DELETE FROM `tempinteriors` WHERE `id`='" . $intid . "'";
        // Going to parse the map, try some checks here
        $error = false;

        // Going to parse the map, try some checks here
        $xml = @simplexml_load_string($fileData);
        foreach ($xml->object as $id => $value) {
            $model = $db->escape($value['model']);
            $posX = $db->escape($value['posX']);
            $posY = $db->escape($value['posY']);
            $posZ = $db->escape($value['posZ']);
            $rotX = $db->escape($value['rotX']);
            $rotY = $db->escape($value['rotY']);
            $rotZ = $db->escape($value['rotZ']);
            $alpha = $db->escape($value['alpha']);
            $interior = $int["interior"];

            if (isset($value['doublesided']) and ( $value['doublesided'] == 'true'))
                $doublesided = 1;
            else
                $doublesided = 0;

            //changed this from "solid" to "collisions" since map editor uses "collisions" argument, not "solid" --Exciter 11.06.2014
            if (isset($value['collisions']) and ( $value['collisions'] == 'false'))
                $solid = 0;
            else
                $solid = 1;

            //added support for scale and breakable --Exciter 11.06.2014
            if (isset($value['scale']))
                $scale = $db->escape($value['scale']);
            else
                $scale = 1;

            if (isset($value['breakable']) and ( $value['breakable'] == 'false'))
                $breakable = 0;
            else
                $breakable = 1;

            if ($posX > 3000 or $posX < -3000) {
                $error = true;
                echo 'Error: Object with model ID ' . $value['model'] . ' is placed outside the would boundaries on the X axis<BR />';
            }

            if ($posY > 3000 or $posY < -3000) {
                $error = true;
                echo 'Error: Object with model ID ' . $value['model'] . ' is placed outside the would boundaries on the Y axis<BR />';
            }

            if ($posZ > 3000 or $posZ < -3000) {
                $error = true;
                echo 'Error: Object with model ID ' . $value['model'] . ' is placed outside the would boundaries on the Z axis<BR />';
            }


            flush();
            $makequery = "INSERT INTO `tempobjects` (`model`, `posX`, `posY`, `posZ`, `rotX`, `rotY`, `rotZ`, `interior`, `dimension`, `doublesided`,`solid`,`scale`,`breakable`, `alpha`, `comment`) VALUES ('" . $model . "', '" . $posX . "', '" . $posY . "', '" . $posZ . "', '" . $rotX . "', '" . $rotY . "', '" . $rotZ . "', '" . $interior . "', '" . $intid . "', '" . $doublesided . "', '" . $solid . "', '" . $scale . "', '" . $breakable . "', '".$alpha."', '" . $comment . "')";
            $queries[] = $makequery;
        }
        $hasmarker = false;
        if (isset($xml->marker)) { // Update the interior spawn location
            foreach ($xml->marker as $id => $value) {
                $spawnX = $db->escape($value["posX"]);
                $spawnY = $db->escape($value["posY"]);
                $spawnZ = $db->escape($value["posZ"]);
                $spawnInterior = $int["interior"];
                $queries[] = "INSERT into `tempinteriors` SET `posX`='" . $spawnX . "', `posY`='" . $spawnY . "', `posZ`='" . $spawnZ . "', `interior`='" . $spawnInterior . "', `id`='" . $intid . "', `uploaded_by`='" . $db->escape($userID) . "', `uploaded_at`=NOW(), `amount_paid`='" . $db->escape($uploadCost) . "'";

                if ($spawnX > 3000 or $spawnX < -3000) {
                    $error = true;
                    echo 'Error: The entrance is placed outside the would boundaries on the X axis<BR />';
                }

                if ($spawnY > 3000 or $spawnY < -3000) {
                    $error = true;
                    echo 'Error: The entrance is placed outside the would boundaries on the Y axis<BR />';
                }

                if ($spawnZ > 3000 or $spawnZ < -3000) {
                    $error = true;
                    echo 'Error: The entrance is placed outside the would boundaries on the Z axis<BR />';
                }


                $hasmarker = true;
                break; // just the first one, please.				
            }
        }

        if (!$hasmarker) {
            $error = true;
            echo 'Error: The map does not have any cylinder(marker/spawnpoint for the exit of interior).<BR />';
        }


        $objectsLimitStandard = 251;
        $objectsLimit = $objectsLimitStandard;
        if (count($queries) > $objectsLimit) {
            $error = true;
            $objectsLimit = $objectsLimit - 1;
            $objectsLimitStandard = $objectsLimitStandard - 1;
            if ($objectsLimit != $objectsLimitStandard) {
                echo("Error: The map has exceeded the maximum number of objects (" . $objectsLimitStandard . " standard, " . $objectsLimit . " for you (" . $objectsLimitSpecialReason . ")).<BR />");
            } else {
                echo("Error: The map has exceeded the maximum number of objects (" . $objectsLimitStandard . ").<BR />");
            }
        } else {
            
        }
        
        if ($error) {
            $db->close();
            die();
        }

        //Now take GC
        $acc = $db->query_first("SELECT `username`,`credits` FROM `accounts` WHERE id='" . $userID . "' ");
        if ($acc['credits'] < $uploadCost) {
            $error = true;
            echo 'Map processing cancelled. Reason: You lack of GC(s) to use this feature. <br>'
            . 'Please <a href="donate.php">get more GC(s)</a> and then try again.';
        } else {
            $db->query("UPDATE `accounts` SET `credits`=`credits`-" . $uploadCost . " WHERE id='" . $userID . "' ");
            $data = array();
            $perkText = "Custom interior upload for ";
            if ($int['name']) {
                $perkText .= $int['name'] . " (#" . $intid . ") ";
            } else {
                $perkText .= "interior ID " . $intid . " ";
            }
            $data['name'] = $perkText;
            $data['cost'] = '-'.$uploadCost;
            $data['account'] = $userID;
            $db->query_insert("don_purchases", $data);
        }
        
        if ($error) {
            $db->close();
            die();
        }

        $counter = 0;
        foreach ($queries as $id => $query) {
            @$db->query($query);
            $counter = $counter + 1;
        }
        
        require_once("../classes/mta_sdk.php");
        $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
        $mtaServer->getResource("object-system")->call("processCustomInterior", false, false, $intid, $_SESSION['username']);
        
        $data = array();
        $data['uploaded_interior'] = 'NOW()';
        $db->query_update("interiors", $data, "id='".$intid."'");
        
        echo ('ok');
        $db->close();
    }
}