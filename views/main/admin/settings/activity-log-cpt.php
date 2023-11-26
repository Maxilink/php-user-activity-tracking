<?php
/**
 * ET Log Doc Comment
 *
 * @category  Views
 * @package   user-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$uat_db_controller 		= new Moove_Activity_Database_Model();
$view_cnt          		= new Moove_Activity_View();
$active_tab        		= isset( $data['post_type'] ) ? $data['post_type'] : '';
$activity_perm     		= apply_filters( 'uat_activity_log_capability', 'manage_options' );
$post_type_settings  	= uat_get_enabled_post_types();
if ( $active_tab && current_user_can( $activity_perm ) ) :
	wp_nonce_field( 'moove_uat_cpt_log_nonce_field', 'moove_uat_cpt_log_nonce' );
	wp_nonce_field( 'moove_uat_dt_log_nonce_field', 'moove_uat_dt_log_nonce' );

	if ( uat_is_post_type_enabled( $active_tab ) ) :
		if ( ! in_array( $active_tab, $post_type_settings ) ) :
			?>
				<h4><?php esc_html_e('Tracking Disabled. Please enable the tracking for this post type.', 'user-activity-tracking-and-log'); ?></h4>
				<a href="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=activity-settings&sm=settings' ) ); ?>" class="uat-orange-bnt"><?php esc_html_e( 'General Settings', 'user-activity-tracking-and-log' ) ?></a>
			<?php
		else :
			?>
			<div id="uat-table-section-html" style="display: none">
				<?php $collapsible_table = apply_filters( 'uat_dt_responsive_table', true ); ?>
				<div class="uat-responsive-table <?php echo $collapsible_table ? '' : 'uat-table-nr'; ?>">
					<?php echo $view_cnt->load( 'moove.admin.data-table-template', array() ); // phpcs:ignore ?>
				</div>
				<!-- .uat-responsive-table -->
			</div>
			<!-- #uat-table-section-html -->
			<div class="moove-accordion-cnt uat-ajax-filled-accordion uat-dt-log" data-adminajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
				<div class="moove-accordion">
					<div class="moove-accordion-section">
						<?php

						if ( is_array( $active_tab ) && count( $active_tab ) > 1 ) :
							echo '<h2>' . esc_attr( ucfirst( $ptlog ) ) . '</h2>';
							endif;

							$posts_from_cpt = $uat_db_controller->get_post_type_logs( $active_tab );
							$sorted_posts   = array();
						if ( $posts_from_cpt && is_array( $posts_from_cpt ) ) :
							foreach ( $posts_from_cpt as $_data ) :
								$_post_id = isset( $_data['post_id'] ) ? $_data['post_id'] : '';
								$_title   = get_the_title( $_post_id );
								if ( $_title ) :
									ob_start();
									?>
											<a class="moove-accordion-section-title" data-type="activity_log" data-id="<?php echo esc_attr( $_post_id ); ?>" href="#moove-accordion-<?php echo intval( $_post_id ); ?>">
											<?php echo esc_attr( $_title ); ?>
											</a>
											<div id="moove-accordion-<?php echo intval( $_post_id ); ?>" class="moove-accordion-section-content" data-permalink="<?php echo get_permalink( $_post_id ); ?>">
												<div class="dt-loader">
													<svg class="dt-uat-spinner" viewBox="0 0 50 50">
														<circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
													</svg> 
												</div>
												<!-- .dt-loader -->
											</div>
										<?php
										$sorted_posts[ sanitize_title( $_title ) . $_post_id ] = ob_get_clean();
									endif;
								endforeach;
							endif;
							ksort( $sorted_posts );
						if ( empty( $sorted_posts ) ) :
							ob_start();
							?>
									<span class="moove-accordion-section-title accordion-no-results"><?php esc_html_e( 'No logs were found.', 'user-activity-tracking-and-log' ); ?></span>
							<?php
							$sorted_posts[] = ob_get_clean();
						endif;
						?>
							<div class="moove-accordion-cnt uat-cpt-accordion">
								<div class="moove-accordion">
									<div class="moove-accordion-section">
										<?php echo implode( '', $sorted_posts ); // phpcs:ignore ?>
									</div>
									<!-- .moove-accordion-section -->
								</div>
								<!-- .moove-accordion -->
							</div>
							<!-- .moove-accordion-cnt -->
					</div>
					<!-- accordion-section-->
				</div>
				<!-- accordion-->
			</div>
			<!-- moove-accordion-cnt -->
			<?php
		endif;
	else :
		do_action( 'moove_activity_premium_section_ads' );
	endif;
endif;
