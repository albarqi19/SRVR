<x-filament-panels::page>
    <div class="curriculum-builder-container">
        <style>
            .curriculum-builder-container {
                direction: rtl;
                text-align: right;
            }
            
            .curriculum-section {
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1rem;
                background: #f9fafb;
            }
            
            .curriculum-section h3 {
                margin: 0 0 1rem 0;
                color: #374151;
                font-weight: 600;
                font-size: 1.1rem;
            }
            
            .section-memorization {
                border-left: 4px solid #10b981;
            }
            
            .section-minor-review {
                border-left: 4px solid #3b82f6;
            }
            
            .section-major-review {
                border-left: 4px solid #8b5cf6;
            }
            
            .curriculum-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1rem;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            .curriculum-table th,
            .curriculum-table td {
                padding: 0.75rem;
                text-align: center;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .curriculum-table th {
                background: #f3f4f6;
                font-weight: 600;
                color: #374151;
            }
            
            .memorization-header {
                background: #ecfdf5 !important;
                color: #065f46 !important;
            }
            
            .minor-review-header {
                background: #eff6ff !important;
                color: #1e40af !important;
            }
            
            .major-review-header {
                background: #f3e8ff !important;
                color: #6b21a8 !important;
            }
            
            .action-buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
                margin-top: 2rem;
                flex-wrap: wrap;
            }
            
            .excel-like-input {
                width: 100%;
                padding: 0.5rem;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                text-align: center;
                font-size: 0.875rem;
            }
            
            .excel-like-input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 1px #3b82f6;
            }
            
            .help-text {
                font-size: 0.875rem;
                color: #6b7280;
                margin-top: 1rem;
                padding: 1rem;
                background: #f9fafb;
                border-radius: 6px;
                border-right: 4px solid #3b82f6;
            }
            
            .stats-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 2rem;
            }
            
            .stat-card {
                background: white;
                padding: 1.5rem;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            
            .stat-number {
                font-size: 2rem;
                font-weight: bold;
                color: #374151;
            }
            
            .stat-label {
                color: #6b7280;
                font-size: 0.875rem;
                margin-top: 0.5rem;
            }
            
            @media (max-width: 768px) {
                .action-buttons {
                    flex-direction: column;
                    align-items: center;
                }
                
                .curriculum-table {
                    font-size: 0.875rem;
                }
                
                .curriculum-table th,
                .curriculum-table td {
                    padding: 0.5rem;
                }
            }
        </style>
        
        <div class="help-text">
            <h4 style="margin: 0 0 0.5rem 0; color: #374151;">📋 تعليمات الاستخدام:</h4>
            <ul style="margin: 0; padding-right: 1.5rem;">
                <li>استخدم هذه الواجهة لإدخال المنهج اليومي للطلاب</li>
                <li>يمكنك تفعيل أو إلغاء تفعيل كل قسم (الحفظ، المراجعة الصغرى، المراجعة الكبرى)</li>
                <li>اختر اسم السورة من القائمة المنسدلة</li>
                <li>أدخل أرقام الآيات (من - إلى)</li>
                <li>يمكنك إضافة أيام متعددة باستخدام زر "إضافة يوم جديد"</li>
                <li>احفظ المنهج أو صدره إلى Excel للمشاركة</li>
            </ul>
        </div>
        
        {{ $this->form }}
        
        <div class="help-text" style="margin-top: 2rem; border-right-color: #10b981;">
            <h4 style="margin: 0 0 0.5rem 0; color: #065f46;">💡 نصائح مفيدة:</h4>
            <ul style="margin: 0; padding-right: 1.5rem;">
                <li><strong>الحفظ:</strong> الآيات الجديدة التي سيحفظها الطالب</li>
                <li><strong>المراجعة الصغرى:</strong> مراجعة الآيات المحفوظة حديثاً</li>
                <li><strong>المراجعة الكبرى:</strong> مراجعة الآيات المحفوظة سابقاً</li>
                <li>يمكنك استخدام النسخ واللصق لنقل البيانات من وإلى Excel</li>
                <li>تأكد من صحة أرقام الآيات قبل الحفظ</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
