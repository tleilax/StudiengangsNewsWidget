<?php
/**
 * Migrations that adds the required cronjob for the widget.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 */
class SetupRoles extends Migration
{
    const ROLES = [
        'fk1_stgnews',
        'fk2_stgnews',
        'fk3_stgnews',
        'fk4_stgnews',
        'fk5_stgnews',
        'fk6_stgnews',
    ];

    public function up()
    {
        foreach (self::ROLES as $name) {
            $role = new Role();
            $role->setRolename($name);
            $role->setSystemtype(false);
            RolePersistence::saveRole($role);
        }

    }

    public function down ()
    {
        $roles = RolePersistence::getAllRoles();
        foreach ($roles as $role) {
            if (in_array($role->getRolename(), self::ROLES)) {
                RolePersistence::deleteRole($role);
            }
        }
    }
}
