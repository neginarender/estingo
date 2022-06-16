function initialize() {
	if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
		var pos = new google.maps.LatLng(position.coords.latitude,
										   position.coords.longitude);
		document.querySelector( '.latitude' ).value = position.coords.latitude;
		document.querySelector( '.longitude' ).value = position.coords.longitude;
	 }, function() {
		  	handleNoGeolocation(true);
		});
	 } else {
			handleNoGeolocation(false);
	 }
	
  	
	function handleNoGeolocation(errorFlag) {
	  	if (errorFlag) {
			var content = 'Error: The Geolocation service failed.';
	  	} else {
			var content = 'Error: Your browser doesn\'t support geolocation.';
	  	}
	  	var options = {
			map: map,
			position: new google.maps.LatLng(postion()),
	  	};
	  	var marker = new google.maps.Marker(options);
	  	map.setCenter(options.position);
	}
	if(navigator.geolocation) {
		 navigator.geolocation.getCurrentPosition(function(position) {
		var mapOptions, map, marker, searchBox, city,
			infoWindow = '',
			addressEl = document.querySelector( '#map-search' ),
			latEl = document.querySelector( '.latitude' ),
			longEl = document.querySelector( '.longitude' ),
			element = document.getElementById( 'map-canvas' );
			address_loc = document.querySelector( '.reg-input-address' );
			city = document.querySelector( '.reg-input-city' );
			state = document.querySelector( '.reg-input-state' );
			country = document.querySelector( '.reg-input-country' );
			postal = document.querySelector( '.reg-input-postal' );
		mapOptions = {
			zoom:15,
			center: new google.maps.LatLng( position.coords.latitude, position.coords.longitude ),
			// center : {
			// 	lat: -34.397,
			// 	lng: 150.644
			// },
			disableDefaultUI: false, 
			scrollWheel: true,
			draggable: true,
			// mapTypeId: google.maps.MapTypeId.HYBRID,
			// maxZoom: 11,
			// minZoom: 9

		};

		
		map = new google.maps.Map( element, mapOptions );
		marker = new google.maps.Marker({
			position: mapOptions.center,
			map: map,
			 //icon: 'api.png',
			draggable: true
		});

	/**
	 * Creates a search box
	 */
	searchBox = new google.maps.places.SearchBox( addressEl );
	google.maps.event.addListener( searchBox, 'places_changed', function () {
		var places = searchBox.getPlaces(),
			bounds = new google.maps.LatLngBounds(),
			i, place, lat, long, resultArray,
			addresss = places[0].formatted_address;

		for( i = 0; place = places[i]; i++ ) {
			bounds.extend( place.geometry.location );
			marker.setPosition( place.geometry.location ); 
		}

		map.fitBounds( bounds );
		map.setZoom( 15 );
		
		lat = marker.getPosition().lat();
		long = marker.getPosition().lng();
		latEl.value = lat;
		longEl.value = long;

		resultArray =  places[0].address_components;
		
		for( var i = 0; i < resultArray.length; i++ ) {
			if ( resultArray[ i ].types[0] && 'route' === resultArray[ i ].types[0] ) {
				route = resultArray[ i ].long_name;
			}
			if ( resultArray[ i ].types[0] && 'locality' === resultArray[ i ].types[0] ) {
				locality = resultArray[ i ].long_name;
			}
			if ( resultArray[ i ].types[0] && 'administrative_area_level_1' === resultArray[ i ].types[0] ) {
				states = resultArray[ i ].long_name;
				state.value = states;
			}
			if ( resultArray[ i ].types[0] && 'country' == resultArray[ i ].types[0] ) {
				countries = resultArray[ i ].long_name;
				country.value = countries;
			}
			if ( resultArray[ i ].types[0] && 'postal_code' == resultArray[ i ].types[0] ) {
				postalCode = resultArray[ i ].long_name;
				postal.value = postalCode;
			}
			if ( resultArray[ i ].types[0] && 'administrative_area_level_2' === resultArray[ i ].types[0] ) {
				citi = resultArray[ i ].long_name;
				city.value = citi;
			}
		}
		//address_loc.value = locality + ', '+ route;
		latEl.value = lat;
		longEl.value = long;

		// Closes the previous info window if it already exists
		if ( infoWindow ) {
			infoWindow.close();
		}
		
		infoWindow = new google.maps.InfoWindow({
			content: addresss
		});

		infoWindow.open( map, marker );
	} );


	google.maps.event.addListener( marker, "dragend", function ( event ) {
		var lat, long, address, resultArray, states, countries, postalCode, citi;

		lat = marker.getPosition().lat();
		long = marker.getPosition().lng();
		
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode( { latLng: marker.getPosition() }, function ( result, status ) {
			if ( 'OK' === status ) {  // This line can also be written like if ( status == google.maps.GeocoderStatus.OK ) {
				address = result[0].formatted_address;
				resultArray =  result[0].address_components;
				// Get the city and set the city input value to the one selected
				for( var i = 0; i < resultArray.length; i++ ) {
					
					if ( resultArray[ i ].types[0] && 'route' === resultArray[ i ].types[0] ) {
						route = resultArray[ i ].long_name;
					}
					if ( resultArray[ i ].types[0] && 'locality' === resultArray[ i ].types[0] ) {
						locality = resultArray[ i ].long_name;
					}
					if ( resultArray[ i ].types[0] && 'administrative_area_level_1' === resultArray[ i ].types[0] ) {
						states = resultArray[ i ].long_name;
						state.value = states;
					}
					if ( resultArray[ i ].types[0] && 'country' == resultArray[ i ].types[0] ) {
						countries = resultArray[ i ].long_name;
						country.value = countries;
					}
					if ( resultArray[ i ].types[0] && 'postal_code' == resultArray[ i ].types[0] ) {
						postalCode = resultArray[ i ].long_name;
						postal.value = postalCode;
					}
					if ( resultArray[ i ].types[0] && 'administrative_area_level_2' === resultArray[ i ].types[0] ) {
						citi = resultArray[ i ].long_name;
						city.value = citi;
					}
				}
				addressEl.value = address;
				//address_loc.value = locality + ', '+ route
				latEl.value = lat;
				longEl.value = long;

			} else {
				console.log( 'Geocode was not successful for the following reason: ' + status );
			}

			// Closes the previous info window if it already exists
			if ( infoWindow ) {
				infoWindow.close();
			}

			infoWindow = new google.maps.InfoWindow({
				content: address
			});

			infoWindow.open( map, marker );
		} );
	});
	 });
	}

}