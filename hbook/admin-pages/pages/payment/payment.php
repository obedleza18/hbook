<?php
class HbAdminPagePayment extends HbAdminPage {
    
    public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$payment_gateway_ids = array();
		foreach( $utils->get_payment_gateways() as $payment_gateway ) {
			$payment_gateway_ids[] = $payment_gateway->id;
		}
        $this->data = array(
			'hb_text' => array(
				'form_saved' => esc_html__( 'Settings have been saved.', 'hbook-admin' ),
				'deposit_not_valid' => esc_html__( 'The deposit is not valid. Enter a number only.', 'hbook-admin' ),
			),
			'hb_payment_gateways' => $payment_gateway_ids,
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	public function display() {
    ?>
    
    <div class="wrap">
    
        <form id="hb-settings-form">
		
			<h1><?php esc_html_e( 'Payment', 'hbook-admin' ); ?></h1>

            <?php 
            $this->display_right_menu(); 
            
            $gateway_options = array();
            $gateway_activate_section = array(
                'label' => esc_html__( 'Active payment gateways', 'hbook-admin' ),
                'options' => array()
            );
            foreach ( $this->utils->get_payment_gateways() as $gateway ) {
                $option_id = 'hb_' . $gateway->id .'_active';
                if ( ! get_option( $option_id ) ) {
                    update_option( $option_id, 'no' );
                }
                $gateway_activate_section['options'][ $option_id ] = array(
					'label' => sprintf( esc_html__( 'Activate %s:', 'hbook-admin' ), esc_html( $gateway->name ) ),
					'type' => 'radio',
					'choice' => array(
						'yes' => esc_html__( 'Yes', 'hbook-admin' ),
						'no' => esc_html__( 'No', 'hbook-admin' ),
					),
					'wrapper-class' => 'hb-payment-gateway-active'
                );
                if ( $gateway->admin_fields() ) {
                    $gateway_options[ $gateway->id ] = $gateway->admin_fields();
                }
            }
            
            $payment_setting_sections = array_merge( $this->options_utils->payment_settings, array( $gateway_activate_section ),$gateway_options );
            
			foreach ( $payment_setting_sections as $section_id => $section ) {
				$section_class = 'hb-payment-section-' . $section_id;
				?>
				
				<div class="<?php echo( esc_attr( $section_class ) ); ?>">
				
				<?php
                $this->options_utils->display_section_title( $section['label'] );
                foreach ( $section['options'] as $id => $option ) {
                    $function_to_call = 'display_' . $option['type'] . '_option';
                    $this->options_utils->$function_to_call( $id, $option );
                }
				$this->options_utils->display_save_options_section();
				?>
				
				</div>
			
				<?php
			}
            wp_nonce_field( 'hb_nonce_update_db', 'hb_nonce_update_db' );
			?>
			
			<input type="hidden" name="action" value="hb_update_payment_settings" />
			<input id="hb-nonce" type="hidden" name="nonce" value="" />
			
		</form>
   
    </div>
    <?php
    }
    
}