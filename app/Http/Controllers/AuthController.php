<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'account_type' => ['required', Rule::in(['teacher', 'administrator'])],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'employee_id' => ['required', 'string', 'max:30', 'unique:users,employee_id'],
            'department' => ['nullable', 'required_if:account_type,teacher', 'string', 'max:100'],
            'admin_code' => ['nullable', 'required_if:account_type,administrator', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Administrator accounts need the secret access code set in .env
        if ($data['account_type'] === 'administrator') {
            $code = config('services.enerbridge_admin_code');

            if (! $code) {
                return back()
                    ->withErrors(['admin_code' => 'Administrator sign-up is not enabled on this system.'])
                    ->withInput($request->except('password', 'password_confirmation', 'admin_code'));
            }

            if (! hash_equals($code, (string) $data['admin_code'])) {
                return back()
                    ->withErrors(['admin_code' => 'The administrator access code is incorrect.'])
                    ->withInput($request->except('password', 'password_confirmation', 'admin_code'));
            }
        }

        $role = Role::where('slug', $data['account_type'])->firstOrFail();

        $user = new User();
        $user->forceFill([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'employee_id' => $data['employee_id'],
            'department' => $data['account_type'] === 'teacher' ? $data['department'] : null,
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'login_as' => ['required', Rule::in(['teacher', 'administrator'])],
        ]);

        $key = Str::lower($data['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['email' => "Too many login attempts. Try again in {$seconds} seconds."])
                ->onlyInput('email', 'login_as');
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $slug = Role::find(Auth::user()->role_id)?->slug;

            // The account type chosen on the login page must match the account's role
            if ($slug !== $data['login_as']) {
                Auth::logout();
                RateLimiter::hit($key, 60);

                $label = $data['login_as'] === 'administrator' ? 'an Administrator' : 'a Teacher / Professor';

                return back()
                    ->withErrors(['email' => "This account is not registered as {$label}. Choose the correct account type."])
                    ->onlyInput('email', 'login_as');
            }

            RateLimiter::clear($key);
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        RateLimiter::hit($key, 60);

        return back()
            ->withErrors(['email' => 'The email or password is incorrect.'])
            ->onlyInput('email', 'login_as');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
