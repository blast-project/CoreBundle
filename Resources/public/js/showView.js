$(document).ready(function () {
    $('.blast-show-collection').each(function() {
        var table = $(this)
           .closest('table')
           .not('blast-show-collection-element')
        ;
        
        table
            .closest('.box-primary')
            .replaceWith($(this))
        ;
    });
});


