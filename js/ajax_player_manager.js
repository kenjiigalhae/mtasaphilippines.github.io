function ajax_load_player_manager_GUI() {
    $.get("../ajax/ajax_load_player_manager_gui.php", {
        test: 0,
    }, function (data) {
        document.getElementById("#acc_info").innerHTML = data;
    });
}

function load_admin_team() {
    $('#char_info').html(' ');
    $('#char_info_mid_top').html('<center><b><br><img src="../images/loading3.gif"/><p>&nbsp;&nbsp;Querying..</p></b></center>');
    $.post("../ajax/ajax_load_admin_team.php", {
    }, function (data) {
        $('#char_info_mid_top').html(data);
    });
}

function load_account(id){
    $('#char_info').html(' ');
    $('#char_info_mid_top').html('<center><b><br><img src="../images/loading3.gif"/><p>&nbsp;&nbsp;Querying..</p></b></center>');
    $.post("../ajax/ajax_load_account.php", {
        userid: id,
    }, function (data) {
        $('#char_info_mid_top').html(data);
    });
}

function load_user_staff_rank(id) {
    $('#char_info').html(' ');
    $('#char_info_mid').html('<center><b><br><img src="../images/loading3.gif"/><p>&nbsp;&nbsp;Querying..</p></b></center>');
    $.post("../ajax/ajax_load_user_staff_rank.php", {
        userid: id,
    }, function (data) {
        $('#char_info_mid').html(data);
    });
}

function update_admin(id){
    $('#char_info').html('<center><b><br><img src="../images/loading3.gif"/><p>&nbsp;&nbsp;Updating..</p></b></center>');
    $.post("../ajax/ajax_update_staff_rank.php", {
        userid: id,
        admin: $( "#s_admin" ).val(),
        supporter: $( "#s_supporter" ).val(),
        vct: $( "#s_vct" ).val(),
        scripter: $( "#s_scripter" ).val(),
        mapper: $( "#s_mapper" ).val(),
    }, function (data) {
        $('#char_info').html(data);
    });
}

function validateLogTypes() {
    var logtypes = $('input[type="checkbox"][name="logtype\\[\\]"]:checked').map(function () {
        return this.value;
    }).get();
    if (logtypes.length < 1) {
        $('#logtype_reminder').text("(Please choose at least one type of logs!)");
        return false;
    } else if (logtypes.length > 5) {
        $('#logtype_reminder').text("(You can only search 5 types of logs or less at once!)");
        return false;
    }
    return true;
}

function validateKeyword() {
    return $('input[type="input"][name="keyword"]').val().length > 0;
}

function validateTimeIntervals() {
    var end_point = $("#end_point option:selected").val();
    var start_point = $("#start_point option:selected").val();
    if (end_point <= start_point) {
        $('#logtype_reminder').text("('End Point' time must be deeper in the past than 'Start Point' time!)");
        return false;
    } else if (end_point - start_point > 2196) {
        $('#logtype_reminder').text("('Start Point' and 'End Point' times can't be more than 3 months different. Please shorten the time interval between 2 points.)");
        return false;
    }
    return true;
}

var ajax_search = null;
function onLogsSubmit() {
    var search_btn = $('input[type="submit"][id="search_btn"]');
    if (search_btn.val() == "Search") {
        if (validateLogTypes() && validateTimeIntervals()) {
            search_btn.val("Abort");
            var logtypes1 = $('input[type="checkbox"][name="logtype\\[\\]"]:checked').map(function () {
                return this.value;
            }).get();
            $('#logs_loading').html('<br><img src="../images/loading3.gif"/><p>&nbsp;&nbsp;Querying..</p>');
            ajax_search = $.post("../ajax/ajax_logs_start_searching.php", {
                logTypes: logtypes1,
                keyword: $('#keyword').val(),
                keyword_type: $("#keyword_type option:selected").val(),
                end_point: $("#end_point option:selected").val(),
                start_point: $("#start_point option:selected").val(),
                max_results: $("#max_results").val(),
            }, function (data) {
                $('#logs_result').prepend(data);
                if (ajax_search) {
                    ajax_search.abort();
                }
                search_btn.val("Search");
                $('#logs_loading').html('<center><input type="button" id="hide_logs" value="Clear Screen" onclick="clear_logs_screen();" /> </center>')

            });
        }
        ;
    } else if (search_btn.val() == "Abort") {
        if (ajax_search) {
            ajax_search.abort();
        }
        search_btn.val("Search");
        $('#logs_loading').html('<center><input type="button" id="hide_logs" value="Clear Screen" onclick="clear_logs_screen();" /> </center>')
    }
    return false;

}

function clear_logs_screen() {
    $('#logs_result').html('');
}