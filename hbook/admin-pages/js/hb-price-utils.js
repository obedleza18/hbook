function hb_is_valid_price( price ) {
    if ( price != parseFloat( price ) ) {
        alert( hb_text.invalid_amount );
        return false;
    } else {
        return true;
    }
}

function hb_format_price( price ) {
    if ( hb_price_precision == 'no_decimals' ) {
        price = parseFloat( price ).toFixed( 0 );
    } else {
        price = parseFloat( price ).toFixed( 2 );
    }
    price = price.replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
    if ( hb_currency_pos == 'before' ) {
        price = hb_currency_symbol + price;
    } else {
        price = price + hb_currency_symbol;
    }
    return price;
}
