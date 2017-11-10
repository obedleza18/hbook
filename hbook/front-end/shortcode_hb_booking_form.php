<?php

class HbBookingForm {

	

	private $plugin_version;

	private $hbdb;

	private $utils;

	private $search_form;

	private $options_form;

	private $details_form;

	private $hb_strings;

	

	public function __construct( $plugin_version, $hbdb, $utils, $search_form, $details_form, $hb_strings ) {

		$this->plugin_version = $plugin_version;

		$this->hbdb = $hbdb;

		$this->utils = $utils;

		$this->search_form = $search_form;

		$this->details_form = $details_form;

		$this->hb_strings = $hb_strings;

	}



	private function load_scripts( $page_accom_id = 0 ) {

		static $script_loaded;

		static $accom_status_days_loaded = array();

		

		if ( ! $script_loaded ) {

			wp_enqueue_script( 'hb-validate-form', plugin_dir_url( __FILE__ ) . 'js/jquery.form-validator.min.js', array( 'jquery' ), $this->plugin_version, true );

			wp_enqueue_script( 'hb-front-end-script', plugin_dir_url( __FILE__ ) . 'js/booking-form.js', array( 'jquery' ), $this->plugin_version.'.6', true );

		}

		

		if ( $page_accom_id && ! isset( $accom_status_days_loaded[ $page_accom_id ] ) ) {

			$accom_status_days_loaded[ $page_accom_id ] = true;

			

			$minimum_stay = 1;

			$booking_rules = $this->hbdb->get_accom_booking_rules( $page_accom_id );

			foreach ( $booking_rules as $i => $rule ) {

				if ( $rule['all_seasons'] && $rule['type'] == 'minimum_stay' && $rule['minimum_stay'] > $minimum_stay ) {

					$minimum_stay = $rule['minimum_stay'];

				}

			}

			$status_days = $this->utils->get_status_days( $page_accom_id, $minimum_stay );

			wp_localize_script( 'hb-front-end-script', 'hb_status_days_' . $page_accom_id, $status_days );

		}

		

		if ( ! $script_loaded ) {

			foreach ( $this->utils->get_active_payment_gateways() as $gateway ) {

				foreach ( $gateway->js_scripts() as $js_script ) {

					wp_enqueue_script( $js_script['id'], $js_script['url'], array( 'jquery' ), $js_script['version'], true );

				}

				foreach ( $gateway->js_data() as $js_data_id => $js_data ) {

					wp_localize_script( 'hb-front-end-script', $js_data_id, strval( $js_data ) );

				}

			}

			

			$this->utils->load_datepicker();

			

			$seasons = $this->hbdb->get_all( 'seasons_dates' );

			

            $deposit_percentage = 0;

            if ( get_option( 'hb_deposit_type' ) == 'percentage' ) {

                $deposit_percentage = get_option( 'hb_deposit_amount' );

            }

            

            $page_padding_top = intval( get_option( 'hb_page_padding_top' ) );

            if ( ! $page_padding_top ) {

                $page_padding_top = '0';

            }

            

            $horizontal_form_min_width = intval( get_option( 'hb_horizontal_form_min_width' ) );

            if ( ! $horizontal_form_min_width ) {

                $horizontal_form_min_width = 500;

            }

            

			$details_form_stack_width = intval( get_option( 'hb_details_form_stack_width' ) );

            if ( ! $details_form_stack_width ) {

                $details_form_stack_width = 400;

            }

            

            $ajax_timeout = intval( get_option( 'hb_ajax_timeout' ) );

            if ( ! $ajax_timeout ) {

                $ajax_timeout = 20000;

            }

            

			global $wp_locale;

			$decimal_point = '.';

			$thousands_sep = '';

			if ( isset( $wp_locale->number_format['decimal_point'] ) ) {

				$decimal_point = $wp_locale->number_format['decimal_point'];

			}

			if ( isset( $wp_locale->number_format['thousands_sep'] ) ) {

				$thousands_sep = $wp_locale->number_format['thousands_sep'];

			}

			

			$booking_form_data = array(		

				'ajax_url' => admin_url( 'admin-ajax.php' ),

                'ajax_timeout' => $ajax_timeout,

				'seasons' => $seasons,

                'price_precision' => get_option( 'hb_price_precision' ),

				'decimal_point' => $decimal_point,

				'thousands_sep' => $thousands_sep,

                'deposit_percentage' => $deposit_percentage,

                'page_padding_top' => $page_padding_top,

                'horizontal_form_min_width' => $horizontal_form_min_width,

                'details_form_stack_width' => $details_form_stack_width,

			);

            

			wp_localize_script( 'hb-front-end-script', 'hb_booking_form_data', $booking_form_data );

			wp_localize_script( 'hb-front-end-script', 'hb_status_days_all', $this->utils->get_status_days( 'all' ) );

			wp_localize_script( 'hb-front-end-script', 'hb_text', $this->hb_strings );

			

			$script_loaded = true;

		}

	}



