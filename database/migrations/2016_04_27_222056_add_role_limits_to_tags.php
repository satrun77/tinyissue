<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User\Activity;

class AddRoleLimitsToTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->bigInteger('role_limit')->nullable();
        });

        // tags to remove
        $tags = (new Tag())->where('name', '=', 'open')
            ->orWhere('name', '=', 'closed')
            ->get();

        // Array of tag ids
        $ids = $tags->lists('id')->toArray();

        // Remove activities
        (new Activity())->where('data', 'like', '%open%')
            ->orWhere('data', 'like', '%closed%')
            ->get()
            ->each(function (Activity $activity) use ($ids) {
                $data = $activity->data;
                foreach ($data as $key => $row) {
                    foreach ($row as $key2 => $tag) {
                        if (in_array($tag['id'], $ids)) {
                            unset($data[$key][$key2]);
                        }
                    }
                }

                $activity->data = $data;
                $activity->save();
            });

        // Remove kanban tags
        DB::table('projects_kanban_tags')->whereIn('tag_id', $ids)->delete();

        // remove relation to issuez
        DB::table('projects_issues_tags')->whereIn('tag_id', $ids)->delete();

        // delete the tags
        foreach ($tags as $tag) {
            $tag->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('role_limit');
        });
    }
}
