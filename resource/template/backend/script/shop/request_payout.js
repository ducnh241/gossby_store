(function ($) {
    'use strict';

    window.initPopupPayout = function () {
        $(this).click(function () {
                let shop_id = $(this).attr('data-shop-id');

                $.unwrapContent('payment');

                let modal = $('<div />').addClass('osc-modal').width(450);
                $.wrapContent(modal, {key: 'payment', backdrop: 'static'});
                let header = $('<header />').appendTo(modal);
                $('<div />').addClass('title').attr({style: 'text-align: center'}).html('Choose payment provider').appendTo($('<div />').addClass('main-group').appendTo(header));
                $('<div />').addClass('close-btn').click(function () {
                    $.unwrapContent('payment');
                }).appendTo(header);
                let modal_body = $('<div />').addClass('body post-frm post-frm-section-location').appendTo(modal);
                let modal_body_section = $('<div />').addClass('body post-frm-colleft').appendTo(modal_body);

                let row = $('<div />').addClass('mt10 list-payout-provider').appendTo(modal_body_section);

                let list_acc = $('<div />').addClass('list-account').appendTo(modal_body_section);

                $('<div />').addClass('payout_provider payoneer').click(function () {
                    if (!$(this).hasClass('choose')) {
                        $('.payout_provider').removeClass('choose');
                        $(this).addClass('choose');
                        $('.list-account').html('');
                        $.ajax({
                            url: $.base_url + '/shop/backend_account/getListAccount/hash/' + OSC_HASH,
                            data: {type: 'payoneer'},
                            method: 'POST',
                            dataType: "json",
                            success: function (response) {
                                renderSelectAccount(response.data.list_account, list_acc);
                            }
                        });
                    }
                }).appendTo(row);

                $('<div />').addClass('payout_provider pingpong').click(function () {
                    if (!$(this).hasClass('choose')) {
                        $('.payout_provider').removeClass('choose');
                        $(this).addClass('choose');
                        $('.list-account').html('');
                        $.ajax({
                            url: $.base_url + '/shop/backend_account/getListAccount/hash/' + OSC_HASH,
                            data: {type: 'pingpong'},
                            method: 'POST',
                            dataType: "json",
                            success: function (response) {
                                renderSelectAccount(response.data.list_account, list_acc);
                            }
                        });
                    }
                }).appendTo(row);

                $.ajax({
                    url: $.base_url + '/shop/backend_account/getListAccount/hash/' + OSC_HASH,
                    data: {type: 'payoneer'},
                    method: 'POST',
                    dataType: "json",
                    success: function (response) {
                        if (response.data.list_account != '') {
                            $('.payout_provider').removeClass('choose');
                            $('.payout_provider.payoneer').addClass('choose');
                            renderSelectAccount(response.data.list_account, list_acc);
                        } else {
                            $.ajax({
                                url: $.base_url + '/shop/backend_account/getListAccount/hash/' + OSC_HASH,
                                data: {type: 'pingpong'},
                                method: 'POST',
                                dataType: "json",
                                success: function (response) {
                                    if (response.data.html != '') {
                                        $('.payout_provider').removeClass('choose');
                                        $('.payout_provider.pingpong').addClass('choose');
                                        renderSelectAccount(response.data.list_account, list_acc);
                                    }
                                }
                            });
                        }
                    }
                });

                let amount = $('<div />').addClass('amount-payout').appendTo(modal_body_section);
                $('<b />').text('Amount ').appendTo(amount);
                $('<span />').text('$ ').appendTo(amount);
                let amount_withdraw = $('.available_withdraw span').html();
                $('<input />').attr({
                    name: 'amount',
                    value: amount_withdraw.replace("$", ""),
                    style: 'width: 18%;',
                    id: 'input_amount_payout'
                }).addClass('styled-input').appendTo(amount);

                let action_bar = $('<div />').addClass('action-bar').attr({style: 'margin-right: 4px;'}).appendTo(modal_body_section);
                $('<button />').addClass('btn btn-outline').attr({style: 'height: 40px;width: 100px;'}).text('Cancel').click(() => $.unwrapContent('payment')).appendTo(action_bar);
                $('<button />').addClass('btn btn-payment ml10').html('Payout').click(function () {
                    let account_id = $('select[name="account_payout"]').val();
                    let amount_post = $('input[name="amount"]').val().replace(",", "");
                    if (account_id && amount_post) {
                        if (Number(amount_post) > 0 && Number(amount_post) <= Number(amount_withdraw.replace("$", "").replace(",", ""))) {
                            $.ajax({
                                url: $.base_url + '/shop/backend_request/post/hash/' + OSC_HASH,
                                data: {payout_account_id: account_id, amount: amount_post, submit_form: 1},
                                method: 'POST',
                                success: function (response) {
                                    alert('Request has been created.');
                                    window.location.reload();
                                }
                            });
                        } else {
                            alert('Invalid amount');
                        }
                    }
                }).appendTo(action_bar);

                modal.moveToCenter().css('top', '100px');

                $("#input_amount_payout").inputFilter(function (value) {
                    return /^-?\d*[.,]?\d*$/.test(value) && (value === "" || parseFloat(value) <= parseFloat(amount_withdraw.replace("$", "").replace(",", "")));
                });
            }
        );
    };

    const changePayoutStatusSuccessModal = (id, status, amount) => {
        $.unwrapContent('changePayoutStatusSuccessModalFrm');
        let modal = $('<div />').addClass('osc-modal edit-variants-success-modal').width(450);
        let header = $('<header />').appendTo(modal);

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('changePayoutStatusSuccessModalFrm');
        }).appendTo(header);
        $($.renderIcon('popup-warning')).appendTo(modal);
        let _status_trans = (status === 'resolved') ? 'transfer' : 'refund';
        $('<p>').html('Are you sure you want to cancel this request?').appendTo(modal);
        $('<span>').css('margin-bottom', '15px').addClass('btn btn-primary btn-small ml5').text('Confirm').on('click', function () {
            $.ajax({
                url: $.base_url + '/shop/backend_request/cancel/hash/' + OSC_HASH,
                data: {
                    id: id,
                    amount: amount
                },
                method: 'POST',
                success: function (response) {
                    alert('Cancel request success');
                    window.location.reload();
                },
                error: function (data) {
                    alert('Error! Please try again!');
                }
            });
        }).appendTo(modal);
        $.wrapContent(modal, {key: 'changePayoutStatusSuccessModalFrm'});
        modal.moveToCenter().css('top', '100px');
    }

    const renderSelectAccount = (list_account, list_acc_location) => {

        let selet_label = $('<label />').attr({style: 'width: 35%;float: left;line-height: 37px;font-size: 14px;'}).text('Select Account').appendTo(list_acc_location);
        let select_div = $('<div />').addClass('styled-select').attr({style: 'width: 65%;display: inline-block;'}).appendTo(list_acc_location);

        let select = $('<select />').attr({name: 'account_payout'}).appendTo(select_div);
        $.each(list_account, function (k, v) {

            if (v.data.default_flag == 1) {
                $('<option />').attr({
                    'value': v.data.account_id,
                    'selected': 'selected'
                }).text(v.data.title).appendTo(select);
            } else {
                $('<option />').attr({
                    'value': v.data.account_id
                }).text(v.data.title).appendTo(select);
            }
        });

        $('<ins />').appendTo(select_div);
    }

    $(document).on('click', '.cancel_payout_requeset', function () {
        let payout_request_id = $(this).data('payout-request-id'),
            amount = $(this).data('amount');
        changePayoutStatusSuccessModal(payout_request_id, 'cancelled', amount);
    });

    $(document).on('click', '.refresh', function () {
        window.location.reload();
    });

    $.fn.inputFilter = function (inputFilter) {
        return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
            if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
            } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
            } else {
                this.value = "";
            }
        });
    };

    window.initAccBulkDeleteBtn = function () {
        $(this).click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            let ids = [];

            $('input[name="post_id"]:checked').each(function () {
                ids.push(this.value);
            });

            if (ids.length < 1) {
                alert('Please select least a account to delete');
                return;
            }

            if (this.getAttribute('data-confirm')) {
                if (!window.confirm(this.getAttribute('data-confirm'))) {
                    return;
                }
            }

            this.setAttribute('disabled', 'disabled');

            let btn = this;

            $.ajax({
                url: this.getAttribute('data-link'),
                data: {ids: ids},
                success: function (response) {
                    btn.removeAttribute('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    alert(response.data.message);

                    window.location.reload();
                }
            });
        });
    };

})
(jQuery);


