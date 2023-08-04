$(function() {

    const all_step_container = $('#step-container');
    const tags_data = fetchJSONTag(all_step_container.closest('form'), 'gift_finder')['filter_tags'];
    const step_config_data = fetchJSONTag(all_step_container.closest('form'), 'gift_finder')['step_config'];
    const limit_step = 5;
    const limit_tags_no_image_one_step = 20;
    const limit_tags_has_image_one_step = 8;
    let count_step_form = $('#step-container').find('.step-form').length;

    const addElemCheckboxChildrenTags = (modalElem, parent_tag_id, step_number, checked_children_tag = []) => {

        const tag_children_checkbox_container_elem = modalElem.find('.tag-children-checkbox').empty();
        const tag_children_data = tags_data[parent_tag_id];

        const addCheckBoxElem = (tag_id, tag_title) => {
            const tag_children_elem = $('<div />').addClass('tag-children-checkbox__item').appendTo(tag_children_checkbox_container_elem);
            const checkbox = $('<input />').attr({
                    'type': 'checkbox',
                    'id': 'tag_' + tag_id,
                    'data-title': tag_title,
                })
                .val(tag_id)
                .on('change', function() {
                    const countCheckedChildrenInAStep = tag_children_checkbox_container_elem.find('input[type="checkbox"]:checked')?.length;
                    const show_image_value = all_step_container.find(`.step-form[data-step-number="${step_number}"]`)?.find('input[name*="[show_image]"]')?.is(':checked');
                    const limit_tags = show_image_value ? limit_tags_has_image_one_step : limit_tags_no_image_one_step;

                    // limit tag checkbox
                    if ($(this).is(':checked') && countCheckedChildrenInAStep > limit_tags) {
                        $(this).prop('checked', false);
                        alert(`Limit ${limit_tags} tags for this step`);
                    }
                });

            if (checked_children_tag.includes(tag_id)) {
                checkbox.prop('checked', true);
            }
            
            $('<div />').addClass('styled-checkbox')
                .append(checkbox)
                .append($('<ins />').append($.renderIcon('check-solid')))
                .appendTo(tag_children_elem);
            $('<label />')
                .addClass('label-inline')
                .attr('for', 'tag_' + tag_id)
                .text(tag_title)
                .appendTo(tag_children_elem);
        };

        if (tag_children_data) {
            modalElem.find('label[name="select-options-label"]').show();

            tag_children_data.children?.forEach(tag => {
                addCheckBoxElem(tag.id, tag.title);
            });
        }
    }

    const addElemChildrenTag = (tag_children_container_elem, checked_children_data, step_number) => {

        const addElem = (tag_id, tag_title, step_number) => {
            const children_tag_elem = $('<div />')
                .addClass('tag-children__item')
                .attr({
                    'data-id': tag_id
                })
                .append($('<div />').text(tag_title))
                .append(
                    $('<span />').addClass('btn-close pl10 pr5').html($.renderIcon('close')).on('click', function () {
                        $(this).closest('.tag-children__item').remove();
                    })
                )
                .append(
                    $('<input />').attr({
                        'name': `step_config[${step_number}][children_tag][]`,
                        'type': 'hidden'
                    }).val(tag_id)
                )
                .appendTo(tag_children_container_elem);

            initItemReorder(children_tag_elem, '.tag-children', '.tag-children__item', 'children-tag-reorder-helper', function (helper) {
                helper.find('svg').hide();
            });
        }

        if (checked_children_data.length) {
            checked_children_data.map(tag => {
                addElem(tag.id, tag.title, step_number);
            });
        }

    }

    window.initShowImageCheckbox = function () {
        $(this).on('change', function () {
            if ($(this).is(':checked')) {
                const countChildrenTagInAStep = $(this).closest('.step-form').find('.tag-children__item')?.length;

                if (countChildrenTagInAStep > limit_tags_has_image_one_step) {
                    $(this).prop('checked', false);
                    alert(`The number of options with images is limited to ${limit_tags_has_image_one_step}. Please remove some of the options.`);
                }
            }
        });
    }

    window.initChildrenTag = function () {
        const step_number = $(this).closest('.step-form').attr('data-step-number');
        const checked_children_data = step_config_data[step_number]['children_tag'];
        addElemChildrenTag($(this), checked_children_data, step_number);
    }

    window.initAddNewStep = function () {
        const resetValueElem = (step_elem) => {
            if (step_elem.length) {
                const children_step_form = all_step_container.children().length + 1;
                const step = ++count_step_form;

                step_elem.attr({
                    'data-step-number': step,
                    'data-parent-tag': 0
                });
                step_elem.find('.step-form-header-title').text('Step ' + children_step_form + ' Title');
                step_elem.find('input[name*="[parent_tag]"]').attr('name', `step_config[${step}][parent_tag]`).val(0);
                step_elem.find('input[name*="[title]"]').attr('name', `step_config[${step}][title]`).val('');
                step_elem.find('input[name*="[show_image]"]').attr({
                    'id': 'show_image_' + step,
                    'name': `step_config[${step}][show_image]`
                }).val(1).removeAttr('checked');
                step_elem.find('label.label-checkbox').attr('for', 'show_image_' + step);
                step_elem.find('div.add-options').attr({'data-insert-cb': 'initAddOptionsPopup'});
                step_elem.find('.tag-children-checkbox').empty();
                step_elem.find('.tag-children').empty();

                step_elem.find('.step-form-header').append($('<div />').addClass('remove-step-btn').attr('data-insert-cb', 'initRemoveStep').text('Remove'));
            }

            return step_elem;
        }

        $(this).on('click', function () {
            const new_step = resetValueElem(all_step_container.children().first().clone()); 
            new_step.appendTo(all_step_container);

            if (all_step_container.find('.step-form').length >= limit_step) {
                $(this).hide();
            }
        });
    }

    window.initRemoveStep = function () {
        $(this).on('click', function () {
            $(this).closest('.step-form').remove();
            const all_step_form = all_step_container.find('.step-form');

            if (all_step_form?.length) {
                all_step_form.each((index, elem) => {
                    $(elem).find('.step-form-header-title').text(`Step ${index + 1} Title`);
                });
                if (all_step_form.length < limit_step) {
                    $('#add-new-step-button').show();
                }
            }
        });
    }

    window.initAddOptionsPopup = function () {
        $(this).on('click', function () {
            const step_config_elem = $(this).closest('.step-form');
            const step_number = step_config_elem.attr('data-step-number');
            const parent_tag_id = step_config_elem.attr('data-parent-tag');
            const checked_children_tag_id = [];
            const other_parent_id = [];

            step_config_elem.find('.tag-children__item').each((_, elem) => {
                checked_children_tag_id.push($(elem).data('id'));
            });
            
            all_step_container.find('.step-form').each((_, elem) => {other_parent_id.push($(elem).attr('data-parent-tag'))});

            $.unwrapContent('addTagOptionsPopup');

            let modal = $('<div />').addClass('osc-modal').width(600);
    
            let header = $('<header />').appendTo(modal);
    
            $('<div />').addClass('title').text('Add new options').appendTo($('<div />').addClass('main-group').appendTo(header));
    
            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('addTagOptionsPopup');
            }).appendTo(header);
    
            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
    
            let row = $('<div />').addClass('frm-grid').appendTo(modal_body);

            let cell = $('<div />').appendTo(row);
    
            $('<label />').css('font-weight', 600).text('Select parent tag').appendTo(cell);

            let parent_tag_selector_elem = $('<select />')
                .attr({ name: 'parent-tags' })
                .append($('<option />').prop('selected', true).attr({'value': 0, 'disabled': 'disabled'}).text('Select parent tag'))
                .on('change', function () {
                    addElemCheckboxChildrenTags(modal, $(this).val(), step_number);
                });
            $('<div />').addClass('styled-select').append(parent_tag_selector_elem).append($('<ins />')).appendTo(cell);

            for (let _parent_tag_id in tags_data) {
                let option = $('<option />').attr({value: _parent_tag_id}).text(tags_data[_parent_tag_id].title).appendTo(parent_tag_selector_elem);
                if (other_parent_id.includes(_parent_tag_id) && _parent_tag_id != parent_tag_id) {
                    option.attr('disabled', 'disabled');
                }
            }

            parent_tag_selector_elem.val(parent_tag_id);

            $('<label />').attr({name: 'select-options-label'}).css({'font-weight': 600}).text('Select options').hide().appendTo($('<div />').addClass('mt15').appendTo(modal_body));
            $('<div />').addClass('tag-children-checkbox').appendTo(modal_body);

            if (parent_tag_id != 0) {
                addElemCheckboxChildrenTags(modal, parent_tag_id, step_number, checked_children_tag_id);
            }
    
            let action_bar = $('<div />').addClass('action-bar').appendTo(modal);
    
            $('<button />').addClass('btn btn-outline').text('Close').click(function () {
                $.unwrapContent('addTagOptionsPopup');
            }).appendTo(action_bar);
    
            $('<button />').addClass('btn btn-primary ml10').text('Save').click(function () {
                if (parent_tag_selector_elem.val() == 0) {
                    return;
                }

                const checked_children = [];
                const step_config_elem = all_step_container.find(`.step-form[data-step-number="${step_number}"]`);
                const tag_children_container_elem = step_config_elem.find('.tag-children');
                const tag_children_checkbox_container_elem = $(this).closest('.osc-modal').find('.tag-children-checkbox');

                step_config_elem.attr('data-parent-tag', parent_tag_selector_elem.val());
                step_config_elem.find('input[name*="[parent_tag]"]').val(parent_tag_selector_elem.val());
                tag_children_container_elem.empty();

                tag_children_checkbox_container_elem.find('input[type="checkbox"]').each((key, elem) => {
                    if ($(elem).is(':checked')) {
                        checked_children.push({id: $(elem).val(), title: $(elem).attr('data-title')});
                    }
                });

                addElemChildrenTag(tag_children_container_elem, checked_children, step_number);

                $.unwrapContent('addTagOptionsPopup');
            }).appendTo(action_bar);
    
            $.wrapContent(modal, {key: 'addTagOptionsPopup'});

    
            modal.moveToCenter().css('top', '100px');
        });
    }
});