<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('select2');
?>

<style>
    .select2-container--default .select2-results__option[aria-selected=true] {
        background: #2684FE!important;
        color: white!important;
    }
    .select2-results .select2-highlighted {
        color: white!important;
    }
    .select2-results__option.group-item {
        background-color: #f3f3f3;
    }
</style>
<div>
    <ins></ins>
    <select class="styled-input select_user_group" style="margin-left: 0;" data-insert-cb="initReportFilterGroup">
        <?php foreach ($params['items'] as $key => $item) : ?>
            <option
                style="background: black"
                class="<?= $item['type'] == 'group' ? 'option-group-item' : 'option-member-item'; ?>"
                value="<?= $item['primary_id']; ?>"
                data-type="<?= $item['type']; ?>"
                data-key="<?= $key; ?>"
                data-link = "<?= $item['link']; ?>"
                data-group_title = "<?= $item['group_title'] ?? ''; ?>"
                <?php if ($item['selected']) : ?> selected="selected" <?php endif; ?>
            ><?= (isset($item['child_of']) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '') .  $item['title']; ?> </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
    $(document).ready(function () {
        $('.select_user_group').select2({
            placeholder: "Select permission mask",
            width: '412px',
            templateResult: formatState,
            templateSelection: formatState,
            matcher: matchCustom,
        });

        function formatState (opt, container) {

            if ($(opt.element).data('type') === 'group') {
                var $state_group = $(
                    '<span><svg data-icon="osc-group-members-black" viewBox="0 0 15 9" width="15px" data-insert-cb="configOSCIcon"><use xlink:href="#osc-group-members-black"></use></svg>&nbsp;&nbsp;<b>' + opt.text + '</b></span>'
                );
                $(container).addClass('group-item');

                return $state_group;
            } else if ($(opt.element).data('type') === '') {
                var $state_group = $(
                    '<span><b>' + opt.text + '</b></span>'
                );
                return $state_group;
            }
            var $state_member = $(
                '<span>' + opt.text + '</span>'
            );
            return $state_member;
        }

        function matchCustom(params, data) {
            // If there are no search terms, return all of the data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Do not display the item if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // `params.term` should be the term that is used for searching
            // `data.text` is the text that is displayed for the data object
            if (data.text.indexOf(params.term) > -1) {
                var modifiedData = $.extend({}, data, true);
                const group_title = modifiedData.element.dataset.group_title
                if (group_title) {
                    modifiedData.text = '<span><svg data-icon="osc-group-members-black" viewBox="0 0 15 9" width="15px" data-insert-cb="configOSCIcon"><use xlink:href="#osc-group-members-black"></use></svg>&nbsp;&nbsp;<b>' + group_title + ' - ' + '</b>'+modifiedData.text.trim().replace('&nbsp;', '')+'</span>'
                }
                // You can return modified objects from here
                // This includes matching the `children` how you want in nested data sets
                return modifiedData;
            }

            // Return `null` if the term should not be displayed
            return null;
        }

    })
</script>