<?php



/**

 * Plugin Name:       HBook

 * Plugin URI:        http://hotelwp.com/booking-plugins/hbook/

 * Description:       Bookings made easy for hospitality businesses.

 * Version:           1.7.5

 * Author:            HotelWP

 * Author URI:        http://hotelwp.com

 */



if ( ! defined( 'WPINC' ) ) {

	die;

}



class HBook {



    public $utils;

    

	private $version;

	private $hbdb;

	private $accommodation;

	private $options_utils;

	private $plugin_check;

	private $plugin_id;

	private $admin_ajax_actions;

	private $front_end_ajax_actions;

	private $resa_ical;

    private $stripe;

	

	public function __construct() {

		

		$this->version = '1.7.5';

		

		load_plugin_textdomain(	'hbook-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		

		require_once plugin_dir_path( __FILE__ ) . 'database-actions/database-actions.php';

		$this->hbdb = new HbDataBaseActions();

		

		require_once plugin_dir_path( __FILE__ ) . 'accom-post-type/accom-post-type.php';

		$this->accommodation = new HbAccommodation( $this->hbdb );

		

		require_once plugin_dir_path( __FILE__ ) . 'utils/utils.php';

		$this->utils = new HbUtils( $this->hbdb, $this->version );



		require_once plugin_dir_path( __FILE__ ) . 'payment/payment-gateway.php';

		require_once plugin_dir_path( __FILE__ ) . 'payment/paypal/paypal.php';

		require_once plugin_dir_path( __FILE__ ) . 'payment/stripe/stripe.php';

		new HbPayPal( $this->hbdb, $this->version, $this->utils );

		$this->stripe = new HbStripe( $this->hbdb, $this->version );

				

		require_once plugin_dir_path( __FILE__ ) . 'utils/options-utils.php';

		$this->options_utils = new HbOptionsUtils( $this->utils );

		

		require_once plugin_dir_path( __FILE__ ) . 'utils/resa-ical.php';

		$this->resa_ical = new HbResaIcal( $this->hbdb, $this->utils );

		

		require_once plugin_dir_path( __FILE__ ) . 'utils/plugin-check.php';

		require_once plugin_dir_path( __FILE__ ) . 'utils/plugin-id.php';

		$this->plugin_id = new HbPluginId();

		$plugin_check = new HbPluginCheck( $this->version, $this->plugin_id->get_id() );

		

		require_once plugin_dir_path( __FILE__ ) . 'admin-pages/admin-ajax-actions.php';

		$this->admin_ajax_actions = new HbAdminAjaxActions( $this->hbdb, $this->utils, $this->options_utils, $this->stripe );

		

		require_once plugin_dir_path( __FILE__ ) . 'front-end/front-end-ajax-actions.php';

		$this->front_end_ajax_actions = new HbFrontEndAjaxActions( $this->hbdb, $this->utils );

		

		register_activation_hook( __FILE__, array( $this, 'plugin_activated' ) );

		

		register_deactivation_hook(__FILE__, array( $this, 'plugin_deactivated' ) );

		

		add_action( 'init', array( $this, 'install_plugin' ) );

		add_action( 'init', array( $this, 'init_plugin' ) );

		add_action( 'init', array( $this->hbdb, 'delete_uncompleted_resa' ) );

		add_action( 'init', array( $this->utils, 'export_lang_file' ) );

		add_action( 'init', array( $this->utils, 'export_resa' ) );

		add_action( 'init', array( $this->utils, 'export_customers' ) );

		

		add_filter( 'template_include', array( $this->accommodation, 'filter_template_page' ) );

		add_action( 'wp_head', array( $this->utils, 'frontend_basic_css' ) );

		add_action( 'wp_head', array( $this->utils, 'frontend_calendar_css' ) );

		add_action( 'wp_head', array( $this->utils, 'frontend_buttons_css' ) );

		add_action( 'wp_head', array( $this->utils, 'frontend_inputs_selects_css' ) );

		add_action( 'wp_head', array( $this->utils, 'frontend_custom_css' ) );

		add_action( 'admin_menu', array( $this, 'create_plugin_admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_wp_admin_style' ) );

		add_action( 'hb_ical_synchronized', array( $this, 'ical_update_calendars' ) );

		add_action( 'hb_check_plugin', array( $this->utils, 'check_plugin' ) );

		

		$hb_shortcodes = array( 'hb_booking_form', 'hb_accommodation_list', 'hb_availability', 'hb_rates', 'hb_contact_form', 'hb_paypal_confirmation' );

		foreach ( $hb_shortcodes as $shortcode ) {

			add_shortcode( $shortcode, array( $this, 'hb_shortcodes' ) );

		}

		

		$front_end_ajax_action = array(

			'hb_get_available_accom',

			'hb_save_details',

			'hb_contact_form_send_email',

		);

		foreach( $front_end_ajax_action as $action ) {

			add_action( 'wp_ajax_' . $action, array( $this->front_end_ajax_actions, $action ) );

			add_action( 'wp_ajax_nopriv_' . $action, array( $this->front_end_ajax_actions, $action ) );

		}

		

		$admin_ajax_action = array( 

			'hb_update_db', 

			'hb_update_misc_settings',

            'hb_update_appearance_settings',

            'hb_update_payment_settings',

			'hb_update_forms',

			'hb_update_contact',

			'hb_update_strings', 

			'hb_update_rates', 

			'hb_change_resa_status',

            'hb_confirm_resa',

			'hb_update_resa_info',

			'hb_update_resa_comment',

            'hb_edit_accom_get_avai',

            'hb_update_resa_accom',

			'hb_create_resa_new_customer',

			'hb_update_customer',

			'hb_update_resa_paid',

			'hb_resa_check_price',

			'hb_create_resa',

			'hb_delete_resa',

			'hb_delete_customer',

			'hb_add_blocked_accom',

			'hb_delete_blocked_accom',

			'hb_update_booking_rules',

			'hb_delete_sync_errors',

			'hb_resa_charging',

			'hb_update_resa_dates',

		);

		foreach ( $admin_ajax_action as $action ) {

			add_action( 'wp_ajax_' . $action, array( $this->admin_ajax_actions, $action ) );

		}

		

		add_action( 'init', array( $this->accommodation, 'create_accommodation_post_type' ) );

		add_action( 'pre_get_posts', array( $this->accommodation, 'admin_accom_order' ) );

		add_action( 'edit_form_before_permalink', array( $this->accommodation, 'display_accom_id' ) );

		add_action( 'add_meta_boxes', array( $this->accommodation, 'accommodation_meta_box' ) );

		add_action( 'save_post_hb_accommodation', array( $this->accommodation, 'save_accommodation_meta' ) );

		add_action( 'delete_post', array( $this->hbdb, 'deleted_accom' ) );

		add_action( 'publish_hb_accommodation', array( $this->hbdb, 'published_accom' ) );

	}

	

    

	public function install_plugin() {

		$installed_version = get_option( 'hbook_installing_version' );

		if ( ! $installed_version ) {

			$installed_version = get_option( 'hbook_version' );

		}

		if ( ! $installed_version || $installed_version != $this->version ) {

			if ( get_option( 'hbook_version' ) ) {

				update_option( 'hbook_previous_version', get_option( 'hbook_version' ) );

			} else {

				update_option( 'hbook_previous_version', 'none' );

			}

			$installing = get_option( 'hbook_installing' );

			if ( $installing && $installing != 1 ) {

				$install_start_time = strtotime( substr( $installing, 0, 19 ) );

				$elapsed_time = time() - $install_start_time;

				if ( $elapsed_time < 300 ) {

					return;

				}

			}

			update_option( 'hbook_installing', current_time( 'mysql', 1 ) . '-' . get_option( 'hbook_version' ) . '-' . $this->version );

		} else {

			return;

		}

		

		require_once plugin_dir_path( __FILE__ ) . 'database-actions/database-creation.php';

		require_once plugin_dir_path( __FILE__ ) . 'database-actions/database-schema.php';

		$hbdb_schema = new HbDataBaseSchema( $this->hbdb );

		$hbdb_creation = new HbDataBaseCreation( $this->hbdb, $this->utils, $hbdb_schema );

		

		if ( $installed_version && $installed_version != $this->version ) {

			$hbdb_creation->alter_data_before_table_update( $installed_version );

		}

		if ( ! $installed_version || $installed_version != $this->version ) {

			$hbdb_creation->create_update_plugin_tables();

			$hbdb_creation->insert_strings( $installed_version );

		}

        if ( ! $installed_version ) {

            $hbdb_creation->insert_data();

			add_action( 'admin_notices', array( $this, 'hbook_activation_notice' ) );

        } else if ( $installed_version != $this->version ) {

			$hbdb_creation->alter_data( $installed_version );

			if ( ( get_option( 'hb_paypal_active' ) == 'yes' ) && ( version_compare( '1.7', $installed_version ) > 0 ) ) {

				add_action( 'admin_notices', array( $this, 'hbook_update_1_7_notice' ) );

			}

        }

        if ( ! $installed_version || $installed_version != $this->version ) {

            $this->options_utils->init_options();

            update_option( 'hbook_version', $this->version );

        }

		delete_option( 'hbook_installing' );

		delete_option( 'hbook_installing_version' );

	}

    

	public function plugin_activated() {

		update_option( 'hb_flush_rewrite', 'flush' );

		wp_schedule_event( time(), 'hourly', 'hb_ical_synchronized' );

		wp_schedule_event( time(), 'daily', 'hb_check_plugin' );

		add_role( 'hb_resa_reader', esc_html__( 'Reservation reader', 'hbook-admin' ), array( 'read_resa' => true, 'read' => true ) );

		add_role( 'hb_resa_manager', esc_html__( 'Reservation manager', 'hbook-admin' ), array( 'manage_resa' => true, 'read_resa' => true, 'read' => true ) );

	}

	

	public function plugin_deactivated() {

		wp_clear_scheduled_hook( 'hb_ical_synchronized' );

		wp_clear_scheduled_hook( 'hb_check_plugin' );

		remove_role( 'hb_resa_reader' );

		remove_role( 'hb_resa_manager' );

	}

    

	public function ical_update_calendars() {

		$this->resa_ical->update_calendars();

	}

	

	public function hbook_activation_notice() {

    ?>

		<div class="updated">

			<p>

				<?php

				$thanks_msg = esc_html__( 'Thanks for using HBook plugin.', 'hbook-admin' );

				if ( strpos( $thanks_msg, 'HBook' ) ) {

					$thanks_msg = str_replace( 'HBook', '<b>HBook</b>', $thanks_msg );

				} else {

					$thanks_msg = 'Thanks for using <b>HBook</b> plugin.';

				}

				

				$doc_msg = esc_html__( 'Before setting the plugin up do not forget to have a look at the %s.', 'hbook-admin' );

				$doc_word = esc_html__( 'documentation', 'hbook-admin' );

				if ( strpos( $doc_msg, '%s' ) ) {

					$doc_msg = str_replace( '%s', '<a target="_blank" href="https://hotelwp.com/documentation/hbook/">'  . $doc_word . '</a>', $doc_msg );

				} else {

					$doc_msg .= ' (<a target="_blank" href="https://hotelwp.com/documentation/hbook/">'  . $doc_word . '</a>)';

				}

				$knowledge_msg = esc_html__( 'For any specific issue you can consult our %s.', 'hbook-admin' );

				$knowledge_word = 'knowledgebase';

				if ( strpos( $knowledge_msg, '%s' ) ) {

					$knowledge_msg = str_replace( '%s', '<a target="_blank" href="https://hotelwp.com/knowledgebase/">'  . $knowledge_word . '</a>', $knowledge_msg );

				} else {

					$knowledge_msg .= ' (<a target="_blank" href="https://hotelwp.com/knowledgebase/">'  . $knowledge_word . '</a>)';

				}

				?>

				<?php  echo( $thanks_msg ); ?>

				<br/>

				<?php  echo( $doc_msg ); ?>

				<br/>

				<?php  echo( $knowledge_msg ); ?>

			</p>

		</div>

	<?php

	}

	

	public function hbook_update_1_7_notice() {

	?>

		<div class="updated">

			<p><?php esc_html_e( 'Please note that since the 1.7 version of HBook the customers paying via PayPal are redirected to the page where they filled the booking form instead of the PayPal confirmation page.', 'hbook-admin' ); ?></p>

		</div>

	<?php

	}

	

	public function init_plugin() {

		if ( 

			isset( $_POST['hb-purchase-code'] ) && 

			wp_verify_nonce( $_POST['hb_nonce_licence'], 'hb_nonce_licence' ) && 

			current_user_can( 'manage_options' )

		) {

			$this->utils->verify_purchase_code( wp_strip_all_tags( trim( $_POST['hb-purchase-code'] ) ) );

		}

		add_filter( 'widget_text', 'do_shortcode' );

		add_feed( 'hbook-calendar.ics', array( $this->resa_ical, 'export_ical' ) );

	}



	public function enqueue_wp_admin_style() {

        wp_enqueue_script( 'hb-global-js', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-global.js', array( 'jquery' ), $this->version );

        

		global $post_type;

		if ( $post_type == 'hb_accommodation' ) {

			wp_enqueue_style( 'hb-accom-style', plugin_dir_url( __FILE__ ) . 'accom-post-type/accom-post-type.css', array(), $this->version );

			wp_enqueue_script( 'hb-accom-script', plugin_dir_url( __FILE__ ) . 'accom-post-type/accom-post-type.js', array( 'jquery' ), $this->version );

			$hb_accom_post_text = array(

                'delete_accom_num_name' => esc_html__( 'Delete', 'hbook-admin' ),

                'delete_accom_num_name_text' => esc_html__( 'Delete accommodation %s? Note that all reservations linked to the %s will also be deleted.', 'hbook-admin' ),

                'delete_accom_text' => esc_html__( 'Move this accommodation type to trash? Note that all reservations linked to this accommodtion will also be deleted when you will empty the trash.', 'hbook-admin' ),

				'starting_price_not_number' => esc_html__( 'Starting price should be a number (without currency symbol).', 'hbook-admin' ),

				'accom_number_zero' => esc_html__( 'There must be at least one accommodation.', 'hbook-admin' ),

			);

			wp_localize_script( 'hb-accom-script', 'hb_accom_post_text', $hb_accom_post_text );

		}

	}

	

	public function enqueue_scripts( $hook ) {

		wp_enqueue_style( 'hb-admin-pages-style', plugin_dir_url( __FILE__ ) . 'admin-pages/css/hb-admin-pages-style.css', array(), $this->version );

        wp_enqueue_script( 'hb-settings', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-settings.js', array( 'jquery' ), $this->version );

        

		$page_name = str_replace( 'hb_', '', $_GET['page'] );

        

        $knockout_pages = array(

            'contact',

            'customers',

            'emails',

            'fees',

            'forms',

            'options',

            'rates',

            'reservations',

            'rules',

            'seasons',

        );

        if ( in_array( $page_name, $knockout_pages ) ) {

            wp_enqueue_script( 'hb-knockout', plugin_dir_url( __FILE__ ) . 'admin-pages/js/knockout-3.2.0.js', array( 'jquery' ), $this->version );    

            wp_enqueue_script( 'hb-settings-knockout', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-settings-knockout.js', array( 'jquery' ), $this->version );

        }

        

        $static_settings_pages = array(

            'appearance',

            'misc',

            'payment',

        );

        if ( in_array( $page_name, $static_settings_pages ) ) {

            wp_enqueue_script( 'hb-settings-static', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-settings-static.js', array( 'jquery' ), $this->version );

        }

        

		if ( $page_name == 'fees' || $page_name == 'options' || $page_name == 'rates' ) {

			wp_enqueue_script( 'hb-options-and-fees-script', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-options-and-fees.js', array( 'jquery' ), $this->version, true );

        }

        

        if ( $page_name == 'fees' || $page_name == 'options' || $page_name == 'rates' || $page_name == 'reservations' ) {

			wp_enqueue_script( 'hb-price-utils', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-price-utils.js', array( 'jquery' ), $this->version, true );

            add_action( 'admin_footer', array( $this->utils, 'currency_symbol_js' ) );

            wp_localize_script( 'hb-price-utils', 'hb_currency_pos', get_option( 'hb_currency_position' ) );

            wp_localize_script( 'hb-price-utils', 'hb_currency', get_option( 'hb_currency' ) );

		}

        

		if ( ( $page_name == 'seasons' ) || ( $page_name == 'reservations' ) ) {

			$this->utils->load_datepicker();

		}

        

		if ( $page_name == 'reservations' ) {

			wp_enqueue_script( 'jquery-ui-resizable' );

			wp_enqueue_script( 'hb-resa-utils', plugin_dir_url( __FILE__ ) . 'admin-pages/pages/reservations/resa-utils.js', array( 'jquery' ), $this->version );

			wp_enqueue_script( 'hb-resa-cal', plugin_dir_url( __FILE__ ) . 'admin-pages/pages/reservations/resa-cal.js', array( 'jquery' ), $this->version );

			wp_enqueue_script( 'hb-resa-export', plugin_dir_url( __FILE__ ) . 'admin-pages/pages/reservations/resa-export.js', array( 'jquery' ), $this->version );

			wp_enqueue_style( 'hb-resa-cal-style', plugin_dir_url( __FILE__ ) . 'admin-pages/pages/reservations/resa-cal.css', array(), $this->version );

		}

        

		if ( $page_name == 'forms' || $page_name == 'contact' ) {

			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_script( 'hb-knockout-sortable', plugin_dir_url( __FILE__ ) . 'admin-pages/js/knockout-sortable.min.js', array( 'jquery' ), $this->version );

			wp_enqueue_script( 'hb-form-builder', plugin_dir_url( __FILE__ ) . 'admin-pages/js/hb-forms-and-contact.js', array( 'jquery' ), $this->version );

			wp_enqueue_style( 'hb-form-builder-style', plugin_dir_url( __FILE__ ) . 'admin-pages/css/hb-form-builder.css', array(), $this->version );

		}

        

		if ( $page_name == 'appearance' ) {

			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_script( 'wp-color-picker' );

		}

		

		wp_enqueue_style( 'hb-' . $page_name . '-style', plugin_dir_url( __FILE__ ) . 'admin-pages/pages/' . $page_name . '/' . $page_name . '.css', array(), $this->version );

        wp_enqueue_script( 'hb-' . $page_name . '-script', plugin_dir_url( __FILE__ ) . 'admin-pages/pages/' . $page_name . '/' . $page_name . '.js', array( 'jquery' ), $this->version, true );

        

        add_action( 'admin_head', array( $this->utils, 'admin_custom_css' ) );

    }

	

	public function create_plugin_admin_menu() {

		if ( get_option( 'hb_valid_purchase_code' ) == 'yes' || strpos( site_url(), '127.0.0.1' ) || strpos( site_url(), 'localhost' ) ) {

	        if ( current_user_can( 'read_resa' ) ) {

				$page = add_menu_page( esc_html__( 'Reservations', 'hbook-admin' ), esc_html__( 'Reservations', 'hbook-admin' ), 'read_resa', 'hb_reservations', array( $this, 'display_admin_page' ), 'dashicons-calendar-alt', '2.82' );

			} else {

				$page = add_menu_page( esc_html__( 'Reservations', 'hbook-admin' ), esc_html__( 'Reservations', 'hbook-admin' ), 'manage_options', 'hb_reservations', array( $this, 'display_admin_page' ), 'dashicons-calendar-alt', '2.82' );

			}

			add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_scripts' ) );

		}

			        

	    $page = add_menu_page( 'HBook', 'HBook', 'manage_options', 'hb_menu', array( $this, 'display_admin_page' ), '', '122.3' );

		add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_scripts' ) );

          

        $hbook_pages = $this->utils->get_hbook_pages();

        foreach ( $hbook_pages as $p ) {

			$page = add_submenu_page( 'hb_menu', $p['name'], $p['name'], 'manage_options', $p['id'], array( $this, 'display_admin_page' ) );

			add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_scripts' ) );

            if ( $p['id'] == 'hb_accommodation' ) {

                add_action( 'load-' . $page, array( $this->accommodation, 'redirect_hb_menu_accom_page' ) );

            }

		}

        

        if ( $this->utils->is_htw_theme_active() ) {

			$page = add_menu_page( esc_html__( 'Contact form', 'hbook-admin' ), esc_html__( 'Contact form', 'hbook-admin' ), 'manage_options', 'hb_contact', array( $this, 'display_admin_page' ), 'dashicons-email-alt', '122.4' );

			add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_scripts' ) );

		}

	}

	

	public function display_admin_page() {

		$page_id = $_GET['page'];

		$page_id = str_replace( 'hb_', '', $page_id );

		if ( current_user_can( 'manage_options' ) || ( $page_id == 'reservations' && current_user_can( 'read_resa' ) ) ) {

			require_once plugin_dir_path( __FILE__ ) . 'admin-pages/admin-page.php';

			require_once plugin_dir_path( __FILE__ ) . 'admin-pages/pages/' . $page_id . '/' . $page_id . '.php';

			$page_class = 'HbAdminPage' . ucfirst( $page_id );

			$admin_page = new $page_class( $page_id, $this->hbdb, $this->utils, $this->options_utils );

			$admin_page->display();

		}

	}

	

	public function hb_shortcodes( $atts, $content = '', $shortcode_name ) {

        if ( is_admin() || $shortcode_name == 'hb_paypal_confirmation' ) {

            return '';

        }

        

		require_once plugin_dir_path( __FILE__ ) . 'front-end/shortcode_' . $shortcode_name . '.php';

        

		switch ( $shortcode_name ) {

			

			case 'hb_availability' :

                wp_enqueue_script( 'hb-front-end-utils-script', plugin_dir_url( __FILE__ ) . 'front-end/js/utils.js', array( 'jquery' ), $this->version, true );

				return hb_availability( $atts, $this->version, $this->hbdb, $this->utils );



			case 'hb_rates' :

                wp_enqueue_script( 'hb-front-end-utils-script', plugin_dir_url( __FILE__ ) . 'front-end/js/utils.js', array( 'jquery' ), $this->version, true );

				return hb_rates( $atts, $this->version, $this->hbdb, $this->utils );



			case 'hb_accommodation_list' :

				return hb_accommodation_list( $atts, $this->version, $this->hbdb, $this->utils );

				

			case 'hb_booking_form' :

                wp_enqueue_script( 'hb-front-end-utils-script', plugin_dir_url( __FILE__ ) . 'front-end/js/utils.js', array( 'jquery' ), $this->version, true );

				require_once plugin_dir_path( __FILE__ ) . 'front-end/booking-form/search-form.php';

				require_once plugin_dir_path( __FILE__ ) . 'front-end/form-fields.php';

                require_once plugin_dir_path( __FILE__ ) . 'utils/resa-fees.php';

                require_once plugin_dir_path( __FILE__ ) . 'front-end/booking-form/details-form.php';

				$hb_strings = $this->hbdb->get_strings();

				$search_form = new HbSearchForm( $this->hbdb, $this->utils, $hb_strings );

				$form_fields = new HbFormFields( $hb_strings );

                $global_fees = new HbResaFees( $this->hbdb, $this->utils );

				$details_form = new HbDetailsForm( $this->hbdb, $this->utils, $hb_strings, $form_fields, $global_fees );

				$booking_form = new HbBookingForm( $this->version, $this->hbdb, $this->utils, $search_form, $details_form, $hb_strings );

				return $booking_form->hb_booking_form( $atts );



			case 'hb_contact_form' :

				$hb_strings = $this->hbdb->get_strings();

				require_once plugin_dir_path( __FILE__ ) . 'front-end/form-fields.php';

				$form_fields = new HbFormFields( $hb_strings );

				$contact_form = new HbContactForm( $this->version, $this->hbdb, $hb_strings, $form_fields );

				return $contact_form->hb_contact_form();

                

		}

	}

}



function hbook_is_active() {

}



$hbook = new HBook();

