jQuery(document).ready(function() {

    jQuery(document).on('keydown', '#iopay_pix_cpf', function (e) {
        var digit = e.key.replace(/\D/g, '');
        var value = jQuery(this).val().replace(/\D/g, '');
        var size = value.concat(digit).length;
        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    jQuery(document).on('keydown', '#iopay_boleto_cpf', function (e) {
        var digit = e.key.replace(/\D/g, '');
        var value = jQuery(this).val().replace(/\D/g, '');
        var size = value.concat(digit).length;
        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    jQuery(document).on('keydown', '#cc_taxvat', function (e) {
        var digit = e.key.replace(/\D/g, '');
        var value = jQuery(this).val().replace(/\D/g, '');
        var size = value.concat(digit).length;
        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    jQuery(document).on('keydown', '#iopay_gateway_creditcard_cc_cid', function (e) {
        jQuery(this).mask('000');
    });

    jQuery(document).on('keydown', '#iopay_gateway_creditcard_cc_number', function (e) {
        jQuery(this).mask('0000 0000 0000 0000');
    });

    jQuery(document).on('keydown', '#iopay_gateway_creditcard_cc_cpf', function (e) {
        var digit = e.key.replace(/\D/g, '');
        var value = jQuery(this).val().replace(/\D/g, '');
        var size = value.concat(digit).length;
        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    (function($) {
        $.getCreditCardType = function(val) {
            if(!val || !val.length) return undefined;
            var type = new Array;
            type[1] = '^4[0-9]{12}(?:[0-9]{3})?$';                          // visa
            type[2] = '^5[1-5][0-9]{14}$';                                  // mastercard
            type[3] = '^65[4-9][0-9]{13}|64[4-9][0-9]{13}|6011[0-9]{12}|(622(?:12[6-9]|1[3-9][0-9]|[2-8][0-9][0-9]|9[01][0-9]|92[0-5])[0-9]{10})$';// Discover Card
            type[4] = '^3[47][0-9]{13}$';                                   // amex
            type[5] = '^(5018|5020|5038|6304|6759|6761|6763)[0-9]{8,15}$'; // Maestro Card
            type[6] = '^(6334|6767)[0-9]{12}|(6334|6767)[0-9]{14}|(6334|6767)[0-9]{15}$';// Solo Card
            type[7] = '^389[0-9]{11}$';                                     // Carte Blanche Card
            type[8] = '^3(?:0[0-5]|[68][0-9])[0-9]{11}$';                   // Diners Club Card

            var ccnum = val.replace(/[^\d.]/g, '');
            var returntype = 0;
            $.each(type, function(idx, re) {
                var regex = new RegExp(re);
                if(regex.test(ccnum) && idx>0) {
                    returntype = idx;
                }
            });
            returntype = parseInt(returntype);
            console.log('returntype: '+returntype);
            switch(returntype) {
                case 0: return undefined;
                case 1: return 'visa';
                case 2: return 'mastercard';
                case 3: return 'discover';
                case 4: return 'amex';
                case 5: return 'maestro';
                case 6: return 'solo';
            };
            return undefined;
        };
        $.fn.creditCardType = function(options) {
            var settings = {
                target: '#credit-card-type',
            };
            if(options) {
                $.extend(settings, options);
            };
            var keyupHandler = function() {
                $('#credit-card-type .VI').css("display", "none");
                $('#credit-card-type .MC').css("display", "none");
                $('#credit-card-type .AE').css("display", "none");
                $('#credit-card-type .DI').css("display", "none");
                $('#credit-card-type .SM').css("display", "none");
                $('#credit-card-type .SO').css("display", "none");
                switch($.getCreditCardType($(this).val())) {
                    case 'visa':
                        $('#credit-card-type .VI').addClass('active');
                        $('#credit-card-type .VI').css("display", "block");
                        break;
                    case 'mastercard':
                        $('#credit-card-type .MC').addClass('active');
                        $('#credit-card-type .MC').css("display", "block");
                        break;
                    case 'amex':
                        $('#credit-card-type .AE').addClass('active');
                        $('#credit-card-type .AE').css("display", "block");
                        break;
                    case 'discover':
                        $('#credit-card-type .DI').addClass('active');
                        $('#credit-card-type .DI').css("display", "block");
                        break;
                    case 'maestro':
                        $('#credit-card-type .SM').addClass('active');
                        $('#credit-card-type .SM').css("display", "block");
                        break;
                    case 'solo':
                        $('#credit-card-type .SO').addClass('active');
                        $('#credit-card-type .SO').css("display", "block");
                        break;
                };
            };
            return this.each(function() {
                $(this).bind('keyup',keyupHandler).trigger('keyup');
            });
        };
    })(jQuery);
});