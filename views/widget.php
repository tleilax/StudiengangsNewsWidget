<article class="studip">
    <? if ($is_admin) : ?>
        <?= $this->render_partial('_admin_select.php', compact('is_admin', 'studiengaenge', 'id', 'controller', 'entries')) ?>
    <? endif; ?>

    <? if (!empty($news)) : ?>
        <?= $this->render_partial('_news.php', ['news' => $news, 'studiengaenge' => $studiengaenge]) ?>
    <? endif; ?>

    <? if ($GLOBALS['perm']->have_perm('root')) : ?>
        <footer>
            <a href="<?=URLHelper::getLink('?nshow_all=1')?>">
                <?= $_('Abgelaufene Neuigkeiten anzeigen') ?>
            </a>
        </footer>
    <? endif; ?>
</article>