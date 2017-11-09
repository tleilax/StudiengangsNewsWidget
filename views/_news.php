<? foreach ($entries as $entry): ?>
    <article <? if ($entry->is_new): ?>class="new" data-visiturl="<?= $controller->url_for('visit/' . $entry->id) ?>"<? endif; ?>>
        <header>
            <h1>
                <a href="<?= URLHelper::getLink('?studiengangsnews-toggle=' . $entry->id) ?>">
                    <?= htmlReady($entry->subject) ?>
                </a>
            </h1>
            <nav>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $entry->author->username) ?>">
                    <?= Avatar::getAvatar($entry->author->id)->getImageTag(Avatar::SMALL) ?>
                    <?= htmlReady($entry->author->getFullname()) ?>
                </a>
                <span>
                    <?= strftime('%x', $entry->mkdate) ?>
                <? if ($is_root): ?>
                    /
                    <?= strftime('%x', $entry->expires) ?>
                <? endif; ?>
                </span>
                <span style="color: #050;"><?= $entry->views ?></span>
                <? if ($is_admin): ?>
                    <a href="<?= $controller->url_for('edit', $entry->id) ?>" data-dialog>
                        <?= Icon::create('edit', 'clickable')->asImg(tooltip2($_('Eintrag bearbeiten'))) ?>
                    </a>
                    <form action="<?= $controller->url_for('delete', $entry->id) ?>" method="post" data-confirm="<?= $_('Wollen Sie diesen Eintrag wirklich löschen?') ?>">
                        <?= Icon::create('trash', 'clickable')->asInput(tooltip2($_('Eintrag löschen'))) ?>
                    </form>
                <? endif; ?>
            </nav>
        </header>
        <section>
            <?= formatReady($entry->content) ?>
        </section>
    </article>
<? endforeach; ?>
<? if (!$entries): ?>
    <section>
        <?= $_('Es sind keine Einträge vorhanden') ?>
    </section>
<? endif; ?>
