<article class="studip">
    <? if ($is_admin) : ?>
        <?= $this->render_partial('_admin_select.php', compact('is_admin', 'studiengaenge', 'id', 'controller', 'entries')) ?>
    <? endif; ?>

    <? if (!empty($news)) : ?>
        <?= $this->render_partial('_news.php', ['news' => $news, 'studiengaenge' => $studiengaenge]) ?>
    <? endif; ?>

    <? if ($GLOBALS['perm']->have_perm('root')) : ?>
        <footer>
            <? if (!$_SESSION['old_studycourse_news']) : ?>
                <a href="<?=URLHelper::getLink('?old_studycourse_news=1')?>">
                    <?= $_('Abgelaufene Neuigkeiten anzeigen') ?>
                </a>
            <? else : ?>
                <a href="<?=URLHelper::getLink('?old_studycourse_news=0')?>">
                    <?= $_('Aktive Neuigkeiten anzeigen') ?>
                </a>
            <? endif; ?>
        </footer>
    <? endif; ?>
</article>