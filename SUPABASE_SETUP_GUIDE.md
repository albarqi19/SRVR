# دليل إعداد Supabase بسرعة

## 1. إنشاء حساب Supabase
1. اذهب إلى: https://supabase.com
2. سجل حساب جديد (مجاني)
3. انشئ مشروع جديد
4. اختر منطقة قريبة (Frankfurt للمنطقة العربية)

## 2. الحصول على بيانات الاتصال
بعد إنشاء المشروع، ستحصل على:

```env
# معلومات الاتصال (ستجدها في Settings > Database)
DB_CONNECTION=pgsql
DB_HOST=db.xyz.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password
```

## 3. السماح بالاتصالات الخارجية
- في Supabase Dashboard
- اذهب إلى Settings > Database
- ابحث عن "Connection pooling"
- فعّل "Enable connection pooling"

## 4. نسخ string الاتصال الكامل
```
postgresql://postgres.xyz:password@aws-0-eu-central-1.pooler.supabase.com:6543/postgres
```

## ملاحظات مهمة:
- استخدم Connection Pooling دائماً للأداء الأفضل
- احتفظ بكلمة المرور في مكان آمن
- يمكنك تغيير كلمة المرور لاحقاً من Dashboard
