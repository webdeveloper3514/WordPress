<!--my token ->>>  pk_test_jgmwLo0RtxV342m0e5sfmxwY -->
<!--client token ->> pk_test_Oxm6sN3ADQ2kN69GfpfBwOGK  -->
<style>.stripe-button-el{position:relative;z-index:9999999999;opacity:0}</style>
<?php
global $wpdb;
$getStripeConfig = $wpdb->get_row("SELECT * FROM  wp_stripe_config", ARRAY_A);
//unset($_SESSION['pp_users']);
//$buttonClass = get_option('button_class');
//$modal_popup = get_option('modal_popup');

$sql_stripe_option = 'SELECT * FROM wp_stripe_funnel';
$stripe_options = $wpdb->get_results($sql_stripe_option);
$cnt = 1;
if (count($stripe_options) > 0) {
    foreach ($stripe_options as $opt) {
        $funnel_step = isset($opt->funnel_step) ? unserialize($opt->funnel_step) : array();
        $logo_url_data = unserialize($opt->checkout_app);
        $logo_array = array();
        parse_str($checkout_app, $logo_array);        
        $logo_url = isset($logo_array['funnel-co-app-logo']) ? $logo_array['funnel-co-app-logo'] : '';
        $buttonClass = '';
        if (ltrim($opt->checkout_page, '/') == get_page_uri()) {
            $checkout_app = unserialize($opt->checkout_app);
            $params = array();
            parse_str($checkout_app, $params);
            $buttonClass = isset($opt->checkout_class) ? $opt->checkout_class : $params['funnel-co-app-class'];
            $modal_popup = $params['funnel-co-app-plan'];
            $plan_fee = $params['funnel-co-app-plan-fee'];
            $webhook_id = $params['funnel-co-app-webhook'];
            $redirect_page = $params['funnel-co-app-page'];
            $logo_url = $params['funnel-co-app-logo'];
            $modalTitleVal = $opt->funnel_title;
        } else if (ltrim($opt->step_2_page, '/') == get_page_uri()) {
            $step_2_app = unserialize($opt->step_2_app);
            $params = array();
            parse_str($step_2_app, $params);
            $buttonClass = isset($opt->step_2_class) ? $opt->step_2_class : $params['funnel-step_2-app-class'];
            $modal_popup = $params['funnel-step_2-app-plan'];
            $plan_fee = $params['funnel-step_2-app-plan-fee'];
            $webhook_id = $params['funnel-step_2-app-webhook'];
            $redirect_page = $params['funnel-step_2-app-page'];
            $modalTitleVal = $opt->funnel_title;
        } else if ($funnel_step) {
            foreach ($funnel_step as $key => $single) {
                if (ltrim($single['funnel-step-page'], '/') == get_page_uri()) {
                    $step_2_app = $single['funnel-step-app-hidden'];
                    $params = array();
                    parse_str($step_2_app, $params);
                    $buttonClass = isset($single['funnel-step-class']) ? $single['funnel-step-class'] : $params['funnel-step_2-app-class'];
                    $modal_popup = $params['funnel-step_2-app-plan'];
                    $plan_fee = $params['funnel-step_2-app-plan-fee'];
                    $webhook_id = $params['funnel-step_2-app-webhook'];
                    $redirect_page = $params['funnel-step_2-app-page'];
                    $modalTitleVal = $opt->funnel_title;
                }
            }
        }
        //if ($buttonClass == get_page_uri()) {
        //print_r(${'option_' . $cnt});
        //echo $plan_fee;
        if (!isset($_SESSION['pp_users']['stripe']['subscription'])) {
            $getStripe = array();
            $getFee = $wpdb->get_row("SELECT * FROM wp_stripe_fee WHERE id=$plan_fee", ARRAY_A);
            $getPlan = $wpdb->get_row("SELECT * FROM wp_stripe_plan WHERE plan_id='$modal_popup'", ARRAY_A);
            if ($getFee) {
                $getStripe['funnel'] = 'Y';
                $getStripe['fee_redirect'] = $redirect_page;
                $getStripe['fee_amount'] = $getFee['fee_amount'];
                $getStripe['fee_name'] = $getFee['name'];
                $getStripe['fee_description'] = $getFee['description'];
                $getStripe['page'] = $buttonClass;
                $getStripe['plan_id'] = $getPlan['plan_id'];
                $getStripe['plan_name'] = $getPlan['plan_name'];
                $getStripe['plan_interval'] = $getPlan['plan_interval'];
                $getStripe['plan_trial'] = $getPlan['plan_trial'];
                $getStripe['plan_price'] = $getPlan['plan_price'];
                $getStripe['plan_description'] = $getPlan['description'];
            }
            // echo 'hello world';
            // exit;
            /* echo "SELECT sp.page,sp.is_funnel as funnel,f.fee_amount,f.redirect_page as fee_redirect,
              f.description as fee_description,p.plan_price,p.plan_trial, p.plan_name,p.plan_id,
              p.description as plan_description, p.redirect_page as plan_redirect
              FROM wp_stripe_fee f
              LEFT JOIN wp_stripe_page sp ON f.id = sp.fee_id
              LEFT JOIN wp_stripe_plan p ON p.id = sp.plan_id WHERE (p.plan_id = '$modal_popup' OR f.id=$plan_fee) AND sp.page = '".get_page_uri()."'
              "; */
//get_page_uri()
            if (empty($getStripe)) {
                $getDetail = $getPlan;
                if ($getDetail) {
                    $getStripe['funnel'] = 'Y';
                    $getStripe['fee_redirect'] = $redirect_page;
                    $getStripe['fee_amount'] = $getDetail['plan_price'];
                    $getStripe['fee_name'] = $getDetail['plan_name'];
                    $getStripe['fee_description'] = $getDetail['description'];
                    $getStripe['page'] = $buttonClass;
                    $getStripe['plan_id'] = $getDetail['plan_id'];
                    $getStripe['plan_name'] = $getDetail['plan_name'];
                    $getStripe['plan_interval'] = $getDetail['plan_interval'];
                    $getStripe['plan_trial'] = $getDetail['plan_trial'];
                    $getStripe['plan_price'] = $getDetail['plan_price'];
                    $getStripe['plan_description'] = $getDetail['description'];
                }
            }
            if ($getStripe['plan_interval'] == '30') {
                $getStripe['plan_interval'] = 'month';
            } elseif ($getStripe['plan_interval'] == '360') {
                $getStripe['plan_interval'] = 'month';
                //$getStripe['interval_count'] = '12';
            }
            if (!empty($getStripeConfig) && (!empty($getStripe))) {
                if (!empty($getStripe['fee_amount']) || !empty($getStripe['plan_price'])) {
                    ?>
                    <form style="display: none" action="" class="stripe_f" id="<?php echo $buttonClass; ?>" method="POST">
                        <input type="hidden" name="m_funnel" value="<?php echo $getStripe['funnel']; ?>">
                        <input type="hidden" name="m_redirect" value="/<?php echo $getStripe['fee_redirect']; ?>/"> <!-- /oto-gqkc/ -->
                        <input type="hidden" name="m_page_type" value="<?php echo $getStripe['page']; ?>"> <!--join-kc -->
                        <input type="hidden" name='m_amount' value="<?php echo $getStripe['fee_amount']; ?>"> <!--one time payment -->
                        <input type="hidden" name='m_sub_id' value="<?php echo $getStripe['plan_id']; ?>">
                        <input type="hidden" name='m_sub_name' value="<?php echo $getStripe['plan_name']; ?>">
                        <input type="hidden" name='m_sub_interval' value="<?php echo $getStripe['plan_interval']; ?>">
                        <input type="hidden" name='m_sub_trial' value="<?php echo $getStripe['plan_trial']; ?>">
                        <input type="hidden" name='m_sub_amount' value="<?php echo $getStripe['plan_price']; ?>"> <!--subscription -->
                        <input type="hidden" name='m_sub_desc' value="<?php echo trim($getStripe['plan_description']) != '' ? $getStripe['plan_description'] : $getStripe['fee_description']; ?>">  <!--subscription -->
                        <input type="hidden" name='m_webhook_id' value="<?php echo $webhook_id; ?>">  <!-- Webhook Id -->
                        <?php
                        /* $logoUrlVal2 = get_option('logo_url');
                          if(empty($logoUrlVal2)){
                          $logoUrlVal2 = "https://stripe.com/img/documentation/checkout/marketplace.png";
                          }
                          $modalTitleVal = get_option('modal_title'); */
                        ?>
                        <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="<?php echo $getStripeConfig['stripe_secret']; ?>" data-amount="<?php echo $getStripe['fee_amount']; ?>" data-name="<?php echo $modalTitleVal ?>" data-description="<?php echo trim($getStripe['plan_description']) != '' ? $getStripe['plan_description'] : $getStripe['fee_description']; ?>" data-shipping-address="true" data-image="<?php echo $logo_url; ?>" data-locale="auto"></script>
                    </form>
                    <?php
                } elseif (!empty($getStripe['fee_amount']) && empty($getStripe['plan_price'])) {
                    $redirect_url = ($plan_fee == 'plan') ? $getStripe['redirect_page'] : $getStripe['fee_redirect'];
                    $amount = ($plan_fee == 'plan') ? $getStripe['plan_price'] : $getStripe['fee_amount'];
                    ?>
                    <form style="display: none" action="" class="stripe_f" id="<?php echo $buttonClass; ?>" method="POST">
                        <input type="hidden" name="m_funnel" value="/<?php echo $getStripe['funnel']; ?>/">
                        <input type="hidden" name="onetime_redirect" value="/<?php echo empty($getStripe['fee_redirect']) ? $getStripe['plan_redirect'] : $getStripe['fee_redirect']; ?>/"> <!-- /oto-gqkc/ -->
                        <input type="hidden" name="onetime_page_type" value="<?php //echo $getStripe['page'];      ?>"> <!--join-kc -->
                        <input type="hidden" name='onetime_desc' value="<?php echo $getStripe['fee_description']; ?>">
                        <input type="hidden" name='onetime_amount' value="<?php echo $getStripe['fee_amount']; ?>"> <!--one time payment -->
                        <?php
                        $logoUrlVal2 = $logo_url;
                        if (empty($logoUrlVal2)) {
                            $logoUrlVal2 = "https://stripe.com/img/documentation/checkout/marketplace.png";
                        }
                        ?>
                        <script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="<?php echo $getStripeConfig['stripe_secret']; ?>" data-amount="<?php echo $getStripe['fee_amount']; ?>" data-name="<?php echo $modalTitleVal ?>" data-description="<?php echo trim($getStripe['plan_description']) != '' ? $getStripe['plan_description'] : $getStripe['fee_description']; ?>" data-shipping-address="true" data-image="<?php echo $logoUrlVal2 ?>"></script>
                    </form>
                <?php } ?>

            <?php } ?>

        <?php
        } else {
            $getStripe = array();
            $getFee = $wpdb->get_row("SELECT * FROM wp_stripe_fee WHERE id=$plan_fee", ARRAY_A);
            $getPlan = $wpdb->get_row("SELECT * FROM wp_stripe_plan WHERE plan_id='$modal_popup'", ARRAY_A);
            if ($getFee) {
                $getStripe['funnel'] = 'Y';
                $getStripe['fee_redirect'] = $redirect_page;
                $getStripe['fee_amount'] = $getFee['fee_amount'];
                $getStripe['fee_name'] = $getFee['name'];
                $getStripe['fee_description'] = $getFee['description'];
                $getStripe['page'] = $buttonClass;
                $getStripe['plan_id'] = $getPlan['plan_id'];
                $getStripe['plan_name'] = $getPlan['plan_name'];
                $getStripe['plan_interval'] = $getPlan['plan_interval'];
                $getStripe['plan_trial'] = $getPlan['plan_trial'];
                $getStripe['plan_price'] = $getPlan['plan_price'];
                $getStripe['plan_description'] = $getPlan['description'];
            }
            if (empty($getStripe)) {
                $getDetail = $getPlan;
                if ($getDetail) {
                    $getStripe['funnel'] = 'Y';
                    $getStripe['fee_redirect'] = $redirect_page;
                    $getStripe['fee_amount'] = $getDetail['plan_price'];
                    $getStripe['fee_name'] = $getDetail['plan_name'];
                    $getStripe['fee_description'] = $getDetail['description'];
                    $getStripe['page'] = $buttonClass;
                    $getStripe['plan_id'] = $getDetail['plan_id'];
                    $getStripe['plan_name'] = $getDetail['plan_name'];
                    $getStripe['plan_interval'] = $getDetail['plan_interval'];
                    $getStripe['plan_trial'] = $getDetail['plan_trial'];
                    $getStripe['plan_price'] = $getDetail['plan_price'];
                    $getStripe['plan_description'] = $getDetail['description'];
                }
            }
            if ($getStripe['plan_interval'] == '30') {
                $getStripe['plan_interval'] = 'month';
            } elseif ($getStripe['plan_interval'] == '360') {
                $getStripe['plan_interval'] = 'month';
            }
            if (!empty($getStripe)) {
                ?>
                <form style="display: none" action="" class="stripe_f" id="<?php echo $buttonClass; ?>" method="POST">
                    <?php
                    $logoUrlVal2 = $logo_url;
                    if (empty($logoUrlVal2)) {
                        $logoUrlVal2 = "https://stripe.com/img/documentation/checkout/marketplace.png";
                    }
                    //$modalTitleVal = get_option('modal_title');
                    ?>                    
                    <input type="hidden" name="m_funnel" value="<?php echo $getStripe['funnel']; ?>">
                    <input type="hidden" name="m_redirect" value="/<?php echo $redirect_page; ?>/"> <!-- /oto-gqkc/ -->
                    <input type="hidden" name="m_page_type" value="<?php echo $getStripe['page']; ?>"> <!--join-kc -->
                    <input type="hidden" name='m_amount' value="<?php echo $getStripe['fee_amount']; ?>"> <!--one time payment -->
                    <input type="hidden" name='m_sub_id' value="<?php echo $_SESSION['pp_users']['stripe']['subscription']['id']; ?>">
                    <input type="hidden" name='m_customer_id' value="<?php echo $_SESSION['pp_users']['stripe']['customer']['id']; ?>">
                    <input type="hidden" name='m_sub_name' value="<?php echo $getStripe['plan_name']; ?>">
                    <input type="hidden" name='new_plan_name' value="<?php echo $getStripe['plan_id']; ?>">
                    <input type="hidden" name='m_sub_interval' value="<?php echo $getStripe['plan_interval']; ?>">
                    <input type="hidden" name='m_sub_trial' value="<?php echo $getStripe['plan_trial']; ?>">
                    <input type="hidden" name='m_sub_amount' value="<?php echo $getStripe['plan_price']; ?>"> <!--subscription -->
                    <input type="hidden" name='m_sub_desc' value="<?php echo trim($getStripe['plan_description']) != '' ? $getStripe['plan_description'] : $getStripe['fee_description']; ?>"> <!--subscription -->
                    <input type="hidden" name='m_webhook_id' value="<?php echo $webhook_id; ?>">  <!-- Webhook Id -->
                    <input type="submit" style="display: none" id="stripe_upsell_<?php echo $buttonClass; ?>">
                <!--                    <script src="https://checkout.stripe.com/checkout.js" id="join-kc" class="stripe-button" data-key="<?php echo $getStripeConfig['stripe_secret']; ?>" data-amount="<?php echo $m_amount_new; ?>" data-name="MusicSupervisor" data-description="<?php
                    if (isset($getStripe['fee_description'])) {
                        echo $getStripe['fee_description'];
                    } else {
                        echo $getStripe['plan_description'];
                    }
                    ?>" data-shipping-address="true" data-image="<?php echo $logUrl ?>" data-locale="auto" data-email="<?php echo $_SESSION['pp_users']['stripe']['customer']['email']; ?>">></script>-->
                </form>

            <?php } ?>
            <?php
        }
        //}
    }
}


if (!is_admin()) {
    ?>
    <script type="text/javascript">$.noConflict();
        jQuery(document).ready(function () {
            jQuery(window).resize(function () {
                jQuery(".stripe-button-el").offset({top: jQuery(".stripe-btn").find("i").offset().top});
                jQuery(".stripe-button-el").offset({left: jQuery(".stripe-btn").find("i").offset().left});
                jQuery(".stripe-button-el").height(jQuery(".stripe-btn").find("i").height());
                jQuery(".stripe-button-el").width(jQuery(".stripe-btn").find("i").width());
                console.log(jQuery(".stripe-button-el").offset().top + ' - ' + jQuery(".stripe-button-el").width());
            });
            jQuery(window).resize();
        });</script>
<?php } ?>
