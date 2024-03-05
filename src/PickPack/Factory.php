<?php
namespace Vendidero\Germanized\Shipments\PickPack;

use \WC_Data_Store;
use \Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Shipment factory class
 */
class Factory {

	/**
	 * Get pick pack order.
	 *
	 * @param  mixed $pick_pack_order_id (default: false) Pick pack order id to get or empty if new.
	 * @return Order|false
	 */
	public static function get_pick_pack_order( $pick_pack_order_id = false ) {
		$pick_pack_order_id = self::get_pick_pack_order_id( $pick_pack_order_id );

		/**
		 * Filter to adjust the classname used to construct a Pick & Pack order.
		 *
		 * @param string  $classname The classname to be used.
		 * @param integer $pick_pack_order_id The pick pack order id.
		 */
		$classname = apply_filters( 'woocommerce_gzd_pick_pack_order_class', '\Vendidero\Germanized\Shipments\PickPack\Order', $pick_pack_order_id );

		if ( ! class_exists( $classname ) ) {
			return false;
		}

		try {
			$pick_pack_order = new $classname( $pick_pack_order_id );

			return $pick_pack_order;
		} catch ( Exception $e ) {
			wc_caught_exception( $e, __FUNCTION__, array( $pick_pack_order_id ) );
			return false;
		}
	}

	public static function get_pick_pack_order_id( $pick_pack_order ) {
		if ( is_numeric( $pick_pack_order ) ) {
			return $pick_pack_order;
		} elseif ( $pick_pack_order instanceof Order ) {
			return $pick_pack_order->get_id();
		} elseif ( ! empty( $pick_pack_order->pick_pack_order_id ) ) {
			return $pick_pack_order->pick_pack_order_id;
		} else {
			return false;
		}
	}
}
