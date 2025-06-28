<x-filament-panels::page>
    <div class="space-y-6">
        <!-- إحصائيات النظام -->
        <x-filament::section>
            <x-slot name="heading">
                إحصائيات النظام
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-950 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-clock class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                الرسائل المعلقة
                            </div>
                            <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                {{ $stats['pending_messages'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 dark:bg-green-950 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-check-circle class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-green-600 dark:text-green-400">
                                الرسائل المرسلة
                            </div>
                            <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                                {{ $stats['sent_messages'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-red-50 dark:bg-red-950 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-x-circle class="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                الرسائل الفاشلة
                            </div>
                            <div class="text-2xl font-bold text-red-900 dark:text-red-100">
                                {{ $stats['failed_messages'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 dark:bg-purple-950 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-queue-list class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-purple-600 dark:text-purple-400">
                                المهام في القائمة
                            </div>
                            <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                {{ $stats['queue_jobs'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- معلومات إضافية -->
        <x-filament::section>
            <x-slot name="heading">
                معلومات تفصيلية
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        إجمالي الرسائل
                    </div>
                    <div class="text-lg font-semibold">
                        {{ $stats['total_messages'] }}
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        المهام الفاشلة
                    </div>
                    <div class="text-lg font-semibold">
                        {{ $stats['failed_jobs'] }}
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        الرسائل خلال 24 ساعة
                    </div>
                    <div class="text-lg font-semibold">
                        {{ $stats['recent_messages'] }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- تعليمات الاستخدام -->
        <x-filament::section>
            <x-slot name="heading">
                كيفية معالجة الرسائل العالقة
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <div class="bg-yellow-50 dark:bg-yellow-950 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2" />
                        <div>
                            <h4 class="text-yellow-800 dark:text-yellow-200 font-medium">
                                إذا كانت رسائل الواتساب عالقة:
                            </h4>
                            <ol class="list-decimal list-inside text-yellow-700 dark:text-yellow-300 mt-2 space-y-1">
                                <li>استخدم زر "عرض حالة النظام" لفهم المشكلة</li>
                                <li>جرب "معالجة الرسائل المعلقة" أولاً</li>
                                <li>إذا فشل ذلك، استخدم "إعادة محاولة الرسائل الفاشلة"</li>
                                <li>كحل أخير، استخدم "مسح قائمة الانتظار" ثم "إعادة تشغيل النظام"</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2" />
                        <div>
                            <h4 class="text-blue-800 dark:text-blue-200 font-medium">
                                ملاحظات مهمة:
                            </h4>
                            <ul class="list-disc list-inside text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                                <li>يمكن أيضاً استخدام الأمر في الطرفية: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">php artisan whatsapp:manage send</code></li>
                                <li>تأكد من تشغيل queue worker في الخلفية</li>
                                <li>مراجعة إعدادات الواتساب API في حالة تكرار المشاكل</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    <script>
        // تحديث الإحصائيات كل 30 ثانية
        setInterval(function() {
            window.location.reload();
        }, 30000);
    </script>
</x-filament-panels::page>
