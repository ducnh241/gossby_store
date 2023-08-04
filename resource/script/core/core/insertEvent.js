(function ($) {
    'use strict';

    $(document).ready(function () {
        var style = document.createElement("style");

        style.appendChild(document.createTextNode(""));
        document.head.appendChild(style);

        var css_index = 0;

        try {
            style.sheet.insertRule("@keyframes OSC_InsertedMarker_Anim {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-webkit-keyframes OSC_InsertedMarker_Anim {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-moz-keyframes OSC_InsertedMarker_Anim {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-o-keyframes OSC_InsertedMarker_Anim {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-ms-keyframes OSC_InsertedMarker_Anim {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        style.sheet.insertRule("[data-insert-cb] {animation-duration: 0.001s; -o-animation-duration: 0.001s; -ms-animation-duration: 0.001s; -moz-animation-duration: 0.001s; -webkit-animation-duration: 0.001s; animation-name: OSC_InsertedMarker_Anim; -o-animation-name: OSC_InsertedMarker_Anim; -ms-animation-name: OSC_InsertedMarker_Anim; -moz-animation-name: OSC_InsertedMarker_Anim; -webkit-animation-name: OSC_InsertedMarker_Anim;}", css_index);

        function _processCallback(node, callback_funcs, e) {
            try {
                callback_funcs = callback_funcs.replace(/\s+/gm, '');
                
                if (callback_funcs) {
                    callback_funcs = callback_funcs.split(',');

                    $.each(callback_funcs, function (k, f) {
                        try {
                            window[f].apply(node, e);
                        } catch (e) {
                            if(e.stack) {
                                console.error(f);
                                console.error(e.stack);
                            } else {
                                console.error(f + ':' + e.message);
                            }
                        }
                    });
                } else {
                    $(document).trigger('insert', [node]);
                    $(node).trigger('insert');
                }
            } catch (e) {
                console.error('Insert callback: ' + e.message);
            }
        }

        var OSC_Insert_Listener = function (e) {
            if (e.animationName === 'OSC_InsertedMarker_Anim') {
                var callback_funcs = e.target.getAttribute('data-insert-cb');
                e.target.removeAttribute('data-insert-cb');
            
                _processCallback(e.target, callback_funcs, e);
            }
        };

        document.addEventListener("animationstart", OSC_Insert_Listener, false);
        document.addEventListener("MSAnimationStart", OSC_Insert_Listener, false);
        document.addEventListener("webkitAnimationStart", OSC_Insert_Listener, false);

        $('[data-insert-cb]').each(function () {
            var callback_funcs = this.getAttribute('data-insert-cb');
            this.removeAttribute('data-insert-cb');
            _processCallback(this, callback_funcs);
        });
    });
})(jQuery);