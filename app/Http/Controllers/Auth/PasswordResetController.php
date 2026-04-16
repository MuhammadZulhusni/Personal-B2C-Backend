<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link to email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.'
        ]);

        // Send the reset link (this will trigger the notification)
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link has been sent to your email address.'
            ], 200);
        }

        return response()->json([
            'message' => 'Unable to send reset link. Please try again later.'
        ], 400);
    }

    /**
     * Reset password with token (POST request from frontend)
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Your password has been reset successfully.'
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid or expired reset token.'
        ], 400);
    }

    /**
     * Verify if reset token is valid (GET request)
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        if (!Hash::check($request->token, $reset->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        $createdAt = Carbon::parse($reset->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'Reset token has expired'
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token is valid'
        ]);
    }
}