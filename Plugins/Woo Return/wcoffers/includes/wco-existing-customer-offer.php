<?php

class WCO_Existing_Customer_Offers {

    public function __construct() {
        add_action('admin_init', array($this, 'wcoffers_save_existing_customer_settings'), 14);
        add_action('woocommerce_cart_calculate_fees', array($this, 'wcoffers_add_existing_customer_discount'), 13);
        add_action('woocommerce_thankyou', array($this, 'wcoffers_remove_existing_customer_offer'));
        add_action('wp_ajax_wcoffers_editinline_wu_offer', array($this, 'wcoffers_editinline_wu_offer'));
        add_action('wp_ajax_wcoffers_save_editinline_wu_offer', array($this, 'wcoffers_save_editinline_wu_offer'));
    }

    /**
     * @since 1.0
     * Save existing customer offer when edit inline
     */
    function wcoffers_save_editinline_wu_offer() {
        $edit_id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
        $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
        if (!isset($wcoffers_existing_customer[$edit_id])) {
            echo '0';
            exit;
        }
        if ($edit_id) {
            $params = isset($_POST['params']) ? $_POST['params'] : '';
            parse_str($params, $data);
            $user_ids = $wcoffers_existing_customer[$edit_id]['user_ids'];
            $discount_type = '';
            if (isset($data['offers_existing_user_discount_type'])) {
                $discount_type = $data['offers_existing_user_discount_type'];
            }
            $percentage_discount = '';
            if (isset($data['offers_existing_user_percentage_discount'])) {
                $percentage_discount = $data['offers_existing_user_percentage_discount'];
            }
            $user_expiry_number = '';
            if (isset($data['offer_existing_user_expiry_number'])) {
                $user_expiry_number = $data['offer_existing_user_expiry_number'];
            }
            $user_expiry_time = '';
            if (isset($data['offer_existing_user_expiry_time'])) {
                $user_expiry_time = $data['offer_existing_user_expiry_time'];
            }

            $user_cashback_enable = '';
            if (isset($data['offer_existing_user_enable_cashback'])) {
                $user_cashback_enable = $data['offer_existing_user_enable_cashback'];
            }

            if (isset($data['existing_customer_offer_percentage_quantity'])) {
                $d = $data['existing_customer_offer_percentage_quantity'];
                if (is_array($d) && !empty($d)) {
                    foreach ($d as $key => $value) {
                        if ($value['min_quantity'] == '') {
                            unset($d[$key]);
                        }
                    }
                } else {
                    $d = array();
                }
            } else {
                $d = array();
            }

            if (isset($data['existing_customer_offer_percentage_amount'])) {
                $b = $data['existing_customer_offer_percentage_amount'];
                if (is_array($b) && !empty($b)) {
                    foreach ($b as $key => $value) {
                        if ($value['min_amount'] == '') {
                            unset($b[$key]);
                        }
                    }
                } else {
                    $b = array();
                }
            } else {
                $b = array();
            }

            if (isset($data['existing_customer_offer_fixed_cart'])) {
                $c = $data['existing_customer_offer_fixed_cart'];
                if (is_array($c) && !empty($c)) {
                    foreach ($c as $key => $value) {
                        if ($value['min_amount'] == '') {
                            unset($c[$key]);
                        }
                    }
                } else {
                    $c = array();
                }
            } else {
                $c = array();
            }

            if (isset($data['existing_customer_offer_exclude_product_category'])) {
                $exclude_product_cat = $data['existing_customer_offer_exclude_product_category'];
            } else {
                $exclude_product_cat = array();
            }

            if (isset($data['existing_customer_offer_exclude_product'])) {
                $exclude_product = $data['existing_customer_offer_exclude_product'];
            } else {
                $exclude_product = array();
            }

            $wcoffers_existing_customer[$edit_id] = array(
                'user_ids' => $user_ids,
                'discount_type' => $discount_type,
                'percentage_discount' => $percentage_discount,
                'user_expiry_number' => $user_expiry_number,
                'user_expiry_time' => $user_expiry_time,
                'percentage_quantity' => $d,
                'percentage_amount' => $b,
                'fixed_cart' => $c,
                'exclude_product' => $exclude_product,
                'exclude_product_category' => $exclude_product_cat,
                'enable_cashback' => $user_cashback_enable,
                'created_date' => date('Y-m-d')
            );
            $final_array = array_combine(range(1, count($wcoffers_existing_customer)), array_values($wcoffers_existing_customer));
            update_option('wcoffers_existing_customer', $final_array);
            //Adding Offer for customers
            $offer_existing_user_expiry_number = $user_expiry_number;
            $offer_existing_user_expiry_time = $user_expiry_time;
            if ($offer_existing_user_expiry_number == '' || $offer_existing_user_expiry_number == 0) {
                $expiry_date = 'unlimited';
            } else {
                $currentDate = date('Y-m-d');
                $string = $offer_existing_user_expiry_number . ' ' . $offer_existing_user_expiry_time;
                $expiry_date = new DateTime($currentDate . ' + ' . $string);
                $expiry_date = $expiry_date->format('Y-m-d');
            }
            $wcoffers_existing_customer[$edit_id]['expiry_date'] = $expiry_date;
            if ($user_ids) {
                foreach ($user_ids as $id) {
                    update_user_meta($id, 'wco_existing_user_offer_expiry_date', $expiry_date);
                }
            }
            echo json_encode($wcoffers_existing_customer[$edit_id]);
        }
        exit;
    }

