<?php
include("header.php");
?>
<div id="main-wrapper">
    <div id="lib_top">
        <h2>OwlGaming Roleplay Library</h2>
        <ul>
            <b>
                <li><a href='' onclick="ajax_load_int_top(); return false;">Interior List<a></li>
                <li><a href='' onclick="ajax_load_veh(); return false;">Vehicle List</a></li>
                <li><a href='' onclick="ajax_load_skin_top(); return false;">Skin List</a></li>
            </b>
        </ul>
    </div>
    <div id="lib_mid" ></div>
    <div id="lib_bot"></div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
<script type="text/javascript" src="./js/ajax_lib.js"/></script>

