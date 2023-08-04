(function ($) {
    'use strict';

    window.initSelectProductCollectionComponent = function () {
        const that = $(this);
        const isEdit = !!parseInt(that.attr('data-mode'))
        Object.assign(that, {
            selectedProduct: [],
            isEdit
        });

        function renderVariantData(defaultData) {
            that.selectedProduct = JSON.parse(
                JSON.stringify(defaultData)
            );
            renderCollectionItem(that);
        }
        let variant_selector_data = [];
        try {
            variant_selector_data = fetchJSONTag($(this), "data")?.product_selector_params;
        } catch (error) {
            console.log(error);
        }

        try {
            if (
                variant_selector_data.length !== 0
            ) {
                renderVariantData(variant_selector_data);
            }
        } catch (error) {
            console.log(error);
        }

    };

    window.initAddNewCollection = function () {
        const that = $(this)

        let collection = fetchJSONTag($(this), "collection")?.collection;
        collection = JSON.parse(
          JSON.stringify(collection)
        );

        collection.splice(0, 0, {'id': 0, 'title': 'All Collection'});
        that.on('click', function () {
            _renderCollectionFrm('Add new collection', 'Apply', collection, true)
        })
    }

    window.initSubmitBlock = function () {
        const btn_submit = $(this)
        const category = btn_submit.attr('data-category');
        btn_submit.on('click', function () {

            if (btn_submit.attr('disabled') === 'disabled') {
                return;
            }
            let exist_sku_empty = false
            let data = [];

            $('.collection_item').each(function () {

                const collection_item = $(this)
                const collection_id = collection_item.attr('data-collection-id')
                const sku_list = collection_item.find('.product-collection-item__list-item span')
                if (!sku_list.length) {
                    exist_sku_empty = true
                    return false
                } else {
                    sku_list.each(function () {
                        data.push({
                            collection_id,
                            'sku': $(this).attr('data-product-sku').toUpperCase()
                        })
                    })
                }
            })

            if (exist_sku_empty || !data.length) {
                alert('Product in collection is can not empty!')
                return
            }

            this.setAttribute('disabled', 'disabled');
            this.setAttribute('data-state', 'submitting');

            $.ajax({
                type: 'POST',
                url:
                  `${$.base_url}/feed/backend_block/saveBlock/hash/${OSC_HASH}`,
                data: {
                    country_code: btn_submit.attr('data-country-code') || $('#country-select').find(':selected').val(),
                    data,
                    category
                },
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        btn_submit.removeAttr('disabled')
                        return;
                    }
                    window.location.href = `${$.base_url}/feed/backend_block/${category}/hash/${OSC_HASH}`
                },
            });
        })
    }

    function renderCollectionItem(container) {
        container.empty();
        const list = $("<div>")
            .addClass("select-product-collection-component__list")
            .appendTo(container);

        const div = $("<div>")
            .addClass(
                `product-collection-item`
            )
            .appendTo(list);
        const header = $(
            "<div class='d-flex product-collection-item__header'/>"
        ).appendTo(div);

        container.isEdit && $("<div>")
            .addClass("remove-item")
            .click(function () {
                container.remove()
                return
            })
            .appendTo(header);

        const collection_id = parseInt(container.attr('data-collection-id'));

        const collection = $("<strong>")
            .append(
                $("<strong>").text(
                    `${(collection_id ? (collection_id + ' - ') : '') } ${container.attr('data-collection-name')}`
                ).append(`<span class='text-blue ml5 mr5'>(${container.selectedProduct.length})</span>`)
            )
            .append($.renderIcon("angle-down-solid-thin"))
            .appendTo(header);
        container.isEdit && $("<div class='d-flex' />")
            .append(
                $("<span class='underline text-blue mr5'/>").text("Add").click(function (e) {
                        e.stopPropagation();
                        _renderCollectionFrm(`${container.attr('data-collection-name')}`, 'Apply', null, false, container)
                })
            )
            .appendTo(header);

        $.ajax({
            type: 'POST',
            url:
              `${$.base_url}/feed/backend_block/getProductBySku/hash/${OSC_HASH}`,
            data: {
                skus: container.selectedProduct
            },
            success: function (response) {
                if (response.result !== 'OK') {
                    alert(response.message)
                    return;
                }
                const product = response.data;
                if (container.selectedProduct.length) {
                    const listVariant = $("<div>")
                      .addClass(`product-collection-item__list`)
                      .appendTo(div);
                    const wrapper = $("<div>").addClass("p-8").appendTo(listVariant);

                    container.selectedProduct
                      .forEach((sku) => {
                          let item = $("<div>")
                            .addClass("product-collection-item__list-item d-flex")
                            .append($("<span>").attr({'data-product-sku': sku}).text(`${sku} - ${product[sku]}`))
                            .appendTo(wrapper);
                          if (container.isEdit) item.append(
                            $($.renderIcon("times")).click(function (e) {
                                container.selectedProduct = container.selectedProduct.filter(
                                  (v) => v !== sku
                                );
                                collection.find('span').text(`(${container.selectedProduct.length})`)
                                item.remove()
                                container.open
                                    ? listVariant.css(
                                        "height",
                                        `${container.selectedProduct.length * 32 + 16}px`
                                    )
                                    : listVariant.css("height", "0px");
                            })
                          )
                      });

                    container.open
                      ? listVariant.css(
                        "height",
                        `${container.selectedProduct.length * 32 + 16}px`
                      )
                      : listVariant.css("height", "0px");
                    header.click(function () {
                        div.toggleClass("show");
                        if (div.hasClass("show")) {
                            container.open = true;
                            listVariant.css(
                              "height",
                              `${container.selectedProduct.length * 32 + 16}px`
                            );
                        } else {
                            container.open = false;
                            listVariant.css("height", "0px");
                        }
                    });
                }
            },
        });
    }

    function _renderCollectionFrm(frm_title, btn_action_title, collection, new_collection = false, container = null) {
        $.unwrapContent('AddCollectionFrm');

        if (new_collection) {
            $('.collection_item').each(function () {
                const item = $(this)
                collection = collection.filter(function (col) {
                    return parseInt(col.id) !== parseInt($(item).attr('data-collection-id'))
                })
            })
        }

        var modal = $('<div />').addClass('osc-modal').width(500);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(frm_title).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('AddCollectionFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        if (new_collection) {
            const frm_collection = $('<div />').appendTo($('<div />').addClass('frm-grid').appendTo(modal_body));
            frm_collection.append($('<label />').addClass('required').text('Collection'))

            const select = $('<select />').addClass('selection-options').attr({id: 'select-collection'}).prependTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo(frm_collection));
            collection.forEach(function (item) {
                var option = $('<option />').attr({'value': item.id, 'data-collection-name': item.title}).text(item.id > 0 ? `${item.id} - ${item.title}` : item.title).appendTo(select);
            });
        }

        const div_product = $('<div />').addClass('frm-grid')
            .appendTo(modal_body)

        const el_label_product = $('<div />').append($('<label />').addClass('required mb0').text('Product SKU'))
          .append($('<span />').addClass('label-product-note').text('Enter space separation to add multiple products SKU.\nEx: 600F8DD496EC2WG 600F8DD8E3484AB'))

        const el_list_product = $('<div />').addClass('modal-product mt10').appendTo($('<div />').append(el_label_product).appendTo(div_product))

        if (!new_collection) {
            const sku = container.selectedProduct

            sku.forEach(function (item) {
                $('<div />').addClass('frm-grid')
                    .append(
                        $('<div />').addClass('d-flex')
                            .append($('<input />').addClass('styled-input new-product').attr({ type: 'text', name: 'new-product'}).val(item))
                            .append($('<div />').append($.renderIcon('trash-alt-regular'))
                                .addClass('btn btn-small btn-icon ml5')
                                .click(function () {
                                    $(this).parent().parent().remove()
                            }))
                    ).appendTo(el_list_product)
            })
        }

      $('<button />').addClass('btn btn-secondary mt10 add-product').html('Add Product').click(function () {
          $('<div />').addClass('frm-grid')
              .append(
                  $('<div />').addClass('d-flex')
                      .append($('<input />').addClass('styled-input new-product').attr({ type: 'text', name: 'new-product'}))
                      .append($('<div />').append($.renderIcon('trash-alt-regular'))
                          .addClass('btn btn-small btn-icon ml5')
                          .click(function () {
                              $(this).parent().parent().remove()
                          }))
              ).appendTo(el_list_product)
      }).appendTo(modal_body);

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('AddCollectionFrm');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html(btn_action_title).click(function () {

            let product_empty = false
            let product_sku = []

            $('input[name="new-product"]').each(function () {
                if (!$(this).val().trim().length) {
                    product_empty = true
                    return false
                } else {

                    $(this).val().trim().split(" ").forEach(function (sku) {
                        if (sku.trim().length) {
                            product_sku.push(sku.trim().toUpperCase())
                        }
                    })
                }
            })

            if (product_empty || !$('input[name="new-product"]').length) {
                alert('Product in collection is can not empty!')
                return
            }

            product_sku = product_sku.filter((value, index, item) => item.indexOf(value) === index)
            _validateSKU(product_sku, function () {
                _renderCollectionItem(product_sku, new_collection, container)
                $.unwrapContent('AddCollectionFrm');
            })
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'AddCollectionFrm'});

        modal.moveToCenter().css('top', '100px');
    }

    function _renderCollectionItem(product_sku, new_collection, container) {
        if (new_collection) {
            const el_collection_list = $('#collection_list')
            const new_collection_container = $('<div />')
              .addClass('select-product-collection-component collection_item')
              .attr({
                  'data-collection-id': $('#select-collection').find(':selected').val(),
                  'data-collection-name': $('#select-collection').find(':selected').attr('data-collection-name'),
              }).appendTo(el_collection_list)

            Object.assign(new_collection_container, {
                selectedProduct: product_sku,
                isEdit: true
            });

            renderCollectionItem(new_collection_container)
        } else {
            Object.assign(container, {
                selectedProduct: product_sku,
                isEdit: true
            });
            renderCollectionItem(container)
        }
    }

    function _validateSKU(skus, callback) {
        $.ajax({
            type: 'POST',
            url:
              `${$.base_url}/feed/backend_block/validateSKU/hash/${OSC_HASH}`,
            data: {
                skus
            },
            success: function (response) {
                if (response.result !== 'OK') {
                    alert(response.message)
                    return;
                }
                callback()
            },
        });
    }

})(jQuery);
