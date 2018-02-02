<?php
/**
 * Migrations that adds the required cronjob for the widget.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 */
class ChangeRoleConfig extends Migration
{
    public function up()
    {
        $delete_roles = [
            'fk1_stgnews',
            'fk2_stgnews',
            'fk3_stgnews',
            'fk4_stgnews',
            'fk5_stgnews',
            'fk6_stgnews',
        ];
        $roles = RolePersistence::getAllRoles();
        foreach ($roles as $role) {
            if (in_array($role->getRolename(), $delete_roles)) {
                RolePersistence::deleteRole($role);
            }
        }

        $role = new Role();
        $role->setRolename('stgnews_admin');
        $role->setSystemtype(false);
        RolePersistence::saveRole($role);
    }

    public function down ()
    {   }
}
