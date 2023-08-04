(function ($) {
    'use strict';

    window.osc_editor_plugin_highlight = function (config) {

        if (typeof config !== 'object' || config === null) {
            config = {};
        } else {
            for (var x in config) {
                if (x.indexOf('highlight') !== 0) {
                    delete(config[x]);
                }
            }
        }

        return {
            initialize: function () {
                $.extend(this, config);
                this._commands.highlight = {cmd: 'highlight', context: 'highlight', icon: 'pen', initialize: '_initCommandHighlight'}
            },
            extends: {
                _highlight_preview: [],
                _initCommandHighlight: function (node) {
                    if (typeof this._node_allowed_style.span === 'undefined') {
                        this._node_allowed_style.span = [];
                    }

                    this._node_allowed_style.span.push('background-color');

                    var self = this;

                    node.className = node.className + ' osc-editor__highlight';
                    this._highlight_preview.push($("<span/>").addClass('osc-editor__color-preview').appendTo(node));

                    $(node).osc_colorPicker({
                        swatch_name: 'osc_editor_highlight',
                        callback: function (color) {
                            self._checkFocus();
                            self._commandHighlight(self._getHighlightColor() === color ? 'transparent' : color);
                        }
                    });
                },
                _commandHighlight: function (color) {
                    if (!color) {
                        return;
                    }

                    this._execStandardCommand('hiliteColor', false, color);
                },
                _getHighlightColor: function () {
                    var ancestor = this._getSelectionCommonAncestor();

                    if (!ancestor || this._nodeIsBlock(ancestor)) {
                        return null;
                    }

                    var self = this;

                    var color = null;

                    this._getParentNode(ancestor, 'span', function (node) {
                        color = node.getParsedCssText()['background-color'];
                        return color;
                    }, null, function (node) {
                        return self._nodeIsBlock(node);
                    });

                    return color;
                },

                _contextHighlight: function () {
                    var color = this._getHighlightColor();

                    for (var i = 0; i < this._highlight_preview.length; i++) {
                        this._highlight_preview[i].css('background', color ? color : '#333');
                    }

                    return color ? true : false;
                }
            }
        };
    };
})(jQuery);


