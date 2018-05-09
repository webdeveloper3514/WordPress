<p class="current-wallet-balance">
    <?php
    $wallet_balance = get_user_meta($user_id, 'wco_wl_balance', true);
    _e('Wallet Balance: ', 'wc-offers');
    echo wc_price($wallet_balance);
    ?>
</p>
<?php
add_filter( 'woocommerce_cart_needs_payment', '__return_true', 20 );
add_filter( 'woocommerce_checkout_show_terms', '__return_false', 20 );
$user_profile_complete = $functions->wcoffers_check_user_profile($user_id);
if ($user_profile_complete) {
    $min = get_option('wcoffer_deposit_min_amount', 0);
    $max = get_option('wcoffer_deposit_max_amount', 0);
    $currency = get_woocommerce_currency();
    $payment = array('checkout' => WC()->checkout(),
        'available_gateways' => WC()->payment_gateways()->get_available_payment_gateways(),
        'order_button_text' => apply_filters('woocommerce_order_button_text', __('Place order', 'wc-offers')));
    ?>
    <div class="wallet_deposit_wrapper">
        <?php
        $price_format = get_woocommerce_price_format();
        ?>
        <form id="add-deposit" class="checkout woocommerce-checkout" name="wcoffers_add_deposit" method="post" >
            <p>
                <label for="amount_deposit"><?php _e('Amount', 'wc-offers'); ?></label>                
                <?php echo sprintf($price_format, '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol($currency) . '</span>', ''); ?>
                <input type="number" name="deposit_amount" placeholder="Enter amount" min="<?php echo $min; ?>" max="<?php echo $max; ?>" value="" step="any" style="display: inline-block;width: auto;">
            </p>
            <?php wc_get_template('checkout/payment.php', $payment); ?>
        </form>
    </div>
    <?php
} else {
    $myaccount = wc_get_page_permalink('myaccount');
    $billing_url = esc_url($myaccount . 'edit-address/billing/');

    $button = sprintf('<a href="%s">%s</a>', $billing_url, __('Complete your profile', 'wc-offers'));
    $message = sprintf(__('To add new deposit, you must  %s first.', 'wc-offers'), $button);
    $error_messages = array($message);
    $messages['messages'] = $error_messages;
    wc_get_template('notices/error.php', $messages);
}
remove_filter( 'woocommerce_checkout_show_terms', '__return_false', 20 );
remove_filter( 'woocommerce_cart_needs_payment', '__return_true',20 );