/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var customAmount = false;
function showCustomOption() {
    $("#don_option_normal").hide();
    $("#don_option_custom").show();
    customAmount = true;
}

function showDefaultOption() {
    $("#don_option_normal").show();
    $("#don_option_custom").hide();
    customAmount = false;
}

function getGcFromDollar(dollar) {
    dollar = Math.floor(dollar);
    var rate = 3;
    var benRate = 10;
    var benefit = (dollar / benRate) * (dollar / benRate);
    var actualGC = dollar * rate;
    var finalGC = actualGC + benefit;
    var discount = 100 - (actualGC / finalGC * 100);

    if (benefit < 0) {
        discount = 0;
    }
    if (discount > 55) {
        discount = 55;
        finalGC  = actualGC + actualGC*0.55;
        benefit = finalGC - actualGC;
    }
 
    return [finalGC.toFixed(0) , benefit.toFixed(0), discount.toFixed(0)];
}

function calculateGc() {
    var result = getGcFromDollar($("#custom_amount").val());
    document.getElementById("calculated_gc").innerHTML = result[0];
    document.getElementById("calculated_bonus").innerHTML = result[1];
    document.getElementById("calculated_discount").innerHTML = result[2];
}

function format_money(n, currency) {
    return currency + n.toFixed(2).replace(/./g, function (c, i, a) {
        return i > 0 && c !== "." && (a.length - i) % 3 === 0 ? "," + c : c;
    });
}

function startDonation() {
    var button = $('input[type="submit"][name="I1"]');
    var customAmountBtn = $('input[id="custom_amount"]');
    var defaultAmountBtn = $('input[name=amount1]:checked');
    if (button.val() == "Processing..") {
        return true;
    }
    else if (button.val() == "Donate") {
        var donateTo = $('input[type="text"][name="custom"]').val();
        var donorId = $('input[type="hidden"][name="item_number"]').val(); //Logged in userid
        var donorUsername = $('input[type="hidden"][name="donor_username"]').val();
        if (!donorId || donorId.length == 0 || isNaN(donorId)) {
            donorUsername = donateTo;
        }
        $('input[type="hidden"][name="item_name"]').val('Donation from ' + donorUsername + ' to ' + donateTo + ' [NOT REFUNDABLE]');
        button.val("Validating..");
        $.post("../ajax/ajax_functions.php", {
            getUserIdFromUsername: donateTo
        }, function (data) {
            if (!data || data == 0) {
                button.val("Account is not existed");
                setTimeout(function () {
                    button.val("Donate");
                }, 3000);
            } else {
                if (!donorId || donorId.length == 0 || isNaN(donorId)) {
                    button.val("Querying donor data..");
                    $.post("../ajax/ajax_functions.php", {
                        getUserIdFromUsername: donateTo
                    }, function (data) {
                        if (!data || data.length == 0) {
                            button.val("Failed! Try again later.");
                            setTimeout(function () {
                                button.val("Donate");
                            }, 5000);
                        } else {
                            $('input[type="hidden"][name="item_number"]').val(data);
                            if (customAmount) {
                                $('input[id="final_donation_amount"]').val(customAmountBtn.val());
                            } else {
                                $('input[id="final_donation_amount"]').val(defaultAmountBtn.val());
                            }
                            var donate_amount = $('input[id="final_donation_amount"]').val();
                            var result = getGcFromDollar(donate_amount);
                            var go = confirm('You are about to donate $' + donate_amount + ' for ' + result[0] + ' GC(s) with account name "' + donorUsername + '" to account name "' + donateTo + '".\n\nBy donating to the OwlGaming Community, you do understand that a refund will not be possible.\n\nAre you sure you want to do that?');
                            if (go) {
                                button.val("Processing..");
                                $("#donation_form").submit();
                                return true;
                            } else {
                                button.val("Donate");
                                return false;
                            }
                        }
                    });
                } else {
                    if (customAmount) {
                        $('input[id="final_donation_amount"]').val(customAmountBtn.val());
                    } else {
                        $('input[id="final_donation_amount"]').val(defaultAmountBtn.val());
                    }
                    var donate_amount = $('input[id="final_donation_amount"]').val();
                    var result = getGcFromDollar(donate_amount);
                    var go = confirm('You are about to donate $' + donate_amount + ' for ' + result[0] + ' GC(s) with account name "' + donorUsername + '" to account name "' + donateTo + '".\n\nBy donating to the OwlGaming Community, you do understand that a refund will not be possible.\n\nAre you sure you want to do that?');
                    if (go) {
                        button.val("Processing..");
                        $("#donation_form").submit();
                        return true;
                    } else {
                        button.val("Donate");
                        return false;
                    }
                }
            }
        });
    }
    return false;
}

function ajax_load_top_donor() {
    document.getElementById("char_info_mid").innerHTML = '<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>';
    $.get("../ajax/ajax_load_top_donor.php", {
        
    }, function (data) {
        document.getElementById("char_info_mid").innerHTML = data;
        $("#hide_top_donor").click(function () {
            document.getElementById("char_info_mid").innerHTML = '';
        });
    });
}


