var initcheckHashFail = function () {
    $.unwrapContent('checkHashFail');

    var restore = null;

    var modal = $('<div />').addClass('osc-modal').width(450);

    var header = $('<header />').appendTo(modal);

    $('<div />').addClass('title').html('Notification').appendTo($('<div />').addClass('main-group').appendTo(header));

    // $('<div />').addClass('close-btn').click(function () {
    //     $.unwrapContent('checkHashFail');
    // }).appendTo(header);

    var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

    $('<div />').addClass('mt10').html('Your version has been expired. Do you want to restore the previous process').appendTo(modal_body);

    var action_bar = $('<div />').addClass('action-bar').appendTo(modal);
    var cancel = null;
    var url =  OSC_BASE+'/checkHash/common/restore';
    cancel = $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
        if (cancel.attr('disabled') === 'disabled') {
            return;
        }
        cancel.attr('disabled','disabled');
        cancel.prepend($($.renderIcon('preloader')).addClass('mr15'));
        window.location.href = url + '?cancel=1';
    }).appendTo(action_bar);

    restore = $('<button />').addClass('btn btn-primary ml10').html('Ok').click(function () {
        if (restore.attr('disabled') === 'disabled') {
            return;
        }
        restore.attr('disabled','disabled');
        restore.prepend($($.renderIcon('preloader')).addClass('mr15'));
        window.location.href = url;
    }).appendTo(action_bar);

    $.wrapContent(modal, {key: 'checkHashFail'});

    modal.moveToCenter().css('top', '100px');
};

initcheckHashFail();





