<?php

namespace App\Filament\Admin\Resources\CircleGroupResource\Pages;

use App\Filament\Admin\Resources\CircleGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCircleGroup extends CreateRecord
{
    protected static string $resource = CircleGroupResource::class;
    
    public function getTitle(): string
    {
        return 'إضافة حلقة فرعية جديدة';
    }
    
    protected function getHeaderTitle(): string
    {
        return 'إنشاء حلقة فرعية';
    }
    
    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('إنشاء الحلقة الفرعية');
    }
    
    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
