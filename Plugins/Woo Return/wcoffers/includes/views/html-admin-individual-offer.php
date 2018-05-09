<h3>
    <?php
    _e('Individual Offers', 'wc-offers');
    ?>
</h3>
<div id="wco_product_pricing" class="wco_rules">
    <div id="wco_product_pricing_wrapper">
        <?php
        $wco_product_pricing = get_option('wco_product_pricing', array());
        if ($wco_product_pricing) {
            foreach ($wco_product_pricing as $key => $single_price) {
                ?>
                <div class="wco-product-pricing-row">
                    <div class="wco-product-pricing-title">
                        <span class="title-span">
                            <?php
                            if ($single_price['private_note'] == '') {
                                _e('Untitle', 'wc-offers');
                            } else {
                                echo $single_price['private_note'];
                            }
                            ?>
                        </span>
                        <span class="wco_row_remove" title="Remove">
                            <span class="dashicons dashicons-no-alt"></span>
                        </span>
                    </div>
                    <div class="wco-product-pricing-content">
                        <div class="wco-product-pricing-first-row">
                            <div class="wco-col-6">
                                <label>
                                    <?php _e('Method', 'wc-offers'); ?>
                                </label>
                                <select class="wco_product_pricing_method" name="wco_product_pricing[<?php echo $key; ?>][method]">
                                    <option value="simple" <?php selected($single_price['method'], 'simple') ?>><?php _e('Simple', 'wc-offers'); ?></option>
                                    <option value="bulk" <?php selected($single_price['method'], 'bulk') ?>><?php _e('Bulk pricing', 'wc-offers') ?></option>
                                    <option value="group" <?php selected($single_price['method'], 'group') ?>><?php _e('Group of products', 'wc-offers') ?></option>
                                </select>
                            </div>
                            <div class="wco-col-6">
                                <label>
                                    <?php _e('Private Note', 'wc-offers'); ?>
                                </label>
                                <input type="text" class="wco_product_pricing_private_note" value="<?php echo $single_price['private_note']; ?>" name="wco_product_pricing[<?php echo $key; ?>][private_note]">
                            </div>
                        </div>
                        <div class="wco-product-pricing-bulk-row wco_product_pricing_bulk_method" style="display: none;">
                            <label><?php _e('Quantity Range', 'wc-offers'); ?></label>
                            <div class="wco_product_pricing_bulk_range">                    
                                <div class="wco_rules_add_range_row" style="text-align: right;">
                                    <button type="button" class="button"><?php _e('Add Range', 'wc-offers'); ?></button>
                                </div>
                                <table class="wco-bulk-range-quantity-table" style="width: 100%;">
                                    <tr style="text-align: left;">
                                        <th><?php _e('Minimum Quantity', 'wc-offers') ?></th>
                                        <th><?php _e('Maximum Quantity', 'wc-offers') ?></th>
                                        <th><?php _e('Discount Type', 'wc-offers') ?></th>
                                        <th><?php _e('Discount Amount', 'wc-offers') ?></th>
                                        <th><?php _e('Action', 'wc-offers') ?></th>
                                    </tr>
                                    <?php
                                    $quantity_range = isset($single_price['quantity_range']) ? $single_price['quantity_range'] : array();
                                    if ($quantity_range) {
                                        $count = 0;
                                        foreach ($quantity_range as $single_pre_quantity) {
                                            $readonly = '';
                                            if ($count != 0) {
                                                $readonly = 'readonly="readonly"';
                                            }
                                            ?>
                                            <tr class="wco-bulk-range-quantity-rule-tr">
                                                <td><input <?php echo $readonly; ?> data-name="min_quantity" class="textNumberOnly min_quantity" min="0" max="100" type="text" value="<?php echo $single_pre_quantity['min_quantity']; ?>" name="wco_product_pricing[<?php echo $key; ?>][quantity_range][<?php echo $count; ?>][min_quantity]"></td>
                                                <td><input data-name="max_quantity" class="textNumberOnly max_quantity" min="0" max="100" type="text" value="<?php echo $single_pre_quantity['max_quantity']; ?>" name="wco_product_pricing[<?php echo $key; ?>][quantity_range][<?php echo $count; ?>][max_quantity]"></td>
                                                <td>
                                                    <select name="wco_product_pricing[<?php echo $key; ?>][quantity_range][<?php echo $count; ?>][discount_type]">
                                                        <option value="fixed_pricing" <?php selected($single_pre_quantity['discount_type'], 'fixed_pricing') ?>><?php _e('Fixed Price', 'wc-offers'); ?></option>
                                                        <option value="percentage" <?php selected($single_pre_quantity['discount_type'], 'percentage') ?>><?php _e('Percentage Discount', 'wc-offers') ?></option>
                                                    </select>
                                                </td>
                                                <td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="<?php echo $single_pre_quantity['discount_amount']; ?>" min="0" max="100" name="wco_product_pricing[<?php echo $key; ?>][quantity_range][<?php echo $count; ?>][discount_amount]"></td>
                                                <td><div class="button wco-delete-quantity-range-rule">Remove</div></td>
                                            </tr>
                                            <?php
                                            $count++;
                                        }
                                    } else {
                                        ?>
                                        <tr class="wco-no-rule-found">
                                            <td colspan="5" style="text-align: center;padding: 10px 0;">
                                                <?php _e('No rules found', 'wc-offers'); ?>
                                            </td>
                                        </tr>
                                    <?php }
                                    ?>                                                        
                                </table>
                            </div>
                        </div>
                        <div class="wco-product-pricing-group-row wco_product_pricing_group_method">
                            <label><?php _e('Products in Group', 'wc-offers'); ?></label>
                            <div class="wco_product_pricing_products_group">
                                <div class="wco_rules_add_product_group_row" style="text-align: right;">
                                    <button type="button" class="button"><?php _e('Add Product', 'wc-offers'); ?></button>
                                </div>
                                <?php
                                if (isset($single_price['group_condition'])) {                                    
                                    foreach ($single_price['group_condition'] as $K => $condition) {
                                        ?>
                                        <div class="wco-product-pricing-product-group-row">
                                            <div class="wco_product_condition_content">
                                                <input data-name='quantity' type="text" class="textNumberOnly" placeholder="Quantity" value="<?php echo $condition['quantity']; ?>" name="wco_product_pricing[<?php echo $key; ?>][group_condition][<?php echo $K; ?>][quantity]">
                                            </div>
                                            <div class="wco_product_condition_content">
                                                <select data-name='type' class="wco_product_condition_type" name="wco_product_pricing[<?php echo $key; ?>][group_condition][<?php echo $K; ?>][type]">
                                                    <option value="products" <?php selected($condition['type'], 'products') ?>><?php _e('Product', 'wc-offers'); ?></option>
                                                    <option value="products_category" <?php selected($condition['type'], 'products_category') ?>><?php _e('Product Category', 'wc-offers'); ?></option>
                                                </select>
                                            </div>                                            
                                            <div class="wco_product_condition_content wco_product_condition_type_product">
                                                <select  data-name='product_id' class="wcoffers_cashback_products" multiple="multiple" style="width: 100%;" name="wco_product_pricing[<?php echo $key; ?>][group_condition][<?php echo $K; ?>][product_id][]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                    <?php
                                                    $product_ids = isset($condition['product_id']) ? $condition['product_id'] : array();
                                                    $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                                    $loop = new WP_Query($args);
                                                    if ($loop->have_posts()) {
                                                        while ($loop->have_posts()) : $loop->the_post();
                                                            echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                        endwhile;
                                                        wp_reset_query();
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="wco_product_condition_content wco_product_condition_type_product_category" style="display: none;">
                                                <select data-name='product_cat' class="wcoffers_cashback_products" style="width: 100%;" multiple="multiple" name="wco_product_pricing[<?php echo $key; ?>][group_condition][<?php echo $K; ?>][product_cat][]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                    <?php
                                                    $cat_ids = isset($condition['product_cat']) ? $condition['product_cat'] : array();
                                                    $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                    if ($categories) {
                                                        foreach ($categories as $cat) {
                                                            echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="wco_product_group_remove">
                                                <span class="wco_remove_product_group_row" title="close">
                                                    <span class="dashicons dashicons-no-alt"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    echo '<div class="wco-product-condition-nule" style="text-align:center;display:none;">';
                                    _e('No rules are configured.', 'wc-offers');
                                    echo '</div>';
                                } else {
                                    echo '<div class="wco-product-condition-nule" style="text-align:center;">';
                                    _e('No rules are configured.', 'wc-offers');
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="wco-product-pricing-group-row wco_product_pricing_group_method">
                            <label><?php _e('Adjustment', 'wc-offers'); ?></label>
                            <div class="wco-col-6">
                                <select name="wco_product_pricing[<?php echo $key; ?>][group_pricing_method]">
                                    <option value="fixed_pricing_product" <?php selected($single_price['group_pricing_method'], 'fixed_pricing_product') ?>><?php _e('Fixed Price Per Product', 'wc-offers'); ?></option>
                                    <option value="fixed_pricing_group" <?php selected($single_price['group_pricing_method'], 'fixed_pricing_group') ?>><?php _e('Fixed Price Per Group', 'wc-offers'); ?></option>
                                    <option value="percentage_product" <?php selected($single_price['group_pricing_method'], 'percentage_product') ?>><?php _e('Percentage Discount Per Product', 'wc-offers') ?></option>
                                    <!--<option value="percentage_group" <?php selected($single_price['group_pricing_method'], 'percentage_group') ?>><?php _e('Percentage Discount Per Group', 'wc-offers') ?></option>-->
                                </select>
                            </div>
                            <div class="wco-col-6">
                                <input type="text" class="textNumberWithFloat" placeholder="00.00" value="<?php echo $single_price['group_pricing_value']; ?>" name="wco_product_pricing[<?php echo $key; ?>][group_pricing_value]">
                            </div>
                        </div>
                        <div class="wco-product-pricing-adjustment-row wco_product_pricing_simple_method">
                            <label><?php _e('Adjustment', 'wc-offers'); ?></label>
                            <div class="wco-col-6">
                                <select name="wco_product_pricing[<?php echo $key; ?>][pricing_method]">
                                    <option value="fixed_pricing" <?php selected($single_price['pricing_method'], 'fixed_pricing') ?>><?php _e('Fixed Price', 'wc-offers'); ?></option>
                                    <option value="percentage" <?php selected($single_price['pricing_method'], 'percentage') ?>><?php _e('Percentage Discount', 'wc-offers') ?></option>
                                </select>
                            </div>
                            <div class="wco-col-6">
                                <input type="text" class="textNumberWithFloat" placeholder="00.00" value="<?php echo $single_price['pricing_value']; ?>" name="wco_product_pricing[<?php echo $key; ?>][pricing_value]">
                            </div>
                        </div>
                        <div class="wco-product-pricing-products-row wco_product_pricing_simple_bulk_method">
                            <label><?php _e('Products', 'wc-offers'); ?></label>
                            <div class="wco_product_pricing_products">
                                <div id="wco_rules_add_product_row" style="text-align: right;">
                                    <button type="button" class="button"><?php _e('Add Product', 'wc-offers'); ?></button>
                                </div>
                                <?php
                                if (isset($single_price['product_condition'])) {
                                    foreach ($single_price['product_condition'] as $K => $condition) {
                                        ?>
                                        <div class="wco-product-pricing-product-condition-row">        
                                            <div class="wco_product_condition_content">
                                                <select data-name='type' class="wco_product_condition_type" name="wco_product_pricing[<?php echo $key; ?>][product_condition][<?php echo $K; ?>][type]">
                                                    <option value="products" <?php selected($condition['type'], 'products') ?>><?php _e('Product', 'wc-offers'); ?></option>
                                                    <option value="products_category" <?php selected($condition['type'], 'products_category') ?>><?php _e('Product Category', 'wc-offers'); ?></option>
                                                </select>
                                            </div>
                                            <div class="wco_product_condition_content">
                                                <select data-name='method_option' name="wco_product_pricing[<?php echo $key; ?>][product_condition][<?php echo $K; ?>][method_option]">
                                                    <option value="in_list" <?php selected($condition['method_option'], 'in_list') ?>><?php _e('in list', 'wc-offers'); ?></option>
                                                    <option value="not_in_list" <?php selected($condition['method_option'], 'not_in_list') ?>><?php _e('not in list', 'wc-offers'); ?></option>
                                                </select>
                                            </div>
                                            <div class="wco_product_condition_content wco_product_condition_type_product" style="padding: 0;">
                                                <select  data-name='product_id' class="wcoffers_cashback_products" multiple="multiple" style="width: 100%;" name="wco_product_pricing[<?php echo $key; ?>][product_condition][<?php echo $K; ?>][product_id][]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                                                    <?php
                                                    $product_ids = isset($condition['product_id']) ? $condition['product_id'] : array();
                                                    $args = array('post_type' => 'product', 'posts_per_page' => -1);
                                                    $loop = new WP_Query($args);
                                                    if ($loop->have_posts()) {
                                                        while ($loop->have_posts()) : $loop->the_post();
                                                            echo '<option value="' . get_the_ID() . '" ' . selected(in_array(get_the_ID(), $product_ids), true, false) . '>' . get_the_title() . '</option>';
                                                        endwhile;
                                                        wp_reset_query();
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="wco_product_condition_content wco_product_condition_type_product_category" style="display: none;padding: 0;">
                                                <select data-name='product_cat' class="wcoffers_cashback_products" style="width: 100%;" multiple="multiple" name="wco_product_pricing[<?php echo $key; ?>][product_condition][<?php echo $K; ?>][product_cat][]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                                                    <?php
                                                    $cat_ids = isset($condition['product_cat']) ? $condition['product_cat'] : array();
                                                    $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                                                    if ($categories) {
                                                        foreach ($categories as $cat) {
                                                            echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected(in_array($cat->term_id, $cat_ids), true, false) . '>' . esc_html($cat->name) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="wco_product_condition_content_remove">
                                                <span class="wco_remove_product_row" title="close">
                                                    <span class="dashicons dashicons-no-alt"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    echo '<div class="wco-product-condition-nule" style="text-align:center;display:none;">';
                                    _e('Applies to all products', 'wc-offers');
                                    echo '</div>';
                                } else {
                                    echo '<div class="wco-product-condition-nule" style="text-align:center;">';
                                    _e('Applies to all products', 'wc-offers');
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            
        }
        ?>

    </div>
    <div id="wco_rules_add_row" style="text-align: left;">
        <button type="button" class="button" value="Add Rule"><?php _e('Add Rule', 'wc-offers'); ?></button>
    </div>
</div>
<div class="wco-product-pricing-wrap" style="display: none;">
    <div class="wco-product-pricing-row">
        <div class="wco-product-pricing-title">
            <span class="title-span">
                <?php _e('Untitle', 'wc-offers'); ?>
            </span>
            <span class="wco_row_remove" title="Remove">
                <span class="dashicons dashicons-no-alt"></span>
            </span>
        </div>
        <div class="wco-product-pricing-content">
            <div class="wco-product-pricing-first-row">
                <div class="wco-col-6">
                    <label>
                        <?php _e('Method', 'wc-offers'); ?>
                    </label>
                    <select class="wco_product_pricing_method" name="wco_product_pricing[%N%][method]">
                        <option value="simple"><?php _e('Simple', 'wc-offers'); ?></option>
                        <option value="bulk"><?php _e('Bulk Pricing', 'wc-offers') ?></option>
                        <option value="group"><?php _e('Group of Products', 'wc-offers') ?></option>
                    </select>
                </div>
                <div class="wco-col-6">
                    <label>
                        <?php _e('Private Note', 'wc-offers'); ?>
                    </label>
                    <input type="text" class="wco_product_pricing_private_note" name="wco_product_pricing[%N%][private_note]">
                </div>
            </div>
            <div class="wco-product-pricing-bulk-row wco_product_pricing_bulk_method" style="display: none;">
                <label><?php _e('Quantity Range', 'wc-offers'); ?></label>
                <div class="wco_product_pricing_bulk_range">                    
                    <div class="wco_rules_add_range_row" style="text-align: right;">
                        <button type="button" class="button"><?php _e('Add Range', 'wc-offers'); ?></button>
                    </div>
                    <table class="wco-bulk-range-quantity-table" style="width: 100%;">
                        <tr style="text-align: left;">
                            <th><?php _e('Minimum Quantity', 'wc-offers') ?></th>
                            <th><?php _e('Maximum Quantity', 'wc-offers') ?></th>
                            <th><?php _e('Discount Type', 'wc-offers') ?></th>
                            <th><?php _e('Discount Amount', 'wc-offers') ?></th>
                            <th><?php _e('Action', 'wc-offers') ?></th>
                        </tr>                    
                        <tr class="wco-no-rule-found">
                            <td colspan="5" style="text-align: center;padding: 0;">
                                <?php _e('No rules found', 'wc-offers'); ?>
                            </td>
                        </tr>                    
                    </table>
                </div>
            </div>
            <div class="wco-product-pricing-adjustment-row wco_product_pricing_simple_method">
                <label><?php _e('Adjustment', 'wc-offers'); ?></label>
                <div class="wco-col-6">
                    <select name="wco_product_pricing[%N%][pricing_method]">
                        <option value="fixed_pricing"><?php _e('Fixed Price', 'wc-offers'); ?></option>
                        <option value="percentage"><?php _e('Percentage Discount', 'wc-offers') ?></option>
                    </select>
                </div>
                <div class="wco-col-6">
                    <input type="text" class="textNumberWithFloat" placeholder="00.00" value="" name="wco_product_pricing[%N%][pricing_value]">
                </div>
            </div>
            <div class="wco-product-pricing-products-row wco_product_pricing_bulk_method">
                <label><?php _e('Products', 'wc-offers'); ?></label>
                <div class="wco_product_pricing_products">                    
                    <div id="wco_rules_add_product_row" style="text-align: right;">
                        <button type="button" class="button"><?php _e('Add Product', 'wc-offers'); ?></button>
                    </div>
                    <?php
                    echo '<div class="wco-product-condition-nule" style="text-align:center;">';
                    _e('Applies to all products', 'wc-offers');
                    echo '</div>';
                    ?>
                </div>
            </div>
            <div class="wco-product-pricing-products-row wco_product_pricing_simple_method">
                <label><?php _e('Products', 'wc-offers'); ?></label>
                <div class="wco_product_pricing_products">                    
                    <div id="wco_rules_add_product_row" style="text-align: right;">
                        <button type="button" class="button"><?php _e('Add Product', 'wc-offers'); ?></button>
                    </div>
                    <?php
                    echo '<div class="wco-product-condition-nule" style="text-align:center;">';
                    _e('Applies to all products', 'wc-offers');
                    echo '</div>';
                    ?>
                </div>
            </div>
            <div class="wco-product-pricing-group-row wco_product_pricing_group_method">
                <label><?php _e('Products in Group', 'wc-offers'); ?></label>
                <div class="wco_product_pricing_products_group">
                    <div class="wco_rules_add_product_group_row" style="text-align: right;">
                        <button type="button" class="button"><?php _e('Add Product', 'wc-offers'); ?></button>
                    </div>
                    <?php                    
                    echo '<div class="wco-product-condition-nule" style="text-align:center;">';
                    _e('No rules are configured.', 'wc-offers');
                    echo '</div>';                    
                    ?>
                </div>
            </div>
            <div class="wco-product-pricing-group-row wco_product_pricing_group_method">
                <label><?php _e('Adjustment', 'wc-offers'); ?></label>
                <div class="wco-col-6">
                    <select name="wco_product_pricing[%N%][group_pricing_method]">
                        <option value="fixed_pricing_product"><?php _e('Fixed Price Per Product', 'wc-offers'); ?></option>
                        <option value="fixed_pricing_group"><?php _e('Fixed Price Per Group', 'wc-offers'); ?></option>
                        <option value="percentage_product"><?php _e('Percentage Discount Per Product', 'wc-offers') ?></option>
                        <!--<option value="percentage_group"><?php _e('Percentage Discount Per Group', 'wc-offers') ?></option>-->
                    </select>
                </div>
                <div class="wco-col-6">
                    <input type="text" class="textNumberWithFloat" placeholder="00.00" value="" name="wco_product_pricing[%N%][group_pricing_value]">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="wco-product-pricing-product-condition-wrap" style="display: none;">
    <div class="wco-product-pricing-product-condition-row">        
        <div class="wco_product_condition_content">
            <select data-name='type' class="wco_product_condition_type" name="wco_product_pricing[%N%][product_condition][%NC%][type]">
                <option value="products"><?php _e('Product', 'wc-offers'); ?></option>
                <option value="products_category"><?php _e('Product Category', 'wc-offers'); ?></option>
            </select>
        </div>
        <div class="wco_product_condition_content">
            <select data-name='method_option' name="wco_product_pricing[%N%][product_condition][%NC%][method_option]">
                <option value="in_list"><?php _e('in list', 'wc-offers'); ?></option>
                <option value="not_in_list"><?php _e('not in list', 'wc-offers'); ?></option>
            </select>
        </div>
        <div class="wco_product_condition_content wco_product_condition_type_product" style="padding: 0;">
            <select data-name='product_id' class="wco_product_select2" multiple="multiple" style="width: 100%;" name="wco_product_pricing[%N%][product_condition][%NC%][product_id][]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                <?php
                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                $loop = new WP_Query($args);
                if ($loop->have_posts()) {
                    while ($loop->have_posts()) : $loop->the_post();
                        echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                    endwhile;
                    wp_reset_query();
                }
                ?>
            </select>
        </div>
        <div class="wco_product_condition_content wco_product_condition_type_product_category" style="display: none;padding: 0;">
            <select data-name='product_cat' class="wco_product_select2" style="width: 100%;" multiple="multiple" name="wco_product_pricing[%N%][product_condition][%NC%][product_cat][]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                <?php
                $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                if ($categories) {
                    foreach ($categories as $cat) {
                        echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="wco_product_condition_content_remove">
            <span class="wco_remove_product_row" title="close">
                <span class="dashicons dashicons-no-alt"></span>
            </span>
        </div>
    </div>    
</div>
<div class="wco-product-pricing-product-group-wrap" style="display: none;">
    <div class="wco-product-pricing-product-group-row">
        <div class="wco_product_condition_content">
            <input data-name='quantity' type="text" class="textNumberOnly" placeholder="Quantity" value="" name="wco_product_pricing[%N%][group_condition][%NC%][quantity]">
        </div>
        <div class="wco_product_condition_content">
            <select data-name='type' class="wco_product_condition_type" name="wco_product_pricing[%N%][group_condition][%NC%][type]">
                <option value="products"><?php _e('Product', 'wc-offers'); ?></option>
                <option value="products_category"><?php _e('Product Category', 'wc-offers'); ?></option>
            </select>
        </div>        
        <div class="wco_product_condition_content wco_product_condition_type_product">
            <select data-name='product_id' class="wco_product_select2" multiple="multiple" style="width: 100%;" name="wco_product_pricing[%N%][group_condition][%NC%][product_id][]" data-placeholder="<?php esc_attr_e('Search product', 'wc-offers'); ?>">
                <?php
                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                $loop = new WP_Query($args);
                if ($loop->have_posts()) {
                    while ($loop->have_posts()) : $loop->the_post();
                        echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                    endwhile;
                    wp_reset_query();
                }
                ?>
            </select>
        </div>
        <div class="wco_product_condition_content wco_product_condition_type_product_category" style="display: none;">
            <select data-name='product_cat' class="wco_product_select2" style="width: 100%;" multiple="multiple" name="wco_product_pricing[%N%][group_condition][%NC%][product_cat][]" data-placeholder="<?php esc_attr_e('Search for a product categories&hellip;', 'wc-offers'); ?>">
                <?php
                $categories = get_terms('product_cat', 'orderby=name&hide_empty=1');
                if ($categories) {
                    foreach ($categories as $cat) {
                        echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="wco_product_group_remove">
            <span class="wco_remove_product_group_row" title="close">
                <span class="dashicons dashicons-no-alt"></span>
            </span>
        </div>
    </div>
</div>