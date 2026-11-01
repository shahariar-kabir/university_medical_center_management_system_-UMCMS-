<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'This account has been deactivated. Please contact the administrator.']);
            }

            $request->session()->regenerate();

            return $this->redirectForRole($user)->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function quickLogin(string $role): RedirectResponse
    {
        $targetEmail = match ($role) {
            'admin' => 'admin@bu.edu.bd',
            'doctor' => 'doctor@bu.edu.bd',
            'pharmacist' => 'pharma@bu.edu.bd',
            'patient' => 'patient@bu.edu.bd',
            default => null,
        };

        if (! $targetEmail) {
            return redirect()->route('login')->with('error', 'Invalid role requested.');
        }

        $user = User::where('email', $targetEmail)->first();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Demo account not found. Please run seeder.');
        }

        Auth::login($user);
        request()->session()->regenerate();

        $roleLabel = $user->designation ?? ucfirst($user->role);

        return $this->redirectForRole($user)->with('success', "Logged in as {$user->name} ({$roleLabel})");
    }

    public function registerPatient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'university_id' => 'required|string|max:50|unique:patients,university_id',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'blood_group' => 'nullable|string|max:5',
            'nationality' => 'nullable|string|max:50',
            'role' => 'required|string',
            'department' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:50',
            'email' => 'required|email|max:200|unique:users,email',
            'phone' => 'required|string|max:20',
            'emergency_name' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'allergies' => 'nullable|string|max:500',
            'conditions' => 'nullable|array',
            'medications' => 'nullable|string|max:500',
            'declaration' => 'accepted',
        ]);

        // Create User account for the patient
        $user = User::create([
            'name' => "{$validated['first_name']} {$validated['last_name']}",
            'email' => $validated['email'],
            'password' => Hash::make('password'), // default initial password
            'role' => 'patient',
            'university_id' => $validated['university_id'],
            'phone' => $validated['phone'],
            'department' => $validated['department'] ?? null,
            'designation' => ucfirst($validated['role']),
            'is_active' => true,
        ]);

        // Create Patient profile
        Patient::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'university_id' => $validated['university_id'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'blood_group' => $validated['blood_group'] ?? null,
            'nationality' => $validated['nationality'] ?? 'Bangladeshi',
            'role_category' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'year_semester' => $validated['year'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'emergency_name' => $validated['emergency_name'] ?? null,
            'emergency_phone' => $validated['emergency_phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'conditions' => $validated['conditions'] ?? [],
            'medications' => $validated['medications'] ?? null,
            'is_verified' => true,
        ]);

        // Log the newly registered patient in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('patient.dashboard')->with('success', 'Registration successful! Your patient account has been verified and created.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    private function redirectForRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'doctor' => redirect()->route('doctor.dashboard'),
            'pharmacist' => redirect()->route('pharmacy.dashboard'),
            'patient' => redirect()->route('patient.dashboard'),
            default => redirect('/'),
        };
    }
}
