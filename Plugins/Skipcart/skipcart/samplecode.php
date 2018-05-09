<?php

/**
  Plugin Name: Skip Cart
  Plugin URI: #
  Description: This is Skip Cart Plugin By woo curve..
  Author: woo curve
  Version: 1.6
  Author URI: #
 */


/**
 * 
 * my_enqueue($hook) for enque styles and css in plugin only
 */
function my_enqueue( $hook ) {

    if ( 'skip-cart_page_create-skip-cart-page' == $hook || 'toplevel_page_skip-cart-submenu-page' == $hook || 'skip-cart_page_skip-cart-settings' == $hook ) {

        wp_register_style( 'custom_wp_admin_css', plugin_dir_url(__FILE__) . 'assets/css/custom-admin.css' );
        wp_enqueue_style( 'custom_wp_admin_css' );

        wp_register_style( 'bootstrap_min_admin_css', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css' );
        wp_enqueue_style( 'bootstrap_min_admin_css' );

        wp_register_style( 'select2_min_css', plugin_dir_url(__FILE__) . 'assets/css/select2/select2.min.css' );
        wp_enqueue_style( 'select2_min_css' );

        wp_register_script( 'bootstrap_min_admin_js', plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js' );
        wp_enqueue_script( 'bootstrap_min_admin_js' );

        wp_register_script( 'jquery_min_admin_js', plugin_dir_url(__FILE__) . 'assets/js/jquery.min.js' );
        wp_enqueue_script( 'jquery_min_admin_js' );

        wp_register_script( 'select2_min_js', plugin_dir_url(__FILE__) . 'assets/js/select2/select2.min.js' );
        wp_enqueue_script( 'select2_min_js' );

        wp_register_script( 'custom_admin_js', plugin_dir_url(__FILE__) . 'assets/js/custom-js-admin.js' );
        wp_enqueue_script( 'custom_admin_js' );

        load_template(dirname(__FILE__) . '/skipcart-config.php');
    }
?>

<style>
    .toplevel_page_skip-cart-submenu-page .wp-menu-image img{
        padding: 0px !important;
    }
</style>    

<?php
}

add_action( 'admin_enqueue_scripts', 'my_enqueue' );


/**
 * get_products_title() to get products name in select2 dropdown list (ajax)
 */
function get_products_title() {    
    $title_array = array();
    $args = array(
        'post_type' => 'product',
        's' => $_REQUEST['search'],
        'post_status' => 'publish'
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) :
        $i = 0;
        while ( $the_query->have_posts() ) : $the_query->the_post();
                $title_array[$i]['id'] = get_the_ID();
                $title_array[$i]['text'] = get_the_title();
                $i++;
            wp_reset_postdata();
        endwhile;
    else:

    endif;
    echo json_encode( $title_array );
    exit;
}

add_action( 'wp_ajax_get_products_title', 'get_products_title' );


/**
 * get_categories_title() to get name of category (ajax)
 */
function get_categories_title() {
    $cat_array = array();
    $cat_args = array(
        'taxonomy' => 'product_cat',
        'search' => $_REQUEST['search'],
        'hide_empty' => false
    );
    $categories = get_terms( $cat_args );

    $j = 0;
    foreach ( $categories as $category ) {
        $cat_array[$j]['id'] = $category->term_id;
        $cat_array[$j]['text'] = $category->name;
        $j++;
    }
    echo json_encode( $cat_array );
    exit;
}

add_action( 'wp_ajax_get_categories_title', 'get_categories_title' );



/**
 * view_all_skip_cart_menu_page() to main menu ( Skip Cart ) in MenuBar
 */
function view_all_skip_cart_menu_page() {
    
    include plugin_dir_path(__FILE__) . 'skipcart-config.php';
    
    $server_name_contain = get_site_url();
    add_menu_page( 'Skip Cart', 'Skip Cart', 'manage_options', 'skip-cart-submenu-page', 'view_all_skipcarts_callback', plugin_dir_url(__FILE__) . 'assets/img/skip_cart.png', 26 );
    add_submenu_page( 'skip-cart-submenu-page', 'All Skip Cart', 'All Skip Cart', 'manage_options', 'skip-cart-submenu-page', 'view_all_skipcarts_callback' );
    add_submenu_page( 'skip-cart-submenu-page', 'Create Skip Cart', 'Create Skip Cart', 'manage_options', 'create-skip-cart-page', 'create_skip_cart_submenu_page_callback' );

}

add_action( 'admin_menu', 'view_all_skip_cart_menu_page');



/**
 * Callable function for Create New Skip Cart Form
 */
function create_skip_cart_submenu_page_callback() {
    load_template( dirname(__FILE__) . '/templates/skip-cart-settings-layout.php' );
}

/**
 * Callable function for view All Skip Carts
 */
function view_all_skipcarts_callback() {
    load_template( dirname(__FILE__) . '/templates/view-saved-settings-layout.php' );
}


/*
 * Change Add to cart Button name
 */
function woo_custom_cart_button_text( $text, $product ) {

    global $wpdb;
    $table_name = $wpdb->prefix . "skip_cart_setting";

    $id = $product->id;
    $terms = wp_get_post_terms($id, 'product_cat', array( 'fields' => 'ids' ) );
    if ( !empty( $terms ) ) {
        $categories = implode( ",", $terms );
        $where_cat = "|| ( mode = 'category' AND skip_target IN ( $categories ) )";
    } else {
        $where_cat = "";
    }
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE ( ( mode = 'product' AND skip_target LIKE '%" . $id . "%' ) $where_cat) AND ( active = 1 ) ORDER BY created_on DESC LIMIT 1" );
    $retrieve_global_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE ( mode = 'global' AND active = 1 ) ORDER BY created_on DESC LIMIT 1" );

    if ( !empty( $retrieve_data ) ) {
        $text = $retrieve_data[0]->custom_add_to_cart_text;
    } elseif ( ( $retrieve_global_data[0]->mode ) == 'global' ) {
        $text = $retrieve_global_data[0]->custom_add_to_cart_text;
    } else {
        $text = "Add to Cart";
    }
    return $text;
}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text', 9999, 2 );

/**
 * Redirect users after add to cart.
 */
function my_custom_add_to_cart_redirect( $checkout_page_url ) {

    global $wpdb, $woocommerce;

    $table_name = $wpdb->prefix . "skip_cart_setting";
    $id = ( int ) apply_filters( 'woocommerce_add_to_cart_product_id', $_POST['add-to-cart'] );

    $terms = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'ids' ) );
    if ( !empty($terms ) ) {
        $categories = implode(",", $terms);
        $where_cat = "|| ( mode = 'category' AND skip_target IN ( $categories ) )";
    } else {
        $where_cat = "";
    }
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE ( ( mode = 'product' AND skip_target LIKE '%" . $id . "%' ) $where_cat) AND ( active = 1 ) ORDER BY created_on DESC LIMIT 1" );
    $retrieve_global_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE ( mode = 'global' AND active = 1 ) ORDER BY created_on DESC LIMIT 1" );

    if ( !empty( $retrieve_data ) ) {
        if ( $retrieve_data[0]->checkout_page_type == 'custom' ) {
            $custom_checkout_page_id = $retrieve_data[0]->custom_checkout_page;
            $checkout_page_url = get_the_permalink( $custom_checkout_page_id );
        } elseif ( $retrieve_data[0]->checkout_page_type == 'handsomecheckout' ) {
            $custom_checkout_page_id = $retrieve_data[0]->custom_checkout_page;
            $checkout_page_url = get_the_permalink( $custom_checkout_page_id );
        } else {
            $checkout_page_url = WC()->cart->get_checkout_url();
        }
        return $checkout_page_url;
    } elseif ( !empty( $retrieve_global_data ) ) {
        if ( $retrieve_global_data[0]->checkout_page_type == 'custom' ) {
            $custom_checkout_page_id = $retrieve_global_data[0]->custom_checkout_page;
            $checkout_page_url = get_the_permalink( $custom_checkout_page_id );
        } elseif ( $retrieve_global_data[0]->checkout_page_type == 'handsomecheckout' ) {
            $custom_checkout_page_id = $retrieve_global_data[0]->custom_checkout_page;
            $checkout_page_url = get_the_permalink( $custom_checkout_page_id );
        } else {
            $checkout_page_url = WC()->cart->get_checkout_url();
        }
        return $checkout_page_url;
    } else {
        $checkout_page_url = WC()->cart->get_checkout_url();
    }
}

