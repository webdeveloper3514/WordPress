jQuery(document).ready(function () {
    if (jQuery('#wc_birthday').length > 0) {
        jQuery('#wc_birthday').datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            numberOfMonths: 1,
            showButtonPanel: true,
            yearRange: "-100:+0"
        });
    }
});