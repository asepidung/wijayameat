<?php

namespace App\Filament\Resources\LogisticReceivingResource\Pages;

use App\Filament\Resources\LogisticReceivingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\LogisticReceiving;
use App\Models\LogisticPurchaseOrder;
use App\Models\LogisticReceivingItem;

class CreateLogisticReceiving extends CreateRecord
{
    protected static string $resource = LogisticReceivingResource::class;

    // Properti sementara buat nyimpen data item dari form UI
    public array $tempItems = [];

    public function mount(): void
    {
        $poId = request()->query('po_id');

        if (!$poId) {
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount();

        $po = LogisticPurchaseOrder::with('items.item')->find($poId);

        if ($po) {
            $items = [];

            foreach ($po->items as $poItem) {
                $received = LogisticReceivingItem::whereHas('receiving', function ($q) use ($poId) {
                    $q->where('logistic_purchase_order_id', $poId);
                })->where('logistic_item_id', $poItem->logistic_item_id)->sum('qty_received');

                $sisa = $poItem->qty - $received;

                // Pakai array biasa tanpa UUID karena kita udah hapus ->relationship()
                $items[] = [
                    'logistic_item_id' => $poItem->logistic_item_id,
                    'item_name_display' => $poItem->item->name ?? 'Unknown Item',
                    'qty_ordered' => $sisa > 0 ? $sisa : 0,
                    'qty_received' => null,
                ];
            }

            $this->form->fill([
                'logistic_purchase_order_id' => $po->id,
                'po_number_display' => $po->po_number,
                'supplier_id' => $po->supplier_id,
                'supplier_name_display' => $po->supplier->name ?? '',
                'receive_date' => now()->format('Y-m-d'),
                'items' => $items,
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['created_by'] = auth()->id();

        $currentYear2Digit = date('y');
        $currentYear4Digit = date('Y');
        $countThisYear = LogisticReceiving::whereYear('created_at', $currentYear4Digit)->count();
        $urut = $countThisYear + 1;

        $data['receiving_number'] = 'GRL#' . $currentYear2Digit . str_pad($urut, 3, '0', STR_PAD_LEFT);

        // AMBIL DATA ITEMS LALU HAPUS DARI ARRAY UTAMA
        // Ini mencegah Filament error nyari kolom 'items' di tabel database
        if (isset($data['items'])) {
            $this->tempItems = $data['items'];
            unset($data['items']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $receiving = $this->record;
        $poId = $receiving->logistic_purchase_order_id;

        // Tarik data PO buat ngintip harga beli aslinya
        $po = LogisticPurchaseOrder::with(['items', 'supplier'])->find($poId);

        foreach ($this->tempItems as $item) {
            $poItem = $po->items->where('logistic_item_id', $item['logistic_item_id'])->first();
            $price = $poItem ? $poItem->price : 0;
            $qty = (int) $item['qty_received'];

            if ($qty > 0) {
                // 1. Simpan ke detail GR (Dokumen Fisik)
                LogisticReceivingItem::create([
                    'logistic_receiving_id' => $receiving->id,
                    'logistic_item_id' => $item['logistic_item_id'],
                    'qty_received' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                ]);

                // 2. Update Stok di tabel logistic_stocks
                // firstOrCreate: Kalau barangnya belum pernah ada di gudang, bikin baris baru dengan qty 0
                $stockRecord = \App\Models\LogisticStock::firstOrCreate(
                    ['logistic_item_id' => $item['logistic_item_id']],
                    ['qty' => 0]
                );

                $currentStock = $stockRecord->qty;
                $newStock = $currentStock + $qty;

                $stockRecord->update([
                    'qty' => $newStock
                ]);

                // 3. Catat Mutasi ke Ledger (History)
                \App\Models\LogisticStockMovement::create([
                    'logistic_item_id' => $item['logistic_item_id'],
                    'transaction_type' => 'GR',
                    'reference_document' => $receiving->receiving_number,
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'balance' => $newStock,
                    'note' => "Penerimaan dari '" . ($po->supplier->name ?? 'Unknown') . "' " . $po->po_number,
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }

    // NGILANGIN TOMBOL "CREATE & CREATE ANOTHER"
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
