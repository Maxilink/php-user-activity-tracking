<?php
/**
 * Plugin Boxes Doc Comment
 *
 * @category  Views
 * @package   user-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$activity_controller = new Moove_Activity_Controller();
$plugin_details      = $activity_controller->get_plugin_details( 'user-activity-tracking-and-log' );
?>
<div class="moove-uat-plugins-info-boxes">

	<?php ob_start(); ?>
	<div class="m-plugin-box m-plugin-box-highlighted">
		<div class="box-header">
			<h4>Premium Add-On</h4>
		</div>
		<!--  .box-header -->
		<div class="box-content">
			<ul class="plugin-features">
				<li><strong>NEW Time-tracking:</strong> see the duration of user visits</li>
				<li><strong>NEW Event-tracking:</strong> setup goals and event triggers for button clicks, PDF downloads and more</li>			
				<li>Track all custom post-types and archives</li>
				<li>Auto logout</li>
				<li>Anonymize IP addresses (GDPR)</li>
				<li>Export logs to CSV</li>
				<li>Advanced filters</li>
				<li>Track logged-in users only</li>
				<li>Exclude users from tracking by user role</li>
				<li>Rest API endpoint for data export</li>
				<li>Set timezone</li>
				<li>...and more</li>
			</ul>
			<hr />
			<a href="https://www.mooveagency.com/wordpress-plugins/user-activity-tracking-and-log/" target="_blank" class="plugin-buy-now-btn">Buy Now</a>
		</div>
		<!--  .box-content -->
	</div>
	<!--  .m-plugin-box -->
	<?php
		$premium_box = apply_filters( 'uat_premium_section', ob_get_clean() );
		echo wp_kses( $premium_box, wp_kses_allowed_html( 'post' ) );
	?>

	<div class="m-plugin-box">
		<div class="box-header">
			<h4>GDPR / CCPA Cookie Compliance</h4>
		</div>
		<!--  .box-header -->
		<div class="box-content">
			<a href="https://www.mooveagency.com/wordpress-plugins/gdpr-cookie-compliance/" target="_blank">
				<img src='<?php echo trailingslashit( uat_get_plugin_directory_url() ); ?>assets/images/gdpr-promo-wp.png?rev=<?php echo MOOVE_UAT_VERSION; ?>'/>
			</a>
			<hr>
			<p>Prepare your website for cookie consent requirements with this incredibly powerful, easy-to-use, well supported and 100% free WordPress plugin.</p>

			<hr />
			<a href="https://www.mooveagency.com/wordpress-plugins/gdpr-cookie-compliance/" target="_blank" class="plugin-buy-now-btn">Free trial</a>
		</div>
		<!--  .box-content -->
	</div>
	<!--  .m-plugin-box -->

</div>
<!--  .moove-plugins-info-boxes -->
