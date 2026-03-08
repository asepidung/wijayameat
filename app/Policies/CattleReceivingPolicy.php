<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CattleReceiving;
use Illuminate\Auth\Access\HandlesAuthorization;

class CattleReceivingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cattle::receiving');
    }

    public function view(User $user, CattleReceiving $cattleReceiving): bool
    {
        return $user->can('view_cattle::receiving');
    }

    public function create(User $user): bool
    {
        return $user->can('create_cattle::receiving');
    }

    public function update(User $user, CattleReceiving $cattleReceiving): bool
    {
        if (!$user->can('update_cattle::receiving')) {
            return false;
        }

        // KUNCI: Jangan kasih edit kalau sudah ada data penimbangan (Weighing)
        if ($cattleReceiving->weighing()->exists()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, CattleReceiving $cattleReceiving): bool
    {
        if (!$user->can('delete_cattle::receiving')) {
            return false;
        }

        // KUNCI: Jangan kasih hapus kalau sudah ada data penimbangan (Weighing)
        if ($cattleReceiving->weighing()->exists()) {
            return false;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cattle::receiving');
    }

    public function forceDelete(User $user, CattleReceiving $cattleReceiving): bool
    {
        return $user->can('force_delete_cattle::receiving') && !$cattleReceiving->weighing()->exists();
    }

    public function restore(User $user, CattleReceiving $cattleReceiving): bool
    {
        return $user->can('restore_cattle::receiving');
    }

    public function replicate(User $user, CattleReceiving $cattleReceiving): bool
    {
        return $user->can('replicate_cattle::receiving');
    }
}
