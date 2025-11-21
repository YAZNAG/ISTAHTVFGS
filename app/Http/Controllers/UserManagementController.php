<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_utilisateurs', only: ['index']),
            new Middleware('permission:show_utilisateurs', only: ['show']),
            new Middleware('permission:create_utilisateurs', only: ['create', 'store']),
            new Middleware('permission:edit_utilisateurs', only: ['edit', 'update']),

        ];
    }

    
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::query()
            ->with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // keeps ?search= in pagination links

        return Inertia::render('UserManagement/Index', [
            'users' => UserResource::collection($users),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $roles = Role::all(['id', 'name']);
        return Inertia::modal('UserManagement/CreateUser', [
            'roles' => $roles
        ])->baseRoute('users.index');
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'L\'utilisateur a été créé avec succès');
    }

    public function show(User $user)
    {
        return Inertia::modal('UserManagement/ShowUser', [
            'user' => UserResource::make($user),
        ])->baseRoute('users.index');
    }

    public function edit(User $user)
    {
        $roles = Role::all(['id', 'name']);
        return Inertia::modal('UserManagement/EditUser', [
            'user' =>  UserResource::make($user),
            'roles' => $roles,
        ])->baseRoute('users.index');
    }

    public function update(UpdateUserRequest $request, User $user)
    {

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ]);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'L\'utilisateur a été mis à jour avec succès');
    }


    public function toggleStatus(User $user)
    {
        // Toggle the status
        $user->status = !$user->status;
        $user->save();

        return redirect()->route('users.index');
    }
}
