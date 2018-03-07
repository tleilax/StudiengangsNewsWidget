<? if (!empty($news)) : ?>
    <section class="contentbox default">
        <? foreach($studiengaenge as $studiengang) : ?>
            <? if (array_key_exists($studiengang->studiengang_id, $news)) : ?>
                <article class="contentbox <?= count($studiengaenge) == 1 ? 'open' : ''?>"
                    id="<?= $studiengang->studiengang_id?>">
                    <header>
                        <h1>
                            <a href="<?= ContentBoxHelper::href($studiengang->studiengang_id) ?>"
                                    title="<?= htmlReady($studiengang->name) ?>">
                                    <?= htmlReady($studiengang->name) ?>
                            </a>
                        </h1>
                    </header>
                    <section>
                        <table class="default nohover collapsable" style="width:95%; margin: 0 auto">
                            <? foreach ($news[$studiengang->studiengang_id] as $entry) : ?>
                                <tbody class="<?= $entry['news_id'] != Request::get('news_id_open') ? 'collapsed' : '' ?>">
                                    <tr class="header-row">

                                        <th class="toggle-indicator"
                                            onclick="STUDIP.StudiengaengeWidget.showNews(this,'<?= $entry['news_id'] ?>')"
                                            data-update-url="<?= $controller->url_for('visit') ?>">

                                            <a href="<?= URLHelper::getURL('dispatch.php/start',
                                                    array('contentbox_open' => Request::get('contentbox_open'),
                                                        'news_id_open' => $entry['news_id']))?>"
                                                    name="<?= $entry['news_id'] ?>" class="toggler">
                                            <? if (!object_get_visit($entry['news_id'], "news", false, false)
                                                    || $entry['chdate'] >= object_get_visit($entry['news_id'], 'news', false, false)) :?>

                                                <?= Icon::create('news+new','clickable',
                                                        array('style' => 'vertical-align:middle')) ?>
                                            <? else : ?>
                                                <?= Icon::create('news', 'clickable',
                                                        array('style' => 'vertical-align:middle')) ?>
                                            <? endif; ?>

                                            <?= htmlReady($entry['topic']) ?>
                                            </a>
                                        </th>

                                        <th class="dont-hide">
                                            <a href="<?= $entry->user_id != $GLOBALS['user']->user_id ?
                                                       URLHelper::getURL('dispatch.php/profile?username=' . $entry->owner->username) :
                                                       URLHelper::getURL('dispatch.php/profile?')?>">
                                                <?= htmlReady($entry['author']) ?>
                                            </a>
                                        </th>
                                        <th class="dont-hide">
                                            <?= strftime('%x', $entry['date']) ?>
                                        </th>
                                        <th class="dont-hide" style="white-space:nowrap;">
                                            | <span id="visit_count_<?= $entry['news_id']?>" style="color: #050"><?= object_return_views($entry['news_id']) ?></span> |
                                        </th>
                                    </tr>
                                    <tr style="border: 1px solid graytext;">
                                        <td colspan="5"><?= formatReady($entry['body']) ?></td>
                                    </tr>
                                </tbody>
                        <? endforeach; ?>
                        </table>
                    </section>
                </article>
            <? endif; ?>
        <?endforeach;?>
    </section>
<? else : ?>
    <div class="messagebox messagebox_info">
        <?= $_('Es sind keine Einträge vorhanden') ?>
    </div>
<? endif; ?>
