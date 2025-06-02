<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid API Key - PUP-Taguig</title>
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
                <div style="text-align: center; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h2>Invalid API Key</h2>
                    <p style="color: #666; margin: 20px 0;">
                        The provided API key is invalid, expired, or inactive.<br>
                        Please contact the application developer.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>