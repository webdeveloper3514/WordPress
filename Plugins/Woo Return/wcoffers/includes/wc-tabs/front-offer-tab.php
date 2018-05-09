<?php

/**
 * Create offer tab in my-account page & related functionality
 */
class Offers_My_Account_Endpoint {

    /**
     * Custom endpoint name.
     */
    public static $endpoint = 'offers';
    public static $makeapayment = 'add-deposit';
    public static $deposithistory = 'deposit-history';

    /**
     * Plugin actions.
     */
    public function __construct() {
        // Actions used to insert a new endpoint in the WordPress.
        add_action('init', array($this, 'wcoffers_add_endpoints'));
        add_filter('query_vars', array($this, 'wcoffers_add_query_vars'), 10);
        add_filter('woocommerce_get_query_vars', array($this, 'wcoffers_woocommerce_add_query_vars'), 10);

        // Change the My Accout page title.
        add_filter('the_title', array($this, 'wcoffers_endpoint_title'));

        // Insering your new tab/page into the My Account page.
        add_filter('woocommerce_account_menu_items', array($this, 'wcoffers_new_menu_items'));
        add_action('woocommerce_account_' . self::$endpoint . '_endpoint', array($this, 'wcoffers_endpoint_content'));
        add_action('woocommerce_account_' . self::$makeapayment . '_endpoint', array($this, 'wcoffers_adddeposit_endpoint_content'));
        add_action('woocommerce_account_' . self::$deposithistory . '_endpoint', array($this, 'wcoffers_deposit_transaction_history'));

        //Add style for front page
        add_action('wp_enqueue_scripts', array($this, 'wcoffers_add_script_style'));

        //Add payment methods
        add_filter('woocommerce_available_payment_gateways', array($this, 'wcoffers_deposit_available_payment_method'), 30);

        //Load checkout js
        add_filter('woocommerce_is_checkout', array($this, 'wcoffers_load_checkout_script'), 10);

        //Checkout deposit form
        add_action('woocommerce_before_checkout_process', array($this, 'wcoffers_deposit_checkout_amount'));

        //Create product for deposit
        add_action('wp', array($this, 'wcoffers_create_deposite_product'), 15);

        //Add virtual amount
        add_action('woocommerce_order_status_changed', array($this, 'wcoffers_add_user_virtual_money'), 11, 3);

        //Add admin menu for order
        if (is_admin()) {
            add_filter('views_edit-shop_order', array($this, 'wcoffers_add_order_deposit_view'));
            add_action('pre_get_posts', array($this, 'wcoffers_filter_order_deposit_for_view'));
        }
        //Add body class
        add_filter('body_class', array($this, 'wcoffers_add_deposit_body_classes'));
    }

    /**
     * @since 1.0
     * Enqueue script and style for my account page
     */
    public function wcoffers_add_script_style() {
        wp_enqueue_style('wcoffers_my_account_css', plugins_url() . '/wcoffers/assets/css/front.css', false, '1.0', 'all');
    }

