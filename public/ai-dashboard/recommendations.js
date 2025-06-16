// بيانات توزيع التوصيات حسب النوع
const recommendationTypeData = {
    labels: [
        'توصيات الطلاب',
        'توصيات المعلمين',
        'توصيات إدارية',
        'توصيات مالية',
        'توصيات تقنية'
    ],
    datasets: [{
        data: [42, 28, 15, 10, 5],
        backgroundColor: [
            '#3b82f6',
            '#06b6d4',
            '#8b5cf6',
            '#22c55e',
            '#f59e0b'
        ],
        borderWidth: 0
    }]
};

// بيانات معدل تطبيق التوصيات
const adoptionRateData = {
    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو'],
    datasets: [{
        label: 'النسبة المئوية للتوصيات المطبقة',
        data: [45, 58, 65, 75, 87],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
    }]
};

// بيانات تقدم الطالب عمر
const studentOmarData = {
    labels: ['أسبوع 1', 'أسبوع 2', 'أسبوع 3', 'أسبوع 4', 'أسبوع 5', 'أسبوع 6'],
    datasets: [{
        label: 'الأداء الفعلي',
        data: [3, 3.2, 2.8, 3.1, 3, 3.2],
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245, 158, 11, 0)',
        borderWidth: 2,
        pointBackgroundColor: '#f59e0b'
    }, {
        label: 'الأداء المتوقع مع الخطة الجديدة',
        data: [3, 3.5, 4, 4.5, 5, 5.5],
        borderColor: '#22c55e',
        backgroundColor: 'rgba(34, 197, 94, 0)',
        borderWidth: 2,
        borderDash: [5, 5],
        pointBackgroundColor: '#22c55e'
    }]
};

// بيانات إعادة توزيع المجموعات
const groupsRedistributionData = {
    labels: ['حلقة 1', 'حلقة 2', 'حلقة 3', 'حلقة 4'],
    datasets: [{
        label: 'التوزيع الحالي',
        data: [28, 15, 35, 12],
        backgroundColor: 'rgba(59, 130, 246, 0.7)',
    }, {
        label: 'التوزيع المقترح',
        data: [22, 20, 25, 23],
        backgroundColor: 'rgba(34, 197, 94, 0.7)',
    }]
};

// بيانات مهارات المعلمين
const teacherSkillsData = {
    labels: [
        'مهارات التدريس',
        'التجويد',
        'مهارات التواصل',
        'استخدام التكنولوجيا',
        'تحفيز الطلاب',
        'إدارة الصف'
    ],
    datasets: [{
        label: 'متوسط المهارات الحالية',
        data: [65, 75, 60, 45, 70, 55],
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 2,
        pointBackgroundColor: '#3b82f6'
    }, {
        label: 'المستوى المطلوب',
        data: [80, 85, 75, 70, 85, 75],
        backgroundColor: 'rgba(245, 158, 11, 0.2)',
        borderColor: 'rgba(245, 158, 11, 1)',
        borderWidth: 2,
        pointBackgroundColor: '#f59e0b'
    }]
};

