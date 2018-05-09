<?php
$order_args = array(
    'post_type' => 'shop_order',
    'post_status' => array_keys(wc_get_order_statuses()),
    'meta_key' => '_customer_user',
    'meta_value' => $user_id,
    'numberposts' => -1,
    'order_by' => 'ID',
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => 'wcoffers_order_has_deposit',
            'value' => 'yes',
            'compare' => '=',
        )
    )
);
$the_query = new WP_Query($order_args);
?>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
    <th><?php _e('Order', 'wc-offers'); ?></th>
    <th><?php _e('Date', 'wc-offers'); ?></th>
    <th><?php _e('Amount Status', 'wc-offers'); ?></th>
    <th><?php _e('Amount', 'wc-offers'); ?></th>
</thead>
<?php if ($the_query->have_posts()) { ?>    
    <?php
    while ($the_query->have_posts()) {
        $the_query->the_post();
        $order = wc_get_order(get_the_ID());
        ?>
        <tr>
            <td>
                <a href="<?php echo esc_url($order->get_view_order_url()); ?>">
                    <?php echo _x('#', 'hash before order number', 'woocommerce') . $order->get_order_number(); ?>
                </a>
            </td>
            <td>
                <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>                   
            </td>
            <td>                    
                <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
            </td>
            <td>
                <?php
                echo '+' . wc_price($order->get_total());
                ?>
            </td>
        </tr>
        <?php
    }
    /* Restore original Post Data */
    wp_reset_postdata();
} else {
    ?>
    <tr>
        <td colspan="4" style="text-align: center;"><?php _e('No transaction made.', 'wc-offers'); ?></td>
    </tr>
    <?php
}
?>
</table>