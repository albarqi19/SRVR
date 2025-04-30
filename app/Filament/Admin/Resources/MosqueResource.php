<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MosqueResource\Pages;
use App\Filament\Admin\Resources\MosqueResource\RelationManagers;
use App\Models\Mosque;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MosqueResource extends Resource
{
    protected static ?string $model = Mosque::class;

    // تعيين أيقونة مناسبة للمساجد
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    
    // تعيين العنوان بالعربية
    protected static ?string $label = 'مسجد';
    protected static ?string $pluralLabel = 'المساجد';
    
    // وضع المورد في مجموعة التنقل المناسبة
    protected static ?string $navigationGroup = 'التعليمية';
    
    // ترتيب المورد في القائمة
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم المسجد')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('neighborhood')
                    ->label('الحي')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location_lat')
                    ->label('خط العرض')
                    ->numeric(),
                Forms\Components\TextInput::make('location_long')
                    ->label('خط الطول')
                    ->numeric(),
                Forms\Components\TextInput::make('contact_number')
                    ->label('رقم الاتصال')
                    ->tel()
                    ->maxLength(255),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المسجد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('neighborhood')
                    ->label('الحي')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_number')
                    ->label('رقم الاتصال')
                    ->searchable(),
                // إضافة عمود لعرض عدد الحلقات في هذا المسجد
                Tables\Columns\TextColumn::make('circles_count')
                    ->label('عدد الحلقات')
                    ->counts('quranCircles')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('neighborhood')
                    ->label('تصفية حسب الحي')
                    ->options(fn(): array => Mosque::query()->pluck('neighborhood', 'neighborhood')->toArray())
                    ->searchable()
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuranCirclesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMosques::route('/'),
            'create' => Pages\CreateMosque::route('/create'),
            'edit' => Pages\EditMosque::route('/{record}/edit'),
        ];
    }
}
