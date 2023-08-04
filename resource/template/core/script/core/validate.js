(function($) {
    function OSC_Validate() {
        this.validEmail = function(value) {
            // check exist space, '@', '.@', '@.', '@-'
            if (
                value.substr_count(' ') > 0 ||  // not include space
                value.substr_count('@') !== 1 ||    // only has one '@'
                value.substr_count('.@') > 0 || // '.' and '@' are not close
                value.substr_count('@.') > 0 || // '.' and '@' are not close
                value.substr_count('@-') > 0    // '-' is not behind '@'
            ) {
                return false;
            }

            const email_array = value.split('@');

            if (email_array.length !== 2) {
                return false;
            }

            const local_name = email_array[0];
            const domain_name = email_array[1];

            // check local name
            const local_regex = /^([a-zA-Z0-9.!#%&*+=?^_~`{}|/-])+$/i; // regex
            const spec_regex = /^([.!#%&*+=?^_~`{}|/-])+$/i; // spec regex
            if (
                local_name.length < 1 ||    // min length = 1
                local_name.length > 64 ||   // max length = 64
                spec_regex.test(local_name.charAt(0)) ||    // first char is not special char
                !local_regex.test(local_name)   // regex
            ) {
                return false;
            }

            // check "." - not start, not multiple
            const local_parts = local_name.split('.');
            for (let _i = 0; _i < local_parts.length; _i++) {
                const part = local_parts[_i];
                if (part === '') return false;
            }

            // check domain name
            if (domain_name.substr_count('.-') > 0 || domain_name.substr_count('-.') > 0) { // '.' and '-' are not close
                return false;
            }
            const domain_regex = /^([a-zA-Z0-9.-])+$/i;
            const domain_number = /^([0-9])+$/i;
            const domain_spec = /^([.-])+$/i;
            if (
                domain_name.substr_count('.') < 1 || // at least one dot
                domain_name.length < 3 ||   // min length = 3
                domain_name.length > 253 || // max length = 253
                domain_spec.test(domain_name.charAt(0)) ||  // first char is not special char
                domain_spec.test(domain_name.charAt(domain_name.length - 1)) || // last char is not special char
                domain_number.test(domain_name.charAt(domain_name.length - 1)) || // last char is not number
                domain_number.test(domain_name) ||  // regex not only number
                !domain_regex.test(domain_name) // regex
            ) {
                return false;
            }

            // check "." - not start, not multiple
            const domain_parts = domain_name.split('.');
            for (let _i = 0; _i < domain_parts.length; _i++) {
                const part = domain_parts[_i];
                if (part === '') return false;
            }

            return true;
        };

        this.validUrl = function(value) {
            return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
        };

        this.validateEmoji = function(input) {
            const patt = /([\uD800-\uDBFF][\uDC00-\uDFFF])/g
            let input_val = input.val()
            input_val = input_val.replace('‘', "'")
            input_val = input_val.replace('‘', '\'')
            input_val = input_val.replace('’', '\'')
            input_val = input_val.replace('‚', ',')
            input_val = input_val.replace('“', '"')
            input_val = input_val.replace('”', '"')
            input_val = input_val.replace('„', ',,')
            input.val(input_val)
            if (input_val) {
                let rs = input.val().match(patt)
                if (rs !== null && rs.length > 0) {
                    throw 'Please do not include special characters in the ' + input.attr('placeholder') + ' field';
                }
            }
        }

        this.validateXSS = function(input) {
            const patt_xss = /<(.*)>/g
            let input_val = input.val()
            input.val(input_val)
            if (input_val) {
                let xss = input.val().match(patt_xss)
                if (xss !== null && xss.length > 0) {
                    throw 'Please do not include special characters in the ' + input.attr('placeholder') + ' field';
                }
            }
        }

        function fetchJSONTag(container, key) {
            return JSON.parse(container.find('script[data-json="' + key + '"]')[0].innerHTML);
        }

        this.validateZipCode = function (input, input_country) {
            let patt = /[^a-z0-9 ]/gi;
            const countries_code_special = fetchJSONTag(input.closest('body'), 'country_zip_code_special')
            if (input.val() && input_country !== null && countries_code_special) {
                const country_code = input_country.data('code')
                if (countries_code_special.indexOf(country_code) !== -1) {
                    patt = /[^a-z0-9- ]/gi;
                }
                const new_val = input.val().replace(patt, '')
                input.val(new_val)
            }
        }

        this.validateCountryDeactive = function (input) {
            if (input.val() == 'N/A') {
                throw 'Please select a country to complete your order';
            }
        }
    }

    $.validator = new OSC_Validate();

    $.extend($.fn, {
        validate : function(options) {

        }
    });
})(jQuery);