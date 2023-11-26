<?php
/**
 * Moove_Activity_Actions File Doc Comment
 *
 * @category  Moove_Activity_Actions
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_Actions Class Doc Comment
 *
 * @category Class
 * @package  Moove_Activity_Actions
 * @author   Moove Agency
 */
class Moove_Activity_Actions {
	/**
	 * Global variable used in localization
	 *
	 * @var array
	 */
	public $activity_loc_data;

	/**
	 * Global variable used in localization admin
	 *
	 * @var array
	 */
	public $activity_loc_data_admin;
	/**
	 * Construct
	 */
	public function __construct() {
		$this->moove_register_scripts();
		add_action( 'wp_ajax_moove_activity_track_pageview', array( 'Moove_Activity_Controller', 'moove_track_user_access_ajax' ) );
		add_action( 'wp_ajax_nopriv_moove_activity_track_pageview', array( 'Moove_Activity_Controller', 'moove_track_user_access_ajax' ) );

		add_action( 'wp_ajax_moove_activity_track_unload', array( 'Moove_Activity_Controller', 'moove_activity_track_unload' ) );
		add_action( 'wp_ajax_nopriv_moove_activity_track_unload', array( 'Moove_Activity_Controller', 'moove_activity_track_unload' ) );

		// AJAX Data Tables.
		add_action( 'wp_ajax_uat_activity_get_dt_logs', array( 'Moove_Activity_Controller', 'uat_activity_get_dt_logs' ) );
		add_action( 'wp_ajax_uat_activity_export_dt_logs', array( 'Moove_Activity_Controller', 'uat_activity_export_dt_logs' ) );
		add_action( 'wp_ajax_uat_activity_delete_dt_logs', array( 'Moove_Activity_Controller', 'uat_activity_delete_dt_logs' ) );
		add_action( 'wp_ajax_uat_manage_table_settings', array( 'Moove_Activity_Controller', 'uat_manage_table_settings' ), 10, 1 );

		add_action( 'admin_enqueue_scripts', array( &$this, 'uat_thirdparty_admin_scripts' ) );
		add_action( 'moove_activity_delete_options', array( &$this, 'moove_activity_bl_delete_options' ), 10, 2 );
		add_action( 'moove_activity_tab_content', array( &$this, 'moove_activity_tab_content' ), 999, 1 );
		add_action( 'moove-activity-tab-content', array( &$this, 'moove_activity_tab_content' ), 999, 1 );
		add_action( 'moove_activity_filters', array( &$this, 'moove_activity_filters' ), 5, 2 );
		add_action( 'moove_activity_check_extensions', array( &$this, 'moove_activity_check_extensions' ), 10, 2 );
		add_action( 'moove_activity_premium_section_ads', array( &$this, 'moove_activity_premium_section_ads' ) );
		add_action( 'moove_uat_filter_plugin_settings', array( &$this, 'moove_uat_filter_plugin_settings' ), 10, 1 );
		// Custom meta box for protection.
		add_action( 'add_meta_boxes', array( 'Moove_Activity_Content', 'moove_activity_meta_boxes' ) );
		add_action( 'save_post', array( 'Moove_Activity_Content', 'moove_save_post' ) );
		add_action( 'moove_activity_check_tab_content', array( &$this, 'moove_activity_check_tab_content' ), 10, 2 );
		add_action( 'uat_licence_action_button', array( 'Moove_Activity_Content', 'uat_licence_action_button' ), 10, 2 );
		add_action( 'uat_get_alertbox', array( 'Moove_Activity_Content', 'uat_get_alertbox' ), 10, 3 );
		add_action( 'uat_licence_input_field', array( 'Moove_Activity_Content', 'uat_licence_input_field' ), 10, 2 );
		add_action( 'uat_premium_update_alert', array( 'Moove_Activity_Content', 'uat_premium_update_alert' ) );
		add_action( 'uat_activity_log_restriction_content', array( 'Moove_Activity_Content', 'uat_activity_log_restriction_content' ), 10, 1 );
		add_action( 'uat_log_settings_restriction_content', array( 'Moove_Activity_Content', 'uat_log_settings_restriction_content' ), 10, 1 );
		add_action( 'uat_activity_screen_options_extension', array( &$this, 'uat_activity_screen_options_extension' ), 10, 1 );
		add_action( 'profile_update', array( &$this, 'uat_update_display_name_in_log' ), 10, 3 );
		add_action( 'uat_tab_section_cnt_class', array( &$this, 'uat_tab_section_cnt_class_filter' ), 10, 1 );
		add_action( 'uat_sidebar_menu_links', array( &$this, 'uat_sidebar_menu_links' ), 10, 2 );
		add_action( 'uat_delete_option_select_values', array( &$this, 'uat_delete_option_select_values' ), 10, 2 );
		add_action( 'uat_activity_settings_cpt', array( &$this, 'uat_activity_settings_cpt' ), 10, 1);
		add_action( 'uat_activity_settings_archives', array( &$this, 'uat_activity_settings_archives' ), 10, 1);
		add_action( 'uat_sidebar_menu_cpt_links', array( &$this, 'uat_sidebar_menu_cpt_links' ), 10, 2 );
		add_action( 'uat_get_table_settings', array( &$this, 'uat_get_table_settings' ), 10, 2 );
		/**
		 * Version incompatibility & deprecation notice.
		 */
		add_action(
			'uat_premium_update_alert',
			function() {
				if ( defined( 'MOOVE_UAT_PREMIUM_VERSION' ) && floatval( MOOVE_UAT_PREMIUM_VERSION ) < 2 ) :
					?>
						<div class="uat_license_log_alert">
							<div class="uat-admin-alert uat-admin-alert-error">
								<h3 style="margin: 5px 0 10px; color: inherit;">New version of the <strong style="color: #000">User Activity Tracking and Log - Premium </strong> Plugin is available.</h3>
								<p>Your current version is no longer supported and may have compatibility issues. Please update to the <a href="<?php echo esc_url( admin_url( '/plugins.php' ) ); ?>" target="_blank">latest version</a>.</p>
							</div>
							<!-- .uat-admin-alert uat-admin-alert-error -->
						</div>
						<!--  .uat-cookie-alert -->
					<?php
				endif;
			}
		);

		/**
		 * Legacy "Activity Settings" page redirect in admin area to new location.
		 */
		add_action(
			'admin_menu',
			function() {
				if ( is_admin() && isset( $_GET['page'] ) ) : // phpcs:ignore
					$page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore
					if ( 'moove-activity' === $page ) :
						$tab        = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
						$plugin_url = admin_url( '/admin.php?page=moove-activity-log' );
						$plugin_url = $tab ? add_query_arg( 'tab', $tab, $plugin_url ) : $plugin_url;
						wp_safe_redirect( $plugin_url, 307 );
						exit();
					endif;
				endif;
			}
		);

		add_action( 'uat_licence_key_visibility', array( &$this, 'uat_licence_key_visibility_hide' ), 10, 1 );

		$uat_default_content = new Moove_Activity_Content();
		$option_key          = $uat_default_content->moove_uat_get_key_name();
		$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );

