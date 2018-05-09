<?php

class WCO_Second_Purchase_Offers {

    var $enable_offer = '1';

    public function __construct() {
        $this->enable_offer = get_option('disable_freebies', '1');
        add_action('admin_init', array($this, 'wcoffers_save_second_purchase_settings'), 12);
        add_action('woocommerce_cart_calculate_fees', array($this, 'wcoffers_add_second_purchase_discount'), 11);
        if ($this->enable_offer == 1) {
            add_action('woocommerce_new_order', array($this, 'wcoffers_create_sp_expiry_date'), 10, 1);
        }
    }

    /**
     * @since 1.0
     * @param int $order_id
     * Add offer of second purchase
     */
    function wcoffers_create_sp_expiry_date($order_id) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'meta_key' => '_customer_user',
                'meta_value' => $user_id,
            ));
            if (count($orders) == 1) {
                $offer_freebies_expiry_number = get_option('offer_freebies_expiry_number', '');
                $offer_freebies_expiry_time = get_option('offer_freebies_expiry_time', 'day');
                if ($offer_freebies_expiry_number == '' || $offer_freebies_expiry_number == 0) {
                    $expiry_date = 'unlimited';
                } else {
                    $currentDate = date('Y-m-d');
                    $string = $offer_freebies_expiry_number . ' ' . $offer_freebies_expiry_time;
                    $expiry_date = new DateTime($currentDate . ' + ' . $string);
                    $expiry_date = $expiry_date->format('Y-m-d');
                }
                update_user_meta($user_id, 'wco_second_purchase_expire_date', $expiry_date);
                $email_template = new WCO_Email_Templates();
                $email_template->wcoffers_second_purchase_email($user_id, $string);
            }
        }
    }

    /**
     * @since 1.0
     * @param obj $cart
     * Apply second purchase on cart discount
     */
    function wcoffers_add_second_purchase_discount($cart) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $currentDate = date('Y-m-d');
            $orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'meta_key' => '_customer_user',
                'meta_value' => $user_id,
            ));
            if (count($orders) == 1) {
                if ($this->enable_offer == 1) {
                    $first_purchase_date = get_user_meta($user_id, 'wco_second_purchase_expire_date', TRUE);
                    if ($first_purchase_date != 'unlimited') {
                        if ($first_purchase_date == '')
                            return;
                        if ($first_purchase_date) {
                            if (strtotime($first_purchase_date) < strtotime($currentDate)) {
                                return;
                            }
                        }
                    }
                }
                $current_user_allow_offer = '';
                if ($this->enable_offer == 'disable_keep_existing') {
                    $offer_exp_date = get_user_meta($user_id, 'wco_second_purchase_expire_date', TRUE);
                    if ($offer_exp_date && ($offer_exp_date == 'unlimited' || strtotime($offer_exp_date) > strtotime($currentDate))) {
                        $current_user_allow_offer = 'allow';
                    }
                }
                if ($this->enable_offer == 1 || $current_user_allow_offer == 'allow') {
                    $functions = new WCOCommonFunctions();
                    $product_ids = get_option('freebies_offer_exclude_product', array());
                    $cat_ids = get_option('freebies_offer_exclude_product_category', array());
                    $offer_discount_type = get_option('offers_freebies_discount_type', 'percent');
                    $offer_freebies_enable_cashback = get_option('offer_freebies_enable_cashback', 'no');
                    if ($offer_discount_type == 'percent') {
                        $offer_percentage_discount = get_option('offers_freebies_discount', 'quantity');
                        if ($offer_percentage_discount == 'quantity') {
                            $registration_offer_percentage_quantity = get_option('second_purchase_offer_percentage_quantity', array());
                            $cart_total_quantity = WC()->cart->get_cart_contents_count();
                            $exclude_quantity = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'percent_quantity');
                            if ($exclude_quantity) {
                                $cart_total_quantity = $cart_total_quantity - $exclude_quantity;
                            }
                            $applied_offer = '';
                            foreach ($registration_offer_percentage_quantity as $key => $value) {
                                $minValue = $value['min_quantity'];
                                $maxValue = $value['max_quantity'];
                                if ($maxValue == '' && $cart_total_quantity >= $minValue) {
                                    $applied_offer = $key;
                                } else if ($cart_total_quantity >= $minValue && $cart_total_quantity <= $maxValue) {
                                    $applied_offer = $key;
                                }
                            }
                            if (isset($registration_offer_percentage_quantity[$applied_offer])) {
                                $discount_amount = isset($registration_offer_percentage_quantity[$applied_offer]['discount_amount']) ? $registration_offer_percentage_quantity[$applied_offer]['discount_amount'] : '';
                                if ($discount_amount != '' && $discount_amount < 100) {
                                    $discount_amount = $registration_offer_percentage_quantity[$applied_offer]['discount_amount'];
                                    $total_dis = $discount_amount / 100;
                                    $excluded_total = $cart->subtotal;
                                    $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                                    if ($exclude_total) {
                                        $excluded_total = $excluded_total - $exclude_total;
                                    }
                                    $discount = $excluded_total * $total_dis;
                                    if ($offer_freebies_enable_cashback == 'yes') {
                                        $functions->wcoffers_add_cashback_row($discount);
                                    } else {
                                        if (isset($_SESSION['cashback_amount']))
                                            unset($_SESSION['cashback_amount']);
                                        $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                    }
                                }
                            }
                        } else if ($offer_percentage_discount == 'amount') {
                            $registration_offer_percentage_amount = get_option('second_purchase_offer_percentage_amount', array());
                            if ($registration_offer_percentage_amount) {
                                $subtotal = $cart->subtotal;
                                $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                                if ($exclude_total) {
                                    $subtotal = $subtotal - $exclude_total;
                                }
                                $applied_offer = '';
                                foreach ($registration_offer_percentage_amount as $key => $amount) {
                                    $min_amount = $amount['min_amount'];
                                    $max_amount = $amount['max_amount'];
                                    if ($max_amount == '' && $subtotal >= $min_amount) {
                                        $applied_offer = $key;
                                    } else if ($subtotal >= $min_amount && $subtotal <= $max_amount) {
                                        $applied_offer = $key;
                                    }
                                }
                                if (isset($registration_offer_percentage_amount[$applied_offer])) {
                                    $discount_amount = isset($registration_offer_percentage_amount[$applied_offer]['discount_amount']) ? $registration_offer_percentage_amount[$applied_offer]['discount_amount'] : '';
                                    if ($discount_amount != '' && $discount_amount < 100) {
                                        $discount_amount = $registration_offer_percentage_amount[$applied_offer]['discount_amount'];
                                        $total_dis = $discount_amount / 100;
                                        $discount = $subtotal * $total_dis;
                                        if ($offer_freebies_enable_cashback == 'yes') {
                                            $functions->wcoffers_add_cashback_row($discount);
                                        } else {
                                            if (isset($_SESSION['cashback_amount']))
                                                unset($_SESSION['cashback_amount']);
                                            $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                        }
                                    }
                                }
                            }
                        }
                    } else if ($offer_discount_type == 'fixed_cart') {
                        $registration_offer_fixed_cart = get_option('second_purchase_offer_fixed_cart', array());
                        if ($registration_offer_fixed_cart) {
                            $subtotal = $cart->subtotal;
                            $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                            if ($exclude_total) {
                                $subtotal = $subtotal - $exclude_total;
                            }
                            $applied_offer = '';
                            foreach ($registration_offer_fixed_cart as $key => $fix_amount) {
                                $min_amount = $fix_amount['min_amount'];
                                $max_amount = $fix_amount['max_amount'];
                                if ($max_amount == '' && $subtotal >= $min_amount) {
                                    $applied_offer = $key;
                                } else if ($subtotal >= $min_amount && $subtotal <= $max_amount) {
                                    $applied_offer = $key;
                                }
                            }
                            if (isset($registration_offer_fixed_cart[$applied_offer])) {
                                $discount_amount = isset($registration_offer_fixed_cart[$applied_offer]['discount_amount']) ? $registration_offer_fixed_cart[$applied_offer]['discount_amount'] : '';
                                if ($discount_amount != '') {
                                    $discount_amount = $registration_offer_fixed_cart[$applied_offer]['discount_amount'];
                                    $total_dis = $discount_amount;
                                    $discount = $total_dis;
                                    if ($offer_freebies_enable_cashback == 'yes') {
                                        $functions->wcoffers_add_cashback_row($discount);
                                    } else {
                                        if (isset($_SESSION['cashback_amount']))
                                            unset($_SESSION['cashback_amount']);
                                        $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @since 1.0
     * Save second purchase setting
     */
    function wcoffers_save_second_purchase_settings() {
        $current_offer_page = isset($_GET['page']) && $_GET['page'] == 'wc-offers' && isset($_GET['tab']) ? $_GET['tab'] : 'new_reg';
        if (isset($_POST['save_offers']) && isset($_POST['wpoffers_nonce_field']) && wp_verify_nonce($_POST['wpoffers_nonce_field'], 'wpoffers_nonce_data')) {
            if ($current_offer_page == 'freebies') {
                if (isset($_POST['disable_freebies'])) {
                    update_option('disable_freebies', $_POST['disable_freebies']);
                } else {
                    update_option('disable_freebies', '0');
                }
                if (isset($_POST['offers_freebies_discount_type'])) {
                    update_option('offers_freebies_discount_type', $_POST['offers_freebies_discount_type']);
                }
                if (isset($_POST['offers_freebies_discount'])) {
                    update_option('offers_freebies_discount', $_POST['offers_freebies_discount']);
                }
                if (isset($_POST['offer_freebies_expiry_number'])) {
                    update_option('offer_freebies_expiry_number', $_POST['offer_freebies_expiry_number']);
                }
                if (isset($_POST['offer_freebies_expiry_time'])) {
                    update_option('offer_freebies_expiry_time', $_POST['offer_freebies_expiry_time']);
                }
                if (isset($_POST['offer_freebies_enable_cashback'])) {
                    update_option('offer_freebies_enable_cashback', $_POST['offer_freebies_enable_cashback']);
                }
                if (isset($_POST['second_purchase_offer_percentage_quantity'])) {
                    $a = $_POST['second_purchase_offer_percentage_quantity'];
                    if (is_array($a) && !empty($a)) {
                        foreach ($a as $key => $value) {
                            if ($value['min_quantity'] == '') {
                                unset($a[$key]);
                            }
                        }
                    } else {
                        $a = array();
                    }
                    update_option('second_purchase_offer_percentage_quantity', $a);
                } else {
                    update_option('second_purchase_offer_percentage_quantity', array());
                }
                if (isset($_POST['second_purchase_offer_percentage_amount'])) {
                    $a = $_POST['second_purchase_offer_percentage_amount'];
                    if (is_array($a) && !empty($a)) {
                        foreach ($a as $key => $value) {
                            if ($value['min_amount'] == '') {
                                unset($a[$key]);
                            }
                        }
                    } else {
                        $a = array();
                    }
                    update_option('second_purchase_offer_percentage_amount', $a);
                } else {
                    update_option('second_purchase_offer_percentage_amount', array());
                }
                if (isset($_POST['second_purchase_offer_fixed_cart'])) {
                    $a = $_POST['second_purchase_offer_fixed_cart'];
                    if (is_array($a) && !empty($a)) {
                        foreach ($a as $key => $value) {
                            if ($value['min_amount'] == '') {
                                unset($a[$key]);
                            }
                        }
                    } else {
                        $a = array();
                    }
                    update_option('second_purchase_offer_fixed_cart', $a);
                } else {
                    update_option('second_purchase_offer_fixed_cart', array());
                }
                if (isset($_POST['freebies_offer_exclude_product'])) {
                    update_option('freebies_offer_exclude_product', $_POST['freebies_offer_exclude_product']);
                } else {
                    update_option('freebies_offer_exclude_product', array());
                }
                if (isset($_POST['freebies_offer_exclude_product_category'])) {
                    update_option('freebies_offer_exclude_product_category', $_POST['freebies_offer_exclude_product_category']);
                } else {
                    update_option('freebies_offer_exclude_product_category', array());
                }
            }
        }
    }

}

new WCO_Second_Purchase_Offers();

