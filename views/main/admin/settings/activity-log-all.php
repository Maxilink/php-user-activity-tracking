<?php
/**
 * Activity Log ALL Doc Comment
 *
 * @category  Views
 * @package   user-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$collapsible_table = apply_filters( 'uat_dt_responsive_table', true );
?>
<div class="uat-dt-section <?php echo $collapsible_table ? 'uat-dt-section-responsive' : 'uat-dt-section-nr'; ?> uat-dt-list uat-dt-log uat-dt-log-all">
	<?php

	wp_nonce_field( 'moove_uat_dt_log_nonce_field', 'moove_uat_dt_log_nonce' );
	$activity_perm       = apply_filters( 'uat_activity_log_capability', 'manage_options' );
	$plugin_settings     = apply_filters( 'moove_uat_filter_plugin_settings', get_option( 'moove_post_act' ) );
	$screen_options      = get_user_meta( get_current_user_id(), 'moove_activity_screen_options', true );
	$selected_val        = isset( $screen_options['moove-activity-dtf'] ) ? $screen_options['moove-activity-dtf'] : 'a';
	$view_cnt            = new Moove_Activity_View();
	$date_filter_content = '';

	if ( current_user_can( $activity_perm ) ) :
		?>

		<div class="tablenav top">
			<div class="alignleft actions uat-dt-top-filters">
				<?php ob_start(); ?>
				<label for="dt-date-filter" class="screen-reader-text"><?php esc_html_e( 'Filter by date', 'user-activity-tracking-and-log' ); ?></label>
				<select name="dt-date-filter" id="dt-date-filter">
					<option selected="selected" value="-1"><?php esc_html_e( 'All Dates', 'user-activity-tracking-and-log' ); ?></option>
				</select>
				<label class="screen-reader-text" for="dt-post_type-filter"><?php esc_html_e( 'Filter by post type', 'user-activity-tracking-and-log' ); ?></label>
				<select name="dt-post_type-filter" id="dt-post_type-filter" class="postform">
					<option value="-1"><?php esc_html_e( 'All Post Types', 'user-activity-tracking-and-log' ); ?></option>
					<?php
						$_post_types = get_post_types( array( 'public' => true ) );
						unset( $_post_types['attachment'] );
						foreach ( $_post_types as $_post_type ) :
							if ( isset( $plugin_settings[ $_post_type ] ) && intval( $plugin_settings[ $_post_type ] ) === 1 ) :
								$_post_type_object = get_post_type_object( $_post_type );
								if ( uat_is_post_type_enabled( $_post_type ) ) :
									?>
										<option class="level-0" value="<?php echo esc_attr( $_post_type ); ?>"><?php echo esc_attr( $_post_type_object->label ); ?></option>
									<?php
								endif;
							endif;
						endforeach;
					?>
				</select>
				<?php
					$filters = ob_get_clean();
					do_action( 'moove_activity_filters', $filters, $date_filter_content );
				?>
			</div>
			<!-- .alignleft actions -->
			<br class="clear">
		</div>
		<!-- .tablenav -->
		<?php $collapsible_table = apply_filters( 'uat_dt_responsive_table', true ); ?>
		<div class="uat-responsive-table <?php echo $collapsible_table ? '' : 'uat-table-nr'; ?>">
			<?php echo $view_cnt->load( 'moove.admin.data-table-template', array() ); // phpcs:ignore ?>
		</div>
		<!-- .uat-responsive-table -->
		<?php
	endif;
	?>
</div>
<!-- .uat-dt-list -->
