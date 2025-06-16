// أفكار الذكاء الاصطناعي المقترحة
const aiIdeas = [
    "نظام توصية خطط الحفظ المخصصة لكل طالب.",
    "تنبؤ بمستوى تقدم الطلاب وتقديم تدخلات مبكرة.",
    "توصية المعلم الأنسب لكل طالب بناءً على أسلوب التعلم.",
    "تصحيح التلاوة الآلي باستخدام الذكاء الاصطناعي.",
    "تحليل أخطاء القراءة الشائعة وتقديم تدريبات مخصصة.",
    "محرك بحث ذكي للآيات والتفسير.",
    "توزيع المعلمين والمشرفين الأمثل حسب المهارات والقرب الجغرافي.",
    "جدولة المواعيد الذكية للاختبارات والاجتماعات.",
    "مساعد افتراضي (شات بوت) للرد على الاستفسارات.",
    "تحليل المشاعر في التغذية الراجعة للطلاب والمعلمين.",
    "توليد التقارير الدورية آلياً مع إبراز النقاط المهمة.",
    "لوحات تحكم تحليلية متقدمة لمؤشرات الأداء.",
    "التنبؤ بمعدلات النجاح والتخرج.",
    "اكتشاف الأنماط غير المرئية في بيانات الطلاب والمعلمين.",
    "نظام ذكي للتنبؤ بالميزانية واحتياجات التمويل.",
    "اكتشاف الاحتيال المالي وتحسين تخصيص الموارد.",
    "استهداف المانحين والمتبرعين المحتملين.",
    "تحسين محتوى وسائل التواصل الاجتماعي تلقائياً.",
    "قياس تأثير الحملات التسويقية وتوجيه الاستثمار.",
    "نماذج محاكاة لافتتاح حلقات جديدة وتحليل المخاطر والفرص."
];

// ويدجات إحصائية وهمية
const widgets = [
    { icon: '<i class="fas fa-brain"></i>', stat: '92%', desc: 'دقة توصية خطط الحفظ' },
    { icon: '<i class="fas fa-microphone-alt"></i>', stat: '1,250', desc: 'جلسة تصحيح تلاوة آلي' },
    { icon: '<i class="fas fa-user-check"></i>', stat: '37', desc: 'معلم تم اقتراحه تلقائياً' },
    { icon: '<i class="fas fa-robot"></i>', stat: '4,800', desc: 'ردود مساعد افتراضي' },
    { icon: '<i class="fas fa-chart-bar"></i>', stat: '6', desc: 'تقارير تحليلية ذكية' },
    { icon: '<i class="fas fa-graduation-cap"></i>', stat: '89%', desc: 'دقة تنبؤات النجاح' },
    { icon: '<i class="fas fa-search"></i>', stat: '1,420', desc: 'بحث ذكي للآيات' },
    { icon: '<i class="fas fa-coins"></i>', stat: '15%', desc: 'توفير في الميزانية' }
];

