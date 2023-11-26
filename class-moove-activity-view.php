<?php
/**
 * Moove_Activity_View File Doc Comment
 *
 * @category    Moove_Activity_View
 * @package   moove-activity-tracking
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_View Class Doc Comment
 *
 * @category Class
 * @package  Moove_Activity_View
 * @author   Moove Agency
 */
class Moove_Activity_View {
	/**
	 * Load and update view
	 *
	 * Parameters:
	 *
	 * @param string $view // the view to load, dot used as directory separator, no file extension given.
	 * @param mixed  $data // The data to display in the view (could be anything, even an object).
	 */
	public static function load( $view, $data ) {
		$view_file_origin = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'views';
		$view_name        = str_replace( '.', DIRECTORY_SEPARATOR, $view ) . '.php';
		if ( file_exists( $view_file_origin . DIRECTORY_SEPARATOR . $view_name ) ) :
			ob_start();
			include $view_file_origin . DIRECTORY_SEPARATOR . $view_name;
			return ob_get_clean();
		endif;
	}
}
