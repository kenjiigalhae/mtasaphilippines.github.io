<?php

@session_start();
$now = new DateTime();
echo '<hr><h3>-> logs query made @ server time ' . $now->format('d/m/Y H:i:s') . '</h3>';
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    @session_destroy();
} else {
    $_SESSION['timeout'] = time();
}

if (!isset($_SESSION['groups'])) {
    echo "Session has timed out.";
    exit();
} else {
    if (!isset($_POST['logTypes']) || count($_POST['logTypes']) < 1) {
        exit('<center>Connection to server was broken.</center>');
    }
    require_once '../classes/mysql/Database.class.php';

    $dbMTACache = new Database("MTA");
    $dbMTACache->connect();


    $characterCache = array();

    function nameCache($id) {
        global $characterCache, $dbMTACache;
        if (isset($characterCache[$id])) {
            return $characterCache[$id];
        }

        $pos = strpos($id, "ch");
        if ($pos === false) {
            $pos = strpos($id, "fa");
            if ($pos === false) {
                $pos = strpos($id, "ve");
                if ($pos === false) {
                    $pos = strpos($id, "ac");
                    if ($pos === false) {
                        $pos = strpos($id, "in");
                        if ($pos === false) {
                            $pos = strpos($id, "ph");
                            if ($pos === false) {
                                $characterCache[$id] = $id . '[unknown]';
                                return $id;
                            } else {
                                $tempid = substr($id, 2);
                                $characterCache[$id] = "phone " . $tempid;
                                return $id;
                            }
                        } else {
                            $tempid = substr($id, 2);
                            $characterCache[$id] = "interior " . $tempid;
                            return $id;
                        }
                    } else {
                        $tempid = substr($id, 2);
                        $awsQry = $dbMTACache->query_first("SELECT `username` FROM `accounts` WHERE `id`='" . $tempid . "'");
                        if ($awsQry and $awsQry['username'] and strlen($awsQry['username']) > 0) {
                            $characterCache[$id] = $awsQry['username'];
                            return $awsQry['username'];
                        } else {
                            $characterCache[$id] = $id;
                            return $id;
                        }
                    }
                } else {
                    $tempid = substr($id, 2);
                    $characterCache[$id] = "vehicle " . $tempid;
                    return $characterCache[$id];
                }
            } else {
                $tempid = substr($id, 2);
                $awsQry = $dbMTACache->query_first("SELECT `name` FROM `factions` WHERE `id`='" . $tempid . "'");
                if ($awsQry and $awsQry['name'] and strlen($awsQry['name']) > 0) {
                    $characterCache[$id] = '[F]' . $awsQry['name'];
                    return $awsQry['name'];
                } else {
                    $characterCache[$id] = $id;
                    return $id;
                }
            }
        } else {
            $tempid = substr($id, 2);
            $awsQry = $dbMTACache->query_first("SELECT `charactername` FROM `characters` WHERE `id`='" . $tempid . "'");
            if ($awsQry and $awsQry['charactername'] and strlen($awsQry['charactername']) > 0) {
                $characterCache[$id] = str_replace("_", " ", $awsQry['charactername']);
                return $characterCache[$id];
            } else {
                $characterCache[$id] = $id . '[' . $tempid . ']';
                return $id;
            }
        }
    }

    $tableText = '<center><table id="newspaper-a" border="0" align="center" >
    <tr>
        <th>Time</th>
        <th>Action</th>
        <th>Player</th>
        <th>Data</th>
        <th>Affected Elements</th>
    </tr>';

    function getExactKeywordIfAny($text) {
        $first = substr($text, 0, 1);
        $last = substr($text, -1, 1);
        if ($first == '[' and $last == ']') {
            return substr($text, 1, -1);
        }
        return false;
    }

    $foundElement = "none";
    $error = 'none';

    $dbMTA = new Database("MTA");
    $dbMTA->connect(true);

    if ($_POST['keyword_type'] == 'character') {
        $exactKeyword = getExactKeywordIfAny($_POST['keyword']);
        if ($exactKeyword) {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`charactername` FROM `characters` WHERE `charactername` = '" . $dbMTA->escape(str_replace(" ", "_", $exactKeyword)) . "' LIMIT 1");
        } else {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`charactername` FROM `characters` WHERE `charactername` LIKE '%" . $dbMTA->escape(str_replace(" ", "_", $_POST['keyword'])) . "%' ORDER BY charactername LIMIT 20");
        }
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'ch' . $sqlRow['id'];
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No character name matched your query. Try again with another keyword.';
        } else {
            $error = 'The following character name matched your query:<BR />';
            $count = array();
            while ($sqlRow = $dbMTA->fetch_array($fetchIDquery)) {
                $error .= ' ' . htmlspecialchars($sqlRow['charactername']) . '<BR />';
                array_push($count, $sqlRow['charactername']);
            }
            if (count($count) >= 20) {
                $error .= '<i>- (And more..)</i><BR />';
            }
            $error .= "<br>Please be more specific or use [" . $_POST['keyword'] . "] to find exact. <br>For example: Use [" . $_POST['keyword'] . "] to find '" . $_POST['keyword'] . "' among '" . $count[0] . "', '" . $count[1] . "' and '".$_POST['keyword']."'. ";
        }
        $dbMTA->free_result();
    } else if ($_POST['keyword_type'] == 'vehicle ID') {
        $fetchIDquery = $dbMTA->query("SELECT `id` FROM `vehicles` WHERE `id`='" . $dbMTA->escape($_POST['keyword']) . "' LIMIT 1");
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 've' . $sqlRow['id'];
        } else {
            $error = 'No vehicle or too many vehicles were found with that ID.';
        }
        $dbMTA->free_result();
    } elseif ($_POST['keyword_type'] == 'interior ID') {
        $fetchIDquery = $dbMTA->query("SELECT `id` FROM `interiors` WHERE `id`='" . $dbMTA->escape($_POST['keyword']) . "' LIMIT 1");
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'in' . $sqlRow['id'];
            $dbMTA->free_result();
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No interior found with that ID.';
        }
    } elseif ($_POST['keyword_type'] == 'account') {
        $exactKeyword = getExactKeywordIfAny($_POST['keyword']);
        if ($exactKeyword) {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`username` FROM `accounts` WHERE `username` = '" . $dbMTA->escape(str_replace(" ", "_", $exactKeyword)) . "' LIMIT 1");
        } else {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`username` FROM `accounts` WHERE `username` LIKE '%" . $dbMTA->escape(str_replace(" ", "_", $_POST['keyword'])) . "%' ORDER BY username LIMIT 20");
        }
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'ac' . $sqlRow['id'];
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No account name matched your query. Try again with another keyword.';
        } else {
            $error = 'The following accounts matched your query:<BR />';
            $count = array();
            while ($sqlRow = $dbMTA->fetch_array($fetchIDquery)) {
                $error .= ' ' . htmlspecialchars($sqlRow['username']) . '<BR />';
                array_push($count, $sqlRow['username']);
            }
            if (count($count) >= 20) {
                $error .= '<i>- (And more..)</i><BR />';
            }
            $error .= "<br>Please be more specific or use [" . $_POST['keyword'] . "] to find exact. <br>For example: Use [" . $_POST['keyword'] . "] to find '" . $_POST['keyword'] . "' among '" . $count[0] . "', '" . $count[1] . "' and '".$_POST['keyword']."'. ";
        }
        $dbMTA->free_result();
    } elseif ($_POST['keyword_type'] == 'phonenumber') {
        $fetchIDquery = $dbMTA->query("SELECT `phonenumber` FROM `phones` WHERE `phonenumber`='" . $dbMTA->escape($_POST['keyword']) . "' LIMIT 1");
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'ph' . $sqlRow['phonenumber'];
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No phone or too many phones were found with that number. ';
        } else {
            $dbMTA->free_result();
        }
    }

    $dbMTA->close();
    if ($error != 'none') {
        die($error);
    }

    $selecterror = false;
    $queryLogTypes = '( (1=2) ';
    foreach ($_POST['logTypes'] as $logtype) {
        $queryLogTypes .= " OR (`action`='" . $logtype . "') ";
    }
    $queryLogTypes .= ')';

    $dbLogs = new Database("LOGS");
    $dbLogs->connect(true);

    $awesomeQuery = "SELECT *, DATE_FORMAT(`time`,'%b %d, %Y %h:%i %p') AS `time` FROM `owl_logs` WHERE ";
    $timeCoundition = " ( (`time` > (NOW() - INTERVAL " . $_POST['end_point'] . " HOUR)) AND (`time` < (NOW() - INTERVAL " . $_POST['start_point'] . " HOUR)) ) ";
    $order = " ORDER BY time DESC";
    $awesomeQuery .= $timeCoundition . " AND " . $queryLogTypes . " ";
    if ($_POST['keyword_type'] == 'logtext') {
        $awesomeQuery .= " AND (`content` LIKE '%" . $dbLogs->escape($_POST['keyword']) . "%') ";
    } else {
        if ($foundElement == 'none') {
            $dbLogs->close();
            die($error);
        }
        $awesomeQuery .= " AND (`source`='" . $dbLogs->escape($foundElement) . "' OR `affected` LIKE '%" . $dbLogs->escape($foundElement) . ";%') ";
    }
    $awesomeQuery .= $order;

    $awesomeQryExe = $dbLogs->query($awesomeQuery . ' LIMIT ' . $_POST['max_results']);
    if ($dbLogs->affected_rows() > 0) {
        echo $tableText;
    } else {
        echo "Nothing has found in logs database.";
    }

    require_once '../functions/functions_logs.php';

    while ($row = $dbLogs->fetch_array($awesomeQryExe)) {
        $explodedArr = explode(';', $row['affected']);
        $explodedStr = ""; //"Affected: <BR />";
        foreach ($explodedArr as $objectid) {
            if ($objectid != '') {
                $explodedStr .= htmlspecialchars(nameCache($objectid)) . ", ";
            }
        }
        echo "<tr><td style='overflow: hidden;white-space: nowrap;'>" . htmlspecialchars($row['time']) . "</td><td style='overflow: hidden;white-space: nowrap;'>" . $logTypes[$row['action']][0] . "</td><td style='overflow: hidden;white-space: nowrap;'>" . htmlspecialchars(nameCache($row['source'])) . "</td><td>" . htmlspecialchars($row['content']) . "</td><td>" . $explodedStr . "</td></tr>\r\n";
    }

    if ($dbLogs->affected_rows() > 0) {
        echo "</table></center><br><br><p>Logs query ended! Results found: " . $dbLogs->affected_rows() . "</p>";
    }

    $dbLogs->free_result();

    //Delete logs older than 6 months.
    $dbLogs->query("DELETE FROM owl_logs WHERE `time` < (NOW() - INTERVAL 4392 HOUR)");
    $dbLogs->close();
    $dbMTACache->close();
}    