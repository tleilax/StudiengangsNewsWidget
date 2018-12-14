<? if (!empty($news)) : ?>
    <section>
    <? foreach ($studiengaenge as $studiengang): ?>
        <? if (array_key_exists($studiengang->studiengang_id, $news)) : ?>
            <article class="studip toggle <? if (count($studiengaenge) === 1) echo 'open'; ?>"
                id="<?= htmlReady($studiengang->studiengang_id) ?>">
                <header>
                    <h1>
                        <a href="<?= ContentBoxHelper::href($studiengang->studiengang_id) ?>">
                            <?= htmlReady($studiengang->name) ?>
                        </a>
                    </h1>
                </header>
                <section>
                    <table class="default nohover collapsable">
                        <colgroup>
                            <col>
                            <col style="width: 20%">
                            <col style="width: 10%">
                            <col style="width: 10%">
                        </colgroup>
                    <? foreach ($news[$studiengang->studiengang_id] as $entry) : ?>
                        <tbody <? if ($entry['news_id'] !== Request::get('news_id_open')) echo 'class="collapsed"'; ?>>
                            <tr class="header-row">

                                <th class="toggle-indicator"
                                    onclick="STUDIP.StudiengaengeWidget.showNews(this, '<?= $entry['news_id'] ?>')"
                                    data-update-url="<?= $controller->link_for('visit') ?>">

                                    <a href="<?= URLHelper::getLink('dispatch.php/start',
                                            array('contentbox_open' => Request::get('contentbox_open'),
                                                'news_id_open' => $entry['news_id'])) ?>"
                                            name="<?= $entry['news_id'] ?>" class="toggler">
                                    <? if (!object_get_visit($entry['news_id'], "news", false, false)
                                            || $entry['chdate'] >= object_get_visit($entry['news_id'], 'news', false, false)): ?>

                                        <?= Icon::create('news+new')->asImg(['style' => 'vertical-align:middle']) ?>
                                    <? else : ?>
                                        <?= Icon::create('news')->asImg(['style' => 'vertical-align:middle']) ?>
                                    <? endif; ?>
                                        <?= htmlReady($entry['topic']) ?>
                                    </a>
                                </th>

                                <th class="dont-hide">
                                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $entry->owner->username]) ?>">
                                        <?= htmlReady($entry->owner->getFullName()) ?>
                                    </a>
                                </th>
                                <th class="dont-hide">
                                    <?= strftime('%x', $entry['date']) ?>
                                </th>
                                <th class="dont-hide" style="white-space:nowrap; text-align: right;">
                                    | <span data-news-id-count="<?= htmlReady($entry['news_id']) ?>" style="color: #050">
                                        <?= object_return_views($entry['news_id']) ?>
                                    </span>
                                </th>
                            </tr>
                            <tr style="border: 1px solid gray;">
                                <td colspan="5"><?= formatReady($entry['body']) ?></td>
                            </tr>
                        </tbody>
                    <? endforeach; ?>
                    </table>
                </section>
            </article>
        <? endif; ?>
    <? endforeach; ?>
    </section>
<? else : ?>
    <div class="messagebox messagebox_info">
        <?= $_('Es sind keine Einträge vorhanden') ?>
    </div>
<? endif; ?>
