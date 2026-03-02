<?php

namespace App\Filament\Resources\CattlePurchaseOrderResource\Pages;

use App\Filament\Resources\CattlePurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\CattlePurchaseOrder;

class CreateCattlePurchaseOrder extends CreateRecord
{
    protected static string $resource = CattlePurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        $currentYear2Digit = date('y');
        $currentYear4Digit = date('Y');

        $countThisYear = CattlePurchaseOrder::whereYear('created_at', $currentYear4Digit)->count();
        $urut = $countThisYear + 1;

        $data['po_number'] = 'SWM/PC#' . $currentYear2Digit . str_pad($urut, 3, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
