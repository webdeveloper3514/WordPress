<?php

/**
 * Send email to users
 */
class WCO_Email_Templates {

    var $headers = array('Content-Type: text/html; charset=UTF-8');

    public function __construct() {
        add_action('admin_init', array($this, 'wcoffers_save_email_templates'), 20);
    }

    /**
     * @since 1.0
     * Save email templates
     */
    function wcoffers_save_email_templates() {
        $current_offer_page = isset($_GET['page']) && $_GET['page'] == 'wc-offers' && isset($_GET['tab']) ? $_GET['tab'] : 'new_reg';
        if ($current_offer_page == 'email-templates') {
            if (isset($_POST['save_offers']) && isset($_POST['wpoffers_nonce_field']) && wp_verify_nonce($_POST['wpoffers_nonce_field'], 'wpoffers_nonce_data')) {
                if (isset($_POST['reg_email_subject'])) {
                    update_option('reg_email_subject', stripslashes($_POST['reg_email_subject']));
                }
                if (isset($_POST['reg_email_template'])) {
                    update_option('reg_email_template', stripslashes($_POST['reg_email_template']));
                }
                if (isset($_POST['freebies_email_subject'])) {
                    update_option('freebies_email_subject', stripslashes($_POST['freebies_email_subject']));
                }
                if (isset($_POST['freebies_email_template'])) {
                    update_option('freebies_email_template', stripslashes($_POST['freebies_email_template']));
                }
                if (isset($_POST['existing_customer_email_subject'])) {
                    update_option('existing_customer_email_subject', stripslashes($_POST['existing_customer_email_subject']));
                }
                if (isset($_POST['existing_customer_email_template'])) {
                    update_option('existing_customer_email_template', stripslashes($_POST['existing_customer_email_template']));
                }
                if (isset($_POST['birthday_email_subject'])) {
                    update_option('birthday_email_subject', stripslashes($_POST['birthday_email_subject']));
                }
                if (isset($_POST['birthday_email_template'])) {
                    update_option('birthday_email_template', stripslashes($_POST['birthday_email_template']));
                }
                if (isset($_POST['cashback_email_subject'])) {
                    update_option('cashback_email_subject', stripslashes($_POST['cashback_email_subject']));
                }
                if (isset($_POST['cashback_email_template'])) {
                    update_option('cashback_email_template', stripslashes($_POST['cashback_email_template']));
                }
            }
        }
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * @param string $expiry_date
     * Send mail when registration offer is available
     */
    public function wcoffers_registration_email($customer_id, $expiry_date = '') {
        $user_info = get_userdata($customer_id);
        $reg_email_subject = get_option('reg_email_subject', '');
        $email_message = get_option('reg_email_template', '');
        $replace = $this->wcoffers_calculate_registration_discount($customer_id);
        $subject = do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $reg_email_subject));
        $message = preg_replace("/\r\n|\r|\n/", '<br/>', do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $email_message)));
        wp_mail($user_info->user_email, $subject, $message, $this->headers);
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * @return array
     * Return minimum and maximum amount & discount from offers
     */
    public function wcoffers_calculate_registration_discount($customer_id) {
        $user_info = get_userdata($customer_id);
        $offer_discount_type = get_option('offer_discount_type', 'percent');
        $replace = array(
            $user_info->user_login
        );
        $result = array();
        if ($offer_discount_type == 'percent') {
            $sign = 'percent';
            $offer_percentage_discount = get_option('offer_percentage_discount', 'quantity');
            if ($offer_percentage_discount == 'quantity') {
                $result = get_option('registration_offer_percentage_quantity', array());
            } else {
                $result = get_option('registration_offer_percentage_amount', array());
            }
        } else {
            $sign = 'amount';
            $result = get_option('registration_offer_fixed_cart', array());
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
        if($sign == 'percent'){
            $min_discount = $min_discount.'%';
            $max_amount = $max_discount.'%';
        }else{
            $min_discount = '$'.$min_discount;
            $max_amount = '$'.$max_discount;
        }
        $replace[] = $min_min_amount;
        $replace[] = $max_amount;
        $replace[] = $min_discount;
        $replace[] = $max_min_amount;
        return $replace;
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * @param string $expiry_date
     * Send email for second purchase
     */
    public function wcoffers_second_purchase_email($customer_id, $expiry_date = '') {
        $user_info = get_userdata($customer_id);
        $reg_email_subject = get_option('freebies_email_subject', '');
        $email_message = get_option('freebies_email_template', '');
        $replace = $this->wcoffers_calculate_second_purchase_discount($customer_id);
        $subject = do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $reg_email_subject));
        $message = preg_replace("/\r\n|\r|\n/", '<br/>', do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $email_message)));
        wp_mail($user_info->user_email, $subject, $message, $this->headers);
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * @return array
     * Return minimum and maximum amount & discount from offers
     */
    public function wcoffers_calculate_second_purchase_discount($customer_id) {
        $user_info = get_userdata($customer_id);
        $offer_discount_type = get_option('offers_freebies_discount_type', 'percent');
        $replace = array(
            $user_info->user_login
        );
        $result = array();
        if ($offer_discount_type == 'percent') {
            $sign = 'percent';
            $offer_percentage_discount = get_option('offers_freebies_discount', 'quantity');
            if ($offer_percentage_discount == 'quantity') {
                $result = get_option('second_purchase_offer_percentage_quantity', array());
            } else {
                $result = get_option('second_purchase_offer_percentage_amount', array());
            }
        } else {
            $sign = 'amount';
            $result = get_option('second_purchase_offer_fixed_cart', array());
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
        if($sign == 'percent'){
            $min_discount = $min_discount.'%';
            $max_amount = $max_discount.'%';
        }else{
            $min_discount = '$'.$min_discount;
            $max_amount = '$'.$max_discount;
        }
        $replace[] = $min_min_amount;
        $replace[] = $max_amount;
        $replace[] = $min_discount;
        $replace[] = $max_min_amount;
        return $replace;
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * @param string $expiry_date
     * Send email for existing customer
     */
    public function wcoffers_existing_customer_email($customer_id, $expiry_date = '') {
        $user_info = get_userdata($customer_id);
        $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
        $eu_offer_key = '';
        if ($wcoffers_existing_customer) {
            foreach ($wcoffers_existing_customer as $key => $single_offer) {
                if (isset($single_offer['user_ids']) && in_array($customer_id, $single_offer['user_ids'])) {
                    $eu_offer_key = $key;
                }
            }
        }
        $eu_offer_data = isset($wcoffers_existing_customer[$eu_offer_key]) ? $wcoffers_existing_customer[$eu_offer_key] : array();
        if (empty($eu_offer_data)) {
            return;
        }
        $replace = $this->wcoffers_calculate_existing_customer_discount($customer_id, $eu_offer_data);
        $ec_email_subject = get_option('existing_customer_email_subject', '');
        $email_message = get_option('existing_customer_email_template', '');
        $subject = do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $ec_email_subject));
        $message = preg_replace("/\r\n|\r|\n/", '<br/>', do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $email_message)));
        wp_mail($user_info->user_email, $subject, $message, $this->headers);
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * Return minimum and maximum amount & discount from offers
     */
    public function wcoffers_calculate_existing_customer_discount($customer_id, $eu_offer_data) {
        $user_info = get_userdata($customer_id);
        $replace = array(
            $user_info->user_login
        );
        $result = array();
        $offer_discount_type = isset($eu_offer_data['discount_type']) ? $eu_offer_data['discount_type'] : 'percent';
        if ($offer_discount_type == 'percent') {
            $sign = 'percent';
            $offer_percentage_discount = isset($eu_offer_data['percentage_discount']) ? $eu_offer_data['percentage_discount'] : 'quantity';
            if ($offer_percentage_discount == 'quantity') {
                $result = isset($eu_offer_data['percentage_quantity']) ? $eu_offer_data['percentage_quantity'] : array();
            } else if ($offer_percentage_discount == 'amount') {
                $result = isset($eu_offer_data['percentage_amount']) ? $eu_offer_data['percentage_amount'] : array();
            }
        } else if ($offer_discount_type == 'fixed_cart') {
            $sign = 'amount';
            $result = isset($eu_offer_data['fixed_cart']) ? $eu_offer_data['fixed_cart'] : array();
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
        if($sign == 'percent'){
            $min_discount = $min_discount.'%';
            $max_amount = $max_discount.'%';
        }else{
            $min_discount = '$'.$min_discount;
            $max_amount = '$'.$max_discount;
        }
        $replace[] = $min_min_amount;
        $replace[] = $max_amount;
        $replace[] = $min_discount;
        $replace[] = $max_min_amount;
        return $replace;
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * Send email on birthday
     */
    public function wcoffers_birthday_email($customer_id, $offer = array()) {
        $user_info = get_userdata($customer_id);
        $replace = $this->wcoffers_calculate_birthday_offer_discount($customer_id, $offer);
        $ec_email_subject = get_option('birthday_email_subject', '');
        $email_message = get_option('birthday_email_template', '');

        $subject = do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $ec_email_subject));
        $message = preg_replace("/\r\n|\r|\n/", '<br/>', do_shortcode(str_replace(array('{customer_name}', '{minimum_offer}', '{maximum_discount}', '{minimum_discount}', '{maximum_offer}'), $replace, $email_message)));
        wp_mail($user_info->user_email, $subject, $message, $this->headers);
    }

    /**
     * @since 1.0
     * @param int $customer_id
     * @param array $offer
     * @return array
     * Return minimum and maximum amount & discount from offers
     */
    public function wcoffers_calculate_birthday_offer_discount($customer_id, $offer) {
        $user_info = get_userdata($customer_id);
        $replace = array(
            $user_info->user_login
        );
        $result = array();
        $birthday_offer_arr = $offer;
        $offer_discount_type = $birthday_offer_arr['offers_birthday_discount_type'];
        if ($offer_discount_type == 'percent') {
            $sign = 'percent';
            $offer_percentage_discount = isset($birthday_offer_arr['offer_percentage_discount']) ? $birthday_offer_arr['offer_percentage_discount'] : 'quantity';
            if ($offer_percentage_discount == 'quantity') {
                $result = isset($birthday_offer_arr['birthday_offer_percentage_quantity']) ? $birthday_offer_arr['birthday_offer_percentage_quantity'] : array();
            } else if ($offer_percentage_discount == 'amount') {
                $result = isset($birthday_offer_arr['birthday_offer_percentage_amount']) ? $birthday_offer_arr['birthday_offer_percentage_amount'] : array();
            }
        } else if ($offer_discount_type == 'fixed_cart') {
            $sign = 'amount';
            $result = isset($birthday_offer_arr['birthday_offer_fixed_cart']) ? $birthday_offer_arr['birthday_offer_fixed_cart'] : array();
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
        if($sign == 'percent'){
            $min_discount = $min_discount.'%';
            $max_amount = $max_discount.'%';
        }else{
            $min_discount = '$'.$min_discount;
            $max_amount = '$'.$max_discount;
        }
        $replace[] = $min_min_amount;
        $replace[] = $max_amount;
        $replace[] = $min_discount;
        $replace[] = $max_min_amount;
        return $replace;
    }

    /**
     * @since 1.0
     * @param int $user_id
     * @param int $discount
     * @param int $order_id
     * Send mail when cashback is added
     */
    public function wcoffers_cashback_email($user_id, $discount, $order_id) {
        $user_info = get_userdata($user_id);
        $wallet_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));
        $cashback_email_subject = get_option('cashback_email_subject', '');
        $email_message = get_option('cashback_email_template', '');
        $subject = do_shortcode(str_replace(array('{customer_name}', '{cashback_amount}', '{wallet_balance}', '{order_id}'), array($user_info->user_login, $discount, $wallet_balance, $order_id), $cashback_email_subject));
        $message = preg_replace("/\r\n|\r|\n/", '<br/>', do_shortcode(str_replace(array('{customer_name}', '{cashback_amount}', '{wallet_balance}', '{order_id}'), array($user_info->user_login, $discount, $wallet_balance, $order_id), $email_message)));
        wp_mail($user_info->user_email, $subject, $message, $this->headers);
    }

}

new WCO_Email_Templates();
