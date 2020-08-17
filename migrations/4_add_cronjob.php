<?php
/**
 * Migrations that adds the required cronjob for the widget.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 */
class AddCronjob extends Migration
{
    /**
     * Returns the description of the migration.
     *
     * @return String containing the migration
     */
    public function description()
    {
        return 'Fügt den Cronjob zum (De)Aktivieren des Widgets hinzu';
    }

    /**
     * Sets up the cronjob and schedules it to run every minute.
     */
    public function up()
    {
        $task_id = CronjobScheduler::registerTask($this->getCronjobFilename());
    }

    /**
     * Removes the cronjob.
     */
    public function down()
    {
        $task_id = CronjobTask::findByFilename($this->getCronjobFilename())->task_id;
        if ($task_id) {
            CronjobScheduler::unregisterTask($task_id);
        }
    }

    /**
     * Returns the relative path to the cronjob.
     *
     * @return String containing the relative path
     */
    private function getCronjobFilename()
    {
        return studip_relative_path(realpath(__DIR__ . '/../classes/Cronjob.php'));
    }
}
