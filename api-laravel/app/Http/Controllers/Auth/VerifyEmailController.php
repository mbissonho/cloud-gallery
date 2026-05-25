<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark a user's email address as verified.
     *
     * The link is opened straight from the email, so there is no authenticated
     * session. Authenticity is guaranteed by the `signed` middleware (the URL
     * signature) plus the hash check below, which mirrors what Laravel's
     * EmailVerificationRequest does for the logged-in flow.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect(config('app.frontend_url').'/?verified=1');
    }
}
