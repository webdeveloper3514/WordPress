<?php
/**
 * Plugin Name: Stripe Config
 * Description: Stripe Config is a WordPress plugin designed to make it easy for you to accept payments and create subscriptions from your WordPress site. 
 * Version: 1.0
 * Author: Wunder LLC
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function test_plugin_setup_menu() {
    // add_menu_page( 'Test Plugin Page', 'Stripe Config', 'manage_options', 'stripe-config-plugin', 'test_init' );
    // need to include styles inside plugin page
    global $page_hook_suffix;
    //$page_hook_suffix = add_options_page('Your_plugin', 'Your_plugin', 'manage_options', __FILE__, 'display_form');
    $page_hook_suffix = add_menu_page('Stripe Config Plugin', 'Stripe Config', 'manage_options', 'stripe-config-plugin', 'test_init');
}

add_action('admin_menu', 'test_plugin_setup_menu');

add_filter('plugin_row_meta', 'plugin_links', 10, 4);

function plugin_links($links, $plugin_file, $plugin_data) {

    if (current_user_can('install_plugins') && $plugin_data['Title'] == 'Stripe Config') {
        ?>
        <div id="sc-long-content" style="display:none;">
            <style>#TB_window:before{background:none!important}.stripe-content{padding-right:30px}</style>
            <div class="stripe-content">
                <h2>Stripe Config</h2>
                <p>
                    Stripe Config is a WordPress plugin designed to make it easy for you to accept payments and create subscriptions from your WordPress site. 
                </p>
                <p>
                    You can assign Stripe checkout modals to take payments directly from your website without forcing your customers leave for a 3rd party site. 
                </p>
                <p>
                    In addition, the plugin allows you to create 1-click up- or down-sells, so that you can sell additional products with just a single click. Your customers do not need to re-enter their credit card details again, which dramatically increases your conversions and revenues.
                </p>
                <p>
                    Need to register new customers automatically after a purchase on your website? No problem, use the Web-hook feature to select and send the required data to your website.
                </p>
                <p>
                    Works with any Wordpress site and setup.
                </p>
                <strong>Key features:</strong><br/>
                - Create checkout funnels visually <br/>
                - Easily create one-click up- or down-sells<br/>
                - Automatically create a new user by sending data from new customers to your website<br/>
                - Works with any wordpress site <br/>
                - Test all checkout funnels in "test-mode" before going live<br/>
                - Easily remove & edit existing checkout settings<br/>
                <br/>
                <br/>
                <strong>Requirements: </strong><br/>
                Stripe requires you to use an SSL certificate to be able to accept payments on your site.
            </div>                
        </div>
        <?PHP
        $links[] = sprintf('<a href="%s" class="thickbox sc-long-content" title="%s">%s</a>', self_admin_url('#TB_inline?width=650&height=550&inlineId=sc-long-content'), esc_attr(sprintf(__('More information about %s'), $plugin_data['Name'])), __('View Details')
        );
    }
    return $links;
}

function stripe_config_style($hook) {
    global $page_hook_suffix;
    if ($hook != $page_hook_suffix)
        return;
    //wp_register_style('options_page_style', plugins_url('css/options_style.css',__FILE__));
    //wp_enqueue_style('options_page_style');
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.12.4.js', array('jquery'), '1.9.1', true); // we need the jquery library for bootsrap js to function
    wp_enqueue_script('bootstrap-js', '//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js', array('jquery'), true); // all the bootstrap javascript goodness
    wp_enqueue_style('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css');
    //wp_enqueue_script('myScript', plugins_url( 'js/bs-snippet.js', __FILE__ ), array(), true);
}

add_action('admin_enqueue_scripts', 'stripe_config_style');

function my_custom_js() {
    global $wpdb;
    $getStripePage = $wpdb->get_results("SELECT wp_stripe_page.page FROM  wp_stripe_page", ARRAY_A);

    $stripePage = array();
    foreach ($getStripePage as $k => $v) {
        $stripePage[] = $v['page'];
    }

    $sql_stripe_option = 'SELECT * FROM wp_stripe_funnel';
    $stripe_options = $wpdb->get_results($sql_stripe_option);
    $cnt = 1;
    if (count($stripe_options) > 0) {
        foreach ($stripe_options as $opt) {
            $funnel_step = isset($opt->funnel_step) ? unserialize($opt->funnel_step) : array();            
            $buttonClassVal = '';
            if (ltrim($opt->checkout_page, '/') == get_page_uri()) {
                $checkout_app = unserialize($opt->checkout_app);
                $params = array();
                parse_str($checkout_app, $params);
                $buttonClassVal = isset($opt->checkout_class) ? $opt->checkout_class : $params['funnel-co-app-class'];
                $modal_popup = $params['funnel-co-app-plan'];
                $plan_fee = $params['funnel-co-app-plan-fee'];
            } else if (ltrim($opt->step_2_page, '/') == get_page_uri()) {
                $step_2_app = unserialize($opt->step_2_app);
                $params = array();
                parse_str($step_2_app, $params);
                $buttonClassVal = isset($opt->step_2_class) ? $opt->step_2_class : $params['funnel-step_2-app-class'];
                $modal_popup = isset($params['funnel-step_2-app-plan']) ? $params['funnel-step_2-app-plan'] : '';
                $plan_fee = isset($params['funnel-step_2-app-plan-fee']) ? $params['funnel-step_2-app-plan-fee'] : '';
            } else if ($funnel_step){
                foreach ($funnel_step as $key => $single) {
                    if(ltrim($single['funnel-step-page'], '/') == get_page_uri()){
                        $step_2_app = $single['funnel-step-app-hidden'];
                        $params = array();
                        parse_str($step_2_app, $params);
                        $buttonClassVal = isset($single['funnel-step-class']) ? $single['funnel-step-class'] : $params['funnel-step_2-app-class'];
                        $modal_popup = $params['funnel-step_2-app-plan'];
                        $plan_fee = $params['funnel-step_2-app-plan-fee'];
                    }
                }
            }
            //print_r($buttonClassVal);
            //exit;
            //$buttonClassVal = get_option('button_class');
            //$jsStripeMainPath = plugin_dir_url( __FILE__ ) . 'js/stripe-main.js';
            if (in_array(get_page_uri(), $stripePage) && !empty($buttonClassVal)) { // if(get_page_uri() == 'join-kc' || get_page_uri() == 'oto-gqkc' || get_page_uri() == 'oto-ygkc') {
                ?>
                <script type="text/javascript">jQuery(document).ready(function () {
                        jQuery('.<?php echo $buttonClassVal ?>').click(function (e) {
                            e.preventDefault();
                            jQuery("a.<?php echo $buttonClassVal ?>").removeAttr("href");
                            jQuery('#<?php echo $buttonClassVal ?> .stripe-button-el').click();
                            jQuery('#stripe_upsell_<?php echo $buttonClassVal ?>').click();
                        })
                        jQuery('.<?php echo $buttonClassVal ?>').click(function (e) {
                            e.preventDefault();
                            jQuery('#<?php echo $buttonClassVal ?> .stripe-button-el').click();
                            jQuery('#stripe_upsell_<?php echo $buttonClassVal ?>').click();
                        })
                    });</script>            
                <?php ?>

                <?php
                //echo '<script type="text/javascript" src="'.$jsStripeMainPath.'"></script>';
                $defaultPage = array('join-kc', 'oto-gqkc', 'oto-ygkc');
                require_once('includes/form/multiple-page.php');
            }
        }
    }

    $jsPath = plugin_dir_url(__FILE__) . 'js/stripe-config.js';
    ?>
    <script type="text/javascript">var ajax_admin_url = '<?php echo admin_url('admin-ajax.php'); ?>';
        var sc_plugin = '<?php echo admin_url('admin.php?page=stripe-config-plugin'); ?>';</script>
    <?php
    wp_enqueue_media();
    echo '<script type="text/javascript" src="' . $jsPath . '"></script>';    
}

// Add hook for admin <head></head>
add_action('admin_head', 'my_custom_js');
// Add hook for front-end <head></head>
add_action('wp_head', 'my_custom_js');

function test_init() {
    global $wpdb;

    //display message and error message
    $message_success = '';
    $message_error = '';
    if (isset($_SESSION['stripe_plugin']['success'])) {
        $message_success = $_SESSION['stripe_plugin']['success'];
        unset($_SESSION['stripe_plugin']['success']);
    }
    if (isset($_SESSION['stripe_plugin']['error'])) {
        $message_error = $_SESSION['stripe_plugin']['error'];
        unset($_SESSION['stripe_plugin']['error']);
    }
    if (!empty($message_success)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">{$message_success}</div>";
    }
    if (!empty($message_error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">{$message_error}</div>";
    }
    /* ./ */

    //get Stripe config
    $stripe_secret = $wpdb->get_row("SELECT * FROM  wp_stripe_config", ARRAY_A);
    if (!empty($stripe_secret)) {
        $api_key = $stripe_secret['stripe_secret'];
        $key = $stripe_secret['stripe_key'];
    } else {
        $api_key = '';
        $key = '';
    }


    //get All Plan    
    $last_time = get_option('stripe_last_execution', '10-09-2017 11:55:00');    
    $to_time = strtotime($last_time);
    $from_time = strtotime(date('m-d-Y h:i:s'));
    $difference = round(abs($to_time - $from_time) / 60,2);        
    if($difference > 30){
        $sql_i = "SELECT plan_id from wp_stripe_plan";
        $stripe_plan_i = $wpdb->get_results($sql_i);
        $db_plan = array();
        foreach ($stripe_plan_i as $k => $v) {
            $db_plan[] = $v->plan_id;
        }
        $get_plan = new Stripe();
        //check if plane exist
        $check_plan_id = $get_plan->getAllPlan();
        $StripePlan = isset($check_plan_id['data']) ? $check_plan_id['data'] : array(); 
        if($StripePlan){
            foreach ($StripePlan as $key => $s_data) {
                $plan_id = $s_data['id'];
                $plan_name = $s_data['name'];
                $trial = $s_data['trial_period_days'];
                $desc = $s_data['statement_descriptor'] != '' ? $s_data['statement_descriptor'] : '';
                $amount_subscription = $s_data['amount'];
                $stripe_plan_trial_select = $s_data['interval'];
                switch ($stripe_plan_trial_select) {
                    case "day":
                        $plan_interval = '1';
                        break;
                    case "week":
                        $plan_interval = '7';
                        break;            
                    case "month":
                        $plan_interval = '30';
                        break;            
                    case "year":
                        $plan_interval = '365';
                        break;            
                    default:
                        $plan_interval = '30';                
                        break;
                }
                if(in_array($s_data['id'], $db_plan)){                    
                    $update_db_plan = $wpdb->update(
                                'wp_stripe_plan', array(
                            'plan_name' => $plan_name,
                            'plan_trial' => $trial,
                            'redirect_page' => '',
                            'description' => $desc
                                ), array('plan_id' => $plan_id), array(
                            '%s',
                            '%d',
                            '%s',
                            '%s'
                                ), array('%s')
                        );                    
                }else{
                    $id = $s_data['id'];
                    $sql_id = "SELECT id FROM wp_stripe_plan WHERE plan_id='$id'";
                    $stripe_plan_s = $wpdb->get_row($sql_id);
                    if($stripe_plan_s){
                        $wpdb->delete('wp_stripe_plan', array('plan_id' => $id), array('%d'));
                    }else{
                        $wpdb->insert(
                                'wp_stripe_plan', array(
                            'plan_price' => $amount_subscription,
                            'plan_interval' => $plan_interval,
                            'plan_trial' => $trial,
                            'plan_name' => $plan_name,
                            'plan_id' => $plan_id,
                            'description' => $desc,
                            'redirect_page' => '',
                            'active' => 'Y'
                                ), array(
                            '%d',
                            '%d',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s'
                                )
                        );
                    }
                }
            } 
        }else{
            if($db_plan){
                foreach ($dbp as $dbp_value) {
                    $wpdb->delete('wp_stripe_plan', array('plan_id' => $dbp_value), array('%d'));
                }
            }
        }
        update_option('stripe_last_execution', date('m-d-Y h:i:s'));
    }
    $sql = "SELECT * from wp_stripe_plan";
    $stripe_plan = $wpdb->get_results($sql);
    //$stripe_plan = $wpdb->get_row( "SELECT * FROM  wp_stripe_plan", ARRAY_A );
    //Update plan template
    $update_plan_html = '<table class="table sc-plan-table"><tr><td></td><td>Plan name</td><td>Amount</td><td>Interval</td><td>Trial</td><td></td><td></td></tr>';
    $i = 1;
    $plan_interval_value = array('1', '30', '360', '7', '90', '180');
    foreach ($stripe_plan as $k => $v) {
        $new_plan = new Stripe();
        //check if plane exist
        $check_plan_id = $new_plan->getPlan($v->plan_id);
        //if plan not exist                   
        $value_plan_custom_interval = '';
        if (in_array($v->plan_interval, $plan_interval_value) || ($v->plan_interval == 0 || $v->plan_interval == '')) {
            $input_style = 'display:none;';
            $value_plan_interval = $v->plan_interval;
        } else {
            $input_style = 'display:block;';
            $value_plan_interval = 'custom';
            $value_plan_custom_interval = $v->plan_interval;
        }
        $update_plan_html .= '<tr>                                
                                    <td>
                                        <div class="lbl-number">' . $i . '</div>
                                    </td>
                                    <td>
                                        <input type="hidden" name="database_plan_id" value="' . $v->id . '">
                                        <input type="hidden" name="c_plan_id" value="' . $v->plan_id . '">
                                        <div class="input-group">
                                            <input required class="frm-fields radius-all form-control" name="c_plan_name" value="' . $v->plan_name . '">
                                            <span class="input-group-addon" id="basic-addon2"><i class="fa fa-info" aria-hidden="true"></i></span>        
                                        </div>
                                    </td>
                                    <td>
                                        <input required name="c_plan_amount" class="txt-sm frm-fields radius-all" value="' . substr_replace($v->plan_price, '.', strlen($v->plan_price) - 2, 0) . '">
                                    </td>
                                    <td>
                                        <select name="c_plan_interval" class="c_plan_interval frm-fields radius-all" disabled>
                                            <option value="">No Plan</option>
                                            <option value="1" ' . selected($value_plan_interval, '1', false) . '>Daily</option>
                                            <option value="30" ' . selected($value_plan_interval, '30', false) . '>Monthly</option>
                                            <option value="360" ' . selected($value_plan_interval, '360', false) . '>Yearly</option>
                                            <option value="7" ' . selected($value_plan_interval, '7', false) . '>Weekly</option>
                                            <option value="90" ' . selected($value_plan_interval, '90', false) . '>Every 3 Months</option>
                                            <option value="180" ' . selected($value_plan_interval, '180', false) . '>Every 6 Months</option>
                                            <option value="custom" ' . selected($value_plan_interval, 'custom', false) . '>Custom - ' . $value_plan_custom_interval . '</option>
                                        </select>
                                        <!--<input type="text" name="c_plan_custom_interval" class="c_plan_custom_interval" style="margin-top:10px;' . $input_style . '" value="' . $value_plan_custom_interval . '">-->
                                    </td>
                                    <td>
                                        <input required class="txt-sm frm-fields radius-all" name="c_plan_trial" value="' . $v->plan_trial . '">                                            
                                    </td>                                    
                                    <td>
                                        <button type="button" data-id="' . $v->id . '" name="Update" value="Update" class="btn btn-default sc-edit-plan">Edit Plan</button>
                                    </td>
                                    <td>                                    
                                        <input type="hidden" class="del_plan_id" name="del_plan_id" value="' . $v->id . '">
                                        <input type="hidden" class="del_plan_stripe_id" name="del_plan_stripe_id" value="' . $v->plan_id . '"> 
                                        <button type="button" name="Delete" value="Delete" class="btn btn-danger sc-delete-plan">Delete Plan</button>
                                    </td>                                
                            </tr>';
        $i++;               
    }
    $update_plan_html .= '</table>';

    //$sql_p = "SELECT * from wp_stripe_page s WHERE s.plan_id is NULL";
    $sql_p = "SELECT * from wp_stripe_page s";
    $stripe_page_r = $wpdb->get_results($sql_p);


    //Update plan template
    $page_select = '<select class="form-control" name="stripe_plan_page">';
    $i = 1;
    foreach ($stripe_page_r as $k => $v) {
        $page_select .= '<option value=' . $v->id . '>' . $v->page . '</option>';
        $i++;
    }
    $page_select .= '</select>';
    $add_new_plan_html = '
	<div class="modal fade" id="add-plan-m" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">			
			<h4 class="modal-title">Create Plan:</h4>
		  </div>
		  <form method="POST">
                        <div class="modal-body">                              
                               <div class="form-group">
                                      <label>Plan name</label>
                                      <input type="text" class="form-control" required name="stripe_plan_name" value="">
                               </div>
                                <div class="form-group">
                                      <label>Plan description</label>
                                      <input type="text" class="form-control" required name="stripe_plan_desc" value="">
                               </div>
                               <div class="form-group">
                                      <label>Plan id</label>
                                      <input type="text" class="form-control" required name="stripe_plan_id" value="">
                               </div>                                                             
                                <div class="form-group" style="display:none;">
                                      <label>Plan page</label>
                                      ' . $page_select . '
                               </div>
                               <div class="form-group" style="display:none;">
                                      <label>Page redirect</label>
                                      <input type="text" class="form-control" name="stripe_redirect_page" value="">
                               </div>
                               <div class="form-group form-group-in-row">
                                    <label>Amount</label>
                                    <input class="form-control" type="text" data-last-num="" required name="stripe_plan_amount" value="">
                               </div> 
                               <div class="form-group form-group-in-row">
                                      <label>Interval</label>
                                      <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Dropdown Example
                                        <span class="caret"></span></button>
                                        <ul class="dropdown-menu">
                                          <li><a href="#">HTML</a></li>
                                            <li><a href="#">CSS</a></li>
                                            <li><a href="#">JavaScript</a></li>
                                            <li class="divider"></li>
                                            <li><a href="#">About Us</a></li>
                                        </ul>
                                      </div>
                                      <select name="stripe_plan_trial_select" id="stripe_plan_trial_select" style="width: 100%;">
                                          <option value="">No Plan</option>
                                          <option value="1">Daily</option>
                                          <option value="30">Monthly</option>
                                          <option value="360">Yearly</option>
                                          <option value="7">Weekly</option>
                                          <option value="90">Every 3 Months</option>
                                          <option value="180">Every 6 Months</option>
                                          <option value="custom">Custom</option>
                                      </select>
                                    <span class="interval-info" style="display:none;color: #840101;margin-left: 0px; margin-top: 8px;">Intervals must be entered in days, divisible by 30 only</span>
                               </div>
                               <div class="form-group stripe_plan_trial_custom_data form-group-in-row" style="display:none;">
                                    <label>Custom Interval</label>
                                    <input class="form-control" type="number" name="stripe_plan_custom_trial" id="stripe_plan_custom_trial" value="">
                               </div>
                               <div class="form-group form-group-in-row">
                                    <label>Trial Period Days</label>
                                    <input class="form-control" type="number" min="0" max="30" required name="stripe_plan_trial" id="stripe_plan_trial" onchange="handlePeriodDayChange(this);" value="">
                               </div>
                        </div>
                        <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-success">Save Plan</button>
                        </div>
		  </form>
		</div>
	  </div>
	</div>
	';


    $sql_pp = "SELECT * from wp_stripe_page  WHERE fee_id is NULL";
    $stripe_page_rr = $wpdb->get_results($sql_pp);

    //Update plan template
    $page_select_price = '<select class="form-control" name="stripe_pay_page_r">';
    $ii = 1;
    foreach ($stripe_page_rr as $k => $v) {
        $page_select_price .= '<option value=' . $v->id . '>' . $v->page . '</option>';
        $ii++;
    }
    $page_select_price .= '</select>';
    $add_new_price_html = '
        <div class="modal fade" id="add-price-m" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">			
			<h4 class="modal-title">Create Payment:</h4>
		  </div>
		  <form method="POST">
			  <div class="modal-body">
                                <div class="form-group">
                                    <label>Payment Name</label>
                                    <input class="form-control" type="text" required name="stripe_pay_name" value="">
				</div>
                                <div class="form-group">
                                    <label>Payment Description</label>
                                    <input class="form-control" type="text" name="stripe_pay_desc" value="">
				</div>
				<div class="form-group">
                                    <label>Amount</label>
                                    <input class="form-control" type="text" data-last-num="" required name="stripe_pay_amount" value="">
				</div>				
                                <div class="form-group" style="display:none;">
                                      <label>One-time Payment page</label>
                                      ' . $page_select_price . '
                                </div>
				<div class="form-group" style="display: none">
                                    <label>Page redirect</label>
                                    <input class="form-control" type="text" name="stripe_pay_redirect_page" value="">
				</div>
			  </div>
			  <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Payment</button>
			  </div>
		  </form>
		</div>
            </div>
	</div>
	';

    //all Stripe fee html template (for update)
    $sql = "SELECT * from wp_stripe_fee";
    $stripe_fee = $wpdb->get_results($sql);
    $i = 0;
    $update_price_html = '<table class="table"><tr><td></td><td>Payment Name</td><td>Amount</td><td></td><td></td></tr>';
    foreach ($stripe_fee as $k => $v) {
        $update_price_html .= '<tr>					
						<td>
                                                    <div class="lbl-number">' . $i . '</div>
						</td>

						<td>
							<input required name="c_desc" class="frm-fields radius-all" value="' . $v->name . '">
						</td>
						<td>							
							<input required name="c_fee" class="fee_price txt-sm frm-fields radius-all" value="' . substr_replace($v->fee_amount, '.', strlen($v->fee_amount) - 2, 0) . '">
						</td>

						<td>
							<button type="button" data-id="' . $v->id . '" name="Update" value="Update" class="btn btn-default sc-edit-payment">Edit Payment</button>
						</td>					
						<td>
							<input type="hidden" class="del_fee_id" name="del_fee_id" value="' . $v->id . '">
                                                        <button type="button" name="Delete" value="Delete" class="btn btn-danger sc-delete-payment">Delete Payment</button>
						</td>					
			  </tr>';
        $i++;
    }
    $update_price_html .= '</table>';
    $update_price_html .= "<script>jQuery('.fee_price').maskMoney({prefix:'$',allowNegative:false,thousands:'',decimal:'.',affixesStay:false});</script>";

    //add new page
    //select all price
    $sql_pp_price = "SELECT * from wp_stripe_fee  WHERE active ='Y'";
    $stripe_page_fee = $wpdb->get_results($sql_pp_price);

    //Update plan template
    $page_fee = '<select class="form-control" name="stripe_fee_id"><option value=""></option>';
    $ii = 1;
    foreach ($stripe_page_fee as $k => $v) {
        $page_fee .= '<option value=' . $v->id . '>' . $v->description . '</option>';
        $ii++;
    }
    $page_fee .= '</select>';
    //select all plan
    $sql_pp_plan = "SELECT * from wp_stripe_plan  WHERE active ='Y'";
    $stripe_page_plan = $wpdb->get_results($sql_pp_plan);

    //Update plan template
    $page_plan = '<select class="form-control" name="stripe_plan_id"><option value=""></option>';
    $ii = 1;
    foreach ($stripe_page_plan as $k => $v) {
        $page_plan .= '<option value=' . $v->id . '>' . $v->plan_name . '</option>';
        $ii++;
    }
    $page_plan .= '</select>';
    $page_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $stripe_page = '<div class="modal fade" id="add-page-m" tabindex="-1" role="dialog">
					  <div class="modal-dialog" role="document">
						<div class="modal-content">
						  <div class="modal-header">							
							<h4 class="modal-title">Add page:</h4>
						  </div>
						  <form method="POST" action="' . $page_url . '&tab=stripe-new-page">
							  <div class="modal-body">
								<div class="form-group">
									<label>Page url</label>
									<input type="text" class="form-control" required name="stripe_pay_page" value="">
								 </div>
								 <div class="form-group" style="display:none;">
									<label>Page One-time Payment</label>
									' . $page_fee . '
								 </div>
								 <div class="form-group" style="display:none;">
									<label>Page Plan</label>
									' . $page_plan . '
								 </div>
								 <div class="checkbox">
								  <label>
									<input type="checkbox" class="form-control" name="funnel" value="Y">
									<span style="display:inline-block;margin-top: 5px;margin-left: 10px;color: #0C0033;">Funnel page</span>
								  </label>
								</div>
							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-success">Save Page</button>
							  </div>
						  </form>
						</div>
					  </div>
					</div>';

    //delete page
    //get All Plan
    $sql_page = "SELECT * from wp_stripe_page";
    $stripe_page_update = $wpdb->get_results($sql_page);

    $stripe_webhook = $wpdb->get_results("SELECT * FROM wp_stripe_webhook");
    //$stripe_plan = $wpdb->get_row( "SELECT * FROM  wp_stripe_plan", ARRAY_A );
    //Update plan template
    $update_page_html = '<table class="table sc_pages_list" style="width: 85%;"><tr><td></td><td style="text-align: center;">URL</td><td></td><td></td></tr>';
    $i = 1;
    foreach ($stripe_page_update as $k => $v) {
        $update_page_html .= '<tr>
					
						<td>
                                                    <div class="lbl-number">' . $i . '</div>
						</td>
                                                <form action="" method="post">
						<td style="width 450px;">
                                                    <input type="hidden" name="update_page_id" value="' . $v->id . '">
                                                    <div class="input-group">
                                                        <span class="input-group-addon frm-lbl-txt">' . get_site_url() . '/</span>
                                                        <input name="update_page_name" class="frm-fields" value="' . $v->page . '">
                                                        <span class="input-group-addon" id="basic-addon2"><i class="fa fa-info" aria-hidden="true"></i></span>    
                                                    </div>
						</td>
						<td style="width 100px;">
                                                    <button type="submit" name="Update" value="Update" class="btn btn-default">Update</button>
						</td>
                                                </form>					
                                        <td>                                        
                                                <input type="hidden" class="d_page_id" name="d_page_id" value="' . $v->id . '">
                                                <input type="hidden" name="d_page_plan_id" value="' . $v->plan_id . '">
                                                <input type="hidden" name="d_page_fee_id" value="' . $v->fee_id . '">
                                                <button type="button" name="Delete" value="Delete" class="btn btn-danger sc-delete-page">Delete URL</button>                                        
                                        </td>					
			  </tr>';
        $i++;
    }
    $update_page_html .= '</table>';


    /* Code of Pooja - Start */
    $stripe_all_keys = get_option("stripe_config_keys");
    $rdo_live = '';
    $rdo_test = '';
    $lbl_class = '';
    if ($stripe_all_keys['active_mode'] == 'live') {
        $rdo_live = 'checked';
        $lbl_class = 'bg-live';
    } elseif ($stripe_all_keys['active_mode'] == 'test') {
        $rdo_test = 'checked';
        $lbl_class = 'bg-test';
    }
    //print_r($stripe_all_keys);

    $buttonClass = get_option('button_class');
    $modal_popup = get_option('modal_popup');
    $logoUrl = get_option('logo_url');
    $modalTitle = get_option('modal_title');

    $arr_tmp = array(
        'button_class' => $buttonClass,
        'modal_popup' => $modal_popup,
        'logo_url' => $logoUrl,
        'modal_title' => $modalTitle
    );
    //echo "Hello";
    //echo json_encode($arr_tmp);

    $sql_stripe_option = 'SELECT * from wp_options where option_name like "stripe_config_options_%"';
    $stripe_options = $wpdb->get_results($sql_stripe_option);
    $new_option = 1;
    $cnt = 1;
    //print_r($stripe_options);
    if (count($stripe_options) > 0) {
        $new_option = count($stripe_options) + 1;
        foreach ($stripe_options as $opt) {
            ${'option_' . $cnt} = (json_decode($opt->option_value));
            $cnt++;
            //print_r($option_1);
        }
    }
    //echo $new_option;
    //Dropdown List for Plans
    $sql_plan = "SELECT * from wp_stripe_plan";
    $stripe_plan = $wpdb->get_results($sql_plan);

    $sql_slt_fee = "SELECT * from wp_stripe_fee";
    $stripe_slt_fee = $wpdb->get_results($sql_slt_fee);
    //$stripe_plan = $wpdb->get_row( "SELECT * FROM  wp_stripe_plan", ARRAY_A );
    //Class from Page Content
    $sql_page = "SELECT * from wp_stripe_page";
    $stripe_page_p = $wpdb->get_results($sql_page);
    $all_matched_classes = array();
    foreach ($stripe_page_p as $k => $v) {
        $page = get_posts(
                array(
                    'name' => $v->page,
                    'post_type' => 'page'
                )
        );
        if ($page) {
            //echo $page[0]->post_content;
            $page_content = $page[0]->post_content;
            //$page_content = '<a>Hello1</a><a>Hello2</a><a>Hello3</a><a>Hello4</a><a>Hello5</a>';
            //$matches = array();
            $match_3 = preg_match_all("'\[button(.*?)\]'si", $page_content, $matches_3);
            $match_2 = preg_match_all("'<a(.*?)</a>'si", $page_content, $matches_2);
            $match_4 = preg_match_all("'\[.*button(.*?)\[/.*|<a(.*?)</a>'si", $page_content, $matches_4);
            $match = preg_match_all("'\[mk_button(.*?)\[/mk_button|<a(.*?)</a>'si", $page_content, $matches);
            $arr_matched = array_merge($matches[1], $matches_2[1], $matches_3[1], $matches_4[1]);

            foreach ($arr_matched as $match_content) {
                $class = preg_match_all("'el_class=\"(.*?)\"'si", $match_content, $match_class);
                //array_map(function($v){ array_push($all_matched_classes,$v); },array_unique($match_class[1]));
                foreach ($match_class[1] as $m_class) {
                    //array_push($all_matched_classes,trim($m_class));
                    //print_r (explode(' ',$m_class));
                    foreach (explode(' ', $m_class) as $cls) {
                        array_push($all_matched_classes, trim($cls));
                    }
                    //print_r(array_map('add_class', explode(' ',$m_class)));
                }
            }
        }
    }
    $all_matched_classes = array_unique($all_matched_classes);

    //Select Class
    $selected = '';
    $select_class_html = '<td><span>Button Class</span></td>';
    $select_class_html .= '<td><select class="add_new_button_class" name="button_class[]"><option value="">Select Class</option><option value="mho">Join MHO</option>';
    foreach ($all_matched_classes as $k => $v) {
        //($buttonClass == $v) ? $selected = 'selected' : $selected = '';
        $select_class_html .= '<option ' . $selected . ' value="' . $v . '">' . $v . '</option>';
    }
    $select_class_html .= '</select></td>';

    //Select Plan
    $select_plan_html = '<td><span>Plan</span></td>';
    $select_plan_html .= '<td><select name="modal_popup[]" class="modal_popup"><option value="">No Plan</option>';
    $select_plan_options = '<option value="">Select Plan</option>';
    foreach ($stripe_plan as $k => $v) {
        //($modal_popup == $v->plan_id) ? $selected = 'selected' : $selected = '';
        $select_plan_html .= '<option ' . $selected . ' value="' . $v->plan_id . '">' . $v->plan_name . '</option>';
        $select_plan_options .= '<option ' . $selected . ' value="' . $v->plan_id . '">' . $v->plan_name . '</option>';
    }
    $select_plan_html .= '</select></td>';
    $select_fee_options = '';
    $select_fee_options .= '<option value="">Select One-time Payment</option>';
    foreach ($stripe_page_fee as $k => $v) {
        $select_fee_options .= '<option ' . $selected . ' value="' . $v->id . '">' . $v->description . '</option>';
    }
    $select_plan_html .= '</select></td>';

    /* Code of Pooja - End */
    //display plugin body with html template
    echo '
	<div id="myModal" class="popup-modal-bg" style="display: none;">
	<div class="popup-modal">
	  <div class="modal-dialog">
		<div class="modal-content div-add-new">
		</div>
	  </div>
	</div>	
	</div>
	';
    echo "<div class='hidden select_plan'>$select_plan_options</div>";
    echo "<div class='hidden select_fee'>$select_fee_options</div>";
    echo '<link rel="stylesheet" type="text/css" href="' . plugins_url('css/bs-snippet.css', __FILE__) . '"/>';
    echo '<link rel="stylesheet" type="text/css" href="' . plugins_url('assets/font-awesome/css/font-awesome.min.css', __FILE__) . '"/>';
    echo '<script src="' . plugins_url('js/jquery.mask.js', __FILE__) . '"></script>';
    echo "
    <div class=\"container-fluid main-container\">
    <div class=\"col-md-3 col-sm-3 sidebar left-sidebar\">
        <!-- uncomment code for absolute positioning tweek see top comment in css -->
        <div class=\"absolute-wrapper\"> </div>
        <!-- Menu -->
        <div class=\"side-menu\">
            <nav class=\"navbar navbar-default\" role=\"navigation\">
                <!-- Main Menu -->
                <div class=\"side-menu-container\">
                    <ul class=\"nav navbar-nav\">
                        <li><a href=\"stripe-config\" class=\"v-tab\"><span class=\"glyphicon glyphicon-dashboard\"></span> Stripe API Key Config </a></li>
                        <li><a href=\"stripe-page\" class=\"v-tab\"><span class=\"glyphicon glyphicon-cloud\"></span> Stripe Pages </a></li>
                        <li><a href=\"stripe-plan\" class=\"v-tab\"><span class=\"glyphicon glyphicon-user\"></span> Plans </a></li>
                        <li><a href=\"stripe-product-price\" class=\"v-tab\"><span class=\"glyphicon glyphicon-signal\"></span> Payments </a></li>                        
                        <li><a href=\"plans-payments-new\" class=\"v-tab\"><span class=\"glyphicon glyphicon-plane\"></span> Funnels </a></li>
                        <li><a href=\"stripe-webhook\" class=\"v-tab\"><span class=\"glyphicon glyphicon-road\"></span> Webhook </a></li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </nav>
        </div>		
    </div>
    <div class=\"col-md-9 col-sm-9 content right-sidebar\">
        <div class=\"sc-loader\"></div>
        <div class=\"panel panel-default v-tab-pane\" id=\"stripe-config\">
            <form method=\"POST\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title panel-header\">
                    Stripe API Key
                    <a href=\"#\" data-tooltip=\"Please enter your api token keys from stripe. you should enter both your 'publishable key' as well as the 'secret key' if you want to test your checkout forms you should also enter your test keys. all keys are available via your stripe account.\"><i class=\"fa fa-info icon-info\" aria-hidden=\"true\"></i></a>
                    <button type=\"submit\" value=\"Update\" class=\"btn btn-success margin-l\">Save Settings</button>
                </h4>
            </div>
            <div class=\"panel-body\">                
                <div style=\"width: 95%;\">
                    <table class=\"table-space tbl-fix-input\" style=\"width: 100%;\">
                        <tr style=\"border-bottom: 0 !important;\">
                            <td class=\"frm-lbl\">
                                <label style=\"float: left;\" class=\"switch\">
                                    <input type=\"checkbox\" name=\"stripe_api_keys\" value=\"live\" id=\"chk_test\" $rdo_live>
                                    <div class=\"slider round\"></div>
                                </label>
                                <div class=\"lbl-desc\">Your Stripe API Keys are currently set to</div>
                                <div class=\"lbl-fix-outer radius-all\">
                                    <div class=\"input-group-addon lbl-fix-inner $lbl_class\">{$stripe_all_keys['active_mode']}</div>
                                </div>								
                            </td>
                        </tr>
                        <tr>
                            <td class=\"frm-lbl\">
                                <div class=\"frm-lbl-txt\">Publishable Key</div>
                                <input name=\"txt_stripe_api_key_class\" class=\"stripe-conf frm-fields frm-field-half pull-left txt-bold radius-left\" data-id=\"stripe_api_key_class\" value=\"\">
                                <input type=\"hidden\" id=\"stripe_api_key_class\" name=\"stripe_api_key_class\" value=\"{$stripe_all_keys['live_publishable']}\">
                                <div class=\"lbl-fix-outer radius-right\">
                                    <div class=\"input-group-addon lbl-fix-inner bg-live radius-right\">live</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"frm-lbl\">
                                <div class=\"frm-lbl-txt\">Secret Key</div>
                                <input name=\"txt_stripe_key_form\" class=\"stripe-conf frm-fields frm-field-half pull-left txt-bold radius-left\" data-id=\"stripe_key_form\" value=\"\">
                                <input type=\"hidden\" id=\"stripe_key_form\" name=\"stripe_key_form\" value=\"{$stripe_all_keys['live_secret']}\">
                                <div class=\"lbl-fix-outer radius-right\">
                                    <div class=\"input-group-addon lbl-fix-inner bg-live radius-right\">live</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"frm-lbl\">
                                <div class=\"frm-lbl-txt\">Publishable Key</div>
                                <input name=\"txt_stripe_api_key_class_test\" data-id=\"stripe_api_key_class_test\" class=\"stripe-conf frm-fields frm-field-half pull-left txt-bold radius-left\" value=\"\">
                                <input type=\"hidden\" id=\"stripe_api_key_class_test\" name=\"stripe_api_key_class_test\" value=\"{$stripe_all_keys['test_publishable']}\">
                                <div class=\"lbl-fix-outer radius-right\">
                                    <div class=\"input-group-addon lbl-fix-inner bg-test radius-right\">test</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class=\"frm-lbl\">
                                <div class=\"frm-lbl-txt\">Secret Key</div>
                                <input name=\"txt_stripe_key_form_test\" data-id=\"stripe_key_form_test\" class=\"stripe-conf frm-fields frm-field-half pull-left txt-bold radius-left\" value=\"\">
                                <input type=\"hidden\" id=\"stripe_key_form_test\" name=\"stripe_key_form_test\" value=\"{$stripe_all_keys['test_secret']}\">
                                <div class=\"lbl-fix-outer radius-right\">
                                    <div class=\"input-group-addon lbl-fix-inner bg-test\">test</div>
                                </div>
                            </td>
                        </tr>
                    </table>                    
                </div>
            </div>
			</form>
        </div>        
        <div class=\"panel panel-default v-tab-pane\" id=\"plans-payments-new\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title panel-header\">
                    Funnels
                    <a href=\"#\" data-tooltip=\"To assign a plan and/or payment to a specific text link or button, you must manually assign a class. For example, class='stripe-signup'.\"><i class=\"fa fa-info icon-info\" aria-hidden=\"true\"></i></a>
                    <button type=\"button\" class=\"btn btn-primary margin-l\" id=\"btn-add-new-funnel\">
                        Add Funnel
                    </button>                    
                    <button type=\"button\" class=\"btn btn-success\" id=\"btn-funnel-save\" style=\"display:none;\">
                        Save Funnel
                    </button>
                    <button type=\"button\" class=\"btn btn-default\" id=\"btn-funnel-cancel\" style=\"display:none;\">
                        Cancel Changes
                    </button>                    
                </h4>
            </div>
            <div class=\"panel-body\">";
    $cnt = 1;
    $option_html_n = '';
    $option_html_n .= '<table class="table sc-funnel" style="width: 85%;"><tr><td></td><td style="text-align: left;">Funnel Title</td><td></td><td></td></tr>';
    $stripe_funnel_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_funnel';
    $stripe_funnel_data = $wpdb->get_results($stripe_funnel_sql, ARRAY_A);
    if (count($stripe_funnel_data) > 0) {
        foreach ($stripe_funnel_data as $opt) {
            $option_html_n .= '<tr>
                                        <td>
                                            <div class="lbl-number">' . $cnt . '</div>
                                        </td>
                                        <td style="width 450px;">
                                            <div class="input-group">
                                                <input type="text" class="form-control" aria-describedby="basic-addon2" value="' . $opt['funnel_title'] . '">
                                                <span class="input-group-addon" id="basic-addon2"><i class="fa fa-info" aria-hidden="true"></i></span>
                                          </div>
                                        </td>
                                        <td style="width 100px;">
                                            <button type="button" name="Update" data-id="' . $opt['id'] . '" value="Update" class="btn btn-success btn-edit-funnel">Edit Funnel</button>&nbsp;
                                            <button type="button" name="Delete" data-id="' . $opt['id'] . '" value="Delete" class="btn btn-danger btn-delete-funnel">Delete Funnel</button>
                                        </td>
                      </tr>';
            $cnt++;
        }
    } else {
        $option_html_n .= '<tr>
                                <td></td>
                                <td style="text-align: center;">
                                    No funnels created 
                                </td>
                                <td></td>
                                <td></td>
                            </tr>';
    }
    $option_html_n .= '</table>';
    echo $option_html_n;
    echo "
        <div class=\"funnel-edit-add\" style=\"display:none;\">            
            <form id=\"funnel-edit-add-form\" method=\"POST\" name=\"funnel-edit-add\">
                <input type=\"hidden\" name=\"funnel-edit-add-form-hidden\">                
                <div class=\"input-group\" style=\"margin-bottom:20px;\">                
                    <input type=\"text\" placeholder=\"Funnel title\" class=\"form-control\" aria-describedby=\"basic-addon2\" name=\"funnel-title\" class=\"funnel-title-text\">
                </div>
                <div class=\"input-group\" style=\"margin-bottom:20px;\">       
                    <a href=\"#\" class=\"sc-create-funnel-step\">Add new step</a>
                </div>
                <div class=\"funnel-checkout funnel-checkout-step-count\">
                    <div class=\"checkout-text\">Checkout Page</div>
                    <label>Select Pages</label>
                    <div class=\"input-group funnel-border-bottom\">
                        <span class=\"input-group-addon\" id=\"basic-addon3\">page:</span>
                        <select name=\"funnel-checkout-page\" class=\"funnel-checkout-page\">";
    if ($stripe_page_update) {
        echo "<option value=''>Select page</option>";
        foreach ($stripe_page_update as $k => $v) {
            echo "<option value='$v->page'>" . $v->page . "</option>";
        }
    } else {
        echo "<option value=''>No pages created</option>";
    }
    echo "
                        </select>
                    </div>
                    <label>Available classes</label>
                    <div class=\"input-group funnel-square-border funnel-available-class-div\">";
    foreach ($all_matched_classes as $k => $v) {
        echo '<label class="input-label"><input type="radio" class="funnel-checkout-class" name="funnel-checkout-class" value="' . $v . '"><span class="class-style">class: </span><span class="value-style">' . $v . '</span></label>';
    }
    echo "</div>
                    <input type=\"hidden\" name=\"funnel-checkout-app-hidden\" class=\"funnel-checkout-app-hidden\">    
                    <button type=\"button\" name=\"Update\" class=\"btn btn-success btn-assign-checkout-funnel\">Assign Payment/Plan</button>
                </div>
                <div class=\"funnel-step-2 funnel-checkout-step-count\">
                    <div class=\"checkout-text\"><span class=\"sc-step-number\">Step 2</span></div>
                    <input type=\"hidden\" name=\"funnel-step-number\" class=\"funnel-step-number\">
                    <label>Select Page</label>
                    <div class=\"input-group funnel-square-border\">";
    if ($stripe_page_update) {
        foreach ($stripe_page_update as $k => $v) {
            echo '<label class="input-label"><input type="radio" name="funnel-step_2-page" class="funnel-step-page" value="' . $v->page . '"><span class="class-style">page: </span><span class="value-style">' . $v->page . '</span></label>';
        }
    }
    echo "
                    </div>
                    <hr style='border-top: 2px solid #eee;margin-top: 30px;margin-bottom: 10px;'></hr>
                    <label>Available classes</label>
                    <div class=\"input-group funnel-square-border funnel-available-class-div\">";
    foreach ($all_matched_classes as $k => $v) {
        echo '<label class="input-label"><input type="radio" name="funnel-step_2-class" class="funnel-step-class" value="' . $v . '"><span class="class-style">class: </span><span class="value-style">' . $v . '</span></label>';
    }
    echo "</div>
                    <input type=\"hidden\" name=\"funnel-step_2-app-hidden\" class=\"funnel-step-app-hidden\">    
                    <button type=\"button\" name=\"Update\" class=\"btn btn-success btn-assign-step-funnel btn-assign-step_2-funnel\">Assign Payment/Plan</button>
                </div>
            </form>
        </div>
        </div>
        </div>
        <div class=\"panel panel-default v-tab-pane\" id=\"stripe-page\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title panel-header\">
                    Stripe Pages
                    <a href=\"#\" data-tooltip=\"You must specify on which pages you wish to trigger a stripe checkout. if you are creating a series of pages with multiple products for checkout you should select the checkbox called 'funnel page' so that a user will not need to re-enter his credit card details (i.e. this will allow you to create a â€œone-click up-sellâ€?). tip: it is recommended to add a final checkout page (such as â€œcongratulationsâ€?) to be able to track your conversions (for example, via google analytics). do not select â€œfunnel pageâ€? for the final page such as â€œcongratulationsâ€?. \"><i class=\"fa fa-info icon-info\" aria-hidden=\"true\"></i></a>
                    <button type=\"button\" class=\"btn btn-primary margin-l\" id=\"add-n-p\" data-toggle=\"modal\" data-target=\"#add-page-m\">
                        Add Page
                    </button>
                </h4>
            </div>
            <div class=\"panel-body\">
                {$stripe_page}
                <div style=\" overflow-x: auto;\">
                      {$update_page_html}
                </div>
            </div>
        </div>
        <div class=\"panel panel-default v-tab-pane\" id=\"stripe-plan\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title panel-header\">
                    Plans
                    <a href=\"#\" data-tooltip=\"To create a plan simply enter the required inputs\"><i class=\"fa fa-info icon-info\" aria-hidden=\"true\"></i></a>
                    <button type=\"button\" class=\"btn btn-primary margin-l\" id=\"add-new-plan\">
                        Add Plan
                    </button>
                </h4>
            </div>
            
            <div class=\"panel-body\">
                {$add_new_plan_html}
                <div style=\"overflow:auto;\">
                      {$update_plan_html}
                </div>
            </div>
        </div>
        <div class=\"panel panel-default v-tab-pane\" id=\"stripe-product-price\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title panel-header\">
                    Payments
                    <a href=\"#\" data-tooltip=\"To create a one-time payment simply enter the required inputs\"><i class=\"fa fa-info icon-info\" aria-hidden=\"true\"></i></a>
                    <button type=\"button\" class=\"btn btn-primary margin-l\" id=\"add-new-payment\">
                        Add Payment
                    </button>
                </h4>
            </div>
            <div class=\"panel-body\" style=\"padding-right: 10%;\">
                {$add_new_price_html}
                <div style=\"overflow:auto;\">
                    {$update_price_html}
                </div>
            </div>
        </div>
        <div class=\"panel panel-default v-tab-pane\" id=\"stripe-webhook\">
            <div class=\"panel-heading\">
                <h4 class=\"panel-title panel-header\">
                    Webhook
                    <a href=\"#\" data-tooltip=\"You can send data from new clients to your application. this could be used, for example, to automatically create newly registered customers with instant access. just make sure to enter the correct url as indicated.\"><i class=\"fa fa-info icon-info\" aria-hidden=\"true\"></i></a>
                    <button type=\"button\" class=\"btn btn-primary margin-l\" id=\"add-n-f\" data-toggle=\"modal\" data-target=\"#add-webhook\">
                        Add Webhook
                    </button>
                </h4>
            </div>
            <div class=\"panel-body\">";
    $stripe_send_url = get_option('stripe_send_url');
    //wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.12.4.js', array('jquery'), '1.9.1', true); // we need the jquery library for bootsrap js to function
    //wp_enqueue_script('bootstrap-js', '//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js', array('jquery'), true); // all the bootstrap javascript goodness
    //wp_enqueue_style('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css');
    $arr_allowed_data = json_decode(get_option('stripe_allowed_data'));
    //print_r($arr_allowed_data);
