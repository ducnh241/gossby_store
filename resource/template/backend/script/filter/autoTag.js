window.initSettingFieldAutoTag = function () {
    let btn_setting_field = $(this)
    let setting_fields = [];
    try {
        setting_fields = fetchJSONTag($(this), "data")?.setting_fields;
    } catch (error) {
        console.log(error);
    }

    btn_setting_field.click(function () {
        $.unwrapContent('autoTagSettingField');
        let modal_form = $('<form />').addClass('osc-modal').width(500);

        let header = $('<header />').appendTo(modal_form);

        let save_btn = null;

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('autoTagSettingField');
        }).appendTo(header);

        $('<div />').addClass('title').html(btn_setting_field.attr('data-title')).appendTo($('<div />').addClass('main-group').appendTo(header));
        let modal_body = $('<div />').addClass('body post-frm').appendTo(modal_form);

        let list_fields = $('<div />')

        Object.keys(setting_fields).forEach(function (key) {
            let checkbox_container = $('<div />').addClass('mb5').appendTo(list_fields);

            let checkbox_elm = $('<div />').addClass('styled-checkbox').appendTo(checkbox_container);

            let id = $.makeUniqid();

            let input = $('<input />').attr({type: 'checkbox', name: setting_fields[key].key, 'data-text' : setting_fields[key].text, id: id}).appendTo(checkbox_elm).click(function () {
                setting_fields[key].value = $(this).is(":checked") ? 1 : 0;
            });

            if (parseInt(setting_fields[key].value)) {
                input.attr('checked', 'checked')
            } else {
                input.removeAttr('checked')
            }

            $('<ins />').append($.renderIcon('check-solid')).appendTo(checkbox_elm);

            $('<label />').attr('for', id).addClass('ml5 label-inline').text(setting_fields[key].text).appendTo(checkbox_container);
        })

        list_fields.appendTo(modal_body)

        let action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('autoTagSettingField');
        }).appendTo(action_bar);

        save_btn = $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {
            this.setAttribute('disabled', 'disabled');

            save_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

            $.ajax({
                type: 'POST',
                url: btn_setting_field.attr('data-process-url'),
                data: {
                    setting_fields
                },
                success: function (response) {
                    save_btn.removeAttr('disabled');
                    save_btn.find('svg').remove();

                    if (response.result !== 'OK') {
                        alert(response.message)
                        return;
                    }

                    window.location.reload(true);
                }
            });

        }).appendTo(action_bar);

        $.wrapContent(modal_form, {key: 'autoTagSettingField'});

        modal_form.moveToCenter().css('top', '100px');
    })
}