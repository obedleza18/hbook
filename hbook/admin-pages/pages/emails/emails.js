function EmailTmpl( brand_new, id, name, to_address, reply_to_address, from_address, subject, message, format, lang, action ) {
	HbSetting.call(this, brand_new, 'email_template', id, name);
    
    this.to_address = ko.observable( to_address );
    this.reply_to_address = ko.observable( reply_to_address );
	this.from_address = ko.observable( from_address );
	this.subject = ko.observable( subject );
	this.message = ko.observable( message );
	this.format = ko.observable( format );
	this.lang = ko.observable( lang );
	this.action = ko.observable( action );
    
    var self = this;
    
	this.to_address_html = ko.computed( function() {
		var to_address = self.to_address();

		if ( to_address == '' ) {
			return '';
		}
		if ( to_address.indexOf( ';' ) != -1 ) {
			return to_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + ' <b>' + hb_text.invalid_email_address + ' ' + hb_text.invalid_multiple_to_address + '</b>';
		}
		
		to_address = to_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
		
		var nb_at = to_address.split( '@' ).length - 1,
			nb_bracket = to_address.split( '[' ).length - 1,
			nb_comma = to_address.split( ',' ).length - 1;
		
		if ( nb_bracket == 0 && nb_at == 0 ) {
			return to_address + ' <b>' + hb_text.invalid_email_address + '</b>';
		}
		if ( nb_at > 1 && nb_comma != nb_at - 1 ) {
			return to_address + ' <b>' + hb_text.invalid_email_address + ' ' + hb_text.invalid_multiple_to_address + '</b>';
		}
		return to_address;
	});
	
	this.reply_to_address_html = ko.computed( function() {
		var reply_to_address = self.reply_to_address();
		
		if ( reply_to_address == '' ) {
			return '';
		} else if ( reply_to_address.indexOf( '<' ) == -1 || reply_to_address.indexOf( '>' ) == -1 ) {
			var error_text;
			error_text = hb_text.invalid_complete_address.replace( '<', '&lt;' );
			error_text = error_text.replace( '>', '&gt;' );
			return reply_to_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + ' <b>' + hb_text.invalid_email_address + ' ' + error_text + '</b>';
		} else if ( ( reply_to_address.indexOf( '[' ) < 0 ) && ( reply_to_address.indexOf( '@' ) == -1 ) ) {
			return reply_to_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + ' <b>' + hb_text.invalid_email_address + '</b>';
		}
		return reply_to_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	});
	
	this.from_address_html = ko.computed( function() {
		var from_address = self.from_address();
		
		if ( from_address == '' ) {
			return '';
		} else if ( from_address.indexOf( '<' ) == -1 || from_address.indexOf( '>' ) == -1 ) {
			var error_text;
			error_text = hb_text.invalid_complete_address.replace( '<', '&lt;' );
			error_text = error_text.replace( '>', '&gt;' );
			return from_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + ' <b>' + hb_text.invalid_email_address + ' ' + error_text + '</b>';
		} else if ( ( from_address.indexOf( '[' ) < 0 ) && ( from_address.indexOf( '@' ) == -1 ) ) {
			return from_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ) + ' <b>' + hb_text.invalid_email_address + '</b>';
		}
		return from_address.replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	});
	
    this.message_html = ko.computed( function() {
        var msg = self.message().replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
        if ( msg.length > 50 ) {
            msg = msg.substr( 0, 50 ) + '<b>...</b>';
        }
        msg = msg.replace( /(?:\r\n|\r|\n)/g, '<br/>' );
        return msg;
    });
    
    this.action_text = ko.computed( function() {
		for ( var i = 0; i < hb_email_actions.length; i++ ) {
			if ( hb_email_actions[i]['action_value'] == self.action() ) {
				return hb_email_actions[i]['action_text'];
			}
		}
	});
    
    this.lang_text = ko.computed( function() {
		for ( var i = 0; i < hb_email_langs.length; i++ ) {
			if ( hb_email_langs[i]['lang_value'] == self.lang() ) {
				return hb_email_langs[i]['lang_name'];
			}
		}
	});
    
    this.revert = function( email_tmpl ) {
        if ( email_tmpl ) {
            self.name( email_tmpl.name );
            self.to_address( email_tmpl.to_address );
            self.from_address( email_tmpl.from_address );
            self.subject( email_tmpl.subject );
            self.message( email_tmpl.message );
            self.format( email_tmpl.format );
            self.lang( email_tmpl.lang );
            self.action( email_tmpl.action );
        }
    }
}

function EmailTmplViewModel() {
	var self = this;
	
	observable_email_tmpls = [];
	for ( var i = 0; i < email_tmpls.length; i++ ) {
		observable_email_tmpls.push( 
            new EmailTmpl( 
                false, 
                email_tmpls[i].id, 
                email_tmpls[i].name, 
                email_tmpls[i].to_address, 
                email_tmpls[i].reply_to_address, 
                email_tmpls[i].from_address, 
                email_tmpls[i].subject, 
                email_tmpls[i].message, 
                email_tmpls[i].format, 
                email_tmpls[i].lang, 
                email_tmpls[i].action 
            ) 
        );
	}
	
    this.email_tmpls = ko.observableArray( observable_email_tmpls );
    
	ko.utils.extend( this, new HbSettings() );

    this.create_email_tmpl = function() {
                                //EmailTmpl( brand_new, id, name, to_address, reply_to_address, from_address, subject, message, format, lang, action );
        var new_email_tmpl = new EmailTmpl( true, 0, hb_text.new_email_tmpl, '', '', '', '', '', 'TEXT', 'all', 'new_resa' );
		self.create_setting( new_email_tmpl, function( new_email_tmpl ) {
			self.email_tmpls.push( new_email_tmpl );
		});
	}
    
    this.remove = function( setting ) {
		callback_function = function() {
			self.email_tmpls.remove( setting );
		}
		self.delete_setting( setting, callback_function );
	}
}

ko.applyBindings( new EmailTmplViewModel() );