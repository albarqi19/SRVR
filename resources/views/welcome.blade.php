<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GARB - نظام إدارة حلقات القرآن</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Fallback to CDN when Vite manifest is not available -->
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
        
        <style>
            body {
                font-family: 'IBM Plex Sans Arabic', sans-serif;
                color: #1a202c;
                margin: 0;
                padding: 0;
                background-color: #f5f7fa; /* تغيير لون الخلفية إلى لون فاتح */
            }
            
            .bg-image {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('{{ asset('images/خلفية.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                z-index: -1;
                opacity: 0.9;
            }
            
            .btn-primary {
                background-color: #4338CA;
                padding: 0.75rem 1.5rem;
                color: white;
                border-radius: 0.5rem;
                font-weight: 600;
                transition: background-color 0.2s;
                text-decoration: none;
                display: inline-block;
            }
            
            .btn-primary:hover {
                background-color: #3730a3;
            }
            
            .welcome-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            
            .welcome-content {
                max-width: 1000px;
                width: 100%;
                display: flex;
                background-color: #ffffff; /* تغيير لون خلفية المربع إلى أبيض */
                border-radius: 0.5rem;
                overflow: hidden;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
            
            .logo-container {
                padding: 2.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f8fafc; /* تغيير لون خلفية الشعار إلى لون فاتح متناسق */
            }
            
            .welcome-text {
                flex: 1;
                padding: 2.5rem;
                display: flex;
                flex-direction: column;
                justify-content: center;
                background: linear-gradient(135deg, #4338CA, #6366F1); /* تغيير لون خلفية النص من أحمر إلى تدرج أزرق */
                color: white;
                border-top-right-radius: 0.5rem;
                border-bottom-right-radius: 0.5rem;
            }
            
            .welcome-list {
                list-style-type: none;
                padding-right: 1rem;
                margin: 1.5rem 0;
            }
            
            .welcome-list li {
                position: relative;
                padding-right: 1.5rem;
                margin-bottom: 0.75rem;
            }
            
            .welcome-list li:before {
                content: "";
                position: absolute;
                right: 0;
                top: 0.5rem;
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 50%;
                background-color: white;
            }
            
            @media (max-width: 768px) {
                .welcome-content {
                    flex-direction: column-reverse;
                }
                
                .welcome-text {
                    border-radius: 0;
                }
            }
        </style>
    </head>
    <body>
        <!-- Background Image -->
        <div class="bg-image"></div>
        
        <div class="welcome-container">
            <div class="welcome-content">
                <div class="logo-container">
                    <img src="{{ asset('images/logo_home.png') }}" alt="GARB Logo" class="h-48 w-auto">
                </div>
                <div class="welcome-text">
                    <h1 class="text-4xl font-bold mb-4">هيا نبدأ</h1>
                    <p class="text-lg mb-4">
                        نظام إدارة حلقات القرآن يمتلك بيئة متكاملة .
                        
                    </p>
                    <ul class="welcome-list">
                        <li>مرحلة تجريبية</li>
                        <li> غير مكتمل وينقصه الكثير</li>
                    </ul>
                    <div class="mt-4">
                        <a href="/admin" class="btn-primary">
                            انتقل للوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
