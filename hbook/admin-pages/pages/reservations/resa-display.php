<?php
class HbAdminPageReservationsDisplayHelper {
	
	private $accom_list;
	
	public function __construct( $accom_list ) {
		$this->accom_list = $accom_list;
	}
	
	public function display_resa_calendar() {
	?>
	
	<div class="hb-resa-section">
	
		<h3><?php esc_html_e( 'Calendar', 'hbook-admin' ); ?></h3>
		<div>
			<select id="hb-resa-cal-accommodation">
				<option value="all"><?php esc_html_e( 'All accommodations', 'hbook-admin' ); ?></option>
				<?php
				foreach( $this->accom_list as $accom_id => $accom_name ) { ?>
					<option value="<?php echo( $accom_id ); ?>"><?php echo( $accom_name ); ?></option>
				<?php
				}
				?>
			</select>
		</div><br/>
		
		<div id="hb-resa-cal-wrapper">
			<div id="hb-resa-cal-scroller">
				<table id="hb-resa-accom-table" class="hb-resa-cal-table"></table>
				<table id="hb-resa-cal-table" class="hb-resa-cal-table"></table>
			</div>
		</div>
	
	</div><!-- end .hb-resa-section -->
	
	<hr/>
	
	<?php
	}
	
	public function display_resa_details() {
	?>
	
	<div class="hb-resa-section">
	
		<h3><?php esc_html_e( 'Reservation details', 'hbook-admin' ); ?></h3>
		
		<!-- ko if: selected_resa() == 0 -->
		<p><?php esc_html_e( 'Click on a number in the calendar to view the reservation details', 'hbook-admin' ); ?></p>
		<!-- /ko -->
		
		<!-- ko if: selected_resa() != 0 -->
		<p>
			<a href="#" data-bind="click: hide_selected_resa"><?php esc_html_e( 'Hide', 'hbook-admin' ); ?></a>
		</p>
		
		<table class="wp-list-table widefat hb-resa-table">
		
			<?php $this->display_resa_thead(); ?>
						
			<tbody data-bind="foreach { data: resa_detailed }">
			<?php $this->display_resa_tr(); ?>
			</tbody>
		
		</table>
		<!-- /ko -->
		
	</div>
	
	<hr/>
	
	<?php
	}
	
	public function display_resa_list() {
	?>
	
	<div class="hb-resa-section">
	
		<h3><?php esc_html_e( 'Reservation list', 'hbook-admin' ); ?></h3>
		
		<!-- ko if: resa().length == 0 -->
		<?php esc_html_e( 'No reservations yet.', 'hbook-admin' ); ?>
		<!-- /ko -->
		
		<!-- ko if: resa().length != 0 -->
		
		<p>
			<select data-bind="value: resa_filter">
				<option value="none"><?php esc_html_e( 'No filter', 'hbook-admin' ); ?></option>
				<option value="customer"><?php esc_html_e( 'Filter by Customer', 'hbook-admin' ); ?></option>
				<option value="check_in_date"><?php esc_html_e( 'Filter by Check-in date', 'hbook-admin' ); ?></option>
				<option value="check_out_date"><?php esc_html_e( 'Filter by Check-out date', 'hbook-admin' ); ?></option>
				<option value="check_in_out_date"><?php esc_html_e( 'Filter by Check-in and check-out date', 'hbook-admin' ); ?></option>
			</select>
			<select data-bind="value: resa_sort">
				<option value="received_date"><?php esc_html_e( 'Sort by Received date', 'hbook-admin' ); ?></option>
				<option value="check_in_date"><?php esc_html_e( 'Sort by Check-in date', 'hbook-admin' ); ?></option>
			</select>
		</p>
		<div class="hb-resa-filter">
			<p data-bind="visible: resa_filter() == 'customer'">
				<input type="text" data-bind="value: resa_filter_customer, valueUpdate: 'afterkeydown'" placeholder="<?php esc_attr_e( 'Enter customer name or email', 'hbook-admin' ); ?>" />
			</p>
			<?php 
			$resa_filter_dates_types = array( 'check_in', 'check_out', 'check_in_out' );
			foreach ( $resa_filter_dates_types as $filter_type ) :
			?>
			<p data-bind="visible: resa_filter() == '<?php echo( $filter_type ); ?>_date'">
				<?php esc_html_e( 'Between', 'hbook-admin' ); ?>&nbsp;
				<input type="text" data-bind="value: resa_filter_<?php echo( $filter_type ); ?>_from" class="hb-input-date hb-filter-date-from" /><a href="#" class="hb-filter-clear-date dashicons dashicons-no" /></a>&nbsp;
				<?php esc_html_e( 'and', 'hbook-admin' ); ?>&nbsp;
				<input type="text" data-bind="value: resa_filter_<?php echo( $filter_type ); ?>_to" class="hb-input-date hb-filter-date-to" /><a href="#" class="hb-filter-clear-date dashicons dashicons-no" /></a>
			</p>
			<?php endforeach; ?>
		</div>
		
		<?php $this->display_resa_pagination(); ?>
		
		<table class="wp-list-table widefat hb-resa-table">
		
			<?php $this->display_resa_thead(); ?>
		
			<tbody data-bind="foreach { data: resa_paginated }">
			<?php $this->display_resa_tr(); //, beforeRemove: hide_resa, afterAdd: show_resa?>
			</tbody>
			
		</table>

		<?php $this->display_resa_pagination(); ?>
		
		<!-- /ko -->
		
	</div>
	
	<?php	
	}
	
