<?php

namespace App\Policies;

use App\Models\Penggalang_Dana;
use App\Models\User;

class PenggalangDanaPolicy
{
    public function update(User $user, Penggalang_Dana $penggalang): bool
    {
        return (int) $user->id === (int) $penggalang->user_id
            || (method_exists($user, 'isAdmin') && $user->isAdmin());
    }
}
