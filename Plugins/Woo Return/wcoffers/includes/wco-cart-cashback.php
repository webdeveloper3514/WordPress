<?php

class WCO_Cashback_Offers {

    /**
     * Define action and filters
     */
    public function __construct() {
        if (!session_id()) {
            session_start();
        }
        add_action('woocommerce_before_cart', array($this, 'wcoffers_wallet_notice_cart_checkout_page'));
        add_action('woocommerce_before_checkout_form', array($this, 'wcoffers_wallet_notice_cart_checkout_page'));
        add_action('wp', array($this, 'wcoffers_apply_wallet_fund'));
        add_action('woocommerce_get_order_item_totals', array($this, 'wcoffers_wc_order_items_table'), 99, 3);
        add_action('woocommerce_admin_order_totals_after_tax', array($this, 'wcoffers_show_wallet_discount'), 10, 1);


        if (isset($_SESSION['enable_wallet_discount']) && $_SESSION['enable_wallet_discount'] == 'enable') {
            add_action('woocommerce_cart_totals_before_order_total', array($this, 'wcoffers_add_wallet_discount_html'));
            add_action('woocommerce_review_order_before_order_total', array($this, 'wcoffers_add_wallet_discount_html'));
            add_filter('woocommerce_calculated_total', array($this, 'wcoffers_action_cart_calculate_totals'), 10, 2);
            add_action('woocommerce_new_order', array($this, 'wcoffers_remove_funds_from_user'), 99, 1);
        }
    }
    
    /**
     * @since 1.0
     * @param int $order_id
     * Add wallet discount tr in cart and checkout page
     */
    public function wcoffers_show_wallet_discount($order_id) {
        $wallet = get_post_meta($order_id, '_order_wallet_discount', true);
        if ($wallet) {
            ?>
            <tr>
                <td class="label"><?php _e( 'Wallet Discount', 'wc-offers' ); ?>:</td>
                <td width="1%"></td>
                <td class="total">
                    <?php echo wc_price($wallet); ?>
                </td>
            </tr>
            <?php
        }
    }
    
    /**
     * @since 1.0
     * @param array $total_rows
     * @param obj $order
     * @param string $tax_display
     * @return array
     * Add Wallet Discount message on order receipt
     */
    public function wcoffers_wc_order_items_table($total_rows, $order, $tax_display) {
        $order_id = $order->get_id();
        $wallet = get_post_meta($order_id, '_order_wallet_discount', true);
        $subtotal_index = array_search("cart_subtotal",array_keys($total_rows));
        if(count($total_rows) <= 2 ){
            $subtotal_index = $subtotal_index + 1;
        }else{
            $subtotal_index = 2;
        }
        if ($wallet) {
            $res = array_slice($total_rows, 0, $subtotal_index, true) +
                    array("wallet_discount" => array('label' => 'Wallet Discount:', 'value' => '-' . wc_price($wallet))) +
                    array_slice($total_rows, $subtotal_index, count($total_rows) - 1, true);
            return $res;
        }
        return $total_rows;
    }
    
    /**
     * @since 1.0
     * @param int $order_id
     * Update user wallet balance
     */
    public function wcoffers_remove_funds_from_user($order_id) {
        if (is_user_logged_in()) {
            if (!session_id()) {
                session_start();
            }
            if (isset($_SESSION['enable_wallet_discount'])) {
                $user_id = get_current_user_id();
                $wallet_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));
                $remaining_balance = $wallet_balance - $_SESSION['wallet_discount'];
                update_user_meta($user_id, 'wco_wl_balance', $remaining_balance);
                update_post_meta($order_id, '_order_wallet_discount', $_SESSION['wallet_discount']);
                unset($_SESSION['enable_wallet_discount']);
                unset($_SESSION['wallet_discount']);
            }
        }
    }
    
    /**
     * @since 1.0
     * @param int $cart_total
     * @param obj $cart_object
     * @return int
     * Return cart total after removing wallet balance
     */
    public function wcoffers_action_cart_calculate_totals($cart_total, $cart_object) {
        if (is_admin() && !defined('DOING_AJAX'))
            return;

        $user_id = get_current_user_id();
        $wallet_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));
        if ($cart_total >= $wallet_balance) {
            $amount = $wallet_balance;
        } else {
            $amount = $cart_total;
        }
        $_SESSION['wallet_discount'] = $amount;
        return $cart_total - $amount;
    }
    
    /**
     * @since 1.0
     * @return HTML
     * Display wallet fund apply form on cart and checkout page
     */
    public function wcoffers_wallet_notice_cart_checkout_page() {
        if (!is_user_logged_in() || (isset($_SESSION['enable_wallet_discount']) && $_SESSION['enable_wallet_discount'] == 'enable' ))
            return;
        $user_id = get_current_user_id();
        $wallet_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));
        ?>
        <div class="woocommerce-info wc-account-funds-apply-notice">
            <form class="wc-account-funds-apply" method="post">
                <input type="submit" class="button wc-account-funds-apply-button" name="wc_account_funds_apply" value="Use Wallet Funds">You have <strong><?php echo $wallet_balance; ?></strong> balance left in your wallet.
        <?php wp_nonce_field('wc_account_funds_apply'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * @since 1.0
     * Apply wallet fund on cart and checkout page
     */
    public function wcoffers_apply_wallet_fund() {
        if (isset($_POST['wc_account_funds_apply']) && $_POST['wc_account_funds_apply'] == 'Use Wallet Funds') {
            if (wp_verify_nonce($_POST['_wpnonce'], 'wc_account_funds_apply')) {
                $user_id = get_current_user_id();
                $wallet_balance = floatval(get_user_meta($user_id, "wco_wl_balance", true));
                if ($wallet_balance) {
                    if (!session_id()) {
                        session_start();
                    }
                    $_SESSION['enable_wallet_discount'] = 'enable';
                    $_SESSION['wallet_discount'] = 0;
                    wp_redirect($_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
        if (isset($_GET['remove_account_funds']) && $_GET['remove_account_funds'] == 1 && (is_checkout() || is_cart())) {
            if (!session_id()) {
                session_start();
            }
            if (isset($_SESSION['enable_wallet_discount'])) {
                unset($_SESSION['enable_wallet_discount']);
                unset($_SESSION['wallet_discount']);
                $url = remove_query_arg('remove_account_funds', $_SERVER['REQUEST_URI']);
                wp_redirect($url);
                exit;
            }
        }
    }
    
    /**
     * @since 1.0
     * Add wallet fund tr
     */
    public function wcoffers_add_wallet_discount_html() {
        $discount_amount = $this->wcoffers_wallet_discount_amount();
        if (is_checkout()) {
            $page_id = wc_get_page_id('checkout');
            $server_url = get_permalink($page_id);
        } else {
            $server_url = $_SERVER['REQUEST_URI'];
        }
        ?>
        <tr class="order-discount account-funds-discount">
            <th><?php _e('Wallet Funds','wc-offers'); ?></th>
            <td>-<?php echo wc_price($discount_amount); ?> <a href="<?php echo add_query_arg('remove_account_funds', '1', $server_url); ?>">[Remove]</a></td>
        </tr>
        <?php
    }
    
    /**
     * @since 1.0
     * @global object $woocommerce
     * @return int
     * Return wallet discount balance
     */
    function wcoffers_wallet_discount_amount() {
        global $woocommerce;
        $cart_total = $woocommerce->cart->cart_contents_total;
        if (!session_id()) {
            session_start();
        }
        return $_SESSION['wallet_discount'];
    }

}

new WCO_Cashback_Offers();
