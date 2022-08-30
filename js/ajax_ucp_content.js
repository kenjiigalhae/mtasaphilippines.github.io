function ajax_load_acc_info() {
    //document.getElementById("acc_info").innerHTML = '<center><img src="/images/loading3.gif"/></center>';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("acc_info").innerHTML = xmlhttp.responseText;
        }
    };

    xmlhttp.open("GET", "../ajax/ajax_get_acc_info.php", true);
    xmlhttp.send();
}

function ajax_load_char_info() {
    //document.getElementById("char_info").innerHTML = '<center><img src="/images/loading3.gif"/></center>';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("char_info").innerHTML = xmlhttp.responseText;
        }
    };

    xmlhttp.open("GET", "../ajax/ajax_get_char_info.php", true);
    xmlhttp.send();
}

function ajax_load_char_details(charid) {
    $("#char_info_mid").append('<div id="overlay_loading"><center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center></div>');
    $.post("../ajax/ajax_get_char_details.php", {
        step: "load_char_details",
        charid: charid,
    }, function (stuff) {
        $('#char_info_mid').html(stuff);
    });
    return false;
}

function ajax_update_character() {
    var updating = document.getElementById("char_update_status").innerText;
    if (updating == "Updating..") {
        return false;
    }
    var heightval = document.forms["updateCharacter"]["heightval"].value;
    var weighteval = document.forms["updateCharacter"]["weighteval"].value;
    var charid = document.forms["updateCharacter"]["charid"].value;
    var char_alive = document.getElementById("char_alive").innerText;
    var char_active = document.getElementById("char_active").innerText;

    //alert(char_alive);
    if (char_alive == "Deceased") {
        alert("You can not update a deceased character.");
        return false;
    }
    if (char_active == "De-activated") {
        alert("You can not update a de-activated character.");
        return false;
    }
    if (heightval == null || heightval == "" || heightval < 50 || heightval > 250) {
        alert("Character height must be higher than 50 and lower than 250 cm(s).");
        return false;
    }
    if (weighteval == null || weighteval == "" || weighteval < 2 || weighteval > 300) {
        alert("Character weight must be heavier than 2 and lighter than 300 kg(s).");
        return false;
    }
    document.getElementById("char_update_status").innerHTML = 'Updating..';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("char_update_status").innerHTML = xmlhttp.responseText;
            //ajax_load_char_info();
        }
    };

    xmlhttp.open("POST", "../ajax/ajax_update_character.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("charid=" + charid + "&heightval=" + heightval + "&weighteval=" + weighteval);
    return false;
}

function ajax_activate_character(charid) {
    var char_active = document.getElementById("char_active").innerText;

    if ((char_active == "Activating..") || (char_active == "Deactivating..")) {
        return false;
    }

    var active = 1;
    var statusText = "Activating..";
    if (char_active == "Activated") {
        statusText = "Deactivating.."
        active = 0;
    }
    document.getElementById("char_active").innerHTML = statusText;
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("char_active").innerHTML = xmlhttp.responseText;
            ajax_load_char_info();
        }
    };

    xmlhttp.open("POST", "../ajax/ajax_activate_character.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("charid=" + charid + "&active=" + active);
    return false;
}

function ajax_stat_transfer() {
    $("#char_info_mid").append('<div id="overlay_loading"><center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center></div>');
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("char_info_mid").innerHTML = xmlhttp.responseText;
        }
    };

    xmlhttp.open("GET", "../ajax/ajax_stat_transfer.php", true);
    xmlhttp.send();
    return false;
}

function sFromCharChange() {
    var charIdFrom = document.getElementById("fromcharacter").value;
    var charIdTo = document.getElementById("tocharacter").value;

    var selectedTo = $('input[id="toCharPreviewSelected"]').val();
    var selectedFrom = $('input[id="fromCharPreviewSelected"]').val();

    if (charIdFrom > 0) {
        //alert(charIdFrom+"-"+charIdTo);
        //alert(selectedTo+"-"+selectedFrom);
        if (charIdFrom == charIdTo || (selectedTo && charIdFrom == selectedTo) || (selectedFrom && charIdTo == selectedFrom) || (selectedTo && selectedFrom && selectedTo == selectedFrom)) {
            document.getElementById("validateText").innerHTML = "The source and destination characters cannot be the same!"
        } else {
            document.getElementById("validateText").innerHTML = "Please select the source and destination character for the transfer below."
            ajax_load_character_preview(charIdFrom, "fromCharPreview");
        }
        document.getElementById("fromcharacter").selectedIndex = "0";
        document.getElementById("tocharacter").selectedIndex = "0";
    }
}

