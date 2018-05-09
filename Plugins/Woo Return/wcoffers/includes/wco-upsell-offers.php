<?php
if (!class_exists('wcoffersUpsellOffers')) {

    class wcoffersUpsellOffers {

        public function __construct() {
            add_filter('woocommerce_get_checkout_order_received_url', array($this, 'wcoffers_checkout_order_received_url'), 10, 2);
            add_shortcode('wc_upsell_default_page', array($this, 'wcoffers_default_offer_page_shortcode'));
            add_action('wp_loaded', array($this, 'wcoffers_process_offer'), 2);
            add_action('wp_loaded', array($this, 'wcoffers_process_before_checkout_offer'), 1);
            add_action('wp_loaded', array($this, 'wcoffers_process_paypal_offer'), 3);
            add_action('woocommerce_thankyou', array($this, 'wcoffers_woocommerce_thankyou'));
            add_action('woocommerce_order_details_after_order_table_items', array($this, 'wcoffers_order_items_table'));
            add_filter('woocommerce_get_order_item_totals', array($this, 'wcoffers_get_order_item_totals'), 1, 3);
            add_filter('woocommerce_order_get_total', array($this, 'wcoffers_order_amount_total'), 10, 2);
            add_action('woocommerce_admin_order_data_after_order_details', array($this, 'wcoffers_admin_after_table'));
            add_filter('woocommerce_payment_successful_result', array($this, 'wcoffers_paypal_redirect_url'), 10, 2);
            add_filter('wp_enqueue_scripts', array($this, 'wcoffers_front_page_scripts'), 999);
            add_filter('template_redirect', array($this, 'wcoffers_before_proceed_to_checkout'));
            add_action('woocommerce_before_calculate_totals', array($this, 'wcoffers_add_custom_price'), 10, 1);
            add_action('woocommerce_cart_item_product', array($this, 'wcoffers_change_custom_price'), 10, 3);
        }

        /**
         * @since 1.0
         * @param array $_product
         * @param array $cart_item
         * @param string $cart_item_key
         * @return Product
         */
        function wcoffers_change_custom_price($_product, $cart_item, $cart_item_key) {
            if (isset($_GET['wcu_added'])) {
                $added_product = explode(',', $_GET['wcu_added']);
                $first_value = current($added_product);
                if ($this->wcoffers_verify_nonce($_GET['wcu_verify'], 'wcu_offer_' . $first_value)) {
                    if ((is_array($added_product) && in_array($cart_item['product_id'], $added_product)) || ($cart_item['product_id'] == $_GET['wcu_added'])) {
                        if (session_id() == '') {
                            session_start();
                            $offer_product_price = isset($_SESSION['wcu_offer_product_disc'][$cart_item['product_id']]) ? $_SESSION['wcu_offer_product_disc'][$cart_item['product_id']] : '';
                        } else {
                            $offer_product_price = isset($_SESSION['wcu_offer_product_disc'][$cart_item['product_id']]) ? $_SESSION['wcu_offer_product_disc'][$cart_item['product_id']] : '';
                        }
                        if ($offer_product_price != '') {
                            $_product = $this->wcoffers_product_set_custom_price($_product, $offer_product_price, array(), 'charge');
                            return $_product;
                        }
                    }
                }
            }
            return $_product;
        }

        /**
         * @since 1.0
         * @param array $cart_object
         * Provide discount on checkout page
         */
        function wcoffers_add_custom_price($cart_object) {
            if (isset($_GET['wcu_added'])) {
                $added_product = explode(',', $_GET['wcu_added']);
                $first_value = current($added_product);
                if ($this->wcoffers_verify_nonce($_GET['wcu_verify'], 'wcu_offer_' . $first_value)) {
                    foreach (WC()->cart->get_cart() as $key => $value) {
                        if ((is_array($added_product) && in_array($value['product_id'], $added_product)) || ($value['product_id'] == $_GET['wcu_added'])) {
                            $_product = $value['data'];
                            if (session_id() == '') {
                                session_start();
                                $offer_product_price = isset($_SESSION['wcu_offer_product_disc'][$value['product_id']]) ? $_SESSION['wcu_offer_product_disc'][$value['product_id']] : '';
                            } else {
                                $offer_product_price = isset($_SESSION['wcu_offer_product_disc'][$value['product_id']]) ? $_SESSION['wcu_offer_product_disc'][$value['product_id']] : '';
                            }
                            if ($offer_product_price != '') {
                                $this->wcoffers_product_set_custom_price($_product, $offer_product_price, array(), 'charge');
                            }
                        }
                    }
                }
            }
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * @global array $wpdb
         * @return Get upsell for cart items
         */
        function wcoofers_get_product_id_from_upsell() {
            global $woocommerce;
            $upsell = FALSE;
            $items = $woocommerce->cart->get_cart();
            $upsell_target_query = '';
            foreach ($items as $item => $values) {
                if (!empty($values['product_id'])) {
                    // custom item qty
                    $upsell_target_query .= "( upsell_target LIKE '%{$values['product_id']}%' AND upsell_target_qty = {$values['quantity']} ) OR ";
                    // any item qty
                    $upsell_target_query .= "( upsell_target LIKE '%{$values['product_id']}%' AND upsell_target_qty = 0 ) OR ";
                }
            }
            $upsell_target_query = rtrim($upsell_target_query, 'OR ');

            if (!empty($upsell_target_query)) {
                global $wpdb;
                $table_wc_upsells = $wpdb->prefix . 'wc_upsells';
                $upsell = $wpdb->get_row(
                        "	SELECT
						id,
						upsell_name,
						upsell_display,
						upsell_mode,
						upsell_target,
						upsell_offers_skip,
						upsell_offers,
						upsell_active
					FROM
						$table_wc_upsells
					WHERE
						(
							{$upsell_target_query} OR
							upsell_mode = 'global'
						)
						AND
							upsell_active = 1
					LIMIT 1; ", ARRAY_A
                );
            }
            return $upsell;
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * Upsell procedure before checkout
         */
        function wcoffers_process_before_checkout_offer() {
            if (!empty($_GET['wcu_rd']) && !empty($_GET['wcu_n']) && !empty($_GET['wcu_s']) && (!empty($_GET['wcu_yes']) || !empty($_GET['wcu_no']) )) {
                $options = get_option('wco_upsell_options');
                $upsell = $this->wcoofers_get_product_id_from_upsell();
                if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                    $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                    $offer_id = intval($_GET['wcu_n']);
                    if (!empty($upsell['upsell_offers'][$offer_id])) {
                        $offer = $upsell['upsell_offers'][$offer_id];
                        if (!empty($offer['product_id'])) {
                            global $woocommerce;
                            if ($this->wcoffers_verify_nonce($_GET['wcu_s'], 'wcu_offer_' . $upsell['id'])) {
                                $offer_action = '';
                                $url_append = '';
                                $url_append_yes = '';
                                if (!empty($_GET['wcu_yes'])) {
                                    $offer['product_id'] = explode(',', $offer['product_id']);
                                    $offer['product_id'] = $offer['product_id'][0];
                                    $offer_product = wc_get_product($offer['product_id']);
                                    if (!empty($offer_product)) {
                                        if (empty($offer['product_price'])) {
                                            $offer['product_price'] = '100%';
                                        }
                                        $append_this = '';
                                        if (isset($_GET['wcu_added'])) {
                                            $append_this = ',' . $_GET['wcu_added'];
                                        }
                                        $url_append_yes = 'wcu_added=' . $offer_product->get_id() . $append_this . '&wcu_verify=' . $this->wcoffers_create_nonce('wcu_offer_' . $offer_product->get_id());
                                        if (session_id() == '') {
                                            session_start();
                                            if (is_array($_SESSION['wcu_offer_product_disc']) && !empty($_SESSION['wcu_offer_product_disc'])) {
                                                $_SESSION['wcu_offer_product_disc'][$offer_product->get_id()] = $offer['product_price'];
                                            } else {
                                                $_SESSION['wcu_offer_product_disc'][$offer_product->get_id()] = $offer['product_price'];
                                            }
                                        } else {
                                            if (is_array($_SESSION['wcu_offer_product_disc']) && !empty($_SESSION['wcu_offer_product_disc'])) {
                                                $_SESSION['wcu_offer_product_disc'][$offer_product->get_id()] = $offer['product_price'];
                                            } else {
                                                $_SESSION['wcu_offer_product_disc'][$offer_product->get_id()] = $offer['product_price'];
                                            }
                                        }
                                        $woocommerce->cart->add_to_cart($offer_product->get_id());
                                    }
                                    $offer_action = $offer['offer_accepted_action'];
                                    $offer_action_custom_page = $offer['offer_accepted_custom_page'];
                                } else {
                                    $append_this = '';
                                    if (isset($_GET['wcu_added'])) {
                                        $append_this = $_GET['wcu_added'];
                                        $exp_product = explode(',', $_GET['wcu_added']);
                                        $first_product = current($exp_product);
                                        $url_append_yes = 'wcu_added=' . $append_this . '&wcu_verify=' . $this->wcoffers_create_nonce('wcu_offer_' . $first_product);
                                    }
                                    $offer_action = $offer['offer_rejected_action'];
                                    $offer_action_custom_page = $offer['offer_rejected_custom_page'];
                                }
                                if (!empty($offer_action)) {
                                    // if action = show_offer_N, get link to the next N offer
                                    if (mb_strpos($offer_action, 'show_offer_') !== FALSE) {
                                        $next_offer_id = mb_substr(
                                                $offer_action, mb_strrpos($offer_action, '_') + 1
                                        );

                                        // check if next offer exists
                                        if (!empty($upsell['upsell_offers_skip'])) {
                                            $next_offer = $this->wcoffers_before_proceed_to_offers($woocommerce->cart->get_cart(), $upsell, $next_offer_id);
                                        } else {
                                            $next_offer = $upsell['upsell_offers'][$next_offer_id];
                                            $next_offer['offer_id'] = $next_offer_id;
                                        }

                                        // if next_offer is offer
                                        if (!empty($next_offer) && is_array($next_offer)) {
                                            $url_append = '&wcu_s=' . $_GET['wcu_s'] .
                                                    '&wcu_n=' . $next_offer['offer_id'] . '&wcu_co=before_checkout' .
                                                    (!empty($url_append_yes) ? '&' . $url_append_yes : '' );

                                            if ($next_offer['offer_method'] == 'custom_page' && !empty($next_offer['offer_custom_page'])) {
                                                $next_offer['offer_custom_page'] = trim($next_offer['offer_custom_page']);
                                                if (is_numeric($next_offer['offer_custom_page'])) {
                                                    $next_offer['offer_custom_page'] = get_permalink(intval($next_offer['offer_custom_page']));
                                                }
                                                $result = $this->wcoffers_url_append(
                                                        $next_offer['offer_custom_page'], $url_append
                                                );
                                            } elseif ($next_offer['offer_method'] == 'default') {
                                                $default_offer_page = $result;
                                                if (!empty($options['default_offer_page'])) {
                                                    $default_offer_page = get_permalink(intval($options['default_offer_page']));
                                                }
                                                $result = $this->wcoffers_url_append(
                                                        $default_offer_page, $url_append
                                                );
                                            }
                                        } else {
                                            $result = $woocommerce->cart->get_checkout_url();
                                        }
                                    } elseif ($offer_action == 'show_custom' && !empty($offer_action_custom_page)) {
                                        $url_append = (!empty($url_append_yes) ? '&' . $url_append_yes : '' );
                                        if (is_numeric($offer_action_custom_page)) {
                                            $offer_action_custom_page = get_permalink(intval($offer_action_custom_page));
                                        }
                                        $result = $this->wcoffers_url_append(
                                                $offer_action_custom_page, $url_append
                                        );
                                    } else {
                                        $url_append = $url_append_yes;
                                        if ($url_append_yes == '') {
                                            $url_append = 'wcu_nadded=yes';
                                        }
                                        $result = $woocommerce->cart->get_checkout_url();
                                        $result = $this->wcoffers_url_append(
                                                $result, $url_append
                                        );
                                    }
                                }
                            } else {
                                $result = $woocommerce->cart->get_checkout_url();
                            }
                        } else {
                            $result = $woocommerce->cart->get_checkout_url();
                        }
                    } else {
                        $result = $woocommerce->cart->get_checkout_url();
                    }
                } else {
                    $result = $woocommerce->cart->get_checkout_url();
                }
                if (!empty($result)) {
                    wp_redirect($result);
                    die();
                }
            }
        }

        /**
         * @since 1.0
         * @param array $items
         * @param array $upsell
         * @param int $current_offer_id
         * @return Return product with custom price
         */
        function wcoffers_before_proceed_to_offers($items, $upsell, $current_offer_id = 1) {
            $result = FALSE;
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (!empty($item['product_id'])) {
                        $order_items_ids[] = $item['product_id'];
                    }
                }
                if (!empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                    $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                }
                $current_offer = $upsell['upsell_offers'][$current_offer_id];
                if (!empty($current_offer)) {
                    $current_offer['product_id'] = explode(',', $current_offer['product_id']);
                    foreach ($current_offer['product_id'] as $id) {
                        if (in_array($id, $order_items_ids)) {
                            $result = $current_offer['offer_accepted_action'];
                            break;
                        }
                    }
                    if ($result === FALSE) {
                        $current_offer['offer_id'] = $current_offer_id;
                        $result = $current_offer;
                    } else {
                        if (mb_strpos($result, 'show_offer_') !== FALSE) {
                            $next_offer_id = mb_substr(
                                    $result, mb_strrpos($result, '_') + 1
                            );
                            $result = $this->wcoffers_before_proceed_to_offers($items, $upsell, $next_offer_id);
                        }
                    }
                }
            }
            return $result;
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * @global array $wpdb
         * Check upsell product is in cart or not
         */
        function wcoffers_before_proceed_to_checkout() {
            global $woocommerce;
            $result = FALSE;
            //Remove session for offer product
            $options = get_option('wco_upsell_options');
            $default_offer_page = '';
            if (!empty($options['default_offer_page'])) {
                $default_offer_page = $options['default_offer_page'];
            }
            $page_id = get_queried_object_id();
            if ($default_offer_page != $page_id) {
                if (session_id() == '')
                    session_start();
                if (is_cart()) {
                    $items = $woocommerce->cart->get_cart();
                    if ($items) {
                        foreach ($items as $cart_item_key => $cart_item) {
                            if (isset($_SESSION['wcu_offer_product_disc'][$cart_item['product_id']])) {
                                wc()->cart->remove_cart_item($cart_item_key);
                            }
                        }
                    }
                }
                if (!is_checkout()) {
                    unset($_SESSION['wcu_offer_product_disc']);
                }
            }
            $prev_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            $cart_url = get_permalink(get_option('woocommerce_cart_page_id'));
            //Redirect to offer page when offer is available
            if (is_checkout() && $prev_url == $cart_url && (!isset($_GET['wcu_added']) || (isset($_GET['wcu_nadded']) && $_GET['wcu_nadded'] != 'yes'))) {
                $options = get_option('wco_upsell_options');
                $items = $woocommerce->cart->get_cart();
                $upsell = $this->wcoofers_get_product_id_from_upsell();
                if (!empty($upsell)) {
                    if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers']) && $upsell['upsell_display'] == 'before_checkout') {
                        $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                        // get first offer
                        if (!empty($upsell['upsell_offers_skip'])) {
                            $first_offer = $this->wcoffers_before_proceed_to_offers($items, $upsell, 1);
                        } else {
                            $first_offer = $upsell['upsell_offers'][1];
                            $first_offer['offer_id'] = 1;
                        }

                        // if first_offer is offer
                        if (!empty($first_offer) && is_array($first_offer)) {
                            $offer = $first_offer;
                            if (!empty($offer['product_id'])) {
                                $url_append = '&wcu_n=' . $offer['offer_id'] .
                                        '&wcu_s=' . $this->wcoffers_create_nonce('wcu_offer_' . $upsell['id']) .
                                        '&wcu_co=before_checkout';

                                // CUSTOM
                                if ($offer['offer_method'] == 'custom_page' && !empty($offer['offer_custom_page'])) {
                                    $offer['offer_custom_page'] = trim($offer['offer_custom_page']);

                                    // if post_id get it's link
                                    if (is_numeric($offer['offer_custom_page'])) {
                                        $offer['offer_custom_page'] = get_permalink(intval($offer['offer_custom_page']));
                                    } elseif (mb_strpos($offer['offer_custom_page'], '/') === 0) {
                                        $offer['offer_custom_page'] = home_url() . $offer['offer_custom_page'];
                                    }
                                    $result = $this->wcoffers_url_append(
                                            $offer['offer_custom_page'], $url_append
                                    );
                                    wp_redirect($result);
                                    exit;
                                } elseif ($offer['offer_method'] == 'default') {

                                    $default_offer_page = $result;

                                    if (!empty($options['default_offer_page'])) {
                                        $default_offer_page = get_permalink(intval($options['default_offer_page']));
                                    }
                                    // end
                                    $result = $this->wcoffers_url_append(
                                            $default_offer_page, $url_append
                                    );
                                    wp_redirect($result);
                                    exit;
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * @since 1.0
         * @global array $post
         * @global array $woocommerce
         * Include woocommerce css and js for offer page
         */
        function wcoffers_front_page_scripts() {
            global $post, $woocommerce;
            if (!empty($post)) {
                $options = get_option('wco_upsell_options');
                if (!empty($options['default_offer_page'])) {
                    $options['default_offer_page'] = intval($options['default_offer_page']);
                    if ($options['default_offer_page'] == $post->ID || (!empty($_GET['wcu']) )) {
                        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
                        $assets_path = str_replace(array('http:', 'https:'), '', WC()->plugin_url()) . '/assets/';
                        wp_enqueue_style('woocommerce');
                        wp_enqueue_style('photoswipe', $assets_path . 'css/photoswipe/photoswipe.css', array(), WC_VERSION);
                        wp_enqueue_style('photoswipe-default-skin', $assets_path . 'css/photoswipe/default-skin/default-skin.css', array('photoswipe'), WC_VERSION);
                        wp_enqueue_style('default-page-style', plugins_url() . '/wcoffers/assets/css/default_page_style.css');
                        wp_enqueue_script('wc-single-product', $assets_path . 'js/frontend/single-product' . $suffix . '.js', array('jquery'), WC_VERSION, TRUE);
                        wp_enqueue_script('flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array('jquery'), '2.6.1', TRUE);
                        wp_enqueue_script('zoom', $assets_path . 'js/zoom/jquery.zoom' . $suffix . '.js', array('jquery'), '1.7.15', TRUE);
                        wp_enqueue_script('photoswipe', $assets_path . 'js/photoswipe/photoswipe' . $suffix . '.js', array(), '4.1.1', TRUE);
                        wp_enqueue_script('photoswipe-ui-default', $assets_path . 'js/photoswipe/photoswipe-ui-default' . $suffix . '.js', array('photoswipe'), '4.1.1', TRUE);
                        add_action('wp_footer', 'woocommerce_photoswipe');
                    }
                }
            }
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * Proceed payment for paypal
         */
        function wcoffers_process_paypal_offer() {
            if (!empty($_GET['wcu']) && !empty($_GET['wcu_n']) && !empty($_GET['wcu_s'])) {
                global $woocommerce;
                $order_key = $_GET['wcu'];
                $order_id = wc_get_order_id_by_order_key($order_key);
                if (!empty($order_id)) {
                    $order_id = $this->wcoffers_get_order_id($order_id);
                    $order = wc_get_order($order_id);
                    if (!empty($order)) {
                        $gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
                        if (!empty($gateways[$order->get_payment_method()]) && ( $order->get_payment_method() == 'paypal' )) {
                            $WCO_PayPal_URL = new WCO_PayPal_URL();
                            $WCO_PayPal_URL->wcoffers_paypal_api_do_final_payment($order_id);
                        }
                    }
                }
            }
        }

        /**
         * @since 1.0
         * @param array $result
         * @param int $order_id
         * Change WooCommerce paypal redirect URL
         */
        function wcoffers_paypal_redirect_url($result, $order_id) {
            $order = wc_get_order($order_id);
            $options = get_option('wco_upsell_options', array());
            if (!empty($order) && $order->get_payment_method() == 'paypal' && empty($options['disable_paypal'])) {
                $upsell = $this->wcoffers_get_upsell_for_order_items($order);
                if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                    $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                    // get first offer
                    if (!empty($upsell['upsell_offers_skip'])) {
                        $first_offer = $this->wcoffers_get_suitable_offer($order, $upsell, 1);
                    } else {
                        $first_offer = $upsell['upsell_offers'][1];
                        $first_offer['offer_id'] = 1;
                    }
                    // if first_offer is offer
                    if (!empty($first_offer) && is_array($first_offer)) {
                        $offer = $first_offer;
                        if (!empty($offer['product_id'])) {
                            $WCO_PayPal_URL = new WCO_PayPal_URL();
                            $result = $WCO_PayPal_URL->wcoffers_paypal_api_set_express_checkout($order);
                            if (!empty($result)) {
                                return $result;
                            }
                        }
                    }
                }
            }
            return $result;
        }

        /**
         * @since 1.0
         * @param array $order
         * Add parent order detail
         */
        function wcoffers_admin_after_table($order) {
            $result = '';

            // get parent order id on child
            $parent_order = get_post_meta($order->get_id(), 'wcoffers_parent_order_id', TRUE);

            // get all child orders on parent
            $child_orders = get_posts(array(
                'post_per_page' => 5,
                'post_type' => 'shop_order',
                'post_status' => 'any',
                'meta_key' => 'wcoffers_parent_order_id',
                'meta_value' => $order->get_id(),
                'orderby' => 'ID',
                'order' => 'ASC',
            ));

            if (!empty($parent_order) || !empty($child_orders)) {
                $result .= '<style type="text/css">
                    .wcoffers_linked_orders > div {
                            margin-bottom: 5px;
                            padding-bottom: 5px;
                            border-bottom: 1px solid #EEE;
                    }
                    </style>';

                $result .= '<div class="clear"></div>';
                $result .= '<div class="wcoffers_linked_orders">';
                $result .= '<div><h4>WC offers linked Orders</h4></div>';

                // display parent order link
                if (!empty($parent_order)) {
                    $parent_order = wc_get_order($parent_order); // numeric
                    if (!empty($parent_order)) {
                        $result .= '<div><a href="' . get_edit_post_link($parent_order->get_id()) . '">Parent order #' . $parent_order->get_order_number() . '</a> ' . wc_price($parent_order->get_total()) . '</div>';
                    }
                }

                // display child order links
                if (!empty($child_orders)) {
                    foreach ($child_orders as $child_order) {
                        $child_order = wc_get_order($child_order); // numeric
                        $order_item = $child_order->get_items();
                        $order_item = array_shift($order_item);
                        $order_item = $order_item['name'];
                        $result .= '<div><a href="' . get_edit_post_link($child_order->get_id()) . '">Upsell order #' . $child_order->get_order_number() . '</a> &rarr; ' . $order_item . ' <br /> ' . wc_price($child_order->get_total()) . '</div>';
                    }
                }
                $result .= '</div>';
                echo $result;
            }
        }

        /**
         * @since 1.0
         * @param int $parent_order_total
         * @param int $parent_order
         * Return total
         */
        public function wcoffers_order_amount_total($parent_order_total, $parent_order) {

            $is_custom_ty_page = FALSE;

            // change order total
            $combined_order_total = 0;
            if (!empty($parent_order) && ( is_order_received_page() || is_wc_endpoint_url('view-order') || $is_custom_ty_page )) {
                $countable_order_statuses = $this->wcoffers_get_countable_order_statuses();

                // parent order
                if (in_array($parent_order->get_status(), $countable_order_statuses)) {
                    $combined_order_total = $combined_order_total + $parent_order->get_total('other');
                }

                // child orders
                $child_orders = get_posts(array(
                    'posts_per_page' => -1,
                    'post_type' => 'shop_order',
                    'post_status' => 'any',
                    'meta_key' => 'wcoffers_parent_order_id',
                    'meta_value' => $parent_order->get_id(),
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ));

                if (!empty($child_orders)) {
                    $wc_order_factory = new WC_Order_Factory();
                    foreach ($child_orders as &$child_order) {
                        $child_order = $wc_order_factory->get_order($child_order->ID);
                        if (in_array($child_order->get_status(), $countable_order_statuses)) {
                            $combined_order_total = $combined_order_total + $child_order->get_total('other');
                        }
                    }
                    $parent_order_total = $combined_order_total;
                }
            }
            return $parent_order_total;
        }

        /**
         * @since 1.0
         * @param array $total_rows
         * @param int $parent_order
         * @param string $tax_display
         * Return total of order item
         */
        public function wcoffers_get_order_item_totals($total_rows, $parent_order, $tax_display) {

            if ($tax_display == 'hack') {
                $tax_display = '';
                return $total_rows;
            }

            if (!empty($parent_order)) {
                $linked_orders = get_posts(array(
                    'posts_per_page' => -1,
                    'post_type' => 'shop_order',
                    'post_status' => 'any',
                    'meta_key' => 'wcoffers_parent_order_id',
                    'meta_value' => $parent_order->get_id(),
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ));

                if (!empty($linked_orders)) {
                    $items = array();
                    $items_total = 0;
                    $items_subtotal = 0;
                    $items_shipping = 0;
                    $items_tax = 0;
                    $items_discount = 0;
                    $countable_order_statuses = $this->wcoffers_get_countable_order_statuses();
                    $items = $parent_order->get_items();
                    if (!empty($items)) {
                        foreach ($items as $order_item_id => $item) {
                            if (in_array($parent_order->get_status(), $countable_order_statuses)) {
                                $items_subtotal += $parent_order->get_line_subtotal($item, TRUE); // $inc_tax = TRUE
                                if ($parent_order->get_prices_include_tax() === FALSE) {
                                    $items_subtotal = $items_subtotal - $parent_order->get_line_tax($item);
                                }
                                $items_total += $parent_order->get_line_total($item);
                            }
                        }
                        if (in_array($parent_order->get_status(), $countable_order_statuses)) {
                            $items_shipping += $parent_order->get_total_shipping();
                            $items_tax += $parent_order->get_total_tax();
                            $items_total += $parent_order->get_total_shipping() + $parent_order->get_total_tax();
                            $items_discount += $parent_order->get_total_discount(FALSE); // $ex_tax = FALSE
                        }
                    }

                    // identify custom or default tax key index
                    $tax_key = 'tax';
                    $tax_label = 'Tax:';
                    if (!empty($total_rows['tax'])) {
                        $tax_key = 'tax';
                    } else {
                        foreach ($total_rows as $k => $v) {
                            if (mb_strpos($k, 'tax') !== FALSE) {
                                $tax_key = $k;
                                if (!empty($v['label'])) {
                                    $tax_label = $v['label'];
                                }
                                break;
                            }
                        }
                    }

                    // display additional orders items
                    $_order_factory = new WC_Order_Factory();
                    $child_total_rows = array();
                    foreach ($linked_orders as $order) {
                        $order = $_order_factory->get_order($order->ID);
                        $items = $order->get_items();
                        if (!empty($items)) {
                            foreach ($items as $order_item_id => $item) {
                                if (in_array($order->get_status(), $countable_order_statuses)) {
                                    $items_subtotal += $order->get_line_subtotal($item, TRUE); // $inc_tax = TRUE
                                    if ($order->get_prices_include_tax() === FALSE) {
                                        $items_subtotal = $items_subtotal - $order->get_line_tax($item);
                                    }
                                    $items_total += $order->get_line_total($item);
                                }
                            }
                        }

                        if (in_array($order->get_status(), $countable_order_statuses)) {
                            $items_shipping += $order->get_total_shipping();
                            $items_tax += $order->get_total_tax();
                            $items_total += $order->get_total_shipping() + $order->get_total_tax();
                            $items_discount += $order->get_total_discount(FALSE); // $ex_tax = FALSE
                        }

                        if ($order->get_prices_include_tax() === FALSE) {
                            $child_total_rows = $order->get_order_item_totals('hack');
                            foreach ($child_total_rows as $k => $v) {
                                if (mb_strpos($k, 'tax') !== FALSE) {
                                    $tax_key = $k;
                                    if (!empty($v['label'])) {
                                        $tax_label = $v['label'];
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    // update total rows
                    if (!empty($items_subtotal)) {
                        $total_rows['cart_subtotal']['value'] = wc_price($items_subtotal);
                    }
                    if (!empty($items_discount)) {
                        $total_rows['discount']['value'] = '-' . wc_price($items_discount);
                    }
                    if (!empty($items_shipping)) {
                        $total_rows['shipping']['value'] = wc_price($items_shipping) . '&nbsp;<small class="shipped_via">' . sprintf(__('via %s', 'woocommerce'), $parent_order->get_shipping_method()) . '</small>';
                    }
                    if (!empty($items_total)) {
                        $total_rows['order_total']['value'] = wc_price($items_total);
                    }

                    // sort total rows and set the tax row properly
                    if ($order->get_prices_include_tax() === FALSE) {
                        unset($total_rows[$tax_key]);
                        $total_rows_updated = array();
                        foreach ($total_rows as $k => $v) {
                            $total_rows_updated[$k] = $v;
                            if ($k == 'cart_subtotal' && !empty($items_tax)) {
                                $total_rows_updated[$tax_key]['value'] = wc_price($items_tax);
                                if (empty($total_rows[$tax_key]['label'])) {
                                    $total_rows_updated[$tax_key]['label'] = $tax_label;
                                }
                            }
                        }
                        $total_rows = $total_rows_updated;
                    }
                }
            }

            return $total_rows;
        }

        /**
         * @since 1.0
         * @param int $parent_order
         * Return html of newly added product
         */
        public function wcoffers_order_items_table($parent_order) {
            $result = '';
            if (!empty($parent_order)) {
                $show_purchase_note = $parent_order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
                $linked_orders = get_posts(array(
                    'posts_per_page' => -1,
                    'post_type' => 'shop_order',
                    'post_status' => 'any',
                    'meta_key' => 'wcoffers_parent_order_id',
                    'meta_value' => $parent_order->get_id(),
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ));
                if (!empty($linked_orders)) {
                    ob_start();
                    foreach ($linked_orders as $order) {
                        $order = wc_get_order($order->ID);
                        foreach ($order->get_items() as $item_id => $item) {
                            $item->set_name($item->get_name() . ' (#' . $order->get_id() . ', ' . $order->get_status() . ')');
                            $product = apply_filters('woocommerce_order_item_product', $item->get_product(), $item);
                            wc_get_template('order/order-details-item.php', array(
                                'order' => $order,
                                'item_id' => $item_id,
                                'item' => $item,
                                'show_purchase_note' => $show_purchase_note,
                                'purchase_note' => $product ? $product->get_purchase_note() : '',
                                'product' => $product,
                            ));
                        }
                    }
                    $result = ob_get_clean();
                }
            }
            echo $result;
        }

        /**
         * @since 1.0
         * @param int $order_id
         * Display specific message on thank you page
         */
        function wcoffers_woocommerce_thankyou($order_id) {

            $result = $this->wcoffers_custom_page_upsell_accepted_msg();
            echo $result;
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * @param URL $result
         * @param array $order
         * @return Create offer page link or default page
         */
        function wcoffers_checkout_order_received_url($result, $order) {

            global $woocommerce;
            if (!empty($order)) {

                // exceptions, when not to run upsell offers
                if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order, array('renewal', 'resubscribe', 'switch'))) {
                    return $result;
                }
                $options = get_option('wco_upsell_options', array());
                $gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
                if (
                        ($order->get_payment_method() == 'cod' && empty($options['disable_cod'])) ||
                        ($order->get_payment_method() == 'bacs' && empty($options['disable_bacs'])) ||
                        ($order->get_payment_method() == 'cheque' && empty($options['disable_cp'])) ||
                        ($order->get_payment_method() == 'paypal' && empty($options['disable_paypal']))
                ) {
                    if (!empty($_GET['wcu_n']) && $_GET['wcu_n'] == 'ty') {
                    } else {
                        $upsell = $this->wcoffers_get_upsell_for_order_items($order);
                        if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers']) && $upsell['upsell_display'] == 'after_checkout') {
                            $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);

                            // get first offer
                            if (!empty($upsell['upsell_offers_skip'])) {
                                $first_offer = $this->wcoffers_get_suitable_offer($order, $upsell, 1);
                            } else {
                                $first_offer = $upsell['upsell_offers'][1];
                                $first_offer['offer_id'] = 1;
                            }

                            // if first_offer is offer
                            if (!empty($first_offer) && is_array($first_offer)) {
                                $offer = $first_offer;

                                if (!empty($offer['product_id'])) {

                                    $url_append = 'wcu=' . $order->get_order_key() .
                                            '&wcu_n=' . $offer['offer_id'] .
                                            '&wcu_s=' . $this->wcoffers_create_nonce('wcu_offer_' . $order->get_id() . $upsell['id']);

                                    if ($offer['offer_method'] == 'custom_page' && !empty($offer['offer_custom_page'])) {
                                        $offer['offer_custom_page'] = trim($offer['offer_custom_page']);
                                        if (is_numeric($offer['offer_custom_page'])) {
                                            $offer['offer_custom_page'] = get_permalink(intval($offer['offer_custom_page']));
                                        } elseif (mb_strpos($offer['offer_custom_page'], '/') === 0) {
                                            $offer['offer_custom_page'] = home_url() . $offer['offer_custom_page'];
                                        }
                                        $result = $this->wcoffers_url_append(
                                                $offer['offer_custom_page'], $url_append
                                        );
                                    } elseif ($offer['offer_method'] == 'default') {
                                        $default_offer_page = $result;

                                        if (!empty($options['default_offer_page'])) {
                                            $default_offer_page = get_permalink(intval($options['default_offer_page']));
                                        }
                                        $hcc_referer = get_post_meta($order->get_id(), '_hcc_referer', TRUE);

                                        if (!empty($hcc_referer)) {
                                            $default_offer_page = home_url($hcc_referer);
                                        }
                                        $result = $this->wcoffers_url_append(
                                                $default_offer_page, $url_append
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $result;
        }

        /**
         * @since 1.0
         * @global array $wpdb
         * @param array $order
         * @return array Get UPSELL value for current order
         */
        public function wcoffers_get_upsell_for_order_items($order) {
            global $wpdb;
            $result = FALSE;
            $table_wc_upsells = $wpdb->prefix . 'wc_upsells';
            if (!empty($order)) {
                $order_items = $order->get_items();
                $order_total_item_qty = 0;

                foreach ($order_items as $item) {
                    if (!empty($item['product_id'])) {
                        $order_total_item_qty += intval($item['qty']);
                    }
                }

                $upsell_target_query = '';
                foreach ($order_items as $item) {
                    if (!empty($item['product_id'])) {
                        $upsell_target_query .= "( upsell_target LIKE '%{$item['product_id']}%' AND upsell_target_qty = {$order_total_item_qty} ) OR ";
                        $upsell_target_query .= "( upsell_target LIKE '%{$item['product_id']}%' AND upsell_target_qty = 0 ) OR ";
                    }
                }
                $upsell_target_query = rtrim($upsell_target_query, 'OR ');
                if (!empty($upsell_target_query)) {
                    $upsell = $wpdb->get_row(
                            "	SELECT
						id,
						upsell_name,
						upsell_display,
						upsell_mode,
						upsell_target,
						upsell_offers_skip,
						upsell_offers,
						upsell_active
					FROM
						$table_wc_upsells
					WHERE
						(
							{$upsell_target_query} OR
							upsell_mode = 'global'
						)
						AND
							upsell_active = 1
					LIMIT 1; ", ARRAY_A
                    );
                    if (!empty($upsell)) {
                        $result = $upsell;
                    }
                }
            }
            return $result;
        }

        /**
         * @since 1.0
         * @param int $parent_order
         * @param array $upsell
         * @param int $current_offer_id
         * @return array Get suitable for offer for current order
         */
        public function wcoffers_get_suitable_offer($parent_order, $upsell, $current_offer_id = 1) {
            $result = FALSE;

            if (!empty($parent_order)) {
                $order_items_ids = array();
                $order_items = $parent_order->get_items();

                foreach ($order_items as $item) {
                    if (!empty($item['product_id'])) {
                        $order_items_ids[] = $item['product_id'];
                    }
                }

                if (!empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                    $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                }
                $current_offer = $upsell['upsell_offers'][$current_offer_id];

                if (!empty($current_offer)) {
                    $current_offer['product_id'] = explode(',', $current_offer['product_id']);
                    foreach ($current_offer['product_id'] as $id) {
                        if (in_array($id, $order_items_ids)) {
                            $result = $current_offer['offer_accepted_action'];

                            break;
                        }
                    }

                    // if none of parent order items matches the offer items, show the current offer
                    if ($result === FALSE) {
                        $current_offer['offer_id'] = $current_offer_id;
                        $result = $current_offer;
                    } else {
                        if (mb_strpos($result, 'show_offer_') !== FALSE) {
                            $next_offer_id = mb_substr(
                                    $result, mb_strrpos($result, '_') + 1
                            );
                            $result = $this->wcoffers_get_suitable_offer($parent_order, $upsell, $next_offer_id);
                        }
                    }
                }
            }

            return $result;
        }

        /**
         * @since 1.0
         * @param type $action
         * @return int Create nonce for url
         */
        public function wcoffers_create_nonce($action = '0') {
            $token = $_SERVER['REMOTE_ADDR'];
            $i = wp_nonce_tick();
            return substr(wp_hash($i . '|' . $action . '|' . $token, 'nonce'), -12, 10);
        }

        /**
         * @since 1.0
         * @param string $url
         * @param string $arg
         * @return string Create new url with specific values
         */
        public function wcoffers_url_append($url = '', $arg = '') {
            if (!empty($url)) {
                if (mb_strpos($url, '?') !== FALSE) {
                    $url .= '&' . $arg;
                } else {
                    $url .= '?' . $arg;
                }
            }

            return $url;
        }

        /**
         * @since 1.0
         * @global array $post
         * @global array $woocommerce
         * @global array $product
         * @param array $atts
         * @param string $content
         * Default Page Shortcode
         */
        public function wcoffers_default_offer_page_shortcode($atts = array(), $content = '') {
            $result = '';
            $offer_product = array();

            //Check query data and prepare product data
            if (!empty($_GET['wcu_s']) && isset($_GET['wcu_co']) && $_GET['wcu_co'] == 'before_checkout') {
                global $woocommerce;
                $url_base = $url_yes = $url_no = $offer_product_original_price = '';
                $upsell = $this->wcoofers_get_product_id_from_upsell();
                if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                    $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                    if ($this->wcoffers_verify_nonce($_GET['wcu_s'], 'wcu_offer_' . $upsell['id'])) {
                        $offer_id = 1;
                        if (!empty($_GET['wcu_n'])) {
                            $offer_id = intval($_GET['wcu_n']);
                        }
                        // if offer exists
                        if (!empty($upsell['upsell_offers'][$offer_id])) {
                            $offer = $upsell['upsell_offers'][$offer_id];
                            $offer['product_id'] = explode(',', $offer['product_id']);
                            $offer['product_id'] = $offer['product_id'][0];
                            $offer_product = wc_get_product($offer['product_id']);
                            $offer_product_original_price = $offer_product->get_price();
                            if (method_exists($offer_product, 'get_sign_up_fee_including_tax') && $offer_product->get_sign_up_fee_including_tax()) {

                            } else {
                                $offer_product = $this->wcoffers_product_set_custom_price($offer_product, $offer['product_price'], array(), 'display');
                            }
                            $added_p = '';
                            if (isset($_GET['wcu_added'])) {
                                $added_p = '&wcu_added=' . $_GET['wcu_added'];
                            }
                            $url_append = '&wcu_n=' . $offer_id .
                                    '&wcu_s=' . $_GET['wcu_s'] .
                                    '&wcu_rd=before_checkout' . $added_p;
                            $url_yes = $url_append . '&wcu_yes=1';
                            $url_no = $url_append . '&wcu_no=1';
                            $url_base = mb_substr($_SERVER['REQUEST_URI'], 0, mb_strpos($_SERVER['REQUEST_URI'], 'wcu'));
                            $url_base = trim($url_base, '?');
                            $url_base = trim($url_base, '&');
                            $url_yes = $this->wcoffers_url_append($url_base, $url_yes);
                            $url_no = $this->wcoffers_url_append($url_base, $url_no);
                        } else {
                            $url_yes = $url_no = $woocommerce->cart->get_checkout_url();
                        }
                    } else {
                        $result .= 'Your special offers session timed out. <a href="' . $this->wcoffers_get_thank_you_page_url($parent_order) . '" class="button">Go to the "Order details" page.</a>';
                    }
                }
            }


            if (!empty($_GET['wcu']) && !empty($_GET['wcu_s'])) {
                $order_key = $_GET['wcu'];
                $parent_order = wc_get_order_id_by_order_key($order_key);
                $url_base = $url_yes = $url_no = '';
                if (!empty($parent_order)) {
                    $parent_order = $this->wcoffers_get_order_id($parent_order);
                    $parent_order = wc_get_order($parent_order);
                    if (!empty($parent_order)) {
                        $upsell = $this->wcoffers_get_upsell_for_order_items($parent_order);
                        if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                            $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                            if ($this->wcoffers_verify_nonce($_GET['wcu_s'], 'wcu_offer_' . $parent_order->get_id() . $upsell['id'])) {
                                $offer_id = 1;
                                if (!empty($_GET['wcu_n'])) {
                                    $offer_id = intval($_GET['wcu_n']);
                                }
                                // if offer exists
                                if (!empty($upsell['upsell_offers'][$offer_id])) {
                                    $offer = $upsell['upsell_offers'][$offer_id];
                                    $offer['product_id'] = explode(',', $offer['product_id']);
                                    $offer['product_id'] = $offer['product_id'][0];
                                    $offer_product = wc_get_product($offer['product_id']);
                                    $offer_product_original_price = $offer_product->get_price();
                                    if (method_exists($offer_product, 'get_sign_up_fee_including_tax') && $offer_product->get_sign_up_fee_including_tax()) {

                                    } else {
                                        $offer_product = $this->wcoffers_product_set_custom_price($offer_product, $offer['product_price'], array(), 'display');
                                    }
                                    $url_append = 'wcu=' . $parent_order->get_order_key() .
                                            '&wcu_n=' . $offer_id .
                                            '&wcu_s=' . $_GET['wcu_s'];

                                    $url_yes = $url_append . '&wcu_yes=1';
                                    $url_no = $url_append . '&wcu_no=1';
                                    $url_base = mb_substr($_SERVER['REQUEST_URI'], 0, mb_strpos($_SERVER['REQUEST_URI'], 'wcu'));
                                    $url_base = trim($url_base, '?');
                                    $url_base = trim($url_base, '&');
                                    $url_yes = $this->wcoffers_url_append($url_base, $url_yes);
                                    $url_no = $this->wcoffers_url_append($url_base, $url_no);
                                } else {
                                    $url_yes = $url_no = $this->wcoffers_get_thank_you_page_url($parent_order);
                                }
                            } else {
                                $result .= 'Your special offers session timed out. <a href="' . $this->wcoffers_get_thank_you_page_url($parent_order) . '" class="button">Go to the "Order details" page.</a>';
                            }
                        }
                    }
                }
            }

//Display default offer page
            if (!empty($offer_product)) {
                global $post, $woocommerce, $product;

// save original global post, product
                $saved_post = $post;
                $saved_product = $product;

// replace them to use in function below
                $post = get_post($offer_product->get_id());
                $product = $offer_product;

                $options = get_option('wco_upsell_options');
                if (empty($options['default_offer_page_color'])) {
                    $options['default_offer_page_color'] = '#3BB13B';
                }
                if (empty($options['default_offer_page_button_yes_text'])) {
                    $options['default_offer_page_button_yes_text'] = 'Buy Now';
                }
                if (empty($options['default_offer_page_button_no_text'])) {
                    $options['default_offer_page_button_no_text'] = 'No, Thanks';
                }
                $product_description = $product->get_description();

// optionally strip tags from product description
                if (empty($options['disable_strip_html'])) {
                    $product_description = strip_tags($product_description);
                }

// get custom product upsell description
                $product_description_meta = get_post_meta($product->get_id(), '_wcu_offer_description', TRUE);
                if (!empty($product_description_meta)) {
                    $product_description = $product_description_meta;
                }

                $result .= $this->wcoffers_custom_page_upsell_accepted_msg();
                ob_start();
                ?>
                <style type="text/css">

                    .page .wco_default_offer_page .wco_default_offer_price,
                    .page .wco_default_offer_page .wco_default_offer_variation_price {
                        font-size: 1.4em;
                        color: <?php echo $options['default_offer_page_color']; ?>;
                    }
                    .page:not(.page-gb-wc-hcc-upsell) .wco_default_offer_page .wco_default_offer_btns .wco_default_offer_btn.wco_default_offer_accept {
                        background: <?php echo $options['default_offer_page_color']; ?>;
                        margin-right: 5px;
                    }
                </style>
                <div class="woocommerce">
                    <div class="wco_default_offer_page product">
                        <div class="wco_default_offer_images_wrap images">
                <?php woocommerce_show_product_images(); ?>
                        </div>
                        <div class="wco_default_offer_summary">
                            <div class="wco_default_offer_title">
                                <h2><?php echo $product->get_title(); ?></h2>
                            </div>
                            <div class="wco_default_offer_desc">
                            <?php echo $product_description; ?>
                            </div>
                            <?php
                            // product type: variable
                            if ($product->is_type('variable')) {
                                ?>
                                <div class="wco_default_offer_variations">
                                    <table>
                                        <?php
                                        $variations = $product->get_available_variations();
                                        // set custom variation prices (display only)
                                        if (!empty($variations)) {
                                            foreach ($variations as &$v) {
                                                if (!empty($v['variation_id'])) {
                                                    $v_product = wc_get_product($v['variation_id']);
                                                    $v_product = $this->wcoffers_product_set_custom_price($v_product, $offer['product_price'], $v_product, 'display');
                                                    $v['display_price'] = wc_get_price_to_display($v_product); // ->get_display_price();
                                                    $v['display_regular_price'] = wc_get_price_to_display($v_product, array('price' => $v_product->get_regular_price())); // ->get_display_price( $v_product->get_regular_price() );
                                                    $v['price_html'] = $v_product->get_price_html();
                                                }
                                            }
                                        }

                                        $attributes = $product->get_variation_attributes();
                                        foreach ($attributes as $attribute_name => $attribute_options) :
                                            ?>
                                            <tr>
                                                <td class="label">
                                                    <label for="<?php echo sanitize_title($attribute_name); ?>">
                        <?php echo wc_attribute_label($attribute_name); ?>
                                                    </label>
                                                </td>
                                                <td class="value">
                                                    <?php
                                                    $selected = $product->get_variation_default_attribute($attribute_name);
                                                    wc_dropdown_variation_attribute_options(
                                                            array(
                                                                'options' => $attribute_options,
                                                                'attribute' => $attribute_name,
                                                                'product' => $product,
                                                                'selected' => $selected,
                                                                'class' => 'wco_default_offer_variation_select',
                                                            )
                                                    );
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endforeach;
                                        ?>
                                    </table>
                                    <div class="wco_default_offer_variation_price" style="display: none;"></div>
                                </div>
                                <script type="text/javascript">
                                    jQuery(document).ready(function () {

                                        var wco_dp_accept_btn = jQuery('.wco_default_offer_accept');

                                        var wco_dp_accept_href = wco_dp_accept_btn.attr('href');

                                        wco_dp_accept_btn
                                                .attr('href', '#')
                                                .prop('disabled', true);

                                        var wco_dp_variations = <?php echo json_encode($variations); ?>;

                                        var wco_dp_selectors = {};

                                        jQuery('.wco_default_offer_variation_select').change(function () {

                                            var wco_dp_options_selected = false;

                                            // collect selected attributes

                                            wco_dp_selectors = {};
                                            wco_dp_selectors_count = 0;

                                            jQuery('.wco_default_offer_variation_select').each(function () {

                                                var $this = jQuery(this);

                                                // check if all options are selected

                                                if ($this.val().length == 0)
                                                {
                                                    wco_dp_options_selected = false;

                                                    return false;
                                                } else
                                                {
                                                    wco_dp_options_selected = true;

                                                    wco_dp_selectors[ $this.attr('name') ] = $this.val();

                                                    wco_dp_selectors_count++;
                                                }
                                            });

                                            var matching_variations = wc_variation_form_matcher.find_matching_variations(wco_dp_variations, wco_dp_selectors);

                                            if (wco_dp_options_selected == false) {
                                                return false;
                                            }

                                            var $variation_price = jQuery('.wco_default_offer_variation_price');

                                            var $main_image = jQuery('.wco_default_offer_page').find('a.zoom');

                                            var wco_dp_compare_count = 0;
                                            var wco_dp_selected_variation = {};

                                            wco_dp_selected_variation = matching_variations.shift();

                                            // display the price

                                            if (typeof wco_dp_selected_variation != 'undefined') {
                                                // console.log( wco_dp_selected_variation );

                                                $variation_price.html(wco_dp_selected_variation.price_html);

                                                if ($variation_price.is(':hidden')) {
                                                    $variation_price.slideDown(300);
                                                }

                                                // switch variation image if it exists

                                                if (
                                                        typeof wco_dp_selected_variation.image_link != 'undefined' &&
                                                        wco_dp_selected_variation.image_link.length > 0
                                                        ) {
                                                    $main_image.attr('href', wco_dp_selected_variation.image_link);

                                                    $main_image.find('img')
                                                            .attr('src', wco_dp_selected_variation.image_src)
                                                            .attr('srcset', wco_dp_selected_variation.image_srcset)
                                                            .attr('title', wco_dp_selected_variation.image_title)
                                                            .attr('sizes', wco_dp_selected_variation.image_sizes)
                                                            .attr('alt', wco_dp_selected_variation.image_alt);
                                                }

                                                var wco_dp_accept_href_append = '&1cu_attr=';

                                                wco_dp_accept_btn.attr('href', '');

                                                jQuery.each(wco_dp_selectors, function (attribute, value) {

                                                    wco_dp_accept_href_append = wco_dp_accept_href_append + attribute + ':' + value + ';';
                                                });

                                                wco_dp_accept_href_append = wco_dp_accept_href_append.substring(0, wco_dp_accept_href_append.length - 1);

                                                // wco_dp_accept_href = wco_dp_accept_href + wco_dp_accept_href_append;

                                                wco_dp_accept_btn
                                                        .attr('href', wco_dp_accept_href + wco_dp_accept_href_append)
                                                        .prop('disabled', false);

                                                // return false;
                                            }
                                            // });

                                            // if variation not found

                                            if (typeof wco_dp_selected_variation == 'undefined')
                                            {
                                                $variation_price.html('Not available. Please, select another.');

                                                wco_dp_accept_btn
                                                        .attr('href', '#')
                                                        .prop('disabled', true);
                                            }
                                        });

                                        jQuery('.wco_default_offer_variation_select').trigger('change');
                                    });

                                    /**
                                     * Matches inline variation objects to chosen attributes
                                     * @type {Object}
                                     */
                                    var wc_variation_form_matcher = {
                                        find_matching_variations: function (product_variations, settings) {
                                            var matching = [];
                                            for (var i = 0; i < product_variations.length; i++) {
                                                var variation = product_variations[i];

                                                if (wc_variation_form_matcher.variations_match(variation.attributes, settings)) {
                                                    matching.push(variation);
                                                }
                                            }
                                            return matching;
                                        },
                                        variations_match: function (attrs1, attrs2) {
                                            var match = true;
                                            for (var attr_name in attrs1) {
                                                if (attrs1.hasOwnProperty(attr_name)) {
                                                    var val1 = attrs1[ attr_name ];
                                                    var val2 = attrs2[ attr_name ];
                                                    if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2) {
                                                        match = false;
                                                    }
                                                }
                                            }
                                            return match;
                                        }
                                    };
                                </script>
                                <?php
                            } else {
                                ?>
                                <div class="wco_default_offer_price">
                                    <?php
                                    echo '<del>' . wc_price($offer_product_original_price) . '</del>&nbsp&nbsp&nbsp';
                                    echo $product->get_price_html();
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="wco_default_offer_btns">
                                <a href="<?php echo $url_yes; ?>" class="wco_default_offer_btn wco_default_offer_accept"><?php echo $options['default_offer_page_button_yes_text']; ?></a>
                                <a href="<?php echo $url_no; ?>" class="wco_default_offer_btn wco_default_offer_reject"><?php echo $options['default_offer_page_button_no_text']; ?></a>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <?php
                $result .= ob_get_clean();
                $post = $saved_post;
                $product = $saved_product;
            }
            return $result;
        }

        /**
         * @since 1.0
         * @param int $order_number
         * Return order id
         */
        public function wcoffers_get_order_id($order_number) {
            $order_id = $order_number;
            if (function_exists('wc_seq_order_number_pro')) {
                $order_number = wc_seq_order_number_pro()->find_order_by_order_number($order_number);
                if (!empty($order_number)) {
                    $order_id = $order_number;
                }
            }

            return $order_id;
        }

        /**
         * @since 1.0
         * @param string $nonce
         * @param string $action
         * @return boolean|int Verify nonce
         */
        public function wcoffers_verify_nonce($nonce = '', $action = '0') {
            $nonce = (string) $nonce;
            if (empty($nonce)) {
                return FALSE;
            }
            $token = $_SERVER['REMOTE_ADDR'];
            $i = wp_nonce_tick();
            $expected = substr(wp_hash($i . '|' . $action . '|' . $token, 'nonce'), -12, 10);
            if (hash_equals($expected, $nonce)) {
                return 1;
            }
            $expected = substr(wp_hash(( $i - 1 ) . '|' . $action . '|' . $token, 'nonce'), -12, 10);
            if (hash_equals($expected, $nonce)) {
                return 2;
            }
            return FALSE;
        }

        /**
         * @since 1.0
         * @param array $product
         * @param int $custom_price
         * @param array $variation
         * @param string $mode
         * Set new price for offer product
         */
        public function wcoffers_product_set_custom_price($product, $custom_price, $variation = array(), $mode = 'display') {
            $result = FALSE;

            if (empty($custom_price)) {
                $custom_price = '100%';
            }
            if (!empty($product)) {
                $product_price = $product->get_price();
                if ($product->is_type('variable') && !empty($variation)) {
                    $variation_id = $product->get_matching_variation($variation);
                    $variation = wc_get_product($variation_id);
                    $product_price = $variation->get_price();
                }
                if ($product->is_type('subscription')) {
                    $sign_up_fee = wcs_get_price_including_tax($product, array('qty' => 1, 'price' => WC_Subscriptions_Product::get_sign_up_fee($product)));
                    $has_trial = ( WC_Subscriptions_Product::get_trial_length($product) > 0 ) ? TRUE : FALSE;

                    if ($mode == 'charge') {
                        if ($has_trial && $sign_up_fee >= 0) {
                            $product_price = $sign_up_fee;
                        } else {
                            $product_price += $sign_up_fee;
                        }
                    } else {

                    }
                }
                if (mb_strpos($custom_price, '%') !== FALSE) {
                    $custom_price = trim($custom_price, '%');
                    $custom_price = $product_price - (floatval($product_price) * ( floatval($custom_price) / 100 ));
                    $product->set_price($custom_price);
                } else {
                    $custom_price = floatval($custom_price);
                    $product->set_price($custom_price);
                }
                $result = $product;
            }
            return $result;
        }

        /**
         * @since 1.0
         * @param array $order
         * Return thank you url
         */
        public function wcoffers_get_thank_you_page_url($order) {
            $url_append = 'wcu_n=ty';
            $_GET['wcu_n'] = 'ty';
            $result = $this->wcoffers_url_append(
                    $order->get_checkout_order_received_url(), $url_append
            );
            return $result;
        }

        /**
         * @since 1.0
         * @param array $atts
         * @param string $content
         * Return message of accepted offer
         */
        public function wcoffers_custom_page_upsell_accepted_msg($atts = array(), $content = '') {
            $result = '';
            $options = get_option('wco_upsell_options');

            if (empty($options['hide_added_msg'])) {
                if (
                        (
                        (!empty($_GET['wcu']) && !empty($_GET['wcu_n']) && !empty($_GET['wcu_s']) ) ||
                        (!empty($_GET['key']) && !empty($_GET['wcu_n']) )
                        ) &&
                        !empty($_GET['wcu_added'])
                ) {
                    $added_product = wc_get_product(intval($_GET['wcu_added']));

                    $result .= '<style type="text/css">
			.wc_1cu_offer_accepted_msg {
				background: #CCFFD0;
				padding: 10px 15px;
				margin: 0 0 24px;
			}
			</style>';

                    if (!empty($added_product)) {
                        $result .= '&quot;' . htmlspecialchars($added_product->get_title()) . '&quot; has been added to your order.';
                    } else {
                        $result .= 'Item has been added to your order.';
                    }

                    if ($_GET['wcu_n'] == 'ty') {
                        $result .= ' You can find your order details below this message.';
                    }
                    $result = '<div class="wc_1cu_offer_accepted_msg">' . $result . '</div>';
                }
            }

            return $result;
        }

        /**
         * @since 1.0
         * @param string $url
         * @param int $timeout
         * Redirect to given location
         */
        function wcoffers_force_redirect($url = '', $timeout = 1000) {
            if (!empty($url) && !empty($timeout)) {
                return '<script type="text/javascript">var a = setTimeout( function(){ window.location.replace(\'' . $url . '\'); }, ' . intval($timeout) . ' );</script>';
            }
        }

        /**
         * @since 1.0
         * Redirect to offer page or thank you page
         */
        function wcoffers_process_offer() {
            $result = FALSE;
            if (is_admin()) {
                return;
            }

            if (!empty($_GET['wc-ajax']) && $_GET['wc-ajax'] == 'checkout') {
                $fields = array();
                /*
                  WooCommerce Authorize.Net CIM (Cards)
                 */
                if (!empty($_POST['payment_method']) && $_POST['payment_method'] == 'authorize_net_cim_credit_card') {
                    $fields = array(
                        'wc-authorize-net-cim-credit-card-account-number',
                        'wc-authorize-net-cim-credit-card-expiry',
                        'wc-authorize-net-cim-credit-card-csc',
                        'wc-authorize-net-cim-credit-card-payment-token',
                    );
                }

                /*
                  WooCommerce Authorize.Net CIM (eCheck)
                 */ elseif (!empty($_POST['payment_method']) && $_POST['payment_method'] == 'authorize_net_cim_echeck') {
                    $fields = array(
                        'wc-authorize-net-cim-echeck-routing-number',
                        'wc-authorize-net-cim-echeck-account-number',
                        'wc-authorize-net-cim-echeck-account-type',
                        'wc-authorize-net-cim-echeck-payment-token',
                    );
                }

                if (!empty($fields)) {
                    foreach ($fields as $f) {
                        $fields_data[$f] = isset($_POST[$f]) ? $_POST[$f] : '';
                    }
                    $fields_data = base64_encode(json_encode($fields_data));
                    $cookie = setcookie('wc_wcu', $fields_data, time() + 1800, COOKIEPATH, COOKIE_DOMAIN);
                }
            }

            if (!empty($_GET['wcu']) && !empty($_GET['wcu_n']) && !empty($_GET['wcu_s']) && (!empty($_GET['wcu_yes']) || !empty($_GET['wcu_no']) )) {
                $options = get_option('wco_upsell_options');
                if (!empty($_GET['wcu'])) {
                    $order_key = $_GET['wcu'];
                    $parent_order = wc_get_order_id_by_order_key($order_key);
                    if (!empty($parent_order)) {
                        $parent_order = $this->wcoffers_get_order_id($parent_order);
                        $parent_order = wc_get_order($parent_order);
                        if (!empty($parent_order)) {
                            $upsell = $this->wcoffers_get_upsell_for_order_items($parent_order);
                            if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                                $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                                $offer_id = intval($_GET['wcu_n']);
                                if (!empty($upsell['upsell_offers'][$offer_id])) {
                                    $offer = $upsell['upsell_offers'][$offer_id];
                                    if (!empty($offer['product_id'])) {
                                        if ($this->wcoffers_verify_nonce($_GET['wcu_s'], 'wcu_offer_' . $parent_order->get_id() . $upsell['id'])) {
                                            $offer_action = '';
                                            $url_append = '';
                                            $url_append_yes = '';

                                            if (!empty($_GET['wcu_yes'])) {
                                                $offer['product_id'] = explode(',', $offer['product_id']);
                                                if (!empty($_GET['wcu_pid'])) {
                                                    $_GET['wcu_pid'] = intval($_GET['wcu_pid']);
                                                    if (in_array($_GET['wcu_pid'], $offer['product_id'])) {
                                                        $offer['product_id'] = $_GET['wcu_pid'];
                                                    } else {
                                                        $offer['product_id'] = $offer['product_id'][0];
                                                    }
                                                } else {
                                                    $offer['product_id'] = $offer['product_id'][0];
                                                }
                                                $offer_product = wc_get_product($offer['product_id']);
                                                if (!empty($offer_product)) {
                                                    if (empty($offer['product_price'])) {
                                                        $offer['product_price'] = '100%';
                                                    }
                                                    $offer_meta = get_post_meta($parent_order->get_id(), '_wcu_security_' . $offer['product_id'], TRUE);
                                                    if (empty($offer_meta)) {
                                                        if ($this->wcoffers_one_click_payment($offer, $order_key, $parent_order->get_payment_method())) {
                                                            $url_append_yes = 'wcu_added=' . $offer_product->get_id();
                                                        } else {
                                                            $url_append_yes = 'wcu_nadded=' . $offer_product->get_id();
                                                        }
                                                    } else {
                                                        $url_append_yes = 'wcu_nadded_meta=' . $offer_product->get_id();
                                                    }
                                                }
                                                $offer_action = $offer['offer_accepted_action'];
                                                $offer_action_custom_page = $offer['offer_accepted_custom_page'];
                                            } else {
                                                $offer_action = $offer['offer_rejected_action'];
                                                $offer_action_custom_page = $offer['offer_rejected_custom_page'];
                                            }

                                            if (!empty($offer_action)) {
                                                if (mb_strpos($offer_action, 'show_offer_') !== FALSE) {
                                                    $next_offer_id = mb_substr(
                                                            $offer_action, mb_strrpos($offer_action, '_') + 1
                                                    );
                                                    if (!empty($upsell['upsell_offers_skip'])) {
                                                        $next_offer = $this->wcoffers_get_suitable_offer($parent_order, $upsell, $next_offer_id);
                                                    } else {
                                                        $next_offer = $upsell['upsell_offers'][$next_offer_id];
                                                        $next_offer['offer_id'] = $next_offer_id;
                                                    }
                                                    if (!empty($next_offer) && is_array($next_offer)) {
                                                        $url_append = 'wcu=' . $parent_order->get_order_key() .
                                                                '&wcu_s=' . $_GET['wcu_s'] .
                                                                '&wcu_n=' . $next_offer['offer_id'] .
                                                                (!empty($url_append_yes) ? '&' . $url_append_yes : '' );
                                                        if ($next_offer['offer_method'] == 'custom_page' && !empty($next_offer['offer_custom_page'])) {
                                                            $next_offer['offer_custom_page'] = trim($next_offer['offer_custom_page']);
                                                            if (is_numeric($next_offer['offer_custom_page'])) {
                                                                $next_offer['offer_custom_page'] = get_permalink(intval($next_offer['offer_custom_page']));
                                                            }
                                                            $result = $this->wcoffers_url_append(
                                                                    $next_offer['offer_custom_page'], $url_append
                                                            );
                                                        } elseif ($next_offer['offer_method'] == 'default') {
                                                            $default_offer_page = $result;
                                                            if (!empty($options['default_offer_page'])) {
                                                                $default_offer_page = get_permalink(intval($options['default_offer_page']));
                                                            }
                                                            $hcc_referer = get_post_meta($parent_order->get_id(), '_hcc_referer', TRUE);
                                                            if (!empty($hcc_referer)) {
                                                                $default_offer_page = home_url($hcc_referer);
                                                            }
                                                            $result = $this->wcoffers_url_append(
                                                                    $default_offer_page, $url_append
                                                            );
                                                        }
                                                    } else {
                                                        $result = $this->wcoffers_get_thank_you_page_url($parent_order);
                                                    }
                                                } elseif ($offer_action == 'show_custom' && !empty($offer_action_custom_page)) {
                                                    $url_append = 'wcu=' . $parent_order->get_order_key() .
                                                            (!empty($url_append_yes) ? '&' . $url_append_yes : '' );
                                                    if (is_numeric($offer_action_custom_page)) {
                                                        $offer_action_custom_page = get_permalink(intval($offer_action_custom_page));
                                                    }
                                                    $result = $this->wcoffers_url_append(
                                                            $offer_action_custom_page, $url_append
                                                    );
                                                } else { // if( $offer_action == 'show_thank_you' )
                                                    $url_append = $url_append_yes;
                                                    $result = $this->wcoffers_get_thank_you_page_url($parent_order);
                                                    $result = $this->wcoffers_url_append(
                                                            $result, $url_append
                                                    );
                                                }
                                            }
                                        } else {
                                            $result = $this->wcoffers_get_thank_you_page_url($parent_order);
                                        }
                                    } else {
                                        $result = $this->wcoffers_get_thank_you_page_url($parent_order);
                                    }
                                } else {
                                    $result = $this->wcoffers_get_thank_you_page_url($parent_order);
                                }
                            } else {
                                $result = $this->wcoffers_get_thank_you_page_url($parent_order);
                            }
                        }
                    }
                }
            }
            if (!empty($result)) {
                wp_redirect($result);
                die();
            }
        }

        /**
         * @since 1.0
         * @global array $woocommerce
         * @param array $offer
         * @param int $order_key
         * @param array $payment_method
         * Add order of offered product and redirect to thank you page or second order if exists
         */
        function wcoffers_one_click_payment($offer = array(), $order_key, $payment_method = '') {
            global $woocommerce;
            $result = FALSE;
            $product_id = 0;

            if (!empty($offer['product_id'])) {
                $product_id = $offer['product_id'];
            }
            $custom_price = '100%';

            if (!empty($offer['product_price'])) {
                $custom_price = $offer['product_price'];
            }
            if (
                    !empty($product_id) &&
                    !empty($order_key) &&
                    !empty($payment_method)
            ) {
                $product_id = intval($product_id);

                $gateways = $woocommerce->payment_gateways->get_available_payment_gateways();

                if (
                        !empty($gateways[$payment_method]) && (
                        method_exists($gateways[$payment_method], 'process_payment_short') ||
                        $payment_method == 'cod' ||
                        $payment_method == 'bacs' ||
                        $payment_method == 'cheque' ||
                        $payment_method == 'paypal'
                        )
                ) {
                    $product = wc_get_product($product_id);

                    $is_subscription = FALSE;

                    if (
                            class_exists('WC_Product_Subscription') &&
                            $product instanceof WC_Product_Subscription
                    ) {
                        $is_subscription = TRUE;
                    }
                    if (class_exists('WC_Subscriptions_Order')) {
                        $is_purchasable = true;
                    } else {
                        $is_purchasable = $product->is_purchasable();
                    }
                    // create order
                    if ($is_purchasable) {
                        $options = get_option('wco_upsell_options');
                        $parent_order = wc_get_order_id_by_order_key($order_key);
                        if (!empty($parent_order)) {
                            $parent_order = $this->wcoffers_get_order_id($parent_order);
                            $parent_order = wc_get_order($parent_order);

                            if (!empty($parent_order)) {
                                $parent_order_billing = $parent_order->get_address('billing');
                                if (!empty($parent_order_billing['email'])) {
                                    $current_user_id = get_current_user_id();
                                    if ($is_subscription) {
                                    // if upsell is subcription & user is guest, create an account and authorize user
                                        if (!is_user_logged_in()) {
                                            // create user account / or get the id or existing one
                                            $email_exists = email_exists($parent_order_billing['email']);
                                            if ($email_exists) {
                                                $current_user_id = $email_exists;
                                            } else {
                                                $current_user_id = $this->wcoffers_create_new_customer($parent_order_billing['email']);
                                                // authorize user
                                                if (!empty($current_user_id)) {
                                                    update_user_meta($current_user_id, 'first_name', $parent_order_billing['first_name']);
                                                    update_user_meta($current_user_id, 'last_name', $parent_order_billing['last_name']);

                                                    update_user_meta($current_user_id, 'billing_first_name', $parent_order_billing['first_name']);
                                                    update_user_meta($current_user_id, 'billing_last_name', $parent_order_billing['last_name']);
                                                }
                                            }
                                        }
                                    }

                                    $order = wc_create_order(array(
                                        'customer_id' => $current_user_id,
                                    ));

                                    // if variation attributes defined
                                    $variation = array();
                                    $variation_id = 0;

                                    if (!empty($_GET['wcu_attr'])) {
                                        $_GET['wcu_attr'] = explode(';', urldecode($_GET['wcu_attr']));

                                        foreach ($_GET['wcu_attr'] as $v) {
                                            $v = explode(':', $v);

                                            if (!empty($v[0])) {
                                                $variation[$v[0]] = $v[1];
                                            }
                                        }

                                        // get matching variation
                                        $data_store = WC_Data_Store::load('product');
                                        $variation_id = $data_store->find_matching_product_variation($product, $variation);

                                        if (!empty($variation_id)) {
                                            $product = wc_get_product($variation_id);
                                        }
                                    }
                                    $product_price_initial = $product->get_price();
                                    $product = $this->wcoffers_product_set_custom_price($product, $custom_price, array(), 'charge');
                                    @$order->add_product($product, 1);
                                    $order->set_address($parent_order->get_address('billing'), 'billing');
                                    $order->set_address($parent_order->get_address('shipping'), 'shipping');
                                    $order->set_payment_method($gateways[$payment_method]);
                                    if (!wc_tax_enabled()) {
                                        $order->set_total(0, 'shipping_tax');
                                        $order->set_total(0, 'tax');
                                    }
                                    $order->calculate_totals();
                                    if (function_exists('wc_seq_order_number_pro')) {
                                        $wc_seq_order_number_pro = wc_seq_order_number_pro();
                                        $wc_seq_order_number_pro->set_sequential_order_number($order->get_id());
                                    }
                                    update_post_meta($order->get_id(), 'wcoffers_parent_order_id', $parent_order->get_id());
                                    update_post_meta($parent_order->get_id(), '_wcu_security_' . $product->get_id(), '0');
                                    if (!empty($options['transfer_meta'])) {
                                        $transfer_meta = explode('|', $options['transfer_meta']);
                                        $transfer_meta_val = '';
                                        foreach ($transfer_meta as $v) {
                                            $transfer_meta_val = get_post_meta($parent_order->get_id(), $v, TRUE);
                                            if (!empty($transfer_meta_val)) {
                                                update_post_meta($order->get_id(), $v, $transfer_meta_val);
                                            }
                                        }
                                    }
                                    if ($is_subscription) {
                                        $start_date = date('Y-m-d H:i:s');
                                        $period = WC_Subscriptions_Product::get_period($product);
                                        $interval = WC_Subscriptions_Product::get_interval($product);
                                        $trial_period = WC_Subscriptions_Product::get_trial_period($product);
                                        $subscription = wcs_create_subscription(array(
                                            'start_date' => $start_date,
                                            'order_id' => $order->get_id(),
                                            'billing_period' => $period,
                                            'billing_interval' => $interval,
                                            'customer_note' => $order->get_customer_note(),
                                            'customer_id' => $current_user_id,
                                        ));
                                        if (!empty($current_user_id) && !empty($subscription)) {
                                            $product->set_price($product_price_initial);
                                            @$subscription_item_id = $subscription->add_product($product, 1); // $args
                                            $subscription = wcs_copy_order_address($parent_order, $subscription);
                                            $trial_end_date = WC_Subscriptions_Product::get_trial_expiration_date($product->get_id(), $start_date);
                                            $next_payment_date = WC_Subscriptions_Product::get_first_renewal_payment_date($product->get_id(), $start_date);
                                            $end_date = WC_Subscriptions_Product::get_expiration_date($product->get_id(), $start_date);
                                            $subscription->update_dates(array(
                                                'trial_end' => $trial_end_date,
                                                'next_payment' => $next_payment_date,
                                                'end' => $end_date,
                                            ));

                                            if (WC_Subscriptions_Product::get_trial_length($product->get_id()) > 0) {
                                                wc_add_order_item_meta($subscription_item_id, '_has_trial', 'true');
                                            }
                                            if (!empty($trial_period)) {
                                                update_post_meta($subscription->get_id(), '_trial_period', $trial_period);
                                            }
                                            $subscription->set_payment_method($gateways[$payment_method]);
                                            wcs_copy_order_meta($parent_order, $subscription, 'subscription');
                                            if (!empty($current_user_id)) {
                                                update_post_meta($subscription->get_id(), '_customer_user', $current_user_id);
                                            }
                                            $subscription->calculate_totals();
                                            $subscription->save();
                                        }
                                    }
                                    do_action('woocommerce_checkout_order_processed', $order->get_id(), array()); // $this->posted

                                    if (method_exists($gateways[$payment_method], 'process_payment_short')) {
                                        $result = $gateways[$payment_method]->process_payment_short($order->get_id(), $parent_order_billing['email']);
                                    }elseif (
                                            $payment_method == 'authorize_net_cim_credit_card' ||
                                            $payment_method == 'authorize_net_cim_echeck'
                                    ) {

                                        if ($payment_method == 'authorize_net_cim_credit_card') {
                                            $_POST['payment_method'] = $payment_method;

                                            $fields = array(
                                                'wc-authorize-net-cim-credit-card-account-number',
                                                'wc-authorize-net-cim-credit-card-expiry',
                                                'wc-authorize-net-cim-credit-card-csc',
                                                'wc-authorize-net-cim-credit-card-payment-token',
                                            );

                                            if ($is_subscription) {
                                                if (empty($_POST['wc-authorize-net-cim-credit-card-payment-token'])) {
                                                    $_POST['wc-authorize-net-cim-credit-card-tokenize-payment-method'] = 'true';
                                                }
                                            }
                                        }elseif ($payment_method == 'authorize_net_cim_echeck') {
                                            $_POST['payment_method'] = $payment_method;

                                            $fields = array(
                                                'wc-authorize-net-cim-echeck-routing-number',
                                                'wc-authorize-net-cim-echeck-account-number',
                                                'wc-authorize-net-cim-echeck-account-type',
                                                'wc-authorize-net-cim-echeck-payment-token',
                                            );
                                            if ($is_subscription) {
                                                if (empty($_POST['wc-authorize-net-cim-echeck-payment-token'])) {
                                                    $_POST['wc-authorize-net-cim-echeck-tokenize-payment-method'] = 'true';
                                                }
                                            }
                                        }

                                        if (!empty($_COOKIE['wc_wcu']) && !empty($fields)) {
                                            $fields_data = json_decode(base64_decode($_COOKIE['wc_wcu']), TRUE);
                                            foreach ($fields as $f) {
                                                $_POST[$f] = isset($fields_data[$f]) ? $fields_data[$f] : '';
                                            }
                                            $result = $gateways[$payment_method]->process_payment($order->get_id());
                                            if (!empty($result['result']) && $result['result'] == 'success') {
                                                $result = TRUE;
                                            }
                                        }
                                    } elseif (
                                            $payment_method == 'cod' ||
                                            $payment_method == 'bacs' ||
                                            $payment_method == 'cheque' ||
                                            $payment_method == 'paypal'
                                    ) {
                                        if ($payment_method == 'paypal') {
                                            $paypal_payment = new WCO_PayPal_URL();
                                            $result = $paypal_payment->wcoffers_process_payment_short($order->get_id(), $parent_order_billing['email']);
                                        } else {
                                            $result = $gateways[$payment_method]->process_payment($order->get_id());
                                        }
                                    }
                                    if ($result === TRUE) {
                                        $offer_id = 1;
                                        if (!empty($_GET['wcu_n'])) {
                                            $offer_id = intval($_GET['wcu_n']);
                                        }
                                        $offer = $this->wcoffers_get_offer_for_order_items($parent_order, $offer_id);

                                        if (!empty($offer) && !empty($offer['cancel_target'])) {
                                            if (method_exists($gateways[$payment_method], 'process_refund')) {
                                                $result = $gateways[$payment_method]->process_refund($parent_order->get_id(), $parent_order->get_total(), $reason = 'Refund / Void called by upsell.');
                                            }

                                            if (empty($options['cancel_order_status'])) {
                                                $options['cancel_order_status'] = 'wc-cancelled';
                                            }

                                            if ($options['cancel_order_status'] == 'wc-cancelled') {
                                                $parent_order->cancel_order();
                                            } else {
                                                WC()->session->set('order_awaiting_payment', false);

                                                $parent_order->update_status($options['cancel_order_status']);
                                            }

                                            $parent_order->add_order_note(__('Order was replaced by upsell (1CU)', 'gb_ocu'));

                                            /*
                                              WooCommerce Subscriptions & Memberships: expire subscription
                                             */

                                            if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order, array('parent'))) {
                                                $parent_subscription = wcs_get_subscriptions_for_order($parent_order->get_id());
                                                if (!empty($parent_subscription)) {
                                                    $parent_subscription = array_pop($parent_subscription);
                                                    if (!empty($parent_subscription)) {
                                                        if (class_exists('WC_Memberships_User_Memberships')) {
                                                            $user_memberships = $this->wco_dp_get_user_memberships_by_subscription_id($parent_subscription->get_id());
                                                            if (!empty($user_memberships) && is_array($user_memberships)) {
                                                                foreach ($user_memberships as $um) {
                                                                    $um->cancel_membership('Membership cancelled because subscription was replaced by upsell.');
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            /*
                                              END WooCommerce Subscriptions & Memberships: expire subscription
                                             */
                                        }
                                        update_post_meta($parent_order->get_id(), '_wcu_security_' . $product->get_id(), '1');
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $result;
        }

        /**
         * @since 1.0
         * @param array $order
         * @param int $offer_id
         * @return $offer
         */
        function wcoffers_get_offer_for_order_items($order, $offer_id) {
            $result = FALSE;
            if (!empty($order) && !empty($offer_id)) {
                $upsell = $this->wcoffers_get_upsell_for_order_items($order);
                if (!empty($upsell) && !empty($upsell['id']) && !empty($upsell['upsell_offers'])) {
                    $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
                    if (!empty($upsell['upsell_offers'][$offer_id])) {
                        $offer = $upsell['upsell_offers'][$offer_id];
                        if (!empty($offer['product_id'])) {
                            $result = $offer;
                        }
                    }
                }
            }
            return $result;
        }

        /**
         * @since 1.0
         * @param string $email
         * @param bool $authorize
         * Create user and return user id
         */
        function wcoffers_create_new_customer($email = '', $authorize = FALSE) {
            if (empty($email)) {
                return FALSE;
            }

            $username = sanitize_user(current(explode('@', $email)), true);

            // Ensure username is unique
            $append = 1;
            $o_username = $username;

            while (username_exists($username)) {
                $username = $o_username . $append;
                ++$append;
            }
            $password = wp_generate_password();
            $customer_id = wc_create_new_customer($email, $username, $password);
            if (!empty($customer_id) && !empty($authorize)) {
                wp_set_current_user($customer_id, $username);
                // wp_set_auth_cookie( $user_id );
                wc_set_customer_auth_cookie($customer_id);
                do_action('wp_login', $username);
            }
            return $customer_id;
        }

        /**
         * @since 1.0
         * Return array of order status
         */
        function wcoffers_get_countable_order_statuses() {
            $countable_order_statuses = array(
                'processing',
                'on-hold',
                'completed',
            );
            return apply_filters('wc_1cu_countable_order_statuses', $countable_order_statuses);
        }

        /**
         * @since 1.0
         * Get user memberships by subscription ID
         */
        function wco_dp_get_user_memberships_by_subscription_id($subscription_id) {
            $user_memberships = null;
            $subscription_key = '';
            if (!class_exists('WC_Subscriptions_Order')) {
                return;
            }
            if (SV_WC_Plugin_Compatibility::is_wc_subscriptions_version_gte_2_0()) {
                $subscription_key = wcs_get_old_subscription_key(wcs_get_subscription($subscription_id));
            }
            $user_membership_ids = new WP_Query(array(
                'post_type' => 'wc_user_membership',
                'post_status' => array_keys(wc_memberships_get_user_membership_statuses()),
                'fields' => 'ids',
                'nopaging' => true,
                'suppress_filters' => 1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_subscription_id',
                        'value' => $subscription_id,
                        'type' => 'numeric',
                    ),
                    array(
                        'key' => '_subscription_key',
                        'value' => $subscription_key,
                    ),
                ),
            ));

            if (!empty($user_membership_ids->posts)) {
                $user_memberships = array();
                foreach ($user_membership_ids->posts as $user_membership_id) {
                    $user_memberships[] = wc_memberships_get_user_membership($user_membership_id);
                    // ensure the _subscription_id meta exists
                    if (!metadata_exists('post', $user_membership_id, '_subscription_id')) {
                        update_post_meta($user_membership_id, '_subscription_id', $subscription_id);
                    }

                    // delete the _subscription_key meta
                    if (metadata_exists('post', $user_membership_id, '_subscription_key')) {
                        delete_post_meta($user_membership_id, '_subscription_key');
                    }
                }
            }
            return $user_memberships;
        }

    }

    new wcoffersUpsellOffers();
}