add_filter( 'add_to_cart_redirect', 'my_custom_add_to_cart_redirect', 9999 );


/**
 * create_plugin_database_table() for create table in database when plugin is being activated  
 */
function create_plugin_database_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "skip_cart_setting";

    $query = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT( 9 ) AUTO_INCREMENT,
        `mode`  varchar( 255 )   NOT NULL,
        `skip_target` varchar( 255 ),
        `checkout_page_type` varchar( 255 ),
        `custom_checkout_page` varchar( 255 ),
        `custom_add_to_cart_text` varchar( 255 ),
        `active` int( 1 ) DEFAULT 1,
        `created_on` timestamp DEFAULT CURRENT_TIMESTAMP,       
        PRIMARY KEY ( id ) );";

    $wpdb->query( $query );
}

register_activation_hook( __FILE__, 'create_plugin_database_table' );


/**
 * @param type $url
 * @param type $timeout
 * @return type
 * skip_cart_force_redirect() for redirect page
 */
function skip_cart_force_redirect( $url = '', $timeout = 1000 )
{
	if( !empty( $url ) && !empty( $timeout ) )
	{
		return '<script type="text/javascript">var a = setTimeout( function(){ window.location.replace(\'' . $url . '\'); }, ' . intval( $timeout ) . ' );</script>';
	}
}


function wpdocs_register_my_setting() {
    register_setting( 'my_options_group', 'my_option_name', 'intval' ); 
} 
add_action( 'admin_init', 'wpdocs_register_my_setting' );
