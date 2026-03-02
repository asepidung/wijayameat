<?php

namespace App\Filament\Resources\CattlePurchaseOrderResource\Pages;

use App\Filament\Resources\CattlePurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCattlePurchaseOrder extends EditRecord
{
    protected static string $resource = CattlePurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
