/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */
(function ($, STUDIP) {
    'use strict';

    function xprintf(str, params) {
        return str.replace(/#\{(\w+)\}/g, function (chunk, key) {
            return params.hasOwnProperty(key) ? params[key] : chunk;
        });
    }

    $(document).on('ajaxComplete', function (event, jqxhr) {
        if (jqxhr.getResponseHeader('X-Initialize-Dialog')) {
            $('.ui-dialog-content textarea.add_toolbar').addToolbar();
            $('.ui-dialog-content .has-datepicker').datepicker();
        }
    });

    $(document).on('submit', '.studiengangsnews-editor', function (event) {
        if ($('.multi-checkbox-required :checkbox', this).length > 0 && $('.multi-checkbox-required :checkbox:checked', this).length === 0) {
            alert('Bitte wählen Sie mindestens eine Sichtbarkeit aus.'.toLocaleString());
            event.preventDefault();
        }
    });

    $(document).on('click', '.studiengangsnews-widget .widget-tabs a', function (event) {
        var source_url = $(this).closest('.widget-tabs').data().source,
            perm       = $(this).data().perm,
            url        = xprintf(source_url, {perm: perm}),
            timeout;

        timeout = setTimeout(function () {
            STUDIP.Overlay.show(true, '.studiengangsnews-widget');
        }, 200);

        $(this).closest('.studiengangsnews-widget').parent().load(url, function () {
            clearTimeout(timeout);
            STUDIP.Overlay.hide();
        });

        event.preventDefault();
    });

    $(document).on('change', '.studiengangsnews-editor :checkbox[name="visibility[]"][value="autor"]', function (event) {
        if (this.checked) {
            $(':checkbox[name="visibility[]"][value="tutor"]').attr('checked', true);
        }
    });

    STUDIP.StudiengaengeWidget = {
        getTable: function (element) {
            var fk_ids = $('#faculty_id').val(),
                news_id = $('#news_id').val(),
                textSrc = $(element).data('update-url').split('?'),
                url = textSrc[0] + '/' +  encodeURIComponent(fk_ids.join('_')) + '/' + news_id;

            $('#path_table').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
            $('#path_table').load(url);
        },
        getEntries: function (element) {
            var study_course = $('#study_course_selection option:selected').val(),
                textSrc      = $(element).data('update-url').split('?'),
                url          = textSrc[0] + '/' + encodeURIComponent(study_course);
            $('#stg_news_content').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
            $('#stg_news_content').load(url);
        },
        showNews: function (element, news_id) {
            var textSrc = $(element).data('update-url').split('?'),
                id = '#visit_count_' + news_id,
                url = textSrc[0] + '/' + encodeURIComponent(news_id);

            $(id).html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
            console.log(news_id);
            $(id).load(url);
        },
    };
}(jQuery, STUDIP));
