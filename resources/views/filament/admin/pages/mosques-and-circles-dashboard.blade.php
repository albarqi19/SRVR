<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-filament::section>
            <x-slot name="heading">
                إحصائيات عامة للمساجد
            </x-slot>
            
            <div class="grid grid-cols-2 gap-4">
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">عدد المساجد</h3>
                            <p class="text-2xl font-bold text-primary-600">{{ $mosquesCount }}</p>
                        </div>
                        <div class="rounded-full bg-primary-100 p-3">
                            <x-heroicon-o-building-library class="w-8 h-8 text-primary-600" />
                        </div>
                    </div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">عدد الحلقات</h3>
                            <p class="text-2xl font-bold text-success-600">{{ $circlesCount }}</p>
                        </div>
                        <div class="rounded-full bg-success-100 p-3">
                            <x-heroicon-o-academic-cap class="w-8 h-8 text-success-600" />
                        </div>
                    </div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">الحلقات النشطة</h3>
                            <p class="text-2xl font-bold text-info-600">{{ $activeCirclesCount }}</p>
                        </div>
                        <div class="rounded-full bg-info-100 p-3">
                            <x-heroicon-o-check-circle class="w-8 h-8 text-info-600" />
                        </div>
                    </div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-gray-600 mb-1">الحلقات المتوقفة</h3>
                            <p class="text-2xl font-bold text-danger-600">{{ $inactiveCirclesCount }}</p>
                        </div>
                        <div class="rounded-full bg-danger-100 p-3">
                            <x-heroicon-o-x-circle class="w-8 h-8 text-danger-600" />
                        </div>
                    </div>
                </x-filament::card>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                توزيع الحلقات حسب الفترة
            </x-slot>
            
            <div id="periodChart" class="w-full h-64"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const periodData = @json($circlesByPeriod ?? []);
                    
                    // تحويل البيانات إلى تنسيق مناسب للرسم البياني
                    const periods = Object.keys(periodData);
                    const counts = Object.values(periodData);
                    
                    // إنشاء الرسم البياني الشريطي
                    const options = {
                        series: [{
                            name: 'عدد الحلقات',
                            data: counts
                        }],
                        chart: {
                            height: 256,
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
                        colors: ['#0ea5e9'],
                        xaxis: {
                            categories: periods,
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
                    
                    if (periods.length > 0) {
                        const chart = new ApexCharts(document.querySelector("#periodChart"), options);
                        chart.render();
                    } else {
                        document.querySelector("#periodChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">لا توجد بيانات متاحة</div>';
                    }
                });
            </script>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- قسم توزيع المساجد حسب المنطقة -->
        <x-filament::section>
            <x-slot name="heading">
                توزيع المساجد حسب المنطقة
            </x-slot>
            
            <div id="regionChart" class="w-full h-64"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const regionData = @json($mosquesByRegion ?? []);
                    
                    // تحويل البيانات إلى تنسيق مناسب للرسم البياني
                    const regions = Object.keys(regionData);
                    const counts = Object.values(regionData);
                    
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
                        labels: regions,
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
                    
                    if (regions.length > 0) {
                        const chart = new ApexCharts(document.querySelector("#regionChart"), options);
                        chart.render();
                    } else {
                        document.querySelector("#regionChart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">لا توجد بيانات متاحة</div>';
                    }
                });
            </script>
        </x-filament::section>

        <!-- قسم توزيع الحلقات حسب المسجد (أعلى 10) -->
        <x-filament::section>
            <x-slot name="heading">
                أكثر المساجد عدداً في الحلقات
            </x-slot>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">المسجد</th>
                            <th class="px-4 py-3">عدد الحلقات</th>
                            <th class="px-4 py-3">النسبة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($circlesPerMosque ?? [] as $item)
                            <tr class="border-b">
                                <td class="px-4 py-3">
                                    @php
                                        $mosqueName = "المسجد " . $item['mosque_id'];
                                        // يمكن هنا استبدال الكود بجلب اسم المسجد من قاعدة البيانات
                                        // حسب معرف المسجد ولكن هذا يحتاج لاستعلام إضافي
                                    @endphp
                                    {{ $mosqueName }}
                                </td>
                                <td class="px-4 py-3">{{ $item['count'] }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $percentage = $circlesCount > 0 ? round(($item['count'] / $circlesCount) * 100, 1) : 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <span class="ml-2">{{ $percentage }}%</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">لا توجد بيانات متاحة</td>
                            </tr>
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
                <a href="{{ route('filament.admin.resources.mosques.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-primary-600">إدارة المساجد</h5>
                    <p class="text-sm text-gray-600">إضافة وتعديل وتفعيل المساجد وإدارة بياناتها الأساسية</p>
                </a>
                
                <a href="{{ route('filament.admin.resources.quran-circles.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-success-600">إدارة الحلقات القرآنية</h5>
                    <p class="text-sm text-gray-600">إضافة وتعديل وتفعيل الحلقات القرآنية وتعيين المعلمين</p>
                </a>
                
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-warning-600">تقارير المساجد</h5>
                    <p class="text-sm text-gray-600">الاطلاع على تقارير أداء المساجد وأنشطتها</p>
                </a>
                
                <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition">
                    <h5 class="mb-2 text-lg font-bold text-danger-600">تقارير الحلقات</h5>
                    <p class="text-sm text-gray-600">الاطلاع على تقارير أداء الحلقات والتقييمات</p>
                </a>
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 mt-6">
        <!-- تقسيم الويدجات إلى صفين -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @livewire('widget-mosques-by-region')
            @livewire('widget-circles-by-type')
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @livewire('widget-circles-per-mosque')
            @livewire('widget-occupancy-rates')
        </div>
    </div>

    <!-- تحميل مكتبة ApexCharts للرسوم البيانية -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</x-filament-panels::page>