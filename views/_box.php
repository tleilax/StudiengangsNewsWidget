<? if($path == 'abschluss'): ?>
    <select id="studiengaenge" name="studiengaenge[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>">
        <? foreach($studiengaenge as $id => $name): ?>
            <option value="<?= $id ?>" <?= in_array($id, $selected_studiengaenge) ? 'selected="selected"' : '' ?>><?= $name ?></option>
        <? endforeach; ?>
    </select>
<? else: ?>
    <select id="abschluesse" name="abschluesse[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>">
        <? foreach($abschluesse as $id => $name): ?>
            <option value="<?= $id ?>" <?= in_array($id, $selected_abschluesse) ? 'selected="selected"' : '' ?>><?= $name ?></option>
        <? endforeach; ?>
    </select>
<? endif; ?>