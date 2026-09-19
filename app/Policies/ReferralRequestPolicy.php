<?php

namespace App\Policies;

use App\Models\ReferralRequest;
use App\Models\User;

class ReferralRequestPolicy
{
    public function view(User $user, ReferralRequest $referralRequest): bool
    {
        return $user->isAdmin() || $user->id === $referralRequest->user_id;
    }

    public function update(User $user, ReferralRequest $referralRequest): bool
    {
        return $user->isAdmin();
    }
}
