<?php
include CURR_PLUGIN_URL.'skipcart-config.php';
//echo CURR_PLUGIN_URL.'skipcart-config.php';
if( isset ( $_GET['edit'] ) && !empty( $_GET['edit'] ) ){
    $id = $_GET['edit'];
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE id = '$id'" );

                if( $retrieve_data[0]->mode == 'product' ){
			$skipcart_target = explode( ',', $retrieve_data[0]->skip_target );
			$skipcart_targets_json = array();

			// get target product names

			foreach( $skipcart_target as $st )
			{
				if( !empty( $st ) )
				{
					$product = wc_get_product( $st );

					if( !empty( $product ) )
					{
                                            $skipcart_targets_json[] = array(
								'id' => $st,
								'text' => $product->get_title(),
							);
					}
				}
			}

			$skipcart_targets_json = json_encode( $skipcart_targets_json );
                }




                if( $retrieve_data[0]->mode == 'category' ){
			$skipcart_target = explode( ',', $retrieve_data[0]->skip_target );
			$skipcart_targets_json = array();

			// get target categories names

			foreach( $skipcart_target as $st )
			{
				if( !empty( $st ) )
				{
					$product = get_term( $st );

					if( !empty( $product ) )
					{
                                            $skipcart_targets_json[] = array(
								'id' => $st,
								'text' => $product->name,
							);
                                            }
				}
			}
			$skipcart_targets_json = json_encode( $skipcart_targets_json );
                }

}




if(isset( $_POST['btn_save'] ) && isset( $_GET['edit'] ) && !empty( $_GET['edit'] ) ){

    $mode = $_POST['select_mode'];
    $global_checkbox = $_POST['enable_for_all'];
    if ( $global_checkbox == 'Global' ) {
            $mode = 'global';
        }
    if( $mode == 'product' && is_array( $_POST['skip_product_target'] ) ){
        $skip_target = implode( ',',$_POST['skip_product_target'] );
    }
    if( $mode == 'category' && is_array( $_POST['skip_category_target'] ) ){
        $skip_target = implode( ',',$_POST['skip_category_target'] );
    }

    $checkout_page_type = $_POST['checkout_page_type'];
    $date = $_POST['created_on'];

    if( $checkout_page_type == "custom" ){
        $custom_checkout_page = $_POST['custom_checkout_page'];
    } else if( $checkout_page_type == "handsomecheckout" ){
        $custom_checkout_page = $_POST['custom_handsomecheckout_page'];
    } else {
        $custom_checkout_page = 0;
    }

    $add_to_cart_button = $_POST['add_to_cart_text'];
    $id = $_GET['edit'];

    $result = $wpdb->update( $wpdb->skip_cart_setting,
            array(
        'mode' => $mode,
        'skip_target' => $skip_target,
        'checkout_page_type' => $checkout_page_type,
        'custom_checkout_page' => $custom_checkout_page,
        'custom_add_to_cart_text' => $add_to_cart_button,
            ),
            array(
                    'id' => $id
            ),
            array(
                     '%s', '%s', '%s', '%d', '%s'
            ),
            array( '%d' ) );

        if ( $result > 0 ) {
            echo '<div class="updated notice gb-updated"><p>Skip Cart Updated Successfully.</p></div>';
        }
        echo skip_cart_force_redirect( 'admin.php?page=skip-cart-submenu-page' );
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE id = '$id'" );
}





