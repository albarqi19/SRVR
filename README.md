# GARB - نظام إدارة حلقات القرآن الكريم

نظام شامل لإدارة حلقات تحفيظ القرآن الكريم في المساجد، مبني على Laravel مع واجهة إدارية متقدمة باستخدام Filament.

## الميزات الرئيسية

- **إدارة شاملة للحلقات**: تنظيم حلقات التحفيظ والمعلمين والطلاب
- **نظام متابعة التقدم**: تتبع تقدم الطلاب في حفظ القرآن الكريم
- **إدارة الحضور والغياب**: نظام متطور لتسجيل ومتابعة الحضور
- **نظام التقييم**: تقييم الطلاب وتسجيل درجاتهم
- **تقارير مفصلة**: تقارير شاملة عن أداء الطلاب والحلقات
- **واجهة إدارية متقدمة**: لوحة تحكم سهلة الاستخدام مع Filament
- **دعم الهواتف الذكية**: واجهة متجاوبة تعمل على جميع الأجهزة
- **نظام أذونات متقدم**: إدارة صلاحيات المستخدمين حسب الأدوار

## متطلبات النظام

- PHP 8.1 أو أحدث
- MySQL 8.0 أو أحدث
- Composer
- Node.js & NPM

## التثبيت

1. استنساخ المشروع:
```bash
git clone https://github.com/albarqi19/SRVR.git
cd SRVR
```

2. تثبيت التبعيات:
```bash
composer install
npm install && npm run build
```

3. إعداد ملف البيئة:
```bash
cp .env.example .env
php artisan key:generate
```

4. إعداد قاعدة البيانات:
```bash
php artisan migrate --seed
```

5. إنشاء المستخدم الأول:
```bash
php artisan make:filament-user
```

## النشر على Railway

### 1. إعداد متغيرات البيئة في Railway:

```
APP_NAME="GARB Project"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-railway-domain.up.railway.app

DB_CONNECTION=mysql
DB_HOST=containers-us-west-xyz.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=YOUR_DB_PASSWORD

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

### 2. إعداد قاعدة البيانات:
- أضف خدمة MySQL في Railway
- احفظ تفاصيل الاتصال في متغيرات البيئة

### 3. نشر المشروع:
```bash
git push origin main
```

## الاستخدام

### الوصول للوحة التحكم:
- الرابط: `https://your-domain.com/admin`
- المستخدم الافتراضي: admin@example.com
- كلمة المرور: password

### الواجهات الرئيسية:
- **/admin**: لوحة تحكم المدراء
- **/teacher**: واجهة المعلمين
- **/api**: واجهة برمجة التطبيقات

## هيكل المشروع

```
├── app/
│   ├── Filament/          # ملفات Filament Admin Panel
│   ├── Models/            # نماذج البيانات
│   ├── Http/              # Controllers & Middleware
│   └── Services/          # خدمات النظام
├── database/
│   ├── migrations/        # ملفات الهجرة
│   └── seeders/          # بيانات الاختبار
├── resources/
│   ├── views/            # ملفات العرض
│   └── lang/             # ملفات الترجمة
└── public/               # الملفات العامة
```

## المساهمة

نرحب بمساهماتكم في تطوير النظام. يرجى:

1. عمل Fork للمشروع
2. إنشاء فرع جديد للميزة
3. إجراء التغييرات المطلوبة
4. إرسال Pull Request

## الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

## الدعم

للحصول على المساعدة أو الإبلاغ عن المشاكل، يرجى:
- فتح Issue في GitHub
- التواصل عبر البريد الإلكتروني: support@garb-project.com

---

تم تطوير هذا النظام بعناية لخدمة بيوت الله وتسهيل تحفيظ كتاب الله الكريم.
