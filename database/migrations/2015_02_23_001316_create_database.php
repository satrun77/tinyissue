<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model;

class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // activity
        Schema::create('activity', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('description', 255)->nullable();
            $table->string('activity', 255)->nullable();
        });

        // permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('permission', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('auto_has', 255)->nullable();
        });

        // projects
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->tinyInteger('status')->nullable()->default("1");
            $table->timestamps();
        });

        // projects_issues
        Schema::create('projects_issues', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('closed_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('assigned_to')->nullable();
            $table->bigInteger('project_id')->nullable();
            $table->tinyInteger('status')->nullable()->default("1");
            $table->string('title', 255)->nullable();
            $table->text('body')->nullable();
            $table->timestamps();
            $table->dateTime('closed_at')->nullable();
        });

        // projects_issues_attachments
        Schema::create('projects_issues_attachments', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->bigInteger('issue_id')->nullable();
            $table->bigInteger('comment_id')->nullable();
            $table->bigInteger('uploaded_by')->nullable();
            $table->bigInteger('filesize')->nullable();
            $table->string('filename', 250)->nullable();
            $table->string('fileextension', 255)->nullable();
            $table->string('upload_token', 100)->nullable();
            $table->timestamps();
        });

        // projects_issues_comments
        Schema::create('projects_issues_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('project_id')->nullable();
            $table->bigInteger('issue_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // projects_users
        Schema::create('projects_users', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('project_id')->nullable();
            $table->bigInteger('role_id')->nullable();
            $table->timestamps();
        });

        // roles
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->string('role', 255)->nullable();
            $table->string('description', 255)->nullable();
        });

        // roles_permissions
        Schema::create('roles_permissions', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('permission_id')->nullable();
        });

        // sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->increments('id', 40);
            $table->unsignedInteger('last_activity');
            $table->text('data');
        });

        // settings
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 255)->nullable();
            $table->text('value')->nullable();
            $table->string('name', 255)->nullable();
        });

        // users
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->bigInteger('role_id')->default("1")->unsigned();
            $table->string('email', 255)->unique();
            $table->string('password', 60)->nullable();
            $table->string('firstname', 255)->nullable();
            $table->string('lastname', 255)->nullable();
            $table->string('language', 5)->nullable();
            $table->unsignedInteger('deleted');
            $table->rememberToken();
            $table->timestamps();
        });

        // users_activity
        Schema::create('users_activity', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('action_id')->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });

        // Insert Permisions Data
        $permissions = [
            [
                'permission'  => 'issue-view',
                'description' => 'View issues in project assigned to',
                'auto_has'    => null,
            ],
            [
                'permission'  => 'issue-create',
                'description' => 'Create issues in projects assigned to',
                'auto_has'    => null,
            ],
            [
                'permission'  => 'issue-comment',
                'description' => 'Comment in issues in projects assigned to',
                'auto_has'    => '1',
            ],
            [
                'permission'  => 'issue-modify',
                'description' => 'Modify issues in projects assigned to',
                'auto_has'    => '1',
            ],
            [
                'permission'  => 'administration',
                'description' => 'Administration tools, such as user management and application settings',
                'auto_has'    => null,
            ],
            [
                'permission'  => 'project-create',
                'description' => 'Create a new project',
                'auto_has'    => null,
            ],
            [
                'permission'  => 'project-modify',
                'description' => 'Modify a project assigned to',
                'auto_has'    => null,
            ],
            [
                'permission'  => 'project-all',
                'description' => 'View, modify all projects and issues',
                'auto_has'    => '1,2,3,4',
            ],
        ];
        foreach ($permissions as $permission) {
            $this->insert(new  Model\Permission(), $permission);
        }

        // Insert Roles Data
        $roles = [
            [
                'name'        => 'User',
                'role'        => 'user',
                'description' => 'Only can read the issues in the projects they are assigned to',
            ],
            [
                'name'        => 'Developer',
                'role'        => 'developer',
                'description' => 'Can update issues in the projects they are assigned to',
            ],
            [
                'name'        => 'Manager',
                'role'        => 'manager',
                'description' => 'Can update issues in all projects, even if they aren\'t assigned',
            ],
            [
                'name'        => 'Administrator',
                'role'        => 'administrator',
                'description' => 'Can update all issues in all projects, create users and view administration',
            ],
        ];

        foreach ($roles as $role) {
            $this->insert(new Model\Role(), $role);
        }

        // Insert Roles Permissions Data
        $rolesPermissions = [
            ['role_id' => '1', 'permission_id' => '1'],
            ['role_id' => '1', 'permission_id' => '2'],
            ['role_id' => '1', 'permission_id' => '3'],
            ['role_id' => '2', 'permission_id' => '1'],
            ['role_id' => '2', 'permission_id' => '2'],
            ['role_id' => '2', 'permission_id' => '3'],
            ['role_id' => '2', 'permission_id' => '4'],
            ['role_id' => '3', 'permission_id' => '8'],
            ['role_id' => '3', 'permission_id' => '1'],
            ['role_id' => '3', 'permission_id' => '2'],
            ['role_id' => '3', 'permission_id' => '3'],
            ['role_id' => '3', 'permission_id' => '4'],
            ['role_id' => '4', 'permission_id' => '1'],
            ['role_id' => '4', 'permission_id' => '2'],
            ['role_id' => '4', 'permission_id' => '3'],
            ['role_id' => '4', 'permission_id' => '5'],
            ['role_id' => '4', 'permission_id' => '6'],
            ['role_id' => '4', 'permission_id' => '7'],
            ['role_id' => '4', 'permission_id' => '8'],
            ['role_id' => '4', 'permission_id' => '4'],
        ];

        foreach ($rolesPermissions as $rolePermission) {
            $this->insert(new Model\Role\Permission(), $rolePermission);
        }

        $activities = [
            ['description' => 'Opened a new issue', 'activity' => 'create-issue'],
            ['description' => 'Commented on a issue', 'activity' => 'comment'],
            ['description' => 'Closed an issue', 'activity' => 'close-issue'],
            ['description' => 'Reopened an issue', 'activity' => 'reopen-issue'],
            ['description' => 'Reassigned an issue', 'activity' => 'reassign-issue'],
        ];

        foreach ($activities as $activity) {
            $this->insert(new Model\Activity(), $activity);
        }
    }

    protected function insert($model, $data)
    {
        foreach ($data as $name => $value) {
            $model->$name = $value;
        }

        return $model->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('activity');
        Schema::drop('permissions');
        Schema::drop('projects');
        Schema::drop('projects_issues');
        Schema::drop('projects_issues_attachments');
        Schema::drop('projects_issues_comments');
        Schema::drop('projects_users');
        Schema::drop('roles');
        Schema::drop('roles_permissions');
        Schema::drop('sessions');
        Schema::drop('settings');
        Schema::drop('users');
        Schema::drop('users_activity');
    }
}
