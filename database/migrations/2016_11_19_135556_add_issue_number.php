<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model\Project;

class AddIssueNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_issues', function (Blueprint $table) {
            $table->integer('issue_no')->unsigned()->default(1);
        });

        $projects = Project::all();
        foreach ($projects as $project) {
            $issues = $project->issues()->orderBy('id')->get();
            $counter = 1;
            foreach ($issues as $issue) {
                $issue->issue_no = $counter;
                $issue->save();
                $counter++;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_issues', function (Blueprint $table) {
            $table->dropColumn('issue_no');
        });
    }
}
