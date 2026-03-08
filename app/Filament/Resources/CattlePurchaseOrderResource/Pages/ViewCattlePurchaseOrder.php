<?php

namespace App\Filament\Resources\CattlePurchaseOrderResource\Pages;

use App\Filament\Resources\CattlePurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCattlePurchaseOrder extends ViewRecord
{
    protected static string $resource = CattlePurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL BACK
            Actions\Action::make('back')
                ->label('Back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl('index')),

            // TOMBOL PRINT
            Actions\Action::make('print')
                ->label('Print')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->url(fn($record) => route('print.cattle-po', ['id' => $record->id]))
                ->openUrlInNewTab(),

            // TOMBOL EDIT
            Actions\EditAction::make()
                ->label('Edit')
                ->color('warning')
                ->icon('heroicon-o-pencil-square'),

            // TOMBOL DELETE (Otomatis pakai Soft Delete karena di Model ada trait SoftDeletes)
            Actions\DeleteAction::make()
                ->label('Delete')
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }
}
