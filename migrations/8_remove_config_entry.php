<?php
class RemoveConfigEntry extends Migration
{
    /**
     * Removes the config entry
     */
    public function up()
    {
        $query = "DELETE FROM `config` WHERE `field` = 'STG_NEWS_WIDGET_TITLE'";
        DBManager::get()->exec($query);
    }

    /**
     * Creates the config entry
     */
    public function down()
    {
        $query = "INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`,
                                               `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
                  VALUES (MD5(:field), '', :field, :value, '1', 'string',
                          'global', '', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description, '', '')";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':field', 'STG_NEWS_WIDGET_TITLE');
        $statement->bindValue(':value', 'Neuigkeiten zu Ihren Studiengängen');
        $statement->bindValue(':description', 'Enthält den Titel des "Neuigkeiten zu ihren Studiengängen"-Widgets');
        $statement->execute();
    }
}