if (isset( $_POST['btn_save'] ) && empty( $_GET['edit'] ) ) {

    $mode = $_POST['select_mode'];
    if ( isset( $_POST['enable_for_all'] ) ) {
        $global_checkbox = $_POST['enable_for_all'];
        if ( $global_checkbox == 'Global' ) {
            $mode = 'global';
        }
    }
    if( $mode == 'product' && is_array( $_POST['skip_product_target'] ) ){
        $skip_target = implode( ',',$_POST['skip_product_target'] );
    }
    if( $mode == 'category' && is_array( $_POST['skip_category_target'] ) ){
        $skip_target = implode( ',',$_POST['skip_category_target'] );
    }

    $checkout_page_type = $_POST['checkout_page_type'];

    $date = $_POST['created_on'];
    if( $checkout_page_type == "custom" ){
        $custom_checkout_page = $_POST['custom_checkout_page'];
    } else if( $checkout_page_type == "handsomecheckout" ){
        $custom_checkout_page = $_POST['custom_handsomecheckout_page'];
    }
    else{
        $custom_checkout_page = 0;
    }

    $add_to_cart_button = $_POST['add_to_cart_text'];
                    $result = $wpdb->insert(
					$wpdb->skip_cart_setting,
					array(
						'mode' => $mode,
						'skip_target' => $skip_target,
						'checkout_page_type' => $checkout_page_type,
						'custom_checkout_page' => $custom_checkout_page,
						'custom_add_to_cart_text' => $add_to_cart_button,
                                                'active' => 1,
                                                'created_on' => $date
                                    	),
                                        array(
                                        '%s', '%s', '%s', '%d', '%s', '%d', '%s'
                                      )
				);
                    if ( $result > 0 ) {
                        echo '<div class="updated notice gb-updated"><p>Skip Cart Inserted Successfully.</p></div>';
                    }
                    echo skip_cart_force_redirect( 'admin.php?page=skip-cart-submenu-page' );

}




$args_for_handsome_checkout = array(
    'post_type' => 'handsome-checkout',
    'posts_per_page' => -1
);
$handsome_checkout_query = new WP_Query( $args_for_handsome_checkout );

if ( $handsome_checkout_query->have_posts() ) :
    $handsome_checkout_sr_no = 0;
    $handsome_page_id = array();
    $handsome_page_title = array();

    while ( $handsome_checkout_query->have_posts() ) : $handsome_checkout_query->the_post();
        $handsome_page_id[] = get_the_ID();
        $handsome_page_title[] = get_the_title();
        wp_reset_postdata();
    endwhile;
else:
endif;


$args_for_custom_pages = array(
    'post_type' => 'page',
    'posts_per_page' => -1
);
$custom_page_query = new WP_Query( $args_for_custom_pages );

if ( $custom_page_query->have_posts() ) :

    $custom_page_id = array();
    $custom_page_title = array();

    while ( $custom_page_query->have_posts() ) : $custom_page_query->the_post();
        $custom_page_id[] = get_the_ID();
        $custom_page_title[] = get_the_title();
        wp_reset_postdata();
    endwhile;
    else:
    endif;
?>

