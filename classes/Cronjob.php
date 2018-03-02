<?php
namespace StudiengangsNews;

use CronJob as GloblCronjob;
use DBManager;
use PDO;
use PluginManager;
use RolePersistence;
use SimpleCollection;
use Studiengang;
use UserStudyCourse;

/**
 * StudiengangsNewsCronjob.php
 *
 * @author  Chris Schierholz <Chris.Schierholz1@uni-oldenburg.de>
 */
class Cronjob extends GloblCronjob
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
    public function execute($last_result, $parameters = array())
    {

        $info = PluginManager::getInstance()->getPluginInfo('StudiengangsNewsWidget');
        if (!$info) {
            return false;
        }
        $plugin_id = $info['id'];

        //TODO news noch aktuell
        $studiengaenge = SimpleCollection::createFromArray(
            Studiengang::findBySQL(
                'JOIN news_range ON (mvv_studiengang.studiengang_id = news_range.range_id)
                 JOIN news ON (news.news_id = news_range.news_id)
                   AND :time <= news.date + news.expire',
                [':time' => time()]
            )
        );

        $user_ids = array_unique(SimpleCollection::createFromArray(
            UserStudyCourse::findBySQL(
                'JOIN mvv_stgteil ON (mvv_stgteil.fach_id = user_studiengang.fach_id)
                 JOIN mvv_stg_stgteil ON (mvv_stg_stgteil.stgteil_id = mvv_stgteil.stgteil_id)
                 WHERE abschluss_id IN (:abschluss_ids)
                   AND mvv_stg_stgteil.studiengang_id IN (:studycourse_ids)',
                [
                    'abschluss_ids' => array_unique($studiengaenge->pluck('abschluss_id')),
                    ':studycourse_ids' => array_unique($studiengaenge->pluck('studiengang_id'))
                ]
            )
        )->pluck('user_id'));

        if (!empty($user_ids)) {
            $this->positionWidget($plugin_id, $user_ids);
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
