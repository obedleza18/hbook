<?php

class HbFrontEndAjaxActions {



	private $hbdb;

	private $utils;

	

	public function __construct( $db, $utils ) {

		$this->hbdb = $db;

		$this->utils = $utils;

	}

	

	public function hb_get_available_accom() {

		require_once plugin_dir_path( __FILE__ ) . 'booking-form/available-accom.php';

		require_once dirname( plugin_dir_path( __FILE__ ) ) . '/utils/resa-options.php';

		require_once dirname( plugin_dir_path( __FILE__ ) ) . '/utils/price-calc.php';

		$price_calc = new HbPriceCalc( $this->hbdb, $this->utils );

        $options_form = new HbOptionsForm( $this->hbdb, $this->utils );

        $available_accom = new HbAvailableAccom( $this->hbdb, $this->utils, $price_calc, $options_form );

		$search_request = array(

			'check_in' => $_POST['check_in'],

			'check_out' => $_POST['check_out'],

			'adults' => $_POST['adults'],

			'children' => $_POST['children'],

			'page_accom_id' => $_POST['page_accom_id'],

			'exists_main_booking_form' => $_POST['exists_main_booking_form']

		);

		$response = $available_accom->get_available_accom( $search_request );

		echo( json_encode( $response ) );

		die;

	}

	