// بيانات الجدول الحالي والمقترح
const scheduleData = {
    current: {
        labels: ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'],
        datasets: [{
            label: 'متوسط ساعات العمل',
            data: [8, 7, 9, 6, 9, 4],
            backgroundColor: [
                'rgba(59, 130, 246, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(34, 197, 94, 0.7)'
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(239, 68, 68, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(239, 68, 68, 1)',
                'rgba(34, 197, 94, 1)'
            ],
            borderWidth: 1
        }]
    },
    proposed: {
        labels: ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'],
        datasets: [{
            label: 'متوسط ساعات العمل',
            data: [7, 7, 7, 7, 8, 6],
            backgroundColor: [
                'rgba(59, 130, 246, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(59, 130, 246, 0.7)'
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(59, 130, 246, 1)'
            ],
            borderWidth: 1
        }]
    }
};

// بيانات تنبؤ أداء المعلمين
const teacherPredictionData = {
    labels: ['يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر'],
    datasets: [{
        label: 'الأداء المتوقع',
        data: [75, 78, 80, 83, 85, 88],
        borderColor: '#22c55e',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        tension: 0.4,
        fill: true
    }, {
        label: 'الهدف',
        data: [80, 80, 80, 85, 85, 85],
        borderColor: '#3b82f6',
        borderDash: [5, 5],
        backgroundColor: 'rgba(0, 0, 0, 0)',
        tension: 0.1
    }]
};

// بيانات تحسين الميزانية
const budgetOptimizationData = {
    labels: [
        'الرواتب والمكافآت',
        'المواد التعليمية',
        'تكنولوجيا المعلومات',
        'المرافق والصيانة',
        'الأنشطة الطلابية',
        'المصاريف الإدارية',
        'التدريب والتطوير'
    ],
    datasets: [{
        label: 'الميزانية الحالية',
        data: [350000, 80000, 120000, 95000, 40000, 75000, 40000],
        backgroundColor: 'rgba(59, 130, 246, 0.7)'
    }, {
        label: 'الميزانية المقترحة',
        data: [350000, 57500, 85000, 95000, 40000, 56800, 40000],
        backgroundColor: 'rgba(34, 197, 94, 0.7)'
    }]
};

window.addEventListener('DOMContentLoaded', () => {
    // تعيين التاريخ الحالي
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // إنشاء مخططات الرسوم البيانية
    createRecommendationTypeChart();
    createAdoptionRateChart();
    createStudentOmarProgressChart();
    createGroupsRedistributionChart();
    createTeacherSkillsChart();
    createScheduleCharts();
    createTeacherPredictionChart();
    createBudgetOptimizationChart();

    // تفعيل التبويبات
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.dataset.tab;
            
            // إزالة الفئة النشطة من جميع التبويبات
            tabs.forEach(t => t.classList.remove('active'));
            
            // إضافة الفئة النشطة إلى التبويب المحدد
            this.classList.add('active');
            
            // إخفاء جميع المحتويات
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // إظهار المحتوى المرتبط بالتبويب المحدد
            document.getElementById(targetId).classList.remove('hidden');
        });
    });

    // تفعيل فلترة التوصيات
    document.getElementById('recommendation-filter').addEventListener('change', function() {
        const filterValue = this.value;
        alert(`تم تطبيق فلتر: ${filterValue}`);
        // هنا يمكن إضافة منطق التصفية الفعلي
    });

    // تفاعلات أزرار التوصية
    document.querySelectorAll('.recommendation-actions button').forEach(button => {
        button.addEventListener('click', function() {
            const actionText = this.textContent.trim();
            alert(`سيتم تنفيذ الإجراء: ${actionText}`);
        });
    });
});

// دالة إنشاء الرسم البياني لأنواع التوصيات
function createRecommendationTypeChart() {
    const ctx = document.getElementById('recommendations-by-type').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: recommendationTypeData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
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
            cutout: '60%'
        }
    });
}

// دالة إنشاء الرسم البياني لمعدل تطبيق التوصيات
function createAdoptionRateChart() {
    const ctx = document.getElementById('recommendation-adoption-rate').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: adoptionRateData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
                        callback: function(value) {
                            return value + '%';
                        },
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
}

// دالة إنشاء الرسم البياني لتقدم الطالب عمر
function createStudentOmarProgressChart() {
    const ctx = document.getElementById('student-omar-progress').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: studentOmarData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Tajawal'
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
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw + ' صفحات';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 7,
                    title: {
                        display: true,
                        text: 'عدد الصفحات أسبوعيًا',
                        font: {
                            family: 'Tajawal',
                            size: 14
                        }
                    },
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
}

// دالة إنشاء الرسم البياني لإعادة توزيع المجموعات
function createGroupsRedistributionChart() {
    const ctx = document.getElementById('groups-redistribution').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: groupsRedistributionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Tajawal'
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
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw + ' طالب';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
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
}

// دالة إنشاء الرسم البياني لمهارات المعلمين
function createTeacherSkillsChart() {
    const ctx = document.getElementById('teacher-skills').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: teacherSkillsData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        display: false
                    },
                    pointLabels: {
                        font: {
                            family: 'Tajawal'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Tajawal'
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
            }
        }
    });
}

// دالة إنشاء الرسوم البيانية للجدول الحالي والمقترح
function createScheduleCharts() {
    const currentCtx = document.getElementById('current-schedule').getContext('2d');
    new Chart(currentCtx, {
        type: 'bar',
        data: scheduleData.current,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return 'ساعات العمل: ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
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

    const proposedCtx = document.getElementById('proposed-schedule').getContext('2d');
    new Chart(proposedCtx, {
        type: 'bar',
        data: scheduleData.proposed,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return 'ساعات العمل: ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
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
}

// دالة إنشاء الرسم البياني لتنبؤ أداء المعلمين
function createTeacherPredictionChart() {
    const ctx = document.getElementById('teacher-performance-prediction')?.getContext('2d');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: teacherPredictionData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Tajawal'
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
    }
}

// دالة إنشاء الرسم البياني لتحسين الميزانية
function createBudgetOptimizationChart() {
    const ctx = document.getElementById('budget-optimization')?.getContext('2d');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: budgetOptimizationData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Tajawal'
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
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString('ar-SA') + ' ريال';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) {
                                    return value / 1000 + ' ألف';
                                }
                                return value;
                            },
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
    }
}
