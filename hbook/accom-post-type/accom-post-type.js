jQuery( document ).ready( function( $ ) {

    $( 'input[name="hb-accom-default-page"]' ).change( function() {
        if ( $( 'input[name="hb-accom-default-page"]:checked' ).val() == 'yes' ) {
            $( '.hb-accom-select-template' ).show();
            $( '.hb-accom-select-linked-page' ).hide();
        } else {
            $( '.hb-accom-select-template' ).hide();
            $( '.hb-accom-select-linked-page' ).show();
        }
    }).change();

	if ( $( '#hb-accom-num-name-wrapper' ).length ) {
		
	    var current_db_index = $( '#hb-accom-num-name-index' ).val();
	    	
		update_accom_num_name_inputs();
	    
	    $( '#hb-accom-quantity' ).keyup( function() {
			var accom_quantity = parseInt( $( '#hb-accom-quantity' ).val(), 10 ) || 1;
			if ( accom_quantity > 999 ) {
				accom_quantity = 999;
			}
			if ( $( '#hb-accom-quantity' ).val() != '' ) {
				$( '#hb-accom-quantity' ).val( accom_quantity );
			}
	        update_accom_num_name_inputs();
	    });
	        
	    function update_accom_num_name_inputs() {
	        var new_accom_num_name_markup = '',
	            accom_quantity = parseInt( $( '#hb-accom-quantity' ).val(), 10 ) || 0;
	           
	        $( '.hb-accom-num-name' ).hide();
	        
	        if ( $( '.hb-accom-num-name' ).length < accom_quantity ) {
	            var id = 0;
	            if ( $( '.hb-accom-num-name' ).length ) {
	                id = $( '.hb-accom-num-name' ).last().find( 'input' ).data( 'id' );
	                if ( id < current_db_index ) {
	                    id = current_db_index;
	                }
	            } else {
	                id = current_db_index;
	            }
	            for ( var i = 0; i < accom_quantity - $( '.hb-accom-num-name' ).length; i++ ) {
	                id++;
	                new_accom_num_name_markup += '<p class="hb-accom-num-name">';
	                new_accom_num_name_markup += '<input data-id="' + id + '" type="text" value="' + id + '" /> ';
	                new_accom_num_name_markup += '<a class="hb-accom-num-name-delete" href="#">' + hb_accom_post_text.delete_accom_num_name + '<a/>'
	                new_accom_num_name_markup += '</p>';
	            }
	            $( '#hb-accom-num-name-wrapper' ).append( new_accom_num_name_markup );
	            if ( id > current_db_index ) {
	                $( '#hb-accom-num-name-index' ).val( id );
	            }
	        }
	        
	        for ( var i = 0; i < accom_quantity; i++ ) {
	            $( '.hb-accom-num-name' ).eq( i ).show();
	        }
	        
	    }
	    
	    $( 'body' ).on( 'click', '.hb-accom-num-name-delete', function() {
	        $( this ).blur();
	        var accom_num_name = $( '#title' ).val() + ' (' + $( this ).parents( '.hb-accom-num-name' ).find( 'input' ).val() + ')',
	            confirm_text = hb_accom_post_text.delete_accom_num_name_text.split( '%s' ).join( accom_num_name ),
	            new_accom_num_json;
	        if ( confirm( confirm_text ) ) {
	            $( this ).parent().remove();
	            var accom_quantity = parseInt( $( '#hb-accom-quantity' ).val(), 10 );
	            accom_quantity--;
                $( '#hb-accom-quantity' ).val( accom_quantity );    
	        }
	        return false;
	    });
	    
	    $( '.hb-edit-accom-numbering' ).click( function() {
	        $( this ).blur();
	        $( '#hb-accom-num-name-wrapper' ).slideToggle();
	        return false;
	    });
	    
	    update_accom_num_name_json();
	    
	    $( '#post' ).submit( function() {
			if ( $( '#hb-accom-starting-price' ).val() && ! $.isNumeric( $( '#hb-accom-starting-price' ).val() ) ) {
				alert( hb_accom_post_text.starting_price_not_number );
				$( '#hb-accom-starting-price' ).focus();
				return false;
			}
			if ( $( '#hb-accom-quantity' ).length ) {
				var accom_quantity = parseInt( $( '#hb-accom-quantity' ).val(), 10 );
				if ( ! accom_quantity ) {
					alert( hb_accom_post_text.accom_number_zero );
					$( '#hb-accom-quantity' ).focus();
					return false;
				}
				$( '#hb-accom-quantity' ).val( accom_quantity );
			}
			update_accom_num_name_inputs();
	        update_accom_num_name_json();
	    });
	    
	    function update_accom_num_name_json() {
	        var accom_quantity = parseInt( $( '#hb-accom-quantity' ).val(), 10 ) || 0,
	            accom_num_name = {},
	            accom_num_name_json;
	        for ( var i = 0; i < accom_quantity; i++ ) {
	            var $input = $( '.hb-accom-num-name' ).eq( i ).find( 'input' );
	                id = $input.data( 'id' ),
	                num_name = $input.val();
	            accom_num_name[ id ] = num_name;
	        }
	        $( '#hb-accom-num-name-json' ).val( JSON.stringify( accom_num_name ) );
	    }
    
	}
    
    $( '#delete-action a' ).click( function() {
        return confirm( hb_accom_post_text.delete_accom_text );
    });
    
});