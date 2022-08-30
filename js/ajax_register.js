function ajax_submit_register() {
    if ($('#submit_reg').val() == "Register") {
        var errorText = '';
        if ($('#reg_username').val().length < 3) {
            errorText += '*Username must contain at least 3 characters.<br>';
        }
        if (/^[a-zA-Z0-9-_]*$/.test($('#reg_username').val()) == false) {
            errorText += '*Username can only contain alphanumeric and underscores.<br>';
        }

        if (/\s/.test($('#reg_password1').val())) {
            errorText += '*Password must not contain any white spaces.<br>';
        }
        if ($('#reg_password1').val().length < 6) {
            errorText += '*Password must contain at least 6 characters.<br>';
        }

        if ($('#reg_password1').val() == $('#reg_username').val()) {
            errorText += '*Username and password can not be the same.<br>';
        }

        if ($('#reg_password1').val() != $('#reg_password2').val()) {
            errorText += '*Re-type password does not match.<br>';
        }

        if ($('#reg_referrer').val().length < 3 && $('#reg_referrer').val().length > 0) {
            errorText += '*Referrer must contain at least 3 characters.<br>';
        }
        if (($('#reg_referrer').val().length > 0) && (/^[a-zA-Z0-9-_]*$/.test($('#reg_referrer').val())) == false) {
            errorText += '*Referrer can only contain alphanumeric and underscores.<br>';
        }

        if ($('#reg_captcha').val().length < 5) {
            errorText += '*Captcha text is not completed.<br>';
        }

        $('#error_reg').html(errorText);
        if (errorText.length > 0) {
            return false;
        }
        $('#error_reg').html('');
        $('#submit_reg').val('Registering..');

        $.post("../ajax/ajax_register.php", {
            username: $('#reg_username').val(),
            password: $('#reg_password1').val(),
            email: $('#reg_email').val(),
            referrer: $('#reg_referrer').val(),
            captcha: $('#reg_captcha').val(),
        }, function (errors) {
            $('#error_reg').html('');
            if (!errors || errors.lenth == 0) {
                $('#submit_reg').val('Register');
                $('#error_reg').html('Server is busy at the moment, please try again later.');
                return false;
            } else if (!isNaN(errors)) {
                alert("Congratulations! Your OwlGaming MTA account (ID#'" + errors + ") is almost ready for action! \n\nFollow this link that we dispatched to your email address to finish the final step to activate your MTA account.");
                window.location.href = 'index.php';
                return false;
            } else {
                $('#error_reg').html(errors);
                $('#submit_reg').val('Register');
                return false;
            }
        });
    }
    return false;
}

function resetError() {
    $('#error_reg').html('');
}

function ajax_reload_captcha() {
    $('#img_captcha').html('<img src="captcha/captcha.php?access_key=' + Math.random() + '" />');
}

function ajax_resend_activation_email() {
    if ($('#submit_reg').val() == "Activate") {
        var errorText = '';
        if ($('#reg_username').val().length < 3) {
            errorText += '*Username must contain at least 3 characters.<br>';
        }
        if (/^[a-zA-Z0-9-_]*$/.test($('#reg_username').val()) == false) {
            errorText += '*Username can only contain alphanumeric and underscores.<br>';
        }

        if ($('#reg_captcha').val().length < 5) {
            errorText += '*Captcha text is not completed.<br>';
        }

        $('#error_reg').html(errorText);
        if (errorText.length > 0) {
            return false;
        }
        $('#error_reg').html('');
        $('#submit_reg').val('Working..');
        
        $.post("../ajax/ajax_email_activation.php", {
            username: $('#reg_username').val(),
            captcha: $('#reg_captcha').val(),
        }, function (errors) {
            $('#error_reg').html('');
            if (errors == "ok") {
                alert("Your OwlGaming MTA account is almost ready for action! \n\nFollow this link that we dispatched to your email address to finish the final step to activate your MTA account.");
                window.location.href = 'index.php';
                return false;
            } else {
                $('#error_reg').html(errors);
                $('#submit_reg').val('Activate');
                return false;
            }
        });

        return false;
    }
    return false;
}