define([
    'dashaddon_developer/codemirror',
    'dashaddon_developer/codemirror_mode_xml',
    'dashaddon_developer/codemirror_mode_handlebars',
    'dashaddon_developer/codemirror_mode_sql',
    'dashaddon_developer/codemirror_addon_matchbrackets',
    'dashaddon_developer/codemirror_addon_show_hint',
    'dashaddon_developer/codemirror_addon_sql_hint'
], function(CodeMirror) {

    var mustacheTextarea = document.getElementById("id_mustache_template");

    if (mustacheTextarea) {
        CodeMirror.fromTextArea(mustacheTextarea, {
            lineNumbers: true,
            matchBrackets: true,
            mode: {name: 'handlebars', base: 'text/html'}
        });
    }
});
