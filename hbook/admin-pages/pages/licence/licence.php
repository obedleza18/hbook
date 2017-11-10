<?php
class HbAdminPageLicence extends HbAdminPage {
	
	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$this->data = array(
			'hb_text' => array(
				'remove_purchase_code' => esc_html__( 'Remove purchase code for this website?', 'hbook-admin' ),
			)
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	public function display() {
	?>

	<div class="wrap">
		
		<h2><?php esc_html_e( 'Licence', 'hbook-admin' ); ?></h2>
		
		<hr/>

		<?php if ( $this->utils->is_htw_theme_active() ) : ?>

		<p><?php printf( esc_html__( 'HBook is bundled with %1$s. Please use %1$s purchase code to activate HBook.', 'hbook-admin' ), wp_get_theme() ); ?></p>

		<?php else : ?>
			
		<p><a href="https://hotelwp.com/documentation/hbook/#faq-purchase-code"><?php esc_html_e( 'Where can I find my purchase code?', 'hbook-admin' ); ?></a></p>

		<?php endif; ?>
		
		<?php $this->display_right_menu(); ?>
		
		<form id="hb-verify-licence" method="post">
			
			<?php 
			$valid_purchase_code = get_option( 'hb_valid_purchase_code' );
			
			if ( $valid_purchase_code == 'error' ) :
				if ( get_option( 'hb_purchase_code_error' ) == 'no-online-validation' ) {
					$error_text = esc_html__( 'An error occurred when trying to validate your licence. Please go to HotelWP to obtain a validation code.', 'hbook-admin' );
					if ( strpos( $error_text, 'HotelWP' ) ) {
						$error_text = str_replace( 'HotelWP', '<a target="_blank" href="https://hotelwp.com/hbook-licence-validation/">HotelWP</a>', $error_text );
					} else {
						$error_text .= ' (<a target="_blank" href="https://hotelwp.com/hbook-licence/">HotelWP</a>)';
					}
				} else if ( get_option( 'hb_purchase_code_error' ) == 'wrong-validation-code' ) { 
					$error_text = esc_html__( 'The code you entered is not valid. Please try again. You can also contact %s if you need further help.', 'hbook-admin' );
					$hwp_support_string = esc_html__( 'HotelWP support', 'hbook-admin' );
					if ( strpos( $error_text, '%s' ) ) {
						$error_text = str_replace( '%s', '<a target="_blank" href="https://hotelwp.com/contact-support/">' . $hwp_support_string . '</a>', $error_text );
					} else {
						$error_text .= ' (<a target="_blank" href="https://hotelwp.com/contact-support/">HotelWP support</a>)';
					}
				}
				?>
				
				<p><?php echo( wp_kses_post( $error_text ) ); ?></p>
				
				<p>
					<label><?php esc_html_e( 'Your website url:', 'hbook-admin' ); ?></label><br/>
					<input type="text" onclick="this.select()" onfocus="this.select()" readonly="readonly" value="<?php echo( esc_attr( site_url() ) ); ?>" />
				</p>
				<p>
					<label><?php esc_html_e( 'Enter validation code', 'hbook-admin' ); ?></label><br/>
					<input type="text" name="hb-licence-validation-code" />
				</p>
				<p style="display:none">
					<input type="text" name="hb-forced-licence-validation" />
					<?php echo( wp_kses_post( get_option( 'hb_purchase_code_error_text' ) ) ); ?>
				</p>
				
				<?php
				delete_option( 'hb_purchase_code_error' );
				delete_option( 'hb_purchase_code_error_text' );
				delete_option( 'hb_purchase_code_error_text' );
				
			endif;
			?>
			
			<p>
				<label><?php esc_html_e( 'Enter your purchase code', 'hbook-admin' ); ?></label><br/>
				<input type="text" name="hb-purchase-code" size="50" value="<?php echo( esc_attr( get_option( 'hb_purchase_code' ) ) ); ?>" />
				<?php if ( $valid_purchase_code == 'error' ) : ?>
				<br/><br/>
				<?php endif; ?>
				<input type="submit" value="<?php esc_attr_e( 'Verify purchase code', 'hbook-admin' ) ?>" class="button-primary" />
			</p>
			
			<?php if ( in_array( $valid_purchase_code, array( 'yes', 'no', 'already', 'removed', 'pending' ) ) ) : ?>
				
			<p <?php if ( $valid_purchase_code == 'no' ) { echo( 'class="hb-purchase-code-not-valid-msg"'); } ?>>
				<?php
				if ( $valid_purchase_code == 'yes' ) {
					esc_html_e( 'Your purchase code is valid.', 'hbook-admin' );
				} else if ( $valid_purchase_code == 'no' ) {
					esc_html_e( 'Your purchase code is not valid.', 'hbook-admin' );
				} else if ( $valid_purchase_code == 'already' ) {
					esc_html_e( 'This purchase code is already in use.', 'hbook-admin' );
				} else if ( $valid_purchase_code == 'removed' ) {
					esc_html_e( 'The purchase code has been removed.', 'hbook-admin' );
					delete_option( 'hb_valid_purchase_code' );
				} else if ( $valid_purchase_code == 'pending' ) {
					esc_html_e( 'This purchase code has not been verified.', 'hbook-admin' );
				}
				?>
			</p>
			
			<?php 
			endif;
			
			if ( $valid_purchase_code == 'error' ) {
				update_option( 'hb_valid_purchase_code', 'pending' );
			}
			
			if ( $valid_purchase_code == 'yes' ) : ?>
			<p>
				<a class="hb-remove-purchase-code" href="#"><?php esc_html_e( 'Remove purchase code for this website.', 'hbook-admin' ); ?></a>
			</p>
			<?php 
			endif; 
			
			wp_nonce_field( 'hb_nonce_licence', 'hb_nonce_licence' );
			?>
			
		</form>
	</div><!-- end .wrap -->
	
	<?php
	}
	
}