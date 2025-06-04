<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid API Key - PUP-Taguig Systems</title>
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
            max-width: 600px;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
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
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .solution-steps {
            text-align: left;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-key"></i>
                <i class="fas fa-times-circle position-absolute" style="margin-left: -1rem; margin-top: 1rem; font-size: 2rem;"></i>
            </div>
            
            <h2 class="error-title">Invalid API Key</h2>
            <p class="text-muted mb-4">The API key you provided is invalid, expired, or has been revoked.</p>
            
            @if(request('error_detail'))
                <div class="error-details">
                    <strong>Error Details:</strong><br>
                    {{ request('error_detail') }}
                </div>
            @endif
            
            @if(request('api_key'))
                <div class="error-details">
                    <strong>Provided API Key:</strong><br>
                    <code>{{ Str::limit(request('api_key'), 20, '...') }}</code>
                </div>
            @endif
            
            <div class="solution-steps">
                <h5><i class="fas fa-wrench me-2"></i>How to Fix This:</h5>
                <ol class="mb-0">
                    <li><strong>Check API Key:</strong> Verify that you're using the correct API key</li>
                    <li><strong>Check Expiration:</strong> Ensure your API key hasn't expired</li>
                    <li><strong>Check Status:</strong> Make sure the API key is still active</li>
                    <li><strong>Generate New Key:</strong> If needed, please contact the admin to generate a new API key</li>
                </ol>
            </div>
        
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    If you continue to experience issues, please contact the puptloginsystem69@gmail.com
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