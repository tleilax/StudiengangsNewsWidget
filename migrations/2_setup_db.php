<?php
/**
 * Migrations that creates the table for the studiengangs news.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
  */
class SetupDb extends Migration
{
    /**
     * Returns the description of the migration.
     *
     * @return String containing the migration
     */
    public function description()
    {
        return 'Creates table that stores the studiengangs news';
    }

    /**
     * Create table.
     */
    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `studiengang_news_entries` (
                      `news_id` CHAR(32) NOT NULL,
                      `fk_id` CHAR(32) NOT NULL,
                      `fachsemester` INT(2) DEFAULT NULL,
                      `fs_qualifier` enum ('no_filter', 'equals', 'smaller_equals', 'greater_equals') DEFAULT 'no_filter',
                      `subject` VARCHAR(256) NOT NULL,
                      `content` TEXT NOT NULL,
                      `user_id` CHAR(32) NOT NULL,
                      `activated` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                      `expires` INT(11) UNSIGNED NOT NULL,
                      `mkdate` INT(11) UNSIGNED NOT NULL,
                      `chdate` INT(11) UNSIGNED NOT NULL,
                      PRIMARY KEY (`news_id`)
                  )";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `studiengang_news_abschluss` (
                      `news_id` CHAR(32) NOT NULL,
                      `abschluss_id` CHAR(32) DEFAULT NULL,
                      PRIMARY KEY (`news_id`, `abschluss_id`)
                  )";
        DBManager::get()->exec($query);
        $query = "CREATE TABLE IF NOT EXISTS `studiengang_news_fach` (
                      `news_id` CHAR(32) NOT NULL,
                      `studiengang_id` CHAR(32) DEFAULT NULL,
                      PRIMARY KEY (`news_id`, `studiengang_id`)
                  )";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }

    /**
     * Remove table.
     */
    public function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `studiengang_news_entries`");
        DBManager::get()->exec("DROP TABLE IF EXISTS `studiengang_news_abschluss`");
        DBManager::get()->exec("DROP TABLE IF EXISTS `studiengang_news_fach`");
        SimpleORMap::expireTableScheme();
    }
}
