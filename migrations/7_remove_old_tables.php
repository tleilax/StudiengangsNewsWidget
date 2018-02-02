<?php
/**
 * Migrations that adds the required cronjob for the widget.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 */
class RemoveOldTables extends Migration
{
    public function up()
    {

        $query = "DROP TABLE
            `studiengang_news_abschluss`,
            `studiengang_news_entries`,
            `studiengang_news_fach`";

        DBManager::get()->exec($query);
        SimpleORMap::expireTableScheme();
    }

    public function down ()
    {   }
}
