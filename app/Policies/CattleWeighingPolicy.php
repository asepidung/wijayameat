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
        if (!$user->can('update_cattle::weighing')) {
            return false;
        }

        // GEMBOK: Jangan kasih edit kalau sudah ditarik ke Karkas/Slaughter
        if (method_exists($cattleWeighing, 'carcass') && $cattleWeighing->carcass()->exists()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, CattleWeighing $cattleWeighing): bool
    {
        if (!$user->can('delete_cattle::weighing')) {
            return false;
        }

        // GEMBOK: Jangan kasih hapus kalau sudah ditarik ke Karkas/Slaughter
        if (method_exists($cattleWeighing, 'carcass') && $cattleWeighing->carcass()->exists()) {
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
        return $user->can('force_delete_cattle::weighing');
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
        return $user->can('replicate_cattle::weighing');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_cattle::weighing');
    }
}
