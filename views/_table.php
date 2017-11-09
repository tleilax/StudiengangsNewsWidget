<? if (($path !== 'abschluss' && $path !== 'fach') || intval($fk_ids) == 0): ?>
    <b><?= $_('Bitte treffen Sie eine Auswahl') ?></b>
<? else: ?>
<table class="default">
    <colgroup>
        <col width="40%">
        <col width="20%">
        <col width="40%">
    </colgroup>
    <thead>
        <tr>
            <th>1. <?= $path === 'abschluss' ? $_('Abschluss') : $_('Fach') ?></th>
            <th></th>
            <th>2. <?= $path === 'abschluss' ? $_('Fach') : $_('Abschluss') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div id="step_1">
            <? if ($path === 'abschluss'): ?>
                <select id="abschluesse" name="abschluesse[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.getFaecher(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" data-update-url="<?= $controller->url_for('get_faecher') ?>">
                <? foreach ($abschluesse as $id => $name): ?>
                    <option value="<?= $id ?>" <? if (in_array($id, $selected_abschluesse)) echo 'selected'; ?>>
                        <?= htmlReady($name) ?>
                    </option>
                <? endforeach; ?>
                </select>
            <? else: ?>
                <select id="faecher" name="faecher[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.getAbschluesse(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" data-update-url="<?= $controller->url_for('get_abschluesse') ?>">
                <? foreach ($faecher as $id => $name): ?>
                    <option value="<?= $id ?>" <? if (in_array($id, $selected_faecher)) echo 'selected'; ?>>
                        <?= htmlReady($name) ?>
                    </option>
                <? endforeach; ?>
                </select>
            <? endif; ?>
                </div>
            </td>
            <td></td>
            <td>
                <div id="step_2">
                <? if($edit): ?>
                <? if ($path === 'abschluss'): ?>
                    <select id="faecher" name="faecher[]" multiple onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" style="height:200px">
                    <? foreach ($faecher as $id => $name): ?>
                        <option value="<?= $id ?>" <? if (in_array($id, $selected_faecher)) echo 'selected'; ?>>
                            <?= htmlReady($name) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                <? else: ?>
                    <select id="abschluesse" name="abschluesse[]" multiple onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" style="height:200px">
                    <? foreach ($abschluesse as $id => $name): ?>
                        <option value="<?= $id ?>" <? if (in_array($id, $selected_abschluesse)) echo 'selected'; ?>>
                            <?= htmlReady($name) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                <? endif; ?>
                <? endif; ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<br>

<table class="default">
    <colgroup>
        <col width="5%">
        <col width="10%">
        <col>
    </colgroup>
    <thead>
        <tr>
            <th colspan="3"><?= $_('Fachsemester') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= $_('Filtern') ?>:</td>
            <td>
                <select id="fs_qualifier" name="fs_qualifier" onchange="STUDIP.StudiengaengeWidget.getFS(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" data-update-url="<?= $controller->url_for('get_fachsemester') ?>">
                    <option value="no_filter" <? if ($entry->fs_qualifier === 'no_filter') echo 'selected'; ?>>
                        <?= $_('alle') ?>
                    </option>
                    <option value="equals" <? if ($entry->fs_qualifier === 'equals') echo 'selected'; ?>>
                        <?= $_('Im') ?>
                    </option>
                    <option value="smaller_equals" <? if ($entry->fs_qualifier === 'smaller_equals') echo 'selected'; ?>>
                        <?= $_('Höchstens') ?>
                    </option>
                    <option value="greater_equals" <? if ($entry->fs_qualifier === 'greater_equals') echo 'selected'; ?>>
                        <?= $_('Mindestens') ?>
                    </option>
                </select>
            </td>
            <td>
                <div id="fs_selector">
                    <?= $_('Fachsemester') ?>
                <? if ($entry && !$entry->isNew() && $entry->fs_qualifier !== 'no_filter'): ?>
                    <?= $this->render_partial('_fsfilter.php', compact('entry')) ?>
                <? endif; ?>
                </div id="fs_selector">
            </td>
        </tr>
    </tbody>
</table>
<? endif; ?>
