$(document).ready(function () {
    initRScriptsTable();
    initDeleteRScript();
    initREditor();
});

function initDeleteRScript() {
    $("#r-script-delete-btn").off('click').on('click', (e) => {
        e.preventDefault();
        deleteScript();
    });
}

function deleteScript() {
    var script_generated_id = $('input[name="generated_id"]').val();
    console.log(script_generated_id);
    if (script_generated_id) {
        $.confirm({
            title: 'Delete script: <code>' + script_generated_id + "</code>",
            type: "red",
            content: '<p>This will delete the script <code>' + script_generated_id + '</code> and all the jobs related to this script will not work.</p><p>You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the generated id of the script.</p> <input id="deleteValue" type="text" class="form-control" >',
            buttons: {
                confirm: function () {
                    if ($("#deleteValue").val() == script_generated_id) {
                        location.href = $("#r-script-delete-btn").attr('href');
                    } else {
                        $.alert({
                            title: 'Delete Script: ' + script_generated_id,
                            type: "red",
                            content: 'Failed to delete the script: The verification text does not match with the survey name.',
                        });
                    }
                },
                cancel: function () {
                }
            }
        });
    } else {
        $.alert({
            title: 'Delete script: <code>' + script_generated_id + "</code>",
            type: "red",
            content: 'Something went wrong!',
        });
    }

}

function initRScriptsTable() {
    var table = $('#r-scripts').DataTable({
        "order": [[0, "asc"]]
    });

    table.on('click', 'tr[id|="r-script-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = window.location + '/update/' + parseInt(ids[3]);
    });
}

function initREditor() {

    // load the monaco editor for css fields
    if ($('.r-script').length > 0) {
        var rScript = $('.r-script')[0];
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        require(['vs/editor/editor.main'], function () {
            var editorOptions = {
                value: $('.r-script-value textarea').val(),
                language: 'r',
                automaticLayout: true,
                renderLineHighlight: "none"
            }
            var editorConfig = monaco.editor.create(rScript, editorOptions);
            editorConfig.getAction('editor.action.formatDocument').run().then(() => {
                calcMonacoEditorSize(editorConfig, rScript);
            });
            editorConfig.onDidChangeModelContent(function (e) {
                $('.r-script-value textarea').val(editorConfig.getValue());
            });
        });
    }
}