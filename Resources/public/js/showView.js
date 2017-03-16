$(document).ready(function () {
    var collections = $('.blast-show-collection');
    var container = collections.eq(0).closest('.box-primary').closest('div');
    
    collections.each(function () {
        var new_container = $('<div>').insertAfter(container);
        $('<div class="box box-primary">').appendTo(new_container).append($(this));
    });
    container.remove();
});


