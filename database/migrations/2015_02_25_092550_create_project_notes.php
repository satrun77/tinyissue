<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model;

class CreateProjectNotes extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('projects_notes')) {
            Schema::create('projects_notes', function (Blueprint $table) {
                $table->increments('id')->unsigned();
                $table->bigInteger('project_id');
                $table->bigInteger('created_by');
                $table->text('body');
                $table->timestamps();
            });

            $activity = new Model\Activity();
            $activity->description = 'Note on a project';
            $activity->activity = 'note';
            $activity->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('activity');
        Model\Activity::where('activity', '=', 'note')->delete();
    }

}