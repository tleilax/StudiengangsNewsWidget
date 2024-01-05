<?php
/**
 * @var bool $is_admin
 * @var StudiengangsNewsWidget $controller
 * @var Studiengang[] $studiengaenge
 * @var callable $_
 */
?>
<section class="contentbox studiengangsnews-widget">
<?  if ($is_admin): ?>
    <form action="#" onsubmit="return false" class="default">
        <label>
            <?= $_('Studiengang auswählen') ?>
            <select style="width:100%" id="study_course_selection"
                    name="study_course_selection"
                    onchange="STUDIP.StudiengaengeWidget.getEntries(this)"
                    data-update-url="<?= $controller->url_for('get_entries') ?>">

                <option value="">--- <?= $_('Studiengang auswählen') ?> ---</option>
                <? foreach ($studiengaenge as $std) : ?>
                    <option value="<?= htmlReady($std->studiengang_id) ?>">
                        <?= htmlReady($std->name)?>
                    </option>
                <? endforeach;?>
            </select>
        </label>
    </form>

    <div id="stg_news_content"></div>
<? endif; ?>
</section>
