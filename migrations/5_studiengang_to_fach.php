<?php

class StudiengangToFach extends Migration {

    function up() {
        DBManager::get()->query("ALTER TABLE `studiengang_news_studiengang`
                                 CHANGE COLUMN `studiengang_id` `fach_id` CHAR(32) NOT NULL , RENAME TO `studiengang_news_fach` ;
        ");

        StudipCacheFactory::getCache()->expire('DB_TABLE_SCHEMES');
    }

    function down() {

    }
}