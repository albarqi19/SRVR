<x-filament-panels::page.simple class="bg-cover bg-center" style="background-image: url('{{ asset('images/خلفية.png') }}');">
    <div class="bg-white dark:bg-gray-900 bg-opacity-90 dark:bg-opacity-90 backdrop-blur-sm rounded-xl shadow-md p-8 max-w-2xl mx-auto">
        <div class="flex flex-col items-center justify-center">
            <!-- نموذج تسجيل الدخول -->
            <div class="w-full">
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

                <x-filament-panels::form id="form" wire:submit="authenticate" class="space-y-4">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
            </div>
        </div>

        <!-- حقوق الملكية في الأسفل -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-600 dark:text-gray-400">&copy; {{ date('Y') }} جميع الحقوق محفوظة لنظام غرب</p>
        </div>
    </div>

    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}
            {{ $this->registerAction }}
        </x-slot>
    @endif
</x-filament-panels::page.simple>
