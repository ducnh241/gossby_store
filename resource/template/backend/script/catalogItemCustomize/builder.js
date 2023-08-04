(function ($) {
    'use strict';

    function _initCustomInsertCb(class_selector, callback, skip_once_flag) {
        var style = document.createElement("style");

        style.appendChild(document.createTextNode(""));
        document.head.appendChild(style);

        var css_index = 0;

        var uniqid = $.makeUniqid();

        var anim_name = 'OSC_InsertedMarker_Anim__' + uniqid;

        class_selector = class_selector.split(',').map(function (segment) {
            return segment.trim();
        });

        if (!skip_once_flag) {
            class_selector = class_selector.map(function (segment) {
                return segment + ':not([data-anim-' + uniqid + '="1"])';
            });
        }

        try {
            style.sheet.insertRule("@keyframes " + anim_name + " {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-webkit-keyframes " + anim_name + " {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-moz-keyframes " + anim_name + " {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-o-keyframes " + anim_name + " {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule("@-ms-keyframes " + anim_name + " {from {opacity: 0;} to {opacity: 1;}}", css_index);
            css_index++;
        } catch (e) {
        }

        try {
            style.sheet.insertRule(class_selector.join(',') + " {animation-duration: 0.001s; -o-animation-duration: 0.001s; -ms-animation-duration: 0.001s; -moz-animation-duration: 0.001s; -webkit-animation-duration: 0.001s; animation-name: " + anim_name + "; -o-animation-name: " + anim_name + "; -ms-animation-name: " + anim_name + "; -moz-animation-name: " + anim_name + "; -webkit-animation-name: " + anim_name + ";}", css_index);
        } catch (e) {
        }

        var listener = function (e) {
            if (e.animationName === anim_name) {
                e.target.setAttribute('data-anim-' + uniqid, 1);

                try {
                    callback.apply(e.target, e);
                } catch (e) {
                    console.error(callback + ':' + e.message);
                }
            }
        };

        document.addEventListener("animationstart", listener, false);
        document.addEventListener("MSAnimationStart", listener, false);
        document.addEventListener("webkitAnimationStart", listener, false);

        $(class_selector.join(',')).each(function () {
            if (!skip_once_flag) {
                this.setAttribute('data-anim-' + uniqid, 1);
            }

            try {
                callback.apply(this);
            } catch (e) {
                console.error(f + ':' + e.message);
            }
        });
    }

    function PageBuilder(container) {

        this.renderComponent = function (type, component_config) {
            if (!component_config || typeof component_config !== 'object') {
                component_config = null;
            }

            var render_func = 'component' + type.substr(0, 1).toUpperCase() + type.substr(1) + '_Render';
            var setup_func = 'component' + type.substr(0, 1).toUpperCase() + type.substr(1) + '_Setup';

            var comp_node = this[render_func](component_config).attr('data-component', type);

            this.componentWrap(comp_node);

            if (typeof this[setup_func] === 'function') {
                this[setup_func](comp_node);
            }

            var selected_node = this.scene.find('.component-selected');

            if (!selected_node[0]) {
                this.scene.append(comp_node);
            } else {
                selected_node.closest('[data-pagebuilder-droparea]').append(comp_node);
            }

            return comp_node;
        };

        this.renderComponentPanel = function () {
            var $this = this;
            var panel = $('<div />').addClass('component-panel').appendTo(this.container);

            $.each(this.component_panel_data, function (k, v) {
                var group = $('<div />').addClass('component-group').appendTo(panel);
                $('<div />').addClass('group-title').text(v.group).append($.renderIcon('angle-right-solid')).appendTo(group);
                var component_list = $('<div />').addClass('component-list').appendTo(group);

                $.each(v.components, function (k, component) {
                    if (typeof component === 'string') {
                        $('<div />').addClass('head').text(component).appendTo(component_list);
                        return;
                    }

                    var item = $('<div />').addClass('component').text(component.title).appendTo(component_list);

                    if (component.icon) {
                        item.append($.renderIcon(component.icon));
                    }

                    if (component.block_mode) {
                        item.addClass('block-mode');
                    }

                    item.mousedown(function () {
                        var id = $.makeUniqid();

                        try {
                            $this.renderComponent(component.type).attr('data-comp-marker', id);
                            item.attr('data-marker-id', id);
                        } catch (e) {
                        }
                    });

                    $this.initComponentDragDrop(item, function () {
                        var id = item.attr('data-marker-id');

                        var comp = null;

                        if (id) {
                            item.removeAttr('data-marker-id');

                            comp = $('[data-comp-marker="' + id + '"]');

                            if (!comp[0]) {
                                comp = null;
                            } else {
                                comp.removeAttr('data-comp-marker');
                            }
                        }

                        if (!comp) {
                            comp = $this.renderComponent(component.type);
                        }

                        return comp;
                    });
                });

                group.find('> .group-title').click(function () {
                    if (group.hasClass('active')) {
                        return;
                    }

                    var groups = panel.find('.component-group');

                    groups.removeClass('active').removeClass('scrollable');

                    panel.find('.component-group > .component-list').css('max-height', '0px');

                    var height = panel[0].getBoundingClientRect().height;

                    groups.each(function () {
                        height -= $(this).find('> .group-title')[0].getBoundingClientRect().height;
                    });

                    component_list.css('max-height', height + 'px');

                    group.addClass('active');
                });
            });

            panel.find('.component-group:first-child > .group-title').trigger('click');
        };

        this.componentWrap = function (component) {
            var $this = this;

            component.unbind('.addRemoveBtn').bind('mouseenter.addRemoveBtn', function () {
                $('.remove-component-btn').remove();

                if (!component.attr('data-component-require')) {
                    $('<div />').addClass('remove-component-btn').appendTo(component).click(function () {
                        component.remove();
                    });
                }
            }).bind('mouseleave.addRemoveBtn', function () {
                component.find('.remove-component-btn').remove();
            });

            this.initComponentDragDrop(component);

            component.click(function (e) {
                return $this.componentClick(e, component);
            });

            return component;
        };

        this.componentClick = function (e, component) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            component.trigger('select');

            if (component.hasClass('component-selected')) {
                return;
            }

            $this.scene.findAll('.component-selected').removeClass('component-selected');
            component.addClass('component-selected');

            return $this.componentRenderConfigFrm(component);
        };

        this.componentRenderConfigFrm = function (component) {
            this.config_panel.html('');

            var component_type = component[0] === this.scene[0] ? 'scene' : component.attr('data-component');

            if (!component_type || typeof this.components[component_type] === 'undefined' || !Array.isArray(this.components[component_type].config_frm)) {
                return;
            }

            var $this = this;

            $('<div />').addClass('heading').text(this.components[component_type].name).appendTo(this.config_panel);

            var container = $('<div />').addClass('post-frm').appendTo(this.config_panel);

            this.components[component_type].config_frm.forEach(function (form) {
                if (typeof form.type === 'function') {
                    try {
                        form.type.apply($this, [component, $.extend({}, form), container]);
                    } catch (e) {

                    }

                    return;
                }

                var render = 'componentConfigElm_' + form.type.substr(0, 1).toUpperCase() + form.type.substr(1);

                try {
                    container.append($this[render](component, $.extend({}, form)));
                } catch (e) {
                }
            });
        };

        this.dec2Hex = function (dec) {
            return dec.toString(16);
        };

        this.RGB2Hex = function (R, G, B) {
            var hex = [this.dec2Hex(R), this.dec2Hex(G), this.dec2Hex(B)];

            $.each(hex, function (nr, val) {
                if (val.length === 1) {
                    hex[nr] = '0' + val;
                }
            });

            return '#' + hex.join('').toLowerCase();
        };

        this.color2Hex = function (color) {
            var matched = color.match(/^\s*rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*([\d\.]+)\s*)?\)\s*$/i);

            if (matched) {
                color = this.RGB2Hex(parseInt(matched[1]), parseInt(matched[2]), parseInt(matched[3]));
            } else {
                if (!(/^#[0-9a-f]{3,6}$/i).test(color)) {
                    color = '#000';
                }
            }

            return color;
        };

        this._verifyComponentConfigElmId = function (component, id) {
            if (typeof id === 'undefined') {
                id = $.makeUniqid();
            }

            var comp_name = component.attr('data-component');

            if (comp_name) {
                id = comp_name + 'Conf_' + id;
            }

            return id;
        };

        this.componentConfigElm_OptionList = function (component, config) {
            var $this = this;

            var options = [];

            try {
                options = config.getter.apply(this, [component]);
            } catch (e) {

            }

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            $('<label />').attr('for', config.id).text(config.title).appendTo(container);

            var option_list = $('<div />').addClass('config-option-list').appendTo(container);

            var __update = function () {
                var options = [];

                option_list.find('.option-text').each(function () {
                    options.push($(this).attr('data-text'));
                });

                config.setter.apply($this, [component, options]);
            };

            var __getValueList = function (skip_item) {
                var values = [];

                option_list.find('.option-item').each(function () {
                    if (this !== skip_item) {
                        values.push($(this).find('.option-text').attr('data-text'));
                    }
                });

                return values;
            };

            var __optionInputRender = function (value, focus) {
                var item = $('<div />').addClass('option-item').appendTo(option_list);

                var item_content = $('<div />').appendTo(item);

                $('<div />').addClass('option-text').appendTo(item_content).attr('data-text', value).text(value).click(function () {
                    var node = $(this);

                    if (node.find('input')[0]) {
                        return;
                    }

                    var old_text = node.text();

                    node.html('');

                    $('<input />').attr({value: old_text}).appendTo(node).keydown(function (e) {
                        if (e.keyCode === 13) {
                            e.preventDefault();
                        }
                    }).focus(function () {
                        this.select();
                    }).blur(function () {
                        var text = this.value.trim();

                        if (!text) {
                            text = old_text;
                        }

                        item.find('> .error').remove();

                        if (__getValueList(item[0]).indexOf(text) >= 0) {
                            item.append($('<div />').addClass('error').text('The value is always used, please enter another value'));
                            this.focus();
                            this.select();
                            return;
                        }

                        if (typeof config.update_callback === 'function') {
                            config.update_callback.apply($this, [component, old_text, text]);
                        }

                        node.attr('data-text', text).text(text);

                        __update();
                    }).focus();
                });

                $('<div />').addClass('option-control').append($.renderIcon('caret-down')).appendTo(item_content).click(function () {
                    if (!item.next()[0]) {
                        return;
                    }

                    item.insertAfter(item.next());

                    __update();
                });

                $('<div />').addClass('option-control').append($.renderIcon('caret-up')).appendTo(item_content).click(function () {
                    if (!item.prev()[0]) {
                        return;
                    }

                    item.insertBefore(item.prev());

                    __update();
                });

                $('<div />').addClass('option-control').append($.renderIcon('trash-alt-regular')).appendTo(item_content).click(function () {
                    item.remove();
                    __update();
                });

                if (focus) {
                    item.find('.option-text').trigger('click');
                    __update();
                }
            };

            options.forEach(function (value) {
                __optionInputRender(value);
            });

            __update();

            $('<div />').addClass('btn btn-primary btn-small mt10').text('Add new option').click(function () {
                var title = 'Untitled';

                var values = __getValueList();

                var counter = 0;

                while (values.indexOf(title + (counter < 1 ? '' : ' ' + counter)) >= 0) {
                    counter++;
                }

                __optionInputRender(title + (counter < 1 ? '' : ' ' + counter), true);
            }).appendTo(container);

            return container.parent();
        };

        this.componentConfigElm_ColorList = function (component, config) {
            var $this = this;

            var colors = [];

            try {
                colors = config.getter.apply(this, [component]);
            } catch (e) {

            }

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            $('<label />').attr('for', config.id).text(config.title).appendTo(container);

            var option_list = $('<div />').addClass('config-option-list').appendTo(container);

            var __update = function () {
                var colors = [];

                option_list.find('.option-text').each(function () {
                    colors.push({title: $(this).attr('data-text'), hex: $(this).prev().attr('data-color')});
                });

                config.setter.apply($this, [component, colors]);
            };

            var __getValueList = function (skip_item) {
                var values = [];

                option_list.find('.option-item').each(function () {
                    if (this !== skip_item) {
                        values.push($(this).find('.option-text').attr('data-text'));
                    }
                });

                return values;
            };

            var __optionInputRender = function (title, color, focus) {
                var item = $('<div />').addClass('option-item').appendTo(option_list);

                var item_content = $('<div />').appendTo(item);

                var color_picker = $('<span />').addClass('color-picker').appendTo(item_content).css('background-color', color);

                $('<div />').addClass('option-text').appendTo(item_content).attr('data-text', title).text(title).click(function () {
                    var node = $(this);

                    if (node.find('input')[0]) {
                        return;
                    }

                    var old_text = node.text();

                    node.html('');

                    $('<input />').attr({value: old_text}).appendTo(node).keydown(function (e) {
                        if (e.keyCode === 13) {
                            e.preventDefault();
                        }
                    }).focus(function () {
                        this.select();
                    }).blur(function () {
                        var text = this.value.trim();

                        if (!text) {
                            text = old_text;
                        }

                        item.find('> .error').remove();

                        if (__getValueList(item[0]).indexOf(text) >= 0) {
                            item.append($('<div />').addClass('error').text('The value is always used, please enter another value'));
                            this.focus();
                            this.select();
                            return;
                        }

                        if (typeof config.update_callback === 'function') {
                            config.update_callback.apply($this, [component, old_text, text]);
                        }

                        node.attr('data-text', text).text(text);

                        __update();
                    }).focus();
                });

                $('<div />').addClass('option-control').append($.renderIcon('caret-down')).appendTo(item_content).click(function () {
                    if (!item.next()[0]) {
                        return;
                    }

                    item.insertAfter(item.next());

                    __update();
                });

                $('<div />').addClass('option-control').append($.renderIcon('caret-up')).appendTo(item_content).click(function () {
                    if (!item.prev()[0]) {
                        return;
                    }

                    item.insertBefore(item.prev());

                    __update();
                });

                $('<div />').addClass('option-control').append($.renderIcon('trash-alt-regular')).appendTo(item_content).click(function () {
                    item.remove();
                    __update();
                });

                color_picker.attr('data-color', color).osc_colorPicker({
                    divergent_y: 10,
                    allow_no_color: false,
                    allow_alpha_channel: false,
                    callback: function (color) {
                        if (color) {
                            color_picker.attr('data-color', color).css('background-color', color);
                            __update();
                        }
                    }
                });

                if (focus) {
                    item.find('.option-text').trigger('click');
                    __update();
                }

            };

            colors.forEach(function (color) {
                __optionInputRender(color.title, color.hex);
            });

            __update();

            $('<div />').addClass('btn btn-primary btn-small mt10').text('Add new color').click(function () {
                var title = 'Untitled';

                var values = __getValueList();

                var counter = 0;

                while (values.indexOf(title + (counter < 1 ? '' : ' ' + counter)) >= 0) {
                    counter++;
                }

                __optionInputRender(title + (counter < 1 ? '' : ' ' + counter), '#fff', true);
            }).appendTo(container);

            return container.parent();
        };

        this.componentConfigElm_ImageUploader = function (component, config) {
            $.extend(config, {extensions: [], image_mode: true});

            return this.componentConfigElm_Uploader(component, config);
        };

        this.componentConfigElm_Uploader = function (component, config) {
            var $this = this;

            var container = $('<div />').addClass('file-uploader').appendTo($('<div />').addClass('frm-grid'));

            var uploader = $('<div />').addClass('uploader').appendTo(container);

            var file_list = $('<div />').addClass('file-list').appendTo(container);

            uploader.osc_uploader({
                max_files: typeof config.file_limit !== 'undefined' ? config.file_limit : 1,
                max_connections: 5,
                process_url: this.container.attr('data-upload-url'),
                btn_content: config.title,
                dragdrop_content: 'Drop file here to upload',
                image_mode: config.image_mode ? true : false,
                extensions: Array.isArray(config.extensions) ? config.extensions : []
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                var item = $('<div />').attr('file-id', file_id).attr('data-uploader-step', 'queue').addClass('file-item').appendTo(file_list);

                $('<div />').addClass('filename').text(file_name).appendTo(item);
                $('<div />').addClass('filesize').text($.formatSize(file_size)).append($('<span />').addClass('upload-step').appendTo(item)).appendTo(item);

                $('<div />').addClass('uploader-progress-bar').appendTo(item).append($('<div />'));
            }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                var item = file_list.find('> [file-id="' + file_id + '"]');

                if (!item[0]) {
                    return;
                }

                if (parseInt(uploaded_percent) === 100) {
                    item.attr('data-uploader-step', 'process');
                } else {
                    item.attr('data-uploader-step', 'upload');
                    item.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
                }

            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                var item = file_list.find('> [file-id="' + file_id + '"]');

                pointer.success = false;

                if (!item[0]) {
                    return;
                }

                eval('response = ' + response);

                if (response.result !== 'OK') {
                    item.attr('data-uploader-step', 'error')[0].removeAttribute('file-id');
                    item.append($('<div />').addClass('error-info').text(response.message));

                    setTimeout(function () {
                        item.remove();
                    }, 2000);

                    return;
                }

                item.remove();

                try {
                    config.setter.apply($this, [component, response.data.url, response.data.name, response.data.width, response.data.height, config.manage_config]);
                } catch (e) {
                }
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                var item = file_list.find('> [file-id="' + file_id + '"]');

                if (!item[0]) {
                    return;
                }

                item.attr('data-uploader-step', 'error')[0].removeAttribute('file-id');

                item.append($('<div />').addClass('error-info').text('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại'));

                setTimeout(function () {
                    item.remove();
                }, 2000);
            });

            return container.parent();
        };

        this.componentConfigElm_Button = function (component, config) {
            var $this = this;

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            container.append($('<div />').addClass('btn btn-primary').css({width: '100%'}).text(config.title).click(function () {
                try {
                    config.setter.apply($this, [component]);
                } catch (e) {
                }
            }));

            return container.parent();
        };

        this.componentConfigElm_Line = function (component, config) {
            return $('<div />').addClass('frm-line e10');
        };

        this.componentConfigElm_Separate = function (component, config) {
            return $('<div />').addClass('frm-separate e10');
        };

        this.componentConfigElm_TextContent = function (component, config) {
            return this[config.input !== 'textarea' ? 'componentConfigElm_Input' : 'componentConfigElm_Textarea'](component, {
                title: config.title ? config.title : 'Text content',
                setter: function (component, text) {
                    (config.selector ? component.find(config.selector) : component).text(text);
                },
                getter: function () {
                    return (config.selector ? component.find(config.selector) : component).text();
                }
            });
        };

        this.componentConfigElm_LayerKey = function (component, config) {
            return this.componentConfigElm_Input(component, {
                title: 'Layer key',
                setter: function (component, text) {
                    component.attr('data-layer-key', text);
                },
                getter: function () {
                    return component.attr('data-layer-key');
                }
            });
        };

        this.componentConfigElm_Checkbox = function (component, config) {
            var $this = this;

            var value = [];

            try {
                if (typeof config.store_attr !== 'undefined') {
                    var attr_value = (typeof config.store_selector === 'undefined' ? component : component.find(config.store_selector)).attr(config.store_attr);

                    if (attr_value) {
                        value = attr_value.split(',');
                    }
                } else {
                    value = config.getter.apply(this, [component]);

                    if (!Array.isArray(value)) {
                        value = [value];
                    }
                }
            } catch (e) {
            }

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            if (config.title) {
                $('<label />').text(config.title).appendTo(container);
            }

            config.options.forEach(function (option_data, index) {
                if (!Array.isArray(option_data)) {
                    option_data = [option_data, option_data];
                }

                var line = $('<div />').appendTo(container);

                var checkbox = $('<input />').attr({type: 'checkbox', name: config.id + '[]', id: config.id + '__' + index, value: option_data[0]}).click(function () {
                    var data = [];

                    container.find('input[type="checkbox"]').each(function () {
                        if (this.checked) {
                            data.push(this.value);
                        }
                    });

                    try {
                        if (typeof config.store_attr !== 'undefined') {
                            (typeof config.store_selector === 'undefined' ? component : component.find(config.store_selector)).attr(config.store_attr, data.join(','));
                        } else {
                            config.setter.apply($this, [component, data]);
                        }
                    } catch (e) {

                    }
                });

                var checkbox_wrap = $('<div />').addClass('styled-checkbox').appendTo(line);

                checkbox_wrap.append(checkbox);
                checkbox_wrap.append($('<ins />').append($.renderIcon('check-solid')));

                $('<label />').attr('for', config.id + '__' + index).addClass('ml10 label-inline').text(option_data[1]).appendTo(line);

                if (value.indexOf(option_data[0]) >= 0) {
                    checkbox.attr('checked', 'checked');
                }
            });

            return container.parent();
        };

        this.componentConfigElm_Checker = function (component, config) {
            var $this = this;

            try {
                var value = component.attr('data-require') === '1' ? 1 : 0;
            } catch (e) {

            }

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            if (!config.label) {
                config.label = 'Untitled';
            }

            $('<label />').appendTo(container).append($('<div />').addClass('styled-checkbox').append($('<input />').attr({type: 'checkbox', value: '1'}).click(function () {
                try {
                    component.attr('data-require', this.checked ? 1 : 0);
                } catch (e) {

                }
            })).append($('<ins />').append($.renderIcon('check-solid')))).append($('<span />').addClass('ml5').text(config.title));

            if (value === 1) {
                container.find('input')[0].checked = true;
            }

            return container.parent();
        };

        this.componentConfigElm_Radio = function (component, config) {
            var $this = this;

            var value = [];

            try {
                value = config.getter.apply(this, [component]);

                if (!Array.isArray(value)) {
                    value = [value];
                }
            } catch (e) {
            }

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            if (config.title) {
                $('<label />').text(config.title).appendTo(container);
            }

            config.options.forEach(function (option_data, index) {
                if (!Array.isArray(option_data)) {
                    option_data = [option_data, option_data];
                }

                var line = $('<div />').appendTo(container);

                var radio = $('<input />').attr({type: 'radio', name: config.id + '[]', id: config.id + '__' + index, value: option_data[0]}).click(function () {
                    var data = [];

                    container.find('input[type="radio"]').each(function () {
                        if (this.checked) {
                            data.push(this.value);
                        }
                    });

                    try {
                        config.setter.apply($this, [component, data]);
                    } catch (e) {

                    }
                });

                var radio_wrap = $('<div />').addClass('styled-radio').appendTo(line);

                radio_wrap.append(radio).append($('<ins />'));

                $('<label />').attr('for', config.id + '__' + index).addClass('ml10 label-inline').text(option_data[1]).appendTo(line);

                if (value.indexOf(option_data[0]) >= 0) {
                    radio.attr('checked', 'checked');
                }
            });

            return container.parent();
        };

        this.componentConfigElm_Select = function (component, config) {
            var $this = this;

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            $('<label />').attr('for', config.id).text(config.title).appendTo(container);

            var select = $('<select />').attr({id: config.id}).change(function () {
                try {
                    config.setter.apply($this, [component, this.options[this.selectedIndex].value]);
                } catch (e) {

                }
            });

            $('<div />').addClass('styled-select').append(select).append($('<ins />')).appendTo(container);

            select.bind('updateConfig', function (e, new_config) {
                if (new_config !== null && typeof new_config === 'object') {
                    $.extend(config, new_config);
                }

                if (!Array.isArray(config.options)) {
                    config.options = [];
                }

                var value = '';

                try {
                    value = config.getter.apply($this, [component]) + '';
                } catch (e) {
                }

                select.html('');

                config.options.forEach(function (option_data) {
                    if (!Array.isArray(option_data)) {
                        option_data = [option_data, option_data];
                    }

                    option_data[0] = option_data[0] + '';

                    var option = $('<option />').attr('value', option_data[0]).text(option_data[1]).appendTo(select);

                    if (value === option_data[0]) {
                        option.attr('selected', 'selected');
                    }
                });
            });

            select.trigger('updateConfig');

            return container.parent();
        };

        this.componentConfigElm_Input = function (component, config) {
            var $this = this;

            var value = '';

            try {
                value = config.getter.apply(this, [component]);
            } catch (e) {
            }

            config.id = this._verifyComponentConfigElmId(component, config.id);

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            $('<label />').attr('for', config.id).text(config.title).appendTo(container);

            $('<input />').attr({type: 'text', id: config.id, placeholder: config.placeholder ? config.placeholder : ''}).val(value).addClass('styled-input').appendTo($('<div />').appendTo(container)).blur(function () {
                try {
                    config.setter.apply($this, [component, this.value]);
                } catch (e) {

                }
            });

            return container.parent();
        };

        this.componentConfigElm_Textarea = function (component, config) {
            var $this = this;

            var value = '';

            try {
                value = config.getter.apply(this, [component]);
            } catch (e) {
            }

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            $('<label />').text(config.title).appendTo(container);

            $('<textarea />').attr({placeholder: config.placeholder ? config.placeholder : ''}).val(value).addClass('styled-textarea').appendTo($('<div />').appendTo(container)).blur(function () {
                try {
                    config.setter.apply($this, [component, this.value]);
                } catch (e) {

                }
            });

            return container.parent();
        };

        this.componentConfigElm_ColorPicker = function (component, config) {
            var $this = this;

            var container = $('<div />').appendTo($('<div />').addClass('frm-grid'));

            $('<label />').text(config.title).appendTo(container);

            var input_container = $('<div />').addClass('styled-color').appendTo(container);

            var color = '#fff';

            try {
                color = config.getter.apply(this, [component]);
            } catch (e) {

            }

            $('<input />').attr({type: 'text', value: color}).addClass('styled-input').change(function () {
                var color = this.value.trim();

                if (color.substr(0, 1) !== '#') {
                    color = '#' + color;
                }

                if (!(/^#[a-f0-9]{3,6}$/i).test(this.value)) {
                    color = '#000000';
                } else if (color.length < 7) {
                    var char = color.substr(1, 1);

                    color = '#';

                    for (var i = 1; i <= 6; i++) {
                        color += char;
                    }
                }

                if (this.value !== color) {
                    this.value = color;
                }

                $(this.nextSibling).css('background-color', color);
            }).appendTo(input_container);

            var ins = $('<ins />').appendTo(input_container).css('background-color', color);

            ins.osc_colorPicker({
                divergent_y: 10,
                allow_no_color: false,
                allow_alpha_channel: false,
                callback: function (color) {
                    if (color) {
                        ins.css('background-color', color);

                        if (ins[0].previousSibling.value !== color) {
                            ins[0].previousSibling.value = color;
                        }

                        try {
                            config.setter.apply($this, [component, color]);
                        } catch (e) {

                        }
                    }
                }
            });

            return container.parent();
        };

        this.componentImageSelector_Render = function (component_config) {
            var $this = this;

            var component = $('<div />').addClass('comp-imageSelector').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Image Selector component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('image-selector'));

            if (component_config) {
                component_config.images.forEach(function (image) {
                    $this.componentImageSelector_Setter(component, image.url, image.title);
                });
            }

            return component;
        };

        this.componentImageSelector_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                images: this.componentImageSelector_Getter(component),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentImageSelector_Getter = function (component) {
            var images = [];

            component.find('> .image-selector [data-image]').each(function () {
                images.push({title: this.getAttribute('title'), url: this.getAttribute('data-image')});
            });

            return images;
        };

        this.componentImageSelector_Setter = function (component, file_url, file_name, width, height, config) {
            var values = [];

            this.componentImageSelector_Getter(component).forEach(function (image) {
                values.push(image.title);
            });

            var counter = 0;

            while (values.indexOf(file_name + (counter < 1 ? '' : ' ' + counter)) >= 0) {
                counter++;
            }

            file_name += (counter < 1 ? '' : ' ' + counter);

            var list = component.find('> .image-selector');

            $('<div />').addClass('img-item-preview')
                    .attr('title', file_name)
                    .attr('data-image', file_url)
                    .css('background-image', 'url(' + file_url + ')')
                    .appendTo(list);

            this.componentImageSelector_RenderItemConfig(component, file_name, config);

            return file_name;
        };

        this.componentImageSelector_RenderItemsConfig = function (component, config) {
            var $this = this;

            component.find('> .image-selector [data-image]').each(function () {
                $this.componentImageSelector_RenderItemConfig(component, this.getAttribute('title'), config);
            });
        };

        this.componentImageSelector_RenderItemConfig = function (component, title, config) {
            if (typeof config !== 'object' || config === null) {
                config = {};
            }

            var slide_item = component.find('[title="' + title + '"]');

            var post_frm = this.config_panel.find('.post-frm');

            var container = post_frm.find('.img-list');

            if (!container[0]) {
                container = $('<div />').addClass('img-list').appendTo(post_frm);
            }

            $('<div />').attr('title', title).addClass('img-item-preview img-item-config').css('background-image', 'url(' + slide_item.attr('data-image') + ')').appendTo(container).append($('<span />').click(function () {
                slide_item.remove();
                container.find('[title="' + title + '"]').remove();

                if (typeof config.callback !== 'undefined' && typeof config.callback.remove === 'function') {
                    config.callback.remove.apply($this, [component, title]);
                }
            }));
        };

        this.componentSwitcherByImage_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-switcherByImage');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Switcher by Image component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('image-selector'));

            var scene_container = $('<div />').addClass('scene-list').appendTo(component);

            if (component_config) {
                component_config.scenes.forEach(function (scene_config) {
                    $this.componentSwitcherByImage_ImageSetter(component, scene_config.image.url, scene_config.image.title, 0, 0);
                });

                component_config.scenes.forEach(function (scene_config) {
                    var scene = scene_container.find('> [data-key="' + $.md5(scene_config.image.title) + '"]');

                    scene_config.components.forEach(function (_component_config) {
                        try {
                            scene.append($this.renderComponent(_component_config.component_type, _component_config));
                        } catch (e) {

                        }
                    });
                });
            }

            return component;
        };

        this.componentSwitcherByImage_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0,
                scenes: this.componentSwitcherByImage_SceneGetter(component)
            };
        };

        this.componentSwitcherByImage_ImageSetter = function (component, file_url, file_name, width, height) {
            file_name = this.componentImageSelector_Setter(component, file_url, file_name, width, height, {callback: {remove: this.componentSwitcher_SceneRemove}});

            var options = [];

            this.componentImageSelector_Getter(component).forEach(function (image) {
                options.push(image.title);
            });

            this.componentSwitcher_SceneRender(component, options);
        };

        this.componentSwitcherByImage_SceneGetter = function (component) {
            var $this = this;

            var scenes = [];

            this.componentImageSelector_Getter(component).forEach(function (image) {
                scenes.push({
                    image: image,
                    components: $this._fetchComponentsData(component.find('> .scene-list > .scene[data-key="' + $.md5(image.title) + '"]'))
                });
            });

            return scenes;
        };

        this.componentSwitcherByColor_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-switcherByColor');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Switcher by Color component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('color-list'));

            var scene_container = $('<div />').addClass('scene-list').appendTo(component);

            if (component_config) {
                var colors = [];
                var options = [];

                component_config.scenes.forEach(function (scene_config) {
                    colors.push(scene_config.color);
                    options.push(scene_config.color.title);
                });

                this.componentColorSelector_Setter(component, colors);
                this.componentSwitcher_SceneRender(component, options);

                component_config.scenes.forEach(function (scene_config) {
                    var scene = scene_container.find('> [data-key="' + $.md5(scene_config.color.title) + '"]');

                    scene_config.components.forEach(function (_component_config) {
                        try {
                            scene.append($this.renderComponent(_component_config.component_type, _component_config));
                        } catch (e) {

                        }
                    });
                });
            }

            return component;
        };

        this.componentSwitcherByColor_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0,
                scenes: this.componentSwitcherByColor_SceneGetter(component)
            };
        };

        this.componentSwitcherByColor_SceneGetter = function (component) {
            var $this = this;

            var scenes = [];

            component.find('> .scene-list > .scene').each(function () {
                var item = component.find('> .color-list > .color-item[data-label="' + this.getAttribute('data-label') + '"]');

                scenes.push({
                    color: {title: item.attr('data-label'), hex: item.attr('data-color')},
                    components: $this._fetchComponentsData($(this))
                });
            });

            return scenes;
        };

        this.componentSwitcherByColor_ColorSetter = function (component, colors) {
            this.componentColorSelector_Setter(component, colors);

            var options = [];

            colors.forEach(function (value) {
                options.push(value.title);
            });

            this.componentSwitcher_SceneRender(component, options);
        };

        this.componentSwitcherBySelect_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-switcherBySelect');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Switcher by Select component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('selector').append($('<select />')));

            var scene_container = $('<div />').addClass('scene-list').appendTo(component);

            if (component_config) {
                var options = [];

                component_config.scenes.forEach(function (scene_config) {
                    options.push(scene_config.title);
                });

                this.componentSwitcherBySelect_OptionSetter(component, options);
                this.componentSwitcher_SceneRender(component, options);

                component_config.scenes.forEach(function (scene_config) {
                    var scene = scene_container.find('> [data-key="' + $.md5(scene_config.title) + '"]');

                    scene_config.components.forEach(function (_component_config) {
                        try {
                            scene.append($this.renderComponent(_component_config.component_type, _component_config));
                        } catch (e) {

                        }
                    });
                });
            }

            return component;
        };

        this.componentSwitcherBySelect_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0,
                scenes: this.componentSwitcherBySelect_SceneGetter(component)
            };
        };

        this.componentSwitcherBySelect_SceneGetter = function (component) {
            var $this = this;

            var scenes = [];

            component.find('> .scene-list > .scene').each(function () {
                scenes.push({
                    title: this.getAttribute('data-label'),
                    components: $this._fetchComponentsData($(this))
                });
            });

            return scenes;
        };

        this.componentSwitcherBySelect_OptionSetter = function (component, options) {
            var list = component.find('> .selector select').html('');

            options.forEach(function (value) {
                $('<option />').attr({value: value}).text(value).appendTo(list);
            });

            this.componentSwitcher_SceneRender(component, options);
        };

        this.componentSwitcher_SceneRender = function (component, options) {
            var scene_container = component.find('> .scene-list');

            var keys = [];

            options.forEach(function (value) {
                var key = $.md5(value);

                var scene = scene_container.find('> [data-key="' + key + '"]');

                if (!scene[0]) {
                    scene = $('<div />').addClass('scene').attr('data-key', key).attr('data-pagebuilder-droparea', 1).appendTo(scene_container);
                }

                scene.attr('data-label', value);

                scene_container.append(scene);

                keys.push(key);
            });

            scene_container.find('> .scene').each(function () {
                if (keys.indexOf(this.getAttribute('data-key')) < 0) {
                    console.log(keys);
                    console.log(this.getAttribute('data-key'));
                    $(this).remove();
                }
            });
        };

        this.componentSwitcher_SceneUpdateKey = function (component, old_text, new_text) {
            component.find('> .scene-list > [data-key="' + $.md5(old_text) + '"]').attr('data-key', $.md5(new_text));
        };

        this.componentSwitcher_SceneRemove = function (component, text) {
            component.find('> .scene-list > [data-key="' + $.md5(text) + '"]').remove();
        };

        this.componentImageGroupSelector_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0)
                    .attr('data-layer-key', component_config ? component_config.layer_key : '')
                    .addClass('comp-imageGroupSelector')
                    .attr('data-json', component_config ? JSON.stringify(component_config.groups) : '{}');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Image Group Selector component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('selector').append($('<select />')));

            var img_list = $('<div />').addClass('image-selector').appendTo(component);

            for (var i = 0; i < 4; i++) {
                $('<div />').addClass('img-item-preview').appendTo(img_list);
            }

            return component;
        };

        this.componentImageGroupSelector_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                groups: JSON.parse(component.attr('data-json')),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentImageGroupSelector_UploaderRender = function (component) {
            var $this = this;

            var container = $('<div />').addClass('config-image-group').appendTo(this.config_panel.find('.post-frm'));

            $('<div />').addClass('btn btn-outline btn-primary btn--block').text('Hide all images').click(function () {
                container.toggleClass('hide-image');

                $(this).text(container.hasClass('hide-image') ? 'Show images' : 'Hide images');
            }).appendTo(container);

            container.append($this.componentConfigElm_Line());

            var list = $('<div />').addClass('image-group-list mb15').appendTo(container);

            var __image_renderer = function (group, group_key, img_id, file_url, file_name) {
                var group_data = JSON.parse(component.attr('data-json'));

                if (Array.isArray(group_data[group_key].images)) {
                    group_data[group_key].images = {};
                }

                group_data[group_key].images[img_id] = {title: file_name, url: file_url};

                component.attr('data-json', JSON.stringify(group_data));

                $('<div />').attr('data-image', img_id)
                        .attr('title', file_name)
                        .addClass('img-item-preview img-item-config')
                        .css('background-image', 'url(' + file_url + ')')
                        .appendTo(group.find('.img-list'))
                        .append($('<span />').attr('data-skipdrag', 1).click(function () {
                            var group_data = JSON.parse(component.attr('data-json'));
                            delete group_data[group_key].images[img_id];
                            component.attr('data-json', JSON.stringify(group_data));

                            group.find('[data-image="' + img_id + '"]').remove();
                        }));
            };

            var __group_renderer = function (group_key, title, items) {
                var group_data = JSON.parse(component.attr('data-json'));

                group_data[group_key] = {
                    title: title,
                    images: items
                };

                component.attr('data-json', JSON.stringify(group_data));

                var group = $('<div />').addClass('image-group').attr('data-key', group_key)
                        .bind('reordered', function () {
                            var group_data = JSON.parse(component.attr('data-json'));

                            var new_group_data = {};

                            list.find('> .image-group').each(function () {
                                new_group_data[this.getAttribute('data-key')] = group_data[this.getAttribute('data-key')];
                            });

                            component.attr('data-json', JSON.stringify(new_group_data));
                        })
                        .appendTo(list);

                $('<div />').addClass('group-title mb10').append($('<input />').addClass('styled-input').attr('type', 'text').attr('data-skipdrag', 1).val(title).keydown(function (e) {
                    if (e.keyCode === 13) {
                        e.preventDefault();
                    }
                }).focus(function () {
                    this.select();
                }).blur(function () {
                    var text = this.value.trim();

                    if (!text) {
                        return;
                    }

                    var group_data = JSON.parse(component.attr('data-json'));
                    group_data[group_key].title = text;
                    component.attr('data-json', JSON.stringify(group_data));
                })).append($('<span />').attr('data-skipdrag', 1).addClass('btn btn-icon').append($.renderIcon('trash-alt-regular')).click(function () {
                    var group_data = JSON.parse(component.attr('data-json'));
                    delete group_data[group_key];
                    component.attr('data-json', JSON.stringify(group_data));
                    group.remove();
                })).appendTo(group);

                $('<div />').addClass('img-list mb10').appendTo(group);

                group.append($this.componentConfigElm_ImageUploader(component, {
                    title: 'Upload new image',
                    setter: function (component, file_url, file_name) {
                        __image_renderer(group, group_key, $.makeUniqid(), file_url, file_name);
                    },
                    file_limit: 0
                }));

                group.append($this.componentConfigElm_Line());

                $.each(items, function (img_id, image) {
                    __image_renderer(group, group_key, img_id, image.url, image.title);
                });

                initItemReorder(group, '.image-group-list', '.image-group', 'catalog-customize-image-group-reorder-helper', function (helper) {
                    helper.text(group.find('input').val());
                });
            };

            container.append(this.componentConfigElm_Button(component, {
                title: 'Add new group',
                setter: function () {
                    __group_renderer('A' + $.makeUniqid(), 'Untitled Group', {});
                }
            }));

            var groups = JSON.parse(component.attr('data-json'));

            $.each(groups, function (group_key, group) {
                __group_renderer(group_key, group.title, group.images);
            });

            return container;
        };

        this.componentTextarea_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-textarea');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Textarea component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('content').append($('<textarea />')));

            return component;
        };

        this.componentTextarea_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentInput_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-input');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Input component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('content').append($('<input />')));

            return component;
        };

        this.componentInput_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentChecker_Render = function (component_config) {
            var component = $('<div />').attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-checker');

            $('<label />').append($('<input />').attr({type: 'checkbox', value: ''})).append($('<span />').text(component_config ? component_config.title : 'Checker component')).appendTo(component);

            if (component_config) {
                this.componentChecker_ValueSetter(component, component_config.value);
            }

            return component;
        };

        this.componentChecker_Encode = function (component) {
            return {
                title: component.find('label span').text(),
                layer_key: component.attr('data-layer-key'),
                value: this.componentChecker_ValueGetter(component)
            };
        };

        this.componentChecker_ValueGetter = function (component) {
            return component.find('input').attr('value');
        };

        this.componentChecker_ValueSetter = function (component, value) {
            component.find('input').attr('value', value);
        };

        this.componentColorSelector_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-checkbox');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Color Selector component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('color-list'));

            if (component_config) {
                this.componentColorSelector_Setter(component, component_config.colors);
            }

            return component;
        };

        this.componentColorSelector_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                colors: this.componentColorSelector_Getter(component),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentColorSelector_Getter = function (component) {
            var colors = [];

            component.find('> .color-list > .color-item').each(function () {
                colors.push({title: $(this).attr('data-label'), hex: $(this).attr('data-color')});
            });

            return colors;
        };

        this.componentColorSelector_Setter = function (component, colors) {
            var list = component.find('> .color-list').html('');

            var id = $.makeUniqid();

            colors.forEach(function (color) {
                var RGB = OSC_ColorPicker_Helper.Hex2RGB(color.hex);

                $('<label />').attr('data-color', color.hex).attr('data-label', color.title).css('background-color', color.hex).append($('<input />').attr({type: 'checkbox', name: id + '[]', value: color.title})).append($('<span />').css('color', OSC_ColorPicker_Helper.brightness(RGB.R, RGB.G, RGB.B) < 120 ? '#fff' : '#333').text(color.title)).appendTo(list).addClass('color-item');
            });
        };

        this.componentListItem_Render = function (component_config) {
            var $this = this;

            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-listItem');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'List Item component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('selector').append($('<select />')));

            component.append($('<div />').addClass('container').attr('data-pagebuilder-droparea', 1));

            if (component_config) {
                this.componentListItem_MinSetter(component, component_config.min);
                this.componentListItem_MaxSetter(component, component_config.max);

                component_config.components.forEach(function (_component_config) {
                    try {
                        component.find('> .container').append($this.renderComponent(_component_config.component_type, _component_config));
                    } catch (e) {

                    }
                });
            }

            return component;
        };

        this.componentListItem_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                min: this.componentListItem_MinGetter(component),
                max: this.componentListItem_MaxGetter(component),
                components: this._fetchComponentsData(component.find('> .container')),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentListItem_MinGetter = function (component) {
            return component.find('> .selector select').attr('data-min');
        };

        this.componentListItem_MinSetter = function (component, value) {
            component.find('> .selector select').attr('data-min', value);
        };

        this.componentListItem_MaxGetter = function (component) {
            return component.find('> .selector select').attr('data-max');
        };

        this.componentListItem_MaxSetter = function (component, value) {
            component.find('> .selector select').attr('data-max', value);
        };

        this.componentImageUploader_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0)
                    .attr('data-layer-key', component_config ? component_config.layer_key : '')
                    .attr('data-key', component_config ? component_config.key : $.makeUniqid())
                    .attr('data-min-width', component_config ? component_config.min_width : 0)
                    .attr('data-min-height', component_config ? component_config.min_height : 0)
                    .addClass('comp-imageUploader');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Image Uploader component'))
                    .append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''))
                    .append($('<div />').addClass('uploader-btn'));

            return component;
        };

        this.componentImageUploader_Encode = function (component) {
            return {
                key: component.attr('data-key'),
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                min_width: this.componentImageUploader_MinWidthGetter(component),
                min_height: this.componentImageUploader_MinHeightGetter(component),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentImageUploader_MinWidthSetter = function (component, value) {
            value = parseInt(value);

            if (isNaN(value) || value < 1) {
                value = 1;
            }

            component.attr('data-min-width', value);
        };

        this.componentImageUploader_MinWidthGetter = function (component) {
            return component.attr('data-min-width');
        };

        this.componentImageUploader_MinHeightSetter = function (component, value) {
            value = parseInt(value);

            if (isNaN(value) || value < 1) {
                value = 1;
            }

            component.attr('data-min-height', value);
        };

        this.componentImageUploader_MinHeightGetter = function (component) {
            return component.attr('data-min-height');
        };

        this.componentSelect_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-select');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Select component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('selector').append($('<select />')));

            if (component_config) {
                this.componentSelect_OptionSetter(component, component_config.options);
            }

            return component;
        };

        this.componentSelect_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                options: this.componentSelect_OptionGetter(component),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentSelect_OptionGetter = function (component) {
            var options = [];

            component.find('> .selector option').each(function () {
                options.push($(this).val());
            });

            return options;
        };

        this.componentSelect_OptionSetter = function (component, options) {
            var list = component.find('select').html('');

            options.forEach(function (value) {
                $('<option />').attr({value: value}).text(value).appendTo(list);
            });
        };

        this.componentCheckbox_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-checkbox');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Checkbox component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('option-list'));

            if (component_config) {
                this.componentCheckbox_OptionSetter(component, component_config.options);
            }

            return component;
        };

        this.componentCheckbox_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                options: this.componentCheckbox_OptionGetter(component),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentCheckbox_OptionGetter = function (component) {
            var options = [];

            component.find('.option-item').each(function () {
                options.push($(this).find('input').val());
            });

            return options;
        };

        this.componentCheckbox_OptionSetter = function (component, options) {
            var list = component.find('.option-list').html('');

            var id = $.makeUniqid();

            options.forEach(function (value) {
                $('<label />').append($('<input />').attr({type: 'checkbox', name: id + '[]', value: value})).append($('<span />').text(value)).appendTo($('<div />').addClass('option-item').appendTo(list));
            });
        };

        this.componentRadio_Render = function (component_config) {
            var component = $('<div />').attr('data-require', component_config ? component_config.require : 0).attr('data-layer-key', component_config ? component_config.layer_key : '').addClass('comp-radio');

            component.append($('<div />').addClass('title').text(component_config ? component_config.title : 'Radio component'));
            component.append($('<div />').addClass('desc').text(component_config ? component_config.desc : ''));
            component.append($('<div />').addClass('option-list'));

            if (component_config) {
                this.componentRadio_OptionSetter(component, component_config.options);
            }

            return component;
        };

        this.componentRadio_Encode = function (component) {
            return {
                title: component.find('> .title').text(),
                desc: component.find('> .desc').text(),
                options: this.componentRadio_OptionGetter(component),
                layer_key: component.attr('data-layer-key'),
                require: component.attr('data-require') === '1' ? 1 : 0
            };
        };

        this.componentRadio_OptionGetter = function (component) {
            var options = [];

            component.find('.option-item').each(function () {
                options.push($(this).find('input').val());
            });

            return options;
        };

        this.componentRadio_OptionSetter = function (component, options) {
            var list = component.find('.option-list').html('');

            var id = $.makeUniqid();

            options.forEach(function (value) {
                $('<label />').append($('<input />').attr({type: 'radio', name: id + '[]', value: value})).append($('<span />').text(value)).appendTo($('<div />').addClass('option-item').appendTo(list));
            });
        };

        this.initComponentDragDrop = function (item, render_drag_item) {
            var $this = this;

            var timer = null;

            item.attr('data-dragable', 1).mousedown(function (e) {
                if ($(e.target).closest('[data-pagebuilder-skipdrag]')[0] || $(e.target).closest('[data-dragable]')[0] !== this) {
                    return;
                }

                e.preventDefault();

                clearTimeout(timer);

                var drag_item, helper, dropareas;

                $(document).unbind('.itemDragDrop').bind('mouseup.itemDragDrop', function (e) {
                    clearTimeout(timer);

                    $(document).unbind('.itemDragDrop');
                    $(document.body).removeClass('pagebuilder-dragging');

                    if (drag_item) {
                        helper.remove();
                        drag_item.removeClass('pagebuilder-dragging-item');
                    }
                });

                timer = setTimeout(function () {
                    drag_item = typeof render_drag_item === 'function' ? render_drag_item(item) : item;

                    helper = drag_item.clone().addClass('pagebuilder-dragging-helper')
                            .css({
                                position: 'absolute',
                                zIndex: 99999,
                                width: item.width() + 'px',
                                height: item.height() + 'px',
                                marginLeft: ((item[0].getBoundingClientRect().x + $(window).scrollLeft()) - e.pageX) + 'px',
                                marginTop: ((item[0].getBoundingClientRect().y + $(window).scrollTop()) - e.pageY) + 'px'
                            }).appendTo(document.body);

                    helper.find('.remove-component-btn').remove();

                    drag_item.addClass('pagebuilder-dragging-item');

                    $(document.body).addClass('pagebuilder-dragging');

                    dropareas = $this.scene.findAll('[data-pagebuilder-droparea]');

                    $(document).bind('mousemove.itemDragDrop', function (e) {
                        e.preventDefault();

                        var scroll_top = $(window).scrollTop();
                        var scroll_left = $(window).scrollLeft();

                        helper.css({top: e.pageY + 'px', left: e.pageX + 'px'}).css({});

                        var droparea = null;

                        var cursor_x = e.pageX - scroll_left;
                        var cursor_y = e.pageY - scroll_top;

                        dropareas.each(function () {
                            var rect = this.getBoundingClientRect();

                            if (cursor_x < rect.x || cursor_x > (rect.x + rect.width) || cursor_y < rect.y || cursor_y > (rect.y + rect.height)) {
                                $(this).removeClass('dropable');
                                return;
                            }

                            if (!droparea || (droparea[0].compareDocumentPosition(this) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY) {
                                if (droparea) {
                                    droparea.removeClass('dropable');
                                }

                                droparea = $(this).addClass('dropable');
                            }
                        });

                        if (!droparea) {
                            return;
                        }

                        if (drag_item.closest('[data-pagebuilder-droparea]')[0] !== droparea[0]) {
                            droparea.append(drag_item);
                        }

                        var collection = droparea.find('> *');

                        collection.each(function () {
                            if (this === drag_item[0] || (this.compareDocumentPosition(drag_item[0]) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY) {
                                return;
                            }

                            var rect = this.getBoundingClientRect();

                            var item_top = rect.y + scroll_top;
                            var item_left = rect.x + scroll_left;

                            if (e.pageY < item_top) {
                                return false;
                            }

                            if (e.pageY > item_top && e.pageY < (item_top + rect.height)) {
                                if (e.pageX > item_left && e.pageX < (item_left + rect.width)) {
                                    if (this.previousSibling && (this.previousSibling === drag_item[0] || (this.previousSibling.compareDocumentPosition(drag_item[0]) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY)) {
                                        drag_item.insertAfter(this);
                                    } else {
                                        drag_item.insertBefore(this);
                                    }
                                }
                            }
                        });
                    });
                }, 150);
            });
        };

        this.getContent = function () {
            var content = this._fetchComponentsData(this.scene);

            if (content.length < 1) {
                throw "Please add least a component";
            }

            return JSON.stringify(content);
        };

        this._fetchComponentsData = function (container) {
            var $this = this;

            var data = [];

            container.find('> [data-component]').each(function () {
                var component_data = $this._fetchComponentData($(this));

                if (component_data) {
                    data.push(component_data);
                }
            });

            return data;
        };

        this._fetchComponentData = function (component) {
            var component_type = component.attr('data-component');

            if (!component_type) {
                return null;
            }

            var callback = 'component' + component_type.substr(0, 1).toUpperCase() + component_type.substr(1) + '_Encode';

            try {
                var component_data = $this[callback].apply($this, [component]);

                if (component_data && typeof component_data === 'object') {
                    component_data.component_type = component_type;
                    return component_data;
                }
            } catch (e) {

            }

            return null;
        };

        this.setContent = function (components) {
            try {
                components = JSON.parse(components);
            } catch (e) {
                return;
            }

            if (!Array.isArray(components) || components.length < 1) {
                return;
            }

            var $this = this;
            this.scene.html('');

            components.forEach(function (component) {
                $this.renderComponent(component.component_type, component);
            });
        };

        this.component_panel_data = [
            {
                group: 'Basic components',
                components: [
                    {type: 'textarea', title: 'Textarea', icon: 'textbox'},
                    {type: 'input', title: 'Input', icon: 'textbox'},
                    {type: 'select', title: 'Select', icon: 'textbox'},
                    {type: 'radio', title: 'Radio', icon: 'textbox'},
                    {type: 'checkbox', title: 'Checkbox', icon: 'textbox'},
                    {type: 'checker', title: 'Checker', icon: 'textbox'}
                ]
            },
            {
                group: 'Special components',
                components: [
                    {type: 'imageSelector', title: 'Image Selector', icon: 'grid-vertical'},
                    {type: 'imageGroupSelector', title: 'Image Group Selector', icon: 'grid-vertical'},
                    {type: 'imageUploader', title: 'Image Uploader', icon: 'grid-vertical'},
                    {type: 'colorSelector', title: 'Color Selector', icon: 'grid-vertical'},
                    'Others',
                    {type: 'listItem', title: 'List Item', icon: 'grid-vertical'},
                    {type: 'switcherBySelect', title: 'Switcher', icon: 'grid-vertical'},
                    {type: 'switcherByColor', title: 'Color Switcher', icon: 'grid-vertical'},
                    {type: 'switcherByImage', title: 'Image Switcher', icon: 'grid-vertical'}
                ]
            }
        ];

        this.components = {
            scene: {
                name: 'Main container',
                config_frm: []
            },
            listItem: {
                name: 'List Item',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'line'},
                    {
                        type: 'input',
                        title: 'Min number',
                        getter: this.componentListItem_MinGetter,
                        setter: this.componentListItem_MinSetter
                    },
                    {
                        type: 'input',
                        title: 'Max number',
                        getter: this.componentListItem_MaxGetter,
                        setter: this.componentListItem_MaxSetter
                    },
                    {type: 'line'},
                    {type: 'checker', title: 'Require'}
                ]
            },
            textarea: {
                name: 'Textarea',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'}
                ]
            },
            input: {
                name: 'Input',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'}
                ]
            },
            checker: {
                name: 'Checker',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: 'span'},
                    {type: 'layerKey'},
                    {type: 'line'},
                    {
                        type: 'input',
                        title: 'Value',
                        getter: this.componentChecker_ValueGetter,
                        setter: this.componentChecker_ValueSetter
                    }
                ]
            },
            checkbox: {
                name: 'Checkbox',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'optionList',
                        title: 'Options',
                        getter: this.componentCheckbox_OptionGetter,
                        setter: this.componentCheckbox_OptionSetter
                    }
                ]
            },
            radio: {
                name: 'Radio',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'optionList',
                        title: 'Options',
                        getter: this.componentRadio_OptionGetter,
                        setter: this.componentRadio_OptionSetter
                    }
                ]
            },
            select: {
                name: 'Select',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'optionList',
                        title: 'Options',
                        getter: this.componentSelect_OptionGetter,
                        setter: this.componentSelect_OptionSetter
                    }
                ]
            },
            imageUploader: {
                name: 'Image uploader',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'input', title: 'Min width', setter: this.componentImageUploader_MinWidthSetter, getter: this.componentImageUploader_MinWidthGetter},
                    {type: 'input', title: 'Min height', setter: this.componentImageUploader_MinHeightSetter, getter: this.componentImageUploader_MinHeightGetter},
                    {type: 'checker', title: 'Require'}
                ]
            },
            imageSelector: {
                name: 'Image selector',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'imageUploader',
                        title: 'Upload new image',
                        setter: this.componentImageSelector_Setter,
                        file_limit: 0
                    },
                    {type: 'line'},
                    {type: this.componentImageSelector_RenderItemsConfig}
                ]
            },
            imageGroupSelector: {
                name: 'Image Group selector',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {type: this.componentImageGroupSelector_UploaderRender}
                ]
            },
            switcherBySelect: {
                name: 'Switcher by Select',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'optionList',
                        title: 'Options',
                        getter: this.componentSelect_OptionGetter,
                        setter: this.componentSwitcherBySelect_OptionSetter,
                        update_callback: this.componentSwitcher_SceneUpdateKey
                    }
                ]
            },
            switcherByColor: {
                name: 'Switcher by Color',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'colorList',
                        title: 'Colors',
                        getter: this.componentColorSelector_Getter,
                        setter: this.componentSwitcherByColor_ColorSetter,
                        update_callback: this.componentSwitcher_SceneUpdateKey
                    }
                ]
            },
            switcherByImage: {
                name: 'Switcher by Image',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'imageUploader',
                        title: 'Upload new image',
                        setter: this.componentSwitcherByImage_ImageSetter,
                        callback: {
                            remove: this.componentSwitcher_SceneUpdateKey
                        },
                        file_limit: 0
                    },
                    {type: 'line'},
                    {type: this.componentImageSelector_RenderItemsConfig, callback: {remove: this.componentSwitcher_SceneRemove}}
                ]
            },
            colorSelector: {
                name: 'Color selector',
                config_frm: [
                    {type: 'textContent', title: 'Label', selector: '> .title'},
                    {type: 'textContent', input: 'textarea', title: 'Description', selector: '> .desc'},
                    {type: 'layerKey'},
                    {type: 'checker', title: 'Require'},
                    {type: 'line'},
                    {
                        type: 'colorList',
                        title: 'Colors',
                        getter: this.componentColorSelector_Getter,
                        setter: this.componentColorSelector_Setter
                    }
                ]
            }
        };

        var $this = this;

        this.container = container;

        this.container.html('');

        this.container.addClass('page-builder show-blocks');

        this.renderComponentPanel();

        this.scene = $('<div />').addClass('page-scene post-frm').attr('data-pagebuilder-droparea', 1).appendTo($('<div />').addClass('page-scene-wrap').appendTo(this.container));
        this.scene.click(function (e) {
            $this.componentClick(e, $this.scene);
        });
        this.questions = null;
        this.config_panel = $('<div />').addClass('config-panel').appendTo(this.container);

        var control_panel = $('<div />').addClass('control-panel').appendTo(this.container);

        $('<div />').append($('<input />').attr({id: 'visual-aid-switch', type: 'checkbox', checked: 'checked'}).click(function () {
            $this.container[this.checked ? 'addClass' : 'removeClass']('show-blocks');
        })).append($('<label />').attr('for', 'visual-aid-switch').text('Show blocks')).appendTo(control_panel);

        $('<div />').append($.renderIcon('desktop-light')).append($('<span />').text('Duplicate').click(function () {
            var selected_component = $this.scene.find('.component-selected');

            if (!selected_component[0] || selected_component.hasClass('page-scene')) {
                return;
            }

            var component_data = $this._fetchComponentData(selected_component);

            if (component_data) {
                var component = $this.renderComponent(component_data.component_type, component_data);

                if (component) {
                    component.insertAfter(selected_component);
                }
            }
        })).appendTo(control_panel);

        _initCustomInsertCb('.comp-gridHorizontal > :not(.grid-item):not(.remove-component-btn), .comp-gridVertical > :not(.grid-item):not(.remove-component-btn)', function () {
            $('<div />').addClass('grid-item').insertBefore(this).append(this);
        }, true);

        _initCustomInsertCb('.comp-gridHorizontal > .grid-item:empty, .comp-gridVertical > .grid-item:empty', function () {
            $(this).remove();
        }, true);

        _initCustomInsertCb('.init-editor', function () {
            $this.initEditor($(this));
        });

        var frm = this.container.closest('form');

        var input = frm.find('input[name="config"]');

        this.setContent(input.val());

        frm.submit(function (e) {
            try {
                input.val($this.getContent());
            } catch (ex) {
                alert(ex);
                e.preventDefault();
            }
        });
    }

    window.customizeItemBuilderInit = function () {
        new PageBuilder($(this));
    };
})(jQuery);