<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model;

class CreateIssueTags extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->increments('id')->unsigned();
                $table->bigInteger('parent_id')->default(0);
                $table->string('name', 255)->unique();
                $table->string('bgcolor', 50)->nullable();
                $table->boolean('group');
                $table->timestamps();
            });

            $groups = ['status', 'type', 'resolution'];
            foreach ($groups as $group) {
                $model = new Model\Tag;
                $model->name = $group;
                $model->group = true;
                $model->save();
            }

            $tags = [
                [
                    'name'      => 'open',
                    'parent_id' => 'status',
                    'bgcolor'   => '#c43c35',
                ],
                [
                    'name'      => 'testing',
                    'parent_id' => 'status',
                    'bgcolor'   => '#6c8307',
                ],
                [
                    'name'      => 'closed',
                    'parent_id' => 'status',
                    'bgcolor'   => '#46a546',
                ],
                [
                    'name'      => 'feature',
                    'parent_id' => 'type',
                    'bgcolor'   => '#62cffc',
                ],
                [
                    'name'      => 'bug',
                    'parent_id' => 'type',
                    'bgcolor'   => '#f89406',
                ],
                [
                    'name'      => 'won\'t fix',
                    'parent_id' => 'resolution',
                    'bgcolor'   => '#812323',
                ],
                [
                    'name'      => 'fixed',
                    'parent_id' => 'resolution',
                    'bgcolor'   => '#048383',
                ],
            ];
            foreach ($tags as $tag) {
                $model = new Model\Tag;
                $model->name = $tag['name'];
                $model->bgcolor = $tag['bgcolor'];
                $model->parent_id = Model\Tag::where('name', '=', $tag['parent_id'])->first()->id;
                $model->group = false;
                $model->save();
            }
        }

        if (!Schema::hasTable('projects_issues_tags')) {
            Schema::create('projects_issues_tags', function (Blueprint $table) {
                $table->bigInteger('issue_id');
                $table->bigInteger('tag_id');
                $table->primary(['issue_id', 'tag_id']);
            });
        }

        $openIssues = Model\Project\Issue::where('status', '=', Model\Project\Issue::STATUS_OPEN)->lists('id');
        if (count($openIssues) > 0) {
            $openTag = Model\Tag::where('parent_id', '=', Model\Tag::where('name', '=', 'status')->first()->id)->where('name', '=', 'open')->first();
            $openTag->issues()->attach($openIssues);
        }

        $closedIssues = Model\Project\Issue::where('status', '=', Model\Project\Issue::STATUS_CLOSED)->lists('id');
        if (count($closedIssues) > 0) {
            $closedTag = Model\Tag::where('parent_id', '=', Model\Tag::where('name', '=', 'status')->first()->id)->where('name', '=', 'closed')->first();
            $closedTag->issues()->attach($closedIssues);
        }

        // Create activity type for tag update
        $activity = new Model\Activity();
        $activity->description = 'Updated issue tags';
        $activity->activity = 'update-issue-tags';
        $activity->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tags');
        Schema::drop('projects_issues_tags');
        Model\Activity::where('activity', '=', 'update-issue-tags')->delete();
    }

}
