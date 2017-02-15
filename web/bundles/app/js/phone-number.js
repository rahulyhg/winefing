var errorMsg = $("#error-phone-number"),
    telInputDiv = $("#phone_number_group_div"),
    validMsg = $("#valid-phone-number");
telInput.intlTelInput({
        nationalMode: false,
        initialCountry: "auto",
        allowDropdown: true,
        geoIpLookup: function (callback) {
            $.get('https://ipinfo.io', function () {
            }, "jsonp").always(function (resp) {
                var countryCode = (resp && resp.country) ? resp.country : "";
                callback(countryCode);
            });
        },
        utilsScript: "../../../assets/vendor/intl-tel-input/build/js//utils.js"
    }
);

var reset = function() {
    telInputDiv.removeClass("has-error");
    errorMsg.addClass("hide");
    validMsg.addClass("hide");
};

// on blur: validate
telInput.blur(function() {
    reset();
    if ($.trim(telInput.val())) {
        if (telInput.intlTelInput("isValidNumber")) {
            validMsg.removeClass("hide");
        } else {
            telInputDiv.addClass("has-error");
            errorMsg.removeClass("hide");
        }
    }
});

// on keyup / change flag: reset
telInput.on("keyup change", reset);
