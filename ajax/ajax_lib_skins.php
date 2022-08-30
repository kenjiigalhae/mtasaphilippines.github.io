<?php
if (isset($_GET['part'])) {
    if ($_GET['part'] == 'top') {
        ?>
        <hr>
        <ul>
            <li>
                <a href="" onclick="ajax_load_skin_content('small');
                                return false;">Small version</a>
            </li>
            <li>
                <a href="" onclick="ajax_load_skin_content('medium');
                                return false;">Medium version</a>
            </li>
            <li>
                <a href="" onclick="ajax_load_skin_content('large');
                                return false;">Large version</a>
            </li>
        </ul>
    <?php } else if ($_GET['part'] == 'content') {
        ?>
        <hr>
        <table align="center" border="0" width=100%><tr>
                <?php
                if ($_GET['size'] == "small") {
                    $rowpos = 1;
                    //while ($row = mysql_fetch_assoc($charQuery))
                    for ($x = 0; $x <= 288; $x++) {
                        if (($x > 0) and ( $x < 7)) {
                            
                        } elseif (($x == 8) or ( $x == 42) or ( $x == 65) or ( $x == 74) or ( $x == 86) or ( $x == 119) or ( $x == 149) or ( $x == 208)) {
                            
                        } elseif (($x > 264) and ( $x < 274)) {
                            
                        } else {
                            if ($rowpos == 14) {
                                echo "								</tr>\r\n";
                                echo "								<tr>\r\n";

                                $rowpos = 1;
                            }
                            ?>
                            <td align="center"><img src="../images/MTA_skins/Skin_<?php echo $x ?>.png"><BR /><b>ID: <?php echo $x ?></b></td><?php
                            $rowpos++;
                        }
                    }
                } else if ($_GET['size'] == 'medium') {
                    $rowpos = 1;
                    //while ($row = mysql_fetch_assoc($charQuery))
                    for ($x = 0; $x <= 288; $x++) {
                        if (($x > 0) and ( $x < 7)) {
                            
                        } elseif (($x == 8) or ( $x == 42) or ( $x == 65) or ( $x == 74) or ( $x == 86) or ( $x == 119) or ( $x == 149) or ( $x == 208)) {
                            
                        } elseif (($x > 264) and ( $x < 274)) {
                            
                        } else {
                            if ($rowpos == 10) {
                                echo "								</tr>\r\n";
                                echo "								<tr>\r\n";

                                $rowpos = 1;
                            }

                            // workaround for the current image links
                            $add = '';
                            $addd = '';
                            if (strlen($x) != 3)
                                $add = '0';
                            if (strlen($x) + 1 < 3)
                                $addd = '0';
                            // end workaround
                            ?>
                            <td align="center"><img src="../images/chars/<?php echo $add . $addd . $x ?>.png"><BR /><b>ID: <?php echo $x ?></b></td><?php
                            $rowpos++;
                        }
                    }
                } else if ($_GET['size'] == 'large') {
                    $rowpos = 1;
                    //while ($row = mysql_fetch_assoc($charQuery))
                    for ($x = 0; $x <= 288; $x++) {
                        if (($x > 0) and ( $x < 7)) {
                            
                        } elseif (($x == 8) or ( $x == 42) or ( $x == 65) or ( $x == 74) or ( $x == 86) or ( $x == 119) or ( $x == 149) or ( $x == 208)) {
                            
                        } elseif (($x > 264) and ( $x < 274)) {
                            
                        } else {
                            if ($rowpos == 6) {
                                echo "								</tr>\r\n";
                                echo "								<tr>\r\n";

                                $rowpos = 1;
                            }
                            ?>
                            <td align="center"><img src="../images/skinlist_detailed_og/<?php echo $x ?>.png"><BR /><b>ID: <?php echo $x ?></b></td><?php
                            $rowpos++;
                        }
                    }
                }
                ?>
            </TR>
        </table>
        <?php
    }
}
?>

