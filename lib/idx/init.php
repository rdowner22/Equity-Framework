<?php
/**
 * Equity Framework
 *
 * WARNING: This file is part of the core Equity Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Equity\IDX
 * @author  IDX, LLC
 * @license GPL-2.0+
 * @link    http://equityframework.com
 *
 * Loads files and initializes classes for setting up IDX integration
 *
 * Will not initialize IDX integration without an IDX API key and valid Equity key
 */

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$idxbroker_api_key = get_option('idx_broker_apikey');
if ( !is_plugin_active( 'idx-broker-platinum/idx-broker-platinum.php' ) || $idxbroker_api_key == '' )
	return;

add_action('equity_setup', 'equity_idx_features_init', 16);
function equity_idx_features_init() {

	$idxbroker_api_key = get_option('idx_broker_apikey');

	if ( $idxbroker_api_key == '' ) {
		delete_option('equity_endpoint_validation_failed');
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$plugin_data = get_plugins();

	if(isset($plugin_data['wp-listings/plugin.php'])) {
		if($plugin_data['wp-listings/plugin.php']['Version'] < 2.0 ) {
			require_once 'class.Equity_Idx_Listing.php';
		}
	}
	
	require_once 'class.Equity_Idx_Api.inc.php';

	require_once 'class.Equity_Quicksearch_Widget.inc.php';

	require_once 'class.Equity_City_Links_Widget.inc.php';

	require_once 'class.Equity_Showcase_Widget.inc.php';

	require_once 'class.Equity_Carousel_Widget.inc.php';

	require_once 'class.Equity_Lead_Login_Widget.inc.php';

	require_once 'class.Equity_Idx_Widget.inc.php';

	if (current_theme_supports( 'equity_idx_scrollspy' )) {
		require_once 'search-scrollspy.inc.php';
	}

	require_once 'class.Equity_Idx_Content.inc.php';

	$_equity_idx_content = new Equity_Idx_Content;

	add_action( 'wp_loaded', 'equity_register_idx_shortcodes' );

	add_action( 'widgets_init', 'equity_register_idx_widgets' );

	add_action( 'wp_enqueue_scripts', 'equity_enqueue_idx_stylesheet', 10 );

	add_action( 'admin_init', 'equity_ping_setup_schedule' );

	add_action( 'equity_ping_event', 'equity_ping_request' );

	add_action( 'upgrader_process_complete', 'equity_ping_request', 10, 0 );

	add_action( 'admin_notices', 'equity_validation_admin_notice' );
	
}

/**
 * Registers IDX widgets
 *
 * @return void
 */
function equity_register_idx_widgets() {
	register_widget('Equity_Quicksearch_Widget');
	register_widget('Equity_City_Links_Widget');
	register_widget('Equity_Showcase_Widget');
	register_widget('Equity_IDX_Carousel_Widget');
	register_widget('Equity_Lead_Login_Widget');
	register_widget('Equity_Idx_Widget');
}

/**
 * Registers IDX shortcodes
 * 
 * @return void
 * @since  1.5.8
 */
function equity_register_idx_shortcodes() {
	require_once( EQUITY_SHORTCODES_DIR . '/idx.php' );
}

/**
 * Sets cron job to ping IDX API for key validation
 *
 * @since 1.3.5
 */
add_filter( 'cron_schedules', 'equity_cron_add_weekly' );
function equity_cron_add_weekly( $schedules ) {
 	// Adds once weekly to the existing schedules.
 	$schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __( 'Once Weekly' )
 	);
 	return $schedules;
}
/** 
 * Check if ping is scheduled - if not, schedule it.
 *
 * @since 1.3.5
 */
function equity_ping_setup_schedule() {
	if ( ! wp_next_scheduled( 'equity_ping_event' ) ) {
		wp_schedule_event( time(), 'daily', 'equity_ping_event');
	}
}
/**
 * On the scheduled ping event, run wp_remote_post with activation status
 *
 * @since 1.3.5
 */
function equity_ping_request() {

	$equity_api_key = get_option('equity_api_key');

	$activation_status = get_option( 'equity_api_manager_activated' );
	if ($activation_status == 'Activated') {
		$enabled = 'y';
	} else {
		$enabled = 'n';
	}
	$domain = site_url();

	$idxbroker_api_key = get_option('idx_broker_apikey');

	$response = wp_remote_post( 'https://api.idxbroker.com/equity/validatekey', array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array(
				'accesskey' => $idxbroker_api_key,
				'equitykey' => $equity_api_key,
				'enabled' => $enabled,
				'domain' => $domain
				),
			'body' => null
	    )
	);
	
}

/*
 * Add admin notice if equity_endpoint_validation_failed option is true
 */
function equity_validation_admin_notice() {
	if (get_option('equity_endpoint_validation_failed') == true) {
		echo
			'<div class="notice notice-error is-dismissible">
	    		<p>' . __( 'The IDX API returned 401 Unauthorized. This is usually caused by a domain mismatch/API usage on unapproved domain. Will retry on next API call. Check the <a href="' . admin_url('admin.php?page=equity') . '">Equity Theme Settings</a> and verify you have the IDX approved URL in the IDX Info section. If this error persists, please contact <a href="https://idxbroker.desk.com/customer/portal/emails/new">IDX Broker support</a>.', 'equity' ) .'</p>
			</div>';
	}
}
