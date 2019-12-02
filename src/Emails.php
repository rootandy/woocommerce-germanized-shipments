<?php

namespace Vendidero\Germanized\Shipments;

defined( 'ABSPATH' ) || exit;

class Emails {

    public static function init() {
        add_filter( 'woocommerce_email_classes', array( __CLASS__, 'register_emails' ), 10 );
        add_filter( 'woocommerce_email_actions', array( __CLASS__, 'register_email_notifications' ), 10, 1 );

        add_action( 'init', array( __CLASS__, 'email_hooks' ), 10 );

	    // Change email template path if is germanized email template
	    add_filter( 'woocommerce_template_directory', array( __CLASS__, 'set_woocommerce_template_dir' ), 10, 2 );
    }

	public static function set_woocommerce_template_dir( $dir, $template ) {
		if ( file_exists( Package::get_path() . '/templates/' . $template ) ) {
			return 'woocommerce-germanized';
		}

		return $dir;
	}

    public static function register_emails( $emails ) {
        $emails['WC_GZD_Email_Customer_Shipment']        = include Package::get_path() . '/includes/emails/class-wc-gzd-email-customer-shipment.php';
	    $emails['WC_GZD_Email_Customer_Return_Shipment'] = include Package::get_path() . '/includes/emails/class-wc-gzd-email-customer-return-shipment.php';

        return $emails;
    }

    public static function email_hooks() {
	    add_action( 'woocommerce_gzd_email_shipment_details', array( __CLASS__, 'email_return_instructions' ), 5, 4 );
	    add_action( 'woocommerce_gzd_email_shipment_details', array( __CLASS__, 'email_tracking' ), 10, 4 );
        add_action( 'woocommerce_gzd_email_shipment_details', array( __CLASS__, 'email_address' ), 20, 4 );
        add_action( 'woocommerce_gzd_email_shipment_details', array( __CLASS__, 'email_details' ), 30, 4 );
    }

    public static function register_email_notifications( $actions ) {

        $actions = array_merge( $actions, array(
            'woocommerce_gzd_shipment_status_draft_to_processing',
            'woocommerce_gzd_shipment_status_draft_to_shipped',
            'woocommerce_gzd_shipment_status_draft_to_delivered',
            'woocommerce_gzd_shipment_status_draft_to_returned',
            'woocommerce_gzd_shipment_status_processing_to_shipped',
            'woocommerce_gzd_shipment_status_processing_to_delivered',
            'woocommerce_gzd_shipment_status_processing_to_returned',
            'woocommerce_gzd_shipment_status_shipped_to_delivered',
            'woocommerce_gzd_shipment_status_shipped_to_returned',
            'woocommerce_gzd_shipment_status_delivered_to_returned',
            'woocommerce_gzd_shipment_status_returned_to_processing',
	        'woocommerce_gzd_return_shipment_status_draft_to_processing',
	        'woocommerce_gzd_return_shipment_status_draft_to_shipped',
	        'woocommerce_gzd_return_shipment_status_draft_to_delivered',
	        'woocommerce_gzd_return_shipment_status_draft_to_requested',
	        'woocommerce_gzd_return_shipment_status_processing_to_shipped',
	        'woocommerce_gzd_return_shipment_status_processing_to_delivered',
	        'woocommerce_gzd_return_shipment_status_shipped_to_delivered',
	        'woocommerce_gzd_return_shipment_status_requested_to_processing',
	        'woocommerce_gzd_return_shipment_status_requested_to_shipped',
        ) );

        return $actions;
    }

	/**
	 * @param Shipment $shipment
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 * @param string $email
	 */
    public static function email_return_instructions( $shipment, $sent_to_admin = false, $plain_text = false, $email = '' ) {

    	if ( 'return' !== $shipment->get_type() ) {
    		return;
	    }

	    if ( $plain_text ) {
		    wc_get_template(
			    'emails/plain/email-return-shipment-instructions.php', array(
				    'shipment'      => $shipment,
				    'sent_to_admin' => $sent_to_admin,
				    'plain_text'    => $plain_text,
				    'email'         => $email,
			    )
		    );
	    } else {
		    wc_get_template(
			    'emails/email-return-shipment-instructions.php', array(
				    'shipment'      => $shipment,
				    'sent_to_admin' => $sent_to_admin,
				    'plain_text'    => $plain_text,
				    'email'         => $email,
			    )
		    );
	    }
    }

	/**
	 * @param Shipment $shipment
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 * @param string $email
	 */
	public static function email_tracking( $shipment, $sent_to_admin = false, $plain_text = false, $email = '' ) {

		// Do only include shipment tracking if estimated delivery date or tracking instruction or tracking url exists
		if ( ! $shipment->has_tracking() ) {
			return;
		}

		if ( $plain_text ) {
			wc_get_template(
				'emails/plain/email-shipment-tracking.php', array(
					'shipment'      => $shipment,
					'sent_to_admin' => $sent_to_admin,
					'plain_text'    => $plain_text,
					'email'         => $email,
				)
			);
		} else {
			wc_get_template(
				'emails/email-shipment-tracking.php', array(
					'shipment'      => $shipment,
					'sent_to_admin' => $sent_to_admin,
					'plain_text'    => $plain_text,
					'email'         => $email,
				)
			);
		}
	}

	/**
	 * @param Shipment $shipment
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 * @param string $email
	 */
    public static function email_address( $shipment, $sent_to_admin = false, $plain_text = false, $email = '' ) {

		if ( 'return' === $shipment->get_type() ) {
			if ( $provider = $shipment->get_shipping_provider_instance() ) {
				if ( $provider->hide_return_address() ) {
					return;
				}
			}
		}

        if ( $plain_text ) {
            wc_get_template(
                'emails/plain/email-shipment-address.php', array(
                    'shipment'      => $shipment,
                    'sent_to_admin' => $sent_to_admin,
                    'plain_text'    => $plain_text,
                    'email'         => $email,
                )
            );
        } else {
            wc_get_template(
                'emails/email-shipment-address.php', array(
                    'shipment'      => $shipment,
                    'sent_to_admin' => $sent_to_admin,
                    'plain_text'    => $plain_text,
                    'email'         => $email,
                )
            );
        }
    }

    /**
     * Show the order details table
     *
     * @param WC_Order $order         Order instance.
     * @param bool     $sent_to_admin If should sent to admin.
     * @param bool     $plain_text    If is plain text email.
     * @param string   $email         Email address.
     */
    public static function email_details( $shipment, $sent_to_admin = false, $plain_text = false, $email = '' ) {
        if ( $plain_text ) {
            wc_get_template(
                'emails/plain/email-shipment-details.php', array(
                    'shipment'      => $shipment,
                    'sent_to_admin' => $sent_to_admin,
                    'plain_text'    => $plain_text,
                    'email'         => $email,
                )
            );
        } else {
            wc_get_template(
                'emails/email-shipment-details.php', array(
                    'shipment'      => $shipment,
                    'sent_to_admin' => $sent_to_admin,
                    'plain_text'    => $plain_text,
                    'email'         => $email,
                )
            );
        }
    }
}