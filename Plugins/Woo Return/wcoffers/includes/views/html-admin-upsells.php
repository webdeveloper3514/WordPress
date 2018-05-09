<?php
if (!defined('ABSPATH'))
    die();
global $wpdb;
$table_name = $wpdb->prefix . 'wc_upsells';
$wcoffersUpsellOffers = new wcoffersUpsellOffers();
?>

<?php
global $wpdb;
//Create or Update upsell
if (!empty($_POST['upsell_name']) || !empty($_POST['upsell_targets']) || !empty($_POST['upsell_offers'])) {

    if (empty($_POST['upsell_name'])) {
        $_POST['upsell_name'] = __('Untitled Upsell', 'wc-offers');
    }

    $upsell_id = 0;
    $upsell_offers_count = count(isset($_POST['upsell_offers']) ? $_POST['upsell_offers'] : array());

    $upsell_name = htmlspecialchars($_POST['upsell_name']);
    $upsell_mode = $_POST['upsell_mode'];
    $upsell_display = isset($_POST['upsell_display']) ? $_POST['upsell_display'] : 'after_checkout';
    $upsell_targets = $_POST['upsell_targets'];
    $upsell_targets_qty = $_POST['upsell_targets_qty'];
    $upsell_offers_skip = (!empty($_POST['upsell_offers_skip']) ? $_POST['upsell_offers_skip'] : 0 );
    $upsell_offers = isset($_POST['upsell_offers']) ? $_POST['upsell_offers'] : array();
    if (is_array($upsell_targets)) {
        $upsell_targets = implode(',', $upsell_targets);
    }
    if ($upsell_offers) {
        unset($upsell_offers['%N%']);
        foreach ($upsell_offers as $k => &$v) {
            if (isset($v['product_id']) && is_array($v['product_id'])) {
                $v['product_id'] = implode(',', $v['product_id']);
            }
        }
    }
    $upsell_offers = maybe_serialize($upsell_offers);


    if (!empty($_POST['upsell_update'])) {
        $_POST['upsell_update'] = intval($_POST['upsell_update']);
        $result = $wpdb->update(
                $table_name, array(
            'upsell_name' => $upsell_name,
            'upsell_display' => $upsell_display,
            'upsell_mode' => $upsell_mode,
            'upsell_target' => $upsell_targets,
            'upsell_target_qty' => intval($upsell_targets_qty),
            'upsell_offers_skip' => $upsell_offers_skip,
            'upsell_offers' => $upsell_offers,
                ), array(
            'id' => $_POST['upsell_update']
                )
        );

        if (!empty($result)) {
            $upsell_id = intval($_POST['upsell_update']);
        }
    } elseif (!empty($_POST['upsell_create'])) {
        $result = $wpdb->insert(
                $table_name, array(
            'upsell_name' => $upsell_name,
            'upsell_display' => $upsell_display,
            'upsell_mode' => $upsell_mode,
            'upsell_target' => $upsell_targets,
            'upsell_target_qty' => intval($upsell_targets_qty),
            'upsell_offers_skip' => $upsell_offers_skip,
            'upsell_offers' => $upsell_offers,
            'upsell_active' => 1,
                )
        );

        if (!empty($result)) {
            $upsell_id = intval($wpdb->insert_id);
        }
    }

    //Display message or creation
    if ($result !== FALSE) {
        echo '<div class="updated notice wco-updated"><p>' . __('Upsell saved successfully.', 'wc-offers') . '</p></div>';
        echo $wcoffersUpsellOffers->wcoffers_force_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&section=savedupsells', 1500);
    } else {
        echo '<div class="updated error wco-updated"><p>' . __('Failed to save upsell.', 'wc-offers') . '</p></div>';
    }
}

//Delete upsell
if (!empty($_GET['delete'])) {
    $upsell_id = intval($_GET['delete']);
    $result = $wpdb->delete(
            $table_name, array(
        'id' => $upsell_id
            )
    );
    if ($result !== FALSE) {
        echo '<div class="updated notice wco-updated"><p>' . __('Upsell deleted successfully.', 'wc-offers') . '</p></div>';
        echo $wcoffersUpsellOffers->wcoffers_force_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&section=savedupsells', 1500);
    } else {
        echo '<div class="updated error wco-updated"><p>' . __('Failed to delete upsell.', 'wc-offers') . '</p></div>';
    }
}

// Change mode of upsells
if (!empty($_GET['switch'])) {
    $upsell_id = intval($_GET['switch']);
    $upsell_active = $wpdb->get_var("SELECT upsell_active FROM $table_name WHERE id = {$upsell_id} LIMIT 1;");
    if ($upsell_active == 1) {
        $result = $wpdb->update(
                $table_name, array(
            'upsell_active' => 0,
                ), array(
            'id' => $upsell_id
                )
        );
    } else {
        $result = $wpdb->update(
                $table_name, array(
            'upsell_active' => 1,
                ), array(
            'id' => $upsell_id
                )
        );
    }

    if ($result !== FALSE) {
        if (!empty($upsell_active)) {
            echo '<div class="updated notice wco-updated"><p>' . __('Upsell is now switched off.', 'wc-offers') . '</p></div>';
        } else {
            echo '<div class="updated notice wco-updated"><p>' . __('Upsell is now active.', 'wc-offers') . '</p></div>';
        }
        echo $wcoffersUpsellOffers->wcoffers_force_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&section=savedupsells', 1500);
    } else {
        echo '<div class="updated error wco-updated"><p>' . __('Failed to switch upsell.', 'wc-offers') . '</p></div>';
    }
}


