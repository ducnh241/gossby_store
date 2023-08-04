(function ($) {
    'use strict';

    window.OSC_ColorPicker_Helper = {
        fixHSB: function (H, S, B) {
            H = parseInt(H);
            S = parseInt(S);
            B = parseInt(B);

            if (isNaN(H)) {
                H = 0;
            }

            if (isNaN(S)) {
                S = 0;
            }

            if (isNaN(B)) {
                B = 0;
            }

            return {
                H: Math.min(360, Math.max(0, H)),
                S: Math.min(100, Math.max(0, S)),
                B: Math.min(100, Math.max(0, B))
            };
        },
        fixRGB: function (R, G, B) {
            return {
                R: Math.min(255, Math.max(0, R)),
                G: Math.min(255, Math.max(0, G)),
                B: Math.min(255, Math.max(0, B))
            };
        },
        fixHex: function (hex) {
            var len = 6 - hex.length;

            if (len > 0) {
                var o = [];

                for (var i = 0; i < len; i++) {
                    o.push('0');
                }

                o.push(hex);

                hex = o.join('');
            }

            return hex;
        },
        HSB2RGB: function (H, S, B) {
            var RGB = {};

            H = Math.round(H);
            S = Math.round(S * 255 / 100);

            var V = Math.round(B * 255 / 100);

            if (S == 0) {
                RGB.R = RGB.G = RGB.B = V;
            } else {
                var t1 = V;
                var t2 = (255 - S) * V / 255;
                var t3 = (t1 - t2) * (H % 60) / 60;

                if (H == 360) {
                    H = 0;
                }

                if (H < 60) {
                    RGB.R = t1;
                    RGB.G = t2 + t3;
                    RGB.B = t2;
                } else if (H < 120) {
                    RGB.R = t1 - t3;
                    RGB.G = t1;
                    RGB.B = t2;
                } else if (H < 180) {
                    RGB.R = t2;
                    RGB.G = t1;
                    RGB.B = t2 + t3;
                } else if (H < 240) {
                    RGB.R = t2;
                    RGB.G = t1 - t3;
                    RGB.B = t1;
                } else if (H < 300) {
                    RGB.R = t2 + t3;
                    RGB.G = t2;
                    RGB.B = t1;
                } else if (H < 360) {
                    RGB.R = t1;
                    RGB.G = t2;
                    RGB.B = t1 - t3;
                } else {
                    RGB.R = 0;
                    RGB.G = 0;
                    RGB.B = 0;
                }
            }

            for (var x in RGB) {
                RGB[x] = Math.round(RGB[x]);
            }

            return RGB;
        },
        RGB2HSB: function (R, G, B) {
            var HSB = {
                H: 0,
                S: 0,
                B: 0
            };

            var min = Math.min(R, G, B);
            var max = Math.max(R, G, B);

            var delta = max - min;

            HSB.B = max;
            HSB.S = max != 0 ? 255 * delta / max : 0;

            if (HSB.S != 0) {
                if (R == max) {
                    HSB.H = (G - B) / delta;
                } else if (G == max) {
                    HSB.H = 2 + (B - R) / delta;
                } else {
                    HSB.H = 4 + (R - G) / delta;
                }
            } else {
                HSB.H = -1;
            }

            HSB.H *= 60;

            if (HSB.H < 0) {
                HSB.H += 360;
            }

            HSB.S *= 100 / 255;
            HSB.B *= 100 / 255;

            return HSB;
        },
        RGB2CMYK: function (R, G, B) {
            R /= 255;
            G /= 255;
            B /= 255;

            var CMYK = {};

            CMYK.K = Math.min(1 - R, 1 - G, 1 - B);
            CMYK.C = (1 - R - CMYK.K) / (1 - CMYK.K);
            CMYK.M = (1 - G - CMYK.K) / (1 - CMYK.K);
            CMYK.Y = (1 - B - CMYK.K) / (1 - CMYK.K);

            CMYK.C = Math.round(CMYK.C * 100);
            CMYK.M = Math.round(CMYK.M * 100);
            CMYK.Y = Math.round(CMYK.Y * 100);
            CMYK.K = Math.round(CMYK.K * 100);

            for (var x in CMYK) {
                if (isNaN(CMYK[x])) {
                    CMYK[x] = 0;
                }
            }

            return CMYK;
        },
        CMYK2RGB: function (C, M, Y, K) {
            C /= 100;
            M /= 100;
            Y /= 100;
            K /= 100;

            var RGB = {};

            RGB.R = 1 - Math.min(1, C * (1 - K) + K);
            RGB.G = 1 - Math.min(1, M * (1 - K) + K);
            RGB.B = 1 - Math.min(1, Y * (1 - K) + K);

            RGB.R = Math.round(RGB.R * 255);
            RGB.G = Math.round(RGB.G * 255);
            RGB.B = Math.round(RGB.B * 255);

            return RGB;
        },
        CMYK2HSB: function (C, M, Y, K) {
            var RGB = this.CMYK2RGB(C, M, Y, K);
            return this._RGB2HSB(RGB.R, RGB.G, RGB.B);
        },
        RGB2Hex: function (R, G, B) {
            var hex = [this.dec2Hex(R), this.dec2Hex(G), this.dec2Hex(B)];

            $.each(hex, function (nr, val) {
                if (val.length === 1) {
                    hex[nr] = '0' + val;
                }
            });

            return hex.join('').toLowerCase();
        },
        HSB2Hex: function (H, S, B) {
            var RGB = this.HSB2RGB(H, S, B);
            return this.RGB2Hex(RGB.R, RGB.G, RGB.B);
        },
        Hex2RGB: function (hex) {
            var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);

            return {
                R: hex >> 16,
                G: (hex & 0x00FF00) >> 8,
                B: hex & 0x0000FF
            };
        },
        Hex2HSB: function (hex) {
            var RGB = this.Hex2RGB(hex);
            return this.RGB2HSB(RGB.R, RGB.G, RGB.B);
        },
        dec2Hex: function (dec) {
            return dec.toString(16);
        },
        invertColor: function (R, G, B) {
            var hex = this.RGB2Hex(R, G, B);

            R = (255 - parseInt(hex.slice(0, 2), 16)).toString(16);
            G = (255 - parseInt(hex.slice(2, 4), 16)).toString(16);
            B = (255 - parseInt(hex.slice(4, 6), 16)).toString(16);

            return this.Hex2RGB(R.str_pad(1, '0') + G.str_pad(1, '0') + B.str_pad(1, '0'));
        },
        brightness: function (R, G, B) {
            return (R * 299 + G * 587 + B * 114) / 1000;
        },
        webSafe: function (R, G, B) {
            var tmp;

            tmp = R % 51;

            if (tmp > 25) {
                tmp = R + 51 - tmp;
            } else {
                tmp = R - tmp;
            }

            var c1 = this.dec2Hex(Math.round(tmp / 17));

            tmp = G % 51;

            if (tmp > 25) {
                tmp = G + 51 - tmp;
            } else {
                tmp = G - tmp;
            }

            var c2 = this.dec2Hex(Math.round(tmp / 17));

            tmp = B % 51;

            if (tmp > 25) {
                tmp = B + 51 - tmp;
            } else {
                tmp = B - tmp;
            }

            var c3 = this.dec2Hex(Math.round(tmp / 17));

            return (c1 + c1 + c2 + c2 + c3 + c3).toLowerCase();
        },
        theme_colors: [['#ffffff', '#f2f2f2', '#d8d8d8', '#bfbfbf', '#a5a5a5', '#7f7f7f'],
            ['#000000', '#7f7f7f', '#595959', '#3f3f3f', '#262626', '#0c0c0c'],
            ['#eeece1', '#ddd9c3', '#c4bd97', '#938953', '#494429', '#1d1b10'],
            ['#1f497d', '#c6d9f0', '#8db3e2', '#548dd4', '#17365d', '#0f243e'],
            ['#4f81bd', '#dbe5f1', '#b8cce4', '#95b3d7', '#366092', '#244061'],
            ['#c0504d', '#f2dcdb', '#e5b9b7', '#d99694', '#953734', '#632423'],
            ['#9bbb59', '#ebf1dd', '#d7e3bc', '#c3d69b', '#76923c', '#4f6128'],
            ['#8064a2', '#e5e0ec', '#ccc1d9', '#b2a2c7', '#5f497a', '#3f3151'],
            ['#4bacc6', '#dbeef3', '#b7dde8', '#92cddc', '#31859b', '#205867'],
            ['#f79646', '#fdeada', '#fbd5b5', '#fac08f', '#e36c09', '#974806']],
        standard_colors: ['#c00000', '#ff0000', '#ffc000', '#ffff00', '#92d050', '#00b050', '#00b0f0', '#0070c0', '#002060', '#7030a0'],
        swatches: {},
        recent: {},
        renderColorTable: function (toggleMenuInst, swatch_name, allow_alpha_channel, allow_no_color) {
            var self = this;

            var func_arguments = $.map(arguments, function (value, index) {
                return [value];
            });

            this.menu_wrap.html('');

            var container = $('<div />').appendTo(this.menu_wrap);

            $('<div />').addClass('head').html('Theme colors').appendTo(container);

            var grid = $('<div />').addClass('color-table').appendTo(container);

            var _hex_to_data_color = function (hex) {
                var RGBA = self.Hex2RGB(hex);
                RGBA.A = 100;

                return JSON.stringify(RGBA);
            };

            for (var x = 0; x < this.theme_colors.length; x++) {
                var color_container = null;

                for (var y = 0; y < this.theme_colors[x].length; y++) {
                    if (y < 2) {
                        var node = $('<div />');

                        if (y < 1 && grid[0].childNodes[x]) {
                            node.insertBefore(grid[0].childNodes[x]);
                        } else {
                            node.appendTo(grid);
                        }

                        color_container = $('<ul />').addClass('color-container').appendTo(node);
                    }

                    $('<li />').css('background', this.theme_colors[x][y]).attr('data-color', _hex_to_data_color(this.theme_colors[x][y])).click(function () {
                        self.selectColor.apply(self, func_arguments.concat([$(this).attr('data-color')]));
                    }).appendTo(color_container);
                }
            }

            $('<div />').addClass('head').html('Standard colors').appendTo(container);

            grid = $('<div />').addClass('color-table').appendTo(container);

            for (var x = 0; x < this.standard_colors.length; x++) {
                $('<li />').css('background', this.standard_colors[x]).attr('data-color', _hex_to_data_color(this.standard_colors[x])).click(function () {
                    self.selectColor.apply(self, func_arguments.concat([$(this).attr('data-color')]));
                }).appendTo($('<ul />').addClass('color-container').appendTo($('<div />').appendTo(grid)));
            }

            if (typeof swatch_name === 'string' && typeof this.swatches[swatch_name] !== 'undefined' && this.swatches[swatch_name].length > 0) {
                $('<div />').addClass('head').html('Swatches').appendTo(container);

                grid = $('<div />').addClass('color-table').appendTo(container);

                for (var x = 0; x < this.swatches[swatch_name].length; x++) {
                    var color_data = this.swatches[swatch_name][x];

                    if (!allow_alpha_channel && color_data.A < 100) {
                        continue;
                    }

                    $('<li />').css('background', this.colorData2ColorString(color_data)).attr('data-color', JSON.stringify(color_data)).click(function () {
                        self.selectColor.apply(self, func_arguments.concat([$(this).attr('data-color')]));
                    }).appendTo($('<ul />').addClass('color-container').appendTo($('<div />').appendTo(grid)));
                }
            }

            if (typeof swatch_name === 'string' && typeof this.recent[swatch_name] !== 'undefined' && this.recent[swatch_name].length > 0) {
                $('<div />').addClass('head').html('Recent').appendTo(container);

                grid = $('<div />').addClass('color-table').appendTo(container);

                for (var x = 0; x < this.recent[swatch_name].length; x++) {
                    var color_data = this.recent[swatch_name][x];

                    if (!allow_alpha_channel && color_data.A < 100) {
                        continue;
                    }

                    $('<li />').css('background', this.colorData2ColorString(color_data)).attr('data-color', JSON.stringify(color_data)).click(function () {
                        self.selectColor.apply(self, func_arguments.concat([$(this).attr('data-color')]));
                    }).appendTo($('<ul />').addClass('color-container').appendTo($('<div />').appendTo(grid)));
                }
            }

            var bottom_bar = $('<div />').addClass('bottom-bar').appendTo(container);

            $('<div />').addClass('color-picker-btn').click(function (e) {
                self.renderColorPicker.apply(self, func_arguments);
            }).appendTo(bottom_bar);

            if (allow_no_color) {
                $('<li />').addClass('no-color').appendTo($('<ul />').addClass('color-container').click(function () {
                    self.selectColor.apply(self, func_arguments.concat([false]));
                }).appendTo(bottom_bar));
            }
        },
        colorData2ColorString: function (RGBA) {
            return (RGBA.A < 100) ? ('rgba(' + RGBA.R + ',' + RGBA.G + ',' + RGBA.B + ',' + (RGBA.A / 100) + ')') : ('#' + this.RGB2Hex(RGBA.R, RGBA.G, RGBA.B));
        },
        selectColor: function (toggleMenuInst, swatch_name, allow_alpha_channel, allow_no_color, color_data) {
            if (!color_data) {
                $(toggleMenuInst.toggler).data('osc-colorPicker')._selectColor(null, null, null);
                toggleMenuInst.hide();
                return;
            } else if (typeof color_data === 'string') {
                eval('color_data = ' + color_data);
            }

            if (typeof swatch_name === 'string') {
                if (typeof this.recent[swatch_name] === 'undefined') {
                    this.recent[swatch_name] = [];
                } else if (this.recent[swatch_name].length == this.standard_colors.length) {
                    this.recent[swatch_name].shift();
                }

                this.recent[swatch_name].push(color_data);
            }

            toggleMenuInst.hide();

            if ($(toggleMenuInst.toggler).data('osc-colorPicker').color_mode === 1) {
                var selected_color = this.colorData2ColorString(color_data);
            } else if ($(toggleMenuInst.toggler).data('osc-colorPicker').color_mode === 2) {
                var selected_color = {hex: '#' + this.RGB2Hex(color_data.R, color_data.G, color_data.B), alpha: color_data.A};
            } else if ($(toggleMenuInst.toggler).data('osc-colorPicker').color_mode === 3) {
                var selected_color = {
                    color: this.colorData2ColorString(color_data),
                    hex: '#' + this.RGB2Hex(color_data.R, color_data.G, color_data.B),
                    alpha: color_data.A
                };
            } else {
                var selected_color = color_data;
            }

            $(toggleMenuInst.toggler).data('osc-colorPicker')._selectColor(selected_color, this.brightness(color_data.R, color_data.G, color_data.B), this.invertColor(color_data.R, color_data.G, color_data.B));
        },
        renderColorPicker: function (toggleMenuInst, swatch_name, allow_alpha_channel, allow_no_color) {
            var self = this;

            this.menu_wrap.html('');

            var container = $('<div />').addClass('color-picker').appendTo(this.menu_wrap);

            var grid = $('<div />').addClass('grid').appendTo(container);

            var column = $('<div />').appendTo(grid);

            var sliders = $('<div />').addClass('sliders').appendTo(column);

            var overlay_area = $('<div />').addClass('overlay-area').appendTo(sliders);
            var overlay_selector = $('<div />').addClass('selector').appendTo(overlay_area);

            var hue_area = $('<div />').addClass('hue-area').appendTo(sliders);
            var hue_slider = $('<div />').addClass('slider').appendTo(hue_area);

            if (allow_alpha_channel) {
                var alpha_channel_area = $('<div />').addClass('alpha-channel-area').appendTo(sliders);
                var alpha_channel_bg = $('<div />').addClass('alpha-channel-bg').appendTo(alpha_channel_area);
                var alpha_channel_slider = $('<div />').addClass('slider').appendTo(alpha_channel_area);
            }

            var inputs = $('<div />').addClass('inputs').appendTo(column);

            var input = $('<div />').addClass('input').appendTo(inputs);
            var R_input = $('<input />').attr('type', 'text')
                    .change(function () {
                        updateColor('rgb');
                    })
                    .appendTo(input);
            $('<label />').html('R').appendTo(input);

            input = $('<div />').addClass('input').appendTo(inputs);
            var G_input = $('<input />').attr('type', 'text')
                    .change(function () {
                        updateColor('rgb');
                    })
                    .appendTo(input);
            $('<label />').html('G').appendTo(input);

            input = $('<div />').addClass('input').appendTo(inputs);
            var B_input = $('<input />').attr('type', 'text')
                    .change(function () {
                        updateColor('rgb');
                    })
                    .appendTo(input);
            $('<label />').html('B').appendTo(input);

            if (allow_alpha_channel) {
                input = $('<div />').addClass('input').appendTo(inputs);
                var A_input = $('<input />').attr('type', 'text')
                        .change(function () {
                            updateColor('rgb');
                        })
                        .appendTo(input);
                $('<label />').html('A').appendTo(input);
            }

            column = $('<div />').appendTo(grid);

            var color_selected = $('<div />').addClass('color-selected').appendTo(column);

            var color_preview = $('<div />').addClass('color').appendTo($('<div />').addClass('preview').appendTo(color_selected));

            input = $('<div />').addClass('hex-input input').appendTo(color_selected);
            var HEX_input = $('<input />').attr('type', 'text')
                    .change(function () {
                        updateColor('hex');
                    })
                    .appendTo(input);
            $('<label />').html('HEX').appendTo(input);

            var actions = $('<div />').addClass('actions').appendTo(column);

            var func_arguments = $.map(arguments, function (value, index) {
                return [value];
            });

            var get_color_data = function () {
                return {R: parseInt(R_input.val()), G: parseInt(G_input.val()), B: parseInt(B_input.val()), A: allow_alpha_channel ? parseInt(A_input.val()) : 100};
            };

            $('<button />').attr('type', 'button').html('Apply').click(function () {
                self.selectColor.apply(self, func_arguments.concat([get_color_data()]));
            }).appendTo(actions);

            if (typeof swatch_name === 'string') {
                $('<button />').attr('type', 'button').html('Swatches').click(function () {
                    if (typeof self.swatches[swatch_name] === 'undefined') {
                        self.swatches[swatch_name] = [];
                    }

                    self.swatches[swatch_name].push(get_color_data());

                    self.renderColorTable.apply(self, func_arguments);
                }).appendTo(actions);
            }

            $('<button />').attr('type', 'button').html('Cancel').click(function () {
                self.renderColorTable.apply(self, func_arguments);
            }).appendTo(actions);

            var H_val = 0;
            var S_val = 0;
            var B_val = 0;

            var hue_value = 0;
            var hex_value = 0;

            var updateColor = function (color_mode, skip_reset_position) {
                var HSB, RGB;

                if (typeof color_mode !== 'string') {
                    color_mode = 'hsb';
                }

                switch (color_mode.toLowerCase()) {
                    case 'hex':
                        HSB = self.Hex2HSB(HEX_input.val());
                        break;
                    case 'rgb':
                        HSB = self.RGB2HSB(R_input.val(), G_input.val(), B_input.val());
                        break;
                    default:
                        HSB = self.fixHSB(H_val, S_val, B_val);
                }

                H_val = HSB.H;
                S_val = HSB.S;
                B_val = HSB.B;

                if (hue_value !== HSB.H) {
                    hue_value = HSB.H;
                    overlay_area.css('background-color', '#' + self.HSB2Hex(HSB.H, 100, 100));
                }

                hex_value = self.HSB2Hex(HSB.H, HSB.S, HSB.B);

                HEX_input.val(hex_value);

                var RGB = self.HSB2RGB(HSB.H, HSB.S, HSB.B);

//                var safe_hex = self.webSafe(RGB.R, RGB.G, RGB.B);

                R_input.val(RGB.R);
                G_input.val(RGB.G);
                B_input.val(RGB.B);

                if (allow_alpha_channel) {
                    var alpha_val = parseInt(A_input.val());

                    if (isNaN(alpha_val)) {
                        alpha_val = 100;
                    }

                    A_input.val(Math.min(100, Math.max(0, alpha_val)));

                    alpha_channel_bg.css('background', 'linear-gradient(to top, rgba(' + RGB.R + ',' + RGB.G + ',' + RGB.B + ', 1), rgba(' + RGB.R + ',' + RGB.G + ',' + RGB.B + ', 0))');
                }

                color_preview.css('background-color', allow_alpha_channel ? ('rgba(' + RGB.R + ',' + RGB.G + ',' + RGB.B + ',' + (A_input.val() / 100) + ')') : ('#' + hex_value));

//                if (safe_hex != this.hex_value) {
//                    this.renderer.safe_color.show().attr('safe_hex', safe_hex);
//                    this.renderer.safe_color_preview.css('background-color', '#' + safe_hex);
//                } else {
//                    this.renderer.safe_color.hide();
//                }

                if (!skip_reset_position) {
                    hue_slider.css({
                        left: (hue_area.width() - hue_slider.width()) / 2 + 'px',
                        top: (hue_area.height() - (HSB.H / 360 * hue_area.height()) - (hue_slider.height() / 2)) + 'px'
                    });

                    overlay_selector.css({
                        top: (overlay_area.height() - (HSB.B / 100 * overlay_area.height()) - (overlay_selector.height() / 2)) + 'px',
                        left: ((HSB.S / 100 * overlay_area.width()) - (overlay_selector.width() / 2)) + 'px'
                    });

                    if (allow_alpha_channel) {
                        alpha_channel_slider.css({
                            left: (alpha_channel_area.width() - alpha_channel_slider.width()) / 2 + 'px',
                            top: (alpha_channel_area.height() / 100 * parseInt(A_input.val())) + 'px'
                        });
                    }
                }
            };

            var hueSliderMoveHook = function () {
                var y = hue_slider.position().top;

                H_val = 360 - y / hue_area.height() * 360;

                updateColor(null, true);
            };

            if (allow_alpha_channel) {
                var alphaChannelSliderMoveHook = function () {
                    A_input.val(Math.ceil(alpha_channel_slider.position().top / alpha_channel_area.height() * 100));
                    updateColor(null, true);
                };
            }

            var selectorMoveHook = function () {
                var x = overlay_selector.position().left;
                var y = overlay_selector.position().top;

                S_val = x / overlay_area.width() * 100;
                B_val = 100 - y / overlay_area.height() * 100;

                updateColor(null, true);
            };

            hue_area.osc_dragger({
                target: hue_slider,
                cursor: 'pointer',
                fire_hook_callback: function (params) {
                    toggleMenuInst.lock_doc_hide = true;

                    var hue_area_offset = hue_area.offset();

                    hue_slider.offset({
                        top: params.e.pageY,
                        left: hue_area_offset.left + (hue_area.width() - hue_slider.width()) / 2
                    });

                    hueSliderMoveHook();

                    params.inst.config({
                        min_x: hue_area_offset.left + (hue_area.width() - hue_slider.width()) / 2,
                        min_y: hue_area_offset.top,
                        max_x: hue_area_offset.left + (hue_area.width() + hue_slider.width()) / 2,
                        max_y: hue_area_offset.top + hue_area.height()
                    });
                },
                drag_hook_callback: function () {
                    hueSliderMoveHook();
                }
            });

            if (allow_alpha_channel) {
                alpha_channel_area.osc_dragger({
                    target: alpha_channel_slider,
                    cursor: 'pointer',
                    fire_hook_callback: function (params) {
                        toggleMenuInst.lock_doc_hide = true;

                        var alpha_channel_area_offset = alpha_channel_area.offset();

                        alpha_channel_slider.offset({
                            top: params.e.pageY,
                            left: alpha_channel_area_offset.left + (alpha_channel_area.width() - alpha_channel_slider.width()) / 2
                        });

                        alphaChannelSliderMoveHook();

                        params.inst.config({
                            min_x: alpha_channel_area_offset.left + (alpha_channel_area.width() - alpha_channel_slider.width()) / 2,
                            min_y: alpha_channel_area_offset.top,
                            max_x: alpha_channel_area_offset.left + (alpha_channel_area.width() + alpha_channel_slider.width()) / 2,
                            max_y: alpha_channel_area_offset.top + alpha_channel_area.height()
                        });
                    },
                    drag_hook_callback: function () {
                        alphaChannelSliderMoveHook();
                    }
                });
            }

            overlay_area.osc_dragger({
                target: overlay_selector,
                cursor: 'crosshair',
                fire_hook_callback: function (params) {
                    toggleMenuInst.lock_doc_hide = true;

                    var overlay_offset = overlay_area.offset();

                    overlay_selector.offset({
                        top: params.e.pageY,
                        left: params.e.pageX
                    });

                    selectorMoveHook();

                    params.inst.config({
                        min_x: overlay_offset.left,
                        min_y: overlay_offset.top,
                        max_x: overlay_offset.left + overlay_area.width(),
                        max_y: overlay_offset.top + overlay_area.height()
                    });

                    overlay_area.addClass('dis-cursor');
                },
                drag_hook_callback: function () {
                    selectorMoveHook();
                },
                drop_hook_callback: function () {
                    overlay_area.removeClass('dis-cursor');
                }
            });

            var node = toggleMenuInst.toggler;
 
            var current_color = node[0].hasAttribute('data-color') ? node[0].getAttribute('data-color') : $(node).css('background-color');

            var matched = current_color.match(/^\s*rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*([\d\.]+)\s*)?\)\s*$/i);

            if (matched) {
                if (allow_alpha_channel && typeof matched[5] !== 'undefined') {
                    A_input.val(parseInt(matched[5] * 100));
                }

                current_color = {
                    R: matched[1],
                    G: matched[2],
                    B: matched[3]
                };
            } else {
                if (!(/^#[0-9a-f]{3,6}$/i).test(current_color)) {
                    current_color = '#000';
                }

                current_color = this.Hex2RGB(current_color);
            }

            R_input.val(current_color.R);
            G_input.val(current_color.G);
            B_input.val(current_color.B);

            updateColor('rgb');




//            var form = $('<div />').addClass('form-area').appendTo(scene);
//
//            var section = $('<div />').addClass('section').appendTo(form);
//
//            var preview = $('<div />').addClass('color-preview').appendTo(section);
//            this.inst._color_preview = $('<div />').addClass('preview').appendTo(preview);
//            this.inst._inputs.hex.hex = $('<input />').appendTo(preview);
//
//            this.safe_color = $('<div />').addClass('safe-color').appendTo(section);
//            $('<div />').addClass('icon').appendTo(this.safe_color);
//            this.safe_color_preview = $('<div />').addClass('preview').appendTo(this.safe_color);
//
//            this.buttons = {};
//
//            var action_scene = $('<div />').addClass('action').appendTo(section);
//            this.buttons.select = $('<span />').addClass('btn btn-mini').html('Select the color').appendTo(action_scene);
//            this.buttons.add2Swatches = $('<span />').addClass('btn btn-mini').html('Add to swatches').appendTo(action_scene);
//
//            section = $('<div />').addClass('section').appendTo(form);
//
//            var color_modes = ['RGB', 'HSB', 'CMYK'];
//
//            for (var i = 0; i < color_modes.length; i++) {
//                var color_mode = color_modes[i];
//
//                var input_group = $('<div />').addClass('input-group ' + color_mode.toLowerCase()).appendTo(section);
//
//                $('<div />').addClass('label').html(color_mode).appendTo(input_group);
//
//                var color_mode_elements = color_mode.split('');
//
//                for (var k = 0; k < color_mode_elements.length; k++) {
//                    var input_element = $('<div />').addClass('input-element').appendTo(input_group);
//                    $('<span />').html(color_mode_elements[k]).appendTo(input_element);
//                    this.inst._inputs[color_mode][color_mode_elements[k]] = $('<input />').attr('type', 'text').appendTo(input_element);
//                }
//            }
        },
        menu_wrap: null
    };

    OSC_ColorPicker_Helper.menu_wrap = $('<div />').addClass('osc-color-picker').click(function (e) {
        e.stopPropagation();
    }).appendTo(document.body);

    function OSC_ColorPicker_Item(node, options) {
        this._initialize = function (node, config) {
            var self = this;

            if (typeof config !== 'object') {
                config = {};
            } else {
                for (var x in config) {
                    if (x.substring(0, 1) === '_') {
                        delete(config[x]);
                    }
                }
            }

            $.extend(this, config);

            this._trigger = $(node);

//            this._trigger.bind('click.OSCColorPicker', function (e) {
//                OSC_ColorPicker_Helper.fire(this.swatch_name, this.allow_alpha_channel);
//            });

            this._trigger.osc_toggleMenu({
                menu: OSC_ColorPicker_Helper.menu_wrap,
                divergent_x: this.divergent_x,
                divergent_y: this.divergent_y,
                open_hook: function (params) {
                    OSC_ColorPicker_Helper.menu_wrap.swapZIndex();
                    
                    try {
                        OSC_ColorPicker_Helper.renderColorTable(this, self.swatch_name, self.allow_alpha_channel, self.allow_no_color);
                    } catch (e) {

                    }
                },
                close_hook: function () {
                    OSC_ColorPicker_Helper.menu_wrap.html('');
                }
            });
        };

        this._selectColor = function (color, brightness, invert_color) {
            try {
                if (typeof this.callback === 'function') {
                    this.callback(color, brightness, invert_color);
                } else {
                    this._trigger.css('background-color', color).css('color', brightness < 120 ? '#fff' : '#333');
                }
            } catch (e) {

            }
        };

        this.getHelper = function () {
            return OSC_ColorPicker_Helper;
        };

        this._trigger = null;
        this.callback = null;
        this.color_mode = 1; //1: rgba, 2: (hex: #fff, alpha: .5), 3: {color: rgba(0,0,0,0), hex: #fff, alpha: 0},4: color data ({R: 0, G: 0, B: 0, A: 0})
        this.swatch_name = 'default';
        this.allow_alpha_channel = true;
        this.allow_no_color = true;
        this.divergent_x = 0;
        this.divergent_y = 0;

        this._initialize(node, options);
    }

    $.fn.osc_colorPicker = function () {
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
            if (func) {
                var instance = $(this).data('osc-colorPicker');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-colorPicker', new OSC_ColorPicker_Item(this, opts));
            }
        });
    };
})(jQuery);