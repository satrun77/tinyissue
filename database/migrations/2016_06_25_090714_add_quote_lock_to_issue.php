<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Tinyissue\Model\Permission;
use Tinyissue\Model\Role;

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

        // Insert Permissions Data
        $permission = new  Permission();
        if (!($permission = $permission->where('permission', '=', Permission::PERM_ISSUE_VIEW_QUOTE)->first())) {
            $permission->permission  = Permission::PERM_ISSUE_VIEW_QUOTE;
            $permission->description = 'Allow user to view issue quote with it\'s locked.';
            $permission->auto_has    = null;
            $permission->save();
        }
        $manager = (new Role())->where('role', '=', Role::ROLE_MANAGER)->first();
        $admin   = (new Role())->where('role', '=', Role::ROLE_ADMIN)->first();

        // Insert Roles Permissions Data
        $roles = [
            ['role_id' => $manager->id],
            ['role_id' => $admin->id],
        ];
        foreach ($roles as $role) {
            $rolePermission = new Role\Permission();
            if (!$rolePermission->where('role_id', '=', $role['role_id'])->where('permission_id', '=', $permission->id)->first()) {
                $rolePermission->role_id       = $role['role_id'];
                $rolePermission->permission_id = $permission->id;
                $rolePermission->save();
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
            $table->dropColumn('lock_quote');
        });
    }
}
