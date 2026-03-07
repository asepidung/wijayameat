<?php

namespace App\Filament\Resources\CattleReceivingResource\Pages;

use App\Filament\Resources\CattleReceivingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCattleReceiving extends ViewRecord
{
    protected static string $resource = CattleReceivingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Edit di kanan atas (Standar Filament Page)
            Actions\EditAction::make()
                ->color('warning')
                ->icon('heroicon-o-pencil-square'),

            // Tombol Print
            Actions\Action::make('print')
                ->label('Print')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->url(fn($record) => route('print.grc', $record->id)) // Nanti kita sesuaikan routenya
                ->openUrlInNewTab(),

            Actions\Action::make('close')
                ->label('Back')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    // Kalau lu mau tombolnya ada di bawah (footer) persis sebelah Close:

}
