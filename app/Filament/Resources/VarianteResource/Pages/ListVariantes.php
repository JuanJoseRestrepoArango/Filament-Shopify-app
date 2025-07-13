<?php

namespace App\Filament\Resources\VarianteResource\Pages;

use App\Filament\Resources\VarianteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVariantes extends ListRecords
{
    protected static string $resource = VarianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
