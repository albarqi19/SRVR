<?php

namespace App\Filament\Admin\Resources\MarketingTaskCompletionResource\Pages;

use App\Filament\Admin\Resources\MarketingTaskCompletionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingTaskCompletion extends EditRecord
{
    protected static string $resource = MarketingTaskCompletionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
