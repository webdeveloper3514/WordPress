<?php
/**
 * Admin View: Offers
 */
if (!defined('ABSPATH')) {
    exit;
}
$current_offer_tab = isset($_GET['page']) && $_GET['page'] == 'wc-offers' && isset($_GET['tab']) ? $_GET['tab'] : 'new_reg';
$o_name = 'new_reg';
$o_label = __('New Registration', 'wc-offers');
global $wpoffers_success;
?>
<div class="wrap woocommerce wcoffers-main">
    <h2>
        <span><?php _e('WooCommerce Offers', 'wc-offers'); ?></span>		
    </h2>
    <form method="post" id="mainform" action="" enctype="multipart/form-data">
        <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=' . $o_name) . '" class="nav-tab ' . ( $current_offer_tab == $o_name ? 'nav-tab-active' : '' ) . '">' . $o_label . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=freebies') . '" class="nav-tab ' . ( $current_offer_tab == 'freebies' ? 'nav-tab-active' : '' ) . '">' . __('Second Purchase', 'wc-offers') . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=old-users') . '" class="nav-tab ' . ( $current_offer_tab == 'old-users' ? 'nav-tab-active' : '' ) . '">' . __('Inactive Customers', 'wc-offers') . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=existing-users') . '" class="nav-tab ' . ( $current_offer_tab == 'existing-users' ? 'nav-tab-active' : '' ) . '">' . __('Existing Customers', 'wc-offers') . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=individual-offer') . '" class="nav-tab ' . ( $current_offer_tab == 'individual-offer' ? 'nav-tab-active' : '' ) . '">' . __('Individual Offers', 'wc-offers') . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=birthday-offer') . '" class="nav-tab ' . ( $current_offer_tab == 'birthday-offer' ? 'nav-tab-active' : '' ) . '">' . __('Birthday Offers', 'wc-offers') . '</a>';            
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=upsell-offer') . '" class="nav-tab ' . ( $current_offer_tab == 'upsell-offer' ? 'nav-tab-active' : '' ) . '">' . __('Upsell Offers', 'wc-offers') . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=add-deposit') . '" class="nav-tab ' . ( $current_offer_tab == 'add-deposit' ? 'nav-tab-active' : '' ) . '">' . __('Deposit Setting', 'wc-offers') . '</a>';
            echo '<a href="' . admin_url('admin.php?page=wc-offers&tab=email-templates') . '" class="nav-tab ' . ( $current_offer_tab == 'email-templates' ? 'nav-tab-active' : '' ) . '">' . __('Email Templates', 'wc-offers') . '</a>';
            ?>
        </nav>
        <div class="wcoffers_backend_setting_wrapper <?php
        if ($current_offer_tab == 'individual-offer' || $current_offer_tab == 'upsell-offer' || $current_offer_tab == 'email-templates') {
            echo 'wcoffers_io_wrap';
        }
        ?>">
                 <?php
                 if ((isset($_GET['deleted']) && $_GET['deleted'] == 'successfully') || (isset($_GET['updated']) && $_GET['updated'] == 'successfully')) {
                     if (isset($_GET['deleted'])) {
                         $message = __('Offer deleted successfully', 'wc-offers');
                     } else {
                         $message = __('Offer created successfully', 'wc-offers');
                     }
                     ?>
                <div id="message" class="updated notice">
                    <p><strong><?php echo $message; ?></strong></p>
                </div>
                <?php
            }
            if ($wpoffers_success) {
                ?>
                <div id="message" class="updated notice">
                    <p><strong><?php echo $wpoffers_success; ?></strong></p>
                </div>
                <?php
            }
            if ($current_offer_tab == 'new_reg') {
                ?>
                <h3><?php echo _e('Discount for New Registration', 'wc-offers'); ?></h3>
                <div id="poststuff">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Discount Settings', 'wc-offers'); ?></span></h2>
                        <div class="inside">
                            <table class="registration-table">
                                <tbody>
                                    <tr>
                                        <td><?php _e('Enable/Disable', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $disable_new_reg = get_option('disable_new_reg', '1');
                                            ?>
                                            <label>
                                                <input type="radio" value="1" name="disable_new_reg" <?php checked($disable_new_reg, '1'); ?>><?php _e('Enable', 'wc-offers'); ?>
                                            </label>
                                            <br/>
                                            <label>
                                                <input type="radio" value="disable_all" name="disable_new_reg" <?php checked($disable_new_reg, 'disable_all'); ?>><?php _e('Disable for all', 'wc-offers'); ?>
                                            </label>
                                            <br/>
                                            <label>
                                                <input type="radio" value="disable_keep_existing" name="disable_new_reg" <?php checked($disable_new_reg, 'disable_keep_existing'); ?>><?php _e('Disable offer and keep existing offers', 'wc-offers'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php _e('Do you want Cashback?', 'wc-offers'); ?><span class="wco-help-tip"></span>
                                        </td>
                                        <td>
                                            <?php
                                            $offer_enable_cashback = get_option('offer_enable_cashback', 'no');
                                            ?>
                                            <label>
                                                <input type="radio" value="yes" class="offer_enable_cashback" name="offer_enable_cashback" <?php checked($offer_enable_cashback, 'yes'); ?>><?php _e('Yes', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="no" class="offer_enable_cashback" name="offer_enable_cashback" <?php checked($offer_enable_cashback, 'no'); ?>><?php _e('No', 'wc-offers'); ?>
                                            </label>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('if yes then discount amount will be added in wallet', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Discount type', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offer_discount_type = get_option('offer_discount_type');
                                            ?>
                                            <select id="offer_discount_type" name="offer_discount_type" class="select short" style="">
                                                <option value="percent" <?php selected($offer_discount_type, 'percent'); ?>><?php _e('Percentage discount', 'wc-offers'); ?></option>
                                                <option value="fixed_cart"<?php selected($offer_discount_type, 'fixed_cart'); ?>><?php _e('Fixed cart discount', 'wc-offers'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="discount_type_percent">
                                        <td><?php _e('Percentage Discount', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offer_percentage_discount = get_option('offer_percentage_discount', 'quantity');
                                            ?>
                                            <label>
                                                <input type="radio" value="quantity" class="offer_percentage_discount" name="offer_percentage_discount" <?php checked($offer_percentage_discount, 'quantity'); ?>><?php _e('Quantity', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="amount" class="offer_percentage_discount" name="offer_percentage_discount" <?php checked($offer_percentage_discount, 'amount'); ?>><?php _e('Amount', 'wc-offers'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Coupon expiry time', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <?php
                                            $offer_discount_expiry_number = get_option('offer_discount_expiry_number', '');
                                            $offer_discount_expiry_time = get_option('offer_discount_expiry_time', 'day');
                                            ?>
                                            <input type="number" class="textNumberOnly" name="offer_discount_expiry_number" id="offer_discount_expiry_number" value="<?php echo $offer_discount_expiry_number; ?>" placeholder="0">
                                            <select name="offer_discount_expiry_time" id="offer_discount_expiry_time">
                                                <option value="day" <?php selected($offer_discount_expiry_time, 'day'); ?>><?php _e('Day', 'wc-offers'); ?></option>
                                                <option value="week" <?php selected($offer_discount_expiry_time, 'week'); ?>><?php _e('Week', 'wc-offers'); ?></option>
                                                <option value="month" <?php selected($offer_discount_expiry_time, 'month'); ?>><?php _e('Month', 'wc-offers'); ?></option>
                                                <option value="year" <?php selected($offer_discount_expiry_time, 'year'); ?>><?php _e('Year', 'wc-offers'); ?></option>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('Leave empty or add zero to expire this offer until user use this offer', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr class="wco-registration-percentage-quantity-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-registration-percentage-quantity">+ Add Rule</div>
                                            <table class="wco-registration-percentage-quantity-table">
                                                <tr>
                                                    <td><?php _e('Minimum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $registration_offer_percentage_quantity = get_option('registration_offer_percentage_quantity', array());
                                                if ($registration_offer_percentage_quantity) {
                                                    $count = 0;
                                                    foreach ($registration_offer_percentage_quantity as $single_pre_quantity) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-registration-quantity-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_quantity" class="textNumberOnly min_quantity" min="0" max="100" type="text" value="<?php echo $single_pre_quantity['min_quantity']; ?>" name="registration_offer_percentage_quantity[<?php echo $count; ?>][min_quantity]"></td>
                                                            <td><input data-name="max_quantity" class="textNumberOnly max_quantity" min="0" max="100" type="text" value="<?php echo $single_pre_quantity['max_quantity']; ?>" name="registration_offer_percentage_quantity[<?php echo $count; ?>][max_quantity]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_quantity['discount_amount']; ?>" min="0" max="100" name="registration_offer_percentage_quantity[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-reg-delete-quantity-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-registration-percentage-amount-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-registration-percentage-amount">+ Add Rule</div>
                                            <table class="wco-registration-percentage-amount-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $registration_offer_percentage_amount = get_option('registration_offer_percentage_amount', array());
                                                if ($registration_offer_percentage_amount) {
                                                    $count = 0;
                                                    foreach ($registration_offer_percentage_amount as $single_pre_amount) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-registration-amount-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_amount['min_amount']; ?>" name="registration_offer_percentage_amount[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_amount['max_amount']; ?>" name="registration_offer_percentage_amount[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_amount['discount_amount']; ?>" min="0" max="100" name="registration_offer_percentage_amount[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-reg-delete-amount-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-registration-fixed_cart-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-registration-fixed_cart">+ Add Rule</div>
                                            <table class="wco-registration-fixed_cart-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Discount Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $registration_offer_fixed_cart = get_option('registration_offer_fixed_cart', array());
                                                if ($registration_offer_fixed_cart) {
                                                    $count = 0;
                                                    foreach ($registration_offer_fixed_cart as $single_pre_fixed_cart) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-registration-fixed_cart-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['min_amount']; ?>" name="registration_offer_fixed_cart[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['max_amount']; ?>" name="registration_offer_fixed_cart[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_fixed_cart['discount_amount']; ?>" min="0" name="registration_offer_fixed_cart[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-reg-delete-fixed_cart-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="reg_offer_exclude_product[]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                <?php
                                                $product_ids = get_option('reg_offer_exclude_product', array());
                                                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                                $loop = new WP_Query($args);
                                                if ($loop->have_posts()) {
                                                    while ($loop->have_posts()) : $loop->the_post();
                                                        echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                    endwhile;
                                                    wp_reset_query();
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="reg_offer_exclude_product_category[]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                <?php
                                                $cat_ids = get_option('reg_offer_exclude_product_category', array());
                                                $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                if ($categories) {
                                                    foreach ($categories as $cat) {
                                                        echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>                
                </div>            
            <?php } else if ($current_offer_tab == 'old-users') {
                ?>
                <h2><?php echo _e('Discount for Inactive Customers', 'wc-offers'); ?></h2>
                <div id="poststuff">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Discount Settings', 'wc-offers'); ?></span></h2>
                        <div class="inside">
                            <table class="inactive_customer_offer_table">
                                <tbody>
                                    <tr>
                                        <td><?php _e('Disable offer'); ?></td>
                                        <td>
                                            <?php
                                            $disable_exist_customer = get_option('disable_exist_customer', '0');
                                            ?>
                                            <input type="checkbox" value="1" name="disable_exist_customer" id="disable_exist_customer" <?php checked($disable_exist_customer, 1); ?>>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Last Order Time', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offer_old_user_last_order_number = get_option('offer_old_user_last_order_number', '');
                                            $offer_old_user_last_order_time = get_option('offer_old_user_last_order_time', 'day');
                                            ?>
                                            <input type="number" class="textNumberOnly" name="offer_old_user_last_order_number" id="offer_old_user_last_order_number" value="<?php echo $offer_old_user_last_order_number; ?>" placeholder="0">
                                            <select name="offer_old_user_last_order_time" id="offer_old_user_last_order_time">
                                                <option value="day" <?php selected($offer_old_user_last_order_time, 'day'); ?>><?php _e('Day', 'wc-offers'); ?></option>
                                                <option value="week" <?php selected($offer_old_user_last_order_time, 'week'); ?>><?php _e('Week', 'wc-offers'); ?></option>
                                                <option value="month" <?php selected($offer_old_user_last_order_time, 'month'); ?>><?php _e('Month', 'wc-offers'); ?></option>
                                                <option value="year" <?php selected($offer_old_user_last_order_time, 'year'); ?>><?php _e('Year', 'wc-offers'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php _e('Do you want Cashback?', 'wc-offers'); ?><span class="wco-help-tip"></span>
                                        </td>
                                        <td>
                                            <?php
                                            $offer_old_user_enable_cashback = get_option('offer_old_user_enable_cashback', 'no');
                                            ?>
                                            <label>
                                                <input type="radio" value="yes" class="offer_old_user_enable_cashback" name="offer_old_user_enable_cashback" <?php checked($offer_old_user_enable_cashback, 'yes'); ?>><?php _e('Yes', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="no" class="offer_old_user_enable_cashback" name="offer_old_user_enable_cashback" <?php checked($offer_old_user_enable_cashback, 'no'); ?>><?php _e('No', 'wc-offers'); ?>
                                            </label>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('if yes then discount amount will be added in wallet', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Discount type', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offer_old_user_discount_type = get_option('offers_old_user_discount_type');
                                            ?>
                                            <select id="offers_old_user_discount_type" name="offers_old_user_discount_type" class="select short" style="">
                                                <option value="percent" <?php selected($offer_old_user_discount_type, 'percent'); ?>><?php _e('Percentage discount', 'wc-offers'); ?></option>
                                                <option value="fixed_cart"<?php selected($offer_old_user_discount_type, 'fixed_cart'); ?>><?php _e('Fixed cart discount', 'wc-offers'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="discount_type_percent">
                                        <td><?php _e('Percentage Discount', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offers_old_user_percentage_discount = get_option('offers_old_user_percentage_discount', 'quantity');
                                            ?>
                                            <label>
                                                <input type="radio" value="quantity" class="offers_old_user_percentage_discount" name="offers_old_user_percentage_discount" <?php checked($offers_old_user_percentage_discount, 'quantity'); ?>><?php _e('Quantity', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="amount" class="offers_old_user_percentage_discount" name="offers_old_user_percentage_discount" <?php checked($offers_old_user_percentage_discount, 'amount'); ?>><?php _e('Amount', 'wc-offers'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Coupon expiry date', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <?php
                                            $offer_old_user_expiry_number = get_option('offer_old_user_expiry_number', '');
                                            $offer_old_user_expiry_time = get_option('offer_old_user_expiry_time', 'day');
                                            ?>
                                            <input type="number" class="textNumberOnly" name="offer_old_user_expiry_number" min="0" id="offer_old_user_expiry_number" value="<?php echo $offer_old_user_expiry_number; ?>" placeholder="0">
                                            <select name="offer_old_user_expiry_time" id="offer_old_user_expiry_time">
                                                <option value="day" <?php selected($offer_old_user_expiry_time, 'day'); ?>><?php _e('Day', 'wc-offers'); ?></option>
                                                <option value="week" <?php selected($offer_old_user_expiry_time, 'week'); ?>><?php _e('Week', 'wc-offers'); ?></option>
                                                <option value="month" <?php selected($offer_old_user_expiry_time, 'month'); ?>><?php _e('Month', 'wc-offers'); ?></option>
                                                <option value="year" <?php selected($offer_old_user_expiry_time, 'year'); ?>><?php _e('Year', 'wc-offers'); ?></option>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('Leave empty or add zero to expire this offer until user use this offer', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr class="wco-inactive-customer-percentage-quantity-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-inactive-customer-percentage-quantity">+ Add Rule</div>
                                            <table class="wco-inactive-customer-percentage-quantity-table">
                                                <tr>
                                                    <td><?php _e('Minimum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $inactive_customer_offer_percentage_quantity = get_option('inactive_customer_offer_percentage_quantity', array());
                                                if ($inactive_customer_offer_percentage_quantity) {
                                                    $count = 0;
                                                    foreach ($inactive_customer_offer_percentage_quantity as $single_pre_quantity) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-inactive-customer-quantity-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_quantity" class="textNumberOnly min_quantity" min="0" type="text" value="<?php echo $single_pre_quantity['min_quantity']; ?>" name="inactive_customer_offer_percentage_quantity[<?php echo $count; ?>][min_quantity]"></td>
                                                            <td><input data-name="max_quantity" class="textNumberOnly max_quantity" min="0" type="text" value="<?php echo $single_pre_quantity['max_quantity']; ?>" name="inactive_customer_offer_percentage_quantity[<?php echo $count; ?>][max_quantity]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_quantity['discount_amount']; ?>" min="0" max="100" name="inactive_customer_offer_percentage_quantity[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-ic-delete-quantity-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-inactive-customer-percentage-amount-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-inactive-customer-percentage-amount">+ Add Rule</div>
                                            <table class="wco-inactive-customer-percentage-amount-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $inactive_customer_offer_percentage_amount = get_option('inactive_customer_offer_percentage_amount', array());
                                                if ($inactive_customer_offer_percentage_amount) {
                                                    $count = 0;
                                                    foreach ($inactive_customer_offer_percentage_amount as $single_pre_amount) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-inactive-customer-amount-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_amount['min_amount']; ?>" name="inactive_customer_offer_percentage_amount[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_amount['max_amount']; ?>" name="inactive_customer_offer_percentage_amount[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_amount['discount_amount']; ?>" min="0" max="100" name="inactive_customer_offer_percentage_amount[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-ic-delete-amount-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-inactive-customer-fixed_cart-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-inactive-customer-fixed_cart">+ Add Rule</div>
                                            <table class="wco-inactive-customer-fixed_cart-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Discount Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $inactive_customer_offer_fixed_cart = get_option('inactive_customer_offer_fixed_cart', array());
                                                if ($inactive_customer_offer_fixed_cart) {
                                                    $count = 0;
                                                    foreach ($inactive_customer_offer_fixed_cart as $single_pre_fixed_cart) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-inactive-customer-fixed_cart-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['min_amount']; ?>" name="inactive_customer_offer_fixed_cart[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['max_amount']; ?>" name="inactive_customer_offer_fixed_cart[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_fixed_cart['discount_amount']; ?>" min="0" name="inactive_customer_offer_fixed_cart[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-ic-delete-fixed_cart-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="inactive_customer_offer_exclude_product[]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                <?php
                                                $product_ids = get_option('inactive_customer_offer_exclude_product', array());
                                                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                                $loop = new WP_Query($args);
                                                if ($loop->have_posts()) {
                                                    while ($loop->have_posts()) : $loop->the_post();
                                                        echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                    endwhile;
                                                    wp_reset_query();
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="inactive_customer_offer_exclude_product_category[]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                <?php
                                                $cat_ids = get_option('inactive_customer_offer_exclude_product_category', array());
                                                $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                if ($categories) {
                                                    foreach ($categories as $cat) {
                                                        echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>                
            <?php } else if ($current_offer_tab == 'existing-users') {
                ?>
                <h2><?php echo _e('Discount for Existing Customers', 'wc-offers'); ?></h2>
                <div id="poststuff">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Discount Settings', 'wc-offers'); ?></span></h2>
                        <div class="inside">
                            <?php
                            $edit_id = isset($_GET['edit']) ? $_GET['edit'] : '';
                            $wcoffers_existing_customer_used_offer = get_option('wcoffers_existing_customer_used_offer', array());
                            $get_single_data = isset($wcoffers_existing_customer_used_offer[$edit_id]) ? $wcoffers_existing_customer_used_offer[$edit_id] : array();
                            $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
                            if ($wcoffers_existing_customer) {
                                $disable_user = array();
                                foreach ($wcoffers_existing_customer as $key => $single_array) {
                                    $disable_user = array_merge($disable_user, $single_array['user_ids']);
                                }
                            }
                            ?>
                            <table class="existing_customer_offer_table">
                                <tbody>
                                    <tr>
                                        <td><?php _e('Select Customers', 'wc-offers'); ?></td>
                                        <td class="offer-old-user">
                                            <?php
                                            $user_ids = isset($get_single_data['user_ids']) ? $get_single_data['user_ids'] : array();
                                            ?>
                                            <select class="wcoffers_cashback_products" style="width: 40% !important;" multiple="multiple" name="wcoffers_user_ids[]" data-placeholder="<?php esc_attr_e('Search user', 'wc-offers'); ?>">
                                                <option value=""><?php _e('Select User', 'wc-offers'); ?></option>
                                                <?php
                                                $args = array('order' => 'ASC', 'role' => 'customer');
                                                $users = get_users($args);
                                                if ($users) {
                                                    ?>
                                                    <?php
                                                    foreach ($users as $user) {
                                                        $selected = in_array($user->ID, $user_ids) ? "selected='selected'" : '';
                                                        $disable = in_array($user->ID, $disable_user) ? "disabled='disabled'" : '';
                                                        ?>
                                                        <option <?php echo $selected . ' ' . $disable; ?> value="<?php echo $user->ID; ?>"><?php echo $user->user_login; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php _e('Do you want Cashback?', 'wc-offers'); ?><span class="wco-help-tip"></span>
                                        </td>
                                        <td>
                                            <?php
                                            $offer_existing_user_enable_cashback = get_option('offer_existing_user_enable_cashback', 'no');
                                            ?>
                                            <label>
                                                <input type="radio" value="yes" checked="checked" class="offer_existing_user_enable_cashback" name="offer_existing_user_enable_cashback"><?php _e('Yes', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="no" checked="checked" class="offer_existing_user_enable_cashback" name="offer_existing_user_enable_cashback"><?php _e('No', 'wc-offers'); ?>
                                            </label>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('if yes then discount amount will be added in wallet', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Discount type', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offers_existing_user_discount_type = isset($get_single_data['discount_type']) ? $get_single_data['discount_type'] : 'percent';
                                            ?>
                                            <select class="offers_existing_user_discount_type" name="offers_existing_user_discount_type" class="select short" style="">
                                                <option value="percent" <?php selected($offers_existing_user_discount_type, 'percent'); ?>><?php _e('Percentage discount', 'wc-offers'); ?></option>
                                                <option value="fixed_cart"<?php selected($offers_existing_user_discount_type, 'fixed_cart'); ?>><?php _e('Fixed cart discount', 'wc-offers'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="discount_type_percent">
                                        <td><?php _e('Percentage Discount', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offers_existing_user_percentage_discount = isset($get_single_data['percentage_discount']) ? $get_single_data['percentage_discount'] : 'quantity';
                                            ?>
                                            <label>
                                                <input type="radio" value="quantity" class="offers_existing_user_percentage_discount" name="offers_existing_user_percentage_discount" <?php checked($offers_existing_user_percentage_discount, 'quantity'); ?>><?php _e('Quantity', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="amount" class="offers_existing_user_percentage_discount" name="offers_existing_user_percentage_discount" <?php checked($offers_existing_user_percentage_discount, 'amount'); ?>><?php _e('Amount', 'wc-offers'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Coupon expiry date', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <?php
                                            $offer_existing_user_expiry_number = isset($get_single_data['user_expiry_number']) ? $get_single_data['user_expiry_number'] : '';
                                            $offer_existing_user_expiry_time = isset($get_single_data['user_expiry_time']) ? $get_single_data['user_expiry_time'] : 'day';
                                            ?>
                                            <input class="textNumberOnly" type="number" name="offer_existing_user_expiry_number" min="0" id="offer_existing_user_expiry_number" value="<?php echo $offer_existing_user_expiry_number; ?>" placehexistinger="0">
                                            <select name="offer_existing_user_expiry_time" id="offer_existing_user_expiry_time">
                                                <option value="day" <?php selected($offer_existing_user_expiry_time, 'day'); ?>><?php _e('Day', 'wc-offers'); ?></option>
                                                <option value="week" <?php selected($offer_existing_user_expiry_time, 'week'); ?>><?php _e('Week', 'wc-offers'); ?></option>
                                                <option value="month" <?php selected($offer_existing_user_expiry_time, 'month'); ?>><?php _e('Month', 'wc-offers'); ?></option>
                                                <option value="year" <?php selected($offer_existing_user_expiry_time, 'year'); ?>><?php _e('Year', 'wc-offers'); ?></option>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('Leave empty or add zero to expire this offer until user use this offer', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr class="wco-existing-customer-percentage-quantity-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-existing-customer-percentage-quantity">+ Add Rule</div>
                                            <table class="wco-existing-customer-percentage-quantity-table">
                                                <tr>
                                                    <td><?php _e('Minimum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $existing_customer_offer_percentage_quantity = isset($get_single_data['percentage_quantity']) ? $get_single_data['percentage_quantity'] : array();
                                                if ($existing_customer_offer_percentage_quantity) {
                                                    $count = 0;
                                                    foreach ($existing_customer_offer_percentage_quantity as $single_pre_quantity) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-existing-customer-quantity-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_quantity" class="textNumberOnly min_quantity" min="0" type="text" value="<?php echo $single_pre_quantity['min_quantity']; ?>" name="existing_customer_offer_percentage_quantity[<?php echo $count; ?>][min_quantity]"></td>
                                                            <td><input data-name="max_quantity" class="textNumberOnly max_quantity" min="0" type="text" value="<?php echo $single_pre_quantity['max_quantity']; ?>" name="existing_customer_offer_percentage_quantity[<?php echo $count; ?>][max_quantity]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_quantity['discount_amount']; ?>" min="0" max="100" name="existing_customer_offer_percentage_quantity[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-ec-delete-quantity-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-existing-customer-percentage-amount-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-existing-customer-percentage-amount">+ Add Rule</div>
                                            <table class="wco-existing-customer-percentage-amount-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $existing_customer_offer_percentage_amount = isset($get_single_data['percentage_amount']) ? $get_single_data['percentage_amount'] : array();
                                                if ($existing_customer_offer_percentage_amount) {
                                                    $count = 0;
                                                    foreach ($existing_customer_offer_percentage_amount as $single_pre_amount) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-existing-customer-amount-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_amount['min_amount']; ?>" name="existing_customer_offer_percentage_amount[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_amount['max_amount']; ?>" name="existing_customer_offer_percentage_amount[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_amount['discount_amount']; ?>" min="0" max="100" name="existing_customer_offer_percentage_amount[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-ec-delete-amount-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-existing-customer-fixed_cart-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-existing-customer-fixed_cart">+ Add Rule</div>
                                            <table class="wco-existing-customer-fixed_cart-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Discount Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $existing_customer_offer_fixed_cart = isset($get_single_data['fixed_cart']) ? $get_single_data['fixed_cart'] : array();
                                                if ($existing_customer_offer_fixed_cart) {
                                                    $count = 0;
                                                    foreach ($existing_customer_offer_fixed_cart as $single_pre_fixed_cart) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-existing-customer-fixed_cart-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['min_amount']; ?>" name="existing_customer_offer_fixed_cart[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['max_amount']; ?>" name="existing_customer_offer_fixed_cart[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_fixed_cart['discount_amount']; ?>" min="0" name="existing_customer_offer_fixed_cart[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-ec-delete-fixed_cart-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="existing_customer_offer_exclude_product[]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                <?php
                                                $product_ids = isset($get_single_data['exclude_product']) ? $get_single_data['exclude_product'] : array();
                                                $args_products = array('post_type' => 'product', 'posts_per_page' => -1);
                                                $loop = new WP_Query($args_products);
                                                if ($loop->have_posts()) {
                                                    while ($loop->have_posts()) : $loop->the_post();
                                                        echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                    endwhile;
                                                    wp_reset_query();
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="existing_customer_offer_exclude_product_category[]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                <?php
                                                $cat_ids = isset($get_single_data['exclude_product_category']) ? $get_single_data['exclude_product_category'] : array();
                                                $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                if ($categories) {
                                                    foreach ($categories as $cat) {
                                                        echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>                
                <p class="submit">
                    <input name="save_offers" class="button button-primary button-hero woocommerce-save-button" type="submit" value="<?php esc_attr_e('Save changes', 'wc-offers'); ?>" />
                    <?php
                    wp_nonce_field('wpoffers_nonce_data', 'wpoffers_nonce_field');
                    ?>
                </p>
                <div class="wco-created-offer" style="width: 100%;padding-left: 0%;">
                    <?php
                    echo '<h2>' . __('Created Offers List', 'wc-offers') . '</h2>';
                    ?>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td class="manage-column"><?php _e('Users', 'wc-offers') ?></td>
                                <td class="manage-column"><?php _e('Discount Type', 'wc-offers') ?></td>
                                <td class="manage-column"><?php _e('Created Date', 'wc-offers') ?></td>
                                <td class="manage-column"><?php _e('Expiry Date', 'wc-offers') ?></td>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php
                            $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
                            if ($wcoffers_existing_customer) {
                                foreach ($wcoffers_existing_customer as $key => $single_data) {
                                    ?>
                                    <tr id="wco-offer-<?php echo $key; ?>" class="iedit">
                                        <td style="text-transform: capitalize;"><?php
                                            $data_count = count($single_data['user_ids']);
                                            $count = 1;
                                            if ($single_data['user_ids']) {
                                                foreach ($single_data['user_ids'] as $user_id) {
                                                    $user_data = get_userdata($user_id);
                                                    echo $user_data->user_login;
                                                    if ($data_count != $count) {
                                                        echo '<span class="comma">,</span>';
                                                    }
                                                    $count++;
                                                }
                                            } else {
                                                _e('No User', 'wc-offers');
                                            }
                                            ?>
                                            <div class="row-actions">
                                                <span class="edit">
                                                    <a class="wco-editinline-offer" data-id="<?php echo $key; ?>" href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&edit=<?php echo $key; ?>">Edit</a> |
                                                </span>
                                                <span class="trash">
                                                    <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&delete=<?php echo $key; ?>">Delete</a>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            if ($single_data['discount_type'] == 'percent') {
                                                _e('Percentage Discount', 'wc-offers');
                                            } else if ($single_data['discount_type'] == 'fixed_cart') {
                                                _e('Fixed Cart Discount', 'wc-offers');
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (isset($single_data['created_date'])) {
                                                echo $single_data['created_date'];
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($single_data['user_expiry_number'] == '') {
                                                _e('Lifetime', 'wc-offers');
                                            } else {
                                                $currentDate = isset($single_data['created_date']) ? $single_data['created_date'] : '';
                                                $expiry_date = '';
                                                if ($currentDate) {
                                                    $string = $single_data['user_expiry_number'] . ' ' . $single_data['user_expiry_time'];
                                                    $expiry_date = new DateTime($currentDate . ' + ' . $string);
                                                    $expiry_date = $expiry_date->format('Y-m-d');
                                                }
                                                echo $single_data['user_expiry_number'] . ' ' . $single_data['user_expiry_time'] . ' (' . $expiry_date . ')';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr class="wco-no-rule-found">
                                    <td colspan="4" style="text-align: center;">
                                        <?php _e('No offers found, ', 'wc-offers'); ?>
                                        <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>"><?php _e('Create new offer', 'wc-offers'); ?></a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="wco-created-offer" style="width: 100%;padding-left: 0%;">
                    <?php
                    echo '<h2>' . __('Used Offers List', 'wc-offers') . '</h2>';
                    ?>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td class="manage-column"><?php _e('Users', 'wc-offers') ?></td>
                                <td class="manage-column"><?php _e('Discount Type', 'wc-offers') ?></td>
                                <td class="manage-column"><?php _e('Expiry Time', 'wc-offers') ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $wcoffers_existing_customer_used_offer = get_option('wcoffers_existing_customer_used_offer', array());
                            if ($wcoffers_existing_customer_used_offer) {
                                foreach ($wcoffers_existing_customer_used_offer as $key => $single_data) {
                                    ?>
                                    <tr class="iedit">
                                        <td style="text-transform: capitalize;"><?php
                                            $data_count = count($single_data['user_ids']);
                                            $count = 1;
                                            if ($single_data['user_ids']) {
                                                foreach ($single_data['user_ids'] as $user_id) {
                                                    $user_data = get_userdata($user_id);
                                                    echo $user_data->user_login;
                                                    if ($data_count != $count) {
                                                        echo '<span class="comma">,</span>';
                                                    }
                                                    $count++;
                                                }
                                            } else {
                                                _e('No User', 'wc-offers');
                                            }
                                            ?>
                                            <div class="row-actions">
                                                <span class="edit">
                                                    <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&edit=<?php echo $key; ?>">Create Again</a> |
                                                </span>
                                                <span class="trash">
                                                    <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&co_delete=<?php echo $key; ?>">Delete</a>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            if ($single_data['discount_type'] == 'percent') {
                                                _e('Percentage Discount', 'wc-offers');
                                            } else if ($single_data['discount_type'] == 'fixed_cart') {
                                                _e('Fixed Cart Discount', 'wc-offers');
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($single_data['user_expiry_number'] == '') {
                                                _e('Lifetime', 'wc-offers');
                                            } else {
                                                echo $single_data['user_expiry_number'] . ' ' . $single_data['user_expiry_time'];
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr class="wco-no-rule-found">
                                    <td colspan="3" style="text-align: center;">
                                        <?php _e('No offers is used', 'wc-offers'); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                ?>
                <?php
            } else if ($current_offer_tab == 'freebies') {
                ?>
                <h3><?php echo _e('Freebies on next purchase', 'wc-offers'); ?></h3>
                <div id="poststuff">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Discount Settings', 'wc-offers'); ?></span></h2>
                        <div class="inside">
                            <table class="freebies-table">
                                <tbody>
                                    <tr>
                                        <td><?php _e('Enable/Disable', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $disable_freebies = get_option('disable_freebies', '1');
                                            ?>
                                            <label>
                                                <input type="radio" value="1" name="disable_freebies" <?php checked($disable_freebies, '1'); ?>><?php _e('Enable', 'wc-offers'); ?>
                                            </label>
                                            <br/>
                                            <label>
                                                <input type="radio" value="disable_all" name="disable_freebies" <?php checked($disable_freebies, 'disable_all'); ?>><?php _e('Disable for all', 'wc-offers'); ?>
                                            </label>
                                            <br/>
                                            <label>
                                                <input type="radio" value="disable_keep_existing" name="disable_freebies" <?php checked($disable_freebies, 'disable_keep_existing'); ?>><?php _e('Disable offer and keep existing offers', 'wc-offers'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php _e('Do you want Cashback?', 'wc-offers'); ?><span class="wco-help-tip"></span>
                                        </td>
                                        <td>
                                            <?php
                                            $offer_freebies_enable_cashback = get_option('offer_freebies_enable_cashback', 'no');
                                            ?>
                                            <label>
                                                <input type="radio" value="yes" class="offer_freebies_enable_cashback" name="offer_freebies_enable_cashback" <?php checked($offer_freebies_enable_cashback, 'yes'); ?>><?php _e('Yes', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="no" class="offer_freebies_enable_cashback" name="offer_freebies_enable_cashback" <?php checked($offer_freebies_enable_cashback, 'no'); ?>><?php _e('No', 'wc-offers'); ?>
                                            </label>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('if yes then discount amount will be added in wallet', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Discount type', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offer_freebies_discount_type = get_option('offers_freebies_discount_type');
                                            ?>
                                            <select id="offers_freebies_discount_type" name="offers_freebies_discount_type" class="select short" style="">
                                                <option value="percent" <?php selected($offer_freebies_discount_type, 'percent'); ?>><?php _e('Percentage discount', 'wc-offers'); ?></option>
                                                <option value="fixed_cart"<?php selected($offer_freebies_discount_type, 'fixed_cart'); ?>><?php _e('Fixed cart discount', 'wc-offers'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="discount_type_percent">
                                        <td><?php _e('Percentage Discount', 'wc-offers'); ?></td>
                                        <td>
                                            <?php
                                            $offers_freebies_discount = get_option('offers_freebies_discount', 'quantity');
                                            ?>
                                            <label>
                                                <input type="radio" value="quantity" class="offers_freebies_discount" name="offers_freebies_discount" <?php checked($offers_freebies_discount, 'quantity'); ?>><?php _e('Quantity', 'wc-offers'); ?>
                                            </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <input type="radio" value="amount" class="offers_freebies_discount" name="offers_freebies_discount" <?php checked($offers_freebies_discount, 'amount'); ?>><?php _e('Amount', 'wc-offers'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Coupon expiry date', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <?php
                                            $offer_freebies_expiry_number = get_option('offer_freebies_expiry_number', '');
                                            $offer_freebies_expiry_time = get_option('offer_freebies_expiry_time', 'day');
                                            ?>
                                            <input type="number" name="offer_freebies_expiry_number" class="textNumberOnly" min="0" id="offer_freebies_expiry_number" value="<?php echo $offer_freebies_expiry_number; ?>" placeholder="0">
                                            <select name="offer_freebies_expiry_time" id="offer_freebies_expiry_time">
                                                <option value="day" <?php selected($offer_freebies_expiry_time, 'day'); ?>><?php _e('Day', 'wc-offers'); ?></option>
                                                <option value="week" <?php selected($offer_freebies_expiry_time, 'week'); ?>><?php _e('Week', 'wc-offers'); ?></option>
                                                <option value="month" <?php selected($offer_freebies_expiry_time, 'month'); ?>><?php _e('Month', 'wc-offers'); ?></option>
                                                <option value="year" <?php selected($offer_freebies_expiry_time, 'year'); ?>><?php _e('Year', 'wc-offers'); ?></option>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php
                                                _e('Leave empty or add zero to expire this offer until user use this offer', 'wc-offers');
                                                ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr class="wco-second-purchase-percentage-quantity-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-second-purchase-percentage-quantity">+ Add Rule</div>
                                            <table class="wco-second-purchase-percentage-quantity-table">
                                                <tr>
                                                    <td><?php _e('Minimum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Quantity', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $second_purchase_offer_percentage_quantity = get_option('second_purchase_offer_percentage_quantity', array());
                                                if ($second_purchase_offer_percentage_quantity) {
                                                    $count = 0;
                                                    foreach ($second_purchase_offer_percentage_quantity as $single_pre_quantity) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-second-purchase-quantity-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_quantity" class="textNumberOnly min_quantity" min="0" type="text" value="<?php echo $single_pre_quantity['min_quantity']; ?>" name="second_purchase_offer_percentage_quantity[<?php echo $count; ?>][min_quantity]"></td>
                                                            <td><input data-name="max_quantity" class="textNumberOnly max_quantity" min="0" type="text" value="<?php echo $single_pre_quantity['max_quantity']; ?>" name="second_purchase_offer_percentage_quantity[<?php echo $count; ?>][max_quantity]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_quantity['discount_amount']; ?>" min="0" max="100" name="second_purchase_offer_percentage_quantity[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-sp-delete-quantity-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-second-purchase-percentage-amount-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-second-purchase-percentage-amount">+ Add Rule</div>
                                            <table class="wco-second-purchase-percentage-amount-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $second_purchase_percentage_amount = get_option('second_purchase_offer_percentage_amount', array());
                                                if ($second_purchase_percentage_amount) {
                                                    $count = 0;
                                                    foreach ($second_purchase_percentage_amount as $single_pre_amount) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-second-purchase-amount-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_amount['min_amount']; ?>" name="second_purchase_offer_percentage_amount[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_amount['max_amount']; ?>" name="second_purchase_offer_percentage_amount[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_amount['discount_amount']; ?>" min="0" max="100" name="second_purchase_offer_percentage_amount[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-sp-delete-amount-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="wco-second-purchase-fixed_cart-wrap wco-second-purchase-tr-wrap">
                                        <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                        <td>
                                            <div class="button wco-add-second-purchase-fixed_cart">+ Add Rule</div>
                                            <table class="wco-second-purchase-fixed_cart-table">
                                                <tr>
                                                    <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Discount Amount', 'wc-offers') ?></td>
                                                    <td><?php _e('Action', 'wc-offers') ?></td>
                                                </tr>
                                                <?php
                                                $second_purchase_offer_fixed_cart = get_option('second_purchase_offer_fixed_cart', array());
                                                if ($second_purchase_offer_fixed_cart) {
                                                    $count = 0;
                                                    foreach ($second_purchase_offer_fixed_cart as $single_pre_fixed_cart) {
                                                        $readonly = '';
                                                        if ($count != 0) {
                                                            $readonly = 'readonly="readonly"';
                                                        }
                                                        ?>
                                                        <tr class="wco-second-purchase-fixed_cart-rule-tr">
                                                            <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['min_amount']; ?>" name="second_purchase_offer_fixed_cart[<?php echo $count; ?>][min_amount]"></td>
                                                            <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['max_amount']; ?>" name="second_purchase_offer_fixed_cart[<?php echo $count; ?>][max_amount]"></td>
                                                            <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_fixed_cart['discount_amount']; ?>" min="0" name="second_purchase_offer_fixed_cart[<?php echo $count; ?>][discount_amount]"></td>
                                                            <td><div class="button wco-sp-delete-fixed_cart-rule">Remove</div></td>
                                                        </tr>
                                                        <?php
                                                        $count++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="wco-no-rule-found">
                                                        <td colspan="4" style="text-align: center;padding: 0;">
                                                            <?php _e('No rules found', 'wc-offers'); ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="freebies_offer_exclude_product[]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                <?php
                                                $product_ids = get_option('freebies_offer_exclude_product', array());
                                                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                                $loop = new WP_Query($args);
                                                if ($loop->have_posts()) {
                                                    while ($loop->have_posts()) : $loop->the_post();
                                                        echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                    endwhile;
                                                    wp_reset_query();
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></td>
                                        <td>
                                            <select class="wcoffers_cashback_products" multiple="multiple" name="freebies_offer_exclude_product_category[]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                <?php
                                                $cat_ids = get_option('freebies_offer_exclude_product_category', array());
                                                $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                if ($categories) {
                                                    foreach ($categories as $cat) {
                                                        echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>                                
                <?php
            } else if ($current_offer_tab == 'email-templates') {
                include 'html-admin-email-templates.php';
            } else if ($current_offer_tab == 'individual-offer') {
                include 'html-admin-individual-offer.php';
            } else if ($current_offer_tab == 'birthday-offer') {
                ?>
                <h3><?php echo _e('Birthday Offers', 'wc-offers'); ?></h3>                
                <div class="offers-birthday-wrap">
                    <div id="poststuff">
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e('Discount Settings', 'wc-offers'); ?></span></h2>
                            <div class="inside">
                                <table class="birthday-common-settings">
                                    <tbody>
                                        <tr>
                                            <td><?php _e('Disable birthday offers', 'wc-offers'); ?></td>
                                            <td>
                                                <?php
                                                $disable_birthday_offers = get_option('disable_birthday_offers', '0');
                                                ?>
                                                <input type="checkbox" value="1" name="disable_birthday_offers" id="disable_birthday_offers" <?php checked($disable_birthday_offers, 1); ?>>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Disable birthday field', 'wc-offers'); ?></td>
                                            <td>
                                                <?php
                                                $disable_birthday_field = get_option('disable_birthday_field', '0');
                                                ?>
                                                <input type="checkbox" value="1" name="disable_birthday_field" id="disable_birthday_field" <?php checked($disable_birthday_field, 1); ?>>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Select user birthday meta', 'wc-offers') ?> <span class="wco-help-tip"></span></td>
                                            <td>
                                                <?php
                                                global $wpdb;
                                                $select = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
                                                $usermetas = $wpdb->get_results($select);
                                                if ($usermetas) {
                                                    $offer_birthday_custom_meta_field = get_option('offer_birthday_custom_meta_field', '');
                                                    ?>
                                                    <select name="offer_birthday_custom_meta_field" id="offer_birthday_custom_meta_field">
                                                        <option value=""><?php _e('No Meta', 'wc-offers'); ?></option>
                                                        <?php
                                                        foreach ($usermetas as $usermeta) {
                                                            ?>
                                                            <option value="<?php echo $usermeta->meta_key; ?>" <?php selected($usermeta->meta_key, $offer_birthday_custom_meta_field); ?> ><?php echo $usermeta->meta_key; ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <?php
                                                }
                                                ?>
                                                <p class="wco-field-desc">
                                                    <?php
                                                    _e("Select user birthday meta when this plugin's birthday field is disable.", 'wc-offers');
                                                    ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr class="discount_type_percent">
                                            <td><?php _e('Offer Availability', 'wc-offers'); ?></td>
                                            <td>
                                                <?php
                                                $offer_birthday_available = get_option('offer_birthday_available', 'whole_day');
                                                ?>
                                                <label>
                                                    <input type="radio" value="whole_day" class="offer_birthday_available" name="offer_birthday_available" <?php checked($offer_birthday_available, 'whole_day'); ?>><?php _e('Available for whole day', 'wc-offers'); ?>
                                                </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label>
                                                    <input type="radio" value="single_order" class="offer_birthday_available" name="offer_birthday_available" <?php checked($offer_birthday_available, 'single_order'); ?>><?php _e('Available for only one order', 'wc-offers'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                    
                    <div class="offers-append-birthday-discount">
                        <?php
                        $offers_birthday_product = get_option('offers_birthday_product', array());
                        if ($offers_birthday_product) {
                            $count = 0;
                            foreach ($offers_birthday_product as $single_birthday) {
                                ?>
                                <table class="offers-birthday-table">
                                    <tbody>
                                        <tr>
                                            <td><div class="wcoffers-delete-birthday-discount button"><?php _e('Delete', 'wc-offers'); ?></div></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Order', 'wc-offers'); ?></td>
                                            <td>
                                                <input data-name="order_amount" class="textNumberOnly" type="number" value="<?php echo $single_birthday['order_amount']; ?>" name="offers_birthday_product[<?php echo $count; ?>][order_amount]">
                                                <select data-name="offers_birthday_order_comparison" name="offers_birthday_product[<?php echo $count; ?>][offers_birthday_order_comparison]">
                                                    <option value="=" <?php selected('=', $single_birthday['offers_birthday_order_comparison']); ?>><?php _e('Equal to', 'wc-offers'); ?></option>
                                                    <option value="<" <?php selected('<', $single_birthday['offers_birthday_order_comparison']); ?>><?php _e('Less than', 'wc-offers'); ?></option>
                                                    <option value=">" <?php selected('>', $single_birthday['offers_birthday_order_comparison']); ?>><?php _e('Greater than', 'wc-offers'); ?></option>
                                                    <option value="<=" <?php selected('<=', $single_birthday['offers_birthday_order_comparison']); ?>><?php _e('Less than or equal to', 'wc-offers'); ?></option>
                                                    <option value=">=" <?php selected('>=', $single_birthday['offers_birthday_order_comparison']); ?>><?php _e('Greater than or equal to', 'wc-offers'); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php _e('Do you want Cashback?', 'wc-offers'); ?><span class="wco-help-tip"></span>
                                            </td>
                                            <td>
                                                <?php
                                                $offers_birthday_enable_cashback = isset($single_birthday['offers_birthday_enable_cashback']) ? $single_birthday['offers_birthday_enable_cashback'] : 'no';
                                                ?>
                                                <label>
                                                    <input type="radio" value="yes" class="offer_freebies_enable_cashback" name="offers_birthday_product[<?php echo $count; ?>][offers_birthday_enable_cashback]" <?php checked($offers_birthday_enable_cashback, 'yes'); ?>><?php _e('Yes', 'wc-offers'); ?>
                                                </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label>
                                                    <input type="radio" value="no" class="offer_freebies_enable_cashback" name="offers_birthday_product[<?php echo $count; ?>][offers_birthday_enable_cashback]" <?php checked($offers_birthday_enable_cashback, 'no'); ?>><?php _e('No', 'wc-offers'); ?>
                                                </label>
                                                <p class="wco-field-desc">
                                                    <?php
                                                    _e('if yes then discount amount will be added in wallet', 'wc-offers');
                                                    ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Discount Type', 'wc-offers'); ?></td>
                                            <td>
                                                <select class="offers_birthday_discount_type" data-name="offers_birthday_discount_type" name="offers_birthday_product[<?php echo $count; ?>][offers_birthday_discount_type]">
                                                    <option value="percent" <?php selected('percent', $single_birthday['offers_birthday_discount_type']); ?>><?php _e('Percentage discount', 'wc-offers'); ?></option>
                                                    <option value="fixed_cart" <?php selected('fixed_cart', $single_birthday['offers_birthday_discount_type']); ?>><?php _e('Fixed cart discount', 'wc-offers'); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="discount_type_percent">
                                            <td><?php _e('Percentage Discount', 'wc-offers'); ?></td>
                                            <td>
                                                <?php
                                                $offer_percentage_discount = isset($single_birthday['offer_percentage_discount']) ? $single_birthday['offer_percentage_discount'] : 'quantity';
                                                ?>
                                                <label>
                                                    <input type="radio" value="quantity" data-name="offer_percentage_discount" class="offer_percentage_discount" name="offers_birthday_product[<?php echo $count; ?>][offer_percentage_discount]" <?php checked($offer_percentage_discount, 'quantity'); ?>><?php _e('Quantity', 'wc-offers'); ?>
                                                </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <label>
                                                    <input type="radio" value="amount" data-name="offer_percentage_discount" class="offer_percentage_discount" name="offers_birthday_product[<?php echo $count; ?>][offer_percentage_discount]" <?php checked($offer_percentage_discount, 'amount'); ?>><?php _e('Amount', 'wc-offers'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr class="wco-birthday-percentage-quantity-wrap">
                                            <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                            <td class="wco-second-purchase-tr-wrap">
                                                <div class="button wco-add-birthday-percentage-quantity">+ Add Rule</div>
                                                <table class="wco-birthday-percentage-quantity-table">
                                                    <tr>
                                                        <td><?php _e('Minimum Quantity', 'wc-offers') ?></td>
                                                        <td><?php _e('Maximum Quantity', 'wc-offers') ?></td>
                                                        <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                        <td><?php _e('Action', 'wc-offers') ?></td>
                                                    </tr>
                                                    <?php
                                                    $birthday_offer_percentage_quantity = isset($single_birthday['birthday_offer_percentage_quantity']) ? $single_birthday['birthday_offer_percentage_quantity'] : array();
                                                    if ($birthday_offer_percentage_quantity) {
                                                        $count1 = 0;
                                                        foreach ($birthday_offer_percentage_quantity as $single_pre_quantity) {
                                                            $readonly = '';
                                                            if ($count1 != 0) {
                                                                $readonly = 'readonly="readonly"';
                                                            }
                                                            ?>
                                                            <tr class="wco-birthday-quantity-rule-tr">
                                                                <td><input <?php echo $readonly; ?> data-name="min_quantity" class="textNumberOnly min_quantity" min="0" max="100" type="text" value="<?php echo $single_pre_quantity['min_quantity']; ?>" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_percentage_quantity][<?php echo $count1; ?>][min_quantity]"></td>
                                                                <td><input data-name="max_quantity" class="textNumberOnly max_quantity" min="0" max="100" type="text" value="<?php echo $single_pre_quantity['max_quantity']; ?>" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_percentage_quantity][<?php echo $count1; ?>][max_quantity]"></td>
                                                                <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_quantity['discount_amount']; ?>" min="0" max="100" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_percentage_quantity][<?php echo $count1; ?>][discount_amount]"></td>
                                                                <td><div class="button wco-bo-delete-quantity-rule">Remove</div></td>
                                                            </tr>
                                                            <?php
                                                            $count1++;
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr class="wco-no-rule-found">
                                                            <td colspan="4" style="text-align: center;padding: 0;">
                                                                <?php _e('No rules found', 'wc-offers'); ?>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                    ?>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="wco-birthday-percentage-amount-wrap">
                                            <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                            <td class="wco-second-purchase-tr-wrap">
                                                <div class="button wco-add-birthday-percentage-amount">+ Add Rule</div>
                                                <table class="wco-birthday-percentage-amount-table">
                                                    <tr>
                                                        <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                        <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                        <td><?php _e('Percentage', 'wc-offers') ?></td>
                                                        <td><?php _e('Action', 'wc-offers') ?></td>
                                                    </tr>
                                                    <?php
                                                    $birthday_offer_percentage_amount = isset($single_birthday['birthday_offer_percentage_amount']) ? $single_birthday['birthday_offer_percentage_amount'] : array();
                                                    if ($birthday_offer_percentage_amount) {
                                                        $count1 = 0;
                                                        foreach ($birthday_offer_percentage_amount as $single_pre_amount) {
                                                            $readonly = '';
                                                            if ($count1 != 0) {
                                                                $readonly = 'readonly="readonly"';
                                                            }
                                                            ?>
                                                            <tr class="wco-birthday-amount-rule-tr">
                                                                <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_amount['min_amount']; ?>" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_percentage_amount][<?php echo $count1; ?>][min_amount]"></td>
                                                                <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_amount['max_amount']; ?>" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_percentage_amount][<?php echo $count1; ?>][max_amount]"></td>
                                                                <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_amount['discount_amount']; ?>" min="0" max="100" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_percentage_amount][<?php echo $count1; ?>][discount_amount]"></td>
                                                                <td><div class="button wco-bo-delete-amount-rule">Remove</div></td>
                                                            </tr>
                                                            <?php
                                                            $count1++;
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr class="wco-no-rule-found">
                                                            <td colspan="4" style="text-align: center;padding: 0;">
                                                                <?php _e('No rules found', 'wc-offers'); ?>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                    ?>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="wco-birthday-fixed_cart-wrap">
                                            <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                            <td class="wco-second-purchase-tr-wrap">
                                                <div class="button wco-add-birthday-fixed_cart">+ Add Rule</div>
                                                <table class="wco-birthday-fixed_cart-table">
                                                    <tr>
                                                        <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                                        <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                                        <td><?php _e('Discount Amount', 'wc-offers') ?></td>
                                                        <td><?php _e('Action', 'wc-offers') ?></td>
                                                    </tr>
                                                    <?php
                                                    $birthday_offer_fixed_cart = isset($single_birthday['birthday_offer_fixed_cart']) ? $single_birthday['birthday_offer_fixed_cart'] : array();
                                                    if ($birthday_offer_fixed_cart) {
                                                        $count1 = 0;
                                                        foreach ($birthday_offer_fixed_cart as $single_pre_fixed_cart) {
                                                            $readonly = '';
                                                            if ($count1 != 0) {
                                                                $readonly = 'readonly="readonly"';
                                                            }
                                                            ?>
                                                            <tr class="wco-birthday-fixed_cart-rule-tr">
                                                                <td><input <?php echo $readonly; ?> data-name="min_amount" class="textNumberOnly min_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['min_amount']; ?>" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_fixed_cart][<?php echo $count1; ?>][min_amount]"></td>
                                                                <td><input data-name="max_amount" class="textNumberOnly max_amount" min="0" type="text" value="<?php echo $single_pre_fixed_cart['max_amount']; ?>" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_fixed_cart][<?php echo $count1; ?>][max_amount]"></td>
                                                                <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_fixed_cart['discount_amount']; ?>" min="0" name="offers_birthday_product[<?php echo $count; ?>][birthday_offer_fixed_cart][<?php echo $count1; ?>][discount_amount]"></td>
                                                                <td><div class="button wco-bo-delete-fixed_cart-rule">Remove</div></td>
                                                            </tr>
                                                            <?php
                                                            $count1++;
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr class="wco-no-rule-found">
                                                            <td colspan="4" style="text-align: center;padding: 0;">
                                                                <?php _e('No rules found', 'wc-offers'); ?>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                    ?>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></td>
                                            <td>
                                                <select data-name="offer_exclude_product" class="wcoffers_cashback_products" multiple="multiple" name="offers_birthday_product[<?php echo $count; ?>][offer_exclude_product][]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                    <?php
                                                    $product_ids = isset($single_birthday['offer_exclude_product']) ? $single_birthday['offer_exclude_product'] : array();
                                                    $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                                    $loop = new WP_Query($args);
                                                    if ($loop->have_posts()) {
                                                        while ($loop->have_posts()) : $loop->the_post();
                                                            echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                        endwhile;
                                                        wp_reset_query();
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></td>
                                            <td>
                                                <select data-name="offer_exclude_product_category" class="wcoffers_cashback_products" multiple="multiple" name="offers_birthday_product[<?php echo $count; ?>][offer_exclude_product_category][]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                    <?php
                                                    $cat_ids = isset($single_birthday['offer_exclude_product_category']) ? $single_birthday['offer_exclude_product_category'] : array();
                                                    $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                    if ($categories) {
                                                        foreach ($categories as $cat) {
                                                            echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php
                                $count++;
                            }
                        }
                        ?>
                    </div>
                    <div class="offers-add-button-div">
                        <div class="button wcoffers-add-birthday-discount"><?php _e('Add Birthday Discount', 'wc-offers'); ?></div>
                    </div>
                </div>
                <div class="offers-birthday-template-wrap" style="display: none;">
                    <table class="offers-birthday-table">
                        <tbody>
                            <tr>
                                <td><div class="wcoffers-delete-birthday-discount button"><?php _e('Delete', 'wc-offers'); ?></div></td>
                            </tr>
                            <tr>
                                <td><?php _e('Order', 'wc-offers'); ?></td>
                                <td>
                                    <input data-name="order_amount" type="number" class="textNumberOnly" value="" name="offers_birthday_product[%N%][order_amount]">
                                    <select data-name="offers_birthday_order_comparison" name="offers_birthday_product[%N%][offers_birthday_order_comparison]">
                                        <option value="="><?php _e('Equal to', 'wc-offers'); ?></option>
                                        <option value="<"><?php _e('Less than', 'wc-offers'); ?></option>
                                        <option value=">"><?php _e('Greater than', 'wc-offers'); ?></option>
                                        <option value="<="><?php _e('Less than or equal to', 'wc-offers'); ?></option>
                                        <option value=">="><?php _e('Greater than or equal to', 'wc-offers'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php _e('Do you want Cashback?', 'wc-offers'); ?>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" value="yes" class="offers_birthday_enable_cashback" name="offers_birthday_product[%N%][offers_birthday_enable_cashback]"><?php _e('Yes', 'wc-offers'); ?>
                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" value="no" checked="checked" class="offers_birthday_enable_cashback" name="offers_birthday_product[%N%][offers_birthday_enable_cashback]"><?php _e('No', 'wc-offers'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e('Discount Type', 'wc-offers'); ?></td>
                                <td>
                                    <select class="offers_birthday_discount_type" data-name="offers_birthday_discount_type" name="offers_birthday_product[%N%][offers_birthday_discount_type]">
                                        <option value="percent"><?php _e('Percentage discount', 'wc-offers'); ?></option>
                                        <option value="fixed_cart"><?php _e('Fixed cart discount', 'wc-offers'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="discount_type_percent">
                                <td><?php _e('Percentage Discount', 'wc-offers'); ?></td>
                                <td>
                                    <label>
                                        <input checked="checked" type="radio" value="quantity" data-name="offer_percentage_discount" class="offer_percentage_discount" name="offers_birthday_product[%N%][offer_percentage_discount]"><?php _e('Quantity', 'wc-offers'); ?>
                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label>
                                        <input type="radio" value="amount" data-name="offer_percentage_discount" class="offer_percentage_discount" name="offers_birthday_product[%N%][offer_percentage_discount]"><?php _e('Amount', 'wc-offers'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr class="wco-birthday-percentage-quantity-wrap">
                                <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                <td class="wco-second-purchase-tr-wrap">
                                    <div class="button wco-add-birthday-percentage-quantity">+ Add Rule</div>
                                    <table class="wco-birthday-percentage-quantity-table">
                                        <tr>
                                            <td><?php _e('Minimum Quantity', 'wc-offers') ?></td>
                                            <td><?php _e('Maximum Quantity', 'wc-offers') ?></td>
                                            <td><?php _e('Percentage', 'wc-offers') ?></td>
                                            <td><?php _e('Action', 'wc-offers') ?></td>
                                        </tr>
                                        <tr class="wco-no-rule-found">
                                            <td colspan="4" style="text-align: center;padding: 0;">
                                                <?php _e('No rules found', 'wc-offers'); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="wco-birthday-percentage-amount-wrap" style="display: none;">
                                <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                <td class="wco-second-purchase-tr-wrap">
                                    <div class="button wco-add-birthday-percentage-amount">+ Add Rule</div>
                                    <table class="wco-birthday-percentage-amount-table">
                                        <tr>
                                            <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                            <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                            <td><?php _e('Percentage', 'wc-offers') ?></td>
                                            <td><?php _e('Action', 'wc-offers') ?></td>
                                        </tr>
                                        <tr class="wco-no-rule-found">
                                            <td colspan="4" style="text-align: center;padding: 0;">
                                                <?php _e('No rules found', 'wc-offers'); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="wco-birthday-fixed_cart-wrap" style="display: none;">
                                <td><?php _e('Discount Rules', 'wc-offers'); ?></td>
                                <td class="wco-second-purchase-tr-wrap">
                                    <div class="button wco-add-birthday-fixed_cart">+ Add Rule</div>
                                    <table class="wco-birthday-fixed_cart-table">
                                        <tr>
                                            <td><?php _e('Minimum Amount', 'wc-offers') ?></td>
                                            <td><?php _e('Maximum Amount', 'wc-offers') ?></td>
                                            <td><?php _e('Discount Amount', 'wc-offers') ?></td>
                                            <td><?php _e('Action', 'wc-offers') ?></td>
                                        </tr>
                                        <tr class="wco-no-rule-found">
                                            <td colspan="4" style="text-align: center;padding: 0;">
                                                <?php _e('No rules found', 'wc-offers'); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></td>
                                <td>
                                    <select data-name="offer_exclude_product" class="wcoffers_birthday_products" multiple="multiple" name="offers_birthday_product[%N%][offer_exclude_product][]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                        <?php
                                        $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                        $loop = new WP_Query($args);
                                        if ($loop->have_posts()) {
                                            while ($loop->have_posts()) : $loop->the_post();
                                                echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                                            endwhile;
                                            wp_reset_query();
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></td>
                                <td>
                                    <select data-name="offer_exclude_product_category" class="wcoffers_birthday_products" multiple="multiple" name="offers_birthday_product[%N%][offer_exclude_product_category][]" data-placeholder="<?php esc_attr_e('Search for a product categories', 'wc-offers'); ?>">
                                        <?php
                                        $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                        if ($categories) {
                                            foreach ($categories as $cat) {
                                                echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php
            }else if ($current_offer_tab == 'upsell-offer') {
                include 'html-admin-upsells.php';
            } else if ($current_offer_tab == 'add-deposit') {
                include 'html-admin-add-deposit.php';
            }
            if ($current_offer_tab != 'upsell-offer') {
                if ($current_offer_tab != 'existing-users') {
                    ?>
                    <p class="submit">
                        <input name="save_offers" class="button button-primary button-hero woocommerce-save-button" type="submit" value="<?php esc_attr_e('Save changes', 'wc-offers'); ?>" />
                        <?php
                        wp_nonce_field('wpoffers_nonce_data', 'wpoffers_nonce_field');
                        ?>
                    </p>
                    <?php
                }  
            }
            ?>
        </div>
    </form>
</div>