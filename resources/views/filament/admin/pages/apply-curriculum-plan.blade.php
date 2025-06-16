<x-filament-panels::page>
    <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10">
            <div class="fi-ta-header flex flex-col gap-3 p-4 sm:px-6 sm:flex-row sm:items-center">
                <div class="grid gap-y-1">
                    <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                        تطبيق خطط المناهج على الطلاب
                    </h1>
                    <p class="fi-header-subheading text-sm text-gray-500 dark:text-gray-400">
                        قم بتطبيق خطط المناهج المنشأة من منشئ المناهج على الطلاب
                    </p>
                </div>
            </div>
        </div>
        
        <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-white/10">
            <div class="p-6">
                <div class="text-center">
                    <div class="mx-auto max-w-md">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            صفحة تطبيق المناهج
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            هذه الصفحة تحت التطوير حالياً. ستتيح لك تطبيق الخطط المنشأة من منشئ المناهج على الطلاب المحددين.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('filament.admin.pages.curriculum-builder') }}" 
                               class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                إنشاء منهج جديد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
