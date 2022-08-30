function tc_switch(state1) {
    $.post("../ajax/ajax_support.php", {
        step: "tc_switch",
        state: state1,
    }, function (stuff) {
        self.location = "support.php";
    });
}

function load_submit_form(type1) {
    $('#lib_bot').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.post("../ajax/ajax_support.php", {
        step: "load_submit_form",
        type: type1,
    }, function (stuff) {
        $('#lib_bot').html(stuff);
    });
}

function load_backend_midtop() {
    var closed1 = 0;
    if ($('#chk_global_closed').is(':checked')) {
        closed1 = 1;
    }
    var locked1 = 0;
    if ($('#chk_global_locked').is(':checked')) {
        locked1 = 1;
    }
    $.post("../ajax/ajax_support.php", {
        step: "load_backend_midtop",
        closed: closed1,
        locked: locked1,
    }, function (stuff) {
        $('#lib_mid_top').html(stuff);
    });
}

function load_my_tickets() {
    //$('#lib_mid').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    var closed1 = 0;
    if ($('#chk_my_ticket_closed').is(':checked')) {
        closed1 = 1;
    }
    var locked1 = 0;
    if ($('#chk_my_ticket_locked').is(':checked')) {
        locked1 = 1;
    }
    $.post("../ajax/ajax_support.php", {
        step: "load_my_tickets",
        closed: closed1,
        locked: locked1,
    }, function (stuff) {
        $('#lib_mid').html(stuff);
    });
}

function checkBoxMyTicketChanges() {
    load_my_tickets();
}

function checkBoxGlobalChanges() {
    load_backend_midtop();
}

function reassign_ticket(tcid1) {
    if (confirm('Are you sure?')) {
        $.post("../ajax/ajax_support.php", {
            step: "reassign_ticket",
            tcid: tcid1,
            assignto: $("#reassign_ticket option:selected").val(),
            assigntoname: $("#reassign_ticket option:selected").text(),
        }, function (stuff) {
            //alert(stuff);
            if (isNaN(stuff))
                alert("Opps, sorry!\n\nSomething went wrong, please try again later.");
            else {
                load_my_tickets();
                load_ticket(stuff);
            }
        });
    }
}

function client_submit_ticket(type1) {
    if ($('#btn_submit_ticket').val() == "Create") {
        $('#btn_submit_ticket').val('Creating..');
        var subj = $('#subject').val();
        var cont = $('#content').val();
        var assignto1 = 0;
        var private1 = 0;
        var reported_accounts1 = false;
        var involved_accounts1 = false;
        if (type1 == 2) {
            if ($("#unban_account_type option:selected").val() == "Forums") {
                subj = "Forums - " + $("#subject").val();
                cont = subj + "\n" + cont;
            } else {
                cont = $('#banrecord').val() + "\n" + cont;
                assignto1 = $('#assignto').val();
            }
        } else if (type1 == 6) {//bug 
            var where = $("#bug_where option:selected").text();
            var sum = $("#bug_sum").val();
            var steps = $("#bug_steps").val();
            var notes = $("#bug_notes").val();
            if ($('#bug_private').is(':checked')) {
                private1 = 1;
            }
            if (where == "MTA Server") {
                var cate = $("#bug_cate option:selected").text();
                subj = where + " - " + cate + " - " + sum;
            } else {
                subj = where + " - " + sum;
            }
            cont = "<b>Steps to produce:</b><br><br>" + steps + "<br><br><b>Notes:</b><br>" + notes + "";
        } else if (type1 == 7) {//player report
            reported_accounts1 = $('#reported_accounts').val();
            if (!reported_accounts1 || reported_accounts1.length < 1) {
                $('#btn_submit_ticket').val('Create');
                return alert("Please add at least one player to report on.");
            }
            involved_accounts1 = $('#reported_accounts').val();
            var reported_text = $('#reported_text').html();
            if (!reported_text) {
                reported_text = '<b>The players are being reported: </b><br>None<br>';
            }
            var involved_text = $('#involved_text').html();
            if (!involved_text) {
                involved_text = '<b>The players are being involved: </b><br>None<br>';
            }
            var date = $('#date').val();
            var rules = $("#rules_broken").val();
            var story = $("#story").val();
            subj = reported_text;
            cont = reported_text + "<br>" + involved_text + "<br><b>Date of incident: </b><br>" + date + "<br><br><b>What rules did these players break:</b><br>" + rules + "<br><br><b>Explain your side of the story and list your evidence:</b><br>" + story;
        } else if (type1 == 8) {
            cont = $('#histories').html()+'<hr><b>Explain to us what exactly happened and why your history should be removed:</b><br>'+cont;
            assignto1 = $('#prefer_admin').val();
        }
        //alert(private1);

        $.post("../ajax/ajax_support.php", {
            step: 'submit',
            type: type1,
            email: $('#email').val(),
            subject: subj,
            content: cont,
            captcha: $('#captcha').val(),
            assignto: assignto1,
            private: private1,
        }, function (stuff) {
            //alert(stuff);
            if (stuff == "captcha") {
                alert("Opps, sorry! \n\nCaptcha was incorrect!");
            } else if (stuff && !isNaN(stuff)) {
                alert("You have successfully submitted new ticket #" + stuff + "!\n\nA staff member has been assigned to your ticket.\n\nWe will update you with future status and details about your ticket via email and possibly in-game notifications too, please allow us a few hours to get back to you! ");
                load_my_tickets();
                load_ticket(stuff);
            } else {
                alert("Opps, sorry! \n\nYour ticket has failed to create, please try again later.");
            }
            $('#btn_submit_ticket').val('Create');
        });
    }
}

