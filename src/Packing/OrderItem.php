<?php

namespace Vendidero\Germanized\Shipments\Packing;

defined( 'ABSPATH' ) || exit;

/**
 * An item to be packed.
 */
class OrderItem extends Item {

	/**
	 * Box constructor.
	 *
	 * @param \WC_Order_Item_Product $item
	 *
	 * @throws \Exception
	 */
	public function __construct( $item ) {
		$this->item = $item;

		if ( ! is_callable( array( $item, 'get_product' ) ) ) {
			throw new \Exception( 'Invalid item' );
		}

		if ( $product = $this->get_product() ) {
			$width  = empty( $product->get_width() ) ? 0 : wc_format_decimal( $product->get_width() );
			$length = empty( $product->get_length() ) ? 0 : wc_format_decimal( $product->get_length() );
			$depth  = empty( $product->get_height() ) ? 0 : wc_format_decimal( $product->get_height() );

			$this->dimensions = array(
				'width'  => (int) ceil( (float) wc_get_dimension( $width, 'mm' ) ),
				'length' => (int) ceil( (float) wc_get_dimension( $length, 'mm' ) ),
				'depth'  => (int) ceil( (float) wc_get_dimension( $depth, 'mm' ) ),
			);

			$weight       = empty( $this->product->get_weight() ) ? 0 : wc_format_decimal( $this->product->get_weight() );
			$this->weight = (int) ceil( (float) wc_get_weight( $weight, 'g' ) );
		}

		$quantity      = (int) ceil( (float) $item->get_quantity() );
		$incl_taxes    = $item->get_order() ? $item->get_order()->get_prices_include_tax() : wc_prices_include_tax();
		$line_total    = (int) wc_add_number_precision( $this->item->get_total() );
		$line_subtotal = (int) wc_add_number_precision( $this->item->get_subtotal() );

		if ( $incl_taxes ) {
			$line_total    += (int) wc_add_number_precision( $this->item->get_total_tax() );
			$line_subtotal += (int) wc_add_number_precision( $this->item->get_subtotal_tax() );
		}

		$this->total    = $quantity > 0 ? $line_total / $quantity : 0;
		$this->subtotal = $quantity > 0 ? $line_subtotal / $quantity : 0;
	}

	protected function load_product() {
		$this->product = $this->item->get_product();
	}

	public function get_id() {
		return $this->item->get_id();
	}

	/**
	 * @return \WC_Order_Item_Product
	 */
	public function get_order_item() {
		return $this->get_reference();
	}
}
