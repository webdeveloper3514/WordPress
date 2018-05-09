<h2>
    <?php
    _e('Email Templates', 'wc-offers');
    ?>
</h2>
<div class="offer-email-templates">
    <div id="v-nav">
        <ul>
            <li class="first current"><a href="#" data-target="reg-email"><?php _e('Registration Email', 'wc-offers'); ?></a></li>
            <li><a href="#" data-target="second-purchase-email"><?php _e('Second Purchase Email', 'wc-offers'); ?></a></li>
            <li><a href="#" data-target="existing-customer-email"><?php _e('Existing Customer Email', 'wc-offers'); ?></a></li>
            <li><a href="#" data-target="birthday-email"><?php _e('Birthday Email', 'wc-offers'); ?></a></li>
            <li class="last"><a href="#" data-target="cashback-email"><?php _e('Cashback Email', 'wc-offers'); ?></a></li>
        </ul>
        <div id="reg-email" class="tab-content" style="display: block;">
            <h4><?php _e('Registration Email Template', 'wc-offers'); ?></h4>
            <table class="template-table">
                <tr>
                    <th>Subject</th>
                    <td>
                        <?php
                        $reg_email_subject = get_option('reg_email_subject', '');
                        ?>
                        <input type="text" value="<?php echo $reg_email_subject; ?>" name="reg_email_subject">
                    </td>
                </tr>
                <tr>
                    <th>Email Template</th>
                    <td>
                        <?php
                        $content = stripslashes(get_option('reg_email_template', ''));
                        $editor_id = 'reg_email_template';
                        $settings = array(
                            'editor_height' => '200px'
                        );
                        wp_editor($content, $editor_id, $settings);
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div id="second-purchase-email" class="tab-content">
            <h4><?php _e('Second Purchase Email Template', 'wc-offers'); ?></h4>
            <table class="template-table">
                <tr>
                    <th>Subject</th>
                    <td>
                        <?php
                        $freebies_email_subject = get_option('freebies_email_subject', '');
                        ?>
                        <input type="text" value="<?php echo $freebies_email_subject; ?>" name="freebies_email_subject">
                    </td>
                </tr>
                <tr>
                    <th>Email Template</th>
                    <td>
                        <?php
                        $content_freebies = stripslashes(get_option('freebies_email_template', ''));
                        $editor_id_f = 'freebies_email_template';
                        $settings_f = array(
                            'editor_height' => '200px'
                        );
                        wp_editor($content_freebies, $editor_id_f, $settings_f);
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div id="existing-customer-email" class="tab-content">
            <h4><?php _e('Existing Customer Email Template', 'wc-offers'); ?></h4>
            <table class="template-table">
                <tr>
                    <th>Subject</th>
                    <td>
                        <?php
                        $existing_customer_email_subject = get_option('existing_customer_email_subject', '');
                        ?>
                        <input type="text" value="<?php echo $existing_customer_email_subject; ?>" name="existing_customer_email_subject">
                    </td>
                </tr>
                <tr>
                    <th>Email Template</th>
                    <td>
                        <?php
                        $content_ec = stripslashes(get_option('existing_customer_email_template', ''));
                        $editor_id_ec = 'existing_customer_email_template';
                        $settings_ec = array(
                            'editor_height' => '200px'
                        );
                        wp_editor($content_ec, $editor_id_ec, $settings_ec);
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div id="birthday-email" class="tab-content">
            <h4><?php _e('Birthday Email Template', 'wc-offers'); ?></h4>
            <table class="template-table">
                <tr>
                    <th>Subject</th>
                    <td>
                        <?php
                        $birthday_email_subject = get_option('birthday_email_subject', '');
                        ?>
                        <input type="text" value="<?php echo $birthday_email_subject; ?>" name="birthday_email_subject">
                    </td>
                </tr>
                <tr>
                    <th>Email Template</th>
                    <td>
                        <?php
                        $content_birthday = stripslashes(get_option('birthday_email_template', ''));
                        $editor_id_b = 'birthday_email_template';
                        $settings_b = array(
                            'editor_height' => '200px'
                        );
                        wp_editor($content_birthday, $editor_id_b, $settings_b);
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div id="cashback-email" class="tab-content">
            <h4><?php _e('Cashback Email Template', 'wc-offers'); ?></h4>
            <table class="template-table">
                <tr>
                    <th>Subject</th>
                    <td>
                        <?php
                        $cashback_email_subject = get_option('cashback_email_subject', '');
                        ?>
                        <input type="text" value="<?php echo $cashback_email_subject; ?>" name="cashback_email_subject">
                    </td>
                </tr>
                <tr>
                    <th>Email Template</th>
                    <td>
                        <?php
                        $content = stripslashes(get_option('cashback_email_template', ''));
                        $editor_id = 'cashback_email_template';
                        $settings = array(
                            'editor_height' => '200px'
                        );
                        wp_editor($content, $editor_id, $settings);
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>    
</div>