    /**
     * @since 1.0
     * Create inline edit tr
     */
    function wcoffers_editinline_wu_offer() {
        $edit_id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
        $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
        $get_single_data = isset($wcoffers_existing_customer[$edit_id]) ? $wcoffers_existing_customer[$edit_id] : array();
        if ($wcoffers_existing_customer) {
            $disable_user = array();
            foreach ($wcoffers_existing_customer as $key => $single_array) {
                $disable_user = array_merge($disable_user, $single_array['user_ids']);
            }
        }
        ?>
        <tr id="edit-<?php echo $edit_id; ?>" class="wco-existing-customer-inline-wrap">
            <td colspan="4">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><?php _e('Select Customers', 'wc-offers'); ?></th>
                            <td class="offer-old-user">
                                <?php
                                $user_ids = isset($get_single_data['user_ids']) ? $get_single_data['user_ids'] : array();
                                ?>
                                <select disabled="disabled" class="wcoffers_cashback_products" style="width: 40% !important;" multiple="multiple" name="wcoffers_user_ids[]" data-placeholder="<?php esc_attr_e('Search user', 'wc-offers'); ?>">
                                    <option value=""><?php _e('Select User', 'wc-offers'); ?></option>
                                    <?php
                                    $args = array('order' => 'ASC', 'role' => 'customer');
                                    $users = get_users($args);
                                    if ($users) {
                                        ?>
                                        <?php
                                        foreach ($users as $user) {
                                            $selected = in_array($user->ID, $user_ids) ? "selected='selected'" : '';
                                            if ($selected == '') {
                                                $disable = in_array($user->ID, $disable_user) ? "disabled='disabled'" : '';
                                            }
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
                            <th>
                                <?php _e('Do you want Cashback?', 'wc-offers'); ?>
                            </th>
                            <td>
                                <?php
                                $offer_existing_user_enable_cashback = isset($get_single_data['enable_cashback']) ? $get_single_data['enable_cashback'] : 'no';
                                ;
                                ?>
                                <label>
                                    <input type="radio" value="yes" <?php checked($offer_existing_user_enable_cashback, 'yes'); ?> class="offer_existing_user_enable_cashback" name="offer_existing_user_enable_cashback"><?php _e('Yes', 'wc-offers'); ?>
                                </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                <label>
                                    <input type="radio" value="no" <?php checked($offer_existing_user_enable_cashback, 'no'); ?> class="offer_existing_user_enable_cashback" name="offer_existing_user_enable_cashback"><?php _e('No', 'wc-offers'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Discount type', 'wc-offers'); ?></th>
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
                            <th><?php _e('Percentage Discount', 'wc-offers'); ?></th>
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
                            <th><?php _e('Coupon expiry date', 'wc-offers'); ?></th>
                            <td>
                                <?php
                                $offer_existing_user_expiry_number = isset($get_single_data['user_expiry_number']) ? $get_single_data['user_expiry_number'] : '';
                                $offer_existing_user_expiry_time = isset($get_single_data['user_expiry_time']) ? $get_single_data['user_expiry_time'] : 'day';
                                ?>
                                <input type="number" class="textNumberOnly" name="offer_existing_user_expiry_number" min="0" id="offer_existing_user_expiry_number" value="<?php echo $offer_existing_user_expiry_number; ?>" placehexistinger="0">
                                <select name="offer_existing_user_expiry_time" id="offer_existing_user_expiry_time">
                                    <option value="day" <?php selected($offer_existing_user_expiry_time, 'day'); ?>><?php _e('Day', 'wc-offers'); ?></option>
                                    <option value="week" <?php selected($offer_existing_user_expiry_time, 'week'); ?>><?php _e('Week', 'wc-offers'); ?></option>
                                    <option value="month" <?php selected($offer_existing_user_expiry_time, 'month'); ?>><?php _e('Month', 'wc-offers'); ?></option>
                                    <option value="year" <?php selected($offer_existing_user_expiry_time, 'year'); ?>><?php _e('Year', 'wc-offers'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="wco-existing-customer-percentage-quantity-wrap wco-second-purchase-tr-wrap">
                            <th><?php _e('Discount Rules', 'wc-offers'); ?></th>
                            <td>
                                <div class="button wco-add-existing-customer-percentage-quantity">+ Add Rule</div>
                                <table class="wco-existing-customer-percentage-quantity-table">
                                    <tr>
                                        <th><?php _e('Minimum Quantity', 'wc-offers') ?></th>
                                        <th><?php _e('Maximum Quantity', 'wc-offers') ?></th>
                                        <th><?php _e('Percentage', 'wc-offers') ?></th>
                                        <th><?php _e('Action', 'wc-offers') ?></th>
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
                            <th><?php _e('Discount Rules', 'wc-offers'); ?></th>
                            <td>
                                <div class="button wco-add-existing-customer-percentage-amount">+ Add Rule</div>
                                <table class="wco-existing-customer-percentage-amount-table">
                                    <tr>
                                        <th><?php _e('Minimum Amount', 'wc-offers') ?></th>
                                        <th><?php _e('Maximum Amount', 'wc-offers') ?></th>
                                        <th><?php _e('Percentage', 'wc-offers') ?></th>
                                        <th><?php _e('Action', 'wc-offers') ?></th>
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
                            <th><?php _e('Discount Rules', 'wc-offers'); ?></th>
                            <td>
                                <div class="button wco-add-existing-customer-fixed_cart">+ Add Rule</div>
                                <table class="wco-existing-customer-fixed_cart-table">
                                    <tr>
                                        <th><?php _e('Minimum Amount', 'wc-offers') ?></th>
                                        <th><?php _e('Maximum Amount', 'wc-offers') ?></th>
                                        <th><?php _e('Discount Amount', 'wc-offers') ?></th>
                                        <th><?php _e('Action', 'wc-offers') ?></th>
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
                            <th><?php _e('Exclude Product(s) from Offer', 'wc-offers'); ?></th>
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
                            <th><?php _e('Exclude Product Category from Offer', 'wc-offers'); ?></th>
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
                <p class="submit inline-edit-save">
                    <span class="spinner"></span>
                    <button type="button" class="button cancel alignleft">Cancel</button>
                    <button type="button" class="button button-primary save alignright">Update</button>
                </p>
            </td>
        </tr>
        <?php
        exit;
    }

    /**
     * @since 1.0
     * @param int $order_id
     * Delete existing user offer once user use offer
     */
    function wcoffers_remove_existing_customer_offer($order_id) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            if (session_id() == '') {
                session_start();
            }
            if (isset($_SESSION['existing_user_offfer']) && $_SESSION['existing_user_offfer'] == $user_id) {
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
                    if (count($eu_offer_data['user_ids']) == 1) {
                        unset($wcoffers_existing_customer[$eu_offer_key]);
                        $final_array = array_combine(range(1, count($wcoffers_existing_customer)), array_values($wcoffers_existing_customer));
                        update_option('wcoffers_existing_customer', $final_array);
                    } else {
                        foreach ($eu_offer_data['user_ids'] as $user_key => $single_user_id) {
                            if ($single_user_id == $user_id) {
                                unset($wcoffers_existing_customer[$eu_offer_key]['user_ids'][$user_key]);
                                update_option('wcoffers_existing_customer', $wcoffers_existing_customer);
                            }
                        }
                    }
                    $used_offer_array = get_option('wcoffers_existing_customer_used_offer', array());
                    if (empty($used_offer_array)) {
                        $used_offer_array = array();
                    }
                    if (count($eu_offer_data['user_ids']) != 1) {
                        foreach ($eu_offer_data['user_ids'] as $user_key => $single_user_id) {
                            if ($single_user_id == $user_id) {
                                
                            } else {
                                unset($eu_offer_data['user_ids'][$user_key]);
                            }
                        }
                    }
                    $used_offer_array[] = $eu_offer_data;
                    $final_array1 = array_combine(range(1, count($used_offer_array)), array_values($used_offer_array));
                    update_option('wcoffers_existing_customer_used_offer', $final_array1);
                    delete_user_meta($user_id, 'wco_existing_user_offer_expiry_date');
                }
            }
        }
    }

    /**
     * @since 1.0
     * @param object $cart
     * Add discount on cart page
     */
    function wcoffers_add_existing_customer_discount($cart) {
        if (is_user_logged_in()) {
            if (session_id() == '') {
                session_start();
            }
            $user_id = get_current_user_id();
            $currentDate = date('Y-m-d');
            $eu_offer_date = get_user_meta($user_id, 'wco_existing_user_offer_expiry_date', TRUE);
            if ($eu_offer_date != 'unlimited') {
                if ($eu_offer_date == '')
                    return;
                if ($eu_offer_date) {
                    if (strtotime($eu_offer_date) < strtotime($currentDate)) {
                        return;
                    }
                }
            }
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
            if (empty($eu_offer_data)) {
                return;
            }
            $offer_discount_type = isset($eu_offer_data['discount_type']) ? $eu_offer_data['discount_type'] : 'percent';
            $functions = new WCOCommonFunctions();
            $product_ids = isset($eu_offer_data['exclude_product']) ? $eu_offer_data['exclude_product'] : array();
            $cat_ids = isset($eu_offer_data['exclude_product_category']) ? $eu_offer_data['exclude_product_category'] : array();
            $enable_cashback = isset($eu_offer_data['enable_cashback']) ? $eu_offer_data['enable_cashback'] : 'no';
            if ($offer_discount_type == 'percent') {
                $offer_percentage_discount = isset($eu_offer_data['percentage_discount']) ? $eu_offer_data['percentage_discount'] : 'quantity';
                if ($offer_percentage_discount == 'quantity') {
                    $existing_customer_offer_percentage_quantity = isset($eu_offer_data['percentage_quantity']) ? $eu_offer_data['percentage_quantity'] : array();
                    $cart_total_quantity = WC()->cart->get_cart_contents_count();
                    $exclude_quantity = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'percent_quantity');
                    if ($exclude_quantity) {
                        $cart_total_quantity = $cart_total_quantity - $exclude_quantity;
                    }
                    $applied_offer = '';
                    foreach ($existing_customer_offer_percentage_quantity as $key => $value) {
                        $minValue = $value['min_quantity'];
                        $maxValue = $value['max_quantity'];
                        if ($maxValue == '' && $cart_total_quantity >= $minValue) {
                            $applied_offer = $key;
                        } else if ($cart_total_quantity >= $minValue && $cart_total_quantity <= $maxValue) {
                            $applied_offer = $key;
                        }
                    }
                    if (isset($existing_customer_offer_percentage_quantity[$applied_offer])) {
                        $discount_amount = isset($existing_customer_offer_percentage_quantity[$applied_offer]['discount_amount']) ? $existing_customer_offer_percentage_quantity[$applied_offer]['discount_amount'] : '';
                        if ($discount_amount != '' && $discount_amount < 100) {
                            $discount_amount = $existing_customer_offer_percentage_quantity[$applied_offer]['discount_amount'];
                            $total_dis = $discount_amount / 100;
                            $excluded_total = $cart->subtotal;
                            $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                            if ($exclude_total) {
                                $excluded_total = $excluded_total - $exclude_total;
                            }
                            $discount = $excluded_total * $total_dis;
                            if ($enable_cashback == 'yes') {
                                $functions->wcoffers_add_cashback_row($discount);
                            } else {
                                if (isset($_SESSION['cashback_amount']))
                                    unset($_SESSION['cashback_amount']);
                                $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                            }
                            $_SESSION['existing_user_offfer'] = $user_id;
                        }
                    }
                } else if ($offer_percentage_discount == 'amount') {
                    $existing_customer_offer_percentage_amount = isset($eu_offer_data['percentage_amount']) ? $eu_offer_data['percentage_amount'] : array();
                    if ($existing_customer_offer_percentage_amount) {
                        $subtotal = $cart->subtotal;
                        $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                        if ($exclude_total) {
                            $subtotal = $subtotal - $exclude_total;
                        }
                        $applied_offer = '';
                        foreach ($existing_customer_offer_percentage_amount as $key => $amount) {
                            $min_amount = $amount['min_amount'];
                            $max_amount = $amount['max_amount'];
                            if ($max_amount == '' && $subtotal >= $min_amount) {
                                $applied_offer = $key;
                            } else if ($subtotal >= $min_amount && $subtotal <= $max_amount) {
                                $applied_offer = $key;
                            }
                        }
                        if (isset($existing_customer_offer_percentage_amount[$applied_offer])) {
                            $discount_amount = isset($existing_customer_offer_percentage_amount[$applied_offer]['discount_amount']) ? $existing_customer_offer_percentage_amount[$applied_offer]['discount_amount'] : '';
                            if ($discount_amount != '' && $discount_amount < 100) {
                                $discount_amount = $existing_customer_offer_percentage_amount[$applied_offer]['discount_amount'];
                                $total_dis = $discount_amount / 100;
                                $discount = $subtotal * $total_dis;
                                if ($enable_cashback == 'yes') {
                                    $functions->wcoffers_add_cashback_row($discount);
                                } else {
                                    if (isset($_SESSION['cashback_amount']))
                                        unset($_SESSION['cashback_amount']);
                                    $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                                }
                                $_SESSION['existing_user_offfer'] = $user_id;
                            }
                        }
                    }
                }
            } else if ($offer_discount_type == 'fixed_cart') {
                $existing_customer_offer_fixed_cart = isset($eu_offer_data['fixed_cart']) ? $eu_offer_data['fixed_cart'] : array();
                if ($existing_customer_offer_fixed_cart) {
                    $subtotal = $cart->subtotal;
                    $exclude_total = $functions->wco_calculae_exclude_total(WC()->cart, $product_ids, $cat_ids, 'fixed_cart');
                    if ($exclude_total) {
                        $subtotal = $subtotal - $exclude_total;
                    }
                    $applied_offer = '';
                    foreach ($existing_customer_offer_fixed_cart as $key => $fix_amount) {
                        $min_amount = $fix_amount['min_amount'];
                        $max_amount = $fix_amount['max_amount'];
                        if ($max_amount == '' && $subtotal >= $min_amount) {
                            $applied_offer = $key;
                        } else if ($subtotal >= $min_amount && $subtotal <= $max_amount) {
                            $applied_offer = $key;
                        }
                    }
                    if (isset($existing_customer_offer_fixed_cart[$applied_offer])) {
                        $discount_amount = isset($existing_customer_offer_fixed_cart[$applied_offer]['discount_amount']) ? $existing_customer_offer_fixed_cart[$applied_offer]['discount_amount'] : '';
                        if ($discount_amount != '') {
                            $discount_amount = $existing_customer_offer_fixed_cart[$applied_offer]['discount_amount'];
                            $total_dis = $discount_amount;
                            $discount = $total_dis;
                            if ($enable_cashback == 'yes') {
                                $functions->wcoffers_add_cashback_row($discount);
                            } else {
                                if (isset($_SESSION['cashback_amount']))
                                    unset($_SESSION['cashback_amount']);
                                $cart->add_fee(__('Discount', 'wc-offers'), -$discount);
                            }
                            $_SESSION['existing_user_offfer'] = $user_id;
                        }
                    }
                }
            }
        }
    }