//    $chk_name = ($arr_allowed_data->name == 1) ? "checked" : "";
//    $chk_email = ($arr_allowed_data->email == 1) ? "checked" : "";
//    $chk_desc = ($arr_allowed_data->description == 1) ? "checked" : "";
//    $chk_address = ($arr_allowed_data->address_line1 == 1) ? "checked" : "";
//    $chk_city = ($arr_allowed_data->address_city == 1) ? "checked" : "";
//    $chk_state = ($arr_allowed_data->address_state == 1) ? "checked" : "";
//    $chk_country = ($arr_allowed_data->address_country == 1) ? "checked" : "";
//    $chk_zip = ($arr_allowed_data->address_zip == 1) ? "checked" : "";    

    $stripe_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_webhook';
    $stripe_webhook_data = $wpdb->get_results($stripe_sql, ARRAY_A);
    $i = 1;
    echo '
            
                            <div style="width: 95%;overflow:auto;">
                                <table class="table-space tbl-fix-input" style="width: 100%;">
                                    <tr style="height: auto;">
                                        <td></td>
                                        <td class="frm-lbl">
                                            Webhook URL
                                        </td>
                                        <td class="frm-lbl">
                                            Webhook ID
                                        </td>
                                        <td></td><td></td>
                                    </tr>';
    if ($stripe_webhook_data) {
        $count = 1;
        foreach ($stripe_webhook_data as $key => $stripe_webhook_single) {
            echo '<tr style="border-bottom: 0 !important;">
                                        <td><div class="lbl-number">' . $count . '</div></td>
                                        <td>
                                            <div class="input-group">
                                                <input class="frm-fields radius-all form-control" type="text" style="width: 300px; line-height: inherit;" id="stripe_send_url" name="stripe_send_url" value="' . $stripe_webhook_single['webhook_url'] . '">
                                                <span class="input-group-addon" id="basic-addon2"><i class="fa fa-info" aria-hidden="true"></i></span>
                                            </div>
                                        </td>
                                        <td>
                                            <input class="txt-sm frm-fields radius-all" type="text" name="stripe_webhook_id" value="' . $stripe_webhook_single['webhook_id'] . '">
                                        </td>
                                        <td>
                                            <button type="button" name="Update" value="Update" class="btn btn-default btn-modify-webhook" data-id="' . $stripe_webhook_single['id'] . '">Edit Webhook</button>
                                        </td>
                                        <td>
                                            <button type="button" name="Delete" value="Delete" class="btn btn-danger btn-delete-webhook" data-id="' . $stripe_webhook_single['id'] . '">Delete Webhook</button>
                                        </td>
                                    </tr>';
            $count++;
        }
    }

    echo '                                    
                                </table>
                            </div>
                            
                            <div class="modal fade" id="add-webhook" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">                                                
                                            <h4 class="modal-title">Create Webhook:</h4>
                                        </div>
                                        <form method="POST" class="frm-stripe-send-data">
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="frm-lbl-txt" for="modify_stripe_send_url" style="width: 300px; margin-left: 0;">Webhook URL</label>
                                                    <label class="frm-lbl-txt" for="modify_stripe_webhook_id" style="margin-left: 0;">Webhook ID</label>
                                                    <input class="form-control" type="text" style="width: 280px;line-height: inherit;display: inline-block;float: left;margin-right: 25px;" id="stripe_send_url" name="stripe_send_url" value="">
                                                    <input class="txt-sm form-control" maxlength="5" type="text" id="modify_stripe_webhook_id" name="modify_stripe_webhook_id" value="">
                                                </div>

                                                <label class="frm-lbl-txt" style="margin-left: 0;">Select Data:</label>                                            
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_id" class="form-control" name="stripe_id" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_id">Customer ID</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_name" class="form-control" name="stripe_name" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_name">Customer Name</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_email" class="form-control" name="stripe_email" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_email">Customer Email</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_description" class="form-control" name="stripe_description" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_description">Plan, Payment Description</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_address" class="form-control" name="stripe_address" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_address">Customer Street Address</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_city" class="form-control" name="stripe_city" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_city">Customer City</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_state" class="form-control" name="stripe_state" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_state">Customer State</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_country" class="form-control" name="stripe_country" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_country">Customer Country</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_zipcode" class="form-control" name="stripe_zipcode" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_zipcode">Customer Zipcode</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <div>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancel</button>
                                                    <button type="submit" name="stripe_webhook" class="btn btn-success" value="stripe_webhook">Save Webhook</button>
                                                </div>
                                           </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" id="modify-webhook" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">                                                
                                            <h4 class="modal-title">Edit Webhook:</h4>
                                        </div>
                                        <form method="POST" class="frm-stripe-send-data">
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="frm-lbl-txt" for="modify_stripe_send_url" style="width: 300px; margin-left: 0;">Webhook URL</label>
                                                    <label class="frm-lbl-txt" for="modify_stripe_webhook_id" style="margin-left: 0;">Webhook ID</label>
                                                    <input class="form-control" type="text" style="width: 280px;line-height: inherit;display: inline-block;float: left;margin-right: 25px;" id="stripe_send_url" name="stripe_send_url" value="">
                                                    <input class="txt-sm form-control" maxlength="5" type="text" id="modify_stripe_webhook_id" name="modify_stripe_webhook_id" value="">
                                                    <input type="hidden" id="modify_stripe_webhook_data_id" name="modify_stripe_webhook_data_id" value="">
                                                </div>

                                                <label class="frm-lbl-txt" style="margin-left: 0;">Select Data:</label> 
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_id" class="form-control" name="stripe_id" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_id">Customer ID</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_name" class="form-control" name="stripe_name" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_name">Customer Name</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_email" class="form-control" name="stripe_email" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_email">Customer Email</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_description" class="form-control" name="stripe_description" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_description">Plan, Payment Description</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_address" class="form-control" name="stripe_address" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_address">Customer Street Address</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_city" class="form-control" name="stripe_city" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_city">Customer City</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_state" class="form-control" name="stripe_state" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_state">Customer State</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_country" class="form-control" name="stripe_country" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_country">Customer Country</label>
                                                </div>

                                                <div class="form-group">
                                                    <input type="checkbox" id="chk_stripe_zipcode" class="form-control" name="stripe_zipcode" value="1"/>
                                                    <label class="lbl-chk" for="chk_stripe_zipcode">Customer Zipcode</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <div>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancel</button>
                                                    <button type="submit" name="stripe_webhook" class="btn btn-success" value="stripe_webhook">Save Webhook</button>
                                                </div>
                                           </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            ';
    echo "</div>
        </div>
        ";
    echo '<div class="modal fade" id="tooltip-dialog" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">                    
                    <div class="modal-body"></div>
                    <div class="modal-footer">                                                            
                        <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancel</button>                            
                   </div>                    
                </div>
            </div>
        </div>        
        <div class="modal fade" id="funnel-checkout-add-edit" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">                        
                        <h4 class="modal-title">Assign Payment/Plan</h4>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Class selected</label>                                
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">class:</span>
                                    <select class="form-control funnel-co-app-class" name="funnel-co-app-class">';
                                    foreach ($all_matched_classes as $k => $v) {
                                        echo '<option value="' . $v . '">' . $v . '</option>';
                                    }
                                    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Select one-time Payment(optional)</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">otp:</span>
                                    <select class="form-control funnel-co-app-plan-fee" name="funnel-co-app-plan-fee">
                                        <option value="">No One Time Payment</option>';
    foreach ($stripe_slt_fee as $k => $v) {
        echo '<option value="' . $v->id . '">' . $v->description . '</option>';
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Select subscription plan(optional)</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">plan:</span>
                                    <select class="form-control funnel-co-app-plan" name="funnel-co-app-plan">
                                            <option value="">No Plan</option>';
    foreach ($stripe_plan as $k => $v) {
        //($modal_popup == $v->plan_id) ? $selected = 'selected' : $selected = '';
        echo '<option ' . $selected . ' value="' . $v->plan_id . '">' . $v->plan_name . '</option>';
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Select page redirection</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">page:</span>
                                    <select class="form-control funnel-co-app-page" name="funnel-co-app-page">
                                        <option value="">Select Page</option>';
    if ($stripe_page_update) {
        foreach ($stripe_page_update as $k => $v) {
            echo "<option value='$v->page'>" . $v->page . "</option>";
        }
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Webhook (optional)</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">webhook id:</span>
                                    <select class="form-control funnel-co-app-webhook" name="funnel-co-app-webhook">
                                        <option value="">Select webhook</option>';
    if ($stripe_webhook) {
        foreach ($stripe_webhook as $key => $single_webhook) {
            echo "<option value='$single_webhook->id'>" . $single_webhook->webhook_id . "</option>";
        }
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Funnel Logo</label>
                                <div class="input-group">
                                    <button class="btn btn-default funnel-logo-button">Upload Logo</button>
                                    <input type="hidden" name="funnel-co-app-logo" class="funnel-co-app-logo">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancel</button>                            
                            <button type="button" class="btn btn-success funnel-checkout-app">Save Assignment</button>
                       </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="funnel-step_2-add-edit" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">                        
                        <h4 class="modal-title">Assign Payment/Plan</h4>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Class selected</label>                                
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">class:</span>
                                    <select class="form-control funnel-step_2-app-class" name="funnel-step_2-app-class">';
    foreach ($all_matched_classes as $k => $v) {
        echo '<option value="' . $v . '">' . $v . '</option>';
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Select one-time Payment(optional)</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">otp:</span>
                                    <select class="form-control funnel-step_2-app-plan-fee" name="funnel-step_2-app-plan-fee">
                                        <option value="">No One Time Payment</option>';
    foreach ($stripe_slt_fee as $k => $v) {
        echo '<option value="' . $v->id . '">' . $v->description . '</option>';
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Select subscription plan(optional)</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">plan:</span>
                                    <select class="form-control funnel-step_2-app-plan" name="funnel-step_2-app-plan">
                                            <option value="">No Plan</option>';
    foreach ($stripe_plan as $k => $v) {
        //($modal_popup == $v->plan_id) ? $selected = 'selected' : $selected = '';
        echo '<option ' . $selected . ' value="' . $v->plan_id . '">' . $v->plan_name . '</option>';
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Select page redirection</label>
                                <label style="margin-bottom: 10px;">
                                    <input type="checkbox" name="funnel_step_2_last_page"> last page in funnel
                                </label>
                                <div class="input-group funnel-border-bottom">                                    
                                    <span class="input-group-addon" id="basic-addon3">page:</span>
                                    <select class="form-control funnel-step_2-app-page" name="funnel-step_2-app-page">
                                        <option value="">Select Page</option>';
    if ($stripe_page_update) {
        foreach ($stripe_page_update as $k => $v) {
            echo "<option value='$v->page'>" . $v->page . "</option>";
        }
    }
    echo '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Webhook (optional)</label>
                                <div class="input-group funnel-border-bottom">
                                    <span class="input-group-addon" id="basic-addon3">webhook id:</span>
                                    <select class="form-control funnel-step_2-app-webhook" name="funnel-step_2-app-webhook">
                                        <option value="">Select webhook</option>';
    if ($stripe_webhook) {
        foreach ($stripe_webhook as $key => $single_webhook) {
            echo "<option value='$single_webhook->id'>" . $single_webhook->webhook_id . "</option>";
        }
    }
    echo '</select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancel</button>                                                        
                            <button type="button" class="btn btn-success funnel-step_2-app">Save Assignment</button>
                            <input type="hidden" name="funnel-step-app-number" class="funnel-step-app-number">
                       </div>                       
                    </form>
                </div>
            </div>
        </div>
        ';
    echo '<script src="' . plugins_url('js/bs-snippet.js', __FILE__) . '"></script>';
    ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>$('body').addClass('custom-bg');</script>
    <script>var add_cnt = 1;var add_new_prepend = '<div id="add_new_' + (add_cnt) + '" class="postbox"> ' + '<button type="button" class="handlediv" aria-expanded="false">' + '<span class="screen-reader-text">Toggle panel: add_new_' + (add_cnt) + '</span>' + '<span class="toggle-indicator dashicons dashicons-arrow-down" aria-hidden="true"></span>' + '</button>' + '<h4 class="hndle ui-sortable-handle handle-heading">' + '<span>add_new_' + (add_cnt) + '</span>' + '</h4>' + '<div class="inside" style="">';var add_new_append = '</div></div>';jQuery(function () {            
            jQuery('.postbox').find('.handlediv').click(function () {
                jQuery(this).siblings().find('.inside').show();
            });
        });
        function handlePeriodDayChange(input) {
            if (input.value < 0)
                input.value = 0;
            if (input.value > 30)
                input.value = 30;
        }
        var new_flag = 0;
        $('#btn-add-new').click(function (e) {
            new_flag = 1;
            e.preventDefault();
            var add_new = "<table class='add_new tbl_add_new table-space'> " + "<tbody>" + "<tr style='border: none !important;'>" + select_class + "</tr><tr class=''>" + "<td><span>One-time Payment</span></td><td><select name='plan_fee[]' class='plan_fee' >" + select_fee + "</select></td>" + "</tr><tr class='add_new'>" + select_plan + "</tr><tr class=''>" + "<td><span>Modal Title</span></td>" + "<td><input name=\"modal_title[]\" value=\"\"><br></td>" + "</tr><tr class=''>" + "<td><span>Logo URL</span></td>" + "<td><input name=\"logo_url[]\" style='margin: 8px 0;' value=\"\"></td>" + "</tr>" + "<tr><td>" + "</td><td>" + "<button class='btn btn-success add_new_pnp' value=\"Add\" type=\"button\" name=\"add\">Add</button>" + "<button class='btn btn-danger remove_new_pnp' style='display: none;' value=\"Delete\" type=\"button\" name=\"delete\">Delete</button>" + "</td></tr>" + "</tbody>" + "</table>";console.log(add_new);$("#myModal").fadeIn('fast');$('.div-add-new').html(add_new);$('.add_new_pnp').unbind('click');$('.add_new_pnp').click(function (e) {
                var btn = $('.div-add-new').find('.add_new_button_class').val();
                if (btn.trim() !== '') {
                    e.preventDefault();
                    $('#myModal').fadeOut('fast');
                    $('.div-add-new').find('.add_new_pnp').remove();
                    $('.div-add-new').find('.remove_new_pnp').css('display', 'block');
                    var new_button = $('.div-add-new').find('.add_new_button_class').val();
                    add_new_prepend = '<div id="add_new_' + (add_cnt) + '" class="postbox"> ' + '<button type="button" class="handlediv" aria-expanded="false">' + '<span class="screen-reader-text">Toggle panel: add_new_' + (add_cnt) + '</span>' + '<span class="toggle-indicator dashicons dashicons-arrow-down" aria-hidden="true"></span>' + '</button>' + '<h4 class="hndle ui-sortable-handle handle-heading">' + '<span>' + new_button + '</span>' + '</h4>' + '<div class="inside" style="">';
                    add_new_append = '</div></div>';
                    $('.div-add-new-data').append(add_new_prepend + add_new_append);
                    $('.div-add-new').find('.tbl_add_new').appendTo($('#add_new_' + (add_cnt)).find('.inside'));
                    add_cnt++;
                    jQuery('.postbox').find('.handlediv').unbind('click');
                    jQuery('.postbox').find('.handle-heading').unbind('click');
                    jQuery('.postbox').find('.handlediv').click(function () {
                        jQuery(this).siblings('.inside').slideToggle('fast');});jQuery('.postbox').find('.handle-heading').click(function () {
                        jQuery(this).siblings('.inside').slideToggle('fast');});} else {
                    alert("Select a button to add");
                }
            });
            $('.remove_new_pnp').click(function (e) {
                e.preventDefault();
                var cnf = confirm('Are you sure to remove Plan / Payment');
                if (cnf) {
                    $(this).parent().parent().parent().parent().parent().parent().remove();
                }
            });
        });
        $("#myModal").click(function () {
            $(this).fadeOut("fast");
        }).children().click(function (e) {
            return false;
        });
        $('.btn-remove').click(function (e) {
            e.preventDefault();
            var cnf = confirm('Are you sure to remove Plan / Payment');
            if (cnf) {
                $(this).parent().parent().parent().parent().parent().parent().remove();
            }
        });
        $(".update_option").click(function () {
            $(".counter").val($(this).data('cnt'));
            return true;
        });
        $(".c_plan_interval").change(function () {
            if ($(this).val() == 'custom') {
                $(this).siblings('.c_plan_custom_interval').show();
            } else {
                $(this).siblings('.c_plan_custom_interval').hide();
            }
        });
        $("#stripe_plan_trial_select").change(function () {
            if ($(this).val() == 'custom') {
                $('.stripe_plan_trial_custom_data').show();
                $('.interval-info').show();
            } else {
                $('.stripe_plan_trial_custom_data').hide();
                $('.interval-info').hide();
            }
        });</script>

    <?php
}

/* catch stripe_config plugin settings */

function catch_stripe_config() {
    //load database class
    global $wpdb;
    //load Stripe library
    require_once( 'includes/Stripe.php' );

    //print_r($_POST);
    //change stripe secret key and token
    if (isset($_POST['stripe_api_keys']) && $_POST['stripe_api_keys'] == 'live') {
        $mode = 'live';
    } else {
        $mode = 'test';
    }
    if (isset($mode)) {
        if (isset($_POST['stripe_api_key_class']) && isset($_POST['stripe_key_form']) && isset($_POST['stripe_api_key_class_test']) && isset($_POST['stripe_key_form_test'])) {
            $stripe_keys = array(
                'active_mode' => $mode,
                'live_publishable' => $_POST['stripe_api_key_class'],
                'live_secret' => $_POST['stripe_key_form'],
                'test_publishable' => $_POST['stripe_api_key_class_test'],
                'test_secret' => $_POST['stripe_key_form_test']
            );
            update_option('stripe_config_keys', $stripe_keys);
            if ($mode == 'live') {
                $update_publish_key = $_POST['stripe_api_key_class'];
                $update_secret_key = $_POST['stripe_key_form'];
            } elseif ($mode == 'test') {
                $update_publish_key = $_POST['stripe_api_key_class_test'];
                $update_secret_key = $_POST['stripe_key_form_test'];
            }
        }
    }
    if (isset($update_publish_key) && $update_publish_key != '' && isset($update_secret_key) && $update_secret_key != '') {
        $check_duplicat = $wpdb->get_row("SELECT * FROM  wp_stripe_config", ARRAY_A);
        //if secret exist -> update data ! need only one row in database
        if (empty($check_duplicat)) {
            $wpdb->insert(
                    'wp_stripe_config', array(
                'stripe_secret' => $update_publish_key,
                'stripe_key' => $update_secret_key,
                'is_active' => 'Y'
                    ), array(
                '%s',
                '%s',
                '%s'
                    )
            );
            $_SESSION['stripe_plugin']['success'] = 'Stripe config change successfully';
            //echo $wpdb->insert_id;
        } else {
            $wpdb->update(
                    'wp_stripe_config', array(
                'stripe_secret' => $update_publish_key,
                'stripe_key' => $update_secret_key
                    ), array('id' => $check_duplicat['id']), array(
                '%s',
                '%s'
                    ), array('%d')
            );
            $_SESSION['stripe_plugin']['success'] = 'Stripe config change successfully';
        }
    }

    if (isset($_POST['add'])) {
        $cnt = 1;
        $sql_stripe_option_delete = 'DELETE FROM wp_options where option_name like "stripe_config_options_%"';
        $wpdb->query($sql_stripe_option_delete);
        $arr_global = array();
        foreach ($_POST as $key => $value) {
            foreach ($value as $k => $v) {
                $arr_global[$k][$key] = $v;
            }
        }
        foreach ($arr_global as $data_key => $data_value) {
            $insert_option = 'stripe_config_options_' . $cnt;
            add_option($insert_option, json_encode($data_value), "", false);
            $cnt = $cnt + 1;
        }
    }

    if (isset($_POST['stripe_funnel'])) {
        if (isset($_POST['edit_id_funnel'])) {
            $total = $_POST['edit_id_funnel'];
        } else {
            $sql_funnel_option = 'SELECT * from wp_options where option_name like "stripe_config_options_%"';
            $funnel_options = $wpdb->get_results($sql_funnel_option);
            $total = count($funnel_options);
            $total = $total + 1;
        }
        $insert_option = 'stripe_config_options_' . $total;
        $data_value = array();
        $data_value['button_class'] = $_POST['funnel_button_class'];
        $data_value['plan_fee'] = $_POST['funnel_plan_fee'];
        $data_value['modal_popup'] = $_POST['funnel_plan'];
        $data_value['modal_title'] = $_POST['funnel_modal_title'];
        $data_value['logo_url'] = $_POST['funnel_logo_url'];
        update_option($insert_option, json_encode($data_value), "", false);
    }

    //create Stripe page
    if (isset($_POST['stripe_pay_page'])) {
        $s_page = htmlspecialchars($_POST['stripe_pay_page']);
        $s_fee = intval($_POST['stripe_fee_id']);
        $s_plan = intval($_POST['stripe_plan_id']);
        if (empty($s_fee)) {
            $s_fee = null;
        }
        if (empty($s_plan)) {
            $s_plan = null;
        }
        if (!empty($_POST['funnel'])) {
            $funnel = htmlspecialchars($_POST['funnel']);
        } else {
            $funnel = 'N';
        }

        $sql = "SELECT * from wp_stripe_page WHERE page='{$s_page}'";
        $stripe_page = $wpdb->get_results($sql);

        if (empty($stripe_page)) {
            $wpdb->insert(
                    'wp_stripe_page', array(
                'page' => $s_page,
                'fee_id' => $s_fee,
                'plan_id' => $s_plan,
                'is_funnel' => $funnel
                    ), array(
                '%s',
                '%d',
                '%d',
                '%s'
                    )
            );
            //echo plan create successfully
            $_SESSION['stripe_plugin']['success'] = 'Stripe page create successfully';
        } else {
            $_SESSION['stripe_plugin']['error'] = 'Stripe page already exist!';
        }
    }

    //create new Plan
    if (isset($_POST['stripe_plan_name'])) {
        $plan_id = htmlspecialchars($_POST['stripe_plan_id']);
        $plan_name = htmlspecialchars($_POST['stripe_plan_name']);
        $amount_subscription = htmlspecialchars($_POST['stripe_plan_amount']);
        $page_use = htmlspecialchars($_POST['stripe_plan_page']);
        $page_redirect = htmlspecialchars($_POST['stripe_redirect_page']);
        $desc = htmlspecialchars($_POST['stripe_plan_desc']);
        $stripe_plan_trial_select = $_POST['stripe_plan_trial_select'];

        if ($stripe_plan_trial_select == 'custom') {
            $stripe_plan_trial_select = $_POST['stripe_plan_custom_trial'];
        } else {
            $stripe_plan_trial_select = $_POST['stripe_plan_trial_select'];
        }
        $plan_interval = 'month';
        $plan_interval_count = '0';
        switch ($stripe_plan_trial_select) {
            case "1":
                $plan_interval = 'day';
                break;
            case "7":
                $plan_interval = 'week';
                break;
            default:
                $plan_interval = 'month';
                $plan_interval_count = '0';
                break;
        }
        $plan_divide_month = (int) $stripe_plan_trial_select / 30;
        $plan_divide_month_reminder = (int) $stripe_plan_trial_select % 30;
        if (((int) $plan_divide_month_reminder) == 0) {
            $plan_interval_count = (string) $plan_divide_month;
        }
        //echo $plan_interval . ' - ' . $plan_interval_count; exit;

        $amount_subscription = (int) preg_replace('/[^0-9]/', '', $amount_subscription);


        if (!empty($_POST['stripe_plan_trial'])) {
            $trial = $_POST['stripe_plan_trial'];
        } else {
            $trial = 0;
        }
        
        if(isset($_POST['edit_id_plan']) && $_POST['edit_id_plan'] != ''){
            $stripe = new Stripe();
            $database_plan_id = $_POST['edit_id_plan'];
            $plan_update_data = array(
                "name" => $plan_name,
                "trial_period_days" => $trial,
                "statement_descriptor" => $desc
            );

            //echo '<pre>'; print_r($plan_update_data); exit;
            //update plan in stripe API
            $update_plan = $stripe->updatePlan($plan_update_data, $plan_id);
            //update plan in database
            if (isset($update_plan['name'])) {
                $update_database_plan = $wpdb->update(
                        'wp_stripe_plan', array(
                    'plan_name' => $plan_name,
                    'plan_trial' => $trial,
                    'redirect_page' => $page_redirect,
                    'description' => $desc
                        ), array('id' => $database_plan_id), array(
                    '%s',
                    '%d',
                    '%s',
                    '%s'
                        ), array('%d')
                );
                if ($update_database_plan !== FALSE) {
                    $_SESSION['stripe_plugin']['success'] = 'Stripe plan update successfully';
                } else {
                    $_SESSION['stripe_plugin']['error'] = 'Stripe plan update error';
                }
            } else {
                $_SESSION['stripe_plugin']['error'] = 'Stripe plan update error ';
            }
        } else {

                //create subscription
                $new_plan = new Stripe();
                //check if plane exist
                $check_plan = $new_plan->getPlan($plan_id);


                //if plan not exist
                if (isset($check_plan['error'])) {
                    $new_plan->url .= 'plans';
                    $plan_data = array(
                        "name" => $plan_name,
                        "id" => $plan_id,
                        "interval" => $plan_interval,
                        "currency" => "usd",
                        "amount" => $amount_subscription,
                        "trial_period_days" => $trial,
			"statement_descriptor" => $desc
                    );
                    if ($plan_interval_count > 0) {
                        $plan_data["interval_count"] = $plan_interval_count;
                    }

                    //create new plan
                    $plan = $new_plan->createPlan($plan_data);                    
                    if (isset($plan['id'])) {
						
                        //save plan in database
                        $wpdb->insert(
                                'wp_stripe_plan', array(
                            'plan_price' => $amount_subscription,
                            'plan_interval' => $stripe_plan_trial_select,
                            'plan_trial' => $trial,
                            'plan_name' => $plan_name,
                            'plan_id' => $plan_id,
                            'description' => $desc,
                            'redirect_page' => $page_redirect,
                            'active' => 'Y'
                                ), array(
                            '%d',
                            '%d',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s'
                                )
                        );
                        $l_plan_id = $wpdb->insert_id;
                        
                        if ($l_plan_id) {
                            $_SESSION['stripe_plugin']['success'] = 'Stripe plan create successfully';
                        } else {
                            $_SESSION['stripe_plugin']['error'] = 'Stripe plan create error';
                        }
                    } else {                        
                        //$plan to log
                        $_SESSION['stripe_plugin']['error'] = 'Stripe plan create error';
                    }
                } else {
                    $_SESSION['stripe_plugin']['error'] = 'Stripe plan already exist';
                }   
        }
        
    }


    //update plan
    if (isset($_POST['database_plan_id'])) {
        
    }

    //delete plan
    if (isset($_POST['del_plan_id'])) {
        $plan_id_database = $_POST['del_plan_id'];
        $stripe_plan_id = $_POST['del_plan_stripe_id'];

        $stripe = new Stripe();
        $delete_plan = $stripe->deletePlan($stripe_plan_id);

        if (isset($delete_plan['deleted'])) {
            //api delete plan -> then delete in database
            if ($delete_plan['deleted'] == true) {
                $del_plan = $wpdb->delete('wp_stripe_plan', array('id' => $plan_id_database), array('%d'));
                if ($del_plan) {
                    $_SESSION['stripe_plugin']['success'] = 'Stripe plan delete successfully';
                } else {
                    $_SESSION['stripe_plugin']['error'] = 'Stripe plan delete error';
                }
            } else {
                $_SESSION['stripe_plugin']['error'] = 'Stripe plan delete error';
            }
        } else {
            //save to log file $delete_plan if delete error
            $_SESSION['stripe_plugin']['error'] = 'Stripe plan delete error';
        }
    }

    //add product and page price
    if (isset($_POST['stripe_pay_amount']) && !isset($_POST['edit_id_payment'])) {
        $fee = htmlspecialchars($_POST['stripe_pay_amount']);
        $page_use = htmlspecialchars($_POST['stripe_pay_page_r']);
        $fee_description = htmlspecialchars($_POST['stripe_pay_desc']);
        $fee_name = htmlspecialchars($_POST['stripe_pay_name']);
        $redirect_page = htmlspecialchars($_POST['stripe_pay_redirect_page']);
        $redirect_page = '';
        $fee = (int) preg_replace('/[^0-9]/', '', $fee);
        //check plan in page -> only one plan in page
//        $sql = "SELECT * from wp_stripe_fee WHERE page_use='{$page_use}'";
//        $check_fee_page = $wpdb->get_results($sql);
//        if (empty($check_fee_page)) {
        $new_fee = $wpdb->insert(
                'wp_stripe_fee', array(
            'name' => $fee_name,
            'fee_amount' => $fee,
            'redirect_page' => $redirect_page,
            'description' => $fee_description,
            'active' => 'Y'
                ), array(
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
                )
        );
        if ($new_fee) {

            $l_fee_id = $wpdb->insert_id;

            $update_stripe_page = $wpdb->update(
                    'wp_stripe_page', array(
                'fee_id' => $l_fee_id,
                    ), array('id' => $page_use), array(
                '%d',
                    ), array('%d')
            );
            if ($update_stripe_page) {
                $_SESSION['stripe_plugin']['success'] = 'Stripe fee created successfully';
            } else {
                $_SESSION['stripe_plugin']['error'] = 'Stripe fee created error';
            }
        } else {
            $_SESSION['stripe_plugin']['error'] = 'Stripe fee page already in use';
        }
//        }
    }

    //update Stripe price
    if (isset($_POST['edit_id_payment'])) {
        $fee_id = $_POST['edit_id_payment'];
        $fee = htmlspecialchars($_POST['stripe_pay_amount']);
        //$page_use = htmlspecialchars($_POST['stripe_pay_page_r']);
        $fee_description = htmlspecialchars($_POST['stripe_pay_desc']);
        $fee_name = htmlspecialchars($_POST['stripe_pay_name']);
        $redirect_page = htmlspecialchars($_POST['stripe_pay_redirect_page']);
        $redirect_page = '';
        $fee = (int) preg_replace('/[^0-9]/', '', $fee);

        //check plan in page -> only one plan in page
//        $sql = "SELECT * from wp_stripe_fee WHERE page_use='{$page_use}' AND  id NOT IN ({$fee_id})";
//        $check_fee_page = $wpdb->get_results($sql);
//        if (empty($check_fee_page)) {
        //update price
        $update_fee = $wpdb->update(
                'wp_stripe_fee', array(
            'name' => $fee_name,
            'fee_amount' => $fee,
//                'page_use' => $page_use,
            'redirect_page' => $redirect_page,
            'description' => $fee_description
                ), array('id' => $fee_id), array(
            '%s',
            '%d',
//                '%s',
            '%s',
            '%s'
                ), array('%d')
        );

        if ($update_fee) {
            $_SESSION['stripe_plugin']['success'] = 'Payment successfully update';
            //eche message ->> fee successfully update
        } else {
            $_SESSION['stripe_plugin']['error'] = 'Payment update error';
        }
//        } else {
//            $_SESSION['stripe_plugin']['error'] = 'One-time Payment page already in use';
//        }
    }

    //delete Stripe price
    if (isset($_POST['del_fee_id'])) {
        $fee_id = $_POST['del_fee_id'];

        $del_fee = $wpdb->delete('wp_stripe_fee', array('id' => $fee_id), array('%d'));
        if ($del_fee) {
            $_SESSION['stripe_plugin']['success'] = 'Payment deleted successfully';
        } else {
            $_SESSION['stripe_plugin']['error'] = 'Payment deleted error';
        }
    }

    //delete Stripe page
    if (isset($_POST['d_page_id'])) {
        $d_page_id = intval($_POST['d_page_id']);
        $del_page = $wpdb->delete('wp_stripe_page', array('id' => $d_page_id), array('%d'));
        if ($del_page) {
            $_SESSION['stripe_plugin']['success'] = 'Page deleted successfully';
        } else {
            $_SESSION['stripe_plugin']['error'] = 'Page deleted error';
        }
    }

    //update Stripe page
    if (isset($_POST['update_page_name'])) {
        $u_page_id = intval($_POST['update_page_id']);
        $u_page_name = htmlspecialchars($_POST['update_page_name']);

        $update_page = $wpdb->update(
                'wp_stripe_page', array(
            'page' => $u_page_name,
                ), array('id' => $u_page_id), array(
            '%s'
                ), array('%d')
        );

        if ($update_page === FALSE) {
            $_SESSION['stripe_plugin']['error'] = 'Page update error';
            //eche message ->> fee successfully update
        } else {
            $_SESSION['stripe_plugin']['success'] = 'Page successfully update';
        }
    }
    if (isset($_POST) && !empty($_POST) && isset($_POST['stripe_webhook'])) {
        $stripe_webhook_data = get_option('stripe_webhook_data', array());

        $arr_update_data = array();

        if (isset($_POST['stripe_id']) && $_POST['stripe_id'] != '')
            $arr_update_data['id'] = 1;
        else
            $arr_update_data['id'] = 0;
        
        if (isset($_POST['stripe_name']) && $_POST['stripe_name'] != '')
            $arr_update_data['name'] = 1;
        else
            $arr_update_data['name'] = 0;

        if (isset($_POST['stripe_email']) && $_POST['stripe_email'] != '')
            $arr_update_data['email'] = 1;
        else
            $arr_update_data['email'] = 0;

        if (isset($_POST['stripe_description']) && $_POST['stripe_description'] != '')
            $arr_update_data['description'] = 1;
        else
            $arr_update_data['description'] = 0;

        if (isset($_POST['stripe_address']) && $_POST['stripe_address'] != '') {
            $arr_update_data['address_line1'] = 1;
            $arr_update_data['address_line2'] = 1;
        } else {
            $arr_update_data['address_line1'] = 0;
            $arr_update_data['address_line2'] = 0;
        }
        if (isset($_POST['stripe_city']) && $_POST['stripe_city'] != '')
            $arr_update_data['address_city'] = 1;
        else
            $arr_update_data['address_city'] = 0;

        if (isset($_POST['stripe_state']) && $_POST['stripe_state'] != '')
            $arr_update_data['address_state'] = 1;
        else
            $arr_update_data['address_state'] = 0;

        if (isset($_POST['stripe_country']) && $_POST['stripe_country'] != '')
            $arr_update_data['address_country'] = 1;
        else
            $arr_update_data['address_country'] = 0;

        if (isset($_POST['stripe_zipcode']) && $_POST['stripe_zipcode'] != '')
            $arr_update_data['address_zip'] = 1;
        else
            $arr_update_data['address_zip'] = 0;

        $stripe_webhook = $wpdb->prefix . 'stripe_webhook';
        if (isset($_POST['modify_stripe_webhook_data_id'])) {
            $update = $wpdb->update($stripe_webhook, array(
                'webhook_id' => isset($_POST['modify_stripe_webhook_id']) ? $_POST['modify_stripe_webhook_id'] : '',
                'webhook_url' => isset($_POST['stripe_send_url']) ? $_POST['stripe_send_url'] : '',
                'webhook_data' => serialize($arr_update_data)
                    ), array('id' => $_POST['modify_stripe_webhook_data_id']), array('%s', '%s', '%s')
            );
            if ($update === FALSE) {
                $_SESSION['stripe_plugin']['error'] = 'Webhook update error';                
            } else {
                $_SESSION['stripe_plugin']['success'] = 'Webhook successfully updated';
            }
        } else {
            $insert = $wpdb->insert($stripe_webhook, array(
                'webhook_id' => isset($_POST['modify_stripe_webhook_id']) ? $_POST['modify_stripe_webhook_id'] : '',
                'webhook_url' => isset($_POST['stripe_send_url']) ? $_POST['stripe_send_url'] : '',
                'webhook_data' => serialize($arr_update_data)
                    ), array('%s', '%s', '%s')
            );
            if ($insert) {
                $_SESSION['stripe_plugin']['success'] = 'Webhook created successfully';                
            } else {
                $_SESSION['stripe_plugin']['error'] = 'Webhook not created';
            }
        }
    }

    //Save funnel
    if (isset($_POST['funnel-edit-add-form-hidden'])) {
        $funnel_title = isset($_POST['funnel-title']) ? $_POST['funnel-title'] : '';
        $checkout_page = isset($_POST['funnel-checkout-page']) ? $_POST['funnel-checkout-page'] : '';
        $checkout_class = isset($_POST['funnel-checkout-class']) ? $_POST['funnel-checkout-class'] : '';
        $checkout_app = isset($_POST['funnel-checkout-app-hidden']) ? serialize($_POST['funnel-checkout-app-hidden']) : '';
        $step_2_page = isset($_POST['funnel-step_2-page']) ? $_POST['funnel-step_2-page'] : '';
        $step_2_class = isset($_POST['funnel-step_2-class']) ? $_POST['funnel-step_2-class'] : '';
        $step_2_app = isset($_POST['funnel-step_2-app-hidden']) ? serialize($_POST['funnel-step_2-app-hidden']) : '';
        $funnel_step = isset($_POST['funnel_step']) ? $_POST['funnel_step'] : '';
        $new_arr = array();
        if ($funnel_step) {
            $nu = 3;
            foreach ($funnel_step as $single_funnel) {
                $new_arr[$nu] = $single_funnel;
                $nu++;
            }
        }
        $funnel_step = serialize($new_arr);
        $stripe_funnel = $wpdb->prefix . 'stripe_funnel';
        if (isset($_POST['edit_id_funnel'])) {
            $update = $wpdb->update($stripe_funnel, array(
                'funnel_title' => $funnel_title,
                'checkout_page' => $checkout_page,
                'checkout_class' => $checkout_class,
                'checkout_app' => $checkout_app,
                'step_2_page' => $step_2_page,
                'step_2_class' => $step_2_class,
                'step_2_app' => $step_2_app,
                'funnel_step' => $funnel_step
                    ), array('id' => $_POST['edit_id_funnel']), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            if ($update === FALSE) {
                $_SESSION['stripe_plugin']['error'] = 'Funnel not updated successfully';
            } else {
                $_SESSION['stripe_plugin']['success'] = 'Funnel updated successfully';
            }
        } else {
            $insert = $wpdb->insert($stripe_funnel, array(
                'funnel_title' => $funnel_title,
                'checkout_page' => $checkout_page,
                'checkout_class' => $checkout_class,
                'checkout_app' => $checkout_app,
                'step_2_page' => $step_2_page,
                'step_2_class' => $step_2_class,
                'step_2_app' => $step_2_app,
                'funnel_step' => $funnel_step
                    ), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            if ($insert === FALSE) {
                $_SESSION['stripe_plugin']['error'] = 'Funnel not created';
            } else {
                $_SESSION['stripe_plugin']['success'] = 'Funnel created successfully';
            }
        }
    }
}

add_action('init', 'catch_stripe_config');



//SESSION START
add_action('init', 'myStartSession', 1);

function myStartSession() {
    if (!session_id()) {
        session_start();
    }
}

//Stripe payment request catch
function strip_request_catch() {
    //load Stripe library
    require_once( 'includes/Stripe.php' );
    require_once( 'includes/sendStripeData.php' );

    //Stripe mupltiply page payment request get
    if (isset($_POST['m_page_type'])) {
        $webhook_id = isset($_POST['m_webhook_id']) ? $_POST['m_webhook_id'] : '';
        if (empty($_SESSION['pp_users']['stripe']['subscription'])) {
            $payAmount = intval($_POST['m_amount']);
            $mRedirect = htmlspecialchars($_POST['m_redirect']);
            $mPlanId = htmlspecialchars($_POST['m_sub_id']);
            $mPlanName = htmlspecialchars($_POST['m_sub_name']);
            $mPlanAmount = intval($_POST['m_sub_amount']);
            $mPlanTrial = intval($_POST['m_sub_trial']);
            $mPlanDesc = htmlspecialchars($_POST['m_sub_desc']);
            $mFunnel = $_POST['m_funnel'];
            //create customer
            $cus = new Stripe();
            $cus->url .= 'customers';
            $cus->fields['email'] = $_POST['stripeEmail'];
            $cus->fields['source'] = $_POST['stripeToken'];
            $customer = $cus->call();

            //object to API - to create user
            $userToApi = array();

            if (!empty($payAmount)) {
                //create payment request
                $pay = new Stripe();
                $pay->url .= 'charges';
                $param = array(
                    'amount' => $payAmount,
                    'currency' => 'usd',
                    'customer' => $customer['id'],
                    'description' => $mPlanDesc
                );
                $charge = $pay->charge($param);
                $userToApi['one_time_payment'] = $charge;
            } else {
                $userToApi['one_time_payment'] = '';
            }
            //add customer to Api request
            $userToApi['user'] = $customer;

            //if payment do not have PLAN
            if (!empty($mPlanId)) {
                if ($charge['status'] == 'succeeded') {
                    $_SESSION['pp_users']['stripe']['customer'] = $customer;
                    //log error Payment OK ->>> And send API Request with payment parameters
                    //if no plan stop
                    // $customerData = $cus->getCustomer($customer['id']);
                    $thirdApi = new sendStripeData();
                    $customerSend = $thirdApi->sendCustomerPayment('user/stripe', $userToApi, $webhook_id);
                    //if customerSend => true --- send data to API request!!!
                    //$tt = json_encode($userToApi);

                    if (empty($mPlanId)) {
                        ?>
                        <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                            var uri = "<?php echo $mRedirect; ?>";
                            window.location.href = base_url + uri;</script>
                        <?php
                    }
                }
            }
            //} else {
            //if plan payment exist


            if (!empty($mPlanId)) {
                if (isset($customer['id'])) {


                    //create subscription
                    $new_plan = new Stripe();
                    //check if plane exist
                    $check_plan = $new_plan->getPlan($mPlanId);
                    //if plan not exist
                    if (isset($check_plan['error'])) {
                        $new_plan->url .= 'plans';
                        $plan_data = array(
                            "name" => $mPlanName,
                            "id" => $mPlanId,
                            "interval" => "month",
                            "currency" => "usd",
                            "amount" => $mPlanAmount,
                            "trial_period_days" => $mPlanTrial
                        );
                        //create new plan
                        $plan = $new_plan->createPlan($plan_data);


                        if (isset($plan['id'])) {
                            //create new subscription
                            $sub = new Stripe();
                            $sub->url .= 'subscriptions';
                            $data = array(
                                "plan" => $mPlanId,
                                "customer" => $customer['id'],
                                "trial_period_days" => $mPlanTrial
                            );
                            $response = $sub->subscription($data);
                            //add customer data to api request
                            $userToApi['user_subscription'] = $response;

                            if (isset($response['id'])) {
                                if ($mFunnel == 'Y') {
                                    $_SESSION['pp_users']['stripe']['subscription'] = $response;
                                    $_SESSION['pp_users']['stripe']['customer_id'] = $customer['id'];
                                }

                                $thirdApi = new sendStripeData();
                                $customerSend = $thirdApi->sendCustomerPayment('user/stripe', $userToApi, $webhook_id);
                                //if customerSend => true --- send data to API request!!!
                                //redirect if Payment Successfully
                                ?>
                                <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                    var uri = "<?php echo $mRedirect; ?>";
                                    window.location.href = base_url + uri;</script>
                                <?php
                            } else {

                                //if payment fals ->>>> do log #response
                            //
									}
                        }
                    } else {

                        // if plan exist
                        if (isset($check_plan['id'])) {
                            $sub = new Stripe();
                            $sub->url .= 'subscriptions';
                            $data = array(
                                "plan" => $check_plan['id'],
                                "customer" => $customer['id'],
                                "trial_period_days" => $mPlanTrial
                            );
                            $response = $sub->subscription($data);


                            $userToApi['user_subscription'] = $response;


                            $thirdApi = new sendStripeData();
                            $customerSend = $thirdApi->sendCustomerPayment('user/stripe', $userToApi, $webhook_id);
                            //if customerSend => true --- send data to API request!!!



                            if (isset($response['id'])) {

                                if ($mFunnel == 'Y') {
                                    //if sybscription create -> put description id to $_SESSION -> need in next step
                                    $_SESSION['pp_users']['stripe']['subscription'] = $response;
                                    $_SESSION['pp_users']['stripe']['customer_id'] = $customer['id'];
                                }


                                //if payment Successfully
                                ?>
                                <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                    var uri = "<?php echo $mRedirect; ?>";
                                    window.location.href = base_url + uri;</script>
                                <?php
                            } else {
                                //if payment fals ->>> do log file or message $response
                            }
                        }
                    }
                } else {
                    //Log error (or Plan id do not exist or Payment charge false)
                }


                //}
            }
        } else {
            //if funnel exist
            if (isset($_POST['m_funnel'])) {
                $plan_id = htmlspecialchars($_POST['new_plan_name']);
                $subscription_id = $_POST['m_sub_id'];
                $plan_name = htmlspecialchars($_POST['m_sub_name']);
                $amount_plan = $_POST['m_sub_amount'];
                $redirect = $_POST['m_redirect'];
                $customer_id = $_POST['m_customer_id'];

                $payAmount = intval($_POST['m_amount']);
                $mRedirect = htmlspecialchars($_POST['m_redirect']);
                $mPlanName = htmlspecialchars($_POST['m_sub_name']);
                $mPlanAmount = intval($_POST['m_sub_amount']);
                $mPlanTrial = intval($_POST['m_sub_trial']);
                $mPlanDesc = htmlspecialchars($_POST['m_sub_desc']);
                $mFunnel = $_POST['m_funnel'];

                //object to API - to create user
                $userToApi = array();

                if (!empty($payAmount)) {


                    if (!empty($customer_id)) { //if isset $customer_id
                        //create payment request
                        $pay = new Stripe();
                        $pay->url .= 'charges';
                        $param = array(
                            'amount' => $payAmount,
                            'currency' => 'usd',
                            'customer' => $customer_id,
                            'description' => $mPlanDesc
                        );
                        $charge = $pay->charge($param);
                    } else {
                        //if customer do not exist -> create
                        //create payment request
                        $pay = new Stripe();
                        $pay->url .= 'charges';
                        $param = array(
                            'amount' => $payAmount,
                            'currency' => 'usd',
                            'customer' => $_SESSION['pp_users']['stripe']['customer_id'],
                            'description' => $mPlanDesc
                        );
                        $charge = $pay->charge($param);
                    }



                    //if payment do not have PLAN
                    if ($charge['status'] == 'succeeded') {

                        //add customer to Api request
                        $userToApi['user'] = $_SESSION['pp_users']['stripe'];

                        $thirdApi = new sendStripeData();
                        $customerSend = $thirdApi->sendCustomerPayment('user/stripe_update', $userToApi, $webhook_id);
                        //log error Payment OK ->>> And send API Request with payment parameters
                        //Log or send api request payment succeeded
                        //if plan do not exist
                        if (empty($plan_id)) {
                            if ($mFunnel == 'N') {
                                unset($_SESSION['pp_users']);
                            }
                            ?>


                            <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                var uri = "<?php echo $mRedirect; ?>";
                                window.location.href = base_url + uri;</script>
                            <?php
                        }
                    }
                }
                //if no new plan id stop
                if (!empty($plan_id)) {
                    if (!empty($subscription_id)) {

                        $new_plan = new Stripe();
                        //check if plane exist
                        $check_plan = $new_plan->getPlan($plan_id);

                        if (isset($check_plan['error'])) {
                            //if no plan -> create new
                            $new_plan->url .= 'plans';


                            $plan_data = array(
                                "name" => $plan_name,
                                "id" => $plan_id,
                                "interval" => "month",
                                "currency" => "usd",
                                "amount" => $amount_plan,
                                "trial_period_days" => 30
                            );
                            //create new plan
                            $plan = $new_plan->createPlan($plan_data);


                            if (isset($plan['id'])) {

                                $new_sub = new Stripe();
                                $new_sub->url .= 'subscriptions/' . $subscription_id;
                                $sub_param = array(
                                    "plan" => $plan_id,
                                    "trial_end" => time() + 2592000, //one month
                                );
                                $update_subscription = $new_sub->subscriptionUpdate($sub_param);

                                //if plan update successfully
                                if (isset($update_subscription['id'])) {

                                    //add customer data to api request
                                    $userToApi['user_subscription'] = $update_subscription;
                                    $thirdApi = new sendStripeData();
                                    $customerSend = $thirdApi->sendCustomerPayment('user/stripe_update', $userToApi, $webhook_id);
                                    //destroy Stripe subscription session
                                    if ($mFunnel == 'N') {
                                        unset($_SESSION['pp_users']);
                                    }
                                    ?>
                                    <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                        var uri = "<?php echo $redirect; ?>";
                                        window.location.href = base_url + uri;</script>
                                    <?php
                                } else {
                                    //if plan update fail
                                    //log error in $update_subscription
                                }
                            }
                        } else {
                            //if plan exist
                            if (isset($check_plan['id'])) {
                                $new_sub = new Stripe();
                                $new_sub->url .= 'subscriptions/' . $subscription_id;
                                $sub_param = array(
                                    "plan" => $check_plan['id'], //$plan_id
                                    "trial_end" => time() + 2592000, //one month
                                );
                                $update_subscription = $new_sub->subscriptionUpdate($sub_param);


                                //if plan update successfully
                                if (isset($update_subscription['id'])) {
                                    //desctroy Stripe subscription session
                                    if ($mFunnel == 'N') {
                                        unset($_SESSION['pp_users']);
                                    }
                                    ?>
                                    <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                        var uri = "<?php echo $redirect; ?>";
                                        window.location.href = base_url + uri;</script>
                                    <?php
                                } else {
                                    //if plan update fail
                                    //log error in $update_subscription
                                }
                            }
                        }
                    } else {
                        //if subscription do not exist
                        //create new subscription
                        //create customer
                        $cus = new Stripe();
                        $cus->url .= 'customers';
                        $cus->fields['email'] = $_POST['stripeEmail'];
                        $cus->fields['source'] = $_POST['stripeToken'];
                        $customer = $cus->call();


                        //check if plane exist
                        $new_plan = new Stripe();
                        $check_plan = $new_plan->getPlan($plan_id);


                        if (isset($check_plan['error'])) {
                            $new_plan->url .= 'plans';
                            $plan_data = array(
                                "name" => $plan_name,
                                "id" => $plan_id,
                                "interval" => "month",
                                "currency" => "usd",
                                "amount" => $amount_plan,
                                "trial_period_days" => 30
                            );
                            //create new plan
                            $plan = $new_plan->createPlan($plan_data);


                            if (isset($plan['id'])) {
                                //create new subscription
                                $sub = new Stripe();
                                $sub->url .= 'subscriptions';
                                $data = array(
                                    "plan" => $plan_id,
                                    "customer" => $customer['id'],
                                    "trial_period_days" => 30
                                );
                                $response = $sub->subscription($data);
                                if (isset($response['id'])) {

                                    $userToApi['user_subscription'] = $response;
                                    $thirdApi = new sendStripeData();
                                    $customerSend = $thirdApi->sendCustomerPayment('user/stripe_update', $userToApi, $webhook_id);

                                    //desctroy Stripe subscription session
                                    if ($mFunnel == 'N') {
                                        unset($_SESSION['pp_users']);
                                    }
                                    //redirect if Payment Successfully
                                    ?>
                                    <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                        var uri = "<?php echo $redirect; ?>";
                                        window.location.href = base_url + uri;</script>
                                    <?php
                                } else {

                                    //if payment fals ->>>> do log #response
                                //
                                                        }
                            }
                        } else {

                            // if plan exist
                            if (isset($check_plan['id'])) {
                                $sub = new Stripe();
                                $sub->url .= 'subscriptions';
                                $data = array(
                                    "plan" => $check_plan['id'],
                                    "customer" => $customer['id'],
                                    "trial_period_days" => 30
                                );
                                $response = $sub->subscription($data);




                                if (isset($response['id'])) {

                                    //send customer data to API
                                    $userToApi['user_subscription'] = $response;

                                    $thirdApi = new sendStripeData();
                                    $customerSend = $thirdApi->sendCustomerPayment('user/stripe_update', $userToApi, $webhook_id);

                                    //desctroy Stripe subscription session
                                    if ($mFunnel == 'N') {
                                        unset($_SESSION['pp_users']);
                                    }
                                    //if payment Successfully
                                    ?>
                                    <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                                        var uri = "<?php echo $redirect; ?>";
                                        window.location.href = base_url + uri;</script>
                                    <?php
                                } else {
                                    //if payment fals ->>> do log file or message $response
                                }
                            }
                        }
                    }
                }
            }
        }
    }





    //multiple page One Time Payment
    if (isset($_POST['onetime_amount'])) {
        $payAmount = intval($_POST['onetime_amount']);
        $mRedirect = htmlspecialchars($_POST['onetime_redirect']);

        $payDesc = htmlspecialchars($_POST['onetime_desc']);
        //create customer
        $cus = new Stripe();
        $cus->url .= 'customers';
        $cus->fields['email'] = $_POST['stripeEmail'];
        $cus->fields['source'] = $_POST['stripeToken'];
        $customer = $cus->call();

        //create payment request
        $pay = new Stripe();
        $pay->url .= 'charges';
        $param = array(
            'amount' => $payAmount,
            'currency' => 'usd',
            'customer' => $customer['id'],
            'description' => $payDesc
        );
        $charge = $pay->charge($param);
        if ($charge['status'] == 'succeeded') {
            //log error Payment OK ->>> And send API Request with payment parameters
            ?>
            <script type="text/javascript">var base_url = "<?php echo get_site_url(); ?>";
                var uri = "<?php echo $mRedirect; ?>";
                window.location.href = base_url + uri;</script>
            <?php
        }
    }
}

add_action('init', 'strip_request_catch');

//function add_sub_menus() {
//    add_submenu_page('stripe-config-plugin', 'Stripe Send Data', 'Stripe Send Data', 'manage_options', 'stripe-send-plan', 'stripe_send_data');
//}
//add_action('admin_menu', 'add_sub_menus', 10);

function stripe_send_data() {
    
}

/* admin panel styles */

function wpdocs_enqueue_custom_admin_style() {

    // wp_enqueue_style( 'my-style', get_template_directory_uri() . '/style.css');
}

add_action('admin_enqueue_scripts', 'wpdocs_enqueue_custom_admin_style');

function stripe_config_activate_plugin() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $prefixVal = "wp_";
    $createConfigTbl = "CREATE TABLE IF NOT EXISTS `" . $prefixVal . "stripe_config` (
						`id` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
						`stripe_secret` varchar(150) NOT NULL,
						`stripe_key` varchar(150) NOT NULL,
						`is_active` enum('Y','N') NOT NULL
						) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8";
    dbDelta($createConfigTbl);

    $createFeeTbl = "CREATE TABLE IF NOT EXISTS `" . $prefixVal . "stripe_fee` (
					  `id` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					  `fee_amount` int(11) NOT NULL,
					  `redirect_page` varchar(20) NOT NULL,
					  `description` varchar(100) NOT NULL,
					  `active` enum('Y','N') NOT NULL DEFAULT 'N'
					) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8";
    dbDelta($createFeeTbl);

    $createStripePagesTbl = "CREATE TABLE IF NOT EXISTS `" . $prefixVal . "stripe_page` (
							  `id` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
							  `page` varchar(50) NOT NULL,
							  `plan_id` int(11) DEFAULT NULL,
							  `fee_id` int(11) DEFAULT NULL,
							  `is_funnel` enum('Y','N') NOT NULL
							) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8";
    dbDelta($createStripePagesTbl);

    $createStripePlanTbl = "CREATE TABLE IF NOT EXISTS `" . $prefixVal . "stripe_plan` (
							  `id` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
							  `plan_price` float NOT NULL,
							  `plan_trial` int(10) DEFAULT NULL,
							  `plan_name` varchar(50) NOT NULL,
							  `plan_id` varchar(50) NOT NULL,
							  `description` varchar(100) NOT NULL,
							  `redirect_page` varchar(20) NOT NULL,
							  `active` enum('Y','N') NOT NULL DEFAULT 'N'
							) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8";
    dbDelta($createStripePlanTbl);

    $createStripeWebhookTbl = "CREATE TABLE IF NOT EXISTS `" . $prefixVal . "stripe_webhook` (
							  `id` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,							  
							  `webhook_id` varchar(50) NOT NULL,
							  `webhook_url` varchar(50) NOT NULL,
							  `webhook_data` varchar(255) NOT NULL,							  
							) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8";
    dbDelta($createStripeWebhookTbl);

    $configTbl = $prefixVal . "stripe_config";
    $insConfigRec = $wpdb->insert($configTbl, array('stripe_secret' => 'pk_test_Oxm6sN3ADQ2kN69GfpfBwOGK', 'stripe_key' => 'sk_test_qy8CtMDg5OWhtrLbDFIUhV7c', 'is_active' => 'Y'));

    dbDelta($insConfigRec);
}

function stripe_config_deactivate_plugin() {
    global $wpdb;
    $prefixVal = "wp_";
    $configTblRemove = $prefixVal . "stripe_config";
    $feeTblRemove = $prefixVal . "stripe_fee";
    $pagesTblRemove = $prefixVal . "stripe_page";
    $planTblRemove = $prefixVal . "stripe_plan";

    /* $wpdb->query("DROP TABLE IF EXISTS $configTblRemove");
      $wpdb->query("DROP TABLE IF EXISTS $feeTblRemove");
      $wpdb->query("DROP TABLE IF EXISTS $pagesTblRemove");
      $wpdb->query("DROP TABLE IF EXISTS $planTblRemove"); */
}

register_activation_hook(__FILE__, 'stripe_config_activate_plugin');
register_deactivation_hook(__FILE__, 'stripe_config_deactivate_plugin');

add_action('wp_ajax_sc_edit_webhook', 'sc_edit_webhook');

function sc_edit_webhook() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    if ($id != '' || $id == 0) {
        global $wpdb;
        $stripe_select_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_webhook WHERE id=' . $id;
        $stripe_webhook_data = $wpdb->get_row($stripe_select_sql, ARRAY_A);
        $data = array();
        $data['stripe_send_url'] = $stripe_webhook_data['webhook_url'];
        $data['modify_stripe_webhook_id'] = $stripe_webhook_data['webhook_id'];
        $data['modify_stripe_data'] = unserialize($stripe_webhook_data['webhook_data']);
        echo json_encode($data);
    } else {
        echo '0';
    }
    exit;
}

add_action('wp_ajax_sc_delete_webhook', 'sc_delete_webhook');

function sc_delete_webhook() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    if ($id != '' || $id == 0) {
        global $wpdb;
        $delete = $wpdb->delete($wpdb->prefix . 'stripe_webhook', array('id' => $id));        
        if ($delete) {
            echo '<div class="alert alert-success" role="alert">Webhook deleted successfully</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">Webhook delete error</div>';
        }
    } else {
        echo '<div class="alert alert-danger" role="alert">Webhook delete error</div>';
    }
    exit;
}

