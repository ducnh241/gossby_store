(function ($) {
    'use strict';

    function _renderBulkFrm(frm_title, action_title, btn_action_title, in_search, option_frm_callback, process_callback) {
        $.unwrapContent('catalogProductBulkFrm');

        var modal = $('<div />').addClass('osc-modal').width(350);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(frm_title).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogProductBulkFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        $('<div />').text(action_title).appendTo(modal_body);

        let row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'search', id: 'bulk_condition__search'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__search').addClass('label-inline').text('Current search').appendTo(row);

        row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'selected', id: 'bulk_condition__selected'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__selected').addClass('label-inline').text('Selected products').appendTo(row);

        if (typeof option_frm_callback === 'function') {
            option_frm_callback(modal_body);
        }

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('catalogProductBulkFrm');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html(btn_action_title).click(function () {
            var condition = null;

            $('input[name="bulk_condition"]').each(function () {
                if (this.checked) {
                    condition = this.value;
                    return false;
                }
            });

            if (condition === 'selected') {
                condition = [];

                $('input[name="product_id"]:checked').each(function () {
                    condition.push(this.value);
                });
            }

            process_callback(condition, function (response) {
                alert(response.message);
            }, function (response) {
                $.unwrapContent('catalogProductBulkFrm');
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'catalogProductBulkFrm'});

        modal.moveToCenter().css('top', '100px');

        if (!$('input[name="product_id"]:checked')[0]) {
            $('#bulk_condition__selected').attr('disabled', 'disabled');
        } else {
            $('#bulk_condition__selected')[0].checked = true;
        }

        if (!in_search) {
            $('#bulk_condition__search').attr('disabled', 'disabled');
        }
    }

    window.initCatalogProductBulkDiscardBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrm('Bulk Discard products', 'Discard', 'Discard products', btn.attr('data-search') === '1', function (modal_body) {
            }, function (condition, error_callback, success_callback) {
                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {condition: condition},
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }

                        success_callback(response);

                        window.location.reload();
                    }
                });
            });
        });
    };

    window.initCatalogProductBulkActiveBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrm('Bulk Active products', 'Active', 'Active products', btn.attr('data-search') === '1', function (modal_body) {
            }, function (condition, error_callback, success_callback) {
                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {condition: condition},
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }

                        success_callback(response);

                        window.location.reload();
                    }
                });
            });
        });
    };

    window.initCatalogProductBulkSetListingBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrm('Bulk ' + (btn.attr('data-mode') === '1' ? 'listing' : 'unlisting') + ' products', (btn.attr('data-mode') === '1' ? 'Listing' : 'Unlisting'), (btn.attr('data-mode') === '1' ? 'Listing' : 'Unlisting') + ' products', btn.attr('data-search') === '1', function (modal_body) {
            }, function (condition, error_callback, success_callback) {
                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {condition: condition, mode: btn.attr('data-mode')},
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }

                        success_callback(response);

                        window.location.reload();
                    }
                });
            });
        });
    };


    window.initCatalogProductBulkSetCollectionBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogProductSetCollectionFrm');

            var post_data = {
                condition: null
            };

            if (!$('input[name="product_id"]:checked')[0] && btn.attr('data-search') === '1') {
                post_data.condition = 'search';
            } else if ($('input[name="product_id"]:checked')[0] ) {
                post_data.selected_ids = [];
                post_data.condition = 'select';
                $('input[name="product_id"]:checked').each(function () {
                    post_data.selected_ids.push(this.value);
                });
            }else{
                post_data.condition = 'all';
            }

            post_data.get_data = true;

            post_data.method =  btn.attr('data-mode') == 'add_collection' ? 'add_collection' : 'remove_collection';

            $.ajax({
                url: btn.attr('data-process-url'),
                data: post_data,
                type: 'post',
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    if (btn.attr('data-mode') == 'add_collection') {
                        if (response.data.length < 1){
                            alert("There are no collection to add");
                            return;
                        }
                    }

                    var modal = $('<div />').addClass('osc-modal').width(400);

                    var header = $('<header />').appendTo(modal);

                    $('<div />').addClass('title').html(btn.attr('data-mode') == 'add_collection' ? 'Add Collection' : 'Remove Collection').appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('catalogProductSetCollectionFrm');
                    }).appendTo(header);

                    var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

                    var row = $('<div />').addClass('mt10').appendTo(modal_body);

                    $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'all', id: 'export_condition__all', checked: 'checked'})).append($('<ins />')).appendTo(row);
                    $('<label />').attr('for', 'export_condition__all').addClass('label-inline').text('All products').appendTo(row);

                    row = $('<div />').addClass('mt5').appendTo(modal_body);

                    $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'search', id: 'export_condition__search'})).append($('<ins />')).appendTo(row);
                    $('<label />').attr('for', 'export_condition__search').addClass('label-inline').text('Current search').appendTo(row);

                    row = $('<div />').addClass('mt5').appendTo(modal_body);

                    $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'selected', id: 'export_condition__selected'})).append($('<ins />')).appendTo(row);
                    $('<label />').attr('for', 'export_condition__selected').addClass('label-inline').text('Selected products').appendTo(row);

                    row = $('<div />').addClass('mt5').appendTo(modal_body);

                    $('<div />').css({margin: '10px 0'}).html(btn.attr('data-mode') == 'add_collection' ? 'Add Collection: ' : 'Remove Collection: ').appendTo(row);

                    var table = $('<table />').css({border:'1px solid  rgba(0, 0, 0, 0.1)'}).addClass('grid grid-borderless').appendTo(row);

                    var tbody = $('<tbody />').appendTo(table);

                    var tr = $('<tr />').appendTo(tbody);

                    var th = $('<th style="background: white"/>').css({width:'10px'}).appendTo(tr);

                    var div = $('<div />').addClass('styled-checkbox').appendTo(th);

                    $('<input />').attr({type: 'checkbox','data-insert-cb' : 'initCheckboxSelectAll', 'data-checkbox-selector' : "input[name='set_colection_id']" }).addClass('styled-checkbox').appendTo(div);

                    $('<ins />').append($.renderIcon('check-solid')).appendTo(div);

                    $('<th style="background: white" />').html('Collection name ').appendTo(tr);

                    $.each(response.data, function (idx, collection) {
                        tr = $('<tr />').appendTo(tbody);

                        th = $('<td />').css({width:'10px'}).appendTo(tr);

                        div = $('<div />').addClass('styled-checkbox').appendTo(th);

                        $('<input />').attr({type: 'checkbox', 'name' : 'set_colection_id' ,'value' : collection.id }).addClass('styled-checkbox').appendTo(div);

                        $('<ins />').append($.renderIcon('check-solid')).appendTo(div);

                        $('<td />').text(collection.title).appendTo(tr);
                    });

                    var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

                    $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                        $.unwrapContent('catalogProductSetCollectionFrm');
                    }).appendTo(action_bar);

                    $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {
                        post_data.list_collection = [];

                        $('input[name="set_colection_id"]').each(function () {
                            if (this.checked) {
                                post_data.list_collection.push($(this).val());
                            }
                        });


                        if (post_data.list_collection.length  < 1 ){
                            alert('You have not click collection');
                            return;
                        }

                        post_data.get_data = false;

                        $.ajax({
                            url: btn.attr('data-process-url'),
                            data: post_data,
                            type: 'post',
                            success: function (response) {
                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }
                                $.unwrapContent('catalogProductSetCollectionFrm');

                                alert(response.data.message);

                                window.location.reload();
                            }
                        });

                    }).appendTo(action_bar);

                    $.wrapContent(modal, {key: 'catalogProductSetCollectionFrm'});

                    modal.moveToCenter().css('top', '100px');


                    if (!$('input[name="product_id"]:checked')[0]) {
                        if (btn.attr('data-search') === '1') {
                            $('#export_condition__search')[0].checked = true;
                        } else {
                            $('#export_condition__selected').attr('disabled', 'disabled');
                        }
                    } else {
                        $('#export_condition__selected')[0].checked = true;
                    }

                    if (btn.attr('data-search') !== '1') {
                        $('#export_condition__search').attr('disabled', 'disabled');
                    }
                }
            });


        });
    };

    window.initCatalogProductBulkSetTagBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogProductSetFrm');

            var post_data = {
                condition: null
            };

            if (!$('input[name="product_id"]:checked')[0] && btn.attr('data-search') === '1') {
                post_data.condition = 'search';
            } else if ($('input[name="product_id"]:checked')[0] ) {
                post_data.selected_ids = [];
                post_data.condition = 'select';
                $('input[name="product_id"]:checked').each(function () {
                    post_data.selected_ids.push(this.value);
                });
            }else{
                post_data.condition = 'all';
            }

            post_data.get_data = true;

            $.ajax({
                url: btn.attr('data-process-url'),
                data: post_data,
                type: 'post',
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    if (btn.attr('data-mode') != 'add_tag') {
                        if (response.data.length < 1){
                            alert("There are no tags to remove");
                            return;
                        }
                    }

                    var modal = $('<div />').addClass('osc-modal').width(1000);

                    var header = $('<header />').appendTo(modal);

                    $('<div />').addClass('title').html(btn.attr('data-mode') == 'add_tag' ? 'Add Tag' : 'Remove Tag').appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('catalogProductSetFrm');
                    }).appendTo(header);

                    var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

                    var row = $('<div />').addClass('mt10').appendTo(modal_body);

                    $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'all', id: 'export_condition__all', checked: 'checked'})).append($('<ins />')).appendTo(row);
                    $('<label />').attr('for', 'export_condition__all').addClass('label-inline').text('All products').appendTo(row);

                    row = $('<div />').addClass('mt5').appendTo(modal_body);

                    $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'search', id: 'export_condition__search'})).append($('<ins />')).appendTo(row);
                    $('<label />').attr('for', 'export_condition__search').addClass('label-inline').text('Current search').appendTo(row);

                    row = $('<div />').addClass('mt5').appendTo(modal_body);

                    $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'selected', id: 'export_condition__selected'})).append($('<ins />')).appendTo(row);
                    $('<label />').attr('for', 'export_condition__selected').addClass('label-inline').text('Selected products').appendTo(row);

                    row = $('<div />').addClass('mt5').appendTo(modal_body);

                    if (response.data.length > 0){
                        $('<div />').addClass('').html("Exits Tag").appendTo(row);
                    }

                    var product_post = $('<div />').addClass('product-post-frm').appendTo(row);
                    var div = $('<div />').addClass('product-tags').appendTo(product_post);
                    $.each(response.data, function (idx, tag) {
                        if (btn.attr('data-mode') == 'add_tag') {
                            $('<div />').addClass('product-tag').attr({title:tag}).html(tag).appendTo(div);
                        }else{
                            $('<div />').css("cursor","pointer").addClass('product-tag').attr({title:tag}).html(tag).click(function () {
                                addTags($(this).attr('title'));
                            }).appendTo(div);
                        }
                    });

                    row = $('<div />').addClass('mt5 ,frm-grid').appendTo(modal_body);


                    $('<div />').addClass('').html(btn.attr('data-mode') == 'add_tag' ? 'Add Tag' : 'Remove Tag').css('margin-bottom','5px').appendTo(row);

                    if (btn.attr('data-mode') != 'add_tag') {
                        $('<div />').addClass('').html('Note: You can click on the available tags or enter the input box below').css({'margin-bottom':'5px',"font-style": "italic", "font-weight": "bold"}).appendTo(row);
                    }

                    div = $('<div />').addClass('product-post-frm '  + (btn.attr('data-mode') == 'add_tag' ? 'add-tags-products' : 'remove-tags-products')).appendTo(row);

                    function addTags(tag) {
                        tag = tag.trim();

                        if (tag == ''){
                            return;
                        }
                        let container = null;

                        if (btn.attr('data-mode') == 'add_tag'){
                            container  = $('.add-tags-products').find('.product-tags');
                        }else{
                            container = $('.remove-tags-products').find('.product-tags');
                        }

                        let tags = {};

                        container.find('input[type="hidden"]').each(function () {
                            tags[this.value.toLowerCase()] = $(this).closest('.product-tag');
                        });

                        if (typeof tags[tag.toLowerCase()] !== 'undefined') {
                            container.prepend(tags[tag.toLowerCase()]);
                        } else {
                            container.append(_productPostFrm_renderTag(tag));
                        }
                    }

                    function _productPostFrm_renderTag(tag,container) {
                        return $('<div />').addClass('product-tag').attr('title', tag).text(tag).append($('<input />').attr({type: 'hidden', name: 'tags[]', value: tag})).append($('<ins />').click(function () {
                            $(this).closest('.product-tag').remove();
                        }));
                    }

                    if(btn.attr('data-mode') == 'add_tag'){
                        var input  = $('<input />').attr({type: 'text', name: 'add_tag', placeholder: 'Press Enter to add multiple tags'}).addClass('styled-input').appendTo(div);
                    }else{
                        var input  =  $('<input />').attr({type: 'text', name: 'remove_tag', placeholder: 'Press Enter to remove multiple tags'}).addClass('styled-input').appendTo(div);
                    }

                    $('<div />').addClass('product-tags').appendTo(div);

                    input.keyup(function (e) {
                        if (e.key === 'Enter') {
                            addTags($(this).val());
                            $(this).attr('value', '');
                        }
                    }).click(function(event){
                        event.stopPropagation();
                    });

                    $(window).click(function() {
                        if (input.val() != ''){
                            addTags(input.val());
                            input.attr('value', '');
                        }
                    });

                    input.focus();

                    var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

                    $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                        $.unwrapContent('catalogProductSetFrm');
                    }).appendTo(action_bar);

                    $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {
                        post_data.list_tag = [];

                        $('input[name="tags[]"]').each(function () {
                            if ($(this).val() != '') {
                                post_data.list_tag.push($(this).val());
                            }
                        });

                        if (post_data.list_tag.length  < 1 ){
                            alert('You have not added the tag!!');
                            return;
                        }

                        let data_mode = btn.attr('data-mode') == "add_tag" ? "add_tag" : "remove_tag";

                        if ($('input[name="'+data_mode+'"]').val() != '' ){
                            alert('you are still typing ..');
                            return;
                        }

                        post_data.get_data = false;
                        post_data.method = btn.attr('data-mode') == 'add_tag' ? 'add_tag' : 'remove_tag';

                        $.ajax({
                            url: btn.attr('data-process-url'),
                            data: post_data,
                            type: 'post',
                            success: function (response) {
                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }

                                $.unwrapContent('catalogProductSetFrm');

                                alert(response.data.message);

                                window.location.reload();
                            }
                        });

                    }).appendTo(action_bar);

                    $.wrapContent(modal, {key: 'catalogProductSetFrm'});

                    modal.moveToCenter().css('top', '100px');


                    if (!$('input[name="product_id"]:checked')[0]) {
                        if (btn.attr('data-search') === '1') {
                            $('#export_condition__search')[0].checked = true;
                        } else {
                            $('#export_condition__selected').attr('disabled', 'disabled');
                        }
                    } else {
                        $('#export_condition__selected')[0].checked = true;
                    }

                    if (btn.attr('data-search') !== '1') {
                        $('#export_condition__search').attr('disabled', 'disabled');
                    }
                }
            });


        });
    };

    window.initCatalogProductBulkDeleteBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrm('Bulk Delete products', 'Delete', 'Delete products', btn.attr('data-search') === '1', function (modal_body) {
            }, function (condition, error_callback, success_callback) {
                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {condition: condition},
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }

                        alert(response.data.message);

                        success_callback(response);

                        window.location.reload();
                    }
                });
            });
        });
    };

    window.initCatalogProductExportBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogProductExportFrm');

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Export products to XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogProductExportFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            $('<div />').text('Export').appendTo(modal_body);

            var row = $('<div />').addClass('mt10').appendTo(modal_body);

            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'all', id: 'export_condition__all', checked: 'checked'})).append($('<ins />')).appendTo(row);
            $('<label />').attr('for', 'export_condition__all').addClass('label-inline').text('All products').appendTo(row);

            row = $('<div />').addClass('mt5').appendTo(modal_body);

            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'search', id: 'export_condition__search'})).append($('<ins />')).appendTo(row);
            $('<label />').attr('for', 'export_condition__search').addClass('label-inline').text('Current search').appendTo(row);

            row = $('<div />').addClass('mt5').appendTo(modal_body);

            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'selected', id: 'export_condition__selected'})).append($('<ins />')).appendTo(row);
            $('<label />').attr('for', 'export_condition__selected').addClass('label-inline').text('Selected products').appendTo(row);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogProductExportFrm');
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10').html('Export products').click(function () {
                var post_data = {
                    export_condition: null
                };

                $('input[name="export_condition"]').each(function () {
                    if (this.checked) {
                        post_data.export_condition = this.value;
                        return false;
                    }
                });

                if (post_data.export_condition === 'selected') {
                    post_data.selected_ids = [];

                    $('input[name="product_id"]:checked').each(function () {
                        post_data.selected_ids.push(this.value);
                    });
                }

                $.ajax({
                    url: btn.attr('data-export-url'),
                    data: post_data,
                    success: function (response) {
                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        $.unwrapContent('catalogProductExportFrm');

                        window.location = response.data.url;
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogProductExportFrm'});

            modal.moveToCenter().css('top', '100px');

            if (!$('input[name="product_id"]:checked')[0]) {
                $('#export_condition__selected').attr('disabled', 'disabled');
            } else {
                $('#export_condition__selected')[0].checked = true;
            }

            if (btn.attr('data-search') !== '1') {
                $('#export_condition__search').attr('disabled', 'disabled');
            }
        });
    };

    window.initCatalogProductImportBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogProductImportFrm');

            var import_btn = null;

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Import products by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogProductImportFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
            var file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

            var uploader = $('<div />').appendTo(modal_body);
            var preview = $('<div />').appendTo(modal_body);

            uploader.osc_uploader({
                max_files: 1,
                process_url: btn.attr('data-import-url'),
                btn_content: $('<div />').addClass('btn btn-primary').text('Browse a XLSX file'),
                dragdrop_content: 'Drop here to upload',
                extensions: ['xlsx'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                file_input.val('');
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

                if (response.result === 'OK') {
                    import_btn.removeAttr('disabled');
                }

                file_input.val(response.data.file);
            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                uploader.show();
                file_input.val('');
                import_btn.attr('disabled', 'disabled');
            });

            initFileUploadHandler(uploader, preview);

            $('<div />').addClass('mt10').html('Download a <a href="#">sample XLSX template</a> to see an example of the format require').appendTo(modal_body);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogProductImportFrm');
            }).appendTo(action_bar);

            import_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Import products').click(function () {
                if (import_btn.attr('disabled') === 'disabled') {
                    return;
                }

                var file = file_input.val();

                if (!file) {
                    alert('Please upload file to import');
                    return;
                }

                this.setAttribute('disabled', 'disabled');
                this.setAttribute('data-state', 'submitting');

                import_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

                $.ajax({
                    url: btn.attr('data-importprocess-url'),
                    data: {file: file},
                    success: function (response) {
                        import_btn.removeAttr('disabled');
                        import_btn.removeAttr('data-state');

                        import_btn.find('svg').remove();

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        window.location.reload(true);
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogProductImportFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initCatalogProductInventoryImportBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogProductInventoryImportFrm');

            var import_btn = null;

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Import inventory by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogProductInventoryImportFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
            var file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

            var uploader = $('<div />').appendTo(modal_body);
            var preview = $('<div />').appendTo(modal_body);

            uploader.osc_uploader({
                max_files: 1,
                process_url: btn.attr('data-upload-url'),
                btn_content: $('<div />').addClass('btn btn-primary').text('Browse a XLSX file'),
                dragdrop_content: 'Drop here to upload',
                extensions: ['xlsx'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                file_input.val('');
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

                if (response.result === 'OK') {
                    import_btn.removeAttr('disabled');
                }

                file_input.val(response.data.file);
            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                uploader.show();
                file_input.val('');
                import_btn.attr('disabled', 'disabled');
            });

            initFileUploadHandler(uploader, preview);

            $('<div />').addClass('mt10').html('Download a <a href="#">sample XLSX template</a> to see an example of the format require').appendTo(modal_body);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogProductInventoryImportFrm');
            }).appendTo(action_bar);

            import_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Import products').click(function () {
                if (import_btn.attr('disabled') === 'disabled') {
                    return;
                }

                var file = file_input.val();

                if (!file) {
                    alert('Please upload file to import');
                    return;
                }

                this.setAttribute('disabled', 'disabled');
                this.setAttribute('data-state', 'submitting');

                import_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {file: file},
                    success: function (response) {
                        import_btn.removeAttr('disabled');
                        import_btn.removeAttr('data-state');

                        import_btn.find('svg').remove();

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        window.location.reload(true);
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogProductInventoryImportFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initCatalogProductBulkExportAmazonBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrmAmazon('Bulk Export products', 'Export', 'Export products', btn.attr('data-search') === '1', btn.attr('data-mode'), function (modal_body) {
            }, function (condition, error_callback, success_callback) {
                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {condition: condition},
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }
                        alert(response.data.message);
                        window.location.reload();
                    }
                });
            });
        });
    };

    function _renderBulkFrmAmazon(frm_title, action_title, btn_action_title, in_search, type, option_frm_callback, process_callback) {
        $.unwrapContent('catalogProductBulkFrmAMZ');

        let modal = $('<div />').addClass('osc-modal').width(350);

        let header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(frm_title).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogProductBulkFrmAMZ');
        }).appendTo(header);

        let modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        $('<div />').text(action_title).appendTo(modal_body);

        let row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'search', id: 'bulk_condition__search'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__search').addClass('label-inline').text('Current search').appendTo(row);

        row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'selected', id: 'bulk_condition__selected'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__selected').addClass('label-inline').text('Selected products').appendTo(row);

        if (typeof option_frm_callback === 'function') {
            option_frm_callback(modal_body);
        }

        let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('catalogProductBulkFrmAMZ');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html(btn_action_title).click(function () {
            let conditions = {};

            conditions.type = type;

            let condition = null;

            $('input[name="bulk_condition"]').each(function () {
                if (this.checked) {
                    condition = this.value;
                    return false;
                }
            });

            if (condition === 'selected') {
                let condition_ids = [];
                $('input[name="product_id"]:checked').each(function () {
                    condition_ids.push(this.value);
                });

                conditions.ids = condition_ids;
            }

            process_callback(conditions, function (response) {
                alert(response.message);
            }, function (response) {
                $.unwrapContent('catalogProductBulkFrmAMZ');
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'catalogProductBulkFrmAMZ'});

        modal.moveToCenter().css('top', '100px');

        if (!$('input[name="product_id"]:checked')[0]) {
            $('#bulk_condition__selected').attr('disabled', 'disabled');
        } else {
            $('#bulk_condition__selected')[0].checked = true;
        }

        if (!in_search) {
            $('#bulk_condition__search').attr('disabled', 'disabled');
        }
    }

    window.initProductDescriptionAmazon = function () {
        $(this).change(function () {
            let niche_descriptions = fetchJSONTag($(document.body), 'niche-descriptions');
            let descriptions_id = $(this).children("option:selected").val();
            let niche_descriptions_data = niche_descriptions[descriptions_id];

            $('#input-niche_amazon_description').val(niche_descriptions_data['description']);
            $('#input-niche_amazon_key_words').val(niche_descriptions_data['keywords']);

            Object.keys(niche_descriptions_data['key_product_features']).forEach(function (key) {
                $("#input-" + key).val(niche_descriptions_data['key_product_features'][key]);
            });
        });
    }

    window.initCatalogProductImportTagsBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogProductImportTagFrm');
            let import_btn = null;

            let modal = $('<div />').addClass('osc-modal').width(390);

            let header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Import Tags by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogProductImportTagFrm');
            }).appendTo(header);

            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
            let file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

            let uploader = $('<div />').appendTo(modal_body);
            let preview = $('<div />').appendTo(modal_body);

            uploader.osc_uploader({
                max_files: 1,
                process_url: btn.attr('data-import-url'),
                btn_content: $('<div />').addClass('btn btn-primary').text('Browse a XLSX file'),
                dragdrop_content: 'Drop here to upload',
                extensions: ['xlsx'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                file_input.val('');
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

                if (response.result === 'OK') {
                    import_btn.removeAttr('disabled');
                    file_input.val(response.data.file);
                }

                if (response.result === 'ERROR') {
                    file_input.val('');
                }
            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                uploader.show();
                file_input.val('');
                import_btn.attr('disabled', 'disabled');
            });

            initFileUploadHandler(uploader, preview);

            $('<div />').addClass('mt10').html('Download a <a href="#">sample XLSX template</a> to see an example of the format require').appendTo(modal_body);

            const action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogProductImportTagFrm');
            }).appendTo(action_bar);

            import_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Import Tags').click(function () {
                if (import_btn.attr('disabled') === 'disabled') {
                    return;
                }

                let file = file_input.val();

                if (!file) {
                    alert('Please upload file to import');
                    return;
                }

                this.setAttribute('disabled', 'disabled');
                this.setAttribute('data-state', 'submitting');

                import_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

                $.ajax({
                    url: btn.attr('data-importprocess-url'),
                    data: {file: file},
                    success: function (response) {
                        import_btn.removeAttr('disabled');
                        import_btn.removeAttr('data-state');

                        import_btn.find('svg').remove();

                        console.log(response);

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        } else {
                            alert(response.data.message);
                        }

                        window.location.reload(true);
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogProductImportTagFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initVendorSelector = function () {
        const list_member_display_live_preview = JSON.parse($(this).find('script[data-json="list_member_display_live_preview"]')[0].innerHTML);
        const show_product_detail_type_checkbox = $('#show_product_detail_type_container');
        const switcher = $('input[name="show_product_detail_type"]');

        const setSwitcherOn = () => {
            switcher.attr('checked', true);
            switcher.attr('value', 1);
            switcher.parent().addClass('checked');
        }

        const setSwitcherOff = () => {
            switcher.attr('checked', false);
            switcher.attr('value', 0);
            switcher.parent().removeClass('checked');
        }

        const checkIsMemberCanLivePreview = () => {
            if (!!list_member_display_live_preview.find(mem => mem.username == $(this).val())) {
                setSwitcherOn();
                show_product_detail_type_checkbox.show();
            } else {
                setSwitcherOff();
                show_product_detail_type_checkbox.hide();
            }
        }

        $(this).on('change', function () {
            checkIsMemberCanLivePreview();
        });
    }

    window.initCatalogBulkProductBetaBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogBulkProductBetaFrm');
            let import_btn = null;

            let modal = $('<div />').addClass('osc-modal').width(390);

            let header = $('<header />').appendTo(modal);
            if (btn.attr('data-import-product') == 'beta') {
                $('<div />').addClass('title').html('Bulk Product Beta by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));
            } else {
                $('<div />').addClass('title').html('Bulk Campaign by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));
            }

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogBulkProductBetaFrm');
            }).appendTo(header);

            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
            let file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

            let uploader = $('<div />').appendTo(modal_body);
            let preview = $('<div />').appendTo(modal_body);

            uploader.osc_uploader({
                max_files: 1,
                process_url: btn.attr('data-import-url'),
                btn_content: $('<div />').addClass('btn btn-primary').text('Browse a XLSX file'),
                dragdrop_content: 'Drop here to upload',
                extensions: ['xlsx'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                file_input.val('');
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

                if (response.result === 'OK') {
                    import_btn.removeAttr('disabled');
                    file_input.val(response.data.file);
                }

                if (response.result === 'ERROR') {
                    file_input.val('');
                }
            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                uploader.show();
                file_input.val('');
                import_btn.attr('disabled', 'disabled');
            });

            initFileUploadHandler(uploader, preview);

            let url_file = $.base_url + '/resource/template/core/sample_xlsx/bulk_upload_campaign.xlsx';

            if (btn.attr('data-import-product') == 'beta') {
                url_file = $.base_url + '/resource/template/core/sample_xlsx/bulk_upload_product_beta.xlsx';
            }

            $('<div />').addClass('mt10').html('Download a <a class ="link" href="'+ url_file + '">Sample XLSX Template</a> to see an example of the format require').appendTo(modal_body);

            const action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogBulkProductBetaFrm');
            }).appendTo(action_bar);

            import_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Upload').click(function () {
                if (import_btn.attr('disabled') === 'disabled') {
                    return;
                }

                let file = file_input.val();

                if (!file) {
                    alert('Please upload file to import');
                    return;
                }

                this.setAttribute('disabled', 'disabled');
                this.setAttribute('data-state', 'submitting');

                import_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

                $.ajax({
                    url: btn.attr('data-importprocess-url'),
                    data: {file: file},
                    success: function (response) {
                        import_btn.removeAttr('disabled');
                        import_btn.removeAttr('data-state');

                        import_btn.find('svg').remove();

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        } else {
                            alert(response.data.message);
                        }

                        window.location.reload(true);
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogBulkProductBetaFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initExportProductTypeBtn = function () {
        var btn = $(this);
        $(this).click(function () {
            $.ajax({
                url: btn.attr('data-export-url'),
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }
                    window.location = response.data.url;
                }
            });
        });
    }

})(jQuery);