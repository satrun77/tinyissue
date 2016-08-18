<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Tinyissue\Model\Message;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Role;

class AddMessagingSystem extends Migration
{
    protected function getEvents()
    {
        return collect(Queue::getEventsNames())->push(Queue::MESSAGE_IN_ALL_ISSUES);
    }

    public function getMessages()
    {
        $defaultMessages = Message::$defaultMessageToRole;
        $events          = $this->getEvents()->flip()->prepend(0, Queue::MESSAGE_IN_ALL_ISSUES);

        return collect([
            $defaultMessages[Role::ROLE_ADMIN]     => $events->map(function () {
                return 0;
            }),
            $defaultMessages[Role::ROLE_USER]      => $events->map(function ($event, $key) {
                return (int) in_array($key, [
                    Queue::ADD_ISSUE,
                    Queue::CHANGE_TAG_ISSUE,
                    Queue::UPDATE_ISSUE,
                    Queue::ASSIGN_ISSUE,
                    Queue::CLOSE_ISSUE,
                ]);
            }),
            $defaultMessages[Role::ROLE_DEVELOPER] => $events->map(function ($event, $key) {
                return (int) ($key !== Queue::MESSAGE_IN_ALL_ISSUES);
            }),
            $defaultMessages[Role::ROLE_MANAGER]   => $events->map(function () {
                return 1;
            }),
        ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add limit tag messages by role
        Schema::table('tags', function (Blueprint $table) {
            if (!Schema::hasColumn('tags', 'message_limit')) {
                $table->smallInteger('message_limit')->default(0);
            }
        });

        // Add message per project
        Schema::table('projects_users', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_users', 'message_id')) {
                $table->bigInteger('message_id')->nullable();
            }
        });

        // Get all available events
        $events = $this->getEvents();

        // TODO remove this after test complete
        if (Schema::hasTable('messages')) {
            Schema::drop('messages');
        }

        // Create message table
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) use ($events) {
                $table->increments('id')->unsigned();
                $table->string('name', 255)->unique();

                $events->each(function ($event) use ($table) {
                    $table->boolean($event)->default(0);
                });
            });
        }

        // TODO remove this after test complete
        if (Schema::hasTable('messages_queue')) {
            Schema::drop('messages_queue');
        }

        // Create message queue table
        if (!Schema::hasTable('messages_queue')) {
            Schema::create('messages_queue', function (Blueprint $table) use ($events) {
                $table->increments('id')->unsigned();
                $table->timestamps();
                $table->string('event', 255);
                $table->bigInteger('model_id')->nullable();
                $table->bigInteger('change_by_id');
                $table->longText('payload')->nullable();
                $table->string('model_type', 255);
            });
        }

        // Get messages & their roles
        $messages = $this->getMessages();

        // Save the messages to db and add them to a collection
        $savedMessages = new Collection();
        $messages->each(function ($events, $name) use ($savedMessages) {
            $message = new Message();
            $message->name = $name;
            foreach ($events as $event => $status) {
                $message->{$event} = $status;
            }
            $message->save();
            $savedMessages->push($message);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('message_limit');
        });

        Schema::table('projects_users', function (Blueprint $table) {
            $table->dropColumn('message_id');
        });

        Schema::drop('messages');
    }
}
