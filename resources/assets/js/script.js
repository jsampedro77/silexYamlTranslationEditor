$().ready(function() {

    var activeFile = $("#space-select").val() + '.' + $("#locale-select").val() + '.yml';

    $("#locale-select, #space-select").on('change', function() {
        window.location = '/edit/' + $("#space-select").val() + '.' + $("#locale-select").val() + '.yml';
    });

    $(".translatable-content").on('focusout', function() {
        var key = $(this).attr('data-key');
        var file = activeFile;
        var content = $(this).val();
        var element = $(this);

        $.ajax({
            type: "POST",
            url: '/save',
            data: {key: key, file: file, content: content},
            success: function() {
                notifySuccess(element);
            },
            error: function() {
                notifyError(element);
            }
        });
    });
});

var notifySuccess = function(element) {
    var alert = $("<div class='alert alert-success'>Cambio guardado</div>");
    element.before(alert);
    fade(alert);
};

var notifyError = function(element) {
    var alert = $("<div class='alert alert-danger'>No se ha podido guardar el cambio</div>");
    element.before(alert);
    fade(alert);
};

var fade = function(element) {
    setTimeout(function() {
        element.fadeOut("slow");
    }, 2000);
};