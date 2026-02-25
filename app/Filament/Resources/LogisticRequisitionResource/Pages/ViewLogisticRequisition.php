<?php

namespace App\Filament\Resources\LogisticRequisitionResource\Pages;

use App\Filament\Resources\LogisticRequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewLogisticRequisition extends ViewRecord
{
    protected static string $resource = LogisticRequisitionResource::class;

    /**
     * Mendefinisikan tombol aksi yang muncul pada bagian tajuk (header) halaman.
     */
    protected function getHeaderActions(): array
    {
        return [
            /* Tombol untuk kembali ke halaman indeks tanpa menyimpan perubahan */
            Actions\Action::make('cancel')
                ->label('Cancel / Back')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            /* Tombol persetujuan dokumen oleh Purchasing */
            Actions\Action::make('accept')
                ->label('Accept')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'Requested' && auth()->user()->hasAnyRole(['super_admin', 'purchasing']))
                ->action(function () {
                    $this->record->update(['status' => 'Waiting']);
                    Notification::make()->title('Request accepted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            /* Tombol penolakan dokumen oleh Purchasing */
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'Requested' && auth()->user()->hasAnyRole(['super_admin', 'purchasing']))
                ->action(function () {
                    $this->record->update(['status' => 'Rejected']);
                    Notification::make()->title('Request rejected')->danger()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            /* Tombol untuk mengedit dokumen */
            Actions\EditAction::make()
                ->visible(fn() => $this->record->status === 'Requested'),
        ];
    }
}
