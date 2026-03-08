<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CattlePurchaseOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class CattlePurchaseOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cattle::purchase::order');
    }

    public function view(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        return $user->can('view_cattle::purchase::order');
    }

    public function create(User $user): bool
    {
        return $user->can('create_cattle::purchase::order');
    }

    public function update(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        if (!$user->can('update_cattle::purchase::order')) {
            return false;
        }

        // KUNCI: Tidak boleh edit kalau sudah ada penerimaan
        if ($cattlePurchaseOrder->receivings()->exists()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        if (!$user->can('delete_cattle::purchase::order')) {
            return false;
        }

        // KUNCI FATAL: Tidak boleh hapus kalau sudah ada penerimaan
        if ($cattlePurchaseOrder->receivings()->exists()) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cattle::purchase::order');
    }

    public function forceDelete(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        if (!$user->can('force_delete_cattle::purchase::order')) {
            return false;
        }

        // Kunci juga untuk hapus permanen
        if ($cattlePurchaseOrder->receivings()->exists()) {
            return false;
        }

        return true;
    }

    public function restore(User $user, CattlePurchaseOrder $cattlePurchaseOrder): bool
    {
        return $user->can('restore_cattle::purchase::order');
    }
}
