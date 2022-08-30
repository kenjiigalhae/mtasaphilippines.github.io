<?php
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
if (isset($_POST['type']) and isset($userID)) {
    if ($_POST['type'] == 2) { // unban
        if ($_POST['type2'] == "Forums") {
            ?>
            <tr>
                <td colspan="2">
                    <b>Forums account:</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input id="subject" type="text" maxlength="70" required style="width:500px" >
                </td>
                <td valign="top">
                </td>
            </tr>
            <tr>
                <td colspan="2"><b>Explain your side of the story and why you should be unbanned:</b></td>
            </tr>
            <tr>
                <td>
                    <textarea id="content" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                </td>
                <td valign="top"><i>Provide us with your side of the story, as well as a very good reason as to why you deserve another chance.</i></td>
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
            </tr>
            <?php
        } else if ($_POST['type2'] == "MTA") {
            require_once '../classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT id, username, email, ip, mtaserial FROM accounts WHERE id=" . $userID . " LIMIT 1");
            $ban = $db->query_first("SELECT b.ip, b.threadid, b.serial, b.reason, b.id, c.username AS banneduser, d.username AS banninguser, b.admin, DATE_FORMAT(b.date,'%b %d, %Y at %h:%i %p') AS date FROM bans b LEFT JOIN accounts c ON b.account=c.id LEFT JOIN accounts d ON b.admin=d.id WHERE b.account=" . $user['id'] . " OR b.ip='" . $db->escape($user['ip']) . "' OR b.serial='" . $db->escape($user['mtaserial']) . "' LIMIT 1");
            if ($ban and is_numeric($ban['id'])) {
                echo "<tr><td colspan=2>";
                $text .= '<b>Ban Record #' . $ban['id'] . ' Found:</b><br>'
                        . '<i><ul>';
                if (is_null($ban['banneduser']))
                    $text .= '<li>Account: N/A</li>';
                else
                    $text .= '<li>Account: ' . $ban['banneduser'] . "</li>";
                if (is_null($ban['serial']))
                    $text .= '<li>Serial: N/A</li>';
                else
                    $text .= '<li>Serial: ' . $ban['serial'] . "</li>";
                if (is_null($ban['ip']))
                    $text .= '<li>IP: N/A</li>';
                else
                    $text .= '<li>IP: ' . $ban['ip'] . "</li>";
                if (is_null($ban['banninguser']))
                    $text .= '<li>Banning Admin: N/A</li>';
                else
                    $text .= '<li>Banning Admin: ' . $ban['banninguser'] . "</li>";
                if (is_null($ban['date']))
                    $text .= '<li>Date: N/A</li>';
                else
                    $text .= '<li>Date: ' . $ban['date'] . "</li>";
                if (is_null($ban['reason']))
                    $text .= '<li>Reason: N/A</li>';
                else
                    $text .= '<li>Reason: ' . $ban['reason'] . "</li>";
                if (is_null($ban['threadid']))
                    $text .= '<li>Ban Thread: N/A</li>';
                else
                    $text .= "<li>Ban Thread: http://forums.owlgaming.net/showthread.php/" . $ban['threadid'] . " </li>";
                $text .= "</ul></i>";
                echo $text;
                ?>
                <input id="banrecord" type="hidden" value="<?php echo $text; ?>" >
                <input id="subject" type="hidden" value="<?php echo "MTA - " . $user['username']; ?>" >
                <input id="assignto" type="hidden" value="<?php echo $ban['admin']; ?>" >
                </td>
                </tr>
                <tr>
                    <td colspan="2"><b>Explain your side of the story and why you should be unbanned:</b></td>
                </tr>
                <tr>
                    <td>
                        <textarea id="content" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                    </td>
                    <td valign="top"><i>Provide us with your side of the story, as well as a very good reason as to why you deserve another chance.</i></td>
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
                </tr>
                <?php
            } else {
                require_once '../functions/functions_tickets.php';
                echo "<i>We couldn't find any ban record on your MTA game account. If you're having troubles connection or loggin in to MTA server, please use <a href='#' onclick='load_submit_form(1); return false;'>" . getTicketType(1) . " form</a> instead.</i>";
            }
            $db->close();
        }
    } else if ($_POST['type'] == "search_ticket_by") {
        $limit = 3;
        $start = 0;
        if ($_POST['type2'] == "id") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Ticket ID:</b></td>
                        <td>
                            <input id="search_ticket_keyword" type="text" maxlength="100" required placeholder="Enter Ticket ID" style="width:200px;">  
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "status") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Status:</b></td>
                        <td>
                            <select id="search_ticket_keyword" style="width:200px;">
                                <option value="0" selected>Open</option>
                                <option value="1">Assigned</option>
                                <option value="2">Answered</option>
                                <option value="3">Responded</option>
                                <option value="4">Closed</option>
                                <option value="-1">Locked</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <!--
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                -->
                <br>

                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "assign_to") {
            require_once '../functions/functions.php';
            require_once '../functions/functions_tickets.php';
            $staffs = getAllStaffs();
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Assignee:</b></td>
                        <td>
                            <select id="search_ticket_keyword" style="width:200px;">
                                <option value="0" selected>No-one</option>
                                <?php
                                foreach ($staffs as $staff) {
                                    ?>
                                    <option value="<?php echo $staff['id']; ?>" selected><?php echo getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>

                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "creator") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Creator:</b></td>
                        <td>
                            <input id="search_ticket_keyword" type="text" maxlength="100" required placeholder="Enter creator username" style="width:200px;">  
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "subcriber") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Subscriber:</b></td>
                        <td>
                            <input id="search_ticket_keyword" type="text" maxlength="100" required placeholder="Enter subscriber username" style="width:200px;">  
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "date") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Date til present:</b></td>
                        <td>
                            <select id="search_ticket_keyword" style="width:200px;">
                                <option value="Today">Today</option>
                                <option value="Yesterday">Yesterday</option>
                                <option value="3 days ago">3 days ago</option>
                                <option value="1 week ago">1 week ago</option>
                                <option value="1 month ago">1 month ago</option>
                                <option value="3 month ago">3 month ago</option>
                                <option value="1 year ago">1 year ago</option>
                            </select> 
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "subject") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Subject:</b></td>
                        <td>
                            <input id="search_ticket_keyword" type="text" maxlength="100" required placeholder="Enter partial subject" style="width:200px;">  
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "content") {
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Content:</b></td>
                        <td>
                            <input id="search_ticket_keyword" type="text" maxlength="100" required placeholder="Enter partial content" style="width:200px;">  
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        } else if ($_POST['type2'] == "type") {
            require_once '../functions/functions_tickets.php';
            ?>
            <form id="form_search_ticket" onsubmit="ticket_search_start('<?php echo $_POST['type2']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td width="100"><b>Type:</b></td>
                        <td>
                            <select id="search_ticket_keyword" style="width:200px;">
                                <?php
                                $ticketTypes = getTicketTypes();
                                foreach ($ticketTypes as $id => $name) {
                                    echo '<option value="' . $id . '">' . $name . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <br>
                <input type='checkbox' id='chk_closed' /> <i>Include closed tickets</i><br>
                <input type='checkbox' id='chk_locked' /> <i>Include locked tickets</i><br>
                <br>
                <input id="btn_search" value="Search" type="submit"> 
            </form>
            <?php
        }
    } else if ($_POST['type'] == "load_bug_report_area") {
        if ($_POST['type2'] != "") {
            ?>

            <table>
                <?php if ($_POST['type2'] == "MTA Server") { ?>
                    <tr>
                        <td>
                            <b>Category: </b>
                        </td>
                        <td>
                            <select id="bug_cate" style="width:704px;">
                                <option value="" selected>General</option>
                                <option value="" selected>Vehicle System</option>
                                <option value="" selected>Interior System</option>
                                <option value="" selected>Admin System</option>
                                <option value="" selected>Faction System</option>
                                <option value="" selected>Mapping</option>
                                <option value="" selected>Player, Shop or NPC</option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>
                        <b>Summary: </b>
                    </td>
                    <td>
                        <input id="bug_sum" type="text" required maxlength="100" style="width:700px;font: inherit;" placeholder="Describe the bug briefly in one sentence">
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <b>Reproduction: </b>
                    </td>
                    <td>
                        <textarea id="bug_steps" style="width:698px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required placeholder="How exactly to re-create the bug in specified steps."></textarea>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <b>Notes: </b>
                    </td>
                    <td>
                        <textarea id="bug_notes" style="width:698px; height:100px; font: inherit; resize: vertical;" maxlength="5000" placeholder="Provide any futher details or material you may have about the bug that can help developers to fix it (optional)."></textarea>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <b>Private: </b>
                    </td>
                    <td>
                        <input type='checkbox' id='bug_private' /> <i>Make this report private (Only developers and you can see).</i><br>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        <b>Verification:</b>
                    </td>


                    <td>
                        <a href="" onclick="ajax_reload_captcha();
                                            return false;"><div id="img_captcha"><img src="captcha/captcha.php" /></div></a>
                        <input type="text" id="captcha" placeholder="Enter the text above" maxlength="5" required style="width: 146px;" />
                    </td>

                </tr>
            </table>
            <br>
            <input id='btn_submit_ticket' value='Create' type='submit'>


            <?php
        }
    } else if ($_POST['type'] == 7) { // report player
        $guiElement = $_POST['type2'];
        if ($guiElement) { // Validate reported characters
            $charsText = $_POST['chars'];
            //die($charsText);
            if (!$charsText or strlen($charsText) < 1) {
                echo "Please list the players you're being $guiElement (by character name).";
            } else {
                $chars = explode(",", $charsText);
                $reportedPlayers = array();
                if (count($chars) > 0) {
                    require_once '../classes/mysql/Database.class.php';
                    $db = new Database("MTA");
                    $db->connect();

                    function isExisted($key, $array) {
                        foreach ($array as $element) {
                            if ($element['username'] == $key['username']) {
                                return $element['charactername'];
                            }
                        }
                    }

                    $noError = true;
                    $accountIds = array();
                    foreach ($chars as $char) {
                        $char = trim($char);
                        if ($char and strlen($char) > 0) {
                            $charFixed = str_replace(" ", "_", $char);
                            $found = @$db->query_first("SELECT a.id AS aid, a.username, c.id AS cid, c.charactername FROM accounts a LEFT JOIN characters c ON a.id=c.account WHERE c.charactername = '" . $db->escape($charFixed) . "' LIMIT 1");
                            if ($found and ! is_null($found['username'])) {
                                $existed = isExisted($found, $reportedPlayers);
                                if ($existed) {
                                    echo "You have input 2 character names ('" . str_replace("_", " ", $found['charactername']) . "' and '" . str_replace("_", " ", $existed) . "') those are found in the same account '" . $found['username'] . "'.\n\nRemove one of them from the list and try again.";
                                    $noError = false;
                                    break;
                                } else {
                                    array_push($reportedPlayers, $found);
                                    array_push($accountIds, $found['aid']);
                                }
                            } else {
                                echo "Cound't find any account that has character matched '" . $char . "'.\n\nPlease check your input and try again.";
                                $noError = false;
                                break;
                            }
                        }
                    }
                    if (count($reportedPlayers) > 0 and $noError) {
                        echo '<div id="' . $guiElement . '_text">'
                        . '<table><tr><td colspan=2>'
                        . '<b>The players are being ' . $guiElement . ': </b>'
                        . '<input id="' . $guiElement . '_accounts" type="hidden" value="' . implode(",", $accountIds) . '">'
                        . '<ul>';
                        foreach ($reportedPlayers as $reported) {
                            echo "<li>" . str_replace("_", " ", $reported[charactername]) . " ($reported[username])</li>";
                        }
                        echo '</ul>'
                        . '</td></tr>'
                        . '</tr>'
                        . '</table>'
                        . '</div>'
                        . '';

                        //echo implode(",", $accountIds);
                    } else {
                        echo "An error occurred while searching for accounts.\n\nPlease check your input and try again.";
                    }
                    $db->close();
                } else {
                    echo "Please list the players (by character name).";
                }
            }
        }
    } else if ($_POST['type'] == 8) {
        if ($_POST['type2'] == "check_history") {
            $appeals = $_POST['histories'];
            echo "<b>History records to be appealed:</b>"
            . "<ul>";
            require_once '../classes/mysql/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $prefer_admin = false;
            foreach ($appeals as $appeal) {
                $record = explode(",", $appeal);
                if (!$prefer_admin) {
                    $prefer_admin = $record[1];
                    echo '<input type="hidden" id="prefer_admin" value="'.$prefer_admin.'" >';
                }
                $his = $db->query_first("SELECT action, reason, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, h.duration, h.id, a.username AS adminname, c.charactername AS user_char "
                        . "FROM adminhistory h "
                        . "LEFT JOIN accounts a ON h.admin=a.id "
                        . "LEFT JOIN characters c ON h.user_char=c.id "
                        . "WHERE h.id=" . $record[0]);
                echo '<li>Record #' . $his['id'] . ' by ' . $his['adminname'] . ' | <input id="btn_remove_adm_history_'.implode("_", $record).'" onclick="remove_ahistory(\''.implode("_", $record).'\');" value="Remove" type="button"></li>';
                //echo '<li>';

                echo '<ul>';

                echo '<li><i>';
                echo 'Character: ' . $his['user_char'];
                echo '</i></li>';

                echo '<li><i>';
                echo 'Duration: ' . $his['duration'] . ' minute(s)';
                echo '</i></li>';
                $actions = array(0 => "jail", 1 => "kick", 2 => "ban", 3 => "app", 4 => "warn", 5 => "autoban", 6 => "other", 99 => "force-app");
                echo '<li><i>';
                echo 'Type: ' . $actions[$his['action']];
                echo '</i></li>';

                echo '<li><i>';
                echo 'Date: ' . $his['date'];
                echo '</i></li>';

                echo '<li><i>';
                echo 'Reason: ' . $his['reason'] . '';
                echo '</i></li>';

                echo '</ul>';

                //echo '</li>';
            }
            echo "</ul>";
            $db->close();
            
        }
    }
}
?>


