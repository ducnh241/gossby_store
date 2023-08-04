$("input[name=pick-date]").change(function(){
    if ($(this).is(":checked")) {
        $("input[name=modified_date]").removeAttr('disabled').attr('required', 'required');
        $("input[name=modified_time]").removeAttr('disabled').attr('required', 'required');
    } else {
        $("input[name=modified_date]").removeAttr('required').attr('disabled','disabled');
        $("input[name=modified_time]").removeAttr('required').attr('disabled','disabled');
    }
});