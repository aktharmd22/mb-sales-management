<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Visit $visit): bool
    {
        return $visit->user_id === $user->id;
    }

    public function update(User $user, Visit $visit): bool
    {
        return $visit->user_id === $user->id;
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $visit->user_id === $user->id;
    }
}
