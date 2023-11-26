<?php
/**
 * Moove_Activity_Array_Order File Doc Comment
 *
 * @category    Moove_Activity_Array_Order
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Moove_Activity_Array_Order Class Doc Comment
 *
 * @category Class
 * @package  Moove_Activity_Array_Order
 * @author   Moove Agency
 */
class Moove_Activity_Array_Order {
	/**
	 * Global variable used for order
	 *
	 * @var array
	 */
	private $orderby;

	/**
	 * Construct.
	 *
	 * @param string $orderby Order.
	 */
	public function __construct( $orderby = '' ) {
		$this->orderby = $orderby;
	}

	/**
	 * Sort array
	 *
	 * @param array  $a Array 1.
	 * @param array  $b Array 2.
	 * @param string $orderby Order.
	 */
	public function moove_sort_array( $a, $b, $orderby ) {
		if ( 'title' === $orderby ) :
			return strcmp( get_the_title( $a['post_id'] ), get_the_title( $b['post_id'] ) );
		elseif ( 'posttype' === $orderby ) :
			return strcmp( get_post_type( $a['post_id'] ), get_post_type( $b['post_id'] ) );
		else :
			return strcmp( $a[ $orderby ], $b[ $orderby ] );
		endif;
	}

	/**
	 * Custom Order
	 *
	 * @param array $a Array 1.
	 * @param array $b Array 2.
	 */
	public function custom_order( $a, $b ) {
		$uat_array_order = new Moove_Activity_Array_Order();
		return $uat_array_order->moove_sort_array( $a, $b, $this->orderby );
	}
}
