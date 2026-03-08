<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CattlePurchaseOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class CattlePurchaseOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cattle::purchase::order');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        return $user->can('view_cattle::purchase::order');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cattle::purchase::order');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        if (!$user->can('update_cattle::purchase::order')) {
            return false;
        }

        // Kunci: Gak bisa diedit kalau udah di-receive
        if ($cattlePurchaseOrder->receivings()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model (Soft Delete).
     */
    public function delete(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        if (!$user->can('delete_cattle::purchase::order')) {
            return false;
        }

        // Kunci: Gak bisa dihapus kalau udah di-receive
        if ($cattlePurchaseOrder->receivings()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cattle::purchase::order');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        if (!$user->can('force_delete_cattle::purchase::order')) {
            return false;
        }

        // Ekstra Proteksi: Gak bisa di-force delete juga kalau udah di-receive
        if ($cattlePurchaseOrder->receivings()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_cattle::purchase::order');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        return $user->can('restore_cattle::purchase::order');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_cattle::purchase::order');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        return $user->can('replicate_cattle::purchase::order');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_cattle::purchase::order');
    }
}
