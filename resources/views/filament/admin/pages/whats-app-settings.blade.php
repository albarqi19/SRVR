<x-filament-panels::page>
    <div class="space-y-6">
        <!-- إحصائيات سريعة -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الرسائل المرسلة</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->getSentMessagesCount() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">في الانتظار</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->getPendingMessagesCount() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">الرسائل الفاشلة</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->getFailedMessagesCount() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 rounded-full {{ $this->getConnectionStatus() ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                            <div class="h-3 w-3 rounded-full {{ $this->getConnectionStatus() ? 'bg-green-600' : 'bg-red-600' }}"></div>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">حالة الاتصال</p>
                        <p class="text-lg font-semibold {{ $this->getConnectionStatus() ? 'text-green-600' : 'text-red-600' }}">
                            {{ $this->getConnectionStatus() ? 'متصل' : 'غير متصل' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- الرسائل الأخيرة وإحصائيات أسبوعية -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- آخر الرسائل -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">آخر الرسائل المرسلة</h3>
                </div>
                <div class="p-6">
                    @forelse($this->getRecentMessages() as $message)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                    <span class="text-sm font-medium text-gray-900">{{ $message->phone_number }}</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $message->status === 'sent' ? 'bg-green-100 text-green-800' : 
                                           ($message->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $message->status === 'sent' ? 'مرسلة' : ($message->status === 'failed' ? 'فاشلة' : 'في الانتظار') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit($message->content, 80) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $message->user_name ?? 'غير محدد' }}</p>
                            </div>
                            <div class="text-sm text-gray-400 ml-4">
                                {{ $message->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.476L3 21l2.476-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">لم يتم إرسال أي رسائل بعد</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- إحصائيات أسبوعية -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">إحصائيات الأسبوع الماضي</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @php
                            $weeklyStats = $this->getWeeklyStats();
                        @endphp
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">إجمالي الرسائل</span>
                            <span class="text-sm font-medium text-gray-900">{{ $weeklyStats['total'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">معدل النجاح</span>
                            <span class="text-sm font-medium text-green-600">{{ $weeklyStats['success_rate'] }}%</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">رسائل الحضور</span>
                            <span class="text-sm font-medium text-gray-900">{{ $weeklyStats['attendance'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">رسائل الإشعارات</span>
                            <span class="text-sm font-medium text-gray-900">{{ $weeklyStats['notifications'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">رسائل مخصصة</span>
                            <span class="text-sm font-medium text-gray-900">{{ $weeklyStats['custom'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نموذج الإعدادات -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">إعدادات WhatsApp</h3>
                <p class="mt-1 text-sm text-gray-500">قم بتكوين إعدادات الاتصال مع WhatsApp API</p>
            </div>
            
            <div class="p-6">
                {{ $this->form }}
                
                <!-- أزرار الإجراءات -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex space-x-3 rtl:space-x-reverse">
                        {{ $this->testConnectionAction }}
                        {{ $this->resetSettingsAction }}
                    </div>
                    
                    <div class="flex space-x-3 rtl:space-x-reverse">
                        <x-filament::button 
                            type="submit" 
                            wire:click="save"
                            color="primary"
                            size="sm"
                        >
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </x-slot>
                            حفظ الإعدادات
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>

        <!-- معلومات إضافية ونصائح -->
        <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3 rtl:mr-3">
                    <h3 class="text-sm font-medium text-blue-800">نصائح مهمة:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc space-y-1 pl-5 rtl:pr-5 rtl:pl-0">
                            <li>تأكد من صحة رقم الواتساب وAPI Token قبل الحفظ</li>
                            <li>استخدم زر "اختبار الاتصال" للتحقق من صحة الإعدادات</li>
                            <li>يتم إرسال رسائل الحضور تلقائياً عند تسجيل الحضور</li>
                            <li>يمكنك تخصيص نصوص الرسائل من خلال إعدادات القوالب</li>
                            <li>في حالة فشل الإرسال، ستظهر الرسائل في قائمة "الرسائل الفاشلة"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // تحديث الإحصائيات كل 30 ثانية
        setInterval(() => {
            @this.call('refreshStats');
        }, 30000);
    </script>
    @endpush
</x-filament-panels::page>