	public function hb_save_details() {

		$response['success'] = true;

		$accom_num = $this->hbdb->get_first_available_accom_num( $_POST['hb-details-accom-id'], $_POST['hb-details-check-in'], $_POST['hb-details-check-out'] );

		if ( ! $accom_num ) {

			$response['success'] = false;

			$response['error_msg'] = $this->hbdb->get_string( 'accom_no_longer_available' );

		} else {

			$customer_info = $this->utils->get_posted_customer_info();

			$customer_email = '';

			if ( isset( $_POST[ 'hb_email' ] ) ) {

				$customer_email = stripslashes( strip_tags( $_POST[ 'hb_email' ] ) );

			}

			

			$customer_id = $this->hbdb->get_customer_id( $customer_email );

			if ( $customer_id ) {

				$customer_id = $this->hbdb->update_customer_on_resa_creation( $customer_id, $customer_email, $customer_info );

			} else {

				$customer_id = $this->hbdb->create_customer( $customer_email, $customer_info );

			}

			

			if ( ! $customer_id ) {

				$response['success'] = false;

				$response['error_msg'] = 'Error (could not create customer).';

			} else {

				$customer_info['id'] = $customer_id;

				

				require_once dirname( plugin_dir_path( __FILE__ ) ) . '/utils/price-calc.php';

				$price_calc = new HbPriceCalc( $this->hbdb, $this->utils );

				$price = $price_calc->get_price( $_POST['hb-details-accom-id'], $_POST['hb-details-check-in'], $_POST['hb-details-check-out'], $_POST['hb-details-adults'], $_POST['hb-details-children'] );

				if ( ! $price['success'] ) {

					$response['success'] = false;

					$response['error_msg'] = 'Error (could not calculate price).';

				} else {

					$options = $this->hbdb->get_options_with_choices( $_POST['hb-details-accom-id'] );

                    $adults = intval( $_POST['hb-details-adults'] );

                    $children = intval( $_POST['hb-details-children'] );

                    $nb_nights = $this->utils->get_number_of_nights( $_POST['hb-details-check-in'], $_POST['hb-details-check-out'] );

                    $price_options = $this->utils->calculate_options_price( $adults, $children, $nb_nights, $options );

                    $options_total_price = 0;

                    $chosen_options = array();

					foreach ( $options as $option ) {

                        if ( $option['apply_to_type'] == 'quantity' || $option['apply_to_type'] == 'quantity-per-day' ) {

                            $quantity = intval( $_POST[ 'hb_option_' . $option['id'] ] );

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

                    

                    $price = $options_total_price + $price['value'];

                    

                    $fees = $this->hbdb->get_global_fees();

                    $fees_amount = 0;

                    foreach ( $fees as $fee ) {

                        if ( $fee['apply_to_type'] == 'global-percentage' ) {

                            $fees_amount += $fee['amount'] * $price / 100;

                        } else {

                            $fees_amount += $fee['amount'];

                        }

                    }

                    

                    $price += $fees_amount;

                    

					$deposit = 0;

			        if ( get_option( 'hb_deposit_type' ) == 'fixed' ) {

			            $deposit = get_option( 'hb_deposit_amount' );

			        } else if ( get_option( 'hb_deposit_type' ) == 'percentage' ) {

		                $deposit = $price * get_option( 'hb_deposit_amount' ) / 100;

			        }

					

					$currency_to_round = array( 'HUF', 'JPY', 'TWD' );

					if ( in_array( get_option( 'hb_currency' ), $currency_to_round ) || ( get_option( 'hb_price_precision' ) == 'no_decimals' ) ) {

                        $price = round( $price );

						$deposit = round( $deposit );

                    } else {

						$price = round( $price, 2 );

						$deposit = round( $deposit, 2 );

					}

					

					if ( $_POST['hb-payment-type'] == 'store_credit_card' && ( get_option( 'hb_resa_payment_store_credit_card' ) == 'yes' || get_option( 'hb_resa_payment' ) == 'store_credit_card' ) ) {

						$amount_to_pay = 0;

					} else if ( $_POST['hb-payment-type'] == 'deposit' && ( get_option( 'hb_resa_payment_deposit' ) == 'yes' || get_option( 'hb_resa_payment' ) == 'deposit' ) ) {

						$amount_to_pay = $deposit;

					} else {

						$amount_to_pay = $price;

					}

					

					$resa_info = array(

						'booking_form_num' => $_POST['hb-details-booking-form-num'],

						'accom_id' => $_POST['hb-details-accom-id'],

						'accom_num' => $accom_num,

						'check_in' => $_POST['hb-details-check-in'],

						'check_out' => $_POST['hb-details-check-out'],

						'adults' => $_POST['hb-details-adults'],

						'children' => $_POST['hb-details-children'],

						'price' => $price,

						'deposit' => $deposit,

						'paid' => 0,

						'currency' => get_option( 'hb_currency' ),

						'customer_id' => $customer_id,

						'additional_info' => $this->utils->get_posted_additional_booking_info(),

						'options' => $chosen_options,

                        'lang' => get_locale(),

						'payment_token' => '',

						'origin' => 'website',

					);

                    

					if ( $_POST['hb-payment-flag'] == 'yes' ) {

						$payment_gateway = $this->utils->get_payment_gateway( $_POST['hb-payment-gateway'] );

						if ( $payment_gateway ) {

							$resa_info['payment_gateway'] = $payment_gateway->name;

							$response = $payment_gateway->process_payment( $resa_info, $customer_info, $amount_to_pay );

						} else {

							$response['success'] = false;

							$response['error_msg'] = 'Error. Could not find payment gateway.';

						}

						if ( ! $response['success'] ) {

							echo( json_encode( $response ) );

							die;

						}

						if ( $payment_gateway->has_redirection == 'no' ) {

							if ( get_option( 'hb_resa_paid_has_confirmation' ) == 'no' ) {

					            $status = 'new';

	                        } else {

	                            $status = 'pending';

	                            $accom_num = 0;

	                        }

							$resa_info['paid'] = $amount_to_pay;

						} else {

							$status = 'waiting_payment';

							$resa_info['payment_token'] = $response['payment_token'];

							$resa_info['amount_to_pay'] = $amount_to_pay;

						}

					} else {

						$resa_info['payment_gateway'] = '';

                        if ( get_option( 'hb_resa_unpaid_has_confirmation' ) == 'no' ) {

				            $status = 'new';

                        } else {

                            $status = 'pending';

                            $accom_num = 0;

                        }

					}

                    

					$resa_info['accom_num'] = $accom_num;

					$resa_info['status'] = $status;

					

					$resa_id = $this->hbdb->create_resa( $resa_info );

					if ( ! $resa_id && ! $resa_info['paid'] ) {

						$response['success'] = false;

						$response['error_msg'] = 'Error (could not create reservation).';

					} else {

						if ( $status == 'waiting_payment' ) {

							$response['resa_id'] = $resa_id;					

						} else {

							if ( $status == 'new' ) {

								$this->hbdb->block_linked_accom( $resa_info['accom_id'], $resa_info['check_in'], $resa_info['check_out'], $resa_id );

							}

                            $this->utils->send_email( 'new_resa', $resa_id );

						}

					}

				}

			}

		}

		echo( json_encode( $response ) );

		die;

	}



	public function hb_contact_form_send_email() {

		$fields = $this->hbdb->get_form_fields( 'contact' );

		$vars = array();

		foreach ( $fields as $field ) {

			if ( $field['displayed'] == 'yes' ) {

				$vars[] = $field['id'];

			}

		}

		

		$header = array();

		$from = $this->utils->replace_fields_var_with_value( $vars, $_POST, get_option( 'hb_contact_from' ) );

		if ( substr_count( $from, '@' ) > 1 ) {

			$from = '';

		}

		if ( $from != '' ) {

			$header[] = 'From: ' . $from;

		}

		$subject = $this->utils->replace_fields_var_with_value( $vars, $_POST, get_option( 'hb_contact_subject' ) );

		$subject = str_replace( '@', '[at]', $subject );

		$message = $this->utils->replace_fields_var_with_value( $vars, $_POST, get_option( 'hb_contact_message' ) );

		if ( get_option( 'hb_contact_message_type' ) == 'html' ) {

			$header[] = 'Content-type: text/html';

			$message = nl2br( $message );

		}

        $admin_email = get_option( 'hb_contact_email' );

        if ( ! $admin_email ) {

            $admin_email = get_option( 'admin_email' );

        }

		try {

			if ( wp_mail( $admin_email, $subject, $message, $header ) ) {

				$response['success'] = true;

				$response['msg'] = $this->hbdb->get_string( 'contact_message_sent' );

			} else {

				global $phpmailer;

				$response['success'] = false;

				$response['error_msg'] = $this->hbdb->get_string( 'contact_message_not_sent' ) . ' ' . $phpmailer->ErrorInfo;

			}

		} catch( phpmailerException $e ) {

			$response['success'] = false;

			$response['error_msg'] = $this->hbdb->get_string( 'contact_message_not_sent' ) . ' ' . $e->getMessage();

		}

		echo( json_encode( $response ) );

		die;

	}

	

}

?>