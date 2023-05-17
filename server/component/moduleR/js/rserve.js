$(document).ready(function () {
    initRScriptsTable();
    initDeleteRScript();
});

function initDeleteRScript() {
    $("#r-script-delete-btn").off('click').on('click', (e) => {
        e.preventDefault();
        deleteScript();
    });
}

function deleteScript() {
    var survey_name = JSON.parse(creator.text)['title'];
    if (survey_name) {
        $.confirm({
            title: 'Delete survey: <code>' + survey_name + "</code>",
            type: "red",
            content: '<p>This will delete the survey <code>' + survey_name + '</code> and all the data collected by this survey.</p><p>You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the survey.</p> <input id="deleteValue" type="text" class="form-control" >',
            buttons: {
                confirm: function () {
                    if ($("#deleteValue").val() == survey_name) {
                        location.href = $("#survey-js-delete-btn").attr('href');
                    } else {
                        $.alert({
                            title: 'Delete Survey: ' + survey_name,
                            type: "red",
                            content: 'Failed to delete the page: The verification text does not match with the survey name.',
                        });
                    }
                },
                cancel: function () {
                }
            }
        });
    } else {
        $.alert({
            title: 'Delete Survey: <code>' + survey_name + "</code>",
            type: "red",
            content: 'Please first give a name to the survey and then delete it.',
        });
    }

}

function initRScriptsTable() {
    var table = $('#r-scripts').DataTable({
        "order": [[0, "asc"]]
    });

    table.on('click', 'tr[id|="r-scripts-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = window.location + '/update/' + parseInt(ids[3]);
    });
}