<?php 
class HbAdminPageReservationsAddResa {
	
	public function __construct( $hbdb, $utils ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
	}
	
	public function display() {
	?>
	
		<div id="hb-add-resa-section" class="hb-resa-section">

			<h3 id="hb-add-resa-toggle" class="hb-resa-section-toggle">
				<?php esc_html_e( 'Add a reservation', 'hbook-admin' ); ?>
				<span class="dashicons dashicons-arrow-down"></span>
				<span class="dashicons dashicons-arrow-up"></span>
			</h3>
			
			<div id="hb-add-resa" class="stuffbox">
				
				<form id="hb-resa-check-price">
					<p class="hb-resa-date-wrapper">
						<label for="hb-check-in"><?php esc_html_e( 'Check-in date:', 'hbook-admin' ); ?></label><br/>
						<input id="hb-check-in" type="text" class="hb-input-date" value="" />
					</p>		
					<p class="hb-resa-date-wrapper">
						<label for="hb-check-out"><?php esc_html_e( 'Check-out date:', 'hbook-admin' ); ?></label><br/>
						<input id="hb-check-out" type="text" class="hb-input-date" value="" />
					</p>
					
					<?php
					$people_selects = array(
						'adults' => '',
						'children' => ''
					);
					foreach ( $people_selects as $key => $markup ) {
						if ( $key == 'adults' ) {
							$loop_start = 1;
							$loop_end = get_option( 'hb_maximum_adults' );
						} else {
							$loop_start = 0;
							$loop_end = get_option( 'hb_maximum_children' );
						}
						$markup_options = '';
						for ( $i = $loop_start; $i <= $loop_end; $i++ ) {
							$markup_options .= '<option value="' . $i . '">' . $i . '</option>';
						}
						$people_selects[ $key ] = '<select id="hb-' . $key . '">' . $markup_options . '</select>';
					}	
					?>
					<p class="hb-resa-people-wrapper">
						<label for="hb-adults"><?php esc_html_e( 'Adults:', 'hbook-admin' ); ?></label><br/>
						<?php echo( $people_selects['adults'] ); ?>
					</p>		
					<p class="hb-resa-people-wrapper">
						<label for="hb-children"><?php esc_html_e( 'Children:', 'hbook-admin' ); ?></label><br/>
						<?php echo( $people_selects['children'] ); ?>
					</p>		
					<?php
					$options_markup = '';
					$accom = $this->hbdb->get_all_accom();
					foreach ( $accom as $accom_id => $accom_name ) {
						$options_markup .= '<option value="' . $accom_id . '">' . $accom_name . '</option>';
					}
					?>
					<p class="hb-resa-accom-wrapper">
						<label for="hb-accom"><?php esc_html_e( 'Accommodation type:', 'hbook-admin' ); ?></label><br/>
						<select id="hb-accom"><?php echo( $options_markup ); ?></select>
					</p>
					<div class="hb-clearfix">
						<p id="hb-resa-check-submit">
							<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Check price and availability', 'hbook-admin' ); ?>" />
						</p>
						<p id="hb-resa-check-submit-ajax">
							<span class="hb-ajaxing">
								<span class="spinner"></span>
							</span>
						</p>
					</div>
					<p id="hb-resa-price-error"></p>
				</form>
					
				<form id="hb-resa-customer" data-bind="submit: add_resa">
					<p id="hb-resa-price-error"></p>
					<p id="hb-resa-accom-type"><b><?php esc_html_e( 'Accommodation type:', 'hbook-admin' ); ?></b><br/><span></span></p>
					<p id="hb-resa-price-breakdown"></p>
					<div id="hb-resa-options"></div>
					<p id="hb-resa-fees"></p>
					<p id="hb-resa-price">
						<?php esc_html_e( 'Total price:', 'hbook-admin' ); ?> 
						<?php echo( $this->utils->price_placeholder() ); ?>
						<input id="hb-resa-accom-price" type="hidden" />
						<input id="hb-resa-total-price" type="hidden" />
					</p>
					<p id="hb-resa-price-other-wrapper">
						<label for="hb-resa-price-other"><?php esc_html_e( 'Set a different price:', 'hbook-admin' ); ?></label><br/>
						<input id="hb-resa-price-other" type="text" />
					</p>
					<p id="hb-resa-accom-num"></p>
					<div id="hb-resa-customer-details-wrap">
						<p>
							<label><?php esc_html_e( 'Customer:', 'hbook-admin' ); ?></label><br/>
							<input type="radio" id="hb-customer-type-id" name="hb-customer-type" value="id" checked /><label for="hb-customer-type-id"><?php esc_html_e( 'Select existing customer', 'hbook-admin' ); ?></label><br/>
							<input type="radio" id="hb-customer-type-details" name="hb-customer-type" value="details" /><label for="hb-customer-type-details"><?php esc_html_e( 'Enter customer details', 'hbook-admin' ); ?></label>
						</p>
						<div id="hb-resa-customer-id">
							<div class="hb-resa-filter">
								<select id="hb-customer-id-list" multiple size="6" data-bind="options: add_resa_customers_list, optionsValue: 'id', optionsText: 'id_name'"></select><br/>
								<input type="text" data-bind="value: add_resa_customers_list_filter, valueUpdate: 'afterkeydown'" placeholder="<?php esc_attr_e( 'Search a customer...', 'hbook-admin' ); ?>" /><br/>
							</div>
						</div>
						<div id="hb-resa-customer-details">
							<?php
							require_once dirname( dirname( dirname( plugin_dir_path( __FILE__ ) ) ) ) . '/front-end/form-fields.php';
							$form_fields = new HBFormFields( array() );
							$fields = $this->hbdb->get_customer_form_fields();
							foreach ( $fields as $field ) {
								echo( $form_fields->get_field_mark_up( $field, array(), false, false ) );
							}
							?>
						</div>
						<div id="hb-resa-additional-info">
							<?php 
							$fields = $this->hbdb->get_additional_booking_info_form_fields();
							foreach ( $fields as $field ) {
								echo( $form_fields->get_field_mark_up( $field, array(), false, false ) );
							}
							?>
						</div>
						<p>
							<label for="hb-admin-comment"><?php esc_html_e( 'Comment:', 'hbook-admin' ); ?></label><br/>
							<textarea id="hb-admin-comment"></textarea>
						</p>
						<div class="hb-clearfix">
							<p id="hb-resa-customer-submit">
								<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Create reservation', 'hbook-admin' ); ?>" />
							</p>
							<p id="hb-resa-customer-submit-ajax">
								<span class="hb-ajaxing">
									<span class="spinner"></span>
									<span><?php esc_html_e( 'Updating database...', 'hbook-admin' ); ?></span>
								</span>
							</p>
						</div>
					</div>
				</form>
				
				<p id="hb-create-resa-error"></p>
				
			</div>
			
		</div>
		
		<hr/>
		
	<?php
	}
	
}