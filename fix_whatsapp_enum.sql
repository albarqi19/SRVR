-- تنظيف البيانات الموجودة
TRUNCATE TABLE whatsapp_messages;

-- تحديث enum ليشمل جميع القيم المطلوبة
ALTER TABLE whatsapp_messages 
MODIFY COLUMN message_type ENUM(
    'notification', 
    'command', 
    'response', 
    'reminder', 
    'attendance', 
    'custom', 
    'session', 
    'alert'
) COMMENT 'نوع الرسالة';

-- عرض بنية الجدول للتأكد
DESCRIBE whatsapp_messages;
