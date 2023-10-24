<?php

namespace App\Policies;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;

class UserPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $context, User $user): bool
    {
        if ($context->can('view_all_user_info')) {
            return true;
        }

        if ($context->can('view_company_user_info') && $context->company_id == $user->company_id) {
            return true;
        }

        if ($context->can('view_my_user_info') && $context->id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $context, int $companyId): bool
    {
        if ($context->can('create_all_user_info')) {
            return true;
        }

        if ($context->can('create_company_user_info') && $context->company_id == $companyId) {
            return true;
        }

        return false;
    }

    public function update(User $context, User $user): bool
    {
        if ($context->can('edit_all_user_info')) {
            return true;
        }

        if ($context->can('edit_company_user_info') && $context->company_id == $user->company_id) {
            return true;
        }

        if ($context->can('edit_my_user_info') && $context->id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $context, User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($context->can('delete_all_user_info')) {
            return true;
        }

        if ($context->can('delete_company_user_info') && $context->company_id == $user->company_id) {
            return true;
        }

        return false;
    }

    public function multipleDelete(User $context, array $userIds): bool
    {
        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);
        $check = true;
        $userRepository->getUsersByIds($userIds)->each(function (User $user) use ($context, &$check) {
            $check = $this->delete($context, $user);

            return $check;
        });

        return $check;
    }
}
