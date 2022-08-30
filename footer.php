<div class="statsright_bottom"></div>
</div>
</div>
</div>
</div>
<div class="footer">
    <div class="footer_wrap">
        <div class="logo_zasprex"><a href="http://mtasa.com/" target="new"><img src="images/zasprex_logo.png" alt="" /></a></div> <!-- Do not remove -->
        <div class="your_logo"><a href="#"><img src="images/your_logo.png" alt="" /></a></div>
        <!---->
        <div class="quick_links">
            <div class="linkset1">
                <ul>
                    <h3>Quick Links</h3>
                    <li><a class="hover" href="ucp.php" target="_self">User Control Panel</a></li>
                    <li><a class="hover" href="http://forums.owlgaming.net" target="_blank">Community Forums</a></li>
                    <li><a class="hover" href="support.php" target="_self">Mantis / Bugs report</a></li>
                    <li><a class="hover" href="https://www.facebook.com/owlgamingcommunity" target="_blank">Facebook Fanpage</a></li>
                    <li><a class="hover" href="http://www.youtube.com/channel/UCov1MwxOPcO_Pi_b5JGT_rQ" target="_blank">Youtube Channel</a></li>

                </ul>
            </div>
            <div class="linkset2">
                <!--
                <ul>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                </ul>
                -->
            </div>
            <div class="linkset3">
                <!--
                <ul>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                </ul>
                -->
            </div>
            <div class="back_top">
                <h3 class="back_top_h3">Back to top</h3>
                <a href="#top"><img class="backtop_img" src="images/arrow.jpg" alt="" border="0" /></a>
            </div>
        </div>

    </div>

</div>
<center><font size="1">
    Programmed by <a href="#">Maxime</a> <br>
    Copyright © <?php echo date("Y"); ?> OwlGaming Community. All rights reserved.
    </font></center>

<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<!--<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>-->
<script type="text/javascript" src="js/jquery.nivo.slider.pack.js"></script>
<script type="text/javascript" src="js/static.js"></script>
<script type="text/javascript">

    loadLoginCookies();

    load_slider();
    setTimeout(function() {
        ajax_load_server_statistics();
    }, 50);
    checkSession();
    ajax_render_main_menu();
</script>
</body>
</html>