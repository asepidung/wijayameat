<?php

namespace App\Filament\Resources\CattlePurchaseOrderResource\Pages;

use App\Filament\Resources\CattlePurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\CattlePurchaseOrder;
use Illuminate\Support\Facades\Auth; // <-- 1. IMPORT FACADE AUTH DI SINI

class CreateCattlePurchaseOrder extends CreateRecord
{
    protected static string $resource = CattlePurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 2. GANTI auth()->id() JADI Auth::id()
        $data['created_by'] = (int) Auth::id();

        $currentYear2Digit = date('y');
        $currentYear4Digit = date('Y');

        $countThisYear = CattlePurchaseOrder::query()
            ->whereYear('created_at', $currentYear4Digit)
            ->count();

        $urut = $countThisYear + 1;

        $data['po_number'] = 'SWM-PC#' . $currentYear2Digit . str_pad((string)$urut, 3, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
