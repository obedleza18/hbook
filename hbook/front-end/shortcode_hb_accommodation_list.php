<?php
function hb_accommodation_list( $atts, $plugin_version, $hbdb, $utils ) {

	$atts = shortcode_atts(
		array(
			'show_thumbnail' => 'yes',
			'thumbnail_link' => 'yes',
			'title_tag' => 'h2',
            'thumb_width' => 150,
            'thumb_height' => 150,
		), 
		$atts, 
		'hb_accommodation_list' 
	);
	
	$output = '<div class="hb-accom-list-shortcode-wrapper">';
	
	$accom = $hbdb->get_all_accom_ids();
	$i = 1;
	foreach( $accom as $accom_id ) {
		$output .= '<div class="hb-accom-list-item hb-accom-list-item-' . $accom_id . '">';
		if ( $atts['show_thumbnail'] == 'yes' ) {
			$thumb_mark_up = $utils->get_thumb_mark_up( $accom_id, $atts['thumb_width'], $atts['thumb_height'], 'hb-accom-list-thumb alignleft' );
			if ( $thumb_mark_up && $atts['thumbnail_link'] == 'yes' ) {
				$output .= '<a class="hb-thumbnail-link" href="' . $utils->get_accom_link( $accom_id ) . '">' . $thumb_mark_up . '</a>';
			} else {
				$output .= $thumb_mark_up;
			}
		}
		$output .= '<' . $atts['title_tag'] . '><a href="' . $utils->get_accom_link( $accom_id ) . '">' . $utils->get_accom_title ( $accom_id ) . '</a></' . $atts['title_tag'] . '>';
		$starting_price = get_post_meta( $accom_id, 'accom_starting_price', true );
		if ( $starting_price ) {
			$starting_price_text = str_replace( '%price', $utils->price_with_symbol( $starting_price ), $hbdb->get_string( 'accom_starting_price' ) );
			$starting_price_text .= ' ' . $hbdb->get_string( 'accom_starting_price_duration_unit' );
			$output .=  '<p><small>' . $starting_price_text . '</small></p>';
		}
		$output .= '<p>' . $utils->get_accom_list_desc( $accom_id ) . '</p>';
		$output .= '</div>';
		$output .= '<div class="hb-clearfix" /></div>';
		if ( $i != count( $accom ) ) {
			$output .= '<hr/>';
		}
		$i++;
	}
	
	$output .= '</div>';
	
	$output = apply_filters( 'hb_accommodation_list_markup', $output );
	
	return $output;
}