// بيانات أداء الطلاب
const studentPerformanceData = {
    weekly: {
        labels: ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'],
        datasets: [
            {
                label: 'معدل الحفظ',
                data: [65, 59, 80, 81, 56, 55, 70],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'معدل المراجعة',
                data: [45, 70, 65, 75, 60, 68, 78],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    monthly: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
        datasets: [
            {
                label: 'معدل الحفظ',
                data: [65, 59, 80, 81, 56, 55, 70, 68, 72, 76, 82, 85],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'معدل المراجعة',
                data: [45, 70, 65, 75, 60, 68, 78, 73, 80, 82, 85, 90],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    yearly: {
        labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
        datasets: [
            {
                label: 'معدل الحفظ',
                data: [50, 55, 60, 68, 75, 82],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'معدل المراجعة',
                data: [40, 48, 58, 65, 73, 85],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    }
};

// بيانات توزيع مستويات الحفظ
const memorizationDistributionData = {
    labels: [
        'مبتدئ (جزء واحد)',
        'متوسط (1-5 أجزاء)',
        'متقدم (6-15 جزء)',
        'متميز (16-25 جزء)',
        'حافظ (30 جزء)'
    ],
    datasets: [{
        data: [30, 40, 15, 10, 5],
        backgroundColor: [
            '#94a3b8',
            '#60a5fa',
            '#38bdf8',
            '#22d3ee',
            '#2563eb'
        ],
        borderWidth: 0
    }]
};

window.addEventListener('DOMContentLoaded', () => {
    // تعيين التاريخ الحالي
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // تعبئة الأفكار
    const ideasList = document.getElementById('ai-ideas-list');
    aiIdeas.forEach(idea => {
        const li = document.createElement('li');
        li.innerHTML = `<span><i class="fas fa-lightbulb"></i></span> ${idea}`;
        ideasList.appendChild(li);
    });

    // تعبئة الويدجات
    const widgetsSection = document.querySelector('.widgets');
    widgets.forEach(w => {
        const div = document.createElement('div');
        div.className = 'widget';
        div.innerHTML = `
            <div class="icon">${w.icon}</div>
            <div class="stat">${w.stat}</div>
            <div class="desc">${w.desc}</div>
            <button onclick="showWidgetDetails('${w.desc}')">تجربة تفاعل</button>
        `;
        widgetsSection.appendChild(div);
    });

    // إنشاء الرسم البياني لأداء الطلاب
    const performanceChart = createStudentPerformanceChart('monthly');
    
    // تفاعل اختيار فترة الرسم البياني
    document.getElementById('chart-period-selector').addEventListener('change', function() {
        updateStudentPerformanceChart(performanceChart, this.value);
    });

    // إنشاء الرسم البياني الدائري لتوزيع مستويات الحفظ
    createMemorizationDistributionChart();

    // تفاعل مشغل التلاوة
    const playerButton = document.querySelector('.player-button');
    playerButton.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (icon.classList.contains('fa-play')) {
            icon.classList.remove('fa-play');
            icon.classList.add('fa-pause');
        } else {
            icon.classList.remove('fa-pause');
            icon.classList.add('fa-play');
        }
    });

    // تفاعل عناصر القائمة
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // تفاعلات عناصر التوصية
    const actionButtons = document.querySelectorAll('.recommendation-actions button');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recommendationItem = this.closest('.recommendation-item');
            if (this.classList.contains('secondary')) {
                recommendationItem.style.opacity = '0.6';
                setTimeout(() => {
                    recommendationItem.style.display = 'none';
                }, 500);
            } else {
                alert('سيتم تطبيق التوصية قريباً');
            }
        });
    });

    // تفاعل اختيار الطالب للمطابقة
    document.getElementById('student-match').addEventListener('change', function() {
        // هنا يمكن إضافة تحديث حقيقي للبيانات، نكتفي بتنبيه تجريبي
        alert('تم تحديث قائمة المعلمين المناسبين للطالب ' + this.options[this.selectedIndex].text);
    });

    // بدء محاكاة البيانات في الوقت الفعلي
    setInterval(simulateRealTimeData, 5000);

    // استدعاء دالة تهيئة نظام الإشعارات
    initNotificationSystem();
});

// دالة عرض تفاصيل الويدجت
function showWidgetDetails(desc) {
    // يمكن هنا إضافة منطق مختلف لكل ويدجت، نكتفي بتنبيه بسيط
    alert(`معلومات تفصيلية عن ${desc}`);
}

// دالة إنشاء الرسم البياني لأداء الطلاب
function createStudentPerformanceChart(period) {
    const ctx = document.getElementById('student-performance').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: studentPerformanceData[period],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        fontFamily: 'IBM Plex Sans Arabic'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    titleColor: '#1e293b',
                    bodyColor: '#1e293b',
                    bodyFont: {
                        family: 'Tajawal'
                    },
                    titleFont: {
                        family: 'Tajawal',
                        weight: 'bold'
                    },
                    borderWidth: 1,
                    borderColor: '#e2e8f0',
                    rtl: true,
                    padding: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        font: {
                            family: 'Tajawal'
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: 'Tajawal'
                        }
                    }
                }
            }
        }
    });
    return chart;
}

// دالة تحديث الرسم البياني حسب الفترة المختارة
function updateStudentPerformanceChart(chart, period) {
    chart.data = studentPerformanceData[period];
    chart.update();
}

// دالة إنشاء الرسم البياني الدائري لتوزيع مستويات الحفظ
function createMemorizationDistributionChart() {
    const ctx = document.getElementById('memorization-distribution').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: memorizationDistributionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            family: 'Tajawal',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    titleColor: '#1e293b',
                    bodyColor: '#1e293b',
                    bodyFont: {
                        family: 'Tajawal'
                    },
                    titleFont: {
                        family: 'Tajawal',
                        weight: 'bold'
                    },
                    borderWidth: 1,
                    borderColor: '#e2e8f0',
                    rtl: true,
                    padding: 10
                }
            },
            cutout: '65%'
        }
    });
}