    /**
     * @since 1.0
     * Register new endpoint to use inside My Account page.
     */
    public function wcoffers_add_endpoints() {
        add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);
        add_rewrite_endpoint(self::$makeapayment, EP_ROOT | EP_PAGES);
        add_rewrite_endpoint(self::$deposithistory, EP_ROOT | EP_PAGES);
    }

    /**
     * @since 1.0
     * @param array $vars
     * @return array
     * Add new query var.
     */
    public function wcoffers_add_query_vars($vars) {
        $vars[] = self::$endpoint;
        $vars[] = self::$makeapayment;
        $vars[] = self::$deposithistory;

        return $vars;
    }

    /**
     * @since 1.0
     * @param array $vars
     * @return array
     * Add WooCommerce query vars
     */
    public function wcoffers_woocommerce_add_query_vars($vars) {
        $vars[self::$endpoint] = self::$endpoint;
        $vars[self::$makeapayment] = self::$makeapayment;
        $vars[self::$deposithistory] = self::$deposithistory;

        return $vars;
    }

    /**
     * @since 1.0
     * @param string $title
     * @return string
     * Set my account page endpoint title
     */
    public function wcoffers_endpoint_title($title) {
        global $wp_query;

        //offer tab
        $is_endpoint = isset($wp_query->query_vars[self::$endpoint]);
        if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            $title = __('Offers', 'wc-offers');
            remove_filter('the_title', array($this, 'wcoffers_endpoint_title'));
        }

        // Add deposit tab page
        $is_add_moneyendpoint = isset($wp_query->query_vars[self::$makeapayment]);
        if ($is_add_moneyendpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            $title = __('Add Deposit', 'wc-offers');
            remove_filter('the_title', array($this, 'wcoffers_endpoint_title'));
        }

        // Deposit History page
        $is_add_deposithistory = isset($wp_query->query_vars[self::$deposithistory]);
        if ($is_add_deposithistory && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            $title = __('Deposit Transaction History', 'wc-offers');
            remove_filter('the_title', array($this, 'wcoffers_endpoint_title'));
        }

        return $title;
    }

    /**
     * @since 1.0
     * @param bool $is_checkout
     * @return boolean
     * Make add deposit page as checkout page
     */
    public function wcoffers_load_checkout_script($is_checkout) {
        $current_endpoint = WC()->query->get_current_endpoint();
        if ($current_endpoint == 'add-deposit') {
            return true;
        }
        return $is_checkout;
    }

    /**
     * @since 1.0
     * @param string $classes
     * @return string
     * Add account page class in body tag
     */
    public function wcoffers_add_deposit_body_classes($classes) {
        $current_endpoint = WC()->query->get_current_endpoint();
        if ($current_endpoint == 'add-deposit') {
            $classes[] = 'woocommerce-account';
        }
        return $classes;
    }

    /**
     * @since 1.0
     * Create product for deposit amount
     */
    public function wcoffers_create_deposite_product() {
        $deposit_product_id = get_option('wcoffers_deposite_product_id', 0);
        $get_product = wc_get_product($deposit_product_id);
        if ($deposit_product_id == 0 || !$get_product) {
            $product_id = wp_insert_post(
                    array(
                        'post_title' => __('Wallet Balance', 'wc-offers'),
                        'post_type' => 'product',
                        'post_status' => 'private',
                        'post_content' => __('Do not remove this product, this product is cretaed by WooCommerce Offers plugin', 'wc-offers')
                    )
            );
            $catalog = version_compare(WC()->version, '2.7.0', '>=') ? 'catalog_visibility' : '_visibility';
            update_post_meta($product_id, '_sold_individually', 'yes');
            update_post_meta($product_id, $catalog, 'hidden');
            update_post_meta($product_id, '_virtual', 'yes');
            update_post_meta($product_id, '_downloadable', 'yes');
            //Update db field
            update_option('wcoffers_deposite_product_id', $product_id);
        }
    }

    /**
     * @since 1.0
     * @throws Exception
     * Checkout deposit amount
     */
    public function wcoffers_deposit_checkout_amount() {
        if (!isset($_POST['deposit_amount'])) {
            return;
        }
        $post_arr = array();
        $post_arr['deposit_amount'] = $this->wcoffers_validate_deposit_amount();
        $post_arr['payment_method'] = isset($_POST['payment_method']) ? stripslashes($_POST['payment_method']) : '';

        $available_payment_mathods = WC()->payment_gateways()->get_available_payment_gateways();
        WC()->session->set('chosen_payment_method', $_POST['payment_method']);
        if (!isset($available_payment_mathods[$_POST['payment_method']])) {
            $payment_method = '';
            wc_add_notice(__('Invalid payment method.', 'wc-offers'), 'error');
        } else {
            $payment_method = $available_payment_mathods[$_POST['payment_method']];
            $payment_method->validate_fields();
        }
        $post_arr['payment_method'] = $payment_method;
        if (wc_notice_count('error') > 0) {
            throw new Exception();
        }
        //Create order for amount
        $order_id = $this->wcoffers_create_order($post_arr);

        if (is_wp_error($order_id)) {
            throw new Exception($order_id->get_error_message());
        }

        //Assign to wc session for reuse
        WC()->session->order_deposit_awaiting_payment = $order_id;

        // Process Payment
        $result = $post_arr['payment_method']->process_payment($order_id);

        // Payment Page
        if (isset($result['result']) && 'success' === $result['result']) {

            $result = apply_filters('woocommerce_payment_successful_result', $result, $order_id);

            if (is_ajax()) {
                wp_send_json($result);
            } else {
                wp_redirect($result['redirect']);
                exit;
            }
        }
        if (is_ajax()) {

            if (!isset(WC()->session->reload_checkout)) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }

            $response = array(
                'result' => 'failure',
                'messages' => isset($messages) ? $messages : '',
                'refresh' => isset(WC()->session->refresh_totals) ? 'true' : 'false',
                'reload' => isset(WC()->session->reload_checkout) ? 'true' : 'false'
            );

            unset(WC()->session->refresh_totals, WC()->session->reload_checkout);

            wp_send_json($response);
        }
    }

    /**
     * @since 1.0
     * @param array $post_arr
     * @return \WP_Error
     * @throws Exception
     * Create order for deposit amount
     */
    public function wcoffers_create_order($post_arr) {
        try {

            wc_transaction_query('start');
            $customer_id = apply_filters('woocommerce_checkout_customer_id', get_current_user_id());

            $order_data = array(
                'status' => apply_filters('woocommerce_default_order_status', 'pending'),
                'customer_id' => $customer_id,
                'customer_note' => '',
                'created_via' => 'checkout'
            );

            $order_id = absint(WC()->session->order_deposit_awaiting_payment);

            // Resume the unpaid order if its pending
            if ($order_id > 0 && ( $order = wc_get_order($order_id) ) && $order->has_status(array('pending', 'failed'))) {

                $order_data['order_id'] = $order_id;
                $order = wc_update_order($order_data);

                if (is_wp_error($order)) {
                    throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wc-offers'), 522));
                } else {
                    $order->remove_order_items();
                    do_action('woocommerce_resume_order', $order_id);
                }
            } else {

                $order = wc_create_order($order_data);

                if (is_wp_error($order)) {
                    throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wc-offers'), 520));
                } elseif (false === $order) {
                    throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wc-offers'), 521));
                } else {
                    $order_id = $order->get_order_number();
                    do_action('woocommerce_new_order', $order_id);
                }
            }

            // Add the product
            $deposit_product_id = get_option('wcoffers_deposite_product_id', 0);

            $product = wc_get_product($deposit_product_id);

            //Assign product to order
            $item_id = $this->wcoffers_add_deposit_product($order, $product, $post_arr['deposit_amount']);

            if (!$item_id) {
                throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'wc-offers'), 499));
            }

            // Allow plugins to add order item meta
            do_action('wcoffers_add_deposit_order_item_meta', $item_id, array('data' => $product, 'amount' => $post_arr['deposit_amount']));

            //Set order details
            $order->set_payment_method($post_arr['payment_method']);
            $order->set_total($post_arr['deposit_amount']);
            if (version_compare(WC()->version, '2.7.0', '>=')) {
                update_post_meta($order_id, 'order_tax', 0);
                update_post_meta($order_id, 'order_shipping_tax', 0);
                update_post_meta($order_id, 'order_shipping', 0);
                update_post_meta($order_id, 'wcoffers_order_has_deposit', 'yes');
                update_post_meta($order_id, 'wcoffers_order_deposit_amount', $post_arr['deposit_amount']);
                $order->save();
            } else {
                update_post_meta($order_id, '_order_tax', 0);
                update_post_meta($order_id, '_order_shipping_tax', 0);
                update_post_meta($order_id, '_order_shipping', 0);
                update_post_meta($order_id, 'wcoffers_order_has_deposit', 'yes');
                update_post_meta($order_id, 'wcoffers_order_deposit_amount', $post_arr['deposit_amount']);
            }

            wc_transaction_query('commit');
        } catch (Exception $e) {
            wc_transaction_query('rollback');
            return new WP_Error('checkout-error', $e->getMessage());
        }
        return $order_id;
    }

    /**
     * @since 1.0
     * @param obj $order
     * @param obj $product
     * @param int $amount
     * @return integer
     * Add subtotal,tax and total amount in order
     */
    public function wcoffers_add_deposit_product($order, $product, $amount) {
        if (version_compare(WC()->version, '2.7.0', '>=')) {
            $item_id = $order->add_product($product, 1, array('subtotal' => $amount, 'subtotal_tax' => 0, 'total' => $amount, 'tax' => 0,));
        } else {
            $item_id = $order->add_product($product, 1, array('totals' => array('subtotal' => $amount, 'subtotal_tax' => 0, 'total' => $amount, 'tax' => 0)));
        }
        return $item_id;
    }

    /**
     * @since 1.0
     * @return int
     * Validate deposit amount
     */
    public function wcoffers_validate_deposit_amount() {
        $amount = wc_format_decimal($_REQUEST['deposit_amount']);
        if ($amount == '' || !is_numeric($amount)) {
            wc_add_notice(__('Enter deposit amount', 'wc-offers'), 'error');
            return false;
        }
        $amount = floatval($amount);
        $min_amount = get_option('wcoffer_deposit_min_amount', 0);
        $max_amount = get_option('wcoffer_deposit_max_amount', 0);
        if ($min_amount != '' && $amount < floatval($min_amount)) {
            $str = __('Please enter minimum deposit amount ', 'wc-offers') . wc_price($min_amount);
            wc_add_notice($str, 'error');
            return false;
        }
        if ($max_amount != '' && $amount > floatval($max_amount)) {
            $str = __('Please enter less than minimum deposit amount ', 'wc-offers') . wc_price($max_amount);
            wc_add_notice($str, 'error');
            return false;
        }
        return $amount;
    }

    /**
     * @since 1.0
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     * Add virtual amount when order status is completed
     */
    public function wcoffers_add_user_virtual_money($order_id, $old_status, $new_status) {
        $order = wc_get_order($order_id);
        if (get_post_meta($order_id, 'wcoffers_order_has_deposit', TRUE) == 'yes') {
            if ($new_status == 'completed' && get_post_meta($order_id, 'wcoffers_order_amount_deposited', TRUE) != 'yes') {
                $order_amount = $order->get_total();
                $user_id = $order->get_user_id();
                $order_note = sprintf(__('Added %s funds to customer #%s account', 'wc-offers'), wc_price($order_amount), $user_id);
                $order->add_order_note($order_note);
                $deposit_amount = get_user_meta($user_id, 'wco_wl_balance', TRUE);
                $deposit_amount = $deposit_amount + $order_amount;
                update_user_meta($user_id, 'wco_wl_balance', $deposit_amount);
                update_post_meta($order_id, 'wcoffers_order_amount_deposited', 'yes');
            }
        }
    }

    /**
     * @since 1.0
     * @global obj $wpdb
     * @param array $views
     * @return array
     * Add menu at order listing page in admin
     */
    public function wcoffers_add_order_deposit_view($views) {
        global $wpdb;
        $args = array(
            'meta_key' => 'wcoffers_order_has_deposit',
            'meta_value' => 'yes',
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'posts_per_page' => -1
        );
        $posts = new WP_Query($args);
        $count_deposit_order = $posts->post_count;
        wp_reset_postdata();
        if ($count_deposit_order > 0) {
            $url = esc_url(add_query_arg(array('post_type' => 'shop_order', 'wco_deposit_order' => true), admin_url('edit.php')));
            $current = isset($_GET['wco_deposit_order']) ? 'current' : '';
            $views['wco_deposit_order'] = '<a href="' . $url . '" class="' . $current . '">' . __('Deposit Orders', 'wc-offers') . ' <span class="count">(' . $count_deposit_order . ')</span></a>';
        }
        return $views;
    }

    /**
     * @since 1.0
     * @param obj $query
     * @return obj
     * Filter the deposit order
     */
    public function wcoffers_filter_order_deposit_for_view($query) {
        if (isset($_GET['wco_deposit_order']) && $_GET['wco_deposit_order'] == 1) {
            $query->set('meta_key', 'wcoffers_order_has_deposit');
            $query->set('meta_value', 'yes');
        }
        return $query;
    }

    /**
     * @since 1.0
     * @param array $items
     * @return array
     * Insert the new endpoint into the My Account menu.
     */
    public function wcoffers_new_menu_items($items) {
        // Remove the logout menu item.
        $logout = $items['customer-logout'];
        unset($items['customer-logout']);

        // Insert your custom endpoint.
        $items[self::$endpoint] = __('Offers', 'wc-offers');
        $items[self::$makeapayment] = __('Add Deposit', 'wc-offers');
        $items[self::$deposithistory] = __('Deposit History', 'wc-offers');

        // Insert back the logout item.
        $items['customer-logout'] = $logout;

        return $items;
    }

    /**
     * @since 1.0
     * Endpoint HTML content.
     */
    public function wcoffers_endpoint_content() {
        $user_id = get_current_user_id();
        $orders = get_posts(array(
            'post_type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
        ));
        $offer_available = FALSE;
        $currentDate = date('Y-m-d');
        $calculate_discount = new WCO_Email_Templates();
        //Registration Offer
        if (count($orders) == 0) {
            $reg_offer = get_option('disable_new_reg', '1');
            $current_user_allow_offer = '';
            $offer_exp_date = get_user_meta($user_id, 'wco_new_registration_expire_date', TRUE);
            if ($reg_offer == 'disable_keep_existing') {
                if ($offer_exp_date && ( $offer_exp_date == 'unlimited' || strtotime($offer_exp_date) > strtotime($currentDate) )) {
                    $current_user_allow_offer = 'allow';
                }
            }
            if ($reg_offer == 1 || $current_user_allow_offer == 'allow') {
                $offer_available = TRUE;
                ?>
                <div class="offer-wrapper">
                    <div class="offer-heading"><?php _e('Registration offer for you', 'wc-offers'); ?></div>
                    <div class="offer-content">
                        <?php
                        $offer_discount_type = get_option('offer_discount_type', 'percent');
                        if ($offer_discount_type == 'percent') {
                            $offer_percentage_discount = get_option('offer_percentage_discount', 'quantity');
                            if ($offer_percentage_discount == 'quantity') {
                                $pq = $calculate_discount->wcoffers_calculate_registration_discount($user_id);
                                $pq[2] = $pq[2] . '%';
                                $offer_msg = sprintf('Get discount upto %s on new registration. Terms & conditions are applied.', $pq[2]);
                            } else {
                                $pa = $calculate_discount->wcoffers_calculate_registration_discount($user_id);
                                $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', $pa[2] . '%', wc_price($pa[1]));
                            }
                        } else {
                            $f = $calculate_discount->wcoffers_calculate_registration_discount($user_id);
                            $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Discount will expire on %s. Terms & conditions are applied.', wc_price($f[2]), wc_price($f[1]), date('F j, Y', strtotime($offer_exp_date)));
                        }
                        echo $offer_msg;
                        ?>
                    </div>
                </div>
                <?php
            }
        }
        //Second purchase offer
        if (count($orders) == 1) {
            $second_offer = get_option('disable_freebies', '1');
            $current_user_allow_offer = '';
            $offer_exp_date = get_user_meta($user_id, 'wco_second_purchase_expire_date', TRUE);
            if ($second_offer == 'disable_keep_existing') {
                if ($offer_exp_date && ($offer_exp_date == 'unlimited' || strtotime($offer_exp_date) > strtotime($currentDate))) {
                    $current_user_allow_offer = 'allow';
                }
            }
            if ($second_offer == 1 || $current_user_allow_offer == 'allow') {
                $offer_available = TRUE;
                ?>
                <div class="offer-wrapper">
                    <div class="offer-heading"><?php _e('Second Purchase Offer', 'wc-offers'); ?></div>
                    <div class="offer-content">
                        <?php
                        $offer_discount_type = get_option('offers_freebies_discount_type', 'percent');
                        if ($offer_discount_type == 'percent') {
                            $offer_percentage_discount = get_option('offers_freebies_discount', 'quantity');
                            if ($offer_percentage_discount == 'quantity') {
                                $pq = $calculate_discount->wcoffers_calculate_second_purchase_discount($user_id);
                                $pq[2] = $pq[2] . '%';
                                $offer_msg = sprintf('Get discount upto %s on second purchase. Terms & conditions are applied.', $pq[2]);
                            } else {
                                $pa = $calculate_discount->wcoffers_calculate_second_purchase_discount($user_id);
                                $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', $pa[2] . '%', wc_price($pa[1]));
                            }
                        } else {
                            $f = $calculate_discount->wcoffers_calculate_second_purchase_discount($user_id);
                            $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Discount will expire on %s. Terms & conditions are applied.', wc_price($f[2]), wc_price($f[1]), date('F j, Y', strtotime($offer_exp_date)));
                        }
                        echo $offer_msg;
                        ?>
                    </div>
                </div>
                <?php
            }
        }
        //Exclusive offers
        $disable_exist_customer = get_option('disable_exist_customer', '');
        if ($disable_exist_customer != 1) {
            $last_orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'meta_key' => '_customer_user',
                'meta_value' => $user_id,
                'numberposts' => 1,
                'order_by' => 'ID',
                'order' => 'DESC'
            ));
            $last_order = isset($last_orders[0]) ? $last_orders[0] : array();
            if ($last_order) {
                $offer_old_user_last_order_number = get_option('offer_old_user_last_order_number', '');
                $offer_old_user_last_order_time = get_option('offer_old_user_last_order_time', 'day');
                if ($offer_old_user_last_order_number != '' && $offer_old_user_last_order_number != 0) {
                    $string = $offer_old_user_last_order_number . ' ' . $offer_old_user_last_order_time;
                    $last_date = new DateTime($currentDate . ' - ' . $string);
                    $last_date = $last_date->format('Y-m-d');
                    $last_order_date = get_the_time('Y-m-d', $last_order->ID);
                    if (strtotime($last_order_date) <= strtotime($last_date)) {
                        $last_order_diff = date_diff(date_create($last_order_date), date_create($last_date));
                        $diff_day = $last_order_diff->days;
                        $offer_old_user_expiry_number = get_option('offer_old_user_expiry_number');
                        $offer_old_user_expiry_time = get_option('offer_old_user_expiry_time');
                        if ($offer_old_user_expiry_number == '' || $offer_old_user_expiry_number == 0) {
                            $offer_old_user_expiry_number = $diff_day;
                        }
                        if ($offer_old_user_expiry_number) {
                            if ($offer_old_user_expiry_time == 'week') {
                                $total_days = $offer_old_user_expiry_number * 7;
                            } else if ($offer_old_user_expiry_time == 'month') {
                                $total_days = $offer_old_user_expiry_number * 30;
                            } else if ($offer_old_user_expiry_time == 'year') {
                                $total_days = $offer_old_user_expiry_number * 365;
                            } else {
                                $total_days = $offer_old_user_expiry_number;
                            }
                            if ($total_days <= $diff_day) {
                                $offer_available = TRUE;
                                $user_info = get_userdata($user_id);
                                $offer_discount_type = get_option('offers_old_user_discount_type', 'percent');
                                $replace = array(
                                    $user_info->user_login
                                );
                                $result = array();
                                if ($offer_discount_type == 'percent') {
                                    $offer_percentage_discount = get_option('offers_old_user_percentage_discount', 'quantity');
                                    if ($offer_percentage_discount == 'quantity') {
                                        $result = get_option('inactive_customer_offer_percentage_quantity', array());
                                    } else {
                                        $result = get_option('inactive_customer_offer_percentage_amount', array());
                                    }
                                } else {
                                    $result = get_option('inactive_customer_offer_fixed_cart', array());
                                }

                                if (count($result) > 1) {
                                    $first = reset($result);
                                    $last = end($result);
                                    $min_min_amount = isset($first['min_amount']) ? $first['min_amount'] : $first['min_quantity'];
                                    $min_max_amount = isset($first['max_amount']) ? $first['max_amount'] : $first['max_quantity'];
                                    $min_discount = $first['discount_amount'];

                                    $max_min_amount = isset($last['min_amount']) ? $last['min_amount'] : $last['min_quantity'];
                                    $max_max_amount = isset($last['max_amount']) ? $last['max_amount'] : $last['max_quantity'];
                                    $max_amount = $last['discount_amount'];
                                } else {
                                    $min_min_amount = $max_min_amount = isset($result[0]['min_amount']) ? $result[0]['min_amount'] : $result[0]['min_quantity'];
                                    $min_max_amount = $max_max_amount = isset($result[0]['max_amount']) ? $result[0]['max_amount'] : $result[0]['max_quantity'];
                                    $min_discount = $max_discount = $result[0]['discount_amount'];
                                }
                                $replace[] = $min_min_amount;
                                $replace[] = $max_amount;
                                $replace[] = $min_discount;
                                $replace[] = $max_min_amount;
                                ?>
                                <div class="offer-wrapper">
                                    <div class="offer-heading"><?php _e('Exclusive Offer', 'wc-offers'); ?></div>
                                    <div class="offer-content">
                                        <?php
                                        if ($offer_discount_type == 'percent') {
                                            $offer_percentage_discount = get_option('offers_freebies_discount', 'quantity');
                                            if ($offer_percentage_discount == 'quantity') {
                                                $pq = $replace;
                                                $pq[2] = $pq[2] . '%';
                                                $offer_msg = sprintf('Get discount upto %s on this offer. Terms & conditions are applied.', $pq[2]);
                                            } else {
                                                $pa = $replace;
                                                $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', $pa[2] . '%', wc_price($pa[1]));
                                            }
                                        } else {
                                            $f = $replace;
                                            $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Discount will expire on %s. Terms & conditions are applied.', wc_price($f[2]), wc_price($f[1]), date('F j, Y', strtotime($offer_exp_date)));
                                        }
                                        echo $offer_msg;
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                }
            }
        }

        //Special offer
        $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
        $eu_offer_key = '';
        if ($wcoffers_existing_customer) {
            foreach ($wcoffers_existing_customer as $key => $single_offer) {
                if (isset($single_offer['user_ids']) && in_array($user_id, $single_offer['user_ids'])) {
                    $eu_offer_key = $key;
                }
            }
        }
        $eu_offer_data = isset($wcoffers_existing_customer[$eu_offer_key]) ? $wcoffers_existing_customer[$eu_offer_key] : array();
        if ($eu_offer_data) {
            $eu_offer_date = get_user_meta($user_id, 'wco_existing_user_offer_expiry_date', TRUE);
            if ($eu_offer_date != 'unlimited') {
                if ($eu_offer_date != '') {
                    if (strtotime($eu_offer_date) < strtotime($currentDate)) {
                        //do nothing
                    } else {
                        $offer_available = TRUE;
                        ?>
                        <div class="offer-wrapper">
                            <div class="offer-heading"><?php _e('Special Offer!', 'wc-offers'); ?></div>
                            <div class="offer-content">
                                <?php
                                $offer_discount_type = isset($eu_offer_data['discount_type']) ? $eu_offer_data['discount_type'] : 'percent';
                                if ($offer_discount_type == 'percent') {
                                    $offer_percentage_discount = isset($eu_offer_data['percentage_discount']) ? $eu_offer_data['percentage_discount'] : 'quantity';
                                    if ($offer_percentage_discount == 'quantity') {
                                        $pq = $calculate_discount->wcoffers_calculate_existing_customer_discount($user_id, $eu_offer_data);
                                        $pq[2] = $pq[2] . '%';
                                        $offer_msg = sprintf('Get discount upto %s on this special offer. Terms & conditions are applied.', $pq[2]);
                                    } else {
                                        $pa = $calculate_discount->wcoffers_calculate_existing_customer_discount($user_id, $eu_offer_data);
                                        $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', $pa[2] . '%', wc_price($pa[1]));
                                    }
                                } else {
                                    $f = $calculate_discount->wcoffers_calculate_existing_customer_discount($user_id, $eu_offer_data);
                                    $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Discount will expire on %s. Terms & conditions are applied.', wc_price($f[2]), wc_price($f[1]), date('F j, Y', strtotime($offer_exp_date)));
                                }
                                echo $offer_msg;
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
        }
        //Birthday Offer
        $disable_birthday_offers = get_option('disable_birthday_offers', '0');
        if ($disable_birthday_offers != 1) {
            $disable_birthday_field = get_option('disable_birthday_field', '0');
            $match_date = explode('-', $currentDate, 2);
            $today_date = isset($match_date[1]) ? $match_date[1] : '';
            if ($disable_birthday_field != 1) {
                $birthday = get_user_meta($user_id, 'wc_birthday', true);
            } else {
                $offer_birthday_custom_meta_field = get_option('offer_birthday_custom_meta_field', '');
                $get_user_meta = get_user_meta($user_id, $offer_birthday_custom_meta_field, TRUE);
                $birthday = date('Y-m-d', strtotime($get_user_meta));
            }
            $current_user_bd = '';
            if ($birthday) {
                $birthday_date = explode('-', $birthday, 2);
                $current_user_bd = isset($birthday_date[1]) ? $birthday_date[1] : '';
            }
            if ($current_user_bd == $today_date) {
                $offer_birthday_available = get_option('offer_birthday_available', 'whole_day');
                $offers_birthday_product = get_option('offers_birthday_product', array());
                $customer_orders = get_posts(array(
                    'numberposts' => -1,
                    'meta_key' => '_customer_user',
                    'meta_value' => $user_id,
                    'post_type' => wc_get_order_types(),
                    'post_status' => array_keys(wc_get_order_statuses()),
                    'order_by' => 'ID',
                    'order' => 'DESC'
                ));
                $last_order = isset($customer_orders[0]) ? $customer_orders[0] : array();
                $last_order_date = get_the_time('Y-m-d', $last_order->ID);
                $total_order = count($customer_orders);
                $offer_array_index = '';
                foreach ($offers_birthday_product as $key => $birthday_offer) {
                    $order_amount = $birthday_offer['order_amount'];
                    $offers_birthday_order_comparison = $birthday_offer['offers_birthday_order_comparison'];
                    if ($offers_birthday_order_comparison == '=') {
                        if ($order_amount == $total_order) {
                            $offer_array_index = $key;
                        }
                    } else if ($offers_birthday_order_comparison == '<') {
                        if ($total_order < $order_amount) {
                            $offer_array_index = $key;
                        }
                    } else if ($offers_birthday_order_comparison == '>') {
                        if ($total_order > $order_amount) {
                            $offer_array_index = $key;
                        }
                    } else if ($offers_birthday_order_comparison == '<=') {
                        if ($total_order <= $order_amount) {
                            $offer_array_index = $key;
                        }
                    } else if ($offers_birthday_order_comparison == '>=') {
                        if ($total_order >= $order_amount) {
                            $offer_array_index = $key;
                        }
                    }
                }
                if ($offer_array_index != '' || $offer_array_index == 0) {
                    $birthday_offer_arr = isset($offers_birthday_product[$offer_array_index]) ? $offers_birthday_product[$offer_array_index] : array();
                    if ($offer_birthday_available == 'single_order') {
                        if (strtotime($last_order_date) == strtotime($currentDate)) {
                            //do nothing
                        } else {
                            ?>
                            <div class="offer-wrapper">
                                <div class="offer-heading"><?php _e('Birthday Offer', 'wc-offers'); ?></div>
                                <div class="offer-content">
                                    <?php
                                    $offer_discount_type = $birthday_offer_arr['offers_birthday_discount_type'];
                                    if ($offer_discount_type == 'percent') {
                                        $offer_percentage_discount = isset($birthday_offer_arr['offer_percentage_discount']) ? $birthday_offer_arr['offer_percentage_discount'] : 'quantity';
                                        if ($offer_percentage_discount == 'quantity') {
                                            $pq = $calculate_discount->wcoffers_calculate_birthday_offer_discount($user_id, $birthday_offer_arr);
                                            $pq[2] = $pq[2] . '%';
                                            $offer_msg = sprintf('Get discount upto %s on your birthday. Terms & conditions are applied.', $pq[2]);
                                        } else {
                                            $pa = $calculate_discount->wcoffers_calculate_birthday_offer_discount($user_id, $birthday_offer_arr);
                                            $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', $pa[2] . '%', wc_price($pa[1]));
                                        }
                                    } else {
                                        $f = $calculate_discount->wcoffers_calculate_birthday_offer_discount($user_id, $birthday_offer_arr);
                                        $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', wc_price($f[2]), wc_price($f[1]));
                                    }
                                    echo $offer_msg;
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="offer-wrapper">
                            <div class="offer-heading"><?php _e('Birthday Offer', 'wc-offers'); ?></div>
                            <div class="offer-content">
                                <?php
                                $offer_discount_type = $birthday_offer_arr['offers_birthday_discount_type'];
                                if ($offer_discount_type == 'percent') {
                                    $offer_percentage_discount = isset($birthday_offer_arr['offer_percentage_discount']) ? $birthday_offer_arr['offer_percentage_discount'] : 'quantity';
                                    if ($offer_percentage_discount == 'quantity') {
                                        $pq = $calculate_discount->wcoffers_calculate_birthday_offer_discount($user_id, $birthday_offer_arr);
                                        $pq[2] = $pq[2] . '%';
                                        $offer_msg = sprintf('Get discount upto %s on your birthday. Terms & conditions are applied.', $pq[2]);
                                    } else {
                                        $pa = $calculate_discount->wcoffers_calculate_birthday_offer_discount($user_id, $birthday_offer_arr);
                                        $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', $pa[2] . '%', wc_price($pa[1]));
                                    }
                                } else {
                                    $f = $calculate_discount->wcoffers_calculate_birthday_offer_discount($user_id, $birthday_offer_arr);
                                    $offer_msg = sprintf('Get discount upto %s on purchase of minimum %s. Terms & conditions are applied.', wc_price($f[2]), wc_price($f[1]));
                                }
                                echo $offer_msg;
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
        }
        //Indivisual rules
        $wco_product_pricing = get_option('wco_product_pricing', array());
        if ($wco_product_pricing) {
            foreach ($wco_product_pricing as $key => $single_price) {
                $offer_available = TRUE;
                ?>
                <div class="offer-wrapper">
                    <div class="offer-heading">
                        <?php
                        if ($single_price['private_note'] == '') {
                            _e('Untitle', 'wc-offers');
                        } else {
                            echo $single_price['private_note'];
                        }
                        ?>
                    </div>
                    <div class="offer-content">
                        <?php _e('Get discount on products and category. Terms & Conditions are applied.'); ?>
                    </div>
                </div>
                <?php
            }
        }
        if ($offer_available === FALSE) {
            _e('No offers available', 'wc-offers');
        }
    }

    /**
     * @since 1.0
     * Display add deposit form
     */
    public function wcoffers_adddeposit_endpoint_content() {
        $functions = new WCOCommonFunctions();
        $user_id = get_current_user_id();
        include 'add-deposit.php';
    }

    /**
     * @since 1.0
     * Display current user deposit history
     */
    public function wcoffers_deposit_transaction_history() {
        $user_id = get_current_user_id();
        include 'deposit-history.php';
    }

    /**
     * @since 1.0
     * Plugin install action.
     * Flush rewrite rules to make our custom endpoint available.
     */
    public static function install() {
        flush_rewrite_rules();
    }

    /**
     * @since 1.0
     * @param array $gateways
     * @return array
     * Return selected payment method at deposit form
     */
    public function wcoffers_deposit_available_payment_method($gateways) {
        $current_end_point = WC()->query->get_current_endpoint();
        if ($current_end_point == 'add-deposit') {
            $deposite_gateways = get_option('wcoffers_deposit_payment_method', array());
            if ($deposite_gateways) {
                foreach ($gateways as $key => $value) {
                    if (!in_array($key, $deposite_gateways)) {
                        unset($gateways[$key]);
                    }
                }
            }
        }
        return $gateways;
    }

}

new Offers_My_Account_Endpoint();

// Flush rewrite rules on plugin activation.
register_activation_hook(__FILE__, array('Offers_My_Account_Endpoint', 'install'));
