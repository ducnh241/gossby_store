(function ($) {
    'use strict';

    window.catalogProductRenderPrice = function (variant, display_saving) {
        const inp_campaign_option = $("input[name*='custom[campaign][option][type]']")
        const type_price_other_option = inp_campaign_option.attr('price');
        const type_price_compare_other_option = inp_campaign_option.attr('price_compare');

        if (typeof type_price_other_option !== "undefined") {
            variant.price = parseInt(type_price_other_option);
        }

        if (typeof type_price_compare_other_option !== "undefined") {
            variant.compare_at_price = parseInt(type_price_compare_other_option);
        }

        var container = $('<div />').addClass('product_item_price');
        if (!variant.available) {
            $('<span />').addClass('product_price_money product_price--sold_out').text('Sold out').appendTo(container);
        } else {
            if (variant.compare_at_price > variant.price) {
                $('<span />').addClass('product_price_money').html($('<span />').html(catalogFormatPriceByInteger(variant.price, 'html_with_currency'))).appendTo(container);

                $('<span />').addClass('product_price_money product_price_money--original').html($('<span />').html(catalogFormatPriceByInteger(variant.compare_at_price, 'html_with_currency'))).appendTo(container);

                if (display_saving && variant.compare_at_price > variant.price) {
                    var saving_price = variant.compare_at_price - variant.price;
                    var saving_percent = $.round((saving_price * 100.0) / variant.compare_at_price, 0);
                    if ($('.product-gallery__images').has('.product_item_saleoff')) {
                        $('.product-gallery__images').find('.product_item_saleoff').remove();
                    }
                    $('<div />').addClass('product_item_saleoff').html(saving_percent + "%<br />off").appendTo($('.product-gallery__images'));
                }

            } else {
                $('<span />').addClass('product_price_money').html(catalogFormatPriceByInteger(variant.price, 'html_with_currency')).appendTo(container);
                $('.product-gallery__images').find('.product_item_saleoff').remove();
            }
        }

        return container[0];
    };

    window.campaignRenderPreview = function (container, mockup_config) {
        let preview_config = typeof mockup_config.preview_config !== "undefined" ? mockup_config.preview_config : {},
            segment_configs = typeof mockup_config.segment_configs !== "undefined" ? mockup_config.segment_configs : {},
            segment_sources = typeof mockup_config.segment_sources !== "undefined" ? mockup_config.segment_sources : {},
            design_data = typeof mockup_config.design !== "undefined" ? mockup_config.design : {},
            mockup_width = container.width();

        mockup_width = mockup_width < preview_config.dimension.width ? mockup_width : preview_config.dimension.width

        let mockup_height = preview_config.dimension.height * (container.width() / preview_config.dimension.width);
        mockup_height = mockup_height < preview_config.dimension.height ? mockup_height : preview_config.dimension.height

        let mockup = $('<div />').addClass('campaign-dynamic-mockup').attr({
            'data-type': mockup_config.product_type,
            'data-design': mockup_config.design_key
        }).css({
            'width': mockup_width + 'px',
            'height': mockup_height + 'px',
        }).appendTo(container);

        function _renderPreview() {
            if (preview_config.layer && preview_config.layer.length > 0) {
                $.each(preview_config.layer, function (key, item) {
                    if (item !== "main") {
                        $('<div />').addClass('layer')
                            .append($('<img />').attr('src', item))
                            .appendTo(mockup)
                    } else {
                        let _ratio = mockup[0].getBoundingClientRect().width / preview_config.dimension.width;

                        $.each(preview_config.config, function (segment_key, config) {
                            let main = $('<div />').addClass('design-main').appendTo(mockup)
                            main.css({
                                width: (config.dimension.width * _ratio) + 'px',
                                height: (config.dimension.height * _ratio) + 'px',
                                top: (config.position.y * _ratio) + 'px',
                                left: (config.position.x * _ratio) + 'px'
                            });
                            let segment_elm = $('<div />').addClass('segment').appendTo(main),
                                img_container = $('<div />').addClass('img').css({
                                    width: ((segment_sources[segment_key].source.dimension.width / segment_configs[segment_key].dimension.width) * 100) + '%',
                                    height: ((segment_sources[segment_key].source.dimension.height / segment_configs[segment_key].dimension.height) * 100) + '%',
                                    top: ((segment_sources[segment_key].source.position.y / segment_configs[segment_key].dimension.height) * 100) + '%',
                                    left: ((segment_sources[segment_key].source.position.x / segment_configs[segment_key].dimension.width) * 100) + '%',
                                    transform: 'rotate(' + (segment_sources[segment_key].source.rotation ? segment_sources[segment_key].source.rotation : 0) + 'deg)'
                                }).appendTo(segment_elm);

                            if (typeof design_data.svg !== 'undefined') {
                                img_container.html(design_data.svg);
                                img_container.find('svg')[0].setAttribute('preserveAspectRatio', 'none');
                            } else {
                                $('<img />').attr('src', design_data.url).appendTo(img_container);
                            }
                        });
                    }
                })
            }
        }

        new ResizeSensor(container[0], function () {
            _renderPreview()
        });

        _renderPreview()
    };

    window.campaignRenderDynamicMockup = function (container, mockup_config) {
        let preview_config = typeof mockup_config.preview_config !== "undefined" ? mockup_config.preview_config : {}
        let segment_configs = typeof mockup_config.segment_configs !== "undefined" ? mockup_config.segment_configs : {}
        let segment_sources = typeof mockup_config.segment_sources !== "undefined" ? mockup_config.segment_sources : {}
        let design_data = typeof mockup_config.design !== "undefined" ? mockup_config.design : {}

        let mockup = $('<div />').addClass('campaign-dynamic-mockup').attr({
            'data-type': mockup_config.product_type,
            'data-design': mockup_config.design_key
        }).css({
            width: '100%',
            height: '100%',
            position: 'absolute',
            top: 0
        }).appendTo(container);

        function _renderPreview() {
            if (preview_config.layer && preview_config.layer.length > 0) {
                $.each(preview_config.layer, function (key, item) {
                    if (item !== "main") {
                        $('<div />').addClass('layer')
                            .append($('<img />').attr('src', item))
                            .appendTo(mockup)
                    } else {
                        let _ratio = container[0].getBoundingClientRect().width / preview_config.dimension.width;

                        $.each(preview_config.config, function (segment_key, config) {
                            let main = $('<div />').addClass('design-main').appendTo(mockup)
                            main.css({
                                width: (config.dimension.width * _ratio) + 'px',
                                height: (config.dimension.height * _ratio) + 'px',
                                top: (config.position.y * _ratio) + 'px',
                                left: (config.position.x * _ratio) + 'px'
                            });
                            let segment_elm = $('<div />').addClass('segment').appendTo(main),
                                img_container = $('<div />').addClass('img').css({
                                    width: ((segment_sources[segment_key].source.dimension.width / segment_configs[segment_key].dimension.width) * 100) + '%',
                                    height: ((segment_sources[segment_key].source.dimension.height / segment_configs[segment_key].dimension.height) * 100) + '%',
                                    top: ((segment_sources[segment_key].source.position.y / segment_configs[segment_key].dimension.height) * 100) + '%',
                                    left: ((segment_sources[segment_key].source.position.x / segment_configs[segment_key].dimension.width) * 100) + '%',
                                    transform: 'rotate(' + (segment_sources[segment_key].source.rotation ? segment_sources[segment_key].source.rotation : 0) + 'deg)'
                                }).appendTo(segment_elm);

                            if (typeof design_data.svg !== 'undefined') {
                                img_container.html(design_data.svg);
                                img_container.find('svg')[0].setAttribute('preserveAspectRatio', 'none');
                            } else {
                                $('<img />').attr('src', design_data.url).appendTo(img_container);
                            }
                        });
                    }
                })
            }
        }

        new ResizeSensor(container[0], function () {
            _renderPreview();
        });

        _renderPreview();
    };

    window.campaignRenderDynamicMockupAuto = function () {
        var container = $(this);
        let opacity_image_mockup = container.attr('data-mockup-opacity');

        if (opacity_image_mockup === undefined) {
            opacity_image_mockup = 0;
        }

        $.ajax({
            url: container.attr('data-mockup-type') === 'product' ? container.attr('data-mockup-resource') : $.base_url + '/catalog/campaign/getMockupData',
            type: container.attr('data-mockup-type') === 'product' ? 'get' : 'post',
            async: false,
            data: container.attr('data-mockup-type') === 'product' ? null : {id: container.attr('data-mockup-resource'), type: container.attr('data-mockup-type')},
            success: function (response) {
                let design_counter = 0
                let mockup_config = null

                if (container.attr('data-mockup-type') === 'product') {
                    mockup_config = response;
                    design_counter = 1;
                } else {
                    if (response.result !== 'OK') {
                        console.error('Campaign mockup :: ' + response.message);
                        return;
                    }

                    $.each(response.data, function (design_key, config) {
                        if (mockup_config === null) {
                            mockup_config = config;
                        }
                        design_counter++;
                    });
                }

                let class_campaign_design_list = 'campaign-design-list';

                if(opacity_image_mockup == 1) {
                    class_campaign_design_list += ' campaign-design-list-error'
                }

                let panel = $('<div />').addClass(class_campaign_design_list).css({
                    opacity: opacity_image_mockup == 1 ? 0.3 : 1
                }).appendTo(container)

                if (design_counter > 1 && parseInt(container.attr('data-list')) === 1) {
                    $.each(response.data, function (design_key, config) {
                        campaignRenderDynamicMockup($('<div />').attr({
                            'data-design': design_key,
                            'class': 'campaign-design-item'
                        }).appendTo(panel), config)
                    });
                } else {
                    campaignRenderDynamicMockup($('<div />').attr({
                        'data-design': mockup_config.design_key,
                        'class': 'campaign-design-item'
                    }).appendTo(panel), mockup_config)
                }

                if (container.attr('data-mockup-type') === 'product') {
                    return
                }

                if (opacity_image_mockup != 1) {
                    $('<span />').attr('data-design', mockup_config.design_key).text('Preview design').prepend($.renderIcon('search-plus')).appendTo($('<div />').addClass('campaign-design-preview-btn').appendTo(container));
                }

                container.find('[data-design]').click(function () {
                    if (opacity_image_mockup == 1) {
                        return
                    }

                    var design_key = this.getAttribute('data-design');

                    $.unwrapContent('campaign_design_preview');

                    var modal = $('<div />').addClass('osc-modal');

                    var header = $('<header />').appendTo(modal);

                    $('html').css('overflow','hidden');

                    $('<div />').addClass('title').html('Preview').appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('campaign_design_preview');
                        $('html').removeAttr('style')
                    }).appendTo(header);

                    var modal_body = $('<div />').addClass('campaign-preview').appendTo(modal);

                    $.wrapContent(modal, {key: 'campaign_design_preview', close_callback: function () {
                        $('html').removeAttr('style');
                    }});
                    
                    let currentHightlightClass = 'active-0',index =0;

                    var design_tabs = $('<div />').addClass('design-tabs').appendTo(modal_body);
                    const highlight = $('<span />').addClass('highlight').addClass(currentHightlightClass);
                    
                    design_tabs.append(highlight);

                    var design_scene = $('<div />').addClass('design-scene').appendTo(modal_body);

                    $.each(response.data, function (design_key, mockup_config) {
                        const currentIndex = index;
                        $('<div />').text(typeof mockup_config.title === 'undefined' ? '' : mockup_config.title).attr('data-design', design_key).appendTo(design_tabs).click(function () {
                            highlight.removeClass(currentHightlightClass);
                            currentHightlightClass = `active-${currentIndex}`
                            highlight.addClass(currentHightlightClass);

                            design_tabs.find('> *').removeClass('activated');
                            $(this).addClass('activated');
                            design_scene.html('');
                            design_scene.height(0);

                            const layerRatio = mockup_config.preview_config.dimension.width / mockup_config.preview_config.dimension.height;
                            const modalWidth = (window.innerHeight - modal.innerHeight()) * layerRatio;
                            
                            modal_body.width(modalWidth)

                            design_scene.height(design_scene.width())
                            campaignRenderDynamicMockup(design_scene, mockup_config);
                            modal.moveToCenter();
                        });
                        index +=1;
                    });

                    design_scene.swipe({
                        swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                            design_tabs.find('div:not(.activated)').trigger('click');
                        },
                        threshold:0
                    });

                    if (design_tabs[0].childNodes.length < 2) {
                        design_tabs.hide();
                        design_scene.css('margin-bottom', 24)
                    } else {
                        design_tabs.show();
                    }

                    design_tabs.find('> [data-design="' + design_key + '"]').trigger('click');
                });
            }
        });
    };
})(jQuery);