/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function startLoggingIn(username, password) {
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("login_area").innerHTML = xmlhttp.responseText;
            ajax_render_main_menu();
        }
    };

    xmlhttp.open("POST", "../ajax/ajax_login.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("username=" + username + "&password=" + password);
    document.getElementById("login_area").innerHTML = '<center><br><img src="/images/loading11.gif"/><br><div style="color: #fff;text-shadow: 0px 1px #000;font-family: Arial, Helvetica, sans-serif;font-size: 12px;"><p>Logging in..</p></div></center>';

}

function ajax_login_box() {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    if (username && username.length > 0 && password && password.length > 0) {
        promtCookies();
        startLoggingIn(username, password);
    }
    return false;
}

function ajax_logout_box() {
    var yesno = confirm("Are you sure you want to logout?\n\nNotice: If you have saved your username & password, these will also be cleaned.");
    if (yesno) {

        document.getElementById("login_area").innerHTML = '<center><br><img src="/images/loading11.gif"/><br><div style="color: #fff;text-shadow: 0px 1px #000;font-family: Arial, Helvetica, sans-serif;font-size: 12px;"><p>Logging out..</p></div></center>';
        var xmlhttp;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
            {
                document.getElementById("login_area").innerHTML = xmlhttp.responseText;
                ajax_render_main_menu();
            }
        };

        xmlhttp.open("GET", "../ajax/ajax_logout.php", true);
        xmlhttp.send();

        setCookie("login_username", '', 7);
        setCookie("login_password", '', 7);
    }
    return false;
}


function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) != -1)
            return c.substring(name.length, c.length);
    }
    return "";
}

function loadLoginCookies() {
    //alert('cookie loaded - '+getCookie("login_username"));
    var savedUsername = getCookie("login_username");
    var savedPassword = getCookie("login_password");
    var logoutBtnShowing = document.getElementById("ajax_logout_box_btn");
    if (!logoutBtnShowing && savedUsername && savedUsername.length > 0 && savedPassword && savedPassword.length > 0) {
        $('#username').val(savedUsername);
        $('#password').val(savedPassword);
        startLoggingIn(savedUsername, savedPassword);
    }
}

function promtCookies() {
    var savedUsername = getCookie("login_username");
    var inputUsername = $('#username').val();
    //alert(savedUsername+'-'+inputUsername);
    if (savedUsername != inputUsername) {
        var yesno = confirm('Do you want to remember your username and password for next time?\n\nIf yes, these information will be stored in your browser\'s cookie for 7 days if you don\'t click logout button.');
        if (yesno) {
            setCookie("login_username", inputUsername, 7);
            setCookie("login_password", $('#password').val(), 7);
        }
    }
    return true
}

var checkingSession = null;
function checkSession() {
    checkingSession = window.setInterval(function() {
        var logoutBtnShowing = document.getElementById("ajax_logout_box_btn");
        if (logoutBtnShowing == null) {
            //do nathing
        } else {
            var xmlhttp;
            if (window.XMLHttpRequest)
            {// code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            }
            else
            {// code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function()
            {
                if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
                {
                    document.getElementById("login_area").innerHTML = xmlhttp.responseText;
                    loadLoginCookies();
                }
            };

            xmlhttp.open("GET", "../ajax/ajax_session_check.php", true);
            xmlhttp.send();
        }
    }, 10000);
}

function stopCheckingSession() {
    clearInterval(checkingSession);
}

function ajax_render_main_menu() {
    $.get("../ajax/ajax_render_menu.php", {
    }, function(data) {
        document.getElementById("main_menu_header").innerHTML = data;
    });
}