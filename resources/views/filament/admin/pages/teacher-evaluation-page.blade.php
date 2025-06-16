<x-filament-panels::page>
    <div class="space-y-6">
        <!-- قسم الإحصائيات العامة للتقييمات -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-600 mb-1">إجمالي التقييمات</h3>
                        <p class="text-2xl font-bold text-primary-600">{{ $totalEvaluations }}</p>
                    </div>
                    <div class="rounded-full bg-primary-100 p-3">
                        <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-600 mb-1">متوسط التقييمات</h3>
                        <p class="text-2xl font-bold text-success-600">{{ $averageScore }}%</p>
                    </div>
                    <div class="rounded-full bg-success-100 p-3">
                        <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-600 mb-1">تقييمات هذا الشهر</h3>
                        <p class="text-2xl font-bold text-info-600">{{ $monthlyEvaluations }}</p>
                    </div>
                    <div class="rounded-full bg-info-100 p-3">
                        <svg class="w-6 h-6 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-600 mb-1">في انتظار الاعتماد</h3>
                        <p class="text-2xl font-bold text-warning-600">{{ $pendingApproval }}</p>
                    </div>
                    <div class="rounded-full bg-warning-100 p-3">
                        <svg class="w-6 h-6 text-warning-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- قسم الإجراءات السريعة -->
        <x-filament::section>
            <x-slot name="heading">
                الإجراءات السريعة
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('filament.admin.resources.teacher-evaluations.create') }}" 
                   class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition group">
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <div class="rounded-full bg-success-100 p-3 group-hover:bg-success-200 transition">
                            <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-lg font-bold text-success-600">إنشاء تقييم جديد</h5>
                            <p class="text-sm text-gray-600">إضافة تقييم جديد لأحد المعلمين</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('filament.admin.resources.teacher-evaluations.index') }}" 
                   class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition group">
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <div class="rounded-full bg-primary-100 p-3 group-hover:bg-primary-200 transition">
                            <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-lg font-bold text-primary-600">عرض جميع التقييمات</h5>
                            <p class="text-sm text-gray-600">استعراض وإدارة جميع تقييمات المعلمين</p>
                        </div>
                    </div>
                </a>

                <a href="#reports-section" 
                   class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition group">
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <div class="rounded-full bg-info-100 p-3 group-hover:bg-info-200 transition">
                            <svg class="w-6 h-6 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-lg font-bold text-info-600">تقارير التقييم</h5>
                            <p class="text-sm text-gray-600">تقارير تفصيلية وإحصائيات التقييمات</p>
                        </div>
                    </div>
                </a>
            </div>
        </x-filament::section>

        <!-- قسم أفضل المعلمين حسب التقييمات -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">
                    أفضل المعلمين حسب التقييمات
                </x-slot>
                
                <div class="space-y-4">
                    @forelse ($topTeachers as $index => $teacher)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $index === 0 ? 'from-yellow-400 to-yellow-600' : ($index === 1 ? 'from-gray-400 to-gray-600' : 'from-amber-600 to-amber-800') }} flex items-center justify-center text-white font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $teacher->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $teacher->mosque?->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $teacher->average_evaluation >= 90 ? 'text-success-600' : ($teacher->average_evaluation >= 80 ? 'text-primary-600' : 'text-warning-600') }}">
                                    {{ number_format($teacher->average_evaluation, 1) }}%
                                </p>
                                <p class="text-xs text-gray-500">{{ $teacher->completed_evaluations_count }} تقييم</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500">لا توجد تقييمات بعد</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            <!-- قسم آخر التقييمات -->
            <x-filament::section>
                <x-slot name="heading">
                    آخر التقييمات
                </x-slot>
                
                <div class="space-y-4">
                    @forelse ($recentEvaluations as $evaluation)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $evaluation->teacher->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $evaluation->evaluation_date->format('d/m/Y') }} - {{ $evaluation->evaluation_period }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->grade_color === 'success' ? 'bg-success-100 text-success-800' : ($evaluation->grade_color === 'primary' ? 'bg-primary-100 text-primary-800' : 'bg-warning-100 text-warning-800') }}">
                                    {{ $evaluation->total_score }}%
                                </span>
                                <p class="text-xs text-gray-500 mt-1">{{ $evaluation->performance_grade }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                            </svg>
                            <p class="text-gray-500">لا توجد تقييمات حديثة</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <!-- قسم التقارير والإحصائيات -->
        <x-filament::section id="reports-section">
            <x-slot name="heading">
                تقارير التقييم والإحصائيات
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- رسم بياني لتوزيع التقييمات -->
                <div class="bg-white p-6 rounded-lg border">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">توزيع التقييمات حسب الدرجات</h3>
                    <div id="evaluationDistributionChart" class="w-full h-64"></div>
                </div>

                <!-- رسم بياني للاتجاهات الشهرية -->
                <div class="bg-white p-6 rounded-lg border">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">اتجاهات التقييم الشهرية</h3>
                    <div id="monthlyTrendsChart" class="w-full h-64"></div>
                </div>
            </div>

            <!-- جدول معايير التقييم -->
            <div class="mt-6 bg-white p-6 rounded-lg border">
                <h3 class="text-lg font-medium text-gray-900 mb-4">متوسط درجات المعايير</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-600">{{ number_format($criteriaAverages['performance'] ?? 0, 1) }}</div>
                        <div class="text-sm text-gray-500">الأداء</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-success-600">{{ number_format($criteriaAverages['attendance'] ?? 0, 1) }}</div>
                        <div class="text-sm text-gray-500">الحضور</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-info-600">{{ number_format($criteriaAverages['interaction'] ?? 0, 1) }}</div>
                        <div class="text-sm text-gray-500">التفاعل</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-warning-600">{{ number_format($criteriaAverages['behavior'] ?? 0, 1) }}</div>
                        <div class="text-sm text-gray-500">السمت</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ number_format($criteriaAverages['memorization'] ?? 0, 1) }}</div>
                        <div class="text-sm text-gray-500">الحفظ</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600">{{ number_format($criteriaAverages['general'] ?? 0, 1) }}</div>
                        <div class="text-sm text-gray-500">التقييم العام</div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
        <!-- تحميل مكتبة ApexCharts للرسوم البيانية -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // رسم بياني لتوزيع التقييمات
                const distributionData = @json($distributionData ?? []);
                const distributionOptions = {
                    series: [{
                        name: 'عدد التقييمات',
                        data: Object.values(distributionData)
                    }],
                    chart: {
                        type: 'donut',
                        height: 250
                    },
                    labels: Object.keys(distributionData),
                    colors: ['#10b981', '#3b82f6', '#06b6d4', '#f59e0b', '#ef4444'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return Math.round(val) + "%"
                        }
                    }
                };

                if (Object.keys(distributionData).length > 0) {
                    const distributionChart = new ApexCharts(document.querySelector("#evaluationDistributionChart"), distributionOptions);
                    distributionChart.render();
                } else {
                    document.querySelector("#evaluationDistributionChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">لا توجد بيانات متاحة</div>';
                }

                // رسم بياني للاتجاهات الشهرية
                const monthlyData = @json($monthlyTrends ?? []);
                const monthlyOptions = {
                    series: [{
                        name: 'متوسط التقييمات',
                        data: Object.values(monthlyData)
                    }],
                    chart: {
                        type: 'line',
                        height: 250,
                        toolbar: {
                            show: false
                        }
                    },
                    xaxis: {
                        categories: Object.keys(monthlyData),
                        title: {
                            text: 'الشهر'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'متوسط التقييم (%)'
                        },
                        min: 0,
                        max: 100
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#3b82f6'],
                    grid: {
                        borderColor: '#f1f5f9'
                    }
                };

                if (Object.keys(monthlyData).length > 0) {
                    const monthlyChart = new ApexCharts(document.querySelector("#monthlyTrendsChart"), monthlyOptions);
                    monthlyChart.render();
                } else {
                    document.querySelector("#monthlyTrendsChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">لا توجد بيانات متاحة</div>';
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
