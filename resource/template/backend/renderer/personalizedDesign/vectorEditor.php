<?php
/* @var $this Helper_Backend_Template */

$this->push('[core]vectorEditor/vectorEditor.js', 'js')
        ->push('var OSC_VECTOR_EDITOR_ROOT_PATH = "' . OSC::core('template')->getFile('script/vectorEditor') . '";', 'js_init')
        ->push('[core]vectorEditor/vectorEditor.scss', 'css')
        ->push('$("#vectorEditor").osc_vectorEditor()', 'js_code')
        ->addComponent('colorPicker');
?>
<div class="post-frm p25">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block mt10">
                <div class="p20">
                    <div class="frm-grid">
                        <div>
                            <div class="customizable-container" id="vectorEditor">
                                <div class="config-panel"></div>
                                <div class="tool-panel">
                                    <div data-cmd="rect">
                                        <svg data-icon="osc-vector-rect" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-vector-rect"></use></svg>
                                    </div>
                                    <div data-cmd="ellipse">
                                        <svg data-icon="osc-vector-circle" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-vector-circle"></use></svg>
                                    </div>
                                    <div data-cmd="pencil">
                                        <svg data-icon="osc-pencil-alt-light" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-pencil-alt-light"></use></svg>
                                    </div>
                                    <div data-cmd="pen">
                                        <svg data-icon="osc-pen-nib" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-pen-nib"></use></svg>
                                    </div>
                                    <div class="vector-add-point" data-cmd="penAddPoint">
                                        <svg data-icon="osc-plus-light" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-plus-light"></use></svg>
                                        <svg data-icon="osc-pen-nib-light" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-pen-nib-light"></use></svg>
                                    </div>
                                    <div class="vector-remove-point" data-cmd="penRemovePoint">
                                        <svg data-icon="osc-minus-light" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-minus-light"></use></svg>
                                        <svg data-icon="osc-pen-nib-light" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-pen-nib-light"></use></svg>
                                    </div>
                                    <div data-cmd="penEditPoint">
                                        <svg data-icon="osc-vector-edit" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-vector-edit"></use></svg>
                                    </div>
                                    <div data-cmd="text">
                                        <svg data-icon="osc-text" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-text"></use></svg>
                                    </div>
                                    <div>
                                        <div class="layer-image-uploader" data-cmd="image"></div>
                                    </div>
                                    <div data-cmd="mask">
                                        <svg data-icon="osc-vector-mask" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-vector-mask"></use></svg>
                                    </div>
                                    <div data-cmd="trash">
                                        <svg data-icon="osc-trash" viewBox="0 0 512 512" data-insert-cb="configOSCIcon"><use xlink:href="#osc-trash"></use></svg>
                                    </div>
                                    <div class="colors">
                                        <div class="fill no-color" data-cmd="fill"></div>
                                        <div class="stroke" data-cmd="stroke"></div>
                                    </div>
                                </div>
                                <div class="canvas-container">
                                    <svg class="screen" viewBox="0 0 750 750" xmlns="http://www.w3.org/2000/svg">
                                    <defs xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                    <filter xmlns="http://www.w3.org/2000/svg" id="bg-dropshadow" height="130%">
                                        <feGaussianBlur in="SourceAlpha" stdDeviation="3"/> 
                                        <feOffset dx="2" dy="2" result="offsetblur"/>
                                        <feComponentTransfer>
                                            <feFuncA type="linear" slope="0.2"/>
                                        </feComponentTransfer>
                                        <feMerge> 
                                            <feMergeNode/>
                                            <feMergeNode in="SourceGraphic"/> 
                                        </feMerge>
                                    </filter>
                                    </g>
                                    </defs>                    
                                    </svg>
                                </div>
                                <div class="layer-list"></div>
                            </div>       
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>