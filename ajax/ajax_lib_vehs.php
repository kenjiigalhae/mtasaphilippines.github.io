<?php
// define the path and name of cached file
$cachefile = '../cache/ajax_lib_vehs.php';
$cachetime = 60*5; // 5 mins
// Check if the cached file is still fresh. If it is, serve it up and exit.
if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
    include($cachefile);
    exit;
}
// if there is either no file OR the file to too old, render the page and capture the HTML.
ob_start();
?>
<html>
    <?php
    session_start();
    require_once '../classes/mysql/Database.class.php';
    require_once '../functions/functions.php';
    $db = new Database("MTA");
    $db->connect();
    $vehicles = array();
    $mQuery1 = $db->query("SELECT `spawnto`, `id`, `vehmtamodel`, `vehbrand`, `vehmodel`, `vehyear`, `vehprice`, `vehtax` FROM `vehicles_shop` WHERE `enabled`!='0' ORDER BY `id`");
    $viewAdvanced = false;
    if (isset($_SESSION['groups']) and $_SESSION['groups']) {
        $viewAdvanced = isPlayerTrialAdmin($_SESSION['groups']) or isPlayerVCT($_SESSION['groups']);
        //echo 'asd-'.$viewAdvanced;
    }
    while ($result = $db->fetch_array($mQuery1)) {
        if (isset($viewAdvanced) and $viewAdvanced) {
            $result['vehprice'] = '$' . number_format($result['vehprice']);
            $result['vehtax'] = '$' . number_format($result['vehtax']);
        } else {
            $result['vehprice'] = null;
            $result['vehtax'] = null;
        }
        array_push($vehicles, $result);
    }
    $db->free_result();
    $db->close();
    ?>
    <hr>
    <center>
        <b>Total Vehicles: <?php echo count($vehicles); ?></b>
        <?php if (!$viewAdvanced) { ?>
            <br><i>(To prevent any Metagaming possibilities, vehicle prices and taxes only be visible for admins and VCT members.)</i>
        <?php }
        ?>
            
        <br><br>
    </center>

    <table border=1 align=center id=logtable>
        <tr>
            <td><b>ID</b></td>
            <td><b><center>Info</center></b></td>
            <td align=center><b>Preview</b></td>
        </tr> <?php
        foreach ($vehicles as $veh) {
            ?>
            <tr>
                <td><b><?php echo $veh["id"]; ?></b></td>
                <td>
                    <table border=0>
                        <tr>
                            <td>
                                <b>Name: </b>
                            </td>
                            <td>
                                <?php echo $veh["vehyear"]; ?> <?php echo $veh["vehbrand"]; ?> <?php echo $veh["vehmodel"]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Price: </b>
                            </td>
                            <td>
                                <?php echo $veh["vehprice"]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Taxes: </b>
                            </td>
                            <td>
                                <?php echo $veh["vehtax"]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Availability: </b>
                            </td>
                            <td>
                                <?php
                                echo getCarShopNameFromID($veh["spawnto"]);
                                ;
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td><img src="../images/vehicles/Vehicle_<?php echo $veh["vehmtamodel"]; ?>.jpg"/></td>
            </tr>
            <?php
        }
        ?>
    </table>
</html>
<?php
// We're done! Save the cached content to a file
$fp = fopen($cachefile, 'w');
fwrite($fp, ob_get_contents());
fclose($fp);
// finally send browser output
ob_end_flush();
?>