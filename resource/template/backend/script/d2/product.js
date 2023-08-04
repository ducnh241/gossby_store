window.initD2CreateProductBtn = function () {
    let btn_add_product = $(this)
    btn_add_product.click(function () {
        $.unwrapContent('d2ProductCreate');
        let modal_form = $('<form />').attr('id', 'd2-product-create').addClass('osc-modal').width(500);

        let header = $('<header />').appendTo(modal_form);

        let save_btn = null;

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('d2ProductCreate');
        }).appendTo(header);

        $('<div />').addClass('title').html(btn_add_product.attr('data-title')).appendTo($('<div />').addClass('main-group').appendTo(header));
        let modal_body = $('<div />').addClass('body post-frm').appendTo(modal_form);

        // Add input product id

        let product_id_el = $('<div />').addClass('frm-grid')
        $('<label />').html('Product ID').appendTo(product_id_el)
        let product_id_input = null;
        if(!btn_add_product.attr('data-id')) {
            product_id_input = $('<input />').addClass('styled-input').appendTo(product_id_el)
            $('<span />').addClass('product_note').html('Enter space separation to add multiple products. Ex: 1234 3122').appendTo(product_id_el)
        } else {
            product_id_input = $('<input />').addClass('styled-input').attr('type', 'number').val(btn_add_product.attr('data-id')).appendTo(product_id_el)
        }
        product_id_el.appendTo(modal_body)

        let action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('d2ProductCreate');
        }).appendTo(action_bar);

        save_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Save').click(function () {
            this.setAttribute('disabled', 'disabled');

            save_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

            $.ajax({
                type: 'POST',
                url: btn_add_product.attr('data-create-product-url'),
                data: {
                    product_ids: product_id_input.val()
                },
                success: function (response) {
                    save_btn.removeAttr('disabled');


                    if (response.result !== 'OK') {
                        let errors = JSON.parse(response.message)
                        let msg = '';
                        Object.keys(errors).forEach(function (key) {
                            msg += `${key} : ${errors[key]}\n\n`
                        })

                        alert(msg)

                    }

                    window.location.reload(true);
                }
            });

        }).appendTo(action_bar);

        product_id_input.on('keyup change', function () {

            if ($(this).val().length) {
                save_btn.attr('disabled', false)
            } else {
                save_btn.attr('disabled', true)
            }
        })

        $.wrapContent(modal_form, {key: 'd2ProductCreate'});

        modal_form.moveToCenter().css('top', '100px');
    })
}

window.initD2ProductBulkDeleteBtn = function () {
    let btn_delete_progress = $(this)

    btn_delete_progress.click(function () {
        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        let product_ids = [];

        $('input[name="product_id"]:checked').each(function () {
            product_ids.push(this.value);
        });

        if (product_ids.length < 1) {
            return;
        }

        if (this.getAttribute('data-confirm')) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                return;
            }
        }

        this.setAttribute('disabled', 'disabled');
        let _this = this;
        $.ajax({
            type: 'POST',
            url: this.getAttribute('data-process-url'),
            data: {product_ids},
            success: function (response) {
                _this.removeAttribute('disabled');

                if (response.result !== 'OK') {
                    alert(response.message);
                    return;
                }

                alert(response.data.message);

                window.location.reload(true);
            }
        });
    })
}

window.initReCronActionBtn = function () {
    $(this).click(function () {
        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        var queue_ids = [];

        $('input[name="queue_id"]:checked').each(function () {
            queue_ids.push(this.value);
        });

        if (queue_ids.length < 1) {
            return;
        }

        if (this.getAttribute('data-confirm')) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                return;
            }
        }

        this.setAttribute('disabled', 'disabled');

        var btn = this;

        $.ajax({
            url: this.getAttribute('data-process-url'),
            data: {queue_ids},
            success: function (response) {
                btn.removeAttribute('disabled');

                if (response.result !== 'OK') {
                    alert(response.message);
                    $('input[type="checkbox"]:checked').each(function () {
                        $(this).prop('checked', false);
                    });
                    return;
                }

                alert(response.data.message);

                window.location.reload();
            }
        });
    });
}

window.initDeleteCronActionBtn = function () {
    let btn_delete_progress = $(this)

    btn_delete_progress.click(function () {
        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        let queue_ids = [];

        $('input[name="queue_id"]:checked').each(function () {
            queue_ids.push(this.value);
        });

        if (queue_ids.length < 1) {
            return;
        }

        if (this.getAttribute('data-confirm')) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                return;
            }
        }

        this.setAttribute('disabled', 'disabled');
        let _this = this;
        $.ajax({
            type: 'POST',
            url: this.getAttribute('data-process-url'),
            data: {queue_ids},
            success: function (response) {
                _this.removeAttribute('disabled');

                if (response.result !== 'OK') {
                    alert(response.message);
                    return;
                }

                alert(response.data.message);

                window.location.reload(true);
            }
        });
    })
}
