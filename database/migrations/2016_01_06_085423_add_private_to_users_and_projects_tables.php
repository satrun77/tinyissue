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

/**
 * AddPrivateToUsersAndProjectsTables is a migration class for upgrading the database to use
 * private projects and users feature.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class AddPrivateToUsersAndProjectsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('private')->default(true);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('private')->default(false);
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
            $table->dropColumn('private');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('private');
        });
    }
}
