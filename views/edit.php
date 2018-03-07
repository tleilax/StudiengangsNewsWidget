<form action="<?= $controller->url_for('store', $entry->news_id) ?>" method="post" class="studiengangsnews-editor default">
    <input type="hidden" id="news_id" value="<?= $entry->news_id ?>">
    <fieldset>
        <legend><?= $_('Filtern') ?>:</legend>

        <select id="faculty_id" name="faculty_id[]"
            onchange="STUDIP.StudiengaengeWidget.getTable(this)"
            data-update-url="<?= $controller->url_for('table') ?>"
            style="height: 200px; margin-bottom: 15px;" multiple>

            <? foreach($faculties as $fac_id => $faculty): ?>
                <optgroup label="<?= htmlReady($faculty['name']) ?>">
                    <? foreach($faculties[$fac_id]['sub'] as $inst) : ?>
                        <option value="<?= $inst['institut_id']?>"
                                <?= in_array($inst['institut_id'], $study_courses->pluck('institut_id')) ? 'selected' : '' ?>>
                            <?= htmlReady($inst['name']) ?>
                        </option>
                    <? endforeach;?>
                </optgroup>
            <? endforeach; ?>
        </select>

        <div id="path_table">
            <?= $this->render_partial('_studycourses',
                ['selected_study_courses' => $study_courses->pluck('studiengang_id'), 'studycourses' => $all_study_courses, 'graduation_id' => array_unique($all_study_courses->pluck('abschluss_id'))]) ?>
        </div>
    </fieldset>

    <fieldset>
        <legend class="hide-in-dialog"><?= $_('Inhalte bearbeiten') ?></legend>
        <fieldset>
            <label for="expires"><?= $_('Anzeigen bis') ?></label>
            <input type="text" id="expires" name="expires" class="has-datepicker" value="<?= date('d.m.Y', $entry->date + $entry->expire ?: time()) ?>">
        </fieldset>

        <fieldset>
            <label for="subject"><?= $_('Titel') ?></label>
            <input required type="text" name="subject" id="subject" value="<?= htmlReady($entry->topic) ?>" placeholder="<?= $_('Titel des Eintrags') ?>">
        </fieldset>

        <fieldset>
            <label for="content"><?= $_('Inhalt') ?></label>
            <textarea required name="content" id="content" class="add_toolbar" data-secure placeholder="<?= $_('Inhalt des Eintrags') ?>"><?= htmlReady($entry->body) ?></textarea>
        </fieldset>
    </fieldset>

    <div data-dialog-button>
        <?= Studip\Button::createAccept($_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel($_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
    </div>
</form>
