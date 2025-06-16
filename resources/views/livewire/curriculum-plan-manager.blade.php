<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">إدارة خطط المناهج</h1>
        
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">تطبيق خطة على طلاب</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">اختر الخطة:</label>
                <select wire:model="selectedPlan" class="w-full p-2 border rounded-md">
                    <option value="">-- اختر خطة --</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan['id'] }}">
                            {{ $plan['name'] ?? 'خطة بدون اسم' }} 
                            ({{ $plan['type'] ?? 'نوع غير محدد' }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">اختر الطلاب:</label>
                <div class="max-h-40 overflow-y-auto border rounded-md p-2">
                    @foreach($students as $student)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" 
                                   wire:model="selectedStudents" 
                                   value="{{ $student['id'] }}"
                                   class="mr-2">
                            <label>{{ $student['name'] ?? 'طالب بدون اسم' }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <button wire:click="applyPlan" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                تطبيق الخطة
            </button>
        </div>
        
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">الخطط المتاحة</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border p-2">ID</th>
                            <th class="border p-2">اسم الخطة</th>
                            <th class="border p-2">النوع</th>
                            <th class="border p-2">عدد الأيام</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td class="border p-2">{{ $plan['id'] }}</td>
                                <td class="border p-2">{{ $plan['name'] ?? 'بدون اسم' }}</td>
                                <td class="border p-2">{{ $plan['type'] ?? 'غير محدد' }}</td>
                                <td class="border p-2">{{ $plan['total_days'] ?? 'غير محدد' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
