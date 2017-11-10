<?php 
class HbAdminPageReservationsExport {
	
	private $utils;
	
	public function __construct( $utils ) {
		$this->utils = $utils;
	}
	
	public function display() {
	?>
	
	<hr/>
	
	<h3 id="hb-export-resa-toggle" class="hb-resa-section-toggle">
		<?php esc_html_e( 'Export reservations', 'hbook-admin' ); ?>
		<span class="dashicons dashicons-arrow-down"></span>
		<span class="dashicons dashicons-arrow-up"></span>
	</h3>
			
	<div id="hb-export-resa" class="stuffbox">
		
		<form id="hb-export-resa-form" method="POST">
		
			<h4><?php esc_html_e( 'Select reservations to be exported:', 'hbook-admin' ); ?></h4>
			
			<p>
				<input id="hb-export-resa-selection-all" name="hb-export-resa-selection" type="radio" value="all" checked />
				<label for="hb-export-resa-selection-all"><?php esc_html_e( 'All', 'hbook-admin' ); ?><br/>
					
				<input id="hb-export-resa-selection-received-date" name="hb-export-resa-selection" type="radio" value="received-date" />
				<label for="hb-export-resa-selection-received-date"><?php esc_html_e( 'Received between', 'hbook-admin' ); ?></label>
				<input id="hb-export-resa-selection-received-date-from" name="hb-export-resa-selection-received-date-from" class="hb-input-date hb-export-resa-date" type="text" />  
				<?php esc_html_e( 'and', 'hbook-admin' ); ?>  
				<input id="hb-export-resa-selection-received-date-to" name="hb-export-resa-selection-received-date-to" class="hb-input-date hb-export-resa-date" type="text" /><br/>
				
				<input id="hb-export-resa-selection-check-in-date" name="hb-export-resa-selection" type="radio" value="check-in-date" />
				<label for="hb-export-resa-selection-check-in-date"><?php esc_html_e( 'Check-in between', 'hbook-admin' ); ?></label>
				<input id="hb-export-resa-selection-check-in-date-from" name="hb-export-resa-selection-check-in-date-from" class="hb-input-date hb-export-resa-date" type="text" />  
				<?php esc_html_e( 'and', 'hbook-admin' ); ?>  
				<input id="hb-export-resa-selection-check-in-date-to" name="hb-export-resa-selection-check-in-date-to" class="hb-input-date hb-export-resa-date" type="text" />
			
			</p>
				
			<h4><?php esc_html_e( 'Select data to be exported:', 'hbook-admin' ); ?></h4>
			
			<p>
				<a id="hb-export-resa-select-all" href="#"><?php esc_html_e( 'Select all', 'hbook-admin' ); ?></a> - 
				<a id="hb-export-resa-unselect-all" href="#"><?php esc_html_e( 'Unselect all', 'hbook-admin' ); ?></a>
			</p>
			
			<?php 
			$exportable_resa_fields = $this->utils->get_exportable_resa_fields();
			$exportable_additional_info_fields = $this->utils->get_exportable_additional_info_fields();
			$exportable_customer_fields = $this->utils->get_exportable_customer_fields();
			$exportable_extra_services_fields = $this->utils->get_exportable_extra_services_fields();
				
			$exportable_fields = array_merge( 
				$exportable_resa_fields,
				$exportable_additional_info_fields,
				$exportable_extra_services_fields,
				array( 'customer_info' => 'customer_info' ),
				$exportable_customer_fields
			);
			foreach ( $exportable_fields as $field_id => $field_name ) : 
				if ( $field_id == 'customer_info' ) :
				?>
					
					<p><b><?php esc_html_e( 'Customer information:', 'hbook-admin' );?></b></p>
					
				<?php else : ?>
				   
					<p>
						<input id="hb-resa-data-export-<?php echo( $field_id ); ?>" type="checkbox" name="hb-resa-data-export[]" value="<?php echo( $field_id ); ?>">
						<label for="hb-resa-data-export-<?php echo( $field_id ); ?>"><?php echo( $field_name ); ?></label>
					</p>
				
				<?php 
				endif;
			endforeach; 
			?>
		
			<p>
				<a href="#" id="hb-export-resa-download" class="button"><?php esc_html_e( 'Download file', 'hbook-admin' ); ?></a>
				&nbsp;&nbsp;
				<a href="#" id="hb-export-resa-cancel"><?php esc_html_e( 'Cancel', 'hbook-admin' ); ?></a>
			</p>
			
			<input type="hidden" name="hb-import-export-action" value="export-resa" />
			<?php wp_nonce_field( 'hb_import_export', 'hb_import_export' ); ?>
			
		</form>
		
	</div>
	
	<hr/>
	
	<?php 
	}
}