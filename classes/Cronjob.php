<?php
namespace StudiengangsNews;

use PluginManager, RolePersistence, DBManager, PDO;

/**
 * StudiengangsNewsCronjob.php
 *
 * @author  Chris Schierholz <Chris.Schierholz1@uni-oldenburg.de>
 */
class Cronjob extends \CronJob
{
    /**
     * Returns the name of the cronjob
     *
     * @return String containing the name of the cronjob
     */
    public static function getName()
    {
        return _('"Studiengangs news" Cronjob');
    }

    /**
     * Returns the description of the cronjob.
     *
     * @return String containing the description of the cronjob
     */
    public static function getDescription()
    {
        return _('Prüft die Gültigkeit der Einträge für "Studiengangs news" und (de)aktiviert das Widget für die entsprechenden Nutzerkreise.');
    }

    /**
     * Initializes the cronjob execution. Loads required classes.
     */
    public function setUp()
    {
        require __DIR__ . '/../models/Entry.class.php';
    }

    /**
     * Executes/engages the cronjob.
     *
     * @param mixed $last_result The result of the last execution
     * @param array $parameters  Any defined parameters
     * @return boolean
     */
    public function execute($last_result, $parameters = array())
    {
        $info = PluginManager::getInstance()->getPluginInfo('StudiengangsNewsWidget');
        if (!$info) {
            return false;
        }

        $plugin_id = $info['id'];

        // Check for new entries that affect users.
        $query = "SELECT DISTINCT us.user_id
                  FROM `studiengang_news_entries` AS e
                  JOIN `studiengang_news_abschluss` AS a USING(`news_id`)
                  JOIN `studiengang_news_fach` AS s USING(`news_id`)
                  JOIN `user_studiengang` AS us
                     ON  us.`user_id` IN (SELECT `user_id` FROM `auth_user_md5` WHERE `perms` = 'tutor')
                     AND (a.`abschluss_id` = '' OR a.`abschluss_id` = us.`abschluss_id`)
                     AND (s.`fach_id` = '' OR s.`fach_id` = us.`fach_id`)
                     AND (`fs_qualifier` = 'no_filter'
                         OR (`fs_qualifier` = 'equals' AND us.`semester` = e.`fachsemester`)
                         OR (`fs_qualifier` = 'smaller_equals' AND us.`semester` <= e.`fachsemester`)
                         OR (`fs_qualifier` = 'greater_equals' AND us.`semester` >= e.`fachsemester`))
                 JOIN `mod_zuordnung` AS mz
                     ON us.`abschluss_id` = mz.`abschluss_id`
                        AND us.`fach_id` = mz.`fach_id`
                        AND mz.`fk_id` = e.`fk_id`
                 WHERE `expires` > UNIX_TIMESTAMP() AND e.`activated` = 0";
        $user_ids = DBManager::get()->query($query)->fetchAll(PDO::FETCH_COLUMN);

        if(!empty($user_ids)) {
            $this->positionWidget($plugin_id, $user_ids);
        }

        $entries = Entry::findBySQL("expires > UNIX_TIMESTAMP() AND activated = '0'");

        // Update entries
        if(!empty($entries)) {
            foreach($entries as $entry) {
                $entry->activated = true;
                $entry->store();
            }
        }
        return true;
    }

    /**
     * Positions the widget for a set of users.
     *
     * @param String $plugin_id Id of the plugin
     * @param Array $user_ids   the user IDs
     */
    private function positionWidget($plugin_id, $user_ids)
    {
        $query = "DELETE FROM `widget_user`
                  WHERE `pluginid` = :plugin_id
                    AND `range_id` IN (
                        SELECT `user_id`
                        FROM `auth_user_md5`
                        WHERE `perms` = 'tutor'
                          AND `user_id` IN (:user_ids)
                    )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);
        $statement->bindValue(':user_ids', $user_ids);
        $statement->execute();

        $query = "UPDATE `widget_user`
                  SET `position` = `position` + 1
                  WHERE `col` = 0
                    AND `range_id` IN (
                        SELECT `user_id`
                        FROM `auth_user_md5`
                        WHERE `perms` = 'tutor'
                          AND `user_id` IN (:user_ids)
                    )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_ids', $user_ids);
        $statement->execute();

        $query = "INSERT INTO `widget_user` (`pluginid`, `position`, `range_id`, `col`)
                  SELECT DISTINCT :plugin_id, 0, `user_id`, 0
                  FROM `auth_user_md5`
                  JOIN `widget_user` ON `auth_user_md5`.`user_id` = `widget_user`.`range_id`
                  WHERE `perms` = 'tutor'
                    AND `user_id` IN (:user_ids)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);
        $statement->bindValue(':user_ids', $user_ids);
        $statement->execute();
    }
}
