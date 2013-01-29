/**
 * Temando Pickup class for collection points.
 * - Implementation uses google maps API v3
 */

var Pickup = Class.create();

Pickup.prototype = {
    initialize: function(urls, dropdown, show_map, mapholder){
	this.show_map = show_map;
	if(show_map) {
	    this.geocoder = new google.maps.Geocoder();
	    var latlng = new google.maps.LatLng(-33.80, 151.104);
	    var mapOptions = {
		zoom: 13,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	    }
	    this.map = new google.maps.Map(document.getElementById(mapholder), mapOptions);
	}
	this.getLocationsUrl = urls.getLocations;
        this.setLocationsUrl = urls.setLocations;
	this.selectbox = $(dropdown);
        this.locations = []; 
	this.markers = [];
	
	this.defaultOption = new Option('No available pickup locations. Please enter delivery address', '');
	this.selectbox.options.add(this.defaultOption);
	this.currentLocation = null;
    },
    refresh: function() {
	if(this.show_map) {
	    google.maps.event.trigger(this.map, 'resize');
	}
    },
    getLocations: function(){
	var country = $('shipping:country_id').value;
	var pcode = $('shipping:postcode').value;
	var suburb = $('shipping:city').value;
	var pcs = $('shipping:postcode_pcs').value;
	
	if(!country || !pcode || !suburb || !pcs) {
	    alert('Please enter delivery address.');
	    return false;
	}
	if($('loading')) {
	    $('loading').show();
	}
	this.refresh();
	
	request = new Ajax.Request(
	    this.getLocationsUrl,
	    {
		method: 'post', 
		onSuccess: function(transport) {
		    var resp = transport.responseJSON;
		    if(resp.error) {
			alert(resp.error);
		    } else {
			this.pickup.locations = resp;
			if(!this.pickup.selectbox) {this.pickup.selectbox = $('pickup-location');}

			this.pickup.selectbox.options.length = 0;
			this.pickup.clearMarkers();

			if(this.pickup.locations.length == 0) {
			    this.pickup.selectbox.options.add(this.pickup.defaultOption);
			    this.pickup.currentLocation = null;
			} else {
			    for(var i=0; i < this.pickup.locations.length; i++) {
				if(i==0) {
				    this.pickup.currentLocation = this.pickup.locations[i];
				    this.pickup.showLocation(this.pickup.locations[i].description);
				}
				this.pickup.selectbox.options.add(new Option(this.pickup.locations[i].title, this.pickup.locations[i].description));
				this.pickup.setMarker(this.pickup.locations[i]);
			    }
			    //save locatoins in session for observer
			    this.pickup.setLocations();
			}
		    }
		    this.pickup.updateDeliveryAddress(this.pickup.selectbox.value);
		    $('current_location').value = Object.toJSON(this.pickup.currentLocation);
		    if($('loading')) {$('loading').hide();}
		},
		parameters: {country:country,pcode:pcode,suburb:suburb}
	    }
	);
		    
    },
    selectChange: function() {
	this.showLocation(this.selectbox.value);
	this.refresh();
	this.updateDeliveryAddress(this.selectbox.value);
	this.currentLocation = this.getLocationByDescription(this.selectbox.value);
	$('current_location').value = Object.toJSON(this.currentLocation);
    },
    showLocation: function(desc) {
	if(!this.show_map) return;
	
	var location = this.getLocationByDescription(desc);
	if(location) {
	    this.geocoder.geocode( {'address': location.title}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
		    window.pickup.map.setCenter(results[0].geometry.location);
		    
		}
	    })
	}
    },
    setLocations: function() {
	request = new Ajax.Request(
	    this.setLocationsUrl,
	    {
		method: 'post', 
		parameters: {locations:Object.toJSON(this.locations)},
		onSuccess: function(transport) {
		    var resp = transport.responseJSON;
		    if(resp.error) {
			alert(resp.error);
		    }
		}
	    }
	);
    },
    setMarker: function(location) {
	if(!this.show_map) return;
	
	this.geocoder.geocode( {'address': location.title}, function(results, status) {
	    if (status == google.maps.GeocoderStatus.OK) {
		var marker = new google.maps.Marker({
		    map: this.pickup.map,
		    position: results[0].geometry.location,
		    title: location.company_name
		});
		var infoWindow = new google.maps.InfoWindow();
		google.maps.event.addListener(marker, "click", function (e) {
                    infoWindow.setContent(location.company_name+'<br/>'+location.street+'<br/>'+location.code+', '+location.state);
                    infoWindow.open(this.map, marker);
                });
		this.pickup.markers.push(marker);
	    }
	});
    },
    clearMarkers: function() {
	if(!this.show_map) return;
	
	for(var i=0; i<this.markers.length; i++) {
	    this.markers[i].setMap(null);
	}
	this.markers = [];
    },
    getLocationByDescription: function(desc) {
	for(var i=0; i < this.locations.length; i++) {
	    if(this.locations[i].description == desc){
		return this.locations[i];
	    }
	}
	return false;
    },
    updateDeliveryAddress: function(desc){
	var location = this.getLocationByDescription(desc);
	if(location) {
	    if($('shipping:company')) {$('shipping:company').value = location.company_name;}
	    if($('shipping:street1')) {$('shipping:street1').value = location.street;}    
	}
    }
    
}


