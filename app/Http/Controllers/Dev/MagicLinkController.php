<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\DevMagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLinkController extends Controller
{
    public function generate(Request $request, string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            abort(404, "User with email '{$email}' not found.");
        }

        $expiresInMinutes = 60;
        $magicLink = DevMagicLink::generateForUser($user, $expiresInMinutes);

        return response()->json([
            'success' => true,
            'message' => 'Magic link generated successfully',
            'data' => [
                'user_email' => $user->email,
                'expires_in_minutes' => $expiresInMinutes,
                'magic_link_url' => $magicLink->getUrl(),
                'expires_at' => $magicLink->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function authenticate(Request $request, string $token)
    {
        $magicLink = DevMagicLink::findByToken($token);

        if (!$magicLink) {
            abort(404, 'Magic link not found. It may have been used or deleted.');
        }

        if ($magicLink->isExpired()) {
            abort(403, 'This magic link has expired. Please generate a new one.');
        }

        Auth::login($magicLink->user);

        $magicLink->delete();

        return redirect()->route('dashboard')->with('success', 'You have been authenticated successfully via magic link!');
    }
}
