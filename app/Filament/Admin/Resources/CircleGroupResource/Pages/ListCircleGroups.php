<?php

namespace App\Filament\Admin\Resources\CircleGroupResource\Pages;

use App\Filament\Admin\Resources\CircleGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCircleGroups extends ListRecords
{
    protected static string $resource = CircleGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة حلقة فرعية جديدة'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'الحلقات الفرعية';
    }
    
    protected function getHeaderTitle(): string
    {
        return 'إدارة الحلقات الفرعية';
    }
}
