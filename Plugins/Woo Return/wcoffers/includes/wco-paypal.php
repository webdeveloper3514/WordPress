<?php

/**
 * Create paypal payment
 */
if (!defined('ABSPATH'))
    die();

class WCO_PayPal_URL {

    private $api_username;
    private $api_password;
    private $api_signature;
    private $brandname;
    private $testmode;
    private $base_url;
    private $redirect_url;
    private $charge_type;

    public function __construct() {
        $settings = get_option('woocommerce_paypal_settings', array());
        $this->brandname = get_bloginfo('name', 'display');
        $this->api_username = $settings['api_username'];
        $this->api_password = $settings['api_password'];
        $this->api_signature = $settings['api_signature'];
        $this->testmode = $settings['testmode'];
        if ($this->testmode == 'yes') {
            $this->redirect_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=';
            $this->base_url = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $this->redirect_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=';
            $this->base_url = 'https://api-3t.paypal.com/nvp';
        }
        $this->charge_type = 'SALE';
    }

    /**
     * @since 1.0
     * @param $order
     * Return array with paypal redirect link
     */
    function wcoffers_paypal_api_set_express_checkout($order) {
        $payload = array(
            'VERSION' => 98,
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
            'METHOD' => 'SetExpressCheckout',
            'PAYMENTREQUEST_0_INVNUM' => $order->get_id(),
            'PAYMENTREQUEST_0_AMT' => $order->get_total(),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $order->get_currency(),
            'CANCELURL' => urlencode(wc_get_cart_url() . '?wcf_paypal=cancel'),
            'RETURNURL' => urlencode($order->get_checkout_order_received_url()),
            'L_BILLINGTYPE0' => 'MerchantInitiatedBilling'
        );
        $payload['BRANDNAME'] = mb_substr($this->brandname, 0, 127);
        $auth_data = array(
            'USER' => $this->api_username,
            'PWD' => $this->api_password,
            'SIGNATURE' => $this->api_signature,
        );
        $payload = array_merge($auth_data, $payload);
        $payload = array('body' => $payload);
        $type = 'POST';
        $type = mb_strtoupper($type);
        $remote_args = array(
            'method' => $type,
            'timeout' => 300,
        );
        $remote_args = array_merge($remote_args, $payload);
        $remote_args['body'] = $this->wcoffers_compile_payload($remote_args['body']);
        $url = $this->base_url;
        $response = wp_remote_request($url, $remote_args);
        if (is_array($response)) {
            $response_data = wp_remote_retrieve_body($response);
            $status = wp_remote_retrieve_response_code($response);
            $status = mb_substr($status, 0, 2);
            if ($status == 20) {
                $result = $this->wcoffers_parse_payload($response_data);
            }
            $execution_url = $this->redirect_url;
            if (isset($result['TOKEN'])) {
                $execution_url .= $result['TOKEN'];
                return array(
                    'result' => 'success',
                    'redirect' => $execution_url,
                );
            }
        }
        return array();
    }

