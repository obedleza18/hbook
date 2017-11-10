jQuery( document ).ready( function( $ ) {

	$( '.hb-sync-errors-msg .notice-dismiss' ).click( function() {
		if ( confirm( hb_text.confirm_delete_sync_errors ) ) {
			hb_resa_ajax({
				data: {
					'action': 'hb_delete_sync_errors', 
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
	});	
	
	$( '.hb-input-date' ).datepick( hb_datepicker_calendar_options );
    
    $( '.hb-input-date' ).datepick( 'option', {
        dateFormat: 'yyyy-mm-dd',
        onSelect: function() {
            $( this ).change();
        }
    });
	
    $( '#hb-check-in' ).change( function () {
		var check_in_date = $( this ).datepick( 'getDate' )[0],
			check_out_date = $( '#hb-check-out' ).datepick( 'getDate' )[0];
        if ( check_in_date && check_out_date && ( check_in_date.getTime() >= check_out_date.getTime() ) ) {
			$( '#hb-check-out' ).datepick( 'setDate', null );
		}
        if ( check_in_date ) {
            var min_check_out = new Date( check_in_date.getTime() );
            min_check_out.setDate( min_check_out.getDate() + 1 );
            $( '#hb-check-out' ).datepick( 'option', 'minDate', min_check_out );
        }
	});
	
	$( '#hb-block-accom-from-date' ).change( function () {
		var from_date = $( this ).datepick( 'getDate' )[0],
			to_date = $( '#hb-block-accom-to-date' ).datepick( 'getDate' )[0];
        if ( from_date && to_date && ( from_date.getTime() >= to_date.getTime() ) ) {
			$( '#hb-block-accom-to-date' ).datepick( 'setDate', null );
		}
        if ( from_date ) {
            var min_to_date = new Date( from_date.getTime() );
            min_to_date.setDate( min_to_date.getDate() + 1 );
            $( '#hb-block-accom-to-date' ).datepick( 'option', 'minDate', min_to_date );
        }
	});
	
	var customer_id_last_valid_selection = null;
	$( '#hb-customer-id-list' ).change( function() {
		if ( $( this ).val() && $( this ).val().length > 1 ) {
			$( this ).val( customer_id_last_valid_selection );
		} else {
			customer_id_last_valid_selection = $( this ).val();
		}
	});
   
	hb_section_toggle( 'block-accom' );
	hb_section_toggle( 'add-resa' );
	hb_section_toggle( 'export-resa' );
	
	$( '#hb-select-blocked-accom-type' ).change( function() {
		var blocked_accom_num_options = '';
		if ( accoms[ $( '#hb-select-blocked-accom-type' ).val() ] && accoms[ $( '#hb-select-blocked-accom-type' ).val() ].number > 1 ) {
			blocked_accom_num_options += '<option value="all">' + hb_text.all + '</option>';
			$( '#hb-select-blocked-accom-num' ).parent().show();
		} else {
			$( '#hb-select-blocked-accom-num' ).parent().hide();
		}
		if ( accoms[ $( '#hb-select-blocked-accom-type' ).val() ] ) {
			$.each( accoms[ $( '#hb-select-blocked-accom-type' ).val() ].num_name, function( accom_num_id, accom_num_name ) {
				blocked_accom_num_options += '<option value="' +  accom_num_id + '">' + accom_num_name + '</option>';
			});
		}
		$( '#hb-select-blocked-accom-num' ).html( blocked_accom_num_options );
	}).change();
	
	function hb_resa_ajax( ajax_param ) {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: ajax_param['data'],
			timeout: hb_ajax_settings.timeout,
			success: function( ajax_return ) {
				ajax_return = ajax_return.trim();
				ajax_param['success']( ajax_return );
			},
			error: ajax_param['error']
		});
	}
	
	$( '#hb-resa-check-price' ).submit( function() {
		$( this ).find( 'input[type="submit"]' ).blur();
		$( '#hb-resa-customer, #hb-resa-price-error' ).slideUp();
		$( '#hb-resa-check-submit-ajax .hb-ajaxing' ).css( 'display', 'inline' );
		hb_resa_ajax({
			data: {
				'action': 'hb_resa_check_price', 
				'check_in': $( '#hb-check-in' ).val(),
				'check_out': $( '#hb-check-out' ).val(),
				'adults': $( '#hb-adults' ).val(),
				'children': $( '#hb-children' ).val(),
				'accom_id': $( '#hb-accom' ).val(),
				'nonce': $( '#hb_nonce_update_db' ).val()
			},
			success: function( response_text ) {
				$( '#hb-resa-check-submit-ajax .hb-ajaxing' ).css( 'display', 'none' );
				try {
					var response = JSON.parse( response_text );
				} catch ( e ) {
					$( '#hb-resa-price-error' ).html( response_text ).slideDown();
					return false;
				}
				if ( response['success'] ) {
					var selected_accom_name = $( '#hb-accom option:selected' ).html();
					$( '#hb-resa-accom-type span' ).html( selected_accom_name );
					$( '#hb-resa-price span' ).html( response['price'] );
					$( '#hb-resa-accom-price' ).val( response['price'] );
					$( '#hb-resa-price-breakdown' ).html( response['price_breakdown'] );
					$( '#hb-resa-options' ).html( response['options_form'] );
					$( '#hb-resa-options .hb-option' ).hide();
                    if ( $( '#hb-resa-options .hb-option-accom-' + $( '#hb-accom' ).val() ).length ) {
                        $( '#hb-resa-options' ).show();
                        $( '#hb-resa-options .hb-option-accom-' + $( '#hb-accom' ).val() ).show();
                    } else {
                        $( '#hb-resa-options' ).hide();
                    }
                    $( '#hb-resa-fees' ).html( response['fees'] );
                    calculate_options_price();
                    calculate_total_price();
					if ( response['accom_num'].length == 0 ) {
						$( '#hb-resa-price-other-wrapper, #hb-resa-customer-details-wrap' ).hide();
						$( '#hb-resa-accom-num' ).html( '<label>' + hb_text.select_accom_none + '</label>' );
					} else {
						$( '#hb-resa-price-other-wrapper, #hb-resa-customer-details-wrap' ).show();
						var accom_num_radios = '';
						for ( var i = 0; i < response['accom_num'].length; i++ ) {
							var val = response['accom_num'][i];
							var id = 'hb-accom-num-' + val;
							accom_num_radios += '<input type="radio" id="' + id + '" name="hb-accom-num" value="' + val + '" ';
							if ( i == 0 ) {
								accom_num_radios += 'checked ';
							}
							accom_num_radios += '/>';
							accom_num_radios += '<label for="' + id + '">' + selected_accom_name;
							if ( accoms[ $( '#hb-accom' ).val() ].num_name[ val ] ) {
								 accom_num_radios += ' (' + accoms[ $( '#hb-accom' ).val() ].num_name[ val ] + ')';
							}
							accom_num_radios += '</label><br/>';
						}
						$( '#hb-resa-accom-num' ).html( '<label>' + hb_text.select_accom_num + '</label><br/>' + accom_num_radios );
					}
					$( '#hb-resa-customer' ).slideDown();
				} else {
					$( '#hb-resa-price-error' ).html( response['error'] ).slideDown();
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				$( '#hb-resa-check-submit-ajax .hb-ajaxing' ).css( 'display', 'none' );
				alert( textStatus + ' (' + errorThrown + ')' );
			}
		});	
		return false;
	});
	

    function calculate_options_price() {

        var accom_id = $( '#hb-accom' ).val(),
			accom_price = $( '#hb-resa-accom-price' ).val(),
			options_price = 0;

		$( '.hb-option' ).each( function() {
			if ( $( this ).hasClass( 'hb-option-accom-' + accom_id ) ) {
				if ( $( this ).hasClass( 'hb-quantity-option' ) ) {
					options_price += parseFloat( $( this ).find( 'input' ).data( 'price' ) * $( this ).find( 'input' ).val() );
				} else if ( $( this ).hasClass( 'hb-multiple-option' ) ) {
					options_price += parseFloat( $( this ).find( 'input:checked' ).data( 'price' ) );
				} else if ( $( this ).hasClass( 'hb-single-option' ) && $( this ).find( 'input' ).is(':checked' ) ) {
					options_price += parseFloat( $( this ).find( 'input' ).data( 'price' ) );
				}
			}
		});

		$( '.hb-options-price-raw' ).val( options_price );
		
		var total_price = options_price + parseFloat( accom_price );
		$( '#hb-resa-total-price' ).val( total_price );
		total_price = format_price( total_price );
		options_price = format_price( options_price );
		$( '.hb-options-total-price span' ).html( options_price );
		$( '#hb-resa-price span' ).html( total_price );   

    }


    function calculate_total_price() {

        var accom_id = $( '#hb-accom' ).val(),
			accom_price = $( '#hb-resa-accom-price' ).val(),
			options_price = $( '.hb-options-price-raw' ).val(),
            before_fees_price = parseFloat( accom_price ) + parseFloat( options_price ),
            fees_price = 0,
            fee_price = 0;

		$( '#hb-resa-fees .hb-fee' ).each( function() {
            if ( $( this ).hasClass( 'hb-fee-percentage' ) ) {
                fee_price = before_fees_price  * $( this ).data( 'price' ) / 100;
            } else {
                fee_price = $( this ).data( 'price' );
            }
            fees_price += parseFloat( fee_price );
            fee_price = format_price( fee_price );
            $( this ).find( 'span' ).html( fee_price );
        });
		
        var total_price = parseFloat( accom_price ) + parseFloat( options_price ) + parseFloat( fees_price );
        total_price = format_price( total_price );
        $( '#hb-resa-total-price' ).val( total_price );
		$( '#hb-resa-price span' ).html( total_price );

    }
    
    $( '#hb-resa-options' ).on( 'click', '.hb-option', function() {
		calculate_options_price(); 
		calculate_total_price();
	});

	$( '#hb-resa-options' ).on( 'keyup', '.hb-option input', function() {
		calculate_options_price(); 
		calculate_total_price();
	});
    
    function format_price( price ) {
    	if ( hb_price_precision == 'no_decimals' ) {
			var formatted_price = Math.round( price );
		} else {
			var formatted_price = parseFloat( price ).toFixed( 2 );
		}
		return formatted_price;
    }
    
	$( 'input[name="hb-customer-type"]' ).change( function() {
		$( '#hb-resa-customer-submit' ).slideDown();
		if ( $( 'input[name="hb-customer-type"]:checked' ).val() == 'id' ) {
			$( '#hb-resa-customer-details' ).slideUp();
			$( '#hb-resa-customer-id' ).slideDown();
		} else {
			$( '#hb-resa-customer-details' ).slideDown();
			$( '#hb-resa-customer-id' ).slideUp();		
		}
	});
	
	$( '.wrap' ).on( 'click', '.hb-resa-more-info-toggle', function() {
		$( this ).parent().find( '.hb-resa-more-info-content' ).slideToggle( 100 );
		$( this ).toggleClass( 'hb-less-info-toggle-link' );
		return false;
	});
	
	$( '.wrap' ).on( 'click', 'a', function( e ) {
		$( this ).blur();
		e.preventDefault();
	});
	
    var displayed_accoms = accoms;
    
    $( '#hb-resa-cal-accommodation' ).change( function() {
		var accom_selected = $( this ).val();
		if ( accom_selected == 'all' ) {
			displayed_accoms = accoms;
		} else {
            displayed_accoms = {};
            displayed_accoms[ accom_selected ] = accoms[ accom_selected ];
		}
		hb_create_resa_cal_tables( $( '#hb-resa-cal-table' ).data( 'first-day'), displayed_accoms );
        resaViewModel.redraw_calendar();
	});
    
    $( '#hb-resa-cal-wrapper' ).on( 'click', '.hb-go-to-previous-two-weeks, .hb-go-to-next-two-weeks', function() {
        hb_create_resa_cal_tables( $( this ).data( 'day' ), displayed_accoms );
        resaViewModel.redraw_calendar();
        return false;
    });
    
    $( 'body' ).on( 'click', '.hb-month.button', function() {
		$( this ).parents( '.hb-month-picker').slideUp();
		hb_create_resa_cal_tables( $( this ).data( 'day' ), displayed_accoms );
		resaViewModel.redraw_calendar();
		return false;
	});
    
    function Resa( id, status, price, paid, old_currency, check_in, check_out, adults, children, accom_id, accom_num, non_editable_info, admin_comment, customer_id, received_on, origin, additional_info, view_model ) {
        this.id = id;
        this.price = ko.observable( price );
        this.price_tmp = ko.observable();
        this.paid = ko.observable( paid );
        this.paid_tmp = ko.observable();
		this.charge_amount = ko.observable( format_price( price - paid ) );
        this.old_currency = old_currency;
        this.check_in = ko.observable( check_in );
        this.check_out = ko.observable( check_out );
		this.check_in_tmp = ko.observable();
        this.check_out_tmp = ko.observable();
        this.adults = ko.observable( adults );
        this.children = ko.observable( children );
		this.adults_tmp = ko.observable();
		this.children_tmp = ko.observable();
		this.additional_info = ko.observable( additional_info );
		this.origin = origin;
        this.accom_id = ko.observable( accom_id );
        this.accom_num = ko.observable( accom_num );
        this.avai_accom_same_dates = ko.observableArray();
        this.customer_id = ko.observable( customer_id );
        this.received_on = received_on;
        this.non_editable_info = non_editable_info;
        this.admin_comment = ko.observable( admin_comment );
        this.admin_comment_tmp = ko.observable( '' );
        this.status = ko.observable( status );
        this.updating = ko.observable( false );
        this.deleting = ko.observable( false );
        this.deleting_anim = ko.observable( false );
		this.editing_resa_info = ko.observable( false );
        this.editing_comment = ko.observable( false );
        this.editing_accom = ko.observable( false );
        this.fetching_accom = ko.observable( false );
		this.creating_customer = ko.observable( false );
        this.editing_customer = ko.observable( false );
        this.saving_accom = ko.observable( false );
        this.saving_resa_info = ko.observable( false );
        this.saving_comment = ko.observable( false );
        this.saving_customer = ko.observable( false );
        this.editing_paid = ko.observable( false );
        this.saving_paid = ko.observable( false );
        this.marking_paid = ko.observable( false );
		this.editing_charge = ko.observable( false );
        this.charging = ko.observable( false );
		this.editing_dates = ko.observable( false );
		this.saving_dates = ko.observable( false );
        this.anim_class = ko.observable( '' );
        
        var self = this;
        
        this.action_processing = ko.computed( function() {
            if ( self.deleting() || self.updating() ) {
                return true;
            } else {
                return false;
            }
        });
        
        this.status_markup = ko.computed( function() {
            return '<div class="hb-resa-status hb-resa-' + self.status() + '" title="' + hb_text[ self.status() ] + '">' + hb_text[ self.status() ] + '</div>';
        });
        
        this.price_markup = ko.computed( function() {
            var price = '';
            if ( self.old_currency ) {
                price = format_price( self.price() ) + ' ' + old_currency;
            } else {
                price = hb_format_price( self.price() );
            }
            return price;
        });
                                        
        this.price_status = ko.computed( function() {
            var price_status = '';
            if ( self.paid() != -1 ) {
                if ( self.paid() == self.price() ) {
                    price_status = '<div class="hb-payment-status hb-resa-paid" title="' + hb_text['paid'] + '">' + hb_text['paid'] + '</div>';
                } else if ( self.paid() == 0 ) {
                    price_status = '<div class="hb-payment-status hb-resa-unpaid" title="' + hb_text['unpaid'] + '">' + hb_text['unpaid'] + '</div>';
                } else {
                    price_status = '<div class="hb-payment-status hb-resa-not-fully-paid" title="' + hb_text['not_fully_paid'] + '">' + hb_text['not_fully_paid'] + '</div>';    
                }
            }
            return price_status;
        });
        
        this.price_details = ko.computed( function() {
            var price = '';
            price += hb_text['paid_details'] + ' ' + hb_format_price( self.paid() );
            price += '<br/>' + hb_text['to_be_paid_details'] + ' ' + hb_format_price( self.price() - self.paid() )
            return price;
        })
        
		this.charge_action_visible = ko.computed( function() {
			if ( hb_stripe_active != 'yes' ) {
				return false;
			}
			if ( ! self.customer_id() || self.customer_id() == '0' ) {
				return false;
			}
			customer = ko.utils.arrayFirst( view_model.customers_list(), function( customer ) {
			    return self.customer_id() == customer.id;
			});
			if ( ! customer || ! customer.payment_id ) {
				return false;
			}
			if ( self.editing_charge() ) {
				return false;
			}
			return self.price() != self.paid();
		});
		
        this.nb_nights = ko.computed( function() {
            return hb_nb_days( self.check_in(), self.check_out() );
        });
		
		this.nb_nights_tmp = ko.computed( function() {
			if ( ! self.editing_dates() || 
				 ! self.check_in_tmp() || 
				 ! self.check_out_tmp() || 
				 ! hb_valid_date( self.check_in_tmp() ) || 
				 ! hb_valid_date( self.check_out_tmp() ) 
			 ) {
				return '';
			} else {
            	return hb_nb_days( self.check_in_tmp(), self.check_out_tmp() );
			}
        });
        
        this.accom = ko.computed( function() {
            if ( self.accom_id() in accoms ) {
                var accom_txt = accoms[ self.accom_id() ].name;
                if ( self.status() == 'pending' ) {
                    accom_txt += ' <small>' + hb_text['not_allocated'] + '</small>';
                } else if ( accoms[ self.accom_id() ].num_name[ self.accom_num() ] ) {
                    accom_txt += ' <small>(' + accoms[ self.accom_id() ].num_name[ self.accom_num() ] + ')</small>';
                }
                return accom_txt;
            } else {
                return '';
            }
        });
        
        this.accom_editor = ko.computed( function() {
            if ( ! self.editing_accom() ) {
                return '';
            }
            var accom_editor_html = '',
                avai_accom = self.avai_accom_same_dates();
            for ( var i = 0; i < avai_accom.length; i++ ) {
                for ( var j = 0; j < avai_accom[i].accom_num.length; j++ ) {
                    var accom_id = avai_accom[i].accom_id,
                        accom_num = avai_accom[i].accom_num[j],
                        input_id = 'hb-accom-change-' + accom_id + '-' + accom_num;
                    accom_editor_html += '<div class="hb-accom-editor-radio">';
                    accom_editor_html += '<input type="radio" id="' + input_id + '" name="hb-accom-change"';
                    accom_editor_html += ' data-accom-id="' + accom_id + '" data-accom-num="' + accom_num + '" />';
                    accom_editor_html += '<label for="' + input_id + '">';
                    accom_editor_html += accoms[ accom_id ].name + ' (' + accoms[ accom_id ].num_name[ accom_num ] + ')';
                    accom_editor_html += '</label>';
                    accom_editor_html += '</div>';
                }
            }
            return accom_editor_html;
        });
        
		this.resa_info_html = ko.computed( function() {
			var markup = '',
				additional_info = '';
			
			markup += '<b>' + hb_text.info_adults + '</b> ' + self.adults() + '<br/>';
			if ( self.children() ) {
				markup += '<b>' + hb_text.info_children + '</b> ' + self.children() + '<br/>';
			}
			
			try {
				additional_info_data = JSON.parse( self.additional_info() );
			} catch ( e ) {
				additional_info_data = {};
			}
			
			$.each( additional_info_data, function( info_id, info_value ) {
				if ( info_value != '' ) {
					if ( hb_additional_info_fields[ info_id ] ) {
						additional_info += '<b>' + hb_additional_info_fields[ info_id ]['name'] + ':</b> ';
						if ( hb_additional_info_fields[ info_id ]['type'] == 'textarea' ) {
							additional_info += '<br/>';
						}
					}
					additional_info += info_value.replace( /(?:\r\n|\r|\n)/g, '<br/>' ) + '<br/>';
				}
			});
			
			if ( additional_info || self.non_editable_info ) {
				markup += '<a href="#" class="hb-resa-more-info-toggle">';
				markup += '<span class="hb-more-info-link">' + hb_text.more_info + '</span>';
				markup += '<span class="hb-less-info-link">' + hb_text.less_info + '</span>';
				markup += '</a>';
				markup += '<div class="hb-resa-more-info-content">' + additional_info + self.non_editable_info + '</div>';
			}
			return markup;
		});

		this.additional_info_editing_markup = ko.computed( function() {
			var additional_info_edit_markup = '';
			
			try {
				additional_info_data = JSON.parse( self.additional_info() );
			} catch ( e ) {
				additional_info_data = [];
			}
			
			if ( ! additional_info_data ) {
				additional_info_data = [];
			}
			
			$.each( hb_additional_info_fields, function( field_id, field_info ) {
				additional_info_edit_markup += field_info['name'] + '<br/>';
				if ( field_info['type'] == 'textarea' ) {
					additional_info_edit_markup += '<textarea ';
					additional_info_edit_markup += 'rows="2" ';
					additional_info_edit_markup += 'class="hb-textarea-edit-resa hb-input-additional-info-resa-' + self.id + '" ';
					additional_info_edit_markup += 'data-id="' + field_id + '" ';
					additional_info_edit_markup += '>';
					if ( additional_info_data[ field_id ] ) {
						additional_info_edit_markup += additional_info_data[ field_id ];
					}
					additional_info_edit_markup += '</textarea>';
				} else {
					additional_info_edit_markup += '<input ';
					additional_info_edit_markup += 'class="hb-input-edit-resa hb-input-additional-info-resa-' + self.id + '" ';
					additional_info_edit_markup += 'type="text" ';
					if ( additional_info_data[ field_id ] ) {
						additional_info_edit_markup += 'value="' +  additional_info_data[ field_id ] + '" ';
					}
					additional_info_edit_markup += 'data-id="' + field_id + '" ';
					additional_info_edit_markup += '/>';
				}
			});
			
			return additional_info_edit_markup;
		});
				
		this.admin_comment_html = ko.computed( function() {
			return self.admin_comment().replace( /(?:\r\n|\r|\n)/g, '<br/>' );
		});
		
        this.customer_info_markup = ko.computed( function() {
			var customer_info_markup = '',
				customer_more_info_markup = '',
				customer,
				customer_data_json,
				customer_data,
				nb_data = 0;
			
			if ( ! self.customer_id() || self.customer_id() == '0' ) {
				return '';
			}
			
			customer = ko.utils.arrayFirst( view_model.customers_list(), function( customer ) {
			    return self.customer_id() == customer.id;
			});
			
			if ( ! customer ) {
				return '';
			}
			
			customer_data = customer.customer_data();
			if ( ! customer_data ) {
				return customer.info();
			}

			$.each( customer_data, function( info_id, info_value ) {
				if ( info_value != '' ) {
					nb_data++;
					var info_markup = '';
					if ( hb_customer_fields[ info_id ] ) {
						info_markup = '<b>' + hb_customer_fields[ info_id ]['name'] + ':</b> ';
						if ( hb_customer_fields[ info_id ]['type'] == 'textarea' ) {
							info_markup += '<br/>';
						}
					}
					info_markup += info_value.replace( /(?:\r\n|\r|\n)/g, '<br/>' ) + '<br/>';
					if ( nb_data <= 2 ) {
						customer_info_markup += info_markup;
					} else {
						customer_more_info_markup += info_markup;
					}
				}
			});
			
            customer_info_markup = '<b>' + hb_text.customer_id + '</b> ' + self.customer_id() + '<br/>' + customer_info_markup;
            if ( customer_more_info_markup != '' ) {
                customer_info_markup += '<a href="#" class="hb-resa-more-info-toggle">';
                customer_info_markup += '<span class="hb-more-info-link">' + hb_text.more_info + '</span>';
                customer_info_markup += '<span class="hb-less-info-link">' + hb_text.less_info + '</span>';
                customer_info_markup += '</a>';
                customer_info_markup += '<div class="hb-resa-more-info-content">' + customer_more_info_markup + '</div>';
            }
            return customer_info_markup;
        });
        
		this.customer_info_editing_markup = ko.computed( function() {
			var customer_edit_markup = '',
				customer_data = [];
			
			if ( self.customer_id() != 0 ) {
				customer = ko.utils.arrayFirst( view_model.customers_list(), function( customer ) {
				    return self.customer_id() == customer.id;
				});
				if ( customer ) {
					customer_data = customer.customer_data();
				}
			}
			
			$.each( hb_customer_fields, function( field_id, field_info ) {
				customer_edit_markup += field_info['name'] + '<br/>';
				if ( field_info['type'] == 'textarea' ) {
					customer_edit_markup += '<textarea ';
					customer_edit_markup += 'rows="2" ';
					customer_edit_markup += 'class="hb-textarea-edit-resa hb-input-customer-resa-' + self.id + '" ';
					customer_edit_markup += 'data-id="' + field_id + '" ';
					customer_edit_markup += '>';
					if ( customer_data[ field_id ] ) {
						customer_edit_markup += customer_data[ field_id ];
					}
					customer_edit_markup += '</textarea>';
				} else {
					customer_edit_markup += '<input ';
					customer_edit_markup += 'class="hb-input-edit-resa hb-input-customer-resa-' + self.id + '" ';
					customer_edit_markup += 'type="text" ';
					if ( customer_data[ field_id ] ) {
						customer_edit_markup += 'value="' + customer_data[ field_id ] + '" ';
					}
					customer_edit_markup += 'data-id="' + field_id + '" ';
					customer_edit_markup += '/>';
				}
			});
			
			return customer_edit_markup;
		});
		
        this.status.subscribe( function() {
            resaViewModel.redraw_calendar();
        });
        
        this.accom_num.subscribe( function() {
            resaViewModel.redraw_calendar();
        });
		
		this.check_in.subscribe( function() {
            resaViewModel.redraw_calendar();
        });
		
		this.check_out.subscribe( function() {
            resaViewModel.redraw_calendar();
        });
		
		
    }
    
	function BlockedAccom( from_date, to_date, accom_id, accom_num, accom_all_num, accom_all_ids, comment, linked_resa_id ) {
		this.from_date = ko.observable( from_date );
		this.to_date = ko.observable( to_date );
		this.accom_id = accom_id;
		this.accom_num = accom_num;
		this.accom_all_num = parseInt( accom_all_num );
		this.accom_all_ids = parseInt( accom_all_ids );
		this.comment = comment;
		this.linked_resa_id = linked_resa_id;
		this.deleting = ko.observable( false );
		this.anim_class = ko.observable( '' );
		
		if ( this.accom_all_ids ) {
			this.accom_name_num = hb_text.all;
		} else if ( this.accom_id in accoms ) {
			this.accom_name_num = accoms[ this.accom_id ].name;
			if ( ! this.accom_all_num && accoms[ this.accom_id ].num_name[ this.accom_num ] ) {
				this.accom_name_num += ' (' + accoms[ this.accom_id ].num_name[ this.accom_num ] + ')';
			}
		} else {
			this.accom_name_num = '';
		}
		
		this.from_date_display = from_date;
		this.to_date_display = to_date;
		if ( from_date == '2016-01-01' || from_date == '0000-00-00' ) {
			this.from_date_display = '';
		}
		if ( to_date == '2029-12-31' || to_date == '0000-00-00' ) {
			this.to_date_display = '';
		}
		
		this.from_date.subscribe( function() {
            resaViewModel.redraw_calendar();
        });
		
		this.to_date.subscribe( function() {
            resaViewModel.redraw_calendar();
        });
	}
	
	function Customer( id, info, payment_id ) {
		this.id = id;
		this.info = ko.observable( info );
		this.payment_id = payment_id;

		var self = this;
		
		this.customer_data = ko.computed( function() {
			try {
				customer_data = JSON.parse( self.info() );
			} catch ( e ) {
				return [];
			}
			return customer_data;
		});
		
		this.first_name = ko.computed( function() {
			if ( self.customer_data()['first_name'] ) {
				return self.customer_data()['first_name'];
			} else {
				return '';
			}
		});
		
		this.last_name = ko.computed( function() {
			if ( self.customer_data()['last_name'] ) {
				return self.customer_data()['last_name'];
			} else {
				return '';
			}
		});
		
		this.email = ko.computed( function() {
			if ( self.customer_data()['email'] ) {
				return self.customer_data()['email'];
			} else {
				return '';
			}
		});
		
		this.name_email = ko.computed( function() {
			var name_email_raw = self.first_name() + self.last_name() + self.email(); 
			return name_email_raw.toLowerCase();
		});
	}
	
	function ResaViewModel() {
		
		var self = this;
		
        this.resa = ko.observableArray();
        this.blocked_accom = ko.observableArray();
        this.customers_list = ko.observableArray();
		
        this.redraw_calendar = function() {
			hb_set_resa_cal( self.resa(), self.blocked_accom(), self.customers_list(), $( '#hb-resa-cal-table' ).data( 'first-day'), displayed_accoms );
        }
		
        this.resa.subscribe( function() {
			self.redraw_calendar();
        });
		
		this.blocked_accom.subscribe( function() {
            self.redraw_calendar();
        });
		
		this.customers_list.subscribe( function() {
            self.redraw_calendar();
        });
        
		function change_resa_status( new_status, resa ) {
			hb_resa_ajax({
				data: {
					'action': 'hb_change_resa_status', 
					'resa_status': new_status,
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.updating( false );
					if ( ajax_return == 'resa updated' ) {
						if ( new_status == 'cancelled' ) {
							self.blocked_accom.remove( function( blocked_accom ) { 
								return blocked_accom.linked_resa_id == resa.id;
							});
						}
						resa.status( new_status );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
                    resa.updating( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
		
		this.mark_read_resa = function( resa ) {
			resa.updating( true );
            change_resa_status( 'confirmed', resa );
		}
		
        this.confirm_resa = function( resa ) {
            resa.updating( true );
            hb_resa_ajax({
				data: {
					'action': 'hb_confirm_resa', 
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.updating( false );
                    try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( hb_text['error'] + ' ' + ajax_return );
						return false;
					}
					if ( response['status'] == 'confirmed' ) {
                        resa.status( 'confirmed' );
                        resa.accom_num( response['accom_num'] );
						for ( var i = 0; i < response['blocked_linked_accom'].length; i++ ) {
							var new_blocked_accom = new BlockedAccom( 
								response['blocked_linked_accom'][i]['from_date'],
								response['blocked_linked_accom'][i]['to_date'],
								response['blocked_linked_accom'][i]['accom_id'],
								response['blocked_linked_accom'][i]['accom_num'],
								0,
								0,
								response['blocked_linked_accom'][i]['comment'],
								response['blocked_linked_accom'][i]['linked_resa_id']
							);
							self.blocked_accom.unshift( new_blocked_accom );
						}
					} else {
						alert( hb_text['no_accom_available_on_confirmed'] );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
                    resa.updating( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
        }
        
		this.cancel_resa = function( resa ) {
			resa.updating( true );
			change_resa_status( 'cancelled', resa );
		}

		this.edit_resa_info = function( resa ) {
			resa.editing_resa_info( true );
			resa.adults_tmp( resa.adults() );
			resa.children_tmp( resa.children() );
		}
		
		this.cancel_edit_resa_info = function( resa ) {
            resa.editing_resa_info( false );
        }
		
		this.save_resa_info = function( resa ) {
			var additional_info = {};
			resa.saving_resa_info( true );
			$( '.hb-input-additional-info-resa-' + resa.id ).each( function() {
				if ( $( this ).val() != '' ) {
					additional_info[ $( this ).data( 'id' ) ] = $( this ).val();
				}
			});
			additional_info = JSON.stringify( additional_info );			
			hb_resa_ajax({
				data: {
					action: 'hb_update_resa_info',
					resa_id: resa.id,
					adults: resa.adults_tmp(),
					children: resa.children_tmp(),
					additional_info: additional_info,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_resa_info( false );
					resa.editing_resa_info( false );
					if ( ajax_return == 'resa info updated' ) {
						resa.adults( resa.adults_tmp() );
						resa.children( resa.children_tmp() );
						resa.additional_info( additional_info );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
                    resa.saving_resa_info( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
		
        this.edit_customer = function( resa ) {
            resa.editing_customer( true );
        }
        
        this.cancel_edit_customer = function( resa ) {
            resa.editing_customer( false );
        }
        
		this.save_customer = function( resa ) {
			resa.saving_customer( true );
			var customer_details = {},
				customer_email = '';
			$( '.hb-input-customer-resa-' + resa.id ).each( function() {
				if ( $( this ).val() != '' ) {
					customer_details[ $( this ).data( 'id' ) ] = $( this ).val();
				}
				if ( $( this ).data( 'id' ) == 'email' ) {
					customer_email = $( this ).val();
				}
			});
			customer_details = JSON.stringify( customer_details );
			hb_resa_ajax({
				data: {
					action: 'hb_update_customer',
					customer_id: resa.customer_id(),
					email: customer_email,
					info: customer_details,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					resa.saving_customer( false );
					resa.editing_customer( false );
					if ( ajax_return == 'customer updated' ) {
						customer = ko.utils.arrayFirst( self.customers_list(), function( customer ) {
						    return resa.customer_id() == customer.id;
						});
						customer.info( customer_details );
						self.redraw_calendar();
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
                    resa.saving_customer( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
		
		this.create_customer = function( resa ) {
			resa.creating_customer( true );
			hb_resa_ajax({
				data: {
					action: 'hb_create_resa_new_customer',
					resa_id: resa.id,
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
                    resa.creating_customer( false );
                    try {
						var response = JSON.parse( ajax_return );
					} catch ( e ) {
						alert( ajax_return );
                        return;
					}
					if ( response['customer_id'] ) {
						self.customers_list.push( new Customer( response['customer_id'], '', '' ) );
	                    resa.customer_id( response['customer_id'] );
						resa.editing_customer( true );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
                    resa.creating_customer( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
		
        this.edit_accom = function( resa ) {
            resa.fetching_accom( true );
            hb_resa_ajax({
				data: {
					action: 'hb_edit_accom_get_avai',
                    check_in: resa.check_in(),
                    check_out: resa.check_out(),
					nonce: $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
                    resa.fetching_accom( false );
                    try {
						var avai_accom = JSON.parse( ajax_return );
					} catch ( e ) {
                        resa.editing_accom( false );
						alert( ajax_return );
                        return;
					}
                    resa.avai_accom_same_dates( avai_accom );
                    resa.editing_accom( true );
				},
				error: function( jqXHR, textStatus, errorThrown ) {
                    resa.fetching_accom( false );
                    resa.editing_accom( false );
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
        }
        
        this.save_accom = function( resa ) {
            var $selected_input = $( 'input[name="hb-accom-change"]:checked' );
            if ( ! $selected_input.length ) {
                alert( hb_text.accom_not_selected );
            } else {
                var accom_id = $selected_input.data( 'accom-id' ),
                    accom_num = $selected_input.data( 'accom-num' );
                resa.editing_accom( false );
                resa.saving_accom( true );
                hb_resa_ajax({
                    data: {
                        action: 'hb_update_resa_accom',
                        check_in: resa.check_in(),
                        check_out: resa.check_out(),
                        resa_id: resa.id,
                        accom_id: accom_id,
                        accom_num: accom_num,
                        nonce: $( '#hb_nonce_update_db' ).val()
                    },
                    success: function( ajax_return ) {
                        resa.saving_accom( false );
						try {
							var response = JSON.parse( ajax_return );
						} catch ( e ) {
							alert( hb_text['error'] + ' ' + ajax_return );
							return false;
						}
                        if ( response['success'] ) {
                            resa.accom_id( accom_id );
                            resa.accom_num( accom_num );
							self.blocked_accom.remove( function( blocked_accom ) { 
								return blocked_accom.linked_resa_id == resa.id;
							});
							for ( var i = 0; i < response['blocked_linked_accom'].length; i++ ) {
								var new_blocked_accom = new BlockedAccom( 
									response['blocked_linked_accom'][i]['from_date'],
									response['blocked_linked_accom'][i]['to_date'],
									response['blocked_linked_accom'][i]['accom_id'],
									response['blocked_linked_accom'][i]['accom_num'],
									0,
									0,
									response['blocked_linked_accom'][i]['comment'],
									response['blocked_linked_accom'][i]['linked_resa_id']
								);
								self.blocked_accom.unshift( new_blocked_accom );
							}
                        } else {
                            alert( response['error'] );
                        }
                    },
                    error: function( jqXHR, textStatus, errorThrown ) {
                        resa.saving_accom( false );
                        alert( textStatus + ' (' + errorThrown + ')' );
                    }
                });
            }
        }
        
        this.cancel_edit_accom = function( resa ) {
            resa.editing_accom( false );
        }
        
		this.edit_dates = function( resa ) {
			resa.check_in_tmp( resa.check_in() );
			resa.check_out_tmp( resa.check_out() );
			$( '.hb-input-edit-resa-dates' ).datepick( hb_datepicker_calendar_options );
			$( '.hb-input-edit-resa-dates' ).datepick( 'option', {
				dateFormat : 'yyyy-mm-dd',
	            onSelect: function() {
	                jQuery( this ).change();
	            }
			});
	        $( '.hb-input-edit-resa-check-in' ).change( function () {
	            var check_in_date = $( this ).datepick( 'getDate' )[0],
	                $check_out_date_input = jQuery( this ).parent().find( '.hb-input-edit-resa-check-out' ),
	                check_out_date = $check_out_date_input.datepick( 'getDate' )[0];
	            if ( check_in_date && check_out_date && ( check_in_date.getTime() >= check_out_date.getTime() ) ) {
	                $check_out_date_input.datepick( 'setDate', null );
	            }
	            if ( check_in_date ) {
					var min_check_out = new Date( check_in_date.getTime() );
		            min_check_out.setDate( min_check_out.getDate() + 1 );
	                $check_out_date_input.datepick( 'option', 'minDate', min_check_out );
	            }
	        }).change();
            resa.editing_dates( true );
        }
        
        this.cancel_edit_dates = function( resa ) {
            resa.editing_dates( false );
        }
		
		this.save_dates = function( resa ) {
			if ( ! resa.nb_nights_tmp() ) {
				alert( hb_text.invalid_date );
				return;
			} else if ( resa.nb_nights_tmp() < 1 ) {
				alert( hb_text.check_out_before_check_in );
				return;
			} else if ( resa.check_in_tmp() == resa.check_in() && resa.check_out_tmp() == resa.check_out() ) {
				resa.editing_dates( false );
				return;
			}
			resa.saving_dates( true );
            hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_dates', 
					'resa_id': resa.id,
					'new_check_in': resa.check_in_tmp(),
					'new_check_out': resa.check_out_tmp(),
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
                    resa.saving_dates( false );
					if ( ajax_return == 'resa_dates_modified' ) {
						resa.editing_dates( false );
						resa.check_in( resa.check_in_tmp() );
						resa.check_out( resa.check_out_tmp() );
						for ( var i = 0; i < self.blocked_accom().length; i++ ) {
							if ( self.blocked_accom()[i].linked_resa_id == resa.id ) {
								self.blocked_accom()[i].from_date( resa.check_in_tmp() );
								self.blocked_accom()[i].to_date( resa.check_out_tmp() );
							}
						}
					} else if ( ajax_return == 'resa_dates_not_modified' ) {
						alert( hb_text.resa_dates_not_modified );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		}
		
        this.edit_comment = function( resa ) {
            resa.editing_comment( true );
            resa.admin_comment_tmp( resa.admin_comment() );
        }
        
        this.cancel_edit_comment = function( resa ) {
            resa.editing_comment( false );
        }
        
        this.save_comment = function( resa ) {
            resa.saving_comment( true );
            hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_comment', 
					'resa_comment': resa.admin_comment_tmp(),
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
                    resa.editing_comment( false );
                    resa.saving_comment( false );
					if ( ajax_return == 'admin comment updated' ) {
						resa.admin_comment( resa.admin_comment_tmp() );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
        }
        
		this.edit_charge = function( resa ) {
            resa.editing_charge( true );
        }
		
		this.cancel_edit_charge = function( resa ) {
            resa.editing_charge( false );
        }
		
		this.charge = function( resa ) {
			if ( resa.charge_amount() > resa.price() - resa.paid() ) {
				alert( hb_text.charge_amount_too_high.replace( '%amount', hb_format_price( resa.price() - resa.paid() ) ) );
				return;
			}
			resa.charging( true );
			hb_resa_ajax({
				data: {
					'action': 'hb_resa_charging', 
					'charge_amount': resa.charge_amount(),
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
                    resa.editing_charge( false );
                    resa.charging( false );
					if ( ajax_return == 'charge_done' ) {
						resa.paid( format_price( parseFloat( resa.paid() ) + parseFloat( resa.charge_amount() ) ) );
						resa.charge_amount( format_price( resa.price() - resa.paid() ) );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
		};
		
        this.edit_paid = function( resa ) {
            resa.editing_paid( true );
            resa.price_tmp( resa.price() );
            resa.paid_tmp( resa.paid() );
        }
        
        this.cancel_edit_paid = function( resa ) {
            resa.editing_paid( false );            
        }
  
        this.save_paid = function( resa ) {
            resa.saving_paid( true );
            update_paid( resa, resa.price_tmp(), resa.paid_tmp() );
        }
        
        this.mark_paid = function( resa ) {
            resa.marking_paid( true );
            update_paid( resa, resa.price(), resa.price() );
        }
        
        function update_paid( resa, price, paid ) {
            hb_resa_ajax({
				data: {
					'action': 'hb_update_resa_paid', 
					'resa_price': price,
					'resa_paid': paid,
					'resa_id': resa.id,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
                    resa.editing_paid( false );
                    resa.saving_paid( false );
                    resa.marking_paid( false );
					if ( ajax_return == 'paid updated' ) {
						resa.price( price );
						resa.paid( paid );
						resa.charge_amount( format_price( price - paid ) );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' );
				}
			});
        }
        
		this.add_resa = function() {
			
			$( '#hb-resa-customer input[type="submit"]' ).blur();
			
			var price = $( '#hb-resa-total-price' ).val();
			if ( $( '#hb-resa-price-other' ).val() != '' ) {
				if ( $.isNumeric( $( '#hb-resa-price-other' ).val() ) ) {
					price = $( '#hb-resa-price-other' ).val();
				} else {
					alert( hb_text.invalid_price );
					return false;
				}
			}
			
            if ( ! $( 'input[name="hb-accom-num"]:checked').val() ) {
                alert( hb_text.accom_not_selected );
                return false;
            }
			
            if ( $( '#hb-customer-type-id:checked' ).length ) {
                if ( ! $( '#hb-customer-id-list' ).val() ) {
                    alert( hb_text.customer_not_selected );
                    return false;
                }
            }
			
			var new_resa = {
					'price': price,
					'check_in': $( '#hb-check-in' ).val(),
					'check_out': $( '#hb-check-out' ).val(),
					'adults': $( '#hb-adults' ).val(),
					'children': $( '#hb-children' ).val(),
					'accom_id': $( '#hb-accom' ).val(),
					'accom_num': $( 'input[name="hb-accom-num"]:checked' ).val(),
					'admin_comment': $( '#hb-admin-comment' ).val()
				},
				options = {},
				ajax_settings = {
					'action': 'hb_create_resa', 
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				customer = {},
				additional_info = {},
				data; 
			
			$( '.hb-option' ).each( function() {
				if ( $( this ).hasClass( 'hb-quantity-option' ) ) {
					options[ $( this ).find( 'input' ).attr( 'name' ) ] = $( this ).find( 'input' ).val();
				} else if ( $( this ).hasClass( 'hb-multiple-option' ) ) {
					options[ $( this ).find( 'input' ).attr( 'name' ) ] = $( this ).find( 'input:checked' ).val();
				} else if ( $( this ).hasClass( 'hb-single-option' ) ) {
					if (  $( this ).find( 'input' ).is( ':checked' ) ) {
						options[ $( this ).find( 'input' ).attr( 'name' ) ] = 'chosen';
					}
				}
			});

			if ( $( 'input[name="hb-customer-type"]:checked' ).val() == 'details' ) {
				$( '#hb-resa-customer-details .hb-detail-field' ).each( function() {
					customer[ $( this ).attr( 'name' ) ] = $( this ).val();
				});
				$( '#hb-resa-customer-details input[type="radio"]:checked' ).each( function() {
					customer[ $( this ).attr( 'name' ) ] = $( this ).val();
				});
				$( '#hb-resa-customer-details input[type="checkbox"]:checked' ).each( function() {
					if ( customer[ $( this ).attr( 'name' ) ] ) {
						customer[ $( this ).attr( 'name' ) ].push( $( this ).val() );
					} else {
						customer[ $( this ).attr( 'name' ) ] = [ $( this ).val() ];
					}
				});
			} else {
				customer['customer_id'] = $( '#hb-customer-id-list' ).val()[0];
			}
			
			$( '#hb-resa-additional-info .hb-detail-field' ).each( function() {
				additional_info[ $( this ).attr( 'name' ) ] = $( this ).val();
			});
			$( '#hb-resa-additional-info input[type="radio"]:checked' ).each( function() {
				additional_info[ $( this ).attr( 'name' ) ] = $( this ).val();
			});
			$( '#hb-resa-additional-info input[type="checkbox"]:checked' ).each( function() {
				if ( additional_info[ $( this ).attr( 'name' ) ] ) {
					additional_info[ $( this ).attr( 'name' ) ].push( $( this ).val() );
				} else {
					additional_info[ $( this ).attr( 'name' ) ] = [ $( this ).val() ];
				}
			});
			
			data = $.extend( {}, new_resa, options, customer, additional_info, ajax_settings ); 
			
			$( '#hb-resa-customer-submit-ajax .hb-ajaxing' ).css( 'display', 'inline' );
			$( '#hb-create-resa-error' ).slideUp();
			
			hb_resa_ajax({
				data: data,
				success: function( response_text ) {
					$( '#hb-resa-customer-submit-ajax .hb-ajaxing' ).css( 'display', 'none' );
					try {
						var response = JSON.parse( response_text );
					} catch ( e ) {
						$( '#hb-create-resa-error' ).html( response_text ).slideDown();
						return false;
					}
					if ( response['success'] ) {
						for ( var i = 0; i < response['blocked_linked_accom'].length; i++ ) {
							var new_blocked_accom = new BlockedAccom( 
								response['blocked_linked_accom'][i]['from_date'],
								response['blocked_linked_accom'][i]['to_date'],
								response['blocked_linked_accom'][i]['accom_id'],
								response['blocked_linked_accom'][i]['accom_num'],
								0,
								0,
								response['blocked_linked_accom'][i]['comment'],
								response['blocked_linked_accom'][i]['linked_resa_id']
							);
							self.blocked_accom.unshift( new_blocked_accom );
						}
						$( '#hb-add-resa' ).hide();
						$( '#hb-add-resa-toggle .dashicons-arrow-down' ).css( 'display', 'inline-block' );
						$( '#hb-add-resa-toggle .dashicons-arrow-up' ).hide();
                        $( '#hb-resa-customer' ).hide();
                        $( 'html, body' ).animate({ scrollTop: $( '#hb-add-resa-section' ).offset().top - 40 });
						$( '#hb-resa-customer-details-wrap input[type="text"], #hb-resa-customer-details-wrap textarea' ).val( '' );
						$( '#hb-check-out, #hb-check-in' ).val( '' );
                        self.resa_current_page_number( 1 );
						setTimeout( function() {
							customer = ko.utils.arrayFirst( self.customers_list(), function( customer ) {
							    return response.customer.id == customer.id;
							});
							if ( ! customer ) {
								self.customers_list.push(
									new Customer( 
										response.customer.id,
										response.customer.info,
										''
									)
								);
							}
                            the_resa = new Resa( 
                                response.resa_id,
                                hb_new_resa_status,
                                response.price,
                                0,
                                '',
                                new_resa.check_in,
                                new_resa.check_out,
                                new_resa.adults,
                                new_resa.children,
                                new_resa.accom_id,
                                new_resa.accom_num,
                                response.non_editable_info,
                                new_resa.admin_comment,
                                response.customer.id,
                                response.received_on,
								'website',
								response.additional_info,
								self
                            );
                            the_resa.anim_class( 'hb-resa-added' );
							self.resa.unshift( the_resa );
							setTimeout( function() {
                                the_resa.anim_class( '' );
                            }, 300 );
						}, 1000 );
					} else {
						$( '#hb-create-resa-error' ).html( response['error'] ).slideDown();
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( textStatus + ' (' + errorThrown + ')' )
				}
			});
		}
		
		this.delete_resa = function( resa ) {
			if ( confirm( hb_text.confirm_delete_resa ) ) {
				resa.deleting( true );
				hb_resa_ajax({
					data: {
						'action': 'hb_delete_resa', 
						'resa_id': resa.id,
						'nonce': $( '#hb_nonce_update_db' ).val()
					},
					success: function( ajax_return ) {
						if ( ajax_return == 'resa deleted' ) {
							self.blocked_accom.remove( function( blocked_accom ) { 
								return blocked_accom.linked_resa_id == resa.id;
							});
                            resa.anim_class( 'hb-resa-deleting' );
							setTimeout( function() { 
                                self.resa.remove( resa );
                            }, 300 );
						} else {
							resa.deleting( false );
							alert( ajax_return );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						resa.deleting( false );
						alert( textStatus + ' (' + errorThrown + ')' )
					}
				});
			}
		}
			
		this.add_resa_customers_list_filter = ko.observable( '' );
		
		this.add_resa_customers_list = ko.computed( function() {
			customers_id_name_list = [];
			for ( var i = 0; i < self.customers_list().length; i++ ) {
				var customer = {
					id: self.customers_list()[ i ].id,
					id_name: self.customers_list()[ i ].last_name() + ' ' + self.customers_list()[ i ].first_name() + ' (' + hb_text.customer_id_short + ' ' + self.customers_list()[ i ].id + ')'
				}
				customers_id_name_list.push( customer );
			}
			customers_id_name_list.sort( function( a, b ) { 
				return a.id_name.localeCompare( b.id_name );
			});
			if ( ! self.add_resa_customers_list_filter() ) {
				return customers_id_name_list;
			} else {
				var filtered_customers = ko.utils.arrayFilter( customers_id_name_list, function( customer ) {
					if ( customer.id_name.toLowerCase().replace( /\s/g, '' ).indexOf( self.add_resa_customers_list_filter().toLowerCase().replace( /\s/g, '' ) ) >= 0 ) {
						return true;
					} else {
						return false;
					}
				});
				if ( filtered_customers.length == 1 ) {
					$( '#hb-customer-id-list' ).val( [ filtered_customers[0].id ] );
				}
				return filtered_customers;
			}
			
		});
		
		this.resa_filter = ko.observable( 'none' );
		this.resa_filter_customer = ko.observable( '' );
		this.resa_filter_check_in_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_in_to = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_out_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_out_to = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_in_out_from = ko.observable( hb_date_to_str( new Date() ) );
		this.resa_filter_check_in_out_to = ko.observable( hb_date_to_str( new Date() ) );
		
		$( '.hb-filter-clear-date' ).click( function() {
			$( this ).prev().val( '' ).change();
		});
		
		$( '.hb-filter-date-from' ).change( function () {
			var from = $( this ).val(),
				to = $( this ).parent().find( '.hb-filter-date-to' ).val();
			if ( from.length == 10 ) {
				$( this ).parent().find( '.hb-filter-date-to' ).datepick( 'option', 'minDate', from );
			} else {
				$( this ).parent().find( '.hb-filter-date-to' ).datepick( 'option', 'minDate', -9999 );
			}
			if ( to && from && ( from > to ) ) {
				$( this ).parent().find( '.hb-filter-date-to' ).val( '' ).change();
			}
		});
		
		$( '.hb-filter-date-to' ).change( function () {
			var from = $( this ).parent().find( '.hb-filter-date-from' ).val();
			if ( from.length == 10 ) {
				var to = $( this ).val();
		        if ( to.length == 10 && ( from > to ) ) {
					$( this ).parent().find( '.hb-filter-date-from' ).val( '' ).change();
				}
			}
		});
		
		this.resa_filtered = ko.computed( function() {
			if ( self.resa_filter() == 'none' ) {
				return self.resa();
			} else if ( self.resa_filter() == 'customer' ) {
				var filter = self.resa_filter_customer().toLowerCase().replace( /\s/g, '' );
				if ( ! filter ) {
					return self.resa();
				} else {
					return ko.utils.arrayFilter( self.resa(), function( resa ) {
						var customer = ko.utils.arrayFirst( self.customers_list(), function( customer ) {
						    return resa.customer_id() == customer.id;
						});
						if ( ! customer ) {
							return false;
						} else {
							if ( customer.name_email().toLowerCase().indexOf( filter ) >= 0 ) {
								return true;
							} else {
								return false;
							}
						}
					});
				}
			} else if ( 
				self.resa_filter() == 'check_in_date' ||
				self.resa_filter() == 'check_out_date' ||
				self.resa_filter() == 'check_in_out_date'
			) {
				return ko.utils.arrayFilter( self.resa(), function( resa ) {
					var from = self.resa_filter_check_in_from(),
						to = self.resa_filter_check_in_to();
					if ( self.resa_filter() == 'check_out_date' ) {
						from = self.resa_filter_check_out_from(),
						to = self.resa_filter_check_out_to();
					} else if ( self.resa_filter() == 'check_in_out_date' ) {
						from = self.resa_filter_check_in_out_from(),
						to = self.resa_filter_check_in_out_to();
					}
					if ( from.trim() == '' ) {
						from = '0000-00-00';
					}
					if ( to.trim() == '' ) {
						to = '9999-99-99';
					}
					if ( 
						self.resa_filter() == 'check_in_date' && ( resa.check_in() >= from && resa.check_in() <= to ) ||
						self.resa_filter() == 'check_out_date' && ( resa.check_out() >= from && resa.check_out() <= to ) ||
						self.resa_filter() == 'check_in_out_date' && ( ( resa.check_in() >= from && resa.check_in() <= to ) || ( resa.check_out() >= from && resa.check_out() <= to ) )
					) {
						return true;
					} else {
						return false;
					}
				});
			}
		});
		
		this.resa_sort = ko.observable( 'received_date' );
		
		this.resa_sorted = ko.computed( function() {
			if ( self.resa_sort() == 'check_in_date' ) {
				return self.resa_filtered().slice().sort( function( a, b ) {
					if ( a.check_in() < b.check_in() ) {
						return 1;
					} else if ( a.check_in() > b.check_in() ) {
						return -1;
					} else {
						return 0;
					}
				});
			} else {
				return self.resa_filtered();
			}
		});
		
		function blur_buttons() {
			$( '.button' ).blur();
		}
		
		this.resa_per_page = 25;
		this.resa_current_page_number = ko.observable( 1 );
		
		this.resa_first_page = function() {
			self.resa_current_page_number( 1 );
			blur_buttons();
		}
		
		this.resa_last_page = function() {
			self.resa_current_page_number( self.resa_total_pages() );
			blur_buttons();
		}
		
		this.resa_next_page = function() {
			if ( self.resa_current_page_number() != self.resa_total_pages() ) {
				self.resa_current_page_number( self.resa_current_page_number() + 1 );
			}
			blur_buttons();
		}
		
		this.resa_previous_page = function() {
			if ( self.resa_current_page_number() != 1 ) {
				self.resa_current_page_number( self.resa_current_page_number() - 1 );
			}
			blur_buttons();
		}
				
		this.resa_total_pages = ko.computed(function() {
			var total = Math.floor( self.resa_sorted().length / self.resa_per_page );
			total += self.resa_sorted().length % self.resa_per_page > 0 ? 1 : 0;
			return total;
		});
		
		this.resa_paginated = ko.computed( function() {
			if ( self.resa_current_page_number() > self.resa_total_pages() ) {
				self.resa_current_page_number( 1 );
			}
			var first = self.resa_per_page * ( self.resa_current_page_number() - 1 );
			return self.resa_sorted().slice( first, first + self.resa_per_page );
		});
		
		this.selected_resa = ko.observable( 0 );
		
		$( '#hb-resa-cal-scroller' ).on( 'click', '.hbdlcd', function() {
			$( this ).blur();
			self.selected_resa( $( this ).data( 'resa-id' ) );
			return false;
		});
		
		this.hide_selected_resa = function() {
			self.selected_resa( 0 );
		}
		
		this.resa_detailed = ko.computed( function() {
			if ( self.selected_resa() == 0 ) {
				return [];
			}
			for ( var i = 0; i < self.resa().length; i++ ) {
				if ( self.resa()[ i ].id == self.selected_resa() ) {
					return self.resa()[ i ];
				}
			}
			self.selected_resa( 0 );
			return [];
		});
		
		this.add_blocked_accom = function() {
			$( '.hb-add-blocked-accom-submit input' ).blur();
			$( '.hb-add-blocked-accom-submit .hb-ajaxing' ).css( 'display', 'inline-block' );

			var from_date = $( '#hb-block-accom-from-date' ).val(),
				to_date = $( '#hb-block-accom-to-date' ).val(),
				accom_id = $( '#hb-select-blocked-accom-type' ).val(),
				accom_num = $( '#hb-select-blocked-accom-num' ).val(),
				accom_all_ids = 0,
				accom_all_num = 0,
				comment = $( '#hb-block-accom-comment' ).val();

			if ( ! from_date || from_date == '0000-00-00' ) {
				from_date = '2016-01-01';
			}
			if ( ! to_date || to_date == '0000-00-00' ) {
				to_date = '2029-12-31';
			}
			if ( accom_id == 'all' ) {
				accom_all_ids = 1;
				accom_all_num = 1;
				accom_id = 0;
				accom_num = 0;
			}
			if ( accom_num == 'all' ) {
				accom_num = 0;
				accom_all_num = 1;
			}
			
			hb_resa_ajax({
				data: {
					'action': 'hb_add_blocked_accom', 
					'accom_id': accom_id,
					'accom_num': accom_num,
					'accom_all_ids': accom_all_ids,
					'accom_all_num': accom_all_num,
					'from_date': from_date,
					'to_date': to_date,
					'comment': comment,
					'nonce': $( '#hb_nonce_update_db' ).val()
				},
				success: function( ajax_return ) {
					$( '.hb-add-blocked-accom-submit .hb-ajaxing' ).css( 'display', 'none' );
					if ( ajax_return == 'blocked accom added' ) {
						var new_blocked_accom = new BlockedAccom( 
							from_date,
							to_date,
							accom_id,
							accom_num,
							accom_all_num,
							accom_all_ids,
							comment,
							0
						);
						new_blocked_accom.anim_class( 'hb-blocked-accom-added' );
						self.blocked_accom.unshift( new_blocked_accom );
						setTimeout( function() {
							new_blocked_accom.anim_class( '' );
						}, 300 );
					} else {
						alert( ajax_return );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					$( '.hb-add-blocked-accom-submit .hb-ajaxing' ).css( 'display', 'none' );
					blocked_accom.deleting( false );
					alert( textStatus + ' (' + errorThrown + ')' )
				}
			});
		}
		
		this.delete_blocked_accom = function( blocked_accom ) {
			if ( confirm( hb_text.confirm_delete_blocked_accom ) ) {
				blocked_accom.deleting( true );
				var accom_id = 0,
					accom_num = 0;
				if ( blocked_accom.accom_id ) {
					accom_id = blocked_accom.accom_id;
				}
				if ( blocked_accom.accom_num ) {
					accom_num = blocked_accom.accom_num;
				}
				hb_resa_ajax({
					data: {
						'action': 'hb_delete_blocked_accom',
						'date_from': blocked_accom.from_date,
						'date_to': blocked_accom.to_date,
						'accom_id': accom_id,
						'accom_num': accom_num,
						'accom_all_ids': blocked_accom.accom_all_ids,
						'accom_all_num': blocked_accom.accom_all_num,
						'nonce': $( '#hb_nonce_update_db' ).val()
					},
					success: function( ajax_return ) {
						if ( ajax_return == 'blocked accom deleted' ) {
							blocked_accom.anim_class( 'hb-blocked-accom-deleting' );
							setTimeout( function() { 
								self.blocked_accom.remove( blocked_accom );
							}, 300 );
						} else {
							blocked_accom.deleting( false );
							alert( ajax_return );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						blocked_accom.deleting( false );
						alert( textStatus + ' (' + errorThrown + ')' )
					}
				});
			}
		}
		
		
		var observable_customers = [];
		for ( var i = 0; i < hb_customers.length; i++ ) {
			observable_customers.push( 
				new Customer(
					hb_customers[i].id,
					hb_customers[i].info,
					hb_customers[i].payment_id
				)
			);
		}
		this.customers_list( observable_customers );
		
        var prepared_resa = [],
            not_processing_status = [ 'new', 'pending', 'confirmed', 'cancelled' ];
		for ( var i = 0; i < resa.length; i++ ) {
			if ( not_processing_status.indexOf( resa[i].status ) < 0 ) {
				resa[i].status = 'processing';
			}
            the_resa = new Resa( 
                resa[i].id, 
                resa[i].status,
                resa[i].price,
                resa[i].paid,
                resa[i].old_currency,
                resa[i].check_in, 
                resa[i].check_out, 
                resa[i].adults,
                resa[i].children,
                resa[i].accom_id, 
                resa[i].accom_num, 
                resa[i].non_editable_info,
                resa[i].admin_comment,
                resa[i].customer_id,
                resa[i].received_on,
				resa[i].origin,
				resa[i].additional_info,
				this
            );
			prepared_resa.push( the_resa );
		}
        this.resa( prepared_resa );
        
		var observable_blocked_accom = [];
		for ( var i = 0; i < hb_blocked_accom.length; i++ ) {
			observable_blocked_accom.push( 
				new BlockedAccom( 
					hb_blocked_accom[i].from_date,
					hb_blocked_accom[i].to_date,
					hb_blocked_accom[i].accom_id,
					hb_blocked_accom[i].accom_num,
					hb_blocked_accom[i].accom_all_num,
					hb_blocked_accom[i].accom_all_ids,
					hb_blocked_accom[i].comment,
					hb_blocked_accom[i].linked_resa_id
				)
			);
		}
		this.blocked_accom( observable_blocked_accom );
	}
	
	var resaViewModel = new ResaViewModel();
    ko.applyBindings( resaViewModel );
    
    var today = new Date();
	hb_create_resa_cal_tables( hb_date_to_str( today ), displayed_accoms );
	resaViewModel.redraw_calendar();
	
});