<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CattleWeighing;
use Illuminate\Auth\Access\HandlesAuthorization;

class CattleWeighingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cattle::weighing');
    }

    public function view(User $user, CattleWeighing $cattleWeighing): bool
    {
        return $user->can('view_cattle::weighing');
    }

    public function create(User $user): bool
    {
        return $user->can('create_cattle::weighing');
    }

    public function update(User $user, CattleWeighing $cattleWeighing): bool
    {
        // 1. Cek Permission dari Filament Shield
        if (!$user->can('update_cattle::weighing')) {
            return false;
        }

        // 2. GEMBOK GLOBAL: Kalau sudah ada Karkas yang nyantol, GAK BOLEH EDIT!
        if ($cattleWeighing->carcasses()->exists()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, CattleWeighing $cattleWeighing): bool
    {
        // 1. Cek Permission
        if (!$user->can('delete_cattle::weighing')) {
            return false;
        }

        // 2. GEMBOK GLOBAL: Gak boleh dihapus kalau sapinya sudah mulai dipotong
        if ($cattleWeighing->carcasses()->exists()) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cattle::weighing');
    }

    public function forceDelete(User $user, CattleWeighing $cattleWeighing): bool
    {
        // 1. Cek Permission
        if (!$user->can('force_delete_cattle::weighing')) {
            return false;
        }

        // 2. GEMBOK PERMANEN
        if ($cattleWeighing->carcasses()->exists()) {
            return false;
        }

        return true;
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_cattle::weighing');
    }

    public function restore(User $user, CattleWeighing $cattleWeighing): bool
    {
        return $user->can('restore_cattle::weighing');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_cattle::weighing');
    }

    public function replicate(User $user, CattleWeighing $cattleWeighing): bool
    {
        // Biasakan direplicate juga dikunci aja kalau udah dipotong, tapi opsional sih
        return $user->can('replicate_cattle::weighing');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_cattle::weighing');
    }
}