		if ( $uat_key && ! isset( $uat_key['deactivation'] ) ) :
			do_action( 'uat_plugin_loaded' );
		endif;

		add_action( 'admin_menu', array( 'Moove_Activity_Controller', 'moove_register_activity_menu_page' ) );
		add_action( 'save_post', array( 'Moove_Activity_Controller', 'moove_track_user_access_save_post' ), 100 );
	}

	/**
	 * DataTables settings
	 * @param array $settings Settings.
	 */
	public static function uat_get_table_settings( $settings ) {
		$settings = false;
		if ( ! $settings || ( isset( $settings['cols'] ) && empty( $settings['cols'] ) ) ) :
			$settings['cols'] 	= [0,1,2,5,8,9,10];
			$settings['tc']			= apply_filters( 'uat_table_settings_filter_tc', 12 );
			$settings['len'] 		= 100;
		endif;
		$settings = apply_filters( 'uat_table_settings_filter', $settings );
		return $settings;
	}

	/**
	 * Sidebar Menu Links
	 * @param string $content Content.
	 * @param string $active_tab Active tab.
	 */
	public static function uat_sidebar_menu_cpt_links( $content, $active_tab ) {
		ob_start();
		$_post_types = uat_get_post_types( false );
		unset( $_post_types['attachment'] );
		foreach ( $_post_types as $_post_type ) :
			$_post_type_object = get_post_type_object( $_post_type );
			?>
				<a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=moove-activity-log&tab=<?php echo esc_attr( $_post_type ); ?>&sm=activity_tracking" class="nav-tab nav-cc-premium <?php echo $active_tab === $_post_type ? 'nav-tab-active' : ''; ?> nav-sm-activity_tracking">
					<?php echo esc_attr( $_post_type_object->label ); ?>
				</a>
			<?php
		endforeach;
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Activity Archives Settings
	 */
	public static function uat_activity_settings_archives( $activity_settings_option ) {
		$activity_settings        = array();
		ob_start();
		?>
			<tr>
				<th scope="row">
					<span><?php esc_html_e( 'Archives', 'user-activity-tracking-and-log' ); ?></span>
				</th>
				<td class="text-center">
					<span class="nat-checkbox-na">
						<span class="uat-checkbox-slider" data-disable="Premium">									
						</span>
						<!-- .uat-checkbox-slider -->
					</span>
				</td>
				<td>
				</td>
			</tr>
		<?php
		$content = apply_filters( 'uat_activity_settings_archives_response', ob_get_clean() );
		echo $content; // phpcs:ignore
	}

	/**
	 * Activity Custom Post Type Settings
	 */
	public static function uat_activity_settings_cpt( $activity_settings_option ) {
		$activity_settings        = array();
		$_post_types              = uat_get_post_types( false );
		unset( $_post_types['attachment'] );
		if ( is_array( $_post_types ) ) :
			foreach ( $_post_types as &$_post_type ) :
				$_post_type_object                = get_post_type_object( $_post_type );
				$activity_settings[ $_post_type ] = array(
					'post_type_label' => $_post_type_object->label					
				);
			endforeach;
		endif;
		ob_start();
		if ( is_array( $activity_settings ) && ! empty( $activity_settings ) ) :
			$limited = apply_filters( 'uat_delete_option_limit', true );
			foreach ( $activity_settings as $_post_type => $uat_pt_data ) : 
				?>
					<tr>
						<th scope="row">
							<span><?php echo esc_attr( $uat_pt_data['post_type_label'] ); ?></span>
						</th>
						<td class="text-center">
							<span class="nat-checkbox-na">
								<span class="uat-checkbox-slider" data-disable="Premium">									
								</span>
								<!-- .uat-checkbox-slider -->
							</span>
						</td>
						<td>
						</td>
					</tr>
				<?php
			endforeach;
		endif;
		$content = apply_filters( 'uat_activity_settings_cpt_response', ob_get_clean() );
		echo $content; // phpcs:ignore
	}

	/**
	 * Delete logs older than select values
	 * @param int $value Value.
	 * @param bool $limit Limited.
	 */
	public static function uat_delete_option_select_values( $value, $limited ) {
		if ( 1 === $value || ! $limited ) : 
			?>
			<option value="1" <?php echo $value && 1 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '1 day', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 

		if ( 2 === $value || ! $limited ) : 
			?>
			<option value="2" <?php echo $value && 2 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '2 days', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 

		if ( 3 === $value || ! $limited ) : 
			?>
			<option value="3" <?php echo $value && 3 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '3 days', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 

		if ( 4 === $value || ! $limited ) : 
			?>
			<option value="4"<?php echo $value && 4 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '4 days', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 
		
		if ( 5 === $value || ! $limited ) : 
			?>
			<option value="5" <?php echo $value && 5 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '5 days', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 

		if ( 6 === $value || ! $limited ) : 
			?>
			<option value="6" <?php echo $value && 6 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '6 days', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 

		if ( 7 === $value || ! $limited ) : 
			?>
			<option value="7" <?php echo $value && 7 === $value ? ' selected="selected"' : ''; ?>><?php esc_html_e( '1 week', 'user-activity-tracking-and-log' ); ?></option>
			<?php 
		endif; 
		?>

		<option value="14" <?php echo $value && 14 === $value ? ' selected="selected"' : ''; ?>>
			<?php esc_html_e( '2 weeks', 'user-activity-tracking-and-log' ); ?>
		</option>

		<option value="30"<?php echo $value && 30 === $value ? ' selected="selected"' : ''; ?>>
			<?php esc_html_e( '1 month', 'user-activity-tracking-and-log' ); ?>
		</option>

		<?php if ( 60 === $value || ! $limited ) : ?>
			<option value="60" <?php echo $value && 60 === $value ? ' selected="selected"' : ''; ?>>
				<?php esc_html_e( '2 months', 'user-activity-tracking-and-log' ); ?>
			</option>
		<?php endif; ?>

		<option value="120"<?php echo $value && 120 === $value ? ' selected="selected"' : ''; ?>>
			<?php esc_html_e( '3 months', 'user-activity-tracking-and-log' ); ?>
		</option>

		<option value="180"<?php echo $value && 180 === $value ? ' selected="selected"' : ''; ?>>
			<?php esc_html_e( '6 months', 'user-activity-tracking-and-log' ); ?>
		</option>

		<option value="365"<?php echo $value && 365 === $value ? ' selected="selected"' : ''; ?>>
			<?php esc_html_e( '1 year', 'user-activity-tracking-and-log' ); ?>
		</option>

		<?php if ( $value > 365 || ! $limited ) : ?>
			<option value="730"<?php echo $value && 730 === $value ? ' selected="selected"' : ''; ?>>
				<?php esc_html_e( '2 years', 'user-activity-tracking-and-log' ); ?>
			</option>
			
			<option value="1460"<?php echo $value && 1460 === $value ? ' selected="selected"' : ''; ?>>
				<?php esc_html_e( '4 years', 'user-activity-tracking-and-log' ); ?>
			</option>
		<?php endif;
	}

	/**
	 * Updating display_name value in log table
	 *
	 * @param int   $user_id User ID.
	 * @param array $old_user_data Old user data.
	 * @param array $userdata New user data.
	 */
	public static function uat_update_display_name_in_log( $user_id, $old_user_data, $userdata = array() ) {
		if ( $user_id && isset( $userdata['display_name'] ) && $userdata['display_name'] ) :
			$database_controller = new Moove_Activity_Database_Model();
			$where               = array( 'user_id' => $user_id );
			$user_logs           = $database_controller->update( array( 'display_name' => $userdata['display_name'] ), $where );
		endif;
	}

	/**
	 * Activity Menu - Sidebar Links.
	 *
	 * @param string $active_tab Active Tab.
	 * @param array  $plugin_settings Plugin Settings.
	 */
	public static function uat_sidebar_menu_links( $active_tab, $plugin_settings ) {
		?>
			<a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=moove-activity-log&tab=all_logs&sm=activity_tracking" class="nav-tab nav-t-btlr <?php echo 'all_logs' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-activity_tracking">
				<?php esc_html_e( 'Activity Log', 'user-activity-tracking-and-log' ); ?>
			</a>
		<?php
		$_post_types = uat_get_post_types();
		unset( $_post_types['attachment'] );
		foreach ( $_post_types as $_post_type ) :
			if ( isset( $plugin_settings[ $_post_type ] ) && intval( $plugin_settings[ $_post_type ] ) === 1 ) :
				$_post_type_object = get_post_type_object( $_post_type );
				?>
					<a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=moove-activity-log&tab=<?php echo esc_attr( $_post_type ); ?>&sm=activity_tracking" class="nav-tab <?php echo $active_tab === $_post_type ? 'nav-tab-active' : ''; ?> nav-sm-activity_tracking">
						<?php echo esc_attr( $_post_type_object->label ); ?>
					</a>
				<?php
			endif;
		endforeach;
		?>

		<a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=moove-activity-log&tab=archives-tracking&sm=activity_tracking" class="nav-tab nav-cc-premium nav-sm-activity_tracking <?php echo esc_attr( 'archives-tracking' === $active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'Archives', 'user-activity-tracking-and-log' ); ?>
		</a>

		<?php echo apply_filters( 'uat_sidebar_menu_cpt_links', '', $active_tab ); ?>

		<a href="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=moove-activity-log&tab=activity-groups&sm=activity_tracking" class="nav-tab nav-cc-premium nav-sm-activity_tracking <?php echo esc_attr( 'activity-groups' === $active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'Users', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=video-tutorial&sm=activity_tracking' ) ); ?>" class="nav-tab nav-tab-dark <?php echo 'video-tutorial' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-activity_tracking">
			<span class="dashicons dashicons-format-video"></span>
			<?php esc_html_e( 'Video Tutorial', 'user-activity-tracking-and-log' ); ?>
		</a>


		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=activity-settings&sm=settings' ) ); ?>" class="nav-tab <?php echo 'activity-settings' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-settings nav-t-btlr">
			<?php esc_html_e( 'General Settings', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=tracking-settings&sm=settings' ) ); ?>" class="nav-tab nav-cc-premium <?php echo 'tracking-settings' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-settings">
			<?php esc_html_e( 'User Tracking Settings', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=geolocation-tracking&sm=settings' ) ); ?>" class="nav-tab nav-cc-premium <?php echo 'geolocation-tracking' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-settings">
			<?php esc_html_e( 'Geo Location Tracking', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=permissions&sm=settings' ) ); ?>" class="nav-tab nav-cc-premium <?php echo 'permissions' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-settings">
			<?php esc_html_e( 'Permissions', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=advanced-settings&sm=settings' ) ); ?>" class="nav-tab nav-cc-premium <?php echo 'advanced-settings' === $active_tab ? 'nav-tab-active' : ''; ?> nav-t-bblr nav-sm-settings">
			<?php esc_html_e( 'Advanced Settings', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=documentation&sm=documentation' ) ); ?>" class="nav-tab nav-tab-dark <?php echo 'documentation' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-documentation">
			<span class="dashicons dashicons-book"></span>
			<?php esc_html_e( 'Documentation', 'user-activity-tracking-and-log' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=support&sm=support' ) ); ?>" class="nav-tab nav-tab-dark <?php echo 'support' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-support">
			<span class="dashicons dashicons-sos"></span>
			<?php esc_html_e( 'Support', 'user-activity-tracking-and-log' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=licence&sm=licence' ) ); ?>" class="nav-tab nav-tab-dark <?php echo 'licence' === $active_tab ? 'nav-tab-active' : ''; ?> nav-sm-licence">
			<span class="dashicons dashicons-admin-network"></span>
			<?php esc_html_e( 'Licence Manager', 'user-activity-tracking-and-log' ); ?>
		</a>

		<?php

		$tab_data = array(
			array(
				'name'  => esc_html__( 'Event Tracking Log', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium nav-t-btlr',
				'slug'  => 'et-log',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Triggers Setup', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium',
				'slug'  => 'et-triggers',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Triggers Log', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium',
				'slug'  => 'et-triggers-log',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Users', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium',
				'slug'  => 'et-users',
				'sm'    => 'event_tracking'
			),

			array(
				'name'  => esc_html__( 'Video Tutorial', 'user-activity-tracking-and-log' ),
				'icon'  => 'dashicons dashicons-format-video',
				'class' => 'nav-tab nav-tab-dark',
				'slug'  => 'et-video-tutorial',
				'sm'    => 'event_tracking'
			)
		);

		foreach ( $tab_data as $tab ) :
			ob_start();
			?>
				<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=' . $tab['slug'] . '&sm=' . $tab['sm'] ) ); ?>" class="<?php echo isset( $tab['class'] ) ? esc_attr( $tab['class'] ) . ' nav-sm-' . esc_attr( $tab['sm'] ) : ''; ?> <?php echo $active_tab === $tab['slug'] ? 'nav-tab-active' : ''; ?>">
					<?php if ( isset( $tab['icon'] ) && $tab['icon'] ) : ?>
						<i class="<?php echo esc_attr( $tab['icon'] ); ?>"></i>
					<?php endif; ?>
					<?php echo esc_html( $tab['name'] ); ?>
				</a>
			<?php
			$content = ob_get_clean();
			echo apply_filters( 'uat_tab_link_html', $content, $tab['slug'] ); // phpcs:ignore
		endforeach;
		?>
		<span class="nav-tab nav-tab-collapse">
			<i class="dashicons dashicons-admin-collapse"></i>
			<span><?php esc_html_e( 'Collapse Menu', 'user-activity-tracking-and-log' ); ?></span>
		</span>
		<?php
	}

	/**
	 * Supporting larger value for delete log.
	 *
	 * @param array  $activity_settings_option Settings.
	 * @param string $_post_type Post Type slug.
	 */
	public static function moove_activity_bl_delete_options( $activity_settings_option, $_post_type ) {
		if ( isset( $activity_settings_option[ $_post_type . '_transient' ] ) && intval( $activity_settings_option[ $_post_type . '_transient' ] ) > 14 && ! defined( 'MOOVE_UAT_PREMIUM_VERSION' ) ) :
			?>
				<option value="<?php echo esc_attr( $activity_settings_option[ $_post_type . '_transient' ] ); ?>" selected="selected" ><?php echo esc_attr( $activity_settings_option[ $_post_type . '_transient' ] ); ?> <?php esc_html_e( 'days', 'user-activity-tracking-and-log' ); ?></option>
			<?php
		endif;
	}

	/**
	 * Tab main section premium class
	 *
	 * @param array $classes Classes.
	 */
	public static function uat_tab_section_cnt_class_filter( $classes = array() ) {
		if ( defined( 'MOOVE_UAT_PREMIUM_VERSION' ) ) :
			$classes[] = 'uat-has-premium';
		endif;
		return $classes;
	}

	/**
	 * Licence key asterisks hide in admin area
	 *
	 * @param string $key Licence key.
	 */
	public static function uat_licence_key_visibility_hide( $key ) {
		if ( $key ) :
			$_key = explode( '-', $key );
			if ( $_key && is_array( $_key ) ) :
				$_hidden_key = array();
				$key_count   = count( $_key );
				for ( $i = 0; $i < $key_count; $i++ ) :
					if ( 0 === $i || ( $key_count - 1 ) === $i ) :
						$_hidden_key[] = $_key[ $i ];
					else :
						$_hidden_key[] = '****';
					endif;
				endfor;
				$key = implode( '-', $_hidden_key );
			endif;
		endif;
		return $key;
	}

	/**
	 * Event Tracking Navigation Menu
	 *
	 * @param string $active_tab Active Tab slug.
	 *
	 * @return void
	 */
	public static function uat_extend_et_screen_nav( $active_tab ) {

		$tab_data = array(
			array(
				'name'  => esc_html__( 'Event Tracking Log', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium nav-tab-first',
				'slug'  => 'et-log',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Triggers Setup', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium',
				'slug'  => 'et-triggers',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Triggers Log', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium',
				'slug'  => 'et-triggers-log',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Users', 'user-activity-tracking-and-log' ),
				'icon'  => '',
				'class' => 'nav-tab nav-cc-premium',
				'slug'  => 'et-users',
				'sm'    => 'event_tracking'
			),
			array(
				'name'  => esc_html__( 'Video Tutorial', 'user-activity-tracking-and-log' ),
				'icon'  => 'dashicons dashicons-format-video',
				'class' => 'nav-tab nav-tab-dark',
				'slug'  => 'et-video-tutorial',
				'sm'    => 'event_tracking'
			)
		);

		foreach ( $tab_data as $tab ) :
			ob_start();
			?>
				<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=' . $tab['slug'] . '&sm=' . $tab['sm'] ) ); ?>" class="<?php echo isset( $tab['class'] ) ? esc_attr( $tab['class'] ) . ' nav-sm-' . esc_attr( $tab['sm'] ) : ''; ?> <?php echo $active_tab === $tab['slug'] ? 'nav-tab-active' : ''; ?>">
					<?php if ( isset( $tab['icon'] ) && $tab['icon'] ) : ?>
						<i class="<?php echo esc_attr( $tab['icon'] ); ?>"></i>
					<?php endif; ?>
					<?php echo esc_html( $tab['name'] ); ?>
				</a>
			<?php
			$content = ob_get_clean();
			echo apply_filters( 'moove_activity_check_extensions', $content, $tab['slug'] ); // phpcs:ignore
		endforeach;
	}

	/**
	 * Default values for User Screen options
	 *
	 * @param array $screen_options Screen options array.
	 */
	public static function uat_activity_screen_options_extension( $screen_options = array() ) {
		if ( ! function_exists( 'moove_uat_addon_get_plugin_dir' ) ) :
			$screen_options                       = is_array( $screen_options ) ? $screen_options : array();
			$screen_options['moove-activity-dtf'] = 'b';
		endif;
		return $screen_options;
	}

	/**
	 * Disabled post type visiblity
	 *
	 * @param array $global_settings Global settings.
	 */
	public function moove_uat_filter_plugin_settings( $global_settings ) {
		$show_disabled = apply_filters( 'uat_show_disabled_cpt', false );
		if ( $show_disabled ) :
			$post_types = get_post_types( array( 'public' => true ) );
			unset( $post_types['attachment'] );
			foreach ( $post_types as $post_type ) :
				if ( isset( $global_settings[ $post_type ] ) ) :
					$global_settings[ $post_type ] = '1';
				endif;
			endforeach;
		endif;
		return $global_settings;
	}

	/**
	 * Premium restriction if add-on is not installed
	 */
	public function moove_activity_premium_section_ads() {

		if ( class_exists( 'Moove_Activity_Addon_View' ) ) :
			$add_on_view  = new Moove_Activity_Addon_View();
			$slug         = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false; // phpcs:ignore
			$view_content = false;

			if ( function_exists( 'uat_addon_get_plugin_directory' ) ) :
				if ( file_exists( uat_addon_get_plugin_directory() . '/views/moove/admin/settings/' . $slug . '.php' ) ) :
					$view_content = true;
				endif;
			else :
				$add_on_view = new Moove_Activity_Addon_View();
				if ( 'activity-screen-settings' === $slug ) :
					$slug = 'activity-settings';
				elseif ( 'geolocation-tracking' === $slug && defined( 'MOOVE_UAT_PREMIUM_VERSION' ) && MOOVE_UAT_PREMIUM_VERSION < '2.2' ) :
					$slug = 'geolocation-tracking';
				elseif ( 'tracking-settings' === $slug && defined( 'MOOVE_UAT_PREMIUM_VERSION' ) && MOOVE_UAT_PREMIUM_VERSION < '2.2' ) :
					$slug = 'tracking_settings';
				endif;
				$view_content = $add_on_view->load( 'moove.admin.settings.' . $slug, array() );
			endif;

			if ( ! $view_content && $slug && 'help' !== $slug ) :
				?>
				<div class="uat-locked-section">
					<span>
					<i class="dashicons dashicons-lock"></i>
					<h4>This feature is not supported in this version of the Premium Add-on.</h4>
					<p><strong><a href="<?php echo esc_url( admin_url( 'admin.php?page=moove-activity-log&tab=licence&sm=licence' ) ); ?>" class="uat_admin_link">Activate your licence</a> to download the latest version of the Premium Add-on.</strong></p>
					<p class="uat_license_info">Don’t have a valid licence key yet? <br><a href="<?php echo esc_url( MOOVE_SHOP_URL ); ?>/my-account" target="_blank" class="uat_admin_link">Login to your account</a> to generate the key or <a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log" class="uat_admin_link" target="_blank">buy a new licence here</a>.</p>
					<br />
					<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log" target="_blank" class="plugin-buy-now-btn">Buy Now</a>
					</span>
				</div>
				<!--  .uat-locked-section -->
				<?php
			endif;
		else :
			?>
			<div class="muat-locked-section">
				<span>
					<i class="dashicons dashicons-lock"></i>
					<h4>This feature is part of the Premium Add-on</h4>
					<?php
					$uat_default_content = new Moove_Activity_Content();
					$option_key          = $uat_default_content->moove_uat_get_key_name();
					$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );
					?>
					<?php if ( isset( $uat_key['deactivation'] ) || ( isset( $uat_key['activation'] ) && $uat_key['activation'] ) ) : ?>
						<p><strong><a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=licence&sm=licence' ) ); ?>" class="uat_admin_link">Activate your licence</a> or <a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log" class="uat_admin_link" target="_blank">buy a new licence here</a></strong>.</p>
						<?php else : ?>
						<p><strong>Do you have a licence key? <br />Insert your license key to the "<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=licence&sm=licence' ) ); ?>" class="uat_admin_link">Licence Manager</a>" and activate it.</strong></p>
					<?php endif; ?>
					<br />

					<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log" target="_blank" class="plugin-buy-now-btn">Buy Now</a>
				</span>

			</div>
			<!--  .uat-locked-section -->
			<?php
		endif;
	}

	/**
	 * Filter applied to $content returned by View controller, trimmed version
	 *
	 * @param string $content Content.
	 * @param string $slug Active tab slug.
	 */
	public function moove_activity_check_extensions( $content, $slug ) {
		$return = $content;
		if ( class_exists( 'Moove_Activity_Addon_View' ) ) :
			if ( function_exists( 'uat_addon_get_plugin_directory' ) ) :
				;
				if ( file_exists( uat_addon_get_plugin_directory() . '/views/moove/admin/settings/' . $slug . '.php' ) ) :
					$return = '';
				endif;
			else :
				$add_on_view  = new Moove_Activity_Addon_View();
				$view_content = $add_on_view->load( 'moove.admin.settings.' . $slug, array() );
				if ( $view_content ) :
					$return = '';
				endif;
			endif;
		endif;
		return $return;
	}

	/**
	 * Filter applied to $content returned by View controller, non-trimmed version
	 *
	 * @param string $content Content.
	 * @param string $slug Active tab slug.
	 */
	public function moove_activity_check_tab_content( $content, $slug ) {
		$_return = $content;
		if ( class_exists( 'Moove_Activity_Addon_View' ) ) :
			$add_on_view = new Moove_Activity_Addon_View();
			if ( function_exists( 'uat_addon_get_plugin_directory' ) ) :
				if ( file_exists( uat_addon_get_plugin_directory() . '/views/moove/admin/settings/' . $slug . '.php' ) ) :
					$_return = '';
				endif;
			else :
				$view_content = $add_on_view->load( 'moove.admin.settings.' . $slug, array() );
				if ( $view_content ) :
					$_return = '';
				endif;
			endif;
		endif;
		return $_return;
	}

	/**
	 * Tab content filter
	 *
	 * @param string $data Data.
	 * @param string $active_tab Active tab slug.
	 */
	public function moove_activity_tab_content( $data, $active_tab = '' ) {
		$uat_view = new Moove_Activity_View();
		$content  = $uat_view->load( 'moove.admin.settings.' . $data['tab'], true );
		echo apply_filters( 'moove_activity_check_tab_content', $content, $data['tab'] ); // phpcs:ignore
	}

	/**
	 * Register Front-end / Back-end scripts
	 *
	 * @return void
	 */
	public function moove_register_scripts() {
		if ( is_admin() ) :
			add_action( 'admin_enqueue_scripts', array( &$this, 'moove_activity_admin_scripts' ) );
		else :
			add_action( 'wp_enqueue_scripts', array( &$this, 'moove_frontend_activity_scripts' ) );
		endif;
	}

	/**
	 * Activity filter hook
	 *
	 * @param string $filters Filters.
	 * @param string $content Content.
	 */
	public function moove_activity_filters( $filters, $content ) {
		echo $filters; // phpcs:ignore
	}

	/**
	 * Register global variables to head, AJAX, Form validation messages
	 *
	 * @param  string $ascript The registered script handle you are attaching the data for.
	 * @return void
	 */
	public function moove_localize_script( $ascript ) {
		$archive_title = get_the_archive_title();

		$activity_loc_data      	= array(
			'activityoptions' 		=> get_option( 'moove_activity-options' ),
			'referer'         		=> esc_url( uat_get_referer() ),
			'ajaxurl'         		=> admin_url( 'admin-ajax.php' ),
			'post_id'         		=> get_the_ID(),
			'is_page'         		=> is_page(),
			'is_single'       		=> is_single(),
			'is_archive' 					=> is_archive(),
			'is_front_page'				=> is_front_page(),
			'is_home'							=> is_home(),
			'archive_title'				=> wp_slash( strip_tags( $archive_title ) ),
			'current_user'    		=> get_current_user_id(),
			'referrer'        		=> esc_url( uat_get_referer() ),
			'extras'          		=> wp_json_encode( array() )
		);
		$_data_to_check = array(
			'post_id'      	=> isset( $activity_loc_data['post_id'] ) ? $activity_loc_data['post_id'] : false,
			'user_id'      	=> isset( $activity_loc_data['current_user'] ) ? intval( $activity_loc_data['current_user'] ) : false,
			'post_type'    	=> get_post_type(),
			'campaign_id'		=> 'pre_check'
		);
		$pre_check_log 	= apply_filters( 'moove_uat_filter_data', $_data_to_check );
		$activity_loc_data['log_enabled'] = $pre_check_log;

		$this->activity_loc_data = apply_filters( 'moove_uat_extend_loc_data', $activity_loc_data );
		wp_localize_script( $ascript, 'moove_frontend_activity_scripts', $this->activity_loc_data );
	}

	/**
	 * Register global variables to head, AJAX, Form validation messages
	 *
	 * @param  string $ascript The registered script handle you are attaching the data for.
	 * @return void
	 */
	public function moove_localize_script_admin( $ascript ) {
		$activity_loc_data_admin      	= array(
			'ajaxurl'         		=> admin_url( 'admin-ajax.php' ),
			'extras'							=> class_exists('Activity_User_Sessions_DB_Manager') ? 'usla' : 'alsu',
			'rsp'									=> apply_filters( 'uat_dt_responsive_table', false ),
			'tsop'								=> apply_filters( 'uat_get_table_settings', [] )
		);
		$this->activity_loc_data_admin = apply_filters( 'moove_uat_extend_loc_data_admin', $activity_loc_data_admin );
		wp_localize_script( $ascript, 'moove_backend_activity_scripts', $this->activity_loc_data_admin );
	}

	/**
	 * Registe FRONT-END Javascripts and Styles
	 *
	 * @return void
	 */
	public function moove_frontend_activity_scripts() {
		wp_enqueue_script( 'moove_activity_frontend', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/js/moove_activity_frontend.js', array( 'jquery' ), MOOVE_UAT_VERSION, true );
		$this->moove_localize_script( 'moove_activity_frontend' );
	}

	/**
	 * Registe BACK-END Javascripts and Styles
	 *
	 * @return void
	 */
	public function moove_activity_admin_scripts() {
		$enabled_post_types   = uat_get_enabled_post_types();
		$enabled_post_types   = $enabled_post_types && is_array( $enabled_post_types ) ? $enabled_post_types : array();
		$enabled_post_types[] = 'all_logs';
		wp_verify_nonce( 'uat_nonce', 'uat_settings_nonce' );
		$submenu = isset( $_GET['sm'] ) ? sanitize_text_field( wp_unslash( $_GET['sm'] ) ) : 'activity_tracking';
		if ( 'activity_tracking' === $submenu && isset( $_GET['page'] ) && 'moove-activity-log' === sanitize_text_field( $_GET['page'] ) ) :
			wp_enqueue_script( 'activity_datatables', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/js/activity-dt.js', array( 'jquery' ), MOOVE_UAT_VERSION, true );
			wp_enqueue_style( 'activity_datatables', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/css/activity-dt.css', '', MOOVE_UAT_VERSION );
		endif;

		wp_enqueue_script( 'moove_activity_backend', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/js/moove_activity_backend.js', array( 'jquery' ), MOOVE_UAT_VERSION, true );
		$this->moove_localize_script( 'moove_activity_backend' );
		$this->moove_localize_script_admin( 'moove_activity_backend' );
		wp_enqueue_style( 'moove_activity_backend', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/css/moove_activity_backend.css', '', MOOVE_UAT_VERSION );
	}

	/**
	 * Enqueue a script in the WordPress admin, excluding Activity Settings page.
	 *
	 * @param int $hook Hook suffix for the current admin page.
	 */
	public function uat_thirdparty_admin_scripts( $hook ) {
		if ( 'toplevel_page_moove-activity-log' !== $hook ) :
			return;
		endif;
		wp_enqueue_script( 'uat_codemirror_script', plugins_url( basename( dirname( __FILE__ ) ) ) . '/assets/js/codemirror.js', array(), MOOVE_UAT_VERSION, true );
	}
}
new Moove_Activity_Actions();

