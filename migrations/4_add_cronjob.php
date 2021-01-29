<?php
/**
 * Migrations that adds the required cronjob for the widget.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 */
class AddCronjob extends Migration
{
    public function __construct($verbose = false)
    {
        parent::__construct($verbose);

        require_once __DIR__ . '/../classes/Cronjob.php';
    }

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
        $task_id = CronjobScheduler::registerTask(new StudiengangsNews\Cronjob());
    }

    /**
     * Removes the cronjob.
     */
    public function down()
    {
        $task_id = CronjobTask::findOneByClass(StudiengangsNews\Cronjob::class)->task_id;
        if ($task_id) {
            CronjobScheduler::unregisterTask($task_id);
        }
    }
}
