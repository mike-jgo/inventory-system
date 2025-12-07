<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::with('roles')->select('id', 'name', 'email', 'created_at')->get(),
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'nullable', // Allow string or array
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->assignRole($data['roles']);
        }

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|string|email|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:8',
            'roles' => 'nullable',
        ]);

        // 🧹 Remove empty or null password BEFORE updating
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            // Hash if present
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return back()->with('success', "User '{$user->name}' updated successfully.");
    }


    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', "User '{$user->name}' deleted successfully.");
    }
}
