const express = require('express');
const app = express();
const port = 3000;

// Serve static files
app.use(express.static('public'));

// Basic route
app.get('/', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html>
    <head>
        <title>نظام إدارة المراكز - API Interface</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; direction: rtl; text-align: center; padding: 50px; }
            .container { max-width: 800px; margin: 0 auto; }
            .card { background: #f8f9fa; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; }
            .api-link { background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>🕌 نظام إدارة مراكز تحفيظ القرآن الكريم</h1>
                <h2>واجهة API</h2>
                <p>مرحباً بك في واجهة API للنظام</p>
                
                <h3>روابط API المتاحة:</h3>
                <a href="/api/students" class="api-link">قائمة الطلاب</a>
                <a href="/api/teachers" class="api-link">قائمة المعلمين</a>
                <a href="/api/recitation-sessions" class="api-link">جلسات التسميع</a>
                <a href="/api/mosques" class="api-link">المساجد</a>
                
                <p style="margin-top: 30px;">
                    <strong>Laravel API Server:</strong> 
                    <a href="https://inviting-pleasantly-barnacle.ngrok-free.app" target="_blank">
                        https://inviting-pleasantly-barnacle.ngrok-free.app
                    </a>
                </p>
            </div>
        </div>
    </body>
    </html>
  `);
});

// API routes that proxy to Laravel
app.get('/api/*', (req, res) => {
  res.json({
    message: "This is a proxy interface. Please use the Laravel API directly:",
    laravel_api: "https://inviting-pleasantly-barnacle.ngrok-free.app" + req.path,
    method: req.method,
    path: req.path
  });
});

app.listen(port, () => {
  console.log(\`🚀 Node.js server running at http://localhost:\${port}\`);
  console.log(\`🔗 Available via ngrok at: https://amusing-premium-jennet.ngrok-free.app\`);
});