function client_add_comment(tcid1) {
    if ($('#btn_add_comment').val() == "Add") {
        $('#btn_add_comment').val("Adding..");
        var int = 0;
        if ($('#internal').is(':checked')) {
            int = 1;
        }
        //alert($('#email').val());
        $.post("../ajax/ajax_support.php", {
            step: 'add_comment',
            email: $('#email').val(),
            comment: $('#comment').val(),
            tcid: tcid1,
            internal: int,
        }, function (stuff) {
            //alert(stuff);
            if (!isNaN(stuff)) {
                load_my_tickets();
                load_ticket(stuff);
            } else {
                alert("Opps, sorry! \n\nYour comment has failed to add, please try again later.");
                $('#btn_add_comment').val("Add");
            }
        });
    }
}

function add_subcriber(tcid1) {
    var username = prompt("Please enter an exact username");
    if (username != null && username.length > 0) {
        $.post("../ajax/ajax_support.php", {
            step: 'add_subcriber',
            tcid: tcid1,
            subcriber: username,
        }, function (stuff) {
            if (!isNaN(stuff)) {
                //load_my_tickets();
                load_ticket(stuff);
            } else {
                alert("Opps, sorry! \n\nCouldn't not add subscriber. Possible reasons:\n-Username does not exist.\n-Username is already ticket's creator or assigned staff.\n-Username is already added to subscribers.\n\nPlease try again.");
            }
        });
    }
}

function change_status(tcid1, state1) {
    if (confirm("Are you sure?")) {
        $.post("../ajax/ajax_support.php", {
            step: 'change_status',
            tcid: tcid1,
            state: state1,
        }, function (stuff) {
            if (!isNaN(stuff)) {
                load_my_tickets();
                load_ticket(stuff);
            } else {
                alert("Opps, sorry! \n\nTicket status has failed to change.\nPlease try again later.");
            }
        });
    }
}

function toggle_private(tcid1, state1) {
    if (confirm("Are you sure?")) {
        $.post("../ajax/ajax_support.php", {
            step: 'toggle_private',
            tcid: tcid1,
            state: state1,
        }, function (stuff) {
            if (!isNaN(stuff)) {
                load_my_tickets();
                load_ticket(stuff);
            } else {
                alert("Opps, sorry! \n\nTicket privacy has failed to change.\nPlease try again later.");
            }
        });
    }
}

function load_ticket(id) {
    if (id && !isNaN(id)) {
        $.get("../ajax/ajax_support.php", {
            tcid: id,
        }, function (stuff) {
            $('#lib_bot').html(stuff);
        });
    }
}

function ajax_reload_captcha() {
    $('#img_captcha').html('<img src="captcha/captcha.php?access_key=' + Math.random() + '" />');
}

function unban_select() {
    var val = $("#unban_account_type option:selected").val();
    $.post("../ajax/ajax_support_forms.php", {
        type: 2,
        type2: val,
    }, function (stuff) {
        $('#unban_below').html(stuff);
    });

    return false;
}

function load_form_search_ticket() {
    var val = $("#search_ticket_by option:selected").val();
    $.post("../ajax/ajax_support_forms.php", {
        type: "search_ticket_by",
        type2: val,
    }, function (stuff) {
        $('#gui_search_ticket').html(stuff);
    });

    return false;
}

