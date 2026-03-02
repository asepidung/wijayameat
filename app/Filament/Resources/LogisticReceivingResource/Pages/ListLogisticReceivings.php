<?php

namespace App\Filament\Resources\LogisticReceivingResource\Pages;

use App\Filament\Resources\LogisticReceivingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogisticReceivings extends ListRecords
{
    protected static string $resource = LogisticReceivingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('draft')
                ->label('Draft')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                /* Buka komentar dan arahkan ke rute Draft */
                ->url(fn() => LogisticReceivingResource::getUrl('draft')),
        ];
    }
}
