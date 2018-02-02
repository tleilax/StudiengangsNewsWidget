<? if (!empty($news)) : ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= $_('Titel') ?></th>
                <th><?= $_('Inhalt') ?></th>
                <th><?= $_('Aktiv bis') ?></th>
                <th><?= $_('Autor') ?></th>
                <th><?= $_('Studierende') ?></th>
                <th><?= $_('Gelesen') ?></th>
                <th class="actions"></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($news as $new) : ?>
                <tr>
                    <td><?= htmlReady($new['topic']); ?></td>
                    <td><?= formatReady($new['body']) ?></td>
                    <td><?= strftime('%x', $new['date'] + $new['expire'])?></td>
                    <td><?= htmlReady($new['author'])?></td>
                    <td>
                        <?= UserStudyCourse::countBySQL('JOIN mvv_stgteil ON (mvv_stgteil.fach_id = user_studiengang.fach_id)
                            JOIN mvv_stg_stgteil ON (mvv_stg_stgteil.stgteil_id = mvv_stgteil.stgteil_id)
                            WHERE abschluss_id =  :abschluss_id
                            AND mvv_stg_stgteil.studiengang_id = :studycourse_id',
                            ['abschluss_id' => $studiengang->abschluss_id, ':studycourse_id' => $studiengang->studiengang_id])?>
                    </td>
                    <td>
                        <?= object_return_views($new['news_id']) ?>
                    </td>
                    <td class="actions">
                        <a href="<?= $controller->url_for('edit', $new['news_id'])?>" title="<?= $_('Eintrag bearbeiten')?>" data-dialog>
                            <?= Icon::create('edit', 'clickable')?>
                        </a>
                    </td>
                </tr>
            <? endforeach; ?>
        </tobdy>
    </table>
<? else : ?>
    <div class="messagebox messagebox_info">
        <?= sprintf($_('Keine Neuigkeiten für %s vorhanden'), $studiegang->name)?>
    </div>
<? endif; ?>