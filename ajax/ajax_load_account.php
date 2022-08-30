<?php
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
        require_once("../functions/functions_player.php");
        if (!canUserAccessPlayerManager($perms)) {
            die("<center><h3>You don't access to this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
        } else {
            if (isset($_POST['userid'])) {
                require_once '../functions/functions.php';
                getAccountInfo($_POST['userid'], false);
                echo '<br><br><br><br><br><br><br><br>';
                if (canUserManageAdminTeam($perms)) {
                    require_once '../classes/mysql/Database.class.php';
                    $db = new Database("MTA");
                    $db->connect();
                    $data = $db->query_first("SELECT admin, supporter, vct, scripter, mapper FROM accounts WHERE id=" . $_POST['userid']);
                    $db->close();
                    echo '<h2>Staff positions:</h2>';
                    ?>
                    <table id="logtable" border="1" align=center width="100%">
                        <tr>
                            <td align=center><b>Administration Team</b></td><td align=center ><b>Supporter Team</b></td><td align=center><b>Vehicle Consultation Team</b></td><td align=center><b>Development Team</b></td><td align=center><b>Mapping Team</b></td>
                        </tr>
                        <tr>
                            <td align=center>
                                <select id="s_admin" style="width:100%;" onchange="update_admin(<?php echo $_POST['userid']; ?>);">
                                    <option value = "4" <?php if ($data['admin'] == 4) echo 'selected'; ?>>Lead Administrator</option>
                                    <option value = "3" <?php if ($data['admin'] == 3) echo 'selected'; ?>>Senior Administrator</option>
                                    <option value = "2" <?php if ($data['admin'] == 2) echo 'selected'; ?>>Administrator</option>
                                    <option value = "1" <?php if ($data['admin'] == 1) echo 'selected'; ?>>Trial Administrator</option>
                                    <option value = "0" <?php if ($data['admin'] == 0) echo 'selected'; ?>>None</option>
                                </select>
                            </td>
                            <td align=center >
                                <select id="s_supporter" style="width:100%;" onchange="update_admin(<?php echo $_POST['userid']; ?>);">
                                    <option value = "1" <?php if ($data['supporter'] == 1) echo 'selected'; ?>>Supporter</option>
                                    <option value = "0" <?php if ($data['supporter'] == 0) echo 'selected'; ?>>None</option>
                                </select>
                            </td>
                            <td align=center>
                                <select id="s_vct" style="width:100%;" onchange="update_admin(<?php echo $_POST['userid']; ?>);">
                                    <option value = "2" <?php if ($data['vct'] == 2) echo 'selected'; ?>>VCT Leader</option>
                                    <option value = "1" <?php if ($data['vct'] == 1) echo 'selected'; ?>>VCT Member</option>
                                    <option value = "0" <?php if ($data['vct'] == 0) echo 'selected'; ?>>None</option>
                                </select>
                            </td>
                            <td align=center>
                                <select id="s_scripter" style="width:100%;" onchange="update_admin(<?php echo $_POST['userid']; ?>);">
                                    <option value = "3" <?php if ($data['scripter'] == 3) echo 'selected'; ?>>Scripter</option>
                                    <option value = "2" <?php if ($data['scripter'] == 2) echo 'selected'; ?>>Trial Scripter</option>
                                    <option value = "1" <?php if ($data['scripter'] == 1) echo 'selected'; ?>>Script Tester</option>
                                    <option value = "0" <?php if ($data['scripter'] == 0) echo 'selected'; ?>>None</option>
                                </select>
                            </td>
                            <td align=center>
                                <select id="s_mapper" style="width:100%;" onchange="update_admin(<?php echo $_POST['userid']; ?>);">
                                    <option value = "2" <?php if ($data['mapper'] == 2) echo 'selected'; ?>>Mapping Team Leader</option>
                                    <option value = "1" <?php if ($data['mapper'] == 1) echo 'selected'; ?>>Mapping Team Member</option>
                                    <option value = "0" <?php if ($data['mapper'] == 0) echo 'selected'; ?>>None</option>
                                </select>
                            </td> 
                        </tr>
                    </table>
                    <?php
                }
            } else {
                die("<center><h3>Internal Error!<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
            }
            ?>

            <?php
        }
    }
}