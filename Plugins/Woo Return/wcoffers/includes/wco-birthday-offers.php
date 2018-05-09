<?php

class WCO_Birthday_Offers {
    var $set = false;
    /**
     * Define action and filters
     */
    public function __construct() {
        $disable_birthday_field = get_option('disable_birthday_field', '0');
        if ($disable_birthday_field != 1) {
            add_action('woocommerce_register_form', array($this, 'wcoffers_register_birthday_field'));
            add_action('wp_enqueue_scripts', array($this, 'wcoffers_register_front_js_birthday'));
            add_filter('woocommerce_registration_errors', array($this, 'wcoffers_validate_birthday_field'), 10, 3);
            add_action('woocommerce_created_customer', array($this, 'wcoffers_save_extra_register_field'));
            add_action('profile_update', array($this, 'wcoffers_save_extra_register_field'));

            //Show extra birthday field in wordpress
            add_action('show_user_profile', array($this, 'my_show_extra_profile_fields'));
            add_action('edit_user_profile', array($this, 'my_show_extra_profile_fields'));

            //Admin style and js
            add_action('admin_enqueue_scripts', array($this, 'wcoffers_load_admin_profile_script'), 98);
            add_action('woocommerce_cart_calculate_fees', array($this, 'wcoffers_add_birthday_offer_discount'), 13);
        }
        //Adding Cron           
        $disable_birthday_offers = get_option('disable_birthday_offers', '0');
        if ($disable_birthday_offers != 1) {
            if (!wp_next_scheduled('wcoffers_create_birthday_coupon')) {
                wp_schedule_event(strtotime('00:00:00'), 'daily', 'wcoffers_create_birthday_coupon');
            }
            add_action('wcoffers_create_birthday_coupon', array($this, 'wcoffers_create_user_birthday_coupon'));
        }
    }
    