	private function display_resa_thead() {
	?>
	
		<thead>
			<tr>
				<td class="hb-resa-num-column"><?php esc_html_e( 'Num', 'hbook-admin' ); ?></td>
				<td class="hb-resa-status-column"><?php esc_html_e( 'Status', 'hbook-admin' ); ?></td>
				<td class="hb-resa-check-in-out-column"><?php esc_html_e( 'Check-in / Check-out', 'hbook-admin' ); ?></td>
				<td class="hb-resa-accom-column">
					<?php esc_html_e( 'Accom. type', 'hbook-admin' ); ?>
					<small><?php esc_html_e( '(number)', 'hbook-admin' ); ?></small>
				</td>
				<td class="hb-resa-info-column"><?php esc_html_e( 'Information', 'hbook-admin' ); ?></td>
				<td class="hb-resa-comment-column"><?php esc_html_e( 'Comment', 'hbook-admin' ); ?></td>
				<td class="hb-resa-customer-column"><?php esc_html_e( 'Customer', 'hbook-admin' ); ?></td>
				<td class="hb-resa-price-column"><?php esc_html_e( 'Price / Payment', 'hbook-admin' ); ?></td>
				<td class="hb-resa-received-column"><?php esc_html_e( 'Received on', 'hbook-admin' ); ?></td>
				<td class="hb-resa-actions-column"><?php if ( $this->user_can_edit() ) { esc_html_e( 'Actions', 'hbook-admin' ); } ?></td>
			</tr>
		</thead>
		
	<?php
	}
	
