<?php

declare(strict_types=1);

namespace Francken\Features\Auth\Admin;

use Francken\Auth\Http\Controllers\Admin\RolesController;
use Francken\Auth\Permission;
use Francken\Auth\Role;
use Francken\Features\LoggedInAsAdmin;
use Francken\Features\TestCase;

class RolesFeature extends TestCase
{
    use LoggedInAsAdmin;

    /** @test */
    public function it_allows_to_givs_a_roles_to_an_account() : void
    {
        $role = Role::create(['name' => 'Custom role']);
        $permission = Permission::create(['name' => 'Custom permission']);

        $this->visit(action([RolesController::class, 'index']))
            ->click($role->name)
            ->seePageIs(action(
                [RolesController::class, 'show'],
                ['role' => $role]
            ))
            ->see($permission->name)
             ->select($permission->id, 'permission_id')
            ->press('Add')
             ->seePageIs(
                 action([RolesController::class, 'show'], ['role' => $role])
             );

        $this->assertTrue($role->hasPermissionTo($permission->name));
        $this->press('Remove')
             ->seePageIs(
                 action([RolesController::class, 'show'], ['role' => $role])
             );

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo($permission->name));
    }
}
