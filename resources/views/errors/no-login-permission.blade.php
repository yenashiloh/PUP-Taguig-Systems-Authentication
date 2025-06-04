<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Login Permission - PUP-Taguig Systems</title>
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
        .permissions-list {
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
                <i class="fas fa-user-lock"></i>
            </div>
            
            <h2 class="error-title">Insufficient Permissions</h2>
            <p class="text-muted mb-4">This API key does not have permission to access the login functionality.</p>
            
            @if(request('error_detail'))
                <div class="error-details">
                    <strong>Error Details:</strong><br>
                    {{ request('error_detail') }}
                </div>
            @endif
            
            <div class="permissions-list">
                <h6><i class="fas fa-shield-alt me-2"></i>Required Permissions:</h6>
                <ul class="text-start mb-0">
                    <li><strong>User Login Access</strong> - Allow users to login through this application</li>
                    <li><strong>Basic Authentication</strong> - Basic API authentication features</li>
                </ul>
            </div>
            
            <div class="solution-steps">
                <h5><i class="fas fa-tools me-2"></i>How to Fix This:</h5>
                <ol class="mb-0">
                    <li><strong>Edit API Key:</strong>
                        <ul>
                            <li>Contact Admin for API Keys</li>
                            <li>Check the "User Login Access" permission</li>
                            <li>Save the changes</li>
                        </ul>
                    </li>
                    <li><strong>Alternative:</strong>
                        <ul>
                            <li>Contact Admin to generate a new API key with the correct permissions</li>
                        </ul>
                    </li>
                </ol>
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