    /**
     * @since 1.0
     * Save setting of existing customer
     */
    function wcoffers_save_existing_customer_settings() {
        global $wpoffers_success;
        $current_offer_page = isset($_GET['page']) && $_GET['page'] == 'wc-offers' && isset($_GET['tab']) ? $_GET['tab'] : 'new_reg';
        if (isset($_GET['delete']) && $_GET['delete'] != '') {
            $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
            if (isset($wcoffers_existing_customer[$_GET['delete']])) {
                if (isset($wcoffers_existing_customer[$_GET['delete']]['user_ids'])) {
                    foreach ($wcoffers_existing_customer[$_GET['delete']]['user_ids'] as $id) {
                        delete_user_meta($id, 'wco_existing_user_offer_expiry_date');
                    }
                }
                unset($wcoffers_existing_customer[$_GET['delete']]);
                $final_array = array_combine(range(1, count($wcoffers_existing_customer)), array_values($wcoffers_existing_customer));
                update_option('wcoffers_existing_customer', $final_array);
                wp_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&deleted=successfully');
                exit;
            }
        }
        //Delete used offer list
        if (isset($_GET['co_delete']) && $_GET['co_delete'] != '') {
            $wcoffers_existing_customer_used_offer = get_option('wcoffers_existing_customer_used_offer', array());
            if (isset($wcoffers_existing_customer_used_offer[$_GET['co_delete']])) {
                unset($wcoffers_existing_customer_used_offer[$_GET['co_delete']]);
                $final_array = array_combine(range(1, count($wcoffers_existing_customer_used_offer)), array_values($wcoffers_existing_customer_used_offer));
                update_option('wcoffers_existing_customer_used_offer', $final_array);
                wp_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&deleted=successfully');
                exit;
            }
        }

        if (isset($_POST['save_offers']) && isset($_POST['wpoffers_nonce_field']) && wp_verify_nonce($_POST['wpoffers_nonce_field'], 'wpoffers_nonce_data')) {
            if ($current_offer_page == 'existing-users') {
                $wcoffers_existing_customer = get_option('wcoffers_existing_customer', array());
                if (empty($wcoffers_existing_customer)) {
                    $wcoffers_existing_customer = array();
                }
                //Edit section
                $edit_id = isset($_GET['edit']) ? $_GET['edit'] : '';

                if (isset($_POST['wcoffers_user_ids'])) {
                    $user_ids = $_POST['wcoffers_user_ids'];
                } else {
                    $user_ids = array();
                }
                $discount_type = '';
                if (isset($_POST['offers_existing_user_discount_type'])) {
                    $discount_type = $_POST['offers_existing_user_discount_type'];
                }
                $percentage_discount = '';
                if (isset($_POST['offers_existing_user_percentage_discount'])) {
                    $percentage_discount = $_POST['offers_existing_user_percentage_discount'];
                }
                $user_expiry_number = '';
                if (isset($_POST['offer_existing_user_expiry_number'])) {
                    $user_expiry_number = $_POST['offer_existing_user_expiry_number'];
                }
                $user_expiry_time = '';
                if (isset($_POST['offer_existing_user_expiry_time'])) {
                    $user_expiry_time = $_POST['offer_existing_user_expiry_time'];
                }
                $user_cashback_enable = '';
                if (isset($_POST['offer_existing_user_enable_cashback'])) {
                    $user_cashback_enable = $_POST['offer_existing_user_enable_cashback'];
                }

                if (isset($_POST['existing_customer_offer_percentage_quantity'])) {
                    $d = $_POST['existing_customer_offer_percentage_quantity'];
                    if (is_array($d) && !empty($d)) {
                        foreach ($d as $key => $value) {
                            if ($value['min_quantity'] == '') {
                                unset($d[$key]);
                            }
                        }
                    } else {
                        $d = array();
                    }
                } else {
                    $d = array();
                }

                if (isset($_POST['existing_customer_offer_percentage_amount'])) {
                    $b = $_POST['existing_customer_offer_percentage_amount'];
                    if (is_array($b) && !empty($b)) {
                        foreach ($b as $key => $value) {
                            if ($value['min_amount'] == '') {
                                unset($b[$key]);
                            }
                        }
                    } else {
                        $b = array();
                    }
                } else {
                    $b = array();
                }

                if (isset($_POST['existing_customer_offer_fixed_cart'])) {
                    $c = $_POST['existing_customer_offer_fixed_cart'];
                    if (is_array($c) && !empty($c)) {
                        foreach ($c as $key => $value) {
                            if ($value['min_amount'] == '') {
                                unset($c[$key]);
                            }
                        }
                    } else {
                        $c = array();
                    }
                } else {
                    $c = array();
                }

                if (isset($_POST['existing_customer_offer_exclude_product_category'])) {
                    $exclude_product_cat = $_POST['existing_customer_offer_exclude_product_category'];
                } else {
                    $exclude_product_cat = array();
                }

                if (isset($_POST['existing_customer_offer_exclude_product'])) {
                    $exclude_product = $_POST['existing_customer_offer_exclude_product'];
                } else {
                    $exclude_product = array();
                }

                if ($edit_id) {
                    $wcoffers_existing_customer[] = array(
                        'user_ids' => $user_ids,
                        'discount_type' => $discount_type,
                        'percentage_discount' => $percentage_discount,
                        'user_expiry_number' => $user_expiry_number,
                        'user_expiry_time' => $user_expiry_time,
                        'percentage_quantity' => $d,
                        'percentage_amount' => $b,
                        'fixed_cart' => $c,
                        'exclude_product' => $exclude_product,
                        'exclude_product_category' => $exclude_product_cat,
                        'enable_cashback' => $user_cashback_enable,
                        'created_date' => date('Y-m-d')
                    );
                } else {
                    $wcoffers_existing_customer[] = array(
                        'user_ids' => $user_ids,
                        'discount_type' => $discount_type,
                        'percentage_discount' => $percentage_discount,
                        'user_expiry_number' => $user_expiry_number,
                        'user_expiry_time' => $user_expiry_time,
                        'percentage_quantity' => $d,
                        'percentage_amount' => $b,
                        'fixed_cart' => $c,
                        'exclude_product' => $exclude_product,
                        'exclude_product_category' => $exclude_product_cat,
                        'enable_cashback' => $user_cashback_enable,
                        'created_date' => date('Y-m-d')
                    );
                }
                $final_array = array_combine(range(1, count($wcoffers_existing_customer)), array_values($wcoffers_existing_customer));
                update_option('wcoffers_existing_customer', $final_array);
                //Adding Offer for customers
                $offer_existing_user_expiry_number = $user_expiry_number;
                $offer_existing_user_expiry_time = $user_expiry_time;
                if ($offer_existing_user_expiry_number == '' || $offer_existing_user_expiry_number == 0) {
                    $expiry_date = 'unlimited';
                } else {
                    $currentDate = date('Y-m-d');
                    $string = $offer_existing_user_expiry_number . ' ' . $offer_existing_user_expiry_time;
                    $expiry_date = new DateTime($currentDate . ' + ' . $string);
                    $expiry_date = $expiry_date->format('Y-m-d');
                }
                if ($user_ids) {
                    $email_template = new WCO_Email_Templates();
                    foreach ($user_ids as $id) {
                        update_user_meta($id, 'wco_existing_user_offer_expiry_date', $expiry_date);
                        $email_template->wcoffers_existing_customer_email($id, $string);
                    }
                }
                if (isset($_GET['edit'])) {
                    wp_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&updated=successfully');
                    exit;
                }
            }
        }
    }

}

new WCO_Existing_Customer_Offers();
