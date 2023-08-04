(function ($) {
    'use strict';

    const FILTER_FIELD = ['product_id', 'product_title', 'sku', 'added_by', 'solds'];
    const by_pass_quantity_elem = $('#excludeByPassQuantity');
    const container = $('#container_table_by_pass_products');
    const table_elem = $('#list_minsold_product_table');
    const table_body_elem = $('#list_minsold_product_table > tbody');
    const table_info_elem = $('#table_info');
    const pagination_elem = $('#pagination');
    const meta_data = JSON.parse(table_elem.find('script[data-json="meta-data"]')[0].innerHTML);
    var all_products = meta_data.product_data;
    var filtered_products = [...all_products];
    const page_size = meta_data.page_size;
    const key_config = 'config[' + meta_data.additional_key_config + ']';
    const by_pass_product_input_elem = $(`input[name="${key_config}"]`);
    var current_page = 1;

    const getDataByPassProduct = () => {
        return !!by_pass_product_input_elem.val()
            ? JSON.parse(by_pass_product_input_elem.val())
            : [];
    }

    const changeProductInValueConfig = (product, action = 'add') => {
        const data_products_input = getDataByPassProduct();

        let new_products = [];
        if (action == 'add') {
            new_products = [
                {
                    product_id: product.product_id,
                    added_date: product.added_date,
                    added_by: product.added_by,
                }, 
                ...data_products_input
            ];
        } else if (action == 'remove') {
            new_products = data_products_input.filter(_product => _product.product_id != product.product_id);
        }
        by_pass_product_input_elem.val(JSON.stringify(new_products));
    }

    const getListProduct = (product_ids) => {
        return $.ajax({
            type: 'POST',
            url: $.base_url + '/catalog/backend_campaign/getListProductInfo/hash/' + OSC_HASH,
            data: { product_ids },
        });
    }

    const insertProductToTableElem = (products) => {
        for (let product of products) {
            table_body_elem.prepend(
                $('<tr />')
                    .append(`
                        <td style="text-align: center">
                            <div data-insert-cb="initQuickLook" data-image="${product.image}" class="thumbnail-preview" style="background-image: url(${product.image})"></div>
                        </td>
                    `)
                    .append(`<td>${product.product_id}</td>`)
                    .append(`<td>${product.sku}</td>`)
                    .append(`<td style="word-break: break-word">${product.product_title}</td>`)
                    .append(`<td>${product.solds}</td>`)
                    .append(`<td>${product.added_by}</td>`)
                    .append(`<td>${product.added_date_format}</td>`)
                    .append(
                        $('<td />')
                            .css('text-align', 'right')
                            .append(
                                $('<a />')
                                    .addClass('btn btn-small btn-icon')
                                    .attr({
                                        'href' : product.product_url,
                                        'target' : '_blank',
                                    })
                                    .append($.renderIcon('eye-regular'))
                            )
                            .append(
                                $('<a />')
                                    .addClass('btn btn-small btn-icon')
                                    .attr({
                                        'href' : product.analytic_url + '/' + OSC_HASH,
                                        'target' : '_blank',
                                    })
                                    .append($.renderIcon('analytics'))
                            )
                            .append(
                                $('<div />')
                                    .addClass('btn btn-small btn-icon')
                                    .attr({
                                        'data-insert-cb' : 'initRemoveMinsoldProduct',
                                        'data-product-id' : product.product_id,
                                    })
                                    .append($.renderIcon('trash-alt-regular'))
                            )
                    )
            );
        }
    }

    const renderTableInfo = (start, end) => {
        const isFiltering = all_products.length > filtered_products.length;
        const products_total = Math.min(filtered_products.length, all_products.length);
        const infor = `Showing ${!!filtered_products.length ? start : 0} to ${Math.min(end, filtered_products.length, all_products.length)} of ${products_total} entries ${
            isFiltering ? ('(filtered from ' + all_products.length +' total entries)') : ''
        }`;

        table_info_elem.text(infor);
        by_pass_quantity_elem.text(`Exclude ${all_products.length} product(s)`);
    }

    const renderPagination = () => {
        pagination_elem.empty();

        for (let i = 1; i <= Math.ceil(filtered_products.length / page_size); i++) {
            pagination_elem.append(
                $('<li />')
                    .addClass(i == current_page ? 'current' : '')
                    .attr({
                        'data-insert-cb': 'initChangePageMinSoldProduct',
                        'data-page': i
                    })
                    .append($('<div />').text(i))
            );
        }
    }

    window.initEditTableByPassProducts = function () {
        $(this).on('click', function() {
            if (container.is(':visible')) {
                container.hide();
            } else {
                container.show();
            }
        });
    }

    window.initMinSoldProduct = function () {
        $(this).on('click', function() {
            $.unwrapContent('MinSoldProductCreate');
            let modal_form = $('<form />').attr('id', 'minSold-product-create').addClass('osc-modal').width(500);

            let header = $('<header />').appendTo(modal_form);

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('MinSoldProductCreate');
            }).appendTo(header);

            $('<div />').addClass('title').text('Create Product').appendTo($('<div />').addClass('main-group').appendTo(header));
            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal_form);

            let product_id_el = $('<div />').addClass('frm-grid')
            $('<label />').html('Product ID').appendTo(product_id_el);
            let product_id_input = $('<input />')
                .addClass('styled-input')
                .appendTo(product_id_el);
            $('<span />').addClass('product_note').text('Enter dash (-) separation to add multiple products. Ex: 1234-3122').appendTo(product_id_el);
            product_id_el.appendTo(modal_body)

            let action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('MinSoldProductCreate');
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10').text('Save').click(function () {
                let button_save = this;
                $(this).prop('disabled', true);
                $(this).prepend($($.renderIcon('preloader')).addClass('mr15'));
                getListProduct(product_id_input.val()).done((response) => {
                    if (response.result !== 'OK') {
                        alert(response.message?.join(''));
                        $(button_save).prop('disabled', false).text('Save');
                        return;
                    }

                    const current_ids = getDataByPassProduct().map(product => product.product_id);
                    const added_products = response.data?.filter(product => !current_ids.includes(product.product_id));
                    const duplicateProducts = response.data?.filter(product => current_ids.includes(product.product_id));
                    if (duplicateProducts.length) {
                        alert('Product ID: ' + duplicateProducts.map(product => product.product_id).join(', ') + ' is duplicated');
                    }

                    for (let product of added_products) {
                        all_products.unshift(product);
                        changeProductInValueConfig(product);
                    }
                    filtered_products = [...all_products];
                    current_page = 1;
                    
                    table_body_elem.empty();
                    insertProductToTableElem(filtered_products.slice(0, page_size).reverse());
                    renderTableInfo(1, page_size);
                    renderPagination();

                    $.unwrapContent('MinSoldProductCreate');
                });

            }).appendTo(action_bar);


            $.wrapContent(modal_form, {key: 'MinSoldProductCreate'});

            modal_form.moveToCenter().css('top', '100px');
        });
    };

    window.initSearchTable = function() {
        $(this).on('keyup', function () {
            const key_search = $(this).val().toLowerCase();

            filtered_products = all_products.filter((product) => {
                let flag = false;
                FILTER_FIELD.forEach(field => {
                    if ((product[field] + '').toLowerCase().includes(key_search)) {
                        flag = true;
                    }
                });
                return flag;
            });

            current_page = 1;
            table_body_elem.empty();
            insertProductToTableElem(filtered_products.slice(0, page_size).reverse());
            renderTableInfo(1, page_size);
            renderPagination();
        });
    }

    window.initRemoveMinsoldProduct = function () {
        $(this).on('click', function() {
            const product_id = $(this).attr('data-product-id');
            if (window.confirm(`Are you sure to delete by pass product ID: ${product_id} ?`)) {
                changeProductInValueConfig({product_id}, 'remove');
                all_products = all_products.filter(product => product.product_id != product_id);
                filtered_products = filtered_products.filter(product => product.product_id != product_id);
                let _products = filtered_products.slice((current_page - 1) * page_size, current_page * page_size);
                if (!_products.length && current_page > 1) {
                    current_page -= 1;
                    _products = filtered_products.slice((current_page - 1) * page_size, current_page * page_size);
                }
                table_body_elem.empty();
                insertProductToTableElem(_products.reverse());
                renderTableInfo((current_page - 1) * page_size + 1, current_page * page_size);
                renderPagination();
                $(this).closest('tr').remove();
            }
        });
    }

    window.initChangePageMinSoldProduct = function () {
        $(this).on('click', function() {
            const selected_page = $(this).attr('data-page'); 
            if (current_page == Number(selected_page)) {
                return;
            }
            current_page = Number(selected_page);
            $(this).closest('ul').children('li').each(function () {
                $(this).removeClass('current');
                if ($(this).attr('data-page') == selected_page) {
                    $(this).addClass('current');

                    const start = (Number(selected_page) - 1) * page_size;
                    const end = Number(selected_page) * page_size;

                    const _filtered_products = filtered_products.slice(start, end);
                    table_body_elem.empty();
                    insertProductToTableElem(_filtered_products.reverse());
                    renderTableInfo(start + 1, end);
                }
            });
        });
    }

})(jQuery);