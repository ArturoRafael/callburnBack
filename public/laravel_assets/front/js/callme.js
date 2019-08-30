
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#submit").click(function () {

        $(this).attr('disabled',true);
        let data = {};
        let subDomain = $("#sub-domain");
        data.subdomain = subDomain.val();

        $.ajax({
            url : "/front-data/check-snippet-domain",
            type: "POST",
            data : data
        }).done(function(data) {

            $("#submit").removeAttr('disabled');

            if(data.resource.error.no === 0) {

                window.location.assign(data.resource.subdomain);

            } else {

                subDomain.addClass("wrong-border-style animated bounce");
                $("#error-message").removeClass('hidden');

                setTimeout(function () {

                    subDomain.removeClass("animated bounce");

                },2000);
            }

        }).fail(function() {

            $("#submit").removeAttr('disabled');
            subDomain.addClass("wrong-border-style animated bounce");
            $("#error-message").removeClass('hidden');

            setTimeout(function () {

                subDomain.removeClass("animated bounce");

            },2000);


        });

    });


    $("#sub-domain").keydown(function () {

        $("#error-message").addClass('hidden');
        $(this).removeClass('wrong-border-style'); //

    });


    $(document).keypress(function(e) {
        if(e.which === 13) {
            $("#submit").click();
        }
    });

});