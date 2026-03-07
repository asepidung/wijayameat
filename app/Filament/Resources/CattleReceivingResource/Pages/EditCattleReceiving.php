<?php

namespace App\Filament\Resources\CattleReceivingResource\Pages;

use App\Filament\Resources\CattleReceivingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCattleReceiving extends EditRecord
{
    protected static string $resource = CattleReceivingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
