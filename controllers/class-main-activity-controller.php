<?php
/**
 * Moove_Controller File Doc Comment
 *
 * @category  Moove_Controller
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Controller Class Doc Comment
 *
 * @category Class
 * @package  Moove_Controller
 * @author   Moove Agency
 */
class Moove_Activity_Controller {
	/**
	 * Construct function
	 */
	public function __construct() {
	}

	public static function delete_abandoned_logs() {
		$db_controller = new Moove_Activity_Database_Model();
		$db_controller->delete_abandoned_logs();
	}

	/**
	 * Table Settings
	 */
	public static function uat_manage_table_settings() {
		$dt_nonce      = isset( $_POST['dt_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['dt_nonce'] ) ) : '';
		$settings_perm = apply_filters( 'uat_log_settings_capability', 'manage_options' );
		if ( wp_verify_nonce( $dt_nonce, 'moove_uat_dt_log_nonce_field' ) && current_user_can( $settings_perm ) ) :
			$type 		= isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			$settings = apply_filters( 'uat_get_table_settings', [] );
			if ( 'update_column' === $type ) :				
				$col = isset( $_POST['col'] ) ? intval( $_POST['col'] ) : false;
				$state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : 'hidden';
				if ( isset( $settings['cols'] ) ) :
					if ( $state === 'hidden' ) :
						unset( $settings['cols'][$col] );
					elseif ( $state === 'visible' ) :
						$settings['cols'][$col] = $col;
					endif;
					do_action( 'uat_save_table_settings', $settings );
					echo json_encode( $settings );
					die();
				endif;
				echo json_encode( ['success' => false] );
				die();
			elseif ( 'update_len' === $type ) :
				$settings = apply_filters( 'uat_get_table_settings', [] );
				$len 			= isset( $_POST['len'] ) ? intval( $_POST['len'] ) : false;
				if ( isset( $settings['len'] ) && $len ) :
					$settings['len'] = $len;
					do_action( 'uat_save_table_settings', $settings );
					echo json_encode( $settings );
					die();
				endif;
				echo json_encode( ['success' => false] );
				die();
			endif;
		endif;
		echo json_encode( ['success' => false] );
		die();
	}

	/**
	 * Delete DataTables logs
	 */
	public static function uat_activity_delete_dt_logs() {
		$dt_nonce      = isset( $_GET['dt_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['dt_nonce'] ) ) : '';
		$settings_perm = apply_filters( 'uat_log_settings_capability', 'manage_options' );
		if ( wp_verify_nonce( $dt_nonce, 'moove_uat_dt_log_nonce_field' ) && current_user_can( $settings_perm ) ) :
			$uat_db_controller = new Moove_Activity_Database_Model();
			if ( isset( $_GET['type'] ) && 'all' === sanitize_text_field( wp_unslash( $_GET['type'] ) ) ) :
				$uat_db_controller->delete_all_logs();
				echo wp_json_encode( array( 'success' => true ) );
				do_action( 'uat_delete_user_activity', 'all' );
				die();
			elseif (  isset( $_GET['type'] ) && 'all-archives' === sanitize_text_field( wp_unslash( $_GET['type'] ) ) ) :
				$uat_db_controller->delete_all_archive_logs();
				echo wp_json_encode( array( 'success' => true ) );
				do_action( 'uat_delete_user_activity', 'all-archives' );
				die();
			elseif ( isset( $_GET['type'] ) && 'filtered' === sanitize_text_field( wp_unslash( $_GET['type'] ) ) ) :
				$trigger_details = array();
				$columns         = array(
					array(
						'db'        => 'visit_date',
						'dt'        => 0,
						'formatter' => function( $d, $row ) {
							$screen_options = get_user_meta( get_current_user_id(), 'moove_activity_screen_options', true );
							$selected_val   = isset( $screen_options['moove-activity-dtf'] ) ? $screen_options['moove-activity-dtf'] : 'a';
							$date                   = esc_attr( moove_activity_convert_date( $selected_val, $d, $screen_options ) );
							return $date;
						}
					),
					array(
						'db'        => 'post_title',
						'dt'        => 1,
						'formatter' => function( $d, $row ) {
							$title       	= get_the_title( $row['post_id'] );
							return $title;
						}
					),
					array(
						'db'        => 'post_type',
						'dt'        => 2,
						'formatter' => function( $d, $row ) {
							$pt = get_post_type_object( $d );
							$post_type = $pt && isset( $pt->label ) ? esc_attr( $pt->label ) : 'N/A';
							return $post_type;
						}
					),
					array(
						'db'        => 'user_email',
						'dt'        => 3,
						'formatter' => function( $d, $row ) {
							$linkified_value = $d && 'N/A' !== $d ? apply_filters( 'uat_linkify_user_session', $d, $row ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
							return $linkified_value;
						}
					),

					array(
						'db'        => 'user_login',
						'dt'        => 4,
						'formatter' => function( $d, $row ) {
							$linkified_value = $d && 'N/A' !== $d ? apply_filters( 'uat_linkify_user_session', $d, $row ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
							return $linkified_value;
						}
					),
					array(
						'db'        => 'display_name',
						'dt'        => 5,
						'formatter' => function( $d, $row ) {
							$linkified_value = $d && 'N/A' !== $d ? apply_filters( 'uat_linkify_user_session', $d, $row ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
							return $linkified_value;
						}
					),
					array(
						'db'        => 'time_spent',
						'dt'        => 6,
						'formatter' => function( $d, $row ) {
							return esc_attr( apply_filters( 'uat_time_spent_format', $d ) );
						}
					),
					array(
						'db'        => 'user_id',
						'dt'        => 7,
						'formatter' => function( $d, $row ) {
							$user_role = '';
							if ( $d && intval( $d ) ) :
								$user_meta = wp_cache_get( 'uat_user_meta_' . $d, 'user-activity-tracking-and-log' );
								if ( ! $user_meta ) :
									$user_meta = get_userdata( intval( $d ) );
									endif;
								if ( $user_meta && isset( $user_meta->roles ) ) :
									$user_roles = $user_meta->roles;
									if ( isset( $user_roles[0] ) ) :
										$user_role = $user_roles[0];
										endif;
									endif;
								endif;
							return $user_role ? $user_role : 'N/A';
						}
					),
					array(
						'db'        => 'city',
						'dt'        => 8,
						'formatter' => function( $d, $row ) {
							return $d ? esc_attr( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						}
					),
					array(
						'db'        => 'user_ip',
						'dt'        => 9,
						'formatter' => function( $d, $row ) {
							$value = $d ? apply_filters( 'moove_activity_tracking_ip_filter', $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
							$linkified_value = apply_filters( 'uat_linkify_user_session', $value, $row );
							return $linkified_value;
						}
					),
					array(
						'db'        => 'referer',
						'dt'        => 10,
						'formatter' => function( $d, $row ) {
							return $d ? esc_attr( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						}
					),
					array(
						'db'        => 'permalink',
						'dt'        => 11,
						'formatter' => function( $d, $row ) {
							$permalink = get_permalink( $row['post_id'] );
							return $permalink ? esc_url( $permalink ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						}
					),
					array(
						'db'        => 'request_url',
						'dt'        => 12,
						'formatter' => function( $d, $row ) {
							return $d ? esc_url( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						}
					)
				);

				$dt_serverside_ssp = new Activity_DT_Manager();
				$__data            = $_GET;
				
				$response = $dt_serverside_ssp->delete( $__data, $columns );
				echo wp_json_encode( array( 'success' => true ) );
			elseif ( isset( $_GET['type'] ) && 'user' === sanitize_text_field( wp_unslash( $_GET['type'] ) ) ) :
				if ( isset( $_GET['uip'] ) && sanitize_text_field( wp_unslash( $_GET['uip'] ) ) ) :
					$uat_db_controller->delete_log( 'user_ip', sanitize_text_field( wp_unslash( $_GET['uip'] ) ) );
					echo wp_json_encode(
						array(
							'success' => true,
							'user'    => 'true'
						)
					);
					die();
				endif;

				if ( isset( $_GET['uid'] ) && intval( $_GET['uid'] ) ) :
					$uat_db_controller->delete_log( 'user_id', intval( $_GET['uid'] ) );
					do_action( 'uat_delete_user_activity', intval( $_GET['uid'] ) );
					echo wp_json_encode(
						array(
							'success' => true,
							'user'    => 'true'
						)
					);
					die();
				endif;
			elseif ( isset( $_GET['type'] ) && 'cpt' === sanitize_text_field( wp_unslash( $_GET['type'] ) ) ) :
				$log_id = isset( $_GET['id'] ) && intval( $_GET['id'] ) ? intval( $_GET['id'] ) : false;
				if ( $log_id ) :
					$uat_db_controller->delete_log( 'post_id', $log_id );
					echo wp_json_encode(
						array(
							'success' => true,
							'cpt'     => 'true'
						)
					);
					die();
				endif;
			endif;
		endif;
		echo wp_json_encode( array( 'success' => false ) );
		die();
	}

	/**
	 * Data Tables AJAX log
	 */
	public static function uat_activity_get_dt_logs() {
		$dt_nonce = isset( $_GET['dt_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['dt_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $dt_nonce, 'moove_uat_dt_log_nonce_field' ) ) :
			echo wp_json_encode(
				array(
					'draw'            => 1,
					'recordsTotal'    => 0,
					'recordsFiltered' => 0,
					'data'            => array()
				)
			);
		else :
			$trigger_details = array();
			$columns         = array(
				array(
					'db'        => 'visit_date',
					'dt'        => 0,
					'formatter' => function( $d, $row ) {
						$screen_options = get_user_meta( get_current_user_id(), 'moove_activity_screen_options', true );
						$selected_val   = isset( $screen_options['moove-activity-dtf'] ) ? $screen_options['moove-activity-dtf'] : 'a';
						$date                   = esc_attr( moove_activity_convert_date( $selected_val, $d, $screen_options ) );
						return str_replace( ' ', '<br />', $date );
					}
				),
				array(
					'db'        => 'post_title',
					'dt'        => 1,
					'formatter' => function( $d, $row ) {
						if ( $row['post_id'] > 0 ) :
							$permalink 		= get_permalink( $row['post_id'] );
							$title       	= get_the_title( $row['post_id'] );
						else: 
							$permalink 		= isset( $row['request_url'] ) ? $row['request_url'] : '';
							$title       	= isset( $row['archive_title'] ) ? $row['archive_title'] : 'N/A';
						endif;
						return '<span class="uat-dd-title"><a href="' . $permalink . '" target="_blank">' . $title . '</a></span>';
					}
				),
				array(
					'db'        => 'post_type',
					'dt'        => 2,
					'formatter' => function( $d, $row ) {
						$pt = get_post_type_object( $d );
						$post_type = $pt && isset( $pt->label ) ? esc_attr( $pt->label ) : 'N/A';
						return $post_type;
					}
				),
				array(
					'db'        => 'user_email',
					'dt'        => 3,
					'formatter' => function( $d, $row ) {
						$linkified_value = $d && 'N/A' !== $d ? apply_filters( 'uat_linkify_user_session', $d, $row ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $linkified_value;
					}
				),
				array(
					'db'        => 'user_login',
					'dt'        => 4,
					'formatter' => function( $d, $row ) {
						$linkified_value = $d && 'N/A' !== $d ? apply_filters( 'uat_linkify_user_session', $d, $row ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $linkified_value;
					}
				),
				array(
					'db'        => 'display_name',
					'dt'        => 5,
					'formatter' => function( $d, $row ) {
						$linkified_value = $d && 'N/A' !== $d ? apply_filters( 'uat_linkify_user_session', $d, $row ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $linkified_value;
					}
				),
				array(
					'db'        => 'time_spent',
					'dt'        => 6,
					'formatter' => function( $d, $row ) {
						return esc_attr( apply_filters( 'uat_time_spent_format', $d ) );
					}
				),
				array(
					'db'        => 'user_id',
					'dt'        => 7,
					'formatter' => function( $d, $row ) {
						$user_role = '';
						if ( $d && intval( $d ) ) :
							$user_meta = wp_cache_get( 'uat_user_meta_' . $d, 'user-activity-tracking-and-log' );
							if ( ! $user_meta ) :
								$user_meta = get_userdata( intval( $d ) );
								endif;
							if ( $user_meta && isset( $user_meta->roles ) ) :
								$user_roles = $user_meta->roles;
								if ( isset( $user_roles[0] ) ) :
									$user_role = $user_roles[0];
									endif;
								endif;
							endif;
						return $user_role ? $user_role : 'N/A';
					}
				),
				array(
					'db'        => 'city',
					'dt'        => 8,
					'formatter' => function( $d, $row ) {
						return $d ? esc_attr( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				),
				array(
					'db'        => 'user_ip',
					'dt'        => 9,
					'formatter' => function( $d, $row ) {
						$value = $d ? apply_filters( 'moove_activity_tracking_ip_filter', $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						$linkified_value = apply_filters( 'uat_linkify_user_session', $value, $row );
						return $linkified_value;
					}
				),
				array(
					'db'        => 'referer',
					'dt'        => 10,
					'formatter' => function( $d, $row ) {
						return $d ? esc_attr( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				),
				array(
					'db'        => 'permalink',
					'dt'        => 11,
					'formatter' => function( $d, $row ) {
						$permalink = get_permalink( $row['post_id'] );
						return $permalink ? esc_url( $permalink ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				),
				array(
					'db'        => 'request_url',
					'dt'        => 12,
					'formatter' => function( $d, $row ) {
						return $d ? esc_url( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				)
			);

			$dt_serverside_ssp = new Activity_DT_Manager();
			echo wp_json_encode(
				$dt_serverside_ssp->simple( $_GET, $columns )
			);
		endif;
		die();
	}

	/**
	 * Data Tables AJAX log
	 */
	public static function uat_activity_export_dt_logs() {
		$dt_nonce = isset( $_GET['dt_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['dt_nonce'] ) ) : '';
		$type     = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'all';

		if ( ! wp_verify_nonce( $dt_nonce, 'moove_uat_dt_log_nonce_field' ) ) :
			echo wp_json_encode(
				array(
					'draw'            => 1,
					'recordsTotal'    => 0,
					'recordsFiltered' => 0,
					'data'            => array()
				)
			);
		else :
			$trigger_details = array();
			$columns         = array(
				array(
					'db'        => 'visit_date',
					'dt'        => 0,
					'formatter' => function( $d, $row ) {
						$screen_options = get_user_meta( get_current_user_id(), 'moove_activity_screen_options', true );
						$selected_val   = isset( $screen_options['moove-activity-dtf'] ) ? $screen_options['moove-activity-dtf'] : 'a';
						$date                   = esc_attr( moove_activity_convert_date( $selected_val, $d, $screen_options ) );
						return $date;
					}
				),
				array(
					'db'        => 'post_title',
					'dt'        => 1,
					'formatter' => function( $d, $row ) {
						$permalink = get_permalink( $row['post_id'] );
						$title       = get_the_title( $row['post_id'] );
						return $title;
					}
				),
				array(
					'db'        => 'post_type',
					'dt'        => 2,
					'formatter' => function( $d, $row ) {
						$pt = get_post_type_object( $d );
						$post_type = $pt && isset( $pt->label ) ? esc_attr( $pt->label ) : 'N/A';
						return $post_type;
					}
				),
				array(
					'db'        => 'user_email',
					'dt'        => 3,
					'formatter' => function( $d, $row ) {
						$linkified_value = $d && 'N/A' !== $d ? $d : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $linkified_value;
					}
				),

				array(
					'db'        => 'user_login',
					'dt'        => 4,
					'formatter' => function( $d, $row ) {
						$linkified_value = $d && 'N/A' !== $d ? $d : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $linkified_value;
					},
				),
				array(
					'db'        => 'display_name',
					'dt'        => 5,
					'formatter' => function( $d, $row ) {
						$linkified_value = $d && 'N/A' !== $d ? $d : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $linkified_value;
					}
				),
				array(
					'db'        => 'time_spent',
					'dt'        => 6,
					'formatter' => function( $d, $row ) {
						return esc_attr( apply_filters( 'uat_time_spent_format', $d ) );
					}
				),
				array(
					'db'        => 'user_id',
					'dt'        => 7,
					'formatter' => function( $d, $row ) {
						$user_role = '';
						if ( $d && intval( $d ) ) :
							$user_meta = wp_cache_get( 'uat_user_meta_' . $d, 'user-activity-tracking-and-log' );
							if ( ! $user_meta ) :
								$user_meta = get_userdata( intval( $d ) );
								endif;
							if ( $user_meta && isset( $user_meta->roles ) ) :
								$user_roles = $user_meta->roles;
								if ( isset( $user_roles[0] ) ) :
									$user_role = $user_roles[0];
									endif;
								endif;
							endif;
						return $user_role ? $user_role : 'N/A';
					}
				),
				array(
					'db'        => 'city',
					'dt'        => 8,
					'formatter' => function( $d, $row ) {
						return $d ? esc_attr( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				),
				array(
					'db'        => 'user_ip',
					'dt'        => 9,
					'formatter' => function( $d, $row ) {
						$value = $d ? apply_filters( 'moove_activity_tracking_ip_filter', $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
						return $value;
					}
				),
				array(
					'db'        => 'referer',
					'dt'        => 10,
					'formatter' => function( $d, $row ) {
						return $d ? esc_attr( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				),
				array(
					'db'        => 'permalink',
					'dt'        => 11,
					'formatter' => function( $d, $row ) {
						$permalink = get_permalink( $row['post_id'] );
						return $permalink ? esc_url( $permalink ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					},
				),
				array(
					'db'        => 'request_url',
					'dt'        => 12,
					'formatter' => function( $d, $row ) {
						return $d ? esc_url( $d ) : esc_html__( 'N/A', 'user-activity-tracking-and-log' );
					}
				),
			);

			$headers_f 	= apply_filters('uat_csv_dt_header', array() );
			if ( $headers_f && ! empty( $headers_f ) ) :
				$col_id = 12;
				foreach ( $headers_f as $header_key => $header_f ) :
					$header_key = sanitize_title( $header_key );
					$col_id++;
					$filter_key = 'uat_csv_row_' . $header_key;
					$columns[] = array(
						'db'        => 'user_id',
						'dt'        => $col_id,
						'hook'			=> $header_key
					);
				endforeach;
			endif;

			$dt_serverside_ssp = new Activity_DT_Manager();
			$__data            = $_GET;
			echo wp_json_encode(
				$dt_serverside_ssp->export( $__data, $columns, $type )
			);
		endif;
		die();
	}


	/**
	 * Checking if database exists
	 *
	 * @return bool
	 */
	public static function moove_importer_check_database() {
		$has_database = get_option( 'moove_importer_has_database' ) ? true : false;
		return $has_database;
	}

	/**
	 * Plugin details from WordPress.org repository
	 *
	 * @param string $plugin_slug Plugin slug.
	 */
	public static function get_plugin_details( $plugin_slug = '' ) {
		$plugin_return   = false;
		$wp_repo_plugins = '';
		$wp_response     = '';
		$wp_version      = get_bloginfo( 'version' );
		$transient       = get_transient( 'plugin_info_' . $plugin_slug );

		if ( $transient ) :
			$plugin_return = $transient;
		else :
			if ( $plugin_slug && $wp_version > 3.8 ) :
				$url  = 'http://api.wordpress.org/plugins/info/1.2/';
				$args = array(
					'author' => 'MooveAgency',
					'fields' => array(
						'downloaded'      => true,
						'active_installs' => true,
						'ratings'         => true
					)
				);

				$url = add_query_arg(
					array(
						'action'  => 'query_plugins',
						'request' => $args
					),
					$url
				);

				$http_url = $url;
				$ssl      = wp_http_supports( array( 'ssl' ) );
				if ( $ssl ) :
					$url = set_url_scheme( $url, 'https' );
			endif;

				$http_args = array(
					'timeout'    => 30,
					'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
				);
				$request   = wp_remote_get( $url, $http_args );

				if ( ! is_wp_error( $request ) ) :
					$response = json_decode( wp_remote_retrieve_body( $request ), true );
					if ( is_array( $response ) ) :
						$wp_repo_plugins = isset( $response['plugins'] ) && is_array( $response['plugins'] ) ? $response['plugins'] : array();
						foreach ( $wp_repo_plugins as $plugin_details ) :
							$plugin_details = (object) $plugin_details;
							if ( isset( $plugin_details->slug ) && $plugin_slug === $plugin_details->slug ) :
								$plugin_return = $plugin_details;
								set_transient( 'plugin_info_' . $plugin_slug, $plugin_return, 12 * HOUR_IN_SECONDS );
							endif;
						endforeach;
					endif;
				endif;
			endif;
		endif;
		return $plugin_return;
	}

	/**
	 * Importing logs stored in post_meta to database
	 *
	 * @return int $log_id Log_id.
	 */
	public static function import_log_to_database() {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );
		$uat_db_controller = new Moove_Activity_Database_Model();
		$log_id            = false;
		$query             = array(
			'post_type'      => $post_types,
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
		$log_query         = new WP_Query( $query );

		if ( $log_query->have_posts() ) :
			while ( $log_query->have_posts() ) :
				$log_query->the_post();
				$_post_meta      = get_post_meta( get_the_ID(), 'ma_data' );
				$_ma_data_option = $_post_meta[0];
				$ma_data         = maybe_unserialize( $_ma_data_option ); // phpcs:ignore

				if ( $ma_data['log'] && is_array( $ma_data['log'] ) ) :
					foreach ( $ma_data['log'] as $log ) :
						$date               = gmdate( 'Y-m-d H:i:s', $log['time'] );
						$data_to_insert    = array(
							'post_id'      => get_the_ID(),
							'user_id'      => $log['uid'],
							'status'       => $log['response_status'],
							'user_ip'      => $log['show_ip'],
							'city'         => $log['city'],
							'display_name' => $log['display_name'],
							'post_type'    => get_post_type( get_the_ID() ),
							'referer'      => $log['referer'],
							'month_year'   => gmdate( 'm', $log['time'] ) . gmdate( 'Y', $log['time'] ),
							'visit_date'   => $date,
							'campaign_id'  => isset( $ma_data['campaign_id'] ) ? $ma_data['campaign_id'] : ''
						);
						$save_to_db_enabled = apply_filters( 'moove_uat_filter_data', $data_to_insert );
						if ( $save_to_db_enabled ) :
							$log_id = $uat_db_controller->insert( $save_to_db_enabled );
						endif;
					endforeach;
				endif;
			endwhile;
		endif;
		wp_reset_postdata();
		update_option( 'moove_importer_has_database', true );
		return $log_id;
	}
	/**
	 * Create admin menu page
	 *
	 * @return void
	 */
	public static function moove_register_activity_menu_page() {
		$activity_perm = apply_filters( 'uat_activity_log_capability', 'manage_options' );

		add_menu_page(
			'Activity Tracking and Log', // Page_title.
			'User Activity Tracking', // Menu_title.
			$activity_perm, // Capability.
			'moove-activity-log', // Menu_slug.
			array( 'Moove_Activity_Controller', 'moove_activity_menu_page' ), // Function.
			'dashicons-visibility', // Icon_url.
			3 // Position.
		);

		do_action( 'uat_activity_submenu_extension', $activity_perm );
	}

	/**
	 * Pagination function for arrays.
	 *
	 * @param  array $display_array      Array to paginate.
	 * @param  int   $page                Start number.
	 * @param  int   $ppp                 Offset.
	 * @return array                    Paginated array
	 */
	public static function moove_pagination( $display_array, $page, $ppp ) {
		$page      = $page < 1 ? 1 : $page;
		$start     = ( ( $page - 1 ) * ( $ppp ) );
		$offset    = $ppp;
		$out_array = $display_array;
		if ( is_array( $display_array ) ) :
			$out_array = array_slice( $display_array, $start, $offset );
		endif;
		return $out_array;
	}

	/**
	 * Activity log page view
	 *
	 * @return  void
	 */
	public static function moove_activity_menu_page() {
		$uat_view = new Moove_Activity_View();
		echo $uat_view->load( // phpcs:ignore
			'moove.admin.settings.activity-log',
			null
		);
	}

	/**
	 * Tracking the user access when the post will be saved. (status = updated)
	 *
	 * @param int $post_id Post ID.
	 */
	public static function moove_track_user_access_save_post( $post_id ) {
		$log_id = false;
		if ( get_post_type( $post_id ) !== 'nav_menu_item' ) :
			$uat_controller = new Moove_Activity_Controller();
			$uat_shrotcodes = new Moove_Activity_Shortcodes();
			$uat_controller->moove_remove_old_logs( $post_id );
			$post_types = get_post_types( array( 'public' => true ) );
			unset( $post_types['attachment'] );
			// Trigger only on specified post types.
			if ( ! in_array( get_post_type(), $post_types, true ) ) :
				return;
			endif;
			$ma_data    = array();
			$_post_meta = get_post_meta( $post_id, 'ma_data' );
			if ( isset( $_post_meta[0] ) ) :
				$_ma_data_option = $_post_meta[0];
				$ma_data         = maybe_unserialize( $_ma_data_option ); // phpcs:ignore
			endif;
			$activity_status = 'updated';
			$ip              = $uat_shrotcodes->moove_get_the_user_ip();
			$ip_uf           = $uat_shrotcodes->moove_get_the_user_ip( false );
			$loc_enabled     = apply_filters( 'uat_show_location_by_ip', true );
			$details         = $loc_enabled ? $uat_shrotcodes->get_location_details( $ip_uf ) : false;
			$city            = $loc_enabled && isset( $details->city ) ? $details->city : '';
			$data            = array(
				'pid'    => intval( $post_id ),
				'uid'    => intval( get_current_user_id() ),
				'status' => esc_attr( $activity_status ),
				'uip'    => esc_attr( $ip ),
				'city'   => $city,
				'ref'    => esc_url( wp_get_referer() )
			);

			if ( isset( $ma_data['campaign_id'] ) ) :
				$log_id = $uat_controller->moove_create_log_entry( $data );
			else :
				$is_disabled = intval( get_post_meta( $post_id, 'ma_disabled', true ) );
				if ( ! $is_disabled ) :
					$post_type = get_post_type( $post_id );
					$settings  = get_option( 'moove_post_act' );

					if ( isset( $settings[ $post_type ] ) && intval( $settings[ $post_type ] ) !== 0 ) :
						$ma_data                = array();
						$campaign_id            = time() . $post_id;
						$ma_data['campaign_id'] = $campaign_id;
						update_post_meta( $post_id, 'ma_data', maybe_serialize( $ma_data ) ); // phpcs:ignore
						$log_id = $uat_controller->moove_create_log_entry( $data );
					endif;
				endif;
			endif;
		endif;
	}

	/**
	 * Tracking the user access on the front end. (status = visited)
	 */
	public static function moove_track_user_access_ajax() {
		$uat_controller = new Moove_Activity_Controller();
		$uat_shrotcodes = new Moove_Activity_Shortcodes();
		$post_id        = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : false; // phpcs:ignore
		$is_page        = isset( $_POST['is_page'] ) ? sanitize_text_field( wp_unslash( $_POST['is_page'] ) ) : false; // phpcs:ignore
		$is_single      = isset( $_POST['is_single'] ) ? sanitize_text_field( wp_unslash( $_POST['is_single'] ) ) : false; // phpcs:ignore
		$user_id        = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : false; // phpcs:ignore
		$referrer       = isset( $_POST['referrer'] ) ? sanitize_text_field( wp_unslash( $_POST['referrer'] ) ) : ''; // phpcs:ignore

		$request_url    = isset( $_POST['request_url'] ) ? sanitize_url( wp_unslash( $_POST['request_url'] ) ) : ''; // phpcs:ignore
		$is_archive     = isset( $_POST['is_archive'] ) ? sanitize_text_field( wp_unslash( $_POST['is_archive'] ) ) : ''; // phpcs:ignore
		$is_front_page	= isset( $_POST['is_front_page'] ) ? sanitize_text_field( wp_unslash( $_POST['is_front_page'] ) ) : ''; // phpcs:ignore
		$is_home				= isset( $_POST['is_home'] ) ? sanitize_text_field( wp_unslash( $_POST['is_home'] ) ) : ''; // phpcs:ignore
		$archive_title  = isset( $_POST['archive_title'] ) ? sanitize_text_field( wp_unslash( $_POST['archive_title'] ) ) : ''; // phpcs:ignore
		$log_id         = false;
		$settings  			= get_option( 'moove_post_act' );

		if ( ! $settings || empty( $settings ) ) :
			if ( function_exists( 'moove_set_options_values' ) ) :
				moove_set_options_values();
				$settings  			= get_option( 'moove_post_act' );
			endif;
		endif;

		if ( $is_front_page ) :
			$_post_id = get_option( 'page_on_front' );
			$post_id 	= $_post_id ? $_post_id : $post_id;
		endif;

		if ( $post_id && ! $is_archive && ! $is_home ) :
			$uat_controller->moove_remove_old_logs( $post_id );
			// Run on singles or pages.
			if ( $is_page || $is_single ) :
				$post_types = get_post_types( array( 'public' => true ) );
				unset( $post_types['attachment'] );
				// Trigger only on specified post types.
				$tracking_status_allowed = apply_filters('uat_tracking_post_status', ['publish']);
				if ( ! in_array( get_post_type( $post_id ), $post_types, true ) || ! in_array( get_post_status( $post_id ) , $tracking_status_allowed ) ) :
					return;
				endif;
				$_post_meta      = get_post_meta( $post_id, 'ma_data' );
				$_ma_data_option = isset( $_post_meta[0] ) ? $_post_meta[0] : maybe_serialize( array() ); // phpcs:ignore
				$ma_data         = maybe_unserialize( $_ma_data_option ); // phpcs:ignore
				$activity_status = 'visited';
				$ip              = $uat_shrotcodes->moove_get_the_user_ip();
				$ip_uf           = $uat_shrotcodes->moove_get_the_user_ip( false );
				$loc_enabled     = apply_filters( 'uat_show_location_by_ip', true );
				$details         = $loc_enabled ? $uat_shrotcodes->get_location_details( $ip_uf ) : false;
				$city            = $loc_enabled && isset( $details->city ) ? $details->city : '';

				$data = array(
					'pid'    				=> $post_id,
					'uid'    				=> $user_id,
					'status' 				=> $activity_status,
					'uip'    				=> esc_attr( $ip ),
					'city'   				=> $city,
					'ref'    				=> $referrer,
					'request_url'		=> $request_url,
					'is_archive'		=> $is_archive,
					'is_front_page'	=> $is_front_page,
					'archive_title'	=> $archive_title
				);

				if ( isset( $ma_data['campaign_id'] ) ) :
					$log_id = $uat_controller->moove_create_log_entry( $data );
				else :
					$is_disabled = intval( get_post_meta( $post_id, 'ma_disabled', true ) );
					if ( ! $is_disabled ) :
						$post_type = get_post_type( $post_id );
						if ( isset( $settings[ $post_type ] ) && intval( $settings[ $post_type ] ) !== 0 ) :
							$ma_data                = array();
							$campaign_id            = time() . $post_id;
							$ma_data['campaign_id'] = $campaign_id;
							update_post_meta( $post_id, 'ma_data', maybe_serialize( $ma_data ) ); // phpcs:ignore
							$log_id = $uat_controller->moove_create_log_entry( $data );
						endif;
					endif;
				endif;
			endif;
			wp_reset_postdata();
		else :
			do_action('uat_before_tracking_data_save');
		endif;
		echo wp_kses( $log_id, array() );
		die();
	}

	/**
	 * Tracking the user unload event.
	 */
	public static function moove_activity_track_unload() {
		$uat_db_controller = new Moove_Activity_Database_Model();
		$log_id        	= isset( $_POST['log_id'] ) ? intval( $_POST['log_id'] ) : false; // phpcs:ignore
		if ( $log_id ) :
			echo intval( $uat_db_controller->update_log_unload( $log_id ) );
		endif;
	}

	/**
	 * Function to delete a custom post logsm or all logs (if the functions runs without params.)
	 *
	 * @param  int $post_types Post ID.
	 */
	public function moove_clear_logs( $post_types = false ) {
		$uat_db_controller = new Moove_Activity_Database_Model();
		$uat_content       = new Moove_Activity_Content();

		if ( ! $post_types ) :
			$post_types = get_post_types( array( 'public' => true ) );
			unset( $post_types['attachment'] );
		else :
			delete_post_meta( $post_types, 'ma_data' );
			$uat_db_controller->delete_log( 'post_id', $post_types );
			$uat_content->moove_save_post( $post_types, 'enable' );
			return;
		endif;

		$query = array(
			'post_type'      => $post_types,
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

				$_post_meta      = get_post_meta( get_the_ID(), 'ma_data' );
				$_ma_data_option = isset( $_post_meta[0] ) ? $_post_meta[0] : maybe_serialize( array() ); // phpcs:ignore
				$ma_data         = maybe_unserialize( $_ma_data_option ); // phpcs:ignore
				$uat_db_controller->delete_log( 'post_id', get_the_ID() );
				if ( isset( $ma_data['campaign_id'] ) ) :
					delete_post_meta( get_the_ID(), 'ma_data' );
					$uat_content->moove_save_post( get_the_ID(), 'enable' );
				endif;
			endwhile;

		endif;
		wp_reset_postdata();
	}

	/**
	 * Remove the old logs. It calculates the difference between two dates,
	 * and if the difference between the log date and the current date is higher than
	 * the day(s) setted up on the settings page, than it remove that entry.
	 *
	 * @param  int $post_id Post ID.
	 */
	public static function moove_remove_old_logs( $post_id ) {
		$uat_db_controller = new Moove_Activity_Database_Model();
		if ( intval( $post_id ) > 0 ) :
			$_post_meta        = get_post_meta( $post_id, 'ma_data' );
			$ma_data_old       = array();
			if ( isset( $_post_meta[0] ) ) :
				$_ma_data_option = $_post_meta[0];
				$ma_data_old     = maybe_unserialize( $_ma_data_option ); // phpcs:ignore
			endif;
			if ( isset( $ma_data_old['campaign_id'] ) ) :
				$post_type         = get_post_type( $post_id );
				if ( isset( $activity_settings[ $post_type ] ) && intval( $activity_settings[ $post_type ] ) ) :
					$activity_settings = get_option( 'moove_post_act' );
					$days              = intval( $activity_settings[ $post_type . '_transient' ] );
					$today             = date_create( gmdate( 'm/d/Y', strtotime( 'timestamp' ) ) );
					$end_date          = gmdate( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
					$uat_db_controller->remove_old_logs( $post_id, $end_date );
				endif;
			endif;
		else :
			// Archives
			$activity_settings = get_option( 'moove_post_act' );
			if ( isset( $activity_settings['archives_transient'] ) ) :
				if ( isset( $activity_settings['archives'] ) && intval( $activity_settings['archives'] ) > 0 || ! isset( $activity_settings['archives'] ) ) :
					$days 						= intval( $activity_settings[ 'archives_transient' ] );
					$today            = date_create( gmdate( 'm/d/Y', strtotime( 'timestamp' ) ) );
					$end_date         = gmdate( 'Y-m-d H:i:s', strtotime( "-$days days" ) );
					$uat_db_controller->remove_old_logs( $post_id, $end_date );
				endif;
			endif;
		endif;
	}

	/**
	 * Create the log file for post.
	 *
	 * @param  array $data Aarray with log data.
	 */
	public function moove_create_log_entry( $data ) {
		$_post_meta        = get_post_meta( $data['pid'], 'ma_data' );
		$ma_data           = array();
		$uat_controller    = new Moove_Activity_Controller();
		$uat_db_controller = new Moove_Activity_Database_Model();
		$log_id            = 'false';
		if ( isset( $_post_meta[0] ) ) :
			$_ma_data_option = $_post_meta[0];
			$ma_data         = maybe_unserialize( $_ma_data_option ); // phpcs:ignore
		endif;
		$log = isset( $ma_data['log'] ) ? $ma_data['log'] : array();
		// We don't have anything set up.
		if ( '' === $log || ( is_array( $log ) && 0 === count( $log ) ) ) :
			$log = array();
		endif;
		$user = get_user_by( 'id', $data['uid'] );
		if ( $user ) :
			$username = $user->data->display_name;
		else :
			$username = __( 'N/A', 'user-activity-tracking-and-log' );
		endif;

		if ( $data['city'] ) :
			$city_name = $data['city'];
		else :
			$city_name = __( 'N/A', 'user-activity-tracking-and-log' );
		endif;

		$_post_type = get_post_type( $data['pid'] );
		$_post_type = $_post_type ? $_post_type : ( isset( $data['p_type'] ) && $data['p_type'] ? $data['p_type'] : 'n_a' );

		$date               = gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) );
		$data_to_insert    = array(
			'post_id'      	=> $data['pid'],
			'user_id'      	=> intval( $data['uid'] ),
			'status'       	=> esc_attr( $data['status'] ),
			'user_ip'      	=> esc_attr( $data['uip'] ),
			'display_name' 	=> $username,
			'city'         	=> $city_name,
			'post_type'    	=> $_post_type,
			'referer'      	=> isset( $data['ref'] ) ? $data['ref'] : '',
			'month_year'   	=> gmdate( 'm' ) . gmdate( 'Y' ),
			'visit_date'   	=> $date,
			'request_url'  	=> isset( $data['request_url'] ) ? $data['request_url'] : '',
			'archive_title' => $data['pid'] < 0 ? ( isset( $data['archive_title'] ) ? $data['archive_title'] : '' ) : '',
			'campaign_id'  	=> isset( $ma_data['campaign_id'] ) ? $ma_data['campaign_id'] : ''
		);
		$save_to_db_enabled = apply_filters( 'moove_uat_filter_data', $data_to_insert );
		if ( $save_to_db_enabled ) :
			$log_id = $uat_db_controller->insert( $save_to_db_enabled );
		endif;
		return $log_id;
	}
}
new Moove_Activity_Controller();
