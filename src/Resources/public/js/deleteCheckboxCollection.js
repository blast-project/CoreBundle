$(document).on('ifChecked','.sonata-ba-field-inline-natural input[type="checkbox"]',function(e) {
    var input = $(this);
    var name = input.attr('name');

    if(name.match(/\[[0-9]*\]\[_delete\]/)) {
        
    }
});
