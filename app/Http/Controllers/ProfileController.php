<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $role = Role::find($user->role_id)
            ?? Role::where('slug', 'teacher')->first()
            ?? new Role(['slug' => 'teacher', 'label' => 'Teacher / Professor', 'privileges' => [], 'limitations' => []]);

        return view('profile', ['user' => $user, 'role' => $role]);
    }
}
