<?php
/**
 * Moove_Activity_Actions File Doc Comment
 *
 * @category  Moove_Controller
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_Content Class Doc Comment
 *
 * @category Class
 * @package  Moove_Controller
 * @author   Moove Agency
 */
class Moove_Activity_Content {
	/**
	 * Construct
	 */
	public function __construct() {

	}

	/**
	 * Returns the site activation key
	 *
	 * @param string $option_key Option key.
	 */
	public function uat_get_activation_key( $option_key ) {
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

	/**
	 * Checks the log status when the post being saved.
	 *
	 * @param int    $post_id  The post's id if the function is called from another controller.
	 * @param string $action Can be enabled or delete.
	 */
	public static function moove_save_post( $post_id, $action = false ) {

		if ( isset( $post_id ) ) :
			$pid = $post_id;
		else :
			$pid = isset( $_POST['post_ID'] ) ? intval( $_POST['post_ID'] ) : ''; // phpcs:ignore
		endif;

		if ( ! $pid ) {
			$pid = '';
		}

		// We are deleting campaign.
		if ( isset( $_POST['ma-delete-campaign'] ) ) : // phpcs:ignore
			$campaign_id_sanitized = sanitize_key( wp_unslash( $_POST['ma-delete-campaign'] ) ); // phpcs:ignore
		endif;

		if ( ( isset( $campaign_id_sanitized ) && intval( $campaign_id_sanitized ) === 1 ) ) :
			delete_post_meta( $pid, 'ma_data' );
			update_post_meta( $pid, 'ma_disabled', '1' );
			$uat_db_controller = new Moove_Activity_Database_Model();
			$end_date          = gmdate( 'Y-m-d H:i:s' );
			$uat_db_controller->remove_old_logs( $pid, $end_date );
			return; // Break the function.
		endif;
		$trigger_campaign = false;
		// We don't need to create any campaign.
		if ( isset( $_POST['ma-trigger-campaign'] ) ) : // phpcs:ignore
			$trigger_campaign = sanitize_key( wp_unslash( $_POST['ma-trigger-campaign'] ) ); // phpcs:ignore
			if ( ! isset( $trigger_campaign ) ) :
				if ( 'enable' !== $action ) :
					return;
				endif;
			endif;
		endif;

		// Get data for this post.
		$_post_meta = get_post_meta( $pid, 'ma_data' );
		$_post_meta = isset( $_post_meta[0] ) ? $_post_meta : array( 0 => '' );
		if ( isset( $_post_meta[0] ) ) :
			$_ma_data_option = $_post_meta[0];
			$ma_data         = unserialize( $_ma_data_option ); // phpcs:ignore
			// If we have the campaign ID set already, don't do anything.
			if ( isset( $ma_data['campaign_id'] ) && '' !== $ma_data['campaign_id'] ) :
				return;
			endif;

			// We can go ahead and create campaign.
			$campaign_id            = time() . $pid;
			$ma_data['campaign_id'] = $campaign_id;

			$post_type 	= get_post_type( $post_id );
			$settings  	= get_option( 'moove_post_act' );
			$settings 	= apply_filters( 'moove_uat_filter_plugin_settings', $settings );

			if ( isset( $settings[ $post_type ] ) && intval( $settings[ $post_type ] ) !== 0 ) :
				update_post_meta( $pid, 'ma_data', serialize( $ma_data ) ); // phpcs:ignore
				update_post_meta( $pid, 'ma_disabled', '0' );
			endif;

			if ( intval( $trigger_campaign ) === 1 ) :
				update_post_meta( $pid, 'ma_data', serialize( $ma_data ) ); // phpcs:ignore
				update_post_meta( $pid, 'ma_disabled', '0' );
			endif;
		endif;
	}

	/**
	 * Adding META-BOX for protection
	 */
	public static function moove_activity_meta_boxes() {
		$post_types      = get_post_types( array( 'public' => true ) );
		$plugin_settings = apply_filters( 'moove_uat_filter_plugin_settings', get_option( 'moove_post_act' ) );
		unset( $post_types['attachment'] );
		foreach ( $post_types as $post_type ) :
			if ( isset( $plugin_settings[ $post_type ] ) && intval( $plugin_settings[ $post_type ] ) === 1 ) :
				add_meta_box(
					'ma-main-meta-box',
					__( 'User Activity Tracking and Log', 'user-activity-tracking-and-log' ),
					array( 'Moove_Activity_Content', 'moove_main_meta_box_callback' ),
					$post_type,
					'normal',
					'default'
				);
		endif;
	endforeach;
	}

	/**
	 * Meta box callback
	 */
	public static function moove_main_meta_box_callback() {
		$post_id           = get_the_ID();
		$ma_data           = array();
		$uat_view          = new Moove_Activity_View();
		$uat_db_controller = new Moove_Activity_Database_Model();
		$global_setup      = array();
		if ( $post_id ) :
			if ( isset( $post_id ) ) :
				$_post_meta = get_post_meta( $post_id, 'ma_data' );
				if ( isset( $_post_meta[0] ) ) :
					$_ma_data_option = $_post_meta[0];
					$ma_data         = unserialize( $_ma_data_option ); // phpcs:ignore
				endif;
				if ( isset( $ma_data['campaign_id'] ) ) :
					$activity = $uat_db_controller->get_log( 'post_id', $post_id, 5 );
					if ( $activity && is_array( $activity ) ) :
						foreach ( $activity as $log ) :
							$data             = array(
								'post_id'         => $log->post_id,
								'time'            => $log->visit_date,
								'uid'             => $log->user_id,
								'display_name'    => $log->display_name,
								'show_ip'         => $log->user_ip,
								'response_status' => $log->status,
								'referer'         => $log->referer,
								'city'            => $log->city
							);
							$data             = apply_filters( 'uat_filter_data_entry', $data, $log );
							$ma_data['log'][] = $data;

						endforeach;
					endif;
				endif;

				$post_type    = get_post_type( $post_id );
				$settings     = get_option( 'moove_post_act' );
				$global_setup = isset( $settings[ $post_type ] ) ? $settings[ $post_type ] : array();

			else :
				$ma_data = array();
			endif;
		else :
			$ma_data = array();
		endif;

		echo $uat_view->load( // phpcs:ignore
			'moove.admin.activity-metabox',
			array(
				'activity'     => $ma_data, // phpcs:ignore
				'global_setup' => $global_setup // phpcs:ignore
			)
		);

	}

	/**
	 * Licence token generator
	 */
	public static function get_license_token() {
		$license_token = trailingslashit( site_url() );
		return $license_token;
	}

	/**
	 * Licence hash
	 */
	public function get_license_hash() {
		$license_token = is_multisite() ? trailingslashit( network_home_url() ) : trailingslashit( site_url() );
		return $license_token;
	}

	/**
	 * Option key
	 */
	public static function moove_uat_get_key_name() {
		return 'moove_uat_plugin_key';
	}

	/**
	 * Licence buttons based on response
	 *
	 * @param string $response Response.
	 * @param string $uat_key Key.
	 */
	public static function uat_licence_action_button( $response, $uat_key ) {
		$type = isset( $response['type'] ) ? $response['type'] : false;
		if ( 'expired' === $type || 'activated' === $type || 'max_activation_reached' === $type ) :
			if ( 'activated' !== $type ) :
				?>
					<button type="submit" name="uat_activate_license" class="button button-primary button-inverse">
						<?php esc_html_e( 'Activate', 'user-activity-tracking-and-log' ); ?>
					</button>
				<?php
			endif;
		elseif ( 'invalid' === $type ) :
			?>
			<button type="submit" name="uat_activate_license" class="button button-primary button-inverse">
				<?php esc_html_e( 'Activate', 'user-activity-tracking-and-log' ); ?>
			</button>
			<?php
		else :
			?>
			<button type="submit" name="uat_activate_license" class="button button-primary button-inverse">
				<?php esc_html_e( 'Activate', 'user-activity-tracking-and-log' ); ?>
			</button>
			<br /><br />
			<hr />
			<h4 style="margin-bottom: 0;"><?php esc_html_e( 'Buy licence', 'user-activity-tracking-and-log' ); ?></h4>
			<p>
				<?php
				$store_link = __( 'You can buy licences from our [store_link]online store[/store_link].', 'user-activity-tracking-and-log' );
				$store_link = str_replace( '[store_link]', '<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="uat_admin_link">', $store_link );
				$store_link = str_replace( '[/store_link]', '</a>.', $store_link );
				echo $store_link; // phpcs:ignore
				?>
			</p>
			<p>
				<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="button button-primary">Buy Now</a>
			</p>
			<br />
			<hr />

			<?php
		endif;
	}
	/**
	 * Licence key input field
	 *
	 * @param string $response Response.
	 * @param string $uat_key Key.
	 */
	public static function uat_licence_input_field( $response, $uat_key ) {
		$type = isset( $response['type'] ) ? $response['type'] : false;
		if ( 'expired' === $type ) :
			// LICENSE EXPIRED.
			?>
			<tr>
				<th scope="row" style="padding: 0 0 10px 0;">
					<hr />
					<h4 style="margin-bottom: 0;"><?php esc_html_e( 'Renew your licence', 'user-activity-tracking-and-log' ); ?></h4>
					<p><?php esc_html_e( 'Your licence has expired. You will not receive the latest updates and features unless you renew your licence.', 'user-activity-tracking-and-log' ); ?></p>
					<a href="<?php echo esc_url( MOOVE_SHOP_URL ); ?>?renew=<?php echo esc_attr( $response['key'] ); ?>" target="_blank" class="button button-primary">Renew Licence</a>
					<br /><br />
					<hr />

					<h4 style="margin-bottom: 0;"><?php esc_html_e( 'Enter new licence key', 'user-activity-tracking-and-log' ); ?></h4>
				</th>
			</tr>
			<tr>
				<td style="padding: 0 5px 0 0;">
					<input name="moove_uat_license_key" required min="35" type="text" id="moove_uat_license_key" value="" class="regular-text">
				</td>
			</tr>
			<?php
		elseif ( 'activated' === $type || 'max_activation_reached' === $type ) :
			// LICENSE ACTIVATED.
			?>
			<tr>
				<th scope="row" style="padding: 0 0 10px 0;">
					<hr />
					<h4 style="margin-bottom: 0;"><?php esc_html_e( 'Buy more licences', 'user-activity-tracking-and-log' ); ?></h4>
					<p>
						<?php
						$store_link = __( 'You can buy more licences from our [store_link]online store[/store_link].', 'user-activity-tracking-and-log' );
						$store_link = str_replace( '[store_link]', '<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="uat_admin_link">', $store_link );
						$store_link = str_replace( '[/store_link]', '</a>', $store_link );
						echo $store_link; // phpcs:ignore
						?>
					</p>
					<p>
						<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="button button-primary">Buy Now</a>
					</p>
					<br />
					<hr />
				</th>
			</tr>
			<?php
			if ( 'max_activation_reached' === $type ) :
				?>
					<tr>
						<th scope="row" style="padding: 0 0 10px 0;">
							<label><?php esc_html_e( 'Enter a new licence key:', 'user-activity-tracking-and-log' ); ?></label>
						</th>
					</tr>
					<tr>
						<td style="padding: 0 5px 0 0;">
							<input name="moove_uat_license_key" required min="35" type="text" id="moove_uat_license_key" value="" class="regular-text">
						</td>
					</tr>
				<?php
			endif;
		elseif ( 'invalid' === $type ) :
			?>
			<tr>
				<th scope="row" style="padding: 0 0 10px 0;">
					<hr />
					<h4 style="margin-bottom: 0;"><?php esc_html_e( 'Buy licence', 'user-activity-tracking-and-log' ); ?></h4>
					<p>
						<?php
						$store_link = __( 'You can buy licences from our [store_link]online store[/store_link].', 'user-activity-tracking-and-log' );
						$store_link = str_replace( '[store_link]', '<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="uat_admin_link">', $store_link );
						$store_link = str_replace( '[/store_link]', '</a>.', $store_link );
						echo $store_link; // phpcs:ignore
						?>
					</p>
					<p>
						<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="button button-primary">Buy Now</a>
					</p>
					<br />
					<hr />
				</th>
			</tr>
			<tr>
				<th scope="row" style="padding: 0 0 10px 0;">
					<label><?php esc_html_e( 'Enter your licence key:', 'user-activity-tracking-and-log' ); ?></label>
				</th>
			</tr>
			<tr>
				<td style="padding: 0 5px 0 0;">
					<input name="moove_uat_license_key" required min="35" type="text" id="moove_uat_license_key" value="" class="regular-text">
				</td>
			</tr>
			<?php
		else :
			?>
			<tr>
				<th scope="row" style="padding: 0 0 10px 0;">
					<label><?php esc_html_e( 'Enter licence key:', 'user-activity-tracking-and-log' ); ?></label>
				</th>
			</tr>
			<tr>
				<td style="padding: 0 5px 0 0;">
					<input name="moove_uat_license_key" required min="35" type="text" id="moove_uat_license_key" value="" class="regular-text">
				</td>
			</tr>
			<?php
		endif;
	}
	/**
	 * Alert box
	 *
	 * @param string $type Type.
	 * @param array  $response Response.
	 * @param string $uat_key Key.
	 */
	public static function uat_get_alertbox( $type, $response, $uat_key ) {
		if ( 'error' === $type ) :
			$messages = isset( $response['message'] ) && is_array( $response['message'] ) ? implode( '</p><p>', $response['message'] ) : '';
			if ( 'inactive' === $response['type'] ) :
				$uat_default_content = new Moove_Activity_Content();
				$option_key          = $uat_default_content->moove_uat_get_key_name();
				$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );
				update_option(
					$option_key,
					array(
						'key'          => $response['key'],
						'deactivation' => strtotime( 'now' )
					)
				);
				$uat_key = $uat_default_content->uat_get_activation_key( $option_key );
			endif;
			?>
			<div class="uat-admin-alert uat-admin-alert-error">
				<div class="uat-alert-content">        
					<div class="uat-licence-key-wrap">
						<p>License key: 
						<strong><?php echo esc_attr( apply_filters( 'uat_licence_key_visibility', isset( $response['key'] ) ? $response['key'] : ( isset( $uat_key['key'] ) ? $uat_key['key'] : $uat_key ) ) ); ?></strong>								
						</p>
					</div>
					<!-- .uat-licence-key-wrap -->
					<p><?php echo $messages; // phpcs:ignore ?></p>
				</div>
				<span class="dashicons dashicons-dismiss"></span>
			</div>
			<!--  .uat-admin-alert uat-admin-alert-success -->
			<?php
		else :
			$messages = isset( $response['message'] ) && is_array( $response['message'] ) ? implode( '</p><p>', $response['message'] ) : '';
			if ( isset( $response['type'] ) && 'activated' === $response['type'] ) :
				$messages .= '<p>Thanks for activating the premium licence.</p><p>We recommend you to review the <a href="' . esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=activity-settings&sm=settings' ) ) . '" class="uat_admin_link">General Settings</a> screen and adjust the settings accordingly.</p>';
			endif;
			?>
			<div class="uat-admin-alert uat-admin-alert-success">    
				<div class="uat-alert-content">  
					<div class="uat-licence-key-wrap">       
						<p>License key: 
							<strong><?php echo esc_attr( apply_filters( 'uat_licence_key_visibility', isset( $response['key'] ) ? $response['key'] : ( isset( $uat_key['key'] ) ? $uat_key['key'] : $uat_key ) ) ); ?></strong>
						</p>
					</div>
					<!-- .uat-licence-key-wrap -->
					<p><?php echo $messages; // phpcs:ignore ?></p>
				</div>
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<!--  .uat-admin-alert uat-admin-alert-success -->
			<?php
		endif;
		do_action( 'uat_plugin_updater_notice' );
	}

