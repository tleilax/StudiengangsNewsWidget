<?php
/**
 * Migration that creates the config entry for the title of the widget.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
  */
class AddConfigEntry extends Migration
{
    /**
     * Returns the description of the migration.
     *
     * @return String containing the migration
     */
    public function description()
    {
        return 'Adds database config entry';
    }

    /**
     * Creates the config entry
     */
    public function up()
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

    /**
     * Removes the config entry
     */
    public function down()
    {
        $query = "DELETE FROM `config` WHERE `field` IN ('STG_NEWS_WIDGET_TITLE')";
        DBManager::get()->exec($query);
    }
}