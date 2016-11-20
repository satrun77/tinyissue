<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model\Project;

class AddProjectsKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('key', 3)->default('AAA');
        });

        $projects = Project::all();
        foreach ($projects as $project) {
            $project->key = $project->id;
            $project->save();
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->unique('key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('key');
        });
    }
}