	/**
	 * Log restriction by user permission
	 *
	 * @param string $active_tab Tab slug.
	 */
	public static function uat_activity_log_restriction_content( $active_tab ) {
		$setting_tabs = array(
			'activity-settings',
			'geolocation-tracking',
			'tracking-settings',
			'permissions',
			'advanced-settings',
			'licence',
			'et-triggers',
			'et-triggers-log',
			'et-log',
			'et-users',
			'video-tutorial'
		);

		if ( ! in_array( $active_tab, $setting_tabs, true ) ) :
			?>
			<div class="uat-locked-section">
				<span>
				<i class="dashicons dashicons-lock"></i>
				<h4>You do not have sufficient privileges to view this Activity Screen</h4>
				<p><strong>Please contact your website Administrator to grant you an access to this screen</strong></p>
				<br />
				</span>
			</div>
			<!--  .uat-locked-section -->
			<?php
		endif;
	}

	/**
	 * Settings restriction by user permission
	 *
	 * @param string $active_tab Tab slug.
	 */
	public static function uat_log_settings_restriction_content( $active_tab ) {
		$setting_tabs = array(
			'activity-settings',
			'geolocation-tracking',
			'tracking-settings',
			'permissions',
			'advanced-settings',
			'et-triggers',
			'licence'
		);

		if ( in_array( $active_tab, $setting_tabs, true ) ) :
			?>
				<div class="uat-locked-section">
					<span>
					<i class="dashicons dashicons-lock"></i>
					<h4>You do not have sufficient privileges to view this Settings Screen</h4>
					<p><strong>Please contact your website Administrator to grant you an access to this screen</strong></p>
					<br />
					</span>
				</div>
				<!--  .uat-locked-section -->
			<?php
		else :
			$content = array(
				'tab'  => $active_tab,
				'data' => array()
			);
			do_action( 'moove_activity_tab_content', $content, $active_tab );
		endif;
	}

