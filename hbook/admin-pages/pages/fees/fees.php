<?php
class HbAdminPageFees extends HbAdminPage {
	
	private $accom;
	
	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$this->accom = $hbdb->get_all_accom();
		$this->data = array(
			'hb_text' => array(
				'new_fee' => esc_html__( 'New fee', 'hbook-admin' ),
                'invalid_amount' => esc_html__( 'Invalid amount.', 'hbook-admin' ),
                'adults' => esc_html__( 'Adults:', 'hbook-admin' ),
                'children' => esc_html__( 'Children:', 'hbook-admin' ),
			),
			'fees' => $hbdb->get_all_fees(),
			'accom_list' => $this->accom,
			'hb_apply_to_types' => array_merge( 
                $this->get_apply_to_types(), 
                array( 
                    array(
                        'option_value' => 'accom-percentage',
                        'option_text' => esc_html__( 'Per accommodation (percentage)', 'hbook-admin' )
                    ),
                    array(
                        'option_value' => 'global-fixed',
                        'option_text' => esc_html__( 'Global (fixed)', 'hbook-admin' )
                    ),
                    array(
                        'option_value' => 'global-percentage',
                        'option_text' => esc_html__( 'Global (percentage)', 'hbook-admin' )
                    )
                )
			),
            'hb_price_precision' => get_option( 'hb_price_precision' ),
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );
	}
	
	public function display() {
	?>

	<div class="wrap">

		<h2>
			<?php esc_html_e( 'Fees', 'hbook-admin' ); ?>
            <a href="#" class="add-new-h2" data-bind="click: create_fee"><?php esc_html_e( 'Add new fee', 'hbook-admin' ); ?></a>
            <span class="hb-add-new spinner"></span>
		</h2>
        
        <?php $this->display_right_menu(); ?>

        <br/>
        
        <!-- ko if: fees().length == 0 -->
        <?php esc_html_e( 'No fees have been created yet.', 'hbook-admin' ); ?>
        <!-- /ko -->

        <!-- ko if: fees().length > 0 -->
        <div class="hb-table hb-fees-table">

            <div class="hb-table-head hb-clearfix">
                <div class="hb-table-head-data"><?php esc_html_e( 'Fee name', 'hbook-admin' ); ?></div>
                <div class="hb-table-head-data"><?php esc_html_e( 'Type', 'hbook-admin' ); ?></div>
                <div class="hb-table-head-data"><?php esc_html_e( 'Amount', 'hbook-admin' ); ?></div>
                <div class="hb-table-head-data"><?php esc_html_e( 'Accommodation', 'hbook-admin' ); ?></div>
                <div class="hb-table-head-data hb-table-head-data-action"><?php esc_html_e( 'Actions', 'hbook-admin' ); ?></div>
            </div>

            <div data-bind="template: { name: template_to_use, foreach: fees, beforeRemove: hide_setting }"></div>

        </div>
        <!-- /ko -->

        <script id="text_tmpl" type="text/html">
            <div class="hb-table-row hb-clearfix">
                <div class="hb-table-data" data-bind="text: name"></div>
                <div class="hb-table-data" data-bind="text: apply_to_type_text"></div>
                <div class="hb-table-data" data-bind="html: amount_text"></div>
                <div data-bind="visible: ! global(), text: accom_list" class="hb-table-data"></div>
                <div data-bind="visible: global()" class="hb-table-data">-</div>
                <div class="hb-table-data hb-table-data-action"><?php $this->display_admin_action(); ?></div>
            </div>
        </script>

        <script id="edit_tmpl" type="text/html">
            <div class="hb-table-row hb-clearfix">
                <div class="hb-table-data"><input data-bind="value: name" type="text" /></div>
                <div class="hb-table-data"><select data-bind="options: hb_apply_to_types, optionsValue: 'option_value', optionsText: 'option_text', value: apply_to_type"></select></div>
                <div class="hb-table-data">
                    <!-- ko if: apply_to_type() == 'per-person' || apply_to_type() == 'per-person-per-day' -->
                    <?php esc_html_e( 'Adults:', 'hbook-admin' ); ?><br/>
                    <!-- /ko -->
                    <input data-bind="value: amount" type="text" size="5" /><br/>
                    <!-- ko if: apply_to_type() == 'per-person' || apply_to_type() == 'per-person-per-day' -->
                    <?php esc_html_e( 'Children:', 'hbook-admin' ); ?><br/>
                    <input data-bind="value: amount_children" type="text" size="5" />
                    <!-- /ko -->
                </div>
                <div data-bind="visible: ! global()" class="hb-table-data"><?php $this->display_checkbox_list( $this->accom, 'accom' ); ?></div>
                <div data-bind="visible: global()" class="hb-table-data">-</div>
                <div class="hb-table-data hb-table-data-action"><?php $this->display_admin_on_edit_action(); ?></div>				
            </div>
        </script>
                
	</div><!-- end .wrap -->

	<?php	
	}

}