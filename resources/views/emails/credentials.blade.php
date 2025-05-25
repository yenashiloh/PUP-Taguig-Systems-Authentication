<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP-Taguig Account Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #7e0e09;
            color: white;
            padding: 20px;
            text-align: center;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .content {
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .logo {
            max-width: 120px;
            margin: 0 auto;
            display: block;
        }
        .credentials {
            background-color: #f9f9f9;
            border-left: 3px solid #7e0e09;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #7e0e09;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 20px;
        }
        .credential-item {
            margin-bottom: 10px;
        }
        .credential-label {
            font-weight: bold;
            color: #7e0e09;
        }
        .credential-value {
            font-family: monospace;
            padding: 5px;
            background-color: #f0f0f0;
            border-radius: 3px;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to PUP-Taguig Systems Authentication</h2>
        </div>
        <div class="content">
            <p>Dear {{$user->first_name}},</p>
            <p>Your account for the PUP-Taguig Systems has been successfully created. Below are your login credentials:</p>
            
            <div class="credentials">
                <div class="credential-item">
                    <div class="credential-label">Email Address:</div>
                    <div class="credential-value">{{$user->email}}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Password:</div>
                    <div class="credential-value">{{$password}}</div>
                </div>
            </div>
            
            <p><strong>Important:</strong> For security reasons, please change your password after your first login.</p>
            
            <div style="text-align: center;">
                <a href="http://127.0.0.1:8000" class="button" style="color: #ffffff !important;">Login Now</a>
            </div>
            
            <p>If you have any questions or need assistance, please contact our support team at puptloginsystem69@gmail.com</p>
            
            <p>Thank you,<br>PUP-Taguig Administration</p>
        </div>
        <div class="footer">
            <p>&copy; 2025 Polytechnic University of the Philippines - Taguig Branch. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>