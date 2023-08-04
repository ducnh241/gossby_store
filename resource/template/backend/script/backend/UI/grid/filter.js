(function($) {
    function OSC_Grid_Filter () {
        this.showForm = function(e) {
            var frm = $('#' + $(this).attr('rel') + '-filter-frm');      
            $.disablePage();
            frm.show();
            frm.moveToCenter();
        };
        
        this.closeForm = function(e) {
            var frm = $('#' + $(this).attr('rel') + '-filter-frm');      
            $.enablePage();
            frm.hide();          
        };
    }
    
    $.osc_grid_filter = new OSC_Grid_Filter();
    
    $(document).ready(function() {
        $('.mrk-grid-filter-toggler').click($.osc_grid_filter.showForm);
        $('.mrk-grid-filter-frm-close').click($.osc_grid_filter.closeForm);
        $('.mrk-grid-filter-frm-head').each(function(){
            var bar = $(this);
            bar.osc_dragger({target : $('#' + bar.attr('id').replace(/^(.+)-head$/, '$1'))[0]});
        });
    });
})(jQuery);