    /**
     * @since 1.0
     * @param string $hook
     * Enqueue admin js and style
     */
    function wcoffers_load_admin_profile_script($hook) {
        if ($hook == 'profile.php') {
            $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';
            wp_register_script('admin_js', plugins_url() . '/wcoffers/assets/js/admin.js', array('jquery', 'jquery-ui-datepicker'), '1.0.0');
            wp_enqueue_script('admin_js');
            wp_register_style('offers-jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version);
            wp_enqueue_style('offers-jquery-ui-style');
        }
    }
    
    /**
     * @since 1.0
     * @param obj $user
     * Add birthday text field on WC registration page
     */
    function my_show_extra_profile_fields($user) {
        ?>
        <h3><?php _e('Birthday', 'wc-offers'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="phone"><?php _e('Birthday', 'wc-offers'); ?></label></th>
                <td>
                    <?php
                    $disable = '';
                    if (get_user_meta($user->ID, 'wc_birthday', TRUE) != '') {
                        $disable = "disabled=disabled";
                    }
                    ?>
                    <input type="text" name="wc_birthday" <?php echo $disable; ?> id="wc_birthday" value="<?php echo esc_attr(get_user_meta($user->ID, 'wc_birthday', TRUE)); ?>" class="regular-text" />
                    <span class="description">Please enter your birthday.</span>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * @since 1.0
     * @param int $customer_id
     * Save birthday field
     */
    function wcoffers_save_extra_register_field($customer_id) {
        if (isset($_POST['wc_birthday'])) {
            update_user_meta($customer_id, 'wc_birthday', sanitize_text_field($_POST['wc_birthday']));
        }
    }
    
    /**
     * @since 1.0
     * @param array $errors
     * @param string $username
     * @param string $email
     * @return array
     * Validate birthday field
     */
    function wcoffers_validate_birthday_field($errors, $username, $email) {
        if (isset($_POST['wc_birthday']) && empty($_POST['wc_birthday'])) {
            $errors->add('wc_birthday', __('Birthday is required!', 'wc-offers'));
        }
        return $errors;
    }
    
    /**
     * @since 1.0
     * Add js and css on front
     */
    function wcoffers_register_front_js_birthday() {
        if (is_account_page()) {
            $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';
            wp_register_script('wcoffers_front_js', plugins_url() . '/wcoffers/assets/js/front.js', array('jquery', 'jquery-ui-datepicker'), '1.0.0');
            wp_enqueue_script('wcoffers_front_js');
            wp_register_style('offers-jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), $jquery_version);
            wp_enqueue_style('offers-jquery-ui-style');
        }
    }
    
    /**
     * @since 1.0
     * Add html of birthday field on WC registration form
     */
    function wcoffers_register_birthday_field() {
        ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="wc_birthday"><?php _e('Birthday', 'wc-offers'); ?>
                <span class="required">*</span></label>
            <input type="text" class="input-text" name="wc_birthday" id="wc_birthday" value="" />
        </p>
              <?php
    }
    
    /**
     * @since 1.0
     * @param obj $cart
     * Add cart discount on user birthday
     */
    function wcoffers_add_birthday_offer_discount($cart) {
        if (is_user_logged_in()) {            
            $disable_birthday_offers = get_option('disable_birthday_offers', '0');
            if ($disable_birthday_offers == 1)
                return;
            $user_id = get_current_user_id();
            $current_date = date("Y-m-d");
            $match_date = explode('-', $current_date, 2);
            $today_date = isset($match_date[1]) ? $match_date[1] : '';
            $disable_birthday_field = get_option('disable_birthday_field', '0');
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
                    if ($offer_birthday_available == 'single_order') {
                        if (strtotime($last_order_date) == strtotime($current_date)) {
                            return;
                        }
                    }                    
                    $birthday_offer_arr = isset($offers_birthday_product[$offer_array_index]) ? $offers_birthday_product[$offer_array_index] : array();                    
                    $offer_discount_type = $birthday_offer_arr['offers_birthday_discount_type'];
                    $functions = new WCOCommonFunctions();
                    $product_ids = isset($birthday_offer_arr['offer_exclude_product']) ? $birthday_offer_arr['offer_exclude_product'] : array();
                    $cat_ids = isset($birthday_offer_arr['offer_exclude_product_category']) ? $birthday_offer_arr['offer_exclude_product_category'] : array();
                    $offers_birthday_enable_cashback = isset($birthday_offer_arr['offers_birthday_enable_cashback']) ? $birthday_offer_arr['offers_birthday_enable_cashback'] : 'no';
                    if ($offer_discount_type == 'percent') {
                        $offer_percentage_discount = isset($birthday_offer_arr['offer_percentage_discount']) ? $birthday_offer_arr['offer_percentage_discount'] : 'quantity';
                        if ($offer_percentage_discount == 'quantity') {
                            $birthday_offer_percentage_quantity = isset($birthday_offer_arr['birthday_offer_percentage_quantity']) ? $birthday_offer_arr['birthday_offer_percentage_quantity'] : array();
                            $cart_total_quantity = WC()->cart->get_cart_contents_count();
                            $exclude_quantity = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'percent_quantity');
                            if ($exclude_quantity) {
                                $cart_total_quantity = $cart_total_quantity - $exclude_quantity;
                            }
                            $applied_offer = '';
                            foreach ($birthday_offer_percentage_quantity as $key => $value) {
                                $minValue = $value['min_quantity'];
                                $maxValue = $value['max_quantity'];
                                if ($maxValue == '' && $cart_total_quantity >= $minValue) {
                                    $applied_offer = $key;
                                } else if ($cart_total_quantity >= $minValue && $cart_total_quantity <= $maxValue) {
                                    $applied_offer = $key;
                                }
                            }
                            if (isset($birthday_offer_percentage_quantity[$applied_offer])) {
                                $discount_amount = isset($birthday_offer_percentage_quantity[$applied_offer]['discount_amount']) ? $birthday_offer_percentage_quantity[$applied_offer]['discount_amount'] : '';
                                if ($discount_amount != '' && $discount_amount < 100) {
                                    $discount_amount = $birthday_offer_percentage_quantity[$applied_offer]['discount_amount'];
                                    $total_dis = $discount_amount / 100;
                                    $excluded_total = $cart->subtotal;
                                    $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                                    if ($exclude_total) {
                                        $excluded_total = $excluded_total - $exclude_total;
                                    }
                                    $discount = $excluded_total * $total_dis;
                                    if ($offers_birthday_enable_cashback == 'yes') {
                                        if($this->set === false){
                                            $functions->wcoffers_add_cashback_row($discount);
                                            $this->set = true;
                                        }
                                    } else {
                                        if (isset($_SESSION['cashback_amount']))
                                            unset($_SESSION['cashback_amount']);
                                        $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                    }
                                }
                            }
                        } else if ($offer_percentage_discount == 'amount') {
                            $birthday_offer_percentage_amount = isset($birthday_offer_arr['birthday_offer_percentage_amount']) ? $birthday_offer_arr['birthday_offer_percentage_amount'] : array();
                            if ($birthday_offer_percentage_amount) {
                                $subtotal = $cart->subtotal;
                                $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                                if ($exclude_total) {
                                    $subtotal = $subtotal - $exclude_total;
                                }
                                $applied_offer = '';
                                foreach ($birthday_offer_percentage_amount as $key => $amount) {
                                    $min_amount = $amount['min_amount'];
                                    $max_amount = $amount['max_amount'];
                                    if ($max_amount == '' && $subtotal >= $min_amount) {
                                        $applied_offer = $key;
                                    } else if ($subtotal >= $min_amount && $subtotal <= $max_amount) {
                                        $applied_offer = $key;
                                    }
                                }
                                if (isset($birthday_offer_percentage_amount[$applied_offer])) {
                                    $discount_amount = isset($birthday_offer_percentage_amount[$applied_offer]['discount_amount']) ? $birthday_offer_percentage_amount[$applied_offer]['discount_amount'] : '';
                                    if ($discount_amount != '' && $discount_amount < 100) {
                                        $discount_amount = $birthday_offer_percentage_amount[$applied_offer]['discount_amount'];
                                        $total_dis = $discount_amount / 100;
                                        $discount = $subtotal * $total_dis;
                                        if ($offers_birthday_enable_cashback == 'yes') {
                                            if($this->set === false){
                                                $functions->wcoffers_add_cashback_row($discount);
                                                $this->set = true;
                                            }                                            
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
                        $birthday_offer_fixed_cart = isset($birthday_offer_arr['birthday_offer_fixed_cart']) ? $birthday_offer_arr['birthday_offer_fixed_cart'] : array();
                        if ($birthday_offer_fixed_cart) {
                            $subtotal = $cart->subtotal;
                            $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                            if ($exclude_total) {
                                $subtotal = $subtotal - $exclude_total;
                            }
                            $applied_offer = '';
                            foreach ($birthday_offer_fixed_cart as $key => $fix_amount) {
                                $min_amount = $fix_amount['min_amount'];
                                $max_amount = $fix_amount['max_amount'];
                                if ($max_amount == '' && $subtotal >= $min_amount) {
                                    $applied_offer = $key;
                                } else if ($subtotal >= $min_amount && $subtotal <= $max_amount) {
                                    $applied_offer = $key;
                                }
                            }
                            if (isset($birthday_offer_fixed_cart[$applied_offer])) {
                                $discount_amount = isset($birthday_offer_fixed_cart[$applied_offer]['discount_amount']) ? $birthday_offer_fixed_cart[$applied_offer]['discount_amount'] : '';
                                if ($discount_amount != '') {
                                    $discount_amount = $birthday_offer_fixed_cart[$applied_offer]['discount_amount'];
                                    $total_dis = $discount_amount;
                                    $discount = $total_dis;
                                    if ($offers_birthday_enable_cashback == 'yes') {
                                        if($this->set === false){
                                            $functions->wcoffers_add_cashback_row($discount);
                                            $this->set = true;
                                        }
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
     * Create birthday offer and send email to user
     */
    function wcoffers_create_user_birthday_coupon() {
        $current_date = date("Y-m-d");
        $match_date = explode('-', $current_date, 2);
        $today_date = isset($match_date[1]) ? $match_date[1] : '';
        $users = get_users(array('order' => 'ASC'));
        $offers_birthday_product = get_option('offers_birthday_product', array());
        if ($offers_birthday_product) {
            foreach ($users as $user) {
                $disable_birthday_field = get_option('disable_birthday_field', '0');
                if ($disable_birthday_field != 1) {
                    $birthday = get_user_meta($user->ID, 'wc_birthday', true);
                } else {
                    $offer_birthday_custom_meta_field = get_option('offer_birthday_custom_meta_field', '');
                    $get_user_meta = get_user_meta($user->ID, $offer_birthday_custom_meta_field, TRUE);
                    $birthday = date('Y-m-d', strtotime($get_user_meta));
                }
                if ($birthday) {
                    $birthday_date = explode('-', $birthday, 2);
                    $current_user_bd = isset($birthday_date[1]) ? $birthday_date[1] : '';
                } else {
                    $current_user_bd = '';
                }
                if ($current_user_bd == $today_date) {
                    $customer_orders = get_posts(array(
                        'numberposts' => -1,
                        'meta_key' => '_customer_user',
                        'meta_value' => $user->ID,
                        'post_type' => wc_get_order_types(),
                        'post_status' => array_keys(wc_get_order_statuses()),
                    ));
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

                        //Send birthday offer email
                        $email_template = new WCO_Email_Templates();
                        $email_template->wcoffers_birthday_email($user->ID, $birthday_offer_arr);
                    }
                }
            }
        }
    }

}

new WCO_Birthday_Offers();
