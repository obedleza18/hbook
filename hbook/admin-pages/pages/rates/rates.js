function Rate( brand_new, id, type, accom, all_accom, seasons, all_seasons, rules, amount, nights ) {

	HbSetting.call( this, brand_new, 'rate', id );
	Accom.call( this, accom, all_accom );
	HbSeasons.call( this, seasons, all_seasons );

	this.rate_type = type;
	this.amount = ko.observable( amount );
	this.nights = ko.observable( nights );
	
	if ( rules ) {
		this.rules = ko.observableArray( rules.split( ',' ) );
	} else {
		this.rules = ko.observableArray();
	}
	
	this.all_rules = ko.observable( false );
	
	var self = this;
	
	this.rules_list = ko.computed( function() {
		if ( self.rules().length == 0 ) {
			return hb_text.no_rules_selected;
		}
		rules_name_list = [];
		reordered_rules = self.rules().sort();
		for ( var i = 0; i < reordered_rules.length; i++ ) {
			rules_name_list[i] = rules_list[reordered_rules[i]]['name'];
		}
		rules_name_list = rules_name_list.join( ', ' );
		if ( rules_name_list == '' ) {
			return hb_text.no_rules_selected;
		} else {
			return rules_name_list;
		}
    }, self );
	
	var all_rules_ids = [];
	for ( var key in rules_list ) {
		all_rules_ids.push( key );
	}
	
	this.select_all_rules = function( rate ) {
		if ( rate ) {
			self.rules.removeAll();
			for ( var i = 0; i < all_rules_ids.length; i++ ) {
				self.rules.push( all_rules_ids[i] );
			}
		} 
	}
	
	this.unselect_all_rules = function( rate ) {
		if ( rate ) {
			self.rules.removeAll();
		} 
	}
	
	this.amount_text =  ko.computed( function() {
		var amount;
		if ( self.amount() ) {
            amount = hb_format_price( self.amount() );
		} else {
			return '';
		}
		if ( self.nights() > 1 ) { 
			amount = amount + ' ' + hb_text.for_nights.replace( '%s', self.nights() );
		} else {
			amount = amount + ' ' + hb_text.per_night;
		}
		return amount;
	});
	
	this.revert = function( rate ) {
		if ( rate ) {
			self.accom( rate.accom );
			self.all_accom( rate.all_accom );
			self.seasons( rate.seasons );
			self.all_seasons( rate.all_seasons );
			self.rules( rate.rules );
			self.amount( rate.amount );
			self.nights( rate.nights );
		}
	}
		
	this.is_valid = function( rate ) {
		if ( rate ) {
            return hb_is_valid_price( rate.amount() );
		}
	}
}

function Discount( brand_new, id, accom, all_accom, seasons, all_seasons, rules, amount, amount_type ) {
	HbSetting.call( this, brand_new, 'discount', id );
	Accom.call( this, accom, all_accom );
	HbSeasons.call( this, seasons, all_seasons );
	
	if ( amount != '' && amount % 1 == 0 ) {
		amount = parseFloat( amount ).toFixed( 0 );
	}
	this.amount = ko.observable( amount );
	this.amount_type = ko.observable( amount_type );

	if ( rules ) {
		this.rules = ko.observableArray( rules.split( ',' ) );
	} else {
		this.rules = ko.observableArray();
	}
	
	this.all_rules = ko.observable( false );
	
	var self = this;
	
	this.rules_list = ko.computed( function() {
		if ( self.rules().length == 0 ) {
			return hb_text.no_discounts_selected;
		}
		rules_name_list = [];
		reordered_rules = self.rules().sort();
		for ( var i = 0; i < reordered_rules.length; i++ ) {
			rules_name_list[i] = discount_rules_list[reordered_rules[i]];
		}
		rules_name_list = rules_name_list.join( ', ' );
		if ( rules_name_list == '' ) {
			return hb_text.no_rules_selected;
		} else {
			return rules_name_list;
		}
    }, self );
	
	var all_rules_ids = [];
	for ( var key in discount_rules_list ) {
		all_rules_ids.push( key );
	}
	
	this.select_all_rules = function( rate ) {
		if ( rate ) {
			self.rules.removeAll();
			for ( var i = 0; i < all_rules_ids.length; i++ ) {
				self.rules.push( all_rules_ids[i] );
			}
		} 
	}
	
	this.unselect_all_rules = function( rate ) {
		if ( rate ) {
			self.rules.removeAll();
		} 
	}
	
	this.amount_text =  ko.computed( function() {
		if ( self.amount() ) {
			if ( self.amount_type() == 'fixed' ) {
				return hb_format_price( self.amount() );
			} else {
				if ( self.amount() % 1 == 0 ) {
					return parseFloat( self.amount() ).toFixed( 0 ) + '%';
				} else {
					return amount_float = parseFloat( self.amount() ).toFixed( 2 ) + '%';
				}
			}
		} else {
			return '';
		}
	});
	
	this.revert = function( discount ) {
		if ( discount ) {
			self.accom( discount.accom );
			self.all_accom( discount.all_accom );
			self.seasons( discount.seasons );
			self.all_seasons( discount.all_seasons );
			self.rules( discount.rules );
			self.amount( discount.amount );
			self.amount_type( discount.amount_type );
		}
	}
		
	this.is_valid = function( discount ) {
		if ( discount ) {
            return hb_is_valid_price( discount.amount() );
		}
	}
}

function RatesViewModel() {
	
	var self = this;
	
	observable_rates = [];
	for ( var i = 0; i < rates.length; i++ ) {
		observable_rates.push( new Rate( false, rates[i].id, rates[i].type, rates[i].accom, rates[i].all_accom, rates[i].seasons, rates[i].all_seasons, rates[i].rules, rates[i].amount, rates[i].nights ) );
	}
	
	observable_discounts = [];
	for ( var i = 0; i < discounts.length; i++ ) {
		observable_discounts.push( new Discount( false, discounts[i].id, discounts[i].accom, discounts[i].all_accom, discounts[i].seasons, discounts[i].all_seasons, discounts[i].rules, discounts[i].amount, discounts[i].amount_type ) );
	}
	
	this.discounts = ko.observableArray( observable_discounts );
	this.rates = ko.observableArray( observable_rates );
	
	ko.utils.extend( this, new HbSettings() );
	
	this.create_rate = function( rate_type ) {
		var css_class = 'hb-add-' + rate_type + '-rate',
			new_rate = new Rate( true, 0, rate_type, '', 0, '', 0, '', '', 1 );
		self.create_setting( new_rate, function( new_rate ) {
			self.rates.push( new_rate );
		}, css_class );
	}

	this.create_discount = function() {
		var new_discount = new Discount( true, 0, '', 0, '', 0, '', '', 'fixed' );
		self.create_setting( new_discount, function( new_discount ) {
			self.discounts.push( new_discount );
		}, 'hb-add-discount' );
	}
	
	this.nb_rates = function( rate_type ) {
		var rates = self.rates(),
			nb_rates = 0;
		for ( var i = 0; i < rates.length; i++ ) {
			if ( rates[i].rate_type == rate_type ) {
				nb_rates++;
			}
		}
		return nb_rates;
	}
	
	this.remove = function( setting ) {
		callback_function = function() {
			if ( setting.type == 'rate' ) {
				self.rates.remove( setting );
			} else {
				self.discounts.remove( setting );
			}
		}
		self.delete_setting( setting, callback_function );
	}
	
}

ko.applyBindings( new RatesViewModel() );