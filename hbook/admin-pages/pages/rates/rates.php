<?php	
class HbAdminPageRates extends HbAdminPage {
	
	private $seasons;
	private $accom;
	private $rules;
	private $rate_types;
	private $discounts;
	private $discount_rules;
	
	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$this->seasons = $hbdb->get_seasons_id_name();
		$this->accom = $hbdb->get_all_accom();
		$this->rules = $hbdb->get_all_rate_booking_rules();
		$this->discount_rules = $hbdb->get_all_discount_rules();
		$this->discounts = $hbdb->get_all_discounts();
		$this->rate_types = array( 
			array( 
				'id' => 'accom',
				'name' => esc_html__( 'accommodation', 'hbook-admin' ),
				'title' => esc_html__( 'Accommodation rates', 'hbook-admin' ),
			),
			array( 
				'id' => 'extra_adults',
				'name' => esc_html__( 'extra adults', 'hbook-admin' ),
				'title' => esc_html__( 'Price per adult above normal occupancy', 'hbook-admin' ),
			),
			array( 
				'id' => 'extra_children',
				'name' => esc_html__( 'extra children', 'hbook-admin' ),
				'title' => esc_html__( 'Price per child above normal occupancy', 'hbook-admin' ),
			)
		);
		
		$this->data = array(
			'hb_text' => array(
				'invalid_amount' => esc_html__( 'Invalid amount.', 'hbook-admin' ),
				'no_seasons_selected' => esc_html__( 'No seasons selected', 'hbook-admin' ),
				'no_rules_selected' => '',
				'no_discounts_selected' => esc_html__( 'No discounts selected', 'hbook-admin' ),
				'per_night' => esc_html__( 'per night', 'hbook-admin' ),
				'for_nights' => esc_html__( 'for a %s-night stay', 'hbook-admin' ),
				'unset' => esc_html__( 'Unset', 'hbook-admin' ),
				'nights_more' => esc_html__( 'nights and more', 'hbook-admin' ),
			),
			'rates' => $hbdb->get_all_rates(),
			'accom_list' => $this->accom,
			'seasons_list' => $this->seasons,
			'rules_list' => $this->rules,
			'discount_rules_list' => $this->discount_rules,
			'discounts' => $this->discounts,
            'hb_price_precision' => get_option( 'hb_price_precision' ),
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	public function display() {
	?>

	<div class="wrap">

		<h2><?php esc_html_e( 'Rates', 'hbook-admin' ); ?></h2>
		<?php $this->display_right_menu(); ?>
		
		<hr/>

		<?php 
		if ( $this->utils->nb_accom() == 0 ) { 
			$accom_all_status = false;
			$accom_all_languages = true;
			$all_accom_in_all_languages = $this->hbdb->get_all_accom_ids( $accom_all_status, $accom_all_languages );
			if ( count( $all_accom_in_all_languages ) > 0 ) {
				if ( function_exists( 'pll_get_post' ) ) {
					echo( '<p>' );
					printf(
						esc_html__( 'It seems Polylang is not set properly. Please read %s', 'hbook-admin' ),
						'<a target="_blank" href="https://hotelwp.com/knowledgebase/?article=39">' .
						esc_html__( 'HBook documentation about setting up Polylang and HBook', 'hbook-admin' ) . 
						'</a>' 
					);
					echo( '</p>' );
				} else if ( function_exists( 'icl_object_id' ) ) {
					echo( '<p>' );
					printf(
						esc_html__( 'It seems WPML is not set properly. Please read %s', 'hbook-admin' ),
						'<a target="_blank" href="https://hotelwp.com/knowledgebase/?article=37">' .
						esc_html__( 'HBook documentation about setting up WPML and HBook', 'hbook-admin' ) . 
						'</a>' 
					);
					echo( '</p>' );
				} else {
					echo( '<p>' . esc_html__( 'At least one accommodation which uses the default website language must be created in order to set rates.', 'hbook-admin' ) . '</p>' );
				}
			} else {
				if ( count( $this->seasons ) == 0 ) { 
					echo( '<p>' . esc_html__( 'At least one accommodation and one season must be created in order to set rates.', 'hbook-admin' ) . '</p>' );
				} else {
					echo( '<p>' . esc_html__( 'At least one accommodation must be created in order to set rates.', 'hbook-admin' ) . '</p>' );
				}
			}
		} else if ( count( $this->seasons ) == 0 ) { 
			echo( '<p>' . esc_html__( 'At least one one season must be created in order to set rates.', 'hbook-admin' ) . '</p>' );
		} else {
			foreach ( $this->rate_types as $rate_type ) {
		?>
		
			<h3>
				<?php echo( $rate_type['title'] ); ?>
				<a data-bind="click: function() { create_rate( '<?php echo( $rate_type['id'] ); ?>' ) }" href="#" class="add-new-h2"><?php esc_html_e( 'Add rate', 'hbook-admin' ); ?></a>
				<span class="hb-add-new spinner hb-add-<?php echo( $rate_type['id'] ); ?>-rate"></span>
			</h3>
			
			<!-- ko if: nb_rates( '<?php echo( $rate_type['id'] ); ?>' ) == 0 -->
			<p><?php printf( esc_html__( 'No %s rates have been defined yet.', 'hbook-admin' ), $rate_type['name'] ); ?></p>
			<!-- /ko -->
				
			<!-- ko if: nb_rates( '<?php echo( $rate_type['id'] ); ?>' ) > 0 -->
			<table class="wp-list-table widefat">
			
				<?php $this->table_rates_head(); ?>
				
				<tbody data-bind="template: { name: function( rate ) { return rate_template_to_use( rate, '<?php echo( $rate_type['id'] ); ?>' ); }, foreach: rates, beforeRemove: hide_setting }">
				</tbody>
				
			</table>
			<!-- /ko -->
			
			<br/><hr/>
		
		<?php } ?>
		
		<h3>
			<?php esc_html_e( 'Discounts', 'hbook-admin' ); ?>
			<?php if ( $this->discount_rules ) { ?>
			<a data-bind="click: create_discount" href="#" class="add-new-h2"><?php esc_html_e( 'Add discount rate', 'hbook-admin' ); ?></a>
			<span class="hb-add-new spinner hb-add-discount"></span>
			<?php } ?>
		</h3>
		
		<?php if ( ! $this->discount_rules ) { ?>
		
		<p>
			<?php 
			printf( 
				esc_html__( 'No discount rules have been defined yet. You can define a discount rule on the %s.', 'hbook-admin' ),
				'<a href="' . admin_url( 'admin.php?page=hb_rules' ) . '">' .
				esc_html__( 'Booking rules page', 'hbook-admin' ) .
				'</a>'
			);
			?>
		</p>
		
		<?php } else { ?>
		
		<!-- ko if: discounts().length == 0 -->
		<p><?php esc_html_e( 'No discount rates have been defined yet.', 'hbook-admin' ); ?></p>
		<!-- /ko -->
		
		<!-- ko if: discounts().length > 0 -->
		<table class="wp-list-table widefat">
		
			<thead>
				<tr>
					<th width="20%"><?php esc_html_e( 'Accommodation', 'hbook-admin' ); ?></th>
					<th width="20%"><?php esc_html_e( 'Seasons', 'hbook-admin' ); ?></th>
					<th width="20%"><?php esc_html_e( 'Discounts', 'hbook-admin' ); ?></th>
					<th width="20%"><?php esc_html_e( 'Amount', 'hbook-admin' ); ?></th>
					<th width="20%" class="hb-table-action"><?php esc_html_e( 'Actions', 'hbook-admin' ); ?></th>
				</tr>
			</thead>
			
			<tbody data-bind="template: { name: discount_template_to_use, foreach: discounts, beforeRemove: hide_setting }">
			</tbody>
				
		</table>
		<!-- /ko -->
		
		<?php } ?>
		
		<script id="text_tmpl" type="text/html">
			<tr>
				<td data-bind="text: accom_list"></td>
				<td data-bind="text: seasons_list"></td>
				<?php if ( count( $this->rules ) > 0 ) { ?>
				<td data-bind="text: rules_list"></td>
				<?php } ?>
				<td data-bind="html: amount_text"></td>
				<td class="hb-table-action"><?php $this->display_admin_action(); ?></td>
			</tr>
		</script>

		<script id="discount_text_tmpl" type="text/html">
			<tr>
				<td data-bind="text: accom_list"></td>
				<td data-bind="text: seasons_list"></td>
				<td data-bind="text: rules_list"></td>
				<td data-bind="html: amount_text"></td>
				<td class="hb-table-action"><?php $this->display_admin_action(); ?></td>
			</tr>
		</script>
		
		<script id="edit_tmpl" type="text/html">
			<tr>
				<td><?php $this->display_checkbox_list( $this->accom, 'accom' ); ?></td>
				<td><?php $this->display_seasons_checkbox_list(); ?></td>
				<?php if ( count( $this->rules ) > 0 ) { ?>
				<td>
				<?php
				$display_check_all_box = false;
				$display_select_all_link = true;
				$this->display_rules_checkbox_list();
				?>
				</td>
				<?php } ?>
				<td>
					<?php if ( get_option( 'hb_currency_position' ) == 'before' ) { ?>
					<span><?php echo( $this->utils->get_currency_symbol() ); ?></span>
					<?php } ?>
					<input data-bind="value: amount" type="text" class="hb-rate-amount" />
					<?php if ( get_option( 'hb_currency_position' ) == 'after' ) { ?>
					<span><?php echo( $this->utils->get_currency_symbol() ); ?></span>
					<?php } ?>
					<br/>
					<?php esc_html_e( 'for', 'hbook-admin' ); ?><br/>
					<input data-bind="value: nights" type="text" class="hb-rate-nights" />
					<span><?php esc_html_e( 'night(s)', 'hbook-admin' ); ?></span>
				</td>
				<td class="hb-table-action"><?php $this->display_admin_on_edit_action(); ?></td>
			</tr>
		</script>
		
		<script id="discount_edit_tmpl" type="text/html">
			<tr>
				<td><?php $this->display_checkbox_list( $this->accom, 'accom' ); ?></td>
				<td><?php $this->display_seasons_checkbox_list(); ?></td>
				<td>
				<?php
				$display_check_all_box = false;
				$display_select_all_link = true;
				$this->display_checkbox_list( $this->discount_rules, 'rules', $display_check_all_box, $display_select_all_link ); 
				?>
				</td>
				<td><?php $this->display_edit_amount_fixed_percent(); ?></td>
				<td class="hb-table-action"><?php $this->display_admin_on_edit_action(); ?></td>
			</tr>
		</script>
		
		<script id="empty_tmpl" type="text/html"></script>
		
		<?php } ?>
		
	</div><!-- end .wrap -->
	
<?php
	}
	
	private function table_rates_head() {
		if ( count( $this->rules ) > 0 ) {
			$col_width = '20%';
		} else {
			$col_width = '25%';
		}
		?>
			<thead>
				<tr>
					<th width="<?php echo( $col_width ); ?>"><?php esc_html_e( 'Accommodation', 'hbook-admin' ); ?></th>
					<th width="<?php echo( $col_width ); ?>"><?php esc_html_e( 'Seasons', 'hbook-admin' ); ?></th>
					<?php if ( count( $this->rules ) > 0 ) { ?>
					<th width="<?php echo( $col_width ); ?>"><?php esc_html_e( 'Special rates', 'hbook-admin' ); ?></th>
					<?php } ?>
					<th width="<?php echo( $col_width ); ?>"><?php esc_html_e( 'Amount', 'hbook-admin' ); ?></th>
					<th width="<?php echo( $col_width ); ?>" class="hb-table-action"><?php esc_html_e( 'Actions', 'hbook-admin' ); ?></th>
				</tr>
			</thead>
		<?php
	}
	
	private function display_seasons_checkbox_list() {
		?>
		<input data-bind="checked: all_seasons" type="checkbox" id="hb-checkbox-seasons-all" />
		<label for="hb-checkbox-seasons-all"><?php esc_html_e( 'All', 'hbook-admin' ); ?></label><br/>
		<?php
		foreach ( $this->seasons as $id => $season ) {
		?>
			<input id="hb-checkbox-seasons-<?php echo( $id ); ?>" data-bind="checked: seasons, disable: all_seasons" type="checkbox" value="<?php echo( $id ); ?>" />
			<label for="hb-checkbox-seasons-<?php echo( $id ); ?>"><?php echo( $season ); ?></label><br/>
		<?php
		}
		?>
		<a data-bind="click: unselect_all_seasons" href="#"><?php esc_html_e( 'Unselect all', 'hbook-admin' ); ?></a>
		<?php
	}
	
	private function display_rules_checkbox_list() {
		foreach ( $this->rules as $id => $rule ) {
		?>
			<input id="hb-checkbox-rules-<?php echo( $id ); ?>" data-bind="checked: rules, disable: all_rules" type="checkbox" value="<?php echo( $id ); ?>" />
			<label for="hb-checkbox-rules-<?php echo( $id ); ?>"><?php echo( $rule['name'] ); ?></label><br/>
		<?php
		}
		?>
		<a data-bind="click: select_all_rules" href="#"><?php esc_html_e( 'Select all', 'hbook-admin' ); ?></a> - 
		<a data-bind="click: unselect_all_rules" href="#"><?php esc_html_e( 'Unselect all', 'hbook-admin' ); ?></a>
		<?php
	}
	
}