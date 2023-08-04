/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

(function ($) {
    Object.defineProperty(HTMLMediaElement.prototype, 'isPlaying', {
        get: function () {
            return !!(this.currentTime > 0 && !this.paused && !this.ended && this.readyState > 2);
        }
    });

    Node.prototype.prepend = function (node) {
        if (this.firstChild) {
            this.insertBefore(node, this.firstChild);
        } else {
            this.appendChild(node);
        }

        return this;
    };

    Element.prototype.getParsedCssText = function (attr_names) {
        var parsed_css_text = {};

        if (typeof attr_names !== 'string') {
            attr_names = null;
        } else {
            attr_names = attr_names.replace(/[^\w-,]/g, '').split(',');
        }

        var found_counter = 0;

        var matches = this.style.cssText.match(/([\w-]+)\s*:\s*((?:[^;'"]+|"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')*)\s*;?/g);

        if (matches) {
            for (var i = 0; i < matches.length; i++) {
                var _matches = matches[i].match(/^([\w-]+)\s*:\s*(.+?)\;?$/);

                if (_matches && (!attr_names || attr_names.indexOf(_matches[1].toLowerCase()) >= 0)) {
                    parsed_css_text[_matches[1].toLowerCase()] = _matches[2];

                    found_counter++;
                }
            }
        }

        if (Array.isArray(attr_names) && attr_names.length === 1) {
            return found_counter < 1 ? null : parsed_css_text[attr_names[0]];
        }

        return parsed_css_text;
    };

    Array.prototype.unique = function () {
        var a = this.concat();

        for (var i = 0; i < a.length; ++i) {
            for (var j = i + 1; j < a.length; ++j) {
                if (a[i] === a[j]) {
                    a.splice(j--, 1);
                }
            }
        }

        return a;
    };

    String.prototype.ucfirst = function () {
        return this.substr(0, 1).toUpperCase() + this.substr(1);
    };

    String.prototype.lcfirst = function () {
        return this.substr(0, 1).toLowerCase() + this.substr(1);
    };

    String.prototype.substr_count = function (needle, offset, length) {
        var string = offset ? this.substr(offset) : this;

        if (length) {
            string = string.substr(0, length);
        }

        var counter = 0;

        for (var i = 0; i < string.length; i++) {
            if (needle === string.substr(i, needle.length)) {
                counter++;
            }
        }

        return counter;
    };

    String.prototype.nl2br = function () {
        return this.replace(/\n/g, '<br />');
    };

    String.prototype.br2nl = function () {
        return this.replace(/<br\s*\/?>/gi, "\n");
    };

    String.prototype.str_pad = function (length, pad_string) {
        if (!pad_string) {
            pad_string = '0';
        } else {
            pad_string = new String(pad_string);
        }

        length = parseInt(length);

        if (isNaN(length) || length < 1) {
            return this;
        }

        if (this.length >= length) {
            return this;
        }

        var pad_text = '';

        for (var i = this.length; i < length; i++) {
            pad_text += pad_string;
        }

        return pad_text + this;
    };

    jQuery.event.props.push('clipboardData');

    Date.prototype.days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    Date.prototype.short_days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    Date.prototype.months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    Date.prototype.short_months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    Date.prototype.short_format = '';
    Date.prototype.long_format = '';

    Date.prototype.getMonthText = function (short) {
        return short ? this.short_months[this.getMonth()] : this.months[this.getMonth()];
    };

    Date.prototype.getDayText = function (short) {
        return short ? this.short_days[this.getDay()] : this.days[this.getDay()];
    };

    Date.prototype.getFullUnit = function (func, increase) {
        var buff = parseInt(this[func]());

        increase = parseInt(increase);

        if (!isNaN(increase)) {
            buff += increase;
        }

        return buff < 10 ? '0' + buff : buff;
    };

    Date.prototype.getMeridiem = function (lower) {
        return (this.getHours() > 11 ? 'pm' : 'am')[lower ? 'toLowerCase' : 'toUpperCase' ]();
    };

    Date.prototype.get12Hours = function () {
        return this.getHours() > 12 ? this.getHours() - 12 : this.getHours();
    };

    Date.prototype.format = Date.prototype.parse = function (format) {
        if (typeof format != 'string' || format == '') {
            format = format ? this.long_format : this.short_format;
        }

        format = format.split('');

        for (var x = 0; x < format.length; x++) {
            switch (format[x]) {
                case 'l':
                    format[x] = this.getDayText();
                    break;
                case 'D':
                    format[x] = this.getDayText(true);
                    break;
                case 'd':
                    format[x] = this.getFullUnit('getDate');
                    break;
                case 'j':
                    format[x] = this.getDate();
                    break;
                case 'm':
                    format[x] = this.getFullUnit('getMonth', 1);
                    break;
                case 'F':
                    format[x] = this.getMonthText();
                    break;
                case 'M':
                    format[x] = this.getMonthText(true);
                    break;
                case 'Y':
                    format[x] = this.getFullYear();
                    break;
                case 'y':
                    format[x] = (this.getFullYear() + '').substring(2);
                    break;
                case 'h':
                    format[x] = this.getFullUnit('get12Hours');
                    break;
                case 'H':
                    format[x] = this.getFullUnit('getHours');
                    break;
                case 'g':
                    format[x] = this.get12Hours();
                    break;
                case 'G':
                    format[x] = this.getHours();
                    break;
                case 'i':
                    format[x] = this.getFullUnit('getMinutes');
                    break;
                case 's':
                    format[x] = this.getFullUnit('getSeconds');
                    break;
                case 'A':
                    format[x] = this.getMeridiem();
                    break;
            }
        }

        return format.join('');
    };

    Date.prototype.setTimestamp = function (timestamp) {
        this.setTime(timestamp * 1000);
        return this;
    };

    var ICON_MAP = {};

    window.configOSCIcon = function () {
        var name = this.getAttribute('data-icon').replace(/^osc-/, '');

        if (typeof ICON_MAP[name] === 'undefined') {
            return;
        }

        this.setAttribute('viewBox', ICON_MAP[name]);

        var marker = $('<span />').insertBefore(this);
        $(this).detach().insertBefore(marker);
        marker.remove();
    };

    $.extend($, {
        lang: {},
        WIN_READY: false,
        OSCSID: null,
        OSC: true,
        domain: OSC_DOMAIN,
        base: OSC_BASE,
        base_url: OSC_BASE_URL,
        frontend_base_url: OSC_FRONTEND_BASE_URL,
        storage_base_url: OSC_STORAGE_BASE_URL,
        d2_flow_base_url: D2_FLOW_BASE_URL,
        
        // make full url for image in storage
        getImgStorageUrl: function(file_path) {
            if(/http(|s):\/\//.test(file_path)) return file_path;

            return this.storage_base_url + '/storage/' + file_path;
        },
        renderIcon: function (name, className = '') {
            var viewBox = '0 0 512 512';

            if (typeof ICON_MAP[name] !== 'undefined') {
                viewBox = ICON_MAP[name];
            }

            var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('data-icon', 'osc-' + name);
            icon.setAttribute('viewBox', viewBox);
            if (className !== '') {
                icon.classList.add(className)
            }
            var use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
            use.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", '#osc-' + name);
            icon.appendChild(use);

            return icon;
        },
        loadSVGIconSprites: function (sprite_url) {
            if (!document.body) {
                setTimeout(function () {
                    $.loadSVGIconSprites(sprite_url);
                }, 50);
                return;
            }

            $.ajax({
                type: 'get',
                url: sprite_url,
                async: true,
                cache: true,
                success: function (response) {
                    var symbols = response.documentElement.getElementsByTagName('symbol');

                    var new_keys = [];

                    for (var i = symbols.length - 1; i >= 0; i--) {
                        var symbol = symbols[i];

                        var key = symbol.getAttribute('id');

                        if (typeof ICON_MAP[key] !== 'undefined') {
                            console.error('ICON_MAP: the icon ' + key + ' is already exists');
                            symbol.parentNode.removeChild(symbol);
                            continue;
                        }

                        ICON_MAP[key] = symbol.getAttribute('viewBox');
                        symbol.setAttribute('id', 'osc-' + key);

                        new_keys.push(key);
                    }

                    if (new_keys.length > 0) {
                        var res_container = document.createElement("div");
                        document.body.insertBefore(res_container, document.body.firstChild);
                        res_container.innerHTML = new XMLSerializer().serializeToString(response.documentElement);

                        new_keys.forEach(function (key) {
                            var icons = document.body.querySelectorAll('svg[data-icon="osc-' + key + '"]');

                            if (icons.length > 0) {
                                for (var i = 0; i < icons.length; i++) {
                                    configOSCIcon.apply(icons[i]);
                                }
                            }
                        });
                    }
                }
            });
        },
        getObjLength: function (obj) {
            var size = 0;

            for (var key in obj) {
                if (obj.hasOwnProperty(key))
                    size++;
            }

            return size;
        },
        round: function (value, precision, mode) {
            var m, f, isHalf, sgn;
            precision |= 0;
            m = Math.pow(10, precision);
            value *= m;
            sgn = (value > 0) | -(value < 0);
            isHalf = value % 1 === 0.5 * sgn;
            f = Math.floor(value);

            if (isHalf) {
                switch (mode) {
                    case 'PHP_ROUND_HALF_DOWN':
                        value = f + (sgn < 0); // rounds .5 toward zero
                        break;
                    case 'PHP_ROUND_HALF_EVEN':
                        value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
                        break;
                    case 'PHP_ROUND_HALF_ODD':
                        value = f + !(f % 2); // rounds .5 towards the next odd integer
                        break;
                    default:
                        value = f + (sgn > 0); // rounds .5 away from zero
                }
            }

            return (isHalf ? value : Math.round(value)) / m;
        },
        safeCall: function (func) {
            var _func = undefined;

            try {
                eval('var _func = ' + func);
            } catch (e) {
                _func = undefined;
            }

            var params = [];

            if (arguments.length > 1) {
                for (var x = 1; x < arguments.length; x++) {
                    params.push(arguments[x]);
                }
            }

            if (typeof _func == 'undefined') {
                params.unshift(func);
                setTimeout(function () {
                    $.safeCall.apply($, params);
                }, 10);
                return;
            }

            var push_params = [];

            for (var x = 0; x < params.length; x++) {
                push_params.push('params[' + x + ']');
            }

            eval(func + '(' + push_params.join(',') + ')');
        },
        digitGroupping: function (num) {
            var str = num.toString().split('.');
            if (str[0].length >= 5) {
                str[0] = str[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,');
            }
            if (str[1] && str[1].length >= 5) {
                str[1] = str[1].replace(/(\d{3})/g, '$1,');
            }
            return str.join('.');
        },
        makeUniqid: function (strongest) {
            var length = 10;

            if (strongest) {
                length = 15;
            }

            var timestamp = +new Date;

            var ts = timestamp.toString();
            var parts = ts.split("").reverse();
            var id = "";

            for (var i = 0; i < length; ++i) {
                var index = $.rand(0, parts.length - 1);
                id += parts[index];
            }

            return $.randKey(1, 3) + $.randKey(5) + id;
        },
        rand: function (min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        },
        randKey: function (len, mode, special_chars) {

            var key = '';

            switch (parseInt(mode)) {
                case 2:
                    var chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    break;
                case 3:
                    var chars = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F',
                        'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
                        'W', 'X', 'Y', 'Z'];
                    break;
                case 4:
                    var chars = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
                    break;
                case 5:
                    var chars = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
                        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
                    break;
                case 6:
                    var chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f',
                        'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                        'w', 'x', 'y', 'z'];
                    break;
                case 7:
                    var chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F',
                        'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
                        'W', 'X', 'Y', 'Z'];
                    break;
                default:
                    var chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f',
                        'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                        'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
                        'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            }

            if (Array.isArray(special_chars) && special_chars.length > 0) {
                special_chars.forEach(function (char) {
                    chars.push(char);
                });
            }

            var count = chars.length - 1;

            for (var i = 0; i < len; i++) {
                key += chars[$.rand(0, count)];
            }

            return key;
        },
        calculateNewDim: function (w, h, mw, mh, fit_flag) {
            mw = parseInt(mw);
            mh = parseInt(mh);

            if (isNaN(mw) || mw < 1) {
                mw = 0;
            }

            if (isNaN(mh) || mh < 1) {
                mh = 0;
            }

            if (mw < 1 && mh < 1) {
                return {w: w, h: h};
            }

            if (fit_flag && mw > 0 && mh > 0) {
                var scale = Math.min(mw / w, mh / h);

                w *= scale;
                h *= scale;
            } else {
                if (mw > 0 && (mw < w || fit_flag)) {
                    h = h / (w / mw);
                    w = mw;
                }

                if (mh > 0 && (mh < h || fit_flag)) {
                    w = w / (h / mh);
                    h = mh;
                }
            }

            return {w: w, h: h};
        },
        cleanVNMask: function (text) {
            var search_arr = [['á', 'à', 'ả', 'ạ', 'ã', 'â', 'ấ', 'ầ', 'ẩ', 'ậ', 'ẫ', 'ă', 'ắ', 'ằ', 'ẳ', 'ặ', 'ẵ'],
                ['á', 'À', 'Ả', 'Ạ', 'Ã', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ậ', 'Ẫ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ặ', 'Ẵ'],
                ['ó', 'ò', 'ỏ', 'ọ', 'õ', 'ô', 'ố', 'ồ', 'ổ', 'ộ', 'ỗ', 'ơ', 'ớ', 'ờ', 'ở', 'ợ', 'ỡ'],
                ['Ó', 'Ò', 'Ỏ', 'Ọ', 'Õ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ộ', 'Ỗ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ợ', 'Ỡ'],
                ['é', 'è', 'ẻ', 'ẹ', 'ẽ', 'ê', 'ế', 'ề', 'ể', 'ệ', 'ễ'],
                ['É', 'È', 'Ẻ', 'Ẹ', 'Ẽ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ệ', 'Ễ'],
                ['ú', 'ù', 'ủ', 'ụ', 'ũ', 'ư', 'ứ', 'ừ', 'ử', 'ự', 'ữ'],
                ['Ú', 'Ù', 'Ủ', 'Ụ', 'Ũ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ự', 'Ữ'],
                ['í', 'ì', 'ỉ', 'ị', 'ĩ'],
                ['í', 'Ì', 'Ỉ', 'Ị', 'Ĩ'],
                ['ý', 'ỳ', 'ỷ', 'ỵ', 'ỹ'],
                ['Ý', 'Ỳ', 'Ỷ', 'Ỵ', 'Ỹ'],
                ['đ'],
                ['Đ']];

            var replace_arr = ['a', 'A', 'o', 'O', 'e', 'E', 'u', 'U', 'i', 'I', 'y', 'Y', 'd', 'D'];

            for (var k = 0; k < search_arr.length; k++) {
                eval("text = text.replace(/(" + search_arr[k].join('|') + ")/g, replace_arr[k]);");
            }

            return text;
        },
        winReady: function (p) {
            return $.WIN_READY;
        },
        parseConfigString: function (string) {
            string = string.split(';');

            var opts = {};

            for (var x = 0; x < string.length; x++) {
                var opt = string[x].split(':');

                if (opt.length != 2) {
                    continue;
                }

                opt[0] = opt[0].toString().trim();
                opt[1] = opt[1].toString().trim();

                if (opt[0] && opt[1]) {
                    opts[opt[0]] = opt[1];
                }
            }

            return opts;
        },
        str_pad: function (text, length, padstring) {
            text = new String(text);
            padstring = new String(padstring);

            if (text.length < length) {
                var padtext = new String(padstring);

                while (padtext.length < (length - text.length)) {
                    padtext += padstring;
                }

                text = padtext.substr(0, length - text.length) + text;
            }

            return text;
        },
        mb_strlen: function (str) {
            return ($.browser.msie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
        },
        strip_tags: function (txt) {
            return txt.toString().replace(/<\S[^><]*>/g, "");
        },
        htmlspecialchars: function (str) {
            var f = [
                $.browser.mac && $.browser.msie ? new RegExp('&', 'g') : new RegExp('&(?!#[0-9]+;)', 'g'),
                new RegExp('<', 'g'),
                new RegExp('>', 'g'),
                new RegExp('"', 'g')
            ];

            var r = ['&amp;', '&lt;', '&gt;', '&quot;'];

            for (var i = 0; i < f.length; i++) {
                str = str.replace(f[i], r[i]);
            }

            return str;
        },
        validateEmbedCode: function (code) {
            if (code.indexOf("<script") >= 0) {
                return false;
            }

            var patts = [
                /<object[^>]*>(.*?)<\/object>/i,
                /<object[^>]*>/i,
                /<embed[^>]*>(.*?)<\/embed>/i,
                /<embed[^>]*>/i
            ];

            for (var x = 0; x < patts.length; x++) {
                if (patts[x].test(code)) {
                    return true;
                }
            }

            return false;
        },
        parseEmbedTag: function (node) {
            var node_obj = $(node);

            var media_attrs = {
                width: node_obj.attr('width'),
                height: node_obj.attr('height'),
                src: node_obj.attr('src'),
                flashvars: node_obj.attr('flashvars'),
                name: node_obj.attr('name'),
                id: node_obj.attr('id')
            };

            var errors = 0;

            for (var x in media_attrs) {
                if (!media_attrs[x] && x != 'flashvars' && x != 'name' && x != 'id') {
                    return false;
                }
            }

            return media_attrs;
        },
        parseObjectTag: function (node) {
            var node_obj = $(node);

            var media_attrs = {
                width: node_obj.attr('width'),
                height: node_obj.attr('height'),
                src: node_obj.attr('data'),
                flashvars: node_obj.attr('flashvars'),
                name: node_obj.attr('name'),
                id: node_obj.attr('id')
            };

            for (var x in media_attrs) {
                if (!media_attrs[x] && x != 'name' && x != 'id') {
                    var attr_keys = {};
                    attr_keys[x] = 1;

                    if (x == 'src') {
                        attr_keys['movie'] = 1;
                    }

                    media_attrs[x] = $._getObjectParamTagValue(node, attr_keys);
                }
            }

            var embed = node.getElementsByTagName('embed')[0];
            var embed_obj = false;

            if (embed) {
                embed_obj = $(embed);
            }

            var errors = 0;

            for (var x in media_attrs) {
                if (!media_attrs[x] && x != 'name' && x != 'id') {
                    errors++;

                    if (embed_obj) {
                        media_attrs[x] = embed_obj.attr(x);

                        if (media_attrs[x] || x == 'flashvars') {
                            errors--;
                        }
                    } else if (x == 'flashvars') {
                        errors--;
                    }
                }
            }

            if (errors > 0) {
                return false;
            }

            return media_attrs;
        },
        _getObjectParamTagValue: function (node, attrs) {
            var params = node.getElementsByTagName('param');

            for (var x = 0; x < params.length; x++) {
                var paramObj = $(params[x]);

                if (attrs[paramObj.attr('name').toString().toLowerCase()]) {
                    return paramObj.attr('value');
                }
            }

            return null;
        },
        serializeStyle: function (o) {
            var s = '';

            $.each(o, function (k, v) {
                if (k && v) {
                    if ($.browser.mozilla && k.indexOf('-moz-') === 0)
                        return;

                    switch (k) {
                        case 'color':
                        case 'background-color':
                            v = v.toLowerCase();
                            break;
                    }

                    s += (s ? ' ' : '') + k + ': ' + v + ';';
                }
            });

            return s;
        },
        parseStyle: function (st) {
            var o = {};

            if (!st) {
                return o;
            }

            function compress(p, s, ot) {
                var t, r, b, l;

                t = o[p + '-top' + s];

                if (!t)
                    return;

                r = o[p + '-right' + s];

                if (t != r)
                    return;

                b = o[p + '-bottom' + s];

                if (r != b)
                    return;

                l = o[p + '-left' + s];

                if (b != l)
                    return;

                o[ot] = l;

                delete o[p + '-top' + s];
                delete o[p + '-right' + s];
                delete o[p + '-bottom' + s];
                delete o[p + '-left' + s];
            }

            function compress2(ta, a, b, c) {
                var t;

                t = o[a];

                if (!t)
                    return;

                t = o[b];

                if (!t)
                    return;

                t = o[c];

                if (!t)
                    return;

                o[ta] = o[a] + ' ' + o[b] + ' ' + o[c];

                delete o[a];
                delete o[b];
                delete o[c];
            }
            ;

            st = st.replace(/&(#?[a-z0-9]+);/g, '&$1_MCE_SEMI_'); // Protect entities

            $.each(st.split(';'), function (k, v) {
                var sv, ur = [];

                if (v) {
                    v = v.replace(/_MCE_SEMI_/g, ';'); // Restore entities
                    v = v.replace(/url\([^\)]+\)/g, function (v) {
                        ur.push(v);
                        return 'url(' + ur.length + ')'
                    });
                    v = v.split(':');
                    sv = $.trim(v[1]);
                    sv = sv.replace(/url\(([^\)]+)\)/g, function (a, b) {
                        return ur[parseInt(b) - 1]
                    });
                    sv = sv.replace(/(rgb\([^\)]+\))/g, function (a, b) {
                        return $.rgbToColor(b);
                    });

                    o[$.trim(v[0]).toLowerCase()] = sv;
                }
            });

            compress("border", "", "border");
            compress("border", "-width", "border-width");
            compress("border", "-color", "border-color");
            compress("border", "-style", "border-style");
            compress("padding", "", "padding");
            compress("margin", "", "margin");
            compress2('border', 'border-width', 'border-style', 'border-color');

            if ($.browser.msie) {
                if (o.border == 'medium none') {
                    o.border = '';
                }
            }

            return o;
        },
        rgbToColor: function (forecolor) {
            if (forecolor == '' || forecolor == null) {
                return '';
            }

            var matches = forecolor.match(/^rgb\s*\(([0-9]+),\s*([0-9]+),\s*([0-9]+)\)$/i);

            if (matches) {
                return $.rgbhexToColor((matches[1] & 0xFF).toString(16), (matches[2] & 0xFF).toString(16), (matches[3] & 0xFF).toString(16));
            } else {
                return $.rgbhexToColor((forecolor & 0xFF).toString(16), ((forecolor >> 8) & 0xFF).toString(16), ((forecolor >> 16) & 0xFF).toString(16));
            }
        },
        rgbhexToColor: function (r, g, b) {
            return '#' + ($.str_pad(r, 2, 0) + $.str_pad(g, 2, 0) + $.str_pad(b, 2, 0));
        },
//        _each : function(o, cb, s) {
//            var n, l;
//
//            if (!o) return 0;
//
//            s = s || o;
//
//            if(typeof o.length != 'undefined') {
//                for(n = 0, l = o.length; n < l; n ++) {
//                    if(cb.call(s, o[n], n, o) === false) {
//                        return 0;
//                    }
//                }
//            } else {
//                for(n in o) {
//                    if(o.hasOwnProperty(n)) {
//                        if(cb.call(s, o[n], n, o) === false) {
//                            return 0;
//                        }
//                    }
//                }
//            }
//
//            return 1;
//        },

        TXT2XML: function (TXT) {
            try {
                XML = new ActiveXObject("Microsoft.XMLDOM");
                XML.async = "false";
                XML.loadXML(TXT);
            } catch (e) {
                try {
                    parser = new DOMParser();
                    XML = parser.parseFromString(TXT, "text/xml");
                } catch (e) {
                }
            }

            return XML;
        },
        XML2TXT: function (XML) {
            try {
                TXT = XML.xml;
            } catch (e) {
                try {
                    TXT = (new XMLSerializer()).serializeToString(XML);
                } catch (e) {
                }
            }

            return TXT;
        },
        createElement: function (tag, attribs, styles, parent) {
            var el = document.createElement(tag);

            var obj = $(el);

            if (attribs) {
                obj.prop(attribs);
            }

            if (styles) {
                obj.css(styles);
            }

            if (parent) {
                parent.appendChild(el);
            }

            return el;
        },
        validate: function (data) {
            var result, field, type, args, errors = 0;

            for (var x in data) {
                field = $('#' + x);

                if (field.length < 1) {
                    continue;
                }

                if (typeof data[x] == 'function') {
                    result = data[x](field);
                } else {
                    args = {};

                    if (typeof data[x] == 'string') {
                        type = '_' + data[x].replace(/^([a-zA-Z0-9_]+)(:.*)?$/, '$1');

                        if ((/:/).test(data[x])) {
                            args = data[x].replace(/^([a-zA-Z0-9_]+):(.*)?$/, '$2').split(':');
                        }
                    }

                    result = this._validator[type](field, args);
                }

                if (result != 'OK') {
                    errors++;

                    $('#' + x + '__err').show().each(function () {
                        this.innerHTML = result;
                    });

                    field.addClass('error');
                } else {
                    $('#' + x + '__err').hide();
                    field.removeClass('error');
                }
            }

            return errors < 1;
        },
        _validator: {
            _selector: function (field, arguments) {
                var totalOptSelected = 0;

                for (var x = 0; x < field.options.length; x++) {
                    if (!field.options[x].selected) {
                        continue;
                    }

                    if (field.options[x].value == '') {
                        continue;
                    }

                    totalOptSelected++;
                }

                return totalOptSelected > 0 ? true : 'You must select least one option';
            },
            _string: function (field, arguments) {
                var error = '';

                var counter = 0;

                field.each(function () {
                    if (this.tagName == 'INPUT') {
                        this.value = $.trim(this.value);

                        if (this.value == '') {
                            error = 'The field is must enter value';
                        }
                    } else if (this.tagName == 'SELECT') {
                        var result = $._validator._selector(this, arguments);

                        if (result !== true) {
                            error = result;
                        }
                    }

                    counter++;
                });

                return error == '' ? 'OK' : error;
            },
            _number: function (field, arguments) {
                var error = '';

                var counter = 0;

                field.each(function () {
                    if (this.tagName == 'INPUT') {
                        this.value = $.trim(this.value);

                        if (this.value == '') {
                            error = 'The field is must enter value';
                        } else {
                            this.value = arguments.isFloat ? parseFloat(this.value) : parseInt(this.value);

                            if (isNaN(this.value)) {
                                error = 'ERR: The value is not number';
                            } else if (arguments.min && this.value < arguments.min) {
                                error = 'ERR: The value is less than ' + arguments.min;
                            } else if (arguments.max && this.value > arguments.max) {
                                error = 'ERR: The value is greater than ' + arguments.max;
                            }
                        }
                    } else if (this.tagName == 'SELECT') {
                        var result = $._validator._selector(this, arguments);

                        if (result !== true) {
                            error = result;
                        }
                    }

                    counter++;
                });

                return error == '' ? 'OK' : error;
            }
        },
        getScrollBarWidth: function () {
            var t = $('<p />').css({
                width: '100%',
                height: '200px'
            });

            var e = $('<div />').css({
                position: 'absolute',
                top: 0,
                left: 0,
                visibility: 'hidden',
                width: '200px',
                height: '150px',
                overflow: 'scroll'
            }).append(t).appendTo(document.body);

            var width = t[0].getBoundingClientRect().width;

            e.css('overflow', 'hidden');

            var scroll_width = t[0].getBoundingClientRect().width - width;

            e.remove();

            return scroll_width;
        },
        _content_wrapped: {},
        _content_wrapped_state_processor_added: false,
        wrapContent: function (content, opts) {
            var config = {
                key: null,
                close_callback: null,
                push_state: false,
                fixed_mode: false
            };

            $.extend(config, opts);

            if (typeof config.key !== 'string') {
                config.key = '';
            } else {
                config.key = config.key.replace(/[^a-zA-Z0-9_]/g, '');
            }

            if (!config.key) {
                config.key = $.makeUniqid();
            }

            if ($._content_wrapped_state_processor_added && $._content_wrapped_state_processor_added !== config.key) {
                console.error('Please close a wrapcontent before process new content');
                throw 'Please close a wrapcontent before process new content';
            }

            if (config.push_state) {
                $._content_wrapped_state_processor_added = config.key;

                $(window).bindUp('popstate.wrapContent', function (e) {
                    if ($._content_wrapped_state_processor_added) {
                        var key = $._content_wrapped_state_processor_added;

                        $._content_wrapped_state_processor_added = null;

                        $.unwrapContent(key);
                    }

                    e.stopImmediatePropagation();

                    $(window).unbind('popstate.wrapContent');
                });

                $.dynamicUrl.pushState({url: window.location.href, title: window.document.title});
            }

            if (!$(document.body).hasClass('osc-wrap-helper-page-unscrollable')) {
                $(document.body)
                    .attr('data-scroll-y', $(document).scrollTop())
                    .attr('data-scroll-x', $(document).scrollLeft())
                    .addClass('osc-wrap-helper-page-unscrollable')
                    .css('padding-right', $.getScrollBarWidth() + 'px');
            }

            var unclickable_helper = $('.osc-wrap-helper-page-unclickable');

            if (!unclickable_helper[0]) {
                unclickable_helper = $('<div />').addClass('osc-wrap-helper-page-unclickable').appendTo(document.body);
            }

            unclickable_helper.swapZIndex();

            if (typeof $._content_wrapped[config.key] !== 'undefined') {
                var wrap = $._content_wrapped[config.key].wrap;
                var container = $._content_wrapped[config.key].container;
            } else {
                var wrap = $('<div />').addClass('osc-wrap').appendTo(document.body).fixScrollable();
                var container = $('<div />').attr('wrap-key', config.key).addClass('osc-wrap-container').appendTo(wrap);

                container.mousedown(function (e) {
                    if (e.target === this  && config.backdrop !== 'static') {
                        $.unwrapContent(config.key);
                    }
                }).bind('close', function () {
                    $.unwrapContent(config.key);
                });
            }

            wrap[config.fixed_mode ? 'addClass' : 'removeClass']('fixed-mode');

            container.empty().append(content);

            $._content_wrapped[config.key] = {
                close_callback: config.close_callback,
                wrap: wrap,
                container: container
            };

            wrap.swapZIndex();

            return config.key;
        },
        unwrapContent: function (key, force_flag) {
            if (typeof $._content_wrapped[key] === 'undefined') {
                return;
            }

            var data = $._content_wrapped[key];

            if (typeof data.close_callback === 'function') {
                if (data.close_callback(data.container[0], key, force_flag) === false && !force_flag) {
                    return;
                }
            }

            if ($._content_wrapped_state_processor_added) {
                history.go(-1);
                return;
            }

            data.wrap.remove();

            delete $._content_wrapped[key];

            var last_wrap = null;

            for (var k in $._content_wrapped) {
                last_wrap = $._content_wrapped[k].wrap;
            }

            var unclickable_helper = $('.osc-wrap-helper-page-unclickable');

            if (last_wrap === null) {
                $(document.body).removeClass('osc-wrap-helper-page-unscrollable');

                $(window).scrollTop($(document.body).attr('data-scroll-y')).scrollLeft($(document.body).attr('data-scroll-x'));

                $(document.body).removeAttr('data-scroll-x').removeAttr('data-scroll-y').css('padding-right', 0);

                unclickable_helper.remove();
            } else {
                unclickable_helper.swapZIndex();
                last_wrap.swapZIndex();
            }
        },
        confirmAction: function (message, url) {
            var confirmVar = confirm(message);

            if (typeof url == 'undefined') {
                return confirmVar;
            }

            if (confirmVar) {
                window.location = url;
            }
        },
        disablePage: function () {
            var obj = $('#disable');

            if (obj.css('display') != '') {
                var docDim = $.getDocumentDim();
                obj.show().css({
                    width: docDim.w + 'px',
                    height: docDim.h + 'px'
                });
            }
        },
        enablePage: function () {
            $('#disable').hide();
        },
        showLoadingForm: function (message, skipDisable) {
            if (!skipDisable) {
                $.disablePage();
            }

            message = !message ? 'Please wait for loading' : message;

            $('#loading-message').html(message + '...');
            $('#loading').show().moveToCenter();
        },
        hideLoadingForm: function (skipAvailable) {
            $('#loading').hide();

            if (!skipAvailable) {
                $.enablePage();
            }
        },
        getDocumentDim: function () {
            var mode = document.compatMode;

            var browserDim = $.getBrowserDim();

            return {
                w: Math.max((mode != 'CSS1Compat') ? document.body.scrollWidth : document.documentElement.scrollWidth, browserDim.w),
                h: Math.max((mode != 'CSS1Compat') ? document.body.scrollHeight : document.documentElement.scrollHeight, browserDim.h)
            };
        },
        getViewPort: function (win) {
            if (!win) {
                win = window;
            }

            var CSS1Mode = window.document.compatMode == 'CSS1Compat';

            return {
                x: win.pageXOffset || (CSS1Mode ? win.document.documentElement.scrollLeft : win.document.body.scrollLeft),
                y: win.pageYOffset || (CSS1Mode ? win.document.documentElement.scrollTop : win.document.body.scrollTop),
                w: win.innerWidth || (CSS1Mode ? win.document.documentElement.clientWidth : win.document.body.clientWidth),
                h: win.innerHeight || (CSS1Mode ? win.document.documentElement.clientHeight : win.document.body.clientHeight)
            };
        },
        getBrowserDim: function () {
            var dim = {
                w: self.innerWidth,
                h: self.innerHeight
            };

            var mode = document.compatMode;

            if ((mode || $.browser.msie) && !$.browser.opera) {
                dim.w = (mode == 'CSS1Compat') ? document.documentElement.clientWidth : document.body.clientWidth;
                dim.h = (mode == 'CSS1Compat') ? document.documentElement.clientHeight : document.body.clientHeight;
            }

            return dim;
        },
        indexOf: function (arr, elt, from) {
            if (arr.indexOf)
                return arr.indexOf(elt, from);

            from = from || 0;
            var len = arr.length;

            if (from < 0)
                from += len;

            for (; from < len; from++) {
                if (from in arr && arr[from] === elt) {
                    return from;
                }
            }
            return -1;
        },
        formatSize: function (bytes) {
            bytes = parseInt(bytes);

            if (isNaN(bytes)) {
                return 0;
            }

            var i = -1;

            do {
                bytes = bytes / 1024;
                i++;
            } while (bytes > 99);

            return Math.max(bytes, 0.1).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
        },
        insertToTextarea: function (frm, open_tag, close_tag) {
            if (typeof open_tag == 'undefined') {
                open_tag = '';
            }

            if (typeof close_tag == 'undefined') {
                close_tag = '';
            }

            if ($.browser.msie) {
                if (frm.isTextEdit) {
                    frm.focus();

                    var sel = document.selection;
                    var rng = sel.createRange();

                    rng.colapse;

                    if ((sel.type == 'Text' || sel.type == 'None') && rng != null) {
                        if (close_tag != '' && rng.text.length > 0) {
                            open_tag += rng.text + close_tag;
                        } else {
                            open_tag += close_tag;
                        }

                        rng.text = open_tag;
                    }
                } else {
                    frm.value += open_tag + close_tag;
                }
            } else if (frm.selectionEnd) {
                var ss = frm.selectionStart;
                var st = frm.scrollTop;
                var es = frm.selectionEnd;

                if (es <= 2) {
                    es = frm.textLength;
                }

                var start = frm.value.substring(0, ss);
                var middle = frm.value.substring(ss, es);
                var end = frm.value.substring(es, frm.textLength);

                middle = open_tag + middle + close_tag;

                frm.value = start + middle + end;

                var cpos = ss + (middle.length);

                frm.selectionStart = cpos;
                frm.selectionEnd = cpos;
                frm.scrollTop = st;
            } else {
                frm.value += open_tag + close_tag;
            }

            frm.focus();
        }
    });

    $.extend($.fn, {
        bindUp: function () {
            var event_names = {};
            var event_name_alias = {mouseenter: 'mouseover', mouseleave: 'mouseout'};

            this.bind.apply(this, arguments);

            $.each(arguments[0].split(/\s+/), function (k, v) {
                if (v.trim() === '') {
                    return;
                }

                event_names[v.split('.')[0]] = 1;
            });

            return this.each(function () {
                var is_model_jquery = typeof $._data !== 'undefined' && typeof $.data(this, 'events') === 'undefined';

                var data_func = is_model_jquery ? $._data : $.data;

                var events = data_func(this, 'events');

                if (!events) {
                    return;
                }

                for (var event_name in event_names) {
                    var event_handlers = events[event_name];

                    if (!event_handlers) {
                        if (!is_model_jquery || typeof event_name_alias[event_name] === 'undefined') {
                            continue;
                        }

                        event_handlers = events[event_name_alias[event_name]];
                    }

                    event_handlers.splice(0, 0, event_handlers.pop());
                }
            });
        },
        findAll: function (selector) {
            return this.find(selector).andSelf().filter(selector);
        },
        getVal: function () {
            var elm = $(this[0]);

            var _custom = elm.data('custom_method__getVal');

            if (_custom) {
                return _custom.apply(this, arguments);
            }

            switch (this[0].tagName.toLowerCase()) {
                case 'input':
                    var type = elm.attr('type').toLowerCase();

                    if (type == 'radio' || type == 'checkbox') {
                        var name = elm.attr('name');

                        if (name) {
                            var parent = elm.parent();

                            while (parent[0].tagName.toLowerCase() != 'body' && parent[0].tagName.toLowerCase() != 'form') {
                                parent = parent.parent();
                            }

                            var collection = 'input[type="' + type + '"][name="' + name + '"]';

                            if (parent[0].tagName.toLowerCase() == 'body') {
                                collection = ':not(form) ' + collection;
                            }

                            collection = parent.find(collection);

                            if (collection.length > 1) {
                                var data = [];

                                collection.each(function () {
                                    if (this.checked) {
                                        data.push($(this).val());
                                    }
                                });

                                return data;
                            }
                        }

                        return this[0].checked ? elm.val() : '';
                    } else if (!type || type == 'text' || type == 'password') {
                        return elm.attr('placeholder') && elm.val() == elm.attr('placeholder') ? '' : elm.val();
                    }
                    break;
                case 'select':
                    var data = [];
                    for (var i = 0; i < this[0].options.length; i++) {
                        if (this[0].options[i].selected) {
                            data.push(this[0].options[i].value);
                        }
                    }
                    return data;
                    break;
                default:
                    if (elm.data('osc-editor')) {
                        return $(this).data('osc-editor').getContent();
                    }
            }

            return elm.val();
        },
        moveToCenter: function () {
            return this.each(function () {
                var iebody = document.compatMode == 'CSS1Compat' ? document.documentElement : document.body;

                var stop = $.browser.msie ? iebody.scrollTop : window.pageYOffset;
                var doch = $.browser.msie ? iebody.clientHeight : window.innerHeight;
                var docw = $.browser.msie ? iebody.clientWidth : window.innerWidth;

                var objh = this.offsetHeight;
                var objw = this.offsetWidth;

                x = docw / 2 - objw / 2;
                y = stop + doch / 2 - objh / 2;

                if (x < 0) {
                    x = 0;
                }

                if (y < 0) {
                    y = 0;
                }
                
                $(this).offset({
                    left: x,
                    top: y
                });
            });
        },
        realOffset: function () {
            return $(this[0]).offset();
        },
        markElement: function (sExpandoProperty) {
            return this.each(function () {
                this['MenuElem'] = 1;

                for (var x = 0, node = null; x < this.childNodes.length; x++) {
                    node = this.childNodes[x];

                    if (node.tagName) {
                        $(node).markElement(sExpandoProperty);
                    }
                }
            });
        },
        realWidth: function () {
            var elm = this[0];

            if (elm.tagName.toLowerCase() != 'img') {
                elm = $(elm);

                var additional_size = 0;

                if (!isNaN(parseInt(elm.css('padding-left'), 10))) {
                    additional_size += parseInt(elm.css('padding-left'), 10);
                }

                if (!isNaN(parseInt(elm.css('padding-right'), 10))) {
                    additional_size += parseInt(elm.css('padding-right'), 10);
                }

                if (!isNaN(parseInt(elm.css('border-left-width'), 10))) {
                    additional_size += parseInt(elm.css('border-left-width'), 10);
                }

                if (!isNaN(parseInt(elm.css('border-right-width'), 10))) {
                    additional_size += parseInt(elm.css('border-right-width'), 10);
                }

                if (arguments.length > 0) {
                    var width = parseInt(arguments[0]);

                    if (isNaN(width)) {
                        return this;
                    }

                    width -= additional_size;

                    if (width < 1) {
                        return this;
                    }

                    return elm.width(width);
                }

                return elm.width() + additional_size;
            }

            if (!elm.naturalWidth) {
                elm.naturalWidth = elm.width;
                elm.naturalHeight = elm.height;

                var new_img = new Image();

                new_img.onload = function () {
                    elm.naturalWidth = this.width;
                    elm.naturalHeight = this.height;
                };

                new_img.src = elm.src;

                if (new_img.complete) {
                    elm.naturalWidth = new_img.width;
                    elm.naturalHeight = new_img.height;
                }
            }

            return elm.naturalWidth;
        },
        realHeight: function () {
            var elm = this[0];

            if (elm.tagName.toLowerCase() != 'img') {
                elm = $(elm);

                var additional_size = 0;

                if (!isNaN(parseInt(elm.css('padding-top')))) {
                    additional_size += parseInt(elm.css('padding-top'));
                }

                if (!isNaN(parseInt(elm.css('padding-bottom')))) {
                    additional_size += parseInt(elm.css('padding-bottom'));
                }

                if (!isNaN(parseInt(elm.css('border-top-width')))) {
                    additional_size += parseInt(elm.css('border-top-width'));
                }

                if (!isNaN(parseInt(elm.css('border-bottom-width')))) {
                    additional_size += parseInt(elm.css('border-bottom-width'));
                }

                if (arguments.length > 0) {
                    var height = parseInt(arguments[0]);

                    if (isNaN(height)) {
                        return this;
                    }

                    height -= additional_size;

                    if (height < 1) {
                        return this;
                    }

                    return elm.height(height);
                }

                return elm.height() + additional_size;
            }

            if (!elm.naturalHeight) {
                elm.naturalWidth = elm.width;
                elm.naturalHeight = elm.height;

                var new_img = new Image();

                new_img.onload = function () {
                    elm.naturalWidth = this.width;
                    elm.naturalHeight = this.height;
                };

                new_img.src = elm.src;

                if (new_img.complete) {
                    elm.naturalWidth = new_img.width;
                    elm.naturalHeight = new_img.height;
                }
            }

            return elm.naturalHeight;
        },
        fixScrollable: function () {
            $(this[0]).attr('tabindex', '-1').focus();
            return this;
        },
        swapZIndex: function () {
            return this.each(function () {
                var main_node = $(this);

                if (main_node.css('position') !== 'static') {
                    main_node.css('z-index', 1);

                    var max_zindex = Math.max.apply(null, $.map(main_node.parent().children(), function (node) {
                        node = $(node);

                        //if (node.css('position') !== 'static') {
                        return parseInt(node.css('z-index')) || 1;
                        //} else {
                        //    return 1;
                        //}
                    }));

                    main_node.css('z-index', max_zindex + 1);
                }
            });
        },
        getAttrConfig: function (config_map, opts) {
            var config = {}, config_value, is_json, keep_attr = true, attr_prefix = 'osc-', config_key, attr_name;

            if (typeof opts === 'object' && opts !== null) {
                if (typeof opts.keep_attr !== 'undefined') {
                    keep_attr = opts.keep_attr ? true : false;
                }

                if (typeof opts.attr_prefix !== 'undefined') {
                    attr_prefix = (opts.attr_prefix + '').trim();
                }
            }

            for (var config_key in config_map) {
                if (Array.isArray(config_map[config_key])) {
                    attr_name = config_map[config_key][0];
                    is_json = config_map[config_key][1];
                } else {
                    attr_name = config_map[config_key];
                    is_json = false;
                }

                attr_name = attr_prefix + attr_name;

                if (!this.hasAttr(attr_name)) {
                    continue;
                }

                config_value = this.attr(attr_name);

                if (!keep_attr) {
                    this.removeAttr(attr_name);
                }

                if (is_json) {
                    eval('config_value = ' + config_value);
                }

                config[config_key] = config_value;
            }

            return config;
        },
        hasAttr: function (attr_name) {
            var attr_value = this.attr(attr_name);
            return typeof attr_value !== 'undefined' && attr_value !== false;
        }
    });

    $.browser.mac = navigator.userAgent.toLowerCase().indexOf('mac') != -1 || navigator.vendor == 'Apple Computer, Inc.';

    $(window).load(function () {
        $.WIN_READY = true;
    });
})(jQuery);