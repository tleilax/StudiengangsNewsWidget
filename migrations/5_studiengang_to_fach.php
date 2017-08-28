<?php

class StudiengangToFach extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `studiengang_news_studiengang`
                    CHANGE COLUMN `studiengang_id` `fach_id` CHAR(32) NOT NULL,
                    RENAME TO `studiengang_news_fach`";
        DBManager::get()->query($query);

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "ALTER TABLE `studiengang_news_fach`
                    CHANGE COLUMN `fach_id` `studiengang_id` CHAR(32) NOT NULL,
                    RENAME TO `studiengang_news_studiengang`";
        DBManager::get()->query($query);

        SimpleORMap::expireTableScheme();
    }
}
