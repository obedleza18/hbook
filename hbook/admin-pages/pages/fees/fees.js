function Fee( brand_new, id, name, amount, amount_children, apply_to_type, accom, all_accom, global ) {
	if ( amount != '' && amount % 1 == 0 ) {
		amount = parseFloat( amount ).toFixed( 0 );
	}
    if ( amount_children != '' && amount_children % 1 == 0 ) {
		amount_children = parseFloat( amount_children ).toFixed( 0 );
	}
    OptionsAndFees.call( this, brand_new, 'fee', id, name, amount, amount_children, apply_to_type, accom, all_accom );

    this.global = ko.observable();
    if ( typeof global == 'string' ) { // wp-localize-script turns all values to string
		global = parseInt( global );
	}
	if ( global ) {
		this.global( true );
	} else {
		this.global( false );
	}
    
    this.apply_to_type.subscribe( function( new_value ) {
        if ( new_value == 'global-percentage' || new_value == 'global-fixed' ) {
            this.global( true );
        } else {
            this.global( false );
        }
    }, this);
    
	var self = this;
	
	this.revert = function( fee ) {
		if ( fee ) {
			self.name( fee.name );
			self.amount( fee.amount );
			self.amount_children( fee.amount_children );
			self.apply_to_type( fee.apply_to_type );
			self.accom( fee.accom );
			self.all_accom( fee.all_accom );
			self.global( fee.global );
		}
	}
}

function FeesViewModel() {
	
	var self = this;
	observable_fees = [];
	for ( var i = 0; i < fees.length; i++ ) {
		observable_fees.push( 
			new Fee( 
				false, 
				fees[i].id, 
				fees[i].name, 
				fees[i].amount, 
				fees[i].amount_children, 
				fees[i].apply_to_type, 
				fees[i].accom,
				fees[i].all_accom,
                fees[i].global
			) 
		);
	}
	
	ko.utils.extend( this, new HbSettings() );
	
	this.fees = ko.observableArray( observable_fees );
	
	this.create_fee = function() {
		var new_fee = new Fee( true, 0, hb_text.new_fee, 0, 0, 'per-person', '', true );
		self.create_setting( new_fee, function( new_fee ) {
			self.fees.push( new_fee );
		});
	}
	
	this.remove = function( fee ) {
		callback_function = function() {
			self.fees.remove( fee );
		}
		self.delete_setting( fee, callback_function );
	}
	
}

ko.applyBindings( new FeesViewModel() );