(function ($) {
    'use strict';

    window.crossSellRenderDynamicMockupAuto = function () {
        const container = $(this);
        const opacity_image_mockup = container.attr('data-mockup-opacity');
        const image_mockup_url = container.attr('data-image-url');

        if (opacity_image_mockup === undefined) {
            opacity_image_mockup = 0;
        }

        container.click(function () {
            $.unwrapContent('campaign_design_preview');

            const modal = $('<div />').addClass('osc-modal').css('top', '5vh');
            let header = $('<header />').appendTo(modal);

            $('html').css('overflow', 'hidden');
            $('<div />').addClass('title').html('Preview').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />')
                .addClass('close-btn')
                .click(function () {
                    $.unwrapContent('campaign_design_preview');
                    $('html').removeAttr('style');
                })
                .appendTo(header);

            let modal_body = $('<div />').addClass('campaign-preview').appendTo(modal);
            $('<img />').attr('src', image_mockup_url).css('height', '100%').appendTo(modal_body);

            $.wrapContent(modal, {
                key: 'campaign_design_preview',
                close_callback: function () {
                    $('html').removeAttr('style');
                },
            });
            modal.moveToCenter();
        });
    };

    window.crossSellPreviewMockup = function () {
        const container = $(this);
        let design = container.attr('data-design');
        let preview_config = container.attr('data-preview-config');

        if (design === '0' || preview_config === '0') {
            return null;
        }

        design = JSON.parse(design);
        preview_config = JSON.parse(preview_config);

        function renderPreview(wrapper) {
            const layerSize = {
                width: wrapper.width(),
                height: wrapper.width(),
            };
            const scaleX = layerSize.width / preview_config.dimension.width;
            const scaleY = layerSize.height / preview_config.dimension.height;
            const imageWidth = 100;
            const imageHeight = 100;
            wrapper.css({
                position: 'relative',
            });
            const content = $('<div />').css({ position: 'relative' }).appendTo(wrapper);
            preview_config.layer?.map((src) => {
                if (src !== 'main') {
                    return $('<img />').attr('src', src).appendTo(content);
                }
                const top = preview_config.config.front.position.y * scaleY;
                const left = preview_config.config.front.position.x * scaleX;
                const width = preview_config.config.front.dimension.width * scaleX;
                const height = preview_config.config.front.dimension.height * scaleY;
                const imageStyle = {
                    width: `${imageWidth}%`,
                    height: `${imageHeight}%`,
                    left: `${50 - imageWidth / 2}%`,
                    top: `${50 - imageHeight / 2}%`,
                };
                const imageContainer = $('<div />').appendTo(content);
                const imageContent = $('<img />')
                    .attr('src', design.link_thumbnail || design.link)
                    .css({
                        ...imageStyle,
                    });
                imageContainer.css({
                    position: 'absolute',
                    top,
                    left,
                    width,
                    height,
                });
                return imageContent.appendTo(imageContainer);
            });
        }

        renderPreview(container);
        $('<div />')
            .addClass('campaign-design-preview-btn')
            .css({ 'text-align': 'left' })
            .append(
                $('<span />')
                    .append($.renderIcon('search-plus'))
                    .append('Preview design')
                    .click(function () {
                        $.unwrapContent('campaign_design_preview');

                        const modal = $('<div />').addClass('osc-modal').css('top', '5vh');
                        let header = $('<header />').appendTo(modal);

                        $('html').css('overflow', 'hidden');
                        $('<div />').addClass('title').html('Preview').appendTo($('<div />').addClass('main-group').appendTo(header));

                        $('<div />')
                            .addClass('close-btn')
                            .click(function () {
                                $.unwrapContent('campaign_design_preview');
                                $('html').removeAttr('style');
                            })
                            .appendTo(header);

                        let modal_body = $('<div />').addClass('campaign-preview').appendTo(modal);
                        setTimeout(() => {
                            renderPreview(modal_body);
                        }, 0);

                        $.wrapContent(modal, {
                            key: 'campaign_design_preview',
                            close_callback: function () {
                                $('html').removeAttr('style');
                            },
                        });
                        modal.moveToCenter();
                        setTimeout(() => {
                            modal.moveToCenter();
                        }, 0);
                    })
            )
            .appendTo(container);
    };
})(jQuery);
