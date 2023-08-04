(function ($) {
    'use strict';
    window.catalogFormatPrice = function (amount, format_key) {
        var formats;

        formats = {
            html_with_currency: '<span>${{amount}} USD</span>',
            html_without_currency: '<span>${{amount}}</span>',
            email_with_currency: '${{amount}} USD',
            email_without_currency: '${{amount}}'
        };

        if (typeof formats[format_key] === "undefined") {
            format_key = 'html_without_currency';
        }

        return formats[format_key].replace(/{{amount}}/g, formatNumber( $.round(amount, 2))).replace(/{{amount_no_decimals}}/g, formatNumber($.round(amount, 0)));
    };

    function formatNumber(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }

    window.catalogFormatPriceByInteger = function (amount, format_key) {
        return catalogFormatPrice(catalogIntegerToFloat(amount), format_key);
    };

    window.catalogFloatToInteger = function (price) {
        return $.round($.round(parseFloat(price), 2) * 100);
    };

    window.catalogIntegerToFloat = function (price) {
        return $.round(parseInt(price) / 100, 2);
    };

    window.googleAnalyticsEcommerce = function (event, params) {
        if (typeof event !== 'string' || event.replace(/\s+/gi, '') === '') {
            return;
        }
        if (typeof params !== 'object') {
            return;
        }

        if (event == 'begin_checkout') {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push({
                'event': 'checkout',
                'ecommerce': {
                    'checkout': {
                        'actionField': {'step': 1, 'option': 'Visa'},
                        'products': [{
                            'name': params.name,
                            'id': params.id,
                            'price': params.price,
                            'brand': params.brand,
                            'variant': params.variant,
                            'quantity': params.quantity
                        }]
                    }
                }
            });
        }

        if ($.inArray(event, ['add_to_cart','begin_checkout', 'view_item', 'remove_from_cart']) != -1) {
            $('<script>'+ gtag('event', event, { "items": [ params ]}) +'</script>').appendTo(document.body);
            
        } else if ($.inArray(event,['purchase', 'refund']) != -1) {
            $('<script>'+ gtag('event', event, params) +'</script>').appendTo(document.body);

        } else {
            return;
        }
    };

    window.googleAdwordsEcommerce = function (event, params) {
        if (typeof event !== 'string' || event.replace(/\s+/gi, '') === '') {
            return;
        }
        if (typeof params !== 'object') {
            return;
        }

        if ($.inArray(event, ['add_to_cart', 'view_item','purchase']) != -1) {
            $('<script>'+ gtag('event', event, params) +'</script>').appendTo(document.body);
        } else {
            return;
        }
    };

    window.klaviyoTracking = function (event, params) {
        if (typeof event !== 'string' || event.replace(/\s+/gi, '') === '') {
            return;
        }
        if (typeof params !== 'object') {
            return;
        }
        var _learnq = _learnq || [];

        if ($.inArray(event, ['Viewed Product', 'Added to Cart','Started Checkout']) != -1) {
            $('<script>'+ _learnq.push(["track", event, params]) +'</script>').appendTo(document.body);
            if(event == 'Viewed Product') {
                _learnq.push(["trackViewedItem", {
                    "Title": params.ProductName,
                    "ItemId": params.ProductID,
                    "Categories": params.Categories,
                    "ImageUrl": params.ImageURL,
                    "Url": params.URL,
                    "Metadata": {
                        "Brand": params.Brand,
                        "Price": params.Price,
                        "CompareAtPrice": params.CompareAtPrice
                    }
                }]);
            }

        } else {
            return;
        }
    };

    window.debounce = function (func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
})(jQuery);