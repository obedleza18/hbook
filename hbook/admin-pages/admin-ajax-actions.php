<?php
class HbAdminAjaxActions {

	private $hbdb;
	private $utils;
	private $options_utils;
	private $stripe;
	
	public function __construct( $db, $utils, $options_utils, $stripe ) {
		$this->hbdb = $db;
		$this->utils = $utils;
		$this->options_utils = $options_utils;
		$this->stripe = $stripe;
	}
	
	private function hb_verify_nonce() {
		if ( wp_verify_nonce( $_POST['nonce'], 'hb_nonce_update_db' ) ) {
			return true;
		} else {
			esc_html_e( 'Your session has expired. Please refresh the page.', 'hbook-admin' );
			return false;
		}
	}
	
	private function hb_user_can() {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		} else {
			echo( 'Not enough privileges to do this action.' );
			return false;
		}
	}
	
	private function hb_user_can_manage_resa() {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_resa' ) ) {
			return true;
		} else {
			echo( 'Not enough privileges to do this action.' );
			return false;
		}
	}
	
	public function hb_update_db() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			echo( $this->hbdb->update_hb_setting( $_POST['db_action'], $_POST['object'] ) );
		} 
		die;
	}
    
    public function hb_update_appearance_settings() {
        if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
            $settings = $this->options_utils->get_options_list( 'appearance_settings' );
            foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					update_option( $setting, wp_strip_all_tags( stripslashes( $_POST[ $setting ] ) ) );
				}
			}
            echo( 'settings saved' );
		}
		die;
    }
	
    public function hb_update_payment_settings() {
        if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
            $settings = $this->options_utils->get_options_list( 'payment_settings' );
			foreach ( $this->utils->get_payment_gateways() as $gateway ) {
				$settings[] = 'hb_' . $gateway->id .'_active';
				$gateway_admin_fields = $gateway->admin_fields();
				$gateway_options = $gateway_admin_fields['options'];
				foreach ( $gateway_options as $id => $option ) {
					$settings[] = $id;
				}
			}
            foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					update_option( $setting, wp_strip_all_tags( stripslashes( $_POST[ $setting ] ) ) );
				}
			}
            echo( 'settings saved' );
		}
		die;
    }
    
	public function hb_update_misc_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$settings = $this->options_utils->get_options_list( 'misc_settings' );
            if ( isset( $_POST[ 'hb_accommodation_slug' ] ) && $_POST[ 'hb_accommodation_slug' ] != get_option( 'hb_accommodation_slug' ) ) {
                update_option( 'hb_flush_rewrite', 'flush' );
            }
			foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					update_option( $setting, wp_strip_all_tags( trim( stripslashes( $_POST[ $setting ] ) ) ) );
				}
			}
			echo( 'settings saved' );
		}
		die;
	}
	
	public function hb_update_forms() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$options = $this->options_utils->get_options_list( 'search_form_options' );
			foreach ( $options as $option ) {
				update_option( $option, wp_strip_all_tags( stripslashes( $_POST[ $option ] ) ) );
			}
			$options = $this->options_utils->get_options_list( 'accom_selection_options' );
			foreach ( $options as $option ) {
				update_option( $option, wp_strip_all_tags( stripslashes( $_POST[ $option ] ) ) );
			}
			$this->hbdb->update_fields( wp_strip_all_tags( stripslashes( $_POST['hb_fields'] ) ) );
			echo( 'settings saved' );
		}
		die;
	}
	
	public function hb_update_contact() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$options = $this->options_utils->get_options_list( 'contact_form_options' );
			foreach ( $options as $option ) {
				if ( $option != 'hb_contact_from' ) {
					update_option( $option, wp_strip_all_tags( stripslashes( $_POST[ $option ] ) ) );
				} else {
					update_option( $option, stripslashes( $_POST[ $option ] ) );
				}
			}
			$this->hbdb->update_fields( wp_strip_all_tags( stripslashes( $_POST['hb_fields'] ) ) );
			echo( 'settings saved' );
		}
		die;
	}
	
	public function hb_update_strings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$strings = $this->utils->get_string_list();
			$langs = $this->utils->get_langs();
			$strings_to_update = array();
			foreach ( $strings as $string_id => $string_name ) {
				foreach ( $langs as $locale => $lang_name ) {
					$input_name = 'string-id-' . $string_id . '-in-' . $locale;
					if ( isset( $_POST[ $input_name ] ) ) {
						$strings_to_update[] = array(
							'id' => $string_id,
							'locale' => $locale,
							'value' => wp_kses_post( stripslashes( $_POST[ $input_name ] ) )
						);
					}
				}
			}
			$this->hbdb->update_strings( $strings_to_update );
			echo( 'form saved' );
		}
		die;
	}	
	
	public function hb_update_rates() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			if( $this->hbdb->update_rates( $_POST['rate_type'], $_POST['rates'] ) ) {
				echo( 'rates saved' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_change_resa_status() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_status( $_POST['resa_id'], $_POST['resa_status'] ) ) {
                switch ( $_POST['resa_status'] ) {
                    case 'confirmed' : $this->utils->send_email( 'confirmation_resa', $_POST['resa_id'] ); break;
                    case 'cancelled' : $this->utils->send_email( 'cancellation_resa', $_POST['resa_id'] ); break;
                }
				echo( 'resa updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}	
    
    public function hb_confirm_resa() {
        if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
            $resa = $this->hbdb->get_resa_by_id( $_POST['resa_id'] );
            $response = array();
            $accom_num = $this->hbdb->get_first_available_accom_num( $resa['accom_id'], $resa['check_in'], $resa['check_out'] );
            if ( $accom_num ) {
                if ( ( $this->hbdb->update_resa_accom( $_POST['resa_id'], $resa['accom_id'], $accom_num ) !== false ) 
                    && ( $this->hbdb->update_resa_status( $_POST['resa_id'], 'confirmed' ) ) ) {
                    $response['status'] = 'confirmed';
                    $response['accom_num'] = $accom_num;
					$response['blocked_linked_accom'] = $this->hbdb->block_linked_accom( $resa['accom_id'], $resa['check_in'], $resa['check_out'], $_POST['resa_id'] );
                    $this->utils->send_email( 'confirmation_resa', $_POST['resa_id'] );
                } else {
                    $response['status'] = 'Database error.';
                }
            } else {
                $response['status'] = 'no accom available';
            }
            echo( json_encode( $response ) );
        }
        die;
    }
    
	public function hb_update_resa_info() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_info( $_POST['resa_id'], $_POST['adults'], $_POST['children'], $_POST['additional_info'] ) !== false ) {
				echo( 'resa info updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;		
	}
	
    public function hb_update_resa_comment() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_comment( $_POST['resa_id'], $_POST['resa_comment'] ) !== false ) {
				echo( 'admin comment updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}       
    
    public function hb_edit_accom_get_avai() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
            $accom = $this->hbdb->get_all_accom_ids();
            $all_avai_accom_with_num = array();
            foreach ( $accom as $accom_id ) {
                $accom_num = array_keys( $this->hbdb->get_accom_num_name( $accom_id ) );
				$unavai_accom_num = $this->hbdb->get_unavailable_accom_num_per_date( $accom_id, $_POST['check_in'], $_POST['check_out'] );
				$avai_accom_num = array_values( array_diff( $accom_num, $unavai_accom_num ) );
                $all_avai_accom_with_num[] = array(
                    'accom_id' => $accom_id,
                    'accom_num' => $avai_accom_num
                );
            }
            echo( json_encode( $all_avai_accom_with_num ) );
		}
		die;
	} 
    
    
    public function hb_update_resa_accom() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
            if ( $this->hbdb->is_available_accom_num( $_POST['accom_id'], $_POST['accom_num'], $_POST['check_in'], $_POST['check_out'] ) ) {
                if ( $this->hbdb->update_resa_accom( $_POST['resa_id'], $_POST['accom_id'], $_POST['accom_num'] ) ) {
					$response = array(
						'success' => true,
						'blocked_linked_accom' => $this->hbdb->block_linked_accom( $_POST['accom_id'], $_POST['check_in'], $_POST['check_out'], $_POST['resa_id'] )
					);
                } else {
					$response = array(
						'success' => false,
						'error' => 'Database error.'
					);
                }
				echo( json_encode( $response ) );
            } else {
                $accom_num_name = $this->hbdb->get_accom_num_name( $_POST['accom_id'] );
				printf( esc_html__( 'The %s (%s) is no longer available.', 'hbook-admin' ), get_the_title( $_POST['accom_id'] ), $accom_num_name[ $_POST['accom_num'] ] );
            }
        }
        die;
	}
        
    public function hb_update_resa_paid() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_paid( $_POST['resa_id'], $_POST['resa_price'], $_POST['resa_paid'] ) !== false ) {
				echo( 'paid updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}
    
	public function hb_update_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$customer_id = intval( $_POST['customer_id'] );
			$customer_email = wp_strip_all_tags( $_POST['email'] );
			$customer_info = json_decode( wp_strip_all_tags( stripslashes( $_POST['info'] ) ), true );
			if ( $this->hbdb->update_customer( $customer_id, $customer_email, $customer_info ) !== false ) {
				echo( 'customer updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}
	
	public function hb_resa_check_price() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( ! is_numeric( $_POST['accom_id'] ) ) {
				die;
			}
			$validation = $this->utils->validate_date_and_people( $_POST['check_in'], $_POST['check_out'], $_POST['adults'], $_POST['children'] );
			if ( $validation['success'] ) {
				
                require_once dirname( plugin_dir_path( __FILE__ ) ) . '/utils/price-calc.php';
				$price_calc = new HbPriceCalc( $this->hbdb, $this->utils );
				$price_breakdown = '';
				$price = $price_calc->get_price( $_POST['accom_id'], $_POST['check_in'], $_POST['check_out'], $_POST['adults'], $_POST['children'], $price_breakdown );
                
                require_once dirname( plugin_dir_path( __FILE__ ) ) . '/utils/resa-options.php';
                $options_form = new HbOptionsForm( $this->hbdb, $this->utils );
                $adults = intval( $_POST['adults'] );
                $children = intval( $_POST['children'] );
                $nb_nights = $this->utils->get_number_of_nights( $_POST['check_in'], $_POST['check_out'] );
                $options_form_mark_up = $options_form->get_options_form_markup_backend( $adults, $children, $nb_nights );
                
                require_once dirname( plugin_dir_path( __FILE__ ) ) . '/utils/resa-fees.php';
                $fees_markup = new HbResaFees( $this->hbdb, $this->utils );
                $fees = $fees_markup->get_fees_markup_admin();
                
                $avai_accom_num = array();
				$accom_num = array_keys( $this->hbdb->get_accom_num_name( $_POST['accom_id'] ) );
				$unavai_accom_num = $this->hbdb->get_unavailable_accom_num_per_date( $_POST['accom_id'], $_POST['check_in'], $_POST['check_out'] );
				$avai_accom_num = array_values( array_diff( $accom_num, $unavai_accom_num ) );
                
				if ( $price['success'] ) {
					$response = array( 
                        'success' => true, 
                        'price' => $price['value'], 
                        'price_with_symbol' => $this->utils->price_with_symbol( $price['value'] ), 
                        'price_breakdown' => $price_breakdown, 
                        'options_form' => $options_form_mark_up,
                        'fees' => $fees,
                        'accom_num' => $avai_accom_num 
                    );
				} else {
					$response = array( 
                        'success' => false, 
                        'error' => $price['error'] 
                    );
				}
			} else {
				$response = array( 'success' => false, 'error' => 'Error: ' . $validation['error_msg'] );
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_create_resa() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( ! $this->hbdb->is_available_accom_num( $_POST['accom_id'], $_POST['accom_num'], $_POST['check_in'], $_POST['check_out'] ) ) {
                $accom_num_name = $this->hbdb->get_accom_num_name( $_POST['accom_id'] );
				$response = array( 'success' => false, 'error' => sprintf( esc_html__( 'The %s (%s) is no longer available.', 'hbook-admin' ), get_the_title( $_POST['accom_id'] ), $accom_num_name[ $_POST['accom_num'] ] ) );
			} else {
				if ( isset( $_POST['customer_id'] ) ) {
					$customer_id = $_POST['customer_id'];
				} else {
					$customer_info = $this->utils->get_posted_customer_info();
					$customer_email = '';
					if ( isset( $_POST['hb_email'] ) ) {
						$customer_email = stripslashes( strip_tags( $_POST['hb_email'] ) );
					}
					
					$customer_id = $this->hbdb->get_customer_id( $customer_email );
					if ( $customer_id ) {
						$customer_id = $this->hbdb->update_customer_on_resa_creation( $customer_id, $customer_email, $customer_info );
					} else {
						$customer_id = $this->hbdb->create_customer( $customer_email, $customer_info );
					}
				}
				if ( ! $customer_id ) {
					$response = array( 'success' => false, 'error' => $this->hbdb->last_query() );
				} else {
                    
                    $options = $this->hbdb->get_options_with_choices( $_POST['accom_id'] );
                    $adults = intval( $_POST['adults'] );
                    $children = intval( $_POST['children'] );
                    $nb_nights = $this->utils->get_number_of_nights( $_POST['check_in'], $_POST['check_out'] );
                    $price_options = $this->utils->calculate_options_price( $adults, $children, $nb_nights, $options );
                    $options_total_price = 0;
                    $chosen_options = array();
					foreach ( $options as $option ) {
                        if ( $option['apply_to_type'] == 'quantity' || $option['apply_to_type'] == 'quantity-per-day' ) {
                            $quantity = intval( $_POST[ 'hb_option_' . $option['id'] ] );
							if ( $quantity < 0 ) {
								$quantity = 0;
							}
                            $chosen_options[ $option['id'] ] = $quantity;
                            $options_total_price += $quantity * $price_options[ 'option_' . $option['id'] ];
                        } else if ( $option['choice_type'] == 'single' ) {
                            if ( isset( $_POST[ 'hb_option_' . $option['id'] ] ) ) {
                                $chosen_options[ $option['id'] ] = 'chosen';
                                $options_total_price += $price_options[ 'option_' . $option['id'] ];
                            }
                        } else {
                            foreach ( $option['choices'] as $choice ) {
                                if ( $_POST[ 'hb_option_' . $option['id'] ] == $choice['id'] ) {
                                    $chosen_options[ $option['id'] ] = $choice['id'];
                                    $options_total_price += $price_options[ 'option_choice_' . $choice['id'] ];
                                }
                            }
                        }
                    }
					$chosen_options = json_encode( $chosen_options );
                    
					$deposit = 0;
			        if ( get_option( 'hb_deposit_type' ) == 'fixed' ) {
			            $deposit = get_option( 'hb_deposit_amount' );
			        } else if ( get_option( 'hb_deposit_type' ) == 'percentage' ) {
		                $deposit = $_POST['price'] * get_option( 'hb_deposit_amount' ) / 100;
						if ( get_option( 'hb_price_precision' ) == 'no_decimals' ) {
							$deposit = round( $deposit );
	                    } else {
							$deposit = round( $deposit, 2 );
						}
			        }
					
					$resa_info = array(
						'accom_id' => $_POST['accom_id'],
						'accom_num' => $_POST['accom_num'],
						'check_in' => $_POST['check_in'],
						'check_out' => $_POST['check_out'],
						'adults' => $_POST['adults'],
						'children' => $_POST['children'],
						'price' => $_POST['price'],
						'deposit' => $deposit,
						'currency' => get_option( 'hb_currency' ),
						'customer_id' => $customer_id,
						'admin_comment' => $_POST['admin_comment'],
						'additional_info' => $this->utils->get_posted_additional_booking_info(),
                        'options' => $chosen_options,
                        'lang' => get_locale(),
						'payment_token' => '',
						'status' => get_option( 'hb_resa_admin_status' ),
						'origin' => 'website',
						'payment_gateway' => '',
					);
					$resa_id = $this->hbdb->create_resa( $resa_info );
					if ( $resa_id === false ) {
						$response = array( 'success' => false, 'error' => 'Error (could not create customer).' );
					} else {
						$customer = $this->hbdb->get_single( 'customers', $customer_id );
						$response = array( 
							'success' => true, 
							'resa_id' => $resa_id, 
							'price' => $resa_info['price'],
							'customer' => array(
								'id' => $customer['id'],
								'info' => $customer['info'],
							),
                            'non_editable_info' => $this->utils->resa_non_editable_info_markup( $resa_info ),
							'received_on' => $this->utils->get_blog_datetime( current_time( 'mysql', 1 ) ),
							'additional_info' => json_encode( $resa_info['additional_info'] ),
							'blocked_linked_accom' => $this->hbdb->block_linked_accom( $resa_info['accom_id'], $resa_info['check_in'], $resa_info['check_out'], $resa_id ),
						);
						$this->utils->send_email( 'new_resa_admin', $resa_id );
					}
				}
			}
			echo( json_encode( $response ) );
		}
		die;
	}
	
	public function hb_delete_resa() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if( $this->hbdb->delete_resa( $_POST['resa_id'] ) ) {
				echo( 'resa deleted' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}	
	
	public function hb_delete_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if( $this->hbdb->delete_customer( $_POST['customer_id'] ) ) {
				echo( 'customer_deleted' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}
	
	public function hb_add_blocked_accom() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->add_blocked_accom( $_POST['accom_id'], $_POST['accom_num'], $_POST['accom_all_ids'], $_POST['accom_all_num'], $_POST['from_date'], $_POST['to_date'], $_POST['comment'] ) ) {
				echo( 'blocked accom added' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}
	
	public function hb_delete_blocked_accom() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->delete_blocked_accom( $_POST['date_from'], $_POST['date_to'], $_POST['accom_id'], $_POST['accom_num'], $_POST['accom_all_ids'], $_POST['accom_all_num'] ) ) {
				echo( 'blocked accom deleted' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}
	
	public function hb_delete_sync_errors() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$this->hbdb->delete_sync_errors();
		}
		die;
	}
	
	public function hb_create_resa_new_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$response = array();
			$customer_id = $this->hbdb->create_customer( '', array() );
			if ( $customer_id ) {
				if ( $this->hbdb->resa_update_customer_id( $_POST['resa_id'], $customer_id ) ) {
					$response['customer_id'] = $customer_id;
				}
			}
			echo( json_encode( $response ) );
		}
		die;
	}
	
	public function hb_resa_charging() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$resa = $this->hbdb->get_resa_by_id( $_POST['resa_id'] );
			$customer_payment_id = $this->hbdb->get_customer_payment_id( $resa['customer_id'] );
			$customer_info = $this->hbdb->get_customer_info( $resa['customer_id'] );
			
			$charge_amount = floatval( $_POST['charge_amount'] );
			if ( $charge_amount < 0 || $charge_amount > $resa['price'] - $resa['paid'] ) {
				echo( 'Invalid amount.' );
				die;
			}
			
			$currency = get_option( 'hb_currency' );
			if ( $currency != $resa['currency'] ) {
				echo( 'Currency error.' );
				die;
			}
			
			$customer_email = '';
			$customer_first_name = '';
			$customer_last_name = '';
			if ( isset( $customer_info['email'] ) ) {
				$customer_email = $customer_info['email'];
			}
			if ( isset( $customer_info['first_name'] ) ) {
				$customer_first_name = $customer_info['first_name'];
			}
			if ( isset( $customer_info['last_name'] ) ) {
				$customer_last_name = $customer_info['last_name'];
			}
			$payment_description = $customer_email;
			if ( $customer_first_name || $customer_last_name ) {
				$payment_description .= ' (' . $customer_first_name . ' ' . $customer_last_name . ')';
			}
			if ( $payment_description ) {
				$payment_description .= ' - ';
			}
			$payment_description .= get_the_title( $resa['accom_id'] );
			$payment_description .= ' (' . esc_html__( 'from', 'hbook-admin' ) . ' ' . $resa['check_in'] . ' ' . esc_html__( 'to', 'hbook-admin' ) . ' ' . $resa['check_out'] . ')';
	    
			$post_args = array( 
				'amount' => $charge_amount,
				'currency' => $currency,
				'customer' => $customer_payment_id,
				'description' => $payment_description,
			);
			
			$response = $this->stripe->remote_post_to_stripe( 'https://api.stripe.com/v1/charges', $post_args );
			if ( $response['success'] ) {
				$this->hbdb->update_resa_paid( $_POST['resa_id'], $resa['price'], $resa['paid'] + $charge_amount );
				echo( 'charge_done' );
			} else {
				echo( $response['error_msg'] );
			}
		}
		die;
	}
	
	public function hb_update_resa_dates() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$resa = $this->hbdb->get_resa_by_id( $_POST['resa_id'] );		
			$new_check_in_time = strtotime( $_POST['new_check_in'] );
			$new_check_out_time = strtotime( $_POST['new_check_out'] );
			$check_in_time = strtotime( $resa['check_in'] );
			$check_out_time = strtotime( $resa['check_out'] );
			$update_resa = false;
			$check_availability_check_in = '';
			$check_availability_check_out = '';
			$double_check_availability = false;
			
			if ( $new_check_out_time <= $check_in_time || $new_check_in_time >= $check_out_time ) {
				$check_availability_check_in = $_POST['new_check_in'];
				$check_availability_check_out = $_POST['new_check_out'];
			} else {
				if ( $new_check_in_time >= $check_in_time ) {
					if ( $new_check_out_time <= $check_out_time ) {
						$update_resa = true;
					} else {
						$check_availability_check_in = $resa['check_out'];
						$check_availability_check_out = $_POST['new_check_out'];
					}
				} else {
					$check_availability_check_in = $_POST['new_check_in'];
					$check_availability_check_out = $resa['check_in'];
					if ( $new_check_out_time > $check_out_time ) {
						$double_check_availability = true;
					}
				}
			}
			
			if ( $check_availability_check_in ) {
				if ( $resa['accom_num'] ) {
					if ( $this->hbdb->is_available_accom_num( $resa['accom_id'], $resa['accom_num'], $check_availability_check_in, $check_availability_check_out ) ) {
						$update_resa = true;
					}
				} else {
					if ( $this->hbdb->is_available_accom( $resa['accom_id'], $check_availability_check_in, $check_availability_check_out ) ) {
						$update_resa = true;
					}
				}
			}
			
			if ( $double_check_availability ) {
				$check_availability_check_in = $resa['check_out'];
				$check_availability_check_out = $_POST['new_check_out'];
				if ( $resa['accom_num'] ) {
					if ( ! $this->hbdb->is_available_accom_num( $resa['accom_id'], $resa['accom_num'], $check_availability_check_in, $check_availability_check_out ) ) {
						$update_resa = false;
					}
				} else {
					if ( ! $this->hbdb->is_available_accom( $resa['accom_id'], $check_availability_check_in, $check_availability_check_out ) ) {
						$update_resa = false;
					}
				}
			}
			
			if ( $update_resa ) {
				if ( $this->hbdb->update_resa_dates( $_POST['resa_id'], $_POST['new_check_in'], $_POST['new_check_out'] ) ) {
					echo( 'resa_dates_modified');
				} else {
					echo( 'Database error.' );
				}
			} else {
				echo( 'resa_dates_not_modified' );
			}
		}
		die;
	}
	
}