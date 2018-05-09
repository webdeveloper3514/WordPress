<?php

class WCOCommonFunctions {

    /**
     * @since 1.0
     * @param obj $cart
     * @param array $product_ids
     * @param array $cat_ids
     * @param string $discount_type
     * @return int
     * Return Total excluded amount
     */
    public function wco_calculae_exclude_total($cart, $product_ids, $cat_ids, $discount_type) {
        $cart_items = $cart->cart_contents;
        $exclude_item = 0;
        if ($cart_items) {
            foreach ($cart_items as $cart_item_key => $cart_item_data) {
                $_product = $cart_item_data['data'];
                $product_id = $_product->get_id();
                $quantity = $cart_item_data['quantity'];
                $init_price = $_product->get_price();
                $terms = get_the_terms($product_id, 'product_cat');
                $product_cat = array();
                if ($terms) {
                    foreach ($terms as $term) {
                        $product_cat[] = $term->term_id;
                    }
                }
                $matched_category = array_intersect($product_cat, $cat_ids);
                if (in_array($product_id, $product_ids) || !empty($matched_category)) {
                    if ($discount_type == 'fixed_cart') {
                        $exclude_item = $exclude_item + ($quantity * $init_price);
                    } else if ($discount_type == 'percent_quantity') {
                        $exclude_item = $exclude_item + $quantity;
                    }
                }
            }
        }
        return $exclude_item;
    }

    /**
     * @since 1.0
     * @param int $cashback_amount
     * Add Cashback tr on cart and checkout page
     */
    public function wcoffers_add_cashback_row($cashback_amount) {
        if (!session_id()) {
            session_start();
        }
        $_SESSION['cashback_amount'] = $cashback_amount;
        add_action('woocommerce_cart_totals_before_order_total', array($this, 'wcoffers_reg_add_cashback_discount_html'));
        add_action('woocommerce_review_order_before_order_total', array($this, 'wcoffers_reg_add_cashback_discount_html'));
    }

    /**
     * @since 1.0
     * Display cashback tr
     */
    public function wcoffers_reg_add_cashback_discount_html() {
        if(isset($_SESSION['cashback_amount'])){
        ?>
            <tr class="order-discount account-funds-discount">
                <th>Cashback Amount</th>
                <td><?php echo wc_price($_SESSION['cashback_amount']); ?></td>
            </tr>
        <?php
        }
    }

    /**
     * @since 1.0
     * @return array
     * Add payment gateway
     */
    public function wcoffers_get_wc_gateway() {
        $payment = WC()->payment_gateways->payment_gateways();
        $gateways = array();
        foreach ($payment as $gateway) {
            if ($gateway->enabled == 'yes' && ($gateway->id != 'wcop') ) {
                if($gateway->id == 'bacs' || $gateway->id == 'cp' || $gateway->id == 'cod' || $gateway->id == 'paypal' ){
                    $gateways[$gateway->id] = $gateway->title;
                }                
            }
        }
        return $gateways;
    }

    /**
     * @since 1.0
     * @param int $user_id
     * @return boolean Check user profile is completed or not
     */
    public function wcoffers_check_user_profile($user_id) {
        $fname = get_user_meta($user_id, 'billing_first_name', true);
        $lname = get_user_meta($user_id, 'billing_last_name', true);
        $address_1 = get_user_meta($user_id, 'billing_address_1', true);
        $city = get_user_meta($user_id, 'billing_city', true);
        $billing_state = get_user_meta($user_id, 'billing_state', true);
        $postcode = get_user_meta($user_id, 'billing_postcode', true);
        $billing_phone = get_user_meta($user_id, 'billing_phone', true);
        if ($fname == '' || $lname == '' || $address_1 == '' || $city == '' || $billing_state == '' || $postcode == '' || $billing_phone == '') {
            return false;
        }
        return true;
    }
    
    /**
     * @since 1.0
     * @return boolean
     * Check registration offer is available or not
     */
    public function wcoffers_check_registration_offer_avail() {
        if (is_user_logged_in()) {
            $disable_new_reg = get_option('disable_new_reg', '1');
            $user_id = get_current_user_id();
            $udata = get_userdata($user_id);
            $currentDate = date('Y-m-d');
            $orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'meta_key' => '_customer_user',
                'meta_value' => $user_id,
            ));
            if (count($orders) != 0)
                return FALSE;
            if ($disable_new_reg == 1) {
                $registered = $udata->user_registered;
                $registered = date("Y-m-d", strtotime($registered));
                $reg_offer_date = get_user_meta($user_id, 'wco_new_registration_expire_date', TRUE);
                if ($reg_offer_date != 'unlimited') {
                    if ($reg_offer_date == '')
                        return FALSE;
                    if ($reg_offer_date) {
                        if (strtotime($reg_offer_date) < strtotime($currentDate)) {
                            return FALSE;
                        }
                    }
                }
            }
            $current_user_allow_offer = '';
            if ($disable_new_reg == 'disable_keep_existing') {
                $offer_exp_date = get_user_meta($user_id, 'wco_new_registration_expire_date', TRUE);
                if ($offer_exp_date && ( $offer_exp_date == 'unlimited' || strtotime($offer_exp_date) > strtotime($currentDate) )) {
                    $current_user_allow_offer = 'allow';
                } else {
                    return false;
                }
            }
            if ($disable_new_reg == 1 || $current_user_allow_offer == 'allow') {
                return true;
            }
            return FALSE;
        }
        return TRUE;
    }
    
     /**
     * @since 1.0
     * @return boolean
     * Check second purchase offer is available or not
     */
    public function wcoffers_check_second_purchase_offer_avail() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $currentDate = date('Y-m-d');
            $orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'meta_key' => '_customer_user',
                'meta_value' => $user_id,
            ));
            $disable_freebies = get_option('disable_freebies', '1');
            if (count($orders) == 1) {
                if ($disable_freebies == 1) {
                    $first_purchase_date = get_user_meta($user_id, 'wco_second_purchase_expire_date', TRUE);
                    if ($first_purchase_date != 'unlimited') {
                        if ($first_purchase_date == '')
                            return FALSE;
                        if ($first_purchase_date) {
                            if (strtotime($first_purchase_date) < strtotime($currentDate)) {
                                return FALSE;
                            }
                        }
                    }
                }
                $current_user_allow_offer = '';
                if ($disable_freebies == 'disable_keep_existing') {
                    $offer_exp_date = get_user_meta($user_id, 'wco_second_purchase_expire_date', TRUE);
                    if ($offer_exp_date && ($offer_exp_date == 'unlimited' || strtotime($offer_exp_date) > strtotime($currentDate))) {
                        $current_user_allow_offer = 'allow';
                    }
                }
                if ($disable_freebies == 1 || $current_user_allow_offer == 'allow') {
                    return true;
                }
            }
            return FALSE;
        }
        return TRUE;
    }

}
