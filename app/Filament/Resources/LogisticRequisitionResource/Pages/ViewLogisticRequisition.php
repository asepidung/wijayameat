<?php

namespace App\Filament\Resources\LogisticRequisitionResource\Pages;

use App\Filament\Resources\LogisticRequisitionResource;
use Filament\Actions;
use Filament\Forms;
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
            /* Tombol persetujuan dokumen oleh Purchasing */
            Actions\Action::make('accept')
                ->label('Accept')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();
                    // Gembok dibuka kalau user adalah super_admin ATAU punya centangan review
                    return $this->record->status === 'Requested' && ($user->hasRole('super_admin') || $user->can('review_logistic::requisition'));
                })
                ->action(function () {
                    $this->record->update(['status' => 'Waiting']);
                    Notification::make()->title('Request accepted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            /* Tombol penolakan dokumen yang memunculkan form input alasan */
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('reject_note')
                        ->label('Rejection Reason')
                        ->required()
                        ->placeholder('Contoh: Harga terlalu mahal atau supplier salah'),
                ])
                ->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();
                    // Gembok dibuka kalau user adalah super_admin ATAU punya centangan review
                    return $this->record->status === 'Requested' && ($user->hasRole('super_admin') || $user->can('review_logistic::requisition'));
                })
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'Rejected',
                        'reject_note' => $data['reject_note'],
                    ]);
                    Notification::make()->title('Request rejected')->danger()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            /* Tombol untuk kembali ke halaman daftar request */
            Actions\Action::make('cancel')
                ->label('Back')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
