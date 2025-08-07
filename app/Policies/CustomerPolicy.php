<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $item): bool
    {
        return $this->canAccess($user, $item, 'view');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $item): bool
    {
        return $this->canAccess($user, $item, 'update');
    }

    /**
     * Shared authorization logic.
     */
    protected function canAccess(User $user, Customer $item, string $action): bool
    {
        return true;
    }
}
