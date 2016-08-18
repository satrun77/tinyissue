<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddKanbanToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('projects_kanban_tags')) {
            Schema::create('projects_kanban_tags', function (Blueprint $table) {
                $table->bigInteger('project_id');
                $table->bigInteger('tag_id');
                $table->smallInteger('position');
                $table->primary(['project_id', 'tag_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('projects_kanban_tags');
    }
}
