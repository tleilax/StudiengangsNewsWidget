<section class="contentbox studiengangsnews-widget">
<? if($is_admin): ?>
    <section>
        <strong><?= $_('Studiengang auswählen') ?>:</strong>
        <select style="width:100%" id="study_course_selection"
            name="study_course_selection"
            onchange="STUDIP.StudiengaengeWidget.getEntries(this)"
            data-update-url="<?= $controller->url_for('get_entries') ?>">

            <option value="">--- <?= $_('Studiengang auswählen') ?> ---</option>
            <? foreach ($studiengaenge as $std) : ?>
                <option value="<?= $std->studiengang_id?>">
                    <?= htmlReady($std->name)?>
                </option>
            <? endforeach;?>
        </select>
    </section>

    <div id="stg_news_content"></div>

<? endif; ?>

</section>
