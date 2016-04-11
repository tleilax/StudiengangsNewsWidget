<? if($option != 'no_filter'): ?>
<select id="fachsemester" name="fachsemester" onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>">
    <? for($i = 1; $i <= 12; $i++): ?>
        <option value="<?= $i ?>" <?= ($entry != NULL && !$entry->isNew() && intval($entry->fachsemester) == $i )? 'selected="selected"' : '' ?>><?= $i ?>.</option>
    <? endfor; ?>
</select>
<? endif; ?>
<?= _('Fachsemester') ?>
