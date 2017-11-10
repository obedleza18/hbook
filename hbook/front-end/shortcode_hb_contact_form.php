<?php
class HbContactForm {
	
	private $plugin_version;
	private $hbdb;
	private $hb_strings;
	private $form_fields;
	
	public function __construct( $plugin_version, $hbdb, $hb_strings, $form_fields ) {
		$this->hbdb = $hbdb;
		$this->hb_strings = $hb_strings;
		$this->form_fields = $form_fields;
		wp_enqueue_script( 'hb-validate-form', plugin_dir_url( __FILE__ ) . 'js/jquery.form-validator.min.js', array( 'jquery' ), $plugin_version, true );
		wp_enqueue_script( 'hb-contact-form', plugin_dir_url( __FILE__ ) . 'js/contact-form.js', array( 'jquery' ), $plugin_version, true );
		$hb_text = array();
		$strings_to_front = array( 'invalid_email', 'required_field', 'invalid_number', 'connection_error', 'contact_already_sent' );
		foreach ( $strings_to_front as $string_id ) {
			$hb_text[ $string_id ] = $hb_strings[ $string_id ];
		}
        
        $page_padding_top = intval( get_option( 'hb_page_padding_top' ) );
        if ( ! $page_padding_top ) {
            $page_padding_top = '0';
        }

        $ajax_timeout = intval( get_option( 'hb_ajax_timeout' ) );
        if ( ! $ajax_timeout ) {
            $ajax_timeout = 20000;
        }
        
		wp_localize_script( 'hb-contact-form', 'hb_contact_text', $hb_text );
		wp_localize_script( 'hb-contact-form', 'hb_contact_form_data', array( 
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'ajax_timeout' => $ajax_timeout,
            'page_padding_top' => $page_padding_top,
        ) );
	}
	
	public function hb_contact_form() {
		$fields = $this->hbdb->get_form_fields( 'contact' );
		$output = '';
		$nb_columns = 0;
		$current_columns_wrapper = 0;
		$column_num = 0;
		foreach ( $fields as $field ) {
			// add a filter before
			if ( $field['displayed'] == 'yes' ) {
				if ( $field['column_width'] == 'half' ) {
					$nb_columns = 2;
				} else if ( $field['column_width'] == 'third' ) {
					$nb_columns = 3;
				} else {
					$nb_columns = 0;
				}
				if ( $nb_columns ) {
					if ( $column_num && ( $current_columns_wrapper != $nb_columns ) ) {
						$column_num = 0;
						$current_columns_wrapper = 0;
						$output .= '</div><!-- end .htw-contact-form-clearfix -->';
					}
					if ( ! $column_num ) {
						$column_num = 1;
						$current_columns_wrapper = $nb_columns;
						$output .= '<div class="htw-contact-form-clearfix">';
					} else {
						$column_num++;
					}
				} else if ( $column_num != 0 ) {
					$column_num = 0;
					$nb_columns = 0;
					$current_columns_wrapper = 0;
					$output .= '</div><!-- end .htw-contact-form-clearfix -->';
				}

				$output .= $this->form_fields->get_field_mark_up( $field );
				
				if ( $current_columns_wrapper && ( $current_columns_wrapper == $column_num ) ) {
					$column_num = 0;
					$nb_columns = 0;
					$current_columns_wrapper = 0;
					$output .= '</div><!-- end .htw-contact-form-clearfix -->';
				}
			}
			// add a filter after
		}
		if ( $current_columns_wrapper ) {
			$output .= '</div><!-- end .htw-contact-form-clearfix -->';
		}
		$output .= '<p class="hb-contact-submit hb-clearfix"><input type="submit" value="' . $this->hb_strings['contact_send_button'] . '" /><span class="hb-processing-contact-form"></span></p>';
		$output .= '<p class="hb-contact-form-error"></p>';
		$output .= '<p class="hb-contact-msg-send"></p>';
		$output .= '<input type="hidden" name="action" value="hb_contact_form_send_email" />';
		return '<form class="hbook-wrapper hb-contact-form">' . $output . '</form>';
	}
	
}