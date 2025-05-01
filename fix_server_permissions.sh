#!/bin/bash

# إصلاح صلاحيات مجلد المشروع
echo "إصلاح صلاحيات مجلد المشروع..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# تعيين صلاحيات خاصة للملفات التي تحتاج إلى تنفيذ
echo "تعيين صلاحيات التنفيذ للملفات المهمة..."
chmod +x artisan
chmod +x *.sh

# إعطاء صلاحيات الكتابة للمجلدات التي تحتاج إلى كتابة
echo "تعيين صلاحيات الكتابة للمجلدات المهمة..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# تعيين مالك الملفات (استبدل www-data بمستخدم الويب سيرفر الخاص بك، مثل nginx أو apache)
# في معظم خوادم Laravel Forge، يكون المستخدم هو forge
echo "تعيين مالك الملفات..."
chown -R forge:forge .

# إعادة تحميل خدمة الويب سيرفر
echo "إعادة تحميل خدمة الويب سيرفر..."
# استخدم واحدة من هذه الأوامر اعتماداً على نوع الويب سيرفر المستخدم
# sudo systemctl reload nginx
# sudo systemctl reload apache2

echo "تم الانتهاء من إصلاح الصلاحيات!"