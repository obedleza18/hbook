jQuery( document ).ready( function( $ ) {
	
    var page_padding_top = hb_contact_form_data.page_padding_top;
    
	if ( $( '#wpadminbar' ).length ) {
		var adminbar_height = $( '#wpadminbar' ).height();
		page_padding_top = parseInt( page_padding_top ) + adminbar_height;
	}
    
	var langErrorDialogs = {
		badEmail: hb_contact_text.invalid_email,
		requiredFields: hb_contact_text.required_field,
		badInt: hb_contact_text.invalid_number
	};
	
	$.validate({
		form: '.hb-contact-form',
		validateOnBlur: false,
		language: langErrorDialogs,
		borderColorOnError: false,
		scrollToTopOnError: false,
		onError: function( $form ) {
			$form.find( '.hb-contact-submit input' ).blur();
			$( 'html, body' ).animate({	scrollTop: $( 'p.has-error' ).first().offset().top - page_padding_top }, 400 );
		},
		onSuccess: function( $form ) {
			submit_contact_form( $form );
			return false;
		}
	});
	
	function submit_contact_form( $form ) {
		$form.find( '.hb-contact-form-error, .hb-contact-msg-send' ).slideUp();
		$form.find( 'input[type="submit"]' ).blur();
		if ( $form.hasClass( 'already-sent' ) ) {
			$form.find( '.hb-contact-form-error' ).html( hb_contact_text.contact_already_sent ).slideDown();
			return false;
		}
		if ( ! $form.hasClass( 'submitted' ) ) {
			disable_form_submission( $form );
			$form.find( '.hb-processing-contact-form' ).fadeIn();
		} else {
			return false;
		}
		$.ajax({
			data: $form.serialize(),
			success: function( response ) {
				after_contact_form_submit( response, $form );
			},
			type: 'POST',
            timeout: hb_contact_form_data.ajax_timeout,
			url: hb_contact_form_data.ajax_url,
			error: function( jqXHR, textStatus, errorThrown ) {
                $form.find( '.hb-processing-contact-form' ).fadeOut();
				$form.find( '.hb-contact-form-error' ).html( hb_contact_text.connection_error ).slideDown();
				enable_form_submission( $form );
			}
		}); 
	}
	
	function after_contact_form_submit( response_text, $form ) {
		$form.find( '.hb-processing-contact-form' ).fadeOut();
		enable_form_submission( $form );
		try {
			var response = JSON.parse( response_text );
		} catch ( e ) {
			$form.find( '.hb-contact-form-error' ).html( response_text ).slideDown();
			return false;
		}
		if ( response['success'] ) {
			$form.find( '.hb-contact-msg-send' ).html( response['msg'] ).slideDown();
			$form.addClass( 'already-sent' );
		} else {
			$form.find( '.hb-contact-form-error' ).html( response['error_msg'] ).slideDown();
		}	
	}
	
	$( '.hb-contact-form input, .hb-contact-form textarea' ).change( function() {
		$( this ).parents( '.hb-contact-form' ).removeClass( 'already-sent' );
	});
		
	function disable_form_submission( $form ) {
		$form.addClass( 'submitted' );
		$form.find( 'input[type="submit"]' ).prop( 'disabled', true );
	}

	function enable_form_submission( $form ) {
		$form.removeClass( 'submitted' );
		$form.find( 'input[type="submit"]' ).prop( 'disabled', false );
	}
	
});