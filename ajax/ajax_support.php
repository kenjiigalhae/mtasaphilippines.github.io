<?php
require_once '../functions/functions_tickets.php';
require_once '../classes/mysql/Database.class.php';
$db = new Database("MTA");
$db->connect();
$step = $_POST['step'];
session_start();
$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
@$params = $_SERVER['QUERY_STRING'];
$currentUrl = $protocol . '://' . $host;
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time())
    session_destroy();
else {
    $userID = $_SESSION['userid'];
    $_SESSION['timeout'] = time();
}
if ($step == "change_status") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        if ($db->query("UPDATE tc_tickets SET status=" . $_POST['state'] . " WHERE id=" . $_POST['tcid']))
            echo $_POST['tcid'];
        else
            echo "error";
    } else
        echo "error";
} else if ($step == "toggle_private") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        if ($db->query("UPDATE tc_tickets SET private=" . $_POST['state'] . " WHERE id=" . $_POST['tcid']))
            echo $_POST['tcid'];
        else
            echo "error";
    } else
        echo "error";
} else if ($step == "add_subcriber") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        $sub = $db->query_first("SELECT id, username, email FROM accounts WHERE username='" . $db->escape($_POST['subcriber']) . "' LIMIT 1");
        if ($sub and $sub['id'] and is_numeric($sub['id'])) {
            $ticket = $db->query_first("SELECT subcribers, creator, assign_to, status FROM tc_tickets WHERE id=" . $_POST['tcid']);
            $isExisted = (($ticket['creator'] == $sub['id']) or ( $ticket['assign_to'] == $sub['id']));
            if (!$isExisted) {
                $checks = explode(",", $ticket['subcribers']);
                foreach ($checks as $check) {
                    if ($check == $sub['id']) {
                        $isExisted = true;
                        break;
                    }
                }
            }
            if ($isExisted)
                echo "error";
            else {
                $db->query("UPDATE tc_tickets SET subcribers=CONCAT(subcribers, '" . $sub['id'] . ",') WHERE id=" . $_POST['tcid']);
                @addTicketComment($db, $_POST['tcid'], $userID, "Subscribed " . $sub['username'] . " to this ticket.", 0, $ticket);
                echo $_POST['tcid'];
            }
        } else
            echo "error";
    } else
        echo "error";
} else if ($step == "reassign_ticket") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        if (@assignTicket($db, $_POST['tcid'], $_POST['assignto']) and @ addTicketComment($db, $_POST['tcid'], $userID, "Re-assigned to " . $_POST['assigntoname'], 0, false, true)) {
            echo $_POST['tcid'];
        }
    } else {
        echo "error";
    }
} else if ($step == "tc_switch") {
    if (isset($userID) and canUserAccessTcBackEnd($_SESSION['groups'])) {
        if ($db->query("UPDATE accounts SET tc_backend=" . $_POST['state'] . " WHERE id=" . $userID . " AND (admin>0 OR supporter>0 OR vct>0 OR scripter>0 OR mapper>0 )")) {
            $_SESSION['tc_backend'] = $_POST['state'];
        }
    }
} else if ($step == "load_backend_midtop") {
    if (canUserAccessTcBackEnd($_SESSION['groups']) and isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        echo "<br>";
        $condition = "";
        $chk_closed = " checked ";
        $chk_locked = " checked ";
        if ($_POST['closed'] == 0) {
            $condition .= " AND status!=4 ";
            $chk_closed = "";
        }
        if ($_POST['locked'] == 0) {
            $condition .= " AND status!=-1 ";
            $chk_locked = "";
        }
        echo "<h2>Unassigned Tickets</h2>";
        $unassigneds = $db->fetch_all_array("SELECT subject, username AS creatorname, status, t.id, type, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id WHERE assign_to=0 " . $condition . " ORDER BY t.id");
        if ($unassigneds and $db->affected_rows > 0) {
            foreach ($unassigneds as $unassigned) {
                echo getTicketStatus($unassigned['status'], $unassigned['id']) . " <a href='#' onclick='load_ticket(" . $unassigned['id'] . "); return false;'>#" . $unassigned['id'] . " - " . getTicketType($unassigned['type']) . " (Creator: " . $unassigned['creatorname'] . ") - " . $unassigned['date'] . " (" . formatDays($unassigned['dateago']) . ")</a><br>";
            }
        } else {
            echo "<center><p><i>There is no unassigned ticket at the moment.</i></p></center>";
        }
        echo "<br>";
        echo "<input type='checkbox' id='chk_global_closed' onclick='checkBoxGlobalChanges(); return false;' " . $chk_closed . "/> <i>Include closed tickets</i><br>";
        echo "<input type='checkbox' id='chk_global_locked' onclick='checkBoxGlobalChanges(); return false;' " . $chk_locked . "/> <i>Include locked tickets</i>";
        echo "<br>";
    }
} else if ($step == "load_my_tickets") {
    if (isset($userID)) {
        $condition = "";
        $chk_closed = " checked ";
        $chk_locked = " checked ";
        if ($_POST['closed'] == 0) {
            $condition .= " AND status!=4 ";
            $chk_closed = "";
        }
        if ($_POST['locked'] == 0) {
            $condition .= " AND status!=-1 ";
            $chk_locked = "";
        }
        echo "<h2>My Tickets</h2>";
        if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
            $mytickets = $db->fetch_all_array("SELECT username AS creatorname, status, t.id, type, subject, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id WHERE (assign_to=" . $userID . " OR subcribers LIKE '%," . $userID . ",%')" . $condition . "  ORDER BY t.id DESC");
            echo "<p>The following tickets were assigned to you or subscribed by you, click to view in details.</p>";
            if ($mytickets and $db->affected_rows > 0) {
                echo "<ul>";
                foreach ($mytickets as $myticket) {
                    echo getTicketStatus($myticket['status'], $myticket['id']) . " <a href='#' onclick='load_ticket(" . $myticket['id'] . "); return false;'>#" . $myticket['id'] . " - " . getTicketType($myticket['type']) . " (" . $myticket['creatorname'] . ": " . trimSubject($myticket['subject'], 80) . ") - " . $myticket['date'] . " (" . formatDays($myticket['dateago']) . ")</a><br>";
                }
                echo "</ul>";
            } else
                echo "<center><p><i>You don't have any tickets assigned to you at the moment.</i></p></center>";
        } else {
            $mytickets = $db->fetch_all_array("SELECT status, id, type, subject, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets WHERE (creator=" . $userID . " OR subcribers LIKE '%," . $userID . ",%')" . $condition . " ORDER BY id DESC");
            echo "<p>The following tickets were created by you or related to you, click to view in details.</p>";
            if ($mytickets and $db->affected_rows > 0) {
                echo "<ul>";
                foreach ($mytickets as $myticket) {
                    echo getTicketStatus($myticket['status'], $myticket['id']) . " <a href='#' onclick='load_ticket(" . $myticket['id'] . "); return false;'>#" . $myticket['id'] . " - " . getTicketType($myticket['type']) . " (" . trimSubject($myticket['subject'], 80) . ") - " . $myticket['date'] . " (" . formatDays($myticket['dateago']) . ")</a><br>";
                }
                echo "</ul>";
            } else
                echo "<center><p><i>You don't have any tickets at the moment.</i></p></center>";
        }

        echo "<input type='checkbox' id='chk_my_ticket_closed' onclick='checkBoxMyTicketChanges(); return false;' " . $chk_closed . "/> <i>Include closed tickets</i><br>";
        echo "<input type='checkbox' id='chk_my_ticket_locked' onclick='checkBoxMyTicketChanges(); return false;' " . $chk_locked . "/> <i>Include locked tickets</i><br>"
        . "<br><input type=button onclick='load_my_tickets(); return false;' value='Refresh'>";
    }
} else if ($step == "load_submit_form") {
    ?>
    <form action="" onsubmit="client_submit_ticket(<?php echo $_POST['type']; ?>);
                return false;">
        <table border="0">
            <?php
            echo "<br><h2>Submit a new ticket - " . getTicketType($_POST['type']) . "</h2>";
            if (!isset($userID) or ! $userID or $userID < 1) {
                echo "<center><p><i>You must be logged in to submit this type of ticket.</i></p></center>";
            } else {
                if ($_POST['type'] == 2) {
                    echo '<tr><td colspan="2">';
                    echo '<b>Upon submitting your ban appeal, you hereby agree that:</b><br>
<ul>
<li>You will not submit more than one appeal a week (unless instructed to do so by a staff member).</li>
<li>If your appeal is unsuccessful, you will serve your ban without attempting to enter the server on another account and if caught doing so; you will be subject to further discipline.</li>
<li>You will tell the truth and continue to do so throughout the entirety of your appeal.</li>
<li>You have not left any relevant information out of your appeal.</li></ul><br>';
                    echo "<b>Select account type to be unbanned: </b>";
                    echo '<select id="unban_account_type" onchange="unban_select();">';
                    echo '<option value="None" selected>None</option>';
                    echo '<option value="MTA" >MTA</option>';
                    echo '<option value="Forums" >Forums</option>';
                    echo '</td></tr>'
                    . '<tr><td colspan=2><div id="unban_below"></div> </td></tr>';
                } else if ($_POST['type'] == 8) {
                    echo '<tr><td colspan="2">';
                    echo '<p><b>Please choose a history record to appeal with:</b><br><i>Multiple records are allowed however, they should be related. Otherwise, you should make them in separated tickets.</i></p>'
                    . '<div id="histories">';

                    require_once '../functions/functions.php';
                    //SELECT DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, action, h.admin AS hadmin, reason, duration, a.username as username, "
                    //. "c.charactername AS user_char, h.id as recordid "
                    $histories = getAdminHistory($db, $userID);
                    echo '<table id="logtable" border=1 align=center width=100%>';
                    echo '<tr><th align=center valign=center>ID</th><th align=center valign=center>Action</th><th align=center valign=center>Reason</th><th align=center valign=center>Duration</th><th align=center valign=center>Admin</th><th align=center valign=center>Date</th><th align=center valign=center>Appeal</th></tr>';
                    $count = 0;
                    foreach ($histories as $his) {
                        if ($his['action'] != "other") {
                            $count += 1;
                            echo '<tr><td align=center valign=center>';
                            echo $his['recordid'];
                            echo '</td><td align=center valign=center>';
                            echo $his['action'];
                            echo '</td><td align=center valign=center>';
                            echo $his['reason'];
                            echo '</td><td align=center valign=center>';
                            echo $his['duration'];
                            echo '</td><td align=center valign=center>';
                            echo $his['adminname'];
                            echo '</td><td align=center valign=center>';
                            echo $his['date'];
                            echo '</td><td align=center valign=center>';
                            if ($his['action'] == "other")
                                echo "Irrelevant";
                            else
                                echo '<input type="checkbox" name=appeals[] value="' . $his['recordid'] . ',' . $his['adminid'] . '" >';
                            echo '</td></tr>';
                        }
                    }
                    if ($count == 0) {
                        echo "<tr><td colspan=7><i>Your history is clean. Nothing to appeal.</i></td></tr>";
                    }
                    echo '</table><br>'
                    . '<input id="history_next_btn" type="button" value="Next" onclick="history_next(' . $_POST['type'] . '); return false;" >'
                    . '</div>';

                    echo '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="history_below">';

                    echo ' <table border=0><tr>
                            <td colspan="2"><b>How can we help? (in brief):</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="subject" type="text" maxlength="70" required style="width:500px" >
                            </td>
                            <td valign="top"><i>Please describe what you need in brief (one sentence).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Explain to us what exactly happened and why your history should be removed:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="content" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top"><i>Include chatlogs, screenshots, videos if any.</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Prove you are human:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <a href="" onclick="ajax_reload_captcha();
                                                        return false;"><div id="img_captcha"><img src="captcha/captcha.php" /></div></a>
                                <input type="text" id="captcha" placeholder="Enter the text above" maxlength="5" required style="width: 146px;" />
                            </td>
                            <td valign="top"><i></i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>
                        </tr></table>';
                    echo '</div> '
                    . '</td></tr>';
                    echo '<script>$("#history_below").hide();</script>';
                } else if ($_POST['type'] == 6) { //bug report
                    echo '<tr><td colspan="2">';
                    echo '<p><i>Report bugs you find within MTA, UCP or Forums here. Your feedback goes a long way towards making OwlGaming even better!</i></p>';
                    echo '<p><b>Where do you find the bug: </b> <select id="bug_where" onchange="load_bug_report_area();" >'
                    . '<option value="" selected></option>'
                    . '<option value="MTA Server">MTA Server</option>'
                    . '<option value="UCP">UCP</option>'
                    . '<option value="Forums">Forums</option>'
                    . '</select>'
                    . '</p>'
                    . '<div id="bug_report_area"></div> ';
                } else if ($_POST['type'] == 7) { //player report 
                    echo '<tr><td colspan="2">';
                    echo '<p><i>"Our aim is to provide an environment where rule breakers are disciplined accordingly for their actions." - OwlGaming Administration Team.</i></p>'
                    . '<p>As a player, you are entitled to a server with zero tolerance for rule breakers. As a result, this forum exists in order for players to report other players for breaking our server rules. Furthermore, our Administration Team review all reports professionally, quickly and have a non-biased approach.</p>'
                    . '<p>In order to maintain this environment, we require your assistance as a player. As it\'s apparent to all players, administrators are not omnipresent and do depend on your help.</p>'
                    . '<p>Before submitting your report, you hereby agree that:</p>'
                    . '<ul><li style="">Vexatiously reporting a player without sufficient evidence will result in the report being locked, disregarded and the original reporter being disciplined.</li><li style="">You will remain calm/tell the truth throughout the entirety of the report.</li><li style="">You are reporting a player because s/he has broken a server rule that cannot be dealt with in-game.</li><li style="">You have provided screenshots or chatlogs.</li><li style="">You contacted the player and informed them they\'re being reported.</li></ul>';
                    echo '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="add_reported_players">'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>List the players you\'re reporting (by character name): </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="reportedcharacters" type="text" maxlength="300" required style="width:500px" placeholder="Multiple character names separated by a comma.">'
                    . '</td>'
                    . '<td valign="top" align=left><i>For example: Christopher Clark, Minh Nguyen, Matthew Perry</i></td>'
                    . '<tr><td colspan=2>'
                    . '<input id="btn_add_reported_players" type="button" value="Add" onclick="load_player_report_forms(' . $_POST['type'] . ', \'btn_add_reported_players\'); return false;" >'
                    . '</table>'
                    . '</div>'
                    . '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="report_players_invovled">'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>List the players those were involved (by character name, optional): </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="involvedcharacters" type="text" maxlength="300" style="width:500px" placeholder="Multiple character names separated by a comma.">'
                    . '</td>'
                    . '<td valign="top" align=left></td>'
                    . '</tr>'
                    . '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<input id="btn_add_involved_players" type="button" value="Add" onclick="load_player_report_forms(' . $_POST['type'] . ', \'btn_add_involved_players\'); return false;" >'
                    . '</td></tr></table></div>'
                    . '
                        <tr>
                            <td colspan="2"><b>Date of incident:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="date" type="date" required autocomplete>
                            </td>
                            <td valign="top" align="left" width=100%><i>An estimated moment when players broke the rules.</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>What rules did these players break:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="rules_broken" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top" align="left" width=100%><i>List and quote rules from <a href="http://forums.owlgaming.net/forumdisplay.php?366-Rules" target="new">Server Rules</a></i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Explain your side of the story and list your evidence:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="story" style="width:500px; height:300px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top" align="left" width=100%><i>Elaborate on the incident and what your side of the situation is.<br>Provide at a minimum: screenshots or chatlogs or videos. (Screenshots or videos may result in your report being processed faster).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Prove you are human:</b></td>
                        </tr>
                        <tr>
                            <td colspan=2>
                                <a href="" onclick="ajax_reload_captcha();
                                                        return false;"><div id="img_captcha"><img src="captcha/captcha.php" /></div></a>
                                <input type="text" id="captcha" placeholder="Enter the text above" maxlength="5" required style="width: 146px;" />
                            </td>
                            
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>
                        </tr>
                        </table>';
                } else {
                    echo ' <tr>
                            <td colspan="2"><b>How can we help? (in brief):</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="subject" type="text" maxlength="70" required style="width:500px" >
                            </td>
                            <td valign="top"><i>Please describe what you need in brief (one sentence).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Please add any details that might help us help you:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="content" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top"><i>For example, what are you trying to do and what\'s happening? How could we possibly help you? What do you expect us to do?</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Prove you are human:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <a href="" onclick="ajax_reload_captcha();
                                                        return false;"><div id="img_captcha"><img src="captcha/captcha.php" /></div></a>
                                <input type="text" id="captcha" placeholder="Enter the text above" maxlength="5" required style="width: 146px;" />
                            </td>
                            <td valign="top"><i></i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>
                        </tr>';
                }
            }
        } else if ($step == "submit") {
            if (!isset($_SESSION['captcha']) or ! isset($_POST['captcha']) or strtolower($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
                echo "captcha";
            } else {
                $insert = array();
                $insert['private'] = $_POST['private'];
                $insert['type'] = $_POST['type'];
                if (isset($userID))
                    $insert['creator'] = $userID;
                else
                    $insert['creator'] = $_POST['email'];
                if (strlen($insert['creator']) > 0) {
                    $insert['subject'] = strip_tags($_POST['subject']);
                    $insert['content'] = nl2br($_POST['content']);
                    $tcid = $db->query_insert("tc_tickets", $insert);
                    if ($tcid) {
                        echo $tcid;
                    } else {
                        echo "error";
                    }
                    $laziestStaff = @getLaziestStaff($db, $_POST['type'], $_POST['assignto']);
                    if ($laziestStaff) {
                        $insert['id'] = $tcid;
                        $insert['assign_to'] = $laziestStaff['id'];
                        @assignTicket($db, $tcid, $laziestStaff['id'], $insert);
                    }
                } else {
                    echo "error";
                }
            }
        } else if ($step == "add_comment") {
            $commenter = $_POST['email'];
            if (!isset($commenter))
                $commenter = $userID;
            if (@addTicketComment($db, $_POST['tcid'], $commenter, nl2br($_POST['comment']), $_POST['internal'])) {
                echo $_POST['tcid'];
            }
        } else if ($step == "ajax_remove_admin_history") {
            $record = $_POST['record'];
            $data = explode("_", $_POST['record']);
            //echo '$data0 = '.$data[0];
            //echo '$data1 = '.$data[1];
            require_once '../functions/functions.php';
            if (isPlayerTrialAdmin($_SESSION['groups']) or isPlayerSupporter($_SESSION['groups'])) {
                if ((isset($data[1]) and $data[1] > 0 and $data[1] == $userID) or isPlayerSeniorAdmin($_SESSION['groups'])) {
                    if ($db->query("DELETE FROM adminhistory WHERE id=" . $db->escape($data[0]))) {
                        if ($db->affected_rows > 0) {
                            echo ".";
                        } else {
                            echo "This admin history record is already removed by someone else.";
                        }
                    } else {
                        echo "Could not remove this admin history record.\n\nInternal Error!";
                    }
                } else {
                    echo "You don't have sufficient permission to remove this admin history record.";
                }
            } else {
                echo "You don't have sufficient permission to remove this admin history record.";
            }
        } else if ($step == "list_tickets") {
            $start = $_POST['start'];
            if (strlen($start) > 0 and ! is_numeric($start)) {
                echo "Interal Error!";
            } else {
                if (!canUserAccessTcBackEnd($_SESSION['groups']) or ! isset($_SESSION['tc_backend']) or ! $_SESSION['tc_backend'] == 1) {
                    echo "You don't have sufficient permission to access this area.";
                } else {
                    $condition = "";
                    $condition = " WHERE 1=2 ";
                    $type = $_POST['type'];
                    $keyword = $_POST['keyword'];
                    if ($type == "id") {
                        if ($keyword and is_numeric($keyword) and $keyword > 0) {
                            $condition = " WHERE t.id=$keyword ";
                        }
                    } else if ($type == "type") {
                        $condition = " WHERE type=$keyword ";
                    } else if ($type == "status") {
                        $condition = " WHERE status=$keyword ";
                    } else if ($type == "assign_to") {
                        $condition = " WHERE assign_to=$keyword ";
                    } else if ($type == "creator") {
                        $condition = " WHERE creator=(SELECT id FROM accounts WHERE username LIKE '%" . $db->escape($keyword) . "%' LIMIT 1) ";
                    } else if ($type == "subcriber") {
                        $id = $db->query_first("SELECT id FROM accounts WHERE username LIKE '%" . $db->escape($keyword) . "%' LIMIT 1");
                        if ($id and $id['id'] and is_numeric($id['id'])) {
                            $condition = " WHERE subcribers LIKE '%," . $id['id'] . ",%' ";
                        }
                    } else if ($type == "date") {
                        if ($keyword == "Today") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=0 ";
                        } else if ($keyword == "Yesterday") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=1 ";
                        } else if ($keyword == "3 days ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=3 ";
                        } else if ($keyword == "1 week ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=7 ";
                        } else if ($keyword == "1 month ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=30 ";
                        } else if ($keyword == "3 month ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=90 ";
                        } else if ($keyword == "1 year ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=365 ";
                        }
                    } else if ($type == "subject") {
                        $condition = " WHERE subject LIKE '%" . $db->escape($keyword) . "%' ";
                    } else if ($type == "content") {
                        $condition = " WHERE content LIKE '%" . $db->escape($keyword) . "%' ";
                    } else if ($type == "custom") {
                        if ($keyword == "all") {
                            $condition = ' WHERE 1=1 ';
                        } else if ($keyword == 'active') {
                            $condition = ' WHERE 1=1 ';
                        } else if ($keyword == 'unassigned') {
                            $condition = ' WHERE assign_to=0 ';
                        } else if ($keyword == 'assigned') {
                            $condition = ' WHERE assign_to>0 ';
                        } else if ($keyword == 'closed') {
                            $condition = ' WHERE status=4 ';
                        } else if ($keyword == 'locked') {
                            $condition = ' WHERE status=-1 ';
                        } else {
                            $condition = ' WHERE 1=2 ';
                        }
                    }
                    $close = $_POST['close'];
                    $lock = $_POST['lock'];
                    if ($type != "status") {
                        if (!isset($close) or ! $close or $close == 0) {
                            $condition .= " AND status!=4 ";
                        }
                        if (!isset($lock) or ! $lock or $lock == 0) {
                            $condition .= " AND status!=-1 ";
                        }
                    }

                    $sql = "SELECT a.username AS creatorname, b.username AS assigneename, status, t.id, type, subject, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS fdate, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id LEFT JOIN accounts b ON t.assign_to=b.id  " . $condition . "  ORDER BY t.id DESC";
                    //echo $sql;
                    //echo "<br>type: $type";
                    $eu = ($start - 0);
                    $limit = $_POST['limit'];                                 // No of records to be shown per page.
                    $this1 = $eu + $limit;
                    $back = $eu - $limit;
                    $next = $eu + $limit;
                    /////////////// Total number of records in our table. We will use this to break the pages///////
                    $nume = $db->query_first("SELECT COUNT(id) AS count FROM tc_tickets t " . $condition)['count'];
                    if ($nume > 0) {
                        /////// The variable nume above will store the total number of records in the table////
                        echo '<table id="logtable" border="1" align="center" width="100%">';
                        echo "<tr>"
                        . "<th align=center valign=center>Status</th>"
                        . "<th align=center valign=center>ID</th>"
                        . "<th align=center valign=center>Type</th>"
                        . "<th align=center valign=center>Subject</th>"
                        . "<th align=center valign=center>Creator</th>"
                        . "<th align=center valign=center>Assigned to</th>"
                        . "<th align=center valign=center>Date</th>"
                        . "</tr>";
                        $query = $db->query($sql . " LIMIT $eu, $limit ");
                        $i = 0;



                        while ($myticket = $db->fetch_array($query)) {
                            $i = $i + 1;   //  increment for alternate color of rows
                            echo "<tr ><td align=center valign=center>" . getTicketStatus($myticket['status'], $myticket['id']) . "</td><td align=center valign=center><a href='#' onclick='load_ticket(" . $myticket['id'] . "); return false;'>#" . $myticket['id'] . "</a></td><td align=center valign=center>" . getTicketType($myticket['type']) . "</td><td align=center valign=center>" . trimSubject($myticket['subject']) . "</td><td align=center valign=center>" . $myticket['creatorname'] . "</td><td align=center valign=center>" . $myticket['assigneename'] . "</td><td align=center valign=center>" . $myticket['fdate'] . " (" . formatDays($myticket['dateago']) . ")</td></tr>";
                        }
                        $db->free_result();
                        $loadto = $_POST['loadto'];
                        echo "<tr><td align=center colspan=7><i>$nume ticket(s) found.</i></td></tr>";
                        if ($nume > $limit) {
                            echo "<tr><td colspan=7>";
                            echo "<table align = 'center' width='100%'><tr><td  align='left' width='30%'>";
                            if ($back >= 0) {
                                ?>
                                <a href='#' onclick="ticket_search_load_results('<?php echo $back ?>', '<?php echo $limit ?>', '<?php echo $type ?>', '<?php echo $keyword ?>', '<?php echo $close ?>', '<?php echo $lock ?>', '<?php echo $loadto ?>');
                                                                return false;"><b>PREV</b></a>
                                   <?php
                               }
                               echo "</td><td align=center width='30%'>";
                               $i = 0;
                               $l = 1;
                               for ($i = 0; $i < $nume; $i = $i + $limit) {
                                   if ($i <> $eu) {
                                       ?>
                                    <a href='#' onclick="ticket_search_load_results('<?php echo $i ?>', '<?php echo $limit ?>', '<?php echo $type ?>', '<?php echo $keyword ?>', '<?php echo $close ?>', '<?php echo $lock ?>', '<?php echo $loadto ?>');
                                                                        return false;"><b><?php echo $l; ?></b></a>
                                       <?php
                                   } else {
                                       echo "<b>$l</b>";
                                   }        /// Current page is not displayed as link and given font color red
                                   $l = $l + 1;
                               }
                               echo "</td><td  align='right' width='30%'>";
                               if ($this1 < $nume) {
                                   ?>
                                <a href='#' onclick="ticket_search_load_results('<?php echo $next ?>', '<?php echo $limit ?>', '<?php echo $type ?>', '<?php echo $keyword ?>', '<?php echo $close ?>', '<?php echo $lock ?>', '<?php echo $loadto ?>');
                                                                return false;"><b>NEXT</b></a>
                                   <?php
                               }
                               echo "</td></tr></table></td></tr>";
                           }

                           echo "</table>";
                           echo "<br><center><input value='Hide' type='button' onclick=\"$('#$loadto').html(''); return false;\"></center><br><br>";
                       } else {
                           echo "<center><i>No ticket found.</i></center>";
                       }
                   }
               }
           } else if ($step == "load_ticket_comments") {
               $tcid = $_POST['tcid'];
               $start = $_POST['start'];
               $limit = $_POST['limit'];
               $loadto = $_POST['loadto'];
               $tc = $db->query_first("SELECT t.private, t.status, t.assign_to, t.type, t.subcribers, t.creator FROM tc_tickets t WHERE t.id=" . $db->escape($tcid));
               if ($tc) {
                   if (canUserViewTicket($userID, $tc)) {
                       require_once '../functions/base_functions.php';
                       $condition1 = ' internal=0 AND ';
                       if (canUserAccessTcBackEnd($_SESSION['groups']) and isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                           $condition1 = '';
                       }
                       $eu = ($start - 0);                               // No of records to be shown per page.
                       $this1 = $eu + $limit;
                       $back = $eu - $limit;
                       $next = $eu + $limit;
                       /////////////// Total number of records in our table. We will use this to break the pages///////
                       $nume = $db->query_first("SELECT COUNT(id) AS count FROM tc_comments WHERE tcid=".$tcid." ". $condition)['count'];
                       if ($nume > 0) {
                           $sql = "SELECT c.internal, c.comment, c.id, c.poster, a.username AS postername, DATE_FORMAT(c.date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), c.date) AS dateago FROM tc_comments c LEFT JOIN accounts a ON c.poster=a.id WHERE " . $condition1 . " tcid=" . $tcid . " ORDER BY id DESC";
                           $comments = $db->fetch_all_array($sql . " LIMIT $eu, $limit ");
                           $i = 0;
                           for ($index = count($comments)-1 ; $index >= 0 ; $index--){
                               $i = $i + 1;
                               //echo $index;
                               $comment = $comments[$index];
                               echo '<div style="border:1px solid #3D3D3D;margin-bottom: 5px">';
                               if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                                   if ($_SESSION['userid'] == $comment['poster']) {
                                       if ($comment['internal'] == 1) {
                                           echo '<div style="color:white;border-bottom:1px solid #A63D3D;text-align:right;background: #A63D3D;padding:2px;">'
                                           . 'You answered on ' . $comment[date] . ' (' . formatDays($comment['dateago']) . ') [INTERNAL]</div>';
                                       } else {
                                           echo '<div style="color:white;border-bottom:1px solid #3F3E3E;text-align:right;background: #3F3E3E;padding:2px;">'
                                           . 'You answered on ' . $comment[date] . ' (' . formatDays($comment['dateago']) . ')</div>';
                                       }
                                   } else {
                                       echo '<div style="color:white;border-bottom:1px solid #838383;text-align:right;background: #838383;padding:2px;">';
                                       if (!is_null($comment['postername']))
                                           echo $comment['postername'];
                                       elseif ($comment['poster'])
                                           echo $comment['poster'];
                                       else
                                           echo "Someone";
                                       echo ' responded on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ')' . $internalText . '</div>';
                                   }
                               } else {
                                   if ($_SESSION['userid'] == $comment['poster']) {
                                       echo '<div style="color:white;border-bottom:1px solid #3F3E3E;text-align:right;background: #3F3E3E;padding:2px;">'
                                       . 'You responded on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ')</div>';
                                   } else {
                                       echo '<div style="color:white;border-bottom:1px solid #838383;text-align:right;background: #838383;padding:2px;">';
                                       if (!is_null($comment['postername']))
                                           echo $comment['postername'];
                                       elseif ($comment['poster'])
                                           echo $comment['poster'];
                                       else
                                           echo "Someone";
                                       echo ' answered on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ')</div>';
                                   }
                               }

                               echo '<div style="text-align:left;padding:2px;">' . make_clickable($comment['comment']) . '</div></div>';
                           }
                           //$db->free_result();
                           if ($nume > $limit) {
                            echo "<table align = 'center' border=0 width='100%'><tr><td  align='left' width='30%'>";
                            if ($back >= 0) {
                                ?>
                                <a href='#' onclick="load_ticket_comments('<?php echo $tcid; ?>', '<?php echo $back; ?>', '<?php echo $limit; ?>', '<?php echo $loadto; ?>');
                                                                return false;"><b>NEXT</b></a>
                                   <?php
                               }
                               echo "</td><td align=center width='30%'>";
                               $i = 0;
                               $l = 1;
                               for ($i = 0; $i < $nume; $i = $i + $limit) {
                                   if ($i <> $eu) {
                                       ?>
                                    <a href='#' onclick="load_ticket_comments('<?php echo $tcid; ?>','<?php echo $i; ?>', '<?php echo $limit; ?>','<?php echo $loadto; ?>');
                                                                        return false;"><b><?php echo $l; ?></b></a>
                                       <?php
                                   } else {
                                       echo "<b>$l</b>";
                                   }        /// Current page is not displayed as link and given font color red
                                   $l = $l + 1;
                               }
                               echo "</td><td  align='right' width='30%'>";
                               if ($this1 < $nume) {
                                   ?>
                                <a href='#' onclick="load_ticket_comments('<?php echo $tcid; ?>','<?php echo $next; ?>', '<?php echo $limit; ?>','<?php echo $loadto; ?>');
                                                                return false;"><b>PREV</b></a>
                                   <?php
                               }
                               echo "</td></tr></table>";
                           }
                       }
                   }
               }
           } else if (isset($_GET['tcid'])) {
               $tc = $db->query_first("SELECT t.private, t.status, t.assign_to, t.type, t.subcribers, t.creator, t.subject, t.content, DATE_FORMAT(t.date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), t.date) AS dateago, t.id ,a.username AS creatorname, b.username AS assignee FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id LEFT JOIN accounts b ON t.assign_to=b.id WHERE t.id=" . $db->escape($_GET['tcid']));
               if ($tc) {
                   if (canUserViewTicket($userID, $tc)) {
                       echo "<h2>You are viewing ticket #" . $tc['id'] . " - " . getTicketType($tc['type']) . "</h2>"
                       ?>

                    <table border="0" width="100%">
                        <tr>
                            <td valign="top" width="50%">
                                <ul>
                                    <li><b>Creator: </b><?php
                                        if ($tc['creatorname'])
                                            echo $tc['creatorname'];
                                        else
                                            echo $tc['creator'];
                                        ?></li>
                                    <li><b>Creation Date: </b><?php echo $tc['date'] . " (" . formatDays($tc['dateago']) . ")"; ?></li>
                                    <li><b>Type: </b><?php echo getTicketType($tc['type']); ?></li>
                                    <li><b>Subject: </b><?php echo trimSubject($tc['subject'], 60); ?></li>

                                </ul>
                            </td>
                            <td valign="top">
                                <ul>
                                    <li><b>Status: </b><?php echo getTicketStatus($tc['status'], $tc['id'], true, $tc['type'], $tc['private']); ?>
                                    </li>
                                    <li><b>Assigned to: </b><?php
                                        if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1 and $tc['status'] != -1 and $tc['status'] != 4) {
                                            require_once '../functions/functions.php';
                                            $staffs = getAllStaffs($db);
                                            echo '<select id="reassign_ticket" onchange="reassign_ticket(' . $tc['id'] . ');">';
                                            echo '<option value="0" selected>No-one (Unassign)</option>';
                                            foreach ($staffs as $staff) {
                                                $selected = '';
                                                if ($tc['assign_to'] == $staff['id'])
                                                    $selected = 'selected';
                                                echo '<option value="' . $staff['id'] . '" ' . $selected . '>' . getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username'] . '</option>';
                                            }
                                            echo '</select>';
                                        } else {
                                            if ($tc['assignee'])
                                                echo $tc['assignee'];
                                            else
                                                echo "No-one";
                                        }
                                        ?></li>

                                    <?php
                                    echo "<li><b>Subscribers:</b>";
                                    if ($tc['subcribers'] and strlen($tc['subcribers']) > 0) {
                                        $subs = explode(",", $tc['subcribers']);

                                        $tail = '';
                                        foreach ($subs as $sub) {
                                            if ($sub and is_numeric($sub)) {
                                                $tail .= " " . $db->query_first("SELECT id, username, email FROM accounts WHERE id=" . $sub)['username'] . ",";
                                            }
                                        }
                                        $tail = substr($tail, 0, -1);
                                        echo $tail;
                                    } else {
                                        echo " None";
                                    }

                                    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1 and $tc['status'] != -1 and $tc['status'] != 4) {
                                        echo " | <a href='#' onclick='add_subcriber(" . $tc['id'] . "); return false;' >Add</a>";
                                    }
                                    echo "</li>";
                                    ?>
                                    <li><b>URL: </b><?php
                                        $currentUrl = $currentUrl . "/support.php?tcid=" . $tc['id'];
                                        echo "<a href='" . $currentUrl . "'>" . $currentUrl . "</a>";
                                        ?></li>
                                    <ul>
                                        </td>
                                        </tr>
                                        </table>
                                        <div style="border:1px solid #3F3E3E;margin-bottom: 15px">
                                            <div style="color:white;border-bottom:1px solid #3F3E3E;text-align:right;background: #3F3E3E;padding:2px;">Issue started by <?php
                                                if ($tc['creatorname'])
                                                    echo $tc['creatorname'];
                                                else
                                                    echo $tc['creator'];
                                                require_once '../functions/base_functions.php';
                                                ?> on <?php echo $tc['date'] . " (" . formatDays($tc['dateago']) . ")"; ?></div>
                                            <div style="text-align:left;padding:2px;"><?php echo "<b><i>" . strip_tags($tc['subject']) . "</i></b><hr>" . make_clickable($tc['content']); ?></div>
                                        </div>
                                        <?php
                                        echo '<div id="comments"><center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center></div>'
                                        . '<script>load_ticket_comments(' . $tc['id'] . ',0,10);</script>';
                                        if ($tc['status'] >= 0) {
                                            if ($tc['type'] == 6 and ! canUserCommentOnBugReport($userID, $tc)) {
                                                echo "<i>You don't have sufficient permission to comment on this bug report.</i>";
                                            } else {
                                                $placeholder = "Comment on this ticket...";
                                                if ($condition1 == '') { // tc backend
                                                    $placeholder = "Answer this ticket...";
                                                    if ($tc['status'] == 0) { // open
                                                        if ($tc['assign_to'] == 0) {
                                                            $placeholder = "Comment on this ticket may also auto-assign it to you...";
                                                        }
                                                    } else if ($tc['status'] == 4) { // closed
                                                        $placeholder = "Comment on this ticket may also automatically re-open the ticket...";
                                                    }
                                                } else {
                                                    if ($tc['status'] == 4) { // closed
                                                        $placeholder = "Comment on this ticket may also automatically re-open the ticket...";
                                                    }
                                                }
                                                ?>
                                                <form action="" onsubmit="client_add_comment('<?php echo $tc['id']; ?>');
                                                                            return false;">
                                                    <textarea id="comment" style="width:954px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required placeholder='<?php echo $placeholder; ?>' ></textarea>

                                                    <input type="submit" id="btn_add_comment" value="Add">
                                                    <?php
                                                    if ($condition1 == '') { // if tc backend
                                                        echo "<input type='checkbox' id='internal' value='Internal Note'/> Make internal (Only staff members can read)";
                                                    } else {
                                                        if (!is_numeric($tc['creator']) or ( !isset($userID))) {
                                                            echo '<input id="email" type="email" maxlength="200" required style="width:500px" placeholder="Please enter your email address">';
                                                        }
                                                    }
                                                    ?>
                                                </form>

                                                <?php
                                            }
                                        } else {
                                            echo "<i>This ticket is locked and archived. Commenting is not possible.</i>";
                                        }
                                    } else {
                                        echo "<br><br><center><i>You don't have sufficient permission to view this ticket.</i></center>";
                                    }
                                } else {
                                    echo "Opps, sorry! The ticket you're looking for does not exist!";
                                }
                            } else {
                                
                            }
                            $db->close();

                            function trimSubject($subj, $length = 32) {
                                $subj = strip_tags($subj);
                                if ($subj and strlen($subj) > 0) {
                                    if (strlen($subj) > $length) {
                                        return substr($subj, 0, $length) . "[..]";
                                    } else {
                                        return $subj;
                                    }
                                } else {
                                    return "N/A";
                                }
                            }
                            ?>