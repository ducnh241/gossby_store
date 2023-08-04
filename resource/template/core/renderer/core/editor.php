<?php
/* @var $this OSC_Template */

$this->push([
            '[core]editor/editor.js',
            '[core]editor/plugin/embed_block.js',
            '[core]editor/plugin/block_image.js',
            '[core]editor/plugin/autosize_textbox.js',
            '[core]editor/plugin/textbox.js',
            '[core]editor/plugin/textColor.js',
            '[core]editor/plugin/highlight.js'
                ], 'js')
        ->push([
            '[core]editor/plugin/block_image/editor.css',
                ], 'css')
        ->push(<<<EOF
var OSC_EDITOR_ROOT_PATH = '{$this->_tpl_base_url}/script/editor';
EOF
, 'js_init');
