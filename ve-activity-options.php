<?php
/**
 * Moove_Activity_Options File Doc Comment
 *
 * @category    Moove_Activity_Options
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_Options Class Doc Comment
 *
 * @category Class
 * @package  Moove_Activity_Options
 * @author   Moove Agency
 */
class Moove_Activity_Options {
	/**
	 * Global options
	 *
	 * @var array
	 */
	private $options;
	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'uat_log_settings_capability', array( &$this, 'uat_custom_log_settings_capability' ), 10, 1 );
		add_action( 'uat_activity_log_capability', array( &$this, 'uat_custom_activity_log_capability' ), 10, 1 );
		add_action( 'update_option_moove_post_act', array( &$this, 'moove_activity_check_settings' ), 10, 2 );
		add_action( 'plugins_loaded', array( &$this, 'load_languages' ) );
		add_action( 'uat_activity_submenu_extension', array( &$this, 'uat_activity_submenu_extension' ), 10, 1 );
	}

	/**
	 * Submenu Extension.
	 *
	 * @param string $permission User permission.
	 */
	public static function uat_activity_submenu_extension( $permission ) {
		$plugin_link = esc_url( admin_url( 'admin.php?page=moove-activity-log' ) );
		wp_verify_nonce( 'uat_nonce', 'uat_settings_nonce' );
		$current_tab = isset( $_GET['sm'] ) ? sanitize_text_field( wp_unslash( $_GET['sm'] ) ) : '';

		$active_tab = '';
		if ( isset( $_GET['tab'] ) ) :
			$active_tab = rawurlencode( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) );
			$active_tab = $active_tab ? $active_tab : 'all_logs';
		else :
			$active_tab = 'all_logs';
		endif;

		$current_tab = ! isset( $_GET['sm'] ) && 'all_logs' === $active_tab ? 'activity_tracking' : $current_tab;

		$plugin_tabs = array(

			array(
				'title'    => esc_html__( 'Activity Log', 'user-activity-tracking-and-log' ),
				'slug'     => 'all_logs',
				'category' => 'activity_tracking'
			),

			array(
				'title'    => esc_html__( 'Event Tracking', 'user-activity-tracking-and-log' ),
				'slug'     => 'et-log',
				'category' => 'event_tracking'
			),

			array(
				'title'    => esc_html__( 'Settings', 'user-activity-tracking-and-log' ),
				'slug'     => 'activity-settings',
				'category' => 'settings'
			),

			array(
				'title'    => esc_html__( 'Documentation', 'user-activity-tracking-and-log' ),
				'slug'     => 'documentation',
				'category' => 'documentation'
			),

			array(
				'title'    => esc_html__( 'Support', 'user-activity-tracking-and-log' ),
				'slug'     => 'support',
				'category' => 'support'
			),

			array(
				'title'    => esc_html__( 'Licence Manager', 'user-activity-tracking-and-log' ),
				'slug'     => 'licence',
				'category' => 'licence'
			)
		);

		$plugin_tabs = apply_filters( 'uat_plugin_tabs_nav', $plugin_tabs );

		foreach ( $plugin_tabs as $plugin_tab ) :
			add_submenu_page(
				'moove-activity-log',
				$plugin_tab['title'],
				'<span class="uat-menu-item uat-menu-item-' . $plugin_tab['category'] . ' ' . ( $current_tab === $plugin_tab['category'] ? 'udt-current-menu-item' : '' ) . '">' . $plugin_tab['title'] . '</span>',
				$permission,
				$plugin_link . '&tab=' . $plugin_tab['slug'] . '&sm=' . $plugin_tab['category']
			);
		endforeach;
	}

	/**
	 * Custom capability type for Activity log settings (Settings -> Activity log)
	 *
	 * @param string $capability Capability.
	 */
	public function uat_custom_log_settings_capability( $capability ) {
		return $capability;
	}

	/**
	 * Custom capability type for Activity log table (CMS -> Activity log)
	 *
	 * @param string $capability Capability.
	 */
	public function uat_custom_activity_log_capability( $capability ) {
		return $capability;
	}

	/**
	 * Plugin localization data
	 */
	public function load_languages() {
		load_plugin_textdomain( 'user-activity-tracking-and-log', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Callback function after settings page saved. If there is any changes,
	 * it change the selected post type posts by the settings page value.
	 *
	 * @param  mixt $old_value Old value.
	 * @param  mixt $new_value New value.
	 * @return  void
	 */
	public function moove_activity_check_settings( $old_value, $new_value ) {
		$activity_settings = get_option( 'moove_post_act' );
		$post_types        = get_post_types( array( 'public' => true ) );
		$uat_content       = new Moove_Activity_Content();
		unset( $post_types['attachment'] );
		foreach ( $post_types as $post_type => $value ) {
			if ( '1' === $activity_settings[ $post_type ] || 1 === $activity_settings[ $post_type ] ) :
				continue;
			else :
				$query = array(
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'meta_query'     => array( // phpcs:ignore
						'relation' => 'OR',
						array(
							'key'     => 'ma_data',
							'value'   => null,
							'compare' => '!='
						)
					)
				);

				$log_posts = new WP_Query( $query );
				if ( $log_posts->have_posts() ) :
					while ( $log_posts->have_posts() ) :
						$log_posts->the_post();
						delete_post_meta( get_the_ID(), 'ma_data' );
					endwhile;
				endif;
				wp_reset_postdata();
			endif;
		}
	}
}
new Moove_Activity_Options();
