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
        Config::get()->create('STG_NEWS_WIDGET_TITLE', [
            'value'       => 'Neuigkeiten zu Ihren Studiengängen',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => '',
            'description' => 'Enthält den Titel des "Neuigkeiten zu ihren Studiengängen"-Widgets',
        ]);
    }

    /**
     * Removes the config entry
     */
    public function down()
    {
        Config::get()->delete('STG_NEWS_WIDGET_TITLE');
    }
}
