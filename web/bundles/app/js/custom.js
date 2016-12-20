/**
 * Created by audreycarval on 25/10/2016.
 */
function setPathDeleteButton(path) {
    console.log(path);
    $("#delete").attr("href", path);
    $('#confirmation').modal();
}

function loadForm(path, modalId) {
    $.ajax({
        url: path,
        type: 'GET',
        async: false,
        success: function (data, textStatus, jqXHR) {
            $('#modalCreate').html(data);
            $('#' + modalId).modal();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log("no good");
        }
    });
}
function activate(path, checkBox, id) {
    var checkBoxId = checkBox + '-' + id;
    if (document.getElementById(checkBoxId).checked) {
        activatedValue = 1;
    } else {
        activatedValue = 0;
    }
    $.ajax({
        url: path,
        type: 'PUT',
        data: {activated: activatedValue, id: id},
        async: false,
        success: function (data, textStatus, jqXHR) {
            console.log(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log("no good");
        }
    });
}

$(document)
    .on('submit','#characteristicCategory', function(){
    $("#characteristicCategory :disabled").removeAttr('disabled');
    })
    .on('submit','#wineRegion', function(){
    $("#wineRegion :disabled").removeAttr('disabled');
    })
    .on('submit','#subscription', function(){
    $("#subscription :disabled").removeAttr('disabled');
    })
    .on('submit','#tag', function(){
    $("#tag :disabled").removeAttr('disabled');
    })
    .on('submit','#articleCategory', function(){
        $("#articleCategory :disabled").removeAttr('disabled');
    })
    .on('submit','#articleTr', function(){
        $("#articleTr :disabled").removeAttr('disabled');
    })
    .on('submit','#characteristic', function(){
        $("#characteristic :disabled").removeAttr('disabled');
    })
    .on('submit','#box', function(){
        $("#box :disabled").removeAttr('disabled');
    })
    .on('submit','#boxItem', function(){
        $("#boxItem :disabled").removeAttr('disabled');
    })
    .on('submit','#boxItemChoice', function(){
        $("#boxItemChoice :disabled").removeAttr('disabled');
    });

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
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid)
            nextStepWizard.removeAttr('disabled').trigger('click');
    });

    $('div.setup-panel div a.btn-primary').trigger('click');

    $('#Carousel').carousel({
        interval: 5000
    });

    var url = document.location.toString();
    if (url.match('#')) {
        console.log('in');
        $('.nav-pills a[href="#' + url.split('#')[1] + '"]').tab('show', function() {
            console.log('test');
            $('html,body').animate({scrollTop: $(this).offset().top}, 500);
        });
    }
});