add_action('wp_ajax_sc_edit_funnel', 'sc_edit_funnel');

function sc_edit_funnel() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    if ($id != '' || $id == 0) {
        global $wpdb;
        $stripe_select_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_funnel WHERE id=' . $id;
        $stripe_funnel_data = $wpdb->get_row($stripe_select_sql, ARRAY_A);
        $data = array();
        $data['funnel_title'] = $stripe_funnel_data['funnel_title'];
        $data['checkout_page'] = $stripe_funnel_data['checkout_page'];
        $data['checkout_class'] = $stripe_funnel_data['checkout_class'];
        $data['checkout_app'] = unserialize($stripe_funnel_data['checkout_app']);
        $data['step_2_page'] = $stripe_funnel_data['step_2_page'];
        $data['step_2_class'] = $stripe_funnel_data['step_2_class'];
        $data['step_2_app'] = unserialize($stripe_funnel_data['step_2_app']);
        $data['funnel_step'] = unserialize($stripe_funnel_data['funnel_step']);
        echo json_encode($data);
    } else {
        echo '0';
    }
    exit;
}

add_action('wp_ajax_sc_delete_funnel', 'sc_delete_funnel');

function sc_delete_funnel() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    global $wpdb;
    $delete = $wpdb->delete($wpdb->prefix . 'stripe_funnel', array('id' => $id));
    if ($delete) {
        echo '<div class="alert alert-success" role="alert">Funnel deleted successfully</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">Funnel delete error</div>';
    }
    exit;
}

