<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include CURR_PLUGIN_URL . 'skipcart-config.php';

/**
 * Delete Skip Cart
 */
if ( !empty( $_GET['del'] ) ) {

    $delete_record = intval( $_GET['del'] );

    $result = $wpdb->delete(
            $wpdb->skip_cart_setting,
        array(
            'id' => $delete_record
            ),
        array(
            '%d'
            )
    );
}

$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name" );


/**
 *  switch Skip Cart Active State / Deactive State
 */
if ( !empty( $_GET['switch'] ) ) {
    $skip_target_id = intval( $_GET['switch'] );

    $active = $wpdb->get_var( "SELECT active FROM $table_name WHERE id = $skip_target_id LIMIT 1;" );

    if ( $active == 1 ) {
        $result = $wpdb->update(
                $wpdb->skip_cart_setting,
                array(
                    'active' => 0
                ),
                array(
                    'id' => $skip_target_id
                )
        );
    } else {
        $result = $wpdb->update(
                $wpdb->skip_cart_setting, array(
            'active' => 1
                ), array(
            'id' => $skip_target_id
                )
        );
    }

    if ( $result !== FALSE ) {
        if ( !empty( $active ) ) {
            echo '<div class="updated notice gb-updated"><p>Skip Cart is now switched off.</p></div>';
        } else {
            echo '<div class="updated notice gb-updated"><p>Skip Cart is now active.</p></div>';
        }

        echo skip_cart_force_redirect( 'admin.php?page=skip-cart-submenu-page', 1500 );
    } else {
        echo '<div class="updated error gb-updated"><p>Failed to switch Skip Cart.</p></div>';
    }
}
?>

<div class="main-div">
    <form method = "POST" action = "#">
        <div class="container-fluid">
            <h1>Skip Cart - by WooCurve</h1>

            <div class="wrap button-create">
                <a href="?page=create-skip-cart-page" class="page-title-action">Create New</a>
            </div>

            <div class="row top15">
                <div class="container">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product/Category</th>
                                <th>Skip Target</th>
                                <th>Checkout Page</th>
                                <th>Created On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

<?php
if( !empty($retrieve_data) ){
foreach ( $retrieve_data as $retrieved_data ) {
    ?>
       <tr <?php if ( ( $retrieved_data->active != 1 ) ) { echo "class = 'disabled'";  } ?>> 
        <td><?php echo $retrieved_data->mode; ?></td>
        <td style='width: 30%;'>
    <?php
    if ( !empty( $retrieved_data->skip_target ) ) {

        if ( $retrieved_data->mode == 'product' ) {

            $retrieved_data->skip_target = explode( ',', $retrieved_data->skip_target );
            echo "Result of skip target Product(s):" . "</br>";
            foreach ( $retrieved_data->skip_target as $key_target_product => $value_target_product ) {

                $skipped_product = get_post( $value_target_product );
                $id_of_skipped_product = $skipped_product->ID;
                $title_skipped_product = $skipped_product->post_title;

                echo "<a href = '" . get_edit_post_link( $value_target_product ) . " ' >#" . $id_of_skipped_product . ' &ndash; ' . $title_skipped_product . "</a>" . "</br>";
            }
        }

        if ( $retrieved_data->mode == 'category' ) {

            $retrieved_data->skip_target = explode( ',', $retrieved_data->skip_target );
            echo "Result of skip target Category(ies):" . "</br>";
            foreach ( $retrieved_data->skip_target as $key_target_category => $value_target_category ) {
                $category = get_term( $value_target_category );
                $title_skipped_category = $category->name;
                $id_of_skipped_category = $value_target_category;
                $link_skipped_category = get_category_link( $value_target_category );
                echo "<a href = '" . $link_skipped_category . " ' >#" . $id_of_skipped_category . ' &ndash; ' . $title_skipped_category . "</a>" . "</br>";
            }
        }
    }
    if ( $retrieved_data->mode == 'global' && ( $retrieved_data->skip_target == '' || $retrieved_data->skip_target == 0 ) ) {
        echo "<div><b>Skip Cart is applied globally</b> to all existing products. <span style='opacity: 0.3;'>Skip Cart applied to single products will override this one.</span></div>";
    }
    ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ( $retrieved_data->custom_checkout_page == 0 ) {
                                            $id_of_checkout_page = get_option( 'woocommerce_checkout_page_id' );
                                            $title_checkout_page = get_the_title( $id_of_checkout_page );
                                            echo "<a href = '" . get_edit_post_link( $id_of_checkout_page ) . " ' > " . $title_checkout_page . " </a>";
                                        }

                                        if ( $retrieved_data->custom_checkout_page != 0 ) {
                                            $title_checkout_page = get_the_title( $retrieved_data->custom_checkout_page );
                                            echo "<a href = '" . get_edit_post_link( $retrieved_data->custom_checkout_page ) . " ' > " . $title_checkout_page . " </a>";
                                        }
                                        ?>
                                    </td>
                                    <td><?php $date = $retrieved_data->created_on;
                                    echo date( "m/d/Y", strtotime( $date ) );
                                    ?></td>

                                    <td>
                                        <div class="action-buttons">  <a href="?page=create-skip-cart-page&edit=<?php echo $retrieved_data->id; ?>" class="gb-ocu-saved-funnel-edit button btn-action">Edit</a>
                                            <a href="?page=skip-cart-submenu-page&switch=<?php echo $retrieved_data->id; ?>" class="gb-ocu-saved-funnel-edit button btn-action"><?php if ( !empty( $retrieved_data->active ) ) {
                                        echo 'Disable';
                                    } else {
                                        echo '<b>Enable</b>';
                                    } ?></a>
                                            <a href="?page=skip-cart-submenu-page&del=<?php echo $retrieved_data->id; ?>" class="gb-ocu-saved-funnel-delete button btn-action" onclick="return confirm( 'Are you sure you want to delete this item?' );">Delete</a></td></div>
                                        <?php
                                        echo "</tr>";
                                    }
                }
                else{
                    echo '<tr><td colspan="5">Oops, no skipcart saved here...</td></tr>';
                }
                                    ?>
                                        
                        </tbody>
                    </table>

                </div>

            </div>

            <div class="col-sm-3 col-md-6 col-lg-4" style="">

            </div>

        </div>


    </form>
</div>
