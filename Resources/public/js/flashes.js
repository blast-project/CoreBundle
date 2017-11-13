Admin.flashMessage = {
    show: function(type, message) {
        var flash = $('#flash-' + type);

        flash.find('.flash-content').html(message);
        flash.show();
    },
    hide: function(type) {
        if(type) {
            $('#flash-' + type).hide();
            
            return;
        }
        
        $('.js-flash').hide();
    }
};


