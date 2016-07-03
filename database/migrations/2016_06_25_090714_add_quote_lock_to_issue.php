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
        $admin     = (new Role())->where('role', '=', Role::ROLE_ADMIN)->first();
        $manager   = (new Role())->where('role', '=', Role::ROLE_MANAGER)->first();
        $developer = (new Role())->where('role', '=', Role::ROLE_DEVELOPER)->first();

        // Insert Permissions Data
        $permissions = [
            [
                'permission'   => Permission::PERM_ISSUE_VIEW_LOCKED_QUOTE,
                'description'  => 'Allow user to view issue quote when it\'s locked.',
                'auto_has'     => null,
                'assign_roles' => [
                    $manager->id,
                    $admin->id,
                    $developer->id,
                ],
            ],
            [
                'permission'   => Permission::PERM_ISSUE_LOCK_QUOTE,
                'description'  => 'Allow user to modify & lock issue quote.',
                'auto_has'     => null,
                'assign_roles' => [
                    $manager->id,
                    $admin->id,
                ],
            ],
        ];

        foreach ($permissions as $permissionData) {
            if (!($permission = Permission::where('permission', '=', $permissionData['permission'])->first())) {
                $permission = $this->insert(new Permission(), $permissionData);
                foreach ($permissionData['assign_roles'] as $roleId) {
                    if (!Role\Permission::where('role_id', '=', $roleId)->where('permission_id', '=',
                        $permission->id)->first()
                    ) {
                        $this->insert(new Role\Permission(), [
                            'role_id'       => $roleId,
                            'permission_id' => $permission->id,
                        ]);
                    }
                }
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

        $permissions = [
            Permission::PERM_ISSUE_VIEW_LOCKED_QUOTE,
            Permission::PERM_ISSUE_LOCK_QUOTE,
        ];
        foreach ($permissions as $permissionData) {
            $permission = Permission::where('permission', '=', $permissionData)->first();
            if ($permission) {
                Role\Permission::where('permission_id', '=', $permission->id)->delete();
                $permission->delete();
            }
        }
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
