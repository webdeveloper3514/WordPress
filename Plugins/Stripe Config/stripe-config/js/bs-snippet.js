jQuery(function () {
    var FRONT_URL = window.location.protocol + "//" + window.location.host + "/";
    //jQuery('.v-tab-pane').hide();
    setTimeout(function(){
        var tab = getParameterByName('tab');
        if (tab == 'stripe-new-page'){
            console.log("In");
            console.log(jQuery('.v-tab').eq(1).html());
            //jQuery('.v-tab').eq(1).click();
        } else {
            console.log("Out");
            console.log(jQuery('.v-tab').eq(0).html());
            //jQuery('.v-tab').eq(0).click();
        }
    },250);    
    
    jQuery('.v-tab').hover(function (e) {
        jQuery('.nav .navbar-nav li a').css('#background', '#23282D');
        jQuery(this).parent().css('#background', '#1683DE');
    });
    jQuery('.v-tab').parent().hover(function (e) {
        jQuery(this).addClass('slide-right');
    });
    jQuery('.v-tab').parent().mouseleave(function (e) {
        jQuery(this).removeClass('slide-right');
    });    
    jQuery('.v-tab').click(function (e) {
        e.preventDefault();
        jQuery('.navbar-nav').find('.active').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.v-tab-pane').hide();
        jQuery("#" + jQuery(this).attr('href')).show();
        localStorage.setItem('active_stripe_tab', jQuery(this).attr('href'));        
        return false;
    });
    jQuery('.postbox').find('.handlediv').click(function () {
        jQuery(this).siblings('.inside').slideToggle('fast');
    });
    jQuery('.postbox').find('.handle-heading').click(function () {
        jQuery(this).siblings('.inside').slideToggle('fast');
    });

    jQuery('.stripe-conf').click(function () {
        var id = jQuery(this).data('id');
        jQuery(this).val(jQuery('#' + id).val());
    }).blur(function () {
        var id = jQuery(this).data('id');
        var val = jQuery(this).val();
        var pre_val = val.substring(0, (val.indexOf('_', val.indexOf('_') + 1)) + 1);
        var rp_val = val.substring((val.indexOf('_', val.indexOf('_') + 1)) + 1);
        jQuery('#' + id).val(jQuery(this).val());
        jQuery(this).val(pre_val + rp_val.replace(/[\S]/g, "*"));
    });
    jQuery('.stripe-conf').click();
    jQuery('.stripe-conf').blur();
    jQuery(document).on('hover', '.wco-help-tip', function () {
        jQuery(this).parent('td,th').siblings('td').find('.wco-field-desc').fadeToggle();
    });
    //Stripe API key
    if (jQuery('#chk_test').prop('checked')==true){        
        jQuery('table.tbl-fix-input').find('[data-id="stripe_api_key_class_test"]').css('background','#EFEFEF');
        jQuery('table.tbl-fix-input').find('[data-id="stripe_key_form_test"]').css('background','#EFEFEF');
        jQuery('table.tbl-fix-input').find('[data-id="stripe_api_key_class"]').css('background','#ffffff');
        jQuery('table.tbl-fix-input').find('[data-id="stripe_key_form"]').css('background','#ffffff');
    } else{        
        jQuery('table.tbl-fix-input').find('[data-id="stripe_api_key_class"]').css('background','#EFEFEF');
        jQuery('table.tbl-fix-input').find('[data-id="stripe_key_form"]').css('background','#EFEFEF');
        jQuery('table.tbl-fix-input').find('[data-id="stripe_api_key_class_test"]').css('background','#ffffff');
        jQuery('table.tbl-fix-input').find('[data-id="stripe_key_form_test"]').css('background','#ffffff');
    }
    jQuery('#chk_test').change(function(){
        if (jQuery(this).prop('checked')==true){ 
            jQuery(this).closest('td.frm-lbl').find('.input-group-addon').addClass('bg-live').removeClass('bg-test');
            jQuery(this).closest('td.frm-lbl').find('.input-group-addon').text('').text('live');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_api_key_class_test"]').css('background','#EFEFEF');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_key_form_test"]').css('background','#EFEFEF');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_api_key_class"]').css('background','#ffffff');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_key_form"]').css('background','#ffffff');
        } else{
            jQuery(this).closest('td.frm-lbl').find('.input-group-addon').addClass('bg-test').removeClass('bg-live');
            jQuery(this).closest('td.frm-lbl').find('.input-group-addon').text('').text('test');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_api_key_class"]').css('background','#EFEFEF');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_key_form"]').css('background','#EFEFEF');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_api_key_class_test"]').css('background','#ffffff');
            jQuery(this).closest('table.tbl-fix-input').find('[data-id="stripe_key_form_test"]').css('background','#ffffff');
        } 
    });
    jQuery('.navbar-nav').find('.active').removeClass('active');    
    jQuery('.v-tab-pane').hide();
    var current_tab = localStorage.getItem('active_stripe_tab');    
    jQuery('.navbar-nav').find('li').each(function(){
        if(jQuery(this).children('a').attr("href") == current_tab){
            jQuery(this).addClass('active');
        }
    });
    jQuery("#" + current_tab).show();    
});
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}