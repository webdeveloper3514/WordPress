<?php
global $wpdb;

$wpdb->show_errors();

$wpdb->skip_cart_setting = "{$wpdb->prefix}skip_cart_setting";

$table_name = $wpdb->prefix . "skip_cart_setting";

define( 'CURR_PLUGIN_URL', dirname(__FILE__).'/' );

//$domain_for_xml_advertisement = "http://wpplugins.pooja-adani.com/advertisement.xml";

$server_name = "sandbox.bogdanfix.com";