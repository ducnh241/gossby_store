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

(function($) {
    function OSC_Base64() {
        this.encode = function (input) {
            var output = '';
            var i = 0;
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;


            input = this._utf8_encode(input);

            while(i < input.length) {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3 ) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if(isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if(isNaN(chr3)) {
                    enc4 = 64;
                }

                output += this._keyStr.charAt(enc1);
                output += this._keyStr.charAt(enc2);
                output += this._keyStr.charAt(enc3);
                output += this._keyStr.charAt(enc4);
            }

            return output;
        }
        
        this.decode = function (input) {
            var output = '';
            var i = 0;
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;

            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            while(i < input.length) {
                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output += String.fromCharCode(chr1);

                if(enc3 != 64) {
                    output += String.fromCharCode(chr2);
                }

                if(enc4 != 64) {
                    output += String.fromCharCode(chr3);
                }
            }

            output = this._utf8_decode(output);

            return output;
        }
        
        this._utf8_encode = function (string) {
            string  = string.replace(/\r\n/g, "\n");
            var utftext = '';

            for(var i = 0; i < string.length; i ++) {
                var c = string.charCodeAt(i);

                if(c < 128) {
                    utftext += String.fromCharCode(c);
                } else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                } else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
            }

            return utftext;
        }
        
        this._utf8_decode = function (utftext) {
            var string = '';
            var i = 0;
            var c = 0;
            var c1 = 0;
            var c2 = 0;
            var c3;

            while(i < utftext.length) {
                c = utftext.charCodeAt(i);

                if(c < 128) {
                    string += String.fromCharCode(c);
                    i ++;
                } else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i + 1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                } else {
                    c2 = utftext.charCodeAt(i + 1);
                    c3 = utftext.charCodeAt(i + 2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }
            }

            return string;
        }
        
        this._keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    }
    
    $.base64 = new OSC_Base64();
    
    $.extend($, {
        base64_encode : function(text) {
            return $.base64.encode(text);
        },
        base64_decode : function(text) {
            return $.base64.decode(text);
        }
    });
})(jQuery);