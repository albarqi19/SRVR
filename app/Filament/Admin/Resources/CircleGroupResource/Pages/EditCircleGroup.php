<?php

namespace App\Filament\Admin\Resources\CircleGroupResource\Pages;

use App\Filament\Admin\Resources\CircleGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCircleGroup extends EditRecord
{
    protected static string $resource = CircleGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->requiresConfirmation()
                ->modalHeading('حذف الحلقة الفرعية')
                ->modalDescription('هل أنت متأكد من حذف هذه الحلقة الفرعية؟ سيتم نقل جميع الطلاب إلى الحلقة الرئيسية.')
                ->before(function ($record) {
                    // نقل الطلاب إلى الحلقة الرئيسية قبل الحذف
                    \App\Models\Student::where('circle_group_id', $record->id)
                        ->update(['circle_group_id' => null]);
                }),
        ];
    }
    
    public function getTitle(): string
    {
        return 'تعديل الحلقة الفرعية: ' . $this->record->name;
    }
    
    protected function getHeaderTitle(): string
    {
        return 'تعديل بيانات الحلقة الفرعية';
    }
    
    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('حفظ التغييرات');
    }
    
    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('إلغاء');
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
