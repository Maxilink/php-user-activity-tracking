<?php
/**
 * Moove_Activity_Database_Model File Doc Comment
 *
 * @category  Moove_Activity_Database_Model
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_Database_Model Class Doc Comment
 *
 * @category Class
 * @package  Moove_Activity_Database_Model
 * @author   Moove Agency
 */
class Moove_Activity_Database_Model {
	/**
	 * Primary key
	 *
	 * @var array
	 */
	public static $primary_key = 'id';

	/**
	 * Construct.
	 */
	public function __construct() {
		if ( ! get_option( 'moove_importer_has_database' ) ) {
			global $wpdb;
			$uat_db_init = wp_cache_get( 'uat_db_init', 'user-activity-tracking-and-log' );
			if ( ! $uat_db_init ) :
				// @codingStandardsIgnoreStart
				$uat_db_init = $wpdb->query(
					"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}moove_activity_log(
	        id integer not null auto_increment,
	        post_id integer not null,
	        user_id integer DEFAULT NULL,
	        status TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        user_ip TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        city TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        post_type TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        referer TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        campaign_id TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        month_year TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        display_name TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	        visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	        PRIMARY KEY (id)
	        );"
				); // db call ok; no-cache ok.
				// @codingStandardsIgnoreEnd
				update_option( 'moove_importer_has_database', true );
				wp_cache_set( 'uat_db_init', true, 'user-activity-tracking-and-log' );
			endif;
		}

