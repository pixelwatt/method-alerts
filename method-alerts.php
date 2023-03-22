<?php
/**
 * Plugin Name: Method Alerts
 * Plugin URI: https://github.com/pixelwatt/method-alerts
 * Description: This plugin implements a system for displaying alerts on specific pages or posts, loading alerts through the browser to keep performance impact low. This plugin requires CMB2.
 * Version: 1.0.2
 * Author: Rob Clark
 * Author URI: https://robclark.io
 */

function method_alerts_scripts() {
	if ( is_singular() ) {
		wp_enqueue_script( 'method-alerts-loader', plugin_dir_url( __FILE__ ) . 'method-alerts-loader.js', array( 'jquery' ), '', true );

		$data_array = array(
			'postid'   => get_the_id(),
			'site_url' => get_site_url(),
		);
		wp_localize_script( 'method-alerts-loader', 'data_object', $data_array );
	}
}

add_action( 'wp_enqueue_scripts', 'method_alerts_scripts' );

//-----------------------------------------------------
// Register a post type for the alerts
//-----------------------------------------------------

add_action( 'init', 'method_alerts_init' );

function method_alerts_init() {
	$labels = array(
		'name'               => _x( 'Alerts', 'post type general name', 'method-alerts' ),
		'singular_name'      => _x( 'Alert', 'post type singular name', 'method-alerts' ),
		'menu_name'          => _x( 'Alerts', 'admin menu', 'method-alerts' ),
		'name_admin_bar'     => _x( 'Alert', 'add new on admin bar', 'method-alerts' ),
		'add_new'            => _x( 'Add Alert', 'job', 'method-alerts' ),
		'add_new_item'       => __( 'Add New Alert', 'method-alerts' ),
		'new_item'           => __( 'New Alert', 'method-alerts' ),
		'edit_item'          => __( 'Edit Alert', 'method-alerts' ),
		'view_item'          => __( 'View Alert', 'method-alerts' ),
		'all_items'          => __( 'Alerts', 'method-alerts' ),
		'search_items'       => __( 'Search Alerts', 'method-alerts' ),
		'parent_item_colon'  => __( 'Parent Alert:', 'method-alerts' ),
		'not_found'          => __( 'No alerts found.', 'method-alerts' ),
		'not_found_in_trash' => __( 'No alerts found in Trash.', 'method-alerts' ),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __( 'Alerts to display using the Method Alerts plugin.', 'method-alerts' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 99,
		'menu_icon'          => 'dashicons-warning',
		'supports'           => array( 'title' ),
	);

	register_post_type( 'method_alert', $args );
}


//-----------------------------------------------------
// Register custom options for the alerts post type
//-----------------------------------------------------

add_action( 'cmb2_admin_init', 'method_alerts_metabox' );

function method_alerts_metabox() {
	$prefix = '_method_alerts_';

	$cmb_options = new_cmb2_box(
		array(
			'id'           => $prefix . 'metabox',
			'title'        => esc_html__( 'Alert Options', 'method-alerts' ),
			'object_types' => array( 'method_alert' ),
			'priority'     => 'high',
		)
	);

	$cmb_options->add_field(
		array(
			'name' => __( 'Alert Headline', 'method-alerts' ),
			'id'   => $prefix . 'headline',
			'type' => 'text',
		)
	);

	$cmb_options->add_field(
		array(
			'name'    => __( 'Alert Content', 'method-alerts' ),
			'id'      => $prefix . 'content',
			'type'    => 'wysiwyg',
			'options' => array(
				'media_buttons' => false, // show insert/upload button(s)
				'teeny'         => true, // output the minimal editor config used in Press This
				'textarea_rows' => 4,
			),
		)
	);

	$cmb_options->add_field(
		array(
			'name'             => __( 'Alert Color Scheme', 'method-alerts' ),
			'id'               => $prefix . 'theme',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => array(
				'primary'   => 'Primary',
				'secondary' => 'Secondary',
				'success'   => 'Success',
				'danger'    => 'Danger',
				'warning'   => 'Warning',
				'info'      => 'Info',
				'light'     => 'Light',
				'dark'      => 'Dark',
				'unstyled'  => 'Unstyled',
			),
		)
	);

	$cmb_options->add_field(
		array(
			'name'             => __( 'Targeted Posts', 'method-alerts' ),
			'id'               => $prefix . 'targets',
			'type'             => 'select',
			'show_option_none' => true,
			'options'          => method_alerts_get_posts(),
			'repeatable'       => true,
			'column'           => array(
				'position' => 2,
				'name'     => 'Posts',
			),
		)
	);

	$cmb_options->add_field(
		array(
			'name' => __( '<div style="text-transform: none;"><span style="font-size: 1.5rem; font-weight: 900; line-height: 1;">Scheduling</span><p style="font-weight: 400;">Configure scheduling for this alert.</p></div>', 'cmb2' ),
			'id'   => $prefix . 'schedule_info',
			'type' => 'title',
		)
	);

	$cmb_options->add_field(
		array(
			'name'    => 'Enable Scheduling?',
			'id'      => $prefix . 'schedule_status',
			'type'    => 'radio',
			'options' => array(
				'off' => __( 'No, show this alert until it is unpublished.', 'cmb2' ),
				'on'  => __( 'Yes, only show in the window of time specified below.', 'cmb2' ),
			),
			'default' => 'off',
		)
	);

	$cmb_options->add_field(
		array(
			'name'        => esc_html__( 'Start Date', 'cmb2' ),
			'desc'        => esc_html__( '', 'cmb2' ),
			'id'          => $prefix . 'schedule_startdate',
			'type'        => 'text_date',
			'date_format' => 'Y-m-d',
			'attributes'  => array(
				'autocomplete' => 'off',
			),
		)
	);

	$cmb_options->add_field(
		array(
			'name'        => esc_html__( 'Start Time', 'cmb2' ),
			'id'          => $prefix . 'schedule_starttime',
			'type'        => 'text_time',
			'time_format' => 'H:i', // Set to 24hr format
			'attributes'  => array(
				'autocomplete' => 'off',
			),
		)
	);

	$cmb_options->add_field(
		array(
			'name'        => esc_html__( 'End Date', 'cmb2' ),
			'desc'        => esc_html__( '', 'cmb2' ),
			'id'          => $prefix . 'schedule_enddate',
			'type'        => 'text_date',
			'date_format' => 'Y-m-d',
			'attributes'  => array(
				'autocomplete' => 'off',
			),
		)
	);

	$cmb_options->add_field(
		array(
			'name'        => esc_html__( 'End Time', 'cmb2' ),
			'id'          => $prefix . 'schedule_endtime',
			'type'        => 'text_time',
			'time_format' => 'H:i', // Set to 24hr format
			'attributes'  => array(
				'autocomplete' => 'off',
			),
		)
	);

}


//-----------------------------------------------------
// Get an array of post IDs and titles
//-----------------------------------------------------

function method_alerts_get_posts() {
	$args = array(
		'post_type'      => array( 'page' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'order'          => 'ASC',
		'orderby'        => 'title',
	);

	//The Query
	$items = get_posts( apply_filters( 'method_alerts_query_args', $args ) );

	if ( $items ) {
		foreach ( $items as $post ) :
			setup_postdata( $post );
			$output[ "{$post->ID}" ] = get_the_title( $post->ID );
		endforeach;
		wp_reset_postdata();
	}

	return $output;
}


// Convert alert time to unix timestamp on alert save

function method_alerts_sanitize_time( $id, $post ) {

	if ( 'method_alert' == $post->post_type ) {
		$start_date = get_post_meta( $post->ID, '_method_alerts_schedule_startdate', true );
		$start_time = get_post_meta( $post->ID, '_method_alerts_schedule_starttime', true );

		if ( ! empty( $start_date ) ) {
			$start_date_array    = explode( '-', $start_date );
			$start_time_array    = explode( ':', $start_time );
			$start_time_array[0] = ( 0 != $start_time_array[0] ? ltrim( $start_time_array[0], '0' ) : '0' );
			$start_time_array[1] = ( 0 != $start_time_array[1] ? ltrim( $start_time_array[1], '0' ) : '0' );
			date_default_timezone_set( get_option( 'timezone_string' ) );
			$utime_start = mktime( $start_time_array[0], $start_time_array[1], 0, ltrim( $start_date_array[1], '0' ), ltrim( $start_date_array[2], '0' ), $start_date_array[0] );
			update_post_meta( $post->ID, '_method_alerts_schedule_start_utc', $utime_start );
		}

		$end_date = get_post_meta( $post->ID, '_method_alerts_schedule_enddate', true );
		$end_time = get_post_meta( $post->ID, '_method_alerts_schedule_endtime', true );

		if ( ! empty( $end_date ) ) {
			$end_date_array = explode( '-', $end_date );
			$end_time_array = explode( ':', $end_time );

			$end_time_array[0] = ( 0 != $end_time_array[0] ? ltrim( $end_time_array[0], '0' ) : '0' );
			$end_time_array[1] = ( 0 != $end_time_array[1] ? ltrim( $end_time_array[1], '0' ) : '0' );
			date_default_timezone_set( get_option( 'timezone_string' ) );
			$utime_end = mktime( $end_time_array[0], $end_time_array[1], 0, ltrim( $end_date_array[1], '0' ), ltrim( $end_date_array[2], '0' ), $end_date_array[0] );
			update_post_meta( $post->ID, '_method_alerts_schedule_end_utc', $utime_end );
		}

		method_alerts_build_json();
	}

	return;
}

add_action( 'save_post', 'method_alerts_sanitize_time', 10000, 2 );


function method_alerts_build_json() {
	$alerts = array();

	$args = array(
		'posts_per_page' => -1,
		'post_type'      => 'method_alert',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'relation' => 'AND',
				array(
					'key'   => '_method_alerts_schedule_status',
					'value' => 'on',
				),
				array(
					'key'     => '_method_alerts_schedule_end_utc',
					'value'   => date( 'U' ),
					'compare' => '>',
				),
			),
			array(
				array(
					'key'   => '_method_alerts_schedule_status',
					'value' => 'off',
				),
			),
		),
	);

	$alert_items = get_posts( $args );

	foreach ( $alert_items as $item ) {
		$alert = array();
		$meta  = get_post_meta( $item->ID );

		$alert['id']      = $item->ID;
		$alert['targets'] = ( method_alerts_check_key( $meta['_method_alerts_targets'][0] ) ? maybe_unserialize( $meta['_method_alerts_targets'][0] ) : '' );

		// Scheduling
		$alert['schedule']['status'] = ( method_alerts_check_key( $meta['_method_alerts_schedule_status'][0] ) ? $meta['_method_alerts_schedule_status'][0] : 'off' );
		if ( 'on' == $alert['schedule']['status'] ) {
			$alert['schedule']['start'] = ( method_alerts_check_key( $meta['_method_alerts_schedule_start_utc'][0] ) ? $meta['_method_alerts_schedule_start_utc'][0] : '' );
			$alert['schedule']['end']   = ( method_alerts_check_key( $meta['_method_alerts_schedule_end_utc'][0] ) ? $meta['_method_alerts_schedule_end_utc'][0] : '' );
		}

		// Content
		$alert['attr']['headline'] = ( method_alerts_check_key( $meta['_method_alerts_headline'][0] ) ? method_alerts_format_tags( $meta['_method_alerts_headline'][0] ) : '&nbsp;' );
		if ( method_alerts_check_key( $meta['_method_alerts_content'][0] ) ) {
			$alert_content = method_alerts_filter_content( $meta['_method_alerts_content'][0] );
			$alert_content = str_replace( '<a ', '<a class="alert-link" ', $alert_content );
			$alert['attr']['content'] = $alert_content;
		} else {
			$alert['attr']['content'] = '&nbsp;';
		}
		$alert['attr']['theme']    = ( method_alerts_check_key( $meta['_method_alerts_theme'][0] ) ? $meta['_method_alerts_theme'][0] : 'primary' );

		// Add alert data to the array of alerts to be written to json
		$alerts[] = $alert;
	}

	// Encode alert data as json
	$data_json = json_encode( $alerts );

	$upload_dir = wp_upload_dir();
	if ( ! is_dir( $upload_dir['basedir'] . '/method-alerts' ) ) mkdir( $upload_dir['basedir'] . '/method-alerts' );

	$file = $upload_dir['basedir'] . '/method-alerts/data.json';
	file_put_contents( $file, $data_json );
}

//-----------------------------------------------------
// Check an array key to see if it exists
//-----------------------------------------------------

function method_alerts_check_key( $key ) {
	$output = false;
	if ( isset( $key ) ) {
		if ( ! empty( $key ) ) {
			$output = true;
		}
	}
	return $output;
}

//-----------------------------------------------------
// Run a string through WordPress' content filter
//-----------------------------------------------------

function method_alerts_filter_content( $content ) {
	if ( ! empty( $content ) ) {
		$content = apply_filters( 'the_content', $content );
	}
	return $content;
}

//-----------------------------------------------------
// Convert formatting tags
//-----------------------------------------------------

function method_alerts_format_tags( $content ) {
	$content = str_replace( '[badge]', '<span class="badge bg-light">', $content );
	$content = str_replace( '[/badge]', '</span>', $content );
	$content = str_replace( '[strong]', '<strong>', $content );
	$content = str_replace( '[/strong]', '</strong>', $content );
	$content = str_replace( '[br]', '<br>', $content );
	return $content;
}
