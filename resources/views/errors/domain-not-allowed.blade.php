<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Not Allowed - PUP-Taguig Systems</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #7e0e09;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 700px;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #ffc107;
            margin-bottom: 20px;
        }
        .error-title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 15px;
        }
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background: #6b0c08;
            border-color: #6b0c08;
        }
        .error-details {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .solution-steps {
            text-align: left;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .domain-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-globe"></i>
                <i class="fas fa-ban position-absolute" style="margin-left: -1rem; margin-top: 1rem; font-size: 2rem; color: #dc3545;"></i>
            </div>
            
            <h2 class="error-title">Domain Not Allowed</h2>
            <p class="text-muted mb-4">Your current domain is not authorized to use this API key.</p>
            
            <div class="domain-info">
                <strong>Current Domain:</strong> {{ request()->getHost() }}<br>
                <strong>Current URL:</strong> {{ request()->url() }}
            </div>
            
            @if(request('error_detail'))
                <div class="error-details">
                    <strong>Error Details:</strong><br>
                    {{ request('error_detail') }}
                </div>
            @endif
            
            <div class="solution-steps">
                <h5><i class="fas fa-tools me-2"></i>How to Fix This:</h5>
                <ol class="mb-3">
                    <li><strong>For Development:</strong>
                        <ul>
                            <li>Use <code>http://127.0.0.1:8000</code> or <code>http://localhost:8000</code></li>
                            <li>These domains are automatically allowed for testing</li>
                        </ul>
                    </li>
                    <li><strong>For Production:</strong>
                        <ul>
                            <li>Use <code>https://pupt-registration.site</code></li>
                            <li>Contact admin to add your domain to the API key whitelist</li>
                        </ul>
                    </li>
                </ol>
                
                <div class="alert alert-info mb-0">
                    <strong>Supported Domains:</strong><br>
                    • Development: <code>localhost</code>, <code>127.0.0.1</code> (any port)<br>
                    • Production: <code>pupt-registration.site</code>, <code>www.pupt-registration.site</code>
                </div>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Domain restrictions help keep your API key secure by limiting where it can be used.
                </small>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            if (history.length > 1) {
                history.back();
            } else {
                window.location.href = '{{ route("external.student-management") }}';
            }
        }
    </script>
</body>
</html>