<div class="main-div">
    <form method = "POST" action = "#">
    <div class="container-fluid">
        <h1>Skip Cart - by WooCurve</h1>

        <div class="wrap button-view">
            <a href="?page=skip-cart-submenu-page" class="page-title-action">View All</a>
        </div>

        <div class="row">
            <div class="col-sm-9 col-md-6 col-lg-8">

                <div class="postbox">
                    <h2 class="hndle"><span>Skip Cart Settings</span></h2>
                    <div class="inside">
                        <div class="skip-cart-settings">
                            <div class="form-group">
                                <label for="product_or_category">Product or Category</label>

                                <select class="div-toggle form-control" name="select_mode"  id="select_mode" data-target=".my-info-1" <?php if( !empty ( $_GET['edit'] ) ) { if($retrieve_data[0]->mode == 'global' ){ echo "disabled"; } }?> >
                                    <option value="product" data-show=".showproduct" <?php if( !empty( $_GET['edit'] ) ) { if( $retrieve_data[0]->mode == 'product' ){ echo "selected"; } } ?>>Product</option>
                                    <option value="category" data-show=".showcategory" <?php if( !empty( $_GET['edit'] ) ) { if( $retrieve_data[0]->mode == 'category' ){ echo "selected"; } } ?> >category</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="checkbox">
                                    <label><input id="enable_for_all" name="enable_for_all" type="checkbox" value="Global" <?php if( !empty( $_GET['edit'] ) ){if ( $retrieve_data[0]->mode == 'global' ){ echo "checked"; } } ?> >Enable for all products and categories</label>
                                </div>
                            </div>

                            <div class="my-info-1 hidediv" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->mode == 'global' ) { echo "style='display: none;'"; } }?>>
                                <div class="showproduct hide">
                                    <label for="target_product">Target product(s):</label>
                                    <div class="form-group">
                                        <select class="form-control js-example-basic-multiple" name="skip_product_target[]" id="target_product" multiple="multiple" data-selected="<?php if ( !empty( $skipcart_targets_json ) ) echo htmlspecialchars( $skipcart_targets_json ); ?>" data-action="wp_ajax_get_products_title" >
                                        </select>
                                    </div>
                                </div>
                                <div class="showcategory hide">
                                    <label for="target_product">Target Category:</label>
                                    <div class="form-group">
                                        <select class="form-control js-category-basic-multiple" name="skip_category_target[]" id="target_category" multiple="multiple" data-selected = "<?php if ( !empty( $skipcart_targets_json) ) echo htmlspecialchars( $skipcart_targets_json ); ?>">

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="checkout_page_type">Checkout Page Type</label>
                                <select class="form-control" name="checkout_page_type" id="checkout_page_type">
                                    <option value="default" <?php if( !empty( $_GET['edit'] ) ){if( $retrieve_data[0]->checkout_page_type == 'default' ){echo "selected";}}?>>Default</option>

                                        <option value="handsomecheckout" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->checkout_page_type == 'handsomecheckout' ){ echo "selected"; } }?> >Handsome Checkout</option>

                                    <option value="custom" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->checkout_page_type == 'custom' ){ echo "selected"; } }?>>Custom</option>
                                </select>
                                <p id="" class="help-block">Specially if you want to use a custom checkout page or the default one(must be set in "Settings").</p>
                            </div>

                            <div class="form-group custom-checkout-page" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->checkout_page_type == 'custom' ){ echo 'style="display: block;"'; } }?>>
                                <label for="custom_checkout_page">Custom Checkout Page</label>
                                <select class="form-control" name="custom_checkout_page" id="custom_checkout_page">
                                   <?php
                                        for ( $i = 0; $i < count( $custom_page_id ); $i++ ) {
                                            ?>
                                            <option value = "<?php echo $custom_page_id[$i]; ?>" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->custom_checkout_page == $custom_page_id[$i] ){ echo "selected"; } }?>><?php echo $custom_page_title[$i]; ?></option>
                                            <?php
                                        }
                                        ?>
                                </select>
                            </div>

                            <?php if ( is_plugin_active( 'gb-wc-hcc/gb-wc-hcc.php' ) ) { ?>

                                <div class="form-group handsome-checkout-page" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->checkout_page_type == 'handsomecheckout' ){ echo 'style="display: block;"'; } }?>>
                                    <label for="handsome_checkout_page">Handsome Checkout Page</label>
                                    <select class="form-control" name="custom_handsomecheckout_page" id="handsome_checkout_page">
                                        <option value="">-----------Select Checkout Page-----------</option>
                                        <?php
                                        for ( $i = 0; $i < count( $handsome_page_id ); $i++ ) {
                                            ?>
                                        <option value = "<?php echo $handsome_page_id[$i]; ?>" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->custom_checkout_page == $handsome_page_id[$i] ){ echo "selected"; } }?> ><?php echo $handsome_page_title[$i]; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php } else {
                                ?>
                                <div class="form-group handsome-checkout-page" <?php if( !empty( $_GET['edit'] ) ){ if( $retrieve_data[0]->checkout_page_type == 'handsomecheckout' ){ echo 'style="display: block;"'; } }?>>
                                    <label for="handsome_checkout_page">Handsome Checkout Page</label>
                                    <p id="" class="help-block">Handsome Checkout is not enabled on your site. Click here to <a target="_blank" href="<?php echo get_option( 'handsome_checkout_url' ); ?>">learn more</a>  </p>
                                </div>
                                    <?php
                            }
                            ?>

                            <div class="form-group">
                                <label for="add_to_cart_text">Custom Add to Cart Text</label>
                                <input type="text" class="form-control" name="add_to_cart_text" id="add_to_cart_text" value="<?php if( !empty( $_GET['edit'] ) ){ echo $retrieve_data[0]->custom_add_to_cart_text; }?>">
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="created_on" value="<?php echo date( 'Y-m-d' ); ?>" readonly="readonly">
                            </div>


                            <button type="submit" name="btn_save" id="btn_save" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

                <div class="col-sm-3 col-md-6 col-lg-4 top15 border-ad" style="">
                    <?php                   
                    $data_hc_timeout = get_option( '_transient_timeout_' . 'right_hc_ad_block' );
                    $data_non_hc_timeout = get_option( '_transient_timeout_' . 'right_non_ad_block' );                    
                    
                    if ( $data_hc_timeout < time() ){
                           $response_hc = wp_remote_get( 'http://'.$server_name.'/wp-json/rest_api_ads/v1/ads_hc_users' );
                           set_transient( 'right_hc_ad_block', $response_hc['body'] , 900 );
                       }

                    if ( $data_non_hc_timeout < time() ){
                           $response_non_hc = wp_remote_get( 'http://'.$server_name.'/wp-json/rest_api_ads/v1/ads_non_hc_users' );
                           set_transient( 'right_non_ad_block', $response_non_hc['body'] , 900 );
                       }

                    if ( is_plugin_active( 'gb-wc-hcc/gb-wc-hcc.php' ) ){
                        $advert = ( json_decode( get_transient( 'right_hc_ad_block' ), JSON_UNESCAPED_SLASHES ) );            
                       }
                    else{                        
                        $advert = ( json_decode( get_transient( 'right_non_ad_block' ), JSON_UNESCAPED_SLASHES ) );            
                       }
                       $html_ad = "<h3>".$advert[0]['title']."</h3>";
                       $html_ad .= "<p>".$advert[0]['content']."</p>";
                       echo $html_ad;
                    ?>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    jQuery( ".js-category-basic-multiple" ).select2( {
        placeholder: 'Select category',
        minimumInputLength: 2,
        ajax: {
            url: 'admin-ajax.php',
            data: function ( params ) {
                return{
                    search: params.term,
                    action: 'get_categories_title'
                }
            },
            dataType: 'json',
            delay: 250,
            processResults: function ( response ) {
                console.log( response );
                return {
                    results: response
                };
            },
            cache: true
        }
    } );

     jQuery( document ).ready( function( $ ) {
   var $funnel_target = jQuery( 'select.js-category-basic-multiple' );
  if( $funnel_target.length > 0 )
  {
        jQuery( $funnel_target.data( 'selected' ) ).each( function( index, product ){

    $funnel_target.append( '<option value="' + product['id'] + '" selected="selected">' + product['text'] + '</option>' );
   } );
  }
    } );
</script>

<script>
    jQuery( ".js-example-basic-multiple" ).select2( {
        placeholder: 'Select an item',
        minimumInputLength: 2,
        ajax: {
            url: 'admin-ajax.php',
            data: function ( params ) {
                return{
                    search: params.term,
                    action: 'get_products_title'
                }


            },
            dataType: 'json',
            delay: 250,
            processResults: function ( response ) {
                console.log( response );
                return {
                    results: response
                };
            },
            cache: true
        }
    } );

 jQuery( document ).ready( function( $ ) {
   var $funnel_target = jQuery( 'select.js-example-basic-multiple' );

  if( $funnel_target.length > 0 )
  {
   jQuery( $funnel_target.data( 'selected' ) ).each( function( index, product ){
    $funnel_target.append( '<option value="' + product['id'] + '" selected="selected">' + product['text'] + '</option>' );
   } );
  }
    } );
</script>




