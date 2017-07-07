$(document).ready(function() {
    $(document).on('click', 'a[data-gototab]', function() {
        var tabName = $(this).attr('data-gototab');
        $('li[data-tab-name="' + tabName + '"] a').trigger('click');
    });

    var countCollectionsInTabs = function() {
        var countableTabs = $('.countable-tab');

        countableTabs.each(function(index) {
            var tab = $(this);
            tab.data('currentCount', 0);

            var countableClasses = $.grep(tab.attr("class").split(' '), function(v) {
                return v.match(/count-.*/);
            });

            $.each(countableClasses, function(i, cls) {
                var fieldName = cls.replace('count-', '');
                var fieldId = 'field_widget_' + Admin.currentAdmin.uniqid + '_' + fieldName;

                var collectionAsForm = $('#' + fieldId + ' > div.sonata-ba-tabs > div');
                var collectionAsTable = $('#' + fieldId + ' > table > tbody > tr');

                tab.data('currentCount', parseInt(tab.data('currentCount'), 10) + (
                    parseInt(collectionAsForm.length, 10) +
                    parseInt(collectionAsTable.length, 10)
                ));
            });

            var counterItem = tab.find('a > span.counter');

            if (counterItem.length == 0) {
                counterItem = tab.find('a').append(
                    $('<span/>').attr({
                        'class': 'counter'
                    })
                ).find('.counter');
            }

            counterItem.html(tab.data('currentCount')).attr('data-count',tab.data('currentCount'));
        });
    };

    countCollectionsInTabs();

    $(document).on('sonata.add_element',function() {
        countCollectionsInTabs();
    });
});
