<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP-Taguig API Key Information</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #7e0e09, #a01116);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }
        .content {
            padding: 20px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #f39c12;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table th,
        .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .info-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #7e0e09;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            background-color: #7e0e09;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: 600;
        }
        .permission-item {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">📧 API Key Information</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">PUP-Taguig Systems Authentication</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $apiKey->developer_name }}</strong>,</p>
            
            <p>This email contains information about your API key for <strong>{{ $apiKey->application_name }}</strong>.</p>

            @if($customMessage)
                <div class="info-box">
                    <h4 style="color: #7e0e09; margin-top: 0;">💬 Message from {{ $adminName }}</h4>
                    <p style="margin-bottom: 0;">{{ $customMessage }}</p>
                </div>
            @endif

            <div class="warning-box">
                <strong>🔐 Security Notice:</strong><br>
                For security reasons, your actual API key is not included in this email. 
                Please contact the system administrator ({{ $adminName }}) through a secure channel to receive your API key.
            </div>

            <h3 style="color: #7e0e09;">📋 API Key Details</h3>
            <table class="info-table">
                <tr>
                    <th>Application Name</th>
                    <td>{{ $apiKey->application_name }}</td>
                </tr>
                <tr>
                    <th>Rate Limit</th>
                    <td>{{ number_format($apiKey->request_limit_per_minute) }} requests per minute</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($apiKey->is_active)
                            <span style="color: #28a745; font-weight: bold;">✅ Active</span>
                        @else
                            <span style="color: #dc3545; font-weight: bold;">❌ Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Created Date</th>
                    <td>{{ $apiKey->created_at->format('F d, Y \a\t h:i A') }}</td>
                </tr>
                @if($apiKey->expires_at)
                <tr>
                    <th>Expires At</th>
                    <td>{{ $apiKey->expires_at->format('F d, Y \a\t h:i A') }}</td>
                </tr>
                @endif
                @if($apiKey->description)
                <tr>
                    <th>Description</th>
                    <td>{{ $apiKey->description }}</td>
                </tr>
                @endif
            </table>

            <h3 style="color: #7e0e09;">🔧 Permissions Granted</h3>
            <div style="background-color: #e7f3ff; border-radius: 5px; padding: 15px; margin: 15px 0;">
                @foreach($apiKey->formatted_permissions as $permission)
                    <span class="permission-item">{{ $permission }}</span>
                @endforeach
            </div>

            @if(!empty($apiKey->allowed_domains))
            <h3 style="color: #7e0e09;">🌐 Allowed Domains</h3>
            <div style="background-color: #e7f3ff; border-radius: 5px; padding: 15px; margin: 15px 0;">
                @foreach($apiKey->allowed_domains as $domain)
                    <span class="permission-item" style="background-color: #28a745;">{{ $domain }}</span>
                @endforeach
            </div>
            @endif

            <h3 style="color: #7e0e09;">🚀 API Information</h3>
            <table class="info-table">
                <tr>
                    <th>Base URL</th>
                    <td>{{ url('/api') }}</td>
                </tr>
                <tr>
                    <th>Authentication</th>
                    <td>Include your API key in the <code>X-API-Key</code> header</td>
                </tr>
                <tr>
                    <th>Documentation</th>
                    <td><a href="{{ url('/api/documentation') }}">{{ url('/api/documentation') }}</a></td>
                </tr>
            </table>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/api/documentation') }}" class="button">
                    📖 View API Documentation
                </a>
            </div>

            <h3 style="color: #7e0e09;">📞 Next Steps</h3>
            <ol>
                <li><strong>Contact the administrator:</strong> Reach out to {{ $adminName }} to securely receive your API key</li>
                <li><strong>Store securely:</strong> Once received, store the API key in environment variables</li>
                <li><strong>Test integration:</strong> Use the documentation to test your first API calls</li>
                <li><strong>Contact support:</strong> Email puptloginsystem69@gmail.com for technical assistance</li>
            </ol>

            <div class="warning-box">
                <strong>🔄 Important Reminders:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Never share your API key in public channels</li>
                    <li>Store the key securely using environment variables</li>
                    <li>Contact the administrator if you suspect the key is compromised</li>
                    <li>Regularly monitor your API usage through the provided endpoints</li>
                </ul>
            </div>

            <p>Thank you for using PUP-Taguig Systems API!</p>
            
            <p>Best regards,<br>
            <strong>{{ $adminName }}</strong><br>
            PUP-Taguig Administration Team</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Polytechnic University of the Philippines - Taguig Branch. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p style="font-size: 11px; color: #999;">
                API Key ID: {{ $apiKey->id }} | Sent on {{ now()->format('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>