<?php

/**
 * WCO User Wallet System
 *
 * Provides a virtual Payment Gateway.
 *
 * @class 		WC_Gateway_WCOP
 * @extends		WC_Payment_Gateway
 * @version		1.0
 */
if (class_exists('WC_Payment_Gateway')):

    class WC_Gateway_WCOP extends WC_Payment_Gateway {

        /**
         * Gateway Constructor
         */
        public function __construct() {
            // Setup general properties
            $this->id = 'wcop';
            $this->icon = apply_filters('woocommerce_cod_icon', '');
            $this->method_title = __('WCO User Wallet', 'wc-offers');
            $this->method_description = __('Have your customers pay with wallet.', 'wc-offers');
            $this->has_fields = false;
            $this->supports = array(
                'products',
                'refunds'
            );
            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Get settings
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->instructions = $this->get_option('instructions', $this->description);
            $this->enable_for_methods = $this->get_option('enable_for_methods', array());
            $this->enable_for_virtual = $this->get_option('enable_for_virtual', 'yes') === 'yes' ? true : false;

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_thankyou_wpvw', array($this, 'thankyou_page'));
            add_action('woocommerce_before_checkout_process', array($this, 'check_balance'));

            // Customer Emails
            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        }

        /**
         * @since 1.0
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields() {
            $shipping_methods = array();

            foreach (WC()->shipping()->load_shipping_methods() as $method) {
                $shipping_methods[$method->id] = $method->get_method_title();
            }

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable WCO User Wallet', 'wc-offers'),
                    'label' => __('Enable WCO User Wallet', 'wc-offers'),
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __('Title', 'wc-offers'),
                    'type' => 'text',
                    'description' => __('Payment method description that the customer will see on your checkout.', 'wc-offers'),
                    'default' => __('WCO User Wallet', 'wc-offers'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Description', 'wc-offers'),
                    'type' => 'textarea',
                    'description' => __('Payment method description that the customer will see on your website.', 'wc-offers'),
                    'default' => __('Pay using your Wallet.', 'wc-offers'),
                    'desc_tip' => true,
                ),
                'instructions' => array(
                    'title' => __('Instructions', 'wc-offers'),
                    'type' => 'textarea',
                    'description' => __('Instructions that will be added to the thank you page.', 'wc-offers'),
                    'default' => __('Pay using your Wallet', 'wc-offers'),
                    'desc_tip' => true,
                ),
                'enable_for_methods' => array(
                    'title' => __('Enable for shipping methods', 'wc-offers'),
                    'type' => 'multiselect',
                    'class' => 'chosen_select',
                    'css' => 'width: 450px;',
                    'default' => '',
                    'description' => __('If User Wallet is only available for certain shipping methods, set it up here. Leave blank to enable for all methods.', 'wc-offers'),
                    'options' => $shipping_methods,
                    'desc_tip' => true,
                    'custom_attributes' => array(
                        'data-placeholder' => __('Select shipping methods', 'wc-offers')
                    )
                )
            );
        }

        /**
         * @since 1.0
         * @return boolean
         * Check Wallet payment gateway is available for not
         */
        public function is_available() {
            if (!session_id()) {
                session_start();
            }

            if ($this->enabled == 'no') {
                return false;
            }

            if (isset($_SESSION['enable_wallet_discount']) && $_SESSION['enable_wallet_discount'] == 'enable') {
                return false;
            } else {
                return true;
            }
        }

        /**
         * @since 1.0
         * @param int $order_id
         * @return array
         * Process the payment and return the result.
         */
        public function process_payment($order_id) {

            //Get order information
            $order = wc_get_order($order_id);
            $user_id = $order->user_id;

            //Get user wallet balance
            $vw_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));
            $cart_total = floatval(WC()->cart->total);

            //Check user cart total with wallet balance
            if ($cart_total > $vw_balance) {
                wc_add_notice(__('<strong>Payment error:</strong>', 'wc-offers') . ' Insufficient funds. Please purchase more credits or use a different payment method.', 'error');

                return;
            }

            //Deduct balance from wallet
            $new_user_vw_balance = $vw_balance - $cart_total;
            update_user_meta($user_id, 'wco_wl_balance', $new_user_vw_balance);

            //reducdency check
            if (get_user_meta($user_id, 'wco_wl_balance', true) != $new_user_vw_balance) {
                wc_add_notice(__('<strong>System error:</strong>', 'wc-offers') . ' There was an error procesing the payment. Please try another payment method.', 'error');

                return;
            }

            // Mark as processing or on-hold (payment won't be taken until delivery)
            $order->update_status(apply_filters('woocommerce_cod_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order), __('Virtual payment method.', 'wc-offers'));

            /** reduce stock levels */
            $order->reduce_order_stock();

            /** empty the cart */
            WC()->cart->empty_cart();

            /** send to the thankyou page */
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

        /**
         * @since 1.0
         * @param  WC_Order $order
         * @return bool
         * Can the order be refunded via PayPal?
         */
        public function can_refund_order($order) {
            return $order;
        }

        /**
         * @since 1.0
         * @param  int    $order_id
         * @param  float  $amount
         * @param  string $reason
         * @return bool|WP_Error
         *  Process a refund if supported.
         */
        public function process_refund($order_id, $amount = null, $reason = '') {
            $order = wc_get_order($order_id);
            $user_id = $order->user_id;

            if (!$this->can_refund_order($order)) {
                return new WP_Error('error', __('Refund failed: No order ID', 'woocommerce'));
            }

            //Get user wallet balance
            $vw_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));

            $new_user_vw_balance = $vw_balance + $amount;
            update_user_meta($user_id, 'wco_wl_balance', $new_user_vw_balance);

            return true;
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * @return string for checkout page
         */
        public function get_icon() {

            global $woocommerce;
            $vw_balance = wc_price(get_user_meta(get_current_user_id(), 'wco_wl_balance', true));
            return apply_filters('woocommerce_gateway_icon', ' | Your Current Balance: <strong>' . $vw_balance . '</strong>', $this->id);
        }

        /**
         * Output for the order received page.
         */
        public function thankyou_page() {
            if ($this->instructions) {
                echo wpautop(wptexturize($this->instructions));
            }
        }

        /**
         * @since 1.0
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         * Add content to the WC emails.
         */
        public function email_instructions($order, $sent_to_admin, $plain_text = false) {
            if ($this->instructions && !$sent_to_admin && 'vw' === $order->payment_method) {
                echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
            }
        }

    }



endif;
