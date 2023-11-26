<?php
/**
 * Activity_DT_Manager File Doc Comment
 *
 * @category  Activity_DT_Manager
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Activity_DT_Manager Class Doc Comment
 *
 * @category Class
 * @package  Activity_DT_Manager
 * @author   Moove Agency
 */
class Activity_DT_Manager {
	/**
	 * Create the data output array for the DataTables rows.
	 *
	 *  @param  array $columns Column information array.
	 *  @param  array $data    Data from the SQL get.
	 *  @return array          Formatted data in a row based format.
	 */
	public static function data_output( $columns, $data ) {
		$out = array();

		for ( $i = 0, $ien = count( $data ); $i < $ien; $i++ ) {
			$row = array();

			for ( $j = 0, $jen = count( $columns ); $j < $jen; $j++ ) {
				$column = $columns[ $j ];

				// Is there a formatter?
				if ( isset( $column['formatter'] ) ) {
					if ( empty( $column['db'] ) ) {
						$row[ $column['dt'] ] = $column['formatter']( $data[ $i ] );
					} else {
						$row[ $column['dt'] ] = $column['formatter']( $data[ $i ][ $column['db'] ], $data[ $i ] );
					}
				} else {
					if ( ! empty( $column['db'] ) ) {
						$row[ $column['dt'] ] = $data[ $i ][ $columns[ $j ]['db'] ];
					} else {
						$row[ $column['dt'] ] = '';
					}
				}
			}

			$out[] = $row;
		}

		return $out;
	}

