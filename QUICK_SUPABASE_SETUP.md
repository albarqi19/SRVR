# إعداد Supabase - خطوات سريعة

## 1. إنشاء مشروع Supabase (5 دقائق):

1. اذهب إلى: https://supabase.com
2. سجل دخول بـ GitHub
3. "New Project"
4. اختر اسم: `garb-project` 
5. اختر منطقة: `Frankfurt` (أقرب للمنطقة العربية)
6. انتظر إنشاء المشروع (2-3 دقائق)

## 2. الحصول على بيانات الاتصال:

بعد إنشاء المشروع:
1. اذهب إلى Settings > Database
2. انسخ Connection info:

```
Host: db.xxx.supabase.co
Database name: postgres
Port: 5432
User: postgres
Password: [كلمة المرور التي أدخلتها]
```

## 3. Connection String للاستخدام:
```
postgresql://postgres.[project-ref]:[password]@aws-0-eu-central-1.pooler.supabase.com:6543/postgres
```

## 4. تفعيل Connection Pooling:
- في نفس الصفحة
- فعّل "Enable connection pooling"
- استخدم Session mode
- Port: 6543

## الخطوة التالية:
بعد الحصول على هذه المعلومات، ارجع لـ Railway وأدخلها في Environment Variables
