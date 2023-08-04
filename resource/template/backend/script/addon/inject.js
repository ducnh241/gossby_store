window.initAddonForCampaign = function() {
    const $popupBtn = $('.addon-service-open-popup');
    const $input = $('.addon-service-data');
    const template = $('#addon-service-template').html();
    const $table = $('.addon-service-table');
    const $enableSwitcher = $('.addon-service-allow');
    const $form = $(this).closest('form');
    let autoSelectAddons = [];
    let data = ($input.val() ? JSON.parse($input.val()) : []).filter(addon => {
        if (addon.auto_select) {
            delete addon.auto_select;
            autoSelectAddons.push(addon);
            return false;
        }
        return true;
    });

    $form.on('submit', function(e) {
        if (!autoSelectAddons.length) return true;

        let data = $input.val() ? JSON.parse($input.val()) : [];

        $input.val(JSON.stringify([
            ...data,
            ...autoSelectAddons,
        ]));
    });

    $table.on('click', '.addon-service-remove', function() {
        let service_id = $(this).data('service-id');
        data = data.filter(item => item.addon_service_id != service_id);
        renderAddonTable();
    });

    $popupBtn.on('click', function() {
        $.unwrapContent('addonForCampaign');
        const $modal = $('<div />').addClass('osc-modal').width(720);
        const $header = $('<header />').appendTo($modal);
        const $body = $('<form>').addClass('body').appendTo($modal);
        const $list = $('<div>').addClass('addon-service-list').appendTo($body);
        const $footer = $('<div>').html(`
            <div style="text-align: right;">
                <button type="button" class="btn btn-outline mr5 addon-service-cancel">Cancel</button>
                <button type="button" class="btn btn-secondary mr5 addon-service-add">Add Another Service</button>
                <button type="submit" class="btn btn-primary addon-service-save">Save</button>
            </div>
        `).appendTo($body);

        $('<div />').addClass('title').html('Select Add-on Services').appendTo(
            $('<div />').addClass('main-group').appendTo($header)
        );

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('addonInjection');
        }).appendTo($header);

        $.wrapContent($modal, {
            key: 'addonInjection',
            close_callback: function() {
                $('.daterangepicker, .select2-container').remove();
            }
        });
        $modal.moveToCenter().css('top', '100px');

        if (data.length) {
            data.forEach(function(addon) {
                if (!addon.not_available) {
                    appendAddonForm(addon);
                }
            });
        } else {
            appendAddonForm();
        }

        $modal.on('click', '.addon-service-cancel', function() {
            $.unwrapContent('addonInjection');
        });

        $modal.on('click', '.addon-service-add', function() {
            appendAddonForm();
        });

        $modal.on('click', '.addon-service-delete', function() {
            const $service = $(this).closest('.addon-service');
            const $select = $service.find('.addon-service-id');
            const value = $select.val();

            $list.find(`option[value="${value}"]`).prop('disabled', false);
            $list.find(`option[data-disabled]`).prop('disabled', true);

            const productTypeId = $select.get(0).productTypeId;
            if (productTypeId) {
                $list.find(`[data-product-type-id="${productTypeId}"]`).not('[data-disabled]').prop('disabled', false);
            }

            $service.remove();
            renameServiceLabels();
        });

        $modal.on('change', '.addon-service-id', function() {
            const oldValue = this.oldValue;
            const newValue = $(this).val();
            this.oldValue = newValue;

            $list.find(`option[value="${oldValue}"]`).prop('disabled', false);
            $list.find(`option[value="${newValue}"]`).prop('disabled', true);
            $list.find(`option[data-disabled]`).prop('disabled', true);

            const oldProductTypeId = this.productTypeId;
            const newProductTypeId = $(this).find('option:selected').data('product-type-id');
            this.productTypeId = newProductTypeId;

            if (oldProductTypeId) {
                $list.find(`[data-product-type-id="${oldProductTypeId}"]:not(:selected)`).prop('disabled', false);
            }

            if (newProductTypeId) {
                $list.find('.addon-service-id').not(this).find(`[data-product-type-id="${newProductTypeId}"]`).prop('disabled', true);
            }
        });

        $modal.on('keydown input', function(e) {
            e.preventDefault();
        });

        $body.on('submit', function(e) {
            e.preventDefault();
            data = Array.from($list.find('.addon-service')).map(function(item) {
                const $select = $(item).find('.addon-service-id')
                const $selectedOption = $select.find('option:selected');

                const addon_service_id = $select.val();
                const title = $selectedOption.data('title');
                const type = $selectedOption.data('type');
                const product_type_id = $selectedOption.data('product-type-id');
                const date_range = $(item).find('.addon-service-daterange').val();

                return {
                    addon_service_id,
                    type,
                    date_range,
                    title,
                    product_type_id,
                }
            });

            renderAddonTable();
            $.unwrapContent('addonInjection');
        });

        function appendAddonForm(addon) {
            let newTpl = template;

            $list.append(newTpl);
            const $lastService = $list.children().last();
            const $lastIdSelect = $lastService.find('.addon-service-id');

            if (addon) {
                const value = addon.addon_service_id;
                $lastIdSelect.val(value).get(0).oldValue = value;
                $lastService.find('.addon-service-daterange').val(addon.date_range);

                $lastIdSelect.val(value).get(0).productTypeId = addon.product_type_id || 0;
            }

            const selectedIds = Array.from($list.find('.addon-service-id')).map(item => item.oldValue);

            selectedIds.forEach((id) => {
                $list.find(`option[value="${id}"]`).prop('disabled', true);
                $list.find(`option[data-disabled]`).prop('disabled', true);
            });

            const productTypeIds = Array.from($list.find('.addon-service-id')).map(item => item.productTypeId);

            productTypeIds.forEach((id) => {
                if (id == 0) return;
                $list.find('.addon-service-id').each(function() {
                    if (this.productTypeId != id) {
                        $(this).find(`option[data-product-type-id="${id}"]`).prop('disabled', true);
                    } else {
                        $(this).find(`option[data-product-type-id="${id}"]:not(:selected)`).prop('disabled', false);
                    }
                });
            });

            $lastIdSelect.select2({
                placeholder: "Search for add-on services",
            });
            renameServiceLabels();
        }

        function renameServiceLabels() {
            $list.find('.addon-service-label').each(function(index) {
               $(this).html('Service ' + ('0' + (index + 1)).slice(-2));
            });
        }
    });

    renderAddonTable();

    function renderAddonTable() {
        $input.val(JSON.stringify(data));

        if (!data.length) {
            $popupBtn.html('Add new service');
            $enableSwitcher.hide();
            return $table.hide();
        }

        $popupBtn.html('Edit options');
        $table.show();
        $enableSwitcher.show();

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th width="40%">Service Title</th>
                        <th>Service Type</th>
                        <th>Test Date</th>
                        <th width="1%"></th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map((item) => `
                        <tr ${item.not_available ? 'style="color: #ccc;"' : ''}>
                            <td>${item.addon_service_id}</td>
                            <td>${item.title}${item.not_available ? ' (Not available)' : ''}</td>
                            <td>${item.type}</td>
                            <td>${item.date_range}</td>
                            <td><button type="button" class="btn btn-small btn-icon addon-service-remove" data-service-id="${item.addon_service_id}">${($.renderIcon('trash-can').outerHTML)}</button></td>
                        </tr>`).join('')}
                </tbody>
            </table>`;

        $table.html(html);
    }
}

window.initAddonDateRange = function () {
    var daterange_picker = $(this);
    var input = daterange_picker.find('input');

    var drops = $(this).data('drops') || 'auto';

    if (input.val()) {
        var splitted = input.val().split(/\s*\-\s*/i);
    } else {
        var splitted = [moment().format('DD/MM/YYYY')];
    }

    daterange_picker.daterangepicker({
        popupAttrs: {'data-menu-elm': 1},
        alwaysShowCalendars: true,
        startDate: moment(splitted[0], "DD/MM/YYYY"),
        endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
        drops: drops,
    }).bind('apply.daterangepicker', function (e, picker) {
        var value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');

        if (picker.endDate.isBefore(moment())) {
            alert('The end date cannot be earlier than the current date');
            e.preventDefault();
            return false;
        }

        input.val(value);
    });
};
