<?php

namespace App\Filament\Resources\CattleWeighingResource\Pages;

use App\Filament\Resources\CattleWeighingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\CattleReceiving;
use App\Models\CattleWeighing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCattleWeighing extends CreateRecord
{
    protected static string $resource = CattleWeighingResource::class;

    protected function getFormActions(): array
    {
        return [$this->getCreateFormAction(), $this->getCancelFormAction()];
    }

    public function mount(): void
    {
        $grcId = request()->query('grc_id');
        if (!$grcId) {
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount();

        // Ambil data GRC beserta item sapi-sapinya
        $grc = CattleReceiving::with(['items', 'purchaseOrder', 'supplier'])->find($grcId);

        if ($grc) {
            $generatedRows = [];
            foreach ($grc->items as $item) {
                // Pake UUID biar Filament bisa render barisnya satu-satu dengan aman
                $generatedRows[(string) Str::uuid()] = [
                    'cattle_receiving_item_id' => $item->id,
                    'eartag_display' => $item->eartag,
                    'initial_weight_display' => $item->initial_weight,
                    'weight' => null, // Ini yang bakal diinput Weigher
                    'notes' => null,
                ];
            }

            $this->form->fill([
                'cattle_receiving_id' => $grc->id,
                'grc_number_display' => $grc->receiving_number,
                'po_number_display' => $grc->purchaseOrder->po_number,
                'supplier_name_display' => $grc->supplier->name,
                'weigh_date' => now()->format('Y-m-d'),
                'weighing_items' => $generatedRows, // <--- UBAH JADI NAMA REPEATER BARU
            ]);
        }
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            // Generate WGH Number otomatis
            $currentYear2Digit = date('y');
            $latest = CattleWeighing::whereYear('created_at', date('Y'))->latest('id')->first();
            $urut = 1;
            if ($latest && preg_match('/WGH-' . $currentYear2Digit . '(\d{4})/', $latest->weigh_no, $matches)) {
                $urut = (int)$matches[1] + 1;
            }
            $wghNumber = 'WGH-' . $currentYear2Digit . str_pad($urut, 4, '0', STR_PAD_LEFT);

            // Buang array 'weighing_items' dan 'display' dari data Header
            $headerData = collect($data)->except(['weighing_items', 'grc_number_display', 'po_number_display', 'supplier_name_display'])->toArray();
            $headerData['weigh_no'] = $wghNumber;
            $headerData['created_by'] = Auth::id();

            // 1. Simpan Header Timbangan
            $weighing = CattleWeighing::create($headerData);

            // 2. Simpan Detail Timbangan ke tabel cattle_weighing_items
            if (isset($data['weighing_items'])) {
                foreach ($data['weighing_items'] as $item) {
                    $weighing->items()->create([
                        'cattle_receiving_item_id' => $item['cattle_receiving_item_id'],
                        'weight' => $item['weight'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return $weighing;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
