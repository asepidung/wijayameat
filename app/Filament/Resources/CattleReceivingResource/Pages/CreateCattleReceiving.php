<?php

namespace App\Filament\Resources\CattleReceivingResource\Pages;

use App\Filament\Resources\CattleReceivingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\CattlePurchaseOrder;
use App\Models\CattleReceiving;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateCattleReceiving extends CreateRecord
{
    protected static string $resource = CattleReceivingResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    public function mount(): void
    {
        $poId = request()->query('po_id');
        if (!$poId) {
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount();

        $po = CattlePurchaseOrder::with(['items', 'supplier'])->find($poId);

        if ($po) {
            $generatedRows = [];
            foreach ($po->items as $poItem) {
                // Generate baris sebanyak qty_head di PO
                for ($i = 0; $i < $poItem->qty_head; $i++) {
                    $generatedRows[] = [
                        'cattle_category_id' => $poItem->cattle_category_id,
                        'eartag' => null,
                        'initial_weight' => null,
                        'notes' => null,
                    ];
                }
            }

            // ISI STATE FORM
            $this->form->fill([
                'cattle_purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'po_number_display' => $po->po_number,
                'supplier_name_display' => $po->supplier->name,
                'receive_date' => now(),
                'items' => $generatedRows, // Ini yang bikin baris muncul otomatis
            ]);
        }
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Logic GRC Number
            $currentYear2Digit = date('y');
            $latest = CattleReceiving::whereYear('created_at', date('Y'))->latest('id')->first();
            $urut = 1;
            if ($latest && preg_match('/GRC#' . $currentYear2Digit . '(\d{3,})/', $latest->receiving_number, $matches)) {
                $urut = (int)$matches[1] + 1;
            }
            $grNumber = 'GRC#' . $currentYear2Digit . str_pad($urut, 3, '0', STR_PAD_LEFT);

            // 2. Simpan Header
            // Pakai array_diff_key atau unset agar 'items' tidak masuk ke create header
            $headerData = collect($data)->except(['items'])->toArray();
            $headerData['receiving_number'] = $grNumber;
            $headerData['created_by'] = Auth::id();

            $receiving = CattleReceiving::create($headerData);

            // 3. Simpan Items secara Manual karena kita override handleRecordCreation
            if (isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (!empty($item['eartag'])) {
                        $receiving->items()->create([
                            'cattle_category_id' => $item['cattle_category_id'],
                            'eartag' => strtoupper(trim($item['eartag'])),
                            'initial_weight' => $item['initial_weight'],
                            'notes' => $item['notes'],
                        ]);
                    }
                }
            }

            return $receiving;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