// دالة محاكاة البيانات في الوقت الفعلي
function simulateRealTimeData() {
    // تحديث عشوائي لبيانات الرسم البياني
    const charts = Chart.instances;
    for (let i = 0; i < charts.length; i++) {
        const chart = charts[i];
        if (chart.config.type === 'line') {
            chart.data.datasets.forEach(dataset => {
                // تغيير عشوائي بسيط في البيانات
                dataset.data = dataset.data.map(value => {
                    const change = (Math.random() - 0.5) * 5;
                    const newValue = Math.max(Math.min(value + change, 100), 0);
                    return newValue;
                });
            });
            chart.update('none'); // تحديث بدون حركة للأداء الأفضل
        }
    }

    // تحديث أرقام الإحصائيات
    const widgets = document.querySelectorAll('.widget .stat');
    widgets.forEach(widget => {
        const text = widget.textContent;
        if (text.includes('%')) {
            const value = parseFloat(text);
            const newValue = Math.max(Math.min(value + (Math.random() - 0.5) * 2, 100), 0);
            widget.textContent = newValue.toFixed(1) + '%';
        } else if (!isNaN(parseInt(text))) {
            const value = parseInt(text.replace(/,/g, ''));
            const newValue = value + Math.floor((Math.random() - 0.3) * 10);
            widget.textContent = newValue.toLocaleString();
        }
    });

    // إضافة تنبيه جديد عشوائي بين الحين والآخر
    if (Math.random() > 0.9) {
        addRandomAlert();
    }
}

// دالة إضافة تنبيه عشوائي
function addRandomAlert() {
    const alertMessages = [
        "تم اكتشاف تحسن ملحوظ في أداء الطالب أحمد محمد",
        "توصية جديدة: إعادة توزيع المعلمين للفترة المسائية",
        "تنبيه: 5 طلاب لم يحضروا للمراجعة اليوم",
        "تحليل تلاوة جديد جاهز لمراجعة المعلم",
        "اكتشاف نمط تعلم جديد لدى 12 طالب من المستوى المتوسط"
    ];
    
    const alertContainer = document.querySelector('.alerts-container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = 'alert new-alert';
    alert.innerHTML = `
        <div class="alert-icon"><i class="fas fa-bell"></i></div>
        <div class="alert-content">${alertMessages[Math.floor(Math.random() * alertMessages.length)]}</div>
        <div class="alert-time">${new Date().toLocaleTimeString('ar-SA')}</div>
        <div class="alert-close"><i class="fas fa-times"></i></div>
    `;
    
    alertContainer.prepend(alert);
    setTimeout(() => alert.classList.remove('new-alert'), 100);
    
    // إضافة تفاعل إغلاق التنبيه
    alert.querySelector('.alert-close').addEventListener('click', function() {
        alert.classList.add('removing');
        setTimeout(() => alert.remove(), 300);
    });
    
    // إزالة التنبيهات القديمة إذا زادت عن 5
    const existingAlerts = alertContainer.querySelectorAll('.alert');
    if (existingAlerts.length > 5) {
        for (let i = 5; i < existingAlerts.length; i++) {
            existingAlerts[i].remove();
        }
    }
}

// تفعيل تفاعل زر الإشعارات
function initNotificationSystem() {
    const notificationBell = document.getElementById('notification-bell');
    const badge = notificationBell.querySelector('.badge');
    const markAllReadBtn = document.querySelector('.mark-all-read');
    
    // إضافة تفاعل زر تعيين الكل كمقروء
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.add('removing');
                setTimeout(() => alert.remove(), 300);
            });
            badge.textContent = '0';
            badge.classList.remove('pulse');
        });
    }

    // تحديث عدد الإشعارات
    function updateNotificationCount() {
        const alertCount = document.querySelectorAll('.alert').length;
        badge.textContent = alertCount;
        if (alertCount > 0) {
            badge.classList.add('pulse');
        } else {
            badge.classList.remove('pulse');
        }
    }

    // إضافة بعض التنبيهات الأولية
    const initialAlerts = [
        "تم اكتشاف نمط تعلم جديد لدى 3 طلاب",
        "توصية: تعديل جدول المراجعة للطلاب المميزين",
        "تحليل: ارتفاع بنسبة 15% في أداء حلقة الشيخ أحمد"
    ];
    
    const alertContainer = document.querySelector('.alerts-container');
    if (alertContainer) {
        initialAlerts.forEach(alertText => {
            const alert = document.createElement('div');
            alert.className = 'alert';
            alert.innerHTML = `
                <div class="alert-icon"><i class="fas fa-bell"></i></div>
                <div class="alert-content">${alertText}</div>
                <div class="alert-time">${new Date().toLocaleTimeString('ar-SA')}</div>
                <div class="alert-close"><i class="fas fa-times"></i></div>
            `;
            
            alert.querySelector('.alert-close').addEventListener('click', function(e) {
                e.stopPropagation();
                alert.classList.add('removing');
                setTimeout(() => {
                    alert.remove();
                    updateNotificationCount();
                }, 300);
            });
            
            alertContainer.appendChild(alert);
        });
        
        updateNotificationCount();
    }

    // تحديث عدد الإشعارات كلما تم إضافة تنبيه جديد
    const originalAddRandomAlert = addRandomAlert;
    addRandomAlert = function() {
        originalAddRandomAlert();
        updateNotificationCount();
    };
}
