<?php
/**
 * Documentation Doc Comment
 *
 * @category  Views
 * @package   user-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
$demo_url = 'https://www.YourDomain.com';

?>
<h2><?php esc_html_e( 'Documentation', 'user-activity-tracking-and-log' ); ?></h2>
<hr />

<ul class="uat-disable-posts-nav moove-clearfix">
	<li></li>
	<li><a href="#uat_cbm_troubleshooting" class="uat-help-tab-toggle active"><?php esc_html_e( 'Troubleshooting', 'uat-cookie-compliance' ); ?></a></li>
	<li><a href="#uat_cbm_ete" class="uat-help-tab-toggle"><?php esc_html_e( 'Event Tracking - Examples', 'uat-cookie-compliance' ); ?></a></li>
	<li><a href="#uat_cbm_dh" class="uat-help-tab-toggle"><?php esc_html_e( 'Default Hooks', 'uat-cookie-compliance' ); ?></a></li>
</ul>

<br>

<div class="uat-help-content-cnt">
	<div id="uat_cbm_troubleshooting" class="uat-help-content-block help-block-open">
		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>I can’t see the logs because of an error message</h4>
				<div class="trigger-collapse-example">
					<p>You can try fixing this by adding the following line to your wp-config file:</p>
					<code>define( 'WP_MAX_MEMORY_LIMIT', '1028M' );</code>
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->
	</div>
	<!-- .at-help-content-block -->

	<div id="uat_cbm_ete" class="uat-help-content-block">
		<h4 style="margin-top: 0;">You can use the examples listed below as a guide for how to setup the event triggers.</h4>

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track PDF downloads</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url">Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target" selected>Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains" selected>Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="dummy.pdf">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_attr( htmlentities( '<a' ) . ' target="_blank" href="https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/' ); ?><span>dummy.pdf</span><?php echo esc_attr( htmlentities( '>Download a Dummy PDF</a>' ) ); ?></code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track clicks using email address</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url">Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target" selected>Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains" selected>Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="sales@example.com">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_attr( htmlentities( '<a' ) . ' href="mailto:' ); ?><span>sales@example.com</span><?php echo esc_attr( htmlentities( '>Contact Us</a>' ) ); ?></code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track clicks using URL</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url">Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target" selected>Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains">Contains</option>
													<option value="equals" selected>Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="<?php echo esc_url( $demo_url ); ?>/sample-page/">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_attr( htmlentities( '<a' ) ); ?> href="<span><?php echo esc_url( $demo_url ); ?>/sample-page/</span>">Find Out More<?php echo esc_attr( htmlentities( '</a>' ) ); ?></code></li>	
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track clicks using button labels</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url">Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text" selected>Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains">Contains</option>
													<option value="equals" selected>Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="Watch Video">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_attr( htmlentities( '<a href="https://vimeo.com/305493827" target="_blank">' ) ); ?><span>Watch Video</span><?php echo esc_attr( htmlentities( '</a>' ) ); ?></code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track clicks using element ID</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url">Page URL</option>
													<option value="click_element" selected>Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains">Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector" selected>CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="#cta-watch-video">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_attr( htmlentities( '<a href="https://vimeo.com/305493827"' ) . ' id=' ); ?>"<span>cta-watch-video</span>" <?php echo esc_attr( htmlentities( 'target="_blank">Watch Video</a>' ) ); ?></code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track clicks using CSS class</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url">Page URL</option>
													<option value="click_element" selected>Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains">Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector" selected>CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value=".submit">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_attr( htmlentities( '<button type="submit"' ) . ' class=' ); ?>"<span>submit</span>"<?php echo esc_attr( htmlentities( '>Submit form</button>' ) ); ?></code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track search keywords (on your site)</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url" selected>Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains" selected>Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="?s=">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><span><?php echo esc_url( $demo_url ); ?>/?s=test</span></code></li>					
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->


		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track a single page views</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url" selected>Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains">Contains</option>
													<option value="equals" selected>Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="<?php echo esc_url( $demo_url ); ?>/sample/">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><span><?php echo esc_url( $demo_url ); ?>/sample/</span></code></li>					
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track archive page views</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url" selected>Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains" selected>Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="/news/">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_url( $demo_url ); ?><span>/news/</span></code></li>
							<li><code><?php echo esc_url( $demo_url ); ?>/parent-page<span>/news/</span></code></li>
							<li><code><?php echo esc_url( $demo_url ); ?><span>/news/</span>category-1/</code></li>
							<li><code><?php echo esc_url( $demo_url ); ?><span>/news/</span>category-2/</code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Track search pages</h4>
				<div class="trigger-collapse-example">
					<table class="form-table trigger-action-table" style="margin: 0">
						<tbody>
							<tr>
								<td style="padding: 0;">
									<p><strong>Trigger Setup</strong></p>
								</td>
								<td style="padding: 0">
									<table>
										<tr>
											<td style="padding: 0">
												<label>Type</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="page_url" selected>Page URL</option>
													<option value="click_element">Click Element</option>
													<option value="click_target">Click Target</option>
													<option value="click_text">Click Text</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Operator</label>
											</td>
											<td style="padding: 0">
												<select disabled style="width: 100%; max-width: 100%;">
													<option value="contains" selected>Contains</option>
													<option value="equals">Equals</option>
													<option value="css-selector">CSS Selector</option>
												</select>
											</td>
										</tr>

										<tr>
											<td style="padding: 0">
												<label>Value</label>
											</td>
											<td style="padding: 0">
												<input type="text" class="regular-text" disabled value="?s=">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="trigger-sample-fired">
						<hr>
						<ul>
							<li><code><?php echo esc_url( $demo_url ); ?><span>/?s=test</span></code></li>
						</ul>
					</div>
					<!-- .trigger-sample-fired -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

	</div>
	<!-- .at-help-content-block -->

	<div id="uat_cbm_dh" class="uat-help-content-block">

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Disable normal activity log tracking</h4>
				<div class="trigger-collapse-example trigger-collapse-example-cm" style="display: block">
					<?php ob_start(); ?>
					add_action( 'moove_uat_filter_data', 'uat_disable_activity_tracking', 10, 1 );
						function uat_disable_activity_tracking( $data ) {
						if ( isset( $data['campaign_id'] ) ) :
						return false;
						endif;
						return $data;
						}
					<?php $code = trim( ob_get_clean() ); ?>
					<textarea id="<?php echo esc_attr( uniqid( strtotime( 'now' ) ) ); ?>"><?php echo $code; // phpcs:ignore ?></textarea>
					<div class="uat-code"></div><!--  .uat-code -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Enable tracking for disabled custom post types</h4>
				<div class="trigger-collapse-example trigger-collapse-example-cm" style="display: block">
					<?php ob_start(); ?>
					add_action( 'uat_show_disabled_cpt', '__return_true' );
					<?php $code = trim( ob_get_clean() ); ?>
					<textarea id="<?php echo esc_attr( uniqid( strtotime( 'now' ) ) ); ?>"><?php echo $code; // phpcs:ignore ?></textarea>
					<div class="uat-code"></div><!--  .uat-code -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->

		<div class="uat-et-trigger-box-actions-f uat-et-help-example-f">
			<div class="et-box-content">
				<h4>Extending CSV export file</h4>
				<div class="trigger-collapse-example trigger-collapse-example-cm" style="display: block">
					<?php ob_start(); ?>
					// Extending CSV header first.
					add_filter('uat_csv_dt_header','uat_csv_dt_header', 10, 1);
					function uat_csv_dt_header( $headers ) {
					    $headers['custom_col_1'] = 'Custom Column 1'; // user uat_csv_row_custom_col_1 hook to modify it's data
					    $headers['custom_col_2'] = 'Custom Column 2'; // user uat_csv_row_custom_col_2 hook to modify it's data
					    return $headers;
					}

					// Create new hooks for all header columns separately

					// Modify column data of "custom_col_1"
					add_filter('uat_csv_row_custom_col_1', 'uat_csv_row_custom_col_1', 99, 2);
						function uat_csv_row_custom_col_1( $return_value, $csv_row ) {
						    $user_id = isset( $csv_row['user_id'] ) && intval( $csv_row['user_id'] ) ? intval( $csv_row['user_id'] ) : false;
						    if ( $user_id ) :
						        $user = get_user_by( 'id', $user_id );
						        $return_value = $user->user_url;
						    else :
						        $return_value = 'N/A';
						    endif;
						    return $return_value;
						}

						// Modify column of "custom_col_2" - this is a var_dump for investigations and implementation
						add_filter('uat_csv_row_custom_col_2', 'uat_csv_row_custom_col_2', 99, 2);
						function uat_csv_row_custom_col_2( $return_value, $csv_row ) {
						    ob_start();
						    var_dump( $csv_row );
						    $return_value = ob_get_clean();
						    return $return_value;
						}
					<?php $code = trim( ob_get_clean() ); ?>
					<textarea id="<?php echo esc_attr( uniqid( strtotime( 'now' ) ) ); ?>"><?php echo $code; // phpcs:ignore ?></textarea>
					<div class="uat-code"></div><!--  .uat-code -->
				</div>
				<!-- .trigger-collapse-example -->
			</div>
			<!-- .et-box-content -->
		</div>
		<!-- .uat-et-help-example -->
	</div>
	<!-- .uat-help-content-block -->
</div>
<!-- .uat-help-content-cnt -->

<script type="text/javascript">
	window.onload = function() {
		if (typeof CodeMirror !== "undefined") {
			CodeMirror.defineExtension("autoFormatRange", function (from, to) {
			var cm = this;
			var outer = cm.getMode(), text = cm.getRange(from, to).split("\n");
			var state = CodeMirror.copyState(outer, cm.getTokenAt(from).state);
			var tabSize = cm.getOption("tabSize");

			var out = "", lines = 0, atSol = from.ch == 0;
			function newline() {
				out += "\n";
				atSol = true;
				++lines;
			}

			for (var i = 0; i < text.length; ++i) {
				var stream = new CodeMirror.StringStream(text[i], tabSize);
				while (!stream.eol()) {
					var inner = CodeMirror.innerMode(outer, state);
					var style = outer.token(stream, state), cur = stream.current();
					stream.start = stream.pos;
					if (!atSol || /\S/.test(cur)) {
						out += cur;
						atSol = false;
					}
					if (!atSol && inner.mode.newlineAfterToken &&
						inner.mode.newlineAfterToken(style, cur, stream.string.slice(stream.pos) || text[i+1] || "", inner.state))
						newline();
				}
				if (!stream.pos && outer.blankLine) outer.blankLine(state);
				if (!atSol) newline();
			}

			cm.operation(function () {
				cm.replaceRange(out, from, to);
				for (var cur = from.line + 1, end = from.line + lines; cur <= end; ++cur)
					cm.indentLine(cur, "smart");
			});
			});

			// Applies automatic mode-aware indentation to the specified range
			CodeMirror.defineExtension("autoIndentRange", function (from, to) {
				var cmInstance = this;
				this.operation(function () {
					for (var i = from.line; i <= to.line; i++) {
						cmInstance.indentLine(i, "smart");
					}
				});
			});
			function UAT_CodeMirror() {
				jQuery('.uat-et-help-example-f textarea').each(function(){
			var element = jQuery(this).closest('.trigger-collapse-example-cm').find('.uat-code')[0];
			var id = jQuery(this).attr('id');

			jQuery(this).css({
				'opacity'   : '0',
				'height'    : '0',
			});
			var  editor = CodeMirror( element, {
				mode: "javascript",
				lineWrapping: true,
				lineNumbers: false,
				readOnly: true,
				value: document.getElementById(id).value
			});
			var totalLines = editor.lineCount();  
					editor.autoFormatRange({line:0, ch:0}, {line:totalLines});
			});
			}
			jQuery(document).ready(function(){
				UAT_CodeMirror();

				jQuery('.uat-help-content-block:not(.help-block-open)').find('.trigger-collapse-example-cm').hide();
				jQuery('.uat-help-content-block:not(.help-block-open)').hide();
			});
		}
	};
</script>
<style>
	.CodeMirror {
		height: auto;
		border: none !important;
	}
</style>
