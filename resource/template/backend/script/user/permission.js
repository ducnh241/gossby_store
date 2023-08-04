(function ($) {
    'use strict';

    window.initSelectPermissionAnalytic = function () {
        let old_val = $(this).val();
        var member = $('#input-perm-member');

        member.find('option[value=' + old_val + ']').hide();

        $(this).change(function () {
            let new_val = $(this).val();
            member.find('option[value=' + old_val + ']').show();
            member.find('option[value=' + new_val + ']').hide();
            old_val = new_val;
        });
    };
})(jQuery);
