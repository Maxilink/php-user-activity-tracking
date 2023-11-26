<?php
/**
 * Moove_Functions File Doc Comment
 *
 * @category  Moove_Functions
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! function_exists( 'uat_filter_data_entry' ) ) :
	/**
	 * Filtering Log Entry.
	 *
	 * @param array $log_entry Log Entry.
	 * @param array $log Log.
	 */
	function uat_filter_data_entry( $log_entry, $log ) {
		return apply_filters( 'uat_filter_data_entry', $log_entry, $log );
	}
endif;

if ( ! function_exists( 'uat_get_post_types' ) ) :
	function uat_get_post_types( $_builtin = true ) {
		$_cpt_args 		= array( 'public' => true, '_builtin' => $_builtin );
		$_post_types 	= get_post_types( $_cpt_args );
		return $_post_types;
	}
endif;

if ( ! function_exists( 'uat_is_post_type_enabled' ) ) :
	function uat_is_post_type_enabled( $_post_type ) {
		$is_enabled 	= false;
		$_post_types 	= uat_get_post_types();
		$is_enabled 	= apply_filters( 'uat_is_post_type_enabled', in_array( $_post_type, $_post_types ), $_post_type );
		return $is_enabled;
	}
endif;

if ( ! function_exists( 'uat_get_enabled_post_types' ) ) :
	/**
	 * Get enabled post types.
	 *
	 * @return array $response Post Types.
	 */
	function uat_get_enabled_post_types() {
		$response        = array();
		$plugin_settings = apply_filters( 'moove_uat_filter_plugin_settings', get_option( 'moove_post_act' ) );
		$_post_types     = uat_get_post_types();
		unset( $_post_types['attachment'] );
		if ( $_post_types && is_array( $_post_types ) ) :
			foreach ( $_post_types as $_post_type ) :
				if ( isset( $plugin_settings[ $_post_type ] ) && intval( $plugin_settings[ $_post_type ] ) === 1 ) :
					$response[] = $_post_type;
				endif;
			endforeach;
		endif;
		return apply_filters( 'uat_get_enabled_post_types', $response );
	}
endif;

if ( ! function_exists( 'uat_addon_get_activation_key' ) ) :
	/**
	 * Add-on activation key.
	 *
	 * @param string $option_key Option Key.
	 */
	function uat_addon_get_activation_key( $option_key ) {
		$value = get_option( $option_key );
		if ( is_multisite() && ! $value ) :
			$_value = function_exists( 'get_site_option' ) ? get_site_option( $option_key ) : false;
			if ( $_value ) :
				$main_blog_id = get_main_site_id();
				if ( $main_blog_id ) :
					switch_to_blog( $main_blog_id );
					update_option(
						$option_key,
						$_value
					);
					restore_current_blog();
					delete_site_option( $option_key );
					$value = $_value;
				endif;
			endif;
		endif;
		return $value;
	}
endif;

/**
 * Returns activity by referrer URL.
 *
 * @param  mixt $url URL.
 * @return  string
 */
function moove_activity_get_referrer_link_by_url( $url ) {
	$return = '';
	ob_start();
	?>
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" title="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a>
	<?php
	$return = ob_get_clean();
	return $return;
}

if ( ! function_exists( 'moove_desc_sort' ) ) :
	/**
	 * DESC Sort
	 *
	 * @param mixed $item1 Item1.
	 * @param mixed $item2 Item2.
	 */
	function moove_desc_sort( $item1, $item2 ) {
		if ( strtotime( $item1['time'] ) === strtotime( $item2['time'] ) ) {
			return 0;
		}
		return ( $item1['time'] < $item2['time'] ) ? 1 : -1;
	}
endif;

if ( ! function_exists( 'moove_activity_current_order' ) ) :
	/**
	 * Activity Order
	 *
	 * @param string $type Type.
	 * @param string $custom_order Custom Order.
	 * @param string $_order Order.
	 * @param string $_orderby Order by.
	 */
	function moove_activity_current_order( $type, $custom_order, $_order, $_orderby ) {
		return $_order;
	}
endif;

if ( ! function_exists( 'uat_get_referer' ) ) :
	/**
	 * PHP referrer URL for tracking
	 *
	 * @return Referrer URL.
	 */
	function uat_get_referer() {
		return wp_unslash( isset( $_SERVER['HTTP_REFERER'] ) && esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : esc_url( wp_get_raw_referer() ) );
	}
endif;

