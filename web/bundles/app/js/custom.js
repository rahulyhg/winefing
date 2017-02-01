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
    })
    .on('submit','#propertyCategory', function(){
        $("#propertyCategory :disabled").removeAttr('disabled');
    });
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $('div.setup-panel div a.btn-primary').trigger('click');

    $('#Carousel').carousel({
        interval: 5000
    });
})
;


//*********************************************DROPZONE*************************************************************************//
//remove to a simple color
function removePresentationCss(imgPresentation) {
    //change the star to an empty star
    $(imgPresentation).children().removeAttr().attr('class','glyphicon glyphicon-star-empty').attr('style', 'cursor: pointer!;');

    //remove the yellow image border
    $(imgPresentation).closest('.dz-details').prev('.dz-image').removeAttr('style');

    //change the data value
    $(imgPresentation).attr('data-value', false);
}
//remove to a yellow digusting color
function addPresentationCss(element) {
    //add the yellow image border
    $(element).closest('.dz-details').prev('.dz-image').attr('style', 'border: solid 4px #f1c40f !important;');

    //change the star to an full star + yellow color
    $(element).children().removeAttr().attr('class','glyphicon glyphicon-star').attr('style', 'cursor: pointer!important;color:#f1c40f!important;');

    //change the data value
    $(element).attr('data-value', true);
}
//remove a picture from the database and the dropzone
function removeFile(id, url) {
    var result = confirm(confirmationDelete);
    if (result) {
        $.ajax({
            url: url.replace("id", id),
            type: 'DELETE',
            async: false,
            success: function (data, textStatus, jqXHR) {
                //remove file from the dropzone
                $('#'+id).remove();
                $.toast({
                    heading: 'Success',
                    text: 'And these were just the basic demos! Scroll down to check further details on how to customize the output.',
                    showHideTransition: 'slide',
                    icon: 'success',
                    position: 'top-right'
                })
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $.toast({
                    heading: 'Error',
                    text: 'Report any <a href="https://github.com/kamranahmedse/jquery-toast-plugin/issues">issues</a>',
                    showHideTransition: 'fade',
                    icon: 'error',
                    position: 'top-right'
                });
            }
        });
    }
}
//change presentation picture
var  urlMediaPresentation = '';
function presentationPicture(id, scope) {
    var url = urlMediaPresentation;
    var presentation = true;
    var a = $('#'+id).find("a#presentation");
    if ($(a).attr('data-value') === 'true') {
        presentation = false;
    }
    $.ajax({
        url: url.replace("id", id),
        type: 'PATCH',
        data: {'presentation': presentation, 'scope': scope},
        async: false,
        success: function (data, textStatus, jqXHR) {
            if(presentation) {
                var imgPresentation = $("a[data-value='true']");
                if(imgPresentation) {
                    removePresentationCss(imgPresentation);
                }
                addPresentationCss(a);
            } else {
                removePresentationCss(a);
            }
            $.toast({
                heading: 'Success',
                text: 'And these were just the basic demos! Scroll down to check further details on how to customize the output.',
                showHideTransition: 'slide',
                icon: 'success',
                position: 'top-right'
            })
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $.toast({
                heading: 'Error',
                text: 'Report any <a href="https://github.com/kamranahmedse/jquery-toast-plugin/issues">issues</a>',
                showHideTransition: 'fade',
                icon: 'error',
                position: 'top-right'
            })
        }
    });
}
var deletePath = "", scope = "";
function setAddElement(file, media) {
    var _ref = file.previewElement.querySelectorAll("a");
    _ref[0].setAttribute('href', upload + media.name);

    //change the presentation picture
    _ref[1].setAttribute('onClick', "presentationPicture("+media.id+","+ "'"+scope + "'"+ ");");
    _ref[1].setAttribute('data-media', media.presentation);

    //delete picture
    _ref[2].setAttribute('onClick', "removeFile("+media.id+","+ "'"+ deletePath + "'"+ ");");

    //set the id to the div
    div = file.previewElement.querySelector("div").parentNode;
    div.setAttribute('id', media.id);
}
//*********************************************END DROPZONE*************************************************************************//


