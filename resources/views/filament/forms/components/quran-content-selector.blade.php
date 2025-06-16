<div class="filament-forms-quran-content-selector-component">
    <x-filament::card>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-book-open class="w-5 h-5 text-primary-600" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    محدد المحتوى القرآني
                </span>
            </div>
        </x-slot>

        <div class="space-y-4">
            {{ $getChildComponentContainer() }}
        </div>
    </x-filament::card>
</div>
