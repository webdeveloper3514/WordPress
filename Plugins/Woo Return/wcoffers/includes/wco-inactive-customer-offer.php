<?php

class WCO_Inactive_Customer_Offers {

    var $enable_offer = '0';

    public function __construct() {
        $this->enable_offer = get_option('disable_exist_customer', '0');
        add_action('admin_init', array($this, 'wcoffers_save_inactive_customer_settings'), 13);
        if ($this->enable_offer != 1) {
            add_action('woocommerce_cart_calculate_fees', array($this, 'wcoffers_add_inactive_customer_discount'), 12);
        }
    }
    
    /**
     * @since 1.0
     * @param object $cart
     * Add discount on cart page
     */
    function wcoffers_add_inactive_customer_discount($cart) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'meta_key' => '_customer_user',
                'meta_value' => $user_id,
                'numberposts' => 1,
                'order_by' => 'ID',
                'order' => 'DESC'
            ));
            $last_order = isset($orders[0]) ? $orders[0] : array();
            if ($last_order) {
                $offer_old_user_last_order_number = get_option('offer_old_user_last_order_number', '');
                $offer_old_user_last_order_time = get_option('offer_old_user_last_order_time', 'day');
                $currentDate = date('Y-m-d');
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
                                $functions = new WCOCommonFunctions();
                                $product_ids = get_option('inactive_customer_offer_exclude_product', array());
                                $cat_ids = get_option('inactive_customer_offer_exclude_product_category', array());
                                $offer_discount_type = get_option('offers_old_user_discount_type', 'percent');
                                $offer_old_user_enable_cashback = get_option('offer_old_user_enable_cashback', 'no');
                                if ($offer_discount_type == 'percent') {
                                    $offer_percentage_discount = get_option('offers_old_user_percentage_discount', 'quantity');
                                    if ($offer_percentage_discount == 'quantity') {
                                        $registration_offer_percentage_quantity = get_option('inactive_customer_offer_percentage_quantity', array());
                                        $cart_total_quantity = WC()->cart->get_cart_contents_count();
                                        $exclude_quantity = $functions->wco_calculae_exclude_total(WC()->cart,$product_ids,$cat_ids,'percent_quantity');
                                        if($exclude_quantity){
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
                                                $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart,$product_ids,$cat_ids,'fixed_cart');
                                                if($exclude_total){
                                                    $excluded_total = $excluded_total - $exclude_total;
                                                }
                                                $discount = $excluded_total * $total_dis;
                                                if($offer_old_user_enable_cashback == 'yes'){
                                                    $functions->wcoffers_add_cashback_row($discount);
                                                }else{
                                                    if (isset($_SESSION['cashback_amount']))
                                                        unset($_SESSION['cashback_amount']);
                                                    $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                                }                                                   
                                            }
                                        }
                                    } else if ($offer_percentage_discount == 'amount') {
                                        $registration_offer_percentage_amount = get_option('inactive_customer_offer_percentage_amount', array());
                                        if ($registration_offer_percentage_amount) {
                                            $subtotal = $cart->subtotal;
                                            $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart,$product_ids,$cat_ids,'fixed_cart');
                                            if($exclude_total){
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
                                                    if($offer_old_user_enable_cashback == 'yes'){
                                                        $functions->wcoffers_add_cashback_row($discount);
                                                    }else{
                                                        if (isset($_SESSION['cashback_amount']))
                                                            unset($_SESSION['cashback_amount']);
                                                        $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                                    }                                                    
                                                }
                                            }
                                        }
                                    }
                                } else if ($offer_discount_type == 'fixed_cart') {
                                    $registration_offer_fixed_cart = get_option('inactive_customer_offer_fixed_cart', array());
                                    if ($registration_offer_fixed_cart) {
                                        $subtotal = $cart->subtotal;
                                        $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart,$product_ids,$cat_ids,'fixed_cart');
                                        if($exclude_total){
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
                                                if($offer_old_user_enable_cashback == 'yes'){
                                                    $functions->wcoffers_add_cashback_row($discount);
                                                }else{
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
            }
        }
    }
    
    /**
     * @since 1.0
     * Save settings of inactive customers
     */
    function wcoffers_save_inactive_customer_settings() {
        $current_offer_page = isset($_GET['page']) && $_GET['page'] == 'wc-offers' && isset($_GET['tab']) ? $_GET['tab'] : 'new_reg';
        if (isset($_POST['save_offers']) && isset($_POST['wpoffers_nonce_field']) && wp_verify_nonce($_POST['wpoffers_nonce_field'], 'wpoffers_nonce_data')) {
            if ($current_offer_page == 'old-users') {
                if (isset($_POST['disable_exist_customer'])) {
                    update_option('disable_exist_customer', $_POST['disable_exist_customer']);
                } else {
                    update_option('disable_exist_customer', '0');
                }
                if (isset($_POST['offer_old_user_last_order_number'])) {
                    update_option('offer_old_user_last_order_number', $_POST['offer_old_user_last_order_number']);
                }
                if (isset($_POST['offer_old_user_last_order_time'])) {
                    update_option('offer_old_user_last_order_time', $_POST['offer_old_user_last_order_time']);
                }
                if (isset($_POST['offers_old_user_discount_type'])) {
                    update_option('offers_old_user_discount_type', $_POST['offers_old_user_discount_type']);
                }
                if (isset($_POST['offers_old_user_percentage_discount'])) {
                    update_option('offers_old_user_percentage_discount', $_POST['offers_old_user_percentage_discount']);
                }
                if (isset($_POST['offer_old_user_expiry_number'])) {
                    update_option('offer_old_user_expiry_number', $_POST['offer_old_user_expiry_number']);
                }
                if (isset($_POST['offer_old_user_expiry_time'])) {
                    update_option('offer_old_user_expiry_time', $_POST['offer_old_user_expiry_time']);
                }
                if (isset($_POST['offer_old_user_enable_cashback'])) {
                    update_option('offer_old_user_enable_cashback', $_POST['offer_old_user_enable_cashback']);
                }
                if (isset($_POST['inactive_customer_offer_percentage_quantity'])) {
                    $a = $_POST['inactive_customer_offer_percentage_quantity'];
                    if (is_array($a) && !empty($a)) {
                        foreach ($a as $key => $value) {
                            if ($value['min_quantity'] == '') {
                                unset($a[$key]);
                            }
                        }
                    } else {
                        $a = array();
                    }
                    update_option('inactive_customer_offer_percentage_quantity', $a);
                } else {
                    update_option('inactive_customer_offer_percentage_quantity', array());
                }
                if (isset($_POST['inactive_customer_offer_percentage_amount'])) {
                    $a = $_POST['inactive_customer_offer_percentage_amount'];
                    if (is_array($a) && !empty($a)) {
                        foreach ($a as $key => $value) {
                            if ($value['min_amount'] == '') {
                                unset($a[$key]);
                            }
                        }
                    } else {
                        $a = array();
                    }
                    update_option('inactive_customer_offer_percentage_amount', $a);
                } else {
                    update_option('inactive_customer_offer_percentage_amount', array());
                }
                if (isset($_POST['inactive_customer_offer_fixed_cart'])) {
                    $a = $_POST['inactive_customer_offer_fixed_cart'];
                    if (is_array($a) && !empty($a)) {
                        foreach ($a as $key => $value) {
                            if ($value['min_amount'] == '') {
                                unset($a[$key]);
                            }
                        }
                    } else {
                        $a = array();
                    }
                    update_option('inactive_customer_offer_fixed_cart', $a);
                } else {
                    update_option('inactive_customer_offer_fixed_cart', array());
                }
                
                if(isset($_POST['inactive_customer_offer_exclude_product'])){
                    update_option('inactive_customer_offer_exclude_product', $_POST['inactive_customer_offer_exclude_product']);
                }else{
                    update_option('inactive_customer_offer_exclude_product', array());
                }
                if(isset($_POST['inactive_customer_offer_exclude_product_category'])){
                    update_option('inactive_customer_offer_exclude_product_category', $_POST['inactive_customer_offer_exclude_product_category']);
                }else{
                    update_option('inactive_customer_offer_exclude_product_category', array());
                }
            }
        }
    }

}

new WCO_Inactive_Customer_Offers();

