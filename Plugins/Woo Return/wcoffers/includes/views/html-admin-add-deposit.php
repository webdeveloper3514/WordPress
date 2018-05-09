<h3>
    <?php
    _e('Deposit Settings', 'wc-offers');
    ?>
</h3>
<div id="poststuff">
    <div class="postbox">
        <h2 class="hndle"><span><?php _e('Deposit Settings', 'wc-offers'); ?></span></h2>
        <div class="inside">
            <table class="deposit-table">
                <tbody>
                    <tr>
                        <td><?php _e('Minimum Deposit Amount', 'wc-offers'); ?></td>
                        <td>
                            <?php $wcoffer_deposit_min_amount = get_option('wcoffer_deposit_min_amount', 0); ?>
                            <input type="number" class="textNumberOnly" name="wcoffer_deposit_min_amount" id="wcoffer_deposit_min_amount" value="<?php echo $wcoffer_deposit_min_amount; ?>" placeholder="0">
                        </td>
                    </tr>        
                    <tr>
                        <td><?php _e('Maximum Deposit Amount', 'wc-offers'); ?></td>
                        <td>
                            <?php $wcoffer_deposit_max_amount = get_option('wcoffer_deposit_max_amount', 0); ?>
                            <input type="number" class="textNumberOnly" name="wcoffer_deposit_max_amount" id="wcoffer_deposit_max_amount" value="<?php echo $wcoffer_deposit_max_amount; ?>" placeholder="Maximum Amount">
                        </td>
                    </tr>        
                    <tr>
                        <td><?php _e('Deposit Payment Method', 'wc-offers'); ?></td>
                        <td>
                            <?php
                            $wcoffers_deposit_payment_method = get_option('wcoffers_deposit_payment_method', array());
                            ?>
                            <select multiple="multiple" name="wcoffers_deposit_payment_method[]" class="wcoffers_cashback_products">
                                <?php
                                $wc_payment = new WCOCommonFunctions();
                                if ($payment_methods = $wc_payment->wcoffers_get_wc_gateway()) {
                                    foreach ($payment_methods as $payment_method_name => $payment_method_desc) {
                                        $selected = '';
                                        if (in_array($payment_method_name, $wcoffers_deposit_payment_method)) {
                                            $selected = 'selected=selected';
                                        }
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $payment_method_name; ?>"><?php echo $payment_method_desc; ?></option>
                                        <?php
                                    }
                                }
                                ?>                    
                            </select>
                        </td>
                    </tr>  
                </tbody>
            </table>
        </div>
    </div>
</div>

