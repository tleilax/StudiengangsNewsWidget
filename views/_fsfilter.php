<? if ($option !== 'no_filter'): ?>
<select id="fachsemester" name="fachsemester" onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>">
<? for ($i = 1; $i <= 12; $i++): ?>
    <option value="<?= $i ?>" <? if ($entry && !$entry->isNew() && $entry->fachsemester == $i) echo 'selected'; ?>>
        <?= $i ?>.
    </option>
<? endfor; ?>
</select>
<? endif; ?>
<?= $_('Fachsemester') ?>
