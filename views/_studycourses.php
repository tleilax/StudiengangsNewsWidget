<? if (count($studycourses) > 0) : ?>
<section class="contentbox default">
    <? foreach ($graduation_id as $id) : ?>
        <? if (count($studycourses->findBy('abschluss_id', $id)) > 0 ) : ?>
            <article class="contentbox" id="<?= $id?>">
                <header>
                    <h1>
                        <a href="<?= ContentBoxHelper::href($id) ?>"
                                title="<?= htmlReady($studycourses->findOneBy('abschluss_id', $id)->abschluss->name) ?>">
                            <?= htmlReady($studycourses->findOneBy('abschluss_id', $id)->abschluss->name) ?>
                        </a>
                    </h1>
                </header>
            <section>
                <table class="default">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox"
                                    data-proxyfor="tbody.<?= sprintf('graduation_%s', $id)?> input[type=checkbox]">
                            </th>
                            <th>
                                <?= $_('Studienfach')?>
                            </th>
                            <th>
                                <?= $_('Studierende')?>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="<?= sprintf('graduation_%s', $id)?>">
                        <? foreach ($studycourses->findBy('abschluss_id', $id) as $studycourse) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="studycourse_ids[]"
                                        value="<?= $studycourse->studiengang_id ?>"
                                        <?= in_array($studycourse->studiengang_id, $selected_study_courses) ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <?= htmlReady(str_replace($studycourses->findOneBy('abschluss_id', $id)->abschluss->name, '', $studycourse->name)) ?>
                                </td>
                                <td>
                                    <?= UserStudyCourse::countBySQL('JOIN mvv_stgteil ON (mvv_stgteil.fach_id = user_studiengang.fach_id)
                                        JOIN mvv_stg_stgteil ON (mvv_stg_stgteil.stgteil_id = mvv_stgteil.stgteil_id)
                                        WHERE abschluss_id =  :abschluss_id
                                        AND mvv_stg_stgteil.studiengang_id = :studycourse_id',
                                        ['abschluss_id' => $studycourse->abschluss_id, ':studycourse_id' => $studycourse->studiengang_id])?>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    </tbody>
                </table>
            </section>
        </article>
        <? endif; ?>
    <? endforeach; ?>
</section>
<? else : ?>
    <div class="messagebox messagebox_info">
        <?= $_('Keine Studiengänge vorhanden')?>
    </div>
<? endif; ?>