$gb_admin_menu = array(
    'savedupsells' => __('Your Upsells', 'wc-offers'),
    'builder' => __('Create Checkout Upsells', 'wc-offers'),    
    'settings' => __('Settings', 'wc-offers'),
);
if (empty($_GET['section'])) {
    $_GET['section'] = 'savedupsells';
}
echo '<ul class="subsubsub upsell-sub">';
foreach ($gb_admin_menu as $k => $v) {
    echo '<li><a href="?page=' . $_GET['page'] . '&tab=upsell-offer&section=' . $k . '" class="' . ( ( $_GET['section'] == $k ) ? ' current' : '' ) . '">' . $v . '</a><span> | </span></li>';
}
echo '</ul>';

if ($_GET['section'] == 'builder'):

    $upsell = array();

    if (!empty($_GET['edit'])) {
        $upsell_id = intval($_GET['edit']);
        $upsell = $wpdb->get_row("SELECT * FROM $table_name WHERE id = {$upsell_id} LIMIT 1 ;", ARRAY_A);
        if (!empty($upsell['upsell_offers'])) {
            $upsell['upsell_offers'] = maybe_unserialize($upsell['upsell_offers']);
        }
    }

    // preload targets
    $upsell_target = '';
    $a = '';
    if (!empty($upsell['upsell_target'])) {
        $upsell_target = explode(',', $upsell['upsell_target']);
        $upsell_targets_json = array();

        // get target product names
        foreach ($upsell_target as $ft) {
            if (!empty($ft)) {
                $product = wc_get_product($ft);

                if (!empty($product)) {

                    $upsell_targets_json[] = array(
                        'id' => $ft,
                        'text' => '#' . $ft . ' &ndash; ' . $product->get_title(),
                    );
                }
            }
        }

        $upsell_targets_json = json_encode($upsell_targets_json);
    }
    ?>

    <div>

        <?php
        if (!empty($_GET['edit'])):

            echo '<h1 class="wp-heading-inline">Edit your Checkout Upsells</h1>';

        else:

            echo '<h1 class="wp-heading-inline">Create new Checkout Upsells</h1>';

        endif;
        ?>
        <div id="poststuff">            
            <?php
            if (!empty($_GET['edit'])) {
                echo '<input type="hidden" name="upsell_update" value="' . intval($_GET['edit']) . '" />';
            } else {
                echo '<input type="hidden" name="upsell_create" value="1" />';
            }
            ?>
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('Upsells Settings', 'wc-offers'); ?></span></h2>
                <div class="inside">
                    <div class="wco-div-upsell-settings">
                        <table>
                            <tbody>
                                <tr>
                                    <td><?php _e('Upsell name', 'wc-offers'); ?>
                                        <span class="wco-help-tip"></span>
                                    </td>
                                    <td>
                                        <input type="text" name="upsell_name" class="regular-text" value="<?php if (!empty($upsell['upsell_name'])) echo $upsell['upsell_name']; ?>" placeholder="" />
                                        <p class="wco-field-desc">
                                            <?php _e('Identify your upsells using this name', 'wc-offers'); ?>
                                        </p>
                                    </td>
                                </tr>                                    
                                <tr>
                                    <td><?php _e('Display Upsell', 'wc-offers'); ?></td>
                                    <td>
                                        <select name="upsell_display">
                                            <option value="before_checkout" <?php
                                            if (!empty($upsell['upsell_display']) && $upsell['upsell_display'] == 'before_checkout') {
                                                echo 'selected="selected"';
                                            }
                                            ?>><?php _e('Before Checkout', 'wc-offers'); ?></option>
                                            <option value="after_checkout" <?php
                                            if (!empty($upsell['upsell_display']) && $upsell['upsell_display'] == 'after_checkout') {
                                                echo 'selected="selected"';
                                            }
                                            ?>><?php _e('After Checkout', 'wc-offers'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php _e('Upsell is applied', 'wc-offers'); ?></td>
                                    <td>
                                        <select name="upsell_mode" class="upsell_mode">
                                            <option value="product" <?php
                                            if (!empty($upsell['upsell_mode']) && $upsell['upsell_mode'] == 'product') {
                                                echo 'selected="selected"';
                                            }
                                            ?>><?php _e('One or several products', 'wc-offers'); ?></option>
                                            <option value="global" <?php
                                            if (!empty($upsell['upsell_mode']) && $upsell['upsell_mode'] == 'global') {
                                                echo 'selected="selected"';
                                            }
                                            ?>><?php _e('All existing products', 'wc-offers'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="upsell_mode_product">
                                    <td>
                                        <?php _e('Target Products', 'wc-offers'); ?>
                                        <span class="wco-help-tip"></span>
                                    </td>
                                    <td style="width: 27em;float: left;">
                                        <select name="upsell_targets[]" class="wco-div-upsell-target wc-product-search regular-text" multiple="multiple" data-selected="<?php if (!empty($upsell_targets_json)) echo htmlspecialchars($upsell_targets_json); ?>" data-placeholder="<?php esc_attr_e('search for a product&hellip;', 'wc-offers'); ?>" data-action="woocommerce_json_search_products_and_variations"></select>
                                        <p class="wco-field-desc">
                                            <?php _e('select product(s), which you want to apply this upsells to', 'wc-offers'); ?>
                                        </p>
                                    </td>
                                </tr>                                    
                                <tr>
                                    <td>
                                        <?php _e('Target Quantity', 'wc-offers'); ?>
                                        <span class="wco-help-tip"></span>
                                    </td>
                                    <td>
                                        <input type="text" name="upsell_targets_qty" class="textNumberOnly regular-text" value="<?php if (!empty($upsell['upsell_target_qty'])) echo $upsell['upsell_target_qty']; ?>" placeholder="any" />
                                        <p class="wco-field-desc">
                                            <?php _e('Apply this upsell only when the certain quantity of target product is purchased', 'wc-offers'); ?>
                                        </p>
                                    </td>
                                </tr>                                    
                                <tr>
                                    <td>
                                        <?php _e('Skip offers', 'wc-offers'); ?>
                                        <span class="wco-help-tip"></span>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="upsell_offers_skip" value="1" <?php if (!empty($upsell['upsell_offers_skip'])) checked($upsell['upsell_offers_skip'], '1'); ?> />
                                        <p class="wco-field-desc">
                                            <?php _e('Skip this offer if product already in cart.', 'wc-offers'); ?>
                                        </p>
                                    </td>
                                </tr>                                    
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('Offers in upsell', 'wc-offers'); ?></span></h2>
                <div class="inside">
                    <div>
                        <div class="wco-div-offers-wrap">
                            <div class="wco-div-offers-empty"><?php _e('Oops, no offers here...', 'wc-offers'); ?></div>
                        </div>
                        <div class="wco-div-upsell-controls">
                            <div class="button wco-div-add-offer"><?php _e('+ add new offer', 'wc-offers'); ?></div>
                        </div>
                    </div>
                </div>
            </div>            
            <div class="wco-div-offer-template-wrap" style="display: none;">
                <div class="wco-div-offer wco-div-offer-template" data-id="%N%">
                    <div class="wco-div-offer-inside">
                        <div class="wco-div-offer-left">                            
                            <div class="wco-div-offer-controls">
                                <div class="wco-div-offer-delete button"><?php _e('Delete', 'wc-offers'); ?></div>
                            </div>
                        </div>
                        <div class="wco-div-offer-right">
                            <table>
                                <tbody>
                                    <tr>
                                        <td><?php _e('Product in Offer', 'wp-offers'); ?></td>
                                        <td style="width: 27em;float: left;">
                                            <select name="upsell_offers[%N%][product_id][]" class="wco-div-product-search regular-text" multiple="multiple" data-placeholder="<?php esc_attr_e('search for a product&hellip;', 'wc-offers'); ?>" data-action="woocommerce_json_search_products_and_variations"></select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Offer Specific Price', 'wc-offers') ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <input type="text" name="upsell_offers[%N%][product_price]" data-name="product_price" class="wco-div-product-price small-text" placeholder="100%" value="100%" />
                                            <p class="wco-field-desc">
                                                <?php _e('if you want to offer this product at a special price, specify percent ', 'wc-offers'); ?><b>50%</b> <?php _e('or numeric value ', 'wc-offers'); ?><b>59.99</b>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr style="display: none;">
                                        <td><?php _e('Offer Display Method', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <select name="upsell_offers[%N%][offer_method]" data-name="offer_method" class="wco-div-offer-method regular-text" data-conditional=".wco-div-offer-custom-page">
                                                <option value="default"><?php _e('Default Page', 'wc-offers'); ?></option>
                                                <option value="custom_page"><?php _e('Custom Page', 'wc-offers'); ?></option>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php _e('specify if you want to display offer on your own custom page or the default one', 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr class="wco-div-offer-custom-page" style="display: none;">
                                        <td><?php _e('Offer Custom Page', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <input type="text" name="upsell_offers[%N%][offer_custom_page]" data-name="offer_custom_page" class="regular-text" placeholder="" value="" />
                                            <p class="wco-field-desc">
                                                <?php _e('specify WordPress post/page ID or any URL link', 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr>
                                        <td><?php _e('Action after Buy Now', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <select name="upsell_offers[%N%][offer_accepted_action]" data-name="offer_accepted_action" class="wco-div-offer-action wco-div-offer-accepted regular-text" data-conditional=".wco-div-offer-accepted-custom-page">
                                                <optgroup label="Offers" class="wco-div-offer-action-offers"></optgroup>
                                                <optgroup label="Other" class="wco-div-offer-action-others">
                                                    <option value="show_thank_you"><?php _e('show Order received page', 'wc-offers'); ?></option>
                                                    <option value="show_custom"><?php _e('show custom page', 'wc-offers'); ?></option>
                                                </optgroup>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php _e('select next action after the customer <b>accepted</b> this offer', 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr class="wco-div-offer-accepted-custom-page" style="display: none;">
                                        <td><?php _e('Page after Buy Now', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <input type="text" name="upsell_offers[%N%][offer_accepted_custom_page]" data-name="offer_accepted_custom_page" class="regular-text" placeholder="" value="" />
                                            <p class="wco-field-desc">
                                                <?php _e('specify WordPress post/page ID or any URL link', 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr>
                                        <td><?php _e('Action after No, thanks', 'wc-offers'); ?><span class="wco-help-tip"></span></td>
                                        <td>
                                            <select name="upsell_offers[%N%][offer_rejected_action]" data-name="offer_rejected_action" class="wco-div-offer-action wco-div-offer-rejected regular-text" data-conditional=".wco-div-offer-rejected-custom-page">
                                                <optgroup label="Offers" class="wco-div-offer-action-offers">
                                                </optgroup>
                                                <optgroup label="Other" class="wco-div-offer-action-others">
                                                    <option value="show_thank_you" selected="selected"><?php _e('show Order received page', 'wc-offers'); ?></option>
                                                    <option value="show_custom"><?php _e('show custom page', 'wc-offers'); ?></option>
                                                </optgroup>
                                            </select>
                                            <p class="wco-field-desc">
                                                <?php _e('select next action after the customer <b>rejected</b> this offer', 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr class="wco-div-offer-rejected-custom-page" style="display: none;">
                                        <td>
                                            <?php _e('Page after No, thanks', 'wc-offers'); ?>
                                            <span class="wco-help-tip"></span>
                                        </td>
                                        <td>
                                            <input type="text" name="upsell_offers[%N%][offer_rejected_custom_page]" data-name="offer_rejected_custom_page" class="regular-text" placeholder="" value="" />
                                            <p class="wco-field-desc">
                                                <?php _e('specify WordPress post/page ID or any URL link', 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                    <tr style="display: none;">
                                        <td>
                                            <?php _e('Cancel target order?', 'wc-offers'); ?>
                                            <span class="wco-help-tip"></span>
                                        </td>
                                        <td>
                                            <input type="checkbox" name="upsell_offers[%N%][cancel_target]" data-name="cancel_target" value="1" />
                                            <p class="wco-field-desc">
                                                <?php _e("Tick this, if you want to cancel upsell's initial order and void the payment", 'wc-offers'); ?>
                                            </p>
                                        </td>
                                    </tr>                                    
                                </tbody>
                            </table>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div class="button button-primary wco-div-upsell-save"><?php _e('Save changes', 'wc-offers'); ?></div>
        </div>
    </div>

    <script type="text/javascript">
        var wcoffers_offers = {};
        var wcoffers_offers_count = 1;
        var wcoffers_currency_symbol = '<?php echo get_woocommerce_currency_symbol(); ?>';
        var wcoffers_targets = [];

    <?php
// Get all saved offers
    if (!empty($upsell['upsell_offers'])) {
// get offer product names
        foreach ($upsell['upsell_offers'] as &$fo) {
            if (!empty($fo['product_id'])) {
                $fo['product_id'] = explode(',', $fo['product_id']);
                foreach ($fo['product_id'] as $fo_pid) {
                    $product = wc_get_product($fo_pid);
                    if (!empty($product)) {
                        $fo['product_data'][] = array(
                            'id' => $fo_pid,
                            'text' => '#' . $fo_pid . ' &ndash; ' . $product->get_title(),
                        );
                    }
                }
            }
        }
        echo PHP_EOL . 'wcoffers_offers = ' . json_encode($upsell['upsell_offers']) . ';';
        echo PHP_EOL . 'wcoffers_offers_count = ' . ( count($upsell['upsell_offers']) + 1 ) . ';' . PHP_EOL;
    }
    ?>

        jQuery(document).ready(function () {

            var $offers_wrap = jQuery('.wco-div-offers-wrap');
            
            var $upsell_target = jQuery('select.wco-div-upsell-target');
            if ($upsell_target.length > 0) {
                jQuery($upsell_target.data('selected')).each(function (index, product) {
                    $upsell_target.append('<option value="' + product['id'] + '" selected="selected">' + product['text'] + '</option>');
                });
            }

            // On click add new offer
            jQuery('.wco-div-add-offer').on('click', function () {
                var $offer,
                        $offer = wcoffers_offers_add_offer_block(wcoffers_offers_count),
                        new_offer = {};
                $offer.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        new_offer[ name ] = jQuery(this).val();
                    }
                });
                wcoffers_offers[ wcoffers_offers_count ] = new_offer;                
                wcoffers_offers_actions_repopulate();
                wcoffers_offers_count++;

                // if the first offer, hide empty notice
                if (wcoffers_offers_count > 0) {
                    $offers_wrap.find('.wco-div-offers-empty').slideUp(250);
                }
            });

            //Update object value
            jQuery(document.body).on('change', 'input, select', function () {
                var $this = jQuery(this),
                        $type = $this.attr('type'),
                        $offer = $this.closest('.wco-div-offer'),
                        offer_index = $offer.data('id'),
                        name = $this.data('name');

                if (typeof name != 'undefined') {
                    if ($type == 'checkbox') {
                        if ($this.is(':checked')) {
                            wcoffers_offers[ offer_index ][ name ] = $this.val();
                        }
                    } else {
                        wcoffers_offers[ offer_index ][ name ] = $this.val();
                    }
                }
                wcoffers_offers_actions_repopulate();
            });

            // Delete offer
            jQuery(document.body).on('click', '.wco-div-offer-delete', function () {
                if (confirm('Are you sure that you want to remove this offer?') === false) {
                    return false;
                }
                var $this = jQuery(this),
                        $offer = $this.closest('.wco-div-offer');

                // Delete offer from offers object
                if (typeof $offer.data('id') != 'undefined' &&
                        typeof wcoffers_offers[ $offer.data('id') ]) {

                    delete wcoffers_offers[ $offer.data('id') ];

                    // Delete offer block from the form
                    $offer.slideUp(250, function () {
                        jQuery(this).remove();
                        var offers_rebuild = {};

                        jQuery('.wco-div-offers-wrap').find('.wco-div-offer').each(function (index) {

                            var $this = jQuery(this),
                                    new_offer = {},
                                    display_index = parseInt(index) + 1;

                            // replace old offer id with new offer id
                            $this.data('id', display_index);
                            $this.attr('data-id', display_index);                            
                            $this.find('input, select').each(function () {
                                var name = jQuery(this).data('name');
                                if (typeof name != 'undefined') {
                                    new_offer[ name ] = jQuery(this).val();
                                    jQuery(this).attr('name', 'upsell_offers[' + display_index + '][' + name + ']');
                                }
                            });
                            offers_rebuild[ display_index ] = new_offer;
                        });
                        wcoffers_offers = offers_rebuild;
                        wcoffers_offers_actions_repopulate();
                        wcoffers_offers_count--;

                        // if the last offer, show empty notice

                        if (wcoffers_offers_count < 2) {
                            $offers_wrap.find('.wco-div-offers-empty').slideDown(250);
                        }
                    });
                }
            });

            jQuery(document.body).on('change', '.wco-div-offer-method, .wco-div-offer-action', function () {
                var $this = jQuery(this),
                        $offer = $this.closest('.wco-div-offer');

                if (typeof $this.data('conditional') != 'undefined' && $this.data('conditional').length > 0) {
                    if ($this.val() == 'custom_page' || $this.val() == 'show_custom') {
                        $offer.find($this.data('conditional')).show();
                    } else {
                        $offer.find($this.data('conditional')).hide();
                    }
                }
            });

            //Upsells page
            jQuery('.wco-div-upsell-save').on('click', function () {
                jQuery('form#mainform').submit();
            });

            // preload offers and create blocks
            if (wcoffers_offers_count > 1) {

                // create block elements
                jQuery.each(wcoffers_offers, function (index, offer_options) {
                    var $offer = wcoffers_offers_add_offer_block(index);
                    // if preloaded offers exist, hide empty notice
                    if (wcoffers_offers_count > 0) {
                        $offers_wrap.find('.wco-div-offers-empty').slideUp(250);
                    }
                });                
                wcoffers_offers_actions_repopulate();

                // set options
                jQuery('.wco-div-offers-wrap').find('.wco-div-offer').each(function (index) {

                    var $offer = jQuery(this),
                            display_index = parseInt(index) + 1,
                            offer_options = wcoffers_offers[ display_index ];

                    // set the preloaded options for offer's inputs
                    $offer.find('input, select').each(function () {

                        var $field = jQuery(this),
                                $type = $field.attr('type'),
                                name = $field.data('name');

                        if (typeof name != 'undefined' && typeof offer_options[ name ] != 'undefined') {
                            if ($type == 'checkbox') {
                                if (offer_options[ name ].length > 0) {
                                    $field.prop('checked', true).trigger('change');
                                }
                            } else {
                                if (name == 'product_id') {
                                    $field.val('').trigger('change');
                                } else {
                                    $field.val(offer_options[ name ]).trigger('change');
                                }
                            }
                        }
                    });
                });
            }
        });

        // add new offer block to form and return jQuery object
        function wcoffers_offers_add_offer_block(wcoffers_offers_count) {
            var $offer = jQuery('.wco-div-offer-template-wrap').html();
            $offer = $offer.replace(/%N%/g, wcoffers_offers_count);
            $offer = jQuery($offer).removeClass('wco-div-offer-template');
            var $product_search = $offer.find('.wco-div-product-search');
            $product_search.addClass('wc-product-search');

            // add new offer element to the list
            $offer.hide().appendTo('.wco-div-offers-wrap').slideDown(250, function () {
                jQuery(document.body).trigger('wc-enhanced-select-init');
                // set the preloaded value
                if (typeof wcoffers_offers[ wcoffers_offers_count ] != 'undefined') {
                    if (typeof wcoffers_offers[ wcoffers_offers_count ]['product_data'] != 'undefined' &&
                            wcoffers_offers[ wcoffers_offers_count ]['product_data'].length > 0) {
                        jQuery(wcoffers_offers[ wcoffers_offers_count ]['product_data']).each(function (index, product) {
                            $product_search.append('<option value="' + product['id'] + '" selected="selected">' + product['text'] + '</option>');
                        });

                    }
                }
            });
            return $offer;
        }
        
        function wcoffers_offers_actions_repopulate() {
            jQuery('.wco-div-offers-wrap').find('.wco-div-offer').each(function () { // .not('.wco-div-offer-template')

                var $this = jQuery(this);
                if (typeof $this.data('id') != 'undefined') {
                    // save current selected value
                    var selected_value_accepted = $this.find('select.wco-div-offer-accepted').val();
                    var selected_value_rejected = $this.find('select.wco-div-offer-rejected').val();

                    var display_index = 0;
                    var option_html = '';

                    $this.find('optgroup.wco-div-offer-action-offers').empty();

                    jQuery.each(wcoffers_offers, function (index, offer) {

                        option_html = '';
                        display_index = parseInt(index);

                        if ($this.data('id') < display_index) {
                            option_html = option_html + '<option value="show_offer_' + display_index + '">show #' + display_index; // + ' &rarr; ';

                            // display offer product price

                            if (typeof wcoffers_offers[ index ]['product_price'] != 'undefined') {
                                if (wcoffers_offers[ index ]['product_price'].length > 0) {
                                    option_html = option_html + ' @ ';

                                    if (wcoffers_offers[ index ]['product_price'].indexOf('%') == -1) {
                                        option_html = option_html + wcoffers_currency_symbol;
                                    }

                                    option_html = option_html + wcoffers_offers[ index ]['product_price'];
                                }
                            }

                            option_html = option_html + '</option>';

                            $this.find('optgroup.wco-div-offer-action-offers').append(option_html);
                        }
                    });

                    // set back current selected value
                    $this.find('select.wco-div-offer-accepted').val(selected_value_accepted);
                    $this.find('select.wco-div-offer-rejected').val(selected_value_rejected);
                }
            });
        }
    </script>
<?php elseif ($_GET['section'] == 'savedupsells') :

    $result = FALSE;
    $upsells_list = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC ;", ARRAY_A);
    ?>
    <h2><?php _e('List of saved upsells', 'wc-offers'); ?></h2>
    <div class="">
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th class="manage-column"><?php _e('Upsell Name', 'wc-offers'); ?></th>
                    <th class="manage-column"><?php _e('Upsell Targets', 'wc-offers'); ?></th>
                    <th class="manage-column"><?php _e('Upsell Description', 'wc-offers'); ?></th>                    
                </tr>
            </thead>
            <tbody id="the-list">
                <?php
                if (!empty($upsells_list)) {
                    foreach ($upsells_list as $single_upsell) {
                        $upsell_target = '';
                        $upsell_desc = '';
                        $upsell_class = '';
                        if (empty($upsell['upsell_mode'])) {
                            $upsell['upsell_mode'] = 'product';
                        }
                        if (!empty($single_upsell['upsell_mode'])) {
                            $upsell_target .= '<div>';
                            if ($single_upsell['upsell_mode'] == 'product') {
                                $upsell_target .= 'Upsell Target Products:';
                            } elseif ($single_upsell['upsell_mode'] == 'global') {
                                $upsell_target .= '<strong>Upsell is applied globally</strong> to all existing products. Single product upsell override this one.';
                            }
                            $upsell_target .= '</div>';
                        }

                        // upsell_target
                        if (!empty($single_upsell['upsell_target'])) {
                            if ($single_upsell['upsell_mode'] == 'product') {
                                $single_upsell['upsell_target'] = explode(',', $single_upsell['upsell_target']);
                                foreach ($single_upsell['upsell_target'] as $ft) {
                                    $ft = wc_get_product($ft);
                                    if (!empty($ft)) {
                                        $upsell_target .= '<div><a href="' . get_edit_post_link($ft->get_id()) . '">#' . $ft->get_id() . ' &ndash; ' . $ft->get_title() . '</a></div>';
                                    }
                                }
                            } elseif ($single_upsell['upsell_mode'] == 'category') {
                                // categories listed
                            } elseif ($single_upsell['upsell_mode'] == 'global') {
                                // nothing added here
                            }
                        }

                        // upsell_target_qty
                        if (!empty($single_upsell['upsell_target_qty'])) {
                            $upsell_target .= '<div>Quantity: ' . $single_upsell['upsell_target_qty'] . '</div>';
                        } else {
                            $upsell_target .= '<div>Quantity: any</div>';
                        }

                        // upsell_offers
                        if (!empty($single_upsell['upsell_offers'])) {
                            $single_upsell['upsell_offers'] = maybe_unserialize($single_upsell['upsell_offers']);

                            foreach ($single_upsell['upsell_offers'] as $offer_index => $fo) {
                                $fo['product'] = null;
                                $fo['product_links'] = '';

                                if (!empty($fo['product_id'])) {
                                    $fo['product_id'] = explode(',', $fo['product_id']);

                                    if (!empty($fo['product_price'])) {
                                        if (mb_strpos($fo['product_price'], '%') === FALSE) {
                                            $fo['product_price'] = wc_price($fo['product_price']);
                                        }

                                        $fo['product_price'] = ' @ ' . $fo['product_price'];
                                    }

                                    $upsell_desc .= '<div class="wco-div-saved-upsell-desc">';

                                    // IF one product per offer

                                    if (count($fo['product_id']) == 1) {
                                        $fo['product_id'] = $fo['product_id'][0];

                                        $fo['product'] = wc_get_product($fo['product_id']);

                                        if (!empty($fo['product'])) {
                                            $upsell_desc .= PHP_EOL .
                                                    '<div><b>Offer #' . $offer_index . '</b>' .
                                                    // product availability summary
                                                    '&nbsp;( ' . ( $fo['product']->has_enough_stock(1) ? '<span class="wco-div-green">in stock</span>' : '<span class="wco-div-red"><b>out of stock</b></span>' ) . ' /' .
                                                    '&nbsp;' . ( $fo['product']->is_visible() ? '<span class="wco-div-green">visible</span>' : '<span>hidden</span>' ) . ' )' .
                                                    // display offer target + price
                                                    '<br /><a href="' . get_edit_post_link($fo['product']->get_id()) . '">' .
                                                    $fo['product']->get_title() .
                                                    // ' #' . $fo['product']->id . ' ' .
                                                    '</a>' .
                                                    $fo['product_price'] .
                                                    '</div>';
                                        }
                                    }

                                    // ELSE IF several products per offer
                                    elseif (count($fo['product_id']) > 1) {
                                        foreach ($fo['product_id'] as $fo_pid) {
                                            $fo['product'] = wc_get_product($fo_pid);

                                            $fo['product_links'] .= '<a href="' . get_edit_post_link($fo['product']->get_id()) . '">' . $fo['product']->get_title() . '</a>';

                                            if ($fo_pid != end($fo['product_id'])) {
                                                $fo['product_links'] .= ', ';
                                            }
                                        }

                                        if (!empty($fo['product_links'])) {
                                            $upsell_desc .= PHP_EOL .
                                                    '<div><b>Offer #' . $offer_index . '</b>' .
                                                    // display offer display method
                                                    '&nbsp;( ' . ( $fo['offer_method'] == 'custom_page' ? '<a href="' . $fo['offer_custom_page'] . '" target="_blank">custom</a>' : 'default' ) . ' )' .
                                                    // display offer target + price
                                                    '<br />' .
                                                    $fo['product_links'] .
                                                    $fo['product_price'] .
                                                    '</div>';
                                        }
                                    }

                                    $upsell_desc .= '<div class="wco-div-saved-upsell-desc-actions">';

                                    if (!empty($fo['offer_accepted_action'])) {
                                        $upsell_desc .= 'on accept: "' . $fo['offer_accepted_action'] . '" & ';
                                    }

                                    if (!empty($fo['offer_rejected_action'])) {
                                        $upsell_desc .= 'on reject: "' . $fo['offer_rejected_action'] . '"';
                                    }

                                    $upsell_desc .= '</div></div>';
                                }

                                $fo['product'] = null;
                            }
                        }

                        if (empty($single_upsell['upsell_active'])) {
                            $upsell_class .= 'wco-div-saved-upsell-disabled';
                        } else {
                            $upsell_class .= 'wco-div-saved-upsell-active';
                        }

                        if ($single_upsell['upsell_mode'] == 'global') {
                            $upsell_class .= ' wco-div-saved-upsell-global';
                        }
                        ?>
                        <tr class="<?php echo $upsell_class; ?>">
                            <td class="title column-title has-row-actions column-primary page-title">
                                <strong>
                                    <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&section=builder&edit=<?php echo $single_upsell['id']; ?>"><?php echo $single_upsell['upsell_name']; ?></a>
                                </strong>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&section=builder&edit=<?php echo $single_upsell['id']; ?>"><?php _e('Edit', 'wc-offers'); ?></a> | 
                                    </span>
                                    <span class="disable">
                                        <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&section=savedupsells&switch=<?php echo $single_upsell['id']; ?>"><?php
                                            if (!empty($single_upsell['upsell_active']))
                                                _e('Disable', 'wc-offers');
                                            else
                                                echo '<b>' . __('Enable', 'wc-offers') . '</b>';
                                            ?>
                                        </a> | 
                                    </span>
                                    <span class="trash">
                                        <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $_GET['tab']; ?>&section=savedupsells&delete=<?php echo $single_upsell['id']; ?>" onclick="return confirm('Are you sure, you want to delete this upsell?');"><?php _e('Delete', 'wc-offers'); ?></a>
                                    </span>
                                </div>
                            </td>
                            <td><?php echo $upsell_target; ?></td>
                            <td><?php echo $upsell_desc; ?></td>                            
                        </tr>
                        <?php
                    }
                }
                else {
                    echo '<tr><td colspan="3" style="text-align: center;">' . __('Oops, no upsells here...', 'wc-offers') . '</td></tr>';
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="manage-column"><?php _e('Upsell Name', 'wc-offers'); ?></th>
                    <th class="manage-column"><?php _e('Upsell Targets', 'wc-offers'); ?></th>
                    <th class="manage-column"><?php _e('Upsell Description', 'wc-offers'); ?></th>                    
                </tr>
            </tfoot>
        </table>
    </div>
<?php elseif ($_GET['section'] == 'settings') :
        
    if(isset($_POST['submit'])){
        $options = $_POST['wco_upsell_options'];        
        update_option('wco_upsell_options', $options);
        echo '<div class="updated fade wco-updated"><p>' . __('Settings saved', 'wc-offers') . '</p></div>';        
    }
    $options = get_option('wco_upsell_options', array());
    if (empty($options['default_offer_page_color'])) {
        $options['default_offer_page_color'] = '#3BB13B';
    }
    if (empty($options['default_offer_page_button_yes_text'])) {
        $options['default_offer_page_button_yes_text'] = 'Buy Now';
    }
    if (empty($options['default_offer_page_button_no_text'])) {
        $options['default_offer_page_button_no_text'] = 'No, Thanks';
    }    
    // check if default offer page exists
     
    $pages_available = get_posts(array(
        'posts_per_page' => -1,
        'post_type' => 'any',
        'post_status' => 'publish',
        's' => '[wc_upsell_default_page]',
        'orderby' => 'ID',
        'order' => 'ASC',
    ));

    // prepare data: pages
    $pages_available_ids = array();
    $pages_available_html = '';

    foreach ($pages_available as $page) {
        if (!empty($options['default_offer_page']) && $options['default_offer_page'] == $page->ID) {
            $pages_available_html .= '<option value="' . $page->ID . '" selected="selected">' . $page->post_title . ' (#' . $page->ID . ', ' . $page->post_type . ')</option>' . PHP_EOL;
        } else {
            $pages_available_html .= '<option value="' . $page->ID . '">' . $page->post_title . ' (#' . $page->ID . ', ' . $page->post_type . ')</option>' . PHP_EOL;
        }
        $pages_available_ids[] = $page->ID;
    }

    // prepare data: order statuses
    $order_statuses = array();
    $order_statuses_html = '';

    $order_statuses = wc_get_order_statuses();

    // default order_status
    if (empty($options['cancel_order_status'])) {
        $options['cancel_order_status'] = 'wc-cancelled';
    }

    foreach ($order_statuses as $k => $status) {
        if (!empty($options['cancel_order_status']) && $options['cancel_order_status'] == $k) {
            $order_statuses_html .= '<option value="' . $k . '" selected="selected">' . $k . '</option>';
        } else {
            $order_statuses_html .= '<option value="' . $k . '">' . $k . '</option>';
        }
    }    

    // create default offer page
    if (empty($pages_available)) {
        $post = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_content' => '[wc_upsell_default_page]',
            'post_name' => 'wco-special-offer',
            'post_status' => 'publish',
            'post_title' => 'WCO Special Offer',
            'post_type' => 'page',
        );

        $post = wp_insert_post($post);

        if (!empty($post)) {
            $options['default_offer_page'] = $post;
            update_option('wco_upsell_options', $options);
            echo '<div class="updated fade wco-updated"><p><b>Default offer page created!</b> You can find it here &quot;<a href="' . get_permalink($post) . '" target="_blank">' . get_the_title($post) . '</a>&quot;.</p></div>';
            echo $wcoffersUpsellOffers->wcoffers_force_redirect('admin.php?page=' . $_GET['page'] . '&tab=' . $_GET['tab'] . '&section=settings', 1500);
        }
    }
    ?>
    <div class="wco-admin-page wco-div-page-settings">
        <h1 class="wp-heading-inline"><?php _e('Settings', 'wc-offers'); ?></h1>
        <div>            
            <?php settings_fields('wco_upsell_options'); ?>                    
            <table class="form-table">
                <tr>
                    <th><?php _e('Default offer page:', 'wc-offers'); ?></th>
                    <td style="width: 26em;float: left;">
                        <select name="wco_upsell_options[default_offer_page]" class="wc-enhanced-select-nostd" data-placeholder="Select a page...">
                                <option value=""></option>
                                <?php
                                echo $pages_available_html;
                                ?>
                        </select>                        
                    </td>
                </tr>                
                <tr>
                    <th><?php _e('Color for price and button', 'wc-offers'); ?></th>
                    <td>
                        <div>
                            <input type="text" name="wco_upsell_options[default_offer_page_color]" class="wco-div-color-picker" value="<?php if (!empty($options['default_offer_page_color'])) echo $options['default_offer_page_color']; ?>" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Buy Now text for default page', 'wc-offers'); ?></th>
                    <td>
                        <div>
                            <input type="text" name="wco_upsell_options[default_offer_page_button_yes_text]" class="regular-text" value="<?php if (!empty($options['default_offer_page_button_yes_text'])) echo $options['default_offer_page_button_yes_text']; ?>" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('No button text for default page', 'wc-offers'); ?></th>
                    <td>
                        <div>
                            <input type="text" name="wco_upsell_options[default_offer_page_button_no_text]" class="regular-text" value="<?php if (!empty($options['default_offer_page_button_no_text'])) echo $options['default_offer_page_button_no_text']; ?>" />
                        </div>
                    </td>
                </tr>                        
                <tr>
                    <th><?php _e('Cancelled order status:', 'wc-offers'); ?></th>
                    <td style="width: 26em;float: left;">
                        <select name="wco_upsell_options[cancel_order_status]" class="wc-enhanced-select-nostd" data-placeholder="Select order status">
                            <?php
                            echo $order_statuses_html;
                            ?>
                        </select>                        
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Disable stripping HTML from the product description for default page:', 'wc-offers'); ?></th>
                    <td>
                        <div>
                            <input type="checkbox" name="wco_upsell_options[disable_strip_html]" value="1" <?php if (!empty($options['disable_strip_html'])) checked($options['disable_strip_html'], '1'); ?> /> yes
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Hide item added messages:', 'wc-offers'); ?></th>
                    <td>
                        <div>
                            <input type="checkbox" name="wco_upsell_options[hide_added_msg]" value="1" <?php if (!empty($options['hide_added_msg'])) checked($options['hide_added_msg'], '1'); ?> /> yes
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Disable Upsells on gateways:', 'wc-offers'); ?></th>
                    <td>
                        <div>
                            <input type="checkbox" name="wco_upsell_options[disable_bacs]" value="1" <?php if (!empty($options['disable_bacs'])) checked($options['disable_bacs'], '1'); ?> /> BACS
                        </div>
                        <div>
                            <input type="checkbox" name="wco_upsell_options[disable_cp]" value="1" <?php if (!empty($options['disable_cp'])) checked($options['disable_cp'], '1'); ?> /> Check payments
                        </div>
                        <div>
                            <input type="checkbox" name="wco_upsell_options[disable_cod]" value="1" <?php if (!empty($options['disable_cod'])) checked($options['disable_cod'], '1'); ?> /> Cash on delivery
                        </div>
                        <div>
                            <input type="checkbox" name="wco_upsell_options[disable_paypal]" value="1" <?php if (!empty($options['disable_paypal'])) checked($options['disable_paypal'], '1'); ?> /> Paypal
                        </div>                                    
                    </td>
                </tr>
            </table>
            <div>
                <?php submit_button(); ?>
            </div>            
        </div>
    </div>        
    <?php
endif;
?>