<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Required - PUP-Taguig</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/PUPLogo.png') }}">
</head>
<body>
    <div class="main-container">
        <div class="bg-image"></div>
        <div class="diagonal-overlay"></div>
        <div class="logo-form-bg"></div>

        <div class="logo-container">
            <img src="{{ asset('assets/images/PUPLogo.png') }}" alt="PUP Logo" class="logo">
        </div>

        <div class="login-form-container">
            <div class="login-form">
                <div style="text-align: center; color: #7e0e09;">
                    <i class="fas fa-key" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h2>API Key Required</h2>
                    <p style="color: #666; margin: 20px 0;">
                        This login page requires a valid API key to access.<br>
                        Please contact the application developer.
                    </p>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <strong>For testing:</strong><br>
                        <code>http://127.0.0.1:8000/external/login?api_key=YOUR_API_KEY</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
