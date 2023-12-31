<?php
/**
 * 2Checkout payment gateway class.
 *
 * @author   ThimPress
 * @package  LearnPress/2Checkout/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Gateway_2Checkout' ) ) {
	/**
	 * Class LP_Gateway_2Checkout.
	 */
	class LP_Gateway_2Checkout extends LP_Gateway_Abstract {

		/**
		 * @var bool
		 */
		public static $_getscript_loaded = false;

		public $order;

		/**
		 * LP_Gateway_2Checkout constructor.
		 */
		public function __construct() {
			$this->id = '2checkout';

			$this->method_title       = '2Checkout';
			$this->method_description = __( 'Make a payment with 2Checkout payment methods.', 'learnpress-2checkout-payment' );
			$this->icon               = apply_filters( 'learn_press/2checkout-icon', '' );
			add_action( 'get_header', array( $this, 'show_2checkout_payment_form' ) );
			$this->title       = '2Checkout';
			$this->description = __( 'Make a payment with 2Checkout payment methods.', 'learnpress-2checkout-payment' );

			//add_action( '2checkout_checkout_order_processed', array( $this, 'checkout_order_processed' ), 10, 2 );

			// check payment gateway enable
			add_filter(
				'learn-press/payment-gateway/' . $this->id . '/available',
				array(
					$this,
					'gateway_available',
				),
				10,
				2
			);

			add_action( 'learn_press_web_hook_learn_press_2checkout', array( $this, 'web_hook_process_2checkout' ) );
			if ( did_action( 'init' ) ) {
				$this->register_web_hook();
			} else {
				add_action( 'init', array( $this, 'register_web_hook' ) );
			}

			parent::__construct();
		}

		/**
		 * Check gateway available.
		 *
		 * @return bool
		 */
		public function gateway_available() {
			if ( LP_Settings::instance()->get( "{$this->id}.enable" ) != 'yes' ) {
				return false;
			}

			return true;
		}

		/**
		 * Admin payment settings.
		 *
		 * @return array
		 */
		public function get_settings() {
			return apply_filters(
				'learn-press/gateway-payment/2checkout/settings',
				array(
					array(
						'type' => 'title',
					),
					array(
						'title'   => __( 'Enable', 'learnpress-2checkout-payment' ),
						'id'      => '[enable]',
						'default' => 'no',
						'type'    => 'yes-no',
					),
					array(
						'title'      => __( 'Account Number', 'learnpress-2checkout-payment' ),
						'id'         => '[sid]',
						'default'    => '',
						'type'       => 'text',
						'desc'       => __( '2Checkout account number', 'learnpress-2checkout-payment' ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => '[enable]',
									'compare' => '=',
									'value'   => 'yes',
								),
							),
						),
					),
					array(
						'title'      => __( 'Secret Word', 'learnpress-2checkout-payment' ),
						'id'         => '[secret_word]',
						'default'    => '',
						'type'       => 'text',
						'desc'       => __( 'Secret word is set up under the Site Management page in the seller area on 2Checkout site.', 'learnpress-2checkout-payment' ),
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => '[enable]',
									'compare' => '=',
									'value'   => 'yes',
								),
							),
						),
					),
					array(
						'title'      => __( 'Test mode', 'learnpress-2checkout-payment' ),
						'id'         => '[test_mode]',
						'default'    => 'no',
						'type'       => 'yes-no',
						'visibility' => array(
							'state'       => 'show',
							'conditional' => array(
								array(
									'field'   => '[enable]',
									'compare' => '=',
									'value'   => 'yes',
								),
							),
						),
					),
					array(
						'type' => 'sectionend',
					),
				)
			);
		}


		/**
		 * Payment form.
		 */
		public function get_payment_form() {
			return '';
		}

		/**
		 * Generate form for submit data to 2checkout.
		 */
		public function show_2checkout_payment_form() {
			global $wp;
			if ( ! empty( $wp->query_vars['lp-order-received'] ) && isset( $_REQUEST['payment'] ) && isset( $_REQUEST['payment_atc'] ) && $_REQUEST['payment'] == '2checkout' && $_REQUEST['payment_atc'] == 'showform' ) {
				$order_id  = absint( $wp->query_vars['lp-order-received'] );
				$order_key = LP_Helper::sanitize_params_submitted( $_GET['key'] ?? '' );
				$order     = learn_press_get_order( $order_id );
				if ( $order ) {
					if ( $order->get_order_key() === $order_key ) {
						$return_url = $this->get_return_url( $order );
						learn_press_get_template(
							'form.php',
							array(
								'order'      => $order,
								'return_url' => $return_url,
							),
							learn_press_template_path() . '/addons/2checkout-payment/',
							LP_ADDON_2CHECKOUT_PAYMENT_TEMPLATE
						);
						exit();
					}
				}
			}
		}

		/**
		 * Register web hook.
		 */
		public function register_web_hook() {
			learn_press_register_web_hook( '2checkout', 'learn_press_2checkout' );
		}

		/**
		 * Web hook.
		 *
		 * @param $request
		 */
		public function web_hook_process_2checkout( $request ) {
			if ( ! $this->validate_2checkout_ins() ) {
				return;
			}
			$vendor_order_id = $request['vendor_order_id'] ?? '';
			$invoice_status  = $request['invoice_status'] ?? '';
			$t               = explode( '-', $vendor_order_id );
			$order_id        = $t[0];
			$order_key       = $t[1];
			$order           = learn_press_get_order( $order_id );
			if ( ! $order || $order->get_order_key() !== $order_key ) {
				return;
			}
			$method   = 'process_payment_ins_' . $invoice_status;
			$callback = array( $this, $method );
			if ( is_callable( $callback ) ) {
				call_user_func( $callback, $order, $_POST );
			}
		}

		/**
		 * Validate 2checkout ins.
		 *
		 * @return bool
		 */
		public function validate_2checkout_ins() {
			$twocheckout_settings = LearnPress::instance()->settings->get( 'learn_press_2checkout' );

			$hashSecretWord = isset( $twocheckout_settings['secret_word'] ) && $twocheckout_settings['secret_word'] ? $twocheckout_settings['secret_word'] : '';
			$hashSid        = isset( $twocheckout_settings['sid'] ) && $twocheckout_settings['sid'] ? $twocheckout_settings['sid'] : '';
			$message_types  = array( 'ORDER_CREATED', 'INVOICE_STATUS_CHANGED' );
			if ( in_array( $_POST['message_type'], $message_types ) && $hashSecretWord && $hashSid ) {

				$insMessage = array();
				foreach ( $_POST as $k => $v ) {
					$insMessage[ $k ] = $v;
				}

				# Validate the Hash
				$hashOrder    = $insMessage['sale_id'];
				$hashInvoice  = $insMessage['invoice_id'];
				$StringToHash = strtoupper( md5( $hashOrder . $hashSid . $hashInvoice . $hashSecretWord ) );

				if ( $StringToHash != $insMessage['md5_hash'] ) {
					return false;
				}

				return true;
			}

			return false;
		}

		/**
		 * Process payment ins deposited.
		 *
		 * @param $order LP_Order
		 * @param $request
		 */
		public function process_payment_ins_deposited( $order, $request ) {
			// order status is already completed
			if ( $order->has_status( 'completed' ) ) {
				exit;
			}

			if ( 'deposited' === $request['invoice_status'] ) {
				$sale_id = $request['sale_id'];
				$order->payment_complete( $sale_id );
			}
		}

		/**
		 * @param $order_id
		 * @param $posted
		 */
		public function checkout_order_processed( $order_id, $posted ) {
			$lp_order_id = LearnPress::instance()->session->get( 'order_awaiting_payment' );
			if ( $lp_order_id ) {
				// map LP order key with WC order key
				$map_keys = array(
					'_order_currency'       => '_order_currency',
					'_user_id'              => '_customer_user',
					'_order_subtotal'       => '_order_total',
					'_order_total'          => '_order_total',
					'_payment_method_id'    => '_payment_method',
					'_payment_method_title' => '_payment_method_title',
				);

				foreach ( $map_keys as $k => $v ) {
					update_post_meta( $lp_order_id, $k, get_post_meta( $order_id, $v, true ) );
				}
				update_post_meta( $order_id, '_learn_press_order_id', $lp_order_id );
			}
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param $order_id
		 *
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$this->order = learn_press_get_order( $order_id );
			$return_url  = $this->get_return_url( $this->order ) . '&payment=2checkout&payment_atc=showform';

			$json = array(
				'result'   => 'success',
				'redirect' => $return_url,
			);

			return $json;
		}

		/**
		 * Order completed.
		 */
		/*public function order_complete() {
			if ( $this->order->status == 'completed' ) {
				return;
			}
			$this->order->payment_complete();
			LearnPress::instance()->cart->empty_cart();

			$this->order->add_note( sprintf( "%s payment completed with Transaction Id of '%s'", $this->title, $this->charge->id ) );

			LearnPress::instance()->session->set( 'order_awaiting_payment', null );
		}*/
	}
}
