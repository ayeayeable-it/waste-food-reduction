<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'profile_updated',
            targetType: User::class,
            targetId: $user->id,
            userId: $user->id
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Your profile details have been updated.');
    }

    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $this->auditLogService->log(
            action: 'password_changed',
            targetType: User::class,
            targetId: $user->id,
            userId: $user->id
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Your password has been changed successfully.');
    }
}
