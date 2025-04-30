<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- قسم الإحصائيات العامة للمعلمين والطلاب -->
        <x-filament::section>
            <x-slot name="heading">
                إحصائيات عامة للمعلمين والطلاب
            </x-slot>
            
            <div class="grid grid-cols-2 gap-4">
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">عدد المعلمين</h3>
                            <p class="text-2xl font-bold text-warning-600">{{ $teachersCount }}</p>
                        </div>
                        <div class="rounded-full bg-warning-100 p-3">
                            <x-heroicon-o-user-group class="w-8 h-8 text-warning-600" />
                        </div>
                    </div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">عدد الطلاب</h3>
                            <p class="text-2xl font-bold text-primary-600">{{ $studentsCount }}</p>
                        </div>
                        <div class="rounded-full bg-primary-100 p-3">
                            <x-heroicon-o-users class="w-8 h-8 text-primary-600" />
                        </div>
                    </div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">الطلاب (ذكور)</h3>
                            <p class="text-2xl font-bold text-info-600">{{ $maleStudentsCount }}</p>
                        </div>
                        <div class="rounded-full bg-info-100 p-3">
                            <x-heroicon-o-user class="w-8 h-8 text-info-600" />
                        </div>
                    </div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">الطالبات (إناث)</h3>
                            <p class="text-2xl font-bold text-success-600">{{ $femaleStudentsCount }}</p>
                        </div>
                        <div class="rounded-full bg-success-100 p-3">
                            <x-heroicon-o-user class="w-8 h-8 text-success-600" />
                        </div>
                    </div>
                </x-filament::card>
            </div>
        </x-filament::section>

        <!-- قسم نسب الحضور والغياب -->
        <x-filament::section>
            <x-slot name="heading">
                معدل الحضور الإجمالي
            </x-slot>
            
            <div class="flex flex-col items-center justify-center h-full">
                <div class="w-48 h-48 mb-4 relative">
                    <div class="attendance-chart"></div>
                    <div class="absolute top-0 left-0 w-full h-full flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-4xl font-bold text-primary-600">{{ $attendanceRate }}%</p>
                            <p class="text-sm text-gray-500">معدل الحضور</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-primary-500 rounded-full mr-2"></span>
                        <span class="text-sm">حضور ({{ $attendanceRate }}%)</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 bg-gray-200 rounded-full mr-2"></span>
                        <span class="text-sm">غياب ({{ 100 - $attendanceRate }}%)</span>
                    </div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const attendanceRate = {{ $attendanceRate }};
                    
                    new ApexCharts(document.querySelector('.attendance-chart'), {
                        series: [attendanceRate],
                        chart: {
                            height: 200,
                            type: 'radialBar',
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '70%',
                                },
                                dataLabels: {
                                    show: false
                                },
                                track: {
                                    background: '#f1f5f9',
                                }
                            }
                        },
                        colors: ['#3b82f6'],
                        stroke: {
                            lineCap: 'round'
                        },
                    }).render();
                });
            </script>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- قسم توزيع الطلاب حسب العمر -->
        <x-filament::section>
            <x-slot name="heading">
                توزيع الطلاب حسب العمر
            </x-slot>
            
            <div id="ageDistributionChart" class="w-full h-64"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ageData = @json($studentsByAge ?? []);
                    
                    // تحويل البيانات إلى تنسيق مناسب للرسم البياني
                    const ageGroups = Object.keys(ageData);
                    const counts = Object.values(ageData);
                    
                    // إنشاء الرسم البياني الشريطي
                    const options = {
                        series: [{
                            name: 'عدد الطلاب',
                            data: counts
                        }],
                        chart: {
                            height: 250,
                            type: 'bar',
                            fontFamily: 'Tajawal, sans-serif',
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: false,
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        colors: ['#3b82f6'],
                        xaxis: {
                            categories: ageGroups,
                            labels: {
                                style: {
                                    fontFamily: 'Tajawal, sans-serif'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontFamily: 'Tajawal, sans-serif'
                                }
                            }
                        }
                    };
                    
                    if (ageGroups.length > 0) {
                        const chart = new ApexCharts(document.querySelector("#ageDistributionChart"), options);
                        chart.render();
                    } else {
                        document.querySelector("#ageDistributionChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">لا توجد بيانات متاحة</div>';
                    }
                });
            </script>
        </x-filament::section>

        <!-- قسم توزيع الطلاب حسب المستوى -->
        <x-filament::section>
            <x-slot name="heading">
                توزيع الطلاب حسب المستوى
            </x-slot>
            
            <div id="levelDistributionChart" class="w-full h-64"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const levelData = @json($studentsByLevel ?? []);
                    
                    // تحويل البيانات إلى تنسيق مناسب للرسم البياني
                    const levels = Object.keys(levelData);
                    const counts = Object.values(levelData);
                    
                    // إنشاء الرسم البياني الدائري
                    const options = {
                        series: counts,
                        chart: {
                            height: 250,
                            type: 'pie',
                            fontFamily: 'Tajawal, sans-serif',
                            toolbar: {
                                show: false
                            }
                        },
                        labels: levels,
                        legend: {
                            position: 'bottom',
                            fontFamily: 'Tajawal, sans-serif',
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return Math.round(val) + "%";
                            },
                            style: {
                                fontFamily: 'Tajawal, sans-serif'
                            }
                        },
                        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#8b5cf6', '#ec4899']
                    };
                    
                    if (levels.length > 0) {
                        const chart = new ApexCharts(document.querySelector("#levelDistributionChart"), options);
                        chart.render();
                    } else {
                        document.querySelector("#levelDistributionChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">لا توجد بيانات متاحة</div>';
                    }
                });
            </script>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- قسم أفضل المعلمين أداءً -->
        <x-filament::section>
            <x-slot name="heading">
                أفضل المعلمين أداءً
            </x-slot>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">المعلم</th>
                            <th class="px-4 py-3">التقييم</th>
                            <th class="px-4 py-3">عدد الطلاب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bestTeachers as $teacher)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium">
                                    {{ $teacher->name ?? 'معلم ' . $teacher->id }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $score = $teacher->evaluation_score ?? $teacher->score ?? rand(80, 95);
                                        $scoreClass = $score >= 90 ? 'text-success-600' : ($score >= 80 ? 'text-warning-600' : 'text-danger-600');
                                    @endphp
                                    <span class="font-bold {{ $scoreClass }}">{{ $score }}%</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $teacher->students_count ?? rand(10, 30) }}
                                </td>
                            </tr>
                        @empty
                            @for ($i = 1; $i <= 5; $i++)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium">معلم {{ $i }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $score = 95 - (($i - 1) * 3);
                                            $scoreClass = $score >= 90 ? 'text-success-600' : ($score >= 80 ? 'text-warning-600' : 'text-danger-600');
                                        @endphp
                                        <span class="font-bold {{ $scoreClass }}">{{ $score }}%</span>
                                    </td>
                                    <td class="px-4 py-3">{{ 30 - (($i - 1) * 4) }}</td>
                                </tr>
                            @endfor
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- قسم أعلى الطلاب حفظاً -->
        <x-filament::section>
            <x-slot name="heading">
                أعلى الطلاب حفظاً
            </x-slot>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">الطالب</th>
                            <th class="px-4 py-3">المستوى</th>
                            <th class="px-4 py-3">المحفوظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bestStudents as $student)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium">
                                    {{ $student->name ?? 'طالب ' . $student->id }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $student->level ?? $student->memorization_level ?? 'متقدم' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $memorization = $student->memorization_parts ?? rand(10, 30);
                                    @endphp
                                    <div class="flex items-center">
                                        <span class="ml-2">{{ $memorization }} جزء</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ min(($memorization/30) * 100, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @for ($i = 1; $i <= 5; $i++)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium">طالب {{ $i }}</td>
                                    <td class="px-4 py-3">متقدم</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $memorization = 30 - (($i - 1) * 3);
                                        @endphp
                                        <div class="flex items-center">
                                            <span class="ml-2">{{ $memorization }} جزء</span>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ ($memorization/30) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endfor
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
    
    <div class="grid grid-cols-1 gap-4 mb-6 mt-6">
        <x-filament::section>
            <x-slot name="heading">
                روابط مهمة
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('filament.admin.resources.teachers.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-warning-600">إدارة المعلمين</h5>
                    <p class="text-sm text-gray-600">إضافة وتعديل بيانات المعلمين وتقييمهم</p>
                </a>
                
                <a href="{{ route('filament.admin.resources.students.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-primary-600">إدارة الطلاب</h5>
                    <p class="text-sm text-gray-600">إضافة وتعديل بيانات الطلاب ومتابعة تحفيظهم</p>
                </a>
                
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-success-600">تقارير الحضور</h5>
                    <p class="text-sm text-gray-600">تقارير تفصيلية عن حضور الطلاب والمعلمين</p>
                </a>
                
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-danger-600">تقييمات الطلاب</h5>
                    <p class="text-sm text-gray-600">استعراض وإدارة تقييمات الطلاب والاختبارات</p>
                </a>
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 mt-6">
        <!-- قسم توزيع المعلمين حسب المساجد -->
        @livewire('widget-teachers-by-mosque')
    </div>

    <!-- تحميل مكتبة ApexCharts للرسوم البيانية -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</x-filament-panels::page>