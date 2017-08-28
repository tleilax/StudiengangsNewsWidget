<? if ($path === 'abschluss'): ?>
    <select id="faecher" name="faecher[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>">
    <? foreach ($faecher as $id => $name): ?>
        <option value="<?= $id ?>" <? if (in_array($id, $selected_faecher)) echo 'selected'; ?>>
            <?= htmlReady($name) ?>
        </option>
    <? endforeach; ?>
    </select>
<? else: ?>
    <select id="abschluesse" name="abschluesse[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>">
    <? foreach ($abschluesse as $id => $name): ?>
        <option value="<?= $id ?>" <? if (in_array($id, $selected_abschluesse)) echo 'selected'; ?>>
            <?= htmlReady($name) ?>
        </option>
    <? endforeach; ?>
    </select>
<? endif; ?>
