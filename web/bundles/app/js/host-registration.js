/**
 * Created by audreycarval on 18/01/2017.
 */
$(document).ready(function () {
    var navListItems = $('div.setup-panel div a'),
        allWells = $('.setup-content'),
        allNextBtn = $('.nextBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
            $item = $(this);
        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            $item.addClass('btn-primary');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }


    });
    allNextBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='password'],input[type='email']"),
            isValid = true,
            formatEmailIsValid=true,
            firstEmail ='',
            secondEmail ='',
            nbEmailInvalid = 0,
            firstPassword ='',
            secondPassword='',
            nbPasswordInvalid = 0;

        $(".form-group").removeClass("has-error");
        if($('#error-phone-number').is(":visible")) {
            isValid = false;
        }
        for(var i=0; i<curInputs.length; i++){
            // setTimeout(curInputs[i], function() {
            //     $(this).tooltip('destroy');
            // }, 2000);
            $(this).tooltip('destroy');
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
                if(curInputs[i].type === 'email') {
                    nbEmailInvalid++;
                } else if(curInputs[i].type === 'password') {
                    nbPasswordInvalid++;
                }
            } else if(curInputs[i].type === 'email') {
                if(!firstEmail) {
                    firstEmail = curInputs[i];
                } else {
                    secondEmail = curInputs[i];
                }
            }else if(curInputs[i].type === 'password') {
                if(!firstPassword) {
                    firstPassword = curInputs[i];
                } else {
                    secondPassword = curInputs[i];
                }
            }
            $(curInputs[i]).on('keypress', function(){
                $(this).tooltip('hide');
            });
        }
        if(nbEmailInvalid==0) {
            if(!checkCorrepondance(secondEmail, firstEmail, secondEmail)){
                isValid = false;
            }
        }else if(nbPasswordInvalid == 0){
            if(checkCorrepondance(secondPassword, firstPassword, secondPassword)) {
                isValid= false;
                if(!checkFormatPassword(firstPassword)){
                    isValid = false;
                }
            }
        }
        if (isValid)
            nextStepWizard.removeAttr('disabled').trigger('click');
    });
});