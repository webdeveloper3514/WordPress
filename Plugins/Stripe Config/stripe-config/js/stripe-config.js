jQuery(function ($) {
    var planPrice;
    $("input[name='stripe_plan_amount']").focusout(function () {
        planPrice = $("input[name='stripe_plan_amount']").val();
        $("input[name='stripe_plan_amount']").val(Number(planPrice).toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD'
        }));
    });
    var fee;
    $("input[name='stripe_pay_amount']").focusout(function () {
        fee = $("input[name='stripe_pay_amount']").val();
        $("input[name='stripe_pay_amount']").val(Number(fee).toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD'
        }));
    });
    $(document).on('keyup', "input[name='stripe_plan_desc']", function () {
        var val = $(this).val();
        if (val.length > 22) {
            val = val.substring(0, val.length - 1);
            $(this).val(val);
            $(this).focus();
            return false;
        }
    });
    
    $(document).on('keypress', "#modify_stripe_webhook_id,#modify_stripe_webhook_id", function (e) {
        var regex = new RegExp("^[A-Za-z0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);

        if (regex.test(str)) {
            return true;
        }
        e.preventDefault();
        return false;
    });
    $(document).on('click', '.btn-delete-webhook', function () {
        if (confirm('Are you sure.?') === false) {
            return false;
        }
        var $this = $(this);
        $('.sc-loader').show();
        var id = $(this).data('id');
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_delete_webhook',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                $('.alert.alert-danger').hide();
                $('.alert.alert-success').hide();
                if (response != 0) {
                    $this.closest('tr').remove();
                    $('.main-container').prepend(response);
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                }
            });
        }
    });
    $(document).on('click', '.btn-modify-webhook', function () {
        $('.sc-loader').show();
        var id = $(this).data('id');
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_edit_webhook',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                $('.sc-loader').hide();
                if (response != 0) {
                    var data = JSON.parse(response);
                    $('#modify-webhook').find('#stripe_send_url').val(data['stripe_send_url']);
                    $('#modify-webhook').find('#modify_stripe_webhook_id').val(data['modify_stripe_webhook_id']);
                    $('#modify-webhook').find('#modify_stripe_webhook_data_id').val(id);
                    if (data['modify_stripe_data']['id'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_id').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_id').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['name'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_name').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_name').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['email'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_email').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_email').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['description'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_description').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_description').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['address_zip'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_zipcode').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_zipcode').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['address_state'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_state').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_state').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['address_line1'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_address').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_address').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['address_country'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_country').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_country').prop('checked', false);
                    }
                    if (data['modify_stripe_data']['address_city'] == 1) {
                        $('#modify-webhook').find('#chk_stripe_city').prop('checked', true);
                    } else {
                        $('#modify-webhook').find('#chk_stripe_city').prop('checked', false);
                    }
                    $('#modify-webhook').modal('show');
                } else {
                    alert('Webhook not exists.');
                }
            });
        }
    });
    $('.panel-heading > .panel-title > a').click(function () {
        var tooltip = $(this).data('tooltip');
        $('#tooltip-dialog').find('.modal-body').html('').html(tooltip);
        $('#tooltip-dialog').modal('show');
    });
    $('#btn-add-new-funnel').click(function () {
        $('#funnel-edit-add-form')[0].reset();
        $("#btn-add-new-funnel").fadeOut();
        $("#btn-funnel-cancel").slideDown();
        $("#btn-funnel-save").slideDown();
        $(".table.sc-funnel").fadeOut();
        setTimeout(function () {
            $(".funnel-edit-add").slideDown();
        }, 600);
        $('#funnel-edit-add-form').find('.funnel-checkout-step-count').each(function () {
            if ($(this).hasClass('funnel-checkout') || $(this).hasClass('funnel-step-2')) {
            } else {
                $(this).remove();
            }
        });
        $('.funnel-step-page').prop('disabled', true);
    });
    $('#btn-funnel-cancel').click(function () {
        $('#funnel-edit-add-form').find('input[name=edit_id_funnel]').remove();
        $("#btn-funnel-cancel").slideUp();
        $("#btn-funnel-save").slideUp();
        $(".funnel-edit-add").slideUp();
        $(".table.sc-funnel").slideDown();
        $("#btn-add-new-funnel").slideDown();
    });
    //Edit funnel
    $(document).on('click', '.btn-edit-funnel', function () {
        $('.sc-loader').show();
        var id = $(this).data('id');
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_edit_funnel',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                if (response != 0) {
                    $('.sc-loader').hide();
                    var data = JSON.parse(response);
                    $('.funnel-edit-add').find('form').append('<input type="hidden" value="' + id + '" name="edit_id_funnel">');
                    $('#funnel-edit-add-form').find('input[name=funnel-title]').val(data['funnel_title']);
                    $('#funnel-edit-add-form').find('.funnel-checkout-page').val(data['checkout_page']);
                    display_available_classes(data['checkout_page'], $('#funnel-edit-add-form').find('.funnel-checkout-page'), data['checkout_class']);
                    $('#funnel-edit-add-form').find("input[name=funnel-checkout-class][value='" + data['checkout_class'] + "']").prop('checked', true);
                    $('#funnel-edit-add-form').find('.funnel-checkout-app-hidden').val(data['checkout_app']);
                    $('#funnel-edit-add-form').find("input[name=funnel-step_2-page]").prop('disabled', true);
                    $('#funnel-edit-add-form').find("input[name=funnel-step_2-page][value='" + data['step_2_page'] + "']").prop('checked', true);
                    $('#funnel-edit-add-form').find("input[name=funnel-step_2-page][value='" + data['step_2_page'] + "']").prop('disabled', false);
                    $('#funnel-edit-add-form').find("input[name=funnel-step_2-class][value='" + data['step_2_class'] + "']").prop('checked', true);
                    $('#funnel-edit-add-form').find('input[name=funnel-step_2-app-hidden]').val(data['step_2_app']);
                    display_available_classes(data['step_2_page'], $('#funnel-edit-add-form').find('.funnel-step-page'), data['step_2_class']);
                    $('#funnel-edit-add-form').find('.funnel-checkout-step-count').each(function () {
                        if ($(this).hasClass('funnel-checkout') || $(this).hasClass('funnel-step-2')) {
                        } else {
                            $(this).remove();
                        }
                    });
                    if (data['funnel_step']) {
                        $.each(data['funnel_step'], function (key, value) {
                            var step_length = key;
                            var $clone = $('.funnel-edit-add').find('.funnel-step-2').clone().html(function (i, oldHTML) {
                                return oldHTML.replace(/funnel-step_2-class/g, 'funnel_step[' + step_length + '][funnel-step-class]').replace(/funnel-step_2-page/g, 'funnel_step[' + step_length + '][funnel-step-page]').replace(/funnel-step_2-app-hidden/g, 'funnel_step[' + step_length + '][funnel-step-app-hidden]').replace(/step_2/g, 'step_' + step_length).replace(/Step 2/g, 'Step ' + step_length);
                            });
                            $clone.removeClass('funnel-step-2').addClass('funnel-step-' + step_length);
                            $clone.find('.funnel-step-number').val(step_length);
                            $clone.find('.checkout-text').append('<span class="sc-delete-funnel-step"><i class="fa fa-times-circle" aria-hidden="true"></i></span>');
                            $clone.find(".funnel-step-page").prop('disabled', true);
                            $clone.find(".funnel-step-page[value='" + value['funnel-step-page'] + "']").prop('checked', true);
                            $clone.find(".funnel-step-page[value='" + value['funnel-step-page'] + "']").prop('disabled', false);
                            $clone.find(".funnel-step-class[value='" + value['funnel-step-class'] + "']").prop('checked', true);
                            $clone.find('.funnel-step-app-hidden').val(value["funnel-step-app-hidden"]);
                            $('#funnel-edit-add-form').append($clone);
                            display_available_classes(value['funnel-step-page'], $clone.find('.funnel-step-page'), value['funnel-step-class']);
                        });
                    }
                    //open edit option
                    $("#btn-add-new-funnel").fadeOut();
                    $("#btn-funnel-cancel").slideDown();
                    $("#btn-funnel-save").slideDown();
                    $(".table.sc-funnel").fadeOut();
                    setTimeout(function () {
                        $(".funnel-edit-add").slideDown();
                    }, 600);
                } else {
                    $('.sc-loader').hide();
                    alert('Funnel not exists.');
                }
            });
        }
    });
    $(document).on('click', '.btn-delete-funnel', function () {
        if (confirm('Are you sure.?') === false) {
            return false;
        }
        $('.sc-loader').show();
        var $this = $(this);
        var id = $(this).data('id');
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_delete_funnel',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                $('.alert.alert-danger').hide();
                $('.alert.alert-success').hide();
                if (response != 0) {
                    $this.closest('tr').remove();
                    $('.main-container').prepend(response);
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                }
            });
        }
    });
    //Checkout js
    $(document).on('click', '.btn-assign-checkout-funnel', function () {
        if (!$(this).closest('.funnel-checkout').find(".funnel-checkout-class").is(":checked")) {
            alert('Please select any class to assign plan and payments');
            return false;
        }
        var selected_page = $('.funnel-checkout').find('.funnel-checkout-page').val();
        $('#funnel-checkout-add-edit').find('form')[0].reset();
        var selected = $("input[type='radio'][name='funnel-checkout-class']:checked").val();
        if (selected) {
            $('#funnel-checkout-add-edit').find('.funnel-co-app-class').val(selected);
            $('#funnel-checkout-add-edit').find('.funnel-co-app-class').prop('disabled', true);
        }
        if ($('#funnel-edit-add-form').find('input[name=edit_id_funnel]').length > 0 || $('#funnel-checkout-add-edit').find('.funnel-checkout-app-hidden').val() != '') {
            var checkout_app = $('.funnel-checkout-app-hidden').val();
            var hash = checkout_app,
                    split = hash.split('&');
            var obj = {};
            for (var i = 0; i < split.length; i++) {
                var kv = split[i].split('=');
                obj[kv[0]] = decodeURIComponent(kv[1] ? kv[1].replace(/\+/g, ' ') : kv[1]);
            }
            $('#funnel-checkout-add-edit').find('.funnel-co-app-plan-fee').val(obj['funnel-co-app-plan-fee']);
            $('#funnel-checkout-add-edit').find('.funnel-co-app-plan').val(obj['funnel-co-app-plan']);
            $('#funnel-checkout-add-edit').find('.funnel-co-app-page').val(obj['funnel-co-app-page']);
            $('#funnel-checkout-add-edit').find('.funnel-co-app-webhook').val(obj['funnel-co-app-webhook']);
            $('#funnel-checkout-add-edit').find('.funnel-co-app-logo').val(obj['funnel-co-app-logo']);
        }
        $("select.funnel-co-app-page option").prop('disabled', false);
        $("select.funnel-co-app-page option[value*='" + selected_page + "']").prop('disabled', true);
        $('#funnel-checkout-add-edit').modal('show');
    });
    $(document).on('click', '.funnel-checkout-app', function () {
        if ($(this).closest('#funnel-checkout-add-edit').find('.funnel-co-app-page').val() == '') {
            alert('Please select redirection page');
            return false;
        }
        var form_data = $(this).closest('form').serialize();
        $('.funnel-checkout').find('.funnel-checkout-app-hidden').val(form_data);
        var redirect_page = $('#funnel-checkout-add-edit').find('.funnel-co-app-page').val();
        $('.funnel-checkout-step-count.funnel-checkout').nextAll('.funnel-checkout-step-count').each(function () {
            $(this).find(".funnel-step-page").prop('checked', false);
            $(this).find(".funnel-available-class-div").html('').html('No classes available');
            $(this).find(".funnel-step-page").prop("disabled", true);
            $(this).find(".funnel-step-app-hidden").val('');
        });
        $('.funnel-step-2').find(".funnel-step-page").prop('checked', false);
        $('.funnel-step-2').find(".funnel-available-class-div").html('').html('No classes available');
        $('.funnel-step-2').find(".funnel-step-page").prop("disabled", true);
        $('.funnel-step-2').find(".funnel-step-page[value=" + redirect_page + "]").prop("disabled", false);
        $('#funnel-checkout-add-edit').modal('hide');
    });

    var frame,
            metaBox = $('#funnel-checkout-add-edit'),
            addImgLink = metaBox.find('.funnel-co-app-logo');

    jQuery('.funnel-logo-button').on('click', function (event) {
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            // Send the attachment id to our hidden input
            addImgLink.val(attachment.url);
        });
        // Finally, open the modal on click
        frame.open();
    });
    //Step 2 js
    $(document).on('click', '.btn-assign-step_2-funnel', function () {
        if (!$(this).closest('.funnel-checkout-step-count').find(".funnel-step-class").is(":checked")) {
            alert('Please select any class to assign plan and payments');
            return false;
        }
        $('#funnel-step_2-add-edit').find('form')[0].reset();
        var selected = $("input[type='radio'][name='funnel-step_2-class']:checked").val();
        if (selected) {
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-class').val(selected);
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-class').prop('disabled', true);
        }
        $('#funnel-step_2-add-edit').find('.funnel-step-app-number').val('');
        if ($('#funnel-edit-add-form').find('input[name=edit_id_funnel]').length > 0 || $('#funnel-edit-add-form').find('input[name=funnel-step_2-app-hidden]').val() != '') {
            var step_2_app = $('input[name=funnel-step_2-app-hidden]').val();
            var hash = step_2_app,
                    split = hash.split('&');
            var obj = {};
            for (var i = 0; i < split.length; i++) {
                var kv = split[i].split('=');
                obj[kv[0]] = decodeURIComponent(kv[1] ? kv[1].replace(/\+/g, ' ') : kv[1]);
            }
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-plan-fee').val(obj['funnel-step_2-app-plan-fee']);
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-plan').val(obj['funnel-step_2-app-plan']);
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-page').val(obj['funnel-step_2-app-page']);
            if (obj['funnel_step_2_last_page'] == 'on') {
                $('#funnel-step_2-add-edit').find('input[name=funnel_step_2_last_page]').prop('checked', true);
            } else {
                $('#funnel-step_2-add-edit').find('input[name=funnel_step_2_last_page]').prop('checked', false);
            }
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-webhook').val(obj['funnel-step_2-app-webhook']);
        }
        var checkout_page = $('.funnel-checkout').find('.funnel-checkout-page').val();
        var step_2_page = $(this).closest('.funnel-checkout-step-count').find(".funnel-step-page:checked").val();
        $("select.funnel-step_2-app-page option").prop('disabled', false);
        $("select.funnel-step_2-app-page option[value*='" + checkout_page + "']").prop('disabled', true);
        $("select.funnel-step_2-app-page option[value*='" + step_2_page + "']").prop('disabled', true);
        $('#funnel-step_2-add-edit').modal('show');
    });
    $(document).on('click', '.funnel-step_2-app', function () {
        var form_data = $(this).closest('form').serialize();
        var step_no = $(this).siblings('.funnel-step-app-number').val();
        if (step_no) {
            $('.funnel-checkout-step-count.funnel-step-' + step_no).find('.funnel-step-app-hidden').val(form_data);
        } else {
            step_no = 2;
            $('.funnel-step-2').find('.funnel-step-app-hidden').val(form_data);
        }
        $('.funnel-checkout-step-count.funnel-step-' + step_no).nextAll('.funnel-checkout-step-count').each(function () {
            $(this).find(".funnel-step-page").prop('checked', false);
            $(this).find(".funnel-available-class-div").html('').html('No classes available');
            $(this).find(".funnel-step-page").prop("disabled", true);
            $(this).find(".funnel-step-app-hidden").val('');
        });
        var next_div = step_no + 1;
        var next_select = $(this).closest('form').find('select.funnel-step_2-app-page').val();
        $('.funnel-checkout-step-count.funnel-step-' + next_div).find(".funnel-step-page[value=" + next_select + "]").prop("disabled", false);
        $('#funnel-step_2-add-edit').modal('hide');
    });
    //Multiple js click
    $(document).on('click', '.btn-assign-step-funnel', function () {
        if ($(this).hasClass('btn-assign-step_2-funnel'))
            return;
        if (!$(this).closest('.funnel-checkout-step-count').find(".funnel-step-class").is(":checked")) {
            alert('Please select any class to assign plan and payments');
            return false;
        }
        var closest_div = $(this).closest('.funnel-checkout-step-count');
        var step_number = closest_div.find('.funnel-step-number').val();
        $('#funnel-step_2-add-edit').find('form')[0].reset();
        $('#funnel-step_2-add-edit').find('.funnel-step-app-number').val('');
        var selected = closest_div.find('.funnel-step-class:checked').val();
        if (selected) {
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-class').val(selected);
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-class').prop('disabled', true);
        }
        $('#funnel-step_2-add-edit').find('.funnel-step-app-number').val(step_number);
        if ($('#funnel-edit-add-form').find('input[name=edit_id_funnel]').length > 0 || closest_div.find('.funnel-step-app-hidden').val() != '') {
            var step_2_app = closest_div.find('.funnel-step-app-hidden').val();
            var hash = step_2_app,
                    split = hash.split('&');
            var obj = {};
            for (var i = 0; i < split.length; i++) {
                var kv = split[i].split('=');
                obj[kv[0]] = decodeURIComponent(kv[1] ? kv[1].replace(/\+/g, ' ') : kv[1]);
            }
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-plan-fee').val(obj['funnel-step_2-app-plan-fee']);
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-plan').val(obj['funnel-step_2-app-plan']);
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-page').val(obj['funnel-step_2-app-page']);
            if (obj['funnel_step_2_last_page'] == 'on') {
                $('#funnel-step_2-add-edit').find('input[name=funnel_step_2_last_page]').prop('checked', true);
            } else {
                $('#funnel-step_2-add-edit').find('input[name=funnel_step_2_last_page]').prop('checked', false);
            }
            $('#funnel-step_2-add-edit').find('.funnel-step_2-app-webhook').val(obj['funnel-step_2-app-webhook']);
        }
        var checkout_page = $('.funnel-checkout').find('.funnel-checkout-page').val();
        $('.funnel-checkout-step-count').find(".funnel-step-page").each(function () {
            if ($(this).is(":checked")) {
                var checked_val = $(this).val();
                $('#funnel-step_2-add-edit').find("select.funnel-step_2-app-page option[value*='" + checked_val + "']").prop('disabled', true);
            }
        });
        var step_page = closest_div.find(".funnel-step-page:checked").val();
        $('#funnel-step_2-add-edit').find("select.funnel-step_2-app-page option[value*='" + step_page + "']").prop('disabled', true);
        $('#funnel-step_2-add-edit').find("select.funnel-step_2-app-page option[value*='" + checkout_page + "']").prop('disabled', true);
        $('#funnel-step_2-add-edit').modal('show');
    });
    //On click submit form
    $("#btn-funnel-save").click(function () {
        $("#funnel-edit-add-form").submit();
    });
    //Funnel add new step
    $(document).on('click', '.sc-create-funnel-step', function (e) {
        e.preventDefault();
        if (parseInt($('table.sc_pages_list').find('tr').length) - 2 > $('.funnel-edit-add').find('.funnel-checkout-step-count').length) {
        } else {
            alert('No more steps are available');
            return false;
        }
        var toatl_step_le = parseInt($('.funnel-edit-add').find('.funnel-checkout-step-count').length);
        var previous_step = $('.funnel-edit-add').find('.funnel-step-' + toatl_step_le).find('.funnel-step-app-hidden').val();
        if (previous_step == '') {
            alert('Please assign plan and payments of last step');
            return false;
        }
        var step_length = parseInt($('.funnel-edit-add').find('.funnel-checkout-step-count').length) + 1;
        var $clone = $('.funnel-edit-add').find('.funnel-step-2').clone().html(function (i, oldHTML) {
            return oldHTML.replace(/funnel-step_2-class/g, 'funnel_step[' + step_length + '][funnel-step-class]').replace(/funnel-step_2-page/g, 'funnel_step[' + step_length + '][funnel-step-page]').replace(/funnel-step_2-app-hidden/g, 'funnel_step[' + step_length + '][funnel-step-app-hidden]').replace(/step_2/g, 'step_' + step_length).replace(/Step 2/g, 'Step ' + step_length);
        });
        $clone.removeClass('funnel-step-2').addClass('funnel-step-' + step_length);
        $clone.find('.checkout-text').append('<span class="sc-delete-funnel-step"><i class="fa fa-times-circle" aria-hidden="true"></i></span>');
        $clone.find('.funnel-step-number').val(step_length);
        $clone.find('.funnel-step-app-hidden').val('');
        $('#funnel-edit-add-form').append($clone);
        var hash = previous_step,
                split = hash.split('&');
        var obj = {};
        for (var i = 0; i < split.length; i++) {
            var kv = split[i].split('=');
            obj[kv[0]] = decodeURIComponent(kv[1] ? kv[1].replace(/\+/g, ' ') : kv[1]);
        }
        $('#funnel-edit-add-form .funnel-step-' + step_length).find(".funnel-step-page").prop('checked', false);
        $('#funnel-edit-add-form .funnel-step-' + step_length).find(".funnel-available-class-div").html('').html('No classes available');
        $('#funnel-edit-add-form .funnel-step-' + step_length).find(".funnel-step-page").prop("disabled", true);
        $('#funnel-edit-add-form .funnel-step-' + step_length).find(".funnel-step-page[value=" + obj['funnel-step_2-app-page'] + "]").prop("disabled", false);
        var $scroller = $('#plans-payments-new .panel-body');
        var scrollTo = $('.funnel-step-' + step_length).position().left;
        $scroller.animate({'scrollLeft': scrollTo}, 500);
    });
    //Funnel delete
    $(document).on('click', '.sc-delete-funnel-step', function (e) {
        if (confirm('Are you sure, you want to remove this step?') === false) {
            return false;
        }
        var delete_step_no = $(this).closest('.funnel-checkout-step-count').find('.funnel-step-number').val();
        $(this).closest('.funnel-checkout-step-count').nextAll('.funnel-checkout-step-count').each(function (index, value) {
            $(this).find('.checkout-text').children('.sc-step-number').text('').text('Step ' + delete_step_no);
            $(this).find('.funnel-step-number').val(delete_step_no);
            $(this).find('.funnel-step-page').attr('name', 'funnel_step[' + delete_step_no + '][funnel-step-page]');
            $(this).find('.funnel-step-class').attr('name', 'funnel_step[' + delete_step_no + '][funnel-step-class]');
            $(this).find('.funnel-step-app-hidden').attr('name', 'funnel_step[' + delete_step_no + '][funnel-step-app-hidden]');
            delete_step_no = parseInt(delete_step_no) + 1;
        });
        $(this).closest('.funnel-checkout-step-count').slideUp('slow').remove();
    });
    //Edit plan
    $(document).on('click', '.sc-edit-plan', function () {
        $('.sc-loader').show();
        var id = $(this).data('id');
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_edit_plan',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                $('#add-plan-m').find('form').find('input[name=edit_id_plan]').remove();
                if (response != 0) {
                    $('.sc-loader').hide();
                    var data = JSON.parse(response);
                    $('#add-plan-m').find('form').append('<input type="hidden" value="' + id + '" name="edit_id_plan">');
                    $('#add-plan-m').find('input[name=stripe_plan_name]').val(data['plan_name']);
                    $('#add-plan-m').find('input[name=stripe_plan_desc]').val(data['description']);
                    $('#add-plan-m').find('input[name=stripe_plan_id]').val(data['plan_id']);
                    $('#add-plan-m').find('input[name=stripe_plan_page]').val(data['checkout_page']);
                    $('#add-plan-m').find('input[name=stripe_redirect_page]').val(data['redirect_page']);
                    var plan_price = data['plan_price'];
                    var plan_dot = plan_price.slice(0, -2) + "." + plan_price.slice(-2);
                    $('#add-plan-m').find('input[name=stripe_plan_amount]').val(Number(plan_dot).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }));
                    if (data['plan_interval'] == 0 || data['plan_interval'] == 1 || data['plan_interval'] == 30 || data['plan_interval'] == 360 || data['plan_interval'] == 7 || data['plan_interval'] == 90 || data['plan_interval'] == 180) {
                        if (data['plan_interval'] == 0) {
                            data['plan_interval'] = '';
                        }
                        var plan_interval = data['plan_interval'];
                        $('#add-plan-m').find('input[name=stripe_plan_custom_trial]').val('');
                        $('#add-plan-m').find('.stripe_plan_trial_custom_data').hide();
                    } else {
                        var plan_interval = 'custom';
                        $('#add-plan-m').find('input[name=stripe_plan_custom_trial]').val(data['plan_interval']);
                        $('#add-plan-m').find('.stripe_plan_trial_custom_data').show();
                    }
                    $('#add-plan-m').find('#stripe_plan_trial_select').val(plan_interval).prop("disabled", true);
                    $('#add-plan-m').find('input[name=stripe_plan_trial]').val(data['plan_trial']);
                    $('#add-plan-m').find('.modal-title').text('').text('Edit Plan:');
                    $('#add-plan-m').modal('show');
                } else {
                    $('.sc-loader').hide();
                    alert('Plan not exists.');
                }
            });
        }
    });
    //Add new plan
    $(document).on('click', '#add-new-plan', function () {
        $('#add-plan-m').find('form')[0].reset();
        $('#add-plan-m').find('form').find('input[name=edit_id_plan]').remove();
        $('#add-plan-m').find('#stripe_plan_trial_select').val('').prop("disabled", false);
        $('#add-plan-m').find('.modal-title').text('').text('Create Plan:');
        $('#add-plan-m').modal('show');
    });
    //Edit payment    
    $(document).on('click', '.sc-edit-payment', function () {
        $('.sc-loader').show();
        var id = $(this).data('id');
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_edit_payment',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                if (response != 0) {
                    $('.sc-loader').hide();
                    var data = JSON.parse(response);
                    var fee_amount = data['fee_amount'];
                    var fee_dot = fee_amount.slice(0, -2) + "." + fee_amount.slice(-2);
                    $('#add-price-m').find('form').append('<input type="hidden" value="' + id + '" name="edit_id_payment">');
                    $('#add-price-m').find('input[name=stripe_pay_name]').val(data['name']);
                    $('#add-price-m').find('input[name=stripe_pay_desc]').val(data['description']);
                    $('#add-price-m').find('input[name=stripe_pay_amount]').val(Number(fee_dot).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }));
                    $('#add-price-m').find('.modal-title').text('').text('Edit Payment:');
                    $('#add-price-m').modal('show');
                } else {
                    $('.sc-loader').hide();
                    alert('Payment not exists.');
                }
            });
        }
    });
    //Add new payment
    $(document).on('click', '#add-new-payment', function () {
        $('#add-price-m').find('form')[0].reset();
        $('#add-price-m').find('form').find('input[name=edit_id_payment]').remove();
        $('#add-price-m').find('.modal-title').text('').text('Create Payment:');
        $('#add-price-m').modal('show');
    });
    //Change available classes based on select
    $('.funnel-available-class-div').html('').html('No classes available');
    $(document).on('change', '.funnel-checkout-page', function () {
        var $current = $(this).val();
        display_available_classes($current, $(this));
    });
    //On change of pages radio button
    $(document).on('click', '.funnel-step-page', function () {
        if ($(this).is(':checked')) {
            var $current = $(this).val();
            display_available_classes($current, $(this));
        }
    });
    //On Delete stripe page
    $(document).on('click', '.sc-delete-page', function () {
        if (confirm('Are you sure, you want to delete this page?') === false) {
            return false;
        }
        var $this = $(this);
        $('.sc-loader').show();
        var id = $this.siblings('.d_page_id').val();
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_delete_page',
                'edit_id': id,
            };
            $.post(ajax_admin_url, data, function (response) {
                $('.alert.alert-danger').hide();
                $('.alert.alert-success').hide();
                if (response != 0) {
                    $this.closest('tr').remove();
                    $('.main-container').prepend('<div class="alert alert-success" role="alert">Stripe Page deleted successfully</div>');
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                } else {
                    $('.main-container').prepend('<div class="alert alert-danger" role="alert">Stripe Page delete error</div>');
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                }
            });
        }
    });
    //On Delete stripe plan
    $(document).on('click', '.sc-delete-plan', function () {
        if (confirm('Are you sure, you want to delete this plan?') === false) {
            return false;
        }
        var $this = $(this);
        $('.sc-loader').show();
        var id = $this.siblings('.del_plan_id').val();
        var stripe_id = $this.siblings('.del_plan_stripe_id').val();
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_delete_plan',
                'edit_id': id,
                'stripe_id': stripe_id,
            };
            $.post(ajax_admin_url, data, function (response) {
                $('.alert.alert-danger').hide();
                $('.alert.alert-success').hide();
                if (response != 0) {
                    $this.closest('tr').remove();
                    $('.main-container').prepend(response);
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                } else {
                    $('.main-container').prepend('<div class="alert alert-danger" role="alert">Stripe plan delete error</div>');
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                }
            });
        }
    });
    //On Delete stripe payment
    $(document).on('click', '.sc-delete-payment', function () {
        if (confirm('Are you sure, you want to delete this payment?') === false) {
            return false;
        }
        var $this = $(this);
        $('.sc-loader').show();
        var id = $this.siblings('.del_fee_id').val();
        if (id != '' || id == 0) {
            var data = {
                'action': 'sc_delete_payment',
                'edit_id': id
            };
            $.post(ajax_admin_url, data, function (response) {
                $('.alert.alert-danger').hide();
                $('.alert.alert-success').hide();
                if (response != 0) {
                    $this.closest('tr').remove();
                    $('.main-container').prepend(response);
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                } else {
                    $('.main-container').prepend('<div class="alert alert-danger" role="alert">Payment delete error</div>');
                    $('.sc-loader').hide();
                    $("html, body").animate({scrollTop: 0}, "slow");
                }
            });
        }
    });
});
function display_available_classes($current, $this, main_value = false) {
    var data = {
        'action': 'sc_get_current_page_classes',
        'current_page': $current,
    };
    $.post(ajax_admin_url, data, function (response) {
        if (response != 0) {
            var data = JSON.parse(response);
            $this.closest('div.input-group').siblings('.funnel-square-border').html('');
            if ($this.closest('div.funnel-checkout-step-count').hasClass('funnel-checkout')) {
                var label = '';
                $.each(data, function (key, value) {
                    var selected = '';
                    if (main_value == value) {
                        selected = "checked=checked";
                    }
                    label += '<label class="input-label"><input type="radio" class="funnel-checkout-class" name="funnel-checkout-class" value="' + value + '" ' + selected + '><span class="class-style">class: </span><span class="value-style">' + value + '</span></label>';
                });
            } else if ($this.closest('div.funnel-checkout-step-count').hasClass('funnel-step-2')) {
                var label = '';
                $.each(data, function (key, value) {
                    var selected = '';
                    if (main_value == value) {
                        selected = "checked=checked";
                    }
                    label += '<label class="input-label"><input type="radio" class="funnel-step-class" name="funnel-step_2-class" value="' + value + '" ' + selected + '><span class="class-style">class: </span><span class="value-style">' + value + '</span></label>';
                });
            } else {
                var step = $this.closest('div.funnel-checkout-step-count').find('.funnel-step-number').val();
                var label = '';
                $.each(data, function (key, value) {
                    var selected = '';
                    if (main_value == value) {
                        selected = "checked=checked";
                    }
                    label += '<label class="input-label"><input type="radio" class="funnel-step-class" name="funnel_step[' + step + '][funnel-step-class]" value="' + value + '" ' + selected + '><span class="class-style">class: </span><span class="value-style">' + value + '</span></label>';
                });
            }
            $this.closest('div.input-group').siblings('.funnel-square-border').append(label);
        } else {
            $this.closest('div.input-group').siblings('.funnel-square-border').html('');
            $this.closest('div.input-group').siblings('.funnel-square-border').html('No classes available');
        }
    });
}