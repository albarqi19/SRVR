{{-- daily-report-modal.blade.php --}}
<div class="space-y-6">
    {{-- إحصائيات عامة --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalStudents }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">إجمالي الطلاب النشطين</div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $completedToday }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">أكملوا منهج اليوم</div>
        </div>
        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $avgProgress }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">متوسط الصفحة الحالية</div>
        </div>
    </div>

    {{-- تفاصيل الطلاب --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تفاصيل تقدم الطلاب</h3>
        </div>
        
        @if($students->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            اسم الطالب
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            المنهج
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            الصفحة الحالية
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            نسبة التقدم
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            الحالة
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($students as $student)
                    @php
                        $progressPercentage = round((($student->current_page ?? 1) / 604) * 100, 1);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $student->student->name }}
                            </div>
                            @if($student->student->quranCircle)
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $student->student->quranCircle->name }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $student->curriculum->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                صفحة {{ $student->current_page ?? 1 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-1 ml-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                        <div class="h-2 rounded-full 
                                            @if($progressPercentage >= 80) bg-green-600
                                            @elseif($progressPercentage >= 50) bg-yellow-500
                                            @else bg-red-500
                                            @endif" 
                                            style="width: {{ $progressPercentage }}%">
                                        </div>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $progressPercentage }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($student->status === 'قيد التنفيذ') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                @elseif($student->status === 'مكتمل') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                @elseif($student->status === 'متوقف مؤقتاً') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                @endif">
                                {{ $student->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-lg font-medium">لا يوجد طلاب نشطين</p>
                <p class="text-sm">لم يتم العثور على أي مناهج نشطة للطلاب</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ملاحظات وتوصيات --}}
    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-400 mb-2">
            📝 ملاحظات وتوصيات:
        </h4>
        <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
            <li>• تأكد من متابعة الطلاب الذين لم يكملوا منهج اليوم</li>
            <li>• راجع تقدم الطلاب ذوي النسب المنخفضة</li>
            <li>• قم بتحديث البيانات بانتظام لضمان دقة التقارير</li>
        </ul>
    </div>
</div>