    /**
     * @since 1.0
     * @param int $order_id
     * Add payment with paypal and change status from pending to processing
     */
    public function wcoffers_paypal_api_do_final_payment($order_id) {

        if (!empty($_GET['token']) && !empty($_GET['PayerID'])) {
            $token = urldecode($_GET['token']);
            $payer_id = urldecode($_GET['PayerID']);
            $payment_complete = get_post_meta($order_id, 'paypal_payment_complete', TRUE);
            if (empty($payment_complete)) {
                $response = $this->wcoffers_paypal_api_get_express_checkout_details($token);
                $order = wc_get_order($order_id);
                if (!empty($response['TOKEN'])) {
                    if (!$order->get_billing_address_1()) {
                        $address = array(
                            'first_name' => (!empty($response['FIRSTNAME']) ) ? $response['FIRSTNAME'] : '',
                            'last_name' => (!empty($response['LASTNAME']) ) ? $response['LASTNAME'] : '',
                            'company' => '',
                            'email' => (!empty($response['EMAIL']) ) ? $response['EMAIL'] : '',
                            'phone' => (!empty($response['PHONENUM']) ) ? $response['PHONENUM'] : ( (!empty($response['SHIPTOPHONENUM']) ) ? $response['SHIPTOPHONENUM'] : '' ),
                            'address_1' => (!empty($response['SHIPTOSTREET']) ) ? $response['SHIPTOSTREET'] : '',
                            'address_2' => (!empty($response['SHIPTOSTREET2']) ) ? $response['SHIPTOSTREET2'] : '',
                            'city' => (!empty($response['SHIPTOCITY']) ) ? $response['SHIPTOCITY'] : '',
                            'state' => (!empty($response['SHIPTOSTATE']) ) ? $response['SHIPTOSTATE'] : '',
                            'postcode' => (!empty($response['SHIPTOZIP']) ) ? $response['SHIPTOZIP'] : '',
                            'country' => (!empty($response['SHIPTOCOUNTRYCODE']) ) ? $response['SHIPTOCOUNTRYCODE'] : '',
                        );
                        $order->set_address($address, 'billing');
                        $order->set_address($address, 'shipping');
                    }
                    $response = $this->wcoffers_paypal_api_do_express_checkout_payment($token, $payer_id, $order);

                    // save customer's data if Billing Agreement was created only
                    if (!empty($response['BILLINGAGREEMENTID'])) {
                        $customer_data = array(
                            'customer_id' => $response['BILLINGAGREEMENTID'],
                            'user_id' => $order->get_user_id(),
                            'order_id' => $order_id,
                        );
                        update_post_meta($order_id, 'billingagreementid', $response['BILLINGAGREEMENTID']);
                        update_post_meta($order_id, 'billingagreementemailid', $order->get_billing_email());
                    }

                    // if do_express_checkout_succeded
                    if (isset($response['PAYMENTINFO_0_TRANSACTIONID'])) {
                        $order->payment_complete($response['PAYMENTINFO_0_TRANSACTIONID']);
                        update_post_meta($order_id, 'payer_id', $payer_id);
                        update_post_meta($order_id, '_paypal_payment_complete', TRUE);
                        if ($this->charge_type == 'SALE') {
                            // $order->update_status( 'processing' );
                            $message = 'completed';
                        } else {
                            // $order->update_status( 'on-hold' );
                            $message = 'authorized';
                        }
                        $order->add_order_note(
                                sprintf(
                                        __("Payment %s. <strong>Transaction ID:</strong> %s", 'gb_ocu'), $message, $response['PAYMENTINFO_0_TRANSACTIONID']
                                )
                        );

                        if (WC()->cart) {
                            WC()->cart->empty_cart();
                        }
                    } else {
                        $this->wcoffers_add_order_note_error($response, $order);
                    }
                } else {
                    $this->wcoffers_add_order_note_error($response, $order);
                }
            }
        }
    }

    /**
     * @since 1.0
     * @param int $order_id
     * @param string $customer_email
     * @return boolean Proceed payment when offer product added
     */
    public function wcoffers_process_payment_short($order_id, $customer_email) {
        $result = FALSE;
        $wcoffersUpsellOffers = new wcoffersUpsellOffers();
        $order_id = $wcoffersUpsellOffers->wcoffers_get_order_id($order_id);
        $order = wc_get_order($order_id);
        $amount = $order->get_total();

        if ($amount == 0) {
            $order->payment_complete();
            $result = TRUE;
        } else {
            $parent_order_id = get_post_meta($order_id, 'wcoffers_parent_order_id', TRUE);
            $customer = get_post_meta($parent_order_id, '', 'billingagreementid', TRUE);
            if (!empty($customer)) {
                $result = $this->wcoffers_paypal_api_do_referenced_transaction($customer['billingagreementid'][0], $order, $amount);
                if (!empty($result['TRANSACTIONID'])) {
                    $order->payment_complete($result['TRANSACTIONID']);
                    $message = ( $this->charge_type == 'SALE' ) ? 'completed' : 'authorized';
                    $order->add_order_note(
                            sprintf(
                                    __("%s payment %s.\n\n <strong>Transaction ID:</strong> %s", 'gb_ocu'), $this->method_title_short, $message, $result['TRANSACTIONID']
                            )
                    );
                    $result = TRUE;
                } else {
                    $this->wcoffers_add_order_note_error($result, $order);
                }
            }
        }

        return $result;
    }

