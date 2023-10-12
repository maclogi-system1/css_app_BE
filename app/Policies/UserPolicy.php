<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $context, User $user): bool
    {
        if ($context->can('edit_all_user_info')) {
            return true;
        }

        if ($context->can('edit_my_user_info') && $context->id == $user->id) {
            return true;
        }

        return false;
    }
}
