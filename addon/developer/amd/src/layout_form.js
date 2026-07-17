define([
    'dashaddon_developer/codemirror',
    'dashaddon_developer/codemirror_mode_xml',
    'dashaddon_developer/codemirror_mode_handlebars',
    'dashaddon_developer/codemirror_mode_sql',
    'dashaddon_developer/codemirror_addon_matchbrackets',
    'dashaddon_developer/codemirror_addon_show_hint',
    'dashaddon_developer/codemirror_addon_sql_hint'
], function(CodeMirror) {

    var mustacheEditor = null;
    var mustacheTextarea = document.getElementById('id_mustache_template');

    var insertAtCursor = function(textarea, text) {
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || 0;
        var value = textarea.value || '';

        textarea.value = value.substring(0, start) + text + value.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
    };

    var initPlaceholderToggle = function() {
        var containers = document.querySelectorAll('.dash-layout-vars-toggle');

        if (!containers || !containers.length) {
            return;
        }

        containers.forEach(function(container) {
            var button = container.querySelector('.layout-vars-button');

            if (!button) {
                return;
            }

            button.addEventListener('click', function(e) {
                e.preventDefault();

                var target = e.target.closest('a');
                var buttonUrl = target.getAttribute('href');
                var toggleIcon = target.querySelector('i');
                var varsContent = document.querySelector(buttonUrl);

                if (!varsContent) {
                    return;
                }

                varsContent.classList.toggle('show');

                if (toggleIcon) {
                    toggleIcon.classList.toggle('fa-angle-double-up');
                    toggleIcon.classList.toggle('fa-angle-double-down');
                }
            });
        });
    };

    var initPlaceholderInsert = function() {
        var placeholders = document.querySelectorAll('.dash-layout-placeholder');

        if (!placeholders || !placeholders.length) {
            return;
        }

        placeholders.forEach(function(placeholder) {
            placeholder.addEventListener('click', function(e) {
                e.preventDefault();

                var target = e.target.closest('a');
                var text = target.getAttribute('data-text');

                if (!text) {
                    return;
                }

                if (mustacheEditor) {
                    mustacheEditor.focus();
                    mustacheEditor.replaceSelection(text);
                    return;
                }

                if (mustacheTextarea) {
                    insertAtCursor(mustacheTextarea, text);
                }
            });
        });
    };

    var initVarsExpand = function() {
        var buttons = document.querySelectorAll('.dash-layout-vars-toggle .button-show-more');

        if (!buttons || !buttons.length) {
            return;
        }

        buttons.forEach(function(showmorebtn) {
            showmorebtn.addEventListener('click', function(e) {
                e.preventDefault();

                var target = e.target.closest('a');
                var placeholderUrl = target.getAttribute('href');
                var placeholderContent = document.querySelector(placeholderUrl);

                if (!placeholderContent) {
                    return;
                }

                placeholderContent.classList.toggle('less');

                if (placeholderContent.classList.contains('less')) {
                    target.innerHTML = 'Show more';
                } else {
                    target.innerHTML = 'Show less';
                }
            });
        });
    };

    if (mustacheTextarea) {
        mustacheEditor = CodeMirror.fromTextArea(mustacheTextarea, {
            lineNumbers: true,
            matchBrackets: true,
            mode: {name: 'handlebars', base: 'text/html'}
        });
    }

    initPlaceholderToggle();
    initPlaceholderInsert();
    initVarsExpand();
});