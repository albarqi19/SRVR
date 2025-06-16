<x-filament-panels::page>
    <form wire:submit.prevent="applyPlan">
        {{ $this->form }}
        
        <div class="flex justify-end mt-6">
            {{ $this->getFormActions() }}
        </div>
    </form>
</x-filament-panels::page>
