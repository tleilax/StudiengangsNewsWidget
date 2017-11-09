<section class="contentbox studiengangsnews-widget">
<? if($is_admin): ?>
    <section>
        <strong><?= $_('Studiengang auswählen') ?>:</strong>
        <select style="width:100%" id="study_course_selection" name="study_course_selection" onchange="STUDIP.StudiengaengeWidget.getEntries(this)" data-update-url="<?= $controller->url_for('get_entries') ?>">
            <option value="">--- <?= $_('Kein Filter') ?> ---</option>
        <? foreach($study_courses->get() as $study_course): ?>
            <? $count = StudiengangsNews\Entry::getEntriesCountForStudyCourse($study_course['abschluss_id'], $study_course['fach_id'], $study_course['fk_id']) ?>
            <? if($count > 0): ?>
                <? $id = $study_course['abschluss_id'] . '_' . $study_course['fach_id']; ?>
                <option value="<?= $id ?>" <? if ($id === $selected) echo 'selected'; ?>>
                    <?= htmlReady("{$study_course['fach_name']} - {$study_course['abschluss_name']}") ?>
                    (<?= $count ?>)
                </option>
            <? endif; ?>
        <? endforeach; ?>
        </select>
    </section>
<? endif; ?>
    <?= $this->render_partial('_news.php', compact("entries")) ?>
</section>
