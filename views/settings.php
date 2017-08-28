<form action="<?= $controller->url_for('settings', $entry->id) ?>" method="post" class="studiengangsnews-editor default">
    <fieldset>
        <legend class="hide-in-dialog"><?= $_('Einstellungen bearbeiten') ?></legend>

        <fieldset>
            <label>
                <?= $_('Titel') ?>
                <input required type="text" name="title" id="title"
                       value="<?= htmlReady($title) ?>"
                       placeholder="<?= $_('Neuigkeiten zu Ihren Studiengängen') ?>">
            </label>
        </fieldset>
    </fieldset>

    <div data-dialog-button>
        <?= Studip\Button::createAccept($_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel($_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
    </div>
</form>
