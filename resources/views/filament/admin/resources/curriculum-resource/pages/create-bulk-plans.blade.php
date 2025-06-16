<x-filament::page>
    <x-filament::section>        <h2 class="text-xl font-semibold mb-4">إضافة خطط تفصيلية للمنهج: {{ $curriculum->name }}</h2>
        
        @if($curriculum->type === 'منهج طالب')
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <p class="font-medium text-yellow-700 mb-2">تنبيه - المستوى مطلوب:</p>
            <ul class="list-disc list-inside space-y-1 text-gray-700">
                <li>هذا المنهج من نوع "منهج طالب" ويحتوي على عدة مستويات</li>
                <li>يجب عليك اختيار المستوى الذي تريد إضافة الخطط له من القائمة المنسدلة</li>
                <li>سيتم إضافة جميع الخطط التي تقوم بإنشائها للمستوى المحدد</li>
            </ul>
        </div>
        @endif

        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <p class="font-medium text-blue-600 mb-2">طريقة الاستخدام:</p>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>قم بتنسيق بياناتك في ملف إكسل بحيث يكون لديك ثلاثة أعمدة (الدروس، المراجعة الصغرى، المراجعة الكبرى)</li>
                <li>انسخ محتوى كل عمود من الإكسل وقم بلصقه في الحقل المناسب أدناه</li>
                <li>كل سطر في الحقل يمثل خطة منفردة (مثلاً "الناس" أو "من 1 الى 6")</li>
                <li>يمكنك ترك أي من الحقول فارغاً إذا لم يكن لديك بيانات لذلك النوع من الخطط</li>
                <li>سيتم إنشاء الخطط الثلاثة (الدرس، المراجعة الصغرى، المراجعة الكبرى) المرتبطة بالمنهج</li>
            </ol>
        </div>
        
        <div class="mb-6">
            <div class="grid grid-cols-3 gap-4 bg-gray-50 p-2 border rounded-md mb-4">
                <div class="text-center font-semibold p-2 bg-primary-100 rounded">عمود الدروس</div>
                <div class="text-center font-semibold p-2 bg-warning-100 rounded">عمود المراجعة الصغرى</div>
                <div class="text-center font-semibold p-2 bg-success-100 rounded">عمود المراجعة الكبرى</div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mb-2 text-sm">
                <div class="border p-2 rounded">
                    <p class="font-bold">مثال:</p>
                    <p class="whitespace-pre">الناس
من 1 الى 6
الفلق
من 1 الى 5
الإخلاص</p>
                </div>
                <div class="border p-2 rounded">
                    <p class="font-bold">مثال:</p>
                    <p class="whitespace-pre">الناس-الفلق
الإخلاص-المسد
الفيل-قريش</p>
                </div>
                <div class="border p-2 rounded">
                    <p class="font-bold">مثال:</p>
                    <p class="whitespace-pre">الناس-الإخلاص
الكافرون-الفيل
العصر-الشرح</p>
                </div>
            </div>
        </div>
        
        {{ $this->form }}
        
        <div class="mt-6">
            <x-filament::button wire:click="create" color="primary" type="submit">
                إنشاء الخطط
            </x-filament::button>
              <x-filament::button tag="a" :href="App\Filament\Admin\Resources\CurriculumResource::getUrl('edit', ['record' => $curriculum])" color="gray">
                إلغاء
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament::page>
