window.initD2ResourceAddCondition = function () {
    const btn_add_condition = $('#add-condition')
    const conditions = fetchJSONTag($(this), "data")?.conditions;

    Object.keys(conditions).forEach(function (index) {
        _renderConditionElement(conditions[index].key, conditions[index].value)
    })

    btn_add_condition.click( function () {
        _renderConditionElement(null, null)
    })
}

function _renderConditionElement (key_name = null, value = null) {
    const new_index = parseInt($('#list-conditions').attr('data-condition_item') || 0) + 1
    const input_condition = $('<div />').addClass('input-condition')
        .append(
            $('<div />').append($('<label />').addClass('required').attr({"for": `key${new_index}`}).text('Airtable field'))
                .append($('<input />').addClass('styled-input').attr({
                    'type': 'text',
                    'name': `conditions[${new_index}][key]`,
                    'id': `key${new_index}`,
                    'value': key_name,
                    'required': true
                }))
        )
        .append(
            $('<div />').append($('<label />').addClass('required').attr({"for": `value${new_index}`}).text('Value'))
                .append($('<input />').addClass('styled-input').attr({
                    'type': 'text',
                    'name': `conditions[${new_index}][value]`,
                    'id': `value${new_index}`,
                    'value': value,
                    'required': true
                }))
        )
    const delete_icon = $('<div />').addClass('btn btn-small btn-icon ml5 mt25').append($.renderIcon('trash-alt-regular')).click(function () {
        $(this).parent().remove()
    })
    const condition = $('<div />').addClass('mt20').append(input_condition).append(delete_icon).appendTo($('#list-conditions'))
    $('#list-conditions').attr('data-condition_item', new_index)
}

window.initD2ResourceBulkDeleteBtn = function () {
    let btn_delete_progress = $(this)

    btn_delete_progress.click(function () {
        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        let resource_ids = [];

        $('input[name="resource_id"]:checked').each(function () {
            resource_ids.push(this.value);
        });

        if (resource_ids.length < 1) {
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
            data: {resource_ids},
            success: function (response) {

                if (response.result !== 'OK') {
                    alert(response.message);
                    _this.removeAttribute('disabled');
                    return;
                }

                alert(response.data.message);

                window.location.reload(true);
            }
        });
    })
}
