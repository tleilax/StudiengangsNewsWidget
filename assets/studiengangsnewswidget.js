(function ($) {
    'use strict';
    
    function xprintf(str, params) {
        return str.replace(/#{(\w+)}/g, function(chunk, key) {
            return params.hasOwnProperty(key) ? params[key] : chunk;
        });
    };
    
    $(document).on('ajaxComplete', function (event, jqxhr) {
        if (jqxhr.getResponseHeader('X-Initialize-Dialog')) {
            $('.ui-dialog-content textarea.add_toolbar').addToolbar();
            $('.ui-dialog-content .has-datepicker').datepicker();
        }
    });

    $(document).on('submit', '.studiengangsnews-editor', function (event) {
        if ($('.multi-checkbox-required :checkbox', this).length > 0 && $('.multi-checkbox-required :checkbox:checked', this).length === 0) {
            alert("Bitte wählen Sie mindestens eine Sichtbarkeit aus.".toLocaleString());
            event.preventDefault();
        }
    });

    $(document).on('click', '.studiengangsnews-widget .widget-tabs a', function (event) {
        var source_url = $(this).closest('.widget-tabs').data().source,
            perm       = $(this).data().perm,
            url        = xprintf(source_url, {perm: perm}),
            timeout;
        
        timeout = setTimeout(function() {
            STUDIP.Overlay.show(true, '.studiengangsnews-widget');
        }, 200);

        $(this).closest('.studiengangsnews-widget').parent().load(url, function () {
            clearTimeout(timeout);
            STUDIP.Overlay.hide();
        });
        
        event.preventDefault();
    });
    
    $(document).on('click submit', '[data-studiengangsnews-confirm]', function (event) {
        var question = $(this).data().confirmStudiengangsnews || $(this).attr('title') || $(this).text();
        if (!confirm(question)) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    $(document).on('change', '.studiengangsnews-editor :checkbox[name="visibility[]"][value="autor"]', function (event) {
        if (this.checked) {
            $(':checkbox[name="visibility[]"][value="tutor"]').attr('checked', true);
        }
    });
}(jQuery));
STUDIP.StudiengaengeWidget = {
    getTable: function (element) {
        var fk_id = $('#faculty_id option:selected' ).val();
        var path = $('#path option:selected' ).val();
        var textSrc = $(element).data('update-url').split('?');
        var url = textSrc[0] + '/' + encodeURIComponent(path) + '/' + encodeURIComponent(fk_id);
        $('#path_table').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
        $('#path_table').load(url);
    },
    getStudiengaenge: function (element) {
        var selMulti = $.map($("#abschluesse option:selected"), function (el, i) {
            return $(el).val();
        });
        var fk_id = $('#faculty_id option:selected' ).val();
        var textSrc = $(element).data('update-url').split('?');
        var url = textSrc[0] + '/' + encodeURIComponent(selMulti.join("_")) + '/' + encodeURIComponent(fk_id);
        $('#step_2').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
        $('#step_2').load(url);
        STUDIP.StudiengaengeWidget.count(element);
    },
    getAbschluesse: function (element) {
        var selMulti = $.map($("#studiengaenge option:selected"), function (el, i) {
            return $(el).val();
        });
        var fk_id = $('#faculty_id option:selected' ).val();
        var textSrc = $(element).data('update-url').split('?');
        var url = textSrc[0] + '/' + encodeURIComponent(selMulti.join("_")) + '/' + encodeURIComponent(fk_id);
        $('#step_2').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
        $('#step_2').load(url);
        STUDIP.StudiengaengeWidget.count(element);
    },
    getFS: function (element) {
        var textSrc = $(element).data('update-url').split('?');
        var url = textSrc[0] + '/' + encodeURIComponent($(element).val());
        console.log(textSrc, url);
        $('#fs_selector').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
        $('#fs_selector').load(url);
        STUDIP.StudiengaengeWidget.count(element);
    },
    getEntries: function (element) {
        var study_course = $('#study_course_selection option:selected' ).val();
        var textSrc = $(element).data('update-url').split('?');
        var url = textSrc[0] + '/' + encodeURIComponent(study_course);
        $('#stg_news_content').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
        $('#stg_news_content').load(url);
    },
    count: function(element) {
        var fk_id = $('#faculty_id option:selected' ).val();
        var fs_qualifier = $('#fs_qualifier option:selected' ).val();
        var fachsemester = $('#fachsemester option:selected' ).val();
        var studiengaenge = $.map($("#studiengaenge option:selected"), function (el, i) {
            return $(el).val();
        });
        var abschluesse = $.map($("#abschluesse option:selected"), function (el, i) {
            return $(el).val();
        });

        var textSrc = $(element).data('counter-url').split('?');
        var url = textSrc[0] + '?studiengang_ids=' + encodeURIComponent(studiengaenge.join("_"))
                             + '&abschluss_ids=' + encodeURIComponent(abschluesse.join("_"))
                             + '&fk_id=' + encodeURIComponent(fk_id) + '&fs_qualifier=' + encodeURIComponent(fs_qualifier)
                             + '&fachsemester=' + encodeURIComponent(fachsemester);
        console.log(url);
        $('#usercount').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
        $('#usercount').load(url);
    }
};