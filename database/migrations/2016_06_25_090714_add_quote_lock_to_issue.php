<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddQuoteLockToIssue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_issues', 'lock_quote')) {
                $table->boolean('lock_quote')->default(false);
            }
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
            $table->dropColumn('lock_quote');
        });
    }

    protected function insert($model, $data)
    {
        foreach ($data as $name => $value) {
            if (!is_array($value)) {
                $model->$name = $value;
            }
        }
        $model->save();

        return $model;
    }
}
