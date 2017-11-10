<?php
function hb_rates( $atts, $plugin_version, $hbdb, $utils ) {
	
	$atts = shortcode_atts(
		array(
			'accom_id' => '',
			'type' => 'normal', // 'normal', 'adult', 'child'
			'days' => '',
			'season' => '',
			'rule' => '',
			'show_global_price' => 'no',
			'nights' => '0',
			'custom_text_after_amount' => ''
		), 
		$atts, 
		'hb_rates' 
	);
	
	$type = 'accom';
	if ( $atts['type'] == 'adult' ) {
		$type = 'extra_adults';
	} else if ( $atts['type'] == 'child' ) {
		$type = 'extra_children';
	}
	
	$utils->load_datepicker();
	
	wp_enqueue_script( 'hb-rates-script', plugin_dir_url( __FILE__ ) . 'js/rates.js', array( 'jquery' ), $plugin_version, true );	
	
	$accom_id = $atts['accom_id'];
	if ( $accom_id == '' ) {
		$accom_id = $utils->get_default_lang_post_id( get_the_ID() );
	}
	$all_linked_accom = $hbdb->get_all_linked_accom();
	if ( isset( $all_linked_accom[ $accom_id ] ) ) {
		$accom_id = $all_linked_accom[ $accom_id ];
	}
	$all_accom = $hbdb->get_all_accom_ids();
	if ( ! in_array( $accom_id, $all_accom ) ) {
        if ( $atts['accom_id'] == '' ) {
            return esc_html__( 'Invalid shortcode. Use: [hb_rates accom_id="ID"]', 'hbook-admin' );
        } else if ( get_post_type( $accom_id ) == 'hb_accommodation' ) {
            return esc_html__( 'Invalid shortcode. Please use the id of an accommodation which is set in the website default language.', 'hbook-admin' );
        } else {
            return sprintf( esc_html__( 'Invalid shortcode. Could not find an accommodation whose id is %s.', 'hbook-admin' ), $accom_id );
        }
	}
	
	$rule = 0;
	if ( $atts['rule'] != '' ) {
		$rule = $hbdb->get_rule_by_name( $atts['rule'] );
		if ( ! $rule ) {
			return 'Invalid shortcode. The rule "' . $atts['rule'] . '" does not exist.';
		}
	}
	
	if ( $atts['season'] != '' ) {
		$seasons = $hbdb->get_season_by_name( $atts['season'] );
		if ( ! $seasons ) {
			return 'Invalid shortcode. The season "' . $atts['season'] . '" does not exist.';
		}
		$seasons_dates = $hbdb->get_all_season_dates( $seasons[0]['id'] );
	} else {
		$seasons = $hbdb->get_all( 'seasons' );
		$seasons_dates = $hbdb->get_all( 'seasons_dates' );
	}
	
	$price_per_night = false;
	$width = '33%';
	if ( $atts['days'] == '' ) {
		foreach ( $seasons_dates as $dates ) {
			if ( $dates['days'] != '0,1,2,3,4,5,6' ) {
				$price_per_night = true;
				$width = '25%';
				break;
			}
		}
	}
	
	$output = '
		<table class="hb-rates-table">
			<thead>
				<tr>
					<th width="' . $width . '">' . $hbdb->get_string( 'table_rates_from' ) . '</th>
					<th width="' . $width . '">' . $hbdb->get_string( 'table_rates_to' ) . '</th>';
		if ( $price_per_night ) {
			$output .= '
					<th width="' . $width . '">' . $hbdb->get_string( 'table_rates_nights' ) . '</th>';
		}
		$output .= '
					<th width="' . $width . '">' . $hbdb->get_string( 'table_rates_price' ) . '</th>
				</tr>
			</thead>';
	
	if ( $atts['days'] != '' ) {
		$days = explode( ',', $atts['days'] );
	} else {
		$days = false;
	}
	$output .= '<tbody>';
	foreach ( $seasons as $season ) {
		$season_dates = $hbdb->get_all_season_dates( $season['id'] );
		$output_dates = array();
		if ( $days ) {
			foreach( $season_dates as $dates ) {
				$dates_days = explode( ',', $dates['days'] );
				$tmp = array_intersect( $dates_days, $days );
				if ( ! empty( $tmp ) ) {
					$output_dates[] = $dates;
				}
			}
		} else {
			$output_dates = $season_dates;
		}
		foreach ( $output_dates as $j => $dates ) {
			$output .= '
				<tr>
					<td class="hb-format-date">' . $dates['start_date'] . '</td>
					<td class="hb-format-date">' . $dates['end_date'] . '</td>';
			if ( $price_per_night ) {
				if ( $dates['days'] == '0,1,2,3,4,5,6' ) {
					$output .= '
						<td>' . $hbdb->get_string( 'table_rates_all_nights' ) . '</td>';
				} else {
					$output .= '
						<td class="hb-rate-days">' . $dates['days'] . '</td>';
				}
			}
			if ( $j == 0 ) {
				$output .= '
					<td rowspan="' . count( $output_dates ) . '" class="hb-rate-price">';
				$rate_and_nights = $hbdb->get_rate_and_nights( $type, $rule, $accom_id, $season['id'], $atts['nights'] );
				if ( $rate_and_nights ) {
					if ( $atts['show_global_price'] != 'yes' ) {
						$output .= $utils->price_with_symbol( $rate_and_nights['amount'] / $rate_and_nights['nights'] );
					} else {
						$output .= $utils->price_with_symbol( $rate_and_nights['amount'] );
						$output .= ' ';
						if ( $atts['custom_text_after_amount'] != '' ) {
							$output .= $atts['custom_text_after_amount'];
						} else {
							if ( $rate_and_nights['nights'] > 1 ) {
								$output .= str_replace( '%nb_nights', $rate_and_nights['nights'], $hbdb->get_string( 'table_rates_for_night_stay' ) );
							} else {
								$output .= $hbdb->get_string( 'table_rates_per_night' );
							}
						}
					}
				}
				$output .= 
					'</td>';
			}
			$output .= '
				</tr>';
		}
	}
	$output .= '
			</tbody>
		</table>';
	
	return $output;
}
?>