jQuery(document).ready(function () {
    jQuery('.wcoffers_cashback_products').select2();
    jQuery('.offers-single-product-wrap .wcoffers_single_products').select2();
    jQuery('.offers-category-wrap .wcoffers_single_products_category').select2();
    jQuery('.offers-birthday-wrap .wcoffers_birthday_products').select2();
    jQuery('.offers-date-picker').datepicker({
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: true
    });

    if (jQuery('#wc_birthday').length > 0) {
        jQuery('#wc_birthday').datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            numberOfMonths: 1,
            showButtonPanel: true
        });
    }

    //Deposit upload image click
    var frame;
    jQuery('.deposit-table .upload_button').click(function (e) {
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            jQuery('.deposit-table .deposit-image-preview').append('<img src="' + attachment.url + '" alt="" style="max-width:300px;"/>');
            jQuery('.deposit-table .wcoffer_deposit_product_image_text').val(attachment.url);            
            jQuery('.deposit-table .remove_button').css('display','inline-block');
            jQuery('.deposit-table .upload_button').css('display','none');
        });

        frame.open();
    });
    jQuery('.deposit-table .remove_button').click(function (e) {
        jQuery('.deposit-table .deposit-image-preview > img').slideUp();
        jQuery('.deposit-table .wcoffer_deposit_product_image_text').val('');
        jQuery('.deposit-table .remove_button').css('display','none');
        jQuery('.deposit-table .upload_button').css('display','inline-block');
    });
    //Email Templates click
    jQuery('#v-nav ul > li > a').click(function (e) {
        e.preventDefault();
        var $id = jQuery(this).data('target');
        jQuery('#v-nav ul > li').removeClass('current');
        jQuery(this).parent('li').addClass('current');
        jQuery('#v-nav .tab-content').hide();
        jQuery('#' + $id).show();
    });

    //Add single product tab
    jQuery('.wcoffers-add-single-product-discount').click(function () {
        var $offer,
                $count = jQuery(this).closest('.offers-single-product-wrap').find('.offers-product-table').length;
        $offer = offers_add_product_discount_block($count);
    });

    //Add single product category tab
    jQuery('.wcoffers-add-category-discount').click(function () {
        var $offer,
                $count = jQuery(this).closest('.offers-category-wrap').find('.offers-product-table').length;
        $offer = offers_add_product_cat_discount_block($count);
    });

    //Add birthday discount
    jQuery('.wcoffers-add-birthday-discount').click(function () {
        var $count = jQuery(this).closest('.offers-birthday-wrap').find('.offers-birthday-table').length;
        offers_add_birthday_discount_block($count);
    });

    //Delete single product tab
    jQuery(document.body).on('click', '.wcoffers-delete-single-product-discount', function () {
        if (confirm('Are you sure that you want to remove this discount offer?') === false)
        {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('table.offers-product-table');
        $offer.slideUp(250, function () {
            jQuery(this).remove();
            jQuery('.offers-append-products').find('.offers-product-table').each(function (index) {

                var $this = jQuery(this);

                var display_index = parseInt(index);

                $this.find('input, select').each(function () {

                    var name = jQuery(this).data('name');

                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', 'offers_io_product[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'offers_io_product[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });

    //Delete single product tab
    jQuery(document.body).on('click', '.wcoffers-delete-single-product-category-discount', function () {
        if (confirm('Are you sure that you want to remove this discount offer?') === false)
        {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('table.offers-product-table');
        $offer.slideUp(250, function () {
            jQuery(this).remove();
            jQuery('.offers-append-products-cat').find('.offers-product-table').each(function (index) {

                var $this = jQuery(this);

                var display_index = parseInt(index);

                $this.find('input, select').each(function () {

                    var name = jQuery(this).data('name');

                    if (typeof name != 'undefined') {
                        if (name == 'product_cat') {
                            jQuery(this).attr('name', 'offers_io_product_cat[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'offers_io_product_cat[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });

    //Delete birthday offers
    jQuery(document.body).on('click', '.wcoffers-delete-birthday-discount', function () {
        if (confirm('Are you sure that you want to remove birthday discount offer?') === false) {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('table.offers-birthday-table');
                $offer.slideUp(250, function () {
            jQuery(this).remove();            
        });
    });

    //Offer applied to selection
    if (jQuery('.offers_io_apply').val() == 'product') {
        jQuery('.offers-single-product-wrap').show();
        jQuery('.offers-category-wrap').hide();
        jQuery('.offers-global-wrap').hide();
    } else if (jQuery('.offers_io_apply').val() == 'category') {
        jQuery('.offers-category-wrap').show();
        jQuery('.offers-single-product-wrap').hide();
        jQuery('.offers-global-wrap').hide();
    } else {
        jQuery('.offers-category-wrap').hide();
        jQuery('.offers-single-product-wrap').hide();
        jQuery('.offers-global-wrap').show();
    }
    jQuery('.offers_io_apply').change(function () {
        var $this = jQuery(this).val();
        if ($this == 'product') {
            jQuery('.offers-single-product-wrap').show();
            jQuery('.offers-category-wrap').hide();
            jQuery('.offers-global-wrap').hide();
        } else if ($this == 'category') {
            jQuery('.offers-category-wrap').show();
            jQuery('.offers-single-product-wrap').hide();
            jQuery('.offers-global-wrap').hide();
        } else {
            jQuery('.offers-category-wrap').hide();
            jQuery('.offers-single-product-wrap').hide();
            jQuery('.offers-global-wrap').show();
        }
    });

    //Not allow characters
    jQuery(document).on('keypress', '.textNumberOnly', function (e) {
        var regex = new RegExp("^[0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);

        if (regex.test(str)) {
            return true;
        }
        e.preventDefault();
        return false;
    });

    jQuery(document).on('keypress', '.textNumberWithFloat', function (e) {
        var $this = jQuery(this);
        if ((event.which != 46 || $this.val().indexOf('.') != -1) &&
                ((event.which < 48 || event.which > 57) &&
                        (event.which != 0 && event.which != 8))) {
            event.preventDefault();
        }

        var text = jQuery(this).val();
        if ((event.which == 46) && (text.indexOf('.') == -1)) {
            setTimeout(function () {
                if ($this.val().substring($this.val().indexOf('.')).length > 3) {
                    $this.val($this.val().substring(0, $this.val().indexOf('.') + 3));
                }
            }, 1);
        }

        if ((text.indexOf('.') != -1) &&
                (text.substring(text.indexOf('.')).length > 2) &&
                (event.which != 0 && event.which != 8) &&
                (jQuery(this)[0].selectionStart >= text.length - 2)) {
            event.preventDefault();
        }
    });

    /**
     * New Registration Section JS
     */
    if (jQuery('#offer_discount_type').val() == 'percent') {
        jQuery('.discount_type_percent').show();
        jQuery('.wco-registration-fixed_cart-wrap').hide();
        if (jQuery('.offer_percentage_discount:checked').val() == 'quantity') {
            jQuery('.wco-registration-percentage-quantity-wrap').show();
            jQuery('.wco-registration-percentage-amount-wrap').hide();
        } else if (jQuery('.offer_percentage_discount:checked').val() == 'amount') {
            jQuery('.wco-registration-percentage-quantity-wrap').hide();
            jQuery('.wco-registration-percentage-amount-wrap').show();
        }
    } else if (jQuery('#offer_discount_type').val() == 'fixed_cart') {
        jQuery('.discount_type_percent').hide();
        jQuery('.wco-registration-percentage-quantity-wrap').hide();
        jQuery('.wco-registration-percentage-amount-wrap').hide();
        jQuery('.wco-registration-fixed_cart-wrap').show();
    }
    jQuery('#offer_discount_type').change(function () {
        if (jQuery(this).val() == 'percent') {
            jQuery('.discount_type_percent').show();
            if (jQuery('.offer_percentage_discount:checked').val() == 'quantity') {
                jQuery('.wco-registration-percentage-quantity-wrap').show();
                jQuery('.wco-registration-percentage-amount-wrap').hide();
            } else if (jQuery('.offer_percentage_discount:checked').val() == 'amount') {
                jQuery('.wco-registration-percentage-quantity-wrap').hide();
                jQuery('.wco-registration-percentage-amount-wrap').show();
            }
            jQuery('.wco-registration-fixed_cart-wrap').hide();
        } else if (jQuery(this).val() == 'fixed_cart') {
            jQuery('.discount_type_percent').hide();
            jQuery('.wco-registration-percentage-quantity-wrap').hide();
            jQuery('.wco-registration-percentage-amount-wrap').hide();
            jQuery('.wco-registration-fixed_cart-wrap').show();
        }
    });
    jQuery('.offer_percentage_discount').change(function () {
        if (jQuery(this).val() == 'quantity') {
            jQuery('.wco-registration-percentage-quantity-wrap').show();
            jQuery('.wco-registration-percentage-amount-wrap').hide();
        } else if (jQuery(this).val() == 'amount') {
            jQuery('.wco-registration-percentage-quantity-wrap').hide();
            jQuery('.wco-registration-percentage-amount-wrap').show();
        }
    });

    //Registration percentage quantity discount
    jQuery('.wco-add-registration-percentage-quantity').click(function () {
        if (jQuery('.wco-no-rule-found').length > 0)
            jQuery('.wco-no-rule-found').remove();
        var $count = jQuery('.wco-registration-percentage-quantity-table').find('.wco-registration-quantity-rule-tr').length;
        var $last_max_quantity = jQuery('.wco-registration-percentage-quantity-table tr.wco-registration-quantity-rule-tr').last().find('.max_quantity');
        if ($last_max_quantity.val() == '') {
            alert('Please add last row maximum quantity');
            return;
        }
        wcoffers_add_registration_percentage_quantity_block($count);
    });
    jQuery(document).on('change', '.wco-registration-quantity-rule-tr .min_quantity,.wco-registration-quantity-rule-tr .max_quantity', function () {
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr.wco-registration-quantity-rule-tr').find('.min_quantity,.max_quantity,.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass('max_quantity')) {
            var $this_min_q = jQuery(this).closest('tr.wco-registration-quantity-rule-tr').find('.min_quantity').val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert('Add greater value than minimun quantity');
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr.wco-registration-quantity-rule-tr').find('.min_quantity');
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass('min_quantity')) {
            jQuery(this).closest('tr').find('.max_quantity').val('');
        }
    });
    jQuery(document).on('click', '.wco-reg-delete-quantity-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr.wco-registration-quantity-rule-tr');
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr.wco-registration-quantity-rule-tr').find('.min_quantity,.max_quantity,.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr.wco-registration-quantity-rule-tr').find('.max_quantity');
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr.wco-registration-quantity-rule-tr').find('.min_quantity');
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery('.wco-registration-percentage-quantity-table').find('tr.wco-registration-quantity-rule-tr').each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', 'registration_offer_percentage_quantity[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'registration_offer_percentage_quantity[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });

    //Registration percentage amount discount
    jQuery('.wco-add-registration-percentage-amount').click(function () {
        if (jQuery('.wco-no-rule-found').length > 0)
            jQuery('.wco-no-rule-found').remove();
        var $count = jQuery('.wco-registration-percentage-amount-table').find('.wco-registration-amount-rule-tr').length;
        var $last_max_quantity = jQuery('.wco-registration-percentage-amount-table tr.wco-registration-amount-rule-tr').last().find('.max_amount');
        if ($last_max_quantity.val() == '') {
            alert('Please add last row maximum amount');
            return;
        }
        wcoffers_add_registration_percentage_amount_block($count);
    });
    jQuery(document).on('change', '.wco-registration-amount-rule-tr .min_amount,.wco-registration-amount-rule-tr .max_amount', function () {
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr.wco-registration-amount-rule-tr').find('.min_amount,.max_amount,.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass('max_amount')) {
            var $this_min_q = jQuery(this).closest('tr.wco-registration-amount-rule-tr').find('.min_amount').val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert('Add greater value than minimun amount');
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr.wco-registration-amount-rule-tr').find('.min_amount');
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass('min_amount')) {
            jQuery(this).closest('tr').find('.max_amount').val('');
        }
    });
    jQuery(document).on('click', '.wco-reg-delete-amount-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr.wco-registration-amount-rule-tr');
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr.wco-registration-amount-rule-tr').find('.min_amount,.max_amount,.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr.wco-registration-amount-rule-tr').find('.max_amount');
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr.wco-registration-amount-rule-tr').find('.min_amount');
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery('.wco-registration-percentage-amount-table').find('tr.wco-registration-amount-rule-tr').each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', 'registration_offer_percentage_amount[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'registration_offer_percentage_amount[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });

    //Registration fixed cart discount
    jQuery('.wco-add-registration-fixed_cart').click(function () {
        if (jQuery('.wco-no-rule-found').length > 0)
            jQuery('.wco-no-rule-found').remove();
        var $count = jQuery('.wco-registration-fixed_cart-table').find('.wco-registration-fixed_cart-rule-tr').length;
        var $last_max_quantity = jQuery('.wco-registration-fixed_cart-table tr.wco-registration-fixed_cart-rule-tr').last().find('.max_amount');
        if ($last_max_quantity.val() == '') {
            alert('Please add last row maximum amount');
            return;
        }
        wcoffers_add_registration_fixed_cart_block($count);
    });
    jQuery(document).on('change', '.wco-registration-fixed_cart-rule-tr .min_amount,.wco-registration-fixed_cart-rule-tr .max_amount', function () {
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr.wco-registration-fixed_cart-rule-tr').find('.min_amount,.max_amount,.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass('max_amount')) {
            var $this_min_q = jQuery(this).closest('tr.wco-registration-fixed_cart-rule-tr').find('.min_amount').val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert('Add greater value than minimun amount');
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr.wco-registration-fixed_cart-rule-tr').find('.min_amount');
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass('min_amount')) {
            jQuery(this).closest('tr').find('.max_amount').val('');
        }
    });
    jQuery(document).on('click', '.wco-reg-delete-fixed_cart-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr.wco-registration-fixed_cart-rule-tr');
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr.wco-registration-fixed_cart-rule-tr').find('.min_amount,.max_amount,.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr.wco-registration-fixed_cart-rule-tr').find('.max_amount');
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr.wco-registration-fixed_cart-rule-tr').find('.min_amount');
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery('.wco-registration-percentage-amount-table').find('tr.wco-registration-fixed_cart-rule-tr').each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', 'registration_offer_fixed_cart[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'registration_offer_fixed_cart[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });


    /**
     * Second Purchase Section JS
     */
    if (jQuery('#offers_freebies_discount_type').val() == 'percent') {
        jQuery('.discount_type_percent').show();
        jQuery('.wco-second-purchase-fixed_cart-wrap').hide();
        if (jQuery('.offers_freebies_discount:checked').val() == 'quantity') {
            jQuery('.wco-second-purchase-percentage-quantity-wrap').show();
            jQuery('.wco-second-purchase-percentage-amount-wrap').hide();
        } else if (jQuery('.offers_freebies_discount:checked').val() == 'amount') {
            jQuery('.wco-second-purchase-percentage-quantity-wrap').hide();
            jQuery('.wco-second-purchase-percentage-amount-wrap').show();
        }
    } else if (jQuery('#offers_freebies_discount_type').val() == 'fixed_cart') {
        jQuery('.discount_type_percent').hide();
        jQuery('.wco-second-purchase-percentage-quantity-wrap').hide();
        jQuery('.wco-second-purchase-percentage-amount-wrap').hide();
        jQuery('.wco-second-purchase-fixed_cart-wrap').show();
    }
    jQuery('#offers_freebies_discount_type').change(function () {
        if (jQuery(this).val() == 'percent') {
            jQuery('.discount_type_percent').show();
            if (jQuery('.offers_freebies_discount:checked').val() == 'quantity') {
                jQuery('.wco-second-purchase-percentage-quantity-wrap').show();
                jQuery('.wco-second-purchase-percentage-amount-wrap').hide();
            } else if (jQuery('.offers_freebies_discount:checked').val() == 'amount') {
                jQuery('.wco-second-purchase-percentage-quantity-wrap').hide();
                jQuery('.wco-second-purchase-percentage-amount-wrap').show();
            }
            jQuery('.wco-second-purchase-fixed_cart-wrap').hide();
        } else if (jQuery(this).val() == 'fixed_cart') {
            jQuery('.discount_type_percent').hide();
            jQuery('.wco-second-purchase-percentage-quantity-wrap').hide();
            jQuery('.wco-second-purchase-percentage-amount-wrap').hide();
            jQuery('.wco-second-purchase-fixed_cart-wrap').show();
        }
    });
    jQuery('.offers_freebies_discount').change(function () {
        if (jQuery(this).val() == 'quantity') {
            jQuery('.wco-second-purchase-percentage-quantity-wrap').show();
            jQuery('.wco-second-purchase-percentage-amount-wrap').hide();
        } else if (jQuery(this).val() == 'amount') {
            jQuery('.wco-second-purchase-percentage-quantity-wrap').hide();
            jQuery('.wco-second-purchase-percentage-amount-wrap').show();
        }
    });
    //Add click functionality
    jQuery('.wco-add-second-purchase-percentage-quantity,.wco-add-second-purchase-percentage-amount,.wco-add-second-purchase-fixed_cart').click(function () {
        var current_class = jQuery(this).attr("class"), table_class = '', table_tr_class = '', min_class = '', max_class = '', alert_message = '';
        if (jQuery(this).hasClass('wco-add-second-purchase-percentage-quantity')) {
            table_class = '.wco-second-purchase-percentage-quantity-table';
            table_tr_class = '.wco-second-purchase-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            alert_message = 'Please add last row maximum quantity';
        } else if (jQuery(this).hasClass('wco-add-second-purchase-percentage-amount')) {
            table_class = '.wco-second-purchase-percentage-amount-table';
            table_tr_class = '.wco-second-purchase-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        } else if (jQuery(this).hasClass('wco-add-second-purchase-fixed_cart')) {
            table_class = '.wco-second-purchase-fixed_cart-table';
            table_tr_class = '.wco-second-purchase-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        }
        if (jQuery(this).next('table' + table_class).find('.wco-no-rule-found').length > 0)
            jQuery(this).next('table' + table_class).find('.wco-no-rule-found').remove();
        var $count = jQuery(table_class).find(table_tr_class).length;
        var $last_max_quantity = jQuery(table_class + ' tr' + table_tr_class).last().find(max_class);
        if ($last_max_quantity.val() == '') {
            alert(alert_message);
            return;
        }
        wcoffers_add_second_purchase_block($count, current_class, table_class, table_tr_class, min_class, max_class);
    });
    //Input field change
    jQuery(document).on('change', '.wco-second-purchase-quantity-rule-tr .min_quantity,.wco-second-purchase-quantity-rule-tr .max_quantity,.wco-second-purchase-amount-rule-tr .min_amount,.wco-second-purchase-amount-rule-tr .max_amount, .wco-second-purchase-fixed_cart-rule-tr .min_amount,.wco-second-purchase-fixed_cart-rule-tr .max_amount', function () {
        var table_tr_class = '', min_class = '', max_class = '', max_class_dot = '', alert_message = '', min_class_dot = '';
        if (jQuery(this).closest('tr').hasClass('wco-second-purchase-quantity-rule-tr')) {
            table_tr_class = '.wco-second-purchase-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            max_class_dot = 'max_quantity';
            min_class_dot = 'min_quantity';
            alert_message = 'Add greater value than minimun quantity';
        } else if (jQuery(this).closest('tr').hasClass('wco-second-purchase-amount-rule-tr')) {
            table_tr_class = '.wco-second-purchase-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        } else if (jQuery(this).closest('tr').hasClass('wco-second-purchase-fixed_cart-rule-tr')) {
            table_tr_class = '.wco-second-purchase-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        }
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass(max_class_dot)) {
            var $this_min_q = jQuery(this).closest('tr' + table_tr_class).find(min_class).val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert(alert_message);
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass(min_class_dot)) {
            jQuery(this).closest('tr').find(max_class).val('');
        }
    });
    //Delete change event
    jQuery(document).on('click', '.wco-sp-delete-quantity-rule,.wco-sp-delete-amount-rule,.wco-sp-delete-fixed_cart-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var table_class = '', table_tr_class = '', min_class = '', max_class = '', input_name = '';
        if (jQuery(this).hasClass('wco-sp-delete-quantity-rule')) {
            table_class = '.wco-second-purchase-percentage-quantity-table';
            table_tr_class = '.wco-second-purchase-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            input_name = 'second_purchase_offer_percentage_quantity';
        } else if (jQuery(this).hasClass('wco-sp-delete-amount-rule')) {
            table_class = '.wco-second-purchase-percentage-amount-table';
            table_tr_class = '.wco-second-purchase-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'second_purchase_offer_percentage_amount';
        } else if (jQuery(this).hasClass('wco-sp-delete-fixed_cart-rule')) {
            table_class = '.wco-second-purchase-fixed_cart-table';
            table_tr_class = '.wco-second-purchase-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'second_purchase_offer_fixed_cart';
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr' + table_tr_class);
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr' + table_tr_class).find(max_class);
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery(table_class).find('tr' + table_tr_class).each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', input_name + '[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', input_name + '[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });


    /**
     * Inactive Customer JS
     */
    if (jQuery('#offers_old_user_discount_type').val() == 'percent') {
        jQuery('.discount_type_percent').show();
        jQuery('.wco-inactive-customer-fixed_cart-wrap').hide();
        if (jQuery('.offers_old_user_percentage_discount:checked').val() == 'quantity') {
            jQuery('.wco-inactive-customer-percentage-quantity-wrap').show();
            jQuery('.wco-inactive-customer-percentage-amount-wrap').hide();
        } else if (jQuery('.offers_old_user_percentage_discount:checked').val() == 'amount') {
            jQuery('.wco-inactive-customer-percentage-quantity-wrap').hide();
            jQuery('.wco-inactive-customer-percentage-amount-wrap').show();
        }
    } else if (jQuery('#offers_old_user_discount_type').val() == 'fixed_cart') {
        jQuery('.discount_type_percent').hide();
        jQuery('.wco-inactive-customer-percentage-quantity-wrap').hide();
        jQuery('.wco-inactive-customer-percentage-amount-wrap').hide();
        jQuery('.wco-inactive-customer-fixed_cart-wrap').show();
    }
    jQuery('#offers_old_user_discount_type').change(function () {
        if (jQuery(this).val() == 'percent') {
            jQuery('.discount_type_percent').show();
            if (jQuery('.offers_old_user_percentage_discount:checked').val() == 'quantity') {
                jQuery('.wco-inactive-customer-percentage-quantity-wrap').show();
                jQuery('.wco-inactive-customer-percentage-amount-wrap').hide();
            } else if (jQuery('.offers_old_user_percentage_discount:checked').val() == 'amount') {
                jQuery('.wco-inactive-customer-percentage-quantity-wrap').hide();
                jQuery('.wco-inactive-customer-percentage-amount-wrap').show();
            }
            jQuery('.wco-inactive-customer-fixed_cart-wrap').hide();
        } else if (jQuery(this).val() == 'fixed_cart') {
            jQuery('.discount_type_percent').hide();
            jQuery('.wco-inactive-customer-percentage-quantity-wrap').hide();
            jQuery('.wco-inactive-customer-percentage-amount-wrap').hide();
            jQuery('.wco-inactive-customer-fixed_cart-wrap').show();
        }
    });
    jQuery('.offers_old_user_percentage_discount').change(function () {
        if (jQuery(this).val() == 'quantity') {
            jQuery('.wco-inactive-customer-percentage-quantity-wrap').show();
            jQuery('.wco-inactive-customer-percentage-amount-wrap').hide();
        } else if (jQuery(this).val() == 'amount') {
            jQuery('.wco-inactive-customer-percentage-quantity-wrap').hide();
            jQuery('.wco-inactive-customer-percentage-amount-wrap').show();
        }
    });
    //Add click functionality
    jQuery('.wco-add-inactive-customer-percentage-quantity,.wco-add-inactive-customer-percentage-amount,.wco-add-inactive-customer-fixed_cart').click(function () {

        var current_class = jQuery(this).attr("class"), table_class = '', table_tr_class = '', min_class = '', max_class = '', alert_message = '';
        if (jQuery(this).hasClass('wco-add-inactive-customer-percentage-quantity')) {
            table_class = '.wco-inactive-customer-percentage-quantity-table';
            table_tr_class = '.wco-inactive-customer-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            alert_message = 'Please add last row maximum quantity';
        } else if (jQuery(this).hasClass('wco-add-inactive-customer-percentage-amount')) {
            table_class = '.wco-inactive-customer-percentage-amount-table';
            table_tr_class = '.wco-inactive-customer-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        } else if (jQuery(this).hasClass('wco-add-inactive-customer-fixed_cart')) {
            table_class = '.wco-inactive-customer-fixed_cart-table';
            table_tr_class = '.wco-inactive-customer-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        }
        if (jQuery(this).next('table' + table_class).find('.wco-no-rule-found').length > 0)
            jQuery(this).next('table' + table_class).find('.wco-no-rule-found').remove();
        var $count = jQuery(table_class).find(table_tr_class).length;
        var $last_max_quantity = jQuery(table_class + ' tr' + table_tr_class).last().find(max_class);
        if ($last_max_quantity.val() == '') {
            alert(alert_message);
            return;
        }
        wcoffers_add_inactive_customer_block($count, current_class, table_class, table_tr_class, min_class, max_class);
    });
    //Input field change
    jQuery(document).on('change', '.wco-inactive-customer-quantity-rule-tr .min_quantity,.wco-inactive-customer-quantity-rule-tr .max_quantity,.wco-inactive-customer-amount-rule-tr .min_amount,.wco-inactive-customer-amount-rule-tr .max_amount, .wco-inactive-customer-fixed_cart-rule-tr .min_amount,.wco-inactive-customer-fixed_cart-rule-tr .max_amount', function () {
        var table_tr_class = '', min_class = '', max_class = '', max_class_dot = '', alert_message = '', min_class_dot = '';
        if (jQuery(this).closest('tr').hasClass('wco-inactive-customer-quantity-rule-tr')) {
            table_tr_class = '.wco-inactive-customer-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            max_class_dot = 'max_quantity';
            min_class_dot = 'min_quantity';
            alert_message = 'Add greater value than minimun quantity';
        } else if (jQuery(this).closest('tr').hasClass('wco-inactive-customer-amount-rule-tr')) {
            table_tr_class = '.wco-inactive-customer-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        } else if (jQuery(this).closest('tr').hasClass('wco-inactive-customer-fixed_cart-rule-tr')) {
            table_tr_class = '.wco-inactive-customer-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        }
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass(max_class_dot)) {
            var $this_min_q = jQuery(this).closest('tr' + table_tr_class).find(min_class).val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert(alert_message);
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass(min_class_dot)) {
            jQuery(this).closest('tr').find(max_class).val('');
        }
    });
    //Delete change event
    jQuery(document).on('click', '.wco-ic-delete-quantity-rule,.wco-ic-delete-amount-rule,.wco-ic-delete-fixed_cart-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var table_class = '', table_tr_class = '', min_class = '', max_class = '', input_name = '';
        if (jQuery(this).hasClass('wco-ic-delete-quantity-rule')) {
            table_class = '.wco-inactive-customer-percentage-quantity-table';
            table_tr_class = '.wco-inactive-customer-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            input_name = 'inactive_customer_offer_percentage_quantity';
        } else if (jQuery(this).hasClass('wco-ic-delete-amount-rule')) {
            table_class = '.wco-inactive-customer-percentage-amount-table';
            table_tr_class = '.wco-inactive-customer-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'inactive_customer_offer_percentage_amount';
        } else if (jQuery(this).hasClass('wco-ic-delete-fixed_cart-rule')) {
            table_class = '.wco-inactive-customer-fixed_cart-table';
            table_tr_class = '.wco-inactive-customer-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'inactive_customer_offer_fixed_cart';
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr' + table_tr_class);
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr' + table_tr_class).find(max_class);
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery(table_class).find('tr' + table_tr_class).each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', input_name + '[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', input_name + '[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });

    /**
     * Existing Customer JS
     */
    if (jQuery('.offers_existing_user_discount_type').val() == 'percent') {
        jQuery('.discount_type_percent').show();
        jQuery('.wco-existing-customer-fixed_cart-wrap').hide();
        if (jQuery('.offers_existing_user_percentage_discount:checked').val() == 'quantity') {
            jQuery('.wco-existing-customer-percentage-quantity-wrap').show();
            jQuery('.wco-existing-customer-percentage-amount-wrap').hide();
        } else if (jQuery('.offers_existing_user_percentage_discount:checked').val() == 'amount') {
            jQuery('.wco-existing-customer-percentage-quantity-wrap').hide();
            jQuery('.wco-existing-customer-percentage-amount-wrap').show();
        }
    } else if (jQuery('.offers_existing_user_discount_type').val() == 'fixed_cart') {
        jQuery('.discount_type_percent').hide();
        jQuery('.wco-existing-customer-percentage-quantity-wrap').hide();
        jQuery('.wco-existing-customer-percentage-amount-wrap').hide();
        jQuery('.wco-existing-customer-fixed_cart-wrap').show();
    }
    jQuery(document).on('change', '.offers_existing_user_discount_type', function () {
        if (jQuery(this).val() == 'percent') {
            jQuery(this).closest('tr').siblings('.discount_type_percent').show();
            if (jQuery('.offers_existing_user_percentage_discount:checked').val() == 'quantity') {
                jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-quantity-wrap').show();
                jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-amount-wrap').hide();
            } else if (jQuery('.offers_existing_user_percentage_discount:checked').val() == 'amount') {
                jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-quantity-wrap').hide();
                jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-amount-wrap').show();
            }
            jQuery(this).closest('tr').siblings('.wco-existing-customer-fixed_cart-wrap').hide();
        } else if (jQuery(this).val() == 'fixed_cart') {
            jQuery(this).closest('tr').siblings('.discount_type_percent').hide();
            jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-quantity-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-amount-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-existing-customer-fixed_cart-wrap').show();
        }
    });
    jQuery(document).on('change', '.offers_existing_user_percentage_discount', function () {
        if (jQuery(this).val() == 'quantity') {
            jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-quantity-wrap').show();
            jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-amount-wrap').hide();
        } else if (jQuery(this).val() == 'amount') {
            jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-quantity-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-existing-customer-percentage-amount-wrap').show();
        }
    });
    //Add click functionality
    jQuery(document).on('click', '.wco-add-existing-customer-percentage-quantity,.wco-add-existing-customer-percentage-amount,.wco-add-existing-customer-fixed_cart', function () {
        var current_class = jQuery(this).attr("class"), table_class = '', table_tr_class = '', min_class = '', max_class = '', alert_message = '';
        if (jQuery(this).hasClass('wco-add-existing-customer-percentage-quantity')) {
            table_class = '.wco-existing-customer-percentage-quantity-table';
            table_tr_class = '.wco-existing-customer-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            alert_message = 'Please add last row maximum quantity';
        } else if (jQuery(this).hasClass('wco-add-existing-customer-percentage-amount')) {
            table_class = '.wco-existing-customer-percentage-amount-table';
            table_tr_class = '.wco-existing-customer-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        } else if (jQuery(this).hasClass('wco-add-existing-customer-fixed_cart')) {
            table_class = '.wco-existing-customer-fixed_cart-table';
            table_tr_class = '.wco-existing-customer-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        }
        if (jQuery(this).next('table' + table_class).find('.wco-no-rule-found').length > 0)
            jQuery(this).next('table' + table_class).find('.wco-no-rule-found').remove();
        var $count = jQuery(table_class).find(table_tr_class).length;
        var $last_max_quantity = jQuery(table_class + ' tr' + table_tr_class).last().find(max_class);
        if ($last_max_quantity.val() == '') {
            alert(alert_message);
            return;
        }
        wcoffers_add_existing_customer_block($count, current_class, table_class, table_tr_class, min_class, max_class, jQuery(this));
    });
    //Input field change
    jQuery(document).on('change', '.wco-existing-customer-quantity-rule-tr .min_quantity,.wco-existing-customer-quantity-rule-tr .max_quantity,.wco-existing-customer-amount-rule-tr .min_amount,.wco-existing-customer-amount-rule-tr .max_amount, .wco-existing-customer-fixed_cart-rule-tr .min_amount,.wco-existing-customer-fixed_cart-rule-tr .max_amount', function () {
        var table_tr_class = '', min_class = '', max_class = '', max_class_dot = '', alert_message = '', min_class_dot = '';
        if (jQuery(this).closest('tr').hasClass('wco-existing-customer-quantity-rule-tr')) {
            table_tr_class = '.wco-existing-customer-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            max_class_dot = 'max_quantity';
            min_class_dot = 'min_quantity';
            alert_message = 'Add greater value than minimun quantity';
        } else if (jQuery(this).closest('tr').hasClass('wco-existing-customer-amount-rule-tr')) {
            table_tr_class = '.wco-existing-customer-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        } else if (jQuery(this).closest('tr').hasClass('wco-existing-customer-fixed_cart-rule-tr')) {
            table_tr_class = '.wco-existing-customer-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        }
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass(max_class_dot)) {
            var $this_min_q = jQuery(this).closest('tr' + table_tr_class).find(min_class).val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert(alert_message);
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass(min_class_dot)) {
            jQuery(this).closest('tr').find(max_class).val('');
        }
    });
    //Delete change event
    jQuery(document).on('click', '.wco-ec-delete-quantity-rule,.wco-ec-delete-amount-rule,.wco-ec-delete-fixed_cart-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var table_class = '', table_tr_class = '', min_class = '', max_class = '', input_name = '';
        if (jQuery(this).hasClass('wco-ec-delete-quantity-rule')) {
            table_class = '.wco-existing-customer-percentage-quantity-table';
            table_tr_class = '.wco-existing-customer-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            input_name = 'existing_customer_offer_percentage_quantity';
        } else if (jQuery(this).hasClass('wco-ec-delete-amount-rule')) {
            table_class = '.wco-existing-customer-percentage-amount-table';
            table_tr_class = '.wco-existing-customer-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'existing_customer_offer_percentage_amount';
        } else if (jQuery(this).hasClass('wco-ec-delete-fixed_cart-rule')) {
            table_class = '.wco-existing-customer-fixed_cart-table';
            table_tr_class = '.wco-existing-customer-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'existing_customer_offer_fixed_cart';
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr' + table_tr_class);
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr' + table_tr_class).find(max_class);
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery(table_class).find('tr' + table_tr_class).each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id') {
                            jQuery(this).attr('name', input_name + '[' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', input_name + '[' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });

    /**
     * Birthday Hide/Show
     */
    jQuery('.offers_birthday_discount_type').each(function () {
        if (jQuery(this).val() == 'percent') {
            jQuery(this).closest('tr').siblings('.discount_type_percent').show();
            jQuery(this).closest('tr').siblings('.discount_type_fixed_cart').hide();
            if (jQuery(this).closest('tr').siblings('.discount_type_percent').find('.offer_percentage_discount:checked').val() == 'quantity') {
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').show();
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').hide();
            } else if (jQuery(this).closest('tr').siblings('.discount_type_percent').find('.offer_percentage_discount:checked').val() == 'amount') {
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').hide();
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').show();
            }
            jQuery(this).closest('tr').siblings('.wco-birthday-fixed_cart-wrap').hide();
        } else if (jQuery(this).val() == 'fixed_cart') {
            jQuery(this).closest('tr').siblings('.discount_type_percent').hide();
            jQuery(this).closest('tr').siblings('.discount_type_fixed_cart').show();
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-birthday-fixed_cart-wrap').show();
        }
    });
    jQuery(document).on("change", '.offers_birthday_discount_type', function () {
        if (jQuery(this).val() == 'percent') {
            jQuery(this).closest('tr').siblings('.discount_type_percent').show();
            jQuery(this).closest('tr').siblings('.discount_type_fixed_cart').hide();
            if (jQuery(this).closest('tr').siblings('.discount_type_percent').find('.offer_percentage_discount:checked').val() == 'quantity') {
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').show();
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').hide();
            } else if (jQuery(this).closest('tr').siblings('.discount_type_percent').find('.offer_percentage_discount:checked').val() == 'amount') {
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').hide();
                jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').show();
            }
            jQuery(this).closest('tr').siblings('.wco-birthday-fixed_cart-wrap').hide();
        } else if (jQuery(this).val() == 'fixed_cart') {
            jQuery(this).closest('tr').siblings('.discount_type_percent').hide();
            jQuery(this).closest('tr').siblings('.discount_type_fixed_cart').show();
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-birthday-fixed_cart-wrap').show();
        }
    });
    jQuery(document).on('change', '.offers-birthday-table .offer_percentage_discount', function () {
        if (jQuery(this).val() == 'quantity') {
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').show();
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').hide();
        } else if (jQuery(this).val() == 'amount') {
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-quantity-wrap').hide();
            jQuery(this).closest('tr').siblings('.wco-birthday-percentage-amount-wrap').show();
        }
    });
    //Add click functionality
    jQuery(document).on('click', '.wco-add-birthday-percentage-quantity,.wco-add-birthday-percentage-amount,.wco-add-birthday-fixed_cart', function () {
        var current_class = jQuery(this).attr("class"), table_class = '', table_tr_class = '', min_class = '', max_class = '', alert_message = '';
        if (jQuery(this).hasClass('wco-add-birthday-percentage-quantity')) {
            table_class = '.wco-birthday-percentage-quantity-table';
            table_tr_class = '.wco-birthday-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            alert_message = 'Please add last row maximum quantity';
        } else if (jQuery(this).hasClass('wco-add-birthday-percentage-amount')) {
            table_class = '.wco-birthday-percentage-amount-table';
            table_tr_class = '.wco-birthday-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        } else if (jQuery(this).hasClass('wco-add-birthday-fixed_cart')) {
            table_class = '.wco-birthday-fixed_cart-table';
            table_tr_class = '.wco-birthday-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            alert_message = 'Please add last row maximum amount';
        }
        var parent_count = jQuery(this).parents('.offers-birthday-table').index();
        if (jQuery(this).next('table' + table_class).find('.wco-no-rule-found').length > 0)
            jQuery(this).next('table' + table_class).find('.wco-no-rule-found').remove();
        var $count = jQuery(this).next(table_class).find(table_tr_class).length;
        var $last_max_quantity = jQuery(this).next(table_class).find('tr' + table_tr_class).last().find(max_class);
        if ($last_max_quantity.val() == '') {
            alert(alert_message);
            return;
        }
        wcoffers_add_birthday_block($count, current_class, table_class, table_tr_class, min_class, max_class, parent_count, jQuery(this));
    });
    //Input field change
    jQuery(document).on('change', '.wco-birthday-quantity-rule-tr .min_quantity,.wco-birthday-quantity-rule-tr .max_quantity,.wco-birthday-amount-rule-tr .min_amount,.wco-birthday-amount-rule-tr .max_amount, .wco-birthday-fixed_cart-rule-tr .min_amount,.wco-birthday-fixed_cart-rule-tr .max_amount, .wco-bulk-range-quantity-rule-tr .min_quantity,.wco-bulk-range-quantity-rule-tr .max_quantity', function () {
        var table_tr_class = '', min_class = '', max_class = '', max_class_dot = '', alert_message = '', min_class_dot = '';
        if (jQuery(this).closest('tr').hasClass('wco-birthday-quantity-rule-tr')) {
            table_tr_class = '.wco-birthday-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            max_class_dot = 'max_quantity';
            min_class_dot = 'min_quantity';
            alert_message = 'Add greater value than minimun quantity';
        } else if (jQuery(this).closest('tr').hasClass('wco-birthday-amount-rule-tr')) {
            table_tr_class = '.wco-birthday-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        } else if (jQuery(this).closest('tr').hasClass('wco-birthday-fixed_cart-rule-tr')) {
            table_tr_class = '.wco-birthday-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            max_class_dot = 'max_amount';
            min_class_dot = 'min_amount';
            alert_message = 'Add greater value than minimun amount';
        } else if (jQuery(this).closest('tr').hasClass('wco-bulk-range-quantity-rule-tr')) {
            table_tr_class = '.wco-bulk-range-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            max_class_dot = 'max_quantity';
            min_class_dot = 'min_quantity';
            alert_message = 'Add greater value than minimun quantity';
        }
        var next_all_tr = jQuery(this).closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
        next_all_tr.val('');
        //next_all_tr.attr("readonly", false);
        if (jQuery(this).hasClass(max_class_dot)) {
            var $this_min_q = jQuery(this).closest('tr' + table_tr_class).find(min_class).val();
            if (parseInt($this_min_q) > parseInt(jQuery(this).val())) {
                alert(alert_message);
                jQuery(this).val('');
                return;
            }
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            next_tr.attr('readonly', true);
            next_tr.val(parseInt(jQuery(this).val()) + 1);
        }
        if (jQuery(this).hasClass(min_class_dot)) {
            jQuery(this).closest('tr').find(max_class).val('');
        }
    });
    //Delete event
    jQuery(document).on('click', '.wco-bo-delete-quantity-rule,.wco-bo-delete-amount-rule,.wco-bo-delete-fixed_cart-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var table_class = '', table_tr_class = '', min_class = '', max_class = '', input_name = '';
        if (jQuery(this).hasClass('wco-bo-delete-quantity-rule')) {
            table_class = jQuery(this).parents('.wco-birthday-percentage-quantity-table');
            table_tr_class = '.wco-birthday-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            input_name = 'birthday_offer_percentage_quantity';
        } else if (jQuery(this).hasClass('wco-bo-delete-amount-rule')) {
            table_class = jQuery(this).parents('.wco-birthday-percentage-amount-table');
            table_tr_class = '.wco-birthday-amount-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'birthday_offer_percentage_amount';
        } else if (jQuery(this).hasClass('wco-bo-delete-fixed_cart-rule')) {
            table_class = jQuery(this).parents('.wco-birthday-fixed_cart-table');
            table_tr_class = '.wco-birthday-fixed_cart-rule-tr';
            min_class = '.min_amount';
            max_class = '.max_amount';
            input_name = 'birthday_offer_fixed_cart';
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr' + table_tr_class);
        var parent_count = jQuery(this).parents('.offers-birthday-table').index();
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr' + table_tr_class).find(max_class);
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            jQuery(this).remove();
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery(table_class).find('tr' + table_tr_class).each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        jQuery(this).attr('name', 'offers_birthday_product[' + parent_count + '][' + input_name + '][' + display_index + '][' + name + ']');
                    }
                });
            });
        });
    });


    //Show tooltip div on hover
    jQuery(document).on('hover', '.wco-help-tip', function () {
        jQuery(this).parent('td,th').siblings('td').find('.wco-field-desc').fadeToggle();
    });

    //Edit inline existing customers
    jQuery('.wco-editinline-offer').click(function (e) {
        e.preventDefault();
        var $this = jQuery(this).closest('tr');
        var data = {
            'action': 'wcoffers_editinline_wu_offer',
            'edit_id': jQuery(this).data('id')
        };
        jQuery('.wco-existing-customer-inline-wrap').slideUp().remove();
        jQuery.post(ajax_object.ajax_url, data, function (response) {
            $this.after(response).slideDown();
            jQuery('.wcoffers_cashback_products').select2();
            jQuery('.offers_existing_user_discount_type').trigger("change");
            $this.hide();
        });
    });

    //cancel inline data
    jQuery(document).on('click', '.wco-existing-customer-inline-wrap .cancel', function () {
        jQuery(this).closest('.wco-existing-customer-inline-wrap').slideUp().remove();
        jQuery('.iedit').show();
    });

    //Update inline data
    jQuery(document).on('click', '.wco-existing-customer-inline-wrap .save', function () {
        var $edit_id = jQuery(this).closest('.wco-existing-customer-inline-wrap').attr('id').split('-').pop();
        var fields = jQuery('#edit-' + $edit_id).find('input[name],select[name]').serialize();
        var $this = jQuery(this);
        jQuery('table.widefat .spinner').addClass('is-active');
        var data = {
            'action': 'wcoffers_save_editinline_wu_offer',
            'edit_id': $edit_id,
            'params': fields
        };
        jQuery.post(ajax_object.ajax_url, data, function (response) {
            if (response != 0) {
                var result = JSON.parse(response);
                var discount_type = result.discount_type;
                var discount_str;
                if (discount_type == 'percent') {
                    discount_str = 'Percentage Discount';
                } else if (discount_type == 'fixed_cart') {
                    discount_str = 'Fixed Cart Discount';
                }
                var user_expiry_number = result.user_expiry_number;
                var user_expiry_time = result.user_expiry_time;
                var last_td;
                if (user_expiry_number == '') {
                    last_td = 'Lifetime';
                } else {
                    last_td = user_expiry_number + ' ' + user_expiry_time + ' (' + result.expiry_date + ')';
                }
                var create_str = '';
                if (typeof result.created_date !== "undefined") {
                    create_str = result.created_date;
                }
                jQuery('tr#wco-offer-' + $edit_id).children('td:nth-child(2)').text('').text(discount_str);
                jQuery('tr#wco-offer-' + $edit_id).children('td:nth-child(3)').text('').text(create_str);
                jQuery('tr#wco-offer-' + $edit_id).children('td:last').text('').text(last_td);
                jQuery('table.widefat .spinner').removeClass('is-active');
                $this.closest('.wco-existing-customer-inline-wrap').slideUp().remove();
                jQuery('.iedit').show();
            }
        });
    });

    //Upsell hide/show
    if (jQuery('.upsell_mode').val() == 'product') {
        jQuery('.upsell_mode_product').show();
    } else {
        jQuery('.upsell_mode_product').hide();
    }
    jQuery('.upsell_mode').change(function () {
        if (jQuery(this).val() == 'product') {
            jQuery('.upsell_mode_product').show();
        } else {
            jQuery('.upsell_mode_product').hide();
        }
    });

    /**
     * Indivisual rule JS
     */
    //On method change show required div
    jQuery('.wco-product-pricing-first-row .wco_product_pricing_method').each(function () {
        if (jQuery(this).val() == 'simple') {
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_bulk_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_method').show();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_group_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_bulk_method').show();
        } else if (jQuery(this).val() == 'bulk') {
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_bulk_method').show();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_group_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_bulk_method').show();
        } else if (jQuery(this).val() == 'group') {
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_bulk_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_group_method').show();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_bulk_method').hide();
        }
    });
    jQuery(document).on('change', '.wco_product_pricing_method', function () {
        if (jQuery(this).val() == 'simple') {
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_bulk_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_method').show();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_group_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_bulk_method').show();
        } else if (jQuery(this).val() == 'bulk') {
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_bulk_method').show();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_group_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_bulk_method').show();
        } else if (jQuery(this).val() == 'group') {
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_bulk_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_method').hide();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_group_method').show();
            jQuery(this).closest('.wco-product-pricing-first-row').siblings('.wco_product_pricing_simple_bulk_method').hide();
        }
    });
    jQuery('.wco_product_condition_content .wco_product_condition_type').each(function () {
        if (jQuery(this).val() == 'products') {
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product').show();
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product_category').hide();
        } else if (jQuery(this).val() == 'products_category') {
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product').hide();
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product_category').show();
        }
    });
    jQuery(document).on('change', '.wco_product_condition_content .wco_product_condition_type', function () {
        var this_val = jQuery(this).val();
        if (this_val == 'products') {
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product').show();
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product_category').hide();
        } else if (this_val == 'products_category') {
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product').hide();
            jQuery(this).parent('.wco_product_condition_content').siblings('.wco_product_condition_type_product_category').show();
        }
    });
    jQuery('#wco_rules_add_row button').click(function () {
        var $count = jQuery('#wco_product_pricing_wrapper').find('.wco-product-pricing-row').length;
        wco_add_product_pricing_block($count);
        jQuery('.wco_product_pricing_method').trigger("change");
    });
    jQuery(document).on('click', '#wco_rules_add_product_row button', function () {
        jQuery(this).parent().siblings('.wco-product-condition-nule').hide();
        var $count = jQuery(this).closest('.wco_product_pricing_products').find('.wco-product-pricing-product-condition-row').length;
        var $parent_count = jQuery(this).closest('.wco-product-pricing-row').index();
        wco_add_product_pricing_product_row($count, $parent_count, jQuery(this));
    });
    //Add range in indivisual offer
    jQuery(document).on('click', '.wco_rules_add_range_row button', function () {
        var $parent_count = jQuery(this).closest('.wco-product-pricing-row').index();
        if (jQuery(this).parent().siblings('.wco-bulk-range-quantity-table').find('.wco-no-rule-found').length > 0)
            jQuery(this).parent().siblings('.wco-bulk-range-quantity-table').find('.wco-no-rule-found').remove();

        var $count = jQuery(this).parent().siblings('.wco-bulk-range-quantity-table').find('.wco-bulk-range-quantity-rule-tr').length;
        var $last_max_quantity = jQuery(this).parent().siblings('.wco-bulk-range-quantity-table').find('tr.wco-bulk-range-quantity-rule-tr').last().find('.max_quantity');
        if ($last_max_quantity.val() == '') {
            alert('Please add last row maximum quantity');
            return;
        }
        wco_add_product_pricing_bulk_range_row($count, $parent_count, jQuery(this));
    });
    //Change rule title on keyup
    jQuery(document).on('keyup', '.wco_product_pricing_private_note', function () {
        if (jQuery(this).val() == '') {
            jQuery(this).parents('.wco-product-pricing-content').siblings('.wco-product-pricing-title').find('.title-span').html('').text('Untitle');
        } else {
            jQuery(this).parents('.wco-product-pricing-content').siblings('.wco-product-pricing-title').find('.title-span').html('').text(jQuery(this).val());
        }
    });
    //Remove rule
    jQuery(document).on('click', '.wco_row_remove', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var $this = jQuery(this),
                $offer = $this.closest('.wco-product-pricing-row');
        $offer.slideUp(250, function () {
            jQuery(this).remove();
        });
    });
    //Remove product rule
    jQuery(document).on('click', '.wco_remove_product_row', function () {
        var $this = jQuery(this),
                $offer = $this.closest('.wco-product-pricing-product-condition-row');
        var $offer_parent = $this.closest('.wco-product-pricing-product-condition-row').parent('.wco_product_pricing_products');
        var parent_count = jQuery(this).parents('.wco-product-pricing-row').index();
        $offer.slideUp(250, function () {
            jQuery(this).remove();
            if ($offer_parent.find('.wco-product-pricing-product-condition-row').length == 0) {
                $offer_parent.find('.wco-product-condition-nule').show();
            }
            $offer_parent.find('.wco-product-pricing-product-condition-row').each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id' || name == 'product_cat') {
                            jQuery(this).attr('name', 'wco_product_pricing[' + parent_count + '][product_condition][' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'wco_product_pricing[' + parent_count + '][product_condition][' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });
    //Delete quantity rule
    jQuery(document).on('click', '.wco-delete-quantity-range-rule', function () {
        if (confirm('Are you sure that you want to remove this discount rule?') === false) {
            return false;
        }
        var table_class = '', table_tr_class = '', min_class = '', max_class = '', input_name = '';
        if (jQuery(this).hasClass('wco-delete-quantity-range-rule')) {
            table_class = '.wco-bulk-range-quantity-table';
            table_tr_class = '.wco-bulk-range-quantity-rule-tr';
            min_class = '.min_quantity';
            max_class = '.max_quantity';
            input_name = 'quantity_range';
        }
        var $this = jQuery(this),
                $offer = $this.closest('tr' + table_tr_class);
        var parent_table = $offer.closest('.wco-bulk-range-quantity-table');
        var parent_count = jQuery(this).parents('.wco-product-pricing-row').index();
        $offer.slideUp(250, function () {
            var next_all_tr = $this.closest('tr').nextAll('tr' + table_tr_class).find(min_class + ',' + max_class + ',.discount_amount');
            var prev_tr = jQuery(this).closest('tr').prev('tr' + table_tr_class).find(max_class);
            next_all_tr.val('');
            var next_tr = jQuery(this).closest('tr').next('tr' + table_tr_class).find(min_class);
            var tr_length = $offer.closest('.wco-bulk-range-quantity-table').find('.wco-bulk-range-quantity-rule-tr').length;
            jQuery(this).remove();
            if (parseInt(tr_length) - 1 == 0) {
                var no_rule_tr = '<tr class="wco-no-rule-found"><td colspan="5" style="text-align: center;padding: 10px 0;">No rules found</td></tr>';
                parent_table.append(no_rule_tr);
            }
            next_tr.val(parseInt(prev_tr.val()) + 1);
            jQuery(table_class).find('tr' + table_tr_class).each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        jQuery(this).attr('name', 'wco_product_pricing[' + parent_count + '][' + input_name + '][' + display_index + '][' + name + ']');
                    }
                });
            });
        });
    });
    //Add accordion
    if (jQuery('#wco_product_pricing_wrapper').length > 0) {
        jQuery('#wco_product_pricing_wrapper').accordion({
            header: "> div > div.wco-product-pricing-title",
            heightStyle: "content",
            active: -1,
            collapsible: true
        });
    }
    //Add product in group
    jQuery(document).on('click', '.wco_rules_add_product_group_row button', function () {
        jQuery(this).parent().siblings('.wco-product-condition-nule').hide();
        var $count = jQuery(this).closest('.wco_product_pricing_products_group').find('.wco-product-pricing-product-group-row').length;
        var $parent_count = jQuery(this).closest('.wco-product-pricing-row').index();
        wco_add_product_pricing_product_group_row($count, $parent_count, jQuery(this));
    });
    //delete product group rule
    jQuery(document).on('click', '.wco_remove_product_group_row', function () {
        var $this = jQuery(this),
                $offer = $this.closest('.wco-product-pricing-product-group-row');
        var $offer_parent = $this.closest('.wco-product-pricing-product-group-row').parent('.wco_product_pricing_products_group');
        var parent_count = jQuery(this).parents('.wco-product-pricing-row').index();
        $offer.slideUp(250, function () {
            jQuery(this).remove();
            if ($offer_parent.find('.wco-product-pricing-product-group-row').length == 0) {
                $offer_parent.find('.wco-product-condition-nule').show();
            }
            $offer_parent.find('.wco-product-pricing-product-group-row').each(function (index) {
                var $this = jQuery(this);
                var display_index = parseInt(index);
                $this.find('input, select').each(function () {
                    var name = jQuery(this).data('name');
                    if (typeof name != 'undefined') {
                        if (name == 'product_id' || name == 'product_cat') {
                            jQuery(this).attr('name', 'wco_product_pricing[' + parent_count + '][group_condition][' + display_index + '][' + name + '][]');
                        } else {
                            jQuery(this).attr('name', 'wco_product_pricing[' + parent_count + '][group_condition][' + display_index + '][' + name + ']');
                        }
                    }
                });
            });
        });
    });
});
function wco_add_product_pricing_bulk_range_row($count, $parent_count, $this) {
    var new_tr = '<tr class="wco-bulk-range-quantity-rule-tr"><td><input data-name="min_quantity" placeholder="5" class="textNumberOnly min_quantity" min="0" max="100" type="text" value="" name="wco_product_pricing[%N%][quantity_range][%NC%][min_quantity]"></td><td><input data-name="max_quantity" placeholder="10 or blank for unlimited quantity" class="textNumberOnly max_quantity" min="0" max="100" type="text" value="" name="wco_product_pricing[%N%][quantity_range][%NC%][max_quantity]"></td><td><select name="wco_product_pricing[%N%][quantity_range][%NC%][discount_type]"><option value="fixed_pricing">Fixed Price</option><option value="percentage">Percentage Discount</option></select></td><td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="" min="0" name="wco_product_pricing[%N%][quantity_range][%NC%][discount_amount]"></td><td><div class="button wco-delete-quantity-range-rule">Remove</div></td></tr>';
    var $offer = new_tr.replace(/%N%/g, $parent_count).replace(/%NC%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = $this.parent().siblings('.wco-bulk-range-quantity-table').find('tr.wco-bulk-range-quantity-rule-tr').last().find('.max_quantity').val();
    }
    $this.parents('.wco_product_pricing_bulk_range').find('.wco-bulk-range-quantity-table > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = $this.parent().siblings('.wco-bulk-range-quantity-table').find('tr.wco-bulk-range-quantity-rule-tr').last().find('.min_quantity');
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}
function wco_add_product_pricing_product_row($count, $parent_count, $this) {
    var $offer = jQuery('.wco-product-pricing-product-condition-wrap').html();
    $offer = $offer.replace(/%N%/g, $parent_count).replace(/%NC%/g, $count);
    var $append = $this.closest('.wco_product_pricing_products').append($offer);
    $append.find('.wco_product_select2').select2();
}
function wco_add_product_pricing_product_group_row($count, $parent_count, $this) {
    var $offer = jQuery('.wco-product-pricing-product-group-wrap').html();
    $offer = $offer.replace(/%N%/g, $parent_count).replace(/%NC%/g, $count);
    var $append = $this.closest('.wco_product_pricing_products_group').append($offer);
    $append.find('.wco_product_select2').select2();
}
function wco_add_product_pricing_block($count) {
    var $offer = jQuery('.wco-product-pricing-wrap').html();
    $offer = $offer.replace(/%N%/g, $count);
    jQuery('#wco_product_pricing_wrapper').append($offer);
    jQuery("#wco_product_pricing_wrapper").accordion("refresh");
    jQuery("#wco_product_pricing_wrapper").accordion("option", "active", -1);
}

function offers_add_product_discount_block($count) {
    var $offer = jQuery('.offers-product-template-wrap').html();
    $offer = $offer.replace(/%N%/g, $count);
    var $append = jQuery('.offers-single-product-wrap').find('.offers-append-products').append($offer);
    $append.find('.wcoffers_single_products').select2();
}

function offers_add_product_cat_discount_block($count) {
    var $offer = jQuery('.offers-product-category-template-wrap').html();
    $offer = $offer.replace(/%N%/g, $count);
    var $append = jQuery('.offers-category-wrap').find('.offers-append-products-cat').append($offer);
    $append.find('.wcoffers_single_products_category').select2();
}

function offers_add_birthday_discount_block($count) {
    var $offer = jQuery('.offers-birthday-template-wrap').html();
    $offer = $offer.replace(/%N%/g, $count);
    var $append = jQuery('.offers-birthday-wrap').find('.offers-append-birthday-discount').append($offer);
    $append.find('.wcoffers_birthday_products').select2();
}

function wcoffers_add_registration_percentage_quantity_block($count) {
    var new_tr = '<tr class="wco-registration-quantity-rule-tr"><td><input data-name="min_quantity" placeholder="5" class="textNumberOnly min_quantity" min="0" max="100" type="text" value="" name="registration_offer_percentage_quantity[%N%][min_quantity]"></td><td><input data-name="max_quantity" placeholder="10 or blank for unlimited quantity" class="textNumberOnly max_quantity" min="0" max="100" type="text" value="" name="registration_offer_percentage_quantity[%N%][max_quantity]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="registration_offer_percentage_quantity[%N%][discount_amount]"></td><td><div class="button wco-reg-delete-quantity-rule">Remove</div></td></tr>';
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = jQuery('.wco-registration-percentage-quantity-table tr.wco-registration-quantity-rule-tr').last().find('.max_quantity').val();
    }
    jQuery('.wco-registration-percentage-quantity-wrap').find('.wco-registration-percentage-quantity-table > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = jQuery('.wco-registration-percentage-quantity-table tr.wco-registration-quantity-rule-tr').last().find('.min_quantity');
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}

function wcoffers_add_registration_percentage_amount_block($count) {
    var new_tr = '<tr class="wco-registration-amount-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="registration_offer_percentage_amount[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="registration_offer_percentage_amount[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="registration_offer_percentage_amount[%N%][discount_amount]"></td><td><div class="button wco-reg-delete-amount-rule">Remove</div></td></tr>';
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = jQuery('.wco-registration-percentage-amount-table tr.wco-registration-amount-rule-tr').last().find('.max_amount').val();
    }
    jQuery('.wco-registration-percentage-amount-wrap').find('.wco-registration-percentage-amount-table > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = jQuery('.wco-registration-percentage-amount-table tr.wco-registration-amount-rule-tr').last().find('.min_amount');
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}

function wcoffers_add_registration_fixed_cart_block($count) {
    var new_tr = '<tr class="wco-registration-fixed_cart-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="registration_offer_fixed_cart[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="registration_offer_fixed_cart[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="registration_offer_fixed_cart[%N%][discount_amount]"></td><td><div class="button wco-reg-delete-fixed_cart-rule">Remove</div></td></tr>';
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = jQuery('.wco-registration-fixed_cart-table tr.wco-registration-fixed_cart-rule-tr').last().find('.max_amount').val();
    }
    jQuery('.wco-registration-fixed_cart-wrap').find('.wco-registration-fixed_cart-table > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = jQuery('.wco-registration-fixed_cart-table tr.wco-registration-fixed_cart-rule-tr').last().find('.min_amount');
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}

function wcoffers_add_second_purchase_block($count, current_class, table_class, table_tr_class, min_class, max_class) {
    var new_tr = '', table_wrap = '';
    if (current_class.indexOf("wco-add-second-purchase-percentage-quantity") >= 0) {
        new_tr = '<tr class="wco-second-purchase-quantity-rule-tr"><td><input data-name="min_quantity" placeholder="e.g. 5" class="textNumberOnly min_quantity" min="0" type="text" value="" name="second_purchase_offer_percentage_quantity[%N%][min_quantity]"></td><td><input data-name="max_quantity" placeholder="10 or blank for unlimited quantity" class="textNumberOnly max_quantity" min="0" type="text" value="" name="second_purchase_offer_percentage_quantity[%N%][max_quantity]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" max="100" name="second_purchase_offer_percentage_quantity[%N%][discount_amount]"></td><td><div class="button wco-sp-delete-quantity-rule">Remove</div></td></tr>';
        table_wrap = '.wco-second-purchase-percentage-quantity-wrap';
    } else if (current_class.indexOf("wco-add-second-purchase-percentage-amount") >= 0) {
        new_tr = '<tr class="wco-second-purchase-amount-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="second_purchase_offer_percentage_amount[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="second_purchase_offer_percentage_amount[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="second_purchase_offer_percentage_amount[%N%][discount_amount]"></td><td><div class="button wco-sp-delete-amount-rule">Remove</div></td></tr>';
        table_wrap = '.wco-second-purchase-percentage-amount-wrap';
    } else if (current_class.indexOf("wco-add-second-purchase-fixed_cart") >= 0) {
        new_tr = '<tr class="wco-second-purchase-fixed_cart-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="second_purchase_offer_fixed_cart[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="second_purchase_offer_fixed_cart[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="second_purchase_offer_fixed_cart[%N%][discount_amount]"></td><td><div class="button wco-sp-delete-fixed_cart-rule">Remove</div></td></tr>';
        table_wrap = '.wco-second-purchase-fixed_cart-wrap';
    }
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = jQuery(table_class + ' tr' + table_tr_class).last().find(max_class).val();
    }
    jQuery(table_wrap).find(table_class + ' > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = jQuery(table_class + ' tr' + table_tr_class).last().find(min_class);
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}

function wcoffers_add_inactive_customer_block($count, current_class, table_class, table_tr_class, min_class, max_class) {
    var new_tr = '', table_wrap = '';
    if (current_class.indexOf("wco-add-inactive-customer-percentage-quantity") >= 0) {
        new_tr = '<tr class="wco-inactive-customer-quantity-rule-tr"><td><input data-name="min_quantity" placeholder="e.g. 5" class="textNumberOnly min_quantity" min="0" type="text" value="" name="inactive_customer_offer_percentage_quantity[%N%][min_quantity]"></td><td><input data-name="max_quantity" placeholder="10 or blank for unlimited quantity" class="textNumberOnly max_quantity" min="0" type="text" value="" name="inactive_customer_offer_percentage_quantity[%N%][max_quantity]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" max="100" name="inactive_customer_offer_percentage_quantity[%N%][discount_amount]"></td><td><div class="button wco-ic-delete-quantity-rule">Remove</div></td></tr>';
        table_wrap = '.wco-inactive-customer-percentage-quantity-wrap';
    } else if (current_class.indexOf("wco-add-inactive-customer-percentage-amount") >= 0) {
        new_tr = '<tr class="wco-inactive-customer-amount-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="inactive_customer_offer_percentage_amount[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="inactive_customer_offer_percentage_amount[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="inactive_customer_offer_percentage_amount[%N%][discount_amount]"></td><td><div class="button wco-ic-delete-amount-rule">Remove</div></td></tr>';
        table_wrap = '.wco-inactive-customer-percentage-amount-wrap';
    } else if (current_class.indexOf("wco-add-inactive-customer-fixed_cart") >= 0) {
        new_tr = '<tr class="wco-inactive-customer-fixed_cart-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="inactive_customer_offer_fixed_cart[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="inactive_customer_offer_fixed_cart[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="inactive_customer_offer_fixed_cart[%N%][discount_amount]"></td><td><div class="button wco-ic-delete-fixed_cart-rule">Remove</div></td></tr>';
        table_wrap = '.wco-inactive-customer-fixed_cart-wrap';
    }
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = jQuery(table_class + ' tr' + table_tr_class).last().find(max_class).val();
    }
    jQuery(table_wrap).find(table_class + ' > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = jQuery(table_class + ' tr' + table_tr_class).last().find(min_class);
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}

function wcoffers_add_existing_customer_block($count, current_class, table_class, table_tr_class, min_class, max_class, $this) {
    var new_tr = '', table_wrap = '';
    if (current_class.indexOf("wco-add-existing-customer-percentage-quantity") >= 0) {
        new_tr = '<tr class="wco-existing-customer-quantity-rule-tr"><td><input data-name="min_quantity" placeholder="e.g. 5" class="textNumberOnly min_quantity" min="0" type="text" value="" name="existing_customer_offer_percentage_quantity[%N%][min_quantity]"></td><td><input data-name="max_quantity" placeholder="10 or blank for unlimited quantity" class="textNumberOnly max_quantity" min="0" type="text" value="" name="existing_customer_offer_percentage_quantity[%N%][max_quantity]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" max="100" name="existing_customer_offer_percentage_quantity[%N%][discount_amount]"></td><td><div class="button wco-ec-delete-quantity-rule">Remove</div></td></tr>';
        table_wrap = '.wco-existing-customer-percentage-quantity-wrap';
    } else if (current_class.indexOf("wco-add-existing-customer-percentage-amount") >= 0) {
        new_tr = '<tr class="wco-existing-customer-amount-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="existing_customer_offer_percentage_amount[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="existing_customer_offer_percentage_amount[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="existing_customer_offer_percentage_amount[%N%][discount_amount]"></td><td><div class="button wco-ec-delete-amount-rule">Remove</div></td></tr>';
        table_wrap = '.wco-existing-customer-percentage-amount-wrap';
    } else if (current_class.indexOf("wco-add-existing-customer-fixed_cart") >= 0) {
        new_tr = '<tr class="wco-existing-customer-fixed_cart-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="existing_customer_offer_fixed_cart[%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="existing_customer_offer_fixed_cart[%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat" type="text" value="" min="0" name="existing_customer_offer_fixed_cart[%N%][discount_amount]"></td><td><div class="button wco-ec-delete-fixed_cart-rule">Remove</div></td></tr>';
        table_wrap = '.wco-existing-customer-fixed_cart-wrap';
    }
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = jQuery(table_class + ' tr' + table_tr_class).last().find(max_class).val();
    }
    $this.closest('tr' + table_wrap).find(table_class + ' > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = jQuery(table_class + ' tr' + table_tr_class).last().find(min_class);
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}

function wcoffers_add_birthday_block($count, current_class, table_class, table_tr_class, min_class, max_class, parent_count, $this) {
    var new_tr = '', table_wrap = '';
    if (current_class.indexOf("wco-add-birthday-percentage-quantity") >= 0) {
        new_tr = '<tr class="wco-birthday-quantity-rule-tr"><td><input data-name="min_quantity" placeholder="e.g. 5" class="textNumberOnly min_quantity" min="0" type="text" value="" name="offers_birthday_product[' + parent_count + '][birthday_offer_percentage_quantity][%N%][min_quantity]"></td><td><input data-name="max_quantity" placeholder="10 or blank for unlimited quantity" class="textNumberOnly max_quantity" min="0" type="text" value="" name="offers_birthday_product[' + parent_count + '][birthday_offer_percentage_quantity][%N%][max_quantity]"></td><td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="" min="0" max="100" name="offers_birthday_product[' + parent_count + '][birthday_offer_percentage_quantity][%N%][discount_amount]"></td><td><div class="button wco-bo-delete-quantity-rule">Remove</div></td></tr>';
        table_wrap = '.wco-birthday-percentage-quantity-wrap';
    } else if (current_class.indexOf("wco-add-birthday-percentage-amount") >= 0) {
        new_tr = '<tr class="wco-birthday-amount-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="offers_birthday_product[' + parent_count + '][birthday_offer_percentage_amount][%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="offers_birthday_product[' + parent_count + '][birthday_offer_percentage_amount][%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="" min="0" name="offers_birthday_product[' + parent_count + '][birthday_offer_percentage_amount][%N%][discount_amount]"></td><td><div class="button wco-bo-delete-amount-rule">Remove</div></td></tr>';
        table_wrap = '.wco-birthday-percentage-amount-wrap';
    } else if (current_class.indexOf("wco-add-birthday-fixed_cart") >= 0) {
        new_tr = '<tr class="wco-birthday-fixed_cart-rule-tr"><td><input data-name="min_amount" placeholder="e.g. 500" class="textNumberOnly min_amount" min="0" type="text" value="" name="offers_birthday_product[' + parent_count + '][birthday_offer_fixed_cart][%N%][min_amount]"></td><td><input data-name="max_amount" placeholder="1000 or blank for unlimited amount" class="textNumberOnly max_amount" min="0" type="text" value="" name="offers_birthday_product[' + parent_count + '][birthday_offer_fixed_cart][%N%][max_amount]"></td><td><input data-name="discount_amount" class="textNumberWithFloat discount_amount" type="text" value="" min="0" name="offers_birthday_product[' + parent_count + '][birthday_offer_fixed_cart][%N%][discount_amount]"></td><td><div class="button wco-bo-delete-fixed_cart-rule">Remove</div></td></tr>';
        table_wrap = '.wco-birthday-fixed_cart-wrap';
    }
    var $offer = new_tr.replace(/%N%/g, $count);
    if ($count != 0) {
        var $last_range_before_append = $this.next(table_class).find('tr' + table_tr_class).last().find(max_class).val();
    }
    $this.closest(table_wrap).find(table_class + ' > tbody').append($offer);
    if ($count == 0) {
    } else {
        var $last_range = $this.next(table_class).find('tr' + table_tr_class).last().find(min_class);
        $last_range.attr('readonly', true);
        $last_range.val(parseInt($last_range_before_append) + 1);
    }
}