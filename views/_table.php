<? if(($path != 'abschluss' && $path != 'studiengang') || intval($fk_ids) == 0): ?>
    <b><?= _('Bitte treffen Sie eine Auswahl') ?></b>
<? else: ?>
<table class="default" width="100%">
    <colgroup>
        <col width="40%">
        <col width="20%">
        <col width="40%">
    </colgroup>
    <thead>
        <tr>
            <th><b>1. <?= ($path == 'abschluss')? _('Abschluss') : _('Studiengang') ?></b></th>
            <th></th>
            <th><b>2. <?= ($path == 'abschluss')? _('Studiengang') : _('Abschluss') ?></b></th>
        </tr>
    </thead>
    <tbody>
        <tr height="200px">
            <td>
                <div id="step_1">
                <? if($path == 'abschluss'): ?>
                    <select id="abschluesse" name="abschluesse[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.getStudiengaenge(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" data-update-url="<?= $controller->url_for('get_studiengaenge') ?>">
                        <? foreach($abschluesse as $id => $name): ?>
                            <option value="<?= $id ?>" <?= in_array($id, $selected_abschluesse) ? 'selected="selected"' : '' ?>><?= $name ?></option>
                        <? endforeach; ?>
                    </select>
                <? else: ?>
                    <select id="studiengaenge" name="studiengaenge[]" multiple style="height:200px" onchange="STUDIP.StudiengaengeWidget.getAbschluesse(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" data-update-url="<?= $controller->url_for('get_abschluesse') ?>">
                        <? foreach($studiengaenge as $id => $name): ?>
                            <option value="<?= $id ?>" <?= in_array($id, $selected_studiengaenge) ? 'selected="selected"' : '' ?>><?= $name ?></option>
                        <? endforeach; ?>
                    </select>
                <? endif; ?>
                </div>
            </td>
            <td></td>
            <td>
                <div id="step_2">
                <? if($edit): ?>
                <? if($path == 'abschluss'): ?>
                    <select id="studiengaenge" name="studiengaenge[]" multiple onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" style="height:200px">
                        <? foreach($studiengaenge as $id => $name): ?>
                            <option value="<?= $id ?>" <?= in_array($id, $selected_studiengaenge) ? 'selected="selected"' : '' ?>><?= $name ?></option>
                        <? endforeach; ?>
                    </select>
                <? else: ?>
                    <select id="abschluesse" name="abschluesse[]" multiple onchange="STUDIP.StudiengaengeWidget.count(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" style="height:200px">
                        <? foreach($abschluesse as $id => $name): ?>
                            <option value="<?= $id ?>" <?= in_array($id, $selected_abschluesse) ? 'selected="selected"' : '' ?>><?= $name ?></option>
                        <? endforeach; ?>
                    </select>
                <? endif; ?>
                <? endif; ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br/>
<table class="default">
    <colgroup>
        <col>
        <col>
        <col width="100%">
    </colgroup>
    <thead>
        <tr>
            <th colspan="3">
                <b><?= _('Fachsemester') ?></b>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= _('Filtern') ?>:</td>
            <td>
                <select id="fs_qualifier" name="fs_qualifier" onchange="STUDIP.StudiengaengeWidget.getFS(this)" data-counter-url="<?= $controller->url_for('count_users') ?>" data-update-url="<?= $controller->url_for('get_fachsemester') ?>">
                    <option value="no_filter" <?= ($entry->fs_qualifier == 'no_filter') ? 'selected="selected"' : '' ?>><?= _('alle') ?></option>
                    <option value="equals" <?= ($entry->fs_qualifier == 'equals') ? 'selected="selected"' : '' ?>><?= _('Im') ?></option>
                    <option value="smaller_equals" <?= ($entry->fs_qualifier == 'smaller_equals') ? 'selected="selected"' : '' ?>><?= _('Höchstens') ?></option>
                    <option value="greater_equals" <?= ($entry->fs_qualifier == 'greater_equals') ? 'selected="selected"' : '' ?>><?= _('Mindestens') ?></option>
                </select>
            </td>
            <td>
                <div id="fs_selector"><?= _('Fachsemester') ?>
                    <? if($entry != NULL && !$entry->isNew() && $entry->fs_qualifier != 'no_filter'): ?>
                        <?= $this->render_partial('_fsfilter.php', compact("entry")) ?>
                    <? endif; ?>
                </div id="fs_selector">
            </td>
        </tr>
    </tbody>
</table>
<? endif; ?>