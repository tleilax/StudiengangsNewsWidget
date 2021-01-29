<?php
class RemoveConfigEntry extends Migration
{
    /**
     * Removes the config entry
     */
    public function up()
    {
        Config::get()->delete('STG_NEWS_WIDGET_TITLE');
    }

    /**
     * Creates the config entry
     */
    public function down()
    {
        Config::get()->create('STG_NEWS_WIDGET_TITLE', [
            'value'       => 'Neuigkeiten zu Ihren Studiengängen',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => '',
            'description' => 'Enthält den Titel des "Neuigkeiten zu ihren Studiengängen"-Widgets',
        ]);
    }
}
