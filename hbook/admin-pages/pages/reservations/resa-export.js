jQuery( document ).ready( function( $ ) {
    
    $( '#hb-export-resa-cancel' ).click( function() {
        $( '#hb-export-resa' ).slideUp( function() {
            $( '#hb-export-resa-toggle .dashicons-arrow-down' ).css( 'display', 'inline-block' );
            $( '#hb-export-resa-toggle .dashicons-arrow-up' ).hide();
        });
        return false;
    });
    
    $( '#hb-export-resa-select-all' ).click( function() {
        $( this ).blur();
        $( '#hb-export-resa-form input[type="checkbox"]' ).prop( 'checked', true );
        return false;
    });
    
    $( '#hb-export-resa-unselect-all' ).click( function() {
        $( this ).blur();
        $( '#hb-export-resa-form input[type="checkbox"]' ).prop( 'checked', false );
        return false;
    });
    
    $( '#hb-export-resa-selection-received-date-from, #hb-export-resa-selection-received-date-to' ).focus( function() {
        $( '#hb-export-resa-selection-received-date' ).prop( 'checked', true );
    });
	
	$( '#hb-export-resa-selection-check-in-date-from, #hb-export-resa-selection-check-in-date-to' ).focus( function() {
        $( '#hb-export-resa-selection-check-in-date' ).prop( 'checked', true );
    });
    
    $( '#hb-export-resa-download' ).click( function() {
		$( this ).blur();
        if ( ! $( 'input[name="hb-resa-data-export[]"]:checked').length ) {
            alert( hb_text.no_export_data_selected );
            return false;
        }
		$( '#hb-export-resa-form' ).submit();
		return false;
	});
    
});