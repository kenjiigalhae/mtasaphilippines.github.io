<div class="news_holder">
    <div class="news_top"></div>
    <div class="news">
        <img src="images/news_bulletin.png" alt="" />
        <p>OwlGaming is a roleplaying game community based on a Grand Theft Auto modification named Multi Theft Auto. </p>
        <p>OwlGaming is a long-term roleplaying project introduced on January 1st, 2014, with the ambition of housing well-experienced roleplayers and put them under our wing; we intend on bringing roleplayers the best of experiences in their roleplaying career for as long as possible!</p>
        <p>We possess over a well-developed and matured script which not only serves as a major attraction to our server, but to increase the joy our roleplayers gain from staying at Owl.</p>
        <p>Our development team consists of professional programmers and the most experienced scripters in MTA RP servers in recent years. With a strong and stable development team, our MTA server script is continuously improving and growing with weekly script releases.</p>
        <p>OwlGaming wishes you an enjoyable and refreshing stay, welcome to our server.</p>

    </div>
    <div class="news_bottom"></div>
</div>
<div class="right_holder">
    <div class="login_box" id="ajax_login_box">
        <div class="righ_top"></div>
        <div class="right_middle" id="right_middle">
            <img class="login_img" src="images/login_img.png" alt="" />
            <div id="login_area">
                <?php if (!isset($_SESSION['username'])) { ?>
                    <div class="login_text">
                        <form name="login" action="" method="post" onSubmit="ajax_login_box();
                                return false;">
                            <input class="textfield" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
                            <input class="password textfield" name="password" type="password" placeholder="Password" id="password" maxlength="100"required/>
                            <div class="hidden-submit"><input type="submit" tabindex="-1"/></div>
                        </form>
                    </div>
                    <div class="login_btn" id="ajax_login_box_btn">
                        <a onClick="ajax_login_box();
                                return false;" href=""><img src="images/login_btn.png" border="0" alt="" /></a>
                    </div>
                    <div class="copyright"><a href="register.php">Register new account</a> | <a href="lostpw.php">Recover lost password</a></div>
<?php } else { ?>
                    <div class="login_text">
                        <p>You're logged in as <?php echo $_SESSION['username']; ?><br>
                            Email: <?php echo $_SESSION['email']; ?></p>
                    </div>
                    <div class="login_btn" id="ajax_logout_box_btn">
                        <a onClick="ajax_logout_box();
                                return false;" href=""><img src="images/logout_btn.png" border="0" alt="" /></a>
                    </div>
<?php } ?>
            </div>
        </div>

        <div class="right_bottom"></div>
    </div>
    <div class="stats_box">
        <div class="statsrigh_top"></div>
        <div class="statsright_middle">
            <img class="stats_img" src="images/stats.png" alt="" />
            <div id="server_stats">
                
                <center>
                    
                    <br>
                    <img src="/images/loading11.gif"/>
                    <br>
                    <div style="color: #fff;text-shadow: 0px 1px #000;font-family: Arial, Helvetica, sans-serif;font-size: 12px;">
                        <p>Querying..</p>
                    </div>
                </center>
                
                <!--
                <iframe src="http://www.game-state.com/iframe.php?ip=91.121.137.31&port=22003&bgcolor=FFFFFF&bordercolor=000000&fieldcolor=000000&valuecolor=242424&oddrowscolor=F0F0F0&showgraph=false&showplayers=false&graphvalues=242424&graphaxis=000000&width=275&graph_height=105&plist_height=101&font_size=7" frameborder="0" scrolling="no" style="width: 270px; height: 165px"></iframe>
                -->
            </div>

            <!--
            Server: Online<br />
            Server IP: 127.0.0.1<br />
            Port: 7777<br />
            Players: 40/50<br />
            Mode: Server N v1.2<br />
            Map: San Andreas<br />
            -->
        </div>


