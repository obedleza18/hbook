<?php
class HbAdminPageForms extends HbAdminPage {

	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$this->form_name = 'booking';
		$this->data = array(
			'hb_text' => array(
				'form_saved' => esc_html__( 'Forms settings have been saved.', 'hbook-admin' ),
				'new_field' => esc_html__( 'New field', 'hbook-admin' ),
				'confirm_delete_field' => esc_html__( 'Remove \'%field_name\'?', 'hbook-admin' ),
				'confirm_delete_field_no_name' => esc_html__( 'Remove field?', 'hbook-admin' ),
				'confirm_info' => esc_html__( '(This will also erase all data associated to this field.)', 'hbook-admin' ),
				'new_choice' => esc_html__( 'New choice', 'hbook-admin' ),
				'confirm_delete_choice' => esc_html__( 'Remove \'%choice_name\'?', 'hbook-admin' ),
			),
			'hb_form_name' => $this->form_name,
			'hb_fields' => $hbdb->get_form_fields()
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	public function display() {
	?>

	<div class="wrap">

		<div id="hb-admin-forms-options">
			
			<h1><?php esc_html_e( 'Forms', 'hbook-admin' ); ?></h1>
			<?php $this->display_right_menu(); ?>
			
			<hr/>
			
			<h3><?php esc_html_e( 'Search form', 'hbook-admin' ); ?></h3>
				
			<?php
			foreach ( $this->options_utils->search_form_options['search_form_options']['options'] as $id => $option ) {
				$function_to_call = 'display_' . $option['type'] . '_option';
				$this->options_utils->$function_to_call( $id, $option );
			}
			$this->options_utils->display_save_options_section();
			?>
				
			<hr/>
			
			<h3><?php esc_html_e( 'Accommodation selection', 'hbook-admin' ); ?></h3>
			
			<?php
			foreach ( $this->options_utils->accom_selection_options['accom_selection_options']['options'] as $id => $option ) {
				$function_to_call = 'display_' . $option['type'] . '_option';
				$this->options_utils->$function_to_call( $id, $option );
				if ( $id == 'hb_thumb_display' ) {
					echo( '<div class="hb-accom-thumb-options-wrapper">' );
				}
				if ( $id == 'hb_search_accom_thumb_height' ) {
					echo( '</div><!-- end .hb-accom-thumb-options-wrapper -->' );
				}
				if ( $id == 'hb_display_price' ) {
					echo( '<div class="hb-price-options-wrapper">' );
				}
				if ( $id == 'hb_display_detailed_accom_price' ) {
					echo( '</div><!-- end .hb-price-options-wrapper -->' );
				}
				if ( $id == 'hb_display_price_breakdown' ) {
					echo( '<div class="hb-price-breakdown-options-wrapper">' );
				}
				if ( $id == 'hb_display_detailed_accom_price' ) {
					echo( '</div><!-- end .hb-price-breakdown-options-wrapper -->' );
				}
			}
			$this->options_utils->display_save_options_section();
			?>
			
			<hr/>
			
			<h3><?php esc_html_e( 'Customer details form', 'hbook-admin' ); ?></h3>
			
			<p>
				<i>
					<?php esc_html_e( 'Customize the Customer details form.', 'hbook-admin' ); ?>
					<?php esc_html_e( 'Drag and drop fields to reorder them.', 'hbook-admin' ); ?>
				</i>
			</p>
			
			<?php $this->options_utils->display_save_options_section(); ?>
			
			<input id="hb-form-add-field-top" type="button" class="button" value="<?php esc_attr_e( 'Add a field', 'hbook-admin' ); ?>" data-bind="click: add_field_top" />
			
			<?php $this->display_form_builder(); ?>

			<p>
				<input id="hb-form-add-field-bottom" type="button" class="button" value="<?php esc_attr_e( 'Add a field', 'hbook-admin' ); ?>" data-bind="click: add_field_bottom" />
			</p>
			
			<?php $this->options_utils->display_save_options_section(); ?>
		
		</div>
		
	</div><!-- end .wrap -->

	<?php
	}
}