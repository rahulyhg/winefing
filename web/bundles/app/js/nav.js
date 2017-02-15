/**
 * Created by audreycarval on 24/01/2017.
 */
$(document).ready(function () {
    var url = document.location.toString();
    if (url.match('#')) {
        console.log('in');
        $('.nav-pills a[href="#' + url.split('#')[1] + '"]').tab('show', function() {
            $('nav:first').focus();
        });
    } else if(nav.length > 0) {
            $('.nav-pills a[href="#' + nav + '"]').tab('show', function() {
                $('nav:first').focus();
            });
    }
});