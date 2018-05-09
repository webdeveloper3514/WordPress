<?php

/**
 * Calculate individual discount
 */
if (!class_exists('wcoffersIndividualOfferDiscount')) {

    class wcoffersIndividualOfferDiscount {

        var $wco_product_pricing;
        var $old_css = '';
        var $new_css = '';
        var $reg_offer = false;
        var $second_offer = false;

        public function __construct() {
            $this->wco_product_pricing = get_option('wco_product_pricing');
            add_action('woocommerce_loaded', array($this, 'wcoffers_woocommerce_loaded'));
            $this->old_css = esc_attr('color: #777; text-decoration: line-through; margin-right: 4px;');
            $this->new_css = esc_attr('color: #4AB915;');
        }

        /**
         * @since 1.0
         * Add action and filter when cart is loaded
         */
        public function wcoffers_woocommerce_loaded() {
            add_action('woocommerce_cart_loaded_from_session', array($this, 'wcoffers_prepare_cart_discounts'), 11, 1);
            add_action('woocommerce_before_calculate_totals', array($this, 'wcoffers_calculate_product_discount'), 99);
            add_filter('woocommerce_cart_item_price', array($this, 'wcoffers_cart_item_price'), 11, 3);
        }

        /**
         * @since 1.0
         * @param type $cart
         * @return type@since 1.0
         * Calculate single product price
         */
        function wcoffers_prepare_cart_discounts($cart) {
            $functions = new WCOCommonFunctions();
            $this->reg_offer = $functions->wcoffers_check_registration_offer_avail();
            $this->second_offer = $functions->wcoffers_check_second_purchase_offer_avail();
            if ($this->reg_offer === TRUE || $this->second_offer === TRUE) {
                return $cart;
            }
            $cart_items = $cart->cart_contents;
            $rules = $this->wco_product_pricing;
            $total_quantity = 0;
            $group_quantity = 0;
            $group_quantity_product = 0;
            if ($cart_items) {
                foreach ($cart_items as $cart_item_key => $cart_item_data) {
                    if (isset($cart->cart_contents[$cart_item_key]['wco_rule_data'])) {
                        unset($cart->cart_contents[$cart_item_key]['wco_rule_data']);
                    }
                    $_product = $cart_item_data['data'];
                    $product_id = $_product->get_id();
                    $init_price = $_product->get_price();
                    $quantity = $cart_item_data['quantity'];
                    $terms = get_the_terms($product_id, 'product_cat');
                    $wco_rule_data = array();
                    $wco_rule_data['init_price'] = $init_price;
                    $wco_rule_data['updated_price'] = 0;
                    if ($rules) {
                        foreach ($rules as $key => $rule) {
                            if ($rule['method'] == 'simple') {
                                if (isset($rule['product_condition'])) {
                                    foreach ($rule['product_condition'] as $key => $product_condition) {
                                        if ($product_condition['type'] == 'products') {
                                            if ($product_condition['method_option'] == 'in_list' && in_array($product_id, $product_condition['product_id'])) {
                                                $wco_rule_data = $this->wco_set_simple_value($rule, $wco_rule_data, $_product);
                                            } else if ($product_condition['method_option'] == 'not_in_list' && !in_array($product_id, $product_condition['product_id'])) {
                                                $wco_rule_data = $this->wco_set_simple_value($rule, $wco_rule_data, $_product);
                                            }
                                        } else if ($product_condition['type'] == 'products_category') {
                                            if ($product_condition['method_option'] == 'in_list') {
                                                if ($terms) {
                                                    foreach ($terms as $term) {
                                                        $_categoryid = $term->term_id;
                                                        if (in_array($_categoryid, $product_condition['product_cat'])) {
                                                            $wco_rule_data = $this->wco_set_simple_value($rule, $wco_rule_data, $_product);
                                                        }
                                                    }
                                                }
                                            } else if ($product_condition['method_option'] == 'not_in_list') {
                                                if ($terms) {
                                                    foreach ($terms as $term) {
                                                        $_categoryid = $term->term_id;
                                                        if (!in_array($_categoryid, $product_condition['product_cat'])) {
                                                            $wco_rule_data = $this->wco_set_simple_value($rule, $wco_rule_data, $_product);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $wco_rule_data = $this->wco_set_simple_value($rule, $wco_rule_data, $_product);
                                }
                            } else if ($rule['method'] == 'bulk') {
                                if (isset($rule['product_condition'])) {
                                    foreach ($rule['product_condition'] as $key => $product_condition) {
                                        if ($product_condition['type'] == 'products') {
                                            if ($product_condition['method_option'] == 'in_list' && in_array($product_id, $product_condition['product_id'])) {
                                                $wco_rule_data = $this->wco_set_bulk_value($rule, $wco_rule_data, $_product, $quantity);
                                            } else if ($product_condition['method_option'] == 'not_in_list' && !in_array($product_id, $product_condition['product_id'])) {
                                                $wco_rule_data = $this->wco_set_bulk_value($rule, $wco_rule_data, $_product, $quantity);
                                            }
                                        } else if ($product_condition['type'] == 'products_category') {
                                            if ($product_condition['method_option'] == 'in_list') {
                                                if ($terms) {
                                                    foreach ($terms as $term) {
                                                        $_categoryid = $term->term_id;
                                                        if (in_array($_categoryid, $product_condition['product_cat'])) {
                                                            $wco_rule_data = $this->wco_set_bulk_value($rule, $wco_rule_data, $_product, $quantity);
                                                        }
                                                    }
                                                }
                                            } else if ($product_condition['method_option'] == 'not_in_list') {
                                                if ($terms) {
                                                    foreach ($terms as $term) {
                                                        $_categoryid = $term->term_id;
                                                        if (!in_array($_categoryid, $product_condition['product_cat'])) {
                                                            $wco_rule_data = $this->wco_set_bulk_value($rule, $wco_rule_data, $_product, $quantity);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $wco_rule_data = $this->wco_set_bulk_value($rule, $wco_rule_data, $_product, $quantity);
                                }
                            } else if ($rule['method'] == 'group') {
                                if (isset($rule['group_condition'])) {
                                    $group_pricing_method = $rule['group_pricing_method'];
                                    foreach ($rule['group_condition'] as $key => $group_condition) {
                                        if ($group_condition['type'] == 'products') {
                                            $total_product_count = $this->wco_current_product_total($cart_items, $group_condition['product_id']);
                                            if ($total_product_count >= $group_condition['quantity'] && in_array($product_id, $group_condition['product_id'])) {
                                                if ($total_product_count >= $group_condition['quantity']) {
                                                    $group_condition_quantity = $group_condition['quantity'];
                                                    if ($total_product_count % $group_condition['quantity'] == 0) {
                                                        $group_condition_quantity = $total_product_count;
                                                    } else if ($total_product_count % $group_condition_quantity != 0) {
                                                        $group_condition_quantity = $total_product_count - ($total_product_count % $group_condition_quantity);
                                                    }
                                                    if ($group_condition_quantity > $group_quantity_product) {
                                                        $wco_rule_data = $this->wco_set_group_value($rule, $wco_rule_data, $_product, $quantity, $group_condition, $total_product_count, $group_quantity_product);
                                                        $group_quantity_product = $group_quantity_product + $quantity;
                                                    } else {
                                                        $group_quantity_product = 0;
                                                    }
                                                }
                                            }
                                        } else if ($group_condition['type'] == 'products_category') {
                                            if ($terms) {
                                                foreach ($terms as $term) {
                                                    $_categoryid = $term->term_id;
                                                    if (in_array($_categoryid, $group_condition['product_cat'])) {
                                                        $total_cat_product_count = $this->wco_current_cat_total_product($cart_items, $_categoryid, $group_condition['product_cat']);
                                                        if ($total_cat_product_count >= $group_condition['quantity']) {
                                                            $group_condition_quantity = $group_condition['quantity'];
                                                            if ($total_cat_product_count % $group_condition['quantity'] == 0) {
                                                                $group_condition_quantity = $total_cat_product_count;
                                                            } else if ($total_cat_product_count % $group_condition_quantity != 0) {
                                                                $group_condition_quantity = $total_cat_product_count - ($total_cat_product_count % $group_condition_quantity);
                                                            }
                                                            if ($group_condition_quantity > $group_quantity) {
                                                                $wco_rule_data = $this->wco_set_group_value($rule, $wco_rule_data, $_product, $quantity, $group_condition, $total_cat_product_count, $group_quantity);
                                                                $group_quantity = $group_quantity + $quantity;
                                                            } else {
                                                                $group_quantity = 0;
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
                    $cart->cart_contents[$cart_item_key]['wco_rule_data'] = $wco_rule_data;
                }
            }
            return $cart;
        }

        /**
         * @since 1.0
         * @param obj $cart_items
         * @param int $_categoryid
         * @param array $group_condition_cat
         * @return int
         * Return total quantity of specific category
         */
        function wco_current_cat_total_product($cart_items, $_categoryid, $group_condition_cat) {
            $total_quantity = 0;
            //loop through all cart products
            foreach ($cart_items as $product) {
                // See if any product is from the cuvees category or not
                foreach ($group_condition_cat as $single_cat) {
                    if (has_term($single_cat, 'product_cat', $product['product_id'])) {
                        $total_quantity += $product['quantity'];
                    }
                }
            }
            return $total_quantity;
        }

        /**
         * @since 1.0
         * @param obj $cart_items
         * @param array $product_array
         * Return total quantity of product
         */
        function wco_current_product_total($cart_items, $product_array) {
            $total_product = 0;
            //loop through all cart products
            foreach ($cart_items as $product) {
                $product_id = $product['product_id'];
                foreach ($product_array as $single_product) {
                    if ($single_product == $product_id) {
                        $total_product += $product['quantity'];
                    }
                }
            }
            return $total_product;
        }

        /**
         * @since 1.0
         * @param array $rule
         * @param array $wco_rule_data
         * @param obj $_product
         * @param int $quantity
         * @param array $group_condition
         * @param int $total_cat_product_count
         * @param int $group_quantity
         * Set group discount
         */
        function wco_set_group_value($rule, $wco_rule_data, $_product, $quantity, $group_condition, $total_cat_product_count = 0, $group_quantity = 0) {
            $init_price = $_product->get_price();
            $pricing_value = isset($rule['group_pricing_value']) && $rule['group_pricing_value'] != '' ? $rule['group_pricing_value'] : 0;
            $condition_quantity = $group_condition['quantity'];
            if ($rule['group_pricing_method'] == 'fixed_pricing_product') {
                if ($pricing_value > $init_price) {
                    //do nothing
                } else {
                    if ($total_cat_product_count % $condition_quantity == 0) {
                        $new_price = $init_price - $pricing_value;
                        $wco_rule_data['updated_price'] = $new_price;
                    } else if ($total_cat_product_count % $condition_quantity != 0) {
                        $rounded_amount = $init_price - $pricing_value;
                        $temp_quantity = $total_cat_product_count - ($total_cat_product_count % $condition_quantity);
                        $price_multiplier = $temp_quantity / $condition_quantity;
                        if ($required_quantity = $price_multiplier * $condition_quantity - $group_quantity) {
                            if ($quantity >= $required_quantity) {
                                $new_price = (($rounded_amount * $required_quantity) + (($quantity - $required_quantity) * $init_price)) / $quantity;
                                $new_price = round($new_price, wc_get_price_decimals());
                                $wco_rule_data['updated_price'] = $new_price;
                            } else {
                                $new_price = $init_price - $pricing_value;
                                $wco_rule_data['updated_price'] = $new_price;
                            }
                        }
                    }
                }
            } else if ($rule['group_pricing_method'] == 'percentage_product') {
                if ($pricing_value > 100) {
                    //do nothing
                } else {
                    if ($total_cat_product_count % $condition_quantity == 0) {
                        $new_price = $init_price - ($init_price * $pricing_value / 100);
                        $wco_rule_data['updated_price'] = $new_price;
                    } else if ($total_cat_product_count % $condition_quantity != 0) {
                        $rounded_amount = $init_price - ($init_price * $pricing_value / 100);
                        $temp_quantity = $total_cat_product_count - ($total_cat_product_count % $condition_quantity);
                        $price_multiplier = $temp_quantity / $condition_quantity;
                        if ($required_quantity = $price_multiplier * $condition_quantity - $group_quantity) {
                            if ($quantity >= $required_quantity) {
                                $new_price = (($rounded_amount * $required_quantity) + (($quantity - $required_quantity) * $init_price)) / $quantity;
                                $new_price = round($new_price, wc_get_price_decimals());
                                $wco_rule_data['updated_price'] = $new_price;
                            } else {
                                $new_price = $init_price - ($init_price * $pricing_value / 100);
                                $wco_rule_data['updated_price'] = $new_price;
                            }
                        }
                    }
                }
            } else if ($rule['group_pricing_method'] == 'fixed_pricing_group') {
                if ($total_cat_product_count % $condition_quantity == 0) {
                    $adjusted_amount = $init_price - ( $pricing_value / $condition_quantity);
                    $rounded_amount = round($adjusted_amount, wc_get_price_decimals());
                    $new_price = $rounded_amount;
                    $wco_rule_data['updated_price'] = $new_price;
                } else if ($total_cat_product_count % $condition_quantity != 0) {
                    $adjusted_amount = $init_price - ( $pricing_value / $condition_quantity);
                    $rounded_amount = round($adjusted_amount, wc_get_price_decimals());
                    $temp_quantity = $total_cat_product_count - ($total_cat_product_count % $condition_quantity);
                    $price_multiplier = $temp_quantity / $condition_quantity;
                    if ($required_quantity = $price_multiplier * $condition_quantity - $group_quantity) {
                        if ($quantity >= $required_quantity) {
                            $new_price = (($rounded_amount * $required_quantity) + (($quantity - $required_quantity) * $init_price)) / $quantity;
                            $new_price = round($new_price, wc_get_price_decimals());
                            $wco_rule_data['updated_price'] = $new_price;
                        } else {
                            $new_price = $rounded_amount;
                            $wco_rule_data['updated_price'] = $new_price;
                        }
                    }
                }
            }
            return $wco_rule_data;
        }

        /**
         * @since 1.0
         * @param array $rule
         * @param array $wco_rule_data
         * @param obj $_product
         * Set simple discount
         */
        function wco_set_simple_value($rule, $wco_rule_data, $_product) {
            $init_price = $_product->get_price();
            $pricing_value = isset($rule['pricing_value']) && $rule['pricing_value'] != '' ? $rule['pricing_value'] : 0;
            if ($rule['pricing_method'] == 'fixed_pricing') {
                if ($pricing_value > $init_price) {
                    //do nothing
                } else {

                    $new_price = $init_price - $pricing_value;
                    //$_product->set_price($new_price);
                    $wco_rule_data['updated_price'] = $new_price;
                }
            } else if ($rule['pricing_method'] == 'percentage') {
                if ($pricing_value > 100) {
                    //do nothing
                } else {
                    $new_price = $init_price - ($init_price * $pricing_value / 100);
                    //$_product->set_price($new_price);
                    $wco_rule_data['updated_price'] = $new_price;
                }
            }
            return $wco_rule_data;
        }

        /**
         * @since 1.0
         * @param array $rule
         * @param array $wco_rule_data
         * @param obj $_product
         * @param int $quantity
         * Set bulk discount on cart page
         */
        function wco_set_bulk_value($rule, $wco_rule_data, $_product, $quantity) {
            if (isset($rule['quantity_range'])) {
                $init_price = $_product->get_price();
                $applied_offer = '';
                foreach ($rule['quantity_range'] as $key => $value) {
                    $minValue = $value['min_quantity'];
                    $maxValue = $value['max_quantity'];
                    if ($maxValue == '' && $quantity >= $minValue) {
                        $applied_offer = $key;
                    } else if ($quantity >= $minValue && $quantity <= $maxValue) {
                        $applied_offer = $key;
                    }
                }
                if (isset($rule['quantity_range'][$applied_offer])) {
                    $applied_arr = $rule['quantity_range'][$applied_offer];
                    $pricing_value = $applied_arr['discount_amount'] != '' ? $applied_arr['discount_amount'] : 0;
                    if ($applied_arr['discount_type'] == 'fixed_pricing') {
                        if ($pricing_value > $init_price) {
                            //do nothing
                        } else {

                            $new_price = $init_price - $pricing_value;
                            //$_product->set_price($new_price);
                            $wco_rule_data['updated_price'] = $new_price;
                        }
                    } else if ($applied_arr['discount_type'] == 'percentage') {
                        if ($pricing_value > 100) {
                            //do nothing
                        } else {
                            $new_price = $init_price - ($init_price * $pricing_value / 100);
                            //$_product->set_price($new_price);
                            $wco_rule_data['updated_price'] = $new_price;
                        }
                    }
                }
            }
            return $wco_rule_data;
        }

        /**
         * @since 1.0
         * @param string $price_html
         * @param obj $cart_item
         * @param string $cart_item_key
         * @return string
         */
        public function wcoffers_cart_item_price($price_html, $cart_item, $cart_item_key) {
            if ($this->reg_offer === TRUE || $this->second_offer === TRUE) {
                return $price_html;
            }
            if (isset($cart_item['wco_rule_data']['init_price'])) {
                // Get adjusted price
                $adjusted_price = $cart_item['data']->get_price();
                // Adjusted price is lower than initial price
                if ($cart_item['wco_rule_data']['updated_price'] && $cart_item['wco_rule_data']['init_price'] > $cart_item['wco_rule_data']['updated_price'])
                    $price_html = '<del style="' . $this->old_css . '">' . wc_price($cart_item['wco_rule_data']['init_price']) . '</del> <span style="' . $this->new_css . '">' . wc_price($adjusted_price) . '</span>';
            }
            return $price_html;
        }

        /**
         * @since 1.0
         * @global obj $woocommerce
         * @param obj $cart
         * Set new price for product
         */
        function wcoffers_calculate_product_discount($cart = null) {
            if ($this->reg_offer === TRUE || $this->second_offer === TRUE) {
                return false;
            }
            if (!is_a($cart, 'WC_Cart')) {
                global $woocommerce;
                $cart = $woocommerce->cart;
            }
            $cart_items = $cart->cart_contents;
            if ($cart_items) {
                foreach ($cart_items as $cart_item_key => $cart_item_data) {
                    $_product = $cart_item_data['data'];
                    if (isset($cart_item_data['wco_rule_data']) && isset($cart_item_data['wco_rule_data']['updated_price']) && $cart_item_data['wco_rule_data']['updated_price']) {
                        $_product->set_price($cart_item_data['wco_rule_data']['updated_price']);
                    }
                }
            }
        }

    }

    new wcoffersIndividualOfferDiscount();
}