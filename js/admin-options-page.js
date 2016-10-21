/* global ajaxurl, socialWarfarePlugin, wp */
(function( window, $, undefined ) {
	'use strict';

	/*********************************************************
		A Function send the array of setting to ajax.php
	*********************************************************/
	$.fn.selectText = function() {
		var doc = document, element = this[0], range, selection;

		if ( doc.body.createTextRange ) {
			range = document.body.createTextRange();

			range.moveToElementText( element );

			range.select();
		} else if ( window.getSelection ) {
			selection = window.getSelection();

			range = document.createRange();

			range.selectNodeContents( element );

			selection.removeAllRanges();

			selection.addRange( range );
		}
	};

	/*********************************************************
		A Function to gather all the settings
	 *********************************************************/
	function fetchAllOptions() {
		// Create an object
		var values = {};

		// Loop through all the inputs
		$( 'form.sw-admin-settings-form input, form.sw-admin-settings-form select' ).each( function() {
			var $field = $( this );

			var name = $field.attr( 'name' );
			var value;

			if ( 'checkbox' === $field.attr( 'type' ) ) {
				value = $field.prop( 'checked' );
			} else {
				value = $field.val();
			}

			values[name] = value;
		});

		// Create the objects
		values.newOrderOfIcons = {};

		// Loop through each active network
		$( '.sw-active i' ).each( function() {
			var network = $( this ).data( 'network' );
			values.newOrderOfIcons[network] = network;
		});

		return values;
	}

	/*********************************************************
		A function to show/hide conditionals
	*********************************************************/
	function conditionalFields() {
		// Loop through all the fields that have dependancies
		$( 'div[dep]' ).each( function() {
			// Fetch the conditional values
			var conDep = $( this ).attr( 'dep' );

			var conDepVal = $.parseJSON( $( this ).attr( 'dep_val' ) );
			var value;

			// Fetch the value of checkboxes or other input types
			if ( $( '[name="' + conDep + '"]' ).attr( 'type' ) == 'checkbox' ) {
				value = $( '[name="' + conDep + '"]' ).prop( 'checked' );
			} else {
				value = $( '[name="' + conDep + '"]' ).val();
			}

			// Show or hide based on the conditional values (and the dependancy must be visible in case it is dependant)
			if ( $.inArray( value, conDepVal ) !== -1 && $( '[name="' + conDep + '"]' ).parent( '.sw-grid' ).is( ':visible' ) ) {
				$( this ).show();
			} else {
				$( this ).hide();
			}
		});
	}

	/*********************************************************
		Header Menu
	*********************************************************/
	function headerMenuInit() {
		var offset = $( '.sw-top-menu' ).offset();

		var width = $( '.sw-top-menu' ).width();

		$( '.sw-top-menu' ).css({
			position: 'fixed',
			left: offset.left,
			top: offset.top,
			width: width
		});

		$( '.sw-admin-wrapper' ).css( 'padding-top', '75px' );
	}

	/*********************************************************
		Tab Navigation
	*********************************************************/
	function tabNavInit() {
		$( '.sw-tab-selector' ).on( 'click', function( event ) {
			event.preventDefault ? event.preventDefault() : ( event.returnValue = false );

			$( 'html, body' ).animate( { scrollTop: 0 }, 0 );

			var tab = $( this ).attr( 'data-link' );

			$( '.sw-admin-tab' ).hide();

			$( '#' + tab ).show();

			$( '.sw-header-menu li' ).removeClass( 'sw-active-tab' );

			$( this ).parents( 'li' ).addClass( 'sw-active-tab' );

			if ( 'swp_styles' === tab ) {
				socialWarfarePlugin.activateHoverStates();
			}

			conditionalFields();

		});
	}

	/*********************************************************
		Checkboxes
	*********************************************************/
	function checkboxesInit() {

		$( '.sw-checkbox-toggle' ).on( 'click', function() {
			var status = $( this ).attr( 'status' );

			var elem = $( this ).attr( 'field' );

			if ( 'on' === status ) {
				$( this ).attr( 'status', 'off' );

				$( elem ).prop( 'checked', false );
			} else {
				$( this ).attr( 'status', 'on' );

				$( elem ).prop( 'checked', true );
			}

			saveColorToggle();

			conditionalFields();
		});
	}

	function populateOptions() {
		$( 'form.sw-admin-settings-form input, form.sw-admin-settings-form select' ).on( 'change', function() {
			conditionalFields();

			socialWarfarePlugin.newOptions = fetchAllOptions();

			saveColorToggle();
		});

		socialWarfarePlugin.defaultOptions = fetchAllOptions();
	}

	/*********************************************************
		A Function to change the color of the save button
	 *********************************************************/
	function saveColorToggle() {
		socialWarfarePlugin.newOptions = fetchAllOptions();

		if ( JSON.stringify( socialWarfarePlugin.newOptions ) !== JSON.stringify( socialWarfarePlugin.defaultOptions ) ) {
			$( '.sw-save-settings' ).removeClass( 'sw-navy-button' ).addClass( 'sw-red-button' );
		} else {
			$( '.sw-save-settings' ).removeClass( 'sw-red-button' ).addClass( 'sw-navy-button' );
		}
	}

	/*********************************************************
		A Function send the array of setting to ajax.php
	*********************************************************/
	function handleSettingSave() {
		$( '.sw-save-settings' ).on( 'click', function( event ) {
			// Block the default action
			event.preventDefault ? event.preventDefault() : ( event.returnValue = false );

			// The loading screen
			loadingScreen();

			// Fetch all the settings
			var settings = fetchAllOptions();

			// Prepare date
			var data = {
				action: 'swp_store_settings',
				settings: settings
			};

			// Send the POST request
			$.post( ajaxurl, data, function() {
				// Clear the loading screen
				clearLoadingScreen();

				// Reset the default options variable
				socialWarfarePlugin.defaultOptions = fetchAllOptions();

				saveColorToggle();
			});
		});
	}

	function loadingScreen() {
		$( 'body' ).append( '<div class="sw-loading-bg"><div class="sw-loading-message">Saving Changes</div></div>' );
	}

	function clearLoadingScreen() {
		$( '.sw-loading-message' ).html( 'Success!' ).removeClass( 'sw-loading-message' ).addClass( 'sw-loading-complete' );

		$( '.sw-loading-bg' ).delay( 1000 ).fadeOut( 1000 );

		setTimeout( function() {
			$( '.sw-loading-bg' ).remove();
		}, 2000 );
	}

	function updateCustomColor() {
		var visualTheme  = $( 'select[name="visualTheme"]' ).val();
		var dColorSet    = $( 'select[name="dColorSet"]' ).val();
		var iColorSet    = $( 'select[name="iColorSet"]' ).val();
		var oColorSet    = $( 'select[name="oColorSet"]' ).val();

		$( 'style.swp_customColorStuff' ).remove();

		var colorCode = $( 'input[name="customColor"]' ).val();

		var customCSS = '';

		if ( dColorSet == 'customColor' || iColorSet == 'customColor' || oColorSet == 'customColor' ) {
			customCSS = '.nc_socialPanel.swp_d_customColor a, html body .nc_socialPanel.swp_i_customColor .nc_tweetContainer:hover a, body .nc_socialPanel.swp_o_customColor:hover a {color:white} .nc_socialPanel.swp_d_customColor .nc_tweetContainer, html body .nc_socialPanel.swp_i_customColor .nc_tweetContainer:hover, body .nc_socialPanel.swp_o_customColor:hover .nc_tweetContainer {background-color:' + colorCode + ';border:1px solid ' + colorCode + ';}';
		}

		if ( dColorSet == 'ccOutlines' || iColorSet == 'ccOutlines' || oColorSet == 'ccOutlines' ) {
			customCSS = customCSS + ' .nc_socialPanel.swp_d_ccOutlines a, html body .nc_socialPanel.swp_i_ccOutlines .nc_tweetContainer:hover a, body .nc_socialPanel.swp_o_ccOutlines:hover a { color:' + colorCode + '; } .nc_socialPanel.swp_d_ccOutlines .nc_tweetContainer, html body .nc_socialPanel.swp_i_ccOutlines .nc_tweetContainer:hover, body .nc_socialPanel.swp_o_ccOutlines:hover .nc_tweetContainer { background:transparent; border:1px solid ' + colorCode + '; }';
		}

		$( 'head' ).append( '<style type="text/css" class="swp_customColorStuff">' + customCSS + '</style>' );
	}

	// A function for updating the preview
	function updateTheme() {
		var visualTheme  = $( 'select[name="visualTheme"]' ).val();
		var dColorSet    = $( 'select[name="dColorSet"]' ).val();
		var iColorSet    = $( 'select[name="iColorSet"]' ).val();
		var oColorSet    = $( 'select[name="oColorSet"]' ).val();
		var buttonsClass = 'swp_' + visualTheme + ' swp_d_' + dColorSet + ' swp_i_' + iColorSet + ' swp_o_' + oColorSet;

		// Declare a default lastClass based on the default HTML if we haven't declared one
		if('undefined' === typeof socialWarfarePlugin.lastClass){
			console.log('boom');
			socialWarfarePlugin.lastClass = 'swp_flatFresh swp_d_fullColor swp_i_fullColor swp_o_fullColor';
		}
		// Put together the new classes, remove the old ones, add the new ones, store the new ones for removal next time.
		var buttonsClass = 'swp_' + visualTheme + ' swp_d_' + dColorSet + ' swp_i_' + iColorSet + ' swp_o_' + oColorSet;
		$( '.nc_socialPanel' ).removeClass( socialWarfarePlugin.lastClass ).addClass( buttonsClass );
		socialWarfarePlugin.lastClass = buttonsClass;

		var lastClass = buttonsClass;

		if ( dColorSet == 'customColor' || dColorSet == 'ccOutlines' || iColorSet == 'customColor' || iColorSet == 'ccOutlines' || oColorSet == 'customColor' || oColorSet == 'ccOutlines' ) {
			$( '.customColor_wrapper' ).slideDown();

			updateCustomColor();
		} else {
			$( '.customColor_wrapper' ).slideUp();
		}
	}

	/*********************************************************
		A Function to update the preview buttons
	*********************************************************/

	function updateButtonPreviews() {

		var availableOptions = {
			flatFresh: {
				fullColor: 'Full Color',
				lightGray: 'Light Gray',
				mediumGray: 'Medium Gray',
				darkGray: 'Dark Gray',
				lgOutlines: 'Light Gray Outlines',
				mdOutlines: 'Medium Gray Outlines',
				dgOutlines: 'Dark Gray Outlines',
				colorOutlines: 'Color Outlines',
				customColor: 'Custom Color',
				ccOutlines: 'Custom Color Outlines'
			},
			leaf: {
				fullColor: 'Full Color',
				lightGray: 'Light Gray',
				mediumGray: 'Medium Gray',
				darkGray: 'Dark Gray',
				lgOutlines: 'Light Gray Outlines',
				mdOutlines: 'Medium Gray Outlines',
				dgOutlines: 'Dark Gray Outlines',
				colorOutlines: 'Color Outlines',
				customColor: 'Custom Color',
				ccOutlines: 'Custom Color Outlines'
			},
			pill: {
				fullColor: 'Full Color',
				lightGray: 'Light Gray',
				mediumGray: 'Medium Gray',
				darkGray: 'Dark Gray',
				lgOutlines: 'Light Gray Outlines',
				mdOutlines: 'Medium Gray Outlines',
				dgOutlines: 'Dark Gray Outlines',
				colorOutlines: 'Color Outlines',
				customColor: 'Custom Color',
				ccOutlines: 'Custom Color Outlines'
			},
			threeDee: {
				fullColor: 'Full Color',
				lightGray: 'Light Gray',
				mediumGray: 'Medium Gray',
				darkGray: 'Dark Gray'
			},
			connected: {
				fullColor: 'Full Color',
				lightGray: 'Light Gray',
				mediumGray: 'Medium Gray',
				darkGray: 'Dark Gray',
				lgOutlines: 'Light Gray Outlines',
				mdOutlines: 'Medium Gray Outlines',
				dgOutlines: 'Dark Gray Outlines',
				colorOutlines: 'Color Outlines',
				customColor: 'Custom Color',
				ccOutlines: 'Custom Color Outlines'
			},
			shift: {
				fullColor: 'Full Color',
				lightGray: 'Light Gray',
				mediumGray: 'Medium Gray',
				darkGray: 'Dark Gray',
				lgOutlines: 'Light Gray Outlines',
				mdOutlines: 'Medium Gray Outlines',
				dgOutlines: 'Dark Gray Outlines',
				colorOutlines: 'Color Outlines',
				customColor: 'Custom Color',
				ccOutlines: 'Custom Color Outlines'
			}
		};

		// Check if we are on the admin page
		if ( 0 === $( 'select[name="visualTheme"]' ).length ) {
			return;
		}

		// Update the items and previews on the initial page load
		var visualTheme = $( 'select[name="visualTheme"]' ).val();
		var dColorSet   = $( 'select[name="dColorSet"]' ).val();
		var iColorSet   = $( 'select[name="iColorSet"]' ).val();
		var oColorSet   = $( 'select[name="oColorSet"]' ).val();

		$( 'select[name="dColorSet"] option, select[name="iColorSet"] option, select[name="oColorSet"] option' ).remove();

		$.each( availableOptions[visualTheme], function( index, value ) {
			if ( index === dColorSet ) {
				$( 'select[name="dColorSet"]' ).append( '<option value="' + index + '" selected>' + value + '</option>' );
			} else {
				$( 'select[name="dColorSet"]' ).append( '<option value="' + index + '">' + value + '</option>' );
			}

			if ( index === iColorSet ) {
				$( 'select[name="iColorSet"]' ).append( '<option value="' + index + '" selected>' + value + '</option>' );
			} else {
				$( 'select[name="iColorSet"]' ).append( '<option value="' + index + '">' + value + '</option>' );
			}

			if ( index === oColorSet ) {
				$( 'select[name="oColorSet"]' ).append( '<option value="' + index + '" selected>' + value + '</option>' );
			} else {
				$( 'select[name="oColorSet"]' ).append( '<option value="' + index + '">' + value + '</option>' );
			}

			if ( dColorSet == 'customColor' || dColorSet == 'ccOutlines' || iColorSet == 'customColor' || iColorSet == 'ccOutlines' || oColorSet == 'customColor' || oColorSet == 'ccOutlines' ) {
				$( '.customColor_wrapper' ).slideDown();

				updateCustomColor();
			} else {
				$( '.customColor_wrapper' ).slideUp();
			}
		});

		// If the color set changes, update the preview with the function
		$( 'select[name="dColorSet"], select[name="iColorSet"], select[name="oColorSet"]' ).on( 'change', updateTheme );

		// If the visual theme is updated, update the preview manually
		$( 'select[name="visualTheme"]' ).on( 'change', function() {
			var visualTheme  = $( 'select[name="visualTheme"]' ).val();
			var dColorSet    = $( 'select[name="dColorSet"]' ).val();
			var iColorSet    = $( 'select[name="iColorSet"]' ).val();
			var oColorSet    = $( 'select[name="oColorSet"]' ).val();
			var i = 0;
			var array = availableOptions[visualTheme];
			var dColor = array.hasOwnProperty( dColorSet );
			var iColor = array.hasOwnProperty( iColorSet );
			var oColor = array.hasOwnProperty( oColorSet );

			$( 'select[name="dColorSet"] option, select[name="iColorSet"] option, select[name="oColorSet"] option' ).remove();

			$.each( availableOptions[visualTheme], function( index, value ) {
				if ( index === dColorSet || ( dColor == false && i == 0 ) ) {
					$( 'select[name="dColorSet"]' ).append( '<option value="' + index + '" selected>' + value + '</option>' );
				} else {
					$( 'select[name="dColorSet"]' ).append( '<option value="' + index + '">' + value + '</option>' );
				}

				if ( index === iColorSet || ( iColor == false && i == 0 ) ) {
					$( 'select[name="iColorSet"]' ).append( '<option value="' + index + '" selected>' + value + '</option>' );
				} else {
					$( 'select[name="iColorSet"]' ).append( '<option value="' + index + '">' + value + '</option>' );
				}

				if ( index === oColorSet || ( oColor == false && i == 0 ) ) {
					$( 'select[name="oColorSet"]' ).append( '<option value="' + index + '" selected>' + value + '</option>' );
				} else {
					$( 'select[name="oColorSet"]' ).append( '<option value="' + index + '">' + value + '</option>' );
				}

				++i;
			});
			// Declare a default lastClass based on the default HTML if we haven't declared one
			if('undefined' === typeof socialWarfarePlugin.lastClass){
				console.log('boom');
				socialWarfarePlugin.lastClass = 'swp_flatFresh swp_d_fullColor swp_i_fullColor swp_o_fullColor';
			}
			// Put together the new classes, remove the old ones, add the new ones, store the new ones for removal next time.
			var buttonsClass = 'swp_' + visualTheme + ' swp_d_' + dColorSet + ' swp_i_' + iColorSet + ' swp_o_' + oColorSet;
			$( '.nc_socialPanel' ).removeClass( socialWarfarePlugin.lastClass ).addClass( buttonsClass );
			socialWarfarePlugin.lastClass = buttonsClass;
		});
	}

	/*********************************************************
		A Function to update the button sizing options
	 *********************************************************/
	function updateScale() {
		$( 'select[name="buttonSize"],select[name="buttonFloat"]' ).on( 'change', function() {
			$( '.nc_socialPanel' ).css( { width: '100%' } );

			var width = $( '.nc_socialPanel' ).width();
			var scale = $( 'select[name="buttonSize"]' ).val();
			var align = $( 'select[name="buttonFloat"]' ).val();

			var newWidth;

			if ( ( align == 'fullWidth' && scale != 1 ) || scale >= 1 ) {
				newWidth = width / scale;

				$( '.nc_socialPanel' ).css( 'cssText', 'width:' + newWidth + 'px!important;' );

				$( '.nc_socialPanel' ).css({
					transform: 'scale(' + scale + ')',
					'transform-origin': 'left'
				});
			} else if ( align != 'fullWidth' && scale < 1 ) {
				newWidth = width / scale;

				$( '.nc_socialPanel' ).css({
					transform: 'scale(' + scale + ')',
					'transform-origin': align
				});
			}

			socialWarfarePlugin.activateHoverStates();
		});
	}

	/*********************************************************
		Update the Click To Tweet Demo
	 *********************************************************/
	function updateCttDemo() {
		var $cttOptions = $( 'select[name="cttTheme"]' );

		$cttOptions.on( 'change', function() {
			var newStyle = $( 'select[name="cttTheme"]' ).val();

			$( '.swp_CTT' ).attr( 'class', 'swp_CTT' ).addClass( newStyle );
		});

		$cttOptions.trigger( 'change' );
	}

	function getApiUrl( email, domain, regCode ) {
		email = encodeURIComponent( email );
		domain = encodeURIComponent( domain );
		regCode = encodeURIComponent( regCode );

		return 'https://warfareplugins.com/registration-api/?activity=register&emailAddress=' + email + '&domain=' + domain + '&registrationCode=' + regCode;
	}

	function toggleRegistration( status ) {
		clearLoadingScreen();
		$( '.registration-wrapper' ).attr( 'registration', status );
		$( '.sw-admin-wrapper' ).attr( 'sw-registered', status );
	}

	/*******************************************************
		Register the Plugin
	*******************************************************/
	function registerPlugin() {
		// Register the plugin
		$( '#register-plugin' ).on( 'click', function( event ) {
			// Block the default action
			event.preventDefault ? event.preventDefault() : ( event.returnValue = false );

			// The loading screen
			loadingScreen();

			// Fetch all the registration values
			var regCode = $( 'input[name="regCode"]' ).val();
			var email = $( 'input[name="emailAddress"]' ).val();
			var domain = $( 'input[name="domain"]' ).val();

			var apiURL = getApiUrl( email, domain, regCode );

			var ajaxData = {
				action: 'swp_ajax_passthrough',
				url: apiURL
			};

			// Ping the home server to create a registration log
			$.post( ajaxurl, ajaxData, function( data ) {
				// Parse the JSON response
				var object = $.parseJSON( data );

				// If the cURL request failed, let's attempt CORS
				if ( 0 === object || null === object ) {
					console.log( 'cURL request failed. Attempting CORS request.' );

					$.get( apiURL, function( data ) {
						var object = $.parseJSON( data );
						console.log( 'CORS request status: ' + object.status );

						$( 'input[name="premiumCode"]' ).val( object.premiumCode );

						// Send the response to admin-ajax.php
						$.post( ajaxurl, {
							action: 'swp_store_registration',
							premiumCode: object.premiumCode,
							email: email
						}, function() {
							toggleRegistration( '1' );
						});
					});

					return;
				}

				// If the response was a failure...
				if ( 'failure' === object.status ) {
					// Alert the failure status
					alert( 'Failure: ' + object.message );

					// Clear the loading screen
					clearLoadingScreen();

					return;
				}

				// If the response was a success
				$( 'input[name="premiumCode"]' ).val( object.premiumCode );

				// Send the response to admin-ajax.php
				$.post( ajaxurl, {
					action: 'swp_store_registration',
					premiumCode: object.premiumCode,
					email: email
				}, function() {
					toggleRegistration( '1' );
				});
			});
		});
	}

	/*******************************************************
		Unregister the Plugin
	*******************************************************/
	function unregisterPlugin() {
		$( '#unregister-plugin' ).on( 'click', function( event ) {
			// Block the default action
			event.preventDefault ? event.preventDefault() : ( event.returnValue = false );

			// The loading screen
			loadingScreen();

			// Fetch the registration values
			var regCode = $( 'input[name="regCode"]' ).val();
			var email = $( 'input[name="emailAddress"]' ).val();
			var domain = $( 'input[name="domain"]' ).val();

			// Create the ajax object
			var ajaxData = {
				action: 'swp_ajax_passthrough',
				url: getApiUrl( email, domain, regCode )
			};

			// Ping the home server for the registration log
			$.post( ajaxurl, ajaxData, function( data ) {
				// Clear out the premium code and the email address field
				$( 'input[name="premiumCode"]' ).val( '' );

				$( 'input[name="emailAddress"]' ).val( '' );

				// Prepare data
				data = {
					action: 'swp_delete_registration',
					premiumCode: '',
					emailAddress: ''
				};

				// Send the response to admin-ajax.php
				$.post( ajaxurl, data, function() {
					toggleRegistration( '0' );
				});
			});
		});
	}

	/*********************************************************
		Rearm the Registration if the domain has changed
	*********************************************************/
	function rearmRegistration() {
		$( 'input[name="premiumCode"]' ).attr( 'readonly', 'readonly' );

		$( 'input[name="regCode"]' ).parent( '.swp_field' ).hide();

		var premCode = $( 'input#domain' ).attr( 'data-premcode' );

		if ( '' === $( 'input[name="premiumCode"]' ).val() || premCode === $( 'input[name="premiumCode"]' ).val() ) {
			return;
		}

		// Fetch our variables
		var regCode = $( 'input[name="regCode"]' ).val();
		var email = $( 'input[name="emailAddress"]' ).val();
		var domain = $( 'input[name="domain"]' ).val();
		var apiUrl = getApiUrl( email, domain, regCode );

		// Pass the URL to the admin-ajax.php passthrough function
		$.post( ajaxurl, {
			action: 'swp_ajax_passthrough',
			url: apiUrl
		}, function( subdata ) {
			// Parse the response
			var info = $.parseJSON( subdata );

			// If the rearm was successful
			if ( 'success' === info.status ) {
				// Send the response to admin-ajax.php
				$.post( ajaxurl, {
					action: 'swp_store_registration',
					premiumCode: info.premiumCode
				}, function() {
					toggleRegistration( '1' );
				});

				$( 'input[name="premiumCode"]' ).val( info.premiumCode );
			} else {
				// Send the response to admin-ajax.php
				$.post( ajaxurl, {
					action: 'swp_delete_registration',
					premiumCode: '',
					emailAddress: ''
				}, function() {
					toggleRegistration( '0' );
				});
			}
		});
	}

	/*******************************************************
		Make the buttons sortable
	*******************************************************/
	function sortableInit() {
		$( '.sw-buttons-sort.sw-active' ).sortable({
			connectWith: '.sw-buttons-sort.sw-inactive',
			update: function() {
				saveColorToggle();
			}
		});

		$( '.sw-buttons-sort.sw-inactive' ).sortable({
			connectWith: '.sw-buttons-sort.sw-active',
			update: function() {
				saveColorToggle();
			}
		});
	}

	function getSystemStatus() {
		$( '.sw-system-status' ).on( 'click', function() {
			// Block the default action
			event.preventDefault ? event.preventDefault() : ( event.returnValue = false );

			$( '.system-status-wrapper' ).slideToggle();

			$( '.system-status-container' ).selectText();
		});
	}

	function blockPremiumFeatures() {
		$( '.sw-premium-blocker' ).tooltip({
			items: '.sw-premium-blocker',
			content: '<i></i>Unlock this feature by registering your license.',
			position: {
				my: 'center top',
				at: 'center top'
			},

			tooltipClass: 'sw-admin-hover-notice',

			open: function( event, ui ) {
				if ( typeof ( event.originalEvent ) === 'undefined' ) {
					return false;
				}

				var $id = $( ui.tooltip ).attr( 'id' );

				// close any lingering tooltips
				$( 'div.ui-tooltip' ).not( '#' + $id ).remove();

				// ajax function to pull in data and add it to the tooltip goes here
			},

			close: function( event, ui ) {
				ui.tooltip.hover(function() {
					$( this ).stop( true ).fadeTo( 400, 1 );
				},
				function() {
					$( this ).fadeOut( '400', function() {
						$( this ).remove();
					});
				});
			}
		});
	}

	/*********************************************************
		A Function for image upload buttons
	*********************************************************/
	function customUploaderInit() {
		var customUploader;

		$( '.swp_upload_image_button' ).click(function( e ) {
			e.preventDefault();

			var inputField = $( this ).attr( 'for' );

			// If the uploader object has already been created, reopen the dialog
			if ( customUploader ) {
				customUploader.open();

				return;
			}

			// Extend the wp.media object
			customUploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
				},
				multiple: false
			});

			// When a file is selected, grab the URL and set it as the text field's value
			customUploader.on( 'select', function() {
				var attachment = customUploader.state().get( 'selection' ).first().toJSON();

				$( 'input[name="' + inputField + '"' ).val( attachment.url );
			});

			// Open the uploader dialog
			customUploader.open();
		});
	}

	$( document ).ready(function() {
		handleSettingSave();
		populateOptions();
		headerMenuInit();
		tabNavInit();
		checkboxesInit();
		updateButtonPreviews();
		conditionalFields();
		updateCttDemo();
		updateScale();
		registerPlugin();
		unregisterPlugin();
		rearmRegistration();
		sortableInit();
		getSystemStatus();
		blockPremiumFeatures();
		customUploaderInit();
	});
})( this, jQuery );