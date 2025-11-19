<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\PermissionGroupResource;
use App\Http\Resources\RoleEditResource;
use App\Http\Resources\RoleIndexResource;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('users:id,name', 'permissions:id')->get();

        return Inertia::render('Roles/Index', [
            'roles' => RoleIndexResource::collection($roles),
        ]);
    }

    public function create(Request $request)
    {
        $permissionGroups = PermissionGroup::with('permissions')->get();

        return Inertia::modal('Roles/CreateRoleModal', [
            'permission_groups' => PermissionGroupResource::collection($permissionGroups),
        ])->baseRoute('roles.index');
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create([
            'name' => $request->name,
        ]);

        $role->givePermissionTo($request->permissions);

        return redirect()->back()->with('success', "Le rôle $request->name a été créé avec succès.");
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Role $role)
    {
        $permissionGroups = PermissionGroup::with('permissions')->get();
        $role->load('permissions:id');

        return Inertia::modal('Roles/EditRoleModal', [
            'permission_groups' => PermissionGroupResource::collection($permissionGroups),
            'role' => RoleEditResource::make($role),
        ])->baseRoute('roles.index');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {

        $role->update([
            'name' => $request->name,
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->back()->with('success', "Le $role->name rôle a été mis à jour avec succès.");
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->back()->with('success', "Le rôle $role->name a été supprimé avec succès.");
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_roles', only: ['index']),
            new Middleware('permission:show_roles', only: ['show']),
            new Middleware('permission:create_roles', only: ['create', 'store']),
            new Middleware('permission:edit_roles', only: ['edit', 'update']),
            new Middleware('permission:delete_roles', only: ['destroy']),
        ];
    }
}
