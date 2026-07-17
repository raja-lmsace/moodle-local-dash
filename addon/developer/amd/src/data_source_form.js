define([
    'jquery',
    'jqueryui',
    'core/ajax',
    'dashaddon_developer/codemirror',
    'dashaddon_developer/codemirror_mode_xml',
    'dashaddon_developer/codemirror_mode_handlebars',
    'dashaddon_developer/codemirror_mode_sql',
    'dashaddon_developer/codemirror_addon_matchbrackets',
    'dashaddon_developer/codemirror_addon_show_hint',
    'dashaddon_developer/codemirror_addon_sql_hint'
], function ($, jqueryui, Ajax, CodeMirror) {

    var mustacheTextarea = document.getElementById("id_layout_mustache");
    var queryTemplateTextarea = document.getElementById("id_query_template");

    if (mustacheTextarea) {
        CodeMirror.fromTextArea(mustacheTextarea, {
            lineNumbers: true,
            matchBrackets: true,
            mode: { name: 'handlebars', base: 'text/html' }
        });
    }

    if (queryTemplateTextarea) {
        var promises = Ajax.call([{
            methodname: 'dashaddon_developer_get_database_schema_structure',
            args: {}
        }]);
        promises[0].done(function (result) {
            CodeMirror.fromTextArea(queryTemplateTextarea, {
                mode: "text/x-mariadb",
                indentWithTabs: true,
                smartIndent: true,
                lineNumbers: true,
                matchBrackets: true,
                autofocus: true,
                extraKeys: {
                    "Ctrl-Space": "autocomplete"
                },
                hintOptions: {
                    tables: JSON.parse(result.schema)
                }
            });
        });
    }

    $("#field_edits tbody").sortable({
        handle: ".drag-handle"
    });

    $("#add-new-field-definition").on('change', function (e) {
        var fieldName = $(this).val();
        if (fieldName) {
            var promises = Ajax.call([{
                methodname: 'dashaddon_developer_get_field_edit_row',
                args: { name: fieldName }
            }]);
            promises[0].done(function (result) {
                $("#field_edits tbody").append(result.html);
            });
        }
    });

    $("#field_edits").on("click", ".delete-field", function (e) {
        e.preventDefault();
        $(this).closest('.field').remove();
    });
});
