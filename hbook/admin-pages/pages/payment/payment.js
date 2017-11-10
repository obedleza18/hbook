jQuery( document ).ready( function( $ ) {
    
    payment_choice_display();
    
    function payment_choice_display() {
        if ( $( 'input[name="hb_resa_payment_multiple_choice"]:checked' ).val() == 'yes' ) {
            $( '.hb-resa-payment-choice-multiple' ).hide();
            $( '.hb-resa-payment-choice-single' ).show();
        } else {
            $( '.hb-resa-payment-choice-multiple' ).show();
            $( '.hb-resa-payment-choice-single' ).hide();
        }
    }
    
    $( 'input[name="hb_resa_payment_multiple_choice"]' ).change( function() {
        payment_choice_display();
    });
    
	stripe_api_key();
	
	function stripe_api_key() {
		if ( $( 'input[name="hb_stripe_mode"]:checked' ).val() == 'live' ) {
            $( '.hb-stripe-mode-live' ).slideDown();
            $( '.hb-stripe-mode-test' ).slideUp();
        } else {
			$( '.hb-stripe-mode-live' ).slideUp();
            $( '.hb-stripe-mode-test' ).slideDown();
        }
	}
	
	$( 'input[name="hb_stripe_mode"]' ).change( function() {
        stripe_api_key();
    });
	
	paypal_api_key();
	
	function paypal_api_key() {
		if ( $( 'input[name="hb_paypal_mode"]:checked' ).val() == 'live' ) {
            $( '.hb-paypal-mode-live' ).slideDown();
            $( '.hb-paypal-mode-sandbox' ).slideUp();
        } else {
			$( '.hb-paypal-mode-live' ).slideUp();
            $( '.hb-paypal-mode-sandbox' ).slideDown();
        }
	}
	
	$( 'input[name="hb_paypal_mode"]' ).change( function() {
        paypal_api_key();
    });
	
	$( '.hb-payment-gateway-active input' ).change( function() {
		hide_show_payment_gateway_options();
	});
	
	hide_show_payment_gateway_options();
	
	function hide_show_payment_gateway_options() {
		for ( var i = 0; i < hb_payment_gateways.length; i++ ) {
			if ( $( 'input[name=hb_' + hb_payment_gateways[i] + '_active]:checked' ).val() == 'yes' ) {
				$( '.hb-payment-section-' + hb_payment_gateways[i] ).slideDown();
			} else {
				$( '.hb-payment-section-' + hb_payment_gateways[i] ).slideUp();
			}
		}
	}
	
});