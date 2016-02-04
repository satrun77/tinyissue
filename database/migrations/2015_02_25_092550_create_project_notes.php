<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model;

/**
 * CreateProjectNotes is a migration class for upgrading the database to use project notes feature.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
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

            $activity              = new Model\Activity();
            $activity->description = 'Note on a project';
            $activity->activity    = 'note';
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
