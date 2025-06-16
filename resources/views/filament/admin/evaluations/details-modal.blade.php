{{-- عرض تفاصيل تقييم المعلم --}}
<div class="space-y-6 p-6">
    {{-- معلومات المعلم --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">معلومات المعلم</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">اسم المعلم:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">{{ $evaluation->teacher->name }}</span>
            </div>
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">المسجد:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">{{ $evaluation->teacher->mosque?->name ?? 'غير محدد' }}</span>
            </div>
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">نوع المهمة:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                    {{ $evaluation->teacher->task_type }}
                </span>
            </div>
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">الحلقة:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-2">{{ $evaluation->teacher->quranCircle?->name ?? 'غير محدد' }}</span>
            </div>
        </div>
    </div>

    {{-- معايير التقييم --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">معايير التقييم التفصيلية</h3>
        </div>
        <div class="p-4 space-y-4">
            {{-- تقييم الأداء --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">تقييم الأداء</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">جودة التدريس والالتزام بالمنهج</div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($evaluation->performance_score / 20) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white min-w-[60px] text-right">
                        {{ number_format($evaluation->performance_score, 1) }} / 20
                    </span>
                </div>
            </div>

            {{-- تقييم الحضور --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">تقييم الالتزام بالحضور</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">انتظام الحضور والالتزام بالمواعيد</div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ ($evaluation->attendance_score / 20) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white min-w-[60px] text-right">
                        {{ number_format($evaluation->attendance_score, 1) }} / 20
                    </span>
                </div>
            </div>

            {{-- تقييم التفاعل مع الطلاب --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">تقييم التفاعل مع الطلاب</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">التواصل مع الطلاب وحل مشاكلهم</div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-purple-600 h-2.5 rounded-full" style="width: {{ ($evaluation->student_interaction_score / 20) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white min-w-[60px] text-right">
                        {{ number_format($evaluation->student_interaction_score, 1) }} / 20
                    </span>
                </div>
            </div>

            {{-- تقييم السمت والتعاون --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">تقييم السمت والتعاون</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">الأخلاق والتعامل مع الزملاء</div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-yellow-600 h-2.5 rounded-full" style="width: {{ ($evaluation->behavior_cooperation_score / 20) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white min-w-[60px] text-right">
                        {{ number_format($evaluation->behavior_cooperation_score, 1) }} / 20
                    </span>
                </div>
            </div>

            {{-- تقييم الحفظ والتلاوة --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">تقييم الحفظ والتلاوة</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">إتقان القرآن وجودة التلاوة</div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ ($evaluation->memorization_recitation_score / 20) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white min-w-[60px] text-right">
                        {{ number_format($evaluation->memorization_recitation_score, 1) }} / 20
                    </span>
                </div>
            </div>

            {{-- التقييم العام --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">التقييم العام</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">التقييم الشامل للأداء العام</div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ ($evaluation->general_evaluation_score / 20) * 100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white min-w-[60px] text-right">
                        {{ number_format($evaluation->general_evaluation_score, 1) }} / 20
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- النتيجة الإجمالية --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
        <div class="text-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">النتيجة الإجمالية</h3>
            <div class="flex items-center justify-center space-x-4 rtl:space-x-reverse">
                <div class="text-4xl font-bold" style="color: {{ $evaluation->total_score >= 90 ? '#10b981' : ($evaluation->total_score >= 80 ? '#3b82f6' : ($evaluation->total_score >= 70 ? '#06b6d4' : ($evaluation->total_score >= 60 ? '#f59e0b' : '#ef4444'))) }}">
                    {{ number_format($evaluation->total_score, 1) }}%
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold" style="color: {{ $evaluation->total_score >= 90 ? '#10b981' : ($evaluation->total_score >= 80 ? '#3b82f6' : ($evaluation->total_score >= 70 ? '#06b6d4' : ($evaluation->total_score >= 60 ? '#f59e0b' : '#ef4444'))) }}">
                        {{ $evaluation->performance_grade }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">التصنيف</div>
                </div>
            </div>

            {{-- شريط التقدم الإجمالي --}}
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                    <div 
                        class="h-4 rounded-full transition-all duration-300"
                        style="width: {{ $evaluation->total_score }}%; background-color: {{ $evaluation->total_score >= 90 ? '#10b981' : ($evaluation->total_score >= 80 ? '#3b82f6' : ($evaluation->total_score >= 70 ? '#06b6d4' : ($evaluation->total_score >= 60 ? '#f59e0b' : '#ef4444'))) }}"
                    ></div>
                </div>
            </div>
        </div>
    </div>

    {{-- معلومات التقييم --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">معلومات التقييم</h4>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">تاريخ التقييم:</span>
                    <span class="font-medium text-gray-900 dark:text-white ml-2">{{ $evaluation->evaluation_date->format('d-m-Y') }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">فترة التقييم:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 ml-2">
                        {{ $evaluation->evaluation_period }}
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">حالة التقييم:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2
                        {{ $evaluation->status === 'معتمد' ? 'bg-green-100 text-green-800' : 
                           ($evaluation->status === 'مكتمل' ? 'bg-yellow-100 text-yellow-800' : 
                           ($evaluation->status === 'مراجعة' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ $evaluation->status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">معلومات المقيم</h4>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">اسم المقيم:</span>
                    <span class="font-medium text-gray-900 dark:text-white ml-2">{{ $evaluation->evaluator->name }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">صفة المقيم:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 ml-2">
                        {{ $evaluation->evaluator_role }}
                    </span>
                </div>
                <div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">تاريخ الإنشاء:</span>
                    <span class="font-medium text-gray-900 dark:text-white ml-2">{{ $evaluation->created_at->format('d-m-Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- الملاحظات --}}
    @if($evaluation->notes)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700 p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                <svg class="w-5 h-5 text-yellow-600 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                ملاحظات التقييم
            </h4>
            <p class="text-gray-700 dark:text-gray-300">{{ $evaluation->notes }}</p>
        </div>
    @endif
</div>
