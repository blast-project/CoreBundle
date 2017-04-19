
$(document).ready(function(){
    var searchForm = $("form.sidebar-form[role=search]");
    var ajaxSearchUrl = searchForm.attr('action');
    var searchAdminSelect = searchForm.find('select[name=admin]');

    function searchFormatResult(item) {
       var markup = '<div class="row-fluid">' + item.label + '</div>';
       return markup;
    }

    function searchFormatSelection(item) {
       return item.label;
    }

    searchForm.find("input[name=q]").select2({
        minimumInputLength: 1,
        ajax: {
            url: ajaxSearchUrl,
            dataType: 'json',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    q: term,
                    admin: searchAdminSelect.find('option:selected').attr('value')
                };
            },
            results: function (data, page) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        formatResult: searchFormatResult,
        formatSelection: searchFormatSelection
    });

    searchForm.find("input[name=q]").on("change", function(e){
        if (e.added !== undefined && e.added.link !== undefined) {
            window.location = e.added.link;
        }
    });
});