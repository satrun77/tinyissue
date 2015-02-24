<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssueQuote extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('projects_issues', function (Blueprint $table) {
            $table->bigInteger('time_quote')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_issues', function (Blueprint $table) {
            $table->dropColumn('time_quote');
        });
    }

}