function sToCharChange() {
    var charIdFrom = document.getElementById("fromcharacter").value;
    var charIdTo = document.getElementById("tocharacter").value;

    var selectedTo = $('input[id="toCharPreviewSelected"]').val();
    var selectedFrom = $('input[id="fromCharPreviewSelected"]').val();

    if (charIdTo > 0) {
        //alert(charIdFrom+"-"+charIdTo);
        //alert(selectedTo+"-"+selectedFrom);
        if (charIdFrom == charIdTo || (selectedTo && charIdFrom == selectedTo) || (selectedFrom && charIdTo == selectedFrom) || (selectedTo && selectedFrom && selectedTo == selectedFrom)) {
            document.getElementById("validateText").innerHTML = "The source and destination characters cannot be the same!"
        } else {
            document.getElementById("validateText").innerHTML = "Please select the source and destination character for the transfer below."
            ajax_load_character_preview(charIdTo, "toCharPreview");
        }
        document.getElementById("fromcharacter").selectedIndex = "0";
        document.getElementById("tocharacter").selectedIndex = "0";
    }
}

function ajax_load_character_preview(charid, loadto) {
    document.getElementById(loadto).innerHTML = '<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById(loadto).innerHTML = xmlhttp.responseText;
            stat_transfer_step2_validate(loadto);
        }
    };

    xmlhttp.open("POST", "../ajax/ajax_stat_transfer_load_preview.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("charid=" + charid + "&loadto=" + loadto);
    return false;
}

function stat_transfer_step2_validate(loadto) {
    var selectedTo = document.getElementById("toCharPreviewSelected").value;
    var selectedFrom = document.getElementById("fromCharPreviewSelected").value;
    if (selectedTo && selectedTo > 0 && selectedFrom && selectedFrom > 0) {
        document.getElementById("validateText").innerHTML = "Now, let's choose somethings to transfer!"
        ajax_load_source_character_assets(selectedFrom);
    }
}

function stat_transfer_step3_validate() {
    var transfer_icon = document.getElementById("transfer_icon");
    if (!document.getElementById("transferring_status") && transfer_icon) {
        transfer_icon.src = "/images/icons/transfer_icon_active.png";
    }
}

function ajax_load_source_character_assets(charid) {
    document.getElementById("source_char_assets").innerHTML = '<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>';
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("source_char_assets").innerHTML = xmlhttp.responseText;
            stat_transfer_step3_validate();
        }
    };

    xmlhttp.open("POST", "../ajax/ajax_stat_transfer_load_source_character_assets.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("charid=" + charid);
    return false;
}

function mouseClickTransferIcon() {
    var transfer_icon = document.getElementById("transfer_icon");
    if (transfer_icon.src == (location.protocol + "//" + location.hostname + "/images/icons/transfer_icon_inactive.png")) {
        return false;
    }
    $(document).ready(function () {
        var vehs = $('input[type="checkbox"][name="vehicle\\[\\]"]:checked').map(function () {
            return this.value;
        }).get();
        var ints = $('input[type="checkbox"][name="interior\\[\\]"]:checked').map(function () {
            return this.value;
        }).get();
        var money = $('input[type="number"][name="money"]').val();
        var bankmoney = $('input[type="number"][name="bankmoney"]').val();
        var transferFrom = $('input[type="hidden"][id="fromCharPreviewSelected"]').val();
        var transferTo = $('input[type="hidden"][id="toCharPreviewSelected"]').val();
        document.getElementById("source_char_assets").innerHTML = '<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Transfering..</b></p><input id="transferring_status"/></center>';
        transfer_icon.src = "/images/icons/transfer_icon_inactive.png";
        $.post("../ajax/ajax_stat_transfer_start_transfering.php", {
            fromChar: transferFrom,
            toChar: transferTo,
            tmoney: money,
            tbankmoney: bankmoney,
            vehicles: vehs,
            interiors: ints,
        }, function (data) {
            document.getElementById("source_char_assets").innerHTML = data;
            if (document.getElementById("stat_transfer_result") && document.getElementById("stat_transfer_result").value == "completed") {
                ajax_load_acc_info();
                ajax_update_stat_transfer_gc_vs_times();
            }
        });
    });
}