add_action('wp_ajax_sc_edit_payment', 'sc_edit_payment');

function sc_edit_payment() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    if ($id != '' || $id == 0) {
        global $wpdb;
        $stripe_select_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_fee WHERE id=' . $id;
        $stripe_webhook_data = $wpdb->get_row($stripe_select_sql, ARRAY_A);
        echo json_encode($stripe_webhook_data);
    } else {
        echo '0';
    }
    exit;
}

add_action('wp_ajax_sc_edit_plan', 'sc_edit_plan');

function sc_edit_plan() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    if ($id != '' || $id == 0) {
        global $wpdb;
        $stripe_select_sql = 'SELECT * FROM ' . $wpdb->prefix . 'stripe_plan WHERE id=' . $id;
        $stripe_webhook_data = $wpdb->get_row($stripe_select_sql, ARRAY_A);
        echo json_encode($stripe_webhook_data);
    } else {
        echo '0';
    }
    exit;
}

add_action('wp_ajax_sc_get_current_page_classes', 'sc_get_current_page_classes');

function sc_get_current_page_classes() {
    $current_page = isset($_POST['current_page']) ? $_POST['current_page'] : '';
    $page = get_posts(
            array(
                'name' => $current_page,
                'post_type' => 'page'
            )
    );
    $all_matched_classes = array();
    if ($page) {
        $page_content = $page[0]->post_content;        
        $match_3 = preg_match_all("'\[button(.*?)\]'si", $page_content, $matches_3);
        $match_2 = preg_match_all("'<a(.*?)</a>'si", $page_content, $matches_2);
        $match_4 = preg_match_all("'\[.*button(.*?)\[/.*|<a(.*?)</a>'si", $page_content, $matches_4);
        $match = preg_match_all("'\[mk_button(.*?)\[/mk_button|<a(.*?)</a>'si", $page_content, $matches);
        $arr_matched = array_merge($matches[1], $matches_2[1], $matches_3[1], $matches_4[1]);        
        foreach ($arr_matched as $match_content) {
            $class = preg_match_all("'el_class=\"(.*?)\"'si", $match_content, $match_class);            
            foreach ($match_class[1] as $m_class) {
                foreach (explode(' ', $m_class) as $cls) {
                    array_push($all_matched_classes, trim($cls));
                }                
            }
        }
        $all_matched_classes = array_unique($all_matched_classes);
    }
    if($all_matched_classes){
        echo json_encode($all_matched_classes);
    }else{
        echo 0;
    }
    exit;
}


