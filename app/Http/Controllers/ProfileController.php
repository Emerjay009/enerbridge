<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $role = Role::find($user->role_id)
            ?? Role::where('slug', 'teacher')->first()
            ?? new Role(['slug' => 'teacher', 'label' => 'Teacher / Professor', 'privileges' => [], 'limitations' => []]);

        return view('profile', [
            'user' => $user,
            'role' => $role,
            'botUsername' => config('services.telegram.username'),
        ]);
    }

    public function updatePhone(Request $request)
    {
        $request->merge(['phone_number' => PhoneNumber::normalize($request->input('phone_number'))]);

        $data = $request->validate([
            'phone_number' => [
                'required',
                'regex:/^\+639\d{9}$/',
                Rule::unique('users', 'phone_number')->ignore($request->user()->id),
            ],
        ], [
            'phone_number.regex' => 'Enter a valid Philippine mobile number, for example 0917 123 4567.',
            'phone_number.unique' => 'This mobile number is already used by another account.',
        ]);

        $request->user()->forceFill(['phone_number' => $data['phone_number']])->save();

        return back()->with('status', 'Your mobile number was saved.');
    }
}
