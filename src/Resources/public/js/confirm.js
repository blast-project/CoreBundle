$(document).ready(function() {
    $(document).on('click', '.confirmable', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var button = $(this);

        var title = typeof button.attr('data-confirm-text') == !'undefined' ? button.attr('data-confirm-text') : "Confirmez-vous l'action ?";

        bootbox.confirm({
            message: title,
            buttons: {
                confirm: {
                    label: 'Oui',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Non',
                    className: 'btn-danger'
                }
            },
            callback: function(result) {
                if (result === true) {
                    var action = button.attr('data-confirm-action');

                    switch (action) {
                        case 'followHref':
                        default:
                            window.location.href = button.attr('href');
                            break;
                        case 'callAction':
                            var functionName = button.attr('data-confirm-function');
                            functionName(button);
                            break;
                    }
                }
            }
        });
    });
});