	/**
	 * Update alert
	 */
	public static function uat_premium_update_alert() {

		$plugins     = get_site_transient( 'update_plugins' );
		$lm          = new Moove_UAT_License_Manager();
		$plugin_slug = $lm->get_add_on_plugin_slug();

		if ( isset( $plugins->response[ $plugin_slug ] ) && is_plugin_active( $plugin_slug ) ) :
			$version = $plugins->response[ $plugin_slug ]->new_version;

			$current_user = wp_get_current_user();
			$user_id      = isset( $current_user->ID ) ? $current_user->ID : 0;

			if ( isset( $plugins->response[ $plugin_slug ]->package ) && ! $plugins->response[ $plugin_slug ]->package ) :

				$uat_default_content = new Moove_Activity_Content();
				$option_key          = $uat_default_content->moove_uat_get_key_name();
				$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );
				$license_key         = isset( $uat_key['key'] ) ? sanitize_text_field( $uat_key['key'] ) : false;
				$renew_link          = MOOVE_SHOP_URL . '?renew=' . $license_key;
				$license_manager     = admin_url( 'admin.php' ) . '?page=moove-activity-log&amp;tab=licence';
				$purchase_link       = 'https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/';
				$notice_text         = '';
				if ( $license_key && isset( $uat_key['activation'] ) ) :
						// Expired.
					$notice_text = 'Update is not available until you <a href="' . $renew_link . '" target="_blank">renew your licence</a>. You can also update your licence key in the <a href="' . $license_manager . '">Licence Manager</a>.';
				elseif ( $license_key && isset( $uat_key['deactivation'] ) ) :
						// Deactivated.
					$notice_text = 'Update is not available until you <a href="' . $purchase_link . '" target="_blank">purchase a licence</a>. You can also update your licence key in the <a href="' . $license_manager . '">Licence Manager</a>.';
				elseif ( ! $license_key ) :
						// No license key installed.
					$notice_text = 'Update is not available until you <a href="' . $purchase_link . '" target="_blank">purchase a licence</a>. You can also update your licence key in the <a href="' . $license_manager . '">Licence Manager</a>.';
				endif;
				?>
				<div class="uat-cookie-alert uat-cookie-update-alert" style="display: inline-block;">
					<h4><?php esc_html_e( 'There is a new version of User Activity Tracking and Log - Premium Add-On.', 'user-activity-tracking-and-log' ); ?></h4>
					<p><?php echo $notice_text; // phpcs:ignore ?></p>
				</div>
				<!--  .uat-cookie-alert -->
				<?php
				endif;

		endif;
	}
}
$moove_activity_content_provider = new Moove_Activity_Content();
