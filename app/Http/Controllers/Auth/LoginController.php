<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = [
            'email' => strtolower($request->validated('email')),
            'password' => $request->validated('password'),
        ];

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account is currently deactivated. Please contact an administrator.');
        }

        $request->session()->regenerate();

        $this->auditLogService->log(
            action: 'user_logged_in',
            targetType: User::class,
            targetId: $user->id,
            details: ['role' => $user->role],
            userId: $user->id
        );

        return redirect()->intended($this->redirectPathForRole($user))
            ->with('success', 'Signed in successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if ($userId) {
            $this->auditLogService->log(
                action: 'user_logged_out',
                targetType: User::class,
                targetId: $userId,
                userId: $userId
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been signed out safely.');
    }

    private function redirectPathForRole(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'seller' => route('seller.dashboard'),
            default => route('buyer.dashboard'),
        };
    }
}
