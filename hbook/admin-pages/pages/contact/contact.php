<?php
class HbAdminPageContact extends HbAdminPage {
	
	private $options;
	
	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		
		$this->form_name = 'contact';
		$this->options = $options_utils->contact_form_options['contact_form_options']['options'];

		$this->data = array(
			'hb_text' => array(
				'form_saved' => esc_html__( 'Settings have been saved.', 'hbook-admin' ),
				'new_field' => esc_html__( 'New field', 'hbook-admin' ),
				'confirm_delete_field' => esc_html__( 'Remove \'%field_name\'?', 'hbook-admin' ),
				'confirm_delete_field_no_name' => esc_html__( 'Remove field?', 'hbook-admin' ),
				'new_choice' => esc_html__( 'New choice', 'hbook-admin' ),
				'confirm_delete_choice' => esc_html__( 'Remove \'%choice_name\'?', 'hbook-admin' ),
				'variables_intro' => esc_html__( 'In the following fields you can use these variables:', 'hbook-admin' ),
			),
			'hb_form_name' => $this->form_name,
			'hb_fields' => $hbdb->get_form_fields()
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	function display() {
	?>

	<div class="wrap">

		<div id="hb-contact-form-settings">
			
			<h1><?php esc_html_e( 'Contact form settings', 'hbook-admin' ); ?></h1>

			<hr/>
			
			<h3><?php esc_html_e( 'Fields', 'hbook-admin' ); ?></h3>
						
			<p>
				<i>
					<?php esc_html_e( 'Customize the Contact form.', 'hbook-admin' ); ?>
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
			
			<hr/>
			
			<h3><?php esc_html_e( 'Email settings', 'hbook-admin' ); ?></h3>

			<?php
			foreach ( $this->options as $id => $option ) {
				$function_to_call = 'display_' . $option['type'] . '_option';
				$this->options_utils->$function_to_call( $id, $option );
				if ( $id == 'hb_contact_message_type' ) {
				?>
				<br/><small data-bind="html: variables_list"></small>
				<?php
				}
			}
			?>
			
		</div>
		
		<?php $this->options_utils->display_save_options_section(); ?>
		
	</div>

	<?php
	}
}