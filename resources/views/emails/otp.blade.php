<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .message {
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            margin: 20px 0;
        }
        .warning {
            background-color: #fff3cd;
            border-right: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>مرحباً {{ $userName }} 👋</h1>
        </div>
        
        <div class="content">
            <p class="message">
                شكراً لاستخدامك تطبيق <strong>Mishwar Bicklate</strong>
            </p>
            
            <p class="message">
                استخدم رمز التحقق التالي لإكمال عملية التسجيل:
            </p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otpCode }}</div>
            </div>
            
            <div class="warning">
                ⚠️ <strong>تنبيه:</strong> هذا الرمز صالح لمدة 10 دقائق فقط. لا تشارك هذا الرمز مع أي شخص.
            </div>
            
            <p class="message" style="font-size: 14px; color: #666;">
                إذا لم تطلب هذا الرمز، يمكنك تجاهل هذا الإيميل بأمان.
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Mishwar Bicklate. جميع الحقوق محفوظة.</p>
            <p>هذا إيميل تلقائي، يرجى عدم الرد عليه.</p>
        </div>
    </div>
</body>
</html>

