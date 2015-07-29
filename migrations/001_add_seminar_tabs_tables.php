<?php
class AddSeminarTabsTables extends DBMigration {

    public function description () {
        return 'create tables for the SeminarTabs-plugin';
    }

    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE  TABLE `seminar_tabs` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `tab` VARCHAR(64) NULL ,
            `seminar_id` VARCHAR(32) NULL ,
            `title` VARCHAR(255) NULL ,
            `position` INT NULL DEFAULT 0 ,
            `chdate` INT NULL ,
            `mkdate` INT NULL ,
            PRIMARY KEY (`id`)
        )");
		
        SimpleORMap::expireTableScheme();
    }

    public function down () {
        DBManager::get()->exec("DROP TABLE seminar_tabs");
        SimpleORMap::expireTableScheme();
    }
}
