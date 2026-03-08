<?php

namespace App\Filament\Resources\CattleReceivingResource\Pages;

use App\Filament\Resources\CattleReceivingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EditCattleReceiving extends EditRecord
{
    protected static string $resource = CattleReceivingResource::class;

    // 1. SUNTIK DATA BIAR BISA DIEDIT
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load(['items.category', 'purchaseOrder', 'supplier']);

        $data['po_number_display'] = $record->purchaseOrder->po_number ?? '-';
        $data['supplier_name_display'] = $record->supplier->name ?? '-';

        $receivingItems = [];
        foreach ($record->items as $item) {
            $receivingItems[(string) Str::uuid()] = [
                'cattle_category_id' => $item->cattle_category_id,
                'eartag' => $item->eartag,
                'initial_weight' => $item->initial_weight,
                'notes' => $item->notes,
            ];
        }
        $data['receiving_items'] = $receivingItems;

        return $data;
    }

    // 2. SIMPAN MANUAL PERUBAHANNYA KE DATABASE
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        DB::transaction(function () use ($record, $data) {
            // Update tabel cattle_receivings (Header)
            $record->update([
                'receive_date' => $data['receive_date'],
                'doc_no' => $data['doc_no'] ?? null,
                'sv_ok' => $data['sv_ok'] ?? false,
                'skkh_ok' => $data['skkh_ok'] ?? false,
                'note' => $data['note'] ?? null,
            ]);

            // Bersihkan baris sapi lama, timpa dengan data sapi yang baru di-edit
            $record->items()->forceDelete();

            if (isset($data['receiving_items'])) {
                foreach ($data['receiving_items'] as $item) {
                    if (!empty($item['eartag'])) {
                        $record->items()->create([
                            'cattle_category_id' => $item['cattle_category_id'],
                            'eartag' => strtoupper(trim($item['eartag'])),
                            'initial_weight' => $item['initial_weight'],
                            'notes' => $item['notes'],
                        ]);
                    }
                }
            }
        });

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Redirect ke halaman index setelah selesai edit
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
