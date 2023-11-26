<?php
/**
 *  Contributors: MooveAgency
 *  Plugin Name: User Activity Tracking and Log
 *  Plugin URI: http://www.mooveagency.com
 *  Description: This plugin gives you the ability to track user activity on your website.
 *  Version: 4.1.3
 *  Author: Moove Agency
 *  Author URI: http://www.mooveagency.com
 *  License: GPLv2
 *  Text Domain: user-activity-tracking-and-log
 *
 *  @package user-activity-tracking-and-log
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'MOOVE_UAT_VERSION', '4.1.3' );

if ( ! defined( 'MOOVE_SHOP_URL' ) ) :
	define( 'MOOVE_SHOP_URL', 'https://shop.mooveagency.com' );
endif;

register_activation_hook( __FILE__, 'moove_activity_activate' );
register_deactivation_hook( __FILE__, 'moove_activity_deactivate' );

/**
 * Set options page for the plugin
 */
function moove_set_options_values() {
	$settings   = get_option( 'moove_post_act' );
	$post_types = get_post_types( array( 'public' => true ) );
	unset( $post_types['attachment'] );
	if ( ! $settings ) :
		foreach ( $post_types as $post_type ) :
			if ( ( isset( $settings[ $post_type ] ) && 1 !== $settings[ $post_type ] ) || ! isset( $settings[ $post_type ] ) ) :
				$settings[ $post_type ] = 1;
			endif;
			if ( ( isset( $settings[ $post_type . '_transient' ] ) && 1 !== $settings[ $post_type . '_transient' ] ) || ! isset( $settings[ $post_type . '_transient' ] ) ) :
				$settings[ $post_type . '_transient' ] = apply_filters( 'uat_log_retention_default', 30 );				
			endif;
		endforeach;
		$settings = apply_filters( 'moove_post_act_before_save', $settings );
		update_option( 'moove_post_act', $settings );
	endif;
}

/**
 * Functions on plugin activation, create relevant pages and defaults for settings page.
 */
function moove_activity_activate() {
	moove_set_options_values();
	delete_option( 'moove_importer_has_database' );
	delete_option( 'moove_importer_has_extras' );
	delete_option( 'uat_db_support_request_url' );
}

/**
 * Function on plugin deactivation. It removes the pages created before.
 */
function moove_activity_deactivate() {
	try {
		if ( class_exists( 'Moove_UAT_License_Manager' ) ) :
			$uat_default_content = new Moove_Activity_Content();
			$option_key          = $uat_default_content->moove_uat_get_key_name();
			$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );
			if ( $uat_key && isset( $uat_key['key'] ) && isset( $uat_key['activation'] ) ) :
				$license_manager  = new Moove_UAT_License_Manager();
				$validate_license = $license_manager->validate_license( $uat_key['key'], 'uat', 'deactivate' );
				if ( $validate_license && isset( $validate_license['valid'] ) && true === $validate_license['valid'] ) :
					update_option(
						$option_key,
						array(
							'key'          => $uat_key['key'],
							'deactivation' => strtotime( 'now' )
						)
					);
				endif;
			endif;
		endif;

		delete_option( 'moove_importer_has_database' );
		delete_option( 'moove_importer_has_extras' );
		delete_option( 'uat_db_support_request_url' );
	} catch ( Exception $e ) {
		echo esc_html( $e->getMessage() );
	}
}

if ( ! function_exists( 'moove_uat_add_plugin_meta_links' ) ) {
	/**
	 * Star rating on the plugin listing page
	 *
	 * @param array  $meta_fields Meta fields.
	 * @param string $file Plugin file.
	 */
	function moove_uat_add_plugin_meta_links( $meta_fields, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) :
			$plugin_url    = 'https://wordpress.org/support/plugin/user-activity-tracking-and-log/reviews/?rate=5#new-post';
			$meta_fields[] = "<a href='" . esc_url( $plugin_url ) . "' target='_blank' title='" . esc_html__( 'Rate', 'user-activity-tracking-and-log' ) . "'>
          <i class='moove-uat-star-rating'>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
			. '</i></a>';

		endif;
		return $meta_fields;
	}
}
add_filter( 'plugin_row_meta', 'moove_uat_add_plugin_meta_links', 10, 2 );

/**
 * Loading Core files after all the plugins are loaded!
 */
add_action( 'plugins_loaded', 'uat_activity_load_libs' );

/**
 * Core file loader
 *
 * @return void.
 */
function uat_activity_load_libs() {
	/**
	 * Views
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-moove-activity-view.php';

	/**
	 * Content
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-moove-activity-content.php';

	/**
	 * Options
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-moove-activity-options.php';

	/**
	 * Controllers
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-activity-dt-manager.php';
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-moove-uat-license-manager.php';
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-moove-uat-updater.php';
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-moove-uat-review.php';
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-moove-activity-controller.php';
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-moove-activity-array-order.php';
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'class-moove-activity-database-model.php';

	/**
	 * Activity Actions
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-moove-activity-actions.php';

	/**
	 * Shortcodes
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-moove-activity-shortcodes.php';

	/**
	 * Functions
	 */
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'moove-functions.php';
}
