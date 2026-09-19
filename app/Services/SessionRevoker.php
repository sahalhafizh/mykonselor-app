<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionRevoker
{
    public function revoke(User $user, ?string $exceptSessionId = null): void
    {
        $user->forceFill(['remember_token' => Str::random(60)])->save();

        // Database sessions are removed immediately. AuthenticateSession also
        // rejects an old password hash when other session drivers are used.
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->when($exceptSessionId, fn ($query) => $query->where('id', '!=', $exceptSessionId))
                ->delete();
        }
    }
}
