<?php
/**
 * Moove_Activity_Shortcodes File Doc Comment
 *
 * @category    Moove_Activity_Shortcodes
 * @package   moove-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_Shortcodes Class Doc Comment
 *
 * @category Class
 * @package  Moove_Activity_Shortcodes
 * @author   Moove Agency
 */
class Moove_Activity_Shortcodes {
	/**
	 * Construct function
	 */
	public function __construct() {
		$this->moove_activity_register_shortcodes();
	}
	/**
	 * Register shortcodes
	 *
	 * @return void
	 */
	public function moove_activity_register_shortcodes() {
		add_shortcode( 'show_ip', array( &$this, 'moove_get_the_user_ip' ) );
	}

	/**
	 * User IP address
	 *
	 * @param bool $filter Conditional parameter to apply GDPR filter or not.
	 *
	 * @return string IP Address.
	 */
	public function moove_get_the_user_ip( $filter = true ) {
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) :
			// Check ip from share internet.
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) :
			// To check ip is pass from proxy.
			if ( is_array( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) :
				$ip          = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
				$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				$ip          = isset( $ip[0] ) ? $ip[0] : $remote_addr;
			else :
				$ip = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) : '';
			endif;
		else :
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		endif;

		$ip = ( strpos( $ip, ',' ) > 0 ) ? trim( explode( ',', $ip )[0] ) : $ip;

		return $filter ? apply_filters( 'moove_activity_tracking_ip_filter', $ip ) : $ip;
	}

	/**
	 * Location details by IP address.
	 *
	 * @param string $ip IP Address.
	 */
	public function get_location_details( $ip = false ) {
		$response = false;
		if ( $ip ) :
			$transient_key = 'uat_locdata_' . md5( $ip );
			$details       = get_transient( $transient_key );
			if ( ! $details ) :
				try {
					$url_link = "http://www.geoplugin.net/xml.gp?ip={$ip}";
					$response = simplexml_load_file( $url_link );
					$details  = array();
					if ( $response ) :
						$details = array(
							'ip'     => isset( $response->geoplugin_request ) ? strval( $response->geoplugin_request ) : $ip,
							'city'   => isset( $response->geoplugin_city ) ? strval( $response->geoplugin_city ) : '',
							'region' => isset( $response->geoplugin_region ) ? strval( $response->geoplugin_region ) : ''
						);
					endif;

					if ( $details && is_array( $details ) && ! empty( $details ) ) :
						$details = wp_json_encode( $details );
						set_transient( $transient_key, $details, 30 * DAY_IN_SECONDS );
					else :
						$details = false;
					endif;
				} catch ( Exception $e ) {
					$details = false;
				}
			else :
				$details = json_decode( $details );
			endif;
		endif;
		return $details;
	}
}
new Moove_Activity_Shortcodes();
