(function ($) {
    window.initReportFilterABTest = function () {
        var redirect_url = this.getAttribute('data-link');

        var ab_test_data = JSON.parse($(this).parent().find('[data-json="report_ab_test"]')[0].innerHTML);

        $(this).change(function (e, skip_redirect) {
            var ab_test_key = this.options[this.selectedIndex].value;

            if (typeof ab_test_data.keys[ab_test_key] === 'undefined') {
                if (!skip_redirect) {
                    // disable user group select box and show table loading overlay
                    disableUserGroupSelectBoxAndShowTableLoadingOverlay();

                    window.location = redirect_url;
                }

                return;
            }

            var container = $(this).parent().parent();

            if (container.next('div')[0]) {
                container = container.next('div').html('');
            } else {
                container = $('<div />').appendTo(container.parent());
            }

            var selector = $('<select />').appendTo($('<div />').addClass('styled-select styled-select--small').append($('<ins />')).appendTo(container));

            $('<option />').attr('value', '').text('Please select an option').appendTo(selector);

            ab_test_data.keys[ab_test_key].forEach(function (value) {
                var option = $('<option />').attr('value', value).text(value).appendTo(selector);

                if (ab_test_data.current && ab_test_key === ab_test_data.current.key && value === ab_test_data.current.value) {
                    option.attr('selected', 'selected');
                }
            });

            selector.change(function () {
                if (this.selectedIndex === 0) {
                    return;
                }

                redirect_url += (redirect_url.indexOf('?') >= 0 ? '&' : '?') + 'ab_test_key=' + ab_test_key + '&ab_test_value=' + this.options[this.selectedIndex].value;

                // disable user group select box and show table loading overlay
                disableUserGroupSelectBoxAndShowTableLoadingOverlay();

                window.location = redirect_url;
            });
        }).trigger('change', [true]);
    };

    window.initReportFilterMember = function () {
        $(this).change(function (e, skip_redirect) {
            window.location = $(this).children("option:selected").attr('data-link');
        });
    };

    window.initReportFilterGroup = function () {
        $(this).change(function () {
            // disable user group select box and show table loading overlay
            disableUserGroupSelectBoxAndShowTableLoadingOverlay();

            // change location to page in link
            window.location = $(this).children("option:selected").attr('data-link');
        })
    }

    window.initReportCustomDate = function () {
        var node = $(this);

        if (this.getAttribute('data-begin') !== '') {
            var splitted = [this.getAttribute('data-begin'), this.getAttribute('data-end')];
        } else {
            var splitted = [moment().format('DD/MM/YYYY')];
        }

        $(this).daterangepicker({
            popupAttrs: {'data-menu-elm': 1},
            alwaysShowCalendars: true,
            startDate: moment(splitted[0], "DD/MM/YYYY"),
            endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }).bind('apply.daterangepicker', function (e, picker) {
            if (parseInt(picker.startDate.format('YYYYMMDD')) === parseInt(picker.endDate.format('YYYYMMDD'))) {
                var value = picker.startDate.format('DD/MM/YYYY');
            } else {
                var value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            }

            var url = node.attr('data-link');

            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'range=' + value;

            // disable user group select box and show table loading overlay
            disableUserGroupSelectBoxAndShowTableLoadingOverlay();

            window.location = url;
        });
    };

    window.reportInitShoppingBehavior = function () {
        var chart = $(this);

        var max_value = parseInt(this.getAttribute('data-max'));
        var items = chart.find('> [data-value]');

        items.each(function () {
            var value = parseInt(this.getAttribute('data-value'));

            if (isNaN(value)) {
                value = 0;
            }

            var percent = Math.min(100, value / max_value * 100);

            $('<div />').addClass('bar').css('height', 'calc(' + percent + '% + 1px)').prependTo(this);

            var next_item = $(this).next('[data-value]');

            if (next_item[0]) {
                var next_value = parseInt(next_item.attr('data-value'));

                if (isNaN(next_value)) {
                    next_value = 0;
                }

                var next_percent = Math.min(100, next_value / max_value * 100);

                var conversion_rate = $.round(next_value / value * 100, 2);

                $('<div />').addClass('conversion-rate').text(conversion_rate + '%').prependTo(this);
                $('<div />').addClass('tunel').css('height', 'calc(' + next_percent + '% + 1px)').prependTo(this);
                $('<div />').addClass('tunel-top').css('bottom', next_percent + '%').prependTo(this).bind('update', function () {
                    var height = parseInt(Math.abs(percent - next_percent) / 100 * this.parentNode.getBoundingClientRect().height);
                    var width = parseInt($(this.parentNode).find('.tunel')[0].getBoundingClientRect().width);

                    if (width > 2 && height > 2) {
                        width = width + (width % 2);
                        height = height + (height % 2);

                        $(this).show().css({
                            borderLeftWidth: (width / 2) + 'px',
                            borderRightWidth: (width / 2) + 'px',
                            borderTopWidth: (height / 2) + 'px',
                            borderBottomWidth: (height / 2) + 'px'
                        });
                    } else {
                        $(this).hide();
                    }
                }).trigger('update');
            }
        });

        $(window).resize(function () {
            chart.find('.tunel-top').trigger('update');
        });
    };

    window.initAdTrackingCampaign = function() {
        const range = $(this).attr('data-range');
        const is_search = $('#is_search').val(),
            sref_group_id = $('#sref_group_id').val(),
            sref_member_id = $('#sref_member_id').val();
        let selectedCampaignID = [];
        let selectedAdsetID = [];
        const inputList = $('#campaign-list').find('input');

        inputList.change(function() {
            let selectedCampaign = 0;
            selectedCampaignID = [];
            inputList.each(function() {
                if ($(this).is(":checked")) {
                    selectedCampaign += 1;
                    selectedCampaignID.push($(this).attr('data-id'));
                }
            })

            if (selectedCampaign) {
                $('#count-selected-campaign').text(`${selectedCampaign} selected`).removeClass('d-none');
                $('[data-id="campaign-detail"]').text(`Ad Sets for ${selectedCampaign} Campaign`)
            } else {
                $('#count-selected-campaign').addClass('d-none');
                $('[data-id="campaign-detail"]').text(`Ad Sets`)
            }
        })

        $('.ads-tab-container__item').click(function(){
            $('.ads-tab-container__item').removeClass('active');
            $(this).addClass('active');
            const idContent = $(this).attr('data-id');
            $('.ads-tab-content').addClass('d-none');
            $(`#${idContent}`).removeClass('d-none');
            if (idContent === 'campaign-detail') {
                const container = $('#campaign-detail');
                _loading(container);
                adTrackingAPI({
                    campaign_ids: selectedCampaignID,
                    data_type: 'adsets',
                }, res => {
                    renderDetail(container, res.data, idContent);
                })
            } else if (idContent === 'adset-detail') {
                selectedAdsetID = [];
                $('#campaign-detail input:checked').each(function(){
                    selectedAdsetID.push($(this).attr('data-id'));
                })
                const container = $('#adset-detail');
                _loading(container);
                adTrackingAPI({
                    adset_id: selectedAdsetID,
                    campaign_ids: selectedCampaignID,
                    data_type: 'ads'
                }, res => {
                    renderDetail(container, res.data, idContent);
                })
            }
        })

        $(document).on('change', $('#campaign-detail').find('input'), function() {
            const adsetsList = $('#campaign-detail').find('input');
            let selectedAdsets = 0;
            adsetsList.each(function() {
                if ($(this).is(":checked")) selectedAdsets += 1;
            })

            if (selectedAdsets) {
                $('[data-id="adset-detail"]').text(`Ads for ${selectedAdsets} Ad sets`)
            } else {
                $('[data-id="adset-detail"]').text(`Ads`)
            }
        })

        const _loading = (container) => {
            container.find('tbody').html($('<tr/>').append($('<td/>').attr('colspan', 8).css('text-align', 'center').html($('<div />').addClass('loading').css({width: '50px', margin: '0 auto'}).text('Loading...').prepend($.renderIcon('preloader')))));
        }

        const renderDetail = (container, data, type) => {
            const tbody = container.find('tbody');
            const campaignIdList = container.find('.campaign-id');
            tbody.empty();
            campaignIdList.empty();
            container.find('.pagination-bar').remove();
            container.find('.no-result').remove();

            let selectedCampaign = 0;
            $('#campaign-list').find('input').each(function() {
                if ($(this).is(":checked")) selectedCampaign += 1;
            })

            let selectedAdsets = 0;
            $('#campaign-detail').find('input').each(function() {
                if ($(this).is(":checked")) selectedAdsets += 1;
            })

            campaignIdList.text(data.action == 'adTrackingCampaignDetail' ? (selectedCampaign === 0 ? 'All campaigns' : selectedCampaignID.join(', ')) : (selectedAdsets === 0 ? 'All Adsets' : data.adset_id.join(', ')))
            if (data.data.length) {
                renderTotal(tbody, data.total);
                data.data.forEach(item => renderRow(tbody, type, item));
                const pagination = buildPager(data.current_page, data.total.total_record, data.page_size);
                if (pagination) {
                    $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(container);
                    pagination.find('[data-page]:not(.current)').click(function (e) {
                        e.preventDefault();
                        let data_type = data.action == 'adTrackingCampaignDetail' ? 'adsets' : 'ads';
                        const page = this.getAttribute('data-page');
                        const params = data.adset_id.length > 0 ? {
                            page, adset_id: selectedAdsetID,
                            campaign_ids: selectedCampaignID,
                            data_type: data_type
                        } : {
                            campaign_ids: selectedCampaignID,
                            data_type: data_type,
                            page,
                        }
                        _loading(container);
                        adTrackingAPI(params, res => {
                            renderDetail(container, res.data, type);
                        })
                    });
                }
            } else {
                $('<div class="no-result">No data to display</div>').appendTo(container)
            }
        }

        const renderTotal = (container, data) => {
            const tr = $('<tr/>').appendTo(container);
            $('<th/>').css('text-align', 'left').html('Total ' + data.total_record + ((data.total_record > 1) ? ' records' : ' record')).appendTo(tr);
            $('<th/>').css('text-align', 'left').text(data.total_view).appendTo(tr);
            $('<th/>').css('text-align', 'left').text(data.total_add_to_cart).appendTo(tr);
            $('<th/>').css('text-align', 'left').text(data.total_checkout_initialize_count).appendTo(tr);
            $('<th/>').css('text-align', 'left').text(data.total_purchase_count).appendTo(tr);
            $('<th/>').css('text-align', 'left').text(data.total_sale_count).appendTo(tr);
            $('<th/>').css('text-align', 'left').text((data.total_purchase_count/data.total_view * 100).toFixed(2) + '%').appendTo(tr);
            $('<th/>').css('text-align', 'left').html(data.total_subtotal_revenue_format).appendTo(tr);
            $('<th/>').css('text-align', 'left').html(data.total_revenue_format).appendTo(tr);
        }

        const renderRow = (container, type, data) => {
            const tr = $('<tr/>').appendTo(container);
            const idCol = $('<td/>').appendTo(tr);
            const _label = $('<label/>').attr('for', 'adsets_' + data._id).appendTo(idCol);
            const _div = $('<div />').appendTo(_label);
            if (type !== 'adset-detail') {
                $('<div/>').html('<b>' + data.adset_name + '</b>').prependTo(_label);
                const input = $('<input/>').attr({
                    type: 'checkbox',
                    'data-id': data._id,
                    id: 'adsets_' + data._id
                }).appendTo(_div);
                $('<span/>').text(data._id).appendTo(_div);
                if (type == 'campaign-detail' && selectedAdsetID.includes(data._id)) input.prop('checked', true);
            } else {
                $('<div/>').html('<b>' + data.ad_name + '</b>').prependTo(_label);
                $('<span/>').text(data._id).appendTo(_div);
            }

            $('<td/>').text(data.product_view_count).appendTo(tr);
            $('<td/>').text(data.add_to_cart_count).appendTo(tr);
            $('<td/>').text(data.checkout_initialize_count).appendTo(tr);
            $('<td/>').text(data.purchase_count).appendTo(tr);
            $('<td/>').text(data.sale_count).appendTo(tr);
            $('<td/>').text((data.purchase_count/data.product_view_count * 100).toFixed(2) + '%').appendTo(tr);
            $('<td/>').html(data.subtotal_revenue_format).appendTo(tr);
            $('<td/>').html(data.revenue_format).appendTo(tr);
        }
        
        const adTrackingAPI = (data, success) => {
            $.ajax({
                type: "POST",
                url: $.base_url + '/srefReport/backend/adTrackingData/hash/' + OSC_HASH,
                data: {
                    ...data,
                    range,
                    is_search,
                    sref_member_id,
                    sref_group_id
                },
                success,
            });
        }
    };

    window.initReportFilterByDateCondition = function () {
        $(this).find('a').on('click', function () {
            const node = $(this);
            const url = node.data('link');
            const begin = node.data('begin');
            const end = node.data('end');

            if (url && begin === undefined && end === undefined) {
                // disable user group select box and show table loading overlay
                disableUserGroupSelectBoxAndShowTableLoadingOverlay();

                // redirect to link in data
                window.location = url;
            }
        });
    };

    /**
     * disable user group select box and show table loading overlay
     */
    const disableUserGroupSelectBoxAndShowTableLoadingOverlay = function () {
        // disable select box when change
        $('.select_user_group').prop('disabled', true);

        // display wrapper
        $('.loading-overlay-wrapper').show();

        // hide product list table
        $('.loading-wrapper').hide();
    };

    /**
     * event when click sidebar
     */
    $(document).find('.sidebar li > a').on('click', function () {
        // disable user group select box and show table loading overlay
        disableUserGroupSelectBoxAndShowTableLoadingOverlay();
    });

    window.handleProductAttributeSelector = function () {
        $(this).change(function () {
            const productAttributeList = $(this).val();

            // save value to local storage
            localStorage.setItem('report_detail_product_table_filter_options', JSON.stringify(productAttributeList));

            // render report table
            displayReportTable();
        });

        // handle default value
        const filterOptionsSelector = $('#filter-options-selector');
        const jsonTableFilterOptions = localStorage.getItem('report_detail_product_table_filter_options');
        const tableFilterOptions = JSON.parse(jsonTableFilterOptions);

        if (tableFilterOptions) {
            filterOptionsSelector.val(tableFilterOptions).change();
        } else {
            // render report table
            displayReportTable();
        }
    };

    const displayReportTable = async function () {
        const jsonTableFilterOptions = localStorage.getItem('report_detail_product_table_filter_options');
        const productReportTableSelector = $('#product-report-table');
        const productVariantList = productReportTableSelector.data('tableData');

        if (!jsonTableFilterOptions) {
            await render3OptionsTable(productVariantList);

            // handle sort by config
            handleSortReportDetailProductTableEvent();
            return;
        }

        const tableFilterOptions = JSON.parse(jsonTableFilterOptions);

        if (!tableFilterOptions) {
            await render3OptionsTable(productVariantList);

            // handle sort by config
            handleSortReportDetailProductTableEvent();
            return;
        }

        switch (tableFilterOptions.length) {
            case 3: // contains 3 options: variant, billing_country, sref
                await render3OptionsTable(productVariantList);
                break;
            case 2: // contains 2 options in: variant, billing_country, sref
                await render2OptionsTable(productVariantList, tableFilterOptions);
                break;
            case 1: // contains 1 option: variant or billing_country or sref
                await render1OptionsTable(productVariantList, tableFilterOptions);
                break;
        }

        // handle sort by config
        handleSortReportDetailProductTableEvent();
    };

    const render3OptionsTable = async function (productVariantList) {
        const productReportTableSelector = $('#product-report-table');
        const displayList = JSON.parse(JSON.stringify(productVariantList));

        let tableHtml = `<tr>
            <th style="text-align: left; max-width: 100px">Variant ID</th>
            <th style="text-align: left">Title</th>
            <th style="text-align: left; max-width: 150px">SKU</th>
            <th style="text-align: left; max-width: 150px">Billing Country</th>
            <th style="text-align: left; max-width: 150px">Sref</th>
            <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
            <th style="text-align: right; max-width: 100px">Profit</th>
        </tr>`;

        // sort table
        sortReportDetailProductTable(displayList, productVariantList);

        // display table
        for (const [index, variant] of Object.entries(displayList)) {
            const revenue = Math.round(variant['revenue'] * Math.pow(10, 2)) / Math.pow(10, 2);

            tableHtml += `<tr style="cursor: pointer">
                <td style="text-align: left">${variant['variant_id']}</td>
                <td style="text-align: left">${variant['title']}</td>
                <td style="text-align: left">${variant['sku']}</td>
                <td style="text-align: left">${variant['billing_country']}</td>
                <td style="text-align: left">${variant['username'] || ''}</td>
                <td style="text-align: left">${variant['sales']}</td>
                <td style="text-align: right">$${revenue}</td>
            </tr>`;
        }

        productReportTableSelector.html(tableHtml);
    };

    const render2OptionsTable = function (productVariantList, tableFilterOptions) {
        const productReportTableSelector = $('#product-report-table');
        let tableHtml = '';
        let displayList = [];

        if (tableFilterOptions.includes('variant') && tableFilterOptions.includes('billing_country')) {
            for (const [index, variant] of Object.entries(productVariantList)) {
                if (!variant?.variant_id && !variant.member_id) {
                    continue;
                }

                let isExisted = false;
                let cloneVariant = JSON.parse(JSON.stringify(variant));

                for (const displayData of displayList) {
                    if (displayData.variant_id === cloneVariant.variant_id && displayData.billing_country === cloneVariant.billing_country) {
                        isExisted = true;
                        displayData.sales += cloneVariant.sales;
                        displayData.revenue = plusVariables(displayData.revenue, cloneVariant.revenue);
                    }
                }

                if (!isExisted) {
                    displayList.push(cloneVariant);
                }
            }

            tableHtml = `<tr>
                <th style="text-align: left; max-width: 100px">Variant ID</th>
                <th style="text-align: left">Title</th>
                <th style="text-align: left; max-width: 150px">SKU</th>
                <th style="text-align: left; max-width: 150px">Billing Country</th>
                <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
                <th style="text-align: right; max-width: 100px">Profit</th>
            </tr>`;

            // sort table
            sortReportDetailProductTable(displayList, productVariantList);

            for (const displayData of displayList) {
                tableHtml += `<tr style="cursor: pointer">
                    <td style="text-align: left">${displayData['variant_id']}</td>
                    <td style="text-align: left">${displayData['title']}</td>
                    <td style="text-align: left">${displayData['sku']}</td>
                    <td style="text-align: left">${displayData['billing_country']}</td>
                    <td style="text-align: left">${displayData['sales']}</td>
                    <td style="text-align: right">$${displayData['revenue']}</td>
                </tr>`;
            }

            productReportTableSelector.html(tableHtml);
        } else if (tableFilterOptions.includes('variant') && tableFilterOptions.includes('sref')) {
            for (const [index, variant] of Object.entries(productVariantList)) {
                if (!variant?.variant_id && !variant.member_id) {
                    continue;
                }

                let isExisted = false;
                let cloneVariant = JSON.parse(JSON.stringify(variant));

                for (const displayData of displayList) {
                    if (displayData.variant_id === cloneVariant.variant_id && displayData.member_id === cloneVariant.member_id) {
                        isExisted = true;
                        displayData.sales += cloneVariant.sales;
                        displayData.revenue = plusVariables(displayData.revenue, cloneVariant.revenue);
                    }
                }

                if (!isExisted) {
                    displayList.push(cloneVariant);
                }
            }

            tableHtml = `<tr>
                <th style="text-align: left; max-width: 100px">Variant ID</th>
                <th style="text-align: left">Title</th>
                <th style="text-align: left; max-width: 150px">SKU</th>
                <th style="text-align: left; max-width: 150px">Sref</th>
                <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
                <th style="text-align: right; max-width: 100px">Profit</th>
            </tr>`;

            // sort table
            sortReportDetailProductTable(displayList, productVariantList);

            for (const displayData of displayList) {
                tableHtml += `<tr style="cursor: pointer">
                    <td style="text-align: left">${displayData['variant_id']}</td>
                    <td style="text-align: left">${displayData['title']}</td>
                    <td style="text-align: left">${displayData['sku']}</td>
                    <td style="text-align: left">${displayData['username'] || ''}</td>
                    <td style="text-align: left">${displayData['sales']}</td>
                    <td style="text-align: right">$${displayData['revenue']}</td>
                </tr>`;
            }

            productReportTableSelector.html(tableHtml);
        } else if (tableFilterOptions.includes('billing_country') && tableFilterOptions.includes('sref')) {
            for (const [index, variant] of Object.entries(productVariantList)) {
                if (!variant?.variant_id && !variant.member_id) {
                    continue;
                }

                let isExisted = false;
                let cloneVariant = JSON.parse(JSON.stringify(variant));

                for (const displayData of displayList) {
                    if (displayData.billing_country === cloneVariant.billing_country && displayData.member_id === cloneVariant.member_id) {
                        isExisted = true;
                        displayData.sales += cloneVariant.sales;
                        displayData.revenue = plusVariables(displayData.revenue, cloneVariant.revenue);
                    }
                }

                if (!isExisted) {
                    displayList.push(cloneVariant);
                }
            }

            tableHtml = `<tr>
                <th style="text-align: left; max-width: 150px">Billing Country</th>
                <th style="text-align: left; max-width: 150px">Sref</th>
                <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
                <th style="text-align: right; max-width: 100px">Profit</th>
            </tr>`;

            // sort table
            sortReportDetailProductTable(displayList, productVariantList);

            for (const displayData of displayList) {
                tableHtml += `<tr style="cursor: pointer">
                    <td style="text-align: left">${displayData['billing_country'] || ''}</td>
                    <td style="text-align: left">${displayData['username'] || ''}</td>
                    <td style="text-align: left">${displayData['sales']}</td>
                    <td style="text-align: right">$${displayData['revenue']}</td>
                </tr>`;
            }

            productReportTableSelector.html(tableHtml);
        }
    };

    const render1OptionsTable = function (productVariantList, tableFilterOptions) {
        const productReportTableSelector = $('#product-report-table');
        let tableHtml = '';
        let displayList = [];

        if (tableFilterOptions.includes('variant')) {
            for (const [index, variant] of Object.entries(productVariantList)) {
                if (!variant?.variant_id && !variant.member_id) {
                    continue;
                }

                let isExisted = false;
                let cloneVariant = JSON.parse(JSON.stringify(variant));

                for (const displayData of displayList) {
                    if (displayData.variant_id === cloneVariant.variant_id) {
                        isExisted = true;
                        displayData.sales += cloneVariant.sales;
                        displayData.revenue = plusVariables(displayData.revenue, cloneVariant.revenue);
                    }
                }

                if (!isExisted) {
                    displayList.push(cloneVariant);
                }
            }

            tableHtml = `<tr>
                <th style="text-align: left; max-width: 100px">Variant ID</th>
                <th style="text-align: left">Title</th>
                <th style="text-align: left; max-width: 150px">SKU</th>
                <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
                <th style="text-align: right; max-width: 100px">Profit</th>
            </tr>`;

            // sort table
            sortReportDetailProductTable(displayList, productVariantList);

            for (const displayData of displayList) {
                tableHtml += `<tr style="cursor: pointer">
                    <td style="text-align: left">${displayData['variant_id']}</td>
                    <td style="text-align: left">${displayData['title']}</td>
                    <td style="text-align: left">${displayData['sku']}</td>
                    <td style="text-align: left">${displayData['sales']}</td>
                    <td style="text-align: right">$${displayData['revenue']}</td>
                </tr>`;
            }

            productReportTableSelector.html(tableHtml);
        } else if (tableFilterOptions.includes('billing_country')) {
            for (const [index, variant] of Object.entries(productVariantList)) {
                if (!variant?.variant_id && !variant.member_id) {
                    continue;
                }

                let isExisted = false;
                let cloneVariant = JSON.parse(JSON.stringify(variant));

                for (const displayData of displayList) {
                    if (displayData.billing_country === cloneVariant.billing_country) {
                        isExisted = true;
                        displayData.sales += cloneVariant.sales;
                        displayData.revenue = plusVariables(displayData.revenue, cloneVariant.revenue);
                    }
                }

                if (!isExisted) {
                    displayList.push(cloneVariant);
                }
            }

            tableHtml = `<tr>
                <th style="text-align: left; max-width: 150px">Billing Country</th>
                <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
                <th style="text-align: right; max-width: 100px">Profit</th>
            </tr>`;

            // sort table
            sortReportDetailProductTable(displayList, productVariantList);

            for (const displayData of displayList) {
                tableHtml += `<tr style="cursor: pointer">
                    <td style="text-align: left">${displayData['billing_country']}</td>
                    <td style="text-align: left">${displayData['sales']}</td>
                    <td style="text-align: right">$${displayData['revenue']}</td>
                </tr>`;
            }

            productReportTableSelector.html(tableHtml);
        } else if (tableFilterOptions.includes('sref')) {
            for (const [index, variant] of Object.entries(productVariantList)) {
                if (!variant?.variant_id && !variant.member_id) {
                    continue;
                }

                let isExisted = false;
                let cloneVariant = JSON.parse(JSON.stringify(variant));

                for (const displayData of displayList) {
                    if (displayData.member_id === cloneVariant.member_id) {
                        isExisted = true;
                        displayData.sales += cloneVariant.sales;
                        displayData.revenue = plusVariables(displayData.revenue, cloneVariant.revenue);
                    }
                }

                if (!isExisted) {
                    displayList.push(cloneVariant);
                }
            }

            tableHtml = `<tr>
                <th style="text-align: left; max-width: 150px">Sref</th>
                <th class="sold-column" style="text-align: left; max-width: 100px; cursor: pointer">Solds${getSortIconHtml()}</th>
                <th style="text-align: right; max-width: 100px">Profit</th>
            </tr>`;

            // sort table
            sortReportDetailProductTable(displayList, productVariantList);

            for (const displayData of displayList) {
                tableHtml += `<tr style="cursor: pointer">
                    <td style="text-align: left">${displayData['username'] || ''}</td>
                    <td style="text-align: left">${displayData['sales']}</td>
                    <td style="text-align: right">$${displayData['revenue']}</td>
                </tr>`;
            }

            productReportTableSelector.html(tableHtml);
        }
    };

    const plusVariables = function (firstParameter, secondParameter) {
        return (Math.round(firstParameter * Math.pow(10, 2)) + Math.round(secondParameter * Math.pow(10, 2))) / Math.pow(10, 2);
    };

    const getDownAngleIcon = function () {
        return $.renderIcon('angle-down-solid');
    };

    const getUpAngleIcon = function () {
        return $.renderIcon('angle-up-solid');
    };

    const getSortIconHtml = function () {
        const reportTableSortConfig = getReportDetailProductTableSortConfig();
        const sortRow = Object.keys(reportTableSortConfig)[0] || undefined;
        const sortType = reportTableSortConfig[sortRow] || undefined;

        switch (sortType) {
            case 'DESC':
                return getDownAngleIcon().outerHTML;
            case 'ASC':
                return getUpAngleIcon().outerHTML;
            default:
                return '';
        }
    }

    const getReportDetailProductTableConfig = function () {
        const defaultValue = {
            'sort': {
                'sales': 'DESC'
            }
        };

        const reportTableConfig = localStorage.getItem('report_detail_product_table_config');
        if (!reportTableConfig) {
            localStorage.setItem('report_detail_product_table_config', JSON.stringify(defaultValue));

            return defaultValue;
        }

        return JSON.parse(reportTableConfig);
    };

    const getReportDetailProductTableSortConfig = function () {
        const reportTableConfig = getReportDetailProductTableConfig();

        return reportTableConfig.sort;
    };

    const sortReportDetailProductTable = function (tableData, defaultTableData) {
        const reportTableSortConfig = getReportDetailProductTableSortConfig();
        const sortRow = Object.keys(reportTableSortConfig)[0] || undefined;
        const sortType = reportTableSortConfig[sortRow] || undefined;

        if (!sortRow || !sortType) {
            tableData = JSON.parse(JSON.stringify(defaultTableData));
            return;
        }

        tableData.sort(function (row1, row2) {
            if (row1[sortRow] === row2[sortRow] &&
                row1[sortRow] === 0 &&
                row2[sortRow] === 0) {
                return 0;
            }

            if (sortType === 'DESC') {
                return row1[sortRow] < row2[sortRow] ? 1 : -1;
            } else if (sortType === 'ASC') {
                return row1[sortRow] > row2[sortRow] ? 1 : -1;
            }

            return 0;
        });
    };

    const handleSortReportDetailProductTableEvent = function () {
        $('.sold-column').click(function () {
            const reportTableConfig = getReportDetailProductTableConfig();
            const sortRow = Object.keys(reportTableConfig.sort)[0] || undefined;
            const sortType = reportTableConfig.sort[sortRow] || undefined;

            switch (sortType) {
                case 'DESC':
                    reportTableConfig.sort[sortRow] = 'ASC';
                    break;
                case 'ASC':
                    reportTableConfig.sort[sortRow] = '';
                    break;
                default:
                    reportTableConfig.sort[sortRow] = 'DESC';
            }

            localStorage.setItem('report_detail_product_table_config', JSON.stringify(reportTableConfig));
            displayReportTable();
        });
    }
})(jQuery);