function ticket_search_start(type) {
    var start1 = 0;
    var limit1 = 10;
    var keyword = $("#search_ticket_keyword").val();
    if (type == "status" || type == "assign_to" || type == "date" || type == "type") {
        keyword = $("#search_ticket_keyword option:selected").val();
    }
    var closed1 = 0;
    if ($('#chk_closed').is(':checked')) {
        closed1 = 1;
    }
    var locked1 = 0;
    if ($('#chk_locked').is(':checked')) {
        locked1 = 1;
    }
    ticket_search_load_results(start1, limit1, type, keyword, closed1, locked1);
    return false;
}

function ticket_search_load_results(start1, limit1, type1, keyword1, closed1, locked1, loadto1) {
    //alert("start1: "+start1);
    //alert("limit1: "+limit1);
    //alert("type1: "+type1);
    //alert("keyword1: "+keyword1);
    //alert("closed1: "+closed1);
    //alert("locked1: "+locked1);
    var loadto = "lib_mid_top";
    if (loadto1) {
        loadto = loadto1
    }
    $.post("../ajax/ajax_support.php", {
        step: "list_tickets",
        start: start1,
        limit: limit1,
        type: type1,
        keyword: keyword1,
        close: closed1,
        lock: locked1,
        loadto: loadto1,
    }, function (stuff) {
        $('#' + loadto).html(stuff);
    });
    return false;
}

function load_ticket_comments(tcid1, start1, limit1, loadto1) {
    //alert("start1: "+start1);
    //alert("limit1: "+limit1);
    //alert("type1: "+type1);
    //alert("keyword1: "+keyword1);
    //alert("closed1: "+closed1);
    //alert("locked1: "+locked1);
    var loadto = "comments";
    if (loadto1) {
        loadto = loadto1
    }
    $.post("../ajax/ajax_support.php", {
        step: "load_ticket_comments",
        tcid: tcid1,
        start: start1,
        limit: limit1,
        loadto: loadto1,
    }, function (stuff) {
        $('#' + loadto).html(stuff);
    });
    return false;
}

function load_bug_report_area() {
    var val = $("#bug_where option:selected").val();
    //alert(val);
    $.post("../ajax/ajax_support_forms.php", {
        type: "load_bug_report_area",
        type2: val,
    }, function (stuff) {
        $('#bug_report_area').html(stuff);
    });

    return false;
}

function load_player_report_forms(ticketType, btn) {
    if ($('#' + btn).val() == "Add") {
        $('#' + btn).val("Adding..");
        var guiElement = "report_players_invovled";
        var text = "involved";
        var chars1 = $('#involvedcharacters').val();
        if (btn == "btn_add_reported_players") {
            chars1 = $('#reportedcharacters').val();
            guiElement = "add_reported_players";
            text = "reported";
        }
        $.post("../ajax/ajax_support_forms.php", {
            type: ticketType,
            type2: text,
            chars: chars1,
        }, function (stuff) {
            if (stuff.trim()[0] == "<") {
                $('#' + guiElement).html(stuff);
            } else {
                alert(stuff.trim());
            }
            $('#' + btn).val("Add");
        });
    }
}

function history_next(tctype) {
    var nextBtn = $('#history_next_btn');
    if (nextBtn.val() == "Next") {
        var appeals = $('input[type="checkbox"][name="appeals\\[\\]"]:checked').map(function () {
            return this.value;
        }).get();
        if (appeals.length < 1) {
            return alert("Please choose at least one history record to appeal!");
        } else if (appeals.length > 10) {
            return alert("(You can only appeal with maximum of 10 history records at once!");
        }
        nextBtn.val('Validating..');

        $.post("../ajax/ajax_support_forms.php", {
            type: tctype,
            type2: "check_history",
            histories: appeals,
        }, function (stuff) {
            if (stuff.trim()[0] == "<") {
                $('#histories').html(stuff);
                $("#history_below").show();
            } else {
                alert(stuff.trim());
                nextBtn.val('Next');
                $("#history_below").hide();
            }
        });
    }
}

function remove_ahistory(data){
    var btn = $('#btn_remove_adm_history_'+data);
    if (btn.val() == "Remove") {
        btn.val('Removing..');
        $.post("../ajax/ajax_support.php", {
            step: 'ajax_remove_admin_history',
            record: data,
        }, function (stuff) {
            if (stuff.trim()[0] == ".") {
                btn.val('Removed');
                alert('Admin record has been successfully removed!');
            } else {
                alert(stuff.trim());
                btn.val('Remove');
            }
        });
    }
}

