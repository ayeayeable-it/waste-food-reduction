<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->auditLogService->log(
            action: 'user_registered',
            targetType: User::class,
            targetId: $user->id,
            details: ['role' => $user->role],
            userId: $user->id
        );

        $dashboardRoute = $user->isSeller() ? 'seller.dashboard' : 'buyer.dashboard';

        return redirect()->route($dashboardRoute)
            ->with('success', 'Welcome to RescueBite, ' . $user->name . '! Your account has been created.');
    }
}
