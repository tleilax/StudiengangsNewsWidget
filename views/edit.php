<form action="<?= $controller->url_for('store', $entry->id) ?>" method="post" class="studiengangsnews-editor studip_form">
    <strong><?= $_('Fakultät auswählen') ?>:</strong>
    <select id="faculty_id" name="faculty_id" onchange="STUDIP.StudiengaengeWidget.getTable(this)" data-update-url="<?= $controller->url_for('table') ?>">
    <? foreach($faculties as $id => $fk_id): ?>
        <option value="<?= $id ?>" <?= ($id == substr($entry->fk_id, -1))? 'selected="selected"' : '' ?>><?= Institute::find($fk_id)->name ?></option>
    <? endforeach; ?>
    </select>
    <b><?= $_('Kriterium auswählen') ?>: </b>
    <select id="path" name="path" onchange="STUDIP.StudiengaengeWidget.getTable(this)" data-update-url="<?= $controller->url_for('table') ?>">
        <option value="-">--- <?= $_('Bitte wählen') ?>--- </option>
        <option value="abschluss" <? if ($path === 'abschluss') echo 'selected'; ?>>
            <?= $_('Abschluss') ?>
        </option>
        <option value="fach" <? if ($path === 'fach') echo 'selected'; ?>>
            <?= $_('Fach') ?>
        </option>
    </select>
    <br>
    <div id="path_table">
    <? if (!$entry->isNew()): ?>
        <? $fk_ids = substr($entry->fk_id, -1); ?>
        <?= $this->render_partial('_table.php', compact('path', 'entry', 'fk_ids', 'selected_abschluesse', 'selected_faecher', 'abschluesse', 'faecher')) ?>
    <? endif; ?>
    </div>
    <br>
    <div id="usercount">
    <? if (!$entry->isNew()): ?>
        <?= $this->render_partial('_usercount.php', compact('entry')) ?>
    <? endif; ?>
    </div>
    <fieldset>
        <legend class="hide-in-dialog"><?= $_('Inhalte bearbeiten') ?></legend>
        <fieldset>
            <label for="expires"><?= $_('Anzeigen bis') ?></label>
            <input type="text" id="expires" name="expires" class="has-datepicker" value="<?= date('d.m.Y', $entry->expires ?: time()) ?>">
        </fieldset>

        <fieldset>
            <label for="subject"><?= $_('Titel') ?></label>
            <input required type="text" name="subject" id="subject" value="<?= htmlReady($entry->subject) ?>" placeholder="<?= $_('Titel des Eintrags') ?>">
        </fieldset>

        <fieldset>
            <label for="content"><?= $_('Inhalt') ?></label>
            <textarea required name="content" id="content" class="add_toolbar" data-secure placeholder="<?= $_('Inhalt des Eintrags') ?>"><?= htmlReady($entry->content) ?></textarea>
        </fieldset>
    </fieldset>

    <div data-dialog-button>
        <?= Studip\Button::createAccept($_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel($_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
    </div>
</form>