	/**
	 * Create the data output array for the DataTables rows.
	 *
	 *  @param  array $columns Column information array.
	 *  @param  array $data    Data from the SQL get.
	 *  @return array          Formatted data in a row based format.
	 */
	public static function export_data_output( $columns, $data ) {
		$out = array();
		for ( $i = 0, $ien = count( $data ); $i < $ien; $i++ ) {
			$row = array();

			for ( $j = 0, $jen = count( $columns ); $j < $jen; $j++ ) {
				$column = $columns[ $j ];

				// Is there a formatter?
				if ( isset( $column['formatter'] ) ) :
					if ( empty( $column['db'] ) ) {
						$row[ $column['dt'] ] = $column['formatter']( $data[ $i ] );
					} else {
						$row[ $column['dt'] ] = $column['formatter']( $data[ $i ][ $column['db'] ], $data[ $i ] );
					}
				elseif ( isset( $column['hook'] ) ) :
					$row[ $column['dt'] ] = apply_filters( 'uat_csv_row_' . $column['hook'], '', $data[ $i ] );
				else :
					if ( ! empty( $column['db'] ) ) {
						$row[ $column['dt'] ] = $data[ $i ][ $columns[ $j ]['db'] ];
					} else {
						$row[ $column['dt'] ] = '';
					}
				endif;
			}

			$out[] = $row;
		}

		return $out;
	}

	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query.
	 *
	 *  @param  array $request Data sent to server by DataTables.
	 *  @param  array $columns Column information array.
	 *  @return string SQL limit clause.
	 */
	public static function limit( $request, $columns ) {
		$limit = '';

		if ( isset( $request['start'] ) && 1 !== $request['length'] ) {
			$limit = 'LIMIT ' . intval( $request['start'] ) . ', ' . intval( $request['length'] );
		}

		return $limit;
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query.
	 *
	 *  @param  array $request Data sent to server by DataTables.
	 *  @param  array $columns Column information array.
	 *  @return string SQL order by clause.
	 */
	public static function order( $request, $columns ) {
		$order = '';

		if ( isset( $request['order'] ) && count( $request['order'] ) ) {
			$order_by   = array();
			$dt_columns = self::pluck( $columns, 'dt' );

			for ( $i = 0, $ien = count( $request['order'] ); $i < $ien; $i++ ) {
				// Convert the column index into the column data property.
				$column_id_x    = intval( $request['order'][ $i ]['column'] );
				$request_column = $request['columns'][ $column_id_x ];

				$column_id_x = array_search( $request_column['data'], $dt_columns ); // phpcs:ignore
				$column      = $columns[ $column_id_x ];

				if ( 'true' === $request_column['orderable'] ) {
					$dir = 'asc' === $request['order'][ $i ]['dir'] ?
					'ASC' :
					'DESC';

					$order_by[] = '`' . $column['db'] . '` ' . $dir;
				}
			}

			if ( count( $order_by ) ) {
				$order = 'ORDER BY ' . implode( ', ', $order_by );
			}
		}

		return $order;
	}


	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor.
	 *
	 *  @param  array $request Data sent to server by DataTables.
	 *  @param  array $columns Column information array.
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    sql_exec() function.
	 *  @return string SQL where clause.
	 */
	public static function filter( $request, $columns, &$bindings ) {
		$global_search = array();
		$column_search = array();
		$dt_columns    = self::pluck( $columns, 'dt' );
		$has_post_type_f = false;

		if ( isset( $request['search'] ) && '' !== $request['search']['value'] ) {
			$str = $request['search']['value'];

			for ( $i = 0, $ien = count( $request['columns'] ); $i < $ien; $i++ ) {
				$request_column = $request['columns'][ $i ];
				$column_id_x    = array_search( $request_column['data'], $dt_columns ); // phpcs:ignore
				$column         = $columns[ $column_id_x ];

				if ( 'true' === $request_column['searchable'] ) {
					if ( ! empty( $column['db'] ) && 'visit_date' !== $column['db'] ) {
						$binding         = sanitize_text_field( wp_unslash( $str ) );
						$global_search[] = '`' . $column['db'] . '` LIKE ' . "'%" . $str . "%'";
					}
				}
			}
		}

		// Individual column filtering.
		if ( isset( $request['columns'] ) ) {
			for ( $i = 0, $ien = count( $request['columns'] ); $i < $ien; $i++ ) {
				$request_column = $request['columns'][ $i ];
				$column_id_x    = array_search( $request_column['data'], $dt_columns ); // phpcs:ignore
				$column         = $columns[ $column_id_x ];

				$str = $request_column['search']['value'];

				if ( 'true' === $request_column['searchable'] &&
					'' !== $str ) {
					if ( ! empty( $column['db'] ) && 'visit_date' !== $column['db'] ) {
						$binding         = sanitize_text_field( wp_unslash( $str ) );
						$column_search[] = '`' . $column['db'] . '` LIKE ' . "'%" . $str . "'%";
					}
				}
			}
		}

		if ( isset( $request['top_filters'] ) && ! empty( $request['top_filters'] ) ) :
			foreach ( $request['top_filters'] as $filter_name => $filter_value ) :
				switch ( $filter_name ) :
					case 'dt-date-filter':
						$column_search[] = 'CONCAT(YEAR(`visit_date`), RIGHT(CONCAT("0", RTRIM(MONTH(`visit_date`))),2) ) = "' . $filter_value . '"';
						break;
					case 'dt-post_type-filter':
						$column_search[] = '`post_type` = "' . $filter_value . '"';
						$has_post_type_f = true;
						break;
					case 'dt-user-filter':
						$column_search[] = '`user_id` = "' . $filter_value . '"';
						break;
					case 'dt-user_role-filter':
						$column_search[] = '`user_id` IN (' . $filter_value . ')';
						break;
					case 'dt-cpt_post_id':
						$column_search[] = '`post_id` = "' . $filter_value . '"';
						break;
					case 'dt-archive_filter':
						$column_search[] = '`post_id` < 0';
						break;
					case 'dt-ip-address':
						$column_search[] = '`user_ip` = "' . $filter_value . '"';
						break;
					default:
						// code...
						break;
				endswitch;
			endforeach;
		endif;

		if ( ! $has_post_type_f ) :
			$post_types = uat_get_enabled_post_types();
			$post_types = $post_types ? $post_types : array();
			$post_types = apply_filters('uat_before_db_post_types', $post_types);

			$column_search[] = '`post_type` IN ("' . implode( '","', $post_types ) . '")';
		endif;

		// Combine the filters into a single string.
		$where = '';

		if ( count( $global_search ) ) {
			$where = '(' . implode( ' OR ', $global_search ) . ')';
		}

		if ( count( $column_search ) ) {
			$where = '' === $where ?
			implode( ' AND ', $column_search ) :
			$where . ' AND ' . implode( ' AND ', $column_search );
		}

		if ( '' !== $where ) :
			$where = 'WHERE ' . $where;
		endif;

		return $where;
	}

	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array  $request Data sent to server by DataTables.
	 *  @param  array  $columns Column information array.
	 *  @param string $type Type.
	 *  @return array     Server-side processing response array.
	 */
	public static function export( $request, $columns, $type = 'all' ) {
		global $wpdb;
		$bindings    = array();
		$primary_key = 'id';
		$table       = "{$wpdb->prefix}moove_activity_log";
		$order       = 'ORDER BY `visit_date`';
		$where       = 'all' === $type ? '' : self::filter( $request, $columns, $bindings );

		if ( 'cpt' === $type && isset( $request['log_id'] ) && intval( $request['log_id'] ) ) :
			$where = 'WHERE `post_id` = ' . intval( $request['log_id'] );
			try {
				$uat_controller = new Moove_Activity_Controller();
				$uat_controller->moove_remove_old_logs( $request['log_id'] );
			} catch ( Exception $e ) {
				unset( $e );
			}

		endif;
		// Main query to actually get the data.

		$cache_key = 'aut_et_dt_cache_' . md5( $order . $where );
		$data      = wp_cache_get( $cache_key, 'user-activity-tracking-and-log' );
		if ( ! $data ) :
			$_post_types           = get_post_types( array( 'public' => true ) );
			/**
			 * Where clause filters
			 */
			$where = str_replace( '`post_type`', 'uat_log.post_type', $where );
			$where = str_replace( '`display_name`', 'uat_log.display_name', $where );
			$where = str_replace( '`user_email`', 'users_tbl.user_email', $where );
			$where = str_replace( '`user_login`', 'users_tbl.user_login', $where );
			$where = str_replace( '`permalink`', 'posts_tbl.guid', $where );

			$sql = "
				SELECT 
					`post_id`, 
					uat_log.display_name, 
					`user_ip`, 
					`status`,
					`referer`, 
					`visit_date`,
					`city`,					 
					`user_id`,
					posts_tbl.guid as `permalink`, 
					`event`, 
					`type`, 
					`time_spent`, 
					`extras`, 
					users_tbl.user_email,
					users_tbl.user_login,
					uat_log.post_type, 
					posts_tbl.post_title as `post_title`, 
					`request_url`,
					`archive_title`,
					`campaign_id` 
				FROM {$wpdb->prefix}moove_activity_log uat_log 
					LEFT JOIN {$wpdb->prefix}posts posts_tbl	
						ON uat_log.post_id = posts_tbl.id
					LEFT JOIN {$wpdb->base_prefix}users users_tbl	
						ON uat_log.user_id = users_tbl.id
				$where
				$order
			";

			$sql_count = "
				SELECT 
					COUNT(`visit_date`) as '0' 
				FROM {$wpdb->prefix}moove_activity_log uat_log 
					LEFT JOIN {$wpdb->prefix}posts posts_tbl	
						ON uat_log.post_id = posts_tbl.id
					LEFT JOIN {$wpdb->base_prefix}users users_tbl	
						ON uat_log.user_id = users_tbl.id
				$where
			";

			$data = $wpdb->get_results(
				$sql, // phpcs:ignore
				ARRAY_A
			); // db call ok; no-cache ok.

			wp_cache_set( $cache_key, $data, 'user-activity-tracking-and-log' );
		endif;

		$res_filter_length = $wpdb->get_results( $sql_count, ARRAY_A ); // phpcs:ignore
		$records_filtered  = isset( $res_filter_length[0] ) && isset( $res_filter_length[0][0] ) ? $res_filter_length[0][0] : 0;

		/*
		 * Output.
		 */

		$headers 		= array( 'Date / Time', 'Post Title', 'Post Type', 'User Email', 'Username', 'Display Name', 'Visit Duration', 'User Role', 'Location', 'IP Address', 'Referrer', 'Permalink', 'Full URL' );
		$headers_f 	= array_values( apply_filters('uat_csv_dt_header', array() ) );
		$headers 		= array( array_merge( $headers, $headers_f ) );
		
		return array(
			'limit'   => '',
			'headers' => $headers,
			'data'    => self::export_data_output( $columns, $data )
		);
	}

	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array  $request Data sent to server by DataTables.
	 *  @param  array  $columns Column information array.
	 *  @param string $type Type.
	 *  @return array     Server-side processing response array.
	 */
	public static function delete( $request, $columns ) {
		global $wpdb;
		$bindings    = array();
		$primary_key = 'id';
		$table       = "{$wpdb->prefix}moove_activity_log";
		$limit       = '';
		$order       = '';
		$where       = self::filter( $request, $columns, $bindings );

		$_post_types           = get_post_types( array( 'public' => true ) );

		/**
		 * Where clause filters
		 */
		$where = str_replace( '`post_type`', 'uat_log.post_type', $where );
		$where = str_replace( '`display_name`', 'uat_log.display_name', $where );
		$where = str_replace( '`user_email`', 'users_tbl.user_email', $where );
		$where = str_replace( '`user_login`', 'users_tbl.user_login', $where );
		$where = str_replace( '`permalink`', 'posts_tbl.guid', $where );

		$sql = "
			DELETE 
				uat_log
			FROM {$wpdb->prefix}moove_activity_log uat_log 
				LEFT JOIN {$wpdb->prefix}posts posts_tbl	
					ON uat_log.post_id = posts_tbl.id
				LEFT JOIN {$wpdb->base_prefix}users users_tbl	
					ON uat_log.user_id = users_tbl.id
			$where
			$order
			$limit
		";

		$data = $wpdb->get_results(
			$sql, // phpcs:ignore
			ARRAY_A
		); // db call ok; no-cache ok.

		return $data;
	}


	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables.
	 *  @param  array $columns Column information array.
	 *  @return array     Server-side processing response array.
	 */
	public static function simple( $request, $columns ) {
		global $wpdb;
		$bindings    = array();
		$primary_key = 'id';
		$table       = "{$wpdb->prefix}moove_activity_log";

		// Build the SQL query string from the request.
		$limit = self::limit( $request, $columns );
		$order = self::order( $request, $columns );
		$where = self::filter( $request, $columns, $bindings );
		// Main query to actually get the data.

		$cache_key = 'aut_et_dt_cache_' . md5( $limit . $order . $where );
		$data      = wp_cache_get( $cache_key, 'user-activity-tracking-and-log' );
		if ( ! $data ) :
			$_post_types           = get_post_types( array( 'public' => true ) );

			/**
			 * Where clause filters
			 */
			$where = str_replace( '`post_type`', 'uat_log.post_type', $where );
			$where = str_replace( '`display_name`', 'uat_log.display_name', $where );
			$where = str_replace( '`user_email`', 'users_tbl.user_email', $where );
			$where = str_replace( '`user_login`', 'users_tbl.user_login', $where );
			$where = str_replace( '`permalink`', 'posts_tbl.guid', $where );

			$sql = "
				SELECT 
					`post_id`, 
					`visit_date`,
					uat_log.display_name, 
					`user_ip`, 
					`status`,
					`referer`, 
					`city`, 
					`user_id`,
					posts_tbl.guid as `permalink`, 
					`event`, 
					`type`, 
					`time_spent`, 
					`extras`, 
					users_tbl.user_email,
					users_tbl.user_login,
					uat_log.post_type, 
					`request_url`,
					`archive_title`,
					posts_tbl.post_title as `post_title`, 
					`campaign_id` 
				FROM {$wpdb->prefix}moove_activity_log uat_log 
					LEFT JOIN {$wpdb->prefix}posts posts_tbl	
						ON uat_log.post_id = posts_tbl.id
					LEFT JOIN {$wpdb->base_prefix}users users_tbl	
						ON uat_log.user_id = users_tbl.id
				$where
				$order 
				$limit
			";

			$sql_count = "
				SELECT 
					COUNT(`visit_date`) as '0' 
				FROM {$wpdb->prefix}moove_activity_log uat_log 
					LEFT JOIN {$wpdb->prefix}posts posts_tbl	
						ON uat_log.post_id = posts_tbl.id
					LEFT JOIN {$wpdb->base_prefix}users users_tbl	
						ON uat_log.user_id = users_tbl.id
				$where
			";

			$data = $wpdb->get_results(
				$sql, // phpcs:ignore
				ARRAY_A
			); // db call ok; no-cache ok.

			wp_cache_set( $cache_key, $data, 'user-activity-tracking-and-log' );
		endif;

		$sql_count = "
			SELECT 
				COUNT(`visit_date`) as '0' 
			FROM {$wpdb->prefix}moove_activity_log uat_log 
				LEFT JOIN {$wpdb->prefix}posts posts_tbl	
					ON uat_log.post_id = posts_tbl.id
				LEFT JOIN {$wpdb->base_prefix}users users_tbl	
					ON uat_log.user_id = users_tbl.id
			$where
		";

		$res_filter_length = $wpdb->get_results( $sql_count, ARRAY_A ); // phpcs:ignore
		$records_filtered  = isset( $res_filter_length[0] ) && isset( $res_filter_length[0][0] ) ? $res_filter_length[0][0] : 0;

		$sql_month_year = "
			SELECT DISTINCT
			CONCAT(YEAR(uat_log.visit_date), RIGHT(CONCAT('0', RTRIM(MONTH(uat_log.visit_date))),2) ) as `ym`
			FROM {$wpdb->prefix}moove_activity_log uat_log 
			LEFT JOIN {$wpdb->prefix}posts posts_tbl	
			ON uat_log.post_id = posts_tbl.id
			ORDER BY `ym` DESC";

		$sql_users = "
			SELECT DISTINCT
				`user_id`,
				users_tbl.user_login as `username`,
				users_tbl.display_name as `display_name`
			FROM {$wpdb->prefix}moove_activity_log uat_log 
				LEFT JOIN {$wpdb->prefix}posts posts_tbl	
					ON uat_log.post_id = posts_tbl.id
				LEFT JOIN {$wpdb->base_prefix}users users_tbl	
					ON uat_log.user_id = users_tbl.id
			ORDER BY users_tbl.user_login ASC
		";

		$date_filter       = array();
		$user_filter       = array();
		$users_role_filter = array();
		/**
		 * We should fill the filters only on init
		 */
		if ( isset( $request['draw'] ) && intval( $request['draw'] ) === 1 ) {
			$date_filter 	 = $wpdb->get_results( $sql_month_year, ARRAY_A ); // phpcs:ignore
			$user_filter 	 = $wpdb->get_results( $sql_users, ARRAY_A ); // phpcs:ignore

			if ( $user_filter && ! empty( $user_filter ) ) :
				foreach ( $user_filter as $_user_data ) :
					if ( intval( $_user_data['user_id'] ) ) :
						$user_meta = wp_cache_get( 'uat_user_meta_' . $_user_data['user_id'], 'user-activity-tracking-and-log' );
						if ( ! $user_meta ) :
							$user_meta = get_userdata( intval( $_user_data['user_id'] ) );
						endif;
						if ( $user_meta && isset( $user_meta->roles ) ) :
							$user_roles = $user_meta->roles;
							if ( isset( $user_roles[0] ) ) :
								$user_role                         = $user_roles[0];
								$users_role_filter[ $user_role ][] = $_user_data['user_id'];
							endif;
						endif;
					endif;
				endforeach;
			endif;
		}

		/*
		 * Output.
		 */
		return array(
			'draw'              => isset( $request['draw'] ) ?
			intval( $request['draw'] ) :
			0,
			'recordsTotal'      => intval( $records_filtered ),
			'recordsFiltered'   => intval( $records_filtered ),
			'date_filter'       => $date_filter,
			'users_filter'      => $user_filter,
			'users_role_filter' => $users_role_filter,
			'data'              => self::data_output( $columns, $data )
		);
	}

	/*
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods.
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client.
	 */
	public static function fatal( $msg ) {
		echo wp_json_encode(
			array(
				'error' => $msg,
			)
		);
		exit( 0 );
	}

	/**
	 * Pull a particular property from each assoc. array in a numeric array,
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from.
	 *  @param  string $prop Property to read.
	 *  @return array        Array of property values.
	 */
	public static function pluck( $a, $prop ) {
		$out = array();

		for ( $i = 0, $len = count( $a ); $i < $len; $i++ ) {
			if ( empty( $a[ $i ][ $prop ] ) && 0 !== $a[ $i ][ $prop ] ) {
				continue;
			}

			// removing the $out array index confuses the filter method in doing proper binding,
			// adding it ensures that the array data are mapped correctly.
			$out[ $i ] = $a[ $i ][ $prop ];
		}

		return $out;
	}
}
