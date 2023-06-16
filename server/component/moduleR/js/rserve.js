var unsavedChanges = [];

$(document).ready(function () {
    initRScriptsTable();
    initDeleteRScript();
    initREditor();
    initTestScriptBtn();
    initUnsavedChangesListener();
});

function initDeleteRScript() {
    $("#r-script-delete-btn").off('click').on('click', (e) => {
        e.preventDefault();
        deleteScript();
    });
}

function deleteScript() {
    var script_generated_id = $('input[name="generated_id"]').val();
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
    // load the monaco editor for R script
    if ($('.r-script').length > 0) {
        $('.r-script-value textarea').addClass('d-none');
        var rScript = $('.r-script')[0];
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        require(['vs/editor/editor.main'], function () {
            var editorOptions = {
                value: $('.r-script-value textarea').val(),
                language: 'r',
                automaticLayout: true,
                renderLineHighlight: "none",
                automaticLayout: true
            }
            var editorConfig = monaco.editor.create(rScript, editorOptions);
            editorConfig.getAction('editor.action.formatDocument').run().then(() => {
                calcMonacoEditorSize(editorConfig, rScript);
            });
            editorConfig.onDidChangeModelContent(function (e) {
                $('.r-script-value textarea').val(editorConfig.getValue());
                $('.r-script-value textarea').trigger('change');
            });
        });
    }
}

function initTestScriptBtn() {
    $("#r-script-test-btn").off('click').on('click', (e) => {
        e.preventDefault();
        test_r_script();
    });
}

function test_r_script() {
    var btn = $("#r-script-test-btn")[0];
    var origLabel = $(btn).html();
    $(btn).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span> ' + origLabel);
    var script_generated_id = $('input[name="generated_id"]').val();
    $.post(
        window.location,
        {
            mode: "test_script",
            script: $('.r-script-value textarea').val(),
            test_variables: $('.r-script-test-variables textarea').val()
        },
        function (data) {
            $(btn).html(origLabel);
            if (data.result) {
                $.alert({
                    title: 'Successful execution - R Script: ' + script_generated_id,
                    type: "green",
                    content: "<p class='pre-wrap'>" + JSON.stringify(data, null, 3) + "</p>"
                });
            }
            else {
                $.alert({
                    title: 'Error in R Script: ' + script_generated_id,
                    type: "red",
                    content: "<p class='pre-wrap'>" + JSON.stringify(data, null, 3) + "</p>"
                });
            }
        },
        "json"
    ).fail(function (e) {
        console.log(e.responseText);
        // Code to execute when the $.post() call fails
        $(btn).html(origLabel);
        $.alert({
            title: 'The script was not executed: ' + script_generated_id,
            type: "red",
            content: "<p class='pre-wrap'>Something went wrong! <br> " + e.responseText + "</p>"
        });
    });;
}

function initUnsavedChangesListener() {
    $(window).bind('beforeunload', function (e) {
        if (unsavedChanges.length > 0) {
            return false;
        }
    });
    $('input').on('change', function () { //triggers change in all input fields including text type
        unsavedChanges.push(this);
    });

    $('textarea').on('change', function () { //triggers change in all textareas
        unsavedChanges.push(this);
    });

    $('#section-r-script-form').find(':submit').on('click', function () {
        unsavedChanges = []; // clear the state as we will update it
    })
}