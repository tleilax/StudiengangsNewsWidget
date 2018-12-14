<?php
namespace StudiengangsNews;

use CronJob as GlobalCronjob;
use DBManager;
use PDO;
use PluginManager;

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class Cronjob extends GlobalCronjob
{
    /**
     * Returns the name of the cronjob
     *
     * @return String containing the name of the cronjob
     */
    public static function getName()
    {
        return '"Studiengangs News" Cronjob';
    }

    /**
     * Returns the description of the cronjob.
     *
     * @return String containing the description of the cronjob
     */
    public static function getDescription()
    {
        //TODO beschreibung anpassen
        return 'Prüft die Gültigkeit der Einträge für "Studiengangs news" und (de)aktiviert das Widget für die entsprechenden Nutzerkreise.';
    }

    /**
     * Executes/engages the cronjob.
     *
     * @param mixed $last_result The result of the last execution
     * @param array $parameters  Any defined parameters
     * @return boolean
     */
    public function execute($last_result, $parameters = [])
    {
        $info = PluginManager::getInstance()->getPluginInfo('StudiengangsNewsWidget');
        if (!$info) {
            return;
        }
        $plugin_id = $info['id'];

        $query = "SELECT DISTINCT us.`user_id`
                  FROM `user_studiengang` AS us
                  -- Study course info
                  JOIN `mvv_stgteil` AS ms USING (`fach_id`)
                  JOIN `mvv_stg_stgteil` AS mss USING (`stgteil_id`)
                  JOIN `mvv_studiengang` AS msc USING (`studiengang_id`, `abschluss_id`)
                  -- News info
                  JOIN `news_range` AS nr ON (msc.`studiengang_id` = nr.`range_id`)
                  JOIN `news` AS n ON (nr.`news_id` = n.`news_id` AND n.`date` + n.`expire` > UNIX_TIMESTAMP())
                  -- Visited
                  LEFT JOIN `object_user_visits` AS ouv ON (ouv.`object_id` = n.`news_id` AND ouv.`type` = 'news' AND ouv.`user_id` = us.`user_id`)
                  -- Widget activated
                  LEFT JOIN `widget_user` AS wu ON (wu.`range_id` = us.`user_id` AND wu.`pluginid` = :plugin_id)
                  LEFT JOIN `widget_user` AS wu2 ON (wu2.`range_id` = us.`user_id`)
                  WHERE ouv.`user_id` IS NULL
                    AND wu.`range_id` IS NULL
                  GROUP BY us.`user_id`
                  HAVING COUNT(wu2.`range_id`) > 0";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);
        $statement->execute();
        $user_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        if (count($user_ids) > 0) {
            $this->positionWidget($plugin_id, $user_ids);
        }
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
