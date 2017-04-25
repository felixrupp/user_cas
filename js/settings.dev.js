$(document).ready(function () {

    $('#user_cas #casSettings').tabs();


    $("#user_cas #cas_force_login").on('change', function (event) {

        if ($(this).is(':checked')) {

            $("#user_cas #cas_disable_logout").attr("disabled", false);
            document.getElementById("cas_redirect_after_logout").disabled = false;
        }
        else {

            $("#user_cas #cas_disable_logout").attr("disabled", true);
            document.getElementById("cas_redirect_after_logout").disabled = true;
        }
    });

    $("#user_cas #casSettingsSubmit").on('click', function (event) {

        event.preventDefault();

        //console.log("Submit button clicked.");

        var postData = $('#user_cas').serialize();
        var method = $('#user_cas').attr('method');
        var url = OC.generateUrl('/apps/user_cas/settings/save');

        $.ajax({
            method: method,
            url: url,
            data: postData,
            success: function (data) {

                var notification = OC.Notification.show("CAS settings have been successfully saved.");

                setTimeout(function () {
                    OC.Notification.hide(notification);
                }, 5000);

            },
            error: function (data) {

                var notification = OC.Notification.show("CAS settings have not been saved.");

                setTimeout(function () {
                    OC.Notification.hide(notification);
                }, 5000);
            }
        });
    });
});
