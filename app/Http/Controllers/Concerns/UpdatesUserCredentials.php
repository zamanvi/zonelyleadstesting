<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

trait UpdatesUserCredentials
{
    /**
     * Handle email change — clears verification and triggers re-verification.
     */
    protected function handleEmailChange($user, Request $request): void
    {
        if ($user->email !== $request->email) {
            $user->email_verified_at = null;
            $user->save();
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
            }
        }
    }

    /**
     * Handle password change if provided in request.
     */
    protected function handlePasswordChange($user, Request $request): void
    {
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password'         => ['required', 'confirmed', 'min:8'],
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }
    }
}
