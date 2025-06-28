<x-filament-widgets::widget>
    <x-filament::section>
        @if($hasIssues)
            <div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400 mt-1 mr-3" />
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-red-800 dark:text-red-200 mb-2">
                            ⚠️ تحذير: مشاكل في رسائل الواتساب
                        </h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            @if($stats['pending_messages'] > 10)
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                                        {{ $stats['pending_messages'] }}
                                    </div>
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        رسائل معلقة
                                    </div>
                                </div>
                            @endif

                            @if($stats['failed_messages'] > 0)
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                                        {{ $stats['failed_messages'] }}
                                    </div>
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        رسائل فاشلة
                                    </div>
                                </div>
                            @endif

                            @if($stats['queue_jobs'] > 20)
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                                        {{ $stats['queue_jobs'] }}
                                    </div>
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        مهام معلقة
                                    </div>
                                </div>
                            @endif

                            @if($stats['failed_jobs'] > 0)
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                                        {{ $stats['failed_jobs'] }}
                                    </div>
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        مهام فاشلة
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('filament.admin.pages.whats-app-manager') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-heroicon-o-wrench-screwdriver class="h-4 w-4 mr-2" />
                                إصلاح الآن
                            </a>
                            
                            <a href="{{ route('filament.admin.resources.whats-app-messages.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-heroicon-o-envelope class="h-4 w-4 mr-2" />
                                عرض الرسائل
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <x-heroicon-o-check-circle class="h-6 w-6 text-green-600 dark:text-green-400 mr-3" />
                    <div>
                        <h3 class="text-lg font-medium text-green-800 dark:text-green-200">
                            ✅ رسائل الواتساب تعمل بشكل طبيعي
                        </h3>
                        <p class="text-green-600 dark:text-green-400 text-sm mt-1">
                            لا توجد رسائل معلقة أو مشاكل في النظام.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