function mouseOverTransferIcon() {
    var transfer_icon = document.getElementById("transfer_icon");
    if (transfer_icon.src == (location.protocol + "//" + location.hostname + "/images/icons/transfer_icon_inactive.png")) {
        return false;
    }
    transfer_icon.src = "/images/icons/transfer_icon_hover.png";
}

function mouseOutTransferIcon() {
    var transfer_icon = document.getElementById("transfer_icon");
    if (transfer_icon.src == (location.protocol + "//" + location.hostname + "/images/icons/transfer_icon_inactive.png")) {
        return false;
    }
    transfer_icon.src = "/images/icons/transfer_icon_active.png";
}

function mouseDownTransferIcon() {
    var transfer_icon = document.getElementById("transfer_icon");
    if (transfer_icon.src == (location.protocol + "//" + location.hostname + "/images/icons/transfer_icon_inactive.png")) {
        return false;
    }
    transfer_icon.src = "/images/icons/transfer_icon_down.png";
}

function mouseUpTransferIcon() {
    var transfer_icon = document.getElementById("transfer_icon");
    if (transfer_icon.src == (location.protocol + "//" + location.hostname + "/images/icons/transfer_icon_inactive.png")) {
        return false;
    }
    transfer_icon.src = "/images/icons/transfer_icon_hover.png";
}

function ajax_update_stat_transfer_gc_vs_times() {
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            document.getElementById("stat_transfer_gc_vs_times").innerHTML = xmlhttp.responseText;
        }
    };

    xmlhttp.open("GET", "../ajax/ajax_stat_transfer_gc_vs_times.php", true);
    xmlhttp.send();
    return false;
}

function ajax_load_int_uploader(intid1, charid1) {
    //$('#char_info_mid_top').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.post("../ajax/ajax_ucp_int_uploader.php", {
        intid: intid1,
        charid: charid1,
    }, function (data) {
        $('#char_info_mid_top').slideUp(0);
        $('#char_info_mid_top').html(data);
        $('#char_info_mid_top').slideDown(500);
    });
}

function ajax_load_int_protection(intid1, charid1, protected_until1) {
    //$('#char_info_mid_top').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.post("../ajax/ajax_ucp_int_protection.php", {
        step: 1,
        intid: intid1,
        charid: charid1,
        protected_until: protected_until1,
    }, function (data) {
        $('#char_info_mid_top').slideUp(0);
        $('#char_info_mid_top').html(data);
        $('#char_info_mid_top').slideDown(500);
    });
}


function ajax_load_int_protection_veh(vehid1, charid1, protected_until1) {
    //$('#char_info_mid_top').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.post("../ajax/ajax_ucp_veh_protection.php", {
        step: 1,
        vehid: vehid1,
        charid: charid1,
        protected_until: protected_until1,
    }, function (data) {
        $('#char_info_mid_top').slideUp(0);
        $('#char_info_mid_top').html(data);
        $('#char_info_mid_top').slideDown(500);
    });
}

function ajax_start_process_int(intid1, charid1) {
    $('#submit-btn').hide();
    $('#process_btn').hide();
    $('#FileInput').hide();
    $('#output').text('Processing your map..');
    //alert($('#uploadCost').val());
    $.post("../ajax/ajax_ucp_int_map_process.php", {
        intid: intid1,
        charid: charid1,
        uploadCost: $('#uploadCost').val(),
    }, function (data) {
        if (data == 'ok') {
            data = 'Your map was successfully processed and the interior has been set up and ready to use!';
            ajax_load_acc_info();
        } else {
            $('#submit-btn').show();
            $('#FileInput').show();
        }
        $('#output').html(data);

    });
}

function ajax_load_acc_settings() {
    $('#char_info_mid_top').html(' ');
    $('#char_info').html(' ');
    $.post("../ajax/ajax_load_acc_settings.php", {
        step: 'load_acc_settings_gui',
    }, function (data) {
        $('#char_info_mid').html(data);
    });
}

function ajax_change_password() {
    if ($('#changepass').val() == "Change password") {
        var errorText = '';
        if (/\s/.test($('#curpass').val())) {
            errorText += '*Current password must not contain any white spaces.\n';
        }
        if ($('#curpass').val().length < 6) {
            errorText += '*Current password must contain at least 6 characters.\n';
        }

        if ($('#curpass').val() == $('#newpass1').val()) {
            errorText += '*Current password and new password can not be the same.\n';
        }

        if (/\s/.test($('#newpass1').val())) {
            errorText += '*New password must not contain any white spaces.\n';
        }
        if ($('#newpass1').val().length < 6) {
            errorText += '*New password must contain at least 6 characters.\n';
        }

        if ($('#newpass1').val() != $('#newpass2').val()) {
            errorText += '*Re-type password does not match.\n';
        }
        if (errorText.length > 0) {
            alert(errorText);
            return false;
        }
        $('#changepass').val("Working..");
        $.post("../ajax/ajax_load_acc_settings.php", {
            step: 'changepassword',
            curpass: $('#curpass').val(),
            newpass: $('#newpass1').val(),
        }, function (data) {
            alert(data);
            $('#changepass').val("Change password");
        });
    }
}

