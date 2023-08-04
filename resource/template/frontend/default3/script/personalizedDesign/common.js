(function ($) {
    'use strict';

    var _CONFIG_CACHE = localStorage.getItem('personalizedDesignConfig');

    if (typeof _CONFIG_CACHE === 'string') {
        try {
            _CONFIG_CACHE = JSON.parse(_CONFIG_CACHE);
        } catch (e) {
        }
    }

    if (typeof _CONFIG_CACHE !== 'object' || _CONFIG_CACHE === null) {
        _CONFIG_CACHE = {};
    }

    function _updateConfigCache(design_id, config_key, config_value) {
        if (typeof _CONFIG_CACHE[design_id] !== 'object' || _CONFIG_CACHE[design_id] === 'null') {
            _CONFIG_CACHE[design_id] = {};
        }

        if (config_value === '') {
            delete _CONFIG_CACHE[design_id][config_key];
        } else {
            _CONFIG_CACHE[design_id][config_key] = config_value;
        }

        localStorage.setItem('personalizedDesignConfig', JSON.stringify(_CONFIG_CACHE));
    }

    function _tranformSubwordLowercase(){
        const value = this.value
        if(!value) {
            return ""
        }
        //transform to lowercase subword 
        const words = value.split(" ").map(word => {
            const firstLetter = word.charAt(0)
            const others = word.substring(1).toLowerCase()
            return `${firstLetter}${others}`
        })
        const newValue = words.join(" ") 
        return newValue
    }

    function _showError(component, message){
        const icon = $.renderIcon('icon-danger');
        const messageElm = $("<span />").text(message);
        $('<div />').addClass('error-msg').append(icon).append(messageElm).appendTo(component)
    }

    function _removeError(component) {
        component.find('> .error-msg').remove();
    }

    // function _renderImageSelectorShowMore(image_list, selectedIndex, index){
    //     if(!window.isProductDetailV2) return;

    //     const isMobile = window.innerWidth <= 768;
    //     image_list.css("height", "auto");
    //     if(isMobile && (index <= 12 || selectedIndex >= 8)) return
    //     else if(!isMobile && (index <= 15 || selectedIndex >= 10)) return;

    //     const firstOption = image_list.find('label')[0];
    //     const optionSize = firstOption.getBoundingClientRect();
    //     const listHeight = optionSize.height * 3 + 20;
    //     image_list.css("height", listHeight);
    //     const showAllBtn = $('<div />').text("Show All").append($.renderIcon('icon-arrow')).addClass("show-more").css('height', optionSize.height).appendTo(image_list) ;
    //     showAllBtn.click(function(){
    //         image_list.css("height", "auto")
    //         showAllBtn.remove()
    //     })
    // }

    class DesignConfigRenderer {
        constructor(design, options) {
            if (typeof options !== 'object' || options === null) {
                options = {};
            }

            this.design = design;
            this.scene = null;
            this.popup = null;

            this.product_title = (typeof options.product_title === 'string' ? options.product_title : '').toLowerCase().replace(/[^a-zA-Z0-9]/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/g, '');
            this.linking_container = typeof options.linking_container === 'object' && options.linking_container.tagName ? $(options.linking_container) : null;
            this.frm_name_prefix = typeof options.frm_name_prefix === 'string' ? options.frm_name_prefix : 'personalized[' + this.design_id + ']';

            if (typeof options.config === 'object' && options.config !== null) {
                this.config = options.config;
                this.using_cache = false;
            } else {
                if (typeof _CONFIG_CACHE[design.id] === 'undefined') {
                    _CONFIG_CACHE[design.id] = {};
                }

                this.config = _CONFIG_CACHE[design.id];
                this.using_cache = true;
            }
        }

        updateConfig(config_key, config_value, skip_trigger_event) {
            if (this.using_cache) {
                _updateConfigCache(this.design.id, config_key, config_value);
            } else {
                if (config_value === null || config_value === '') {
                    delete this.config[config_key];
                } else {
                    this.config[config_key] = config_value;
                }
            }

            if (!skip_trigger_event) {
                $(this.scene).trigger('personalized-config-update');
            }
        }

        render(scene) {
            this.scene = scene;
            this.scene.html('');

            if (this.linking_container) {
                this.linking_container.unbind('.linking_' + this.design.id);
            }

            this._renderComponents(scene, this.design.components);
        }

        validate() {
            var $this = this;

            if (typeof this.scene === 'undefined' || this.scene === null) {
                return true;
            }

            var error_flag = false;

            this.scene.find('[data-component]').each(function () {
                var component = $(this);
                var component_type = component.attr('data-component');

                var validator = '_' + component_type + 'Validator';

                if (typeof $this[validator] === 'function' && $this[validator](component) === false) {
                    error_flag = true;
                }
            });

            return !error_flag;
        }

        _matchingQuoteByProductTitle(options) {
            var matched = null;

            var $this = this;

            $.each(options, function (option_idx, option) {
                option = option.toLowerCase().replace(/[^a-zA-Z0-9]/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/g, '');

                if ($this.product_title.indexOf(option) < 0) {
                    return;
                }

                var ratio = option.length / $this.product_title.length;

                if (matched === null || ratio > matched.ratio) {
                    matched = {ratio: ratio, idx: option_idx};
                }
            });

            return matched;
        }

        _renderComponents(container, components) {
            var $this = this;

            $.each(components, function (config_key, component) {
                var renderer = '_' + component.component_type + 'Render';

                if (typeof $this[renderer] !== 'function') {
                    return;
                }

                $this[renderer](container, {
                    config_key: config_key,
                    frm_name: $this.frm_name_prefix + '[' + config_key + ']',
                    component: component
                });
            });
        }

        _renderComponentFrame(container, params) {
            var component = $('<div />').addClass('personalized-config-comp').attr({
                'data-component': params.component.component_type,
                'data-key': params.config_key,
                'data-require': params.component.require ? 1 : 0
            }).appendTo(container);

            var title = $('<div />').addClass('personalized-config-comp__title').text(params.component.title).appendTo(component);

            if (params.component.require) {
                $('<span />').addClass('require').text(' *').appendTo(title);
            }

            if (typeof params.component.description !== 'undefined' && params.component.description) {
                $('<div />').addClass('personalized-config-comp__desc').html(params.component.description.replace(/\\n/g, '<br />')).appendTo(component);
            }

            var content = $('<div />').addClass('personalized-config-comp__content').appendTo(component);

            return {
                component: component,
                content: content
            };
        }

        _checkerRender(container, params) {
            var $this = this;

            var checked = this.config[params.config_key] ? parseInt(this.config[params.config_key]) : ((typeof params.component.default_value !== 'undefined' && params.component.default_value) ? 1 : 0);

            var comp = $('<div />').addClass('personalized-config-comp').attr({'data-component': 'checker'}).appendTo(container);
            var content = $('<div />').addClass('personalized-config-comp__content').appendTo(comp);
            var label = $('<label />').appendTo(content);
            var input = $('<input />').attr({type: 'hidden', name: params.frm_name, value: checked ? 1 : 0}).appendTo(label);
            $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label)).click(function () {
                input.val(this.checked ? 1 : 0);
                $this.updateConfig(params.config_key, this.checked ? 1 : 0);
            })[0].checked = checked;

            $('<span />').text(params.component.title).appendTo(label);
        }

        _inputRender(container, params) {
            var $this = this;

            var frame = this._renderComponentFrame(container, params);
            const maxLength = params.component.max_length || 10;
            const input = $('<input />')
            input.attr({
                placeholder: 'Enter',
                type: 'text',
                name: params.frm_name,
                maxlength: maxLength,
                minlength: params.component.min_length || undefined,
                'data-min-len': params.component.min_length,
                'data-max-len': maxLength,
                value: this.config[params.config_key] ? this.config[params.config_key] : (params.component.input_display_default_text ? params.component.default_text : ''),
                'data-config': params.config_key,
                'class': 'input_common input_common--medium',
            }).blur(function () {
                $this._inputValidator(frame.component);
                $this.updateConfig(params.config_key, this.value);
            }).appendTo(frame.content);
            let __updateHelper;

            if(maxLength) {
                const maxLengthHelper = $('<div />').addClass("max_length_helper").appendTo(frame.content)
                __updateHelper = () => {
                    const text = input.val() || '';
                    maxLengthHelper.text(`${text.length}/${maxLength}`);
                }
                __updateHelper()
            }

            if(params.component.input_disable_all_uppercase === 1) {
                input.val(_tranformSubwordLowercase)
            }

            input.on("input", function(){
                if(params.component.input_disable_all_uppercase === 1)
                    $(this).val(_tranformSubwordLowercase)

                if(__updateHelper) __updateHelper();
            })       
        }

        _degress2Radian(degrees) {
            return degrees * (Math.PI / 180);
        }

        _spotifyRender(container, params) {
            const TOKEN = params.component.access_token ||
               'BQDTKoL-RGicCaBHG_2PSaqJYyaN3CycuMAQBA1PkRJn4KKVqA_Eki7N4VLYP1sKr1abou-aECRG9VZyvNy4d8wvrWTw5cGhCjhUF6xecZrf4E62iASrsOt3f6bqIxgMjquCgmJuZC9XqHZpWAYNzjusQEZ4cp2m70nPI8i2xOic0k2zZurkdEM8IA5lsgl65h42_pne-TA5a6AXaOYsWATWTOFMURnGOqsYVMDEgfglv-PYcfRSsa3DfYj61m9n_olee1OuU3ABMI9IdsdEp4VnErNHbHKL7HND-RE_DzUN';
            
            let selectedSong =  this.config[params.config_key] ? JSON.parse(this.config[params.config_key]) : null;

            const renderRow = (dropdown, song, spotifyContainer) => {
                const row = $('<div>').addClass('d-flex align-items-center p-2 song-item').appendTo(dropdown);
                $('<img>').addClass('mr-2').attr('src', song.image).appendTo(row);
                const info = $('<div>').css('width', '90%').appendTo(row);
                $('<div>').addClass('truncate').text(song.name).appendTo(info);
                $('<div>').text(song.artist).appendTo(info);
                row.click(function(){
                    selectedSong = song;
                    renderSpotify({spotifyContainer})
                })
            }

            const debounce = (func, wait, immediate) => {
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
            let spotifyContainer;
            const renderSpotify = ({spotifyContainer, isEdit = false, clickOutsideCb = false}) => {
                spotifyContainer.empty();
                const config = $('<div>').addClass('personalized-config-comp').appendTo(spotifyContainer);
                const container = $('<div>').css('position', 'relative').appendTo(config);
                let input = null;
                const selected = $('<div>').addClass(`selected-song px-2 ${(selectedSong && !isEdit) ? 'd-flex' : 'd-none'}`).appendTo(container);
                if(selectedSong) {
                    const firstDiv = $('<div>').addClass('d-flex align-items-center').appendTo(selected);
                    $('<img>').addClass('mr-2').attr('src', selectedSong.image).appendTo(firstDiv);
                    const info = $('<div>').addClass('py-2').appendTo(firstDiv);
                    $('<div>').addClass('truncate').text(selectedSong.name).appendTo(info);
                    $('<span>').text(selectedSong.artist).appendTo(info);
                    const edit = $('<div>').addClass('edit-selected-song').text('Edit').appendTo(selected);
                    edit.click(function(){
                        renderSpotify({spotifyContainer, isEdit: true});
                    })
                }

                input = $('<input>')
                    .addClass(`pl-2 ${(selectedSong && !isEdit) ? 'd-none' : 'd-block'}`)
                    .attr('placeholder', 'Search your song')
                    .css('width', '100%')
                    .appendTo(container);
                isEdit && input.val(selectedSong.name)

                const dropdown = $('<div>').addClass('spotify-dropdown').appendTo(container)
                isEdit && fetchSong(selectedSong.name, dropdown, spotifyContainer);
                input && input.on('input', debounce(function(){
                    fetchSong($(this).val(), dropdown, spotifyContainer)
                }, 500))
                clickOutsideCb && $(document).click(function(e) {
                    if ($(e.target).closest(".spotify-container").length == 0 && !$(e.target).hasClass("edit-selected-song")) {
                        $('.spotify-container').find('.spotify-dropdown').hide();
                        selectedSong && renderSpotify({spotifyContainer, clickOutside: true})
                    }
                });
                $('<input/>').addClass('d-none').attr('name', params.frm_name).val(JSON.stringify(selectedSong)).appendTo(spotifyContainer);
            }
            
            function fetchSong(searchTxt, dropdown, spotifyContainer){
                $.ajax({
                    url: 'https://api.spotify.com/v1/search',
                    type: 'get',
                    data: {
                        q: searchTxt,
                        type: 'track',
                        limit: 50
                    },
                    headers: {"Authorization": `Bearer ${TOKEN}`},
                    success: function (response) {
                        if (typeof response.tracks !== "undefined" && typeof response.tracks.items !== "undefined") {
                            const searchData = response.tracks.items.map((i) => ({
                                name: i.name,
                                image: typeof i.album.images[0].url !== "undefined" ? i.album.images[0].url : null,
                                artist: i.artists.map((a) => a.name).join(', '),
                                mp3: i.preview_url,
                                uri: i.uri,
                            }));
                            dropdown.empty();
                            if (searchData.length) {
                                searchData.map(song => renderRow(dropdown, song, spotifyContainer))
                                dropdown.show();
                            } else {
                                dropdown.empty();
                                dropdown.hide();
                            }
                        }
                    },
                    error: function (response) {
                        dropdown.empty();
                        dropdown.hide();
                    }
                });
            }
            var frame = this._renderComponentFrame(container, params);

             spotifyContainer = $('<div>').addClass('spotify-container').appendTo(frame.content)
            renderSpotify({ spotifyContainer, clickOutsideCb: true });
        }

        _radian2Degress(radian) {
            return radian * (180 / Math.PI);
        }

        _pointGetDistance(p1, p2) {
            return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
        }

        _getVectorIntersectionPoint(vector1, vector2) {
            var denominator, a, b, numerator1, numerator2, result = {
                x: null,
                y: null,
                onLine1: false,
                onLine2: false
            };

            denominator = ((vector2.point2.y - vector2.point1.y) * (vector1.point2.x - vector1.point1.x)) - ((vector2.point2.x - vector2.point1.x) * (vector1.point2.y - vector1.point1.y));

            if (denominator === 0) {
                return result;
            }

            a = vector1.point1.y - vector2.point1.y;
            b = vector1.point1.x - vector2.point1.x;

            numerator1 = ((vector2.point2.x - vector2.point1.x) * a) - ((vector2.point2.y - vector2.point1.y) * b);
            numerator2 = ((vector1.point2.x - vector1.point1.x) * a) - ((vector1.point2.y - vector1.point1.y) * b);

            a = numerator1 / denominator;
            b = numerator2 / denominator;

            result.x = vector1.point1.x + (a * (vector1.point2.x - vector1.point1.x));
            result.y = vector1.point1.y + (a * (vector1.point2.y - vector1.point1.y));

            if (a > 0 && a < 1) {
                result.onLine1 = true;
            }

            if (b > 0 && b < 1) {
                result.onLine2 = true;
            }

            return result;
        }

        _getPointExceptRotation(point, bbox, rotation) {
            if (typeof rotation === 'undefined') {
                rotation = 0;
            } else {
                rotation = parseFloat(rotation);

                if (isNaN(rotation)) {
                    rotation = 0;
                }
            }

            if (rotation !== 0) {
                var center_pt = {x: 0, y: 0};

                center_pt.x = bbox.x + bbox.width / 2;
                center_pt.y = bbox.y + bbox.height / 2;

                var degress = this._radian2Degress(Math.atan2(point.y - center_pt.y, point.x - center_pt.x));

                degress -= rotation;

                var distance = this._pointGetDistance(center_pt, point);

                var radian = this._degress2Radian(degress);

                point.x = center_pt.x + distance * Math.cos(radian);
                point.y = center_pt.y + distance * Math.sin(radian);
            }

            return point;
        }

        _getPointApplyRotation(point, bbox, rotation) {
            if (typeof rotation === 'undefined') {
                rotation = 0;
            } else {
                rotation = parseFloat(rotation);

                if (isNaN(rotation)) {
                    rotation = 0;
                }
            }

            if (rotation !== 0) {
                var center_pt = {x: 0, y: 0};

                center_pt.x = bbox.x + bbox.width / 2;
                center_pt.y = bbox.y + bbox.height / 2;

                var degress = Math.atan2(point.y - center_pt.y, point.x - center_pt.x) * 180 / Math.PI;

                degress += rotation;

                var distance = this._pointGetDistance(center_pt, point);

                var radian = degress * Math.PI / 180;

                point.x = center_pt.x + distance * Math.cos(radian);
                point.y = center_pt.y + distance * Math.sin(radian);
            }

            return point;
        }

        _calculateViewportByRotation(old_rotation, new_rotation, coords, element_width, element_height) {
            var box_size = Math.max(element_width, element_height);

            var area_width = coords.x2 - coords.x1;
            var area_height = coords.y2 - coords.y1;

            var center_point = {
                x: coords.x1 + (area_width / 2) + (box_size > element_width ? (box_size - element_width) / 2 : 0),
                y: coords.y1 + (area_height / 2) + (box_size > element_height ? (box_size - element_height) / 2 : 0)
            };

            center_point = this._getPointExceptRotation(center_point, {x: 0, y: 0, width: box_size, height: box_size}, this._degress2Radian(old_rotation));

            var max_width = element_width;
            var max_height = element_height;

            if (new_rotation) {
                center_point = this._getPointExceptRotation(center_point, {x: 0, y: 0, width: box_size, height: box_size}, this._degress2Radian(new_rotation));

                if ([90, 270].indexOf(new_rotation) >= 0) {
                    var buff = max_width;
                    max_width = max_height;
                    max_height = buff;
                }

                center_point.x -= box_size > max_width ? (box_size - max_width) / 2 : 0;
                center_point.y -= box_size > max_height ? (box_size - max_height) / 2 : 0;
            }

            if (area_width > max_width || area_height > max_height) {
                var scale = Math.min(max_width / area_width, max_height / area_height);

                area_width *= scale;
                area_height *= scale;
            }

            var new_coords = {
                x1: center_point.x - area_width / 2,
                x2: center_point.x + area_width / 2,
                y1: center_point.y - area_height / 2,
                y2: center_point.y + area_height / 2
            };

            if (new_coords.x1 < 0) {
                new_coords.x2 += Math.abs(new_coords.x1);
                new_coords.x1 = 0;
            }

            if (new_coords.x2 > max_width) {
                new_coords.x1 -= (new_coords.x2 - max_width);
                new_coords.x2 = max_width;
            }

            if (new_coords.y1 < 0) {
                new_coords.y2 += Math.abs(new_coords.y1);
                new_coords.y1 = 0;
            }

            if (new_coords.y2 > max_height) {
                new_coords.y1 -= (new_coords.y2 - max_height);
                new_coords.y2 = max_height;
            }

            return new_coords;
        }

        _imageUploaderEditPanelRenderBody(scene, comp_params, data, apply_callback, remove_callback, new_data_extend) {
            var $this = this, main_scale = 1, new_data = {coords: {}, rotation: data.rotation, effect_config: data.effect_config}, _data = JSON.parse(JSON.stringify(data)), cropper_width = 0, cropper_height = 0;

            scene.html('');

            var crop_panel = $('<div />').addClass('crop-panel img-cropper').appendTo(scene);
            var cropper_area = $('<div />').addClass('cropper-area').appendTo(crop_panel);
            var img = $('<img />').addClass('cropper-preview').attr('src', _data.url);

            var panel_area = $('<div />').addClass('panel-area').appendTo(scene);

            var action_panel = $('<div />').addClass('action-panel').appendTo(panel_area);

            $('<div />').addClass('action-btn').append($.renderIcon('personalized-img-reset')).append($('<div />').text('Reset')).appendTo(action_panel).click(function () {
                $this._imageUploaderEditPanelRenderBody(scene, comp_params, data, apply_callback, remove_callback);
            });

            $('<div />').addClass('action-btn').append($.renderIcon('personalized-img-remove')).append($('<div />').text('Remove')).appendTo(action_panel).click(function () {
                remove_callback();
                $.unwrapContent('personalizeConfigFrm_imageUploaderEditPanel');
            });

            $('<div />').addClass('separate').appendTo(action_panel);

            $('<div />').addClass('action-btn').append($.renderIcon('personalized-img-undo')).append($('<div />').text('-90')).appendTo(action_panel).click(function () {
                if (!new_data.rotation) {
                    new_data.rotation = 0;
                }

                var old_rotation = new_data.rotation;

                new_data.rotation -= 90;

                if (new_data.rotation < 0) {
                    new_data.rotation += 360;
                }

                if (new_data.rotation === 360 || new_data.rotation === 0) {
                    new_data.rotation = null;
                }

                new_data.coords.x1 *= main_scale;
                new_data.coords.x2 *= main_scale;
                new_data.coords.y1 *= main_scale;
                new_data.coords.y2 *= main_scale;

                var new_coords = $this._calculateViewportByRotation(old_rotation, new_data.rotation, new_data.coords, data.width > data.height ? Math.max(cropper_width, cropper_height) : Math.min(cropper_width, cropper_height), data.width > data.height ? Math.min(cropper_width, cropper_height) : Math.max(cropper_width, cropper_height));

                $.extend(new_data.coords, new_coords);

                new_data.coords.x1 /= main_scale;
                new_data.coords.x2 /= main_scale;
                new_data.coords.y1 /= main_scale;
                new_data.coords.y2 /= main_scale;

                $this._imageUploaderEditPanelRenderBody(scene, comp_params, data, apply_callback, remove_callback, new_data);
            });

            $('<div />').addClass('action-btn').append($.renderIcon('personalized-img-redo')).append($('<div />').text('+90')).appendTo(action_panel).click(function () {
                if (!new_data.rotation) {
                    new_data.rotation = 0;
                }

                var old_rotation = new_data.rotation;

                new_data.rotation += 90;

                if (new_data.rotation === 360 || new_data.rotation === 0) {
                    new_data.rotation = null;
                }

                var new_coords = $this._calculateViewportByRotation(old_rotation, new_data.rotation, new_data.coords, data.width, data.height);

                $.extend(new_data.coords, new_coords);

                $this._imageUploaderEditPanelRenderBody(scene, comp_params, data, apply_callback, remove_callback, new_data);
            });

            var effect_panel = $('<div />').addClass('effect-panel').appendTo(panel_area);

            var effect_list = $('<div />').addClass('effect-list').appendTo(effect_panel);

            $('<div />').append($('<div />').addClass('thumb').css('background-image', 'url(' + _data.url + ')')).append($('<div />').addClass('label').text('Original')).appendTo(effect_list).click(function () {
                img.removeAttr('data-personalized-design-filter');
                effect_list.find('> div').removeClass('active');
                $(this).addClass('active');

                new_data.effect_config = null;
            });

            $('<div />').attr('data-effect', 'bw').append($('<div />').addClass('thumb').attr('data-personalized-design-filter', 'bw').css('background-image', 'url(' + _data.url + ')')).append($('<div />').addClass('label').text('B & W')).appendTo(effect_list).click(function () {
                img.attr('data-personalized-design-filter', 'bw');
                effect_list.find('> div').removeClass('active');
                $(this).addClass('active');

                new_data.effect_config = {type: 'bw'};
            });

            $('<div />').attr('data-effect', 'sepia').append($('<div />').addClass('thumb').attr('data-personalized-design-filter', 'sepia').css('background-image', 'url(' + _data.url + ')')).append($('<div />').addClass('label').text('Sepia')).appendTo(effect_list).click(function () {
                img.attr('data-personalized-design-filter', 'sepia');
                effect_list.find('> div').removeClass('active');
                $(this).addClass('active');

                new_data.effect_config = {type: 'sepia'};
            });

            $('<div />').attr('data-effect', 'saturate').append($('<div />').addClass('thumb').attr('data-personalized-design-filter', 'saturate').css('background-image', 'url(' + _data.url + ')')).append($('<div />').addClass('label').text('Saturate')).appendTo(effect_list).click(function () {
                img.attr('data-personalized-design-filter', 'saturate');
                effect_list.find('> div').removeClass('active');
                $(this).addClass('active');

                new_data.effect_config = {type: 'saturate'};
            });

            $('<div />').attr('data-effect', 'contrast').append($('<div />').addClass('thumb').attr('data-personalized-design-filter', 'contrast').css('background-image', 'url(' + _data.url + ')')).append($('<div />').addClass('label').text('Contrast')).appendTo(effect_list).click(function () {
                img.attr('data-personalized-design-filter', 'contrast');
                effect_list.find('> div').removeClass('active');
                $(this).addClass('active');

                new_data.effect_config = {type: 'contrast'};
            });

            var bottom_bar = $('<div />').addClass('bottom-bar').appendTo(scene);

            $('<div />').addClass('btn btn-outline mr10').text('Cancel').click(function () {
                $.unwrapContent('personalizeConfigFrm_imageUploaderEditPanel');
            }).appendTo(bottom_bar);

            $('<div />').addClass('btn btn-primary').text('Apply').click(function () {
                $.unwrapContent('personalizeConfigFrm_imageUploaderEditPanel');
                apply_callback(new_data);
            }).appendTo(bottom_bar);

            var cropper_area_max_size = cropper_area[0].getBoundingClientRect().width;

            cropper_area.width(cropper_area_max_size).height(cropper_area_max_size);

            main_scale = cropper_area_max_size / Math.max(_data.width, _data.height);

            img.appendTo(cropper_area);

            cropper_width = _data.width * main_scale;
            cropper_height = _data.height * main_scale;

            img.css({
                top: ((cropper_area_max_size - cropper_height) / 2) + 'px',
                left: ((cropper_area_max_size - cropper_width) / 2) + 'px',
                width: cropper_width + 'px',
                height: cropper_height + 'px'
            });

            if (typeof _data.coords === 'undefined') {
                var img_ratio = _data.width / _data.height;
                var viewport_ratio = comp_params.component.bbox.width / comp_params.component.bbox.height;

                if (viewport_ratio < img_ratio) {
                    var viewport_width = _data.height * viewport_ratio;
                    var viewport_height = _data.height;
                } else {
                    var viewport_width = _data.width;
                    var viewport_height = _data.width / viewport_ratio;
                }

                _data.coords = {
                    x1: (_data.width - viewport_width) / 2,
                    y1: (_data.height - viewport_height) / 2
                };

                _data.coords.x2 = _data.coords.x1 + viewport_width;
                _data.coords.y2 = _data.coords.y1 + viewport_height;
            }

            $.extend(new_data.coords, _data.coords);

            if (new_data_extend) {
                $.extend(new_data, new_data_extend);
            }

            var crop_coords = JSON.parse(JSON.stringify(new_data.coords));

            crop_coords.x1 *= main_scale;
            crop_coords.x2 *= main_scale;
            crop_coords.y1 *= main_scale;
            crop_coords.y2 *= main_scale;

            if (new_data.rotation) {
                img.css('transform', 'rotate(' + new_data.rotation + 'deg)');

                if ([90, 270].indexOf(new_data.rotation) >= 0) {
                    var buff = cropper_width;
                    cropper_width = cropper_height;
                    cropper_height = buff;
                }
            }

            $('<div />').appendTo(cropper_area).css({
                top: ((cropper_area_max_size - cropper_height) / 2) + 'px',
                left: ((cropper_area_max_size - cropper_width) / 2) + 'px',
                width: cropper_width + 'px',
                height: cropper_height + 'px'
            });

            $('<div />').appendTo(cropper_area.find('> div')).osc_cropper({
                callback: function (coords) {
                    new_data.coords = JSON.parse(JSON.stringify(coords));

                    new_data.coords.x1 /= main_scale;
                    new_data.coords.x2 /= main_scale;
                    new_data.coords.y1 /= main_scale;
                    new_data.coords.y2 /= main_scale;
                },
                ratio: comp_params.component.bbox.width / comp_params.component.bbox.height,
                on_load_coords: crop_coords,
                display_on_init: true
            });

            scene.swapZIndex();

            if (new_data.effect_config) {
                effect_list.find('[data-effect="' + new_data.effect_config.type + '"]').trigger('click');
            }
        }

        _imageUploaderEditPanelRender(comp_params, data, apply_callback, remove_callback) {
            $(document.body).addClass('osc-disable-pull-refresh');

            $.unwrapContent('personalizeConfigFrm_imageUploaderEditPanel');

            var scene = $('<div />').addClass('personalized-config-frm-image-edit-panel').css({
                height: screen.height - 300
            });

            $.wrapContent(scene, {key: 'personalizeConfigFrm_imageUploaderEditPanel', fixed_mode: true, close_callback: function () {
                    $(document.body).removeClass('osc-disable-pull-refresh');
                }});

            this._imageUploaderEditPanelRenderBody(scene, comp_params, data, apply_callback, remove_callback);
        }

        _imageUploaderRender(container, params) {
            var $this = this;

            var frame = this._renderComponentFrame(container, params);

            var uploader_container = $('<div />').addClass('personalized-img-uploader').appendTo(frame.content);

            var uploader = $('<div />').appendTo(uploader_container);

            var __preview_render = function (data) {
                uploader.hide();

                var preview = uploader_container.find('.preview');

                if (!preview[0]) {
                    preview = $('<div />').addClass('preview').prependTo(uploader_container);
                    $('<div />').addClass('thumb').appendTo(preview);
                }

                if (data) {
                    __preview_data_setter(data);
                }

                return preview;
            };

            var __preview_data_setter = function (data) {
                var preview = __preview_render();

                uploader.show();

                preview.removeAttr('data-uploader-step');
                preview.find('.uploader-progress-bar').remove();
                preview.find('.upload-progress-info').remove();
                preview.find('.edit-btn, .info').remove();
                preview.find('input[type="hidden"]').remove();

                var info_col = $('<div />').addClass('info').appendTo(preview);

                $('<div />').addClass('filename').text(data.name).appendTo(info_col);
                $('<div />').addClass('filesize').text($.formatSize(data.size)).appendTo(info_col);

                $('<div />').addClass('edit-btn').text('Edit').appendTo(preview);

                preview.find('.thumb').css('background-image', 'url(' + data.url + ')');

                preview.find('.thumb, .edit-btn').unbind().click(function () {
                    var input = preview.find('input');

                    $this._imageUploaderEditPanelRender(params, JSON.parse(input.val()), function (new_data) {
                        var data = JSON.parse(input.val());

                        if (typeof new_data.coords === 'object' && new_data.coords !== null) {
                            data.coords = new_data.coords;
                        } else {
                            delete data.coords;
                        }

                        if (typeof new_data.rotation !== 'undefined' && new_data.rotation !== null) {
                            data.rotation = new_data.rotation;
                        } else {
                            delete data.rotation;
                        }

                        if (typeof new_data.effect_config === 'object' && new_data.effect_config !== null) {
                            data.effect_config = new_data.effect_config;
                            preview.find('.thumb').attr('data-personalized-design-filter', new_data.effect_config.type);
                        } else {
                            delete data.effect_config;
                            preview.find('.thumb').removeAttr('data-personalized-design-filter');
                        }

                        input.val(JSON.stringify(data));

                        $this.updateConfig(params.config_key, JSON.stringify(data));
                    }, function () {
                        uploader_container.find('.preview').remove();
                        $this.updateConfig(params.config_key, null);
                    });
                });

                if (data.effect_config) {
                    preview.find('.thumb').attr('data-personalized-design-filter', data.effect_config.type);
                }

                $('<input />').attr({type: 'hidden', name: params.frm_name, value: JSON.stringify(data), 'data-config': params.config_key}).appendTo(preview);
            };

            uploader.osc_uploader({
                max_files: 1,
                max_connections: 1,
                process_url: $.base_url + '/personalizedDesign/frontend/uploadImage',
                btn_content: '<div class="uploader-content"></div>',
                dragdrop_content: 'Drop image here to upload',
                image_mode: true
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader_container.find('.preview').remove();
                $this.updateConfig(params.config_key, null);

                var preview = __preview_render();

                preview.attr({'file-id': file_id, 'data-uploader-step': 'queue'});

                $('<div />').addClass('uploader-progress-bar').append($('<div />')).appendTo(preview);
                $('<span />').addClass('upload-progress-info').appendTo(preview);

                try {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        var img = document.createElement('img');

                        img.onload = function () {
                            var canvas = document.createElement('canvas');

                            var MAX_WIDTH = 400;
                            var MAX_HEIGHT = 400;

                            var width = img.width;
                            var height = img.height;

                            if (width > height) {
                                if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                }
                            } else {
                                if (height > MAX_HEIGHT) {
                                    width *= MAX_HEIGHT / height;
                                    height = MAX_HEIGHT;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;

                            var ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            canvas.toBlob(function (blob) {
                                if (preview.attr('file-id') !== file_id) {
                                    return;
                                }

                                preview.find('.thumb').css('background-image', 'url(' + URL.createObjectURL(blob) + ')');
                            });
                        };

                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } catch (e) {

                }
            }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                var preview = __preview_render();

                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                if (parseInt(uploaded_percent) === 100) {
                    preview.attr('data-uploader-step', 'process');
                    preview.find('.upload-progress-info').text('Processing image...');
                } else {
                    preview.attr('data-uploader-step', 'upload');
                    preview.find('.upload-progress-info').text($.round(uploaded_percent) + '% Uploaded');
                    preview.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
                }
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                var preview = __preview_render();

                pointer.success = false;

                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                try {
                    eval('response = ' + response);
                } catch (e) {

                    preview.remove();
                    uploader.show();

                    alert('Upload error, please try again');
                    return;
                }

                if (response.result !== 'OK') {
                    preview.remove();
                    uploader.show();

                    alert(response.message);
                    return;
                }

                var img = $('<img />');

                img.bind('load error', function () {
                    __preview_data_setter(response.data);
                    $this.updateConfig(params.config_key, JSON.stringify(response.data));
                }).attr('src', response.data.url);
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                return;
                var preview = __preview_render();

                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                preview.remove();
                uploader.show();

                alert('Upload error, please try again');
            });

            if (this.config[params.config_key]) {
                __preview_render(JSON.parse(this.config[params.config_key]));
            }

            frame.component.bind('update-data', function (e, value, opts) {
                if (typeof opts !== 'object' || opts === null) {
                    opts = {};
                }

                if (!value) {
                    uploader_container.find('.preview').remove();
                    $this.updateConfig(params.config_key, null, opts.skip_trigger_update_evt);
                    uploader.show();
                } else {
                    __preview_render(value);
                    $this.updateConfig(params.config_key, JSON.stringify(value), opts.skip_trigger_update_evt);
                }
            });
        }

        _imageSelectorRender(container, params) {
            var $this = this;
            const isRequired = params.component.require === 1;
            const titleLowerCase = params.component.title.toLowerCase();
            if (titleLowerCase.indexOf('quote') >= 0) {
                var mapping = {};

                $.each(params.component.images, function (image_key, image) {
                    mapping[image_key] = image.title;
                });

                var matched = this._matchingQuoteByProductTitle(mapping);
            } else {
                var matched = null;
            }

            var selected = this.config[params.config_key] ? this.config[params.config_key] : '';

            if (matched) {
                selected = matched.idx;
            }

            var selected = this.config[params.config_key] ? this.config[params.config_key] : '';

            var frame = this._renderComponentFrame(container, params);

            var image_list = $('<div />').addClass('image-list').appendTo(frame.content);

            var _renderImageList = function (linking_compare_value) {
                image_list.html('');
                let selectedIndex = null, index = 0;

                $.each(params.component.images, function (image_key, image) {
                    if (typeof linking_compare_value !== 'undefined' && image.linking_condition && !$this._validateLinkingCondition(image.linking_condition, linking_compare_value)) {
                        return;
                    }

                    var label = $('<label />').appendTo(image_list);
                    const isChecked = image_key === selected
                    $('<input />').attr({type: 'radio', name: params.frm_name, value: image_key, 'data-config': params.config_key}).appendTo(label).click(function () {
                        if ($this.linking_container) {
                            $this.linking_container.trigger('linking_frm_update', [$this.design.id, params.component.title, image.title]);
                        }

                        $this.updateConfig(params.config_key, this.value);

                        $this._imageSelectorValidator(frame.component);
                    })[0].checked = isChecked;
                    if(isChecked) selectedIndex = index

                    $('<span />').css('background-image', 'url(' + image.url + ')').appendTo(label);
                    index += 1;
                    // $('<ins />').append($.renderIcon('check-solid')).appendTo(label);
                });

                // _renderImageSelectorShowMore(image_list, selectedIndex, index)
                
                if (!isRequired && $('input[name="'+params.frm_name+'"]:checked').length === 0) {
                    $('<input />').addClass('hide').attr({type: 'radio', name: params.frm_name, value: ''}).appendTo(image_list)[0].checked = true
                }
            };

            if (this.linking_container && params.component.linking_condition) {
                this.linking_container.bind('linking_frm_update.linking_' + this.design.id, function (e, design_id, frm_title, frm_value) {
                    if (design_id === $this.design.id) {
                        return;
                    }

                    if (!$this._validateLinkingCondition(params.component.linking_condition, frm_title)) {
                        return;
                    }

                    _renderImageList(frm_value);
                });
            }

            _renderImageList();
        }

        _imageGroupSelectorRender(container, params) {
            var $this = this;

            var groups = params.component.groups;

            var image_selected = this.config[params.config_key] ? this.config[params.config_key] : '';
            var group_selected = '';

            $.each(groups, function (group_idx, group) {
                $.each(group.images, function (image_idx, image) {
                    if (image_idx === image_selected) {
                        group_selected = group_idx;
                    }

                    groups[group_idx]['images'][image_idx] = {
                        title: image.title,
                        url: image.url ? image.url : ''
                    };

                    if (image.linking_condition) {
                        groups[group_idx]['images'][image_idx].linking_condition = image.linking_condition;
                    }
                });
            });

            var frame = this._renderComponentFrame(container, params);

            var image_list = $('<div />').addClass('image-list').appendTo(frame.content);

            var group_selector = $('<div />').addClass('select_wrap select_common--medium').prependTo(frame.content);

            $('<ins />').append($.renderIcon('icon-arrow')).appendTo(group_selector);

            var select = $('<select />').addClass('select_common').attr({'name': params.frm_name + '[group]', 'data-config': params.config_key}).appendTo(group_selector);

//                initCustomSelector.apply(select[0]);

            var _renderGroupSelector = function (linking_compare_value) {
                image_list.html('');

                select.html('').unbind().change(function () {
                    var group_key = $(this).val();
                    let selectedIndex = null, index = 0;

                    image_list.html('');

                    if (typeof groups[group_key] === 'undefined') {
                        return;
                    }

                    $.each(groups[group_key].images, function (image_id, image) {
                        if (typeof linking_compare_value !== 'undefined' && image.linking_condition && !$this._validateLinkingCondition(image.linking_condition, linking_compare_value)) {
                            return;
                        }

                        var label = $('<label />').appendTo(image_list);
                        const isChecked = image_id === image_selected;

                        $('<input />').attr({type: 'radio', name: params.frm_name, value: image_id}).appendTo(label).click(function () {
                            if ($this.linking_container) {
                                $this.linking_container.trigger('linking_frm_update', [$this.design.id, params.component.title, image.title]);
                            }

                            $this.updateConfig(params.config_key, image_id);
                            image_selected = image_id;
                            $this._imageGroupSelectorValidator(frame.component);
                        })[0].checked = isChecked;
                        if(isChecked) selectedIndex = index;

                        $('<span />').css('background-image', 'url(' + image.url + ')').appendTo(label);
                        // $('<ins/>').append($.renderIcon('check-solid')).appendTo(label);
                        index += 1;
                    });

                    // _renderImageSelectorShowMore(image_list, selectedIndex, index);
                });

                $.each(groups, function (group_key, group) {
                    var image_counter = 0;

                    $.each(group.images, function (image_id, image) {
                        if (typeof linking_compare_value !== 'undefined' && image.linking_condition && !$this._validateLinkingCondition(image.linking_condition, linking_compare_value)) {
                            return;
                        }

                        image_counter++;
                    });

                    if (image_counter < 1) {
                        return;
                    }

                    var option = $('<option />').attr('value', group_key).text(group.title).appendTo(select);

                    if (group_key === group_selected) {
                        option.attr('selected', 'selected');
                    }
                });

                select.trigger('change');
            };

            if (this.linking_container && params.component.linking_condition) {
                this.linking_container.bind('linking_frm_update.linking_' + this.design.id, function (e, design_id, frm_title, frm_value) {
                    if (design_id === $this.design.id) {
                        return;
                    }

                    if (!$this._validateLinkingCondition(params.component.linking_condition, frm_title)) {
                        return;
                    }

                    _renderGroupSelector(frm_value);
                });
            }

            _renderGroupSelector();
        }
       
        _validateLinkingCondition(condition_data, compare_value) {
            var matched = 0;

            $.each(condition_data.condition, function (i, condition_item) {
                if (['greater_than', 'less_than'].indexOf(condition_item.operator) >= 0) {
                    var a = parseInt(compare_value);
                    var b = parseInt(condition_item.value);

                    if (isNaN(a)) {
                        a = 0;
                    }

                    if (isNaN(b)) {
                        b = 0;
                    }

                    if (condition_item.operator === 'greater_than') {
                        if (a <= b) {
                            return;
                        }
                    } else if (a >= b) {
                        return;
                    }
                } else {
                    var a = '' + compare_value;
                    var b = '' + condition_item.value;

                    if (condition_item.operator === 'equals') {
                        if (a !== b) {
                            return;
                        }
                    } else if (condition_item.operator === 'not_equals') {
                        if (a === b) {
                            return;
                        }
                    } else {
                        var position = a.indexOf(b);

                        if (condition_item.operator === 'starts_with') {
                            if (position !== 0) {
                                return;
                            }
                        } else if (condition_item.operator === 'ends_with') {
                            if (position !== (a.length - b.length)) {
                                return;
                            }
                        } else if (condition_item.operator === 'contains') {
                            if (position < 0) {
                                return;
                            }
                        } else if (condition_item.operator === 'not_contains') {
                            if (position >= 0) {
                                return;
                            }
                        }
                    }
                }

                matched++;

                if (!condition_data.matching_all) {
                    return false;
                }
            });

            return condition_data.matching_all ? (matched === condition_data.condition.length) : matched > 0;
        }

        _switcherByQuantitySelectRender(container, params){
            const $this = this;
            const isRequired = params.component.require === 1;

            const selected = this.config[params.config_key] ? this.config[params.config_key] : '';
            const frame = this._renderComponentFrame(container, params);

            const option_list = $('<div />').addClass('button-list').appendTo(frame.content);
            const sub_comp_container = $('<div />').addClass('container').appendTo(frame.content);

            const _renderOptionList = (linking_compare_value) => {
                option_list.html('');
                sub_comp_container.html('');

                $.each(params.component.scenes, function (scene_idx, scene) {
                    if (typeof linking_compare_value !== 'undefined' && scene.linking_condition && !$this._validateLinkingCondition(scene.linking_condition, linking_compare_value)) {
                        return;
                    }

                    const option = $('<div />').addClass("option-value").text(scene.title).appendTo(option_list);
                    const isChecked = scene_idx === selected;

                    const input = $('<input />').attr({type: 'radio', name: params.frm_name, value: scene_idx}).appendTo(option).click(function () {
                        sub_comp_container.html('');
                        option_list.find('.selected').removeClass('selected');
                        option.addClass("selected");
        
                        if ($this.linking_container) {
                            $this.linking_container.trigger('linking_frm_update', [$this.design.id, params.component.title, scene.title]);
                        }
        
                        if (typeof params.component.scenes[scene_idx] === 'undefined') {
                            $this.updateConfig(params.config_key, '');
                            return;
                        }
        
                        $this.updateConfig(params.config_key, scene_idx);
        
                        $this._renderComponents(sub_comp_container, params.component.scenes[scene_idx].components);
                        $this._switcherBySelectValidator(frame.component);
                    });
                    input[0].checked = isChecked;

                    if (isChecked) {
                        input.trigger("click");
                    }
                });
                if (!isRequired && $('input[name="'+params.frm_name+'"]:checked').length === 0) {
                    $('<input />').addClass('hide').attr({type: 'radio', name: params.frm_name, value: ''}).appendTo(option_list)[0].checked = true
                }
            }

            if (this.linking_container && params.component.linking_condition) {
                this.linking_container.bind('linking_frm_update.linking_' + this.design.id, function (e, design_id, frm_title, frm_value) {
                    if (design_id === $this.design.id) {
                        return;
                    }

                    if (!$this._validateLinkingCondition(params.component.linking_condition, frm_title)) {
                        return;
                    }

                    _renderOptionList(frm_value);
                });
            }
            _renderOptionList();
        }

        _switcherBySelectRender(container, params) {
            var $this = this;
            const titleLowerCase = params.component.title.toLowerCase();
            if(Object.keys(params.component.scenes).length <=5 
                && titleLowerCase.indexOf('number of') >=0
                ) {
                //Render quantity selector
                return this._switcherByQuantitySelectRender(container, params);
            } else if (titleLowerCase.indexOf('quote') >= 0) {
                var mapping = {};

                $.each(params.component.scenes, function (scene_idx, scene) {
                    mapping[scene_idx] = scene.title;
                });

                var matched = this._matchingQuoteByProductTitle(mapping);
            } else {
                var matched = null;
            }

            var selected = this.config[params.config_key] ? this.config[params.config_key] : '';

            if (matched) {
                selected = matched.idx;
            }

            var frame = this._renderComponentFrame(container, params);

            var sub_comp_container = $('<div />').addClass('container').appendTo(frame.content);

            var group_selector = $('<div />').addClass('select_wrap select_common--medium').prependTo(frame.content);

            $('<ins />').append($.renderIcon('icon-arrow')).appendTo(group_selector);

            var select = $('<select />').addClass('select_common').attr({'name': params.frm_name, 'data-config': params.config_key}).appendTo(group_selector).change(function () {
                var scene_idx = $(this).val();

                sub_comp_container.html('');

                if ($this.linking_container) {
                    $this.linking_container.trigger('linking_frm_update', [$this.design.id, params.component.title, $(this.options[this.selectedIndex]).text()]);
                }

                if (typeof params.component.scenes[scene_idx] === 'undefined') {
                    $this.updateConfig(params.config_key, '');
                    return;
                }

                $this.updateConfig(params.config_key, scene_idx);

                $this._renderComponents(sub_comp_container, params.component.scenes[scene_idx].components);

                $this._switcherBySelectValidator(frame.component);
            });

            var _renderOption = function (linking_compare_value) {
                select.html('');
                sub_comp_container.html('');

                $('<option />').attr('value', '').text('Please select an option').appendTo(select);

                $.each(params.component.scenes, function (scene_idx, scene) {
                    if (typeof linking_compare_value !== 'undefined' && scene.linking_condition && !$this._validateLinkingCondition(scene.linking_condition, linking_compare_value)) {
                        return;
                    }

                    var option = $('<option />').attr('value', scene_idx).text(scene.title).appendTo(select);

                    if (scene_idx === selected) {
                        option.attr('selected', 'selected');
                    }
                });

                select.trigger('change');
            };

            if (this.linking_container && params.component.linking_condition) {
                this.linking_container.bind('linking_frm_update.linking_' + this.design.id, function (e, design_id, frm_title, frm_value) {
                    if (design_id === $this.design.id) {
                        return;
                    }

                    if (!$this._validateLinkingCondition(params.component.linking_condition, frm_title)) {
                        return;
                    }

                    _renderOption(frm_value);
                });
            }

            _renderOption();

//                initCustomSelector.apply(select[0]);
        }

        _switcherByImageRender(container, params) {
            var $this = this;
            const isRequired = params.component.require === 1;
            const titleLowerCase = params.component.title.toLowerCase();
            
            let isSkinColor = false;
            if (titleLowerCase.indexOf('skin color') >= 0) {
                isSkinColor = true;
            } else if (titleLowerCase.indexOf('quote') >= 0) {
                var mapping = {};

                $.each(params.component.scenes, function (scene_idx, scene) {
                    mapping[scene_idx] = scene.image.title;
                });

                var matched = this._matchingQuoteByProductTitle(mapping);
            } else {
                var matched = null;
            }

            var selected = this.config[params.config_key] ? this.config[params.config_key] : '';

            if (matched) {
                selected = matched.idx;
            }

            var frame = this._renderComponentFrame(container, params);

            var sub_comp_container = $('<div />').addClass('container').appendTo(frame.content);

            var image_list = $('<div />').addClass(isSkinColor ? 'color-list' :'image-list').prependTo(frame.content);

            var _renderImageList = function (linking_compare_value) {
                image_list.html('');
                sub_comp_container.html('');
                let selectedIndex = null, index = 0;

                $.each(params.component.scenes, function (scene_idx, scene) {
                    if (typeof linking_compare_value !== 'undefined' && scene.linking_condition && !$this._validateLinkingCondition(scene.linking_condition, linking_compare_value)) {
                        return;
                    }

                    var label = $('<label />').appendTo(image_list);
                    const isChecked = scene_idx === selected;

                    $('<input />').attr({type: 'radio', name: params.frm_name, value: scene_idx, 'data-scene': scene_idx, 'data-title': scene.image.title}).appendTo(label).click(function () {
                        var scene_idx = $(this).val();

                        sub_comp_container.html('');

                        if ($this.linking_container) {
                            $this.linking_container.trigger('linking_frm_update', [$this.design.id, params.component.title, scene.image.title]);
                        }

                        if (typeof params.component.scenes[scene_idx] === 'undefined') {
                            $this.updateConfig(params.config_key, '');
                            $this.config[params.config_key] = '';
                            return;
                        }

                        $this.updateConfig(params.config_key, scene_idx);

                        $this._renderComponents(sub_comp_container, params.component.scenes[scene_idx].components);
                        $this._switcherByImageValidator(frame.component);
                    })[0].checked = isChecked;
                    if(isChecked) selectedIndex = index;

                    $('<span />').css('background-image', 'url(' + scene.image.url + ')').appendTo(label);
                    index += 1;
                    // $('<ins />').append($.renderIcon('check-solid')).appendTo(label);
                });

                // if(!isSkinColor) _renderImageSelectorShowMore(image_list, selectedIndex, index);
                
                if (!isRequired && $('input[name="'+params.frm_name+'"]:checked').length === 0) {
                    $('<input />').css('display', 'none').attr({type: 'radio', name: params.frm_name, value: ''}).appendTo(image_list)[0].checked = true
                }

                image_list.find('input:checked').trigger('click');
            };

            if (this.linking_container && params.component.linking_condition) {
                this.linking_container.bind('linking_frm_update.linking_' + this.design.id, function (e, design_id, frm_title, frm_value) {
                    if (design_id === $this.design.id) {
                        return;
                    }

                    if (!$this._validateLinkingCondition(params.component.linking_condition, frm_title)) {
                        return;
                    }

                    _renderImageList(frm_value);
                });
            }

            _renderImageList();
        }

        
        _validateTextRequire(component) {
            _removeError(component);
            const input = component.find('input, textarea')
            if(input) input.removeClass("input--error");

            if (component.attr('data-require') !== '1') {
                return true;
            }
            var value = input.val().trim();

            if (value === '') {
                input.addClass("input--error")
                _showError(component, 'This option is required!');
                return false;
            }

            return true;
        }

        _validateOptionRequire(component) {
            _removeError(component);

            if (component.attr('data-require') !== '1') {
                return true;
            }

            var checked = false;

            component.find('input').each(function () {
                if (this.checked) {
                    checked = true;
                    return false;
                }
            });

            if (!checked) {
                _showError(component, 'This option is required!');
                return false;
            }

            return true;
        }

        _inputValidator(component) {
            if (!this._validateTextRequire(component)) {
                return false;
            }

            var _input =  component.find('input, textarea');
            var value = _input.val().trim();
            const __showInputError = (message) => {
                _input.addClass("input--error");
                _showError(component, message);
            }
            if (value && /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/.test(value)) {
                __showInputError('Please remove your emoji');
                return false;
            }

            var min_length = parseInt(_input.attr('data-min-len'));
            var max_length = parseInt(_input.attr('data-max-len'));

            if (isNaN(min_length)) {
                min_length = 0;
            }

            if (isNaN(max_length)) {
                max_length = 0;
            }

            var length = value.length;

            if (length > 0 && min_length > 0 && length < min_length) {
                __showInputError('The value need has min ' + min_length + ' characters');
                return false;
            }

            if (max_length > 0 && length > max_length) {
                __showInputError('The value unable to exceed ' + max_length + ' characters');
                return false;
            }

            return true;
        }

        _imageUploaderValidator(component) {
            if (!this._validateTextRequire(component)) {
                return false;
            }
        }

        _imageSelectorValidator(component) {
            if (!this._validateOptionRequire(component)) {
                return false;
            }
        }

        _imageGroupSelectorValidator(component) {
            if (!this._validateOptionRequire(component)) {
                return false;
            }
        }

        _switcherByImageValidator(component) {
            _removeError(component);

            if (component.attr('data-require') === '1') {
                var checked = false;
                component.find('> .personalized-config-comp__content input').each(function () {
                    if (this.checked) {
                        checked = true;
                        return false;
                    }
                });

                if (!checked) {
                    _showError(component, 'This option is required!');
                    return false;
                }
            }
        }

        _switcherBySelectValidator(component) {
            _removeError(component);

            if (component.attr('data-require') === '1') {
                if(component.find('> .personalized-config-comp__content .button-list').length > 0) {
                    return this._validateOptionRequire(component);
                }
                const selectElm = component.find('> .personalized-config-comp__content > .select_wrap select')
                if (!selectElm.val()) {
                    selectElm.addClass("select_common--error");
                    _showError(component, 'This option is required!');
                    return false;
                } else {
                    selectElm.removeClass('select_common--error');
                }
            }
        }
    }

    function _validator_input(component) {
        if (!_validator_text_require(component)) {
            return false;
        }
        const _input = component.find('input, textarea');
        const __showInputError = (message) => {
            if(_input) _input.addClass("input--error");
            _showError(component, message);
        }

        var value = component.find('input, textarea').val().trim();
        
        if (value && /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/.test(value)) {
            __showInputError('Please remove your emoji');
            return false;
        }

        var min_length = parseInt(component.attr('data-min-len'));
        var max_length = parseInt(component.attr('data-max-len'));

        if (isNaN(min_length)) {
            min_length = 0;
        }

        if (isNaN(max_length)) {
            max_length = 0;
        }

        var length = value.length;

        if (length > 0 && min_length > 0 && length < min_length) {
            __showInputError('The value need has min ' + min_length + ' characters');
            return false;
        }

        if (max_length > 0 && length > max_length) {
            __showInputError('The value unable to exceed ' + max_length + ' characters');
            return false;
        }
    }

    function _validator_imageUploader(component) {
        if (!_validator_text_require(component)) {
            return false;
        }
    }

    function _validator_imageSelector(component) {
        if (!_validator_option_require(component)) {
            return false;
        }
    }

    function _validator_imageGroupSelector(component) {
        if (!_validator_option_require(component)) {
            return false;
        }
    }

    function _validator_switcherByImage(component) {
        _removeError(component);

        if (component.attr('data-require') === '1') {
            var checked = false;

            component.find('> .personalized-config-comp__content input').each(function () {
                if (this.checked) {
                    checked = true;
                    return false;
                }
            });

            if (!checked) {
                _showError(component, 'This option is required!');
                return false;
            }
        }
    }

    function _validator_switcherBySelect(component) {
        _removeError(component);

        if (component.attr('data-require') === '1') {
            const selectElm = component.find('> .personalized-config-comp__content > .select_wrap select')
            if (!selectElm.val()) {
                selectElm.addClass("select_common--error");
                _showError(component, 'This option is required!');
                return false;
            }  else {
                selectElm.removeClass("select_common--error");
            }
        }
    }

    function _validator_text_require(component) {
        _removeError(component);
        const input = component.find('input, textarea');
        if(input) input.removeClass("input--error");

        if (component.attr('data-require') !== '1') {
            return true;
        }

        var value = input.val().trim();

        if (value === '') {
            _showError(component, 'This option is required!');
            input.addClass("input--error")
            return false;
        }

        return true;
    }

    function _validator_option_require(component) {
        _removeError(component);

        if (component.attr('data-require') !== '1') {
            return true;
        }

        var checked = false;

        component.find('input').each(function () {
            if (this.checked) {
                checked = true;
                return false;
            }
        });

        if (!checked) {
            _showError(component, 'This option is required!');
            return false;
        }

        return true;
    }

    window.initPersonalizedDesignConfigRenderer = function (design, options) {
        return new DesignConfigRenderer(design, options);
    };

    window.personalizedDesignLoadPreview = function () {
        var node = $(this);

        var design = JSON.parse(node.find('[data-json]')[0].innerHTML);

        var order_line_item_id = parseInt($(this).closest('[data-order-line-id]').attr('data-order-line-id'));

        if (isNaN(order_line_item_id)) {
            order_line_item_id = 0;
        }

        $.ajax({
            url: $.base_url + '/personalizedDesign/common/svg',
            type: 'post',
            data: {id: design.id, config: design.config, order_line_item: order_line_item_id},
            success: function (response) {
                if (response.result !== 'OK') {
                    return;
                }
//                var canvas = $('<canvas />').appendTo(node)[0];
//
//                node.attr('data-doc-type', response.data.document_type);
//
//                var ctx = canvas.getContext("2d");
//                var DOMURL = self.URL || self.webkitURL || self;
//                var img = new Image();
//                var svg = new Blob([response.data.svg], {type: "image/svg+xml;charset=utf-8"});
//                var url = DOMURL.createObjectURL(svg);
//                img.onload = function () {
//                    ctx.drawImage(img, 0, 0);
//                    var png = canvas.toDataURL("image/png");
//                    node.append('<img src="' + png + '"/>');
//                    DOMURL.revokeObjectURL(png);
//                };
//                img.src = url;

                node.attr({'data-doc-type': response.data.document_type}).html(response.data.svg);

            }
        });
    };

    window.personalizedDesignInitFrm = function () {
        var main_container = $(this);

        function _validate() {
            main_container.find('[data-component]').each(function () {
                var component = $(this);
                var component_type = component.attr('data-component');

                var validator = '_validator_' + component_type;

                try {
                    eval('validator = ' + validator);
                } catch (e) {
                    return;
                }

                if (typeof validator !== 'function') {
                    return;
                }

                if (validator(component) === false) {
                    throw "Please correct personalized config";
                }
            });
        }

        main_container.closest('form').bindUp('submit', function (e) {
            try {
                _validate();
            } catch (ex) {
                alert('Please check your design once again. Some required options have been left empty, unselected, or wrongly filled.');
                //Scroll to error message
                var target = $('.error-msg').parent().height();
                $('html,body').animate({
                    scrollTop: $('.error-msg').offset().top - target
                }, 'slow');

                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
            }
        });

        main_container.find('.mrk-preview-btn').click(function () {
            try {
                _validate();
            } catch (e) {
                alert('Please check your design once again. Some required options have been left empty, unselected, or wrongly filled.');
                //Scroll to error message
                var target = $('.error-msg').parent().height();
                $('html,body').animate({
                    scrollTop: $('.error-msg').offset().top - target
                }, 'slow');
                return;
            }

            var form_data = main_container.closest('form').serializeObject();

            if (form_data === null || typeof form_data !== 'object') {
                alert('Some data is incorrect, please contact to our supporter to solve the issue');
            }

            form_data.custom.personalized_design = parseInt(form_data.custom.personalized_design);

            if (form_data.custom.personalized_config === null || typeof form_data.custom.personalized_config !== 'object' || isNaN(form_data.custom.personalized_design) || form_data.custom.personalized_design < 1) {
                alert('Some data is incorrect, please contact to our supporter to solve the issue');
            }

            $.ajax({
                url: main_container.attr('data-preview-url'),
                type: 'post',
                data: {id: form_data.custom.personalized_design, config: form_data.custom.personalized_config, variant_id: form_data.variant_id, size: form_data.size},
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $.unwrapContent('personalizeDesign_previewConfig');

                    var modal = $('<div />').addClass('osc-modal');

                    var header = $('<header />').appendTo(modal);
                    $('html').css('overflow','hidden');

                    $('<div />').addClass('title').html('Preview').appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('personalizeDesign_previewConfig');
                        $('html').removeAttr('style')
                    }).appendTo(header);


                    var modal_body = $('<div />').addClass('body personalized-design-popup-preview').appendTo(modal);

                    var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

                    var cell = $('<div />').appendTo(row);

                    if (response.data.document_type.indexOf('dpi_canvas') >= 0) {
                        var matched = response.data.size.match(/^(.*[\s+\_\-])?(\d+)[\s+\_\-]*(''|\"|inch|in|cm|mm|m)?[\s+\_\-]*x[\s+\_\-]*(\d+)[\s+\_\-]*(''|\"|inch|in|cm|mm|m)?([\s+\_\-]+.*)?$/);

                        if (!Array.isArray(matched) || matched.length < 1) {
                            console.error('Unable to detect canvas size');
                            return;
                        }

                        var canvas_size_portrait = matched[2] + 'x' + matched[4];

                        $('<div />').addClass('preview-container').attr({'data-doc-type': response.data.document_type, 'canvas_size': 'canvas_size_portrait' + canvas_size_portrait}).html(response.data.svg).appendTo(cell);
                    } else {
                        $('<div />').addClass('preview-container').attr({'data-doc-type': response.data.document_type}).html(response.data.svg).appendTo(cell);
                    }

                    $.wrapContent(modal, {key: 'personalizeDesign_previewConfig',close_callback: function () {
                        $('html').removeAttr('style');
                    }});

                    modal.moveToCenter().css('top', '100px');
                }
            });
        });


    };


    window.personalizedDesignInitSemiTestFrm = function () {
        var main_container = $(this);

        function _validate() {
            main_container.find('[data-component]').each(function () {
                var component = $(this);
                var component_type = component.attr('data-component');

                var validator = '_validator_' + component_type;

                try {
                    eval('validator = ' + validator);
                } catch (e) {
                    return;
                }

                if (typeof validator !== 'function') {
                    return;
                }

                if (validator(component) === false) {
                    throw "Please correct personalized config";
                }
            });
        }

        main_container.closest('form').bindUp('submit', function (e) {
            try {
                _validate();
            } catch (ex) {
                alert('Please complete your personalized design. Some required options have been left empty.');
                //Scroll to error message
                var target = $('.error-msg').parent().height();
                $('html,body').animate({
                    scrollTop: $('.error-msg').offset().top - target
                }, 'slow');

                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();
            }
        });

        //Validate on frequently bought together modal

        main_container.closest('.modal-combo__container').find('.tab_action .btn').on('validate_personalized_design', function (e, validator) {
            try {
                _validate();
            } catch (ex) {
                alert('Please complete your personalized design. Some required options have been left empty.');

                if (typeof validator.error !== 'undefined') {
                    validator.error = 1;
                }

                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                return false;
            }

        });

        main_container.find('.mrk-preview-btn').click(function () {
            try {
                _validate();
            } catch (e) {
                alert('Please complete your personalized design. Some required options have been left empty.');
                //Scroll to error message
                var target = $('.error-msg').parent().height();
                $('html,body').animate({
                    scrollTop: $('.error-msg').offset().top - target
                }, 'slow');
                return;
            }

            var form_data = main_container.closest('form').serializeObject();
            if (form_data === null || typeof form_data !== 'object') {
                alert('Some data is incorrect, please contact to our supporter to solve the issue');
            }

            $.ajax({
                url: main_container.attr('data-preview-url'),
                type: 'post',
                data: {design_ids: form_data.custom.personalized_design, config: form_data.custom.personalized_config, variant_id: form_data.variant_id},
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $.unwrapContent('personalizeDesign_previewConfig');

                    var modal = $('<div />').addClass('osc-modal');

                    var header = $('<header />').appendTo(modal);

                    $('<div />').addClass('title').html('Preview').appendTo($('<div />').addClass('main-group').appendTo(header));
                    const closeModal = () => {
                        $.unwrapContent('personalizeDesign_previewConfig');
                        $('html').removeAttr('style')
                    }
                    $('<div />').addClass('close-btn').click(closeModal).appendTo(header);

                    var modal_body = $('<div />').addClass('personalized-semitest-preview').appendTo(modal);

                    var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

                    var cell = $('<div />').appendTo(row);


                    var design_tabs = $('<div />').addClass('design-tabs').appendTo(cell);
                    var design_scene = $('<div />').addClass('design-scene').appendTo(cell);
                    
                    const _renderFooter = () => {
                        //TODO: AB TEST FOR BUY IT NOW
                        if(!window.isDisplayBuyItNow) return;

                        const container = $("<div />").addClass("preview-footer").appendTo(cell);
                        const main_quantity_box = $('.quantity_box');
                        const quantity_box = main_quantity_box.clone().appendTo(container)
                        quantity_box.init = window.initMultiProductDetailQuantityModifier;
                        quantity_box.init();
                        quantity_box.prepend($("<label />").text("Quantity").addClass("quantity-label"))
                        const quantity_input = quantity_box.find('[name="quantity"]');

                        const main_quantity_input = main_quantity_box.find('[name="quantity"]');
                        const onChangeQuantity = () => {
                            main_quantity_input.val(quantity_input.val()); 
                        }
                        quantity_input.on("change paste keyup", onChangeQuantity);
                        quantity_box.find(".quantity_box_btn").click(onChangeQuantity)
                        const button_group = $("<div />").addClass("row").appendTo(container);

                        const buy_it_now_btn = $('<button />').text("Add To Cart").attr({
                            "class": "button_primary button_medium button_full",
                            "id": "buy_it_now"
                        })
                        const cancel_btn = $('<button />').text("Cancel").attr({
                            "class": "button_gray button_medium button_full",
                            "id": "cancel_btn"
                        }).click(closeModal);

                        $("<div />").addClass("col col-md-3").append(cancel_btn).appendTo(button_group);
                        $("<div />").addClass("col col-md-3").append(buy_it_now_btn).appendTo(button_group);
                        buy_it_now_btn.click(() => {
                            $('#add_to_cart_btn').trigger( "click" )
                        })
                    }

                    _renderFooter()
                    var svgs = response.data.data_svg;

                    svgs.forEach(function (svg) {
                        $('<div />').text(typeof svg.title === 'undefined' ||  svg.title == '' ? '' : svg.title).appendTo(design_tabs).click(function () {
                            if (svg.title != '') {
                                design_tabs.find('> *').removeClass('activated');
                                $(this).addClass('activated');
                            }
                            design_scene.html('');
                            
                            const documentRatio = (svg.document.width / svg.document.height) || 1;
                            // if(documentRatio) {
                            const modalWidth = (window.innerHeight - modal.innerHeight()) * documentRatio;
                            modal_body.css({
                                "width": modalWidth,
                                // "padding": modalWidth > 500 ? '0 64px 24px':'0 24px 24px'
                            });
                            $('<div />').addClass('preview-container').attr({'data-doc-type': svg.document_type}).html(svg.svg).appendTo(design_scene);
                            modal.moveToCenter();
                        });
                    });

                    if (design_tabs[0].childNodes.length < 2) {
                        design_tabs.hide();
                        design_scene.css('margin-bottom', 24)
                    } 


                    $.wrapContent(modal, {key: 'personalizeDesign_previewConfig',close_callback: function () {
                        $('html').removeAttr('style');
                    }, fixed_mode: true});
                    design_tabs.find('> :first-child').trigger('click');

                }
            });
        });
    };

    window.personalizedDesign_inputInit = function () {
        var component = $(this);

        component.find('input[type="text"]').blur(function () {
            _validator_input(component);
            _updateConfigCache(component.attr("data-design-id"),component.attr("data-config-key"), $(this).val());
        });
    };

    window.personalizedDesign_imageGroupSelectorInit = function () {
        var component = $(this);

        var group_data = JSON.parse(component.find('script[data-json="groups"]')[0].innerHTML);

        var image_list = component.find('.image-list');

        component.find('select').change(function () {
            var group_key = $(this).val();

            image_list.html('');

            if (typeof group_data[group_key] === 'undefined') {
                return;
            }

            $.each(group_data[group_key].images, function (image_id, image) {
                var label = $('<label />').appendTo(image_list);

                var checker = $('<input />').attr({type: 'radio', name: component.attr('data-name'), value: image_id}).appendTo(label);
                $('<span />').css('background-image', 'url(' + image.url + ')').appendTo(label);
                // $('<ins/>').append($.renderIcon('check-solid')).appendTo(label);

                if (image.selected) {
                    delete image.selected;
                    checker[0].checked = true;
                }
            });
            _updateConfigCache(component.attr("data-design-id"),component.attr("data-config-key"), $(this).val());

        }).trigger('change');
    };

    window.personalizedDesign_switcherBySelectInit = function () {
        var component = $(this);

        var scene_data = JSON.parse(component.find('script[data-json="scenes"]')[0].innerHTML);

        var cached = [];

        var container = component.find('> .personalized-config-comp__content > .container');

        if (parseInt(component.attr('data-flexible-mug-size')) === 1) {
            _initFlexibleMugSizeChangeListener();
        }

        var auto_select = component.attr('data-select');

        if (auto_select && component.find('> .personalized-config-comp__content > .select_wrap select')[0].selectedIndex < 1) {
            var option_default = component.find('> .personalized-config-comp__content > .select_wrap select option')[1];

            if (option_default) {
                option_default.selected = true;
            }
        }

        component.find('> .personalized-config-comp__content > .select_wrap select').change(function () {
            var value = $(this).val();

            if (typeof container.attr('data-idx') !== 'undefined') {
                var elms = [];

                container.children().each(function () {
                    elms.push($(this));
                    $(this).detach();
                });

                cached[container.attr('data-idx')] = elms;
            }

            container.html('').removeAttr('data-idx');

            if (value === '') {
                return;
            }

            var cache_idx = this.selectedIndex - 1;

            container.attr('data-idx', cache_idx);

            if (typeof cached[cache_idx] === 'undefined') {
                container.append(scene_data[value]);
            } else {
                cached[cache_idx].forEach(function (elm) {
                    container.append(elm);
                });
            }

            if (parseInt(component.attr('data-flexible-mug-size')) === 1) {
                //var mug_size = $(this.options[this.selectedIndex]).text().replace(/[^a-zA-Z0-9]/).toLowerCase();
                const mug_size = $(this.options[this.selectedIndex]).text().split(/[^a-zA-Z0-9]/).join('').toLowerCase();

                $(document.body).trigger('personalizedFlexibleMugSizeChanged', [mug_size, component]);
            }

            _updateConfigCache(component.attr("data-design-id"),component.attr("data-config-key"), $(this).val());
        }).trigger('change');
    };

    window.personalizedDesign_switcherByImageInit = function () {
        var component = $(this);

        var scene_data = JSON.parse(component.find('script[data-json="scenes"]')[0].innerHTML);

        var cached = [];

        var container = component.find('> .personalized-config-comp__content > .container');

        if (parseInt(component.attr('data-flexible-mug-size')) === 1) {
            _initFlexibleMugSizeChangeListener();
        }

        var auto_select = component.attr('data-select');
        const inputs = component.find('> .personalized-config-comp__content input[data-scene]');

        if (auto_select) {
            var prevent_auto_select = false;
            inputs.each(function () {
                if (this.checked) {
                    prevent_auto_select = true;
                    return false;
                }
            });

            if (!prevent_auto_select) {
                var option_default = inputs[0];

                if (option_default) {
                    option_default.checked = true;
                }
            }
        }

        inputs.click(function () {
            var value = $(this).val();

            if (typeof container.attr('data-idx') !== 'undefined') {
                var elms = [];

                container.children().each(function () {
                    elms.push($(this));
                    $(this).detach();
                });

                cached[container.attr('data-idx')] = elms;
            }

            container.html('').removeAttr('data-idx');

            if (value === '') {
                return;
            }

            var cache_idx = this.getAttribute('data-scene');

            container.attr('data-idx', cache_idx);

            if (typeof cached[cache_idx] === 'undefined') {
                container.append(scene_data[cache_idx]);
            } else {
                cached[cache_idx].forEach(function (elm) {
                    container.append(elm);
                });
            }

            if (parseInt(component.attr('data-flexible-mug-size')) === 1) {
                //var mug_size = $(this).attr('data-title').replace(/[^a-zA-Z0-9]/).toLowerCase();
                const mug_size = $(this).attr('data-title').split(/[^a-zA-Z0-9]/).join('').toLowerCase();
                $(document.body).trigger('personalizedFlexibleMugSizeChanged', [mug_size, component]);
            }

            _updateConfigCache(component.attr("data-design-id"),component.attr("data-config-key"), $(this).val());
        }).each(function () {
            if (this.checked) {
                this.click();
            }
        });
    };

    window.orderPersonalizedDesignEdit = function () {
        $(this).click(function () {
            var btn = $(this);

            if (btn.attr('disabled') === 'disabled') {
                return;
            }

            btn.attr('disabled', 'disabled');

            $.ajax({
                url: $.base_url + '/personalizedDesign/frontend/edit',
                type: 'post',
                data: {
                    order: btn.attr('data-order'),
                    item: btn.attr('data-line-item')
                },
                beforeSend: function () {
                    var html;
                    html += '<div class="edit_design__loading"><div class="edit_design__loading-icon"><iframe src="/resource/template/frontend/default2/image/loading.svg" width="100%" height="100%" frameborder="0"></iframe></div>';
                    $('body').append(html);
                },
                success: function (response) {
                    btn.removeAttr('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    var modal = $('<div />').addClass('osc-modal').width(450);

                    var header = $('<header />').appendTo(modal);

                    $('<div />').addClass('title').html('Edit personalized design').appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('personalizeDesignEditor');
                    }).appendTo(header);

                    var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

                    modal_body.html(response.data.form);

                    $.wrapContent(modal, {key: 'personalizeDesignEditor'});

                    modal.moveToCenter().css('top', '100px');

                    modal_body.find('form').unbind('submit').submit(function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.stopPropagation();

                        var form_data = $(this).serializeObject();

                        if (form_data === null || typeof form_data !== 'object') {
                            alert('Some data is incorrect, please contact to our supporter to solve the issue');
                        }

                        var submit_btn = $(this).find('button[type="submit"]');

                        if (submit_btn.attr('disabled') === 'disabled') {
                            return;
                        }

                        submit_btn.attr('disabled', 'disabled');

                        form_data.order = btn.attr('data-order');
                        form_data.item = btn.attr('data-line-item');

                        $.ajax({
                            url: $.base_url + '/personalizedDesign/frontend/edit',
                            type: 'post',
                            data: form_data,
                            success: function (response) {
                                submit_btn.removeAttr('disabled', 'disabled');

                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }

                                $.unwrapContent('personalizeDesignEditor');

                                window.location.reload();
                            }
                        });
                    });
                },
                complete: function () {
                    $('.edit_design__loading').remove();
                }
            });
        });
    };

    window.orderPersonalizedDesignSemiTestEdit = function () {
        $(this).click(function () {
            var btn = $(this);

            if (btn.attr('disabled') === 'disabled') {
                return;
            }

            btn.attr('disabled', 'disabled');

            $.ajax({
                url: $.base_url + '/personalizedDesign/frontend/editSemiTest',
                type: 'post',
                data: {
                    order: btn.attr('data-order'),
                    item: btn.attr('data-line-item')
                },
                beforeSend: function () {
                    var html;
                    html += '<div class="edit_design__loading"><div class="edit_design__loading-icon"><iframe src="/resource/template/frontend/default2/image/loading.svg" width="100%" height="100%" frameborder="0"></iframe></div>';
                    $('body').append(html);
                },
                success: function (response) {
                    btn.removeAttr('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    var modal = $('<div />').addClass('osc-modal').width(450);

                    var header = $('<header />').appendTo(modal);

                    $('<div />').addClass('title').html('Edit personalized design').appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('personalizeDesignSemiTestEditor');
                    }).appendTo(header);

                    var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

                    modal_body.html(response.data.form);

                    $.wrapContent(modal, {key: 'personalizeDesignSemiTestEditor'});

                    modal.moveToCenter().css('top', '100px');

                    modal_body.find('form').unbind('submit').submit(function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.stopPropagation();

                        var form_data = $(this).serializeObject();

                        if (form_data === null || typeof form_data !== 'object') {
                            alert('Some data is incorrect, please contact to our supporter to solve the issue');
                        }

                        var submit_btn = $(this).find('button[type="submit"]');

                        if (submit_btn.attr('disabled') === 'disabled') {
                            return;
                        }

                        submit_btn.attr('disabled', 'disabled');

                        form_data.order = btn.attr('data-order');
                        form_data.item = btn.attr('data-line-item');

                        $.ajax({
                            url: $.base_url + '/personalizedDesign/frontend/editSemiTest',
                            type: 'post',
                            data: form_data,
                            success: function (response) {
                                submit_btn.removeAttr('disabled', 'disabled');

                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }

                                $.unwrapContent('personalizeDesignSemiTestEditor');

                                window.location.reload();
                            }
                        });
                    });
                },
                complete: function () {
                    $('.edit_design__loading').remove();
                }
            });
        });
    };

    function _initFlexibleMugSizeChangeListener() {
        $(document.body).unbind('personalizedFlexibleMugSizeChanged').bind('personalizedFlexibleMugSizeChanged', function (e, mug_size, component) {
            const container = component.parents('.catalog-cart-frm__container');
            const mug_size_price = JSON.parse(container.find('script[data-json="flexible_mug_size_price"]')[0].innerHTML);

            let price = 0;

            if (mug_size_price['size_' + mug_size] !== undefined) {
                price = mug_size_price['size_' + mug_size];
            } else if (mug_size_price['size_dpi' + mug_size] !== undefined) {
                price = mug_size_price['size_dpi' + mug_size];
            }

            container.parent().find('.product_price_money').not('.product_price_money--original').html(catalogFormatPriceByInteger(price, 'html_with_currency'));
            container.find('input[name=flexible_mug_size_price]').val(price);
        });
    }

    window._validator_input = _validator_input;
    window._validator_imageUploader = _validator_imageUploader;
    window._validator_imageSelector = _validator_imageSelector;
    window._validator_imageGroupSelector = _validator_imageGroupSelector;
    window._validator_switcherByImage = _validator_switcherByImage;
    window._validator_switcherBySelect = _validator_switcherBySelect;
})(jQuery);