	private function display_resa_tr() {
	?>
	
		<tr data-bind="attr: { 'data-resa-num': id, class: anim_class }">
			<td class="hb-resa-num-column" data-bind="text: id"></td>
			<td data-bind="html: status_markup"></td>
			<td>
				<span data-bind="text: check_in"></span><br/>
				<span data-bind="text: check_out"></span><br/>
				<span data-bind="text: nb_nights"></span>
				<span data-bind="visible: nb_nights() == 1"><?php esc_html_e( 'night', 'hbook-admin' ); ?></span>
				<span data-bind="visible: nb_nights() != 1"><?php esc_html_e( 'nights', 'hbook-admin' ); ?></span>
				<!-- ko if: status() != 'processing' -->
				<?php if ( $this->user_can_edit() ) : ?>
				<div>
					<a data-bind="visible: ! editing_dates() && ! saving_dates(), click: $root.edit_dates" href="#"><?php esc_html_e( 'Edit', 'hbook-admin' ); ?></a>
					<div data-bind="visible: editing_dates()">
						<?php esc_html_e( 'Check-in:', 'hbook-admin' ); ?>
						<br/>
						<input class="hb-input-edit-resa hb-input-edit-resa-dates hb-input-edit-resa-check-in" data-bind="value: check_in_tmp" type="text" />
						<?php esc_html_e( 'Check-out:', 'hbook-admin' ); ?>
						<br/>
						<input class="hb-input-edit-resa hb-input-edit-resa-dates hb-input-edit-resa-check-out" data-bind="value: check_out_tmp" type="text" />
						<span data-bind="text: nb_nights_tmp"></span>
						<span data-bind="visible: nb_nights_tmp() == 1"><?php esc_html_e( 'night', 'hbook-admin' ); ?><br/></span>
						<span data-bind="visible: nb_nights_tmp() > 1"><?php esc_html_e( 'nights', 'hbook-admin' ); ?><br/></span>
						<a data-bind="click: $root.save_dates, visible: ! saving_dates()" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
						<input type="button" disabled data-bind="visible: saving_dates()" href="#" class="button-primary" value="<?php esc_attr_e( 'Saving', 'hbook-admin' ); ?>" />
						<a data-bind="click: $root.cancel_edit_dates" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
					</div>
				</div>
				<?php endif; ?>
				<!-- /ko -->
			</td>
			<td>
				<div data-bind="visible: editing_accom()"><b><?php esc_html_e( 'Current accom.:', 'hbook-admin' ); ?></b></div>
				<div data-bind="html: accom"></div>
				<!-- ko if: status() != 'processing' -->
				<?php if ( $this->user_can_edit() ) : ?>
				<a data-bind="visible: accom_num() != 0 && ! editing_accom() && ! fetching_accom() && ! saving_accom(), click: $root.edit_accom" href="#"><?php esc_html_e( 'Edit', 'hbook-admin' ); ?></a>
				<?php endif; ?>
				<div data-bind="visible: saving_accom()"><?php esc_html_e( 'Updating...', 'hbook-admin' ); ?></div>
				<div data-bind="visible: fetching_accom()"><?php esc_html_e( 'Fetching available accom...', 'hbook-admin' ); ?></div>
				<div data-bind="visible: editing_accom()">
					<br/>
					<div><b><?php esc_html_e( 'Select new accom.:', 'hbook-admin' ); ?></b></div>
					<div class="hb-accom-editor" data-bind="html: accom_editor"></div>
					<a data-bind="click: $root.save_accom" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
					<a data-bind="click: $root.cancel_edit_accom" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
				</div>
				<!-- /ko -->
			</td>
			<td>
				<!-- ko if: status() != 'processing' -->
				<div data-bind="visible: ! editing_resa_info()">
					<div data-bind="html: resa_info_html"></div>
					<?php if ( $this->user_can_edit() ) : ?>
					<div><a data-bind="click: $root.edit_resa_info" href="#"><?php esc_html_e( 'Edit', 'hbook-admin' ); ?></a></div>
					<?php endif; ?>
				</div>
				<div data-bind="visible: editing_resa_info()">
					<?php esc_html_e( 'Adults:', 'hbook-admin' ); ?>
					<br/>
					<input class="hb-input-edit-resa" data-bind="value: adults_tmp" type="text" />
					<?php esc_html_e( 'Children:', 'hbook-admin' ); ?>
					<br/>
					<input class="hb-input-edit-resa" data-bind="value: children_tmp" type="text" />
					<div data-bind="html: additional_info_editing_markup"></div>
					<a data-bind="click: $root.save_resa_info, visible: ! saving_resa_info()" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
					<input type="button" disabled data-bind="visible: saving_resa_info()" href="#" class="button-primary" value="<?php esc_attr_e( 'Saving', 'hbook-admin' ); ?>" />
					<a data-bind="click: $root.cancel_edit_resa_info" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
				</div>
				<!-- /ko -->
			</td>
			<td>
				<!-- ko if: status() != 'processing' -->
				<div data-bind="visible: ! editing_comment()">
					<div data-bind="html: admin_comment_html()"></div>
					<?php if ( $this->user_can_edit() ) : ?>
					<div><a data-bind="visible: admin_comment() == '', click: $root.edit_comment" href="#"><?php esc_html_e( 'Add a comment', 'hbook-admin' ); ?></a></div>
					<div><a data-bind="visible: admin_comment() != '', click: $root.edit_comment" href="#"><?php esc_html_e( 'Edit', 'hbook-admin' ); ?></a></div>
					<?php endif; ?>
				</div>
				<div data-bind="visible: editing_comment()">
					<textarea data-bind="value: admin_comment_tmp" rows="6" class="widefat"></textarea><br/>
					<a data-bind="click: $root.save_comment, visible: ! saving_comment()" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
					<input type="button" disabled data-bind="visible: saving_comment()" href="#" class="button-primary" value="<?php esc_attr_e( 'Saving', 'hbook-admin' ); ?>" />
					<a data-bind="click: $root.cancel_edit_comment" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
				</div>
				<!-- /ko -->
			</td>
			<td>
				<!-- ko if: status() != 'processing' && customer_id() != 0 -->
				<div data-bind="visible: ! editing_customer(), html: customer_info_markup"></div>
				<?php if ( $this->user_can_edit() ) : ?>
				<a data-bind="visible: ! editing_customer(), click: $root.edit_customer" href="#"><?php esc_html_e( 'Edit', 'hbook-admin' ); ?></a>
				<?php endif; ?>
				<div data-bind="visible: editing_customer()">
					<div class="hb-customer-edit-wrapper">
						<h4><?php esc_html_e( 'Customer details', 'hbook-admin' ) ?></h4>
						<a data-bind="click: $root.save_customer, visible: ! saving_customer()" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
						<input type="button" disabled data-bind="visible: saving_customer()" href="#" class="button-primary" value="<?php esc_attr_e( 'Saving', 'hbook-admin' ); ?>" />
						<a data-bind="click: $root.cancel_edit_customer" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
						<hr/>
						<div data-bind="html: customer_info_editing_markup"></div>
						<hr/>
						<a data-bind="click: $root.save_customer, visible: ! saving_customer()" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
						<input type="button" disabled data-bind="visible: saving_customer()" href="#" class="button-primary" value="<?php esc_attr_e( 'Saving', 'hbook-admin' ); ?>" />
						<a data-bind="click: $root.cancel_edit_customer" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
					</div>
				</div>
				<!-- /ko -->
				<!-- ko if: status() != 'processing' && customer_id() == 0 -->
				<a data-bind="click: $root.create_customer, visible: ! creating_customer()" href="#"><?php esc_html_e( 'Create customer', 'hbook-admin' ); ?></a>
				<div data-bind="visible: creating_customer()"><?php esc_html_e( 'Creating customer...', 'hbook-admin' ); ?></div>
				<!-- /ko -->
			</td>
			<td>
				
				<div data-bind="html: price_markup"></div>
				
				<!-- ko if: status() != 'processing' -->
				
				<div data-bind="html: price_status"></div>
				
				<!-- ko if: paid() != -1 -->
				<?php if ( $this->user_can_edit() ) : ?>
				<a data-bind="click: $root.mark_paid, visible: paid() != price() && ! marking_paid()" href="#"><?php esc_html_e( 'Mark as paid', 'hbook-admin' ); ?></a>
				<b data-bind="visible: marking_paid()"><?php esc_html_e( 'Marking as paid...', 'hbook-admin' ); ?></b>
				<?php endif; ?>
				
				<div class="hb-to-be-paid-details">
					<div data-bind="visible: ! editing_paid()">
						<div data-bind="visible: paid() != 0 && paid() != price(), html: price_details"></div>
						<?php if ( $this->user_can_edit() ) : ?>
						<a data-bind="click: $root.edit_paid" href="#"><?php esc_html_e( 'Edit payment', 'hbook-admin' ); ?></a>
						<?php endif; ?>
					</div>
					<div data-bind="visible: editing_paid()">
						<?php esc_html_e( 'Price:', 'hbook-admin' ); ?>
						<br/>
						<input class="hb-input-edit-resa" data-bind="value: price_tmp" type="text" />
						<?php esc_html_e( 'Paid:', 'hbook-admin' ); ?>
						<br/>
						<input class="hb-input-edit-resa" data-bind="value: paid_tmp" type="text" />
						<a data-bind="click: $root.save_paid, visible: ! saving_paid()" href="#" class="button-primary"><?php esc_html_e( 'Save', 'hbook-admin' ); ?></a>
						<input type="button" disabled data-bind="visible: saving_paid()" href="#" class="button-primary" value="<?php esc_attr_e( 'Saving', 'hbook-admin' ); ?>" />
						<a data-bind="click: $root.cancel_edit_paid" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
					</div>
				</div>
				<div class="hb-charge-details">
					<div data-bind="visible: charge_action_visible"><a data-bind="click: $root.edit_charge" href="#"><?php esc_html_e( 'Charge', 'hbook-admin' ); ?></a></div>
					<div data-bind="visible: editing_charge()">
						<?php esc_html_e( 'Charge amount:', 'hbook-admin' ); ?>
						<br/>
						<input class="hb-input-edit-resa" data-bind="value: charge_amount" type="text" />
						<a data-bind="click: $root.charge, visible: ! charging()" href="#" class="button-primary"><?php esc_html_e( 'Charge', 'hbook-admin' ); ?></a>
						<input type="button" disabled data-bind="visible: charging()" href="#" class="button-primary" value="<?php esc_attr_e( 'Charging', 'hbook-admin' ); ?>" />
						<a data-bind="click: $root.cancel_edit_charge" href="#" class="button"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
					</div>
				</div>
				<!-- /ko -->
				
				<!-- /ko -->
				
			</td>
			<td data-bind="text: received_on"></td>
			<td class="hb-resa-actions-column">
				<?php if ( $this->user_can_edit() ) : ?>
				<!-- ko if: status() != 'processing' -->
				<a href="#" title="<?php esc_attr_e( 'Confirm', 'hbook-admin' ); ?>" class="dashicons dashicons-yes" data-bind="click: $root.mark_read_resa, visible: ! action_processing() && status() == 'new'"></a>
				<a href="#" title="<?php esc_attr_e( 'Confirm', 'hbook-admin' ); ?>" class="dashicons dashicons-yes" data-bind="click: $root.confirm_resa, visible: ! action_processing() && status() == 'pending'"></a>
				<a href="#" title="<?php esc_attr_e( 'Cancel', 'hbook-admin' ); ?>" class="dashicons dashicons-no" data-bind="click: $root.cancel_resa, visible: ! action_processing() && status() != 'cancelled'"></a>
				<a href="#" title="<?php esc_attr_e( 'Delete', 'hbook-admin' ); ?>" class="dashicons dashicons-trash" data-bind="click: $root.delete_resa, visible: ! action_processing()"></a>
				<span data-bind="visible: updating" class="hb-ajaxing hb-resa-updating">
					<span class="spinner"></span>
					<span><?php esc_html_e( 'Processing...', 'hbook-admin' ); ?></span>
				</span>
				<span data-bind="visible: deleting" class="hb-ajaxing hb-resa-updating">
					<span class="spinner"></span>
					<span><?php esc_html_e( 'Deleting...', 'hbook-admin' ); ?></span>
				</span>
				<!-- /ko -->
				<?php endif ?>
			</td>
		</tr>
	
	<?php
	}

	private function display_resa_pagination() {
	?>
	
	<!-- ko if: resa_total_pages() > 1 -->
	<p>
		<a href="#" class="button" data-bind="click: resa_first_page">&laquo;</a>
		<a href="#" class="button" data-bind="click: resa_previous_page">&lsaquo;</a>
		&nbsp;&nbsp;
		<?php 
		printf(
			esc_html__( 'Viewing page %s of %s', 'hbook-admin' ),
			'<span data-bind="text: resa_current_page_number"></span>',
			'<span data-bind="text: resa_total_pages"></span>'
		); 
		?>
		&nbsp;&nbsp;
		<a href="#" class="button" data-bind="click: resa_next_page">&rsaquo;</a>
		<a href="#" class="button" data-bind="click: resa_last_page">&raquo;</a>
	</p>
	<!-- /ko -->
	
	<?php
	}
	
	private function user_can_edit() {
		if ( current_user_can( 'manage_resa' ) || current_user_can( 'manage_options' ) ) {
			return true;
		} else {
			return false;
		}
	}
	
}