    /**
     * @since 1.0
     * @param int $billing_id
     * @param string $order
     * @param int $amount
     * @return type Make payment using parent order reference id
     */
    private function wcoffers_paypal_api_do_referenced_transaction($billing_id, $order, $amount) {
        $payload = array(
            'METHOD' => 'DoReferenceTransaction',
            'VERSION' => 98,
            'PAYMENTACTION' => 'SALE',
            'INVNUM' => $order->get_id(),
            'AMT' => $amount,
            'CURRENCYCODE' => $order->get_currency(),
            'REFERENCEID' => $billing_id,
            'MSGSUBID' => md5($billing_id . $order->get_id()),
        );
        $response = $this->wcoffers_paypal_api_make_request($this->base_url, $payload);
        return $response;
    }

    /**
     * @since 1.0
     * @param array $response
     * @param array $order
     * Add note in WooCommerce order
     */
    private function wcoffers_add_order_note_error($response, $order) {
        if (!empty($order)) {
            $error_text = '';
            if (!empty($response['L_LONGMESSAGE0'])) {
                $error_text .= $response['L_LONGMESSAGE0'];
            }
            if (!empty($response['L_LONGMESSAGE1'])) {
                $error_text .= ' ' . $response['L_LONGMESSAGE1'];
            }
            $order->add_order_note(
                    sprintf(
                            __('Payment failed with message: "%s"', 'gb_ocu'), $error_text
                    )
            );
        }
    }

    /**
     * @since 1.0
     * @param str $token
     * @param int $payer_id
     * @param array $order
     * Paypal do express checkout payment
     */
    private function wcoffers_paypal_api_do_express_checkout_payment($token, $payer_id, $order) {
        $payload = array(
            'VERSION' => 98,
            'METHOD' => 'DoExpressCheckoutPayment',
            'TOKEN' => $token,
            'PAYERID' => $payer_id,
            'BUTTONSOURCE' => 'WCurve_SP',
            'PAYMENTREQUEST_0_INVNUM' => $order->get_id(),
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
            'PAYMENTREQUEST_0_AMT' => $order->get_total(),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $order->get_currency(),
            'MSGSUBID' => md5($token), // payer_id + order->id
        );
        $response = $this->wcoffers_paypal_api_make_request($this->base_url, $payload);
        return $response;
    }

    /**
     * @since 1.0
     * @param str $token
     * Get detail of express checkout using token
     */
    private function wcoffers_paypal_api_get_express_checkout_details($token) {
        $payload = array(
            'VERSION' => 98,
            'METHOD' => 'GetExpressCheckoutDetails',
            'TOKEN' => $token,
        );

        $response = $this->wcoffers_paypal_api_make_request($this->base_url, $payload);

        return $response;
    }

    /**
     * @since 1.0
     * @param str $url
     * @param array $payload
     * @param str $type
     * Make curl request and return result
     */
    private function wcoffers_paypal_api_make_request($url = '', $payload = array(), $type = 'POST') {
        $result = FALSE;

        $auth_data = array(
            'USER' => $this->api_username,
            'PWD' => $this->api_password,
            'SIGNATURE' => $this->api_signature,
        );
        $payload = array_merge($auth_data, $payload);
        $payload = array('body' => $payload);
        $type = mb_strtoupper($type);
        $remote_args = array(
            'method' => $type,
            'timeout' => 300,
        );
        $remote_args = array_merge($remote_args, $payload);
        $remote_args['body'] = $this->wcoffers_compile_payload($remote_args['body']);
        $response = wp_remote_request($url, $remote_args);
        if (is_array($response)) {
            $response_data = wp_remote_retrieve_body($response);
            $status = wp_remote_retrieve_response_code($response);
            $status = mb_substr($status, 0, 2);
            if ($status == 20) {
                $result = $this->wcoffers_parse_payload($response_data);
            }
        }
        return $result;
    }

    /**
     * @since 1.0
     * @param array $payload
     * @return boolean|array Build query for api request
     */
    private function wcoffers_compile_payload($payload) {
        if (!empty($payload) && is_array($payload)) {
            $query = build_query($payload);
            return $query;
        } else {
            return FALSE;
        }
    }

    /**
     * @since 1.0
     * @param array $payload
     * @return boolean|array Parse payload
     */
    private function wcoffers_parse_payload($payload) {
        if (!empty($payload)) {
            $parsed_data = array();
            wp_parse_str($payload, $parsed_data);
            return $parsed_data;
        } else {
            return FALSE;
        }
    }
}
