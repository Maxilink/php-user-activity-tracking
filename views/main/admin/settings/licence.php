<?php
/**
 * Licence Manager Doc Comment
 *
 * @category  Views
 * @package   user-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

?>

<h2><?php esc_html_e( 'Licence Manager', 'import-uat-feed' ); ?></h2>
<hr />
<?php
$uat_default_content = new Moove_Activity_Content();
$option_key          = $uat_default_content->moove_uat_get_key_name();
$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );
?>
<form action="<?php echo esc_url( admin_url( '/admin.php?page=moove-activity-log&tab=licence&sm=licence' ) ); ?>" method="post" id="moove_uat_license_settings">
	<table class="form-table">
		<tbody>
			<tr>
				<td colspan="2" class="uat_license_log_alert" style="padding: 0;">
					<?php
					$is_valid_license = false;
					$nonce = isset( $_POST['uat_lt_nonce_k'] ) ? sanitize_key( wp_unslash( $_POST['uat_lt_nonce_k'] ) ) : false;
						;
					if ( isset( $_POST['moove_uat_license_key'] ) && isset( $_POST['uat_activate_license'] ) && wp_verify_nonce( $nonce, 'uat_tab_licence_v' ) ) :
						$license_key = sanitize_text_field( wp_unslash( $_POST['moove_uat_license_key'] ) );
						if ( $license_key ) :
							$license_manager  = new Moove_UAT_License_Manager();
							$is_valid_license = $license_manager->get_premium_add_on( $license_key, 'activate' );

							if ( $is_valid_license && isset( $is_valid_license['valid'] ) && true === $is_valid_license['valid'] ) :
								update_option(
									$option_key,
									array(
										'key'        => $is_valid_license['key'],
										'activation' => $is_valid_license['data']['today']
									)
								);
								// VALID.
								$uat_key  = $uat_default_content->uat_get_activation_key( $option_key );
								$messages = isset( $is_valid_license['message'] ) && is_array( $is_valid_license['message'] ) ? implode( '<br>', $is_valid_license['message'] ) : '';
								do_action( 'uat_get_alertbox', 'success', $is_valid_license, $license_key );
								else :
									// INVALID.
									do_action( 'uat_get_alertbox', 'error', $is_valid_license, $license_key );
								endif;
							endif;
						elseif ( isset( $_POST['uat_deactivate_license'] ) && wp_verify_nonce( $nonce, 'uat_tab_licence_v' ) ) :
							$uat_default_content = new Moove_Activity_Content();
							$option_key          = $uat_default_content->moove_uat_get_key_name();
							$uat_key             = $uat_default_content->uat_get_activation_key( $option_key );
							if ( $uat_key && isset( $uat_key['key'] ) && isset( $uat_key['activation'] ) ) :
								$license_manager  = new Moove_UAT_License_Manager();
								$is_valid_license = $license_manager->premium_deactivate( $uat_key['key'] );
								update_option(
									$option_key,
									array(
										'key'          => $uat_key['key'],
										'deactivation' => strtotime( 'now' )
									)
								);
								$uat_key = $uat_default_content->uat_get_activation_key( $option_key );

								if ( $is_valid_license && isset( $is_valid_license['valid'] ) && true === $is_valid_license['valid'] ) :
									// VALID.
									do_action( 'uat_get_alertbox', 'success', $is_valid_license, $uat_key['key'] );
								else :
									// INVALID.
									do_action( 'uat_get_alertbox', 'error', $is_valid_license, $uat_key['key'] );
								endif;
							endif;
						elseif ( $uat_key && isset( $uat_key['key'] ) && isset( $uat_key['activation'] ) ) :
							$license_manager  = new Moove_UAT_License_Manager();
							$is_valid_license = $license_manager->get_premium_add_on( $uat_key['key'], 'check' );
							$uat_key          = $uat_default_content->uat_get_activation_key( $option_key );
							if ( $is_valid_license && isset( $is_valid_license['valid'] ) && true === $is_valid_license['valid'] ) :
								// VALID.
								do_action( 'uat_get_alertbox', 'success', $is_valid_license, $uat_key );
							else :
								// INVALID.
								do_action( 'uat_get_alertbox', 'error', $is_valid_license, $uat_key );
							endif;
						endif;
						?>
				</td>
			</tr>
			<?php do_action( 'uat_licence_input_field', $is_valid_license, $uat_key ); ?>
		</tbody>
	</table>
	<br />
	<?php do_action( 'uat_licence_action_button', $is_valid_license, $uat_key ); ?>
	<br />
	<?php do_action( 'uat_cc_general_buttons_settings' ); ?>
	<?php wp_nonce_field( 'uat_tab_licence_v', 'uat_lt_nonce_k' ); ?>
</form>

<div class="uat-admin-popup uat-admin-popup-deactivate" style="display: none;">
	<span class="uat-popup-overlay"></span>
	<div class="uat-popup-content">
		<div class="uat-popup-content-header">
			<a href="#" class="uat-popup-close"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
		<!--  .uat-popup-content-header -->
		<div class="uat-popup-content-content">
			<h4><strong>Please confirm that you would like to de-activate this licence. </strong></h4><p><strong>This action will remove all of the premium features from your website.</strong></p>
			<button class="button button-primary button-deactivate-confirm">
				<?php esc_html_e( 'Deactivate Licence', 'import-uat-feed' ); ?>
			</button>
		</div>
		<!--  .uat-popup-content-content -->    
	</div>
	<!--  .uat-popup-content -->
</div>
<!--  .uat-admin-popup -->