		if ( ! get_option( 'moove_importer_has_extras' ) ) {
			global $wpdb;
			$uat_db_cols = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}moove_activity_log LIMIT 1" ); // db call ok; no-cache ok.
			if ( ! isset( $uat_db_cols->type ) ) :
				// @codingStandardsIgnoreStart
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD type INTEGER NOT NULL DEFAULT 0" ); // db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD permalink INTEGER NOT NULL DEFAULT 0" ); // db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD event INTEGER NOT NULL DEFAULT 0" ); // db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD time_spent INTEGER NOT NULL DEFAULT 0" ); // db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD extras TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL" ); // db call ok; no-cache ok.
				// @codingStandardsIgnoreEnd
				update_option( 'moove_importer_has_extras', true );
			endif;
		}

		if ( ! get_option( 'uat_db_support_request_url' ) ) {
			global $wpdb;
			$uat_db_cols = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}moove_activity_log LIMIT 1" ); // db call ok; no-cache ok.
			if ( ! isset( $uat_db_cols->request_url ) ) :
				// @codingStandardsIgnoreStart
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD request_url TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL" );// db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD query_vars TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL" );// db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD is_archive TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL" );// db call ok; no-cache ok.
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}moove_activity_log ADD archive_title TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL" );// db call ok; no-cache ok.
				// @codingStandardsIgnoreEnd
				update_option( 'uat_db_support_request_url', true );
			endif;
		}
	}

	public static function delete_abandoned_logs() {
		global $wpdb;
		try {
			$dal_next = get_option( 'uat_delete_abandoned_logs' );
			if ( $dal_next <= strtotime('now') ) :
				update_option( 'uat_delete_abandoned_logs', strtotime( '+1 day' ), false );
				$sql = "
					DELETE 
						uat_log
					FROM {$wpdb->prefix}moove_activity_log uat_log 
					WHERE NOT EXISTS( SELECT NULL FROM {$wpdb->prefix}posts posts_tbl where posts_tbl.id = uat_log.post_id) AND uat_log.post_id > 0
				";

				$data = $wpdb->get_results(
					$sql, // phpcs:ignore
					ARRAY_A
				); // db call ok; no-cache ok.	
			endif;
		} catch (Exception $e) {
			print_r( $e );
		}
	}

	/**
	 * User activity table name
	 */
	public static function uat_table() {
		global $wpdb;
		$tablename = 'moove_activity_log';
		return $wpdb->prefix . $tablename;
	}

	/**
	 * Get all logs query.
	 *
	 * @param string $post_types Post types.
	 */
	public static function get_all_logs( $post_types ) {
		global $wpdb;
		$post_types = is_array( $post_types ) ? '' . implode( "','", $post_types ) . '' : '';
		$cache_key  = md5( $post_types );
		$response   = wp_cache_get( 'uat_get_all_logs_' . $cache_key, 'user-activity-tracking-and-log' );
		if ( ! $response ) :

			$query = "SELECT `post_id`, `visit_date` as `time`, `user_id` as `uid`, `display_name`, `user_ip` as `ip_address`, `status` as `response_status`, `referer`, `city`, `permalink` as custom_link, `event`, `type`, `time_spent`, `extras`, posts_tbl.post_type, `campaign_id` FROM {$wpdb->prefix}moove_activity_log uat_log JOIN {$wpdb->prefix}posts posts_tbl	ON uat_log.post_id = posts_tbl.id WHERE  posts_tbl.post_type IN ('{$post_types}') ORDER BY `visit_date` DESC LIMIT 50000";

			$response = $wpdb->get_results(
				$query, // phpcs:ignore
				ARRAY_A
			); // db call ok; no-cache ok.
			wp_cache_set( 'uat_get_all_logs_' . $cache_key, $response, 'user-activity-tracking-and-log' );
		endif;
		return $response;
	}

	/**
	 * Returns the ID's from the selected post type
	 *
	 * @param string $post_type Post type.
	 */
	public static function get_post_type_logs( $post_type = false ) {
		global $wpdb;
		$post_type = is_array( $post_type ) ? '' . implode( "','", $post_type ) . '' : $post_type;
		$cache_key = md5( $post_type );
		$response  = wp_cache_get( 'uat_get_all_logs_' . $cache_key, 'user-activity-tracking-and-log' );
		if ( ! $response ) :
			$query = "SELECT DISTINCT `post_id` FROM {$wpdb->prefix}moove_activity_log uat_log WHERE  `post_type` = '{$post_type}'";

			$response = $wpdb->get_results(
				$query, // phpcs:ignore
				ARRAY_A
			); // db call ok; no-cache ok.
			wp_cache_set( 'uat_get_all_logs_' . $cache_key, $response, 'user-activity-tracking-and-log' );
		endif;
		return $response;
	}

	/**
	 * Get search results query.
	 *
	 * @param string $where Custom where.
	 */
	public static function get_search_results( $where = '' ) {
		global $wpdb;
		$_where = '';
		if ( is_array( $where ) && ! empty( $where ) ) :
			$relation = '';
			if ( isset( $where['relation'] ) ) :
				$relation = $where['relation'];
			endif;
			$count = 0;
			foreach ( $where as $key => $value ) :
				$count++;
				if ( 'relation' !== $key ) :
					$_where .= 1 === $count ? '' : ' ' . $relation . ' ';
					$_key    = $value['key'];
					$_val    = $value['value'];
					if ( isset( $value['operator'] ) && 'IN' === $value['operator'] ) :
						$_where .= " `$_key` " . $value['operator'] . " ( $_val ) ";
					else :
						$_where .= " `$_key` = '$_val' ";
					endif;
				endif;
			endforeach;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moove_activity_log WHERE $_where AND %s = %s", '1', '1' ) ); // phpcs:ignore
		else :
			return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}moove_activity_log" ); // db call ok; no-cache ok.
		endif;
	}

	/**
	 * Get results query.
	 *
	 * @param string $key Key.
	 * @param string $value Value.
	 * @param int    $limit Limit.
	 */
	public static function get_log( $key, $value, $limit = false ) {
		global $wpdb;
		$where       = '';
		$cache_key   = 'uat_' . $key . $value . $limit;
		$cache_value = wp_cache_get( $cache_key, 'user-activity-tracking-and-log' );
		if ( ! $cache_value ) :
			if ( $key && $value ) :
				if ( $limit && intval( $limit ) ) :
					$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moove_activity_log WHERE `$key` = %s ORDER BY `visit_date` DESC LIMIT %d, %d", $value, 1, $limit ) ); // phpcs:ignore
					wp_cache_set( $cache_key, $result, 'user-activity-tracking-and-log' );
					return $result;
				else :
					$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moove_activity_log WHERE `$key` = %s", $value ) ); //phpcs:ignore

					wp_cache_set( $cache_key, $result, 'user-activity-tracking-and-log' );
					return $result;
				endif;
			else :
				if ( $limit && intval( $limit ) ) :
					$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moove_activity_log WHERE %s = %s ORDER BY `visit_date` DESC LIMIT %d, %d", '1', '1', 1, $limit ) ); // phpcs:ignore

					wp_cache_set( $cache_key, $result, 'user-activity-tracking-and-log' );
					return $result;
				else :
					$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}moove_activity_log WHERE %s = %s", '1', '1' ) ); // phpcs:ignore

					wp_cache_set( $cache_key, $result, 'user-activity-tracking-and-log' );
					return $result;
				endif;
			endif;
		else :
			return $cache_value;
		endif;
	}
	/**
	 * Get usernames.
	 */
	public static function get_usernames() {
		global $wpdb;
		return $wpdb->get_results( "SELECT DISTINCT user_id FROM {$wpdb->prefix}moove_activity_log ORDER BY display_name ASC" ); // phpcs:ignore
	}

	/**
	 * Removing old logs
	 *
	 * @param int  $post_id Post ID.
	 * @param date $end_date End date.
	 */
	public static function remove_old_logs( $post_id, $end_date ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}moove_activity_log WHERE `post_id` = %s AND `visit_date` <= %s", $post_id, $end_date ) ); // phpcs:ignore
	}

	/**
	 * Delete log
	 *
	 * @param string $key Key.
	 * @param string $value Value.
	 */
	public static function delete_log( $key, $value ) {
		global $wpdb;
		$where = $key . '=' . $value;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}moove_activity_log WHERE `$key` = %s", $value ) ); // phpcs:ignore
	}

	/**
	 * Delete all logs from table
	 */
	public static function delete_all_logs() {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}moove_activity_log WHERE %s = %s", '1', '1' ) ); // phpcs:ignore
	}

	/**
	 * Delete all logs from table
	 */
	public static function delete_all_archive_logs() {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}moove_activity_log WHERE `post_id` < %s", '0' ) ); // phpcs:ignore
	}

	/**
	 * Insert data
	 *
	 * @param array $data Data to insert.
	 */
	public static function insert( $data ) {
		global $wpdb;
		$log_id 	= $wpdb->insert( self::uat_table(), $data ); // phpcs:ignore
		$response = array(
			'data' => $data,
			'id'   => $wpdb->insert_id
		);
		return wp_json_encode( $response );
	}

	/**
	 * Update data
	 *
	 * @param array  $data Data to update.
	 * @param string $where Where statement.
	 */
	public static function update( $data, $where ) {
		global $wpdb;
		$wpdb->update( self::uat_table(), $data, $where ); // phpcs:ignore
	}

	/**
	 * Unload tracker
	 *
	 * @param int $log_id Log id.
	 */
	public static function update_log_unload( $log_id ) {
		try {
			global $wpdb;
			$timestamp       = strtotime( 'now' );
			$res             = '';
			$start_timestamp = $wpdb->get_results( $wpdb->prepare( "SELECT `visit_date` FROM {$wpdb->prefix}moove_activity_log WHERE `id` = %s LIMIT 1", $log_id ) ); // phpcs:ignore
			$start_timestamp = isset( $start_timestamp[0] ) && isset( $start_timestamp[0]->visit_date ) ? $start_timestamp[0]->visit_date : '';

			if ( $start_timestamp ) :
				$start_timestamp = strtotime( $start_timestamp );
				$time_spent      = $timestamp - $start_timestamp;
				$res = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}moove_activity_log SET `time_spent` = %s WHERE `id` = %s", $time_spent, $log_id ) ); // phpcs:ignore
			endif;
			return $time_spent;
		} catch ( Exception $e ) {
			return 0;
		}
		return 0;
	}


	/**
	 * Delete value.
	 *
	 * @param string $value Value.
	 */
	public static function delete( $value ) {
		global $wpdb;
		$key = static::$primary_key;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}moove_activity_log WHERE `$key` = %s", $value ) ); // phpcs:ignore
	}

	/**
	 * Time to date converter.
	 *
	 * @param string $time Date & time timestamp.
	 */
	public static function time_to_date( $time ) {
		return gmdate( 'Y-m-d H:i:s', $time );
	}

	/**
	 * Returns current date.
	 */
	public static function now() {
		return self::time_to_date( time() );
	}

	/**
	 * GMT date converter.
	 *
	 * @param date $date Date.
	 */
	public static function date_to_time( $date ) {
		return strtotime( $date . ' GMT' );
	}
}
new Moove_Activity_Database_Model();
