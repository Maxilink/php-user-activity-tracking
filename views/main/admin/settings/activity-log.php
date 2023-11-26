<?php
/**
 * Activity Log Doc Comment
 *
 * @category  Views
 * @package   user-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
if ( function_exists('wp_cache_flush_group') ) :
	wp_cache_flush_group( 'user-activity-tracking-and-log' );
else: 
	wp_cache_flush();
endif;
$uat_controller  = new Moove_Activity_Controller();
$view_cnt        = new Moove_Activity_View();
$plugin_settings = apply_filters( 'moove_uat_filter_plugin_settings', get_option( 'moove_post_act' ) );

if ( ! $plugin_settings || empty( $plugin_settings ) ) :
	if ( function_exists( 'moove_set_options_values' ) ) :
		moove_set_options_values();
		$plugin_settings  			= get_option( 'moove_post_act' );
	endif;
endif;

$activity_perm = apply_filters( 'uat_activity_log_capability', 'manage_options' );

$settings_perm = apply_filters( 'uat_log_settings_capability', 'manage_options' );

$logs_imported = $uat_controller->moove_importer_check_database();
if ( ! $logs_imported ) :
	$uat_controller->import_log_to_database();
endif;

$screen_options = get_user_meta( get_current_user_id(), 'moove_activity_screen_options', true );
$screen_options = apply_filters( 'uat_activity_screen_options_extension', $screen_options );
$selected_val   = isset( $screen_options['moove-activity-dtf'] ) ? $screen_options['moove-activity-dtf'] : 'b';

$default_tabs = array( 'all_logs', 'settings' );
wp_verify_nonce( 'uat_log_nonce', 'uat_log_nonce_f' );

if ( isset( $_GET['tab'] ) ) :
	$active_tab = rawurlencode( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) );
	$active_tab = $active_tab ? $active_tab : 'all_logs';
else :
	$active_tab = 'all_logs';
endif;

$sub_menu_tab = '';
if ( isset( $_GET['sm'] ) ) :
	$sub_menu_tab = rawurlencode( sanitize_text_field( wp_unslash( $_GET['sm'] ) ) );
	$sub_menu_tab = $sub_menu_tab ? $sub_menu_tab : '';
else :
	$sub_menu_tab = 'all_logs' === $active_tab ? 'activity_tracking' : $sub_menu_tab;
endif;

$_orderby = 'date';
$_order   = 'asc';

$uat_controller->delete_abandoned_logs();

?>

<?php if ( $sub_menu_tab ) : ?>
	<style>
		.uat-tab-section-cnt a.nav-tab:not(.nav-sm-<?php echo esc_attr( $sub_menu_tab ); ?>) {
			display: none !important;
		}
	</style>
<?php endif; ?>

<!-- Wrap for notifications -->
<div class="wrap" style="margin: 0; border: none;">
	<h2 class="nav-tab-wrapper" style="border: none; opacity: 0; padding: 0; height: 0;"></h2>
</div>
<!-- .wrap -->

<!-- .nav-tab-wrapper -->
<link rel="stylesheet" type="text/css" href="<?php echo esc_url( moove_activity_get_plugin_dir() ); ?>/assets/css/moove_activity_backend_select2.css" >

<div class="uat-admin-header-section">
	<h2><?php esc_html_e( 'User Activity Tracking and Log', 'user-activity-tracking-and-log' ); ?> <span class="uat-plugin-version"><?php echo 'v' . esc_attr( MOOVE_UAT_VERSION ); ?></span></h2>
	<br>
</div>
<!--  .uat-header-section -->

<div id="moove-activity-message-cnt"></div>
<!-- #moove-activity-message-cnt -->

<div class="wrap moove-activity-plugin-wrap uat-clearfix uat-active-submenu-<?php echo esc_attr( $sub_menu_tab ); ?> <?php echo isset( $_GET['collapsed'] ) ? 'uat-collapsed' : ''; ?>" id="uat-settings-cnt">

	<div class="uat-tab-section-cnt <?php echo esc_attr( implode( ' ', apply_filters( 'uat_tab_section_cnt_class', array() ) ) ); ?>">
		<?php do_action( 'uat_premium_update_alert' ); ?>
		<h2 class="nav-tab-wrapper">
			<?php do_action( 'uat_sidebar_menu_links', $active_tab, $plugin_settings ); ?>
		</h2>

		<div class="moove-form-container <?php echo esc_attr( $active_tab ); ?>">
			<div class="moove-activity-log-report">
				<?php
				if ( 'all_logs' === $active_tab ) :
					if ( current_user_can( $activity_perm ) ) :
						?>
							<div class="all-logs-header">
							<h2><?php esc_html_e( 'All Logs', 'user-activity-tracking-and-log' ); ?></h2>
								<div class="search-box" id="uat-search-box">
									<label class="screen-reader-text" for="post-search-input">
										<?php esc_html_e( 'Search Posts', 'user-activity-tracking-and-log' ); ?>:
									</label>
									<input type="search" id="post-search-input" name="s" placeholder="<?php esc_html_e( 'Search Logs...', 'user-activity-tracking-and-log' ); ?>" value="">
								</div>
								<hr>
							</div>
							<!-- .all-logs-header -->
						<?php
					endif;
				endif;

				if ( in_array( $active_tab, $default_tabs, true ) || post_type_exists( $active_tab ) || $active_tab === 'archives-tracking' ) :
					if ( current_user_can( $activity_perm ) ) :
						if ( 'all_logs' === $active_tab ) :
							echo $view_cnt->load( 'moove.admin.settings.activity-log-all', array() ); // phpcs:ignore 
						elseif ( 'archives-tracking' === $active_tab ) :
							?>
							<h2><?php esc_html_e('Archives Log', 'user-activity-tracking-and-log'); ?></h2>
							<hr>
							<?php
						elseif ( isset( $_GET['tab'] ) ) :
							?>
							<h2>
								<?php
									$_post_type = get_post_type_object( $active_tab );
									echo $_post_type ? esc_attr( $_post_type->label ) : '';
								?>
							</h2>
							<hr>						
							<?php
							echo $view_cnt->load( 'moove.admin.settings.activity-log-cpt', array( 'post_type' => $active_tab ) ); // phpcs:ignore 
						endif;
					else :
						do_action( 'uat_activity_log_restriction_content', $active_tab );
					endif;
				else :
					if ( current_user_can( $activity_perm ) ) :
						do_action( 'uat_extend_activity_screen_table', $active_tab );
					else :
						do_action( 'uat_activity_log_restriction_content', $active_tab );
					endif;
				endif;
				?>
		<?php
		$content = array(
			'tab'  => $active_tab,
			'data' => $data,
		);
		if ( current_user_can( $settings_perm ) ) :
			do_action( 'moove_activity_tab_content', $content, $active_tab );
		else :
			do_action( 'uat_log_settings_restriction_content', $active_tab );
		endif;
		?>
	</div><!-- .moove-activity-log-report -->
</div>
<!-- moove-form-container -->

</div>
<!--  .uat-tab-section-cnt -->
<?php

echo wp_kses( $view_cnt->load( 'moove.admin.settings.plugin-boxes', array() ), wp_kses_allowed_html( 'post' ) );
?>

</div>
<!-- wrap -->

<div class="uat-admin-popup uat-admin-popup-clear-log-confirm" style="display: none;">
	<span class="uat-popup-overlay"></span>
	<div class="uat-popup-content">
		<div class="uat-popup-content-header">
			<a href="#" class="uat-popup-close"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
		<!--  .uat-popup-content-header -->
		<div class="uat-popup-content-content">
			<?php if ( 'all_logs' === $active_tab ) : ?>
				<h4><strong>Please confirm that you would like to <br> <span class="uat-h">delete all logs</span></strong></h4>
				<br>
				<button class="button button-primary button-clear-log-confirm-confirm clear-all-logs">
					<?php esc_html_e( 'Delete All Logs', 'import-uat-feed' ); ?>
				</button>
			<?php elseif ( 'activity-groups' === $active_tab ) : ?>
				<h4><strong>Please confirm that you would like to <span class="uat-h">delete logs</span> and <span class="uat-h"> login / logout activities</span> associated with this user</strong></h4>
				<br>
				<button class="button button-primary button-clear-log-confirm-confirm clear-user-logs">
					<?php esc_html_e( 'Delete Logs', 'import-uat-feed' ); ?>
				</button>
			<?php else : ?>
				<h4><strong>Please confirm that you would like to <span class="uat-h">delete logs</span> associated with this post</strong></h4>
				<br>
				<button class="button button-primary button-clear-log-confirm-confirm clear-all-logs">
					<?php esc_html_e( 'Delete Logs', 'import-uat-feed' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<!--  .uat-popup-content-content -->    
	</div>
	<!--  .uat-popup-content -->
</div>
<!--  .uat-admin-popup -->

<div class="uat-admin-popup uat-admin-popup-clear-filtered-log-confirm" style="display: none;">
	<span class="uat-popup-overlay"></span>
	<div class="uat-popup-content">
		<div class="uat-popup-content-header">
			<a href="#" class="uat-popup-close"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
		<!--  .uat-popup-content-header -->
		<div class="uat-popup-content-content">
			<h4><strong>Please confirm that you would like to <br> <span class="uat-h">delete filtered logs</span></strong></h4>
			<br>
			<button class="button button-primary button-clear-log-confirm-confirm clear-filtered-logs">
				<?php esc_html_e( 'Delete Filtered Logs', 'import-uat-feed' ); ?>
			</button>
		</div>
		<!--  .uat-popup-content-content -->    
	</div>
	<!--  .uat-popup-content -->
</div>
<!--  .uat-admin-popup -->

<script type="text/javascript" src="<?php echo esc_url( moove_activity_get_plugin_dir() ); ?>/assets/js/moove_activity_backend_select2.js"></script>
