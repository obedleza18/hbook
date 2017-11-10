<?php
class HbAdminPageMisc extends HbAdminPage {
	
	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$this->data = array(
			'hb_text' => array(
				'form_saved' => esc_html__( 'Settings have been saved.', 'hbook-admin' ),
				'date_not_valid' => esc_html__( 'The date is not valid (use a yyyy-mm-dd format).', 'hbook-admin' ),
			)
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	public function display() {
	?>

	<div class="wrap">

		<form id="hb-settings-form">
		
			<h1><?php esc_html_e( 'Miscellaneous', 'hbook-admin' ); ?></h1>

            <?php $this->display_right_menu(); ?>
            
			<?php
			foreach ( $this->options_utils->misc_settings as $section_id => $section ) {
                $this->options_utils->display_section_title( $section['label'] );
                foreach ( $section['options'] as $id => $option ) {
					if ( $id == 'hb_front_end_date_settings' ) {
                        $this->display_date_format_settings();
                    } else {
	                    $function_to_call = 'display_' . $option['type'] . '_option';
	                    $this->options_utils->$function_to_call( $id, $option );
					}
                }
				$this->options_utils->display_save_options_section();
            }
			?>
			
			<input type="hidden" name="action" value="hb_update_misc_settings" />
			<input id="hb-nonce" type="hidden" name="nonce" value="" />
			
			<?php wp_nonce_field( 'hb_nonce_update_db', 'hb_nonce_update_db' ); ?>
			
		</form>
		
	</div>

	<?php
	}
	
	private function display_date_format_settings() {
		$langs = $this->utils->get_langs();
		$saved_settings = json_decode( get_option( 'hb_front_end_date_settings' ), true );
		require_once dirname( dirname( dirname( plugin_dir_path( __FILE__ ) ) ) ) . '/utils/date-localization.php';
		$date_locale_info = new HbDateLocalization();
		$days = $date_locale_info->locale[ $this->utils->get_hb_known_locale() ]['day_names'];
		
		foreach ( $langs as $locale => $lang_name ) {
			$hb_known_locale = $this->utils->get_hb_known_locale( $locale );
			$default_first_day = $date_locale_info->locale[ $hb_known_locale ]['first_day'];
			if ( isset( $saved_settings[ $locale ]['first_day'] ) ) {
				$current_first_day = $saved_settings[ $locale ]['first_day'];
			} else {
				$current_first_day = $default_first_day;
			}
			
			$default_format = $date_locale_info->locale[ $hb_known_locale ]['date_format'];
			if ( isset( $saved_settings[ $locale ]['date_format'] ) ) {
				$current_format = $saved_settings[ $locale ]['date_format'];
			} else {
				$current_format = $default_format;
			}
			$days_select_options = '';
			foreach ( $days as $i => $day ) {
				if ( $i == $current_first_day ) {
					$selected = ' selected';
				} else {
					$selected = '';
				}
				$days_select_options .= '<option value="' . $i . '"' . $selected . '>' . $day . '</option>';
			}
			$formats = array( 
				'mm/dd/yyyy',
				'dd/mm/yyyy',
				'dd.mm.yyyy',
				'dd-mm-yyyy',
				'yyyy/mm/dd',
				'dd-mm-yyyy',
				'dd.mm.yyyy',
				'yyyy-mm-dd',
			);
			$format_select_options = '';
			foreach ( $formats as $format ) {
				if ( $format == $current_format ) {
					$selected = ' selected';
				} else {
					$selected = '';
				}
				$format_select_options .= '<option' . $selected . '>' . $format . '</option>';
			}
			
			if ( sizeof( $langs ) > 1 ) {
			?>
				<h4><u><?php echo( esc_html( $lang_name ) ); ?></u> <small>(<?php echo( esc_html( $locale ) ); ?>)</small></h4>
				<small>
				<?php 
				printf( 
					esc_html__( 'Usual setting: first day is %s and date format is %s', 'hbook-admin' ), 
					'<b>' . esc_html( $days[ $default_first_day ] ) . '</b>',
					'<b>' . esc_html( $default_format ) . '</b>'
				);
				?>
				</small>
			<?php
			}
			?>
			
			<div class="hb-lang-settings" data-locale="<?php echo( esc_attr( $locale ) ); ?>">
			   
				<p>
					<label><?php esc_html_e( 'First day of the week', 'hbook-admin' ); ?></label><br/>
					<select class="hb-first-day">
						<?php 
						echo( wp_kses( 
							$days_select_options, 
							array( 'option' => array( 'value' => array(), 'selected' => array() ) ) 
						) );
						?>
					</select>
				</p>

				<p>
					<label><?php esc_html_e( 'Date format', 'hbook-admin' ); ?></label><br/>
					<select class="hb-date-format">
						<?php 
						echo( wp_kses( 
							$format_select_options, 
							array( 'option' => array( 'value' => array(), 'selected' => array() ) ) 
						) );
						?>
					</select>
				</p>
			
			</div>
			
			<?php
		}
		?>
		
		<input type="hidden" id="hb_front_end_date_settings" name="hb_front_end_date_settings" value="" />
		<p style="line-height: 0.7">&nbsp;</p>
		
		<?php
	}
	
}