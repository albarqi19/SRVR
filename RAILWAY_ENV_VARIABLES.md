# متغيرات البيئة المطلوبة لـ Railway

## المتغيرات الأساسية (أضفها في Railway Dashboard > Variables):

### Laravel Basics
APP_NAME="منصة غرب لادارة حلقات القران الكريم"
APP_ENV=production
APP_KEY=base64:vq2Wca3DdkQKx9hvpsi54vp68zhvDamcmhiYSTMzY+o=
APP_DEBUG=false
APP_URL=https://YOUR-RAILWAY-URL.railway.app

### Database (Supabase) - سنملؤها بعد إعداد Supabase
DB_CONNECTION=pgsql
DB_HOST=db.xyz.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=YOUR_SUPABASE_PASSWORD

### Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

### Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

### Mail (للإشعارات)
MAIL_MAILER=log

## ملاحظات:
- APP_URL سيتم تحديثه بعد النشر
- DB_ variables سنحصل عليها من Supabase
- APP_KEY يجب أن يبقى كما هو (مهم للتشفير)
