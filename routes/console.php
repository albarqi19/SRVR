<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// تسجيل commands نظام التحديث التلقائي للمناهج
Artisan::command('curriculum:daily-tasks {--evaluate-students} {--send-notifications} {--send-reminders} {--daily-reports} {--all}', function () {
    \Illuminate\Support\Facades\Artisan::call('curriculum:daily-tasks-internal');
})->purpose('تشغيل المهام اليومية لنظام التحديث التلقائي للمناهج');

Artisan::command('curriculum:weekly-tasks {--weekly-reports} {--cleanup-alerts} {--performance-analysis} {--all}', function () {
    \Illuminate\Support\Facades\Artisan::call('curriculum:weekly-tasks-internal');
})->purpose('تشغيل المهام الأسبوعية لنظام التحديث التلقائي للمناهج');
