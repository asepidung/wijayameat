<?php

namespace App\Filament\Resources\BeefRequisitionResource\Pages;

use App\Filament\Resources\BeefRequisitionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;

class ReviewBeefRequisition extends ViewRecord
{
    protected static string $resource = BeefRequisitionResource::class;

    /* Menambahkan tombol aksi pada halaman review */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Setujui ke Finance')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'Pending Finance', 'reject_note' => null]);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            Action::make('return')
                ->label('Kembalikan ke Requester')
                ->color('danger')
                ->form([
                    Textarea::make('reject_note')->label('Catatan Revisi')->required(),
                ])
                ->action(function (array $data) {
                    // SEBELUMNYA: 'Returned to Purchasing' (SALAH)
                    // SEKARANG: 'Rejected' (BENAR, biar Manyin bisa resubmit)
                    $this->record->update(['status' => 'Rejected', 'reject_note' => $data['reject_note']]);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}
