{{-- daily-curriculum-modal.blade.php --}}
<div class="space-y-6">
    {{-- معلومات الطالب --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            معلومات الطالب
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">اسم الطالب:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $student->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الحلقة:</span>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $student->quranCircle->name ?? 'بدون حلقة' }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">المنهج:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $curriculum->name }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">نوع المنهج:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $curriculum->type }}</p>
            </div>
        </div>
    </div>

    {{-- التقدم الحالي --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            التقدم الحالي
        </h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الصفحة الحالية:</span>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                    {{ $record->current_page ?? 1 }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">السورة الحالية:</span>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $record->current_surah ?? 'الفاتحة' }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الآية الحالية:</span>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $record->current_ayah ?? 1 }}
                </p>
            </div>
        </div>
        
        {{-- شريط التقدم --}}
        <div class="mt-4">
            @php
                $totalPages = 604;
                $currentPage = $record->current_page ?? 1;
                $progressPercentage = round(($currentPage / $totalPages) * 100, 1);
            @endphp
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">نسبة التقدم:</span>
                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $progressPercentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progressPercentage }}%"></div>
            </div>
        </div>
    </div>

    {{-- المنهج اليومي --}}
    @if(isset($dailyCurriculum))
    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            المنهج اليومي
        </h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">الحفظ الجديد</h4>
                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                        {{ $record->daily_memorization_pages ?? 1 }} صفحة
                    </p>
                </div>
            </div>
            <div class="text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">المراجعة الصغرى</h4>
                    <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                        {{ $record->daily_minor_review_pages ?? 2 }} صفحة
                    </p>
                </div>
            </div>
            <div class="text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">المراجعة الكبرى</h4>
                    <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                        {{ $record->daily_major_review_pages ?? 5 }} صفحة
                    </p>
                </div>
            </div>
        </div>
        
        @if(isset($dailyCurriculum['next_day_content']))
        <div class="mt-4 p-3 bg-white dark:bg-gray-800 rounded-lg">
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">محتوى اليوم التالي:</h4>
            <div class="text-sm text-gray-900 dark:text-white space-y-1">
                @if(isset($dailyCurriculum['next_day_content']['memorization']))
                <p><strong>الحفظ:</strong> {{ $dailyCurriculum['next_day_content']['memorization'] }}</p>
                @endif
                @if(isset($dailyCurriculum['next_day_content']['minor_review']))
                <p><strong>المراجعة الصغرى:</strong> {{ $dailyCurriculum['next_day_content']['minor_review'] }}</p>
                @endif
                @if(isset($dailyCurriculum['next_day_content']['major_review']))
                <p><strong>المراجعة الكبرى:</strong> {{ $dailyCurriculum['next_day_content']['major_review'] }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- حالة المنهج --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            حالة المنهج
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الحالة:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->status === 'قيد التنفيذ') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                    @elseif($record->status === 'مكتمل') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                    @elseif($record->status === 'متوقف مؤقتاً') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                    @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                    @endif">
                    {{ $record->status }}
                </span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ البدء:</span>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $record->start_date ? \Carbon\Carbon::parse($record->start_date)->format('Y-m-d') : 'غير محدد' }}
                </p>
            </div>
        </div>
        
        @if($record->notes)
        <div class="mt-3">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">ملاحظات:</span>
            <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $record->notes }}</p>
        </div>
        @endif
    </div>
</div>
