$(document).ready(function() {
    $(document).on('click', 'a[data-gototab]', function() {
        var tabName = $(this).attr('data-gototab');
        $('li[data-tab-name="' + tabName + '"] a').trigger('click');
    });
});
