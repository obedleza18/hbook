<?php

function hb_availability( $atts, $plugin_version, $hbdb, $utils ) {



	$atts = shortcode_atts(

		array(

			'accom_id' => '',

			'number_of_months' => '',

            'calendar_sizes' => '2x1, 1x1'

		), 

		$atts,

		'hb_availability' 

	);

	

	$accom_id = $atts['accom_id'];

	if ( $accom_id != 'all' ) {

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

	            return esc_html__( 'Invalid shortcode. Use: [hb_availability accom_id="ID"]', 'hbook-admin' );

	        } else if ( get_post_type( $accom_id ) == 'hb_accommodation' ) {

	            return esc_html__( 'Invalid shortcode. Please use the id of an accommodation which is set in the website default language.', 'hbook-admin' );

	        } else {

	            return sprintf( esc_html__( 'Invalid shortcode. Could not find an accommodation whose id is %s.', 'hbook-admin' ), $accom_id );

	        }

		}

	}

	

    $calendar_sizes_cols = array();

    $calendar_sizes_rows = array();

    if ( $atts['number_of_months'] != '' ) { // Backward compatibility (there was a "number_of_month" parameter)

        $calendar_sizes = explode( ',', $atts['number_of_months'] );

        $re = "/\\(\\s*(\\d+)\\s*.\\s*(\\d+)\\s*\\)/";

        foreach ( $calendar_sizes as $size ) {

            $size = trim( $size );

            if ( is_numeric( $size ) ) {

                $calendar_sizes_cols[] = intval( $size );

                $calendar_sizes_rows[ intval( $size ) ] = 1;

            } else {

                $matches = array();

                preg_match( $re, $size, $matches );

                if ( sizeof( $matches ) == 3 && is_numeric( $matches[1] ) && is_numeric( $matches[2] ) ) {

                    $calendar_sizes_cols[] = intval( $matches[1] );

                    $calendar_sizes_rows[ intval( $matches[1] ) ] = $matches[2];

                } else {

                    return 'Incorrect value for the "number_of_months" parameter.';

                }

            }

        }

    } else {

        $calendar_sizes = explode( ',', $atts['calendar_sizes'] );

        foreach ( $calendar_sizes as $size ) {

            $size = trim( $size );

            $cols_rows = explode( 'x', $size );

            $calendar_sizes_cols[] = intval( $cols_rows[0] );

            $calendar_sizes_rows[ intval( $cols_rows[0] ) ] = intval( $cols_rows[1] );

        }

    }

	rsort( $calendar_sizes_cols );

	$calendar_sizes = array();

	foreach ( $calendar_sizes_cols as $col ) {

		$calendar_sizes[] = array(

			'cols' => $col,

			'rows' => $calendar_sizes_rows[ $col ]

		);

	}

	

	$utils->load_datepicker();

	wp_enqueue_script( 'hb-availability-script', plugin_dir_url( __FILE__ ) . 'js/availability.js', array( 'jquery' ), $plugin_version.'1.1.2', true );

	$availability_text = array(

		'legend_past' => $hbdb->get_string( 'legend_past' ),

		'legend_closed' => $hbdb->get_string( 'legend_closed' ),

		'legend_occupied' => $hbdb->get_string( 'legend_occupied' ),

		'legend_check_out_only' => $hbdb->get_string( 'legend_check_out_only' ),

		'legend_check_in_only' => $hbdb->get_string( 'legend_check_in_only' ),

		'legend_available' => $hbdb->get_string( 'legend_available' ),

	);

	wp_localize_script( 'hb-availability-script', 'hb_availability_text', $availability_text );

	$on_click_refresh = apply_filters( 'hb_availability_on_click_refresh', array() );

	wp_localize_script( 'hb-availability-script', 'hb_availability_on_click_refresh', $on_click_refresh );

	

	$status_days = $utils->get_status_days( $accom_id, 0 );

	/*** COLORING PENDING DAYS ***/
	$output = '';
	$dates = [];
	$resa = $hbdb->get_all_resa_by_date();
	foreach ($resa as $key => $value) {
		if ($value['status'] != 'pending') {
			unset($resa[$key]);
		}
		
	}
	foreach ($resa as $key => $value) {
		$dates[] = $value['check_in'];
		$dates[] = $value['check_out'];
	}

	function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

	    $dates = array();
	    $current = strtotime($first);
	    $last = strtotime($last);

	    while( $current <= $last ) {

	        $dates[] = date($output_format, $current);
	        $current = strtotime($step, $current);
	    }

	    return $dates;
	}
	$pending_days = date_range(min($dates), max($dates));
	$output .= '<script>
	var pending_days = [];';
	foreach ($pending_days as $pending_day) {
		$output .= 'pending_days.push("'.$pending_day.'");';
	}
	$output .= '</script>';
	/*** END COLORING PENDING DAYS ***/

	$output .= '' . 

        '<div class="hb-availability-calendar-wrapper">' .

            '<div ' .

                'class="hb-availability-calendar" ' .

                "data-calendar-sizes='" . json_encode( $calendar_sizes ) . "'" .

                "data-status-days='" . json_encode( $status_days ) . "'" .

            '>' . 

            '</div>';

	$legend_available = $hbdb->get_string( 'legend_available' );

	$legend_occupied = $hbdb->get_string( 'legend_occupied' );

	$legend_pending = 'Afventer godkendelse';
	$legend_pending = '';
	if ( $legend_available || $legend_occupied ) {

		$output .= '' .

            '<p class="hb-avail-caption-wrapper hb-dp-clearfix">';

		if ( $legend_available ) {

			$calendar_color_values = json_decode( get_option( 'hb_calendar_colors' ), true );

			if (

				isset( $calendar_color_values[ 'cal-bg' ] ) &&

				isset( $calendar_color_values[ 'available-day-bg' ] ) &&

				( $calendar_color_values[ 'cal-bg' ] != $calendar_color_values[ 'available-day-bg' ] )

			) {

			$output .= '' .

				'<span class="hb-avail-caption hb-avail-caption-available"></span>' . 

				'<span class="hb-avail-caption-text hb-avail-caption-text-available">' . $legend_available . '</span>' .

				'<br class="hb-avail-line-break" />';

			}

		}

        if ( $legend_occupied ) {

			$output .= '' .

				'<span class="hb-avail-caption hb-avail-caption-occupied"></span>' . 

	            '<span class="hb-avail-caption-text hb-avail-caption-text-occupied">' . $legend_occupied . '</span>';

		}
		if ( $legend_pending ) {

			$output .= '' .

				'<span class="hb-avail-caption hb-avail-caption-pending"></span>' . 

	            '<span class="hb-avail-caption-text hb-avail-caption-text-pending">' . $legend_pending . '</span>';

		}


		$output .= '' .

            '</p>';

	}

	$output .= '' . 

        '</div>';

	

	return $output;

}