<?php

/**
 * Migration adding a sub_type column to the mooc_blocks table.
 *
 * @author Christian Flothmann <christian.flothmann@uos.de>
 */
class AddTnvisibleField extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function description()
    {
        return 'Adds field tnvisible to seminar_tabs';
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $db = DBManager::get();
        $db->exec('ALTER TABLE seminar_tabs ADD COLUMN tn_visible BOOLEAN NOT NULL DEFAULT TRUE AFTER position');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $db = DBManager::get();
        $db->exec('ALTER TABLE seminar_tabs DROP COLUMN tn_visible');
    }
}
