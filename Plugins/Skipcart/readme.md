<h2>Skipcart</h2>

- This plugin allow to simply skip the Cart setup for a WooCommerce setup and go straight to the checkout page. 
- Skip Cart lets you link directly from your product page to your checkout page with one click. You can apply this to individual products or apply to categories.

Blog Page: https://woocurve.com/blog/sneak-preview-next-extension-skip-cart/

Check out a video tutorial: https://www.youtube.com/watch?v=LSRXmu9vVGs

<h2>Sample Code</h2>
Due to NDA purpose we cannot show whole plugin code but you can check sample code here.
<code>
  
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

  </code>
