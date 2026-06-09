<?php

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;

class FollowUpPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        return $followUp->user_id === $user->id;
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        return $followUp->user_id === $user->id;
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $followUp->user_id === $user->id;
    }
}
