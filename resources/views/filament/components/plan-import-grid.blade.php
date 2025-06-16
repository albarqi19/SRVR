<div class="space-y-6">
    <div class="grid grid-cols-3 gap-4">
        <div>
            <h3 class="text-lg font-bold text-center border-b pb-2 mb-2">الدرس</h3>
            <div class="prose prose-sm" dir="rtl">
                {{ $slot }}
            </div>
        </div>
        <div>
            <h3 class="text-lg font-bold text-center border-b pb-2 mb-2">المراجعة الصغرى</h3>
        </div>
        <div>
            <h3 class="text-lg font-bold text-center border-b pb-2 mb-2">المراجعة الكبرى</h3>
        </div>
    </div>
</div>
