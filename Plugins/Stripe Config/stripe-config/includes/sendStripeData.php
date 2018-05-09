<?php

class sendStripeData {

    //private $url = 'http://member.musicsupervisorguide.com/';
    private $url = '';
    private $arr_keys = array();

    function sendCustomerPayment($methodUrl, $param, $webhook_id) {
        if ($webhook_id == '')
            return '';
        global $wpdb;
        $stripe_select_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_webhook WHERE id=' . $webhook_id;
        $stripe_webhook_data = $wpdb->get_row($stripe_select_sql, ARRAY_A);
        if ($stripe_webhook_data) {            
            $stripe_send_url = $stripe_webhook_data['webhook_url'];
            if (trim($stripe_send_url) != '') {
                $url = $stripe_send_url . $methodUrl;                
                $arr_allowed_data = unserialize($stripe_webhook_data['webhook_data']);                
                foreach ($arr_allowed_data as $k => $a_data) {
                    if ($k == 'email' && $a_data == 0) {
                        $param['user']['customer']['email'] = '';
                    }
                    if ($k == 'stripe_send_url' || $k == 'modify_stripe_webhook_id') {
                        
                    } else if ($k != 'email' && $a_data == 0) {
                        $param['user']['customer']['sources']['data'][0][$k] = '';
                    }
                }
                //echo '<head/><pre>'; print_r($param); exit; echo '</pre>';

                $data_json = json_encode($param);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-Token:014a8a7d49734c5fb838b2ca617b0275'));
                // curl_setopt($ch, CURLOPT_HTTPHEADER,array('X-Token:014a8a7d49734c5fb838b2ca617b0275'));
                // curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                return $response;
            } else {
                //$url = $this->url.$methodUrl;            
                return '';
            }
        } else {
            return '';
        }
    }

}
