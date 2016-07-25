if ( LI === undefined )
  var LI = {};
if ( LI.varieties === undefined )
  LI.varieties = [];

$(document).ready(function () {

    $('.add-choice').each(function () {

        LI.varieties.checkAndAppend($(this));
    });

    $('.add-choice').editable({
        type: 'text',
        url: '/librinfo/ajax/choices/add',
        pk: 1,
        placement: 'top',
        savenochange: 'false',
        display: false,
        success: function (data) {
            
            LI.varieties.checkAndAppend($(this), data.value);
        }
    });
    
    $('.add-choice').click(function(){
        
        $(this).editable('setValue', null)
               .removeClass('editable-unsaved')
       ;
    });
});

LI.varieties.checkAndAppend = function(widget, value){
 
    if (widget.siblings('select').length > 0) {
        if( value === undefined){
            LI.varieties.loadSelectChoices(widget.attr('id'));
        }else {
            LI.varieties.addSelectChoice(widget.attr('id'), value);
        }
    } else if (widget.siblings('.checkbox').length > 0) {
        if( value === undefined){     
            LI.varieties.loadCheckboxChoices(widget.attr('id'));
        }else{
            LI.varieties.addCheckboxChoice(widget.attr('id'), value);
        }
    } else {
        return;
    }
};

LI.varieties.loadSelectChoices = function(fieldName) {

    $.get('/librinfo/ajax/choices/get/' + fieldName, function (data) {

        var choices = data.choices;
       
        for (var i = 0; i < choices.length; i++) {
            LI.varieties.addSelectChoice(choices[i].label, choices[i].value);
        }
    });
};

LI.varieties.addSelectChoice = function(fieldName, value) {

    var widget = $('#' + fieldName).siblings('select');

    $('<option value="' + widget.children('option').length + '">' + value + '</option>').appendTo(widget);
};

LI.varieties.loadCheckboxChoices = function(fieldName) {
    
    $.get('/librinfo/ajax/choices/get/' + fieldName, function (data) {

        var choices = data.choices;
       
        for (var i = 0; i < choices.length; i++) {
            LI.varieties.addCheckboxChoice(choices[i].label, choices[i].value);
        }
    });
};

LI.varieties.addCheckboxChoice = function (fieldName, value) {

    var widget = $('#' + fieldName).siblings('ul');
    var checkbox = $('<li><div class="checkbox">' +
            '<label><input type="checkbox"><span class="control-label__text">' +
            value +
            '</span></label></div></li>'
            );

    checkbox.appendTo(widget);
    checkbox.iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });
};



