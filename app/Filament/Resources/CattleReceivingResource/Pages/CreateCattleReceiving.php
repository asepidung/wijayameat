<?php

namespace App\Filament\Resources\CattleReceivingResource\Pages;

use App\Filament\Resources\CattleReceivingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\CattlePurchaseOrder;
use App\Models\CattleReceiving;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // TAMBAHKAN INI

class CreateCattleReceiving extends CreateRecord
{
    protected static string $resource = CattleReceivingResource::class;

    protected function getFormActions(): array
    {
        return [$this->getCreateFormAction(), $this->getCancelFormAction()];
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
                for ($i = 0; $i < $poItem->qty_head; $i++) {
                    // PAKE UUID BIAR FILAMENT GAK BINGUNG
                    $generatedRows[(string) Str::uuid()] = [
                        'cattle_category_id' => $poItem->cattle_category_id,
                        'eartag' => null,
                        'initial_weight' => null,
                        'notes' => null,
                    ];
                }
            }

            $this->form->fill([
                'cattle_purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'po_number_display' => $po->po_number,
                'supplier_name_display' => $po->supplier->name,
                'receive_date' => now()->format('Y-m-d'),
                'receiving_items' => $generatedRows, // PAKE NAMA REPEATER BARU
            ]);
        }
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // Generate Nomor GRC
            $currentYear2Digit = date('y');
            $currentYear4Digit = date('Y');

            // KUNCI: Tambahkan withTrashed() biar data yang di tong sampah tetep dihitung
            $latest = CattleReceiving::withTrashed()
                ->whereYear('created_at', $currentYear4Digit)
                ->latest('id')
                ->first();

            $urut = 1;
            if ($latest && preg_match('/GRC#' . $currentYear2Digit . '(\d{3,})/', $latest->receiving_number, $matches)) {
                $urut = (int)$matches[1] + 1;
            }

            $grNumber = 'GRC#' . $currentYear2Digit . str_pad((string)$urut, 3, '0', STR_PAD_LEFT);

            // Buang array dummy biar header bisa disave
            $headerData = collect($data)->except(['receiving_items', 'po_number_display', 'supplier_name_display'])->toArray();
            $headerData['receiving_number'] = $grNumber;
            $headerData['created_by'] = (int) Auth::id();

            // 1. Save Header
            $receiving = CattleReceiving::create($headerData);

            // 2. Save Item Detail Manual
            if (isset($data['receiving_items'])) {
                foreach ($data['receiving_items'] as $item) {
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
