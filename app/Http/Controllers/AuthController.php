<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $request->merge(['phone_number' => PhoneNumber::normalize($request->input('phone_number'))]);

        $data = $request->validate([
            'account_type' => ['required', Rule::in(['teacher', 'administrator'])],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'employee_id' => ['required', 'string', 'max:30', 'unique:users,employee_id'],
            'department' => ['nullable', 'required_if:account_type,teacher', 'string', 'max:100'],
            'admin_code' => ['nullable', 'required_if:account_type,administrator', 'string'],
            'phone_number' => ['required', 'regex:/^\+639\d{9}$/', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'phone_number.regex' => 'Enter a valid Philippine mobile number, for example 0917 123 4567.',
            'phone_number.unique' => 'This mobile number is already used by another account.',
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
            'phone_number' => $data['phone_number'],
            'department' => $data['account_type'] === 'teacher' ? $data['department'] : null,
            // Teachers wait for administrator approval; administrators (valid code) are approved at once.
            'approval_status' => $data['account_type'] === 'teacher' ? 'pending' : 'approved',
        ])->save();

        if ($data['account_type'] === 'teacher') {
            $fullName = collect([$data['first_name'], $data['middle_name'] ?? null, $data['last_name']])->filter()->implode(' ');
            $details = "{$fullName} ({$data['email']})\nEmployee ID: {$data['employee_id']}\nDepartment: {$data['department']}\n\nReview it here: ".url('/admin/approvals');

            // Tell the administrators after the response is sent
            defer(fn () => app(NotificationService::class)
                ->notifyAdmins('New teacher account waiting for approval', $details));

            return redirect('/pending');
        }

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

            $account = Auth::user();

            if ($account->approval_status === 'pending') {
                $waiting = Carbon::parse($account->created_at)->diffForHumans();
                Auth::logout();

                return back()
                    ->withErrors(['email' => "Your account is still waiting for administrator approval (registered {$waiting}). Approval is usually completed within 1 day."])
                    ->onlyInput('email', 'login_as');
            }

            if ($account->approval_status === 'rejected') {
                $reason = $account->rejection_reason ? " Reason: {$account->rejection_reason}" : '';
                Auth::logout();

                return back()
                    ->withErrors(['email' => 'Your registration was not approved, or your access was removed.'.$reason])
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