function ajax_change_email(curMail1) {
    if (($('#changeemail').val() == "Change email") && (curMail1 != $('#email').val())) {
        $('#changeemail').val("Working..");
        $.post("../ajax/ajax_load_acc_settings.php", {
            step: 'change_email_step_1',
            curMail: curMail1,
            newMail: $('#email').val(),
        }, function (data) {
            alert(data);
            $('#changeemail').val("Change email");
        });
    }
}


function ajax_reset_password(clue1) {
    if ($('#resetpass').val() == "Reset password") {
        $('#resetpass').val("Working..");
        $.post("../ajax/ajax_load_acc_settings.php", {
            step: 'reset_password',
            clue: clue1,
        }, function (data) {
            alert(data);
            $('#resetpass').val("Reset password");
        });
    }
}

function ajax_add_new_serial() {
    if ($('#add_new_serial').val() == "Add") {
        var reg = /[^A-Za-z0-9 ]/;
        if ($('#new_serial').val().length != 32 || reg.test($('#new_serial').val())) {
            alert("Serial number must be exact 32 characters in length and can only contains alphanumerics.");
            return false;
        }
        $('#add_new_serial').val("Working..");
        $.post("../ajax/ajax_load_acc_settings.php", {
            step: 'add_new_serial',
            serial: $('#new_serial').val().toUpperCase(),
        }, function (data) {
            $('#add_new_serial').val("Add");
            if (data == "ok") {
                alert("The new serial number has been successfully added!\n\nAn email contains instructions to activate the serial has been dispatched to your email. Please check and proceed it within 10 minutes!");
                ajax_load_acc_settings();
            } else if (!isNaN(data)) {
                if (confirm("You can only have one serial in the whitelist at the same time.\n\nIt costs " + data + " GC(s) for your next additional serial number. Do you want to continue?")) {
                    ajax_increase_serial_cap();
                }
            } else {
                alert(data);
            }
        });
    }
}

function ajax_increase_serial_cap() {
    $.post("../ajax/ajax_load_acc_settings.php", {
        step: 'increase_serial_cap',
    }, function (data) {
        if (data == "ok") {
            alert("You have successfully purchased 1 additional serial whitelist capacity!");
            ajax_load_acc_settings();
        } else if (data == "lackGC") {
            if (confirm("Opps, sorry. You lack of GC(s) to purchase this item. \n\nYou can always get more GC(s) by donating to servers, do you want to go to the donation page now?")) {
                self.location = "donate.php";
            }
        } else {
            alert(data);
        }
    });
}

function ajax_remove_serial(serialid) {
    if ($('#remove_serial_btn_' + serialid).val() == "Remove") {
        if (confirm("Are you sure you want to delete this serial number from the whitelist?")) {
            $('#remove_serial_btn_' + serialid).val("Working..");
            $.post("../ajax/ajax_load_acc_settings.php", {
                step: 'remove_serial',
                serialid: serialid,
            }, function (data) {
                $('#remove_serial_btn_' + serialid).val("Remove");
                if (data == "ok") {
                    alert("The serial number has been removed!");
                    ajax_load_acc_settings();
                } else if (data == "ok-email") {
                    alert("You're trying to remove an active serial from the whitelist. Therefore, an email contains instructions to deactivate and remove the serial has been dispatched to your email. Please check and proceed it within 10 minutes!");
                    ajax_load_acc_settings();
                } else {
                    alert(data);
                }
            });
        }
    }
}

function switchToFactionPanel(charid) {
    //$("#char_info_mid").append('<div id="overlay_loading"><center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center></div>');
    //return alert(charid);
    $.post("../ajax/ajax_get_char_details.php", {
        step: "load_faction_details",
        charid: charid,
    }, function (stuff) {
        $('#faction_details').slideUp(0);
        $('#faction_details').html(stuff);
        $('#char_details').slideUp(500);
        $('#faction_details').slideDown(500);
    });
    return false;
    
    
    return false;
}


