/**
 * Created by audreycarval on 18/01/2017.
 */
function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
                center: geolocation,
                radius: position.coords.accuracy
            });
            address.setBounds(circle.getBounds());
        });
    }
};
var placeSearch, address, adressId = '';
var componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    country: 'long_name',
    postal_code: 'short_name'
};
var componentFormId = {};
function initAutocomplete() {
    // Create the autocomplete object, restricting the search to geographical
    // location types.
    address = new google.maps.places.Autocomplete(
        /** @type {!HTMLInputElement} */(document.getElementById(adressId)),
        {types: ['geocode']});

    // When the user selects an address from the dropdown, populate the address
    // fields in the form.
    address.addListener('place_changed', fillInAddress);
}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = address.getPlace();

    for (var component in componentForm) {
        document.getElementById(componentFormId[component]).value = '';
        document.getElementById(componentFormId[component]).disabled = false;
    }

    // Get each component of the address from the place details
    // and fill the corresponding field on the form.
    for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        if (componentForm[addressType]) {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById(componentFormId[addressType]).value = val;
        }
    }
}
function geocodeAddress(adressId) {
    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById(adressId).value;
    geocoder.geocode( { 'address': address}, function(results, status) {
        console.log(results[0].geometry.location.lng());
        console.log(results[0].geometry.location.lat());
        if (status == google.maps.GeocoderStatus.OK) {
            return true;
        }
    });

}

geocodeAddressFinal = function(f){
    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById(adressId).value;
    if(typeof address != 'undefined' && address != null) {
        geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                console.log(results[0].geometry.location.lat());
                f(results);
            } else {
                console.log('notin')
                f(null);
            }
        });
    }
    return -1;
};

$("#suivant2").on('click', function() {
    geocodeAddressFinal(document.getElementById(adressId).value, function(res) {
        if(res != null) {
            console.log('lol' + res[0]['geometry']['location']);
            console.log('lol' + res[0].geometry.location.lat());
            $('#country').prop("disabled", false);
            $('#locality').prop("disabled", false);
            $('#postal_code').prop("disabled", false);
            var data = $('#property').serializeArray(); // convert form to array
            data.push({name: "name", value: document.getElementById("name").value});
            data.push({name: "lng", value: res[0].geometry.location.lng()});
            data.push({name: "lat", value: res[0].geometry.location.lat()});
            $.ajax({
                url: "{{ path('property_registration_add') }}",
                type: 'POST',
                data: $('#property').serialize(),
                async: false,
                success: function (data, textStatus, jqXHR) {
                },
                error: function (jqXHR, textStatus, errorThrown) {
                }
            });
        } else {
            console.log('not good');
        }
    });

});
