jQuery( document ).ready( function( $ ) {
	
	$( '.hb-remove-purchase-code' ).click( function() {
		if ( confirm( hb_text.remove_purchase_code ) ) {
			$( 'input[name="hb-purchase-code"]' ).val( '' );
			$( '#hb-verify-licence' ).submit();
		}
		return false;
	});
	
});