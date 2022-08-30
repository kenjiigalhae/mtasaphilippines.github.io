<?php
require_once '../functions/functions_interior.php';

if (isset($_GET['part'])) {
if ($_GET['part'] == 'top') {
?>
<hr>
<ul>
    <li>
        <a href="" onclick="ajax_load_int_content('houses'); return false;">Houses/Apartments (<?php echo count($houses); ?>)</a>
    </li>
    <li>
        <a href="" onclick="ajax_load_int_content('garages'); return false;">Garages/Warehouses (<?php echo count($garages); ?>)</a>
    </li>
    <li>
        <a href="" onclick="ajax_load_int_content('shops'); return false;">Shops/Stores/Restaurants (<?php echo count($shops); ?>)</a>
    </li>
    <li>
        <a href="" onclick="ajax_load_int_content('bars'); return false;">Bars/Lounges/Casinos (<?php echo count($bars); ?>)</a>
    </li>
    <li>
        <a href="" onclick="ajax_load_int_content('offices'); return false;">Offices/Lobby/Hallways (<?php echo count($offices); ?>)</a>
    </li>
    <li>
        <a href="" onclick="ajax_load_int_content('others'); return false;">Others (<?php echo count($others); ?>)</a>
    </li>
</ul>
Total Interiors: <?php echo count($houses) + count($garages) + count($shops) + count($bars) + count($offices) + count($others); ?> - Updated on Oct 18th, 2014

<?php
} else if ($_GET['part'] == 'content') {
    if ($_GET['content'] == "houses")
        $toBeDisplayed = $houses;
    else if ($_GET['content'] == "garages")
        $toBeDisplayed = $garages;
    else if ($_GET['content'] == "shops")
        $toBeDisplayed = $shops;
    else if ($_GET['content'] == "bars")
        $toBeDisplayed = $bars;
    else if ($_GET['content'] == "offices")
        $toBeDisplayed = $offices;
    else if ($_GET['content'] == "others")
        $toBeDisplayed = $others;
    ?>
<hr>
    <table align="center" border="0" width=100%>
        <tr>
            <table border = 1 align = center id = logtable>
            <tr>
            <td><b>ID</b></td>
            <td align = center><b>Preview</b></td>
            </tr> <?php
            foreach ($toBeDisplayed as $toBeDisplayedChild) {
            ?>
        <tr>
            <td><b><?php echo $toBeDisplayedChild; ?></b></td>
            <td><img src="../images/interiors/<?php echo $toBeDisplayedChild; ?>.jpg"/></td>
        </tr>
        <?php
        }
        ?>
    </table>
<?php
}
}
?>