	public function hb_booking_form( $atts ) {

		$resa = array(

			'check_in' => '',

			'check_out' => '',

			'adults' => '',

			'children' => '',

			'search_accom_id' => '',

			'accom_id' => '',

			'options' => '',

		);

		

		static $booking_form_num = 0;

		$booking_form_num++;

		

		$status = '';

		if ( isset( $_GET['payment_confirm'] ) || isset( $_GET['payment_cancel'] ) ) {

			if ( ! isset( $_GET['payment_gateway'] ) ) {

				return 'Error: payment gateway is not defined.';

			}

			$payment_gateway = $this->utils->get_payment_gateway( $_GET['payment_gateway'] );

			$payment_token = $payment_gateway->get_payment_token();

			if ( ! $payment_token ) {

				return 'Error: no payment token.';

			}

			$resa = $this->hbdb->get_resa_by_payment_token( $payment_token );

			if ( $resa['booking_form_num'] == $booking_form_num ) {

				if ( isset( $_GET['payment_confirm'] ) ) {

					$payment_confirmation = $payment_gateway->confirm_payment();

					if ( $payment_confirmation['success'] ) {

						$customer_info = $this->hbdb->get_customer_info( $resa['customer_id'] );

		                $resa = array_merge( $customer_info, $resa );

						$this->load_scripts();

						return $this->resa_summary_external_payment( $resa );

					} else {

						$status = 'external-payment-confirm-error';

						wp_localize_script( 'hb-front-end-script', 'hb_payment_confirmation_error', $payment_confirmation['error_msg'] );

					}

				} else if ( isset( $_GET['payment_cancel'] ) ) {

					if ( $resa && $resa['status'] == 'waiting_payment' ) {

						$customer_info = $this->hbdb->get_customer_info( $resa['customer_id'] );

						if ( isset( $resa['additional_info'] ) ) {

							$additional_info = json_decode( $resa['additional_info'], true );

							if ( is_array( $additional_info ) ) {

								foreach ( $additional_info as $info_key => $info_value ) {

									$resa[ $info_key ] = $info_value;

								}

							}

						}

		                $resa = array_merge( $customer_info, $resa );

		                $status = 'external-payment-cancel';

		                $this->hbdb->delete_resa( $resa['id'] );

					} else {

						$status = 'external-payment-timeout';

					}

				}

			}

		}

		

		$atts = shortcode_atts(

			array(

				'form_id' => '',

				'all_accom' => '', // '', 'yes'

				'search_only' => 'no',

				'search_form_placeholder' => 'no',

				'accom_id' => '',

				'redirection_url' => '#',

			), 

			$atts, 

			'hb_booking_form' 

		);

		

		$page_accom_id = '';

		$post_id = $this->utils->get_default_lang_post_id( get_the_ID() );

		$all_accom = $this->hbdb->get_all_accom_ids();

		$all_linked_accom = $this->hbdb->get_all_linked_accom();

		if ( $atts['accom_id'] != '' ) {

			$page_accom_id = $atts['accom_id'];

		} else if ( $post_id && in_array( $post_id, $all_accom ) && ( $atts['all_accom'] != 'yes' ) ) {

			$page_accom_id = $post_id;

		} else if ( isset( $all_linked_accom[ $post_id ] ) && ( $atts['all_accom'] != 'yes' ) ) {

			$page_accom_id = $all_linked_accom[ $post_id ];

		} else if ( count( $all_accom ) == 1 ) {

			$page_accom_id = $all_accom[0];

		}

		

		$this->load_scripts( $page_accom_id );

		

		if ( ! $status && isset( $_POST['hb-check-in-hidden'] ) ) {

			$status = 'search-accom';

			$resa['check_in'] = strip_tags( $_POST['hb-check-in-hidden'] );

			$resa['check_out'] = strip_tags( $_POST['hb-check-out-hidden'] );

			$resa['adults'] = strip_tags( $_POST['hb-adults'] );

			$resa['children'] = strip_tags( $_POST['hb-children'] );

		}



		$results_show_only_accom_id = '';

		if ( isset( $_POST['hb-results-show-only-accom-id'] ) ) {

			$results_show_only_accom_id = $_POST['hb-results-show-only-accom-id'];

		} else if ( $status == 'external-payment-cancel' ) {

			$results_show_only_accom_id = $resa['accom_id'];

		}

		

		$class_page_accom = '';

		if ( $page_accom_id != '' ) {

			$class_page_accom = ' hb-accom-page';

		}

		

		if ( $atts['redirection_url'] != '#' ) {

			$exists_main_booking_form = 'yes';

		} else {

			$exists_main_booking_form = 'no';

		}

		

		if ( $atts['search_form_placeholder'] == 'yes' || get_option( 'hb_search_form_placeholder' ) == 'yes' ) {

			$search_form_placeholder = true;

		} else {

			$search_form_placeholder = false;

		}



		$allowed_check_in_days = 'all';

		$allowed_check_out_days = 'all';

		$minimum_stay = 1;

		$maximum_stay = 9999;

		$conditional_booking_rules = array();

		$seasonal_allowed_check_in_days = array();

		$seasonal_allowed_check_out_days = array();

		$seasonal_minimum_stay = array();

		$seasonal_maximum_stay = array();

		if ( $page_accom_id ) {

			$booking_rules = $this->hbdb->get_accom_booking_rules( $page_accom_id );

		} else {

			$booking_rules = $this->hbdb->get_all_accom_booking_rules();

		}

		foreach ( $booking_rules as $i => $rule ) {

			if ( $rule['type'] == 'check_in_days' ) {

				if ( $rule['all_seasons'] ) {

					$allowed_check_in_days = $rule['check_in_days'];

				} else {

					$rule_seasons = explode( ',', $rule['seasons'] );

					foreach ( $rule_seasons as $rule_season ) {

						$seasonal_allowed_check_in_days[ $rule_season ] = $rule['check_in_days'];

					}

				}

			} else if ( $rule['type'] == 'check_out_days' ) {

				if ( $rule['all_seasons'] ) {

					$allowed_check_out_days = $rule['check_out_days'];

				} else {

					$rule_seasons = explode( ',', $rule['seasons'] );

					foreach ( $rule_seasons as $rule_season ) {

						$seasonal_allowed_check_out_days[ $rule_season ] = $rule['check_out_days'];

					}

				}

			} else if ( $rule['type'] == 'minimum_stay' ) {

				if ( $rule['all_seasons'] ) {

					if ( $rule['minimum_stay'] > $minimum_stay ) {

						$minimum_stay = $rule['minimum_stay'];

					}

				} else {

					$rule_seasons = explode( ',', $rule['seasons'] );

					foreach ( $rule_seasons as $rule_season ) {

						$seasonal_minimum_stay[ $rule_season ] = $rule['minimum_stay'];

					}

				}

			} else if ( $rule['type'] == 'maximum_stay' ) {

				if ( $rule['all_seasons'] ) {

					if ( $rule['maximum_stay'] < $maximum_stay ) {

						$maximum_stay = $rule['maximum_stay'];

					}

				} else {

					$rule_seasons = explode( ',', $rule['seasons'] );

					foreach ( $rule_seasons as $rule_season ) {

						$seasonal_maximum_stay[ $rule_season ] = $rule['maximum_stay'];

					}

				}

			} else if ( $rule['type'] == 'conditional' && ( $rule['conditional_type'] == 'compulsory' || $rule['conditional_type'] == 'comp_and_rate' ) ) {

				$conditional_booking_rules[] = $rule;

			}

		}

		

		$form_booking_rules = array(

			'allowed_check_in_days' => $allowed_check_in_days,

			'allowed_check_out_days' => $allowed_check_out_days,

			'minimum_stay' => $minimum_stay,

			'maximum_stay' => $maximum_stay,

			'seasonal_allowed_check_in_days' => $seasonal_allowed_check_in_days,

			'seasonal_allowed_check_out_days' => $seasonal_allowed_check_out_days,

			'seasonal_minimum_stay' => $seasonal_minimum_stay,

			'seasonal_maximum_stay' => $seasonal_maximum_stay,

			'conditional_booking_rules' => $conditional_booking_rules,

		);

		

		$output = '

		<div id="hbook-booking-form-' . $booking_form_num . '"

			 class="hbook-wrapper hbook-wrapper-booking-form' . $class_page_accom . '"

			 data-status="' . $status . '" 

			 data-page-accom-id="' . $page_accom_id . '" 

			 data-exists-main-booking-form="' . $exists_main_booking_form . '"

			 data-results-show-only-accom-id="' . $results_show_only_accom_id . '" 

			 data-booking-rules=\'' . json_encode( $form_booking_rules ). '\'

		>';

	

		/* search form */

		$output .= $this->search_form->get_search_form_markup( $atts['form_id'], $atts['redirection_url'], $atts['search_only'], $search_form_placeholder, $resa['check_in'], $resa['check_out'], $resa['adults'], $resa['children'], $page_accom_id, $resa['options'] );

		if ( $atts['search_only'] == 'yes' ) {

			return $output . '</div><!-- .hbook-wrapper -->';

		}



		/* details form */

		$output .= $this->details_form->get_details_form_mark_up( $resa, $booking_form_num );

		

		$output .= '</div><!-- end .hbook-wrapper -->';

		

		return $output;

	}

	

