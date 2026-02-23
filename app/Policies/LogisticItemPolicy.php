<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LogisticItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogisticItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_logistic::item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LogisticItem $logisticItem): bool
    {
        return $user->can('view_logistic::item');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_logistic::item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LogisticItem $logisticItem): bool
    {
        return $user->can('update_logistic::item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LogisticItem $logisticItem): bool
    {
        return $user->can('delete_logistic::item');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_logistic::item');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LogisticItem $logisticItem): bool
    {
        return $user->can('force_delete_logistic::item');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_logistic::item');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LogisticItem $logisticItem): bool
    {
        return $user->can('restore_logistic::item');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_logistic::item');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LogisticItem $logisticItem): bool
    {
        return $user->can('replicate_logistic::item');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_logistic::item');
    }
}