if ( ! function_exists( 'moove_activity_get_timezone_dropdown' ) ) :
	/**
	 * Timezone dropdown
	 *
	 * @param string $selected Selected timezone.
	 */
	function moove_activity_get_timezone_dropdown( $selected ) {
		$list = moove_activity_timezone_list( $selected );
		?>
			<select name="timezone">
				<?php foreach ( $list as $t ) : ?>
					<option value="<?php echo esc_html( $t['zone'] ); ?>" <?php echo $selected === $t['zone'] ? 'selected="selected"' : ''; ?>>
						<?php echo esc_html( $t['zone'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php
	}
endif;

if ( ! function_exists( 'moove_activity_timezone_converter' ) ) :
	/**
	 * Timezone converter by offset
	 *
	 * @param date   $date_time Date.
	 * @param string $offset Offset.
	 */
	function moove_activity_timezone_converter( $date_time, $offset ) {

		$timestamp = strtotime( $date_time );

		$date            = new \DateTime();
		$datetime_format = 'Y-m-d H:i:s';

		$date->setTimestamp( $timestamp );
		$date->format( $datetime_format );
		$date_with_timezone = $date;

		$sign = $offset < 0 ? '-' : '+';

		return $date_with_timezone;
	}
endif;

if ( ! function_exists( 'moove_activity_timezone_list' ) ) :
	/**
	 * Timezone list
	 */
	function moove_activity_timezone_list() {
		$zones_array = array();
		$timestamp   = time();
		foreach ( timezone_identifiers_list() as $key => $zone ) {
			$zones_array[ $key ]['zone'] = $zone;
		}
		return $zones_array;
	}
endif;

if ( ! function_exists( 'moove_activity_get_plugin_dir' ) ) :
	/**
	 * Plugin directory url
	 */
	function moove_activity_get_plugin_dir() {
		return plugins_url( basename( dirname( __FILE__ ) ) );
	}
endif;

if ( ! function_exists( 'moove_activity_convert_date' ) ) :
	/**
	 * Date converter
	 *
	 * @param string $selected Selected timezone.
	 * @param date   $date Date & time.
	 * @param array  $options Plugin options.
	 */
	function moove_activity_convert_date( $selected, $date, $options ) {
		if ( 'a' === $selected ) :
			$date = new DateTime( $date );
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			return $date->format( 'Y-m-d H:i:s' );

		elseif ( 'b' === $selected ) :
			$tz   = moove_activity_get_blog_timezone();
			$date = new DateTime( $date );
			$date->setTimezone( new DateTimeZone( $tz ) );
			return $date->format( 'Y-m-d H:i:s' );

		elseif ( 'c' === $selected ) :
			$tz   = isset( $options['timezone'] ) ? $options['timezone'] : 'UTC';
			$date = new DateTime( $date );
			$date->setTimezone( new DateTimeZone( $tz ) );
			return $date->format( 'Y-m-d H:i:s' );
		endif;
	}
endif;

if ( ! function_exists( 'moove_activity_get_blog_timezone' ) ) :
	/**
	 *  Returns the blog timezone
	 *
	 * Gets timezone settings from the db. If a timezone identifier is used just turns
	 * it into a DateTimeZone. If an offset is used, it tries to find a suitable timezone.
	 * If all else fails it uses UTC.
	 *
	 * @return DateTimeZone The blog timezone
	 */
	function moove_activity_get_blog_timezone() {

		$tzstring = get_option( 'timezone_string' );
		$offset   = get_option( 'gmt_offset' );

		if ( empty( $tzstring ) && 0 !== $offset && floor( $offset ) === $offset ) {
			$offset_st = $offset > 0 ? "-$offset" : '+' . absint( $offset );
			$tzstring  = 'Etc/GMT' . $offset_st;
		}

		// Issue with the timezone selected, set to 'UTC'.
		if ( empty( $tzstring ) ) {
			$tzstring = 'UTC';
		}
		return $tzstring;
	}
endif;

if ( ! function_exists( 'uat_get_plugin_directory_url' ) ) :
	/**
	 * Relative path of the User Activity plugin
	 */
	function uat_get_plugin_directory_url() {
		return plugin_dir_url( __FILE__ );
	}
endif;

if ( ! function_exists( 'uat_get_plugin_directory' ) ) :
	/**
	 * Relative path of the User Activity plugin
	 */
	function uat_get_plugin_directory() {
		return dirname( __FILE__ );
	}
endif;

if ( ! function_exists( 'uat_remove_screen_options_from_activity_screen' ) ) :
	/**
	 * Screen options removed from Activity log
	 *
	 * @param bool   $boolval Boolean value.
	 * @param string $screen Screen.
	 */
	function uat_remove_screen_options_from_activity_screen( $boolval, $screen ) {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'moove-activity-log' || $_GET['page'] === 'moove-activity' ) ) { // phpcs:ignore
			return false;
		}
		return $boolval;
	}
	add_filter( 'screen_options_show_screen', 'uat_remove_screen_options_from_activity_screen', 10, 2 );
endif;

if ( ! function_exists( 'moove_uat_plugin_settings_link' ) ) :
	add_filter( 'plugin_action_links', 'moove_uat_plugin_settings_link', 10, 2 );
	/**
	 * Extension to display support, premium and settings links in the plugin listing page
	 *
	 * @param array  $links Links.
	 * @param string $file File.
	 */
	function moove_uat_plugin_settings_link( $links, $file ) {
		if ( plugin_basename( dirname( __FILE__ ) . '/moove-activity.php' ) === $file ) {
			/*
			* Insert the Settings page link at the beginning
			*/
			$in = '<a href="' . esc_url( admin_url( 'admin.php' ) ) . '?page=moove-activity-log&tab=activity-settings&sm=settings" target="_blank">' . __( 'General Settings', 'user-activity-tracking-and-log' ) . '</a>';
			array_unshift( $links, $in );

			/*
			* Insert the Support page link at the end
			*/
			$in = '<a href="https://support.mooveagency.com/forum/user-activity-tracking-and-log/" target="_blank">' . __( 'Support', 'user-activity-tracking-and-log' ) . '</a>';
			array_push( $links, $in );

			/*
			* Insert the Premium Upgrade link at the end
			*/
			if ( ! function_exists( 'moove_uat_addon_get_plugin_dir' ) ) :
				$in = '<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" class="uat_admin_link uat_premium_buy_link" target="_blank">' . __( 'Buy Premium', 'user-activity-tracking-and-log' ) . '</a>';
				array_push( $links, $in );
			endif;
		}
		return $links;
	}
endif;