	private function resa_summary_external_payment( $resa ) {

		$output = '

			<div id="hb-resa-confirm-done" class="hb-resa-summary-external-payment">

				<div>' . $this->hb_strings['thanks_message_payment_done_1'] . '</div>

				<p>' . str_replace( '%customer_email', '<span class="hb-resa-done-email">' . $resa['email'] . '</span>', $this->hb_strings['thanks_message_1'] ) . '</p>

				<div class="hb-resa-summary-content">

					<div>' . $this->hb_strings['chosen_check_in'] . ' <span class="hb-format-date">' . $resa['check_in'] . '</span></div>

					<div>' . $this->hb_strings['chosen_check_out'] . ' <span class="hb-format-date">' . $resa['check_out'] . '</span></div>

					<div>' . $this->hb_strings['number_of_nights'] . ' ' . $this->utils->get_number_of_nights( $resa['check_in'], $resa['check_out'] ) . '</div>';

		if ( get_option( 'hb_display_adults_field' ) == 'yes' ) {

			$output .= '

					<div>' . $this->hb_strings['chosen_adults'] . ' ' . $resa['adults'] . '</div>';

		}

		if ( get_option( 'hb_display_children_field' ) == 'yes' ) {

			$output .= '

					<div>' . $this->hb_strings['chosen_children'] . ' ' . $resa['children'] . '</div>';

		}

		if ( $this->utils->nb_accom() > 1 ) {

			$output .= '

					<div>' . $this->hb_strings['summary_accommodation'] . ' ' . $this->utils->get_accom_title( $resa['accom_id'] ) . '</div>';

		}

		$output .= '

					<div>' . $this->hb_strings['summary_price'] . ' ' .  $this->utils->price_with_symbol( $resa['price'] ) . '</div>

				</div>

				<p>' . $this->hb_strings['thanks_message_2'] . '</p>

				<div>' . $this->hb_strings['thanks_message_payment_done_2'] . '</div>

			</div><!-- end .hb-resa-summary -->';

		$output = apply_filters( 'hb_resa_summary_markup', $output );

		$output = apply_filters( 'hb_resa_summary_external_payment_markup', $output );

		return $output;

	}

	

}