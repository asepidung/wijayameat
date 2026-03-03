<?php

namespace App\Filament\Resources\LogisticReceivingResource\Pages;

use App\Filament\Resources\LogisticReceivingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\LogisticReceiving;
use App\Models\LogisticPurchaseOrder;
use App\Models\LogisticReceivingItem;
use Illuminate\Support\Facades\Auth;

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

        /** @var LogisticPurchaseOrder|null $po */
        $po = LogisticPurchaseOrder::with(['items.item', 'supplier'])->find($poId);

        if ($po) {
            $items = [];

            foreach ($po->items as $poItem) {
                $received = LogisticReceivingItem::whereHas('receiving', function ($q) use ($poId) {
                    $q->where('logistic_purchase_order_id', $poId);
                })
                    ->where('logistic_item_id', $poItem->logistic_item_id)
                    ->sum('qty_received');

                $sisa = $poItem->qty - $received;

                $items[] = [
                    'logistic_item_id'   => $poItem->logistic_item_id,
                    'item_name_display'  => $poItem->item->name ?? 'Unknown Item',
                    'qty_ordered'        => $sisa > 0 ? $sisa : 0,
                    'qty_received'       => null,
                ];
            }

            $this->form->fill([
                'logistic_purchase_order_id' => $po->id,
                'po_number_display'          => $po->po_number,
                'supplier_id'                => $po->supplier_id,
                'supplier_name_display'      => $po->supplier->name ?? '',
                'receive_date'               => now()->format('Y-m-d'),
                'items'                      => $items,
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        $currentYear2Digit = date('y');
        $currentYear4Digit = date('Y');
        $countThisYear = LogisticReceiving::whereYear('created_at', $currentYear4Digit)->count();
        $urut = $countThisYear + 1;

        $data['receiving_number'] = 'GRL#' . $currentYear2Digit . str_pad($urut, 3, '0', STR_PAD_LEFT);

        if (isset($data['items'])) {
            $this->tempItems = $data['items'];
            unset($data['items']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var LogisticReceiving $receiving */
        $receiving = $this->record;

        $poId = $receiving->logistic_purchase_order_id;

        /** @var LogisticPurchaseOrder|null $po */
        $po = LogisticPurchaseOrder::with(['items', 'supplier'])->find($poId);

        if (!$po) {
            return;
        }

        foreach ($this->tempItems as $item) {
            $poItem = $po->items->where('logistic_item_id', $item['logistic_item_id'])->first();
            $price = $poItem ? $poItem->price : 0;
            $qty   = (int) $item['qty_received'];

            if ($qty > 0) {

                LogisticReceivingItem::create([
                    'logistic_receiving_id' => $receiving->id,
                    'logistic_item_id'      => $item['logistic_item_id'],
                    'qty_received'          => $qty,
                    'price'                 => $price,
                    'subtotal'              => $price * $qty,
                ]);

                $stockRecord = \App\Models\LogisticStock::firstOrCreate(
                    ['logistic_item_id' => $item['logistic_item_id']],
                    ['qty' => 0]
                );

                $currentStock = $stockRecord->qty;
                $newStock     = $currentStock + $qty;

                $stockRecord->update([
                    'qty' => $newStock
                ]);

                \App\Models\LogisticStockMovement::create([
                    'logistic_item_id'   => $item['logistic_item_id'],
                    'transaction_type'   => 'GR',
                    'reference_document' => $receiving->receiving_number,
                    'qty_in'             => $qty,
                    'qty_out'            => 0,
                    'balance'            => $newStock,
                    'note'               => "Penerimaan dari '" . ($po->supplier->name ?? 'Unknown') . "' " . $po->po_number,
                    'created_by'         => Auth::id(),
                ]);
            }
        }
    }

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
