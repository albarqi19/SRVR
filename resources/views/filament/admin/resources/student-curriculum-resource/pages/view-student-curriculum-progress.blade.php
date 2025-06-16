<x-filament-panels::page>
    {{ $this->infolist }}    <x-filament::section>
        <div class="mb-4">
            <h3 class="text-xl font-semibold">تقدم الطالب في خطط المنهج</h3>
        </div>
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
