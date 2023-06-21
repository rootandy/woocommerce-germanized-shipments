<?php
/**
 * ShippingProvider impl.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\Shipments\ShippingProvider;

use Vendidero\Germanized\Shipments\Interfaces\ShippingProviderAuto;
use Vendidero\Germanized\Shipments\Labels\Factory;
use Vendidero\Germanized\Shipments\Package;
use Vendidero\Germanized\Shipments\Packaging;
use Vendidero\Germanized\Shipments\Shipment;
use Vendidero\Germanized\Shipments\ShipmentError;
use Vendidero\Germanized\Shipments\SimpleShipment;

defined( 'ABSPATH' ) || exit;

abstract class Auto extends Simple implements ShippingProviderAuto {

	protected $extra_data = array(
		'label_default_shipment_weight'      => '',
		'label_minimum_shipment_weight'      => '',
		'label_auto_enable'                  => false,
		'label_auto_shipment_status'         => 'gzd-processing',
		'label_return_auto_enable'           => false,
		'label_return_auto_shipment_status'  => 'gzd-processing',
		'label_auto_shipment_status_shipped' => false,
	);

	public function get_label_default_shipment_weight( $context = 'view' ) {
		$weight = $this->get_prop( 'label_default_shipment_weight', $context );

		if ( 'view' === $context && '' === $weight ) {
			$weight = $this->get_default_label_default_shipment_weight();
		}

		return $weight;
	}

	protected function get_default_label_default_shipment_weight() {
		return 0;
	}

	public function get_label_minimum_shipment_weight( $context = 'view' ) {
		$weight = $this->get_prop( 'label_minimum_shipment_weight', $context );

		if ( 'view' === $context && '' === $weight ) {
			$weight = $this->get_default_label_minimum_shipment_weight();
		}

		return $weight;
	}

	protected function get_default_label_minimum_shipment_weight() {
		return 0.5;
	}

	/**
	 * @param false|Shipment $shipment
	 *
	 * @return boolean
	 */
	public function automatically_generate_label( $shipment = false ) {
		$setting_key = 'label_auto_enable';

		if ( $shipment ) {
			if ( 'return' === $shipment->get_type() ) {
				$setting_key = 'label_return_auto_enable';
			}

			return wc_string_to_bool( $this->get_shipment_setting( $shipment, $setting_key, false ) );
		} else {
			return wc_string_to_bool( $this->get_setting( $setting_key, false ) );
		}
	}

	/**
	 * @param false|Shipment $shipment
	 *
	 * @return string
	 */
	public function get_label_automation_shipment_status( $shipment = false ) {
		$setting_key = 'label_auto_shipment_status';

		if ( $shipment ) {
			if ( 'return' === $shipment->get_type() ) {
				$setting_key = 'label_return_auto_shipment_status';
			}

			return $this->get_shipment_setting( $shipment, $setting_key, 'gzd-processing' );
		} else {
			return $this->get_setting( $setting_key, 'gzd-processing' );
		}
	}

	public function automatically_set_shipment_status_shipped( $shipment = false ) {
		$setting_key = 'label_auto_shipment_status_shipped';

		if ( $shipment ) {
			return wc_string_to_bool( $this->get_shipment_setting( $shipment, $setting_key, false ) );
		} else {
			return wc_string_to_bool( $this->get_setting( $setting_key, false ) );
		}
	}

	public function get_label_auto_enable( $context = 'view' ) {
		return $this->get_prop( 'label_auto_enable', $context );
	}

	public function get_label_auto_shipment_status_shipped( $context = 'view' ) {
		return $this->get_prop( 'label_auto_shipment_status_shipped', $context );
	}

	public function get_label_auto_shipment_status( $context = 'view' ) {
		return $this->get_prop( 'label_auto_shipment_status', $context );
	}

	public function automatically_generate_return_label() {
		return $this->get_label_return_auto_enable();
	}

	public function get_label_return_auto_enable( $context = 'view' ) {
		return $this->get_prop( 'label_return_auto_enable', $context );
	}

	public function get_label_return_auto_shipment_status( $context = 'view' ) {
		return $this->get_prop( 'label_return_auto_shipment_status', $context );
	}

	public function is_sandbox() {
		return false;
	}

	public function set_label_default_shipment_weight( $weight ) {
		$this->set_prop( 'label_default_shipment_weight', ( '' === $weight ? '' : wc_format_decimal( $weight ) ) );
	}

	public function set_label_minimum_shipment_weight( $weight ) {
		$this->set_prop( 'label_minimum_shipment_weight', ( '' === $weight ? '' : wc_format_decimal( $weight ) ) );
	}

	public function set_label_auto_enable( $enable ) {
		$this->set_prop( 'label_auto_enable', wc_string_to_bool( $enable ) );
	}

	public function set_label_auto_shipment_status_shipped( $enable ) {
		$this->set_prop( 'label_auto_shipment_status_shipped', wc_string_to_bool( $enable ) );
	}

	public function set_label_auto_shipment_status( $status ) {
		$this->set_prop( 'label_auto_shipment_status', $status );
	}

	public function set_label_return_auto_enable( $enable ) {
		$this->set_prop( 'label_return_auto_enable', wc_string_to_bool( $enable ) );
	}

	public function set_label_return_auto_shipment_status( $status ) {
		$this->set_prop( 'label_return_auto_shipment_status', $status );
	}

	public function get_label_classname( $type ) {
		return '\Vendidero\Germanized\Shipments\Labels\Simple';
	}

	/**
	 * Whether or not this instance is a manual integration.
	 * Manual integrations are constructed dynamically from DB and do not support
	 * automatic shipment handling, e.g. label creation.
	 *
	 * @return bool
	 */
	public function is_manual_integration() {
		return false;
	}

	/**
	 * Whether or not this instance supports a certain label type.
	 *
	 * @param string $label_type The label type e.g. simple or return.
	 *
	 * @return bool
	 */
	public function supports_labels( $label_type, $shipment = false ) {
		return true;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 *
	 * @return mixed|void
	 */
	public function get_label( $shipment ) {
		$type  = wc_gzd_get_label_type_by_shipment( $shipment );
		$label = wc_gzd_get_label_by_shipment( $shipment, $type );

		return apply_filters( "{$this->get_hook_prefix()}label", $label, $shipment, $this );
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	public function get_label_fields_html( $shipment ) {
		/**
		 * Setup local variables
		 */
		$settings = $this->get_label_fields( $shipment );
		$provider = $this;

		if ( is_wp_error( $settings ) ) {
			$error = $settings;

			ob_start();
			include Package::get_path() . '/includes/admin/views/label/html-shipment-label-backbone-error.php';
			$html = ob_get_clean();
		} else {
			ob_start();
			include Package::get_path() . '/includes/admin/views/label/html-shipment-label-backbone-form.php';
			$html = ob_get_clean();
		}

		return apply_filters( "{$this->get_hook_prefix()}label_fields_html", $html, $shipment, $this );
	}

	protected function get_automation_settings( $for_shipping_method = false ) {
		$settings = array(
			array(
				'title'          => _x( 'Automation', 'shipments', 'woocommerce-germanized-shipments' ),
				'allow_override' => true,
				'type'           => 'title',
				'id'             => 'shipping_provider_label_auto_options',
			),
		);

		$shipment_statuses = array_diff_key( wc_gzd_get_shipment_statuses(), array_fill_keys( array( 'gzd-draft', 'gzd-delivered', 'gzd-returned', 'gzd-requested' ), '' ) );

		$settings = array_merge(
			$settings,
			array(
				array(
					'title' => _x( 'Labels', 'shipments', 'woocommerce-germanized-shipments' ),
					'desc'  => _x( 'Automatically create labels for shipments.', 'shipments', 'woocommerce-germanized-shipments' ),
					'id'    => 'label_auto_enable',
					'type'  => 'gzd_toggle',
					'value' => wc_bool_to_string( $this->get_setting( 'label_auto_enable' ) ),
				),

				array(
					'title'             => _x( 'Status', 'shipments', 'woocommerce-germanized-shipments' ),
					'type'              => 'select',
					'id'                => 'label_auto_shipment_status',
					'desc'              => '<div class="wc-gzd-additional-desc">' . _x( 'Choose a shipment status which should trigger generation of a label.', 'shipments', 'woocommerce-germanized-shipments' ) . ' ' . ( 'yes' === Package::get_setting( 'auto_enable' ) ? sprintf( _x( 'Your current default shipment status is: <em>%s</em>.', 'shipments', 'woocommerce-germanized-shipments' ), wc_gzd_get_shipment_status_name( Package::get_setting( 'auto_default_status' ) ) ) : '' ) . '</div>',
					'options'           => $shipment_statuses,
					'class'             => 'wc-enhanced-select',
					'custom_attributes' => array( 'data-show_if_label_auto_enable' => '' ),
					'value'             => $this->get_setting( 'label_auto_shipment_status' ),
				),

				array(
					'title' => _x( 'Shipment Status', 'shipments', 'woocommerce-germanized-shipments' ),
					'desc'  => _x( 'Mark shipment as shipped after label has been created successfully.', 'shipments', 'woocommerce-germanized-shipments' ),
					'id'    => 'label_auto_shipment_status_shipped',
					'type'  => 'gzd_toggle',
					'value' => wc_bool_to_string( $this->get_setting( 'label_auto_shipment_status_shipped' ) ),
				),
			)
		);

		if ( $this->supports_labels( 'return' ) ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => _x( 'Returns', 'shipments', 'woocommerce-germanized-shipments' ),
						'desc'  => _x( 'Automatically create labels for returns.', 'shipments', 'woocommerce-germanized-shipments' ),
						'id'    => 'label_return_auto_enable',
						'type'  => 'gzd_toggle',
						'value' => wc_bool_to_string( $this->get_setting( 'label_return_auto_enable' ) ),
					),

					array(
						'title'             => _x( 'Status', 'shipments', 'woocommerce-germanized-shipments' ),
						'type'              => 'select',
						'id'                => 'label_return_auto_shipment_status',
						'desc'              => '<div class="wc-gzd-additional-desc">' . _x( 'Choose a shipment status which should trigger generation of a return label.', 'shipments', 'woocommerce-germanized-shipments' ) . '</div>',
						'options'           => $shipment_statuses,
						'class'             => 'wc-enhanced-select',
						'custom_attributes' => array( 'data-show_if_label_return_auto_enable' => '' ),
						'value'             => $this->get_setting( 'label_return_auto_shipment_status' ),
					),
				)
			);
		}

		$settings = array_merge(
			$settings,
			array(
				array(
					'type' => 'sectionend',
					'id'   => 'shipping_provider_label_auto_options',
				),
			)
		);

		return $settings;
	}

	public function get_settings_help_pointers( $section = '' ) {
		return array();
	}

	protected function get_label_settings( $for_shipping_method = false ) {
		$settings = array(
			array(
				'title' => '',
				'type'  => 'title',
				'id'    => 'shipping_provider_label_options',
			),

			array(
				'title'    => _x( 'Default content weight (kg)', 'shipments', 'woocommerce-germanized-shipments' ),
				'type'     => 'text',
				'desc'     => _x( 'Choose a default shipment content weight to be used for labels if no weight has been applied to the shipment.', 'shipments', 'woocommerce-germanized-shipments' ),
				'desc_tip' => true,
				'id'       => 'label_default_shipment_weight',
				'css'      => 'max-width: 60px;',
				'class'    => 'wc_input_decimal',
				'default'  => $this->get_default_label_default_shipment_weight(),
				'value'    => wc_format_localized_decimal( $this->get_setting( 'label_default_shipment_weight' ) ),
			),

			array(
				'title'    => _x( 'Minimum weight (kg)', 'shipments', 'woocommerce-germanized-shipments' ),
				'type'     => 'text',
				'desc'     => _x( 'Choose a minimum weight to be used for labels e.g. to prevent low shipment weight errors.', 'shipments', 'woocommerce-germanized-shipments' ),
				'desc_tip' => true,
				'id'       => 'label_minimum_shipment_weight',
				'css'      => 'max-width: 60px;',
				'class'    => 'wc_input_decimal',
				'default'  => $this->get_default_label_minimum_shipment_weight(),
				'value'    => wc_format_localized_decimal( $this->get_setting( 'label_minimum_shipment_weight' ) ),
			),

			array(
				'type' => 'sectionend',
				'id'   => 'shipping_provider_label_options',
			),
		);

		return $settings;
	}

	protected function get_available_base_countries() {
		$countries = array();

		if ( function_exists( 'WC' ) && WC()->countries ) {
			$countries = WC()->countries->get_countries();
		}

		return $countries;
	}

	public function get_setting_sections() {
		$sections = array(
			''           => _x( 'General', 'shipments', 'woocommerce-germanized-shipments' ),
			'label'      => _x( 'Labels', 'shipments', 'woocommerce-germanized-shipments' ),
			'automation' => _x( 'Automation', 'shipments', 'woocommerce-germanized-shipments' ),
		);

		$sections = array_replace_recursive( $sections, parent::get_setting_sections() );

		return $sections;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	public function get_label_fields( $shipment ) {
		if ( 'return' === $shipment->get_type() ) {
			return $this->get_return_label_fields( $shipment );
		} else {
			return $this->get_simple_label_fields( $shipment );
		}
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	protected function get_simple_label_fields( $shipment ) {
		$default   = $this->get_default_label_product( $shipment );
		$available = $this->get_available_label_products( $shipment );

		$settings = array(
			array(
				'id'          => 'product_id',
				'label'       => sprintf( _x( '%s Product', 'shipments', 'woocommerce-germanized-shipments' ), $this->get_title() ),
				'description' => '',
				'options'     => $this->get_available_label_products( $shipment ),
				'value'       => $default && array_key_exists( $default, $available ) ? $default : '',
				'type'        => 'select',
			),
		);

		return $settings;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	protected function get_return_label_fields( $shipment ) {
		return $this->get_simple_label_fields( $shipment );
	}

	/**
	 * @param Shipment $shipment
	 * @param $props
	 *
	 * @return ShipmentError|mixed
	 */
	protected function validate_label_request( $shipment, $props ) {
		return $props;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_default_label_props( $shipment ) {
		$default = array(
			'shipping_provider' => $this->get_name(),
			'weight'            => wc_gzd_get_shipment_label_weight( $shipment ),
			'net_weight'        => wc_gzd_get_shipment_label_weight( $shipment, true ),
			'shipment_id'       => $shipment->get_id(),
			'services'          => array(),
			'product_id'        => $this->get_default_label_product( $shipment ),
		);

		$dimensions = wc_gzd_get_shipment_label_dimensions( $shipment );
		$default    = array_merge( $default, $dimensions );

		return $default;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 * @param mixed $props
	 *
	 * @return ShipmentError|true
	 */
	public function create_label( $shipment, $props = false ) {
		/**
		 * In case props is false this indicates an automatic (non-manual) request.
		 */
		if ( false === $props ) {
			$props = $this->get_default_label_props( $shipment );
		} elseif ( is_array( $props ) ) {
			$fields = $this->get_label_fields( $shipment );

			/**
			 * By default checkbox fields won't be transmitted via POST data.
			 * In case the values does not exist within props, assume not checked.
			 */
			foreach ( $fields as $field ) {
				if ( ! isset( $field['value'] ) ) {
					continue;
				}

				if ( 'checkbox' === $field['type'] && ! isset( $props[ $field['id'] ] ) ) {
					// Exclude array fields from default checkbox handling
					if ( isset( $field['name'] ) && strstr( $field['name'], '[]' ) ) {
						continue;
					}

					$props[ $field['id'] ] = 'no';
				} elseif ( 'multiselect' === $field['type'] ) {
					if ( isset( $props[ $field['id'] ] ) ) {
						$props[ $field['id'] ] = (array) $props[ $field['id'] ];
					}
				}
			}

			/**
			 * Merge with default data. That needs to be done after manually
			 * parsing checkboxes as missing data would be overridden with defaults.
			 */
			$props = wp_parse_args( $props, $this->get_default_label_props( $shipment ) );

			foreach ( $props as $key => $value ) {
				if ( substr( $key, 0, strlen( 'service_' ) ) === 'service_' ) {
					$new_key = substr( $key, ( strlen( 'service_' ) ) );

					if ( wc_string_to_bool( $value ) && in_array( $new_key, $this->get_available_label_services( $shipment ), true ) ) {
						if ( ! in_array( $new_key, $props['services'], true ) ) {
							$props['services'][] = $new_key;
						}
						unset( $props[ $key ] );
					} else {
						if ( ( $service_key = array_search( $new_key, $props['services'], true ) ) !== false ) {
							unset( $props['services'][ $service_key ] );
						}
						unset( $props[ $key ] );
					}
				}
			}
		}

		$props = $this->validate_label_request( $shipment, $props );

		if ( is_wp_error( $props ) ) {
			return $props;
		}

		if ( isset( $props['services'] ) ) {
			$props['services'] = array_unique( $props['services'] );
		}

		$label = Factory::get_label( 0, $this->get_name(), $shipment->get_type() );

		if ( $label ) {
			foreach ( $props as $key => $value ) {
				$setter = "set_{$key}";

				if ( is_callable( array( $label, $setter ) ) ) {
					$label->{$setter}( $value );
				} else {
					$label->update_meta_data( $key, $value );
				}
			}

			$label->set_shipment( $shipment );

			/**
			 * Fetch the label via API and store as file
			 */
			$result = $label->fetch();

			if ( is_wp_error( $result ) ) {
				$result = wc_gzd_get_shipment_error( $result );

				if ( ! $result->is_soft_error() ) {
					return $result;
				}
			}

			do_action( "{$this->get_general_hook_prefix()}created_label", $label, $this );
			$label_id = $label->save();

			return is_wp_error( $result ) && $result->is_soft_error() ? $result : $label_id;
		}

		return new ShipmentError( 'label-error', _x( 'Error while creating the label.', 'shipments', 'woocommerce-germanized-shipments' ) );
	}

	public function get_available_label_zones( $shipment_type = 'simple' ) {
		return array(
			'dom',
			'eu',
			'int'
		);
	}

	public function get_services( $shipment = false ) {
		return array();
	}

	public function get_product_services( $product, $shipment = false ) {
		return array();
	}

	protected function override_setting_in_packaging( $option, $shipment_type = 'simple', $zone = 'dom' ) {
		return false;
	}

	/**
	 * @param Packaging $packaging
	 * @param string $shipment_type
	 * @param string $zone
	 *
	 * @return array
	 */
	public function get_packaging_label_settings( $packaging, $shipment_type = 'simple', $zone = 'dom' ) {
		if ( ! $this->supports_labels( $shipment_type ) ) {
			return array();
		}

		if ( ! in_array( $zone, $this->get_available_label_zones( $shipment_type ), true ) ) {
			return array();
		}

		$provider_label_settings = $this->get_label_settings( true );
		$label_settings          = array(
			'product'    => array(),
			'services'   => array(),
			'additional' => array(),
		);
		$has_settings       = false;
		$configuration_set  = $packaging->get_configuration_set( $this->get_name(), $shipment_type, $zone );
		$service_identifier = 'simple' === $shipment_type ? 'label_service_' : $shipment_type . '_label_service_';
		$product_identifier = 'simple' === $shipment_type ? 'label_default_product' : $shipment_type . 'label_default_product';

		foreach( $provider_label_settings as $label_setting ) {
			if ( isset( $label_setting['id'] ) ) {
				$override_type = false;
				$label_setting = wp_parse_args( $label_setting, array(
					'group'             => '',
					'zone'              => array(),
					'custom_attributes' => array(),
					'class'             => '',
					'desc'              => '',
					'is_service'        => false,
					'is_product'        => false,
					'shipment_type'     => 'simple',
				) );

				if ( ! empty( $label_setting['zone'] ) && ! in_array( $zone, $label_setting['zone'], true ) ) {
					continue;
				}

				if ( $service_identifier === substr( $label_setting['id'], 0, strlen( $service_identifier ) ) || ( true === $label_setting['is_service'] && $shipment_type === $label_setting['shipment_type'] ) ) {
					$label_setting = wp_parse_args( $label_setting, array(
						'service_name' => str_replace( $service_identifier, '', $label_setting['id'] ),
					) );

					$label_setting['is_service']        = true;
					$label_setting['custom_attributes'] = array( 'data-service' => $label_setting['service_name'] );
					$label_setting['class']             .= ' service-check';
					$label_setting['group']             = 'services';

					$override_type = 'services';
				} elseif ( $product_identifier === substr( $label_setting['id'], 0, strlen( $product_identifier ) ) || ( true === $label_setting['is_product'] && $shipment_type === $label_setting['shipment_type'] ) ) {
					if ( empty( $label_setting['zone'] ) ) {
						$maybe_setting_zone = str_replace( $product_identifier . '_', '', $label_setting['id'] );
						$maybe_setting_zone = empty( $maybe_setting_zone ) ? 'dom' : $maybe_setting_zone;

						if ( $zone !== $maybe_setting_zone ) {
							continue;
						}
					}

					foreach( $label_setting['options'] as $product_key => $product_title ) {
						$label_setting['custom_attributes']['data-services-' . $product_key] = implode( ',', $this->get_product_services( $product_key ) );
					}

					$label_setting['desc']  = '';
					$label_setting['group'] = 'product';
					$label_setting['class'] .= ' default-product';
					$label_setting['title'] = _x( 'Default Service', 'shipments', 'woocommerce-germanized-shipments' );
 					$label_setting['is_product'] = true;
					$override_type = 'product';
				} elseif ( $current_label_setting = $this->override_setting_in_packaging( $label_setting, $shipment_type, $zone ) ) {
					$current_label_setting['group'] = 'additional';
					$label_setting = $current_label_setting;
					$override_type = 'additional';
				}

				if ( $override_type ) {
					if ( $configuration_set && $configuration_set->get_setting( $label_setting['id'] ) ) {
						$label_setting['value'] = $configuration_set->get_setting( $label_setting['id'] );
					}

					$label_setting['original_id'] = $label_setting['id'];
					$label_setting['id'] .= "[{$zone}]";
					$has_settings = true;

					$label_settings[ $override_type ][] = $label_setting;
				}
			}
		}

		if ( ! $has_settings ) {
			$label_settings = array();
		}

		return $label_settings;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	public function get_available_label_services( $shipment ) {
		return array();
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	abstract public function get_available_label_products( $shipment );

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 */
	abstract public function get_default_label_product( $shipment );
}