add_action('wp_ajax_sc_delete_page', 'sc_delete_page');

function sc_delete_page() {
    $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    global $wpdb;    
    $del_page = $wpdb->delete('wp_stripe_page', array('id' => $id), array('%d'));
    if ($del_page) {
        echo 'success';
    } else {        
        echo '0';
    }
    exit;
}


add_action('wp_ajax_sc_delete_plan', 'sc_delete_plan');

function sc_delete_plan() {
    global $wpdb;
    $plan_id_database = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    $stripe_plan_id = $_POST['stripe_id'];
    
    if($plan_id_database == ''){
        echo '0';
        exit;
    }
    
    $stripe = new Stripe();
    $delete_plan = $stripe->deletePlan($stripe_plan_id);
    if (isset($delete_plan['deleted'])) {
        //api delete plan -> then delete in database
        if ($delete_plan['deleted'] == true) {
            $del_plan = $wpdb->delete('wp_stripe_plan', array('id' => $plan_id_database), array('%d'));
            if ($del_plan) {
                echo '<div class="alert alert-success" role="alert">Stripe plan delete successfully</div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">Stripe plan delete error</div>';
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Stripe plan delete error</div>';
        }
    } else {
        //save to log file $delete_plan if delete error
        echo '<div class="alert alert-danger" role="alert">Stripe plan delete error</div>';
    }
    exit;
}


add_action('wp_ajax_sc_delete_payment', 'sc_delete_payment');

function sc_delete_payment() {
    global $wpdb;
    $fee_id = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    if($fee_id == ''){
        echo '0';
        exit;
    }
    $del_fee = $wpdb->delete('wp_stripe_fee', array('id' => $fee_id), array('%d'));
    if ($del_fee) {
        echo '<div class="alert alert-success" role="alert">Payment deleted successfully</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">Payment deleted error</div>';
    }
    exit;
}