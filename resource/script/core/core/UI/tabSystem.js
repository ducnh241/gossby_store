(function ($) {
    'use strict';

    function OSC_TabSystem(element, options) {
        this.config = function (options) {
            if (typeof options != 'object') {
                options = {};
            }

            for (var k in options) {
                if (k.substr(0, 1) == '_') {
                    delete options[k];
                }
            }

            $.extend(this, options);

            if (typeof this.element != 'object' || !this.element.jquery) {
                this.element = $(this.element);
            }

            var self = this;

            var default_tab_key = null;

            this.element.find('> .tabs > *').each(function () {
                var tab = $(this);
                var tab_key = tab.attr('tab-key');

                if (!tab_key) {
                    return;
                }

                var content = self.element.find('> .tabs-content > [tab-key="' + tab_key + '"]');

                if (!content[0]) {
                    return;
                }

                if (!default_tab_key) {
                    default_tab_key = tab_key;
                }

                tab.unbind('.tabSystem').bind('click.tabSystem', function () {
                    if (self.selected) {
                        self.tabs[self.selected].tab.removeAttr('selected');
                        self.tabs[self.selected].content.removeAttr('selected');
                    }

                    self.selected = tab_key;

                    tab.attr('selected', 'selected');
                    content.attr('selected', 'selected');
                });

                self.tabs[tab_key] = {tab: tab, content: content};
            });

            if (!this.tabs[this.selected]) {
                this.selected = default_tab_key;
            }

            this.tabs[this.selected].tab.trigger('click');
        };

        this.element = null;
        this.selected = null;
        this.tabs = {};

        options.element = element;

        this.config(options);
    }

    $.fn.osc_tabSystem = function () {
        var func = null;

        if (arguments.length > 0 && typeof arguments[0] == 'string') {
            func = arguments[0];
        }

        if (func) {
            var opts = [];

            for (var x = 1; x < arguments.length; x++) {
                opts.push(arguments[x]);
            }
        } else {
            opts = arguments[0];
        }

        return this.each(function () {
            var instance = $(this).data('osc-tabSystem');

            if (func) {
                if (instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if (!instance) {
                    $(this).data('osc-tabSystem', new OSC_TabSystem(this, opts));
                }
            }
        });
    };

    window.initTabSystem = function () {
        var elm = $(this);

        var options = {};

        var selected_tab = elm.attr('selected-tab');

        if (selected_tab) {
            options.selected = selected_tab;
        }

        elm.osc_tabSystem(options);
    };
})(jQuery);