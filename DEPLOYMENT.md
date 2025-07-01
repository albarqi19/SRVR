# دليل النشر على Railway

## الخطوات المطلوبة لنشر المشروع على Railway

### 1. إعداد المشروع على GitHub

```bash
# في مجلد المشروع
git init
git add .
git commit -m "Initial commit: GARB Quran Circles Management System"
git branch -M main
git remote add origin https://github.com/albarqi19/SRVR.git
git push -u origin main
```

### 2. إعداد Railway

1. اذهب إلى [Railway.app](https://railway.app)
2. قم بإنشاء حساب جديد أو تسجيل الدخول
3. انقر على "New Project"
4. اختر "Deploy from GitHub repo"
5. اختر المستودع `albarqi19/SRVR`

### 3. إضافة خدمة قاعدة البيانات

1. في لوحة تحكم Railway، انقر على "Add Service"
2. اختر "Database" > "MySQL"
3. انتظر حتى يتم إنشاء قاعدة البيانات

### 4. إعداد متغيرات البيئة

في تبويب "Variables" للمشروع الرئيسي، أضف المتغيرات التالية:

#### متغيرات التطبيق الأساسية:
```
APP_NAME=GARB Project
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
```

#### متغيرات قاعدة البيانات (ستتم إضافتها تلقائياً من خدمة MySQL):
```
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

#### متغيرات إضافية:
```
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
LOG_CHANNEL=errorlog
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
APP_FAKER_LOCALE=ar_SA
BCRYPT_ROUNDS=12
```

### 5. إعداد الدومين (اختياري)

1. في تبويب "Settings"
2. انقر على "Domains"
3. أضف دومين مخصص أو استخدم الدومين المؤقت

### 6. تشغيل Migration بعد النشر

بعد نجاح النشر، قم بتشغيل الأوامر التالية في Railway CLI أو عبر لوحة التحكم:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. إنشاء أول مستخدم مدير

```bash
php artisan make:filament-user
```

## روابط مهمة بعد النشر

- **لوحة التحكم الرئيسية**: `https://your-domain.railway.app/admin`
- **واجهة المعلمين**: `https://your-domain.railway.app/teacher`
- **API Documentation**: `https://your-domain.railway.app/api/documentation`

## نصائح مهمة

### الأمان:
- تأكد من تعطيل `APP_DEBUG` في الإنتاج
- استخدم كلمات مرور قوية
- فعّل HTTPS دائماً

### الأداء:
- استخدم `php artisan config:cache` للتحسين
- راقب استخدام الذاكرة والمعالج
- استخدم CDN للملفات الثابتة إذا أمكن

### النسخ الاحتياطي:
- قم بعمل نسخ احتياطية دورية لقاعدة البيانات
- احتفظ بنسخة من ملفات التكوين

## استكشاف الأخطاء

### مشاكل شائعة:

1. **خطأ في الاتصال بقاعدة البيانات**:
   - تحقق من متغيرات البيئة
   - تأكد من أن خدمة MySQL تعمل

2. **خطأ في APP_KEY**:
   ```bash
   php artisan key:generate --show
   ```

3. **مشاكل في الملفات المرفوعة**:
   ```bash
   php artisan storage:link
   ```

4. **بطء في التحميل**:
   ```bash
   php artisan optimize
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## الدعم

للحصول على المساعدة:
- راجع وثائق Railway: https://docs.railway.app
- راجع وثائق Laravel: https://laravel.com/docs
- تواصل معنا عبر GitHub Issues
