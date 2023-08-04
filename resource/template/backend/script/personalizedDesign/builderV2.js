(function ($) {
    'use strict';
    var PSD = require('psd');

    const SPECIAL_PSD_CHAR = '*';

    SVGElement.prototype.getTransformToElement = SVGElement.prototype.getTransformToElement || function (elem) {
        return elem.getScreenCTM().inverse().multiply(this.getScreenCTM());
    };

    const PERSONALISED_DESIGN_CLIPBOARD = "personalized_design_clipboard";
    const PERSONALISED_ATTRIBUTES_CLIPBOARD = "personalized_attributes_clipboard";

    const MIN_LENGTH_INPUT_PERSONALIZE_DEFAULT = 1
    const MAX_LENGTH_INPUT_PERSONALIZE_DEFAULT = 12

    const toThumbUrl = (url) => $.getImgStorageUrl(url.replace(/^(.+)\.([a-zA-Z0-9]+)$/, '$1.thumb.$2'));
    const getUniqId = () => {
        const ts = `${(new Date()).getTime()}`.substring(3,13);
        const map = [
            ['0','a','b'],
            ['1','c','d'],
            ['2','e','f'],
            ['3','g','h'],
            ['4','i','j'],
            ['5','k','l'],
            ['6','m','n'],
            ['7','o','p'],
            ['8','q','r'],
            ['9','s','t'],
        ]
        let id = ''
        for (let i = 0; i < ts.length; i++) {
            const n = Number(ts.charAt(i))
            const arr = map[n];
            id += arr[Math.floor(Math.random() * arr.length)];
        }
        return id
    }
    class personalizedDesignBuilder {

        _toThumbUrl(url) {
            return this._getLocalImgStorageUrl(url.replace(/^(.+)\.([a-zA-Z0-9]+)$/, "$1.thumb.$2"));
        }

        _renderDocumentLine() {
            let render_line_callback = '_documentTypeRenderLine_' + this.document_type.key;

            if (typeof this[render_line_callback] === 'function') {
                render_line_callback = this[render_line_callback]();
            }

            const width = this.document_type.width;
            const height = this.document_type.height;

            const custom_document_safe_area_width = this.document_type.custom_document_safe_area_width || null;
            const custom_document_safe_area_height = this.document_type.custom_document_safe_area_height || null;

            if (typeof render_line_callback === 'function') {
                if (custom_document_safe_area_width !== null) {
                    render_line_callback(width, height, custom_document_safe_area_width, custom_document_safe_area_height);
                }else{
                    render_line_callback(width, height);
                }
            }
        }

        //Render tấm nền phía dưới
        _renderFrame(document_type) {
            this.document_type = document_type;

            if (this._zoom_ratio === null) {

                this._zoom_ratio = 1;

                var filter = $(document.createElementNS(this._svg[0].namespaceURI, 'filter'));
                filter[0].setAttribute('id', 'bg-dropshadow');
                filter[0].setAttribute('height', '130%');

                filter.append($(document.createElementNS(this._svg[0].namespaceURI, 'feGaussianBlur')).attr({'in': 'SourceAlpha', 'stdDeviation': '3'}));
                filter.append($(document.createElementNS(this._svg[0].namespaceURI, 'feOffset')).attr({'dx': '2', 'dy': '2', 'result': 'offsetblur'}));
                filter.append($(document.createElementNS(this._svg[0].namespaceURI, 'feComponentTransfer')).append($(document.createElementNS(this._svg[0].namespaceURI, 'feFuncA')).attr({'type': 'linear', 'slope': '0.2'})));
                filter.append($(document.createElementNS(this._svg[0].namespaceURI, 'feMerge')).append($(document.createElementNS(this._svg[0].namespaceURI, 'feMergeNode'))).append($(document.createElementNS(this._svg[0].namespaceURI, 'feMergeNode')).attr({'in': 'SourceGraphic'})));

                filter.appendTo(this._svg_group.defs);

                this._frame = $(document.createElementNS(this._svg[0].namespaceURI, 'g')).insertBefore(this._svg_group.object);
            } else {
                this._frame.empty();
            }

            this.background = $(document.createElementNS(this._svg[0].namespaceURI, 'rect')).attr({
                x: 0,
                y: 0,
                fill: '#fff',
                stroke: 'none',
                filter: 'url(#bg-dropshadow)',
                width: this.document_type.width,
                height: this.document_type.height
            }).appendTo(this._frame);

            this._renderDocumentLine()
        }

        _getDefaultLineStrokeAttrs() {
            const zoom_ratio = this._zoom_ratio;

            return {
                'stroke-width': 1 / zoom_ratio,
                'stroke-dasharray': `${3 / zoom_ratio}px ${2 / zoom_ratio}px`
            }
        }

        _documentTypeRenderLine_design2d() {
            var $this = this;

            var lines = {};

            for (var i = 1; i < 10; i++) {
                lines['vertical_' + i] = $(document.createElementNS(this._svg[0].namespaceURI, 'path'));
                lines['horizon_' + i] = $(document.createElementNS(this._svg[0].namespaceURI, 'path'));
            }

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#eaeaea',
                    ...$this._getDefaultLineStrokeAttrs()
                }).appendTo($this._frame);
            });

            return function (width, height) {
                for (var i = 1; i < 10; i++) {
                    lines['vertical_' + i].attr('d', 'M' + (width * i / 10) + ',0 L' + (width * i / 10) + ',' + height);
                    lines['horizon_' + i].attr('d', 'M0,' + (height * i / 10) + ' L' + width + ',' + (height * i / 10));
                }
            };
        }

        _documentTypeRenderLine_canvas_square() {
            var $this = this;

            var line = $(document.createElementNS(this._svg[0].namespaceURI, 'path'));

            line.attr({
                fill: 'none',
                stroke: '#20b3f8',
                ...$this._getDefaultLineStrokeAttrs(),
                opacity: .5
            }).appendTo($this._frame);

            return function (width, height) {
                var space_ratio = 0.1520730646564222;

                var space_width = width * space_ratio;
                var space_height = height * space_ratio;

                line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
            };
        }

        _documentTypeRenderLine_canvas_portrait() {
            var $this = this;

            var lines = {
                _4656x5935: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _6000x7200: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _5964x8976: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _3543x7133: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._6000x7200.attr('stroke', '#25d325');
            lines._5964x8976.attr('stroke', '#0033bb');
            lines._3543x7133.attr('stroke', '#0033bb');

            return function (width, height) {
                var _space_ratio = {
                    _4656x5935: {w: 0.2974242951618517, h: 0.2956548684754166},
                    _6000x7200: {w: 0.2389488339714584, h: 0.252100261671946},
                    _5964x8976: {w: 0.2405151409676297, h: 0.190951659551026},
                    _3543x7133: {w: 0.3458492864601462, h: 0.254407106459165}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_canvas_landscape() {
            var $this = this;

            var lines = {
                _5935x4656: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _7200x6000: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _8976x5964: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _7133x3543: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._7200x6000.attr('stroke', '#25d325');
            lines._8976x5964.attr('stroke', '#0033bb');
            lines._7133x3543.attr('stroke', '#0033bb');

            return function (width, height, ratio) {
                var _space_ratio = {
                    _5935x4656: {h: 0.2974242951618517, w: 0.2956548684754166},
                    _7200x6000: {h: 0.2389488339714584, w: 0.252100261671946},
                    _8976x5964: {h: 0.2405151409676297, w: 0.190951659551026},
                    _7133x3543: {h: 0.3458492864601462, w: 0.254407106459165}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_desktopPlaque_landscape() {
            var $this = this;

            var lines = {
                _2916x2320: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _2850x2006: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#0033bb',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._2850x2006.attr('stroke', '#25d325');

            return function (width, height) {
                var _space_ratio = {
                    _2916x2320: {h: 0.0313131313131313131313131313131, w: 0.0258536585365853658536585365853},
                    _2850x2006: {h: 0.0947474747474747474747474747474, w: 0.0365853658536585365853658536585}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_fleeceBlanket_landscape() {
            var $this = this;

//                _4076x5576: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
//                _4832x5880: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
//                _5628x7636: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))

            var lines = {
                _5176x7080: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _5702x6948: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _5628x7636: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._5702x6948.attr('stroke', '#25d325');
            lines._5628x7636.attr('stroke', '#0033bb');

            return function (width, height) {
                var _space_ratio = {
                    _5176x7080: {w: 0.0939755255726388, h: 0.0818568391211906},
                    _5702x6948: {w: 0.0527141512394101, h: 0.089652728561304},
                    _5628x7636: {w: 0.0585189833699404, h: 0.0490196078431373}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_puzzles_portrait() {
            var $this = this;

            var lines = {
                _2973x3834: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _2973x3834: {h: 0.0638225255972696, w: 0.0691304347826087}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_puzzles_landscape() {
            var $this = this;

            var lines = {
                _3834x2973: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _3834x2973: {w: 0.0638225255972696, h: 0.0691304347826087}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_facemask_dpi() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/facemask/dpi.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_facemask_teeallover() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/facemask/teeallover.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_facemask_dpi_kid() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/facemask/dpi_kid.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_facemask_cw() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/facemask/cw.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_aluminium_square_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/aluminiumSquare.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_aluminium_medallion_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/aluminiumMedallion.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_aluminium_scalloped_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/aluminiumScalloped.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_circle_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/circle_ornament.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_heart_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/heart_ornament.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_aluminium_circle_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/aluminium_circle_ornament.png');

            $(image).appendTo(this._frame);

            return function (width, height, ratio) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_aluminium_heart_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/aluminium_heart_ornament.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_acrylic_medallion_ornament() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/ornament/acrylic_ornament.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_pillow() {
            var $this = this;

            var lines = {
                _2628x2628: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _2025x2025: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _2628x2628: {w: 0.0507692307692308, h: 0.0507692307692308},
                    _2025x2025: {w: 0.1538461538461538, h: 0.1538461538461538}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_notebook() {
            var $this = this;

            var lines = {
                _1364x2058: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _1364x2058: {w: 0.0891566265060240963855421686747, h: 0.01870907390084190832553788587465}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_yard_sign() {
            var $this = this;

            var lines = {
                _7200x5400: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _7200x5400: {w: 0.0027624309392265, h: 0.0036764705882353}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_garden_flag() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/gardenFlag/8x12.5.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_t_shirt() {
            var $this = this;

            var lines = {
                _4200x4800: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _4200x4800: {w: 1, h: 1}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_hoodie_14x10() {
            var $this = this;

            var lines = {
                _4200x3000: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _4200x3000: {w: 1, h: 1}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_poster_landscape() {
            var $this = this;

            var lines = {
                _5906x4906: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _5896x4695: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _3906x3002: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._5906x4906.attr('stroke', '#25d325');
            lines._5896x4695.attr('stroke', '#0033bb');
            lines._3906x3002.attr('stroke', '#0033bb');

            return function (width, height) {
                var _space_ratio = {
                    _5906x4906: {h: 0.0094, w: 0.0078333333333333},
                    _5896x4695: {h: 0.0305, w: 0.0078333333333333},
                    _3906x3002: {h: 0.1998, w: 0.1745}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_poster_portrait() {
            var $this = this;

            var lines = {
                _4906x5906: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _4695x5896: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                _3002x3906: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._4906x5906.attr('stroke', '#25d325');
            lines._4695x5896.attr('stroke', '#0033bb');
            lines._3002x3906.attr('stroke', '#0033bb');

            return function (width, height) {
                var _space_ratio = {
                    _4906x5906: {h: 0.0078333333333333, w: 0.0094},
                    _4695x5896: {h: 0.0078333333333333, w: 0.0305},
                    _3002x3906: {h: 0.1745, w: 0.1998}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_necklace() {
            var $this = this;

            var lines = {
                _860x860: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._860x860.attr('stroke', '#25d325');

            return function (width, height) {
                var _space_ratio = {
                    _860x860: {h: 0.07, w: 0.07},
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_iphone_case_7() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/phonecase/iphone7.png');

            $(image).appendTo(this._frame);

            return function (width, height, ratio) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_iphone_case_x() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/phonecase/iphoneX.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_iphone_case_12() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/phonecase/iphone12.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_samsung_case_s10() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/phonecase/s10plus.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_key_chain_29x51() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/keyChain/29x51.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_key_chain_30x60() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/keyChain/30x60.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_flat_card_7x5() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/flatCard/7x5.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_flat_card_5x7() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/flatCard/5x7.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_key_chain_51x29() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/keyChain/51x29.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_samsung_case_s20() {
            var image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/phonecase/s20_s21.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_doorMat_24x16() {
            var $this = this;

            var lines = {
                _7200x4800: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._7200x4800.attr('stroke', '#25d325');

            return function (width, height) {
                var _space_ratio = {
                    _7200x4800: {h: 0.02625, w:0.0195833333333333},
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_doorMat_30x18() {
            var $this = this;

            var lines = {
                _9000x5400: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._9000x5400.attr('stroke', '#25d325');

            return function (width, height) {
                var _space_ratio = {
                    _9000x5400: {h: 0.0266666666666667, w:0.0186666666666667},
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_beach_towel() {
            var $this = this;

            var lines = {
                _9072x17390: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._9072x17390.attr('stroke', '#25d325');

            return function (width, height) {
                var _space_ratio = {
                    _9072x17390: {h: 0.0544694170771757, w: 0.0968360071301248},
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_tea_towel() {
            var $this = this;

            var lines = {
                _5374x8102: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#000',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            lines._5374x8102.attr('stroke', '#25d325');

            return function (width, height) {
                var _space_ratio = {
                    _5374x8102: {h: 0.0665267835102444, w: 0.0854112393003349},
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_tumbler_stainless() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/mug/stainless_tumbler.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_tumbler_skinny() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/mug/skinny_tumbler.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_custom(){
            var $this = this;

            var lines = {
                custom: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#25d325',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height, custom_document_safe_area_width , custom_document_safe_area_height) {

                var _space_ratio = {
                    custom: {w:((width-custom_document_safe_area_width)/2)/width, h:((height-custom_document_safe_area_height)/2)/height}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_insulatedcoffeemug12oz() {
            return this._documentTypeRenderLine_mug();
        }

        _documentTypeRenderLine_enamelcampfinemug10oz() {
            return this._documentTypeRenderLine_mug();
        }

        _documentTypeRenderLine_mug11oz() {
            return this._documentTypeRenderLine_mug();
        }

        _documentTypeRenderLine_wineGlassMug15oz() {
            var $this = this;

            var lines = {
                _900x650: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: 'red',
                    ...$this._getDefaultLineStrokeAttrs(),
                    opacity: .5
                }).appendTo($this._frame);
            });

            return function (width, height) {
                var _space_ratio = {
                    _900x650: {h: 1, w: 1}
                };

                $.each(lines, function (k, line) {
                    var space_width = width * _space_ratio[k].w;
                    var space_height = height * _space_ratio[k].h;

                    line.attr('d', 'M' + space_width + ',' + space_height + ' L' + (width - space_width) + ',' + space_height + ' L' + (width - space_width) + ',' + (height - space_height) + ' L' + space_width + ',' + (height - space_height) + ' L' + space_width + ',' + space_height);
                });
            };
        }

        _documentTypeRenderLine_mug() {
            var $this = this;

            var lines = {
                left_vertical: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                right_vertical: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                center_vertical: $(document.createElementNS(this._svg[0].namespaceURI, 'path')),
                horizontal: $(document.createElementNS(this._svg[0].namespaceURI, 'path'))
            };

            $.each(lines, function (k, line) {
                line.attr({
                    fill: 'none',
                    stroke: '#eaeaea',
                    ...$this._getDefaultLineStrokeAttrs(),
                }).appendTo($this._frame);
            });

            return function (width, height) {
                lines.left_vertical.attr('d', 'M' + (width * .209375) + ',0' + ' L' + (width * .209375) + ',' + height);
                lines.right_vertical.attr('d', 'M' + (width * 0.790625) + ',0' + ' L' + (width * 0.790625) + ',' + height);
                lines.center_vertical.attr('d', 'M' + (width * .5) + ',' + (height * .75) + ' L' + (width * .5) + ',' + height);
                lines.horizontal.attr('d', 'M0,' + (height * .5) + ' L' + width + ',' + (height * .5));
            };
        }

        _documentTypeRenderLine_watchband_38mm_42mm_horizontal() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/watchband/wactband_horizontal.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }


        _documentTypeRenderLine_watchband_38mm_42mm() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/watchband/watchband.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_desktop_plaque_acrylic_heart() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/desktop_plaque/acrylic/heart.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_desktop_plaque_acrylic_infinity() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/desktop_plaque/acrylic/infinity.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_desktop_plaque_acrylic_puzzle() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/desktop_plaque/acrylic/puzzle.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _documentTypeRenderLine_desktop_plaque_wooden_puzzle() {
            let image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $.base_url + '/resource/template/backend/image/personalizedDesign/desktop_plaque/wooden/puzzle.png');

            $(image).appendTo(this._frame);

            return function (width, height) {
                image.setAttribute('width', width);
                image.setAttribute('height', height);
            };
        }

        _renderTopBar() {
            this._top_bar = $('<div />').addClass('builder-topbar').appendTo(this._container);

            this._config_panel = $('<div />').addClass('config-panel').appendTo(this._top_bar)[0];
        }

        _renderBottomBar() {
            var $this = this;

            this._bottom_bar = $('<div />').addClass('builder-bottombar').appendTo(this._container);

            var container = $('<div />').addClass('zoom-bar').appendTo(this._bottom_bar);
            $('<span />').text('Zoom').appendTo(container);
            var modifier = $('<div />').addClass('modifier').appendTo(container);
            $('<div />').addClass('minus').appendTo(modifier);
            var input = $('<input />').attr({type: 'text', value: this._zoom_ratio * 100}).appendTo(modifier);
            $('<div />').addClass('plus').appendTo(modifier);

            input.bind('input', function () {
                const view_box = $this._viewBox();
                const val = input.val();
                let new_ratio = (Number(val)/ 100);
                if (!val || new_ratio <= 0 || isNaN(new_ratio))
                    return
                const new_width = $this._svg_size.w / new_ratio;
                const dw = view_box.width - new_width;
                const scale = dw / view_box.width
                // let scale = $this._zoom_ratio / new_ratio ;
                // scale *= new_ratio < $this._zoom_ratio ? - 1 : 1
                $this._zoom(scale);

                input.val(val)
            }).focus(function () {
                this.select();
            }).val(this._zoom_ratio ? this.zoom_ratio * 100 : 100);;

            modifier.find('.plus,.minus').bind('click', function () {
                this.className.indexOf('plus') >= 0 ? $this._zoomIn() : $this._zoomOut();
            });
            container = $('<div />').addClass('config-panel mr5').appendTo(this._bottom_bar);

            var document_type_selector = $('<select />').appendTo($('<div />').addClass('selector').attr('data-selector', 'document-type').append($('<div />')).width(150).appendTo(container)).change(function () {
            var document_type = $this._document_types[this.options[this.selectedIndex].value];
                $(this).parent().find('div').text(document_type.title);

                $this._renderFrame(document_type);
                //blur để tránh khi nhấn space bị sổ ra;
                document_type_selector.blur();
            });

            this._document_types.forEach(function (document_type, index) {
                $('<option />').attr('value', index).text(document_type.title).appendTo(document_type_selector);
            });

            document_type_selector.trigger('change');

            if(!this._permissions.edit_layer) {
                document_type_selector.attr('disabled', true);
            } else {
                $('<div />').html('Custom Document').addClass('btn btn-outline mr5').appendTo(container).click(function () {
                    $this._renderPopupCustom($this);
                });
            }

            $('<div />').html('Show blend mode layers').addClass('btn btn-outline').appendTo(container).click(function () {
                $this._renderPopupBlendModeLayers();
            });

            $('<input />').attr({
                type: 'checkbox',
                checked: this._is_show_line_helper
            }).addClass('styled-checkbox').change(function() {
                $this._is_show_line_helper = this.checked
            }).appendTo(container);

            $('<span />').addClass('ml5').text('Display lines helper').appendTo(container);

            const background_design_color = $('<div />').addClass('background-design-color').appendTo(this._bottom_bar);
            $('<div />').html('Background Color').addClass('btn btn-outline mr5').appendTo(background_design_color).click(function () {
                $this._renderPopupBackgroundDesignColor();
            });

        }

        _saveDesign(frm, btn_submit) {
            if (btn_submit.attr('disabled') === 'disabled') {
                return;
            }
            $('#contineu_input').val(btn_submit.attr('data-continue'))
            frm.force_submit = true
            btn_submit.attr('disabled', 'disabled')
            frm.submit()
        }

        _renderPopupConfirmActionLayer(frm, btn_submit, layers){

            const popupConfirmSave = "popupConfirmActionLayer"

            const list_layers = layers.list_layers
            const key_layer_not_visibles = layers.key_layer_not_visibles
            const key_layer_not_renders = layers.key_layer_not_renders

            if (key_layer_not_visibles.length == 0 && key_layer_not_renders == 0) {
                this._renderPopupConfirmInputConfig(frm, btn_submit)
                return
            }

            $.unwrapContent(popupConfirmSave)
            const modal = $('<div />').addClass('osc-modal').width(1000)
            const header = $('<header />').appendTo(modal)
            $('<div />').addClass('title').html('Please! Confirm Not Visible & Not Render Design layer before save').appendTo($('<div />').addClass('main-group').appendTo(header))

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent(popupConfirmSave)
            }).appendTo(header)

            const modal_body = $('<div />').addClass('body post-frm').css({'overflow-y': 'scroll', 'max-height': '360px'}).appendTo(modal)

            const listElm = $('<div />').addClass('d-flex').appendTo(modal_body)

            const div_not_visible = $('<div />').css({ 'width': '50%', 'border-right': '1px solid' }).append($('<div />').addClass('d-flex justify-content-center').append($('<div />').css('width', '15px').append($.renderIcon('eye-hide'))).append($('<i />').text(' Not Visible'))).appendTo(listElm)

            const div_not_reder_design = $('<div />').css('width', '50%').append($('<div />').addClass('d-flex justify-content-center').append($('<div />').addClass('ml10').css('width', '15px').append($.renderIcon('not-render'))).append($('<i />').text(' Not Render Design'))).appendTo(listElm)

            const listElm_not_visible = $("<ol />").appendTo(div_not_visible)

            const listElm_not_reder_design = $("<ol />").appendTo(div_not_reder_design)

            const that = this

            list_layers.forEach(layer => {

                if (key_layer_not_visibles.includes(layer.key) || key_layer_not_renders.includes(layer.key)) {
                    const liElm = $('<li />')
                    const parentElm = $('<div />').addClass("mb15").append(liElm).css({ display: 'flex', 'justify-content': 'space-between' })

                    if (layer.is_personalized) {
                        parentElm.css('color', '#b81795')
                    }

                    const items = [
                        $('<b />').css('text-decoration', 'underline').text(`${layer.name} (${layer.type})` + (layer.type == 'text' ? ` : ${layer.content}` : '')),
                        $('<div />').text(layer.stack.join(" -> "))
                    ]
                    items.forEach(elm => elm.appendTo(liElm))

                    if (layer.type == 'image') {
                        liElm.css('max-width', 300)
                        const url = that._image_data[layer.image_id]?.url || ""
                        const div_img = $('<div />').addClass('confirm-thumb-img').appendTo(parentElm)

                        const imageItem = $('<img />')
                            .attr('src', url ? that._toThumbUrl(url) : '').appendTo(div_img)
                    }

                    if (key_layer_not_visibles.includes(layer.key)) {
                        parentElm.clone().appendTo(listElm_not_visible)
                    }

                    if (key_layer_not_renders.includes(layer.key)) {
                        parentElm.clone().appendTo(listElm_not_reder_design)
                    }
                }
            })

            let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent(popupConfirmSave);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10').attr('type', 'button').html('Confirm').appendTo(action_bar).click(function () {
                $.unwrapContent(popupConfirmSave);

                that._renderPopupConfirmInputConfig(frm, btn_submit)

            })

            $.wrapContent(modal, {key: popupConfirmSave})
            modal.moveToCenter().css('top', '50px')
        }

        _renderPopupConfirmInputConfig(frm, btn_submit){

            const popupConfirmSave = "popupConfirmInputConfig"

            const that = this

            const layers = that._findAllTextInputPersonalize()

            if (layers.length === 0) {
                this._saveDesign(frm, btn_submit)
                return
            }

            $.unwrapContent(popupConfirmSave)
            const modal = $('<div />').addClass('osc-modal').width(600)
            const header = $('<header />').appendTo(modal)
            $('<div />').addClass('title').html('The maximum length should be <= 12,<br/>too long text may lead to bad printing issues.<br/>Please! Check following text input').appendTo($('<div />').addClass('main-group').appendTo(header))

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent(popupConfirmSave)
            }).appendTo(header)

            const modal_body = $('<div />').addClass('body post-frm').css({'overflow-y': 'scroll', 'max-height': '360px'}).appendTo(modal)


            const ol_element = $("<ol />").appendTo(modal_body)

            layers.forEach(layer => {

                const liElm = $('<li />')
                const parentElm = $('<div />').addClass("mb15").append(liElm).css({ display: 'flex', 'justify-content': 'space-between' })

                const items = [
                    $('<b />').css('text-decoration', 'underline').text(`${layer.name} (${layer.type}) : ${layer.max_length} character`),
                    $('<div />').text(layer.stack.join(" -> "))
                ]
                items.forEach(elm => elm.appendTo(liElm))
                parentElm.clone().appendTo(ol_element)
            })

            let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent(popupConfirmSave);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10').attr('type', 'button').html('Confirm').appendTo(action_bar).click(function () {
                if ($(this).attr('disabled') === 'disabled') {
                    return;
                }
                $(this).attr('disabled', 'disabled')
                that._saveDesign(frm, btn_submit)
            })

            $.wrapContent(modal, {key: popupConfirmSave})
            modal.moveToCenter().css('top', '50px')
        }


        _findAllTextInputPersonalize() {
            var objects = this._fetch(this._object_thumb_list, false, 1);
            const list_layers = [];
            const __findLayers = (object, stack) => {
                stack.push(object.name)
                if (object.hasOwnProperty('personalized') && object.type === 'text' && object.personalized.config.max_length > MAX_LENGTH_INPUT_PERSONALIZE_DEFAULT) {
                    list_layers.push({
                        stack: stack.concat(),
                        type: object.type,
                        name: object.name,
                        max_length: object.personalized.config.max_length
                    });
                }

                object.type_data?.children?.forEach(child => {
                    __findLayers(child, stack.concat())
                });
                if(object.personalized?.config?.options) {
                    const options = object.personalized?.config?.options || {}
                    for (const key in options) {
                        const option = options[key]
                        const dumpStack = stack.concat()
                        dumpStack.push(option.label)
                        option?.data?.objects?.forEach(object => {
                            __findLayers(object, dumpStack)
                        })
                    }
                }
            };
            objects.forEach(object => {
                __findLayers(object, [])
            });

            return list_layers;
        }

        _findAllLayers() {
            var objects = this._fetch(this._object_thumb_list, false, 1);
            const list_layers = [];
            const key_layer_not_visibles = []
            const key_layer_not_renders = []
            const __findLayers = (object, stack, is_visible = true, is_not_render = false) => {
                stack.push(object.name)
                list_layers.push({
                    stack: stack.concat(),
                    type: object.type,
                    name: object.name,
                    is_visible: object.showable,
                    is_not_render_design: object.is_not_render_design,
                    key: object.key,
                    content: object.type_data?.content,
                    image_id: object.type_data?.id,
                    is_personalized: object.hasOwnProperty('personalized')
                });

                if (!is_visible || !object.showable) {
                    key_layer_not_visibles.push(object.key)
                }

                if (is_not_render || object.is_not_render_design) {
                    key_layer_not_renders.push(object.key)
                }

                object.type_data?.children?.forEach(child => {
                    __findLayers(child, stack.concat(), is_visible & object.showable, is_not_render || object.is_not_render_design)
                });
                if(object.personalized?.config?.options) {
                    const options = object.personalized?.config?.options || {}
                    for (const key in options) {
                        const option = options[key]
                        const dumpStack = stack.concat()
                        dumpStack.push(option.label)
                        option.objects?.forEach(object => {
                            __findLayers(object, dumpStack, is_visible & object.showable, is_not_render || object.is_not_render_design)
                        })
                    }
                }
            };
            objects.forEach(object => {
                __findLayers(object, [], object.showable, object.is_not_render_design)
            });

            return {
                list_layers,
                key_layer_not_visibles,
                key_layer_not_renders
            };
        }

        _findAllBlendModeLayers() {
            const objects = this._fetch(this._object_thumb_list, false);
            const listLayers = [];
            const __findBlendModeLayers = (object, stack) => {
                // if (object.type !== "group") return;
                stack.push(object.name)
                const blend_mode = object.type_data?.blend_mode

                if (blend_mode) listLayers.push({
                    stack: stack.concat(),
                    type: object.type,
                    name: object.name,
                    blend_mode,
                });

                object.type_data?.children?.forEach(child => {
                    __findBlendModeLayers(child, stack.concat())
                });
                if(object.personalized?.config?.options) {
                    const options = object.personalized?.config?.options || {}
                    for (const key in options) {
                        const option = options[key]
                        const dumpStack = stack.concat()
                        dumpStack.push(option.label)
                        option.objects?.forEach(object => {
                            __findBlendModeLayers(object, dumpStack)
                        })
                    }
                }
            };
            objects.forEach(object => {
                __findBlendModeLayers(object, [])
            });

            return listLayers;
        }
        _renderPopupBlendModeLayers(){
            const blendModeFrameName = "blendModeLayersFrm"

            $.unwrapContent(blendModeFrameName)
            const modal = $('<div />').addClass('osc-modal').width(600)
            const header = $('<header />').appendTo(modal)
            $('<div />').addClass('title').html('Blend mode layers').appendTo($('<div />').addClass('main-group').appendTo(header))

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent(blendModeFrameName)
            }).appendTo(header)

            const listLayers = this._findAllBlendModeLayers();

            const modal_body = $('<div />').addClass('body post-frm').appendTo(modal)
            const listElm = $("<ol />").appendTo(modal_body)
            listLayers.forEach(layer => {
                const liElm = $('<li />').addClass("mb15")
                const items = [
                    $('<b />').text(`${layer.name} (${layer.type})`),
                    $('<span />').text(`: ${layer.blend_mode}`),
                    $('<div />').text(layer.stack.join(" -> "))
                ]
                items.forEach(elm => elm.appendTo(liElm))
                liElm.appendTo(listElm)
            })

            $.wrapContent(modal, {key: blendModeFrameName})
            modal.moveToCenter().css('top', '100px')
        }

        //Render popup custom document ở bottom bar
        _renderPopupCustom($this){
            $.unwrapContent('customDocumentFrm');

            var modal = $('<div />').addClass('osc-modal').width(300);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Custom Document').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('customDocumentFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            let row = $('<div />').addClass('mt10').appendTo(modal_body);

            $('<div />').append($('<input />').addClass('styled-input').attr({type: 'number', name: 'custom_document_width', placeholder: 'Width'})).appendTo(row);

            row = $('<div />').addClass('mt5').appendTo(modal_body);

            $('<div />').append($('<input />').addClass('styled-input').attr({type: 'number', name: 'custom_document_height', placeholder: 'Height'})).appendTo(row);

            row = $('<div />').addClass('mt5').appendTo(modal_body);

            $('<div />').append($('<input />').addClass('styled-input').attr({type: 'number', name: 'custom_document_safe_area_width', placeholder: 'Safe area width'})).appendTo(row);

            row = $('<div />').addClass('mt5').appendTo(modal_body);

            $('<div />').append($('<input />').addClass('styled-input').attr({type: 'number', name: 'custom_document_safe_area_height', placeholder: 'Safe area height'})).appendTo(row);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('customDocumentFrm');
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {
                let custom_document_width = $('input[name="custom_document_width"]').val().trim();

                let custom_document_height = $('input[name="custom_document_height"]').val().trim();

                let custom_document_safe_area_width = $('input[name="custom_document_safe_area_width"]').val().trim();

                let custom_document_safe_area_height = $('input[name="custom_document_safe_area_height"]').val().trim();

                if (isNaN(custom_document_width) || isNaN(custom_document_height) || isNaN(custom_document_safe_area_width) || isNaN(custom_document_safe_area_height)){
                    alert('all file is number!');
                    return;
                }

                if (custom_document_width < 1 || custom_document_height < 1 ){
                    alert('width or height document need more than 0');
                    return;
                }

                if(custom_document_safe_area_width > custom_document_width ||  custom_document_safe_area_height > custom_document_height){
                    alert('custom_document_safe_area_width need more than custom_document_width or custom_document_safe_area_height need more than custom_document_height ');
                    return;
                }

                var document_type = {
                    key : 'custom',
                    title : 'Custom Document',
                    width : custom_document_width,
                    height : custom_document_height,
                    custom_document_safe_area_width : custom_document_safe_area_width,
                    custom_document_safe_area_height : custom_document_safe_area_height
                }

                $this._renderFrame(document_type);

                $.unwrapContent('customDocumentFrm');
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'customDocumentFrm'});

            modal.moveToCenter().css('top', '100px');
        }

        //Render popup select background design color ở bottom bar
        _renderPopupBackgroundDesignColor() {
            const $this = this;
            $.unwrapContent('backgroundDesignColorFrm');

            var modal = $('<div />').addClass('osc-modal background-design-color-frm').width(400);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Background Design Color').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('backgroundDesignColorFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
            const container = $('<div/>').addClass('background-design-color-select').appendTo(modal_body);
            const input = $('input[name="background_color"]');
            let defaultColor = input.val();
            const COLORS = ['Select', '#282828', '#ffffff', '#222f3e', '#f7d794', '#95afc0', '#5d638b'];
            const select = $('<div/>').addClass('select').appendTo(container);

            const selectColor = $('<span/>').appendTo(select).bind('change-color', function(e, color){
                if (color) {
                    $(this).text(color)
                } else {
                    $(this).text('Select');
                }
                defaultColor = color;
            }).trigger('change-color', [defaultColor]);
            $('<span />').addClass('toggle').append($.renderIcon('angle-down-solid')).appendTo(select).click(function () {
                $(this).toggleClass("up")
            })
            const renderDropdown = () => {
                const dropdown = $('<div/>').addClass('dropdown').appendTo(container);
                COLORS.forEach(color => {
                    const colorItem = $('<div/>').addClass('option').appendTo(dropdown);
                    if (color == input.val()) colorItem.css('background', '#ddd')
                    $('<span/>').text(color).appendTo(colorItem);
                    color !== 'Select' && $('<div/>').css({
                        width: '20px',
                        height: '20px',
                        background: color,
                        border: '1px solid #000'
                    }).appendTo(colorItem);
                    colorItem.click(() => {
                        dropdown.remove();
                        selectColor.trigger('change-color', [color])
                    })
                })
            }
            select.click(() => {
                if (container.children('.dropdown').length) container.children('.dropdown').remove();
                else renderDropdown();
            });
            $(document).click(e => $(e.target).closest(".background-design-color-select").length == 0 &&  container.children('.dropdown').remove());

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('backgroundDesignColorFrm');
            }).appendTo(action_bar);

            $('<button/>').addClass('btn btn-primary').text('Preview').click(function(){
                const contain = $this._svg_group.object[0].previousSibling.getBoundingClientRect();
                const objects = $this._svg_group.object.clone();
                const { width, height, top, left } = $this._svg_group.object[0].getBoundingClientRect();

                const ratio = width/height;
                $.unwrapContent('previewDesignWithBackgroundColorFrm');
                var modal = $('<div />').addClass('osc-modal').width(840);
                var header = $('<header />').appendTo(modal);

                $('<div />').addClass('title').css('font-size', '22px').html('Preview Design').appendTo(header);

                $('<div />').addClass('close-btn').click(function () {
                    $.unwrapContent('previewDesignWithBackgroundColorFrm');
                }).appendTo(header);
                $.wrapContent(modal, {key: 'previewDesignWithBackgroundColorFrm'});

                modal.moveToCenter().css('top', '100px');
                const content = $('<div/>').css({
                    padding: '0px 20px 20px 20px',
                    background: defaultColor,
                }).appendTo(modal);
                const svg =  $(document.createElementNS('http://www.w3.org/2000/svg', 'svg')).appendTo(content);
                const sizeRender = ratio > 1 ? {
                    width: '800px',
                    height: 800/ratio + 'px'
                } : {
                    width: 700 * ratio + 'px',
                    height: '700px'
                }
                svg.css({
                    ...sizeRender,
                    display: 'block',
                    margin: 'auto',
                })
                svg[0].setAttribute('viewBox', `${left-contain.left} ${top-contain.top} ${width} ${height}`)
                objects.appendTo(svg)
            }).appendTo(container)

            $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {
                input.val(defaultColor == 'Select' ? null : defaultColor);
                $.unwrapContent('backgroundDesignColorFrm');
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'backgroundDesignColorFrm'});

            modal.moveToCenter().css('top', '100px');
        }

        _zoom(scale, zoom_point) {
            scale = parseFloat(scale);
            if (isNaN(scale)) {
                scale = 0;
            }

            //do bị config với hành động resize và  nên phải unselect khi zoom
            this._objectUnselectCurrent();

            let viewBox = this._viewBox();
            const w = viewBox.width;
            const h = viewBox.height;
            // const new_x =
            //nếu bấm zoom dưới bottom bar thì đặt zoom_point là c_zoom_ratioenter của view
            if (typeof zoom_point === 'undefined') {
                zoom_point = {
                    x: this._svg_size.w / 2,
                    y: this._svg_size.h / 2
                }
            }

            const dw = w * scale;
            const dh = h * scale;
            const dx = dw * zoom_point.x / this._svg_size.w;
            const dy = dh * zoom_point.y / this._svg_size.h;
            viewBox = {
                x: viewBox.x+dx,
                y: viewBox.y+dy,
                width: viewBox.width - dw,
                height: viewBox.height - dh
            };


            this._svg[0].setAttribute('viewBox', viewBox.x + ' ' + viewBox.y + ' ' + viewBox.width + ' ' + viewBox.height);

            this._zoom_ratio = this._svg_size.w / viewBox.width;
            this._bottom_bar.find('.zoom-bar input').val(this._zoom_ratio * 100);

            this._renderFrame(this.document_type);
            // return percent;
        }

        _zoomIn(zoom_point) {
            return this._zoom(0.2, zoom_point);
        }

        _zoomOut(zoom_point) {
            return this._zoom(-0.2, zoom_point);
        }

        _renderToolbar() {
            var $this = this;

            this._toolbar = $('<div />').addClass('toolbar').appendTo(this._container);

            $('<span />').addClass('border-line').appendTo(this._toolbar).append($('<span />').addClass('toggler').click(function () {
                $this._toolbar.toggleClass('collapse');
            }));

            var main_container = $('<div />').addClass('main-container').appendTo(this._toolbar);

            this._toolbar_command_container = $('<div />').addClass('tool-section').appendTo(main_container);

            var color_section = $('<div />').addClass('color-section').appendTo(main_container);

            var fill_btn = $('<div />').addClass('fill no-color').attr('data-cmd', 'fill').appendTo(color_section).osc_colorPicker({
                swatch_name: 'svg_editor',
                callback: function (color) {
                    if (color) {
                        fill_btn.removeClass('no-color').css('background-color', color);
                    } else {
                        fill_btn.addClass('no-color');
                    }

                    var object = $this._objectGetCurrent();

                    if (object && object.data.personalized?.type !== "spotify") {
                        object.data.type_data.fill = color ? color : 'none';
                        $(object.elm).trigger('object-update');
                    }
                }
            });

            var stroke_btn = $('<div />').addClass('stroke').attr('data-cmd', 'stroke').appendTo(color_section).osc_colorPicker({
                swatch_name: 'svg_editor',
                callback: function (color) {
                    if (color) {
                        stroke_btn.removeClass('no-color').css('background-color', color);
                    } else {
                        stroke_btn.addClass('no-color');
                    }

                    var object = $this._objectGetCurrent();

                    if (object && object.data.personalized?.type !== "spotify") {
                        object.data.type_data.stroke.color = color ? color : 'none';
                        $(object.elm).trigger('object-update');
                    }
                }
            });

            this._renderCommands();
        }

        //new
        _setupKeyboardControl () {
            const $this = this;
            let prevCommand = null;
            let holdCommand = null;
            const keyCodes = [32];
            $(this._container).on('keydown', function(e) {
                if (['input', 'textarea', 'select'].indexOf(e.target?.nodeName?.toLowerCase()) >= 0
                 || e.target.className.includes("svg-editor-text-helper") || !keyCodes.includes(e.keyCode)) {
                    return;
                }

                let command = null;
                switch(e.keyCode){
                    case 32: //Space
                        command = "hand"
                        break;
                    default:
                        break;
                }
                if(holdCommand === command) {
                    return ;
                }

                holdCommand = command;

                const prev_command_btn = $this._toolbar.find('[data-cmd].active')
                if (prev_command_btn[0]) {
                    prevCommand = $(prev_command_btn[0]).attr('data-cmd');
                }

                if (command) $this._commandActive(command)
                e.preventDefault()
            });

            $(this._container).on('keyup', function(event) {

                switch(event.keyCode){
                    case 32: //Space
                        if(prevCommand) $this._commandActive(prevCommand);
                        else $this._commandUnActive("hand")
                        break;
                    default:
                        break;
                }

                prevCommand = null;
                holdCommand = null;
                event.preventDefault()
            });


            $(this._container).keydown(function (e) {
                if (e.target.nodeName && ['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) >= 0) {
                    return;
                }

                if ($this._permissions.edit_layer && (e.key === 'y' || e.key === 'z') && e.ctrlKey) {
                    if (e.key === 'z') {
                        $this._historyUndo();
                    } else {
                        $this._historyRedo();
                    }

                    e.preventDefault();

                    return;
                } else if ($this._permissions.edit_layer && (e.key === 'v' || e.keyCode === 86) && e.ctrlKey && e.altKey) {
                    $this._pasteObject();

                    return;
                } else if ((e.key === 'c' || e.keyCode === 67) && e.ctrlKey && e.shiftKey) {
                    $this._copyObjectAttributes();
                    e.preventDefault();
                } else if ($this._permissions.edit_layer && (e.key === 'v' || e.keyCode === 86) && e.ctrlKey && e.shiftKey) {
                    $this._pasteObjectAttributes();
                    e.preventDefault();
                }
            });

            document.addEventListener('wheel', function(e) {
                if ($($this._container).has(e.target).length && e.ctrlKey == true) {
                    e.preventDefault();

                    const zoom_point = {
                        x: e.offsetX,
                        y: e.offsetY
                    }
                    const scale = -Math.sign(e.deltaY) * 0.2;
                    $this._zoom(scale, zoom_point);
                    // if (e.deltaY < 0){
                    //     $this._zoomIn(zoom_point);
                    // } else if (e.deltaY > 0) {
                    //     $this._zoomOut(zoom_point);
                    // }

                }
            }, { passive: false });

        }

        _renderCommands() {
            var $this = this;
            const cmds = this._permissions.edit_layer ?
                ['rect', 'ellipse', 'pen', 'pencil', 'penAddPoint', 'penRemovePoint', 'penEditPoint','text', 'personalize', 'image', 'hand', 'zoomIn', 'zoomOut', 'fullscreen'] :
                [ 'penEditPoint','hand', 'zoomIn', 'zoomOut', 'fullscreen'];

            cmds.forEach(function (command) {
                var init_func = '_command' + command.ucfirst() + '_init';

                //Bỏ qua nếu không có function
                if (typeof $this[init_func] === 'undefined') {
                    return;
                }

                var command_btn = $('<div />').attr('data-cmd', command).appendTo($this._toolbar_command_container);

                //Khỏi tạo command btn
                var skip_exec = $this[init_func](command_btn);

                command_btn.click(function (e) {
                    if (this.getAttribute('disabled') === 'disabled') {
                        return;
                    }

                    //exec_flag: nếu k active
                    var exec_flag = !command_btn.hasClass('active');

                    //REMOVE tất cả những thằng đang active
                    $('[data-cmd].active').each(function () {
                        var command_btn = $(this);
                        //Xóa css class
                        command_btn.removeClass('active');

                        //Gọi hàm xóa command
                        var command = this.getAttribute('data-cmd');

                        var clear_func = '_command' + command.ucfirst() + '_clear';

                        if (typeof $this[clear_func] === 'function') {
                            $this[clear_func](command_btn, e);
                        }
                    });
                    //Nếu khởi tạo trả về false thì k exec
                    if (exec_flag && skip_exec !== false) {
                        var exec_func = '_command' + command.ucfirst() + '_exec';

                        if (typeof $this[exec_func] !== 'function' || $this[exec_func](command_btn, e) !== false) {
                            command_btn.addClass('active');
                        }
                    }
                });
            });
        }


        _commandActive(cmd) {
            var cmd_btn = this._toolbar.find('[data-cmd="' + cmd + '"]');

            if (cmd_btn[0] && !cmd_btn.hasClass('active')) {
                $(cmd_btn[0]).trigger('click');
            }
        }

        _commandUnActive(cmd) {
            const cmd_btn = this._toolbar.find(`[data-cmd="${cmd}"]`);
            if(cmd_btn[0] && cmd_btn.hasClass("active")) {
                cmd_btn.removeClass('active');
                var clear_func = `_command${cmd.ucfirst()}_clear`;
                if(clear_func) this[clear_func]();
            }
        }

        _commandIsActive(cmd) {
            return this._toolbar.find('[data-cmd="' + cmd + '"]').hasClass('active');
        }

        _renderObjectSwitcher() {
            if(this._is_append_data) return;

            var object = this._objectGetCurrent();
            const $this = this;
            const __clearSwitcher = () =>  {
                if(!this.swicher_container) return;

                this.swicher_container.remove();
                this.swicher_container = null;
            }

            if (!object?.data?.personalized || !["imageSelector", "switcher"].includes(object.data.personalized.type)) {
                __clearSwitcher();
                return false;
            }

            if(!this.swicher_container) {
                this.swicher_container = $("<div />").addClass("swicher-container").appendTo(this._thumb_panel)
            }
            if(this._last_scroll_switcher?.object_key !== object.data.key) {
                delete this._last_scroll_switcher;
            }

            this.swicher_container.empty();
            const toggleBtn =  $("<button />").addClass("btn btn-outline swicher-toggle").append($.renderIcon('list'));

            const switcherPanel = $('<div />').addClass("swicher-panel")


            const togglePanel = () => {
                if(switcherPanel.is(":hidden")) {
                    switcherPanel.show();
                    toggleBtn.attr("data-selected","1");
                    this.swicher_open = true;
                }
                else {
                    switcherPanel.hide();
                    this.swicher_open = false;
                    toggleBtn.attr("data-selected","0");
                }
            }

            toggleBtn.click(togglePanel)

            $("<div />").addClass("collapse-btn").append($.renderIcon('collapse'))
                .appendTo(switcherPanel).click(togglePanel);

            $("<h4 />").addClass("title").text("OPTIONS").appendTo(switcherPanel);


            this.swicher_container.append(switcherPanel).append(toggleBtn)

            //Render when type === imageSelector
            if(object.data.personalized.type === "imageSelector") {
                const gridContainer = $("<div />").addClass("grid-image-list").appendTo(switcherPanel)
                const onSelect = (image, key) => {
                    let group, before;
                    this._last_scroll_switcher = {
                        scroll_top: gridContainer.scrollTop(),
                        object_key: object.data.key
                    }

                    if ($(object.elm).next('[data-object]')[0]) {
                        group = null;
                        before = $(object.elm).next('[data-object]')[0];
                    } else {
                        group = $(object.elm).closest('g[data-type="group"]')[0];
                        before = null;
                    }

                    var object_data = JSON.parse(JSON.stringify(object.data));
                    this._objectRemove(object.data.key);
                    var new_type_data = JSON.parse(JSON.stringify(image.data.type_data))

                    object_data.personalized.config.default_key = key;

                    object = this._objectAdd('image', new_type_data, object_data.key, 1, Object.assign(
                        {group, before}, object_data
                    ));
                }

                let activeImage;
                const addImage = (image,key, isDefaultImage) => {
                    if(!image.data?.type_data?.id) console.log(image)
                    const url = this._image_data[image.data?.type_data?.id]?.url || ""

                    const imageItem = $("<div />")
                        .addClass("grid-image-item")
                        .css('background-image', url ? ('url(' +  $this._toThumbUrl(url) + ')') : 'initial')
                        .click(() => onSelect(image, key));

                    if(isDefaultImage) {
                        imageItem.attr("default-image", 1);
                        activeImage = imageItem;
                    }

                    gridContainer.append(imageItem);
                }

                $.each(object.data.personalized.config.groups, function (key, group) {
                    $.each(group.images, function (key, image) {
                        let isDefaultImage = false;
                        if (object.data.personalized.config.default_key === key) {
                            isDefaultImage = true;

                            image.data = {
                                type_data:  object.data.type_data
                            };
                        }

                        addImage(image,key, isDefaultImage)
                    });
                });

                if(this._last_scroll_switcher?.object_key === object.data.key) {
                    gridContainer.scrollTop(this._last_scroll_switcher.scroll_top);
                } else if(!!activeImage) {

                    const height = activeImage.offset().top -gridContainer.offset().top ;
                    gridContainer.scrollTop(height - 268/2)
                }
            } else if (object.data.personalized.type === "switcher") {
                const config = object.data.personalized.config;

                const optionContainer = $("<div />").addClass("option-list").appendTo(switcherPanel);

                const selectOption = (key, option) =>{
                    const defaultOption = config.options[config.default_option_key];
                    this._last_scroll_switcher = {
                        scroll_top: optionContainer.scrollTop(),
                        object_key: object.data.key
                    }

                    if(defaultOption) {
                        defaultOption.objects = this._fetch($(object.list_item).find('> .thumb-list'))
                    }

                    this._objectRemoveChildsInGroup(object)

                    if (!Array.isArray(option.objects)) {
                        option.objects = [];
                    }

                    this._setDataMakeObject(option.objects, object.elm);

                    config.default_option_key = key;
                    option.objects = [];

                    // $(object.elm).trigger('personalized-update');

                    this._objectSelect(object);
                }
                let activeItem;
                $.each(config.options, function(key, option) {
                    const optionItem = $("<div />").addClass("option-item")

                    const isDefault = key === config.default_option_key;
                    if(isDefault) {
                        optionItem.addClass("option-item--default");
                        activeItem = optionItem;
                    }

                    optionItem.click(() => selectOption(key, option))
                    if(config.image_mode) {
                        $("<div />").addClass("image-preview")
                            .css('background-image', option.image ? `url(${$this._getLocalImgStorageUrl(option.image)})` : 'initial')
                            .appendTo(optionItem);
                    }

                    $("<div />").text(option.label).addClass("label").appendTo(optionItem)
                    optionContainer.append(optionItem);
                })

                if(this._last_scroll_switcher?.object_key === object.data.key) {
                    optionContainer.scrollTop(this._last_scroll_switcher.scroll_top);
                } else if(!!activeItem) {
                    const height = activeItem.offset().top -optionContainer.offset().top ;
                    optionContainer.scrollTop(height - 268/2)
                }
            }
            if(!this.swicher_open) switcherPanel.hide();

        }

        //RENDER PANEL BÊN PHẢI
        _renderThumbPanel() {
            var $this = this;

            this._thumb_panel = $('<div />').addClass('thumb-panel').appendTo(this._container);

            $('<span />').addClass('border-line').mousedown(function (e) {
                var pointer_x = e.pageX - (this.getBoundingClientRect().left + $(window).scrollLeft());

                $(document).bind('mousemove.personalizedResizeThumbPanel', function (e) {
                    var thumb_panel_rect = $this._thumb_panel[0].getBoundingClientRect();

                    var width = thumb_panel_rect.left + thumb_panel_rect.width + $(window).scrollLeft() - e.pageX + pointer_x;

                    width = Math.max(Math.min(width, $this._container.width() / 2), 100);

                    $this._thumb_panel.width(width);
                }).bind('mouseup.personalizedResizeThumbPanel', function () {
                    $(document).unbind('.personalizedResizeThumbPanel');
                });
            }).appendTo(this._thumb_panel).append($('<span />').addClass('toggler').click(function () {
                $this._thumb_panel.toggleClass('collapse');
            }));

            var main_container = $('<div />').addClass('main-container').appendTo(this._thumb_panel);

            var blend_mode_container = $('<div />').addClass('blend-mode-bar').appendTo(main_container);

            var blend_mode_selector = $('<select />').appendTo($('<div />').addClass('selector').append($('<div />')).appendTo(blend_mode_container)).change(function () {
                $(this.parentNode.querySelector('div')).text($(this.options[this.selectedIndex]).text());

                var object = $this._objectGetCurrent();

                if (!object) {
                    return;
                }

                if (this.options[this.selectedIndex].value === 'normal') {
                    delete object.data.type_data.blend_mode;
                } else {
                    object.data.type_data.blend_mode = this.options[this.selectedIndex].value;
                }

                $(object.elm).css('mix-blend-mode', this.options[this.selectedIndex].value);
            });

            $.each({
                normal: 'Normal',
                multiply: 'Multiply',
                screen: 'Screen',
                overlay: 'Overlay',
                darken: 'Darken',
                lighten: 'Lighten',
                'color-dodge': 'Color Dodge',
                'color-burn': 'Color Burn',
                difference: 'Difference',
                exclusion: 'Exclusion',
                hue: 'Hue',
                saturation: 'Saturation',
                color: 'Color',
                luminosity: 'Luminosity'
            }, function (key, value) {
                $('<option />').attr('value', key).text(value).appendTo(blend_mode_selector);
            });

            var thumb_panel_toolbar = $('<div />').addClass('thumb-panel-toolbar').appendTo(main_container);
            $('<div />').append($.renderIcon('export')).click(function () {
                $this._commandExportSvg_exec();

            }).appendTo(thumb_panel_toolbar);


            if (this._permissions.edit_layer) {
                $('<div />').append($.renderIcon('magic-solid')).click(function () {
                    $this._commandPersonalize_exec();
                }).appendTo(thumb_panel_toolbar);

                $('<div />').append($.renderIcon('clone')).click(function () {
                    $this._commandClone_exec();
                }).appendTo(thumb_panel_toolbar);

                $('<div />').append($.renderIcon('mask')).click(function () {
                    $this._commandMask_exec();
                }).appendTo(thumb_panel_toolbar);

                $('<div />').append($.renderIcon('folder-plus-regular')).click(function () {
                    $this._objectUnselectCurrent();
                    $this._objectAdd('group', {children: []});
                }).appendTo(thumb_panel_toolbar);
            }

            if(this._permissions.remove_layer) {
                $('<div />').append($.renderIcon('trash-alt-regular')).click(function () {
                    var selected_thumb = $this._object_thumb_list.find('.thumb-content[data-selected="1"]');

                    if (selected_thumb[0]) {
                        var object_key = selected_thumb.parent().attr('data-object');

                        if (object_key) {
                            $this._objectRemove(object_key);
                        }
                    }
                }).appendTo(thumb_panel_toolbar);
            }

            this._object_thumb_list = $('<div />').addClass('thumb-list').appendTo(main_container);
        }

        _viewBox() {
            var viewbox = {};

            $.extend(viewbox, this._svg[0].viewBox.baseVal);

            return viewbox;
        }

        _cursorPoint(e) {
            var pt = this._svg[0].createSVGPoint();

            pt.x = e.clientX;
            pt.y = e.clientY;

            pt = pt.matrixTransform(this._svg[0].getScreenCTM().inverse());

            return {x: pt.x, y: pt.y};
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
                var center_pt = this._svg[0].createSVGPoint();

                center_pt.x = bbox.x + bbox.width / 2;
                center_pt.y = bbox.y + bbox.height / 2;

                var degress = Math.atan2(point.y - center_pt.y, point.x - center_pt.x) * 180 / Math.PI;

                degress -= rotation;

                var distance = this._pointGetDistance(center_pt, point);

                var radian = degress * Math.PI / 180;

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
                var center_pt = this._svg[0].createSVGPoint();

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

        //Check 2 vector giao nhau
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

        //Khoảng cách giữa 2 điểm (Euclidean Distance)
        _pointGetDistance(p1, p2) {
//            var a = p1.x - p2.x;
//            var b = p1.y - p2.y;
//
//            return Math.sqrt(a * a + b * b);

            return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
        }

        _commandFullscreen_init(cmd_btn) {
            var $this = this;

            cmd_btn.append($.renderIcon('expand-arrows-alt-solid'));

            $(document.body).bind('webkitfullscreenchange mozfullscreenchange fullscreenchange', function (e) {
                var state = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen;

                cmd_btn.find('svg').remove();
                cmd_btn.append($.renderIcon(!state ? 'expand-arrows-alt-solid' : 'compress-arrows-alt-solid'));

                $this._container[state ? 'addClass' : 'removeClass']('fullscreen');
            });
        }

        //request full screens
        _commandFullscreen_exec(cmd_btn) {
            var state = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen;

            if (!state) {
                if (document.body.requestFullscreen) {
                    document.body.requestFullscreen();
                } else if (document.body.mozRequestFullScreen) {
                    document.body.mozRequestFullScreen();
                } else if (document.body.webkitRequestFullscreen) {
                    document.body.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }

            return false;
        }

        _commandPersonalize_exec(cmd_btn) {
            var object = this._objectGetCurrent();

            if (!object || !this._objectIsAncestor(object.elm, this._svg_group.object[0])) {
                return false;
            }

            this._objectUnselectCurrent();

            this._peronalize_renderPanel(object);

            return false;
        }

        _commandExportSvg_exec(cmd_btn) {
            const $this = this;
            var object = this._objectGetCurrent();
            if (!object || !this._objectIsAncestor(object.elm, this._svg_group.object[0])) {
                return false;
            }
            const elm = object.elm.cloneNode(true);

            const { x, y, width, height} = this._elmGetBBox(object.elm)
            const svgContainer = document.createElementNS(this._svg[0].namespaceURI,'svg');
            svgContainer.setAttribute("viewBox", `0 0 ${width} ${height}`);

            const wrapper = document.createElementNS(this._svg[0].namespaceURI,'g');
            const defs = document.createElementNS(this._svg[0].namespaceURI, 'defs')
            wrapper.setAttribute('transform', `translate(${-x} ${-y})`);
            wrapper.append(elm);
            svgContainer.append(defs)
            svgContainer.append(wrapper);

            $(wrapper).find("*").each(function() {
                const filter = this.getAttribute("filter");
                const clipPath = this.getAttribute("clip-path")
                const xlink = this.getAttribute("xlink:href");

                [clipPath, filter].forEach(attr => {
                    if(!attr) return

                    const match = attr.match(/(?<=^url\().*?(?=\))/)
                    const id = match?.[0];
                    if(id){
                        $this._svg_group.defs.find(id).clone(true).appendTo(defs)
                    }
                })

                if(xlink && /(?<=^#).*/.test(xlink)) {
                    $this._svg_group.defs.find(xlink).clone(true).appendTo(defs)
                }

            })

            function __downloadFile(url, filename) {

                const link = document.createElement('a');
                link.download = filename;
                link.href = url;
                link.target = "_blank"
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            const svg_content = svgContainer.outerHTML;
            $.ajax({
                url: $.base_url + '/personalizedDesign/backend/exportSvg',
                type: 'POST',
                data: {
                    width,
                    height,
                    svg_content: svg_content,
                    hash: OSC_HASH
                },
                success: function (response) {
                    if (response.result !== 'OK') {
                        toastr.error('Export image error: ' + response.message);
                        return;
                    }
                    __downloadFile(response?.data?.url, object.data.name + ".png")
                }
            });

            return false;
        }

        _commandClone_exec(cmd_btn) {
            var object = this._objectGetCurrent();

            if (object && this._objectIsAncestor(object.elm, this._svg_group.object[0])) {
                var object_data = JSON.parse(JSON.stringify(object.data));

                var clone_object = this._objectAdd(object_data.type, object_data.type_data, null, null, {personalized: object_data.personalized, name: object_data.name + ' Duplicated', showable: object_data.showable, locked: object_data.locked, is_not_render_design: object_data.is_not_render_design, before: object.elm});

                this._peronalize_fetchCallback(clone_object.data, true);

                if (clone_object.data.type === 'group') {
                    var children = this._fetch($(object.list_item).find('> .thumb-list'), true);

                    this._setDataMakeObject(children, clone_object.elm);
                }
                this._objectSelect(clone_object);
            }

            return false;
        }

        _commandMask_init(cmd_btn) {
            cmd_btn.append($.renderIcon('mask'));
        }

        _commandMask_exec(cmd_btn) {
            this._objectAddMask();
            return false;
        }

        _commandTrash_init(cmd_btn) {
            cmd_btn.append($.renderIcon('trash-alt-regular'));
        }

        _commandTrash_exec(cmd_btn) {
            var object = this._objectGetCurrent();

            if (object) {
                this._objectRemove(object.data.key);
            } else if (typeof this._callback.remove === 'function') {
                this._callback.remove();
            }

            return false;
        }

        _commandZoomIn_init(cmd_btn) {
            cmd_btn.append($.renderIcon('search-plus-solid'));
        }

        _commandZoomIn_exec() {
            var $this = this;

            this._svg.css('cursor', 'zoom-in');

            this._svg.bindUp('mousedown.zoom-tool', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                $this._zoomIn({
                    x: e.pageX - $this._svg[0].getBoundingClientRect().x - $(window).scrollLeft(),
                    y: e.pageY - $this._svg[0].getBoundingClientRect().y - $(window).scrollTop()
                });
            });
        }

        _commandZoomIn_clear() {
            this._svg.unbind('.zoom-tool');

            this._svg.css('cursor', '');
        }

        _commandZoomOut_init(cmd_btn) {
            cmd_btn.append($.renderIcon('search-minus-solid'));
        }

        _commandZoomOut_exec() {
            var $this = this;

            this._svg.css('cursor', 'zoom-out');

            this._svg.bindUp('mousedown.zoom-tool', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                $this._zoomOut({
                    x: e.pageX - $this._svg[0].getBoundingClientRect().x - $(window).scrollLeft(),
                    y: e.pageY - $this._svg[0].getBoundingClientRect().y - $(window).scrollTop()
                });
            });
        }

        _commandZoomOut_clear() {
            this._svg.unbind('.zoom-tool');

            this._svg.css('cursor', '');
        }

        _commandHand_init(cmd_btn) {
            cmd_btn.append($.renderIcon('hand-paper-regular'));
        }

        _commandHand_exec() {
            var $this = this;

            this._objectUnselectCurrent();

            this._svg.css('cursor', 'grab');

            this._svg.bindUp('mousedown.hand-tool', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.stopPropagation();

                var flag_point = {
                    x: e.pageX,
                    y: e.pageY
                };

                console.log("flag_point", e.offsetX, e.offsetY)

                var viewbox = $this._viewBox();

                $this._svg.css('cursor', 'grabbing');

                $this._svg.unbind('mousemove.hand-tool').unbind('mouseup.hand-tool').bind('mousemove.hand-tool', function (e) {
                    var x = viewbox.x - (e.pageX - flag_point.x) / $this._zoom_ratio;
                    var y = viewbox.y - (e.pageY - flag_point.y) / $this._zoom_ratio;

                    $this._svg[0].setAttribute('viewBox', x + ' ' + y + ' ' + viewbox.width + ' ' + viewbox.height);
                }).bind('mouseup.hand-tool', function () {
                    $this._svg.unbind('mousemove.hand-tool').unbind('mouseup.hand-tool');
                    $this._svg.css('cursor', 'grab');
                });
            });
        }

        _commandHand_clear() {
            this._svg.unbind('.hand-tool');

            this._svg.css('cursor', '');
        }

        _commandPencil_init(cmd_btn) {
            cmd_btn.append($.renderIcon('pencil-alt-light '));
        }

        _commandPencil_exec(cmd_btn) {
            const $this = this;

            this._svg.unbind('mousedown.tool-pencil').bindUp('mousedown.tool-pencil', function (e) {
                e.preventDefault;
                e.stopPropagation();
                e.stopImmediatePropagation();

                $this._objectUnselectCurrent();

                var points = [];
                var path = $(document.createElementNS($this._svg[0].namespaceURI, 'path')).attr({
                    fill: 'none',
                    stroke: '#ddd',
                    "stroke-width": 0.5 / $this._zoom_ratio
                }).bind('object-update', function () {
                    var d = '';

                    for (var i = 0; i < points.length; i++) {
                        var point = points[i];

                        if (i === 0) {
                            d = 'M' + point.x + ',' + point.y;
                        } else {
                            d += ' L' + point.x + ',' + point.y;
                        }
                    }

                    this.setAttribute('d', d);
                }).appendTo($this._svg_group.helper);

                $this._svg.unbind('mousemove.tool-pencil').bind('mousemove.tool-pencil', function (e) {
                    points.push($this._cursorPoint(e));
                    path.trigger('object-update');
                });

                $(document).bind('mouseup.tool-pencil', function () {
                    if ($(cmd_btn).hasClass('active')) {
                        $(cmd_btn).trigger('click');
                    }
                    const bbox = $this._elmGetBBox(path[0])

                    path.remove();
                    if(bbox.width <= 0 || bbox.height <= 0) {
                        return
                    }
                    const simplified_points = $this._objectPath_simplify(points, 15);
                    if (simplified_points.length > 0) {
                        $this._objectAdd('path', {closed: false, bbox: {x: 0, y: 0, width: 0, height: 0}, points: simplified_points, rotation: 0});
                    }
                });
            });
        }

        _commandPencil_clear() {
            this._svg.unbind('mousedown.tool-pencil');
            this._svg.unbind('mousemove.tool-pencil');
            $(document).unbind('mouseup.tool-pencil');
        }

        _commandPen_init(cmd_btn) {
            cmd_btn.append($.renderIcon('pen-nib'));
        }

        _commandPen_exec(cmd_btn) {
            var $this = this;

            this._svg.bindUp('mousedown.path-drawer', function (e) {
                e.preventDefault;
                e.stopPropagation();
                e.stopImmediatePropagation();

                var object = $this._objectPath_getCurrent();

                if (object === null || object.data.type_data.closed || $this._objectPath_getCurrentPoint() === null) {
                    object = $this._objectAdd('path', {closed: false, bbox: {x: 0, y: 0, width: 0, height: 0}, points: [], rotation: 0});
                }

                $this._objectEdit(object.data.key);

                var point = $this._getPointExceptRotation($this._cursorPoint(e), $this._elmGetBBox(object.elm), object.data.type_data.rotation);

                var points = object.data.type_data.points;

                if (points.length > 0 && (points[0].point.x - 3) <= point.x && ((points[0].point.x + 3) >= point.x) && (points[0].point.y - 3) <= point.y && ((points[0].point.y + 3) >= point.y)) {
                    object.data.type_data.closed = true;
                    object.point_selected_index = 0;
                } else {
                    points.push({
                        index: points.length,
                        point: point
                    });

                    object.point_selected_index = points.length - 1;
                }

                $(object.elm).trigger('object-update');

                $this._svg.unbind('mousemove.path-drawer').bind('mousemove.path-drawer', function (e) {
                    $this._objectPath_editHandle(e, object.data.type_data.closed ? 2 : 1, object);
                });

                $(document).bind('mouseup.path-drawer', function () {
                    $this._svg.unbind('mousemove.path-drawer');
                    $(document).unbind('mouseup.path-drawer');

                    if (object.data.type_data.closed) {
                        delete object.point_selected_index;

                        if ($(cmd_btn).hasClass('active')) {
                            $(cmd_btn).trigger('click');
                        }
                    }
                });
            });
        }

        _commandPen_clear() {
            this._svg.unbind('mousedown.path-drawer');
        }

        _commandPenAddPoint_init(cmd_btn) {
            cmd_btn.append($.renderIcon('pen-nib-light')).append($.renderIcon('plus-light'));
        }

        _commandPenAddPoint_exec() {
            var $this = this;

            this._svg.bind('mousemove.tool-penAddPoint', function (e) {
                var object = $this._objectPath_getCurrent();

                if (!object) {
                    return;
                }

                var point = $this._objectPath_closestPoint(object, $this._getPointExceptRotation($this._cursorPoint(e), $this._elmGetBBox(object.elm), object.data.type_data.rotation));

                if (!point) {
                    if (object.closest_point && object.closest_point.circle.parentNode) {
                        object.closest_point.circle.parentNode.removeChild(object.closest_point.circle);
                    }

                    delete object.closest_point;

                    return;
                }

                if (object.closest_point) {
                    point.circle = object.closest_point.circle;
                } else {
                    point.circle = document.createElementNS($this._svg[0].namespaceURI, 'circle');
                    point.circle.setAttribute('r', 2);
                    point.circle.setAttribute('fill', '#fff');
                    point.circle.setAttribute('stroke', '#000');
                    point.circle.setAttribute('stroke-width', 1 / $this._zoom_ratio);

                    object.helper.appendChild(point.circle);
                }

                point.circle.setAttribute('cx', point.point.x);
                point.circle.setAttribute('cy', point.point.y);

                object.closest_point = point;
            }).bindUp('mousedown.tool-penAddPoint', function (e) {
                var object = $this._objectPath_getCurrent();

                if (!object || !object.closest_point) {
                    return;
                }

                e.preventDefault;
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (!object.data.type_data.points[object.closest_point.path.point].handle_out && !object.data.type_data.points[object.closest_point.path.next_point].handle_in) {
                    var left_point = object.data.type_data.points[object.closest_point.path.point];
                    var center_point = {point: {x: object.closest_point.point.x, y: object.closest_point.point.y}};
                    var right_point = object.data.type_data.points[object.closest_point.path.next_point];
                } else {
                    var curve = new Bezier($this._objectPath_getCurveCoords(object.data.type_data.points[object.closest_point.path.point], object.data.type_data.points[object.closest_point.path.next_point]));

                    var splited = curve.split(object.closest_point.t);

                    var left_point = object.data.type_data.points[object.closest_point.path.point];

                    if (splited.left.points[0].x !== splited.left.points[1].x || splited.left.points[0].y !== splited.left.points[1].y) {
                        left_point.handle_out = {
                            x: splited.left.points[1].x - left_point.point.x,
                            y: splited.left.points[1].y - left_point.point.y
                        };
                    } else {
                        delete left_point.handle_out;
                    }

                    var center_point = {
                        point: splited.left.points[3] ? splited.left.points[3] : splited.left.points[2]
                    };

                    if (splited.left.points[3] && (splited.left.points[2].x !== splited.left.points[3].x || splited.left.points[2].y !== splited.left.points[3].y)) {
                        center_point.handle_in = {
                            x: splited.left.points[2].x - center_point.point.x,
                            y: splited.left.points[2].y - center_point.point.y
                        };
                    }

                    if (splited.right.points[0].x !== splited.right.points[1].x || splited.right.points[0].y !== splited.right.points[1].y) {
                        center_point.handle_out = {
                            x: splited.right.points[1].x - center_point.point.x,
                            y: splited.right.points[1].y - center_point.point.y
                        };
                    }

                    var right_point = object.data.type_data.points[object.closest_point.path.next_point];

                    if (splited.right.points[3] && (splited.right.points[2].x !== splited.right.points[3].x || splited.right.points[2].y !== splited.right.points[3].y)) {
                        right_point.handle_in = {
                            x: splited.right.points[2].x - right_point.point.x,
                            y: splited.right.points[2].y - right_point.point.y
                        };
                    } else {
                        delete right_point.handle_in;
                    }
                }

                var points = [];

                for (var i = 0; i < object.data.type_data.points.length; i++) {
                    if (i === object.closest_point.path.point || i === object.closest_point.path.next_point) {
                        if (i === object.closest_point.path.point) {
                            points.push(left_point);
                            points.push(center_point);
                            points.push(right_point);
                        }

                        continue;
                    }

                    points.push(object.data.type_data.points[i]);
                }

                object.data.type_data.points = points;

                for (var i = 0; i < object.data.type_data.points.length; i++) {
                    object.data.type_data.points[i].index = i;
                }

                object.point_selected_index = object.closest_point.path.next_point;

                if (object.closest_point && object.closest_point.circle.parentNode) {
                    object.closest_point.circle.parentNode.removeChild(object.closest_point.circle);
                }

                delete object.closest_point;


                $(object.elm).trigger('object-update');
            });
        }

        _commandPenAddPoint_clear() {
            this._svg.unbind('.tool-penAddPoint');
        }

        _commandPenRemovePoint_init(cmd_btn) {
            cmd_btn.append($.renderIcon('pen-nib-light')).append($.renderIcon('minus-light'));
        }

        _commandPenEditPoint_init(cmd_btn) {
            cmd_btn.append($.renderIcon('vector-edit'));
        }

        _objectPath_render(data, key) {
            var path = document.createElementNS(this._svg[0].namespaceURI, 'path');

            path.setAttribute('data-icon', 'bezier-curve');

            var $this = this;

            $(path).bind('object-clearHelper', function () {
                $(this).unbind('.helper-update');
            }).bind('move', function (e, bounding_rect) {
                var path_bounding_rect = $this._elmGetBBox(path);

                if (path_bounding_rect.x === bounding_rect.x && path_bounding_rect.y === bounding_rect.y) {
                    return;
                }

                var x = bounding_rect.x - path_bounding_rect.x;
                var y = bounding_rect.y - path_bounding_rect.y;

                data.points.forEach(p => {
                    p.point.x += x;
                    p.point.y += y;
                })

                $(path).trigger('object-update');
            }).bind('resize', function (e, bounding_rect) {
                var path_bounding_rect = $this._elmGetBBox(path);

                if (path_bounding_rect.width === bounding_rect.width
                    && path_bounding_rect.height === bounding_rect.height) {
                    return;
                }

                var ratio_x = bounding_rect.width / path_bounding_rect.width;
                var ratio_y = bounding_rect.height / path_bounding_rect.height;

                data.points.forEach(point => {
                    point.point.x = bounding_rect.x + (point.point.x - path_bounding_rect.x) * ratio_x;
                    point.point.y = bounding_rect.y + (point.point.y - path_bounding_rect.y) * ratio_y;

                    if (point.handle_in) {
                        point.handle_in.x = point.handle_in.x * ratio_x;
                        point.handle_in.y = point.handle_in.y * ratio_y;
                    }

                    if (point.handle_out) {
                        point.handle_out.x = point.handle_out.x * ratio_x;
                        point.handle_out.y = point.handle_out.y * ratio_y;
                    }
                })

                $(path).trigger('object-update');
            }).bind('rotate', function (e, degress) {
                // if (typeof object === 'undefined') {
                //     object = $this._objectPath_getCurrent();
                // }

                // if (!object || object.elm !== path) {
                //     return;
                // }

                data.rotation = degress;

                $this._elmSetTransform(path, 0, 0, data.rotation);
            }).bind('object-update', function () {
                this.setAttribute('d', $this._objectPath_makeDData(data.points, data.closed));
                var bbox = this.getBBox();

                data.bbox = {
                    x: bbox.x,
                    y: bbox.y,
                    width: bbox.width,
                    height: bbox.height
                };

                $this._elmSetTransform(path, 0, 0, data.rotation);
            })

            return path;
        }

        _objectPath_renderByRect(rect) {
            var x = parseFloat(rect.getAttribute('x'));
            var y = parseFloat(rect.getAttribute('y'));
            var width = parseFloat(rect.getAttribute('width'));
            var height = parseFloat(rect.getAttribute('height'));
            $(rect).trigger("object-remove")
            var path = document.createElementNS(this._svg[0].namespaceURI, 'path');

            path.setAttribute('fill', rect.getAttribute('fill'));
            path.setAttribute('fill-opacity', rect.getAttribute('fill-opacity'));
            path.setAttribute('stroke', rect.getAttribute('stroke'));
            path.setAttribute('stroke-width', rect.getAttribute('stroke-width'));
            path.setAttribute('stroke-opacity', rect.getAttribute('stroke-opacity'));
            path.setAttribute('d', 'M' + x + ',' + y + 'l' + width + ',0l0,' + height + 'l' + (-width) + ',0l0,' + (-height) + 'z');

            return path;
        }

        _objectPath_renderByEllipse(ellipse) {
            var cx = parseFloat(ellipse.getAttribute('cx'));
            var cy = parseFloat(ellipse.getAttribute('cy'));
            var rx = parseFloat(ellipse.getAttribute('rx'));
            var ry = parseFloat(ellipse.getAttribute('ry'));
            $(ellipse).trigger("object-remove")
            var path = document.createElementNS(this._svg[0].namespaceURI, 'path');

            path.setAttribute('fill', ellipse.getAttribute('fill'));
            path.setAttribute('fill-opacity', ellipse.getAttribute('fill-opacity'));
            path.setAttribute('stroke', ellipse.getAttribute('stroke'));
            path.setAttribute('stroke-width', ellipse.getAttribute('stroke-width'));
            path.setAttribute('stroke-opacity', ellipse.getAttribute('stroke-opacity'));
            path.setAttribute('d', "M" + (cx - rx) + "," + cy + "a" + rx + "," + ry + " 0 1,0 " + (2 * rx) + ",0a" + rx + "," + ry + " 0 1,0 " + (-2 * rx) + ",0");

            return path;
        }

        _objectPath_editorRender(object) {
            var $this = this;

            $(object.helper).html('');

            $(object.elm).bind("object-clearHelper", function(){
                $(document).unbind('keydown.path-drawer')
                $this._svg.unbind('mousemove.path-drawer')
            }).unbind('.helper-' + object.key).bind('object-update.helper-' + object.key, function () {
                $this._objectPath_editorRender(object);
            });

            if (!this._commandIsActive('pen') && !this._commandIsActive('penAddPoint') && !this._commandIsActive('penRemovePoint') && !this._commandIsActive('penEditPoint')) {
                this._commandActive('penEditPoint');
            }

            var selected_point = this._objectPath_getCurrentPoint();

            if (selected_point) {
                var handles = [[selected_point, ['handle_in', 'handle_out']]];

                if (object.data.type_data.points[selected_point.index + 1]) {
                    handles.push([object.data.type_data.points[selected_point.index + 1], ['handle_in']]);
                }

                if (object.data.type_data.points[selected_point.index - 1]) {
                    handles.push([object.data.type_data.points[selected_point.index - 1], ['handle_out']]);
                }

                for (var x = 0; x < handles.length; x++) {
                    for (var y = 0; y < handles[x][1].length; y++) {
                        var _point = handles[x][0];
                        var handle_point = _point[handles[x][1][y]];

                        if (typeof handle_point === 'undefined') {
                            continue;
                        }

                        handle_point = {
                            x: _point.point.x + handle_point.x,
                            y: _point.point.y + handle_point.y
                        };

                        var handle_path = document.createElementNS(this._svg[0].namespaceURI, 'path');
                        handle_path.setAttribute('fill', 'none');
                        handle_path.setAttribute('stroke', 'red');
                        handle_path.setAttribute('stroke-width', 0.5 / $this._zoom_ratio);
                        handle_path.setAttribute('d', 'M' + _point.point.x + ',' + _point.point.y + ' L' + handle_point.x + ',' + handle_point.y);

                        object.helper.appendChild(handle_path);

                        var handle_circle = document.createElementNS(this._svg[0].namespaceURI, 'circle');

                        handle_circle.setAttribute('r', 3 / $this._zoom_ratio);
                        handle_circle.setAttribute('cx', handle_point.x);
                        handle_circle.setAttribute('cy', handle_point.y);
                        handle_circle.setAttribute('fill', '#000');
                        handle_circle.setAttribute('data-point-index', _point.index);
                        handle_circle.setAttribute('data-handle', handles[x][1][y]);

                        $(handle_circle).mousedown(function () {
                            if (!$this._commandIsActive('penEditPoint')) {
                                return;
                            }

                            object.point_selected_index = parseInt(this.getAttribute('data-point-index'));

                            var mode = this.getAttribute('data-handle') === 'handle_in' ? 3 : 4;

                            $this._svg.unbind('mousemove.path-drawer').bind('mousemove.path-drawer', function (e) {
                                $this._objectPath_editHandle(e, mode, object);
                            });

                            $(document).bind('mouseup.path-drawer', function () {
                                $this._svg.unbind('mousemove.path-drawer');
                                $(document).unbind('mouseup.path-drawer');
                            });
                        });

                        object.helper.appendChild(handle_circle);
                    }
                }
            }

            for (var i = 0; i < object.data.type_data.points.length; i++) {
                var point = object.data.type_data.points[i];

                var rect = document.createElementNS(this._svg[0].namespaceURI, 'rect');
                
                const radius = 3 / $this._zoom_ratio

                rect.setAttribute('x', point.point.x - radius);
                rect.setAttribute('y', point.point.y - radius);
                rect.setAttribute('width', radius * 2);
                rect.setAttribute('height', radius * 2);

                if (i !== object.point_selected_index) {
                    rect.setAttribute('stroke', '#000');
                    rect.setAttribute('fill', '#fff');
                } else {
                    rect.setAttribute('stroke', 'none');
                    rect.setAttribute('fill', '#000');
                }

                rect.setAttribute('data-point-index', i);

                $(rect).click(function () {
                    var point_index = parseInt(this.getAttribute('data-point-index'));

                    if ($this._commandIsActive('penRemovePoint')) {
                        var points = [];

                        for (var i = 0; i < object.data.type_data.points.length; i++) {
                            if (i === point_index) {
                                continue;
                            }

                            var new_index = points.length;
                            points.push(object.data.type_data.points[i]);
                            points[new_index].index = new_index;
                        }

                        if (points.length < 2) {
                            points = [];
                        }

                        object.data.type_data.points = points;

                        if (point_index === object.point_selected_index) {
                            delete object.point_selected_index;
                        } else if (object.point_selected_index > point_index && points.length > 0) {
                            object.point_selected_index--;
                        }

                        $(object.elm).trigger('object-update');
                    } else if ($this._commandIsActive('pen') || $this._commandIsActive('penEditPoint')) {
                        if (point_index !== object.point_selected_index) {
                            object.point_selected_index = point_index;
                            $this._objectPath_editorRender(object);
                        }
                    }
                }).dblclick(function () {
                    if (!$this._commandIsActive('penEditPoint')) {
                        return;
                    }

                    var point_index = parseInt(this.getAttribute('data-point-index'));

                    delete object.data.type_data.points[point_index].handle_in;
                    delete object.data.type_data.points[point_index].handle_out;

                    $(object.elm).trigger('object-update');
                }).mousedown(function () {
                    if (!$this._commandIsActive('penEditPoint')) {
                        return;
                    }

                    var node = this;

                    $this._svg.unbind('mousemove.path-drawer').bind('mousemove.path-drawer', function (e) {
                        object.point_selected_index = parseInt(node.getAttribute('data-point-index'));
                        $this._objectPath_editHandle(e, 1, object);
                    });

                    $(document).bind('mouseup.path-drawer', function () {
                        $this._svg.unbind('mousemove.path-drawer');
                        $(document).unbind('mouseup.path-drawer');
                    });
                });

                object.helper?.appendChild(rect);
            }

            $(document).unbind('keydown.path-drawer').bind('keydown.path-drawer', function (e) {
                if ((e.target.nodeName && ['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) < 0 )
                || e.target.className.includes("svg-editor-text-helper")) {
                    return
                }

                var point = $this._objectPath_getCurrentPoint();

                if (!point) {
                    return;
                }

                var value = e.shiftKey ? 10 : 1;

                if (e.keyCode === 38) {
                    point.point.y -= value;
                } else if (e.keyCode === 40) {
                    point.point.y += value;
                } else if (e.keyCode === 37) {
                    point.point.x -= value;
                } else if (e.keyCode === 39) {
                    point.point.x += value;
                }

                $($this._objectPath_getCurrent().elm).trigger('object-update');
            });

            var degrees = object.data.type_data.rotation;

            var bbox = this._elmGetBBox(object.elm);

            var matrix = this._svg[0].createSVGMatrix()
                    .translate((bbox.x + bbox.width / 2), (bbox.y + bbox.height / 2))
                    .rotate(degrees)
                    .translate(-(bbox.x + bbox.width / 2), -(bbox.y + bbox.height / 2));

            object.helper?.transform.baseVal.initialize(this._svg[0].createSVGTransformFromMatrix(matrix));
        }

        _objectPath_getCurrent() {
            return this._objectGetCurrent('path');
        }

        _objectPath_getCurrentPoint() {
            var object = this._objectPath_getCurrent();

            if (!object || typeof object.point_selected_index !== 'number' || !object.data.type_data.points[object.point_selected_index]) {
                return null;
            }

            return object.data.type_data.points[object.point_selected_index];
        }

        _objectPath_editHandle(e, mode, object) {
            var point = this._objectPath_getCurrentPoint();

            if (!point) {
                return;
            }

            var cursor = this._getPointExceptRotation(this._cursorPoint(e), this._elmGetBBox(object.elm), object.data.type_data.rotation);
            var object = this._objectPath_getCurrent();

            mode = parseInt(mode);

            if (isNaN(mode) || mode < 1) {
                mode = 1;
            }

            /**
             * Mode 1: edit handle out and set opposite value to handle in
             * Mode 2: edit handle in and change handle out angle
             * Mode 3: edit handle in
             * Mode 4: edit handle out
             */

            if (e.shiftKey) {
                var shifted_angle = Math.round(Math.atan2(cursor.y - point.point.y, cursor.x - point.point.x) / Math.PI * 4) / 4 * Math.PI;
                var distance = this._pointGetDistance(point.point, cursor);

                cursor = {
                    x: point.point.x + distance * Math.cos(shifted_angle),
                    y: point.point.y + distance * Math.sin(shifted_angle)
                };
            }

            if (mode > 1 && mode < 4) {
                point.handle_in = {
                    x: cursor.x - point.point.x,
                    y: cursor.y - point.point.y
                };
            } else {
                point.handle_out = {
                    x: cursor.x - point.point.x,
                    y: cursor.y - point.point.y
                };
            }

            if (mode === 1) {
                point.handle_in = {
                    x: -point.handle_out.x,
                    y: -point.handle_out.y
                };
            } else if (mode === 2) {
                if (point.handle_out) {
                    /*
                     var Point = {X: 25, Y: 35};
                     var Target = {X:45, Y:65};

                     var Angle = Math.atan2(Target.Y - Point.Y, Target.X - Point.X);

                     var Per_Frame_Distance = 2;
                     var Sin = Math.sin(Angle) * Per_Frame_Distance;
                     var Cos = Math.cos(Angle) * Per_Frame_Distance;

                     Now get the Sine and Cosine of that angle, the Sine is the value to move along the Y axis, and the Cosine is how much to move on the X axis. Multiply the sine and cosine by the distance you want to move each frame.
                     */

                    var distance = this._pointGetDistance({x: 0, y: 0}, point.handle_out);
                    var angle = Math.atan2(point.handle_in.y, point.handle_in.x);

                    point.handle_out.x = -(Math.cos(angle) * distance);
                    point.handle_out.y = -(Math.sin(angle) * distance);
                }
            }

            if (point.handle_in && point.handle_in.x === 0 && point.handle_in.y === 0) {
                delete point.handle_in;
            }

            if (point.handle_out && point.handle_out.x === 0 && point.handle_out.y === 0) {
                delete point.handle_out;
            }

            $(object.elm).trigger('object-update');
        }

        _objectPath_closestPoint(object, cursor) {
            var points = object.data.type_data.points;

            if (points.length < 2) {
                return null;
            }

            var closest_point = null;

            for (var i = 0; i < (object.data.type_data.closed ? points.length : (points.length - 1)); i++) {
                var point = points[i];
                var next_point = points[(i === points.length - 1) ? 0 : (i + 1)];

                try {
                    var curve = new Bezier(this._objectPath_getCurveCoords(point, next_point));

                    var _closest_point = curve.project(cursor);

                    if (_closest_point.d < 6 && (!closest_point || _closest_point.d < closest_point.distance)) {
                        closest_point = {
                            t: _closest_point.t,
                            distance: _closest_point.d,
                            point: {
                                x: _closest_point.x,
                                y: _closest_point.y
                            },
                            path: {
                                point: point.index,
                                next_point: next_point.index
                            }
                        };
                    }
                } catch (e) {

                }
            }

            return closest_point;
        }

        _objectPath_getCurveCoords(point, next_point) {
            var coords = [];

            coords.push({x: point.point.x, y: point.point.y});

            if (!point.handle_out && !next_point.handle_in) {
                coords.push({x: point.point.x, y: point.point.y});
            } else {
                if (point.handle_out) {
                    coords.push({x: point.point.x + point.handle_out.x, y: point.point.y + point.handle_out.y});
                }

                if (next_point.handle_in) {
                    coords.push({x: next_point.handle_in.x + next_point.point.x, y: next_point.handle_in.y + next_point.point.y});
                }
            }

            coords.push({x: next_point.point.x, y: next_point.point.y});

            return coords;
        }

        _objectPath_simplify(points, simplify) {
            simplify = parseInt(simplify);

            if (isNaN(simplify) || simplify < 1) {
                simplify = 10;
            }

            if (points.length < 2) {
                return [];
            }

            var _points = [];

            points.forEach(function (point) {
                _points.push([point.x, point.y]);
            });

            var calculated_points = fitCurve(_points, simplify);

            if (calculated_points.length < 1) {
                return [];
            }

            var simplified_points = [{}];

            calculated_points.forEach(function (point) {
                var index = simplified_points.length - 1;

                simplified_points[index].index = index;

                simplified_points[index].point = {
                    x: point[0][0],
                    y: point[0][1]
                };

                simplified_points[index].handle_out = {
                    x: point[1][0] - point[0][0],
                    y: point[1][1] - point[0][1]
                };

                var index = simplified_points.length;

                simplified_points[index] = {
                    index: index,
                    point: {
                        x: point[3][0],
                        y: point[3][1]
                    },
                    handle_in: {
                        x: point[2][0] - point[3][0],
                        y: point[2][1] - point[3][1]
                    }
                };
            });

            return simplified_points;
        }

        _objectPath_makeDData(points, closed) {
            var __drawLine = function (d, point, prev_point) {
                var first_point = null;
                var second_point = null;

                if (prev_point.handle_out) {
                    first_point = {
                        x: prev_point.handle_out.x + prev_point.point.x,
                        y: prev_point.handle_out.y + prev_point.point.y
                    };
                }

                if (point.handle_in) {
                    if (!first_point) {
                        first_point = {
                            x: point.handle_in.x + point.point.x,
                            y: point.handle_in.y + point.point.y
                        };
                    } else {
                        second_point = {
                            x: point.handle_in.x + point.point.x,
                            y: point.handle_in.y + point.point.y
                        };
                    }
                }

                if (first_point) {
                    if (second_point) {
                        d += ' C' + first_point.x + ',' + first_point.y + ',' + second_point.x + ',' + second_point.y + ',' + point.point.x + ',' + point.point.y;
                    } else {
                        d += ' Q' + first_point.x + ',' + first_point.y + ',' + point.point.x + ',' + point.point.y;
                    }
                } else {
                    d += ' L' + point.point.x + ',' + point.point.y;
                }

                return d;
            };

            var d = '';

            for (var i = 0; i < points.length; i++) {
                var point = points[i];

                if (i === 0) {
                    d = 'M' + point.point.x + ',' + point.point.y;
                } else {
                    d = __drawLine(d, point, points[i - 1]);
                }
            }

            if (closed && points.length > 1) {
                d = __drawLine(d, points[0], points[points.length - 1]);
            }

            return d;
        }

        _commandText_init(cmd_btn) {
            cmd_btn.append($.renderIcon('text'));
        }

        _commandText_exec(cmd_btn) {
            var $this = this;

            this._svg.unbind('mousedown.tool-text').bindUp('mousedown.tool-text', function (e) {
                e.preventDefault;
                e.stopPropagation();
                e.stopImmediatePropagation();

                $this._objectUnselectCurrent();

                var cursor = $this._cursorPoint(e);

                var rect = $(document.createElementNS($this._svg[0].namespaceURI, 'rect')).attr({
                    fill: 'none',
                    stroke: '#ddd',
                    "stroke-width": 0.5 / $this._zoom_ratio
                }).appendTo($this._svg_group.helper);

                $this._svg.unbind('mousemove.tool-text').bind('mousemove.tool-text', function (e) {
                    var data = $this._objectRect_calculateData(e, cursor);

                    rect.attr({
                        x: data.position.x,
                        y: data.position.y,
                        width: data.size.width,
                        height: data.size.height
                    });
                });

                $(document).bind('mouseup.tool-text', function () {
                    if ($(cmd_btn).hasClass('active')) {
                        $(cmd_btn).trigger('click');
                    }
                    const width = rect.attr('width'),
                        height = rect.attr('height')

                    if(width && height && Number(width) > 0 && Number(height) > 0) {
                        $this._objectAdd('text', {
                            offset: 0,
                            fill: '#000',
                            stroke: {
                                color: 'none',
                                width: 1
                            },
                            position: {
                                x: parseInt(rect.attr('x')),
                                y: parseInt(rect.attr('y'))
                            },
                            size: {
                                width: parseInt(width),
                                height: parseInt(height)
                            },
                            style: {
                                text_align: 'left',
                                vertical_align: 'bottom',
                                font_size: Math.round(13 / $this._zoom_ratio),
                                font_name: 'Arial',
                                font_style: 'Regular',
                                color: '#333',
                                line_height: 1.5,
                                letter_spacing: 0,
                                word_spacing: 0
                            },
                            content: 'Text',
                            rotation: 0
                        });
                    }

                    rect.remove();
                });
            });
        }

        _commandText_clear() {
            this._svg.unbind('mousedown.tool-text');
            this._svg.unbind('mousemove.tool-text');
            $(document).unbind('mouseup.tool-text');
        }

        _objectText_makeDiv(data) {
            const ratio = this._zoom_ratio;

            return $('<div />').addClass('svg-editor-text-helper').css({
                width: data.size.width * ratio + 'px',
                height: data.size.height * ratio + 'px',
                fontFamily: data.style.font_name,
                fontSize: data.style.font_size * ratio,
                fontWeight: data.style.font_style.indexOf('Bold') >= 0 ? 'bold' : 'normal',
                fontStyle: data.style.font_style.indexOf('Italic') >= 0 ? 'italic' : 'normal',
                lineHeight: 1.5,
                letterSpacing: data.style.letter_spacing,
                wordSpacing: data.style.word_spacing,
                textAlign: data.style.text_align,
                padding: 0,
                margin: 0,
                overflow: 'hidden',
                textOverflow: 'hidden',
                whiteSpace: 'nowrap',
                border: 0
            })[0];
        }

        _objectText_getCurrent() {
            return this._objectGetCurrent('text');
        }

        _getBBoxTextPath(points) {
            let minX = Infinity, minY= Infinity, maxX = -Infinity, maxY = -Infinity;
            points.forEach(p => {
                const {x,y } = p.point;
                minX = x < minX ? x : minX;
                minY = y < minY ? y : minY;
                maxX = x > maxX ? x : maxX;
                maxY = y > maxY ? y : maxY;
            })
            return {x: minX, y: minY, width: maxX - minX, height: maxY - minY}
        }

        _objectText_render(data, textKey) {
            var $this = this;

            var text = document.createElementNS(this._svg[0].namespaceURI, 'text');

            text.setAttribute('data-icon', 'text');

            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");

            const __calculateFontSize = () => {

                const object_data = {};

                $(text).trigger('object-get-data', [object_data]);

                const personalized = object_data.data.personalized;
                const font_size = data.style.font_size;

                if (!personalized?.config.is_dynamic_input || !!data.path) {
                    if (data.style.dynamic_font_size) {
                        delete data.style.dynamic_font_size;
                    }
                    return font_size;
                }

                const size = data.size;
                ctx.font = `${font_size}px ${data.style.font_name}`;
                let textContent = data.content.split(/[\r\n]/);
                const maxLines = object_data?.data?.personalized?.config?.is_dynamic_input ? object_data?.data?.personalized?.config?.max_lines : 1;
                if (textContent.length > maxLines) {
                    textContent = [
                        ...textContent.slice(0, maxLines - 1),
                        textContent.slice(maxLines - 1).join(' '),
                    ];
                }
                let maxLengthText = textContent.reduce((a, b) => {
                    return a.length > b.length ? a : b;
                }, '');
                let measure = ctx.measureText(maxLengthText);
                const text_width = measure.width;
                let new_font_size = font_size;

                if (text_width > size.width) {
                    new_font_size = font_size / text_width  * size.width;
                    ctx.font = `${new_font_size}px ${data.style.font_name}`;
                    measure = ctx.measureText(text);
                }

                let measureHeight = measure.fontBoundingBoxAscent + measure.fontBoundingBoxDescent + new_font_size * data.style.line_height * (textContent.length - 1);

                if (measureHeight > size.height ) {
                    new_font_size = new_font_size / measureHeight * size.height;
                }

                data.style.dynamic_font_size = new_font_size;

                return new_font_size;
            }

            function __setTransform() {
                var matrix = $this._svg[0].createSVGMatrix()
                        .translate((data.position.x + data.size.width / 2), (data.position.y + data.size.height / 2))
                        .rotate(data.rotation)
                        .translate(-(data.position.x + data.size.width / 2), -(data.position.y + data.size.height / 2));

                text.transform.baseVal.initialize($this._svg[0].createSVGTransformFromMatrix(matrix));
            }

            const __renderTextPath = () => {
                this._svg_group.defs.find('#text-path-' + data.path.key).remove();
                let path_elm = this._objectAdd(data.path.type, data.path.data, data.path.key, 3);
                switch (data.path.type) {
                    case "rect":
                        path_elm = this._objectPath_renderByRect(path_elm);
                        break;
                    case "ellipse":
                        path_elm = this._objectPath_renderByEllipse(path_elm);
                        break;
                    case "path":
                        const points = data.path.data?.points || [];
                        const bbox = this._getBBoxTextPath(points);
                        data.path.data.bbox = bbox;
                        break;
                    default:
                        break;
                }

                path_elm.setAttribute('id', 'text-path-' + data.path.key);
                $(path_elm).trigger("object-remove")

                this._svg_group.defs.append(path_elm);

                $(text).html('');

                // text.setAttribute('dominant-baseline', 'text-after-edge');
                text.setAttribute('dominant-baseline', 'auto');
                text.setAttribute('x', '0');

                var textpath = document.createElementNS(this._svg[0].namespaceURI, 'textPath');
                textpath.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', '#text-path-' + data.path.key);
                textpath.appendChild(document.createTextNode(data.content));
                let offset = 0;
                if(data.offset && typeof data.offset === "number")
                    offset = data.offset;

                textpath.setAttribute('startOffset', offset + '%');

                text.appendChild(textpath);
            }

            const __tranformTextPath = (bounding_rect) => {
                const path = data.path;
                if(!path) return;
                let translateX = bounding_rect.x - data.position.x,
                    translateY = bounding_rect.y - data.position.y;

                switch (path.type) {
                    case "path":
                        path.data?.points?.forEach(p => {
                            p.point.x += translateX;
                            p.point.y += translateY;
                        })
                        break;
                    case "ellipse":
                        if(!path.data?.center) break;
                        path.data.center.x += translateX;
                        path.data.center.y += translateY;
                        break;
                    case "rect":
                        if(!path.data?.position) break;
                        path.data.position.x = bounding_rect.x;
                        path.data.position.y = bounding_rect.y;
                    default:
                        break;
                }

                __renderTextPath()
            }

            const __onMove = (bounding_rect) => {
                var modified_x = bounding_rect.x - data.position.x;
                var modified_y = bounding_rect.y - data.position.y;

                data.position.x = bounding_rect.x;
                data.position.y = bounding_rect.y;

                text.querySelectorAll('tspan').forEach(function (tspan) {
                    tspan.setAttribute('x', parseFloat(tspan.getAttribute('x')) + modified_x);
                });
                text.setAttribute('y', parseFloat(text.getAttribute('y')) + modified_y);

                __setTransform();

            }


            $(text).bind('move', function (e, bounding_rect) {
                e.stopPropagation();
                __tranformTextPath(bounding_rect);
                __onMove(bounding_rect);
            })
            .bind('path-move', function (e, bounding_rect) {
                e.stopPropagation();

                __onMove(bounding_rect);
            })
            .bind('resize', function (e, bounding_rect) {
                e.stopPropagation();
                __tranformTextPath(bounding_rect)

                data.position.x = bounding_rect.x;
                data.position.y = bounding_rect.y;
                data.size.width = bounding_rect.width;
                data.size.height = bounding_rect.height;
                $(text).trigger('object-update');
            })
            .bind('object-update', function (e) {
                e.stopPropagation();

                for (var i = 0; i < document.body.childNodes.length; i++) {
                    if (document.body.childNodes[i].className === 'svg-editor-text-helper') {
                        document.body.removeChild(document.body.childNodes[i]);
                        break;
                    }
                }
                $this._objectText_loadFont(data.style.font_name);
                const font_size = __calculateFontSize()
                text.setAttribute('font-family', data.style.font_name);
                text.setAttribute('font-size', font_size);
                text.setAttribute('font-weight', data.style.font_style.indexOf('Bold') >= 0 ? 'bold' : 'normal');
                text.setAttribute('font-style', data.style.font_style.indexOf('Italic') >= 0 ? 'italic' : 'normal');
                text.setAttribute('letter-spacing', data.style.letter_spacing);
                text.setAttribute('word-spacing', data.style.word_spacing);

                text.setAttribute('text-anchor', data.style.text_align === 'center' ? 'middle' : (data.style.text_align === 'left' ? 'start' : 'end'));
                //xml:space="preserve"

                if (data.path) {
                    __renderTextPath();

                    __setTransform();
                    return;
                }

                $(text).html('');

                let baseline, y = data.position.y;
                switch (data.style.vertical_align) {
                    case 'top':
                        baseline = 'hanging';
                        break;
                    case 'middle':
                        y = y + data.size.height / 2;
                        baseline = 'middle';
                        break;
                    default:
                        y = y + data.size.height;
                        baseline = 'baseline';
                        break;
                }

                text.setAttribute('dominant-baseline', baseline);
                text.setAttribute('x', data.position.x);
                text.setAttribute('y', y);

                const object_data = {};

                $(text).trigger('object-get-data', [object_data]);

                let textContent = data.content.split(/[\r\n]/);
                const maxLines = object_data?.data?.personalized?.config?.is_dynamic_input ? object_data?.data?.personalized?.config?.max_lines : 1;

                if (textContent.length > maxLines) {
                    textContent = [
                        ...textContent.slice(0, maxLines - 1),
                        textContent.slice(maxLines - 1).join(' '),
                    ];
                }

                const textContentLines = textContent.length;

                const lineHeight = data.style.line_height;

                textContent.forEach((lineOfText, index) => {
                    let tspan = document.createElementNS($this._svg[0].namespaceURI, 'tspan');
                    $(tspan).css('font-size', 'inherit');
                    tspan.appendChild(document.createTextNode(lineOfText === "" ? " " : lineOfText));
                    tspan.setAttribute('text-anchor', data.style.text_align === 'center' ? 'middle' : (data.style.text_align === 'left' ? 'start' : 'end'));
                    tspan.setAttribute('data-object', text.getAttribute('data-object'));

                    if (data.style.text_align === 'center') {
                        tspan.setAttribute('x', data.position.x + data.size.width / 2);
                    } else if (data.style.text_align === 'right') {
                        tspan.setAttribute('x', data.position.x + data.size.width);
                    } else {
                        tspan.setAttribute('x', data.position.x);
                    }
                    tspan.setAttribute('alignment-baseline', baseline);

                    let dy = '0em';

                    switch (data.style.vertical_align) {
                        case 'top':
                            dy = index ? lineHeight + 'em' : '0em';
                            break;
                        case 'middle':
                            dy = index ? lineHeight + 'em' : ((1 - textContentLines) * lineHeight / 2) + 'em';
                            break;
                        default:
                            dy = index ? lineHeight + 'em' : ((1 - textContentLines) * lineHeight) + 'em';
                            break;
                    }

                    tspan.setAttribute('dy', dy);

                    text.appendChild(tspan);
                });

                __setTransform();
            })
            .bind('rotate', function (e, degress) {
                data.rotation = degress;

                __setTransform();
            })
            .bind('parent_transform', function (e, scale, translate, rotation, flip, pivot) {
                e.stopPropagation();

                $this._objectText_applyTransformToData($this._objects[text.getAttribute('data-object')].data, scale, translate, rotation, flip, pivot);

                $(text).trigger('object-update');
            })
            .bind('calculate-min-size', function(e, object_data) {
                if (
                    object_data.data?.personalized?.config?.is_dynamic_input
                ) {
                    delete data.style.min_width;
                    delete data.style.min_height;
                    return;
                }

                ctx.font = `${data.style.font_size}px ${data.style.font_name}`;
                let textContent = data.content;
                let measure = ctx.measureText(textContent);
                let measureWidth = measure.width;
                let measureHeight = measure.fontBoundingBoxAscent + measure.fontBoundingBoxDescent;

                data.style.min_width = measureWidth;
                data.style.min_height = measureHeight;

                let diff_width = measureWidth - data.size.width;
                if (diff_width > 0) {
                    data.size.width = measureWidth;
                    if (data.style.text_align === 'right') {
                        data.position.x -= diff_width;
                    } else if (data.style.text_align === 'center') {
                        data.position.x -= diff_width / 2;
                    }
                }

                let diff_height = measureHeight - data.size.height;
                if (diff_height > 0) {
                    data.size.height = measureHeight;
                    switch (data.style.vertical_align) {
                        case 'top':
                            break;
                        case 'middle':
                            data.position.y -= diff_height / 2;
                            break;
                        default:
                            data.position.y -= diff_height;
                            break;
                    }
                }
            });

            return text;
        }

        _objectText_applyTransformToData(object_data, scale, translate, rotation, flip, pivot) {
            const _data = object_data.type_data;

            const bounding_rect = {
                x: _data.position.x,
                y: _data.position.y,
                width: _data.size.width,
                height: _data.size.height,
                rotation: _data.rotation,
                flip_vertical: _data.flip_vertical,
                flip_horizontal: _data.flip_horizontal
            };

            this._objectApplyTransformToBoundingRect(bounding_rect, scale, translate, rotation, flip, pivot);

            _data.style.font_size *= scale.y;
            _data.style.letter_spacing *= scale.y;
            _data.style.word_spacing *= scale.y;

            if(_data.outline?.width > 0)  _data.outline.width *= scale.y;

            if (bounding_rect.flip_vertical) {
                _data.flip_vertical = bounding_rect.flip_vertical;
            } else {
                delete _data.flip_vertical;
            }

            if (bounding_rect.flip_horizontal) {
                _data.flip_horizontal = bounding_rect.flip_horizontal;
            } else {
                delete _data.flip_horizontal;
            }

            if(_data.style.dynamic_font_size) _data.style.dynamic_font_size *= scale.y;

            const __transformTextPath = (data, bounding_rect) => {
                const path = data.path;

                if (!path) return;

                let translateX = bounding_rect.x - data.position.x,
                    translateY = bounding_rect.y - data.position.y;

                switch (path.type) {
                    case "path":
                        path.data?.points?.forEach(p => {
                            p.point.x += translateX;
                            p.point.y += translateY;
                        })
                        break;
                    case "ellipse":
                        if(!path.data?.center) break;
                        path.data.center.x += translateX;
                        path.data.center.y += translateY;
                        break;
                    case "rect":
                        if(!path.data?.position) break;
                        path.data.position.x = bounding_rect.x;
                        path.data.position.y = bounding_rect.y;
                    default:
                        break;
                }
            }

            __transformTextPath(_data, bounding_rect);

            _data.position.x = bounding_rect.x;
            _data.position.y = bounding_rect.y;

            _data.size.width = bounding_rect.width;
            _data.size.height = bounding_rect.height;

            _data.rotation = bounding_rect.rotation;
        }

        _peronalize_fetchCallback(object_data, new_key) {
            if (object_data.personalized === null || typeof object_data.personalized !== 'object' || typeof object_data.personalized.type === 'undefined') {
                return;
            }

            var callback_func = this._peronalizeTypeFunctionName(object_data.personalized.type, 'fetchCallback');

            if (typeof this[callback_func] === 'function') {
                this[callback_func](object_data.personalized.config, new_key);
            }
        }

        _peronalize_renderPanel(object) {
            var $this = this;

            var personalize_types = [];

            Object.getOwnPropertyNames(Object.getPrototypeOf(this)).forEach(function (func_name) {
                var matches = func_name.match(/^_personalize([0-9A-Z].+)_Form$/);

                if (!matches) {
                    return;
                }

                var personalize_type = matches[1].lcfirst();
                var verify_type_func = $this._peronalizeTypeFunctionName(personalize_type, 'verifyObjectType');

                if (typeof $this[verify_type_func] !== 'function' || $this[verify_type_func](object.data.type) === true) {
                    personalize_types.push(personalize_type);
                }
            });

            if (personalize_types.length < 1) {
                if (typeof object.data.personalized !== 'undefined') {
                    delete object.data.personalized;
                }

                return;
            }

            if (object.data.personalized !== null && typeof object.data.personalized === 'object') {
                if (personalize_types.indexOf(object.data.personalized.type) < 0) {
                    delete object.data.personalized;
                } else {
                    $.unwrapContent('personalizeBuilder_personalizePanel');
                    this[this._peronalizeTypeFunctionName(object.data.personalized.type, 'form')](object);
                    return;
                }
            }

            $.unwrapContent('personalizeBuilder_personalizePanel');


            //SETUP MODAL
            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);

            $('<div />').text('Please choose a personalize type.').appendTo(cell);

            var type_list = $('<div />').appendTo(cell);

            personalize_types.forEach(function (personalize_type) {
                var label_func = $this._peronalizeTypeFunctionName(personalize_type, 'label');

                $('<div />').click(function () {
                    var frm_func = $this._peronalizeTypeFunctionName(personalize_type, 'form');

                    if (typeof $this[frm_func] === 'function') {
                        $.unwrapContent('personalizeBuilder_personalizePanel');
                        $this[frm_func](object);
                    }
                }).addClass('mt10 btn btn--block btn-outline').append(typeof $this[label_func] === 'undefined' ? personalize_type : $this[label_func]()).appendTo(type_list);
            });

            $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel'});

            modal.moveToCenter().css('top', '100px');
        }

        _peronalizeTypeFunctionName(type, func) {
            return '_personalize' + type.ucfirst() + '_' + func.ucfirst();
        }

        _personalizeInput_Label() {
            return 'Text input';
        }

        _personalizeInput_VerifyObjectType(object_type) {
            return object_type === 'text';
        }

        _personalizeInput_Form(object) {
            var $this = this;

            $.unwrapContent('personalizeBuilder_personalizePanel');

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize :: Text Input').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);

            var label = $('<label />').appendTo(cell);

            $('<div />').text('Title').appendTo(label);

            var title_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Description').appendTo(label);

            var desc_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid frm-grid--separate').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Min Length').appendTo(label);

            var min_len_input = $('<input />').attr({type: 'text'}).addClass('styled-input').val(MIN_LENGTH_INPUT_PERSONALIZE_DEFAULT).appendTo($('<div />').appendTo(label));

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Max Length').appendTo(label);

            var max_len_input = $('<input />').attr({type: 'text'}).addClass('styled-input').val(MAX_LENGTH_INPUT_PERSONALIZE_DEFAULT).appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Position').appendTo(label);

            var position_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Segment tags').appendTo(label);

            var tags_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            const input_dynamic_text_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('Dynamic Text Input').appendTo(label);

            const max_lines_row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(max_lines_row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Lines of text allowed').appendTo(label);

            const max_lines_select = $('<select />').appendTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo($('<div />').appendTo(label)));
            Array.from(Array(10).keys()).forEach(i => {
                const n = i+ 1;
                $('<option />').attr('value', n).text(n).appendTo(max_lines_select);
            })

            input_dynamic_text_checker.change(function() {
                if(this.checked) max_lines_row.show();
                else max_lines_row.hide();
            })

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            var input_display_default_text_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('Display default text in input form?').appendTo(label);

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            const input_disable_uppercase_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label))

            $('<span />').addClass('ml5').text('Disable text all uppercase?').appendTo(label)

            row = $('<div />').addClass('frm-grid').appendTo(modal_body)

            cell = $('<div />').appendTo(row)

            label = $('<label />').appendTo(cell)

            var require_checker = $('<input />').attr({type: 'checkbox', value: 1, checked: true}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('The form is require?').appendTo(label);

            if (object.data.personalized !== null && typeof object.data.personalized === 'object' && object.data.personalized.type === 'input') {
                title_input.val(object.data.personalized.config.title);
                desc_input.val(object.data.personalized.config.description);
                min_len_input.val(object.data.personalized.config.min_length);
                max_len_input.val(object.data.personalized.config.max_length);
                position_input.val(object.data.personalized.position);
                tags_input.val(object.data.personalized.config.tags);
                require_checker[0].checked = object.data.personalized.config.require === 1;
                input_display_default_text_checker[0].checked = object.data.personalized.config.input_display_default_text === 1;
                input_disable_uppercase_checker[0].checked = object.data.personalized.config.input_disable_all_uppercase === 1;
                input_dynamic_text_checker[0].checked = object.data.personalized.config.is_dynamic_input === 1;
                max_lines_select.val(object.data.personalized.config.max_lines || 1);
            }

            if(!input_dynamic_text_checker[0].checked) {
                max_lines_row.hide()
            }

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);

                delete object.data.personalized;
                if(object.data?.type_data?.style?.dynamic_font_size) {
                    delete object.data.type_data.style.dynamic_font_size;
                }

                $(object.elm).trigger('personalized-update');
                $(object.elm).trigger('object-update');

                $this._peronalize_renderPanel(object);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml5').html('Apply').click(function () {
                var title = title_input.val().trim();

                if (title.length < 1) {
                    alert('Please enter title for form');
                    return;
                }

                object.data.personalized = {
                    type: 'input',
                    position: parseInt(position_input.val().replace(/[^0-9]/g, '')),
                    config: {
                        title: title,
                        description: desc_input.val().trim(),
                        min_length: min_len_input.val().trim(),
                        max_length: max_len_input.val().trim(),
                        tags: tags_input.val().trim(),
                        input_display_default_text: input_display_default_text_checker[0].checked ? 1 : 0,
                        input_disable_all_uppercase: input_disable_uppercase_checker[0].checked ? 1: 0,
                        is_dynamic_input: input_dynamic_text_checker[0].checked ? 1: 0,
                        max_lines: input_dynamic_text_checker[0].checked ? max_lines_select.val() : 1,
                        require: require_checker[0].checked ? 1 : 0
                    }
                };

                object.data.personalized.config.min_length = parseInt(object.data.personalized.config.min_length);

                if (isNaN(object.data.personalized.config.min_length) || object.data.personalized.config.min_length < 0) {
                    object.data.personalized.config.min_length = 0;
                }

                object.data.personalized.config.max_length = parseInt(object.data.personalized.config.max_length);

                if (isNaN(object.data.personalized.config.max_length) || object.data.personalized.config.max_length > 150) {
                    object.data.personalized.config.max_length = 150;
                }

                if (object.data.personalized.config.min_length > object.data.personalized.config.max_length) {
                    var buff = object.data.personalized.config.min_length;
                    object.data.personalized.config.min_length = object.data.personalized.config.max_length;
                    object.data.personalized.config.max_length = buff;
                }

                $(object.elm).trigger('personalized-update');

                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel', close_callback: function (container, key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }

                    $this._objectSelect(object);
                }});

            modal.moveToCenter().css('top', '100px');

        }

        _personalizeChecker_Label() {
            return 'Option checker';
        }

        _personalizeChecker_Form(object) {
            var $this = this;

            $.unwrapContent('personalizeBuilder_personalizePanel');

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize :: Option Checker').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);

            var label = $('<label />').appendTo(cell);

            $('<div />').text('Option title').appendTo(label);

            var title_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Position').appendTo(label);

            var position_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            var default_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('Checked by default').appendTo(label);

            if (object.data.personalized !== null && typeof object.data.personalized === 'object' && object.data.personalized.type === 'checker') {
                title_input.val(object.data.personalized.config.title);
                position_input.val(object.data.personalized.position);
                default_checker[0].checked = object.data.personalized.config.default_value === 1;
            }

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);

                delete object.data.personalized;

                $(object.elm).trigger('personalized-update');

                $this._peronalize_renderPanel(object);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml5').html('Apply').click(function () {
                var title = title_input.val().trim();

                if (title.length < 1) {
                    alert('Please enter title for checker');
                    return;
                }

                object.data.personalized = {
                    type: 'checker',
                    position: parseInt(position_input.val().replace(/[^0-9]/g, '')),
                    config: {
                        title: title,
                        default_value: default_checker[0].checked ? 1 : 0
                    }
                };

                $(object.elm).trigger('personalized-update');

                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel', close_callback: function (container, key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }

                    $this._objectSelect(object);
                }});

            modal.moveToCenter().css('top', '100px');
        }

        _personalizeImageUploader_Label() {
            return 'Image Uploader';
        }

        _personalizeImageUploader_VerifyObjectType(object_type) {
            return ['rect', 'ellipse', 'path'].indexOf(object_type) >= 0;
        }

        _personalizeImageUploader_Form(object) {
            var $this = this;

            $.unwrapContent('personalizeBuilder_personalizePanel');

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize :: Image Uploader').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);

            var label = $('<label />').appendTo(cell);

            $('<div />').text('Title').appendTo(label);

            var title_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            //NEW ROW
            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('D2 Flow').appendTo(label);

            const flow_select = $('<select />').appendTo($('<div />').appendTo(label));
            const limit = 10;
            $(flow_select).select2({
                width: '100%',
                theme: 'default select2-container--custom',
                ajax: {
                    delay: 300,
                    cache: true,
                    url: $.d2_flow_base_url + '/api/v1/flows',
                    data: function (params) {
                        const query = {
                          q: params.term,
                          offset: ((params.page || 1) -  1) * limit,
                          limit,
                          select: "name -createdUser",
                        }
                        return query;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                          results: data.docs.map(doc => ({
                            id: doc.id,
                            text: doc.name
                          })),
                          pagination: {
                            more: (params.page * limit) < data.totalDocs
                          }
                        };
                    }
                }
            })

            //NEW ROW
            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Description').appendTo(label);

            var desc_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Position').appendTo(label);

            var position_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            var require_checker = $('<input />').attr({type: 'checkbox', value: 1, checked: true}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('The form is require?').appendTo(label);

            if (object.data.personalized !== null && typeof object.data.personalized === 'object' && object.data.personalized.type === 'imageUploader') {
                title_input.val(object.data.personalized.config.title);
                desc_input.val(object.data.personalized.config.description);
                position_input.val(object.data.personalized.position);
                require_checker[0].checked = object.data.personalized.config.require === 1;

                if(object.data.personalized?.config?.flow_id) {
                    $.ajax({
                        type: 'GET',
                        url: $.d2_flow_base_url + '/api/v1/flows/' + object.data.personalized.config.flow_id
                    }).then(function (data) {
                        const option = new Option(data.name, data.id, true, true);
                        flow_select.append(option).trigger('change');
                    })
                }
            }

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);

                delete object.data.personalized;

                $(object.elm).trigger('personalized-update');

                $this._peronalize_renderPanel(object);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml5').html('Apply').click(function () {
                var title = title_input.val().trim();

                if (title.length < 1) {
                    alert('Please enter title for form');
                    return;
                }

                if (!object.data.type_data.fill || object.data.type_data.fill === 'none') {
                    object.data.type_data.fill = 'rgba(' + $.rand(0, 255) + ',' + $.rand(0, 255) + ',' + $.rand(0, 255) + ',0.5)';
                }

                object.data.personalized = {
                    type: 'imageUploader',
                    position: parseInt(position_input.val().replace(/[^0-9]/g, '')),
                    config: {
                        title: title,
                        description: desc_input.val().trim(),
                        require: require_checker[0].checked ? 1 : 0,
                        flow_id: flow_select.val()
                    }
                };

                $(object.elm).trigger('personalized-update');

                $.unwrapContent('personalizeBuilder_personalizePanel', true);

                $(object.elm).trigger('object-update');
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel', close_callback: function (container, key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }

                    $this._objectSelect(object);
                }});

            modal.moveToCenter().css('top', '100px');

        }

        _personalizeImageSelector_Label() {
            return 'Image Selector';
        }

        _personalizeImageSelector_VerifyObjectType(object_type) {
            return object_type === 'image';
        }

        _personalizeImageSelector_Form(object) {
            var $this = this;

            $.unwrapContent('personalizeBuilder_personalizePanel');

            var modal = $('<div />').addClass('osc-modal').width(550);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize :: Image Selector').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);

            var label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Title').appendTo(label);

            var title_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Description').appendTo(label);

            var desc_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Position').appendTo(label);

            var position_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Segment tags').appendTo(label);

            var tags_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            var linking_condition = {
                flag: false,
                matching_all: false,
                condition: []
            };

            var image_scene = $('<div />').addClass('personalized-imageSelector-image-scene').appendTo(cell);

            var group_list = $('<div />').addClass('group-list').appendTo(image_scene);

            var groups = {};
            var images = {};
            var default_key = null;

            const __getImageDataRelative = (imageData, image) => {
                //copy object
                const tempImageData = JSON.parse(JSON.stringify(imageData));
                let _size, _position;
                if (tempImageData.type_data.original_size && tempImageData.type_data.size && tempImageData.type_data.position) {
                    const {position, size, original_size} = tempImageData.type_data;
                    const ratio = image.width / image.height;
                    const curRatio = original_size.width / original_size.height;

                    if(ratio !== curRatio) {
                        const sizeRatio = size.width / size.height;
                        if(ratio < sizeRatio) {
                            const newWidth = size.height * ratio;
                            position.x += (size.width - newWidth)/ 2;
                            size.width = newWidth;
                        } else {
                            const newHeight = size.width / ratio;
                            position.y += (size.height - newHeight)/2;
                            size.height = newHeight;
                        }
                    }
                    _size = size;
                    _position = position;
                } else {
                    const {x, y, width, height} = $this._getObjectRectFitBackground({
                        width: image.width / $this._zoom_ratio,
                        height: image.height / $this._zoom_ratio,
                    });
                    _size = { width, height };
                    _position = { x, y };
                }


                tempImageData.type_data = Object.assign(tempImageData.type_data, {
                    url: image.url,
                    original_size: {
                        width: image.width,
                        height: image.height
                    },
                    size: _size,
                    position: _position,
                    hash: image.hash,
                    id: getUniqId(),
                })
                return tempImageData
            }

            var __renderAltEditor = function (image_item) {
                $.unwrapContent('imageAltEditor');

                var key = image_item.attr('data-key');
                let tempImageTypeData = null;

                var modal = $('<div />').addClass('osc-modal').width(550);

                var header = $('<header />').appendTo(modal);

                $('<div />').addClass('title').html('Edit image alt text').appendTo($('<div />').addClass('main-group').appendTo(header));

                $('<div />').addClass('close-btn').click(function () {
                    $.unwrapContent('imageAltEditor');
                }).appendTo(header);

                var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

                var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

                var cell = $('<div />').appendTo(row).css('max-width', '100px');

                const preview = $('<div />').addClass('personalized-imageSelector-image-reorder-helper uploader')
                    .css({opacity: 1, 'background-image': images[key].data.type_data.url ? ('url(' + $this._toThumbUrl(images[key].data.type_data.url) + ')') : 'initial'})
                    .appendTo(cell);

                const uploader = $("<div />").addClass("uploader").appendTo(preview);

                uploader.osc_uploader({
                    max_files: 1,
                    max_connections: 1,
                    process_url: $this._container.attr('data-upload-url'),
                    btn_content:  $.renderIcon('pencil'),
                    dragdrop_content: 'Drop here to upload',
                    image_mode: true,
                    xhrFields: {withCredentials: true},
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-OSC-Cross-Request': 'OK'
                    }
                }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {

                }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {

                }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                    pointer.success = false;

                    eval('response = ' + response);

                    if (response.result !== 'OK') {
                        toastr.error(response.message);
                        return;
                    }
                    const image = response.data;
                    const imageData = __getImageDataRelative(images[key].data, image);
                    tempImageTypeData = imageData.type_data;

                    preview.css('background-image', 'url(' + $this._toThumbUrl(image.url) + ')');
                }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                    toastr.error("Upload failed: "+ error_message)
                });


                $('<div />').addClass('separate').appendTo(row);

                cell = $('<div />').appendTo(row);

                var label = $('<label />').addClass('label-wrap').appendTo(cell);

                $('<div />').text('Image alt text').appendTo(label);

                var alt_input = $('<input />').attr('type', 'text').addClass('styled-input').appendTo($('<div />').appendTo(label)).val(images[key].label);

                $('<div />').addClass('input-desc').text('Write a brief description of this image to improve search engine optimization (SEO) and accessibility for visually impaired customers.').appendTo(cell);

                var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

                $('<button />').addClass('btn btn-outline').html('Close').click(function () {
                    $.unwrapContent('imageAltEditor');
                }).appendTo(action_bar);

                $('<button />').addClass('btn btn-primary ml10').html('Update').click(function () {
                    images[key].label = alt_input.val();
                    if(tempImageTypeData) {
                        images[key].data.type_data = tempImageTypeData;
                        image_item.css('background-image', 'url(' + $this._toThumbUrl(tempImageTypeData.url) + ')');
                    }
                    $.unwrapContent('imageAltEditor');
                }).appendTo(action_bar);

                $.wrapContent(modal, {key: 'imageAltEditor'});

                modal.moveToCenter().css('top', '100px');
            };

            var __getGroupLabelList = function () {
                var label_list = [];

                $.each(groups, function (k, label) {
                    label_list.push(label);
                });

                return label_list;
            };

            var __getImageLabelList = function () {
                var label_list = [];

                $.each(images, function (k, option) {
                    label_list.push(option.label);
                });

                return label_list;
            };

            var __renderImage = function (group, key, option) {
                if (!default_key) {
                    default_key = key;
                }

                images[key] = $.extend({
                    label: 'Untitled',
                    data: {
                        type_data: {}
                    }
                }, option);

                var image_item = $('<div />').addClass('image-item').attr('data-key', key).insertBefore(group.find('> .image-list > .uploader')).bind('linking_frm_switched', function () {
                    if (linking_condition.flag) {
                        $('<div />').attr({'data-skipdrag': 1, 'title': $this._conditionLabel(images[key].linking_condition)}).addClass('condition-btn').append($.renderIcon('toggle-' + (images[key].linking_condition ? 'on' : 'off') + '-regular')).click(function () {
                            var btn = $(this);

                            $this._conditionFrm(images[key].linking_condition, function (condition) {
                                btn.attr('title', $this._conditionLabel(condition));

                                if (!condition) {
                                    delete images[key].linking_condition;
                                    btn.removeClass('activated');
                                    btn.html('').append($.renderIcon('toggle-off-regular'));
                                } else {
                                    images[key].linking_condition = condition;
                                    btn.addClass('activated');
                                    btn.html('').append($.renderIcon('toggle-on-regular'));
                                }
                            });
                        }).prependTo(image_item);
                    } else {
                        image_item.find('condition-btn').remove();
                    }
                });

                if (key === default_key) {
                    image_item.addClass('image--default');
                }

                initItemReorder(image_item, '.personalized-imageSelector-image-scene', '.image-item, .uploader', 'personalized-imageSelector-image-reorder-helper', function (helper) {
                    helper.html('');
                });

                var control_bars = $('<div />').attr('data-skipdrag', 1).addClass('controls').appendTo(image_item);

                $($.renderIcon('crosshair-solid')).mousedown(function (e) {
                    if (typeof images[key].data.type_data.position === 'undefined' || images[key].data.type_data.url.indexOf('blob:') >= 0) {
                        toastr.warning("The image is uploading, please waiting...");
                        return;
                    }

                    default_key = key;

                    modal.find('.btn-apply-personalized').trigger('click');
                }).appendTo(control_bars);

                $($.renderIcon('pencil')).mousedown(function (e) {
                    __renderAltEditor(image_item);
                }).appendTo(control_bars);

                if($this._permissions.remove_layer) {
                    $($.renderIcon('trash-alt-regular')).mousedown(function (e) {
                        delete images[key];
                        image_item.remove();
                        if(default_key === key) {
                            const keys = Object.keys(images);
                            if(keys[0]) default_key = keys[0];
                        }
                    }).appendTo(control_bars);
                }
                image_item.css('background-image', images[key].data.type_data.url ?
                    'url(' + $this._toThumbUrl(images[key].data.type_data.url) + ')'
                    : 'initial');

                return image_item;
            };

            var __renderGroup = function (key, label) {
                groups[key] = label;

                var group_item = $('<div />').addClass('group-item').attr('data-key', key).appendTo(group_list);

                const title_item = $('<div />').addClass('title').append($('<div />').addClass('icon')).append($('<div />').addClass('label').append($('<span />').attr('data-skipdraggroup', 1).text(label).click(function () {
                    var node = $(this);

                    $('<input />').attr({value: node.text(), 'data-skipdraggroup': 1}).appendTo(node.parent()).keydown(function (e) {
                        if (e.keyCode === 13) {
                            e.preventDefault();
                        }
                    }).focus(function () {
                        this.select();
                    }).blur(function () {
                        var text = this.value.trim();

                        if (!text || __getGroupLabelList().indexOf(text) >= 0) {
                            text = node.text();
                        }

                        node.text(text);

                        groups[key] = text;

                        $(this).parent().append(node);

                        $(this).remove();
                    }).focus();

                    node.detach();
                }))).appendTo(group_item);;

                if($this._permissions.remove_layer) {
                    title_item.append($('<div />').addClass('remove-btn btn btn-danger').attr('data-skipdraggroup', 1).click(function () {
                        group_item.remove();

                        var default_item = group_item.find('.image--default');

                        if (default_item[0]) {
                            default_key = $(group_list.find('.image-item')[0]).addClass('image--default').attr('data-key');
                        }

                        delete groups[key];
                    }).text('Remove'));
                }

                title_item.append($('<div />').addClass('toggler').attr('data-skipdraggroup', 1).click(function () {
                    group_item.toggleClass('collapsed');
                }).append($.renderIcon('chevron-up-light')))


                var uploader = $('<div />').attr('data-placebefore', 1).addClass('uploader').appendTo($('<div />').addClass('image-list').attr('data-skipdraggroup', 1).appendTo(group_item));

                uploader.osc_uploader({
                    max_files: -1,
                    max_connections: 5,
                    process_url: $this._container.attr('data-upload-url'),
                    btn_content: $.renderIcon('plus'),
                    dragdrop_content: 'Drop here to upload',
                    image_mode: true,
                    xhrFields: {withCredentials: true},
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-OSC-Cross-Request': 'OK'
                    }
                }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                    var title = 'Untitled image';

                    var label_list = __getImageLabelList();

                    var counter = 0;

                    while (label_list.indexOf(title + (counter < 1 ? '' : ' ' + counter)) >= 0) {
                        counter++;
                    }

                    var image_item = __renderImage(group_item, file_id, {label: title + (counter < 1 ? '' : ' ' + counter)}).attr('data-uploader-step', 'queue');

                    $('<div />').addClass('uploader-progress-bar').appendTo(image_item).append($('<div />'));
                    $('<div />').addClass('step').appendTo(image_item);

                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var image_item = group_list.find('.image-item[data-key="' + file_id + '"]');

                        if (!image_item[0]) {
                            return;
                        }

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
                                if (typeof images[file_id] === 'undefined' || typeof images[file_id].data.type_data.url !== 'undefined') {
                                    return;
                                }

                                images[file_id].data.type_data.url = URL.createObjectURL(blob);
                                image_item.css('background-image', 'url(' + images[file_id].data.type_data.url + ')');
                            });
                        };

                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                    var image_item = group_list.find('.image-item[data-key="' + file_id + '"]');

                    if (!image_item[0]) {
                        return;
                    }

                    if (parseInt(uploaded_percent) === 100) {
                        image_item.attr('data-uploader-step', 'process');
                    } else {
                        image_item.attr('data-uploader-step', 'upload');
                        image_item.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
                    }
                }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                    var image_item = group_list.find('.image-item[data-key="' + file_id + '"]');

                    if (!image_item[0]) {
                        return;
                    }

                    eval('response = ' + response);

                    if (response.result !== 'OK') {
                        toastr.error(response.message);
                        image_item.remove();
                        return;
                    }
                    const defaultImageData =  images[default_key].data;
                    const image = response.data;

                    images[file_id].data = __getImageDataRelative(defaultImageData, image);

                    image_item.css('background-image', 'url(' + $this._toThumbUrl(images[file_id].data.type_data.url) + ')');

                    image_item.removeAttr('data-uploader-step');

                    image_item.find('.uploader-progress-bar').remove();
                    image_item.find('.step').remove();
                }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                    delete images[file_id];

                    var image_item = group_list.find('.image-item[data-key="' + file_id + '"]');

                    if (!image_item[0]) {
                        return;
                    }

                    toastr.error('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');

                    image_item.remove();
                });

                initItemReorder(group_item, '.personalized-imageSelector-image-scene', '.group-item', 'personalized-imageSelector-group-reorder-helper', function (helper) {
                    helper.html('');
                }, '[data-skipdraggroup]');

                return group_item;
            };

            $('<div />').addClass('btn btn-secondary btn--block').click(function () {
                var title = 'Untitled group';

                var label_list = __getGroupLabelList();

                var counter = 0;

                while (label_list.indexOf(title + (counter < 1 ? '' : ' ' + counter)) >= 0) {
                    counter++;
                }

                __renderGroup($.makeUniqid(), title + (counter < 1 ? '' : ' ' + counter));
            }).text('Add new group').appendTo(image_scene);

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var require_checker = $('<input />').attr({type: 'checkbox', value: 1, checked: true}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('The form is require?').appendTo(label);

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var condition_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('The form value linking to other form value?').appendTo(label);

            condition_checker.click(function () {
                $(this).trigger('update');
            }).bind('update', function () {
                linking_condition.flag = this.checked;

                var checker_row = $(this).closest('.frm-grid');

                if (linking_condition.flag) {
                    var container = $('<div />').addClass('linking-condition-container').insertBefore(checker_row);

                    $('<div />').addClass('frm-separate e20').appendTo(container);

                    container.append(checker_row);

                    $this._conditionFrmRender(container, linking_condition, 'title');
                } else {
                    var container = modal_body.find('.linking-condition-container');
                    checker_row.insertBefore(container);
                    container.remove();
                }

                group_list.find('.image-item').trigger('linking_frm_switched');
            });

            if (object.data.personalized !== null && typeof object.data.personalized === 'object' && object.data.personalized.type === 'imageSelector') {
                title_input.val(object.data.personalized.config.title);
                tags_input.val(object.data.personalized.config.tags);
                desc_input.val(object.data.personalized.config.description);
                position_input.val(object.data.personalized.position);
                require_checker[0].checked = object.data.personalized.config.require === 1;

                default_key = object.data.personalized.config.default_key;

                $.each(object.data.personalized.config.groups, function (key, group) {
                    var group_item = __renderGroup(key, group.label);

                    $.each(group.images, function (key, image) {
                        if (default_key === key) {
                            image.data = {
                                type_data: JSON.parse(JSON.stringify(object.data.type_data))
                            };
                        }

                        const { position, size, ...img_data } = $this._image_data[image.data.type_data.id] || {};
                        Object.assign(image.data.type_data, img_data)

                        __renderImage(group_item, key, image);
                    });
                });

                if (object.data.personalized.config.linking_condition) {
                    linking_condition = JSON.parse(JSON.stringify(object.data.personalized.config.linking_condition));

                    if (typeof linking_condition.flag === 'undefined' || linking_condition.flag) {
                        linking_condition.flag = true;
                        condition_checker[0].checked = true;
                        condition_checker.trigger('update');
                    }
                }
            } else {
                const type_data = JSON.parse(JSON.stringify(object.data.type_data));
                const img_data = this._image_data[type_data.id] || {};
                Object.assign(type_data, img_data)

                __renderImage(__renderGroup($.makeUniqid(), 'Untitled group'), $.makeUniqid(), {
                    label: 'Untitled',
                    data: {
                        type_data
                    }
                });
            }

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);

                delete object.data.personalized;

                $(object.elm).trigger('personalized-update');

                $this._peronalize_renderPanel(object);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml5 btn-apply-personalized').html('Apply').click(function () {
                var title = title_input.val().trim();

                if (title.length < 1) {
                    alert('Please enter title for form');
                    return;
                }

                const  data = {};
                const image_data = {};
                const links_map = {};
                const tmp_images = JSON.parse(JSON.stringify(images));
                let uploading_flag = false;
                let counter = 0;

                group_list.find('.group-item').each(function () {
                    var group_key = this.getAttribute('data-key');

                    data[group_key] = {
                        label: groups[group_key],
                        images: {}
                    };

                    $(this).find('.image-item').each(function () {
                        counter++;
                        var image_key = this.getAttribute('data-key');

                        if (typeof images[image_key].data.type_data.position === 'undefined') {
                            uploading_flag = true;
                        } else if (images[image_key].data.type_data.url?.indexOf('blob:') >= 0) {
                            uploading_flag = true;
                            alert("Some images have a incorrect URL, please contact admin to solve the issue. Sample data is:\n" + JSON.stringify(images[image_key].data));
                        } else if (images[image_key].data.type_data.url) {
                            const type_data = images[image_key].data.type_data;

                            const img_data = {
                                url: type_data.url,
                                original_size: type_data.original_size,
                                hash: type_data.hash
                            }

                            delete type_data.url;
                            delete type_data.original_size
                            delete type_data.hash;

                            const id = type_data.id || getUniqId();
                            image_data[id] = img_data;
                            links_map[img_data.url] = id;
                            type_data.id = id;

                            data[group_key].images[image_key] = images[image_key];
                        }
                    });
                });

                if (uploading_flag && !window.confirm('Some images is still uploading, you will lost the images if apply now, do you want to continue?')) {
                    return images = tmp_images;
                }

                if (counter < 2) {
                    alert('Please add least 2 images to the selector');
                    return images = tmp_images;
                }

                if (linking_condition.flag) {
                    var empty_flag = false;

                    $.each(linking_condition.condition, function (idx, condition_item) {
                        if (typeof condition_item.value === 'undefined' || condition_item.value === null || condition_item.value === '') {
                            empty_flag = true;
                            return false;
                        }
                    });

                    if (empty_flag) {
                        alert('Some linking condition input field is empty, please enter value before apply');
                        return images = tmp_images;;
                    }
                }
                var object_data = JSON.parse(JSON.stringify(object.data));

                if ($(object.elm).next('[data-object]')[0]) {
                    var group = null;
                    var before = $(object.elm).next('[data-object]')[0];
                } else {
                    var group = $(object.elm).closest('g[data-type="group"]')[0];
                    var before = null;
                }

                $this._objectRemove(object.data.key);

                const new_type_data = JSON.parse(JSON.stringify(images[default_key].data.type_data));

                images[default_key].data = [];

                Object.assign($this._image_data,image_data);
                Object.assign($this._links_map,links_map);

                object = $this._objectAdd('image', new_type_data, object_data.key, 1, {
                    group: group,
                    before: before,
                    name: object_data.name,
                    showable: object_data.showable,
                    locked: object_data.locked,
                    is_not_render_design: object_data.is_not_render_design,
                    index: object_data.index,
                    personalized: {
                        type: 'imageSelector',
                        position: parseInt(position_input.val().replace(/[^0-9]/g, '')),
                        config: {
                            title: title,
                            tags: tags_input.val().trim(),
                            description: desc_input.val().trim(),
                            require: require_checker[0].checked ? 1 : 0,
                            groups: data,
                            default_key: default_key
                        }
                    }
                });

                if (linking_condition.flag) {
                    object.data.personalized.config.linking_condition = linking_condition;
                } else {
                    delete object.data.personalized.config.linking_condition;

                    $.each(object.data.personalized.config.groups, function (idx, group) {
                        $.each(group.images, function (idx, image) {
                            delete image.linking_condition;
                        });
                    });
                }

                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel', close_callback: function (container, key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }

                    $this._objectSelect(object);
                }});

            modal.moveToCenter().css('top', '100px');
        }

        _personalizeSwitcher_Label() {
            return 'Switcher';
        }

        _personalizeSwitcher_VerifyObjectType(object_type) {
            return object_type === 'group';
        }

        _personalizeSwitcher_FetchCallback(config, new_key) {
            var $this = this;

            var __applyToChildren = function (children) {
                if (!Array.isArray(children)) {
                    return;
                }

                children.forEach(function (object_data) {
                    if (new_key) {
                        object_data.key = $.makeUniqid();

                        if (object_data?.type_data?.path?.key) {
                            object_data.type_data.path.key = $.makeUniqid();
                        }
                    }

                    $this._peronalize_fetchCallback(object_data, new_key);

                    if (object_data.type === 'group') {
                        __applyToChildren(object_data.type_data.children);
                    }
                });
            };

            $.each(config.options, function (option_key, option) {
                if (option_key === config.default_option_key) {
                    return;
                }

                __applyToChildren(option.objects);
            });
        }

        _personalizeSpotify_VerifyObjectType(object_type) {
            return object_type === 'rect';
        }

        _personalizeSpotify_Label() {
            return "Spotify";
        }

        _personalizeSpotify_Form(object) {
            const $this = this;
            const modalKey = 'personalizeBuilder_personalizePanel'
            $.unwrapContent(modalKey);

            const modal = $('<div />').addClass('osc-modal').width(550);

            const header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize :: Spotify Code')
                .appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent(modalKey, true);
            }).appendTo(header);

            const formElm = $('<form />').addClass('body post-frm personalized-spotify-form').appendTo(modal);
            const defaultConfig = object.data?.personalized || {};
            const default_display_style = defaultConfig?.config ? 'spotify_barcode' : 'qr_code';

            formElm.submit(function(e){
                e.preventDefault();

                const data = {};
                formElm.serializeArray().forEach(field => {
                    data[field.name] = field.value;
                })

                if(!data.title) {
                    alert('Please enter title for spotify');
                    return;
                }
                const imageSize = {width: 0, height: 0};
                const rectSize = object.data.type_data.size;

                if (data.display_style === 'qr_code') {
                    imageSize.width = Math.min(rectSize.width, rectSize.height);
                    imageSize.height = Math.min(rectSize.width, rectSize.height);
                } else if (rectSize.width > rectSize.height) {
                    imageSize.width = rectSize.width;
                    imageSize.height = rectSize.width / 4;
                } else {
                    imageSize.height = rectSize.height;
                    imageSize.width = rectSize.height * 4;
                }

                const bbox = $this._getRectFitBBox(imageSize, {...rectSize, ...object.data.type_data.position});
                // object.data.type_data

                object.data.type_data.size = {width: bbox.width, height: bbox.height};
                object.data.type_data.position = {x: bbox.x, y:bbox.y};
                                $(object.elm).attr('fill', `url(#spotify-${object.data.key})`)

                // if (!object.data.type_data.fill || object.data.type_data.fill === 'none') {
                //     object.data.type_data.fill = 'rgba(' + $.rand(0, 255) + ',' + $.rand(0, 255) + ',' + $.rand(0, 255) + ',0.5)';
                // }

                object.data.personalized = {
                    type: 'spotify',
                    position: parseInt(data.position.replace(/[^0-9]/g, '')),
                    config: {
                        title: data.title,
                        description: data.description.trim(),
                        require: Number(data.require) === 1 ? 1: 0,
                        background_color: data.background_color,
                        bar_color: data.bar_color || '#000000',
                        display_style: data.display_style || '',
                    }
                };

                $.unwrapContent(modalKey, true);

                $(object.elm).trigger('object-update');
                $(object.elm).trigger('personalized-update');

            });

            const __renderRow = (title, content) => {
                let row = $('<div />').addClass('frm-grid').appendTo(formElm);

                let cell = $('<div />').appendTo(row);

                let label = $('<label />').appendTo(cell);

                $('<div />').text(title).appendTo(label);
                $('<div />').append(content).appendTo(label)
            }

            __renderRow('Option title',$('<input />').attr({type: 'text', name: 'title'}).addClass('styled-input').val(defaultConfig.config?.title || ""));
            __renderRow('Description',$('<input />').attr({type: 'text', name: 'description'}).addClass('styled-input').val(defaultConfig.config?.description || ""));
            __renderRow('Position',$('<input />').attr({type: 'text', name: 'position'}).addClass('styled-input').val(defaultConfig.position || ""));

            const __renderRadioField = ({ name, id, label, value }) => {
                return $(`
                    <div class="label-wrap--checker mt5">
                        <div class="styled-radio">
                            <input type="radio" name="${name}" id="${id}" value="${value}" />
                            <ins></ins>
                        </div>
                        <label for="${id}" class="label-inline ml5">${label}</label>
                    </div>
                `).appendTo(formElm);
            }

            const displayStyleRow = $('<div />').addClass('frm-grid').appendTo(formElm);

            const displayStyleCell = $('<div />').appendTo(displayStyleRow);

            $('<label>Display Style</label>').appendTo(displayStyleCell);

            const display_style = defaultConfig?.config?.display_style || default_display_style;

            __renderRadioField({ name: 'display_style', label: 'QR Code', id: "qr_code", value: 'qr_code' }).appendTo(displayStyleCell).find('input').prop('checked', display_style === 'qr_code');
            __renderRadioField({ name: 'display_style', label: 'Spotify Barcode', id: "spotify_bar_code", value: 'spotify_barcode' }).appendTo(displayStyleCell).find('input').prop('checked', display_style === 'spotify_barcode');

            const _renderColorPicker = (name, defaultValue) => {
                let colorPicker, backgroundColorInput;
                const changeColor = color => {
                    if (color) {
                        colorPicker.removeClass('no-color').css('background-color', color);
                    } else {
                        colorPicker.addClass('no-color');
                    }
                    backgroundColorInput.val(color);
                }

                colorPicker = $('<div />').addClass('bg-color-picker no-color').osc_colorPicker({
                    swatch_name: 'svg_editor',
                    callback: changeColor
                })

                backgroundColorInput = $('<input />').attr({
                    type: 'text',
                    pattern: '^$|#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})',
                    maxlength: 7,
                    name,
                }).addClass('styled-input').on('input',function() {
                    const color = $(this).val();
                    changeColor(color);
                });
                changeColor(defaultValue);

                return $('<div />').addClass("bg-color-input-wrapper").append(colorPicker, backgroundColorInput)
            }

            __renderRow('Background Color (Hex)', _renderColorPicker('background_color',defaultConfig.config?.background_color));

            __renderRow('Bar Color (Hex)', _renderColorPicker('bar_color',defaultConfig.config?.bar_color));

            const rowChecker = $('<div />').addClass('frm-grid').appendTo(formElm);

            const cell = $('<div />').appendTo(rowChecker);

            const label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            $('<input />')
                .attr({type: 'checkbox', name: 'require', value: 1})
                // .val(defaultConfig.config?.require || '')
                .prop('checked', defaultConfig.config ? defaultConfig.config.require  === 1 : true)
                .prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));
            $('<span />').addClass('ml5').text('The form is require?').appendTo(label);

            const actionBar = $('<div />').addClass('bottom-bar').appendTo(formElm);

            $('<button />').attr('type', 'button').addClass('btn btn-outline').html('Cancel').appendTo(actionBar).click(function () {
                $.unwrapContent(modalKey, true);
            }).appendTo(actionBar);

            $('<button />').attr('type', 'button').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent(modalKey, true);

                delete object.data.personalized;

                $(object.elm).trigger('personalized-update');

                $this._peronalize_renderPanel(object);
            }).appendTo(actionBar);

            $('<button />').attr('type', 'submit').addClass('btn btn-primary ml5').html('Apply').appendTo(actionBar);

            $.wrapContent(modal, {key: modalKey, close_callback: function (container, key, force_flag) {
                if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                    return false;
                }

                $this._objectSelect(object);
            }});

            modal.moveToCenter().css('top', '100px');
        }

        _personalizeTab_VerifyObjectType(object_type) {
            return object_type === 'group';
        }

        _personalizeTab_Label() {
            return "Tab";
        }

        _personalizeTab_Form(object) {
            const $this = this;
            const modalKey_Tab = 'personalizeBuilder_personalizePanel';
            const __getListItem = () => {

                // get config.order if exist
                if (object.data.personalized && object.data.personalized.config.order) {
                    let check_order = true;
                    const _children = object.data.type_data.children;
                    const _order = object.data.personalized.config.order;
                    const _children_group = _children.filter((child) => {
                        const _data = $this._objects[child].data;
                        return _data.type === "group" && !_data.personalized;
                    });
                    if (_children_group.length === Object.keys(_order).length) {
                        for (let i = 0; i < _children_group.length; i++) {
                            const _key = _children_group[i];
                            if (!_order[_key]) {
                                object.data.personalized.config.order = null;
                                check_order = false;
                                break;
                            }
                        }
                        if (check_order) return object.data.personalized.config.order;
                    }
                }

                // get type_data.children in object
                const childrens = object.data.type_data.children;
                if (!childrens || !childrens.length || childrens.length < 1) return false;

                // get html node in object
                const list_elements = $(`[data-object='${object.data.key}'] > .thumb-list > .thumb-item`);
                if (list_elements.length < 1) return false;

                // get node is tab item
                let __children_group = [];
                for (let i = 0; i < list_elements.length; i++) {
                    const element = list_elements[i];

                    const element_key = element.getAttribute('data-object') || null;
                    if (!element_key ) continue;

                    const element_data = $this._objects[element_key].data || null;
                    if (!element_data ) continue;

                    if (element_data.type === "group" && element_data.showable && !element_data.personalized && childrens.includes(element_key)) {
                        __children_group.push(element_key)
                    }
                }
                if (__children_group.length < 1) return false;

                // set list items for order
                let _items = {};
                let _order = 0;
                for (let i = 0; i < __children_group.length; i++) {
                    const item = __children_group[i];
                    const elm = $(`[data-object='${item}']`);
                    if (elm.attr('data-type') === 'group' && elm.children('.thumb-content').attr('data-personalized') === '0') {
                        _items[item] = {
                            name: elm.children('.thumb-content').children('.name').children('span').text(),
                            order: _order++,
                            key: item
                        }
                    }
                }
                return _items;
            };
            $.unwrapContent(modalKey_Tab);

            const modalTabForm = $('<div />').addClass('osc-modal').width(350);
            const header = $('<header />').appendTo(modalTabForm);
            $('<div />').addClass('title').html('Setup personalize :: Tab')
                .appendTo($('<div />').addClass('main-group').appendTo(header));
            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent(modalKey_Tab, true);
            }).appendTo(header);

            const formElm = $('<form />').addClass('body post-frm personalized-spotify-form').appendTo(modalTabForm);
            const __renderRow = (title, content) => {
                let row = $('<div />').addClass('frm-grid').appendTo(formElm);
                let cell = $('<div />').appendTo(row);
                let label = $('<label />').appendTo(cell);
                $('<div />').text(title).appendTo(label);
                $('<div />').append(content).appendTo(label);
            }
            const defaultConfig = object.data?.personalized || {};
            __renderRow('Option title',$('<input />').attr({type: 'text', name: 'title'}).addClass('styled-input').val(defaultConfig.config?.title || ""));
            __renderRow('Position',$('<input />').attr({type: 'text', name: 'position'}).addClass('styled-input').val(defaultConfig.position || ""));

            const row = $('<div />').addClass('frm-grid').appendTo(formElm);
            const cell = $('<div />').appendTo(row);
            const tab_list = $('<div />').addClass('personalized-switcher-options-scene').appendTo(cell);
            $('<div />').addClass('title').text('Order items').appendTo(tab_list);
            const list_item = $('<div />').addClass('option-list').appendTo(tab_list);

            const __renderItem = (data, container) => {
                const { key, name, order } = data;
                const item = $('<div />').addClass('option-item').attr('data-key', key).attr('data-order', order).appendTo(container);
                item.append($('<div />').addClass('icon'));
                item.append($('<div />').addClass('label').append($('<span />').attr('data-skipdrag', 1).text(name)));
                initItemReorder(item, '.personalized-switcher-options-scene > .option-list', '.option-item', 'personalized-switcher-option-reorder-helper', function (helper) {
                    helper.find('.image, .remove-btn, .edit-btn').remove();
                });
            }
            const __orderItems = () => {
                if (!__getListItem()) return null;
                const _listItem = __getListItem();
                const _list = list_item.children('.option-item');
                for (let i = 0; i < _list.length; i++) {
                    const element = _list[i];
                    const order = parseInt(element.dataset.order);
                    const key = element.dataset.key;
                    if (order != i) {
                        _listItem[key].order = i;
                    }
                }
                let sorted = {};
                Object.keys(_listItem).sort(function(a, b){
                    return _listItem[a].order - _listItem[b].order;
                }).forEach(function(key) {
                    sorted[key] = _listItem[key];
                });
                return sorted;
            };
            if (__getListItem()) {
                const _listItem = __getListItem();
                for (let i = 0; i < Object.keys(_listItem).length; i++) {
                    const item = _listItem[Object.keys(_listItem)[i]];
                    __renderItem(item, list_item);
                }
            }

            formElm.submit(function(e){
                e.preventDefault();
                const data = {};
                formElm.serializeArray().forEach(field => {
                    data[field.name] = field.value;
                });
                if(!data.title) {
                    alert('Please enter title for Tab');
                    return;
                }
                object.data.personalized = {
                    type: 'tab',
                    position: parseInt(data.position.replace(/[^0-9]/g, '')),
                    config: {
                        title: data.title.replace(/[^\w\|\_\'\"\(\)\[\]\!\-]/g, ''),
                        order: __orderItems()
                    }
                };
                $.unwrapContent(modalKey_Tab, true);
                $(object.elm).trigger('object-update');
                $(object.elm).trigger('personalized-update');
            });

            const actionBar = $('<div />').addClass('bottom-bar').appendTo(formElm);
            $('<button />').attr('type', 'button').addClass('btn btn-outline').html('Cancel').appendTo(actionBar).click(function () {
                $.unwrapContent(modalKey_Tab, true);
            }).appendTo(actionBar);
            $('<button />').attr('type', 'button').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent(modalKey_Tab, true);
                delete object.data.personalized;
                $(object.elm).trigger('personalized-update');
                $(object.list_item).find('> .thumb-content').attr('type-tab', 0);
                $(object.list_item).find('> .thumb-content > .type').html($.renderIcon('folder-solid'));
                $this._peronalize_renderPanel(object);
            }).appendTo(actionBar);
            $('<button />').attr('type', 'submit').addClass('btn btn-primary ml5').html('Apply').appendTo(actionBar);

            $.wrapContent(modalTabForm, {
                key: modalKey_Tab,
                close_callback: function (_container, _key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }
                    $this._objectSelect(object);
                }
            });

            modalTabForm.moveToCenter().css('top', '100px');
        }

        _conditionFrmRender(scene, condition_data, name) {
            var __renderConditionList = function (container, branch) {
                var operators = {
                    equals: 'is equal to',
                    not_equals: 'is not equal to',
                    greater_than: 'is greater than',
                    less_than: 'is less than',
                    starts_with: 'starts with',
                    ends_with: 'ends with',
                    contains: 'contains',
                    not_contains: 'does not contains'
                };

                var list = $('<div />').appendTo(container);

                var __renderConditionItem = function (condition_item) {
                    if (typeof condition_item === 'undefined') {
                        condition_item = {operator: 'equals', value: ''};
                        branch.condition.push(condition_item);
                    }

                    condition_item.id = $.makeUniqid();

                    var row = $('<div />').attr('data-condition-idx', condition_item.id).addClass('frm-grid frm-grid--middle').appendTo(list);

                    var operator_select = $('<select />').appendTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo($('<div />').appendTo(row))).change(function () {
                        condition_item.operator = this.options[this.selectedIndex].value;
                    });

                    $.each(operators, function (operator, title) {
                        var option = $('<option />').attr('value', operator).text(title).appendTo(operator_select);

                        if (condition_item.operator === operator) {
                            option.attr('selected', 'selected');
                        }
                    });

                    $('<div />').addClass('separate').appendTo(row);

                    $('<input />').attr({type: 'text', value: condition_item.value}).addClass('styled-input').appendTo($('<div />').appendTo(row)).blur(function () {
                        condition_item.value = this.value;
                    });

                    __renderConditionRemoveBtn(condition_item);
                };

                var __renderConditionRemoveBtn = function () {
                    if (branch.condition.length > 1) {
                        list.find('> *').each(function () {
                            var row = $(this);

                            if (row.find('[data-condition-rmv="1"]')[0]) {
                                return;
                            }

                            $('<div />').addClass('separate').attr('data-condition-rmv', '1').appendTo(row);

                            $('<div />').addClass('btn btn-icon btn-small').append($.renderIcon('trash-alt-regular')).click(function () {
                                row.remove();

                                var ids = [];

                                list.find('> *').each(function () {
                                    ids.push(this.getAttribute('data-condition-idx'));
                                });

                                var buff = [];

                                branch.condition.forEach(function (item) {
                                    if (ids.indexOf(item.id) >= 0) {
                                        buff.push(item);
                                    }
                                });

                                branch.condition = buff;

                                __renderConditionRemoveBtn();
                            }).appendTo($('<div />').css('max-width', '30px').attr('data-condition-rmv', '1').appendTo(row));

                        });
                    } else {
                        list.find('[data-condition-rmv="1"]').remove();
                    }
                };

                $('<div />').addClass('frm-line e20').appendTo(container);

                var new_condition_btn = $('<div />').addClass('btn btn-primary btn-small').html($.renderIcon('icon-plus', 'mr5')).append('Add another condition').appendTo($('<div />').appendTo($('<div />').addClass('frm-grid').appendTo(container))).click(function () {
                    __renderConditionItem();
                });

                if (branch.condition.length > 0) {
                    branch.condition.forEach(function (condition_item) {
                        __renderConditionItem(condition_item);
                    });
                } else {
                    new_condition_btn.trigger('click');
                }
            };

            name = name ? name : 'value';

            $('<div />').addClass('frm-heading__title').text('Form ' + name + ' matching').appendTo($('<div />').addClass('frm-heading__main').appendTo($('<div />').addClass('frm-heading').appendTo(scene)));

            var container = $('<div />').addClass('mb15').appendTo(scene);

            $('<span />').text(name.ucfirst() + ' must match:').appendTo(container);


            var frm_id = $.makeUniqid();

            $('<input />').attr({type: 'radio', value: 'all', name: 'frm_' + frm_id, id: 'frm_' + frm_id + '__matched_all'}).click(function () {
                condition_data.matching_all = true;
            }).prependTo($('<div />').addClass('styled-radio ml10').append($('<ins />')).appendTo(container))[0].checked = condition_data.matching_all === true;
            $('<label />').addClass('label-inline ml5').attr('for', 'frm_' + frm_id + '__matched_all').text('All condition').appendTo(container);

            $('<input />').attr({type: 'radio', value: 'all', name: 'frm_' + frm_id, id: 'frm_' + frm_id + '__matched_anyone'}).click(function () {
                condition_data.matching_all = false;
            }).prependTo($('<div />').addClass('styled-radio ml10').append($('<ins />')).appendTo(container))[0].checked = condition_data.matching_all === false;
            $('<label />').addClass('label-inline ml5').attr('for', 'frm_' + frm_id + '__matched_anyone').text('Any condition').appendTo(container);

            __renderConditionList($('<div />').appendTo(scene), condition_data);
        }

        _conditionFrm(condition_data, apply_callback) {
            if (typeof condition_data === 'undefined') {
                condition_data = {
                    matching_all: false,
                    condition: []
                };
            } else {
                condition_data = JSON.parse(JSON.stringify(condition_data));
            }

            $.unwrapContent('personalizeBuilder_conditionPanel');

            var modal = $('<div />').addClass('osc-modal').width(500);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_conditionPanel', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            this._conditionFrmRender(modal_body, condition_data);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('personalizeBuilder_conditionPanel', true);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent('personalizeBuilder_conditionPanel', true);
                apply_callback(null);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml5 btn-apply-personalized').html('Apply').click(function () {
                var empty_flag = false;

                $.each(condition_data.condition, function (idx, condition_item) {
                    if (typeof condition_item.value === 'undefined' || condition_item.value === null || condition_item.value === '') {
                        empty_flag = true;
                        return false;
                    }
                });

                if (empty_flag) {
                    alert('Some input field is empty, please enter value before apply');
                    return;
                }

                $.unwrapContent('personalizeBuilder_conditionPanel', true);

                apply_callback(condition_data);
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'personalizeBuilder_conditionPanel', close_callback: function (container, key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }
                }});

            modal.moveToCenter().css('top', '100px');
        }

        _conditionLabel(condition_data) {
            if (!condition_data) {
                return '';
            }

            var operators = {
                equals: 'is equal to',
                not_equals: 'is not equal to',
                greater_than: 'is greater than',
                less_than: 'is less than',
                starts_with: 'starts with',
                ends_with: 'ends with',
                contains: 'contains',
                not_contains: 'does not contains'
            };

            var labels = [];

            labels.push('Form value matching ' + (condition_data.matching_all ? 'all of' : 'any of') + ' condition(s) below:');

            condition_data.condition.forEach(function (condition_item) {
                labels.push(operators[condition_item.operator] + ' "' + condition_item.value + '"');
            });

            return labels.join("\n");
        }

        _personalizeSwitcher_Form(object) {
            var $this = this;

            $.unwrapContent('personalizeBuilder_personalizePanel');

            var modal = $('<div />').addClass('osc-modal').width(500);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Setup personalize :: Switcher').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);

            var label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Title').appendTo(label);

            var title_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Description').appendTo(label);

            var desc_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').appendTo(cell);

            $('<div />').text('Position').appendTo(label);

            var position_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var image_mode_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('Switcher by Image').appendTo(label);

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var tags_mode_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('Use between designs linking tags').appendTo(label);

            var tags_row = $('<div />').addClass('frm-grid hidden').appendTo(modal_body);

            cell = $('<div />').appendTo(tags_row);

            label = $('<label />').addClass('label-wrap').appendTo(cell);

            $('<div />').text('Segment tags').appendTo(label);

            var tags_input = $('<input />').attr({type: 'text'}).addClass('styled-input').appendTo($('<div />').appendTo(label));

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            var options_scene = $('<div />').addClass('personalized-switcher-options-scene').appendTo(cell);

            $('<div />').addClass('title').text('Options').appendTo(options_scene);

            var option_list = $('<div />').addClass('option-list').appendTo(options_scene);

            var linking_condition = {
                flag: false,
                matching_all: false,
                condition: []
            };

            var options = {};
            var default_option_key = null;

            var __getLabelList = function () {
                var label_list = [];

                $.each(options, function (k, option) {
                    label_list.push(option.label);
                });

                return label_list;
            };

            var __renderOption = function (key, option) {
                if (!default_option_key) {
                    default_option_key = key;
                }

                options[key] = $.extend({
                    image: null,
                    label: 'Untitled',
                    objects: []
                }, option);

                var option_item = $('<div />').addClass('option-item').attr('data-key', key).appendTo(option_list);

                if (key === default_option_key) {
                    option_item.addClass('option--default');
                }

                option_item.append($('<div />').addClass('icon'));

                if (image_mode_checker[0].checked) {
                    var thumbnail_uploader = $('<div />').attr('data-skipdrag', 1).addClass('uploader').appendTo($('<div />').addClass('image').appendTo(option_item));

                    var preview = $('<div />').addClass('image-preview').css('background-image', 'url(' + $this._getLocalImgStorageUrl(options[key].image) + ')');

                    thumbnail_uploader.osc_uploader({
                        max_files: 1,
                        max_connections: 1,
                        process_url: $this._container.attr('data-upload-thumb-url'),
                        btn_content: preview,
                        dragdrop_content: 'Drop here to upload',
                        image_mode: true,
                        xhrFields: {withCredentials: true},
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-OSC-Cross-Request': 'OK'
                        }
                    }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {

                    }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {

                    }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                        pointer.success = false;

                        eval('response = ' + response);

                        if (response.result !== 'OK') {
                            toastr.error(response.message);
                            return;
                        }

                        options[key].image = response.data.url;
                        options[key].image_hash = response.data.hash;
                        preview.css('background-image', 'url(' + $this._getLocalImgStorageUrl(response.data.url) + ')');
                    }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {

                    });
                }

                option_item.append($('<div />').addClass('label').append($('<span />').attr('data-skipdrag', 1).text(option.label).click(function () {
                    var node = $(this);

                    $('<input />').attr({value: node.text(), 'data-skipdrag': 1}).appendTo(node.parent()).keydown(function (e) {
                        if (e.keyCode === 13) {
                            e.preventDefault();
                        }
                    }).focus(function () {
                        this.select();
                    }).blur(function () {
                        var text = this.value.trim();

                        if (!text || __getLabelList().indexOf(text) >= 0) {
                            text = node.text();
                        }

                        node.text(text);

                        options[key].label = text;

                        $(this).parent().append(node);

                        $(this).remove();
                    }).focus();

                    node.detach();
                })));

                if (linking_condition.flag) {
                    option_item.append($('<div />').attr({'data-skipdrag': 1, 'title': $this._conditionLabel(options[key].linking_condition)}).addClass('condition-btn').append($.renderIcon('toggle-' + (options[key].linking_condition ? 'on' : 'off') + '-regular')).click(function () {
                        var btn = $(this);

                        $this._conditionFrm(options[key].linking_condition, function (condition) {
                            btn.attr('title', $this._conditionLabel(condition));

                            if (!condition) {
                                delete options[key].linking_condition;
                                btn.removeClass('activated');
                                btn.html('').append($.renderIcon('toggle-off-regular'));
                            } else {
                                options[key].linking_condition = condition;
                                btn.addClass('activated');
                                btn.html('').append($.renderIcon('toggle-on-regular'));
                            }
                        });
                    }));
                }

                option_item.append($('<div />').attr('data-skipdrag', 1).addClass('edit-btn').append($.renderIcon('crosshair-solid')).click(function () {
                    default_option_key = key;

                    modal.find('.btn-apply-personalized').trigger('click');
                }));

                if ($this._permissions.remove_layer) {
                    option_item.append($('<div />').attr('data-skipdrag', 1).addClass('remove-btn').click(function () {
                        delete options[key];
                        option_item.remove();
                        if(default_option_key === key) {
                            const keys = Object.keys(options);
                            if(keys[0]) default_option_key = keys[0];
                        }
                    }));
                }

                __renderOptionConfig(option_item, key);

                initItemReorder(option_item, '.personalized-switcher-options-scene > .option-list', '.option-item', 'personalized-switcher-option-reorder-helper', function (helper) {
                    helper.find('.image, .remove-btn, .edit-btn').remove();
                });
            };

            var __renderOptionConfig = function(option_item, key) {
                let config_box = $('<div />').attr('data-skipdrag', 1).addClass('config-box');

                if (tags_mode_checker[0].checked) {
                    config_box.addClass('active');
                }

                $('<input type="text" name="tags" />')
                    .attr('placeholder', 'Value tags...')
                    .val(options[key]['tags']).appendTo(config_box);
                option_item.append(config_box);
            }

            $('<div />').addClass('btn btn-primary btn--block').click(function () {
                var title = 'Untitled';

                var label_list = __getLabelList();

                var counter = 0;

                while (label_list.indexOf(title + (counter < 1 ? '' : ' ' + counter)) >= 0) {
                    counter++;
                }

                __renderOption($.makeUniqid(), {
                    label: title + (counter < 1 ? '' : ' ' + counter),
                    objects: []
                });
            }).text('Add new options').appendTo(options_scene);

            image_mode_checker.click(function () {
                option_list.html('');

                $.each(options, function (key, option) {
                    __renderOption(key, option);
                });
            });

            tags_mode_checker.on('click', function () {
                if (this.checked) {
                    option_list.find('.option-item').each(function() {
                        const label = $(this).find('.label').text();
                        const config_box = $(this).find('.config-box');
                        const tag_input = config_box.find('input[name="tags"]');

                        config_box.addClass('active');

                        if (label && !tag_input.val()) {
                            tag_input.val(label.toLowerCase().replaceAll(/\s+/g, '_'));
                        }
                    });
                    tags_row.removeClass('hidden');
                } else {
                    option_list.find('.config-box').removeClass('active');
                    tags_row.addClass('hidden');
                }
            });

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var search_mode_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('Allow searching by keyword ?').appendTo(label);

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var require_checker = $('<input />').attr({type: 'checkbox', value: 1, checked: true}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('The form is require?').appendTo(label);

            row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            label = $('<label />').addClass('label-wrap--checker').appendTo(cell);

            var condition_checker = $('<input />').attr({type: 'checkbox', value: 1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo(label));

            $('<span />').addClass('ml5').text('The form value linking to other form value?').appendTo(label);

            condition_checker.click(function () {
                $(this).trigger('update');
            }).bind('update', function () {
                linking_condition.flag = this.checked;

                option_list.html('');

                $.each(options, function (key, option) {
                    __renderOption(key, option);
                });

                var checker_row = $(this).closest('.frm-grid');

                if (linking_condition.flag) {
                    var container = $('<div />').addClass('linking-condition-container').insertBefore(checker_row);

                    $('<div />').addClass('frm-separate e20').appendTo(container);

                    container.append(checker_row);

                    $this._conditionFrmRender(container, linking_condition, 'title');
                } else {
                    var container = modal_body.find('.linking-condition-container');
                    checker_row.insertBefore(container);
                    container.remove();
                }
            });

            if (object.data.personalized !== null && typeof object.data.personalized === 'object' && object.data.personalized.type === 'switcher') {
                title_input.val(object.data.personalized.config.title);
                tags_input.val(object.data.personalized.config.tags);
                desc_input.val(object.data.personalized.config.description);
                require_checker[0].checked = object.data.personalized.config.require === 1;
                search_mode_checker[0].checked = object.data.personalized.config.search_mode === 1;
                image_mode_checker[0].checked = object.data.personalized.config.image_mode === 1;
                tags_mode_checker[0].checked = object.data.personalized.config.tags_mode === 1;
                position_input.val(object.data.personalized.position);

                if (tags_mode_checker[0].checked) {
                    tags_row.removeClass('hidden');
                }

                default_option_key = object.data.personalized.config.default_option_key;

                $.each(object.data.personalized.config.options, function (key, option) {
                    if (default_option_key === key) {
                        option.objects = $this._fetch($(object.list_item).find('> .thumb-list'))
                    }

                    __renderOption(key, option);
                });

                if (object.data.personalized.config.linking_condition) {
                    linking_condition = JSON.parse(JSON.stringify(object.data.personalized.config.linking_condition));

                    if (typeof linking_condition.flag === 'undefined' || linking_condition.flag) {
                        linking_condition.flag = true;
                        condition_checker[0].checked = true;
                        condition_checker.trigger('update');
                    }
                }
            } else {
                __renderOption($.makeUniqid(), {
                    label: 'Untitled',
                    objects: this._fetch($(object.list_item).find('> .thumb-list'))
                });
            }

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-danger ml5').html('Remove').click(function () {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);

                delete object.data.personalized;

                $(object.elm).trigger('personalized-update');

                $this._peronalize_renderPanel(object);
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml5 btn-apply-personalized').html('Apply').click(function () {
                var title = title_input.val().trim();

                if (title.length < 1) {
                    alert('Please enter title for form');
                    return;
                }

                var image_mode_flag = image_mode_checker[0].checked ? 1 : 0;
                var image_missing_flag = false;

                var counter = 0;

                var _options = {};

                option_list.find('.option-item[data-key]').each(function () {
                    var key = this.getAttribute('data-key');

                    if (typeof options[key] !== 'undefined') {
                        _options[key] = options[key];

                        if (image_mode_flag && !_options[key].image) {
                            image_missing_flag = true;
                        }

                        counter++;

                        $(this).find('input').each(function() {
                            var $input = $(this);
                            var name = $input.attr('name');
                            var value = '';

                            if ($input.attr('type') === 'checkbox') {
                                value = $input.prop('checked');
                            } else {
                                value = $input.val();
                            }
                            _options[key][name] = value;
                        });
                    }
                });

                if (image_missing_flag) {
                    alert('Some switcher thumbnail image missing or uploading...');
                    return;
                }

                if (counter < 2) {
                    alert('Please add least 2 option to the switcher');
                    return;
                }

                if (linking_condition.flag) {
                    var empty_flag = false;

                    $.each(linking_condition.condition, function (idx, condition_item) {
                        if (typeof condition_item.value === 'undefined' || condition_item.value === null || condition_item.value === '') {
                            empty_flag = true;
                            return false;
                        }
                    });

                    if (empty_flag) {
                        alert('Some linking condition input field is empty, please enter value before apply');
                        return;
                    }
                }

                options = _options;

                $this._objectRemoveChildsInGroup(object)

                if (!options[default_option_key]) {
                    default_option_key = Object.keys(options)[0];
                }

                if (!Array.isArray(options[default_option_key].objects)) {
                    options[default_option_key].objects = [];
                }

                $this._setDataMakeObject(options[default_option_key].objects, object.elm);

                options[default_option_key].objects = [];

                object.data.personalized = {
                    type: 'switcher',
                    position: parseInt(position_input.val().replace(/[^0-9]/g, '')),
                    config: {
                        title: title,
                        tags: tags_input.val().trim(),
                        description: desc_input.val().trim(),
                        image_mode: image_mode_flag,
                        tags_mode: tags_mode_checker[0].checked ? 1 : 0,
                        search_mode: search_mode_checker[0].checked ? 1 : 0,
                        require: require_checker[0].checked ? 1 : 0,
                        options: options,
                        default_option_key: default_option_key
                    }
                };

                if (linking_condition.flag) {
                    object.data.personalized.config.linking_condition = linking_condition;
                } else {
                    delete object.data.personalized.config.linking_condition;

                    $.each(object.data.personalized.config.options, function (idx, option) {
                        delete option.linking_condition;
                    });
                }

                $(object.elm).trigger('personalized-update');

                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel', close_callback: function (container, key, force_flag) {
                    if (!force_flag && !window.confirm('Your data will lost if you close the form, do you want to continue?')) {
                        return false;
                    }

                    $this._objectSelect(object);
                }});

            modal.moveToCenter().css('top', '100px');
        }

        _objectText_configRender(object) {
            var $this = this;

            this._objectConfigRender_outline(object);

            var font_uploader = $('<div />').appendTo($('<div />').appendTo(this._config_panel));

            $(font_uploader).osc_uploader({
                max_files: 1,
                max_connections: 1,
                process_url: this._container.attr('data-upload-font-url'),
                btn_content: 'Upload font',
                dragdrop_content: 'Drop here to upload',
                extensions: ['ttf', 'otf'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {

            }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {

            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                pointer.success = false;

                eval('response = ' + response);

                if (response.result !== 'OK') {
                    toastr.error(response.message);
                    return;
                }

                $this._fonts[$.md5(response.data.font_name)] = response.data;

                $($this._config_panel).html('');

                $this._objectText_configRender(object);
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {

            });

            var font_selector = $('<select />').appendTo($('<div />').addClass('selector').append($('<div />').text(object.data.type_data.style.font_name)).width(120).appendTo(this._config_panel)).change(function () {
                object.data.type_data.style.font_name = this.options[this.selectedIndex].value;
                $(this.parentNode.querySelector('div')).text(object.data.type_data.style.font_name);
                $(object.elm).trigger('object-update');
            });

            for (var font_key in this._fonts) {
                var font = this._fonts[font_key];

                var option = $('<option />').attr('value', font.font_name).text(font.font_name).appendTo(font_selector);

                if (font.font_name === object.data.type_data.style.font_name) {
                    option.attr('selected', 'selected');
                }
            }

            var style_selector = $('<select />').appendTo($('<div />').addClass('selector').append($('<div />').text(object.data.type_data.style.font_style)).width(90).appendTo(this._config_panel)).change(function () {
                object.data.type_data.style.font_style = this.options[this.selectedIndex].value;
                $(this.parentNode.querySelector('div')).text(object.data.type_data.style.font_style);
                $(object.elm).trigger('object-update');
            });

            ['Regular', 'Bold', 'Italic', 'Bold Italic'].forEach(function (style) {
                var option = $('<option />').attr('value', style).html(style).appendTo(style_selector);

                if (style === object.data.type_data.style.font_style) {
                    option.attr('selected', 'selected');
                }
            });

            $('<div />').addClass('seperate').appendTo(this._config_panel);

            //TEXT ALIGN
            var align_selector = $('<select />').appendTo($('<div />').addClass('selector').append($('<div />').text(object.data.type_data.style.text_align)).width(75).appendTo(this._config_panel)).change(function () {
                const data = object.data.type_data;
                const value = this.options[this.selectedIndex].value
                data.style.text_align = value;
                if(!!data.path) {
                    switch (value) {
                        case "center":
                            data.offset = 50;
                            break;
                        case "right":
                            data.offset = 100;
                            break;
                        default:
                            data.offset = 0;
                            break;
                    }
                }

                $(this.parentNode.querySelector('div')).text(object.data.type_data.style.text_align);
                $(object.elm).trigger('object-update');
            });

            ['left', 'center', 'right'].forEach(function (align) {
                var option = $('<option />').attr('value', align).html(align).appendTo(align_selector);

                if (align === object.data.type_data.style.text_align) {
                    option.attr('selected', 'selected');
                }
            });

            $('<div />').addClass('seperate').appendTo(this._config_panel);

            //VERTICAL ALIGN
            const vertical_align_selector = $('<select />').appendTo($('<div />').addClass('selector').append($('<div />').text(object.data.type_data.style.vertical_align)).width(80).appendTo(this._config_panel)).change(function () {
                const data = object.data.type_data;
                const value = this.options[this.selectedIndex].value
                data.style.vertical_align = value;
                $(this.parentNode.querySelector('div')).text(object.data.type_data.style.vertical_align);
                $(object.elm).trigger('object-update');
            });

            ['top', 'middle', 'bottom'].forEach(function (align) {
                const option = $('<option />').attr('value', align).html(align).appendTo(vertical_align_selector);

                if (align === object.data.type_data.style.vertical_align) {
                    option.attr('selected', 'selected');
                }
            });

            //FONT SIZE
            this._config_panel.appendChild($.renderIcon('font-size'));

            $('<input />').attr({type: 'text'}).width(50).val(object.data.type_data.style.font_size).bind('blur keydown', function (e) {
                if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                object.data.type_data.style.font_size = parseFloat(this.value);
                $(object.elm).trigger('object-update');
                $this._objectSelect(object);
            }).appendTo(this._config_panel);

            //LINE HEIGHT
            if (object.data?.personalized?.config?.is_dynamic_input) {
                this._config_panel.appendChild($.renderIcon('line-height'));
                $('<input />').attr({type: 'text'}).width(50).val(object.data.type_data.style.line_height).bind('blur keydown', function (e) {
                    if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                        return;
                    }

                    object.data.type_data.style.line_height = parseFloat(this.value);
                    $(object.elm).trigger('object-update');
                    $this._objectSelect(object);
                }).appendTo(this._config_panel);
            }

            //WORD SPACING
            this._config_panel.appendChild($.renderIcon('word-spacing'));
            $('<input />').attr({type: 'text'}).width(50).val(object.data.type_data.style.word_spacing).bind('blur keydown', function (e) {
                if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                object.data.type_data.style.word_spacing = parseFloat(this.value);
                $(object.elm).trigger('object-update');
            }).appendTo(this._config_panel);

            this._config_panel.appendChild($.renderIcon('letter-spacing'));

            $('<input />').attr({type: 'text'}).width(50).val(object.data.type_data.style.letter_spacing).bind('blur keydown', function (e) {
                if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                object.data.type_data.style.letter_spacing = parseFloat(this.value);
                $(object.elm).trigger('object-update');
            }).appendTo(this._config_panel);

            $('<div />').addClass('seperate').appendTo(this._config_panel);

            $($.renderIcon('text-path')).appendTo(this._config_panel).attr('class', 'button').click(function () {
                $this._objectText_addPath();
            });

            $(this._UIRenderSlider(function (value) {
                object.data.type_data.offset = Math.ceil(value * 100);
                $(object.elm).trigger('object-update');
            })).width(100).appendTo(this._config_panel);

            this._objectConfigRender_coordinate(object);
            this._objectConfigRender_dimension(object);
            this._objectConfigRender_rotation(object);
            this._objectConfigRender_copyPasteAttributes(object);
        }

        _UIRenderSlider(callback) {
            var slider = $('<div />').addClass('slider');

            var slider_btn = $('<div />').append($('<ins />')).appendTo(slider);

            var _callback = function () {
                callback((slider_btn.offset().left - slider.offset().left + (slider_btn.outerWidth() / 2)) / slider.outerWidth());
            };

            slider_btn.osc_dragger({
                cursor: 'pointer',
                fire_hook_callback: function (params) {
                    var slider_offset = slider.offset();

                    params.inst.config({
                        min_x: slider_offset.left - (slider_btn.outerWidth() / 2),
                        min_y: slider_offset.top + (slider.outerHeight() / 2) - (slider_btn.outerHeight() / 2),
                        max_x: slider_offset.left + slider.outerWidth() + (slider_btn.outerWidth() / 2),
                        max_y: slider_offset.top + (slider.outerHeight() / 2) + (slider_btn.outerHeight() / 2)
                    });

                    _callback();
                },
                drag_hook_callback: function () {
                    _callback();
                }
            });

            return slider[0];
        }

        _objectText_editorRender(object) {
            const viewbox = this._viewBox();
            const ratio = this._zoom_ratio;

            object.elm.setAttribute('fill-opacity', 0);
            object.elm.setAttribute('stroke-opacity', 0);

            var timer = null;

            $(this._objectText_makeDiv(object.data.type_data))
                    .html(object.data.type_data.content.split(/\n/).join('<br />'))
                    .appendTo(this._container)
                    .css({
                        position: 'absolute',
                        top: (object.data.type_data.position.y - viewbox.y) * ratio,
                        left: (object.data.type_data.position.x - viewbox.x) * ratio,
                        transform: 'rotate(' + object.data.type_data.rotation + 'deg)',
                        outlineStyle: 'solid',
                        outlineColor: '#1ba0fd',
                        outlineWidth: '1px'
                    })
                    .swapZIndex()
                    .osc_nodeTextEditor({
                        multiline: true,
                        maxLines: object?.data?.personalized?.config?.max_lines || 1,
                    })
                    .focus(function () {
                        clearTimeout(timer);
                    })
                    .blur(function () {
                        clearTimeout(timer);

                        var node = $(this);

                        timer = setTimeout(function () {
                            object.data.type_data.content = node.osc_nodeTextEditor('getcontent').replace("\n", ' ').replace(/<br\s*\/>/gi, "\n");

                            node.remove();

                            object.elm.removeAttribute('fill-opacity');
                            object.elm.removeAttribute('stroke-opacity');

                            $(object.elm).trigger('object-update');
                        }, 200);
                    })
                    .focus();
        }

        _objectText_addPath() {
            var $this = this;

            var object = this._objectText_getCurrent();

            if (!object) {
                return;
            }

            this._objectUnselectCurrent();

            object.text_path = document.createElementNS($this._svg[0].namespaceURI, 'g');
            this._svg_group.helper.before(object.text_path);

            //Callback khi add new object (this._objectAdd)
            $this._callback.object_render = function (path) {
                if (object.data.type_data.path && object.data.type_data.path.key !== path.data.key) {
                    $this._objectRemove(object.data.type_data.path.key);
                }

                if (['rect', 'ellipse', 'path'].indexOf(path.data.type) < 0) {
                    $this._objectRemove(path.data.key);
                    return;
                }
                switch (object.data.type_data.style.text_align) {
                    case "center":
                        object.data.type_data.offset = 50;
                        break;
                    case "right":
                        object.data.type_data.offset = 100;
                        break;
                    default:
                        object.data.type_data.offset = 0;
                        break;
                }

                object.data.type_data.path = {
                    key: path.data.key,
                    type: path.data.type,
                    data: path.data.type_data
                };

                object.text_path.appendChild(path.elm);

                $(path.elm).bind("move", function(e, bounding_rect) {
                    $(object.elm).trigger("path-move", [bounding_rect])
                }).bind('object-update move resize', function () {
                    $(object.elm).trigger('object-update');
                });

                $this._objectSelect(path);

                $(object.elm).trigger('object-update');
            };

            $this._callback.object_remove = function (path) {
                $this._objectUnselect(path);

                if (object.data.type_data.path) {
                    $this._svg_group.defs.find('#text-path-' + object.data.type_data.path.key).remove();
                }

                delete object.data.type_data.path;

                $(object.elm).trigger('object-update');
            };

            $this._callback.finish_action = function () {
                if (!object.text_path) {
                    return;
                }

                object.text_path.parentNode.removeChild(object.text_path);

                delete object.text_path;
                delete $this._callback.object_render;
                delete $this._callback.object_remove;

                if (object.data.type_data.path) {
                    $this._objectRemove(object.data.type_data.path.key);
                }

                $(object.elm).trigger('object-update');
            };

            if (object.data.type_data.path) {
                this._objectAdd(object.data.type_data.path.type, object.data.type_data.path.data, object.data.type_data.path.key);
            }
        }

        _objectText_loadFont(font_name) {
            if (this._loaded_fonts.indexOf(font_name) >= 0) {
                return;
            }

            var $this = this;

            var font_key = $.md5(font_name);

            if (this._fonts[font_key] === null || typeof this._fonts[font_key] !== 'object') {
                if (!this._fonts[font_key]) {
                    this._fonts[font_key] = true;

                    $.ajax({
                        url: this._container.attr('data-font-url'),
                        type: 'post',
                        data: {font: font_name},
                        success: function (response) {
                            if (response.result !== 'OK') {
                                console.log('Load font error: ' + response.message);
                                return;
                            }
                            $this._fonts[$.md5(response.data.font_name)] = response.data;

                            $this._objectText_loadFont(response.data.font_name);
                        }
                    });
                }

                return;
            }

            this._loaded_fonts.push(font_name);

            var font_data = this._fonts[font_key];

            if (!font_data.css_url) {
                return;
            }

            /*
             <font-face>
             <font-face-src>
             <font-face-uri href="file:///.../DejaVuSans.svg"/>
             </font-face-src>
             </font-face>
             */

//            this._svg_group.defs.append($(document.createElementNS(this._svg[0].namespaceURI, 'style')).attr('type', 'text/css').append(document.createTextNode('@import url(' + font_data.css_url + ')')));

            var head = document.getElementsByTagName('head')[0];
            var link = document.createElement('link');

            link.setAttribute('href', font_data.css_url);
            link.setAttribute('rel', 'stylesheet');
            link.setAttribute('type', 'text/css');
            head.appendChild(link);

            const checkFontLoaded = (fontFamily, callback) => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = 20;
                canvas.height = 20;
                ctx.font = '20px FontRandomText';
                ctx.fillText('a', 0, 20);

                const initialBase64 = canvas.toDataURL();
                let count = 0,
                    isLoaded = false;
                const check = () => {
                    ctx.font = `20px '${fontFamily}'`;
                    ctx.clearRect(0, 0, 20, 20);
                    ctx.fillText('a', 0, 20);

                    if (isLoaded || count > 60) {
                        callback(false);
                        return;
                    }

                    const base64 = canvas.toDataURL();

                    if (initialBase64 !== base64) {
                        isLoaded = true;
                        console.log('loaded', count);
                        callback(true);
                    } else {
                        count += 1;
                        setTimeout(check, 500);
                    }
                };
                check();
            };

            // var sheet, cssRules;
            //
            // if ('sheet' in link) {
            //     sheet = 'sheet';
            //     cssRules = 'cssRules';
            // } else {
            //     sheet = 'styleSheet';
            //     cssRules = 'rules';
            // }
            //
            // var $this = this;
            //
            // var interval_id = setInterval(function () {
            //     try {
            //         if (link[sheet] && link[sheet][cssRules].length) {
            //             clearInterval(interval_id);
            //             clearTimeout(timeout_id);
            //
            //             $this._objects.forEach(function (object) {
            //                 if (object.data.type === 'text') {
            //                     $(object.elm).trigger('object-update');
            //                 }
            //             });
            //         }
            //     } catch (e) {
            //     }
            // }, 10);
            //
            // var timeout_id = setTimeout(function () {
            //     clearInterval(interval_id);
            //     clearTimeout(timeout_id);
            //     head.removeChild(link);
            // }, 30000);
            checkFontLoaded(font_name, (loaded) => {
                try {
                    if (loaded) {
                        $.each($this._objects, function (key, object) {
                            if (object.data.type === 'text') {
                                $(object.elm).trigger('object-update');
                            }
                        });
                    } else {
                        head.removeChild(link);
                    }
                } catch (e) {
                    console.error("loi luc check upload font: ", e.message)
                }
            })
        }

        _commandEllipse_init(cmd_btn) {
            cmd_btn.append($.renderIcon('circle'));
        }

        _commandEllipse_exec(cmd_btn) {
            var $this = this;

            this._svg.unbind('mousedown.tool-ellipse').bindUp('mousedown.tool-ellipse', function (e) {
                e.preventDefault;
                e.stopPropagation();
                e.stopImmediatePropagation();

                $this._objectUnselectCurrent();

                const object = $this._objectAdd('ellipse', {center: $this._cursorPoint(e), rx: 0, ry: 0, rotation: 0});

                $this._svg.unbind('mousemove.tool-ellipse').bind('mousemove.tool-ellipse', function (e) {
                    $this._objectEllipse_calculateData(e);
                });

                $(document).bind('mouseup.tool-ellipse', function () {
                    const {rx, ry} = object.data?.type_data || {}
                    if(!rx || !ry || rx <=0 || ry <=0 ) {
                        $this._objectRemove(object.data.key)
                    }
                    if ($(cmd_btn).hasClass('active')) {
                        $(cmd_btn).trigger('click');
                    }
                });
            });
        }

        _commandEllipse_clear() {
            this._svg.unbind('mousedown.tool-ellipse');
            this._svg.unbind('mousemove.tool-ellipse');
            $(document).unbind('mouseup.tool-ellipse');
        }

        _objectEllipse_render(data, ellipseKey) {
            var $this = this;

            var ellipse = document.createElementNS(this._svg[0].namespaceURI, 'ellipse');

            ellipse.setAttribute('data-icon', 'circle');
            ellipse.setAttribute('cx', data.center.x);
            ellipse.setAttribute('cy', data.center.y);
            ellipse.setAttribute('rx', data.rx);
            ellipse.setAttribute('ry', data.ry);

            $(ellipse).bind('move resize', function (e, bounding_rect) {
                data.rx = bounding_rect.width / 2;
                data.ry = bounding_rect.height / 2;
                data.center.x = bounding_rect.x + data.rx;
                data.center.y = bounding_rect.y + data.ry;

                ellipse.setAttribute('cx', data.center.x);
                ellipse.setAttribute('cy', data.center.y);
                ellipse.setAttribute('rx', data.rx);
                ellipse.setAttribute('ry', data.ry);

                $this._elmSetTransform(ellipse, 0, 0, data.rotation);
            }).bind('parent_transform', function (e, scale, translate, rotation, flip, pivot) {
                e.stopPropagation();
                var bounding_rect = {
                    x: data.center.x - data.rx,
                    y: data.center.y - data.ry,
                    width: data.rx * 2,
                    height: data.ry * 2,
                    rotation: data.rotation,
                    // flip_vertical: data.flip_vertical,
                    // flip_horizontal: data.flip_horizontal
                };

                $this._objectApplyTransformToBoundingRect(bounding_rect, scale, translate, rotation, flip, pivot);

                data.rotation = bounding_rect.rotation;

                // if (bounding_rect.flip_vertical) {
                //     data.flip_vertical = bounding_rect.flip_vertical;
                // } else {
                //     delete data.flip_vertical;
                // }

                // if (bounding_rect.flip_horizontal) {
                //     data.flip_horizontal = bounding_rect.flip_horizontal;
                // } else {
                //     delete data.flip_horizontal;
                // }

                $(ellipse).triggerHandler('resize', [bounding_rect]);
            }).bind('rotate', function (e, degress) {
                data.rotation = degress;

                $this._elmSetTransform(ellipse, 0, 0, data.rotation);
            })

            return ellipse;
        }

        _objectEllipse_calculateData(e) {
            var object = this._objectEllipse_getCurrent();

            if (!object) {
                return;
            }

            var cursor = this._cursorPoint(e);

            var bounding_rect = {
                x: 0,
                y: 0,
                width: Math.abs(cursor.x - object.data.type_data.center.x) * 2,
                height: Math.abs(cursor.y - object.data.type_data.center.y) * 2
            };

            if (e.shiftKey) {
                bounding_rect.width = Math.max(bounding_rect.width, bounding_rect.height);
                bounding_rect.height = bounding_rect.width;
            }

            bounding_rect.x = object.data.type_data.center.x - (bounding_rect.width / 2);
            bounding_rect.y = object.data.type_data.center.y - (bounding_rect.height / 2);

            $(object.elm).triggerHandler('resize', [bounding_rect]);
            $(object.elm).triggerHandler('move', [bounding_rect]);
        }

        _objectEllipse_getCurrent() {
            return this._objectGetCurrent('ellipse');
        }

        _commandRect_init(cmd_btn) {
            cmd_btn.append($.renderIcon('rect'));
        }

        _commandRect_exec(cmd_btn) {
            var $this = this;

            this._svg.unbind('mousedown.tool-rect').bindUp('mousedown.tool-rect', function (e) {
                e.preventDefault;
                e.stopPropagation();
                e.stopImmediatePropagation();

                $this._objectUnselectCurrent();

                var object = $this._objectAdd('rect', {position: $this._cursorPoint(e), size: {width: 0, height: 0}, rotation: 0});

                var anchor_point = object.data.type_data.position;

                $this._svg.unbind('mousemove.tool-rect').bind('mousemove.tool-rect', function (e) {
                    var data = $this._objectRect_calculateData(e, anchor_point);
                    $.extend(object.data.type_data, data);

                    var bounding_rect = {x: data.position.x, y: data.position.y, width: data.size.width, height: data.size.height};

                    $(object.elm).triggerHandler('resize', [bounding_rect]);
                    $(object.elm).triggerHandler('move', [bounding_rect]);
                });

                $(document).bind('mouseup.tool-rect', function () {
                    const size = object.data?.type_data?.size
                    if(!size || !size.width || !size.height || size.width <= 0 || size.height <= 0) {
                        $this._objectRemove(object.data.key)
                    }
                    if (cmd_btn.hasClass('active')) {
                        cmd_btn.trigger('click');
                    }
                });
            });
        }

        _commandRect_clear(cmd_btn) {
            this._svg.unbind('mousedown.tool-rect');
            this._svg.unbind('mousemove.tool-rect');
            $(document).unbind('mouseup.tool-rect');
        }

        _objectRect_render(data, rectKey) {
            var $this = this;

            var rect = document.createElementNS(this._svg[0].namespaceURI, 'rect');

            rect.setAttribute('data-icon', 'rect');
            rect.setAttribute('x', data.position.x);
            rect.setAttribute('y', data.position.y);
            rect.setAttribute('width', data.size.width);
            rect.setAttribute('height', data.size.height);

            var __setTransform = function () {
                $this._elmSetTransform(rect, 0, 0, data.rotation, {x: data.flip_vertical ? 1 : 0, y: data.flip_horizontal ? 1 : 0});
            };

            $(rect).bind('move resize', function (e, bounding_rect) {
                data.position.x = bounding_rect.x;
                data.position.y = bounding_rect.y;
                data.size.width = bounding_rect.width;
                data.size.height = bounding_rect.height;

                rect.setAttribute('x', data.position.x);
                rect.setAttribute('y', data.position.y);
                rect.setAttribute('width', data.size.width);
                rect.setAttribute('height', data.size.height);

                __setTransform();
            }).bind('parent_transform', function (e, scale, translate, rotation, flip, pivot) {
                e.stopPropagation();
                var bounding_rect = {
                    x: data.position.x,
                    y: data.position.y,
                    width: data.size.width,
                    height: data.size.height,
                    rotation: data.rotation,
                    flip_vertical: data.flip_vertical,
                    flip_horizontal: data.flip_horizontal
                };

                $this._objectApplyTransformToBoundingRect(bounding_rect, scale, translate, rotation, flip, pivot);

                data.rotation = bounding_rect.rotation;

                if (bounding_rect.flip_vertical) {
                    data.flip_vertical = bounding_rect.flip_vertical;
                } else {
                    delete data.flip_vertical;
                }

                if (bounding_rect.flip_horizontal) {
                    data.flip_horizontal = bounding_rect.flip_horizontal;
                } else {
                    delete data.flip_horizontal;
                }

                $(rect).triggerHandler('resize', [bounding_rect]);
            })
            .bind('rotate', function (e, degress) {
                data.rotation = degress;

                __setTransform();
            }).bind('flip-horizontal', function (e) {
                e.stopPropagation();

                if (data.flip_horizontal) {
                    delete data.flip_horizontal;
                } else {
                    data.flip_horizontal = 1;
                }

                __setTransform();
            }).bind('flip-vertical', function (e) {
                e.stopPropagation();

                if (data.flip_vertical) {
                    delete data.flip_vertical;
                } else {
                    data.flip_vertical = 1;
                }

                __setTransform();
            }).bind('personalized-update', function(){
                // const image = document.createElementNS($this._svg[0].namespaceURI, 'image');

                // image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", 'https://scannables.scdn.co/uri/plain/jpeg/010101/white/640/spotify:user:spotify:playlist:37i9dQZF1DXcBWIGoYBM5M');
                // image.setAttribute('x', bbox.x);
                // image.setAttribute('y', bbox.y);
                // image.setAttribute('width', bbox.width);
                // image.setAttribute('height', bbox.height);
                // $(document.createElementNS($this._svg[0].namespaceURI, 'pattern'))
                //     .attr({
                //         'id':  'spotify-' + object.data.key,
                //         'patternUnits': 'userSpaceOnUse',
                //         x: 0,
                //         y: 0,
                //         width: '100%',
                //         height: '100%'
                //     }).append(image).appendTo($this._svg_group.defs);
                // $(object.elm).attr('fill', `url(#spotify-${object.data.key})`)

            });

            return rect;
        }

        _objectRect_configRender(object) {
            this._objectConfigRender_coordinate(object);
            this._objectConfigRender_dimension(object);
            this._objectConfigRender_rotation(object);
            this._objectConfigRender_flip(object);
            this._objectConfigRender_copyPasteAttributes(object);
        }

        _objectRect_calculateData(e, anchor) {
            var cursor = this._cursorPoint(e);

            var data = {
                size: {
                    width: Math.abs(cursor.x - anchor.x),
                    height: Math.abs(cursor.y - anchor.y)
                },
                position: {
                    x: anchor.x,
                    y: anchor.y
                }
            };

            if (e.shiftKey) {
                data.size.width = data.size.height = Math.max(data.size.width, data.size.height);
            }

            if (cursor.x < anchor.x) {
                data.position.x = anchor.x - data.size.width;
            }

            if (cursor.y < anchor.y) {
                data.position.y = anchor.y - data.size.height;
            }

            return data;
        }

        _appendSpotifyToRect(object) {
            const personalized = object.data.personalized;
            if(personalized?.type !== "spotify") return;

            const image = document.createElementNS(this._svg[0].namespaceURI, 'image');
            const bgColor = personalized.config.background_color?.substring(1);
            const barColor = personalized.config.bar_color?.substring(1);
            const displayStyle = personalized.config.display_style;

            let url = `/personalizedDesign/backend/previewSpotifySvg`
            if(bgColor) url += `/backgroundColor/${bgColor}`;
            if(barColor) url += `/barColor/${barColor}`;
            if(displayStyle) url += `/displayStyle/${displayStyle}`;

            url += `/hash/${OSC_HASH}`;

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", url);

            image.setAttribute('width', 1);
            image.setAttribute('height', 1);
            image.setAttribute('preserveAspectRatio', 'none');

            const id =  'spotify-' + object.data.key;
            this._svg_group.defs.find(`#${id}`).remove();
            const pattern = document.createElementNS(this._svg[0].namespaceURI, 'pattern');
            pattern.setAttribute('id', id);
            pattern.setAttribute('patternContentUnits', 'objectBoundingBox');
            pattern.setAttribute('width', '100%');
            pattern.setAttribute('height', '100%');

            $(pattern).append(image).appendTo(this._svg_group.defs);

            $(object.elm).attr({
                'fill': `url(#${id})`,
                'stroke': 'none'
            })
                .bind("object-remove", () => {
                    this._svg_group.defs.find(`#${id}`).remove();
                })
        }

        _commandImage_init(cmd_btn) {
            var $this = this;

            $(cmd_btn).osc_uploader({
                max_files: 1,
                max_connections: 1,
                process_url: this._container.attr('data-upload-url'),
                btn_content: $.renderIcon('image'),
                dragdrop_content: 'Drop here to upload',
                image_mode: true,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {

            }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {

            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                pointer.success = false;

                eval('response = ' + response);

                if (response.result !== 'OK') {
                    toastr.error(response.message);
                    return;
                }

                const image = response.data;
                //calculate current background size
                const {x, y, width, height} = $this._getObjectRectFitBackground({
                    width: image.width / $this._zoom_ratio,
                    height: image.height / $this._zoom_ratio
                })

                const img_data = {
                    url: image.url,
                    original_size: {
                        width: image.width,
                        height: image.height
                    },
                    hash: image.hash,
                }

                $this._objectAdd('image', {
                    id: $this._addToImageData(img_data),
                    size: { width, height },
                    position: { x, y },
                    rotation: 0
                });
                $($this._container).focus();
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                toastr.error("UPLOAD ERROR: " + error_message);
            });

            return false;
        }

        _getObjectRectFitBackground(size /* {width, height}*/) {
            //calculate current background size
            const bgBbox = {
                width: this.document_type.width,
                height: this.document_type.height,
                x: 0,
                y: 0
            }
            return this._getRectFitBBox(size, bgBbox, true);
        }

        _getRectFitBBox(size/* {width, height}*/, bbox /* {x, y, width, height}*/, keepSize = false){
            const sizeRatio = size.width / size.height;

            let x = 0, y = 0, width = 0, height = 0;

            if(bbox.width / bbox.height > sizeRatio) {
                if(keepSize && size.height < bbox.height) {
                    height = size.height;
                    y = bbox.y + bbox.height/2 - height/2;
                }else {
                    height = bbox.height;
                    y = bbox.y;
                }
                width = height * sizeRatio;
                x = bbox.x + bbox.width/2 - width/2;
            } else {
                if(keepSize && size.width < bbox.width) {
                    width = size.width;
                    x = bbox.x + bbox.width/2 - width/2;
                } else {
                    width = bbox.width;
                    x = bbox.x;
                }
                height = width / sizeRatio;
                y = bbox.y + bbox.height/2 - height/2;
            };
            return {x, y, width, height}
        }

        _elmGetBoundingSvgRect(elm) {
            var viewbox = this._viewBox();
            var svg_rect = this._svg[0].getBoundingClientRect();
            var rect = elm.getBoundingClientRect();

            return {
                x: rect.x - svg_rect.x + viewbox.x,
                y: rect.y - svg_rect.y + viewbox.y,
                width: rect.width,
                height: rect.height
            };
        }

        _elmGetBBox(elm) {
            var bb = elm.getBBox();

            var object_data = {};

            $(elm).trigger('object-get-data', [object_data]);

            if (typeof object_data.data !== 'undefined' && typeof object_data.data.type_data.size !== 'undefined' && typeof object_data.data.type_data.position !== 'undefined') {
                bb.width = object_data.data.type_data.size.width;
                bb.height = object_data.data.type_data.size.height;

                bb.x = object_data.data.type_data.position.x;
                bb.y = object_data.data.type_data.position.y;

                return bb;
            }

            if (elm.tagName !== 'image') {
                return bb;
            }

            var svg = elm.ownerSVGElement;
            var m = elm.getTransformToElement(elm.parentNode);

            // Create an array of all four points for the original bounding box
            var pts = [
                svg.createSVGPoint(), svg.createSVGPoint(),
                svg.createSVGPoint(), svg.createSVGPoint()
            ];

            pts[0].x = bb.x;
            pts[0].y = bb.y;
            pts[1].x = bb.x + bb.width;
            pts[1].y = bb.y;
            pts[2].x = bb.x + bb.width;
            pts[2].y = bb.y + bb.height;
            pts[3].x = bb.x;
            pts[3].y = bb.y + bb.height;

            var xMin = Infinity, xMax = -Infinity, yMin = Infinity, yMax = -Infinity;
            pts.forEach(function (pt) {
                pt = pt.matrixTransform(m);
                xMin = Math.min(xMin, pt.x);
                xMax = Math.max(xMax, pt.x);
                yMin = Math.min(yMin, pt.y);
                yMax = Math.max(yMax, pt.y);
            });

            bb.x = xMin;
            bb.width = xMax - xMin;
            bb.y = yMin;
            bb.height = yMax - yMin;

            return bb;
        }

        _elmGetTransform(elm) {
            function __deltaTransformPoint(matrix, point) {
                var dx = point.x * matrix.a + point.y * matrix.c + 0;
                var dy = point.x * matrix.b + point.y * matrix.d + 0;

                return {x: dx, y: dy};
            }

            var matrix = elm.getCTM();

            // calculate delta transform point
            var px = __deltaTransformPoint(matrix, {x: 0, y: 1});
            var py = __deltaTransformPoint(matrix, {x: 1, y: 0});

            // calculate skew
            var skewX = ((180 / Math.PI) * Math.atan2(px.y, px.x) - 90);
            var skewY = ((180 / Math.PI) * Math.atan2(py.y, py.x));

            return {
                translateX: matrix.e,
                translateY: matrix.f,
                scaleX: Math.sqrt(matrix.a * matrix.a + matrix.b * matrix.b),
                scaleY: Math.sqrt(matrix.c * matrix.c + matrix.d * matrix.d),
                skewX: skewX,
                skewY: skewY,
                rotation: skewX // rotation is the same as skew x
            };
        }

        _elmSetTransform(elm, scale, translate, degrees, flip) {
            elm.removeAttribute('transform');

            scale = scale ? scale : {x: 1, y: 1};
            translate = translate ? translate : {x: 0, y: 0};
            flip = flip ? flip : {x: 0, y: 0};
            degrees = degrees ? degrees : 0;

            var bbox = elm.getBBox();

            var transform = [];

            if (degrees !== 0) {
                const pivot = {
                    x: bbox.x + (bbox.width * scale.x / 2) + translate.x,
                    y: bbox.y + (bbox.height * scale.y / 2) + translate.y
                }
                transform.push(`rotate(${degrees} ${pivot.x} ${pivot.y})`);
            }

            translate.x += (scale.x - 1) * -bbox.x;
            translate.y += (scale.y - 1) * -bbox.y;

            if (translate.x !== 0 || translate.y !== 0) {
                transform.push('translate(' + translate.x + ' ' + translate.y + ')');
            }

            if (scale.x !== 1 || scale.y !== 1) {
                transform.push('scale(' + scale.x + ' ' + scale.y + ')');
            }

            if (flip.x || flip.y) {
                transform.push('scale(' + (flip.x ? -1 : 1) + ' ' + (flip.y ? -1 : 1) + ')');
                transform.push('translate(' + (flip.x ? -(bbox.width + bbox.x * 2) : 0) + ' ' + (flip.y ? -(bbox.height + bbox.y * 2) : 0) + ')');
            }

            if (transform.length > 0) {
                elm.setAttribute('transform', transform.join(' '));
            }

            return elm;

//            var matrix = this._svg[0].createSVGMatrix();
//
//            if (translate.x !== 0 || translate.y !== 0) {
//                matrix = matrix.translate(translate.x + bbox.width / 2 * (scale.x - 1), translate.y + bbox.height / 2 * (scale.y - 1));
//            }
//
//            if (degrees !== 0) {
//                matrix = matrix.translate((bbox.x + bbox.width / 2), (bbox.y + bbox.height / 2))
//                        .rotate(degrees)
//                        .translate(-(bbox.x + bbox.width / 2), -(bbox.y + bbox.height / 2));
//            }
//
//            if (scale.x !== 1 || scale.y !== 1) {
//                matrix = matrix.translate((bbox.x + bbox.width / 2), (bbox.y + bbox.height / 2))
//                        .scaleNonUniform(scale.x, scale.y)
//                        .translate(-(bbox.x + bbox.width / 2), -(bbox.y + bbox.height / 2));
//            }
//
//            elm.transform.baseVal.initialize(this._svg[0].createSVGTransformFromMatrix(matrix));
        }

        _objectApplyTransformToBoundingRect(bounding_rect, scale, translate, rotation, flip, pivot) {
            if (typeof bounding_rect.rotation === 'undefined') {
                bounding_rect.rotation = 0;
            }

            var new_pivot = {
                x: pivot.x + translate.x,
                y: pivot.y + translate.y
            };

            var points = {
                top_left: {
                    x: new_pivot.x - (pivot.x - bounding_rect.x) * scale.x,
                    y: new_pivot.y - (pivot.y - bounding_rect.y) * scale.y
                },
                top_right: {
                    x: new_pivot.x - (pivot.x - (bounding_rect.x + bounding_rect.width)) * scale.x,
                    y: new_pivot.y - (pivot.y - bounding_rect.y) * scale.y
                },
                bottom_left: {
                    x: new_pivot.x - (pivot.x - bounding_rect.x) * scale.x,
                    y: new_pivot.y - (pivot.y - (bounding_rect.y + bounding_rect.height)) * scale.y
                },
                bottom_right: {
                    x: new_pivot.x - (pivot.x - (bounding_rect.x + bounding_rect.width)) * scale.x,
                    y: new_pivot.y - (pivot.y - (bounding_rect.y + bounding_rect.height)) * scale.y
                }
            };

            bounding_rect.x = points.top_left.x;
            bounding_rect.y = points.top_left.y;
            bounding_rect.width = points.top_right.x - points.top_left.x;
            bounding_rect.height = points.bottom_left.y - points.top_left.y;

            if (rotation !== 0) {
                var point = {x: bounding_rect.x + bounding_rect.width / 2, y: bounding_rect.y + bounding_rect.height / 2};

                var _rotation = Math.atan2(point.y - new_pivot.y, point.x - new_pivot.x) * 180 / Math.PI;

                _rotation += rotation;

                var distance = this._pointGetDistance(new_pivot, point);

                var radian = _rotation * Math.PI / 180;

                bounding_rect.x = new_pivot.x + (distance * Math.cos(radian)) - bounding_rect.width / 2;
                bounding_rect.y = new_pivot.y + (distance * Math.sin(radian)) - bounding_rect.height / 2;

                bounding_rect.rotation += rotation;
            }

            if (flip.x) {
                if (bounding_rect.flip_vertical) {
                    delete bounding_rect.flip_vertical;
                } else {
                    bounding_rect.flip_vertical = 1;
                }

                bounding_rect.x = bounding_rect.x + ((new_pivot.x - bounding_rect.x) * 2) - bounding_rect.width;

                bounding_rect.rotation = -bounding_rect.rotation;
            }

            if (flip.y) {
                if (bounding_rect.flip_horizontal) {
                    delete bounding_rect.flip_horizontal;
                } else {
                    bounding_rect.flip_horizontal = 1;
                }

                bounding_rect.y = bounding_rect.y + ((new_pivot.y - bounding_rect.y) * 2) - bounding_rect.height;

                bounding_rect.rotation = -bounding_rect.rotation;
            }
        }

        _objectImage_render(data, imageKey) {
            const $this = this;

            const image = document.createElementNS(this._svg[0].namespaceURI, 'image');

            const imgData = this._image_data[data.id];

            const __setTransform = function () {
                $this._elmSetTransform(
                        image,
                        {
                            x: data.size.width / imgData.original_size.width,
                            y: data.size.height / imgData.original_size.height
                        },
                        {
                            x: data.position.x,
                            y: data.position.y
                        },
                        data.rotation,
                        {
                            x: data.flip_vertical ? 1 : 0,
                            y: data.flip_horizontal ? 1 : 0
                        }
                );
            };

            const previewUrl = $this._getLocalImgStorageUrl(imgData.url.replace(/^(.+)\.([a-zA-Z0-9]+)$/, '$1.preview.$2'));

            image.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", previewUrl);
            image.setAttribute('width', imgData.original_size.width);
            image.setAttribute('height', imgData.original_size.height);
            image.setAttribute('data-icon', 'image');

            __setTransform();

            $(image).bind('move resize', function (e, bounding_rect) {
                e.stopPropagation();

//                var object = $this._objects[image.getAttribute('data-object')];
//
//                if (object) {
//                    $this._objectImage_applyTransformToData(object.data, {x: bounding_rect.width / data.size.width, y: bounding_rect.height / data.size.height}, {x: bounding_rect.x - data.position.x, y: bounding_rect.y - data.position.y}, 0, {x: 0, y: 0}, {x: data.position.x + data.size.width / 2, y: data.position.y + data.size.height / 2});
//                } else {
                data.position.x = bounding_rect.x;
                data.position.y = bounding_rect.y;

                data.size.width = bounding_rect.width;
                data.size.height = bounding_rect.height;
//                }

                __setTransform();
            }).bind('rotate', function (e, degress) {
                e.stopPropagation();

//                var object = $this._objects[image.getAttribute('data-object')];
//
//                if (object) {
//                    $this._objectImage_applyTransformToData(object.data, {x: 1, y: 1}, {x: 0, y: 0}, degress - data.rotation, {x: 0, y: 0}, {x: data.position.x + data.size.width / 2, y: data.position.y + data.size.height / 2});
//                } else {
                data.rotation = degress;
//                }

                __setTransform();
            }).bind('flip-horizontal', function (e) {
                e.stopPropagation();

//                var object = $this._objects[image.getAttribute('data-object')];
//
//                if (object) {
//                    $this._objectImage_applyTransformToData(object.data, {x: 1, y: 1}, {x: 0, y: 0}, 0, {x: 0, y: 1}, {x: data.position.x + data.size.width / 2, y: data.position.y + data.size.height / 2});
//                } else {
                if (data.flip_horizontal) {
                    delete data.flip_horizontal;
                } else {
                    data.flip_horizontal = 1;
                }
//                }

                __setTransform();
            }).bind('flip-vertical', function (e) {
                e.stopPropagation();

//                var object = $this._objects[image.getAttribute('data-object')];
//
//                if (object) {
//                    $this._objectImage_applyTransformToData(object.data, {x: 1, y: 1}, {x: 0, y: 0}, 0, {x: 1, y: 0}, {x: data.position.x + data.size.width / 2, y: data.position.y + data.size.height / 2});
//                } else {
                if (data.flip_vertical) {
                    delete data.flip_vertical;
                } else {
                    data.flip_vertical = 1;
                }
//                }

                __setTransform();
            }).bind('parent_transform', function (e, scale, translate, rotation, flip, pivot) {
                e.stopPropagation();

                $this._objectImage_applyTransformToData($this._objects[image.getAttribute('data-object')].data, scale, translate, rotation, flip, pivot);

                __setTransform();
            })

            return image;
        }

        _objectImage_applyTransformToData(object_data, scale, translate, rotation, flip, pivot) {
            var $this = this;

            var data_list = [object_data.type_data];

            if (object_data.personalized !== null && typeof object_data.personalized === 'object' && object_data.personalized.type === 'imageSelector') {
                $.each(object_data.personalized.config.groups, function (group_key, group) {
                    $.each(group.images, function (image_key, image) {
                        if (image_key === object_data.personalized.config.default_key) {
                            return;
                        }
                        data_list.push(image.data.type_data);
                    });
                });
            }

            data_list.forEach(function (_data) {
                var bounding_rect = {
                    x: _data.position.x,
                    y: _data.position.y,
                    width: _data.size.width,
                    height: _data.size.height,
                    rotation: _data.rotation,
                    flip_vertical: _data.flip_vertical,
                    flip_horizontal: _data.flip_horizontal
                };

                $this._objectApplyTransformToBoundingRect(bounding_rect, scale, translate, rotation, flip, pivot);

                _data.rotation = bounding_rect.rotation;

                if (bounding_rect.flip_vertical) {
                    _data.flip_vertical = bounding_rect.flip_vertical;
                } else {
                    delete _data.flip_vertical;
                }

                if (bounding_rect.flip_horizontal) {
                    _data.flip_horizontal = bounding_rect.flip_horizontal;
                } else {
                    delete _data.flip_horizontal;
                }

                _data.position.x = bounding_rect.x;
                _data.position.y = bounding_rect.y;

                _data.size.width = bounding_rect.width;
                _data.size.height = bounding_rect.height;
            });
        }

        _objectImage_configRender(object) {
            this._objectConfigRender_coordinate(object);
            this._objectConfigRender_dimension(object);
            this._objectConfigRender_rotation(object);
            this._objectConfigRender_flip(object);
            this._objectConfigRender_copyPasteAttributes(object);
            //this._objectConfigRender_setRenderDesignAttributes(object);
        }

        _objectGroup_render(data) {
            var $this = this;

            var group = document.createElementNS(this._svg[0].namespaceURI, 'g');

            group.setAttribute('data-icon', 'folder-solid');

            var info = {};


            function __setTransform() {
                var bbox = group.getBBox();

                var scale, translate;
                var pivot = {
                    x: bbox.x + bbox.width / 2,
                    y: bbox.y + bbox.height / 2
                };
                const new_pivot = {
                    x: info.x + info.width / 2,
                    y:info.y + info.height / 2
                }

                var scale =  info.scale || {x: 1, y: 1}

                translate = {
                    x:   (new_pivot.x - pivot.x * scale.x) -  ((scale.x - 1) * -bbox.x) ,
                    y:   (new_pivot.y - pivot.y * scale.y)  -  ((scale.y - 1) * -bbox.y)
                };
                const flip = {
                    x: info.flip_vertical ? 1 : 0,
                    y: info.flip_horizontal ? 1 : 0,
                }

                $this._elmSetTransform(group, scale, translate, info.rotation, flip);
            }

            $(group).bind('parent_transform', function (e, scale, translate, rotation, flip, pivot) {
                e.stopPropagation();

                $this._objectGroup_applyTransformToData($this._objects[group.getAttribute('data-object')].data, scale, translate, rotation, flip, pivot);
            }).bind('object-select', function () {
                $(this).trigger('transform-begin');
            }).bind('object-clearHelper', function () {
                $(this).trigger('transform-end');
            }).bind('transform-begin', function (e) {
                e.stopImmediatePropagation();

                var bbox = group.getBBox();

                info = {
                    x: bbox.x,
                    y: bbox.y,
                    width: bbox.width,
                    height: bbox.height,
                    rotation: 0,
                };

            }).bind('transform-end', function (e) {
                e.stopImmediatePropagation();

                group.removeAttribute('transform');

                if (typeof info.x !== 'undefined' && info.width !== 0 && info.height !== 0) {
                    var bbox = group.getBBox();

                    //tâm của bounding box;
                    var pivot = {
                        x: bbox.x + bbox.width / 2,
                        y: bbox.y + bbox.height / 2
                    };

                    const new_pivot = {
                        x: info.x + info.width /2 ,
                        y: info.y + info.height /2
                    }

                    var scale =  info.scale || {x: 1, y: 1}

                    var rotation =  info.rotation || 0;

                    var translate = {
                        x: new_pivot.x - pivot.x ,
                        y: new_pivot.y - pivot.y
                    };

                    var flip = {
                        x: info.flip_vertical,
                        y: info.flip_horizontal
                    };

                    info = {};

                    $(group).find('[data-object]').each(function () {
                        $(this).trigger('parent_transform', [scale, translate, rotation, flip, pivot]);
                    });

//                    $this._objectGroup_applyTransformToData($this._objects[group.getAttribute('data-object')].data, scale, translate, rotation, flip, pivot);
                }

                info = {};
            }).bind('move resize', function (e, bounding_rect) {
                if (typeof info.x === 'undefined') {
                    return;
                }

                info.x = bounding_rect.x;
                info.y = bounding_rect.y;
                info.width = bounding_rect.width;
                info.height = bounding_rect.height;
                info.scale = bounding_rect.scale

                __setTransform();

            }).bind('rotate', function (e, rotation) {
                if (typeof info.x === 'undefined') {
                    return;
                }

                info.rotation = rotation;

                __setTransform();
            }).bind('flip-horizontal', function (e) {
                e.stopPropagation();

                if (typeof info.x === 'undefined') {
                    return;
                }

                if (info.flip_horizontal) {
                    delete info.flip_horizontal;
                } else {
                    info.flip_horizontal = 1;
                }

                __setTransform();
                $this._objectRefresh();
            }).bind('flip-vertical', function (e) {
                e.stopPropagation();

                if (typeof info.x === 'undefined') {
                    return;
                }

                if (info.flip_vertical) {
                    delete info.flip_vertical;
                } else {
                    info.flip_vertical = 1;
                }

                __setTransform();
                $this._objectRefresh();
            })

            return group;
        }

        _objectGroup_applyTransformToData(object_data, scale, translate, rotation, flip, pivot) {
            if (object_data.personalized === null || typeof object_data.personalized !== 'object' || object_data.personalized.type !== 'switcher') {
                return;
            }

            const $this = this;

            const __applyTransformToChildren = function (children) {
                children.forEach(function (_object_data) {
                    const applier = '_object' + $this._objectGetTypeFunctionName(_object_data.type) + '_applyTransformToData';

                    if (typeof $this[applier] === 'function') {
                        $this[applier](_object_data, $.extend({}, scale), $.extend({}, translate), rotation, $.extend({}, flip), $.extend({}, pivot));
                    }

                    if (_object_data.type === 'group') {
                        __applyTransformToChildren(_object_data.type_data.children);
                    }
                });
            };

            $.each(object_data.personalized.config.options, function (option_key, option) {
                if (option_key === object_data.personalized.config.default_option_key) {
                    return;
                }

                __applyTransformToChildren(option.objects);
            });
        }

        _objectGroup_configRender(object) {
            this._objectConfigRender_coordinate(object);
            this._objectConfigRender_dimension(object);
            this._objectConfigRender_rotation(object);
            this._objectConfigRender_flip(object);
            this._objectConfigRender_copyPasteAttributes(object)
        }

        _objectRefresh() {
            const object = Object.values(this._objects).find(object => object.selected);

            if (object) {
                this._objectUnselectCurrent();
                this._objectSelect(object);
            }
        }

        _objectGetTypeFunctionName(type) {
            return type.ucfirst();
        }

        //MODE: 1 [default]: add object, render and append to list, 2: Add object and return, 3: Just render and return object element, 4: Render, make object data and return object data
        _objectAdd(type, type_data, key, mode, options, ignored_auto_select = false) {
            if (options === null || typeof options !== 'object') {
                options = {};
            }
            var render = '_object' + this._objectGetTypeFunctionName(type) + '_render';

            if (typeof this[render] !== 'function') {
                return null;
            }

            mode = parseInt(mode);

            if (![3, 4].includes(mode)) {
                this._objectUnselectCurrent();
            }

            if (isNaN(mode) || mode < 1 || mode > 4) {
                mode = 1;
            }

            if (!key && type_data?.path?.key) {
                type_data.path.key = $.makeUniqid();
            }

            key = key ? key : $.makeUniqid();

            if (typeof type_data.fill === 'undefined') {
                type_data.fill = $('[data-cmd="fill"]').hasClass('no-color') ? 'none' : $('[data-cmd="fill"]').css('background-color');
            }

            if (typeof type_data.stroke === 'undefined') {
                let strokeWidth = 1 / this._zoom_ratio;
                if (strokeWidth < 1) strokeWidth = 1;

                type_data.stroke = {color: $('[data-cmd="stroke"]').hasClass('no-color') ? 'none' : $('[data-cmd="stroke"]').css('background-color'), width: strokeWidth};
            }

            var $this = this;

            var elm = this[render](type_data, key);

            elm.setAttribute('data-object', key);
            elm.setAttribute('data-type', type);

            $(elm).bind('object-update', function () {
                if (type_data.fill && type_data.fill !== 'none') {
                    var matched = type_data.fill.match(/^\s*rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)\s*$/i);

                    if (!matched) {
                        this.setAttribute('fill', type_data.fill);
                        this.removeAttribute('fill-opacity');
                    } else {
                        this.setAttribute('fill', 'rgb(' + matched[1] + ',' + matched[2] + ',' + matched[3] + ')');
                        this.setAttribute('fill-opacity', matched[4]);
                    }
                } else {
                    this.setAttribute('fill', 'none');
                    this.removeAttribute('fill-opacity');
                }
                if (type_data.stroke.color && type_data.stroke.color !== 'none') {
                    matched = type_data.stroke.color.match(/^\s*rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)\s*$/i);

                    if (!matched) {
                        this.setAttribute('stroke', type_data.stroke.color);
                        this.removeAttribute('stroke-opacity');
                    } else {
                        this.setAttribute('stroke', 'rgb(' + matched[1] + ',' + matched[2] + ',' + matched[3] + ')');
                        this.setAttribute('stroke-opacity', matched[4]);
                    }
                } else {
                    this.setAttribute('stroke', 'none');
                    this.removeAttribute('stroke-opacity');
                }

                if (type_data.stroke.color && type_data.stroke.color !== 'none') {
                    matched = type_data.stroke.color.match(/^\s*rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)\s*$/i);

                    if (!matched) {
                        this.setAttribute('stroke', type_data.stroke.color);
                        this.removeAttribute('stroke-opacity');
                    } else {
                        this.setAttribute('stroke', 'rgb(' + matched[1] + ',' + matched[2] + ',' + matched[3] + ')');
                        this.setAttribute('stroke-opacity', matched[4]);
                    }
                } else {
                    this.setAttribute('stroke', 'none');
                    this.removeAttribute('stroke-opacity');
                }

                this.setAttribute('stroke-width', type_data.stroke.width);

                if ($this._objectIsAncestor(this, $this._svg_group.object[0])) {
                    var object = $this._objects[this.getAttribute('data-object')];

                    $this._svg_group.defs.find('#filter-' + object.data.key).remove();
                    $(this).removeAttr('filter');

                    var filter_data = {filter: [], node: []};

                    if (typeof type_data.outline !== 'undefined' && type_data.outline.width !== 0 && type_data.outline.color !== 'none') {
                        let outline_width = type_data.outline.width;
                        if (this.getAttribute('font-size')) {
                            const current_font_size = Number(this.getAttribute('font-size'));
                            const original_font_size = type_data.style.font_size;

                            outline_width *= (current_font_size / original_font_size);
                        }

                        filter_data.filter.push($(document.createElementNS($this._svg[0].namespaceURI, 'feMorphology')).attr({'in': 'SourceAlpha', 'result': 'OUTLINE_DILATED', 'operator': 'dilate', 'radius': outline_width}));
                        filter_data.filter.push($(document.createElementNS($this._svg[0].namespaceURI, 'feFlood')).attr({'flood-color': type_data.outline.color, 'flood-opacity': '1', 'result': 'OUTLINE_COLOR'}));
                        filter_data.filter.push($(document.createElementNS($this._svg[0].namespaceURI, 'feComposite')).attr({'in': 'OUTLINE_COLOR', 'in2': 'OUTLINE_DILATED', 'operator': 'in', 'result': 'OUTLINE'}));

                        filter_data.node.push('OUTLINE');
                    } else {
                        delete type_data.outline;
                    }

                    if (filter_data.filter.length > 0) {
                        $(this).attr('filter', 'url(#filter-' + object.data.key + ')');

                        var filter = $(document.createElementNS($this._svg[0].namespaceURI, 'filter')).attr('id', 'filter-' + object.data.key).appendTo($this._svg_group.defs);

                        filter_data.filter.forEach(function (entry) {
                            filter.append(entry);
                        });

                        if (filter_data.node.length > 0) {
                            var merge_node = $(document.createElementNS($this._svg[0].namespaceURI, 'feMerge')).appendTo(filter);

                            filter_data.node.push('SourceGraphic');

                            filter_data.node.forEach(function (node_key) {
                                merge_node.append($(document.createElementNS($this._svg[0].namespaceURI, 'feMergeNode')).attr('in', node_key));
                            });
                        }
                    }
                }
            }).bind('object-get-data', function (e, pointer) {
                e.stopPropagation();

                $.extend(pointer, JSON.parse(JSON.stringify({
                    data: {
                        key: key,
                        type: type,
                        type_data: type_data
                    }
                })));
            }).trigger('object-update');

            if (mode === 3) {
                return elm;
            }

            this._object_index++;

            var object = {
                data: {
                    key: key,
                    type: type,
                    name: options.name ? options.name : 'New object #' + (this._object_index + 1),
                    showable: (typeof options.showable === 'boolean' && !options.showable) ? false : true,
                    locked: options.locked ? options.locked : false,
                    type_data: type_data,
                    index: options.index ? options.index : this._object_index,
                    personalized: options.personalized ? options.personalized : null
                },
                selected: false,
                elm: elm
            };

            object.data.is_not_render_design = options.is_not_render_design ? options.is_not_render_design : false;

            $(elm).unbind('object-get-data').bind('object-get-data', function (e, pointer) {
                e.stopPropagation();

                var object_data = JSON.parse(JSON.stringify(object));

                delete object_data.elm;

                $.extend(pointer, object_data);
            });

            if (mode === 4) {
                return object;
            }

            this._objects[key] = object;

            if (typeof this._callback.object_render === 'function') {
                this._callback.object_render(this._objects[key]);
            } else if (mode === 1) {
                this._objectAppend(this._objects[key], {group: options.group, before: options.before}, ignored_auto_select);

                $this._historyAdd();
            }

            return this._objects[key];
        }

        _objectEdit(key) {
            if (typeof this._objects[key] === 'undefined') {
                return;
            }

            var object = this._objects[key];

            if (!object.selected) {
                this._objectSelect(object);
            }

            if (!object.editing) {
                var editor_render = '_object' + this._objectGetTypeFunctionName(object.data.type) + '_editorRender';

                if (typeof this[editor_render] !== 'function' || !this._permissions.edit_layer) {
                    return;
                }

                object.editing = true;

                $(object.elm).trigger('object-clearHelper');

                $(object.helper).html('');

                this[editor_render](object);
            }
        }

        _objectAddMask() {
            var $this = this;

            var object = $this._objectGetCurrent();

            if (!object || ['group', "text"].includes(object.data.type)) {
                return;
            }

            this._objectUnselectCurrent();

            if (!Array.isArray(object.data.type_data.mask)) {
                object.data.type_data.mask = [];
            }

            object.elm.removeAttribute('clip-path');
            this._svg_group.defs.find('clipPath#mask-' + object.data.key).remove();

            object.mask = document.createElementNS($this._svg[0].namespaceURI, 'g');
            object.mask.setAttribute('data-mask', object.data.key);
            this._svg_group.helper.before(object.mask);

            this._callback.object_render = function (mask) {
                object.mask.appendChild(mask.elm);
                $this._objectSelect(mask);
            };

            this._callback.object_remove = function (mask) {
                $this._objectUnselect(mask);
            };

            this._callback.remove = function () {
                for (var object_key in object.data.type_data.mask) {
                    $this._objectRemove(object_key);
                }

                object.data.type_data.mask = [];

                object.mask.parentNode.removeChild(object.mask);

                delete object.mask;
                delete $this._callback.remove;
                delete $this._callback.object_render;
                delete $this._callback.object_remove;
                delete $this._callback.finish_action;

                $this._objectSelect(object);

                $(object.elm).trigger('object-update');
            };

            this._callback.finish_action = function () {
                object.data.type_data.mask = [];

                var target_bbox = $this._elmGetBBox(object.elm);

                const target_transform = $this._elmGetTransform(object.elm);

                var target_rotation = object.data.type_data.rotation;
                for (var i = 0; i < object.mask.childNodes.length; i++) {
                    var mask_object = object.mask.childNodes[i];

                    if (mask_object.nodeType !== Node.ELEMENT_NODE) {
                        continue;
                    }
                    const tranform = $this._elmGetTransform(mask_object);

                    if(object.data.type === "image") {
                        const bbox = $this._elmGetBBox(mask_object);

                        const bounding_rect =  {
                            x: (bbox.x - target_bbox.x) / target_transform.scaleX,
                            y: (bbox.y - target_bbox.y) / target_transform.scaleY,
                            width: bbox.width / target_transform.scaleX,
                            height: bbox.height / target_transform.scaleY
                        };

                        $(mask_object).triggerHandler('move', [bounding_rect]);
                        $(mask_object).triggerHandler('resize', [bounding_rect]);
                    }

                    $(mask_object).triggerHandler('rotate', [tranform.rotation - target_rotation]);

                    // bbox = $this._elmGetBBox(mask_object);
                    const mask_data = {};

                    $(mask_object).triggerHandler('object-get-data', [mask_data]);

                    if (typeof mask_data.data !== 'undefined') {
                        object.data.type_data.mask.push({
                            type: mask_data.data.type,
                            data: mask_data.data.type_data,
                            target_bbox:{
                                x: target_bbox.x,
                                y: target_bbox.y,
                                width: target_bbox.width,
                                height: target_bbox.height
                            },
                            target_rotation: target_rotation
                        });
                    }
                    $(mask_object).trigger('object-remove').unbind()
                }

                object.mask.parentNode.removeChild(object.mask);

                delete object.mask;
                delete $this._callback.remove;
                delete $this._callback.object_render;
                delete $this._callback.object_remove;

                for (var object_key in object.data.type_data.mask) {
                    $this._objectRemove(object_key);
                }

                $(object.elm).trigger('object-update');
            };

            const target_bbox = this._elmGetBBox(object.elm);
            const target_transform = this._elmGetTransform(object.elm);
            var target_rotation = object.data.type_data.rotation;

            object.data.type_data.mask.forEach((mask_data)  => {
                const data = JSON.parse(JSON.stringify(mask_data.data)) // clone
                const mask_object = this._objectAdd(mask_data.type, data);

                if(object.data.type === "image") {
                    const bbox = this._elmGetBBox(mask_object.elm);

                    const bounding_rect =  {
                        x: bbox.x * target_transform.scaleX + target_bbox.x,
                        y: bbox.y * target_transform.scaleY + target_bbox.y,
                        width: bbox.width * target_transform.scaleX,
                        height: bbox.height * target_transform.scaleY
                    };

                    $(mask_object.elm).triggerHandler('move', [bounding_rect, mask_object]);
                    $(mask_object.elm).triggerHandler('resize', [bounding_rect, mask_object]);
                }

                $(mask_object.elm).triggerHandler('rotate', [mask_data.data.rotation + target_rotation]);

            });

            $(object.elm).trigger('object-update');
        }

        _objectRemove(key) {
            const object = this._objects[key]
            if (!object) return

            this._objectUnselect(object);

            if (object.data.type === 'group') {
                this._objectRemoveChildsInGroup(object);
            }

            $(object.elm).trigger('object-remove');

            if (object.elm && object.elm.parentNode) {
                object.elm.parentNode.removeChild(object.elm);
            }

            delete this._objects[key];

            if (typeof this._callback.object_remove === 'function') {
                this._callback.object_remove(object);
            } else if (object.list_item) {
                object.list_item.remove();
            }

            //Nếu đang batch delete thì ko rerender và add history (optimize performance)
            if(this._is_batch_remove) return;

            //rerender object switcher khi xóa option
            this._renderObjectSwitcher()
            this._historyAdd();
        }

        _objectRemoveChildsInGroup(object) {
            const $this = this;
            const before_is_batch_remove = this._is_batch_remove;

            this._is_batch_remove = true;
            $(object.elm).find('> [data-object]').each(function () {
                $this._objectRemove(this.getAttribute('data-object'));
            });
            this._is_batch_remove = before_is_batch_remove;
        }

        _objectAppend(object, options, ignored_auto_select = false) {
            var $this = this;
            if (options === null || typeof options !== 'object') {
                options = {};
            }

            if (options.group && options.group === this._svg_group.object[0]) {
                options.group.appendChild(object.elm);
            } else if (options.group && options.group.nodeName && options.group.nodeName.toLowerCase() === 'g' && options.group.getAttribute('data-type') === 'group' && this._objectIsAncestor(options.group, this._svg_group.object[0])) {
                options.group.appendChild(object.elm);
            } else if (options.group && typeof options.group.elm !== 'undefined' && options.group.elm.nodeName.toLowerCase() === 'g' && this._objectIsAncestor(options.group.elm, this._svg_group.object[0])) {
                options.group.elm.appendChild(object.elm);
            } else if (options.before && options.before.nodeType === Node.ELEMENT_NODE && options.before.hasAttribute('data-object') && this._objectIsAncestor(options.before, this._svg_group.object[0])) {
                $(object.elm).insertBefore(options.before);
            } else if (options.before && typeof options.before.elm !== 'undefined' && this._objectIsAncestor(options.before.elm, this._svg_group.object[0])) {
                $(object.elm).insertBefore(options.before.elm);
            } else {
                var last_select_object = this._last_selected_object;

                if (last_select_object && this._objectIsAncestor(last_select_object.elm, this._svg_group.object[0])) {
                    if (last_select_object.data.type !== 'group' && !$(last_select_object.elm).closest('g[data-type="group"]')[0]) {
                        last_select_object = null;
                    }
                } else {
                    last_select_object = null;
                }

                if (last_select_object) {
                    if (last_select_object.data.type === 'group' && object.data.type !== 'group') {
                        last_select_object.elm.appendChild(object.elm);
                    } else {
                        $(object.elm).insertAfter(last_select_object.elm);
                    }
                } else {
                    this._svg_group.object.append(object.elm);
                }
            }

            this._objectAddThumb(object.data.key);

            function __processMask() {
                var mask = $this._svg_group.defs.find('clipPath#mask-' + object.data.key);

                if (mask[0]) {
                    mask.children().each(function () {
                        $(this).trigger("object-remove").unbind()
                    })
                    mask.empty();
                } else {
                    mask = $(document.createElementNS($this._svg[0].namespaceURI, 'clipPath'))
                        .attr('id', 'mask-' + object.data.key)
                        .appendTo($this._svg_group.defs);
                }

                const target_bbox = $this._elmGetBBox(object.elm);

                object.data.type_data.mask.forEach((mask_data) => {
                    const mask_object = $this._objectAdd(mask_data.type, mask_data.data, null, 4);

                    mask.append(mask_object.elm);
                    if (object.data.type !== 'image') {
                        const bbox = $this._elmGetBBox(mask_object.elm);
                        const tbox = mask_data.target_bbox;
                        // for (const key in mask_data.target_bbox) {
                        //     let n = mask_data.target_bbox[key];
                        //     tbox[key] = n * zoom_ratio
                        // }
                        const scale = {
                            x: target_bbox.width / tbox.width,
                            y: target_bbox.height / tbox.height
                        }
                        const translate = {
                            x: target_bbox.x - tbox.x,
                            y: target_bbox.y - tbox.y
                        }

                        bbox.x += translate.x;
                        bbox.y += translate.y;

                        bbox.x =  target_bbox.x + (bbox.x - target_bbox.x)*scale.x
                        bbox.y = target_bbox.y + (bbox.y - target_bbox.y)*scale.y

                        bbox.width *= scale.x;
                        bbox.height *= scale.y;

                        $(mask_object.elm).triggerHandler('move', [bbox, mask_object]);
                        $(mask_object.elm).triggerHandler('resize', [bbox, mask_object]);
                    }
                    mask_data.target_bbox = {
                        x: target_bbox.x,
                        y: target_bbox.y,
                        width: target_bbox.width,
                        height: target_bbox.height
                    }

                    const rotation = mask_data.data.rotation;
                    $(mask_object.elm).trigger("rotate", [rotation])
                    const bbox = $this._elmGetBBox(mask_object.elm);

                    mask_data.data.bbox = {
                        x: bbox.x,
                        y: bbox.y,
                        width: bbox.width,
                        height: bbox.height
                    }

                    // if(object.data.type === "image"){
                    $(mask_object.elm).trigger("object-remove").unbind()
                    // }

                    return mask_object
                });

                object.elm.setAttribute('clip-path', 'url(#mask-' + object.data.key + ')');
            }

            $(object.elm)
            .bind('object-select',  (e) => {
                e.stopPropagation();
                const thumb_content = $(object.list_item).find('> .thumb-content');

                thumb_content.attr('data-selected', 1);

                if(!this._is_append_data) {
                    $(object.list_item).parents('.thumb-item[data-type="group"]').addClass('toggled');

                    if(!isVisible(thumb_content[0], this._object_thumb_list[0])) {
                        const docViewTop = this._object_thumb_list.scrollTop();
                        const elemTop = thumb_content.offset().top - this._object_thumb_list.offset().top;

                        this._object_thumb_list.animate({
                            scrollTop: elemTop+ docViewTop - this._object_thumb_list.height() / 2
                        }, 500);
                    }
                }
            })
            .bind('object-clearHelper', function () {
                $(object.list_item).find('> .thumb-content').attr({
                    'data-selected': 0
                });
            }).bind('object-update', function () {
                if (Array.isArray(object.data.type_data.mask) && object.data.type_data.mask.length > 0) {
                    if (!$(object.list_item).find('> .thumb-content > .mask')[0]) {
                        $('<div />').addClass('mask').click(function () {
                            if ($this._objectIsLocked(object) || !$this._objectIsShowable(object)) {
                                return;
                            }

                            $this._objectSelect($this._objects[object.data.key]);
                            $this._objectAddMask();
                        }).append($.renderIcon('mask')).appendTo($(object.list_item).find('> .thumb-content'));
                    }

                    if (!object.mask) {
                        __processMask();
                    }
                } else {
                    $(object.list_item).find('> .thumb-content > .mask').remove();

                    object.elm.removeAttribute('clip-path');
                    $this._svg_group.defs.find('clipPath#mask-' + object.data.key).remove();
                }

                var list_item = $(object.list_item);

                list_item.find('> .thumb-content').attr({
                    'data-showable': object.data.showable ? 1 : 0,
                    'data-locked': object.data.locked ? 1 : 0,
                    'data-selected': object.selected ? 1 : 0
                });
                list_item.find('> .thumb-list').attr({
                    'data-locked': object.data.locked ? 1 : 0,
                })

                $(object.elm)[object.data.showable ? 'show' : 'hide']();

                if (object.selected && (!$this._objectIsShowable(object) || $this._objectIsLocked(object))) {
                    $this._objectUnselectCurrent();
                }
            })
            .bind('not_render_update', function() {
                let list_item = $(object.list_item);

                list_item.find('> .thumb-content').attr({
                    'data-not-render': object.data.is_not_render_design ? 1 : 0
                });

                list_item.find('> .thumb-list').attr({
                    'data-not-render': object.data.is_not_render_design ? 1 : 0,
                });

                $this._objectIsNotRender(object);

            })
            .bind('move resize', function () {
                if (Array.isArray(object.data.type_data.mask) && object.data.type_data.mask.length > 0 && !object.mask) {
                    __processMask();
                }
            }).bind('personalized-update', function () {
                if (object.data.personalized === null || typeof object.data.personalized !== 'object' || typeof object.data.personalized.type === 'undefined') {
                    delete object.data.personalized;
                }

                $(object.list_item).find('> .thumb-content').attr('data-personalized', object.data.personalized ? 1 : 0);
                if (object.data.personalized?.type === 'tab') {
                    $(object.list_item).find('> .thumb-content').attr('type-tab', 1);
                    $(object.list_item).find('> .thumb-content > .type').html($.renderIcon('tabs-group'));
                }
                if(object.data.personalized?.type === "spotify"){
                    $this._appendSpotifyToRect(object);
                }
            }).trigger('object-update').trigger('personalized-update').trigger('not_render_update');

            // $this._objectSelect(object);
            if(!ignored_auto_select)  {
                $this._objectSelect(object);
            } else {
                const bounding_rect = this._elmGetBBox(object.elm);
                // $(object.elm).trigger('select')
                $(object.elm).triggerHandler('move', [bounding_rect]);
                $(object.elm).triggerHandler('resize', [bounding_rect]);
                $(object.elm).triggerHandler('rotate', [object.data.type_data.rotation]);
                // $(object.elm).trigger('object-clearHelper')
            }
        }

        _addToImageData(img_data) {
            let id = this._links_map[img_data.url];
            if(!id) {
                id = getUniqId()
                this._image_data[id] = img_data;
                this._links_map[img_data.url] = id;
            }
            return id;
        }

        _objectConfigRender_flip(object) {
            $('<div />').addClass('seperate').appendTo(this._config_panel);

            $($.renderIcon('flip-vertical')).appendTo(this._config_panel).attr('class', 'button').click(function () {
                $(object.elm).trigger('flip-vertical');
            });

            $($.renderIcon('flip-horizontal')).appendTo(this._config_panel).attr('class', 'button').click(function () {
                $(object.elm).trigger('flip-horizontal');
            });
        }

        _objectConfigRender_rotation(object) {
            $('<div />').addClass('seperate').appendTo(this._config_panel);

            var rotation = object.data.type_data.rotation;

            if (rotation < 0) {
                rotation += 360;
            }

            $('<input />').attr({type: 'text'}).width(50).val(rotation).bind('blur keydown', function (e) {
                if (e.keyCode === 38) {
                    this.value = parseFloat(this.value) + (e.shiftKey ? 5 : 1);
                } else if (e.keyCode === 40) {
                    this.value = parseFloat(this.value) - (e.shiftKey ? 5 : 1);
                } else if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                var rotation = parseFloat(this.value) % 360;

                if (rotation < 0) {
                    rotation += 360;
                }

                this.value = rotation;

                $(object.elm).triggerHandler('rotate', [rotation]);
            }).appendTo(this._config_panel);
        }

        _objectConfigRender_copyPasteAttributes(object){
            $('<div />').addClass('seperate').appendTo(this._config_panel);

            $($.renderIcon('copy')).appendTo(this._config_panel).attr('class', 'button').click((e) =>  {
                this._copyObjectAttributes();
                e.preventDefault();
            });

            if(object.data.type !== "group") {
                const pasteBtn = $('<div />').append($($.renderIcon('paste')).attr('class', 'button')).click((e) => {
                    this._pasteObjectAttributes();
                    e.preventDefault();
                });
                let copiedData;
                const __getCopiedData = () => {
                    copiedData = null;
                    try {
                        copiedData = localStorage.getItem(PERSONALISED_ATTRIBUTES_CLIPBOARD);
                        copiedData = JSON.parse(copiedData)
                    } catch (error) {
                        copiedData = null;
                    }
                }
                __getCopiedData()
                if(copiedData && copiedData.key) pasteBtn.addClass("button-paste");

                pasteBtn.mouseover(() => {
                    if(copiedData && copiedData.key)
                        this._objectHighlightRender(copiedData.key);
                }).mouseleave(() => {
                    this._objectHighlightRemove();
                })

                pasteBtn.appendTo(this._config_panel)
                $(object.elm).unbind('copied-object pasted-object')
                    .bind("copied-object", () => {
                        pasteBtn.addClass('button-paste')
                        __getCopiedData();
                    }).bind(('pasted-object'),() => {
                        pasteBtn.removeClass('button-paste');
                        copiedData = null;
                    })
            }
        }

        _objectConfigRender_coordinate(object) {
            var $this = this;

            var bounding_rect = this._elmGetBBox(object.elm);

            $('<div />').addClass('seperate').appendTo(this._config_panel);

            $('<div />').text('X').appendTo(this._config_panel);

            $('<input />').attr({type: 'text'}).width(50).val(bounding_rect.x).bind('blur keydown', function (e) {
                if (e.keyCode === 38) {
                    this.value = parseFloat(this.value) + (e.shiftKey ? 10 : 1);
                } else if (e.keyCode === 40) {
                    this.value = parseFloat(this.value) - (e.shiftKey ? 10 : 1);
                } else if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                var bounding_rect = $this._elmGetBBox(object.elm);

                bounding_rect.x = parseFloat(this.value);

                $(object.elm).triggerHandler('move', [bounding_rect]);
            }).appendTo(this._config_panel);

            $('<div />').text('Y').appendTo(this._config_panel);

            $('<input />').attr({type: 'text'}).width(50).val(bounding_rect.y).bind('blur keydown', function (e) {
                if (e.keyCode === 38) {
                    this.value = parseFloat(this.value) + (e.shiftKey ? 10 : 1);
                } else if (e.keyCode === 40) {
                    this.value = parseFloat(this.value) - (e.shiftKey ? 10 : 1);
                } else if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                var bounding_rect = $this._elmGetBBox(object.elm);

                bounding_rect.y = parseFloat(this.value);

                $(object.elm).triggerHandler('move', [bounding_rect]);
            }).appendTo(this._config_panel);
        }

        _objectConfigRender_outline(object) {
            $('<div />').addClass('seperate').appendTo(this._config_panel);

            $('<div />').text('Outline').appendTo(this._config_panel);

            var outline_color = $('<div />').addClass('color-selector stroke').appendTo(this._config_panel).osc_colorPicker({
                swatch_name: 'svg_editor',
                callback: function (color) {
                    if (color) {
                        outline_color.removeClass('no-color').css('background-color', color);

                        var width = parseFloat(outline_width.val());

                        if (isNaN(width)) {
                            width = 0;
                        } else {
                            width = Math.abs(width);
                        }

                        if (width === 0) {
                            delete object.data.type_data.outline;
                        } else {
                            object.data.type_data.outline = {
                                width: width,
                                color: color
                            };
                        }
                    } else {
                        outline_color.addClass('no-color');
                        delete object.data.type_data.outline;
                    }

                    $(object.elm).trigger('object-update', [{color: color ? color : 'none'}]);
                }
            });

            var outline_width = $('<input />').attr({type: 'text'}).width(40).bind('blur keydown', function (e) {
                if (e.keyCode === 38) {
                    this.value = parseFloat(this.value) + (e.shiftKey ? 10 : 1);
                } else if (e.keyCode === 40) {
                    this.value = parseFloat(this.value) - (e.shiftKey ? 10 : 1);
                } else if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                var width = parseFloat(this.value);

                if (isNaN(width)) {
                    width = 0;
                } else {
                    width = Math.abs(width);
                }

                this.value = width;

                if (width === 0) {
                    delete object.data.type_data.outline;
                } else {
                    var color = outline_color.hasClass('no-color') ? 'none' : outline_color.css('background-color');

                    if (color === 'none') {
                        delete object.data.type_data.outline;
                    } else {
                        object.data.type_data.outline = {
                            width: width,
                            color: color
                        };
                    }
                }

                $(object.elm).trigger('object-update');
            }).appendTo(this._config_panel);

            if (typeof object.data.type_data.outline !== 'undefined') {
                outline_width.val(object.data.type_data.outline.width);

                if (object.data.type_data.outline.color === 'none') {
                    outline_color.addClass('no-color');
                } else {
                    outline_color.removeClass('no-color').css('background-color', object.data.type_data.outline.color);
                }
            } else {
                outline_width.val(0);
                outline_color.addClass('no-color');
            }
        }

        _objectConfigRender_dimension(object) {
            var $this = this;

            var bounding_rect = this._elmGetBBox(object.elm);

            $('<div />').addClass('seperate').appendTo(this._config_panel);

            $('<div />').text('W').appendTo(this._config_panel);

            var link_mode = true;

            const triggerResize = (bounding_rect) => {
                const type = object.data.type

                if(type === "group") {
                    $(object.elm).triggerHandler('transform-begin')
                    const bbox = $this._elmGetBBox(object.elm)
                    bounding_rect.scale  = {
                        x: bounding_rect.width / bbox.width,
                        y: bounding_rect.height / bbox.height
                    }
                }

                $(object.elm).triggerHandler('resize', [bounding_rect])

                if(type === "group") {
                    $(object.elm).triggerHandler('transform-end')
                }
            }
            const widthInput = $('<input />').attr({type: 'text'}).width(50).val(bounding_rect.width).bind('blur keydown', function (e) {
                if (e.keyCode === 38) {
                    this.value = parseFloat(this.value) + (e.shiftKey ? 10 : 1);
                } else if (e.keyCode === 40) {
                    this.value = parseFloat(this.value) - (e.shiftKey ? 10 : 1);
                } else if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                var bounding_rect = $this._elmGetBBox(object.elm);

                var new_width = parseFloat(this.value);

                if (new_width <= 0) {
                    console.log('Error: width is less than or equal 0');
                    return;
                }

                if (link_mode) {
                    bounding_rect.height *= new_width / bounding_rect.width;
                }

                bounding_rect.width = new_width;
                triggerResize(bounding_rect)
            }).appendTo(this._config_panel);

            $('<div />').append($.renderIcon('link')).click(function () {
                link_mode = !link_mode;
                $(this).html('').append($.renderIcon(link_mode ? 'link' : 'unlink'));
            }).appendTo(this._config_panel);

            $('<div />').text('H').appendTo(this._config_panel);

            const heightInput = $('<input />').attr({type: 'text'}).width(50).val(bounding_rect.height).bind('blur keydown', function (e) {
                if (e.keyCode === 38) {
                    this.value = parseFloat(this.value) + (e.shiftKey ? 10 : 1);
                } else if (e.keyCode === 40) {
                    this.value = parseFloat(this.value) - (e.shiftKey ? 10 : 1);
                } else if (typeof e.keyCode !== 'undefined' && e.keyCode !== 13) {
                    return;
                }

                var bounding_rect = $this._elmGetBBox(object.elm);

                var new_height = parseFloat(this.value);

                if (new_height <= 0) {
                    console.log('Error: height is less than or equal 0');
                    return;
                }

                if (link_mode) {
                    bounding_rect.width *= new_height / bounding_rect.height;
                }

                bounding_rect.height = new_height;
                triggerResize(bounding_rect)
            }).appendTo(this._config_panel);
            const eventResize = "resize.input-dimension"
            $(object.elm).unbind(eventResize).bind(eventResize, function(e, bounding_rect) {
                if(bounding_rect.width) {
                    widthInput.val(bounding_rect.width)
                }
                if(bounding_rect.height) {
                    heightInput.val(bounding_rect.height)
                }
            })
        }

        _objectHighlightRender(key) {
            this._objectHighlightRemove()

            if(!key) return;

            const object = this._objects[key];

            if(!object) return;

            const bbox = this._elmGetBBox(object.elm);

            const rect = document.createElementNS(this._svg[0].namespaceURI, 'rect');
            this.highlightHelper = $(rect).attr({
                x: bbox.x,
                y: bbox.y,
                width: bbox.width,
                height: bbox.height,
                'store-width': 3,
                "style": 'fill:none;stroke:#6fc055;'
            }).appendTo(this._svg)
        }

        _objectHighlightRemove(){
            if(!this.highlightHelper) return;
            this.highlightHelper.remove();
        }

        _objectLineHelperRender(boundingBox, objectPositions) {
            if(!this._is_show_line_helper) return;
            const zoom_ratio = this._zoom_ratio;
            const lineHelper = this._svg_group.lineHelper;
            lineHelper.empty()

            const {x, y, width, height } = boundingBox;

            const bottomRight = {
                x: x + width,
                y: y + height
            }
            const horizontal = [x + width/ 2, x, bottomRight.x]
            const vertical = [y + height/ 2, y, bottomRight.y]

            const __drawLine = (line, color) => {
                const lineElm = document.createElementNS(this._svg[0].namespaceURI, 'line')
                for (const key in line) {
                    lineElm.setAttribute(key, line[key])
                }
                lineElm.setAttribute("style",`stroke:${color};stroke-width:${1 / zoom_ratio}`)
                // lineElm.setAttribute("stroke-dasharray", "4 2")
                lineHelper.append(lineElm)
            }

            const lineColors = {
                "object": '#0075f3',
                "background": '#ffc107'
            }
            const __checkPositions = (n, arr) => {
                const maxDiff = 1.2 / zoom_ratio;
                for (const number of arr) {
                    if(Math.abs(n - number) <= maxDiff)
                        return number
                }
                return null
            }
            objectPositions.forEach(pos => {
                for (const xn of horizontal) {
                    const match = __checkPositions(xn, pos.horizontal)
                    if(match !== null) {
                        let y1, y2;
                        if(pos.type === "background" || pos.topLeft.y < y) {
                            y1 = pos.topLeft.y
                        } else {
                            y1 = y
                        }

                        if(pos.type === "background" || bottomRight.y < pos.bottomRight.y) {
                            y2 = pos.bottomRight.y
                        } else {
                            y2 = bottomRight.y
                        }
                        const line =  {
                            x1: match , y1,
                            x2: match , y2,
                        }
                        __drawLine(line, lineColors[pos.type])
                        break;
                    }
                }

                for (const yn of vertical) {
                    const match = __checkPositions(yn, pos.vertical)
                    if(match !== null) {
                        let x1, x2;
                        if(pos.type == "background" || pos.topLeft.x < x) {
                            x1 = pos.topLeft.x
                        } else {
                            x1 = x
                        }

                        if(pos.type === "background" || bottomRight.x < pos.bottomRight.x) {
                            x2 = pos.bottomRight.x
                        } else {
                            x2 = bottomRight.x
                        }
                        const line =  {
                            x1 , y1: match,
                            x2 , y2: match,
                        }
                        __drawLine(line, lineColors[pos.type])
                        break;
                    }
                }
            })
        }

        _objectsGetCurrentPosition(object) {
            const positions = [];
            if(!object || !object.data ) return positions;

            const objectData = object.data
            const selectedKey = objectData.key
            const ignoredKeys = {
                [selectedKey]: 1
            }
            if(objectData.type === "group" && objectData.type_data.children && Array.isArray(objectData.type_data.children)) {
                objectData.type_data.children.forEach(childKey => {
                    ignoredKeys[childKey] = 1;
                })
            }
            const objects = this._objects;

            const __calculatePosition = (elm) => {
                const bbox = this._elmGetBBox(elm);
                const topLeft = {
                    x: bbox.x,
                    y: bbox.y
                }
                const pivot = {
                    x: bbox.x + bbox.width/2,
                    y: bbox.y + bbox.height/2
                }
                const bottomRight = {
                    x: bbox.x + bbox.width,
                    y: bbox.y + bbox.height
                }
                return {
                    topLeft,
                    bottomRight,
                    horizontal: [pivot.x,topLeft.x, bottomRight.x],
                    vertical: [pivot.y,topLeft.y, bottomRight.y],
                }
            }

            if(this.background[0]){
                const positionBg = __calculatePosition(this.background[0]);
                positionBg.type = "background"
                positions.push(positionBg)
            }

            for (const key in objects) {
                const object = objects[key];
                if(ignoredKeys[key] === 1 || !object.elm) continue

                const position = __calculatePosition(object.elm);
                position.type = "object";
                positions.push(position)
            }
            return positions;
        }

        _objectLineHelperClear(){
            const lineHelper = this._svg_group.lineHelper;
            if(lineHelper)
                lineHelper.empty()
        }

        _objectHelperRender(object) {
            var $this = this;

            if (!object.helper) {
                return;
            }

            var bounding = document.createElementNS($this._svg[0].namespaceURI, 'rect');
            const objectKey = object.data.key;
            const helperKey = '.helper-' + objectKey;

            const canEdit = this._permissions.edit_layer;

            bounding.setAttribute('class', 'bounding-rect');
            const ratio = this._zoom_ratio;

            bounding.setAttribute('stroke-width', 0.5 / ratio);
            bounding.setAttribute('stroke-dasharray', new Array(3).fill(5/ratio).join(','));
            object.helper.appendChild(bounding);

            $(object.elm).bind('move' + helperKey, function (e, bounding_rect) {
                e.stopPropagation();
                if(!object.helper) return

                const rotation = $this._elmGetTransform(object.helper).rotation;

                if (rotation !== 0) {
                    $this._elmSetTransform(object.helper, 0, 0, 0);
                }

                bounding.setAttribute('x', bounding_rect.x);
                bounding.setAttribute('y', bounding_rect.y);

                if (rotation !== 0) {
                    $this._elmSetTransform(object.helper, 0, 0, rotation);
                }
            }).bind('resize' + helperKey, function (e, bounding_rect) {
                e.stopPropagation();

                if(!object.helper) return

                const rotation = $this._elmGetTransform(object.helper).rotation;

                if (rotation !== 0) {
                    $this._elmSetTransform(object.helper, 0, 0, 0);
                }

                bounding.setAttribute('width', bounding_rect.width);
                bounding.setAttribute('height', bounding_rect.height);

                if (rotation !== 0) {
                    $this._elmSetTransform(object.helper, 0, 0, rotation);
                }
            }).bind('rotate' + helperKey, function (e, rotate) {
                e.stopPropagation();

                if (object.helper && object.helper.parentNode) {
                    $this._elmSetTransform(object.helper, 0, 0, rotate);
                }
            });

            const objectPositions = $this._objectsGetCurrentPosition(object)

            $(bounding).contextmenu(function(e) {
                e.preventDefault()
            }).bind('mousedown', function (e) {
                if (!canEdit) return;

                var bounding_rect = $this._elmGetBBox(this);

                var point = $this._cursorPoint(e);

                point.x -= bounding_rect.x;
                point.y -= bounding_rect.y;

                $this._objectLineHelperRender(bounding_rect, objectPositions)

                $this._svg.bind('mousemove' + helperKey, function (e) {
                    var cursor_point = $this._cursorPoint(e);

                    bounding_rect.x = cursor_point.x - point.x;
                    bounding_rect.y = cursor_point.y - point.y;
                    bounding_rect.rotation = $this._elmGetTransform(object.helper).rotation;

                    $this._objectLineHelperRender(bounding_rect, objectPositions)

                    $(object.elm).triggerHandler('move', [bounding_rect]);
                });

                $(document).bind('mouseup' + helperKey, function () {
                    $(document).unbind('mouseup' + helperKey);
                    $this._svg.unbind('mousemove' + helperKey);

                    $this._objectLineHelperClear()

                    if (object.data.type === 'group') {
                        $(object.elm).trigger('transform-end');
                        $(object.elm).trigger('transform-begin');
                    }

                    $this._historyAdd();
                });
            }).dblclick(function (e) {
                e.preventDefault()
                $this._objectEdit(object.data.key);
            });

            var __resize = function (e, handler, anchor, original_bounding_rect) {
                var cursor_point = $this._getPointExceptRotation($this._cursorPoint(e), original_bounding_rect, $this._elmGetTransform(object.helper).rotation);

                var new_width = 0;
                var new_height = 0;

                var object_size = $this._elmGetBBox(object.elm);

                if (handler.match(/W/)) {
                    new_width = original_bounding_rect.width + (anchor.x - cursor_point.x);
                } else if (handler.match(/E/)) {
                    new_width = original_bounding_rect.width + (cursor_point.x - anchor.x);
                } else {
                    new_width = object_size.width;
                }

                //Nếu giữ shift hoặc resize theo group thì scale theo tỉ lệ
                if ((e.shiftKey && handler.length === 2) || object.data.type === 'group' || object.data.personalized?.type === 'spotify') {
                    new_height = original_bounding_rect.height * (new_width / original_bounding_rect.width);
                } else if (handler.match(/N/)) {
                    new_height = original_bounding_rect.height + (anchor.y - cursor_point.y);
                } else if (handler.match(/S/)) {
                    new_height = original_bounding_rect.height + (cursor_point.y - anchor.y);
                } else {
                    new_height = object_size.height;
                }

                if (new_width <= 0) {
                    new_width = 1;
                }

                if (new_height <= 0) {
                    new_height = 1;
                }

                if (
                    object.data.type === 'text' &&
                    object.data.type_data?.style?.min_width &&
                    new_width < object.data.type_data?.style?.min_width
                ) {
                    new_width = object.data.type_data.style.min_width;
                }

                if (
                    object.data.type === 'text' &&
                    object.data.type_data?.style?.min_height &&
                    new_height < object.data.type_data?.style?.min_height
                ) {
                    new_height = object.data.type_data.style.min_height;
                }

                if ((e.shiftKey && handler.length > 1) || object.data.type === 'group' || object.data.personalized?.type === 'spotify') {
                    var ratio = original_bounding_rect.width / original_bounding_rect.height;

                    if (new_width / new_height > ratio) {
                        new_width = new_height * ratio;
                    } else {
                        new_height = new_width / ratio;
                    }
                }

                var degress = $this._elmGetTransform(object.helper).rotation;

                var bounding_pt = $this._svg[0].createSVGPoint();

                bounding_pt.x = original_bounding_rect.x;
                bounding_pt.y = original_bounding_rect.y;

                bounding_pt = $this._getPointApplyRotation(bounding_pt, original_bounding_rect, degress);

                var radian = degress * Math.PI / 180;

                if (handler.match(/W/)) {
                    bounding_pt.x = bounding_pt.x + (original_bounding_rect.width - new_width) * Math.cos(radian);
                    bounding_pt.y = bounding_pt.y + (original_bounding_rect.width - new_width) * Math.sin(radian);
                }

                if (handler.match(/N/)) {
                    bounding_pt.x = bounding_pt.x + (original_bounding_rect.height - new_height) * Math.cos((degress + 90) * Math.PI / 180);
                    bounding_pt.y = bounding_pt.y + (original_bounding_rect.height - new_height) * Math.sin((degress + 90) * Math.PI / 180);
                }

                var vector1 = {
                    point1: bounding_pt,
                    point2: $this._svg[0].createSVGPoint()
                };

                var vector2 = {
                    point1: $this._svg[0].createSVGPoint(),
                    point2: $this._svg[0].createSVGPoint()
                };

                vector2.point1.x = vector1.point1.x + new_height * Math.cos((degress + 90) * Math.PI / 180);
                vector2.point1.y = vector1.point1.y + new_height * Math.sin((degress + 90) * Math.PI / 180);

                vector1.point2.x = vector2.point1.x + new_width * Math.cos(radian);
                vector1.point2.y = vector2.point1.y + new_width * Math.sin(radian);

                vector2.point2.x = vector1.point1.x + new_width * Math.cos(radian);
                vector2.point2.y = vector1.point1.y + new_width * Math.sin(radian);

                var intersect = $this._getVectorIntersectionPoint(vector1, vector2);

                var center_pt = $this._svg[0].createSVGPoint();

                center_pt.x = intersect.x;
                center_pt.y = intersect.y;

                degress = (Math.atan2(bounding_pt.y - center_pt.y, bounding_pt.x - center_pt.x) * 180 / Math.PI) - degress;

                var distance = $this._pointGetDistance(center_pt, bounding_pt);

                var radian = degress * Math.PI / 180;

                bounding_pt.x = center_pt.x + distance * Math.cos(radian);
                bounding_pt.y = center_pt.y + distance * Math.sin(radian);

                var bounding_rect = {
                    x: bounding_pt.x,
                    y: bounding_pt.y,
                    width: new_width,
                    height: new_height,
                    rotation: $this._elmGetTransform(object.helper).rotation,
                    scale: {
                        x: new_width/original_bounding_rect.width,
                        y: new_height/original_bounding_rect.height
                    }
                };
                $this._objectLineHelperRender(bounding_rect, objectPositions)

                $(object.elm).triggerHandler('resize', [bounding_rect]);
                $(object.elm).triggerHandler('move', [bounding_rect]);
            };

            var __rotate = function (e, handler, original_degress, center_pt) {
                var pt = $this._cursorPoint(e);

                if (e.shiftKey) {
                    var degress = Math.round(Math.atan2(pt.y - center_pt.y, pt.x - center_pt.x) / Math.PI * 4) / 4 * Math.PI;
                } else {
                    var degress = Math.atan2(pt.y - center_pt.y, pt.x - center_pt.x) * (180 / Math.PI);
                }

                degress -= original_degress;

                $this._elmSetTransform(object.helper, 0, 0, degress);
                //$this._elmGetTransform(object.helper).rotation
                $(object.elm).triggerHandler('rotate', [degress]);
            };


            if (canEdit) {
                [
                    [
                        'NW',
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.x - rect_bbox.width + 5;
                        },
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.y - rect_bbox.height + 5;
                        }
                    ],
                    [
                        'NE',
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.x + bounding_rect.width - 5;
                        },
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.y - rect_bbox.height + 5;
                        }
                    ],
                    [
                        'SE',
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.x + bounding_rect.width - 5;
                        },
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.y + bounding_rect.height - 5;
                        }
                    ],
                    [
                        'SW',
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.x - rect_bbox.width + 5;
                        },
                        function (bounding_rect, rect_bbox) {
                            return bounding_rect.y + bounding_rect.height - 5;
                        }
                    ]
                ].forEach(function (point) {
                    var rect = document.createElementNS($this._svg[0].namespaceURI, 'rect');
                    const size = 20 / ratio;
                    rect.setAttribute('width', size);
                    rect.setAttribute('height', size);
                    rect.setAttribute('class', 'rotate-handler rotate-handler__' + point[0]);
                    rect.setAttribute('data-handler', point[0]);

                    object.helper.appendChild(rect);

                    $(object.elm).bind(`move${helperKey} resize${helperKey}`, function (e, bounding_rect) {
                        var rect_bbox = $this._elmGetBBox(rect);

                        rect.setAttribute('x', point[1](bounding_rect, rect_bbox));
                        rect.setAttribute('y', point[2](bounding_rect, rect_bbox));
                    });

                    $(rect).mousedown(function (e) {
                        var anchor = $this._cursorPoint(e);
                        var original_bounding_rect = $this._elmGetBBox(bounding);

                        var center_pt = $this._svg[0].createSVGPoint();

                        center_pt.x = original_bounding_rect.x + original_bounding_rect.width / 2;
                        center_pt.y = original_bounding_rect.y + original_bounding_rect.height / 2;

                        var original_degress = Math.atan2(anchor.y - center_pt.y, anchor.x - center_pt.x) * (180 / Math.PI);
                        // let group_rotation = 0;
                        if (typeof object.data.type_data.rotation !== 'undefined') {
                            original_degress -= object.data.type_data.rotation;
                        }

                        $(document).bind('mousemove' + helperKey, function (e) {
                            __rotate(e, point[0], original_degress, center_pt);
                        }).bind('mouseup' + helperKey, function (e) {
                            $(document).unbind('mousemove' + helperKey);
                            $(document).unbind('mouseup' + helperKey);

                            if (object.data.type === 'group') {
                                $(object.elm).trigger('transform-end');
                                $(object.elm).trigger('transform-begin');
                            }
                            $this._historyAdd();
                        });
                    });
                });

                [
                    [
                        'NW',
                        function (bounding_rect) {
                            return bounding_rect.x;
                        },
                        function (bounding_rect) {
                            return bounding_rect.y;
                        }
                    ],
                    [
                        'N',
                        function (bounding_rect) {
                            return bounding_rect.x + (bounding_rect.width / 2);
                        },
                        function (bounding_rect) {
                            return bounding_rect.y;
                        }
                    ],
                    [
                        'NE',
                        function (bounding_rect) {
                            return bounding_rect.x + bounding_rect.width;
                        },
                        function (bounding_rect) {
                            return bounding_rect.y;
                        }
                    ],
                    [
                        'E',
                        function (bounding_rect) {
                            return bounding_rect.x + bounding_rect.width;
                        },
                        function (bounding_rect) {
                            return bounding_rect.y + (bounding_rect.height / 2);
                        }
                    ],
                    [
                        'SE',
                        function (bounding_rect) {
                            return bounding_rect.x + bounding_rect.width;
                        },
                        function (bounding_rect) {
                            return bounding_rect.y + bounding_rect.height;
                        }
                    ],
                    [
                        'S',
                        function (bounding_rect) {
                            return bounding_rect.x + (bounding_rect.width / 2);
                        },
                        function (bounding_rect) {
                            return bounding_rect.y + bounding_rect.height;
                        }
                    ],
                    [
                        'SW',
                        function (bounding_rect) {
                            return bounding_rect.x;
                        },
                        function (bounding_rect) {
                            return bounding_rect.y + bounding_rect.height;
                        }
                    ],
                    [
                        'W',
                        function (bounding_rect) {
                            return bounding_rect.x;
                        },
                        function (bounding_rect) {
                            return bounding_rect.y + (bounding_rect.height / 2);
                        }
                    ]
                ].forEach(function (point) {
                    var rect = document.createElementNS($this._svg[0].namespaceURI, 'rect');
                    const size = 10 / ratio;
                    rect.setAttribute('width', size);
                    rect.setAttribute('height', size);
                    rect.setAttribute('stroke-width', 1 / ratio)
                    rect.setAttribute('class', 'resize-handler resize-handler__' + point[0]);
                    rect.setAttribute('data-handler', point[0]);

                    object.helper.appendChild(rect);

                    $(object.elm).bind(`move${helperKey} resize${helperKey}`, function (e, bounding_rect) {
                        rect.setAttribute('x', point[1](bounding_rect) - size / 2);
                        rect.setAttribute('y', point[2](bounding_rect) - size / 2);
                    });

                    $(rect).mousedown(function (e) {
                        var original_bounding_rect = $this._elmGetBBox(bounding);
                        var anchor = $this._getPointExceptRotation($this._cursorPoint(e), original_bounding_rect, $this._elmGetTransform(object.helper).rotation);

                        $(document).bind('mousemove'+ helperKey, function (e) {
                            __resize(e, point[0], anchor, original_bounding_rect);
                        }).bind('mouseup'+ helperKey, function (e) {
                            $(document).unbind('mousemove'+ helperKey);
                            $(document).unbind('mouseup'+ helperKey);

                            if (object.data.type === 'group') {
                                $(object.elm).trigger('transform-end');
                                $(object.elm).trigger('transform-begin');
                            }
                            $this._objectLineHelperClear()
                            $this._historyAdd();
                        });
                    });
                });
            }

            $(document).unbind('keydown' + helperKey).bind('keydown' + helperKey, function (e) {
                if ((e.target.nodeName && ['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) >= 0)
                || e.target.className.includes("svg-editor-text-helper")) {
                    return;
                }

                var bounding_rect = $this._elmGetBBox(bounding);

                let value = (e.shiftKey ? 10 : 1) / $this._zoom_ratio;
                if (value < 1) value = 1;

                if (e.key === 'ArrowUp') {
                    bounding_rect.y -= value;
                } else if (e.key === 'ArrowDown') {
                    bounding_rect.y += value;
                } else if (e.key === 'ArrowLeft') {
                    bounding_rect.x -= value;
                } else if (e.key === 'ArrowRight') {
                    bounding_rect.x += value;
                } else if ($this._permissions.remove_layer && (e.key === 'Delete' || e.key === 'Backspace') ) {
                    $this._objectRemove(object.data.key);
                    return;
                } else if (e.key === 'Tab') {
                    e.preventDefault();

                    var collection = $this._object_thumb_list.find('.thumb-item');

                    if (!e.shiftKey) {
                        collection = $(collection.get().reverse());
                    }

                    var matched = false;

                    collection.each(function () {
                        if (matched) {
                            var _object = $this._objects[this.getAttribute('data-object')];

                            if (_object.data.type !== 'group' && !$this._objectIsLocked(_object) && $this._objectIsShowable(_object)) {
                                matched = _object;
                                return false;
                            }

                            return;
                        }

                        if (this.getAttribute('data-object') === object.data.key) {
                            matched = true;
                        }
                    });

                    if (typeof matched !== 'object') {
                        return;
                    }

                    $this._objectSelect(matched);

                    return;
                } else if ((e.key === 'c' || e.keyCode === 67) && e.ctrlKey && e.altKey) {
                    if ($this._objectIsAncestor(object.elm, $this._svg_group.object[0])) {
                        $this._copyObject(object);
                    }

                    return;
                } else {
                    return;
                }

                e.preventDefault();

                if(canEdit) {
                    $(object.elm).triggerHandler('move', [bounding_rect]);

                    if (object.data.type === 'group') {
                        $(object.elm).trigger('transform-end');
                        $(object.elm).trigger('transform-begin');
                    }

                    $this._historyAdd();
                }
            });

            $(object.elm).bind('object-clearHelper' + helperKey, function () {
                $(object.elm).unbind(helperKey);
                $(document).unbind(helperKey);
                $this._svg.unbind(helperKey);
            });

            var bounding_rect = this._elmGetBBox(object.elm);

            $(object.elm).triggerHandler('move', [bounding_rect]);
            $(object.elm).triggerHandler('resize', [bounding_rect]);
            $(object.elm).triggerHandler('rotate', [object.data.type_data.rotation]);
        }

        _objectIsAncestor(descentor, ancestor) {
            return (ancestor.compareDocumentPosition(descentor) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY;
        }

        _objectIsLocked(object) {
            if (this._is_append_data) {
                return false;
            }

            if (object.data.locked) {
                return true;
            }

            var node = $(object.list_item).parent().closest('.thumb-item');

            while (node[0]) {
                if (node.find('> .thumb-content').attr('data-locked') === '1') {
                    return true;
                }

                node = node.parent().closest('.thumb-item');
            }

            return false;
        }

        _objectIsNotRender(object) {
            const dataIconDotName = $($.renderIcon('not-render-dotted')).attr('data-icon');

            const hasAtleaseChildNotRenderDotted = (children) => {
                let _issets = false;
                children.children().each((index, child) => {
                    const thumbContentElem = $(child).children('.thumb-content:first-child');
                    if (
                        thumbContentElem.attr('data-not-render') == 1
                        || thumbContentElem.find(' > div.not-render > svg').attr('data-icon') === dataIconDotName
                    ) {
                        _issets = true;
                        return;
                    }
                });

                return _issets;
            }

            let node = $(object.list_item).parent().closest('.thumb-item');

            if (object.data.is_not_render_design) {

                while (node[0]) {
                    let _parent = node.find('> .thumb-content[data-not-render="0"]');
                    if (_parent) {
                        _parent.find('> .not-render').html($.renderIcon('not-render-dotted'));
                    }

                    node = node.parent().closest('.thumb-item');
                }
            } else {
                // check children
                hasAtleaseChildNotRenderDotted($(object.list_item).find('> .thumb-list'))
                && $(object.list_item).find('> .thumb-list').parent().find('> .thumb-content').find('> .not-render').html($.renderIcon('not-render-dotted'));

                // check parent
                while (node[0]) {
                    const _childrenList = node.find('> .thumb-list');
                    const _dataNotRender = node.find('> .thumb-content:first-child').attr('data-not-render') == 1;

                    if (!_dataNotRender) {
                        _childrenList.parent().find('> .thumb-content').find('> .not-render').html(
                            hasAtleaseChildNotRenderDotted(_childrenList)
                            ? $.renderIcon('not-render-dotted')
                            : $.renderIcon('not-render')
                        );
                    }
                    node = node.parent().closest('.thumb-item');
                }
            }

            return false;
        }

        _objectIsShowable(object) {
            if (!object.data.showable) {
                return false;
            }

            var node = $(object.list_item).parent().closest('.thumb-item');

            while (node[0]) {
                if (node.find('> .thumb-content').attr('data-showable') === '0') {
                    return false;
                }

                node = node.parent().closest('.thumb-item');
            }

            return true;
        }

        _objectSelect(object) {
            this._objectUnselectCurrent();

            this._svg_group.helper.append(object.helper);

            if (this._objectIsAncestor(object.elm, this._svg_group.object[0])) {
                if (this._objectIsLocked(object) || !this._objectIsShowable(object)) {
                    return;
                }

                if (typeof this._callback.finish_action === 'function') {
                    this._callback.finish_action();
                    delete this._callback.finish_action;
                }
            }

            object.selected = true;
            object.editing = false;

            if (object.data.type_data.fill && object.data.type_data.fill !== 'none') {
                $('[data-cmd="fill"]').removeClass('no-color').css('background-color', object.data.type_data.fill);
            } else {
                $('[data-cmd="fill"]').addClass('no-color');
            }

            if (object.data.type_data.stroke.color && object.data.type_data.stroke.color !== 'none') {
                $('[data-cmd="stroke"]').removeClass('no-color').css('background-color', object.data.type_data.stroke.color);
            } else {
                $('[data-cmd="stroke"]').addClass('no-color');
            }

            object.helper = document.createElementNS(this._svg[0].namespaceURI, 'g');
            object.helper.setAttribute('data-helper', object.data.key);
            this._svg_group.helper.append(object.helper);

            $(object.elm).trigger('calculate-min-size', [object]);

            this._objectHelperRender(object);
            var helper_config = '_object' + this._objectGetTypeFunctionName(object.data.type) + '_configRender';

            if (typeof this[helper_config] === 'function' && this._permissions.edit_layer) {
                this[helper_config](object);
            }

            $(object.elm).trigger('object-select');

            var blend_mode_selector = this._thumb_panel.find('.blend-mode-bar select');

            if (object.data.type_data.blend_mode) {
                blend_mode_selector.find('option').each(function (k) {
                    if (this.value === object.data.type_data.blend_mode) {
                        this.parentNode.selectedIndex = k;
                        return false;
                    }
                });
            } else {
                blend_mode_selector[0].selectedIndex = 0;
            }

            blend_mode_selector.trigger('change');
            this._renderObjectSwitcher()
        }

        _objectGetCurrent(type) {
            for (var key in this._objects) {
                if (this._objects[key].selected) {
                    return !type || this._objects[key].data.type === type ? this._objects[key] : null;
                }
            }

            return null;
        }

        _objectUnselectCurrent() {
            for (var key in this._objects) {
                if (this._objects[key].selected) {
                    this._objectUnselect(this._objects[key]);
                }
            }
            this._renderObjectSwitcher()
        }

        _objectUnselect(object) {
            if (!object.selected) {
                return;
            }

            object.selected = false;
            object.editing = false;

            if (object.helper && object.helper.parentNode) {
                object.helper.parentNode.removeChild(object.helper);
            }

            delete object.helper;

            $(this._config_panel).empty();

            $(object.elm).trigger('object-clearHelper');

            this._historyAdd();

            this._last_selected_object = object;

            var blend_mode_selector = this._thumb_panel.find('.blend-mode-bar select');

            blend_mode_selector[0].selectedIndex = 0;

            blend_mode_selector.trigger('change');
            this._objectHighlightRemove();
        }

        _objectAddThumb(key) {
            if (!this._objects[key]) {
                return;
            }

            var $this = this;

            var object = this._objects[key];

            var list_item = $('<div />').attr('data-object', key).attr('data-type', object.data.type).addClass('thumb-item')[0];

            if ($(object.elm).next('[data-object]')[0]) {
                $(list_item).insertAfter(this._object_thumb_list.find('[data-object="' + $(object.elm).next('[data-object]').attr('data-object') + '"]'));
            } else if ($(object.elm).parent().closest('g[data-type="group"]')[0]) {
                this._object_thumb_list.find('[data-object="' + $(object.elm).parent().closest('g[data-type="group"]').attr('data-object') + '"] > .thumb-list').prepend(list_item);
            } else {
                this._object_thumb_list.prepend(list_item);
            }

            var item_content = $('<div />').addClass('thumb-content').appendTo(list_item);

            $('<div />').addClass('thumb-list').appendTo(list_item);

            const name_span = $('<span />').html(object.data.name);

            if(this._permissions.edit_layer) {
                name_span.click(function () {
                    $('<input />').val(name_span.text()).insertBefore(this).blur(function () {
                        var new_name = this.value.trim();

                        if (new_name === '') {
                            new_name = name_span.text();
                        }

                        object.data.name = new_name;

                        name_span.html(new_name).insertBefore(this);

                        $(this).remove();
                    }).focus(function () {
                        this.select();
                    })[0].focus();
                    name_span.detach();
                });
            }

            $('<div />').addClass('showable').click(function () {
                if (object.data.locked) {
                    return;
                }
                $this._objectUnselectCurrent();

                object.data.showable = !object.data.showable;

                // check parent type === tab, reset config.order
                try {
                    if (object.list_item?.parentElement?.parentElement) {
                        const parent_node = object.list_item?.parentElement?.parentElement;
                        const parent_key = parent_node.getAttribute('data-object') || null;
                        const parent_object = parent_key ? $this._objects[parent_key] : null;
                        const parent_data = parent_object ? parent_object.data : null;
                        if (parent_data && parent_data.personalized && parent_data.personalized.type === 'tab')
                            $this._objects[parent_key].data.personalized.config.order = null
                    }
                } catch (error) {
                    console.log(error)
                }
                $(object.elm).trigger('object-update');
            }).append($.renderIcon('eye')).appendTo(item_content);

            $('<div />').addClass('locked').click(function () {
                object.data.locked = !object.data.locked;
                $(object.elm).trigger('object-update');
                $this._objectUnselectCurrent();
            }).append($.renderIcon('lock')).appendTo(item_content);

            $('<div />').addClass('not-render').click(function (e) {
                const is_not_render = !object.data.is_not_render_design
                if (is_not_render && $this._objectHasPersonalized(object.data.key)) {
                    alert('Cannot apply "Not Render Design" with personalized object');
                    return
                }
                object.data.is_not_render_design = is_not_render;
                if (object.data.is_not_render_design) {
                    $(this).html($.renderIcon('not-render'));
                }
                $(object.elm).trigger('not_render_update');

            }).append($.renderIcon('not-render')).appendTo(item_content);

            $('<div />').addClass('type').append($.renderIcon(object.elm.getAttribute('data-icon'))).appendTo(item_content);
            $('<div />').addClass('name').append(name_span).appendTo(item_content);
            $('<div />').addClass('select').click(function () {
                if (object.selected) {
                    $this._objectUnselectCurrent();
                } else {
                    $this._objectSelect(object);
                }
            }).append($('<ins />')).appendTo(item_content);

            if (object.data.type === 'group') {
                $('<div />').addClass('toggler').append($.renderIcon('caret-down')).insertBefore(name_span).click(function () {
                    $(list_item).toggleClass('toggled');
                });
            }

            if(this._permissions.edit_layer) {
                item_content.find('> .type').unbind('.dragger').bind('mousedown.dragger', function (e) {
                    if (e.which !== 1) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    $this._objectSelect(object);

                    $this._object_thumb_list.find('.thumb-item').removeClass('reordering');

                    $('.svg-item-thumb-dragging').remove();

                    var helper = item_content.clone()
                            .removeAttr('class')
                            .addClass('svg-item-thumb-dragging')
                            .css({
                                marginLeft: ((item_content[0].getBoundingClientRect().x + $(window).scrollLeft()) - e.pageX) + 'px',
                                marginTop: ((item_content[0].getBoundingClientRect().y + $(window).scrollTop()) - e.pageY) + 'px',
                                width: item_content.outerWidth() + 'px'
                            }).appendTo(document.body);

                    helper.swapZIndex();

                    $(list_item).addClass('reordering');

                    $(document.body).addClass('dragging');
                    const _parent = list_item?.parentElement?.parentElement || null;
                    const _parent_key = _parent?.getAttribute('data-object') || null;
                    let _parent_object = _parent_key ? $this._objects[_parent_key] : null;
                    $(document).unbind('.dragger').bind('mousemove.dragger', function (e) {
                        var scroll_top = $(window).scrollTop();
                        var scroll_left = $(window).scrollLeft();

                        helper.css({top: (e.pageY - scroll_top) + 'px', left: (e.pageX - scroll_left) + 'px'});

                        var container_rect = $this._object_thumb_list[0].getBoundingClientRect();

                        if (e.pageX < (container_rect.x + scroll_left) || e.pageX > (container_rect.x + container_rect.width + scroll_left) || e.pageY < (container_rect.y + scroll_top) || e.pageY > (container_rect.y + container_rect.height + scroll_top)) {
                            return;
                        }

                        var matched = false;

                        $this._object_thumb_list.find('.thumb-content').each(function () {
                            if (this === item_content[0] || ((item_content[0].compareDocumentPosition(this) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY)) {
                                return;
                            }

                            var rect = this.getBoundingClientRect();

                            if (e.pageX >= (rect.x + scroll_left) && e.pageX <= (rect.x + scroll_left + rect.width) && e.pageY >= (rect.y + scroll_top) && e.pageY <= (rect.y + rect.height + scroll_top)) {
                                matched = true;

                                var sibling_container = $(this).closest('.thumb-item');

                                var _object = $this._objects[sibling_container.attr('data-object')];

                                if (e.pageY < (rect.y + scroll_top + (rect.height / 2))) {
                                    $(list_item).insertBefore(sibling_container);

                                    $(object.elm).insertAfter(_object.elm);
                                } else {
                                    if (this.parentNode.getAttribute('data-type') === 'group' && e.pageX > ($(this).find('.toggler')[0].getBoundingClientRect().x + scroll_left)) {
                                        sibling_container.addClass('toggled');
                                        sibling_container.find('> .thumb-list').prepend(list_item);

                                        $(_object.elm).append(object.elm);
                                    } else {
                                        sibling_container.removeClass('toggled');
                                        $(list_item).insertAfter(sibling_container);

                                        $(object.elm).insertBefore(_object.elm);
                                    }
                                }

                                $this._objectUpdateIndex();
                            }

                            if (e.pageY < (rect.y + scroll_top)) {
                                return false;
                            }
                        });

                        if (!matched) {
                            var main_list_rect = $this._object_thumb_list[0].getBoundingClientRect();

                            if (e.pageY <= main_list_rect.y + scroll_top) {
                                $this._object_thumb_list.prepend(list_item);
                            } else if (e.pageY >= main_list_rect.y + main_list_rect.height + scroll_top) {
                                $this._object_thumb_list.append(list_item);
                            }
                        }
                    }).bind('mouseup.itemReorder', function (_e) {
                        try {
                            const next_parent = list_item?.parentElement?.parentElement || null;
                            const next_parent_key = next_parent?.getAttribute('data-object') || null;
                            let next_object = next_parent_key ? $this._objects[next_parent_key] : null;
                            if ((_parent_key || next_parent_key) && _parent_key !== next_parent_key) {
                                if (next_object && next_object.data && next_object.data.personalized && next_object.data.personalized.type === "tab" && $this._objects[next_parent_key]) {
                                    $this._objects[next_parent_key].data.personalized.config.order = null;
                                }
                                if (_parent_object && _parent_object.data && _parent_object.data.personalized && _parent_object.data.personalized.type === "tab" && $this._objects[_parent_key]) {
                                    $this._objects[_parent_key].data.personalized.config.order = null;
                                }
                            }
                        } catch (error) {
                            console.log("mouseup.itemReorder: ", error.toString());
                        }
                        $(document).unbind('.dragger');
                        $(document.body).removeClass('dragging');
                        helper.remove();
                        $(list_item).removeClass('reordering');
                    });
                });
            }

            object.list_item = list_item;

            this._objectUpdateIndex();
        }

        _objectUpdateIndex() {
            var $this = this;

            this._svg_group.object.find('g[data-type="group"]').each(function () {
                var object = $this._objects[this.getAttribute('data-object')];

                object.data.type_data.children = [];

                $(this).find('> [data-object][data-type]').each(function () {
                    var child_key = this.getAttribute('data-object');

                    object.data.type_data.children.push(child_key);
                });
            });

            var index = -1;

            $(this._svg_group.object.find('[data-object][data-type]').get().reverse()).each(function () {
                $this._objects[this.getAttribute('data-object')].data.index = ++index;
            });
        }

        _objectTransformFitBackground(object) {
            const bbox = this._elmGetBBox(object.elm);

            const rect = this._getObjectRectFitBackground(bbox)
            var pivot = {
                x: bbox.x + bbox.width / 2,
                y: bbox.y + bbox.height / 2
            };
            const new_pivot = {
                x: rect.x + rect.width /2 ,
                y: rect.y + rect.height /2
            }

            var translate = {
                x: new_pivot.x - pivot.x ,
                y: new_pivot.y - pivot.y
            };
            const scale = {
                x: rect.width / bbox.width,
                y: rect.height / bbox.height
            }

            if (object.data.type === 'group') {
                if(!object.data.type_data?.children?.length) return;

                rect.scale = scale
                $(object.elm).trigger('transform-begin');
            }

            if(object.data.personalized?.type !== "imageSelector"){
                $(object.elm).trigger("resize", [rect])
            }

            if(["imageSelector", "switcher"].includes(object.data.personalized?.type)) {
                $(object.elm).trigger("parent_transform", [scale, translate, 0, {x: 0, y: 0}, pivot])
            }

            if (object.data.type === 'group') {
                $(object.elm).trigger('transform-end');
            }
        }

        setDesignId = function (design_id) {
            this._design_id = design_id;
        }

        setMetaData = function (meta_data) {
            if (meta_data?.image_folder_id_on_server?.length) {
                this.folderIdToUploadS3 = meta_data.image_folder_id_on_server;
                $('#folderId').val(this.folderIdToUploadS3);
            }
            this._meta_data = meta_data;
        }

        _getImageDataOfObjects(objects /* object || Array objects */) {
            if(!objects) return {};
            if(!Array.isArray(objects)) {
                objects = [objects];
            }
            const image_data = {}
            const addImage = (type_data) => {
                if(!type_data || !!image_data[type_data.id]) return;
                image_data[type_data.id] = this._image_data[type_data.id];
            }
            objects.forEach(object => {
                if(object.type === 'image') {
                    addImage(object.type_data)
                    if(object.personalized?.type === "imageSelector" && typeof object.personalized?.config?.groups === "object") {
                        for(const groupKey in object.personalized.config.groups) {
                            const group = object.personalized.config.groups[groupKey];

                            for(const imageKey in group.images) {
                                addImage(group.images[imageKey].data?.type_data);
                            }
                        }
                    }
                } else if(object.type === 'group') {
                    Object.assign(image_data, this._getImageDataOfObjects(object.type_data.children));
                    if(!object.type_data.children) console.log('object.type_data.children', object.type_data)

                    if(object.personalized?.type === "switcher" && typeof object.personalized?.config?.options === "object") {
                        for(const optionKey in object.personalized?.config?.options) {
                            const option = object.personalized.config.options[optionKey];
                            Object.assign(image_data, this._getImageDataOfObjects(option.objects))
                        }
                    }
                }
            })
            return image_data;
        }

        getData = function () {
            const objects = this._fetch(this._object_thumb_list, false, 1);

            if (objects.length < 1) {
                return null;
            }

            const bbox = this._elmGetBBox(this._svg_group.object[0]);
            const image_data = this._getImageDataOfObjects(objects);

            return JSON.stringify({
                version: 2,
                document: {
                    type: this.document_type.key,
                    width: this.document_type.width,
                    height: this.document_type.height,
                    custom_document_safe_area_width: this.document_type.custom_document_safe_area_width,
                    custom_document_safe_area_height: this.document_type.custom_document_safe_area_height,
                    ratio: 1
                },
                bbox: {
                    x: bbox.x,
                    y: bbox.y,
                    width: bbox.width,
                    height: bbox.height
                },
                image_data,
                objects,
            });
        }

        //SET DATA OF PERSONALISED DESIGN
        setData = function (data, locked_history) {
            var $this = this;

            if (locked_history) {
                this._history_locked = true;
            }

            if (data.document.type === 'custom') {
                this._document_types.push( {key: 'custom', title: 'Custom', width: data.document.width, height: data.document.height, custom_document_safe_area_width: data.document.custom_document_safe_area_width, custom_document_safe_area_height: data.document.custom_document_safe_area_height})
            }

            var matched_document_type = null;

            $.each(this._document_types, function (index, document_type) {
                if (document_type.key === data.document.type) {
                    matched_document_type = index;
                    return false;
                } else if (!matched_document_type && data.document.width === document_type.width && data.document.height === document_type.height) {
                    matched_document_type = index;
                }
            });

            if (data.document.type === 'custom') {
                this._bottom_bar.find('[data-selector="document-type"] select').append('<option value="' +matched_document_type+ '">Custom</option>')

            }

            if (matched_document_type) {
                this._bottom_bar.find('[data-selector="document-type"] select').val(matched_document_type).trigger('change');
            }

//            this._doc_width = data.document.width;
//            this._doc_height = data.document.height;

            this._objectUnselectCurrent();

            this._object_thumb_list.find('> [data-object]').each(function () {
                $this._objectRemove(this.getAttribute('data-object'));
            });

            // SET IMAGE DATA
            const image_data = !!data.image_data && typeof data.image_data === 'object' ?  data.image_data : {};
            const links_map = {}
            for (const id in image_data) {
                const url = image_data[id].url;
                links_map[url] = id;
            }

            const addImage = (type_data) => {
                if(!type_data || typeof type_data !== 'object' || !!type_data.id) return;

                let id = links_map[type_data.url];
                if(!id) {
                    id = getUniqId();
                    const img_data = {
                        url: type_data.url,
                        original_size: type_data.original_size,
                    }
                    if(type_data.hash) img_data.hash = type_data.hash;
                    image_data[id] = img_data;
                    links_map[img_data.url] = id;
                }
                type_data.id = id;

                delete type_data.url;
                delete type_data.original_size;
                delete type_data.hash;
                delete type_data.slice;
                return type_data;
            }

            const _groupImageData = (objects = []) => {
                objects.forEach(object => {
                    if(object.type === 'image') {
                        addImage(object.type_data)
                        if(object.personalized?.type === "imageSelector" && typeof object.personalized?.config?.groups === "object") {
                            for(const groupKey in object.personalized.config.groups) {
                                const group = object.personalized.config.groups[groupKey];

                                for(const imageKey in group.images) {
                                    if(group.images[imageKey]?.data?.type_data)
                                        addImage(group.images[imageKey].data.type_data);
                                }
                            }
                        }
                    } else if(object.type === 'group') {
                        _groupImageData(object.type_data.children);

                        if(object.personalized?.type === "switcher" && typeof object.personalized?.config?.options === "object") {
                            for(const optionKey in object.personalized?.config?.options) {
                                const option = object.personalized.config.options[optionKey];

                                if(option.objects) _groupImageData(option.objects)
                            }
                        }
                    }
                })
            }
            _groupImageData(data.objects)

            this._image_data = image_data;
            this._links_map = links_map;
            // delete link_map;
            // END SET IMAGE DATA

            this._setDataMakeObject(data.objects, this._svg_group.object[0]);

            if (locked_history) {
                this._history_locked = false;
            }
        }

        _setDataMakeObject(objects, group) {
            var $this = this;

            var before_append_flag = this._is_append_data;

            this._is_append_data = true;

            for (let i = objects.length - 1; i >=0 ; i--) {
                const object_data = objects[i];
                var type_data = JSON.parse(JSON.stringify(object_data.type_data));

                var object = $this._objectAdd(object_data.type, type_data, object_data.key, 1, {
                    personalized: object_data.personalized,
                    name: object_data.name,
                    showable: object_data.showable,
                    locked: object_data.locked,
                    group: group,
                    is_not_render_design: object_data.is_not_render_design
                }, true);

                if (object_data.type === 'group') {
                    $this._setDataMakeObject(object_data.type_data.children, object);
                }
            }

            this._is_append_data = before_append_flag;
        }

        _fetch(container, new_key) {
            var $this = this;

            var objects = [];

            container.find('> [data-object]').each(function () {
                var object = $this._objects[this.getAttribute('data-object')];

                if (typeof object !== 'undefined') {
                    var object_data = JSON.parse(JSON.stringify(object.data));

                    $this._peronalize_fetchCallback(object_data, new_key);

                    if (object.data.type === 'group') {
                        object_data.type_data.children = $this._fetch($(this).find('> .thumb-list'), new_key);
                    }

                    if (new_key) {
                        object_data.key = $.makeUniqid();

                        if (object_data?.type_data?.path?.key) {
                            object_data.type_data.path.key = $.makeUniqid();
                        }
                    }

                    objects.push(object_data);
                }
            });

            return objects;
        }

        _updateTmpData(content) {
            clearTimeout(this._tmp_timer);

            this._tmp_timer = setTimeout(() => {
                content = content || this.getData();
                const formData = new FormData();
                formData.append('id', this._design_id);
                formData.append('hash', OSC_HASH);
                formData.append('content',content);
                formData.append('folderId', this.folderIdToUploadS3);

                if(this.xhrUpdateTmp) this.xhrUpdateTmp.abort();

                this.xhrUpdateTmp = new XMLHttpRequest();
                this.xhrUpdateTmp.open('POST', $.base_url + '/personalizedDesign/backend/updateTmp', true);
                this.xhrUpdateTmp.send(formData);
                this.xhrUpdateTmp.onload = function () {
                    this.xhrUpdateTmp = null;
                };
                this.xhrUpdateTmp.onerror = function () {
                    this.xhrUpdateTmp = null;
                }
            }, 3000);
        }

        _historyAdd(skip_tmp) {
            if (!this._permissions.edit_layer || this._history_locked) {
                return;
            }
            let content = this.getData();

            if (this._history_idx >= 0 && content === this._history_data[this._history_idx]) {
                return;
            }

            if (this._history_idx !== this._history_data.length - 1) {
                this._history_data = this._history_data.slice(0, this._history_idx + 1);
                this._history_idx = this._history_data.length - 1;
            }

            if (this._history_idx > 5) {
                this._history_data = this._history_data.slice(0, 4);
                this._history_idx = this._history_data.length - 1;
            }

            this._history_data.push(content);

            if (!skip_tmp) {
                this._updateTmpData();
            }

            this._history_idx++;
        }

        _historyUndo() {
            if (!this._permissions.edit_layer || this._history_idx === 0) {
                return;
            }

            this._history_idx--;

            this._history_locked = true;

            var content = this._history_data[this._history_idx];

            this.setData(JSON.parse(content));

            this._history_locked = false;

            this._updateTmpData(content);
            toastr.success("Undo successfully!")
        }

        _historyRedo() {
            if (!this._permissions.edit_layer || (this._history_idx === this._history_data.length - 1)) {
                return;
            }

            this._history_idx++;

            this._history_locked = true;

            var content = this._history_data[this._history_idx];

            this.setData(JSON.parse(content));

            this._history_locked = false;

            this._updateTmpData(content);
            toastr.success("Redo successfully!")
        }
        _copyObject(object) {
            const object_data = JSON.parse(JSON.stringify(object.data));

            this._peronalize_fetchCallback(object_data, true);

            if (object.data.type === 'group') {
                object_data.type_data.children = this._fetch($(object.list_item).find('> .thumb-list'), true);
            }

            object_data.key = $.makeUniqid();
            object_data.site = $.base_url;

            const image_data = this._getImageDataOfObjects([object_data]);
            const data = JSON.stringify({
                version: 2,
                object_data,
                image_data
            });
            try {
                localStorage.setItem(PERSONALISED_DESIGN_CLIPBOARD, data);
                toastr.success('The object "' + object.data.name + '" is now on the clipboard.');
            } catch (e) {
                toastr.error('Unable to copy: ' + e.mesage || e);
                console.error('Unable to copy: ', e)
            }
        }

        _pasteObject() {
            let data
            try {
                data = localStorage.getItem(PERSONALISED_DESIGN_CLIPBOARD);

                if (!data) {
                    toastr.warning('Please copy a layer before paste');
                    return;
                }
            } catch (e) {
                toastr.error('Unable to get copied data: ' + e);
                return;
            }
            try {
                data = JSON.parse(data);
            } catch (error) {
                toastr.error('Data in clipboard is not JSON data');
                return
            }

            const {object_data, image_data } = data;
            const version = data.version || 1;

            if(typeof object_data !== "object" || typeof image_data !== "object") {
                toastr.warning('Please copy a layer before paste');
                return;
            }
            if (version === 1) {
                this._transformObjectV1ToV2(object_data);
                console.log("object_data", JSON.parse(JSON.stringify(object_data)))
            }
            Object.assign(this._image_data, image_data)

            const updateKey = (obj_data) => {
                if(!obj_data) return;

                obj_data.key = $.makeUniqid();
                this._peronalize_fetchCallback(obj_data, true);
                if(obj_data.type === 'group') {
                    obj_data.type_data.children.forEach(updateKey);
                }
            }

            updateKey(object_data);

            if (!object_data || typeof object_data.key === 'undefined' || typeof object_data.site === 'undefined' || object_data.site !== $.base_url) {
                return;
            }

            var _cloned_object_data = JSON.parse(JSON.stringify(object_data));

            var object = this._objectAdd(object_data.type, object_data.type_data, object_data.key, 1, {personalized: object_data.personalized, name: object_data.name, showable: object_data.showable, locked: object_data.locked, is_not_render_design: object_data.is_not_render_design});

            this._objectUnselect(object);

            if (object_data.type === 'group') {
                this._setDataMakeObject(_cloned_object_data.type_data.children, object);
            }
            this._objectTransformFitBackground(object);

            //Paste xong thì select
            this._objectSelect(object);
            // Update UX ko xoá clipboard sau khi update
            // localStorage.removeItem(PERSONALISED_DESIGN_CLIPBOARD);
        }

        _transformObjectV1ToV2 (object, parent_old_ratio = 1) {
            this._objectApplyNewRatioToData(object.type, object.type_data, parent_old_ratio, 1);

            if (object.type === 'group') {
                object.type_data.children?.forEach(child => {
                    this._transformObjectV1ToV2(child, parent_old_ratio)
                })
            }
            if(!object.personalized) return;

            switch (object.personalized.type) {
                case 'imageSelector':
                    for (const groupKey in object.personalized.config.groups) {
                       const group = object.personalized.config.groups[groupKey];
                        for (const imageKey in group.images) {
                            const image = group.images[imageKey];
                            console.log("image", image)
                            if (image.data?.type_data && image.data?.ratio) {
                                console.log("image.data.ratio",  image.data.ratio)
                                this._objectApplyNewRatioToData('image', image.data.type_data, image.data.ratio, 1);
                                delete image.data.ratio;
                            }
                        }
                    }
                    break;
                case 'switcher':
                    for (const optionKey in object.personalized.config.options) {
                        const option = object.personalized.config.options[optionKey];
                        if (option.data?.objects && option.data.ratio) {
                            const old_ratio = option.data.ratio;
                            option.objects =  option.data.objects
                            delete option.data;

                            option.objects.forEach(child => {
                                this._transformObjectV1ToV2(child, old_ratio)
                            })
                        } else {
                            option.objects = [];
                            delete option.data;
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        _objectApplyNewRatioToData(type, data, old_ratio, new_ratio) {
            const $this = this;

            if (old_ratio === new_ratio) {
                return;
            }

            var _func = '_object' + this._objectGetTypeFunctionName(type) + '_applyNewRatioToData';

            if (typeof this[_func] === 'function') {
                this[_func](data, old_ratio, new_ratio);
            }

            var ratio = new_ratio / old_ratio;

            if (typeof data.outline !== 'undefined' && typeof data.outline.width !== 'undefined') {
                data.outline.width *= ratio;
            }

            if (Array.isArray(data.mask)) {
                for (var mask_idx = 0; mask_idx < data.mask.length; mask_idx++) {
                    if(type !=="image")
                        $this._objectApplyNewRatioToData(data.mask[mask_idx].type, data.mask[mask_idx].data, old_ratio, new_ratio);

                    data.mask[mask_idx].target_bbox.x *= ratio;
                    data.mask[mask_idx].target_bbox.y *= ratio;
                    data.mask[mask_idx].target_bbox.width *= ratio;
                    data.mask[mask_idx].target_bbox.height *= ratio;
                }
            }
        }

        _objectPath_applyNewRatioToData(data, old_ratio, new_ratio) {
            var ratio = new_ratio / old_ratio;

            for (var i = 0; i < data.points.length; i++) {
                var point = data.points[i];

                point.point.x *= ratio;
                point.point.y *= ratio;

                if (point.handle_in) {
                    point.handle_in.x *= ratio;
                    point.handle_in.y *= ratio;
                }

                if (point.handle_out) {
                    point.handle_out.x *= ratio;
                    point.handle_out.y *= ratio;
                }
            }

            data.bbox.x *= ratio;
            data.bbox.y *= ratio;
            data.bbox.width *= ratio;
            data.bbox.height *= ratio;
        }

        _objectText_applyNewRatioToData(data, old_ratio, new_ratio) {
            var ratio = new_ratio / old_ratio;

            data.style.font_size *= ratio;
            data.style.letter_spacing *= ratio;
            data.style.word_spacing *= ratio;
//            data.offset *= ratio;
            data.position.x *= ratio;
            data.position.y *= ratio;
            data.size.width *= ratio;
            data.size.height *= ratio;
            if(data.style.dynamic_font_size) data.style.dynamic_font_size *= ratio;

            if (data.path) {
                this._objectApplyNewRatioToData(data.path.type, data.path.data, old_ratio, new_ratio);
            }
        }

        _objectEllipse_applyNewRatioToData(data, old_ratio, new_ratio) {
            data.center.x *= new_ratio / old_ratio;
            data.center.y *= new_ratio / old_ratio;
            data.rx *= new_ratio / old_ratio;
            data.ry *= new_ratio / old_ratio;
        }

        _objectRect_applyNewRatioToData(data, old_ratio, new_ratio) {
            data.position.x *= new_ratio / old_ratio;
            data.position.y *= new_ratio / old_ratio;
            data.size.width *= new_ratio / old_ratio;
            data.size.height *= new_ratio / old_ratio;
        }

        _objectImage_applyNewRatioToData(data, old_ratio, new_ratio) {
            data.position.x *= new_ratio / old_ratio;
            data.position.y *= new_ratio / old_ratio;
            data.size.width *= new_ratio / old_ratio;
            data.size.height *= new_ratio / old_ratio;
        }

        _copyObjectAttributes(){
            const object = this._objectGetCurrent();
            if(!object || !object.elm) return;

            const bbox = this._elmGetBBox(object.elm);
            const tranform = this._elmGetTransform(object.elm);

            const dataCopied = {
                x: bbox.x,
                y: bbox.y,
                width: bbox.width,
                height: bbox.height,
                rotation: tranform.rotation,
                key: object.data.key,
            }
            try {
                localStorage.setItem(PERSONALISED_ATTRIBUTES_CLIPBOARD, JSON.stringify(dataCopied));
                toastr.success('The attributes of object "' + object.data.name + '" is now on the clipboard');
                $(object.elm).triggerHandler("copied-object")
            } catch (error) {
                toastr.error(`Copy object attributes failed!, error: ${error.message}`)
            }
        }

        _pasteObjectAttributes(){
            const object = this._objectGetCurrent();
            if(!object || !object.elm) {
                toastr.warning("Please select an object before paste attributes!")
                return;
            }

            if(object.data.type === "group") {
                toastr.warning("Cannot paste object attributes into Group!");
                return
            }

            let dataCopied;
            try {
                dataCopied = localStorage.getItem(PERSONALISED_ATTRIBUTES_CLIPBOARD);
                dataCopied = JSON.parse(dataCopied);
            } catch (error) {
                toastr.error(`Get copied object attributes failed!, error: ${error.message}`)
                return
            }

            if(!dataCopied) {
                toastr.warning("You have not copied any object attributes yet!")
                return;
            }
            const currentBBox = this._elmGetBBox(object.elm);
            const size = {
                width: currentBBox.width,
                height: currentBBox.height
            }
            const bbox = {
                x: dataCopied.x,
                y: dataCopied.y,
                width: dataCopied.width,
                height: dataCopied.height,
            };
            const newRect = this._getRectFitBBox(size, bbox, false)

            $(object.elm).triggerHandler('move', [newRect]);
            $(object.elm).triggerHandler('resize', [newRect]);
            $(object.elm).triggerHandler('rotate', [dataCopied.rotation]);
            $(object.elm).triggerHandler("pasted-object")
            this._historyAdd();

            // Update UX ko xoá clipboard sau khi update
            // localStorage.removeItem(PERSONALISED_ATTRIBUTES_CLIPBOARD);
        }

        //======================================================================================
        //================================= PSD Upload Code ====================================
        //======================================================================================

        _isShowProgressBar(isShow) {
            if (isShow) {
                $('#progress-bar').parent().show();
                $('#label-progress').text('Wait a second ...').show();
            } else {
                $('#progress-bar').css('width', '0').parent().css('display', 'none');
                $('#label-progress').css('display', 'none');
            }
        }

        _showPSDError(err, isStopProcess = true) {
            this._isShowProgressBar(false);
            if (this.isUpdatePSD) $.unwrapContent('dropFilePSDModal', true);
            if (isStopProcess) {
                const messageContainer = $('<ul />').addClass('message-error message-container');
                $('.content-wrap > .message-container').remove();
                $('<li />').text(err).appendTo(messageContainer);
                $('.content-wrap > .header').after(messageContainer);

                throw err;
            } else {
                toastr.error(err);
            }

        }

        _checkNameNodeContainSlash(node) {
            if (node.get('name').includes('/')) {
                this._showPSDError(`ERROR: Name [${node.get('name')}] contains /`, false)
                return true;
            }
            return false
        }

        _checkLayerNodeIsEmpty(node) {
            if (node.isEmpty()) {
                toastr.warning(`WARNING: Layer [${node.path()}] is empty \n .Continuing and ignore this layer`);
                return true;
            }
            return false;
        }

        _upperCaseWords(str) {
            return str.replace(/\b\w/g, l => l.toUpperCase());
        }

        _checkStringBothUpLowCase(str1, str2) {
            if (this._upperCaseWords(str1).trim() === this._upperCaseWords(str2).trim()) {
                return true;
            }
            return false;
        }

        _checkNodesHasSameName(node) {
            let siblings = node.siblings();

            for (let sibling of siblings) {
                if (
                    this._upperCaseWords(node.get('name')) === this._upperCaseWords(sibling.get('name'))
                    && siblings.indexOf(node) !== siblings.indexOf(sibling)
                    && node.type === sibling.type
                ) {
                    this._showPSDError(`ERROR: Node [${node.path()}] has same name siblings`, false);
                    return true;
                }
            }

            return false;
        }

        _checkNodeBelongsToIgnoreNode(nodePath) {
            for (let ignore of this.ignoreNode) {
                if (ignore && nodePath.includes(ignore)) {
                    return true;
                }
            }
            return false;
        }

        _isPersonalizedNode(node) {
            const name = node.get('name').trim();

            if (node.isGroup() && name[name.length - 1] === SPECIAL_PSD_CHAR) {
                return true;
            }
            return false;
        }

        _isSwitcherNode(node) {
            const hasAtleaseOneLayer = node.children().find(child => !child.isGroup());
            const hasGroupImageSelector = node.children().filter(child => this._isGroupImageSelectorNode(child));
            return node.children().length && !hasAtleaseOneLayer && hasGroupImageSelector.length === 0;
        }

        _isImageSelectorNode(node) {
            const hasAtleaseOneGroup = node.children().find(child => !child.isLayer());
            const hasGroupImageSelector = node.children().filter(child => this._isGroupImageSelectorNode(child));
            return node.children().length && (!hasAtleaseOneGroup || hasGroupImageSelector.length === node.children().length);
        }

        _isGroupImageSelectorNode(node) {
            const hasAtleaseOneGroup = node.children().find(child => !child.isLayer());
            const name = node.get('name').trim();
            return node.isGroup() && name.slice(name.length - 2, name.length) == '**' && !hasAtleaseOneGroup;
        }

        _addNodeToGlobalNode(nodeData) {
            this._objectUnselectCurrent();
            let $this = this;
            const key = nodeData.ancestors.find(key => Object.keys($this._objects).includes(key));

            if (key) { // folder in already object
                let object = $this._findObjectByKeyInGlobalObjects(nodeData.ancestors, $this._objects[key].data);
                // 'Cat/Cat - Cat 1/Cat - Cat 123/cat 123' must check again

                if (nodeData.actionType === 'switcher') { // create a new switcher
                    const key = Object.keys(nodeData.personalized.config.options)[0];
                    object.personalized.config.options[key] = nodeData.personalized.config.options[key];
                    object.personalized.config.options[key].objects = nodeData.type_data.children;
                } else if (nodeData.inPersonalized) { // create a new normal folder on switcher folder
                    delete nodeData.ancestors;
                    delete nodeData.parentKey;
                    delete nodeData.inPersonalized;
                    object.push(nodeData);
                } else { // create a new normal folder on default folder
                    let group = nodeData.parentKey ? $this._objects[nodeData.parentKey] : null;
                    $this._setDataMakeObject([nodeData], group);
                }
            } else { // outer folder
                $this._setDataMakeObject([nodeData]);
            }
        }

        _base64ToBlob(imageSrc, mime) {
            mime = mime || '';
            let base64ImageContent = imageSrc.replace(/^data:image\/png;base64,/, "");
            const sliceSize = 1024;
            let byteChars = window.atob(base64ImageContent);
            let byteArrays = [];

            for (let offset = 0, len = byteChars.length; offset < len; offset += sliceSize) {
                let slice = byteChars.slice(offset, offset + sliceSize);

                let byteNumbers = new Array(slice.length);
                for (let i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                let byteArray = new Uint8Array(byteNumbers);

                byteArrays.push(byteArray);
            }

            return new Blob(byteArrays, {type: mime});
        }

        _getRatioFitBg(heightPSD, widthPSD) {
            this.ratioPSD = heightPSD / this.document_type.height;
        }

        _getRectFitBgBox(image) {
            let x = 0, y = 0, width = 0, height = 0;

            height = image.height / this.ratioPSD;
            width = image.width / this.ratioPSD;
            x = image.x / this.ratioPSD;
            y = image.y / this.ratioPSD;

            return {x, y, width, height}
        }

        _getPersonalizedName(name) {
            return name.trim().slice(0, -1).trim();
        }

        _getLocalImgStorageUrl(file_path) {
            var isOnServerImg = false;
            this.folderIdToUploadS3?.map((folderId) => {
                if (file_path?.includes(folderId)) {
                    isOnServerImg = true;
                    return;
                }
            });

            this._meta_data?.image_folder_id_on_server?.map((folderId) => {
                if (file_path?.includes(folderId)) {
                    isOnServerImg = true;
                    return;
                }
            });

            if (isOnServerImg) {
                return OSC_BASE_URL + '/var/tmp/' + file_path;
            }

            return $.getImgStorageUrl(file_path);
        }

        _getSiblingsNode(node) {
            let siblings = node.siblings();
            let brothers = [];

            brothers = siblings.filter((sibling) => {
                return sibling.isLayer()
                    && sibling.get('name').split(SPECIAL_PSD_CHAR)[1]?.trim() === node.get('name').split(SPECIAL_PSD_CHAR)[1]?.trim()
            });

            return brothers;
        }

        _findThumbnailImageInSwitcher(node, parse_data_node) {
            // find thumbnail image of switcher
            let thumbnailImage = node.children().filter((children) => {
                return children.get('name').trim() === 'thumbnail' || children.get('name').trim() === 'Thumbnail';
            });

            if (thumbnailImage.length === 1) {
                this.imageBase64ThumbnailData.push(thumbnailImage[0].layer.image.toBase64());
                this.imageThumbnail.push(parse_data_node);
                return true;
            } else if (thumbnailImage.length === 0){
                return false;
            } else {
                this._showPSDError(`Too much thumbnail image in switcher path: ${node.path()}`);
            }
        }

        _findObjectByKeyInGlobalObjects(ancestors, object, isThumbnail = false, parentObject) {
            if (object.personalized) { // a switcher and imageSelector
                if (object.personalized.type === 'switcher') {
                    const optionKey = ancestors.find(key => Object.keys(object.personalized.config.options).includes(key));
                    if (optionKey) {
                        let optionObject;
                        if (optionKey == object.personalized.config.default_option_key) {
                            optionObject = object.type_data.children;
                        } else {
                            optionObject = object.personalized.config.options[optionKey];
                        }
                        return this._findObjectByKeyInGlobalObjects(ancestors, optionObject, isThumbnail, object);
                    } else { // update: new switcher
                        return object;
                    }
                } else if (object.personalized.type === 'imageSelector') {
                    const groupKey = ancestors.find(key => Object.keys(object.personalized.config.groups).includes(key));

                    if (groupKey) {
                        const optionKey = ancestors.find(key => Object.keys(object.personalized.config.groups[groupKey].images).includes(key));

                        if (optionKey) {
                            if (optionKey == object.personalized.config.default_key) {
                                return object;
                            } else {
                                return object.personalized.config.groups[groupKey].images[optionKey].data
                            }
                        } else {
                            return object.personalized.config.groups[groupKey].images;
                        }
                    } else {
                        return object.personalized.config.groups;
                    }
                }
            } else if (Array.isArray(object.objects)) { // object in a switcher
                const childrenObject = object.objects?.find(object => ancestors.includes(object.key));

                if (childrenObject) {
                    return this._findObjectByKeyInGlobalObjects(ancestors, childrenObject, isThumbnail);
                } else {
                    if (isThumbnail) {
                        parentObject.personalized.config.image_mode = 1;
                        return object;
                    } else {
                        return object.objects;
                    }
                }
            } else if (Array.isArray(object)){ // object in children normal node
                const childrenObject = object.find(object => ancestors.includes(object.key));
                if (childrenObject) {
                    return this._findObjectByKeyInGlobalObjects(ancestors, childrenObject, isThumbnail);
                } else {
                    if (isThumbnail) { // thumbnail in active switcher
                        parentObject.personalized.config.image_mode = 1;
                        return parentObject.personalized.config.options[parentObject.personalized.config.default_option_key];
                    } else {
                        return object;
                    }
                }
            } else { // normal image
                if (Array.isArray(object.type_data?.children)) {
                    const childrenObject = object.type_data.children.find(object => ancestors.includes(object.key));
                    if (childrenObject) {
                        return this._findObjectByKeyInGlobalObjects(ancestors, childrenObject, isThumbnail);
                    } else {
                        return object.type_data.children;
                    }
                }
                return object;
            }
        }

        async _confirmUpdatePSD() {
            var $this = this;
            $('#updatePSDCheckboxGroup').find(' > div > input').each(function (){
                if ($(this).is(':checked') && $(this).attr('id') !== 'checkAll') {
                    let index = $(this).val();
                    let node = $this.nodeToUpdate[index];
                    let data = $this.tempDataToUpdate[index];
                    $this.ignoreNode[index] = '';

                    // update/create layer image
                    if (data.action.action === 'update') { // update
                        if (data.action.type === 'image') {
                            data.data.type_data.position = {
                                x: node.get('coords').left,
                                y: node.get('coords').top
                            }
                            data.data.action = data.action;
                            data.data.parentKey = data.parentKey;
                            data.data.ancestors = data.inPersonalized ? data.ancestors : [];

                            $this.imageNode.push(data.data);
                        } else if (data.action.type === 'imageSelector') {
                            $this.imageNode.push({
                                name: node.get('name').trim(),
                                type_data: {
                                    position: {
                                        x: node.get('coords').left,
                                        y: node.get('coords').top
                                    },
                                },
                                action: data.action,
                                ancestors: data.ancestors,
                                inPersonalized: data.inPersonalized,
                                IsAddImageSelector: data.IsAddImageSelector
                            });
                        }

                        $this.imageBase64MainData.push(node.layer.image.toBase64());
                    } else if (data.action.action === 'create') { // create
                        if (data.action.type === 'imageSelector') { // image selector
                            let nodeData = {
                                key: $.makeUniqid(),
                                type: 'image',
                                name: node.get('name').trim(),
                                locked: node.get('locked').allLocked,
                                index: $this.object_index_PSD++,
                                // showable: node.layer.visible,
                                showable: true,
                                type_data: {
                                    id: getUniqId(),
                                    rotation: 0,
                                    position: {
                                        x: node.get('coords').left,
                                        y: node.get('coords').top
                                    }
                                },
                                action: data.action,
                                parentKey: data.parentKey,
                                ancestors: data.inPersonalized ? data.ancestors : []
                            }
                            const newFolderNode = node.parent.get('name').includes('**')
                                                    ? node.parent.parent
                                                    : node.parent

                            $this._parseImageSelectorNode(newFolderNode, nodeData);
                        } else if (data.action.type === 'groupImageSelector') { // add group imageSelector
                            let imageSelectors = $this._getSiblingsNode(node);
                            const groupLabel = node.parent.get('name').replaceAll('*', '').trim();
                            let group = {
                                label: groupLabel.trim(),
                                images: {}
                            }
                            for (const imageSelector of imageSelectors) {
                                const key = $.makeUniqid();
                                let image = {};

                                image.label = node.get('name').trim();
                                image.data = {
                                    type_data : {
                                        id: getUniqId(),
                                        position: {
                                            x: imageSelector.get('coords').left,
                                            y: imageSelector.get('coords').top
                                        },
                                        rotation: 0,
                                    },
                                    IsAddGroupImageSelector: true,
                                }
                                group.images[key] = image;
                                $this.imageNode.push(image.data);
                                $this.imageBase64MainData.push(imageSelector.layer.image.toBase64());
                            }
                            let ancestors = data.ancestors || [];

                            const key = ancestors.find(key => Object.keys($this._objects).includes(key));
                            let object = $this._findObjectByKeyInGlobalObjects(ancestors, $this._objects[key].data);
                            let group_key = $.makeUniqid();
                            object[group_key] = group;
                        } else if (data.action.type === 'image') { // normal image
                            $this.imageNode.push({
                                key: $.makeUniqid(),
                                name: node.get('name'),
                                type: 'image',
                                locked: node.get('locked').allLocked,
                                index: $this.object_index_PSD++,
                                // showable: node.layer.visible,
                                showable: true,
                                type_data: {
                                    id: getUniqId(),
                                    rotation: 0,
                                    position: {
                                        x: node.get('coords').left,
                                        y: node.get('coords').top
                                    },
                                },
                                action: data.action,
                                parentKey: data.parentKey,
                                ancestors: data.inPersonalized ? data.ancestors : []
                            });

                            $this.imageBase64MainData.push(node.layer.image.toBase64());
                        } else if (data.action.type === 'folder'
                                || data.action.type === 'folderSwitcher'
                                || data.action.type === 'switcher'
                        ) {
                            let newFolderNode = node;
                            for (let i = 0; i < data.countDeepAncestors; i++) {
                                newFolderNode = newFolderNode.parent;
                            }
                            data.action.inPersonalizedFlag = data.inPersonalized;
                            data.action.ancestors = data.ancestors || [];
                            let parse_data = $this._parseDataFromPSD(newFolderNode, data.action);
                            parse_data.ancestors = data.ancestors || [];
                            parse_data.parentKey = data.parentKey;
                            parse_data.inPersonalized = data.inPersonalized;
                            parse_data.actionType = data.action.type;

                            $this.newFolderNode.push(parse_data);
                        } else if (data.action.type === 'thumbnail') {
                            $this.imageBase64ThumbnailData.push(node.layer.image.toBase64());
                            $this.imageThumbnail.push({
                                parentKey: data.parentKey,
                                ancestors: data.inPersonalized ? data.ancestors : []
                            });
                        }
                    }
                }
            });

            $.unwrapContent('dropFilePSDModal', true);

            await this._uploadImageToBackendAndBuild();

            // new active folder have to render after uploadImageToBackendAndBuild()
            for (let i = this.newFolderNode.length - 1; i >= 0; i--) {
                this._addNodeToGlobalNode(this.newFolderNode[i]);
            }
        }

        _compareStructureDataPSDToUpdate(newPSDData, oldDesignData) {
            let objects = oldDesignData.objects;

            let descendants = newPSDData.descendants();

            descendants.map((node) => {
                if (this._checkNameNodeContainSlash(node)) {
                    throw 'Node name contains slash';
                }
                if (this._checkNodesHasSameName(node)) {
                    throw `Node [${node.path()}] has same name siblings`;
                }
                if (this.ignoreNode.includes(node.path())) {
                    return;
                }

                if (node.isLayer()) {
                    if (this._checkLayerNodeIsEmpty(node) || this._checkNodeBelongsToIgnoreNode(node.path())) {
                        return;
                    }
                    const traceToNode = node.path().split('/');
                    const hasLayerInDesignData = this._findTraceToLayer(traceToNode, 0, objects, node);
                    /*
                        hasLayerInDesignData = 0 : Not found layer route                                        ==> err
                        hasLayerInDesignData = 1 : Find full layer route                                        ==> update layer
                        hasLayerInDesignData = 2 : Not found layer name (still has route to folder of layer)    ==> create new layer
                    */

                    switch (hasLayerInDesignData) {
                        case 0:
                            console.log('Not found layer at ---  ', node.path());
                            this.notFoundNode.push(node);
                            break;
                        case 1:
                            console.log('Update layer at ---  ', node.path());
                            this.nodeToUpdate.push(node);
                            break;
                        case 2:
                            console.log('create new -- ', node.path());
                            this.nodeToUpdate.push(node);
                            break;
                    }
                }
            });
        }

        _findTraceToLayer(
            traceToNode,
            index,
            objects,
            node,
            parentObject,
            inPersonalized = false,
            ancestors = []
        ) {
            let trace = traceToNode[index].trim();
            let hasLayerInDesignData = 0;
            let createSwitcher = true;
            const currentNode = this.treePSD.childrenAtPath(traceToNode.slice(0, index + 1))[0];

            inPersonalized && ancestors.push(parentObject?.key);

            for (let objectData of objects) {
                if (objectData.personalized
                    && this._isPersonalizedNode(currentNode)
                    && this._checkStringBothUpLowCase(objectData.name, this._getPersonalizedName(currentNode.get('name')))
                ) {
                    if (objectData.personalized.type === 'switcher' && this._isSwitcherNode(currentNode)) {  // check switcher
                        for (const optionKey in objectData.personalized.config.options) {
                            if (objectData.personalized.config.options[optionKey].label  === traceToNode[index + 1].trim()) {
                                let _objects = [];

                                if (optionKey === objectData.personalized.config.default_option_key) {
                                    _objects = objectData.type_data.children
                                } else {
                                    inPersonalized = true;
                                    _objects = objectData.personalized.config.options[optionKey].objects;
                                }

                                createSwitcher = false;
                                inPersonalized && ancestors.push(optionKey);
                                hasLayerInDesignData = this._findTraceToLayer(traceToNode, index + 2, _objects, node, objectData, inPersonalized, ancestors);
                                break;
                            }
                        }
                        if (createSwitcher) { // create new option switcher
                            ancestors.push(objectData.key);
                            this.tempDataToUpdate.push({
                                inPersonalized,
                                ancestors,
                                countDeepAncestors: traceToNode.length - index - 1,
                                action: {
                                    action: 'create',
                                    type: 'switcher',
                                    switcherName: traceToNode[index + 1].trim()
                                }
                            });
                            this.ignoreNode[this.tempDataToUpdate.length - 1] = traceToNode.slice(0, index + 2).join('/');
                            return 2;
                        }
                    } else if (objectData.personalized.type === 'imageSelector' && this._isImageSelectorNode(currentNode)) {  // check image selector
                        ancestors.push(objectData?.key);

                        let groupName = node.parent.get('name').replaceAll('*', '').trim();
                        let groupKey = false;
                        for (const key in objectData.personalized.config.groups) {
                            if (this._checkStringBothUpLowCase(objectData.personalized.config.groups[key].label, groupName)) {
                                groupKey = key;
                            }
                        }

                        if (groupKey) { // check in all exist group imageSelector
                            ancestors.push(groupKey);
                            const group = objectData.personalized.config.groups[groupKey];
                            for (const optionKey in group.images) {
                                let option = group.images[optionKey];
                                if (option.label  == `${group.label} #${node.get('name').trim()}`) {
                                    ancestors.push(optionKey);
                                    let tmpData = {
                                        action: {
                                            action: 'update',
                                            type: 'imageSelector'
                                        },
                                        ancestors,
                                        inPersonalized,
                                    }
                                    if (optionKey == objectData.personalized.config.default_key) {
                                        tmpData.id = objectData.type_data.id;
                                    } else {
                                        tmpData.id = option.data.type_data.id;
                                        tmpData.optionKey = optionKey;
                                    }

                                    this.tempDataToUpdate.push(tmpData);
                                    return 1;
                                }
                            }
                            this.tempDataToUpdate.push({
                                action: {
                                    action: 'update',
                                    type: 'imageSelector'
                                },
                                ancestors,
                                inPersonalized,
                                IsAddImageSelector: true,
                                data: objectData
                            });
                            return 1;
                        } else { // add new group imageSelector
                            this.tempDataToUpdate.push({
                                inPersonalized,
                                ancestors,
                                // countDeepAncestors: traceToNode.length - index - 1,
                                action: {
                                    action: 'create',
                                    type: 'groupImageSelector'
                                }
                            });
                            const groupImageSelectorName = node.parent.get('name').includes('**') ? traceToNode[index + 1] : ''
                            this.ignoreNode[this.tempDataToUpdate.length - 1] = traceToNode.slice(0, index + 1).join('/') + '/' +  groupImageSelectorName.trim();
                            return 2;
                        }
                    }
                } else if (objectData.name === trace) { // check normal node
                    inPersonalized && ancestors.push(objectData.key);
                    if (index + 1 === traceToNode.length && objectData.type === 'image' && !objectData.personalized) {
                        this.tempDataToUpdate.push({
                            data: objectData,
                            action: {
                                action: 'update',
                                type: 'image'
                            },
                            parentKey: parentObject?.key,
                            inPersonalized,
                            ancestors
                        });
                        return 1;
                    } else if (traceToNode.length > index + 1) {
                        hasLayerInDesignData = this._findTraceToLayer(traceToNode, index + 1, objectData.type_data.children, node, objectData, inPersonalized, ancestors);
                    }
                }
                if (hasLayerInDesignData) break;
            }

            if (index !== traceToNode.length - 1 && hasLayerInDesignData === 0) { // create new folder
                ancestors.push(parentObject?.key);
                let tmpData = {
                    parentKey: parentObject?.key,
                    inPersonalized,
                    ancestors,
                    countDeepAncestors: traceToNode.length - index - 1,
                }

                if (this._isPersonalizedNode(currentNode)) {
                    if (this._isSwitcherNode(currentNode)) {
                        tmpData.action = {
                            action: 'create',
                            type: 'folderSwitcher'
                        }
                        // let ignoreSwicher = traceToNode.slice(0, index);
                        // ignoreSwicher.push(splitTrace[0]);
                        // this.ignoreNode[this.tempDataToUpdate.length] = ignoreSwicher.join('/');
                    } else if (this._isImageSelectorNode(currentNode)) {
                        tmpData.action = {
                            action: 'create',
                            type: 'imageSelector'
                        }
                    } else {
                        this._showPSDError(`Folder -- ${node.path()} -- is invalid`);
                    }
                } else {
                    tmpData.action = {
                        action: 'create',
                        type: 'folder'
                    }
                    // this.ignoreNode[this.tempDataToUpdate.length] = traceToNode.slice(0, index + 1).join('/');
                }
                this.ignoreNode[this.tempDataToUpdate.length] = traceToNode.slice(0, index + 1).join('/');
                this.tempDataToUpdate.push(tmpData);

                return 2;
            } else if (index === traceToNode.length - 1 && hasLayerInDesignData === 0) {
                let tmpData = {
                    parentKey: parentObject?.key,
                    inPersonalized,
                    ancestors,
                };
                // create normal and thumbnail
                tmpData.data = objects;
                tmpData.action = {
                    action: 'create',
                    type: 'image'
                }

                if (this._checkStringBothUpLowCase(trace, 'thumbnail')) {
                    if (parentObject.personalized) {
                        tmpData.action = {
                            action: 'create',
                            type: 'thumbnail'
                        }
                    } else {
                        return 0;
                    }
                }
                this.tempDataToUpdate.push(tmpData);
                return 2;
            }

            return hasLayerInDesignData;
        }

        _renderConfirmUpdatePSD() {
            const updateCheckboxGroup = $('#updatePSDCheckboxGroup');
            updateCheckboxGroup.empty();
            if (this.nodeToUpdate || this.notFoundNode) {
                let checkAll = $('<input type="checkbox" id="checkAll"/>')
                let labelCheckAll = $('<label>').attr('for', 'checkAll').css({
                    'font-weight': 'bold',
                    'display': 'inline-block',
                }).html('Check All');

                checkAll.on('click', function() {
                    let isCheckAll = $(this).is(':checked');
                    $('#updatePSDCheckboxGroup').find(' > div > input').each(function (){
                        if (!$(this).prop('disabled')) {
                            $(this).prop('checked', isCheckAll);
                        }
                    });
                });

                updateCheckboxGroup.css('display', '')
                    .append('<h4>** Confirm layers need to create/update</h4>')
                    .append(checkAll)
                    .append(labelCheckAll)
                    .append('<br>');
                const fitImgInDiv = (img) => {
                    img.css({
                        'width': '45%',
                        'height': '100%',
                        'object-fit': 'contain'
                    });
                    return img;
                }

                for (let [index, node] of this.nodeToUpdate.entries()) {
                    let textCheckbox ;
                    let textColor;
                    let nodeName = `<span class="tooltip-label" >${node.get('name')}</span>`;
                    let parentFolder = node.parent.isRoot() ? '/' : node.path();
                    let tooltipImage =  $('<span />').addClass('tooltip-image');
                    let newImg = node.layer.image.toPng();
                    newImg = fitImgInDiv($(newImg));
                    if (this.tempDataToUpdate[index].action.action === 'update') {
                        let oldImg;
                        let id = this.tempDataToUpdate[index].data?.type_data?.id || this.tempDataToUpdate[index].id;
                        oldImg = $('<img />').attr('src', this._getLocalImgStorageUrl(this._image_data[id].url.replace(/^(.+)\.([a-zA-Z0-9]+)$/, '$1.preview.$2')));
                        textColor = '#FFBB38';
                        if (this.tempDataToUpdate[index].action.type === 'imageSelector') {
                            if (this.tempDataToUpdate[index].IsAddImageSelector) {
                                textColor = '#35B584';
                                textCheckbox = 'Add image selector: "' + nodeName + '" in folder "' + parentFolder + '"';
                                oldImg = null;
                            } else {
                                textCheckbox = 'Update image selector: "' + nodeName + '" in folder "' + parentFolder + '"';
                            }
                        } else {
                            textCheckbox = 'Update layer image: "' + nodeName + '" in folder "' + parentFolder + '"';
                        }
                        oldImg = fitImgInDiv($(oldImg));
                        tooltipImage.css('width', '300px').css('border-color', textColor)
                            .append(oldImg)
                            .append($.renderIcon('caret-down'))
                            .append(newImg)
                            .hide();
                    } else if (this.tempDataToUpdate[index].action.action === 'create') {
                        if (this.tempDataToUpdate[index].action.type === 'thumbnail') {
                            textCheckbox = 'Add thumbnail image "' + nodeName + '" in folder "' + parentFolder + '"';
                        } else if (this.tempDataToUpdate[index].action.type === 'switcher') {
                            textCheckbox = 'Add switcher: ' + this.ignoreNode[index] ;
                        } else if (this.tempDataToUpdate[index].action.type === 'folder') {
                            textCheckbox = 'Create folder : ' + this.ignoreNode[index] ;
                        } else if (this.tempDataToUpdate[index].action.type === 'folderSwitcher') {
                            textCheckbox = 'Create folder switcher: ' + this.ignoreNode[index] ;
                        } else if (this.tempDataToUpdate[index].action.type === 'imageSelector') {
                            textCheckbox = 'Create image selector: ' + this.ignoreNode[index];
                        } else if (this.tempDataToUpdate[index].action.type === 'groupImageSelector') {
                            textCheckbox = 'Add group image selector: ' + this.ignoreNode[index];
                        } else {
                            textCheckbox = 'Create layer image: "' + nodeName + '" in folder "' + parentFolder + '"';
                        }
                        textColor = '#35B584';
                        tooltipImage.css('width', '150px').css('border-color', textColor).append(newImg).hide();
                    }

                    let label = $('<label>').attr('for', `updateNode${index}`).css({
                        'font-weight': 'bold',
                        'display': 'inline-block',
                        'color': textColor
                    }).html(textCheckbox);

                    label.children('span').append(tooltipImage)
                        .hover(function(){
                            tooltipImage.show();
                        }, function() {
                            tooltipImage.hide();
                    });

                    let containInput = $('<div />').css('display', 'flex')
                        .append(`<input type="checkbox" id="updateNode${index}" value="${index}">`)
                        .append(label);


                    updateCheckboxGroup.append(containInput);
                }

                for (let node of this.notFoundNode) {
                    let nodeName = `<span>${node.get('name')}</span>`;
                    let parentFolder = node.parent.isRoot() ? '/' : node.path();
                    let textCheckbox = 'Not found layer image: "' + nodeName + '" in folder "' + parentFolder + '"';
                    let textColor = '#DF1642';
                    let containInput = $('<div />').css('display', 'flex')
                                        .append(`<input type="checkbox" disabled>`)
                                        .append(`<label style="font-weight: bold; display: inline-block; color: ${textColor}">${textCheckbox}</label><br>`);
                    updateCheckboxGroup.append(containInput);
                }

                $('#spinnerLoading').hide();
                $('#confirmBtn').show();
            }
        }

        _parseDataFromPSD(node, dataAction = {}) {

            // return if node is switcher or is thumbnail
            if (
                this.ignoreNode.includes(node.path())
                || this._checkStringBothUpLowCase(node.get('name'), 'thumbnail')
                || node.isEmpty()
            ) {
                return;
            }

            if (this._checkNameNodeContainSlash(node)) {
                throw 'Node name contains slash';
            }

            if (this._checkNodesHasSameName(node)) {
                throw `Node ${node.path()} has same name siblings`;
            }
            console.log(node.path());
            var nodeData = {
                key: $.makeUniqid(),
                name: node.get('name').trim(),
                locked: node.get('locked').allLocked,
                index: this.object_index_PSD++,
                // showable: node.layer.visible,
                showable: true,
                type_data: {}
            }

            // to identify of level 's input node (beta root: level = 0 (tuong doi))
            if (dataAction.isBetaRootNode === undefined && this.isUpdatePSD) {
                dataAction.isBetaRootNode = true;
            } else if (dataAction.isBetaRootNode === true) {
                dataAction.isBetaRootNode = false;
            }

            if (node.isGroup()) {
                nodeData.type = 'group';
                // nodeData.type_data.blend_mode = node.layer.blendMode.mode;
                nodeData.type_data.blend_mode = null;

                if (this._isPersonalizedNode(node)) {
                    if (this._isSwitcherNode(node)) {
                        nodeData = this._parseSwitcherNode(node, nodeData, dataAction);
                    } else if (this._isImageSelectorNode(node)) {
                        nodeData = this._parseImageSelectorNode(node, nodeData, dataAction);
                        if (this.isUpdatePSD) {
                            nodeData.inPersonalizedFlag = dataAction?.inPersonalizedFlag;
                            nodeData.ancestors = dataAction?.ancestors;
                            nodeData.exeDone = true;
                        }
                    } else {
                        this._showPSDError(`Folder -- ${node.path()} -- is invalid`);
                    }
                } else {
                    // parse data in children
                    var childrenData = [];
                    node.children().map((node) => {
                        let parse_data = this._parseDataFromPSD(node, dataAction);
                        if (parse_data) {
                            childrenData.push(parse_data);
                        }
                    });

                    nodeData.type_data.children = childrenData;
                }


            } else if (node.isLayer()) {    // image in main node
                if (this._checkLayerNodeIsEmpty(node)) {
                    return;
                }

                nodeData.type = 'image';
                nodeData.type_data.rotation = 0;
                nodeData.type_data.position = {
                    x: node.get('coords').left,
                    y: node.get('coords').top
                }

                if (this.isUpdatePSD) {
                    nodeData.inPersonalizedFlag = dataAction?.inPersonalizedFlag;
                    nodeData.exeDone = true;
                }

                this.imageNode.push(nodeData);
                this.imageBase64MainData.push(node.layer.image.toBase64());
            }

            return nodeData;
        }

        _parseSwitcherNode(node, nodeData, dataAction) {
            var $this = this;

            //default switcher
            const defaultNode = dataAction && dataAction?.switcherName && dataAction?.isBetaRootNode
                                    ? node.children().find(child => child.get('name') === dataAction.switcherName)
                                    : node.children()[0];

            nodeData.name = this._getPersonalizedName(node.get('name'));
            nodeData.personalized = {
                type: 'switcher',
                config: {
                    image_mode: 0,
                    title: this._getPersonalizedName(node.get('name')),
                    options: {},
                    require: 1,
                    description: ''
                }
            };

            const default_option_key = $.makeUniqid();
            nodeData.personalized.config.default_option_key = default_option_key;
            nodeData.personalized.config.options[default_option_key] = {
                label: defaultNode.get('name').trim(),
                image: '',
                objects: []
            };

            if (this._findThumbnailImageInSwitcher(defaultNode, nodeData.personalized.config.options[default_option_key])) {
                nodeData.personalized.config.image_mode = 1;
            }

            // option switcher
            let switchers = node.children().slice(1, node.children().length);

            if (switchers.length > 0 && (!Object.keys(dataAction).length || dataAction?.type === 'folderSwitcher' || dataAction?.isBetaRootNode === false)) {
                // other switchers
                for (let switcher of switchers) {
                    let key = $.makeUniqid();
                    const objects = []

                    switcher.children().map((node) => {
                        let parse_data = $this._parseDataFromPSD(node, dataAction);
                        if (parse_data) {
                            objects.push(parse_data);
                        }
                    });

                    nodeData.personalized.config.options[key] = {
                        label: switcher.get('name').trim(),
                        image: '',
                        objects
                    };

                    if (this._findThumbnailImageInSwitcher(switcher, nodeData.personalized.config.options[key])) {
                        nodeData.personalized.config.image_mode = 1;
                    }
                    this.ignoreNode.push(switcher.path());
                }
            }

            let childrenData = [];
            defaultNode.children().map((node) => {
                let parse_data = this._parseDataFromPSD(node, dataAction);
                if (parse_data) {
                    childrenData.push(parse_data);
                }
            });

            nodeData.type_data.children = childrenData;

            return nodeData;
        }

        _parseImageSelectorNode(node, nodeData) {
            let defaultNode;

            nodeData.name = this._getPersonalizedName(node.get('name'));
            nodeData.type = 'image';
            nodeData.type_data.rotation = 0;
            nodeData.personalized = {
                type: 'imageSelector',
                config: {
                    title: this._getPersonalizedName(node.get('name')),
                    description: '',
                    require: 1,
                    groups: {}
                }
            };

            if (node.children()[0].isLayer()) {     // only one group image selector
                const groupData = this._parseGroupImageSelectorNode(node);
                const group_key = $.makeUniqid();
                const default_selector_key = Object.keys(groupData.images || {})[0];

                defaultNode = node.children()[0];
                nodeData.personalized.config.groups[group_key] = groupData;
                nodeData.personalized.config.default_key = default_selector_key;
                nodeData.personalized.config.groups[group_key].images[default_selector_key].data = [];
            } else {                                // two more group image selector
                defaultNode = node.children()[0].children()[0];
                for (let i = 0; i < node.children().length; i++) {
                    const groupData = this._parseGroupImageSelectorNode(node.children()[i]);
                    const group_key = $.makeUniqid();
                    const default_selector_key = Object.keys(groupData.images || {})[0];

                    nodeData.personalized.config.groups[group_key] = groupData;
                    if (i == 0) {
                        nodeData.personalized.config.default_key = default_selector_key;
                        nodeData.personalized.config.groups[group_key].images[default_selector_key].data = [];
                    }
                }
            }

            nodeData.type_data.position = {
                x: defaultNode.get('coords').left,
                y: defaultNode.get('coords').top
            }

            this.imageBase64MainData.push(defaultNode.layer.image.toBase64());
            this.imageNode.push(nodeData);

            return nodeData;
        }

        _parseGroupImageSelectorNode(node) {
            let groupData = {
                label: node.get('name').replaceAll('*', '').trim(),
                images: {}
            };

            const imageSelectors = node.children();
            if (imageSelectors.length) {
                for (let i = 0; i < imageSelectors.length; i++) {
                    const imageSelector = imageSelectors[i];
                    const key = $.makeUniqid();
                    const image = {
                        label: `${groupData.label} #${imageSelector.get('name').trim()}`,
                        data: {
                            type_data : {
                                position: {
                                    x: imageSelector.get('coords').left,
                                    y: imageSelector.get('coords').top
                                },
                                rotation: 0
                            },
                        }
                    };

                    if (this.isUpdatePSD) {
                        image.data.ancestors = [];
                        image.data.type_data.id = getUniqId();
                    }

                    groupData.images[key] = image;
                    this.imageNode.push(image.data);
                    this.imageBase64MainData.push(imageSelector.layer.image.toBase64());
                    this.ignoreNode.push(imageSelector.path());
                }
            }

            return groupData;
        }

        _objectHasPersonalized(key) {
            let $this = this;
            const object = $this._objects[key]?.data
            if (object.hasOwnProperty('personalized')) {
                return true
            }
            const type_data = object.type_data
            if (type_data.hasOwnProperty('children')) {
                const children = type_data.children
                for (let i = 0; i < children.length; i++) {
                    if ($this._objectHasPersonalized(children[i])) {
                        return true
                    }
                }
            }

            return false
        }

        async _uploadImageToBackendAndBuild() {
            this._isShowProgressBar(true);
            this.countImageUpload = this.imageBase64MainData.length + this.imageBase64ThumbnailData.length;

            let sizeOfChunk = 50;
            let folderId = getUniqId();
            let chunks = Math.floor(this.imageBase64MainData.length / sizeOfChunk);
            let thumbChunks = Math.floor(this.imageBase64ThumbnailData.length / sizeOfChunk);

            this.folderIdToUploadS3.push(folderId);
            $('#folderId').val(this.folderIdToUploadS3);

            // post main image to backend
            for (let i = 0; i <= chunks; i++) {
                let imagesChunk = {};
                for (let j = 0; j < sizeOfChunk; j++) {
                    let index = i * sizeOfChunk + j;
                    if (index < this.imageBase64MainData.length) {
                        imagesChunk[index] = this.imageBase64MainData[index];
                    }
                }

                if (Object.keys(imagesChunk).length) {
                    await this._callAjaxToPostImage(imagesChunk, folderId)
                        .then((response) => {

                        }, async (err) => {

                        });
                }
            }

            // post thumbnail image to backend
            for (let i = 0; i <= thumbChunks; i++) {
                let imagesChunk = {};
                for (let j = 0; j < sizeOfChunk; j++) {
                    let index = i * sizeOfChunk + j;
                    if (index < this.imageBase64ThumbnailData.length) {
                        imagesChunk[index] = this.imageBase64ThumbnailData[index];
                    }
                }

                if (Object.keys(imagesChunk).length) {
                    await this._callAjaxToPostThumbnailImage(imagesChunk, folderId)
                        .then((response) => {

                        }, async (err) => {

                        });
                }
            }

            if (!this.isUpdatePSD) {
                try {
                    this._isShowProgressBar(false);
                    await this.setData(this.dataPSD, false);
                } catch (e) {

                }
            }

            toastr.success('Upload PSD file successfully!');

            this._isShowProgressBar(false);

            console.log("============== Parse PSD Done ! ================");
        }

        async _callAjaxToPostImage(imagesChunk, folderId) {
            var $this = this;

            let formData = new FormData();
            let indexList = [];

            for (const [index, imageBase64] of Object.entries(imagesChunk)) {
                const blob = $this._base64ToBlob(imageBase64, 'image/png');
                formData.append(`image${index}`, blob);
                indexList.push(index);
            }
            formData.append("index", indexList);
            formData.append("folderId", folderId);

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: $this._container.attr('data-upload-psd-url'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false
                }).done((response) => {
                    try {
                        if (response.result === 'ERROR') {
                            alert(response.message);
                            $this._isShowProgressBar(false);
                            throw ('Something \'s wrong');
                        } else {
                            let datas = response.data.data;

                            for (const data of datas) {
                                let node = $this.imageNode[data.index];
                                console.log('Upload Done: ', node.name,' -- ', parseInt(data.index) + 1, ' / ', $this.imageBase64MainData.length);

                                // set progress bar in UI
                                let percentUpload = (++this.countImageUploadDone / this.countImageUpload) * 100;
                                $('#progress-bar').css('width', Math.round(percentUpload) + '%');
                                $('#label-progress').text(`Upload image: ${this.countImageUploadDone}/${this.countImageUpload}`);

                                let imageInfor = {
                                    x: node.type_data.position.x,
                                    y: node.type_data.position.y,
                                    width: data.width,
                                    height: data.height
                                }

                                const {x, y, width, height} = $this._getRectFitBgBox(imageInfor);
                                node.type_data.url = data.url;

                                node.type_data.size = {
                                    width,
                                    height
                                }

                                node.type_data.position = {
                                    x,
                                    y
                                }

                                node.type_data.original_size = {
                                    width: data.width ,
                                    height: data.height
                                }

                                if ($this.isUpdatePSD && !node.exeDone) {
                                    if (node.ancestors?.length == 0) { // default node
                                        if (node.action?.action === 'update' && node.action?.type === 'image') {
                                            const bounding_rect = {
                                                x: node.type_data.position.x,
                                                y: node.type_data.position.y,
                                                width: node.type_data.size.width,
                                                height: node.type_data.size.height
                                            }
                                            $($this._objects[node.key].elm).triggerHandler('move', [bounding_rect]);
                                            $($this._objects[node.key].elm).triggerHandler('resize', [bounding_rect]);
                                            // $this._objectRemove(node.key);
                                        }

                                        $this._image_data[node.type_data.id] = node.type_data;
                                        $this._links_map[data.url] = node.type_data.id;

                                        if ((node.action?.action === 'create' && node.action?.type === 'image') || node.action?.type === 'imageSelector') { //image
                                            let group = node.parentKey ? $this._objects[node.parentKey] : null;
                                            $this._setDataMakeObject([node], group);
                                        }
                                    } else if (node.ancestors?.length > 0) { //  add image to switcher and update image selector (both default node)
                                        const key = node.ancestors?.find(key => Object.keys($this._objects).includes(key));

                                        if (key) {
                                            let object = $this._findObjectByKeyInGlobalObjects(node.ancestors, $this._objects[key].data);
                                            if (Array.isArray(object)) { //create
                                                delete node.ancestors;
                                                delete node.parentKey;
                                                delete node.action;
                                                delete node.type_data.id;

                                                node.type_data.id = $this._addToImageData(node.type_data);
                                                object.push(node);
                                            } else if (node.action?.action === 'update' && node.action?.type === 'imageSelector') {
                                                // object.type_data.url = data.url;
                                                // object.type_data.id = node.type_data.id;
                                                if (node.IsAddImageSelector) { // add image selector
                                                    const _key = $.makeUniqid();
                                                    const id = getUniqId();

                                                    object[_key] = {
                                                        label: node.name,
                                                        data: {
                                                            type_data: {
                                                                id,
                                                                size: {
                                                                    width,
                                                                    height
                                                                },
                                                                position: {
                                                                    x,
                                                                    y
                                                                },
                                                                rotation: 0
                                                            }
                                                        }
                                                    }

                                                    $this._image_data[id] = node.type_data;
                                                    $this._links_map[data.url] = id;
                                                } else { // update image selector (both default and option switcher)
                                                    $this._image_data[object.type_data.id] = node.type_data;
                                                    $this._links_map[data.url] = object.type_data.id;
                                                    object.type_data.position = {
                                                        x,
                                                        y
                                                    }
                                                    object.type_data.size = {
                                                        width,
                                                        height
                                                    }
                                                }

                                            } else { // update normal image
                                                object.type_data.url = data.url;
                                                $this._image_data[object.type_data.id] = node.type_data;
                                                $this._links_map[data.url] = object.type_data.id;

                                                // fix case: update image in option switcher
                                                object.type_data.position = {
                                                    x: x,
                                                    y: y,
                                                    // x,
                                                    // y
                                                }

                                                object.type_data.size = {
                                                    width: width,
                                                    height: height,
                                                    // width,
                                                    // height
                                                }
                                            }
                                        }

                                    } else if (node.IsAddGroupImageSelector) {
                                        node.type_data.url = data.url;
                                        $this._image_data[node.type_data.id] = node.type_data;
                                        $this._links_map[data.url] = node.type_data.id;
                                    }
                                } else if (node.exeDone) {
                                    if (node.inPersonalizedFlag) {
                                        // fix because case: add new switcher (contain a image selector, so image selector default is wrong) in option switcher
                                        if (!!node.personalized && node.personalized.type === 'imageSelector') {
                                            // fix because case: add new folder (contain a image selector, so image selector default is wrong) in option switcher
                                            const key = node.ancestors?.find(key => Object.keys($this._objects).includes(key));
                                            if (key) {
                                                $this._findObjectByKeyInGlobalObjects(node.ancestors, $this._objects[key].data); // run to get this.parentRation;
                                            }
                                        }
                                    }
                                    node.type_data.id = $this._addToImageData(node.type_data);
                                    $this._image_data[node.type_data.id] = node.type_data;
                                    $this._links_map[node.url] = node.type_data.id;
                                    delete node.inPersonalizedFlag;
                                    delete node.ancestors;
                                    delete node.exeDone;
                                }

                                resolve(response);
                            }
                        }
                    } catch (e) {
                        alert('Something \'s wrong when upload Image');
                        $this._isShowProgressBar(false);
                        throw (e);
                    }
                }).fail(async (err) => {
                    console.log(err);

                    reject(err);
                });
            })
        }

        async _callAjaxToPostThumbnailImage(imagesChunk, folderId) {
            var $this = this;

            let formData = new FormData();
            let indexList = [];

            for (const [index, imageBase64] of Object.entries(imagesChunk)) {
                const blob = $this._base64ToBlob(imageBase64, 'image/png');
                formData.append(`image${index}`, blob);
                indexList.push(index);
            }
            formData.append("index", indexList);
            formData.append("folderId", folderId);

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: $this._container.attr('data-upload-thumb-psd-url'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false
                }).done((response) => {
                    try {
                        if (response.result === 'ERROR') {
                            alert(response.message);
                            $this._isShowProgressBar(false);
                            throw ('Something \'s wrong');
                        } else {
                            let datas = response.data.data;

                            for (const data of datas) {
                                let imageData = $this.imageThumbnail[data.index];

                                console.log("Upload thumbnail Done: ", parseInt(data.index) + 1, ' / ', $this.imageBase64ThumbnailData.length);
                                // set progress bar in UI
                                let percentUpload = (++this.countImageUploadDone / this.countImageUpload) * 100;
                                $('#progress-bar').css('width', Math.round(percentUpload) + '%');
                                $('#label-progress').text(`Upload image: ${this.countImageUploadDone}/${this.countImageUpload}`);

                                $this.imageThumbnail[data.index].image = data.url;

                                if ($this.isUpdatePSD) {
                                    $this._objectUnselectCurrent();

                                    if (imageData.ancestors?.length === 0) { // thumbnail in default_key
                                        let default_key = $this._objects[imageData.parentKey].data.personalized.config.default_option_key;
                                        let object = $this._objects[imageData.parentKey].data.personalized.config.options[default_key];
                                        $this._objects[imageData.parentKey].data.personalized.config.image_mode = 1;
                                        object.image = data.url;

                                    } else if (imageData.ancestors?.length > 0) { // thumbnail in options key
                                        const key = imageData.ancestors.find(key => Object.keys($this._objects).includes(key));

                                        if (key) {
                                            let object = $this._findObjectByKeyInGlobalObjects(imageData.ancestors, $this._objects[key].data, true);
                                            object.image = data.url;
                                        }
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        alert('Something \'s wrong when upload thumbnail Image');
                        $this._isShowProgressBar(false);
                        throw (e);
                    }
                    resolve(response);
                }).fail(async (err) => {
                    console.log(err);

                    reject(err);
                });
            })
        }

        async renderFilePSD(psd) {

            const $this = this;

            this.treePSD = psd.tree();

            this.countImageUpload = 0;
            this.countImageUploadDone = 0;

            this.imageBase64MainData = [];
            this.imageNode = [];

            this.imageBase64ThumbnailData = [];
            this.imageThumbnail = [];

            this.dataImageResponse = [];
            this.ratioPSD = 1;

            this.ignoreNode = [];
            this.object_index_PSD = 0;
            this.newFolderNode = [];

            this.dataPSD = {
                bbox: {},
                document: {
                    type: this.document_type.key,
                    width: this.document_type.width,
                    height: this.document_type.height,
                    ratio: this._zoom_ratio
                },
                image_data: {},
                objects: []
            };

            let ratioPSD = psd.header.rows / psd.header.cols;
            let ratioProductType = this.dataPSD.document.height / this.dataPSD.document.width;

            if (ratioPSD.toFixed(2) != ratioProductType.toFixed(2)) {
                $('.content-wrap > .message-container').remove();
                let suggestMessage = $('<ul />').addClass('message-info message-container');
                let suggestProductType = this._document_types.filter((product_type) => {
                    let ratio = product_type.height / product_type.width;
                    return ratioPSD.toFixed(2) == ratio.toFixed(2);
                });

                this._showPSDError(`PSD canvas dimension width/height must fit to current Product Type: ${this.document_type.title}`, false);
                let suggestTitle = suggestProductType.map((product_type) => {
                    return `[${product_type.title}]`;
                });
                $('<li />').text(`Product Types maybe fit to file PSD: ${suggestTitle.join(', ')}`).appendTo(suggestMessage);
                $('.content-wrap > .header').after(suggestMessage);
                $('#spinnerLoading').hide();

                $.unwrapContent('dropFilePSDModal', true);
                return;
            }

            // calculate ratio to fit background
            this._getRatioFitBg(psd.header.rows, psd.header.cols);

            if (this.isUpdatePSD) {
                // *************** UPDATE BY PSD ********************
                this.tempDataToUpdate = [];     // to link data in design data to update after upload image
                this.nodeToUpdate = [];         // to link node in PSD need to update
                this.notFoundNode = [];         // to link node in PSD not found in design data
                this.dataPSD = JSON.parse(this.getData());

                // const spinnerLoading = $('#spinnerLoading');

                // spinnerLoading.show();
                this._compareStructureDataPSDToUpdate(this.treePSD, this.dataPSD);
                // spinnerLoading.hide();

                this._renderConfirmUpdatePSD();
            } else {
                // ************** CREATE NEW BY PSD *****************
                this.treePSD.children().map((node) => {
                    let parse_data = $this._parseDataFromPSD(node)
                    if (parse_data) {
                        this.dataPSD.objects.push(parse_data);
                    }
                });
                console.log(this.dataPSD);

                await this._uploadImageToBackendAndBuild();
            }

        }

        constructor(container, permissions) {
            const $this = this;

            this._historyAdd =  window.debounce(this._historyAdd, 200)

            this._design_id = 0;

            this._meta_data = [];
            this.folderIdToUploadS3 = [];

            this.isUpdatePSD = false;
            this.uploadByPSD = false;
            this.PSDSize = 0;

            //List các document
            this._document_types = [
                {key: 'mug11oz', title: 'Mug 11oz 15oz TwoTone', width: 2334, height: 991},
                {key: 'enamelcampfinemug10oz', title: 'Enamel Campfine Mug 10oz', width: 2400, height: 750},
                {key: 'insulatedcoffeemug12oz', title: 'Insulated Coffee Mug 12oz', width: 2550, height: 1093},

                {key: 'design2d', title: 'Design for 2D', width: 7800, height: 10000},

                {key: 'canvas_portrait', title: 'Canvas - Portrait', width: 11492, height: 14522},
                {key: 'canvas_landscape', title: 'Canvas - Landscape', width: 14522, height: 11492},
                {key: 'canvas_square', title: 'Canvas - Square', width: 10347, height: 10347},

                {key: 'desktopPlaque_landscape', title: 'Desktop Plaque - Landscape', width: 3075, height: 2475},

                {key: 'fleeceBlanket_landscape', title: 'Fleece Blanket - Landscape', width: 6374, height: 8466},

                {key: 'puzzles_portrait', title: 'Puzzles - Portrait', width: 3450, height: 4395},
                {key: 'puzzles_landscape', title: 'Puzzles - Landscape', width: 4395, height: 3450},

                {key: 'pillow', title: 'Pillow', width: 2925, height: 2925},

                {key: 'notebook', title: 'Notebook', width: 1660, height: 2138},

                {key: 'facemask_dpi', title: 'Facemask DPI', width: 2325, height: 1680}, //1939 x 1349
                {key: 'facemask_cw', title: 'Facemask CW', width: 1275, height: 862}, //1043 x 630

                {key: 'facemask_dpi_kid', title: 'Facemask DPI kid', width: 2154, height: 1389}, //2154 x 1389

                {key: 'facemask_teeallover', title: 'Facemask TeeAllover', width: 1604, height: 972}, //1604 x 972

                {key: 't_shirt', title: 'T Shirt', width: 4200, height: 4800},

                {key: 'aluminium_square_ornament', title: 'Aluminium Square Ornament', width: 999, height: 999},
                {key: 'aluminium_medallion_ornament', title: 'Aluminium Medallion Ornament', width: 1275, height: 956},
                {key: 'aluminium_scalloped_ornament', title: 'Aluminium Scalloped Ornament', width: 1299, height: 924},
                {key: 'aluminium_heart_ornament', title: 'Aluminium Heart Ornament', width: 1110, height: 1110},
                {key: 'aluminium_circle_ornament', title: 'Aluminium  Circle Ornament', width: 1110, height: 1110},
                {key: 'heart_ornament', title: 'Heart Ornament', width: 1036, height: 970},
                {key: 'circle_ornament', title: 'Circle Ornament', width: 991, height: 991},
                {key: 'acrylic_medallion_ornament', title: 'Acrylic Medallion Ornament', width: 1275, height: 956},

                {key: 'yard_sign', title: 'Yard Sign', width: 7240, height: 5440},
                {key: 'garden_flag', title: 'Garden Flag', width:4052, height:5604},

                {key: 'poster_landscape', title: 'poster landscape (max 24x20)', width:6000, height:5000},
                {key: 'poster_portrait', title: 'poster portrait (max 20x24)', width:5000, height:6000},

                {key: 'necklace', title: 'Necklace', width:1000, height:1000},


                {key: 'iphone_case_7', title: 'iphone case 7 8 7plus 8plus', width: 879, height: 1830},
                {key: 'iphone_case_x', title: 'iphone case X XR XS XSmax', width: 879, height: 1830},
                {key: 'iphone_case_12', title: 'iphone case 11 11pro 11promax 12 12mini 12pro 12promax', width: 879, height: 1830},
                {key: 'samsung_case_s10', title: 'samsung case s10Plus', width: 879, height: 1830},
                {key: 'samsung_case_s20', title: 'samsung case s20 s20+ s20Ultra s21 s21+ s21Ultra', width: 937, height: 1950},

                {key: 'beach_towel', title: 'Beach Towel', width:11220, height:19448},
                {key: 'tea_towel', title: 'Tea Towel', width:5374, height:8102},

                {key: 'tumbler_stainless', title: 'Stainless Tumbler', width:3260, height:1838},
                {key: 'tumbler_skinny', title: 'Skinny Tumbler', width:2785, height:2421},

                {key: 'doorMat_24x16', title: 'DoorMat 24x16', width:7200, height:4800},
                {key: 'doorMat_30x18', title: 'DoorMat 30x18', width:9000, height:5400},
                {key: 'key_chain_29x51', title: 'KeyChain 29x51', width:354 , height:626},
                {key: 'key_chain_51x29', title: 'KeyChain 51x29', width:626 , height:354},

                {key: 'hoodie_14x10', title: 'Hoodie 14x10', width: 4200, height: 3000},

                {key: 'watchband_38mm_42mm', title: 'Watchband 38mm 42mm', width: 505, height: 2931},
                {key: 'watchband_38mm_42mm_horizontal', title: 'Watchband 38mm 42mm horizontal', width: 2931, height: 505},


                {key: 'desktop_plaque_acrylic_heart', title: 'Desktop plaque acrylic heart', width: 1532, height: 1532},
                {key: 'desktop_plaque_acrylic_infinity', title: 'Desktop plaque acrylic infinity', width: 1848, height: 1772},
                {key: 'desktop_plaque_acrylic_puzzle', title: 'Desktop plaque acrylic puzzle', width: 2433, height: 2354},
                {key: 'desktop_plaque_wooden_puzzle', title: 'Desktop plaque wooden puzzle', width: 2440, height: 2362},

                {key: 'key_chain_30x60', title: 'Keychain 30x60', width: 1900, height: 3815},

                {key: 'flat_card_5x7', title: 'FlatCard 5x7', width: 2175, height: 1538},
                {key: 'flat_card_7x5', title: 'FlatCard 7x5', width: 1538, height: 2175},
                {key: 'wineGlassMug15oz', title: 'Wine Glass Mug 15oz', width: 900, height: 650}
            ];

            //UNUSED
            this._document_type = null;

            this._zoom_ratio = null;
            this._tmp_timer = null;

            //Load history data
            this._history_data = [];
            this._history_idx = -1;
            this._history_locked = false;

            this._active_command = "";

            this._is_append_data = false;
            this._is_batch_remove = false;
            this._is_show_line_helper = true;

            this._container = container;
            this._thumb_panel = null;
            this._object_thumb_list = null;
            this._top_bar = null;
            this._toolbar = null;
            this._toolbar_command_container = null;
            this._object_index = -1;
            this._objects = {};
            this._callback = {
                object_render: null,
                object_remove: null,
                finish_mask: null,
                remove: null
            };
            this._fonts = {
                '6fbda3a3567da6d01bc9da915e91d702': {font_name: 'Arial'},
                '2bd141ae2a8e92e3cdd9163089ec8924': {font_name: 'Tahoma'}
            };
            this._last_selected_object = null;
            this._loaded_fonts = [];

            //IMAGE DATA
            this._image_data = {};
            //Map của url => id dùng để tìm được url image bị trùng nhanh nhất để tối ưu performance
            this._links_map = {};

            //PERMISSIONS chức năng
            this._permissions = permissions || { "edit_layer":true, "remove_layer":true}

            this._container.html('');

            this._container.addClass('personalized-design-builder');

            //SET UP CONTAINER OF SVG
            this._svg = $(document.createElementNS('http://www.w3.org/2000/svg', 'svg')).appendTo(this._container);

            this._svg_size = {
                w: this._container.outerWidth(),
                h: this._container.outerHeight()
            }

            this._svg[0].setAttribute('viewBox', '0 0 ' + this._container.outerWidth() + ' ' + this._container.outerHeight());


            this._svg_group = {
                defs: $(document.createElementNS(this._svg[0].namespaceURI, 'defs')).attr('data-name', 'defs').appendTo(this._svg),
                object: $(document.createElementNS(this._svg[0].namespaceURI, 'g')).attr('data-name', 'objects').appendTo(this._svg),
                helper: $(document.createElementNS(this._svg[0].namespaceURI, 'g')).attr('data-name', 'helpers').appendTo(this._svg),
                lineHelper: $(document.createElementNS(this._svg[0].namespaceURI, 'g')).attr('data-name', 'lines-helper').appendTo(this._svg)
            };


            new ResizeSensor(this._container[0], function () {
                // var viewbox = $this._viewBox();
                //IF RESIZE WINDOW => CHANGE VIEW BOX
                // $this._svg[0].setAttribute('viewBox', '0 0' + $this._container.outerWidth() + ' ' + $this._container.outerHeight());
                $this._svg_size = {
                    w: $this._container.outerWidth(),
                    h: $this._container.outerHeight()
                }
            });

            this._frame = null;

            //IN TOP BAR
            this._config_panel = null;

            //SET UP RENDER UI
            this._renderTopBar();
            this._renderBottomBar();
            this._renderToolbar();
            this._renderThumbPanel();

            this._svg.mousedown(function (e) {
                if (e.target === this || e.target === $this._frame[0] || $this._objectIsAncestor(e.target, $this._frame[0])) {
                    $this._objectUnselectCurrent();
                    return;
                }

                if ($this._objectIsAncestor(e.target, this)) {
                    var object_key = $(e.target).closest('[data-object]').attr('data-object');

                    if (object_key && $this._objects[object_key]) {
                        $this._objectSelect($this._objects[object_key]);
                    }
                }
            });
            $(container).attr('tabindex', '-1')
            this._setupKeyboardControl();

            //Config toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": false,
                "positionClass": "toast-top-center",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        }
    }

    window.personalizedDesignBuilderInit = function () {
        const frm = $(this).closest('form');
        const permissions = JSON.parse(frm.find('script[data-json="personalized_design-permission_edit_lock"]')[0].innerHTML);

        const builder = new personalizedDesignBuilder($(this), permissions);
        window.builder = builder;

        frm.force_submit = false;

        frm.submit(function (e) {
            if (!frm.force_submit) {
                e.preventDefault();
                return;
            }
        })

        frm.find('.btn-submit').on('click',function (e) {
            //Check if has blend mode alert and return;
            const blendModes = builder._findAllBlendModeLayers();
            if(blendModes.length > 0) {
                alert('Please remove all applied blend mode layers before save!');
                return;
            }
            //End check

            const layers = builder._findAllLayers()
            const list_layers = layers.list_layers
            const key_layer_not_renders = layers.key_layer_not_renders

            let object_has_personalized = false
            let index_layer_not_render_personalized = -1
            for (let i = 0; i< list_layers.length; i ++) {
                if (key_layer_not_renders.includes(list_layers[i].key) && list_layers[i].is_personalized) {
                    index_layer_not_render_personalized = i
                    object_has_personalized = true
                    break
                }
            }
            if (object_has_personalized) {
                alert('Cannot apply "Not Render Design" with personalized object' + "\n" + list_layers[index_layer_not_render_personalized].stack.join(' -> '));
                return;
            }

            const title = frm.find('input[name="title"]').val();
            if (!title) {
                alert('Title Design is empty');
                return;
            }

            const data = builder.getData();
            if (!data) {
                alert('Design is empty');
                return;
            }

            frm.find('input[name="design_data"]').val(data);

            builder._renderPopupConfirmActionLayer(frm, $(this), layers);
        });

        frm.find('#input-title').keydown(function (e) {

        });

        var data = frm.find('input[name="design_data"]').val();
        var meta_data = frm.find('input[name="meta_data"]').val();
        var design_id = parseInt(frm.attr('data-design'));

        meta_data = JSON.parse(meta_data);
        builder.setMetaData(meta_data);
        builder.setDesignId(design_id);

        if (isNaN(design_id)) {
            design_id = 0;
        }

        var draft_content = JSON.parse(frm.find('script[data-json="personalized_design-draft_content"]')[0].innerHTML);

        if (draft_content.content !== null && typeof draft_content.content === 'object' && permissions.edit_layer) {
            if (window.confirm('You have a temporary saved data, do you want to restore?')) {
                data = JSON.stringify(draft_content.content);
                meta_data = draft_content.meta_data;
                builder.setMetaData(meta_data);
            }
        }

        data = JSON.parse(data);

        if (data !== null && typeof data === 'object' && Array.isArray(data.objects)) {
            builder.setData(data, true);
            builder._historyAdd(true);
        }

        $('#upload-psd-btn').on('click', () => {

            const handleEventToPSD = (files) => {
                var fileName = files[0].name.split('.');

                if (fileName[fileName.length - 1] == 'psd' && files.length == 1) {
                    builder.PSDSize = files[0].size;
                    if (builder.getData()) {
                        builder.isUpdatePSD = window.confirm('Do you want to update current Design ?');
                    }

                    if (builder.isUpdatePSD || builder.getData() == null) {
                        if (!builder.isUpdatePSD) {
                            $.unwrapContent('dropFilePSDModal', true);
                            builder._isShowProgressBar(true);
                        } else {
                            $('#spinnerLoading').show();
                        }

                        try {
                            PSD.fromDroppedFile(files[0]).then((psd) => {
                                builder.uploadByPSD = true;
                                builder.renderFilePSD(psd);
                            })
                        } catch (error) {
                            builder._isShowProgressBar(false);
                            alert(error.message);
                            throw(error)
                        }

                    }
                } else {
                    if (fileName[fileName.length - 1] !== 'psd') {
                        toastr.error('File have to be PSD file');
                    }
                    if (evt.dataTransfer.files.length > 1) {
                        toastr.error('Please choose just only 1 file.');
                    }
                }
            }

            $.unwrapContent('dropFilePSDModal');

            var modal = $('<div />').addClass('osc-modal').width(700);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Drop file PSD to create/update design').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('dropFilePSDModal', true);
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            $('<p />')
                .addClass('')
                .css({
                    'margin-top': '0px',
                    'padding-right': '30px',
                    'text-align': 'end'
                })
                .append(
                    $('<a />').text('User guide').attr(
                        {
                            'href': 'https://docs.google.com/presentation/d/1jEH6KxjzOXHyEXpnZsa5TM24cFg7JVkNcH3G50IaDcI/edit#slide=id.g742e3e7cd_1_16',
                            'target': '_blank',
                        }
                    )
                    .css('text-decoration', 'underline')
                )
                .appendTo(modal_body);

            var document_type_selector = $("<select />")
                .css({
                    'margin-left': '30px',
                    'width': '600px',
                    'height': '35px',
                    'padding-left': '10px',
                    'border-radius': '3px',
                }).appendTo(
                    $("<div />")
                        .addClass("selector")
                        .appendTo(modal_body)
                ).on('change', function() {
                    builder.document_type = builder._document_types[$(this).val()];
                });

            builder._document_types.forEach(function (document_type, index) {
                $("<option />")
                    .attr("value", index)
                    .text(document_type.title)
                    .appendTo(document_type_selector);

                if (document_type.key === builder.document_type.key) {
                    document_type_selector.val(index);
                }
            });

            $('<input />')
                .attr('id', 'uploadFilePSDInput')
                .attr('type', 'file')
                .css('display', 'none')
                .on('change', (evt) => {
                    handleEventToPSD(evt.target.files);
                })
                .appendTo(modal_body);

            $('<div />')
                .attr('id', 'drop-psd')
                .css({
                    'width': '600px',
                    'height': '100px',
                    'border': '1px grey dashed',
                    'margin': '10px auto'
                })
                .append(
                    $('<label />')
                    .attr('for', 'uploadFilePSDInput')
                    .css({
                        'text-align': 'center',
                        'font-weight': 'bold',
                        'line-height': '100px',
                        'margin': '0',
                        'padding': '0',
                    }).text('Choose/drop file here')
                )
                .on('dragover', (evt) => {
                evt.stopPropagation();
                evt.preventDefault();
                evt.dataTransfer.dropEffect = 'copy';
            })
            .on('drop', (evt) => {
                evt.stopPropagation();
                evt.preventDefault();
                $('.osc-uploader').removeClass('dragdrop-active');
                handleEventToPSD(evt.dataTransfer.files);
            })
            .appendTo(modal_body);

            $('<div />').attr('id', 'spinnerLoading')
                .addClass('ld-ring')
                .hide()
                .append($('<div/>'))
                .append($('<div/>'))
                .append($('<div/>'))
                .append($('<div/>'))
                .appendTo(modal_body);

            $('<div />').attr('id', 'updatePSDCheckboxGroup').css('display', 'none')
                .appendTo(modal_body);
            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').attr('id', 'confirmBtn').addClass('btn btn-primary mr5').hide().html('Confirm').click(function () {
                builder._confirmUpdatePSD();
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-secondary').html('Cancel').click(function () {
                $.unwrapContent('dropFilePSDModal', true);
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'dropFilePSDModal'});

            modal.moveToCenter().css('top', '100px');
        });
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

    window.isVisible = function(ele, container) {
        const { bottom, height, top } = ele.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        return top <= containerRect.top
            ? (containerRect.top - top <= height)
            : (bottom - containerRect.bottom <= height);
    };

})(jQuery);
