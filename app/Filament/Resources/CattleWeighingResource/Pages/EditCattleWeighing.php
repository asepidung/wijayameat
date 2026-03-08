<?php

namespace App\Filament\Resources\CattleWeighingResource\Pages;

use App\Filament\Resources\CattleWeighingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EditCattleWeighing extends EditRecord
{
    protected static string $resource = CattleWeighingResource::class;

    // SUNTIK DATA SEPERTI DI VIEW
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load(['items.receivingItem', 'receiving.purchaseOrder', 'receiving.supplier']);

        $data['grc_number_display'] = $record->receiving->receiving_number ?? '-';
        $data['po_number_display'] = $record->receiving->purchaseOrder->po_number ?? '-';
        $data['supplier_name_display'] = $record->receiving->supplier->name ?? '-';

        $weighingItems = [];
        foreach ($record->items as $item) {
            $weighingItems[(string) Str::uuid()] = [
                'cattle_receiving_item_id' => $item->cattle_receiving_item_id,
                'eartag_display' => $item->receivingItem->eartag ?? '-',
                'initial_weight_display' => $item->receivingItem->initial_weight ?? 0,
                'weight' => $item->weight,
                'notes' => $item->notes,
            ];
        }
        $data['weighing_items'] = $weighingItems;

        return $data;
    }

    // HANDLE SAVE EDIT SECARA MANUAL
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        DB::transaction(function () use ($record, $data) {
            // Update Header
            $record->update([
                'weigh_date' => $data['weigh_date'],
                'note' => $data['note'] ?? null,
            ]);

            // Hapus detail lama, insert yang baru (biar bersih & update sesuai array)
            $record->items()->forceDelete();

            if (isset($data['weighing_items'])) {
                foreach ($data['weighing_items'] as $item) {
                    $record->items()->create([
                        'cattle_receiving_item_id' => $item['cattle_receiving_item_id'],
                        'weight' => $item['weight'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }
        });

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
